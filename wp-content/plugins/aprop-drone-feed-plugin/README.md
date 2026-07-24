# Aprop Drone Feed Sync

WordPress/WooCommerce plugin for importing products from the bundled Enterra XML feed.

## Behavior

- Adds WooCommerce admin page: `WooCommerce > Aprop Drone Feed`.
- `Sync / resync products` reads the bundled enriched feed file `enterra-feed-with-specifications.xml`.
- Products are imported one by one through AJAX.
- The first sync can create products. Later resyncs update existing imported products only and skip new feed products.
- Existing products are matched by feed id stored in `_aprop_enterra_feed_id`, then by SKU, and updated with the latest title, formatted descriptions, price, stock, category, source URL, gallery, tab content, related products, and specifications.
- The Enterra SKU is used as the WooCommerce SKU when it is available. If another product already owns that SKU, the importer keeps the existing/synthetic SKU and records the conflicting product id in `_aprop_enterra_sku_conflict_product_id`.
- Source SKUs are normalized to their rendered uppercase form and invisible formatting characters such as soft hyphens are removed.
- Imported products are marked with `_aprop_is_feed_imported=1` and `_aprop_import_source=enterra_mergado_feed`.
- Product title strips the trailing `| Enterra.sk`.
- Product image is sideloaded from `image_link` and set as featured image only when the product has no existing featured image.
- Full-size product gallery images are sideloaded from `aprop:gallery` into the standard WooCommerce gallery. Gallery videos are stored in `_aprop_enterra_gallery_videos_json` because WooCommerce core has no native video-gallery field.
- The Enterra short description keeps its HTML formatting and becomes the WooCommerce short description.
- Every Enterra product tab is preserved as formatted HTML in `_aprop_enterra_tab_{tab-id}_html`; the `description` tab becomes the WooCommerce long description. Inline image and video URLs remain in the HTML at their original source URLs.
- Imported tabs are exposed on the product detail as WooCommerce tabs. Specifications use grouped structured rows, included products use the imported package list, and any additional source tabs retain their formatted HTML.
- The full content payload is stored in `_aprop_enterra_content_json`, including tab titles, plain text, HTML, and inline media URL lists.
- Cross-sell and upsell SKUs are stored in `_aprop_enterra_related_products_json` and resolved to standard WooCommerce cross-sell/upsell product ids after the sync.
- Source-page metadata such as availability label, delivery text, displayed prices, flags, source product type, offer expiry, source SKU, and source URL is stored in `_aprop_enterra_source_data_json` and individual `_aprop_enterra_source_*` fields.
- `aprop:web_categories` is preferred for category paths when present; otherwise `product_type` is parsed as a category path.
- Feed root category `Home` is removed and all parsed categories are created below WooCommerce product category id `211`.
- Feed stock values are mapped to WooCommerce:
  - `in_stock` => `instock`
  - `backorder` => `onbackorder`
  - `out_of_stock` => `outofstock`
- Debug button deletes all products imported by this plugin and removes imported featured image attachments when no remaining imported product uses them.
- Scraped specifications are stored as product meta:
  - `_aprop_enterra_specifications_count`
  - `_aprop_enterra_specifications_json`
  - `_aprop_enterra_specifications_source_url`
  - `_aprop_enterra_specification_meta_keys`
  - public filter-friendly keys like `aprop_spec_parametre-dronu_celkova-hmotnost-bez-baterii`
- Products with imported specifications show a read-only, grouped `Enterra špecifikácie` panel in the WooCommerce product editor.

## Feed

```text
https://feeds.mergado.com/enterra-sk-google-nakupy-sk-70a3cb5ee9479a6525566d5af13a3fe6.xml
```

## Local Site Feed Builder

Install the Python dependency (`beautifulsoup4`) and build the bundled feed directly from current Enterra product pages:

```bash
python3 scripts/build_site_feed_xml.py --output enterra-feed-with-specifications.xml
```

This reads current SK product URLs from Enterra product sitemaps, fetches each product page, and writes the fields the plugin imports: id, title, real SKU, formatted short and full descriptions, prices, stock, product category, full-size gallery, gallery videos, every product tab, specifications, in-box products, related-product SKUs, source metadata, and website-derived drone categories.

Test one product without replacing the bundled feed:

```bash
python3 scripts/build_site_feed_xml.py \
  --only-url https://www.enterra.sk/produkt/dji-agras-t100-inteligentny-ram/ \
  --output /tmp/enterra-t100.xml \
  --previous-feed enterra-feed-with-specifications.xml \
  --skip-web-categories
```

Use this when the Mergado feed is stale. After generating `enterra-feed-with-specifications.xml`, run `WooCommerce > Aprop Drone Feed > Sync / resync products`.

## Local Selenium Specification XML Builder

The feed does not include product specification rows from tabs like:

```text
https://www.enterra.sk/produkt/dji-agras-t70p/#tab-specifications
```

Build a local enriched XML file:

```bash
python3 scripts/build_specs_xml.py --output enterra-feed-with-specifications.xml
```

The script uses Selenium with headless Chrome by default. It writes the XML after every product, so if it stops you can run the same command again and it continues by skipping products that already have `aprop:specifications`.

Test one product:

```bash
python3 scripts/build_specs_xml.py --only-id 6846 --output enterra-t70p-with-specifications.xml
```

Force re-scrape already completed products:

```bash
python3 scripts/build_specs_xml.py --output enterra-feed-with-specifications.xml --force
```

Retry only products that previously produced zero specs:

```bash
python3 scripts/build_specs_xml.py --output enterra-feed-with-specifications.xml --retry-empty
```

Use a specific driver if needed:

```bash
python3 scripts/build_specs_xml.py --driver-path /path/to/chromedriver
```

The script adds or refreshes the complete `aprop:*` product-page payload, including specifications, in-box products, gallery media, all tabs, related products, and source metadata.
