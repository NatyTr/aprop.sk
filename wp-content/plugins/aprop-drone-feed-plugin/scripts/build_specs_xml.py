#!/usr/bin/env python3
"""Build a local XML feed enriched with specifications scraped via Selenium."""

from __future__ import annotations

import argparse
import datetime as dt
import html
import json
import os
import re
import shutil
import time
import urllib.parse
import urllib.request
import xml.etree.ElementTree as ET
from html.parser import HTMLParser
from pathlib import Path
from typing import Any

from bs4 import BeautifulSoup, Tag


FEED_URL = (
    "https://feeds.mergado.com/"
    "enterra-sk-google-nakupy-sk-70a3cb5ee9479a6525566d5af13a3fe6.xml"
)
GOOGLE_NS = "http://base.google.com/ns/1.0"
APROP_NS = "https://aprop.sk/feed/specs"
DRONE_CATEGORY_PAGES = {
    "Agriculture": {
        "path": "Home > Drony > Agriculture",
        "urls": ["https://www.enterra.sk/shop/drones/agriculture/"],
    },
    "Enterprise": {
        "path": "Home > Drony > Enterprise",
        "urls": [
            "https://www.enterra.sk/shop/drones/enterprise/",
            "https://www.enterra.sk/shop/drones/enterprise/page/2/",
        ],
    },
    "AUTEL": {
        "path": "Home > Drony > AUTEL",
        "urls": ["https://www.enterra.sk/shop/drones/autel/"],
    },
}


ET.register_namespace("g", GOOGLE_NS)
ET.register_namespace("aprop", APROP_NS)


def local_name(tag: str) -> str:
    return tag.rsplit("}", 1)[-1] if "}" in tag else tag


def collapse_space(value: str) -> str:
    return " ".join(html.unescape(value).split())


def normalize_source_sku(value: Any) -> str:
    normalized = re.sub(r"[\u00ad\u200b-\u200d\u2060\ufeff]", "", str(value or ""))
    return collapse_space(normalized).upper()


def child_text(element: ET.Element, name: str) -> str:
    for child in element:
        if local_name(child.tag) == name and child.text:
            return collapse_space(child.text)
    return ""


def fetch_url(url: str, timeout: int) -> bytes:
    request = urllib.request.Request(
        url,
        headers={
            "User-Agent": "ApropDroneFeedPlugin-spec-builder/1.0",
            "Accept": "application/xml,text/xml,*/*",
        },
    )
    with urllib.request.urlopen(request, timeout=timeout) as response:
        return response.read()


def fetch_html(url: str, timeout: int) -> str:
    request = urllib.request.Request(
        url,
        headers={
            "User-Agent": "ApropDroneFeedPlugin-spec-builder/1.0",
            "Accept": "text/html,application/xhtml+xml,*/*",
        },
    )
    with urllib.request.urlopen(request, timeout=timeout) as response:
        return response.read().decode(response.headers.get_content_charset() or "utf-8", errors="replace")


def canonical_product_url(url: str) -> str:
    parsed = urllib.parse.urlsplit(url)
    path = parsed.path.rstrip("/") + "/" if parsed.path else "/"
    return urllib.parse.urlunsplit((parsed.scheme, parsed.netloc, path, "", ""))


def cache_key_for_product_url(url: str) -> str:
    parsed = urllib.parse.urlsplit(url)
    return urllib.parse.urlunsplit((parsed.scheme, parsed.netloc, parsed.path, "", ""))


def url_with_spec_hash(url: str) -> str:
    parsed = urllib.parse.urlsplit(url)
    return urllib.parse.urlunsplit((parsed.scheme, parsed.netloc, parsed.path, parsed.query, "tab-specifications"))


def class_contains(attrs: list[tuple[str, str | None]], class_name: str) -> bool:
    classes = ""
    for key, value in attrs:
        if key == "class" and value:
            classes = value
            break
    return class_name in classes.split()


def attr_value(attrs: list[tuple[str, str | None]], name: str) -> str:
    for key, value in attrs:
        if key == name and value is not None:
            return value
    return ""


def image_url_sort_key(url: str) -> tuple[int, int]:
    parsed = urllib.parse.urlsplit(url)
    path = parsed.path
    match = re.search(r"-(\d+)x(\d+)(?=\.[^.]+(?:\.webp)?$)", path)
    if not match:
        return (0, 0)

    width = int(match.group(1))
    height = int(match.group(2))
    return (width * height, width)


def largest_srcset_url(srcset: str, fallback: str = "") -> str:
    candidates: list[tuple[int, str]] = []

    for candidate in srcset.split(","):
        parts = candidate.strip().split()
        if not parts:
            continue

        url = parts[0]
        width = 0
        if len(parts) > 1 and parts[1].endswith("w"):
            try:
                width = int(parts[1][:-1])
            except ValueError:
                width = 0

        candidates.append((width, url))

    if not candidates:
        return fallback

    return max(candidates, key=lambda item: item[0])[1]


def absolute_page_url(value: str, source_url: str) -> str:
    value = str(value or "").strip()
    if not value:
        return ""
    return urllib.parse.urljoin(source_url, value)


def full_size_image_url(image: Tag, source_url: str) -> str:
    for attribute in (
        "data-zoom-image",
        "data-o_zoom-image",
        "data-large_image",
    ):
        value = image.get(attribute)
        if value:
            return absolute_page_url(str(value), source_url)

    srcset = str(image.get("data-o_srcset") or image.get("data-srcset") or image.get("srcset") or "")
    largest_srcset = largest_srcset_url(srcset)
    if largest_srcset:
        return absolute_page_url(largest_srcset, source_url)

    return absolute_page_url(str(image.get("data-src") or image.get("data-o_src") or image.get("src") or ""), source_url)


def clean_html_fragment(element: Tag | None, source_url: str) -> str:
    if element is None:
        return ""

    fragment = BeautifulSoup(element.decode_contents(), "html.parser")
    for removable in fragment.select("script, style, noscript, template"):
        removable.decompose()

    for tag in fragment.find_all(True):
        for attribute in list(tag.attrs):
            if attribute.lower().startswith("on") or attribute.lower() == "srcdoc":
                del tag.attrs[attribute]

        if tag.name == "img":
            image_url = full_size_image_url(tag, source_url)
            if image_url:
                tag["src"] = image_url
            for attribute in (
                "srcset",
                "sizes",
                "data-src",
                "data-srcset",
                "data-o_src",
                "data-o_srcset",
                "data-zoom-image",
                "data-o_zoom-image",
                "data-large_image",
            ):
                tag.attrs.pop(attribute, None)

        if tag.name in {"iframe", "video", "source"} and tag.get("src"):
            tag["src"] = absolute_page_url(str(tag["src"]), source_url)

        if tag.name == "video" and tag.get("poster"):
            tag["poster"] = absolute_page_url(str(tag["poster"]), source_url)

        if tag.name == "a" and tag.get("href"):
            tag["href"] = absolute_page_url(str(tag["href"]), source_url)

    return "".join(str(child) for child in fragment.contents).strip()


def html_media_urls(html_value: str) -> dict[str, list[str]]:
    fragment = BeautifulSoup(html_value or "", "html.parser")
    images = []
    videos = []

    for image in fragment.select("img[src]"):
        url = str(image.get("src") or "").strip()
        if url and url not in images:
            images.append(url)

    for media in fragment.select("iframe[src], video[src], source[src]"):
        url = str(media.get("src") or "").strip()
        if url and url not in videos:
            videos.append(url)

    return {"images": images, "videos": videos}


def extract_related_products(soup: BeautifulSoup, selector: str, source_url: str) -> list[dict[str, str]]:
    root = soup.select_one(selector)
    if root is None:
        return []

    products: list[dict[str, str]] = []
    seen_skus: set[str] = set()
    for trigger in root.select("[data-product_sku]"):
        sku = normalize_source_sku(trigger.get("data-product_sku"))
        if not sku or sku in seen_skus:
            continue

        card = trigger.find_parent(class_="cross-sells__product") or trigger.find_parent("li")
        title_node = card.select_one("h2, h3, h4, .woocommerce-loop-product__title") if isinstance(card, Tag) else None
        link_node = card.select_one('a[href*="/produkt/"]') if isinstance(card, Tag) else None

        products.append(
            {
                "sku": sku,
                "source_id": collapse_space(str(trigger.get("data-product_id") or "")),
                "title": collapse_space(title_node.get_text(" ", strip=True)) if title_node else "",
                "url": absolute_page_url(str(link_node.get("href") or ""), source_url) if link_node else "",
            }
        )
        seen_skus.add(sku)

    return products


class DroneCategoryParser(HTMLParser):
    """Parser for Enterra WooCommerce category listing cards."""

    def __init__(self) -> None:
        super().__init__(convert_charrefs=True)
        self.in_product = False
        self.product_depth = 0
        self.current_url = ""
        self.current_title = ""
        self.capture_title = False
        self.capture_depth = 0
        self.capture_parts: list[str] = []
        self.products: list[dict[str, str]] = []

    def handle_starttag(self, tag: str, attrs: list[tuple[str, str | None]]) -> None:
        if tag == "li" and class_contains(attrs, "product"):
            self.in_product = True
            self.product_depth = 1
            self.current_url = ""
            self.current_title = ""
            return

        if not self.in_product:
            return

        self.product_depth += 1

        href = attr_value(attrs, "href")
        if tag == "a" and "/produkt/" in href and not self.current_url:
            self.current_url = canonical_product_url(href)

        if tag in {"h2", "h3"} and class_contains(attrs, "woocommerce-loop-product__title"):
            self.capture_title = True
            self.capture_depth = 1
            self.capture_parts = []
            return

        if self.capture_title:
            self.capture_depth += 1

    def handle_endtag(self, tag: str) -> None:
        if not self.in_product:
            return

        if self.capture_title:
            self.capture_depth -= 1
            if self.capture_depth <= 0:
                self.current_title = collapse_space(" ".join(self.capture_parts))
                self.capture_title = False
                self.capture_parts = []

        self.product_depth -= 1
        if self.product_depth <= 0:
            if self.current_url:
                self.products.append({"url": self.current_url, "title": self.current_title})
            self.in_product = False

    def handle_data(self, data: str) -> None:
        if self.capture_title:
            self.capture_parts.append(data)


def extract_page_data_from_html(page_html: str, source_url: str = "") -> dict[str, Any]:
    soup = BeautifulSoup(page_html, "html.parser")

    specs: list[dict[str, str]] = []
    current_section = ""
    specs_panel = soup.select_one("#tab-specifications")
    if specs_panel is not None:
        for element in specs_panel.select(".specification__main-title, .specification__content"):
            classes = element.get("class") or []
            if "specification__main-title" in classes:
                current_section = collapse_space(element.get_text(" ", strip=True))
                continue

            name_node = element.select_one(".specification-content__title")
            value_node = element.select_one(".specification-content__description")
            name = collapse_space(name_node.get_text(" ", strip=True)) if name_node else ""
            value = collapse_space(value_node.get_text(" ", strip=True)) if value_node else ""
            if name or value:
                specs.append({"section": current_section, "name": name, "value": value})

    products: list[dict[str, str]] = []
    products_panel = soup.select_one("#tab-products")
    if products_panel is not None:
        for element in products_panel.select(".specification__content"):
            name_node = element.select_one(".specification-content__title")
            quantity_node = element.select_one(".specification-content__description")
            name = collapse_space(name_node.get_text(" ", strip=True)) if name_node else ""
            quantity = collapse_space(quantity_node.get_text(" ", strip=True)) if quantity_node else ""
            if name or quantity:
                products.append({"name": name, "quantity": quantity})

    images: list[dict[str, str]] = []
    videos: list[dict[str, str]] = []
    seen_images: set[str] = set()
    seen_videos: set[str] = set()
    gallery_slides = soup.select(".nickx-slider-for .nswiper-slide")

    if gallery_slides:
        for slide in gallery_slides:
            image = slide.select_one("img")
            if image is not None:
                image_url = full_size_image_url(image, source_url)
                if image_url and image_url not in seen_images and "woocommerce-placeholder" not in image_url:
                    images.append(
                        {
                            "url": image_url,
                            "alt": collapse_space(str(image.get("alt") or "")),
                            "title": collapse_space(str(image.get("title") or "")),
                        }
                    )
                    seen_images.add(image_url)

            media = slide.select_one("iframe[src], video[src], source[src]")
            if media is not None:
                media_url = absolute_page_url(str(media.get("src") or ""), source_url)
                if media_url and media_url not in seen_videos:
                    videos.append(
                        {
                            "url": media_url,
                            "type": media.name or "video",
                            "title": collapse_space(str(media.get("title") or "")),
                        }
                    )
                    seen_videos.add(media_url)
    else:
        gallery_root = soup.select_one(".woocommerce-product-gallery")
        if gallery_root is not None:
            for image in gallery_root.select("img"):
                image_url = full_size_image_url(image, source_url)
                if image_url and image_url not in seen_images and "woocommerce-placeholder" not in image_url:
                    images.append(
                        {
                            "url": image_url,
                            "alt": collapse_space(str(image.get("alt") or "")),
                            "title": collapse_space(str(image.get("title") or "")),
                        }
                    )
                    seen_images.add(image_url)

    tabs: list[dict[str, Any]] = []
    seen_tab_ids: set[str] = set()
    for link in soup.select('.woocommerce-tabs .wc-tabs a[href^="#tab-"]'):
        tab_id = str(link.get("href") or "").lstrip("#")
        if not tab_id or tab_id in seen_tab_ids:
            continue

        panel = soup.find(id=tab_id)
        if not isinstance(panel, Tag):
            continue

        tab_html = clean_html_fragment(panel, source_url)
        media = html_media_urls(tab_html)
        tabs.append(
            {
                "id": tab_id.removeprefix("tab-"),
                "panel_id": tab_id,
                "title": collapse_space(link.get_text(" ", strip=True)),
                "html": tab_html,
                "text": collapse_space(panel.get_text(" ", strip=True)),
                "images": media["images"],
                "videos": media["videos"],
            }
        )
        seen_tab_ids.add(tab_id)

    short_description = soup.select_one(".woocommerce-product-details__short-description")
    source_product = soup.select_one(".product.type-product")
    source_classes = [str(value) for value in (source_product.get("class") or [])] if source_product else []

    def selected_text(selector: str) -> str:
        node = soup.select_one(selector)
        return collapse_space(node.get_text(" ", strip=True)) if node else ""

    source_data = {
        "stock_label": selected_text(".summary .product_meta .stock-backorder, .summary .stock-backorder, .summary .stock"),
        "delivery_text": selected_text("form.cart .cart__description"),
        "price_including_tax": selected_text(".summary .main-product-item__inc-price"),
        "price_excluding_tax": selected_text(".summary .main-product-item__ext-price"),
        "source_tags": [value.removeprefix("product_tag-") for value in source_classes if value.startswith("product_tag-")],
        "source_flags": [
            value
            for value in ("featured", "taxable", "shipping-taxable", "purchasable")
            if value in source_classes
        ],
        "source_product_type": next(
            (value.removeprefix("product-type-") for value in source_classes if value.startswith("product-type-")),
            "",
        ),
    }

    return {
        "specs": specs,
        "products": products,
        "images": images,
        "videos": videos,
        "tabs": tabs,
        "short_description_html": clean_html_fragment(short_description, source_url),
        "related_products": {
            "cross_sell": extract_related_products(soup, ".cross-sells", source_url),
            "upsell": extract_related_products(soup, ".up-sells.upsells", source_url),
        },
        "source_data": source_data,
    }


def extract_category_products_from_html(page_html: str) -> list[dict[str, str]]:
    parser = DroneCategoryParser()
    parser.feed(page_html)
    return parser.products


def scrape_drone_web_categories(timeout: int) -> dict[str, list[dict[str, str]]]:
    products_by_url: dict[str, list[dict[str, str]]] = {}

    for category_name, category in DRONE_CATEGORY_PAGES.items():
        for category_url in category["urls"]:
            page_html = fetch_html(category_url, timeout)
            for product in extract_category_products_from_html(page_html):
                product_url = product.get("url", "")
                if not product_url:
                    continue

                rows = products_by_url.setdefault(product_url, [])
                if any(row["name"] == category_name for row in rows):
                    continue

                rows.append(
                    {
                        "name": category_name,
                        "path": category["path"],
                        "category_url": category_url,
                        "product_title": product.get("title", ""),
                    }
                )

    return products_by_url


def create_driver(args: argparse.Namespace):
    try:
        if not args.driver_path and not args.use_path_driver:
            remove_driver_from_path(args.browser)

        if args.browser == "firefox":
            from selenium import webdriver
            from selenium.webdriver.firefox.options import Options
            from selenium.webdriver.firefox.service import Service

            options = Options()
            if args.headless:
                options.add_argument("--headless")
            service = Service(executable_path=args.driver_path) if args.driver_path else Service()
            return webdriver.Firefox(service=service, options=options)

        from selenium import webdriver
        from selenium.webdriver.chrome.options import Options
        from selenium.webdriver.chrome.service import Service

        options = Options()
        if args.headless:
            options.add_argument("--headless=new")
        options.add_argument("--disable-dev-shm-usage")
        options.add_argument("--disable-gpu")
        options.add_argument("--no-sandbox")
        options.add_argument("--window-size=1440,1400")
        service = Service(executable_path=args.driver_path) if args.driver_path else Service()
        return webdriver.Chrome(service=service, options=options)
    except Exception as exc:
        raise SystemExit(
            "Could not start Selenium browser. Install a compatible browser driver "
            "or pass --driver-path. Original error: " + str(exc)
        ) from exc


def remove_driver_from_path(browser: str) -> None:
    driver_name = "geckodriver" if browser == "firefox" else "chromedriver"
    driver_path = shutil.which(driver_name)
    if not driver_path:
        return

    driver_dir = str(Path(driver_path).parent)
    resolved_driver_dir = str(Path(driver_path).resolve().parent)
    path_parts = os.environ.get("PATH", "").split(os.pathsep)
    os.environ["PATH"] = os.pathsep.join(
        part
        for part in path_parts
        if str(Path(part)) not in {driver_dir, resolved_driver_dir}
        and str(Path(part).resolve()) not in {driver_dir, resolved_driver_dir}
    )


def scrape_page_data_with_selenium(driver: Any, url: str, timeout: int) -> dict[str, Any]:
    from selenium.common.exceptions import TimeoutException
    from selenium.webdriver.common.by import By
    from selenium.webdriver.support import expected_conditions as EC
    from selenium.webdriver.support.ui import WebDriverWait

    driver.set_page_load_timeout(timeout)
    driver.get(url_with_spec_hash(url))

    try:
        WebDriverWait(driver, timeout).until(EC.presence_of_element_located((By.TAG_NAME, "body")))
        WebDriverWait(driver, min(timeout, 12)).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, "#tab-specifications"))
        )
    except TimeoutException:
        return extract_page_data_from_html(driver.page_source, url)

    return extract_page_data_from_html(driver.page_source, url)



def quit_driver(driver: Any | None) -> None:
    if driver is None:
        return

    try:
        driver.quit()
    except Exception:
        pass


def add_specs_to_item(item: ET.Element, specs: list[dict[str, str]], source_url: str) -> None:
    for child in list(item):
        if child.tag == f"{{{APROP_NS}}}specifications":
            item.remove(child)

    root = ET.SubElement(
        item,
        f"{{{APROP_NS}}}specifications",
        {
            "source_url": source_url,
            "fetched_at": dt.datetime.now(dt.timezone.utc).isoformat(),
            "count": str(len(specs)),
        },
    )

    sections: dict[str, ET.Element] = {}
    for spec in specs:
        section_name = spec.get("section") or "Specifications"
        section = sections.get(section_name)
        if section is None:
            section = ET.SubElement(root, f"{{{APROP_NS}}}section", {"name": section_name})
            sections[section_name] = section

        spec_el = ET.SubElement(section, f"{{{APROP_NS}}}spec", {"name": spec.get("name", "")})
        value_el = ET.SubElement(spec_el, f"{{{APROP_NS}}}value")
        value_el.text = spec.get("value", "")


def add_products_to_item(item: ET.Element, products: list[dict[str, str]], source_url: str) -> None:
    for child in list(item):
        if child.tag == f"{{{APROP_NS}}}products":
            item.remove(child)

    root = ET.SubElement(
        item,
        f"{{{APROP_NS}}}products",
        {
            "source_url": source_url,
            "fetched_at": dt.datetime.now(dt.timezone.utc).isoformat(),
            "count": str(len(products)),
        },
    )

    for product in products:
        product_el = ET.SubElement(root, f"{{{APROP_NS}}}product")
        name_el = ET.SubElement(product_el, f"{{{APROP_NS}}}name")
        name_el.text = product.get("name", "")
        quantity_el = ET.SubElement(product_el, f"{{{APROP_NS}}}quantity")
        quantity_el.text = product.get("quantity", "")


def add_gallery_to_item(
    item: ET.Element,
    images: list[dict[str, str]],
    source_url: str,
    videos: list[dict[str, str]] | None = None,
) -> None:
    for child in list(item):
        if child.tag == f"{{{APROP_NS}}}gallery":
            item.remove(child)

    root = ET.SubElement(
        item,
        f"{{{APROP_NS}}}gallery",
        {
            "source_url": source_url,
            "fetched_at": dt.datetime.now(dt.timezone.utc).isoformat(),
            "count": str(len(images)),
            "video_count": str(len(videos or [])),
        },
    )

    for image in images:
        url = image.get("url", "")
        if not url:
            continue

        ET.SubElement(
            root,
            f"{{{APROP_NS}}}image",
            {
                "url": url,
                "alt": image.get("alt", ""),
                "title": image.get("title", ""),
            },
        )

    for video in videos or []:
        url = video.get("url", "")
        if not url:
            continue

        ET.SubElement(
            root,
            f"{{{APROP_NS}}}video",
            {
                "url": url,
                "type": video.get("type", "video"),
                "title": video.get("title", ""),
            },
        )


def add_content_to_item(item: ET.Element, page_data: dict[str, Any], source_url: str) -> None:
    for child in list(item):
        if child.tag == f"{{{APROP_NS}}}content":
            item.remove(child)

    root = ET.SubElement(
        item,
        f"{{{APROP_NS}}}content",
        {
            "source_url": source_url,
            "fetched_at": dt.datetime.now(dt.timezone.utc).isoformat(),
        },
    )
    short_description = ET.SubElement(root, f"{{{APROP_NS}}}short_description", {"format": "html"})
    short_description.text = str(page_data.get("short_description_html", ""))

    tabs = page_data.get("tabs", []) if isinstance(page_data.get("tabs"), list) else []
    tabs_root = ET.SubElement(root, f"{{{APROP_NS}}}tabs", {"count": str(len(tabs))})
    for tab in tabs:
        if not isinstance(tab, dict):
            continue

        tab_root = ET.SubElement(
            tabs_root,
            f"{{{APROP_NS}}}tab",
            {
                "id": str(tab.get("id", "")),
                "panel_id": str(tab.get("panel_id", "")),
                "title": str(tab.get("title", "")),
                "image_count": str(len(tab.get("images", []))) if isinstance(tab.get("images"), list) else "0",
                "video_count": str(len(tab.get("videos", []))) if isinstance(tab.get("videos"), list) else "0",
            },
        )
        html_node = ET.SubElement(tab_root, f"{{{APROP_NS}}}html", {"format": "html"})
        html_node.text = str(tab.get("html", ""))
        text_node = ET.SubElement(tab_root, f"{{{APROP_NS}}}text")
        text_node.text = str(tab.get("text", ""))

        media_node = ET.SubElement(tab_root, f"{{{APROP_NS}}}media")
        for image_url in tab.get("images", []) if isinstance(tab.get("images"), list) else []:
            ET.SubElement(media_node, f"{{{APROP_NS}}}image", {"url": str(image_url)})
        for video_url in tab.get("videos", []) if isinstance(tab.get("videos"), list) else []:
            ET.SubElement(media_node, f"{{{APROP_NS}}}video", {"url": str(video_url)})


def add_related_products_to_item(item: ET.Element, related_products: dict[str, Any], source_url: str) -> None:
    for child in list(item):
        if child.tag == f"{{{APROP_NS}}}related_products":
            item.remove(child)

    groups = {
        "cross_sell": related_products.get("cross_sell", []) if isinstance(related_products, dict) else [],
        "upsell": related_products.get("upsell", []) if isinstance(related_products, dict) else [],
    }
    total = sum(len(products) for products in groups.values() if isinstance(products, list))
    root = ET.SubElement(
        item,
        f"{{{APROP_NS}}}related_products",
        {
            "source_url": source_url,
            "fetched_at": dt.datetime.now(dt.timezone.utc).isoformat(),
            "count": str(total),
        },
    )

    for group_name, products in groups.items():
        group = ET.SubElement(
            root,
            f"{{{APROP_NS}}}group",
            {"type": group_name, "count": str(len(products) if isinstance(products, list) else 0)},
        )
        for product in products if isinstance(products, list) else []:
            if not isinstance(product, dict) or not product.get("sku"):
                continue
            ET.SubElement(
                group,
                f"{{{APROP_NS}}}product",
                {
                    "sku": str(product.get("sku", "")),
                    "source_id": str(product.get("source_id", "")),
                    "title": str(product.get("title", "")),
                    "url": str(product.get("url", "")),
                },
            )


def add_source_data_to_item(item: ET.Element, source_data: dict[str, Any], source_url: str) -> None:
    for child in list(item):
        if child.tag == f"{{{APROP_NS}}}source_data":
            item.remove(child)

    root = ET.SubElement(
        item,
        f"{{{APROP_NS}}}source_data",
        {
            "source_url": source_url,
            "fetched_at": dt.datetime.now(dt.timezone.utc).isoformat(),
        },
    )
    json_node = ET.SubElement(root, f"{{{APROP_NS}}}json")
    json_node.text = json.dumps(source_data if isinstance(source_data, dict) else {}, ensure_ascii=False)


def add_web_categories_to_item(item: ET.Element, categories: list[dict[str, str]], source_url: str) -> None:
    for child in list(item):
        if child.tag == f"{{{APROP_NS}}}web_categories":
            item.remove(child)

    root = ET.SubElement(
        item,
        f"{{{APROP_NS}}}web_categories",
        {
            "source_url": source_url,
            "fetched_at": dt.datetime.now(dt.timezone.utc).isoformat(),
            "count": str(len(categories)),
        },
    )

    for category in categories:
        name = category.get("name", "")
        path = category.get("path", "")
        if not name or not path:
            continue

        ET.SubElement(
            root,
            f"{{{APROP_NS}}}category",
            {
                "name": name,
                "path": path,
                "url": category.get("category_url", ""),
                "product_title": category.get("product_title", ""),
            },
        )


def find_specs_node(item: ET.Element) -> ET.Element | None:
    for child in item:
        if child.tag == f"{{{APROP_NS}}}specifications":
            return child
    return None


def find_aprop_node(item: ET.Element, name: str) -> ET.Element | None:
    for child in item:
        if child.tag == f"{{{APROP_NS}}}{name}":
            return child
    return None


def has_completed_page_data(item: ET.Element, retry_empty: bool) -> bool:
    node = find_specs_node(item)
    if node is None:
        return False
    if retry_empty and node.attrib.get("count") == "0":
        return False
    required_nodes = ("products", "gallery", "content", "related_products", "source_data")
    return all(find_aprop_node(item, name) is not None for name in required_nodes)


def copy_aprop_nodes(source_item: ET.Element, target_item: ET.Element) -> None:
    copied_tags = {
        f"{{{APROP_NS}}}specifications",
        f"{{{APROP_NS}}}products",
        f"{{{APROP_NS}}}gallery",
        f"{{{APROP_NS}}}web_categories",
        f"{{{APROP_NS}}}content",
        f"{{{APROP_NS}}}related_products",
        f"{{{APROP_NS}}}source_data",
    }

    for child in list(target_item):
        if child.tag in copied_tags:
            target_item.remove(child)

    for child in source_item:
        if child.tag in copied_tags:
            target_item.append(ET.fromstring(ET.tostring(child, encoding="utf-8")))


def iter_items(root: ET.Element) -> list[ET.Element]:
    return [element for element in root.iter() if local_name(element.tag) == "item"]


def item_map_by_id(root: ET.Element) -> dict[str, ET.Element]:
    result = {}
    for item in iter_items(root):
        feed_id = child_text(item, "id")
        if feed_id:
            result[feed_id] = item
    return result


def write_xml(root: ET.Element, output: Path) -> None:
    ET.indent(root, space="  ")
    tmp_output = output.with_suffix(output.suffix + ".tmp")
    ET.ElementTree(root).write(tmp_output, encoding="utf-8", xml_declaration=True)
    os.replace(tmp_output, output)


def load_cache(path: Path) -> dict[str, Any]:
    if not path.exists():
        return {}
    try:
        return json.loads(path.read_text(encoding="utf-8"))
    except json.JSONDecodeError:
        return {}


def save_cache(path: Path, cache: dict[str, Any]) -> None:
    tmp_path = path.with_suffix(path.suffix + ".tmp")
    tmp_path.write_text(json.dumps(cache, ensure_ascii=False, indent=2), encoding="utf-8")
    os.replace(tmp_path, path)


def page_data_from_cache(cache_entry: Any) -> dict[str, Any] | None:
    if (
        not isinstance(cache_entry, dict)
        or not isinstance(cache_entry.get("specs"), list)
        or not isinstance(cache_entry.get("products"), list)
        or not isinstance(cache_entry.get("images"), list)
        or not isinstance(cache_entry.get("videos"), list)
        or not isinstance(cache_entry.get("tabs"), list)
        or not isinstance(cache_entry.get("related_products"), dict)
        or not isinstance(cache_entry.get("source_data"), dict)
        or not isinstance(cache_entry.get("short_description_html"), str)
    ):
        return None

    specs = [
        {
            "section": str(spec.get("section", "")),
            "name": str(spec.get("name", "")),
            "value": str(spec.get("value", "")),
        }
        for spec in cache_entry["specs"]
        if isinstance(spec, dict)
    ]
    products = [
        {
            "name": str(product.get("name", "")),
            "quantity": str(product.get("quantity", "")),
        }
        for product in cache_entry["products"]
        if isinstance(product, dict)
    ]
    images = [
        {
            "url": str(image.get("url", "")),
            "alt": str(image.get("alt", "")),
            "title": str(image.get("title", "")),
        }
        for image in cache_entry["images"]
        if isinstance(image, dict) and str(image.get("url", ""))
    ]
    videos = [dict(video) for video in cache_entry["videos"] if isinstance(video, dict)]
    tabs = [dict(tab) for tab in cache_entry["tabs"] if isinstance(tab, dict)]
    related_products = {
        "cross_sell": [
            dict(product)
            for product in cache_entry["related_products"].get("cross_sell", [])
            if isinstance(product, dict)
        ],
        "upsell": [
            dict(product)
            for product in cache_entry["related_products"].get("upsell", [])
            if isinstance(product, dict)
        ],
    }

    return {
        "specs": specs,
        "products": products,
        "images": images,
        "videos": videos,
        "tabs": tabs,
        "short_description_html": cache_entry["short_description_html"],
        "related_products": related_products,
        "source_data": dict(cache_entry["source_data"]),
    }


def main() -> int:
    parser = argparse.ArgumentParser(
        description="Create a local XML feed enriched with product-page specifications via Selenium."
    )
    parser.add_argument("--feed-url", default=FEED_URL)
    parser.add_argument("--output", default="enterra-feed-with-specifications.xml")
    parser.add_argument("--cache", default="", help="JSON page-spec cache. Defaults to OUTPUT.spec-cache.json.")
    parser.add_argument("--max-products", type=int, default=0, help="Limit products for testing.")
    parser.add_argument("--only-id", action="append", default=[], help="Only scrape this feed product id. Can be repeated.")
    parser.add_argument("--delay", type=float, default=0.25, help="Delay between unique product page requests.")
    parser.add_argument("--timeout", type=int, default=45)
    parser.add_argument("--retries", type=int, default=2, help="Selenium retries per product page after browser/page failures.")
    parser.add_argument("--browser", choices=["chrome", "firefox"], default="chrome")
    parser.add_argument("--driver-path", default="", help="Path to chromedriver/geckodriver if Selenium Manager is not enough.")
    parser.add_argument("--use-path-driver", action="store_true", help="Use chromedriver/geckodriver from PATH instead of forcing Selenium Manager.")
    parser.add_argument("--headless", action=argparse.BooleanOptionalAction, default=True)
    parser.add_argument("--force", action="store_true", help="Re-scrape products even if output already has specs.")
    parser.add_argument("--retry-empty", action="store_true", help="Re-scrape products whose existing spec count is 0.")
    parser.add_argument("--skip-web-categories", action="store_true", help="Do not scrape Enterra drone category pages.")
    args = parser.parse_args()

    output = Path(args.output)
    cache_path = Path(args.cache) if args.cache else output.with_suffix(output.suffix + ".spec-cache.json")

    feed_xml = fetch_url(args.feed_url, args.timeout)
    root = ET.fromstring(feed_xml)
    items = iter_items(root)

    if output.exists() and not args.force:
        previous_root = ET.parse(output).getroot()
        previous_items = item_map_by_id(previous_root)
        for item in items:
            feed_id = child_text(item, "id")
            previous_item = previous_items.get(feed_id)
            if previous_item is not None:
                copy_aprop_nodes(previous_item, item)

    web_categories: dict[str, list[dict[str, str]]] = {}
    if not args.skip_web_categories:
        try:
            web_categories = scrape_drone_web_categories(args.timeout)
        except Exception as exc:
            print(f"Could not scrape Enterra drone categories: {type(exc).__name__}: {exc}", flush=True)

    if web_categories:
        web_category_source = ", ".join(
            url
            for category in DRONE_CATEGORY_PAGES.values()
            for url in category["urls"]
        )
        matched_web_categories = 0
        for item in items:
            link = child_text(item, "link")
            categories = web_categories.get(canonical_product_url(link), [])
            if categories:
                matched_web_categories += 1
            add_web_categories_to_item(item, categories, web_category_source)
        print(f"Drone website categories matched: {matched_web_categories}")

    only_ids = set(args.only_id)
    selected_items = [
        item for item in items if not only_ids or child_text(item, "id") in only_ids
    ]
    if args.max_products > 0:
        selected_items = selected_items[: args.max_products]

    cache = load_cache(cache_path)
    driver = None
    processed = 0
    skipped = 0
    fetched_pages = 0

    print(f"Feed products: {len(items)}")
    print(f"Products selected: {len(selected_items)}")
    print(f"Output: {output}")
    print(f"Cache: {cache_path}")

    try:
        for index, item in enumerate(selected_items, start=1):
            feed_id = child_text(item, "id")
            title = child_text(item, "title")
            link = child_text(item, "link")

            if not args.force and has_completed_page_data(item, args.retry_empty):
                skipped += 1
                count = find_specs_node(item).attrib.get("count", "0")  # type: ignore[union-attr]
                products_count = find_aprop_node(item, "products").attrib.get("count", "0")  # type: ignore[union-attr]
                images_count = find_aprop_node(item, "gallery").attrib.get("count", "0")  # type: ignore[union-attr]
                print(
                    f"[{index}/{len(selected_items)}] skip id={feed_id} "
                    f"specs={count} products={products_count} images={images_count} title={title}"
                )
                continue

            if not link:
                page_data = {
                    "specs": [],
                    "products": [],
                    "images": [],
                    "videos": [],
                    "tabs": [],
                    "short_description_html": "",
                    "related_products": {"cross_sell": [], "upsell": []},
                    "source_data": {},
                }
                add_specs_to_item(item, [], "")
                add_products_to_item(item, [], "")
                add_gallery_to_item(item, [], "", [])
                add_content_to_item(item, page_data, "")
                add_related_products_to_item(item, page_data["related_products"], "")
                add_source_data_to_item(item, page_data["source_data"], "")
                write_xml(root, output)
                processed += 1
                print(f"[{index}/{len(selected_items)}] id={feed_id} missing link")
                continue

            source_url = cache_key_for_product_url(link)
            page_data = None if args.force else page_data_from_cache(cache.get(source_url))

            if page_data is None:
                last_error = None
                for attempt in range(1, max(args.retries, 0) + 2):
                    try:
                        if driver is None:
                            driver = create_driver(args)
                        page_data = scrape_page_data_with_selenium(driver, source_url, args.timeout)
                        last_error = None
                        break
                    except Exception as exc:
                        last_error = exc
                        print(
                            f"[{index}/{len(selected_items)}] retry {attempt} failed "
                            f"id={feed_id} error={type(exc).__name__}: {exc}",
                            flush=True,
                        )
                        quit_driver(driver)
                        driver = None
                        time.sleep(min(2 * attempt, 10))

                if page_data is None:
                    print(
                        f"[{index}/{len(selected_items)}] giving up id={feed_id} "
                        f"after Selenium error={last_error}",
                        flush=True,
                    )
                    page_data = {
                        "specs": [],
                        "products": [],
                        "images": [],
                        "videos": [],
                        "tabs": [],
                        "short_description_html": "",
                        "related_products": {"cross_sell": [], "upsell": []},
                        "source_data": {},
                    }

                cache[source_url] = {
                    "source_url": source_url,
                    "fetched_at": dt.datetime.now(dt.timezone.utc).isoformat(),
                    "count": len(page_data["specs"]),
                    "products_count": len(page_data["products"]),
                    "images_count": len(page_data["images"]),
                    "videos_count": len(page_data["videos"]),
                    "tabs_count": len(page_data["tabs"]),
                    "specs": page_data["specs"],
                    "products": page_data["products"],
                    "images": page_data["images"],
                    "videos": page_data["videos"],
                    "tabs": page_data["tabs"],
                    "short_description_html": page_data["short_description_html"],
                    "related_products": page_data["related_products"],
                    "source_data": page_data["source_data"],
                }
                save_cache(cache_path, cache)
                fetched_pages += 1
                time.sleep(max(args.delay, 0))

            specs = page_data["specs"]
            products = page_data["products"]
            images = page_data["images"]
            videos = page_data["videos"]
            add_specs_to_item(item, specs, source_url)
            add_products_to_item(item, products, source_url)
            add_gallery_to_item(item, images, source_url, videos)
            add_content_to_item(item, page_data, source_url)
            add_related_products_to_item(item, page_data["related_products"], source_url)
            add_source_data_to_item(item, page_data["source_data"], source_url)
            write_xml(root, output)
            processed += 1
            print(
                f"[{index}/{len(selected_items)}] id={feed_id} specs={len(specs)} "
                f"products={len(products)} images={len(images)} videos={len(videos)} "
                f"tabs={len(page_data['tabs'])} title={title}"
            )
    finally:
        quit_driver(driver)

    write_xml(root, output)
    print(f"Wrote: {output}")
    print(f"Processed products: {processed}")
    print(f"Skipped completed products: {skipped}")
    print(f"Unique product pages fetched this run: {fetched_pages}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
