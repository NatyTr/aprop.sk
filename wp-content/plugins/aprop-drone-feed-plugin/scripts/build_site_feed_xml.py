#!/usr/bin/env python3
"""Build the local WooCommerce import feed directly from Enterra product pages."""

from __future__ import annotations

import argparse
import datetime as dt
import html
import json
import os
import re
import time
import urllib.parse
import xml.etree.ElementTree as ET
from pathlib import Path
from typing import Any

from bs4 import BeautifulSoup

from build_specs_xml import (
    APROP_NS,
    GOOGLE_NS,
    add_gallery_to_item,
    add_products_to_item,
    add_specs_to_item,
    add_web_categories_to_item,
    canonical_product_url,
    collapse_space,
    extract_category_products_from_html,
    extract_page_data_from_html,
    fetch_html,
    fetch_url,
    scrape_drone_web_categories,
    write_xml,
)


SHOP_URL = "https://www.enterra.sk/shop/"
PRODUCT_SITEMAPS = [
    "https://www.enterra.sk/product-sitemap.xml",
    "https://www.enterra.sk/product-sitemap2.xml",
]


ET.register_namespace("g", GOOGLE_NS)
ET.register_namespace("aprop", APROP_NS)


def product_urls_from_sitemaps(sitemaps: list[str], timeout: int) -> list[str]:
    seen: set[str] = set()
    product_urls: list[str] = []

    for sitemap_url in sitemaps:
        root = ET.fromstring(fetch_url(sitemap_url, timeout))
        for url_node in root.findall("{http://www.sitemaps.org/schemas/sitemap/0.9}url"):
            loc_node = url_node.find("{http://www.sitemaps.org/schemas/sitemap/0.9}loc")
            if loc_node is None or not loc_node.text:
                continue

            product_url = canonical_product_url(loc_node.text)
            if "/produkt/" not in product_url or "/en/" in product_url or product_url in seen:
                continue

            seen.add(product_url)
            product_urls.append(product_url)

    return product_urls


def product_urls_from_shop_archive(shop_url: str, max_pages: int, timeout: int) -> list[str]:
    seen: set[str] = set()
    product_urls: list[str] = []

    for page in range(1, max_pages + 1):
        page_url = shop_url if page == 1 else urllib.parse.urljoin(shop_url, f"page/{page}/")
        try:
            page_html = fetch_html(page_url, timeout)
        except Exception:
            if page == 1:
                raise
            break

        products = extract_category_products_from_html(page_html)
        if not products:
            break

        for product in products:
            product_url = canonical_product_url(product.get("url", ""))
            if product_url and product_url not in seen:
                seen.add(product_url)
                product_urls.append(product_url)

    return product_urls


def load_previous_feed_ids(path: Path) -> dict[str, str]:
    if not path.exists():
        return {}

    root = ET.parse(path).getroot()
    by_url: dict[str, list[str]] = {}

    for item in root.iter():
        if local_name(item.tag) != "item":
            continue

        product_url = canonical_product_url(child_text(item, "link"))
        feed_id = child_text(item, "id")
        if product_url and feed_id:
            by_url.setdefault(product_url, []).append(feed_id)

    return {
        product_url: ids[0]
        for product_url, ids in by_url.items()
        if len(set(ids)) == 1
    }


def local_name(tag: str) -> str:
    return tag.rsplit("}", 1)[-1] if "}" in tag else tag


def child_text(element: ET.Element, name: str) -> str:
    for child in element:
        if local_name(child.tag) == name and child.text:
            return collapse_space(child.text)
    return ""


def product_schema_from_soup(soup: BeautifulSoup) -> dict[str, Any]:
    for script in soup.select('script[type="application/ld+json"]'):
        try:
            data = json.loads(script.string or script.get_text())
        except json.JSONDecodeError:
            continue

        product = find_schema_type(data, "Product")
        if isinstance(product, dict):
            return product

    return {}


def find_schema_type(value: Any, schema_type: str) -> Any:
    if isinstance(value, dict):
        item_type = value.get("@type")
        if item_type == schema_type or (isinstance(item_type, list) and schema_type in item_type):
            return value

        for child in value.values():
            found = find_schema_type(child, schema_type)
            if found is not None:
                return found

    if isinstance(value, list):
        for child in value:
            found = find_schema_type(child, schema_type)
            if found is not None:
                return found

    return None


def find_schema_types(value: Any, schema_type: str) -> list[Any]:
    found: list[Any] = []

    if isinstance(value, dict):
        item_type = value.get("@type")
        if item_type == schema_type or (isinstance(item_type, list) and schema_type in item_type):
            found.append(value)

        for child in value.values():
            found.extend(find_schema_types(child, schema_type))

    if isinstance(value, list):
        for child in value:
            found.extend(find_schema_types(child, schema_type))

    return found


def post_id_from_soup(soup: BeautifulSoup) -> str:
    classes = soup.body.get("class", []) if soup.body else []
    for class_name in classes:
        match = re.fullmatch(r"postid-(\d+)", str(class_name))
        if match:
            return match.group(1)

    form = soup.select_one("form.cart [name='add-to-cart'], form.variations_form")
    if form:
        value = form.get("value") or form.get("data-product_id")
        if value and str(value).isdigit():
            return str(value)

    return ""


def text_from_selector(soup: BeautifulSoup, selector: str) -> str:
    element = soup.select_one(selector)
    if element is None:
        return ""
    return collapse_space(element.get_text(" ", strip=True))


def description_from_soup(soup: BeautifulSoup, product_schema: dict[str, Any]) -> str:
    description = text_from_selector(soup, "#tab-description")
    if description.startswith("Popis "):
        description = collapse_space(description[6:])
    if description:
        return description

    return collapse_space(str(product_schema.get("description", "")))


def canonical_from_soup(soup: BeautifulSoup, fallback_url: str) -> str:
    canonical = soup.select_one('link[rel="canonical"]')
    if canonical and canonical.get("href"):
        return canonical_product_url(canonical["href"])
    return canonical_product_url(fallback_url)


def image_from_schema_or_soup(product_schema: dict[str, Any], soup: BeautifulSoup) -> str:
    image = product_schema.get("image")
    if isinstance(image, list) and image:
        image = image[0]
    if isinstance(image, dict):
        image = image.get("url")
    if isinstance(image, str) and image:
        return image

    og_image = soup.select_one('meta[property="og:image"]')
    if og_image and og_image.get("content"):
        return str(og_image["content"])

    image_el = soup.select_one(".woocommerce-product-gallery img, img.wp-post-image")
    return image_el.get("src", "") if image_el else ""


def offer_from_schema(product_schema: dict[str, Any]) -> dict[str, Any]:
    offers = product_schema.get("offers")
    if isinstance(offers, list):
        return offers[0] if offers and isinstance(offers[0], dict) else {}
    return offers if isinstance(offers, dict) else {}


def price_from_offer(offer: dict[str, Any]) -> str:
    currency = str(offer.get("priceCurrency") or "EUR")
    price = offer.get("price")

    if price in {None, ""}:
        price_spec = offer.get("priceSpecification")
        if isinstance(price_spec, list):
            price_spec = price_spec[0] if price_spec else {}
        if isinstance(price_spec, dict):
            price = price_spec.get("price")
            currency = str(price_spec.get("priceCurrency") or currency)

    return f"{price} {currency}" if price not in {None, ""} else ""


def availability_from_offer_or_page(offer: dict[str, Any], soup: BeautifulSoup) -> str:
    availability = str(offer.get("availability", "")).rsplit("/", 1)[-1].lower()
    if availability in {"instock", "limitedavailability"}:
        return "in_stock"
    if availability in {"backorder", "preorder", "presale"}:
        return "backorder"
    if availability in {"outofstock", "soldout", "discontinued"}:
        return "out_of_stock"

    page_text = collapse_space(text_from_selector(soup, ".summary .product_meta, .summary .stock")).lower()
    if "na objednávku" in page_text or "backorder" in page_text:
        return "backorder"
    if "nie je skladom" in page_text or "out of stock" in page_text:
        return "out_of_stock"
    return "in_stock"


def breadcrumb_product_type(soup: BeautifulSoup, title: str) -> str:
    best_names: list[str] = []

    for script in soup.select('script[type="application/ld+json"]'):
        try:
            data = json.loads(script.string or script.get_text())
        except json.JSONDecodeError:
            continue

        for breadcrumb in find_schema_types(data, "BreadcrumbList"):
            if not isinstance(breadcrumb, dict):
                continue

            elements = breadcrumb.get("itemListElement")
            if not isinstance(elements, list):
                continue

            names = []
            for element in elements:
                item = element.get("item") if isinstance(element, dict) else None
                if isinstance(item, dict):
                    name = collapse_space(str(item.get("name", "")))
                else:
                    name = collapse_space(str(element.get("name", ""))) if isinstance(element, dict) else ""
                if name:
                    names.append(name)

            names = clean_breadcrumb_names(names, title)
            if len(names) > len(best_names):
                best_names = names

    if not best_names:
        names = [a.get_text(" ", strip=True) for a in soup.select(".woocommerce-breadcrumb a")]
        best_names = clean_breadcrumb_names(names, title)

    return "Home" + (" > " + " > ".join(best_names) if best_names else "")


def clean_breadcrumb_names(names: list[str], title: str) -> list[str]:
    names = [name for name in names if name and name.lower() not in {"home", "shop", "e-shop"}]
    if names and titles_match(names[-1], title):
        names.pop()
    return names


def titles_match(left: str, right: str) -> bool:
    left_normalized = normalize_title(left)
    right_normalized = normalize_title(right)
    return (
        left_normalized == right_normalized
        or left_normalized in right_normalized
        or right_normalized in left_normalized
    )


def normalize_title(value: str) -> str:
    normalized = html.unescape(html.unescape(collapse_space(value))).lower()
    normalized = normalized.replace("| enterra.sk", "")
    normalized = normalized.replace("–", "-").replace("—", "-").replace("­", "")
    normalized = re.sub(r"[^\w]+", " ", normalized, flags=re.UNICODE)
    return normalized.strip()


def parse_product_page(product_url: str, timeout: int, previous_ids: dict[str, str]) -> dict[str, Any]:
    page_html = fetch_html(product_url, timeout)
    soup = BeautifulSoup(page_html, "html.parser")
    product_schema = product_schema_from_soup(soup)
    offer = offer_from_schema(product_schema)
    canonical_url = canonical_from_soup(soup, product_url)
    title = collapse_space(str(product_schema.get("name", ""))) or text_from_selector(soup, "h1.product_title")
    product_id = previous_ids.get(canonical_url) or post_id_from_soup(soup)

    return {
        "id": product_id,
        "title": title,
        "description": description_from_soup(soup, product_schema),
        "link": canonical_url,
        "image_link": image_from_schema_or_soup(product_schema, soup),
        "availability": availability_from_offer_or_page(offer, soup),
        "price": price_from_offer(offer),
        "product_type": breadcrumb_product_type(soup, title),
        "sku": collapse_space(str(product_schema.get("sku", ""))) or text_from_selector(soup, ".product_meta .sku"),
        "page_data": extract_page_data_from_html(page_html),
    }


def add_google_text(item: ET.Element, name: str, value: str) -> None:
    element = ET.SubElement(item, f"{{{GOOGLE_NS}}}{name}")
    element.text = value


def build_item(channel: ET.Element, product: dict[str, Any], web_categories: list[dict[str, str]]) -> None:
    item = ET.SubElement(channel, "item")
    product_type = product.get("product_type", "")

    fields = {
        "id": product.get("id", ""),
        "title": product.get("title", ""),
        "description": product.get("description", ""),
        "link": product.get("link", ""),
        "image_link": product.get("image_link", ""),
        "availability": product.get("availability", ""),
        "price": product.get("price", ""),
        "condition": "new",
        "product_type": product_type,
        "google_product_category": product_type,
        "brand": "DJI" if "dji" in str(product.get("title", "")).lower() else "",
        "mpn": product.get("sku", ""),
    }

    for name, value in fields.items():
        add_google_text(item, name, str(value or ""))

    source_url = product.get("link", "")
    page_data = product["page_data"]
    add_specs_to_item(item, page_data["specs"], source_url)
    add_products_to_item(item, page_data["products"], source_url)
    add_gallery_to_item(item, page_data["images"], source_url)
    add_web_categories_to_item(item, web_categories, "https://www.enterra.sk/shop/drones/")


def build_feed(products: list[dict[str, Any]], web_categories_by_url: dict[str, list[dict[str, str]]]) -> ET.Element:
    root = ET.Element("rss", {"version": "2.0"})
    channel = ET.SubElement(root, "channel")
    ET.SubElement(channel, "title").text = "Enterra local product feed"
    ET.SubElement(channel, "link").text = SHOP_URL
    ET.SubElement(channel, "description").text = "Generated from Enterra product pages"
    ET.SubElement(channel, f"{{{APROP_NS}}}generated_at").text = dt.datetime.now(dt.timezone.utc).isoformat()

    for product in products:
        product_url = canonical_product_url(product.get("link", ""))
        build_item(channel, product, web_categories_by_url.get(product_url, []))

    return root


def main() -> int:
    parser = argparse.ArgumentParser(description="Create a local feed by scraping Enterra product pages.")
    parser.add_argument("--output", default="enterra-feed-with-specifications.xml")
    parser.add_argument("--source", choices=["sitemap", "shop"], default="sitemap")
    parser.add_argument("--previous-feed", default="enterra-feed-with-specifications.xml")
    parser.add_argument("--max-products", type=int, default=0)
    parser.add_argument("--max-pages", type=int, default=80)
    parser.add_argument("--timeout", type=int, default=45)
    parser.add_argument("--delay", type=float, default=0.15)
    parser.add_argument("--skip-web-categories", action="store_true")
    args = parser.parse_args()

    output = Path(args.output)
    previous_ids = load_previous_feed_ids(Path(args.previous_feed))

    product_urls = (
        product_urls_from_sitemaps(PRODUCT_SITEMAPS, args.timeout)
        if args.source == "sitemap"
        else product_urls_from_shop_archive(SHOP_URL, args.max_pages, args.timeout)
    )

    if args.max_products > 0:
        product_urls = product_urls[: args.max_products]

    web_categories_by_url = {} if args.skip_web_categories else scrape_drone_web_categories(args.timeout)
    products: list[dict[str, Any]] = []

    print(f"Product URLs: {len(product_urls)}")
    print(f"Output: {output}")

    for index, product_url in enumerate(product_urls, start=1):
        try:
            product = parse_product_page(product_url, args.timeout, previous_ids)
            if not product.get("id") or not product.get("title"):
                print(f"[{index}/{len(product_urls)}] skip missing id/title url={product_url}", flush=True)
                continue

            products.append(product)
            page_data = product["page_data"]
            print(
                f"[{index}/{len(product_urls)}] id={product['id']} specs={len(page_data['specs'])} "
                f"products={len(page_data['products'])} images={len(page_data['images'])} title={product['title']}",
                flush=True,
            )
        except Exception as exc:
            print(f"[{index}/{len(product_urls)}] error url={product_url} {type(exc).__name__}: {exc}", flush=True)

        time.sleep(max(args.delay, 0))

    root = build_feed(products, web_categories_by_url)
    write_xml(root, output)
    print(f"Wrote: {output}")
    print(f"Products written: {len(products)}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
