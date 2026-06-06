#!/usr/bin/env python3
"""Build a local XML feed enriched with specifications scraped via Selenium."""

from __future__ import annotations

import argparse
import datetime as dt
import html
import json
import os
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


class SpecificationsParser(HTMLParser):
    """Fallback parser for Selenium page_source."""

    def __init__(self) -> None:
        super().__init__(convert_charrefs=True)
        self.in_panel = False
        self.panel_depth = 0
        self.capture_kind: str | None = None
        self.capture_depth = 0
        self.capture_parts: list[str] = []
        self.current_section = ""
        self.current_row: dict[str, str] | None = None
        self.row_depth = 0
        self.specs: list[dict[str, str]] = []

    def handle_starttag(self, tag: str, attrs: list[tuple[str, str | None]]) -> None:
        if not self.in_panel and attr_value(attrs, "id") == "tab-specifications":
            self.in_panel = True
            self.panel_depth = 1
            return

        if not self.in_panel:
            return

        self.panel_depth += 1

        if tag == "h2" and class_contains(attrs, "specification__main-title"):
            self.start_capture("section")
            return

        if tag == "div" and class_contains(attrs, "specification__content"):
            self.current_row = {"section": self.current_section, "name": "", "value": ""}
            self.row_depth = 1
            return

        if self.current_row is not None:
            self.row_depth += 1

            if tag == "div" and class_contains(attrs, "specification-content__title"):
                self.start_capture("name")
                return

            if tag == "div" and class_contains(attrs, "specification-content__description"):
                self.start_capture("value")
                return

        if self.capture_kind:
            self.capture_depth += 1

    def handle_endtag(self, tag: str) -> None:
        if not self.in_panel:
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

        self.panel_depth -= 1
        if self.panel_depth <= 0:
            self.in_panel = False

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

        self.capture_kind = None
        self.capture_depth = 0
        self.capture_parts = []


def extract_specs_from_html(page_html: str) -> list[dict[str, str]]:
    parser = SpecificationsParser()
    parser.feed(page_html)
    return parser.specs


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


def scrape_specs_with_selenium(driver: Any, url: str, timeout: int) -> list[dict[str, str]]:
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
        return extract_specs_from_html(driver.page_source)

    specs = driver.execute_script(
        """
        const panel = document.querySelector('#tab-specifications');
        if (!panel) return [];

        let section = '';
        const specs = [];
        panel.querySelectorAll('.specification__main-title, .specification__content').forEach((el) => {
            if (el.classList.contains('specification__main-title')) {
                section = el.textContent.trim().replace(/\\s+/g, ' ');
                return;
            }

            const name = el.querySelector('.specification-content__title')?.textContent
                .trim().replace(/\\s+/g, ' ') || '';
            const value = el.querySelector('.specification-content__description')?.textContent
                .trim().replace(/\\s+/g, ' ') || '';

            if (name || value) {
                specs.push({section, name, value});
            }
        });
        return specs;
        """
    )

    cleaned_specs = [
        {
            "section": collapse_space(str(spec.get("section", ""))),
            "name": collapse_space(str(spec.get("name", ""))),
            "value": collapse_space(str(spec.get("value", ""))),
        }
        for spec in specs
        if isinstance(spec, dict)
    ]
    return cleaned_specs or extract_specs_from_html(driver.page_source)


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


def find_specs_node(item: ET.Element) -> ET.Element | None:
    for child in item:
        if child.tag == f"{{{APROP_NS}}}specifications":
            return child
    return None


def has_completed_specs(item: ET.Element, retry_empty: bool) -> bool:
    node = find_specs_node(item)
    if node is None:
        return False
    if retry_empty and node.attrib.get("count") == "0":
        return False
    return True


def copy_specs_node(source_item: ET.Element, target_item: ET.Element) -> None:
    source_specs = find_specs_node(source_item)
    if source_specs is None:
        return

    for child in list(target_item):
        if child.tag == f"{{{APROP_NS}}}specifications":
            target_item.remove(child)

    target_item.append(ET.fromstring(ET.tostring(source_specs, encoding="utf-8")))


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


def specs_from_cache(cache_entry: Any) -> list[dict[str, str]] | None:
    if not isinstance(cache_entry, dict) or not isinstance(cache_entry.get("specs"), list):
        return None
    return [
        {
            "section": str(spec.get("section", "")),
            "name": str(spec.get("name", "")),
            "value": str(spec.get("value", "")),
        }
        for spec in cache_entry["specs"]
        if isinstance(spec, dict)
    ]


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
                copy_specs_node(previous_item, item)

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

            if not args.force and has_completed_specs(item, args.retry_empty):
                skipped += 1
                count = find_specs_node(item).attrib.get("count", "0")  # type: ignore[union-attr]
                print(f"[{index}/{len(selected_items)}] skip id={feed_id} specs={count} title={title}")
                continue

            if not link:
                add_specs_to_item(item, [], "")
                write_xml(root, output)
                processed += 1
                print(f"[{index}/{len(selected_items)}] id={feed_id} missing link")
                continue

            source_url = cache_key_for_product_url(link)
            specs = None if args.force else specs_from_cache(cache.get(source_url))

            if specs is None:
                last_error = None
                for attempt in range(1, max(args.retries, 0) + 2):
                    try:
                        if driver is None:
                            driver = create_driver(args)
                        specs = scrape_specs_with_selenium(driver, source_url, args.timeout)
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

                if specs is None:
                    print(
                        f"[{index}/{len(selected_items)}] giving up id={feed_id} "
                        f"after Selenium error={last_error}",
                        flush=True,
                    )
                    specs = []

                cache[source_url] = {
                    "source_url": source_url,
                    "fetched_at": dt.datetime.now(dt.timezone.utc).isoformat(),
                    "count": len(specs),
                    "specs": specs,
                }
                save_cache(cache_path, cache)
                fetched_pages += 1
                time.sleep(max(args.delay, 0))

            add_specs_to_item(item, specs, source_url)
            write_xml(root, output)
            processed += 1
            print(f"[{index}/{len(selected_items)}] id={feed_id} specs={len(specs)} title={title}")
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
