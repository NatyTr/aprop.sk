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


class ProductPageParser(HTMLParser):
    """Fallback parser for Selenium page_source."""

    def __init__(self) -> None:
        super().__init__(convert_charrefs=True)
        self.in_specs_panel = False
        self.specs_panel_depth = 0
        self.in_products_panel = False
        self.products_panel_depth = 0
        self.capture_kind: str | None = None
        self.capture_depth = 0
        self.capture_parts: list[str] = []
        self.current_section = ""
        self.current_row: dict[str, str] | None = None
        self.row_depth = 0
        self.current_product: dict[str, str] | None = None
        self.product_depth = 0
        self.specs: list[dict[str, str]] = []
        self.products: list[dict[str, str]] = []
        self.images: list[dict[str, str]] = []
        self.image_urls: set[str] = set()

    def handle_starttag(self, tag: str, attrs: list[tuple[str, str | None]]) -> None:
        element_id = attr_value(attrs, "id")
        if not self.in_specs_panel and not self.in_products_panel and element_id == "tab-specifications":
            self.in_specs_panel = True
            self.specs_panel_depth = 1
            return

        if not self.in_specs_panel and not self.in_products_panel and element_id == "tab-products":
            self.in_products_panel = True
            self.products_panel_depth = 1
            return

        if tag == "img":
            classes = attr_value(attrs, "class")
            if "product_thumbnail_item" in classes or "attachment-woocommerce_gallery_thumbnail" in classes or "wp-post-image" in classes:
                image_url = largest_srcset_url(attr_value(attrs, "srcset") or attr_value(attrs, "data-o_srcset"), attr_value(attrs, "src"))
                if image_url and image_url not in self.image_urls and "woocommerce-placeholder" not in image_url:
                    self.image_urls.add(image_url)
                    self.images.append(
                        {
                            "url": image_url,
                            "alt": collapse_space(attr_value(attrs, "alt")),
                            "title": collapse_space(attr_value(attrs, "title")),
                        }
                    )

        if not self.in_specs_panel and not self.in_products_panel:
            return

        if self.in_specs_panel:
            self.specs_panel_depth += 1
        if self.in_products_panel:
            self.products_panel_depth += 1

        if self.in_specs_panel and tag == "h2" and class_contains(attrs, "specification__main-title"):
            self.start_capture("section")
            return

        if self.in_specs_panel and tag == "div" and class_contains(attrs, "specification__content"):
            self.current_row = {"section": self.current_section, "name": "", "value": ""}
            self.row_depth = 1
            return

        if self.current_row is not None:
            self.row_depth += 1

            if tag in {"div", "p"} and class_contains(attrs, "specification-content__title"):
                self.start_capture("name")
                return

            if tag in {"div", "p"} and class_contains(attrs, "specification-content__description"):
                self.start_capture("value")
                return

        if self.in_products_panel and tag == "div" and class_contains(attrs, "specification__content"):
            self.current_product = {"name": "", "quantity": ""}
            self.product_depth = 1
            return

        if self.current_product is not None:
            self.product_depth += 1

            if tag in {"div", "p"} and class_contains(attrs, "specification-content__title"):
                self.start_capture("product_name")
                return

            if tag in {"div", "p"} and class_contains(attrs, "specification-content__description"):
                self.start_capture("product_quantity")
                return

        if self.capture_kind:
            self.capture_depth += 1

    def handle_endtag(self, tag: str) -> None:
        if not self.in_specs_panel and not self.in_products_panel:
            return

        if self.capture_kind:
            self.capture_depth -= 1
            if self.capture_depth <= 0:
                self.finish_capture()

        if self.current_row is not None:
            self.row_depth -= 1
            if self.row_depth <= 0:
                name = self.current_row.get("name", "")
                value = self.current_row.get("value", "")
                if name or value:
                    self.specs.append(dict(self.current_row))
                self.current_row = None

        if self.current_product is not None:
            self.product_depth -= 1
            if self.product_depth <= 0:
                name = self.current_product.get("name", "")
                quantity = self.current_product.get("quantity", "")
                if name or quantity:
                    self.products.append(dict(self.current_product))
                self.current_product = None

        if self.in_specs_panel:
            self.specs_panel_depth -= 1
            if self.specs_panel_depth <= 0:
                self.in_specs_panel = False

        if self.in_products_panel:
            self.products_panel_depth -= 1
            if self.products_panel_depth <= 0:
                self.in_products_panel = False

    def handle_data(self, data: str) -> None:
        if self.capture_kind:
            self.capture_parts.append(data)

    def start_capture(self, kind: str) -> None:
        self.capture_kind = kind
        self.capture_depth = 1
        self.capture_parts = []

    def finish_capture(self) -> None:
        value = collapse_space(" ".join(self.capture_parts))

        if self.capture_kind == "section":
            self.current_section = value
        elif self.current_row is not None and self.capture_kind in {"name", "value"}:
            self.current_row[self.capture_kind] = value
        elif self.current_product is not None and self.capture_kind == "product_name":
            self.current_product["name"] = value
        elif self.current_product is not None and self.capture_kind == "product_quantity":
            self.current_product["quantity"] = value

        self.capture_kind = None
        self.capture_depth = 0
        self.capture_parts = []


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


def extract_page_data_from_html(page_html: str) -> dict[str, list[dict[str, str]]]:
    parser = ProductPageParser()
    parser.feed(page_html)
    return {
        "specs": parser.specs,
        "products": parser.products,
        "images": parser.images,
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


def scrape_page_data_with_selenium(driver: Any, url: str, timeout: int) -> dict[str, list[dict[str, str]]]:
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
        return extract_page_data_from_html(driver.page_source)

    page_data = driver.execute_script(
        """
        const text = (el) => (el?.textContent || '').trim().replace(/\\s+/g, ' ');
        const largestSrcsetUrl = (srcset, fallback = '') => {
            const candidates = String(srcset || '').split(',')
                .map((candidate) => {
                    const parts = candidate.trim().split(/\\s+/);
                    const width = parts[1] && parts[1].endsWith('w') ? parseInt(parts[1], 10) || 0 : 0;
                    return {url: parts[0] || '', width};
                })
                .filter((candidate) => candidate.url);

            if (!candidates.length) return fallback;
            candidates.sort((a, b) => b.width - a.width);
            return candidates[0].url;
        };

        const specs = [];
        const specsPanel = document.querySelector('#tab-specifications');
        if (specsPanel) {
            let section = '';
            specsPanel.querySelectorAll('.specification__main-title, .specification__content').forEach((el) => {
                if (el.classList.contains('specification__main-title')) {
                    section = text(el);
                    return;
                }

                const name = text(el.querySelector('.specification-content__title'));
                const value = text(el.querySelector('.specification-content__description'));

                if (name || value) {
                    specs.push({section, name, value});
                }
            });
        }

        const products = [];
        const productsPanel = document.querySelector('#tab-products');
        if (productsPanel) {
            productsPanel.querySelectorAll('.specification__content').forEach((el) => {
                const name = text(el.querySelector('.specification-content__title'));
                const quantity = text(el.querySelector('.specification-content__description'));

                if (name || quantity) {
                    products.push({name, quantity});
                }
            });
        }

        const seenImages = new Set();
        const images = [];
        document.querySelectorAll('.woocommerce-product-gallery img, .nickx-slider-for img, .nickx-slider-nav img, .product_thumbnail_item img, img.attachment-woocommerce_gallery_thumbnail').forEach((img) => {
            const url = largestSrcsetUrl(img.getAttribute('srcset') || img.getAttribute('data-o_srcset'), img.currentSrc || img.src || '');
            if (!url || seenImages.has(url) || url.includes('woocommerce-placeholder')) return;

            seenImages.add(url);
            images.push({
                url,
                alt: text(img.getAttribute('alt') ? {textContent: img.getAttribute('alt')} : null),
                title: text(img.getAttribute('title') ? {textContent: img.getAttribute('title')} : null),
            });
        });

        return {specs, products, images};
        """
    )

    cleaned_specs = [
        {
            "section": collapse_space(str(spec.get("section", ""))),
            "name": collapse_space(str(spec.get("name", ""))),
            "value": collapse_space(str(spec.get("value", ""))),
        }
        for spec in (page_data or {}).get("specs", [])
        if isinstance(spec, dict)
    ]
    cleaned_products = [
        {
            "name": collapse_space(str(product.get("name", ""))),
            "quantity": collapse_space(str(product.get("quantity", ""))),
        }
        for product in (page_data or {}).get("products", [])
        if isinstance(product, dict)
    ]
    cleaned_images = [
        {
            "url": str(image.get("url", "")).strip(),
            "alt": collapse_space(str(image.get("alt", ""))),
            "title": collapse_space(str(image.get("title", ""))),
        }
        for image in (page_data or {}).get("images", [])
        if isinstance(image, dict) and str(image.get("url", "")).strip()
    ]

    fallback = extract_page_data_from_html(driver.page_source)
    return {
        "specs": cleaned_specs or fallback["specs"],
        "products": cleaned_products or fallback["products"],
        "images": cleaned_images or fallback["images"],
    }


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


def add_gallery_to_item(item: ET.Element, images: list[dict[str, str]], source_url: str) -> None:
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
    if find_aprop_node(item, "products") is None or find_aprop_node(item, "gallery") is None:
        return False
    return True


def copy_aprop_nodes(source_item: ET.Element, target_item: ET.Element) -> None:
    copied_tags = {
        f"{{{APROP_NS}}}specifications",
        f"{{{APROP_NS}}}products",
        f"{{{APROP_NS}}}gallery",
        f"{{{APROP_NS}}}web_categories",
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


def page_data_from_cache(cache_entry: Any) -> dict[str, list[dict[str, str]]] | None:
    if (
        not isinstance(cache_entry, dict)
        or not isinstance(cache_entry.get("specs"), list)
        or not isinstance(cache_entry.get("products"), list)
        or not isinstance(cache_entry.get("images"), list)
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

    return {"specs": specs, "products": products, "images": images}


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
                add_specs_to_item(item, [], "")
                add_products_to_item(item, [], "")
                add_gallery_to_item(item, [], "")
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
                    page_data = {"specs": [], "products": [], "images": []}

                cache[source_url] = {
                    "source_url": source_url,
                    "fetched_at": dt.datetime.now(dt.timezone.utc).isoformat(),
                    "count": len(page_data["specs"]),
                    "products_count": len(page_data["products"]),
                    "images_count": len(page_data["images"]),
                    "specs": page_data["specs"],
                    "products": page_data["products"],
                    "images": page_data["images"],
                }
                save_cache(cache_path, cache)
                fetched_pages += 1
                time.sleep(max(args.delay, 0))

            specs = page_data["specs"]
            products = page_data["products"]
            images = page_data["images"]
            add_specs_to_item(item, specs, source_url)
            add_products_to_item(item, products, source_url)
            add_gallery_to_item(item, images, source_url)
            write_xml(root, output)
            processed += 1
            print(
                f"[{index}/{len(selected_items)}] id={feed_id} specs={len(specs)} "
                f"products={len(products)} images={len(images)} title={title}"
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
