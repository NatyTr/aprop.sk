# Aprop Drone Feed Sync

WordPress/WooCommerce plugin for importing all products from the configured Enterra/Mergado XML feed.

## Behavior

- Adds WooCommerce admin page: `WooCommerce > Aprop Drone Feed`.
- `Sync / resync products` reads the bundled enriched feed file `enterra-feed-with-specifications.xml`.
- Products are imported one by one through AJAX.
- The first sync can create products. Later resyncs update existing imported products only and skip new feed products.
- Existing products are matched by feed id stored in `_aprop_enterra_feed_id`, then by SKU, and updated with the latest title, description, price, stock, category, source URL, featured image, and specifications.
- Imported products are marked with `_aprop_is_feed_imported=1` and `_aprop_import_source=enterra_mergado_feed`.
- Product title strips the trailing `| Enterra.sk`.
- Product image is sideloaded from `image_link` and set as featured image. On resync, changed feed images replace the old featured image.
- `product_type` is parsed as a category path.
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

## Feed

```text
https://feeds.mergado.com/enterra-sk-google-nakupy-sk-70a3cb5ee9479a6525566d5af13a3fe6.xml
```

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

The script adds an `aprop:specifications` subtree to each feed item when specs are found.
