#!/usr/bin/env python3
"""Fetch an XML product feed and print product count plus example item fields."""

from __future__ import annotations

import argparse
import sys
import textwrap
import urllib.request
import xml.etree.ElementTree as ET


DEFAULT_FEED_URL = (
    "https://feeds.mergado.com/"
    "enterra-sk-google-nakupy-sk-70a3cb5ee9479a6525566d5af13a3fe6.xml"
)


def local_name(tag: str) -> str:
    if "}" in tag:
        return tag.rsplit("}", 1)[1]
    return tag


def child_text(element: ET.Element, names: set[str]) -> str | None:
    for child in element:
        if local_name(child.tag) in names and child.text:
            return " ".join(child.text.split())
    return None


def fetch_xml(url: str) -> bytes:
    request = urllib.request.Request(
        url,
        headers={
            "User-Agent": "ApropDroneFeedPlugin-feed-check/1.0",
            "Accept": "application/xml,text/xml,*/*",
        },
    )
    with urllib.request.urlopen(request, timeout=45) as response:
        return response.read()


def find_products(root: ET.Element) -> list[ET.Element]:
    products = [element for element in root.iter() if local_name(element.tag) == "item"]
    if products:
        return products
    return [element for element in root.iter() if local_name(element.tag) == "entry"]


def truncate(value: str | None, width: int) -> str:
    if not value:
        return ""
    return textwrap.shorten(value, width=width, placeholder="...")


def product_summary(product: ET.Element) -> dict[str, str]:
    field_names = {
        "id": {"id"},
        "item_group_id": {"item_group_id"},
        "title": {"title"},
        "brand": {"brand"},
        "price": {"price"},
        "sale_price": {"sale_price"},
        "availability": {"availability"},
        "condition": {"condition"},
        "google_product_category": {"google_product_category"},
        "product_type": {"product_type"},
        "link": {"link"},
        "image_link": {"image_link"},
        "description": {"description"},
    }
    return {
        output_name: child_text(product, names) or ""
        for output_name, names in field_names.items()
    }


def main() -> int:
    parser = argparse.ArgumentParser(
        description="Check product count and sample data from an XML feed."
    )
    parser.add_argument("url", nargs="?", default=DEFAULT_FEED_URL)
    parser.add_argument(
        "--examples",
        type=int,
        default=1,
        help="Number of example products to print.",
    )
    args = parser.parse_args()

    xml_bytes = fetch_xml(args.url)
    root = ET.fromstring(xml_bytes)
    products = find_products(root)

    print(f"Feed URL: {args.url}")
    print(f"XML size: {len(xml_bytes):,} bytes")
    print(f"Root element: {local_name(root.tag)}")
    print(f"Products found: {len(products):,}")

    channel = next((element for element in root.iter() if local_name(element.tag) == "channel"), None)
    if channel is not None:
        channel_title = child_text(channel, {"title"})
        if channel_title:
            print(f"Channel title: {channel_title}")

    for index, product in enumerate(products[: max(args.examples, 0)], start=1):
        summary = product_summary(product)
        print()
        print(f"Example product #{index}")
        for key, value in summary.items():
            if key == "description":
                value = truncate(value, 260)
            print(f"  {key}: {value}")

    return 0


if __name__ == "__main__":
    try:
        raise SystemExit(main())
    except (urllib.error.URLError, ET.ParseError) as exc:
        print(f"Feed check failed: {exc}", file=sys.stderr)
        raise SystemExit(1)
