<?php
/**
 * Plugin Name: Aprop Drone Feed Sync
 * Description: Imports products from the Enterra/Mergado XML feed into WooCommerce.
 * Version: 0.1.0
 * Author: Aprop
 * Requires Plugins: woocommerce
 */

if (!defined('ABSPATH')) {
    exit;
}

final class Aprop_Drone_Feed_Sync {
    private const FEED_URL = 'https://feeds.mergado.com/enterra-sk-google-nakupy-sk-70a3cb5ee9479a6525566d5af13a3fe6.xml';
    private const LOCAL_FEED_FILE = 'enterra-feed-with-specifications.xml';
    private const PARENT_CATEGORY_ID = 211;
    private const NONCE_ACTION = 'aprop_drone_feed_sync';
    private const SYNC_TRANSIENT_PREFIX = 'aprop_drone_feed_sync_';
    private const DELETE_TRANSIENT_PREFIX = 'aprop_drone_feed_delete_';
    private const OPTION_INITIAL_SYNC_DONE = 'aprop_enterra_initial_sync_done';
    private const PRODUCT_META_FEED_ID = '_aprop_enterra_feed_id';
    private const PRODUCT_META_FEED_HASH = '_aprop_enterra_feed_hash';
    private const PRODUCT_META_SOURCE = '_aprop_import_source';
    private const PRODUCT_META_IS_IMPORTED = '_aprop_is_feed_imported';
    private const PRODUCT_META_SPECS_JSON = '_aprop_enterra_specifications_json';
    private const PRODUCT_META_SPECS_COUNT = '_aprop_enterra_specifications_count';
    private const PRODUCT_META_SPECS_SOURCE = '_aprop_enterra_specifications_source_url';
    private const PRODUCT_META_SPECS_KEYS = '_aprop_enterra_specification_meta_keys';
    private const PRODUCT_META_INCLUDED_PRODUCTS_JSON = '_aprop_enterra_included_products_json';
    private const PRODUCT_META_GALLERY_JSON = '_aprop_enterra_gallery_json';
    private const PRODUCT_META_GALLERY_COUNT = '_aprop_enterra_gallery_count';
    private const SOURCE_SLUG = 'enterra_mergado_feed';

    public function __construct() {
        add_action('admin_menu', [$this, 'register_admin_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_ajax_aprop_drone_feed_prepare_sync', [$this, 'ajax_prepare_sync']);
        add_action('wp_ajax_aprop_drone_feed_sync_product', [$this, 'ajax_sync_product']);
        add_action('wp_ajax_aprop_drone_feed_prepare_delete', [$this, 'ajax_prepare_delete']);
        add_action('wp_ajax_aprop_drone_feed_delete_product', [$this, 'ajax_delete_product']);
    }

    public function register_admin_page(): void {
        add_submenu_page(
            'woocommerce',
            'Aprop Drone Feed',
            'Aprop Drone Feed',
            'manage_woocommerce',
            'aprop-drone-feed',
            [$this, 'render_admin_page']
        );
    }

    public function enqueue_admin_assets(string $hook): void {
        if ($hook !== 'woocommerce_page_aprop-drone-feed') {
            return;
        }

        wp_register_script('aprop-drone-feed-admin', false, [], '0.1.0', true);
        wp_enqueue_script('aprop-drone-feed-admin');
        wp_add_inline_script('aprop-drone-feed-admin', $this->admin_js());
    }

    public function render_admin_page(): void {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('You do not have permission to sync products.', 'aprop-drone-feed'));
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Aprop Drone Feed Sync', 'aprop-drone-feed'); ?></h1>
            <p>
                <?php echo esc_html__('Imports products from the bundled enriched XML feed once. Later resyncs update existing imported products with latest feed data, stock status, images, and specifications.', 'aprop-drone-feed'); ?>
            </p>
            <table class="widefat striped" style="max-width: 920px;">
                <tbody>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Feed file', 'aprop-drone-feed'); ?></th>
                        <td><code><?php echo esc_html(self::LOCAL_FEED_FILE); ?></code></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Sync mode', 'aprop-drone-feed'); ?></th>
                        <td>
                            <?php
                            echo (get_option(self::OPTION_INITIAL_SYNC_DONE) || $this->find_imported_product_ids())
                                ? esc_html__('Resync existing imported products only', 'aprop-drone-feed')
                                : esc_html__('Initial import can create products', 'aprop-drone-feed');
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Parent category', 'aprop-drone-feed'); ?></th>
                        <td><?php echo esc_html(sprintf('product_cat #%d', self::PARENT_CATEGORY_ID)); ?></td>
                    </tr>
                </tbody>
            </table>

            <p>
                <button
                    type="button"
                    class="button button-primary"
                    id="aprop-drone-feed-sync"
                    data-nonce="<?php echo esc_attr(wp_create_nonce(self::NONCE_ACTION)); ?>"
                >
                    <?php echo esc_html__('Sync / resync products', 'aprop-drone-feed'); ?>
                </button>
                <button
                    type="button"
                    class="button button-secondary"
                    id="aprop-drone-feed-delete"
                    data-nonce="<?php echo esc_attr(wp_create_nonce(self::NONCE_ACTION)); ?>"
                    style="margin-left: 8px;"
                >
                    <?php echo esc_html__('Delete imported products', 'aprop-drone-feed'); ?>
                </button>
            </p>

            <div id="aprop-drone-feed-status" aria-live="polite"></div>
            <progress id="aprop-drone-feed-progress" max="100" value="0" style="display:none; width: 420px;"></progress>
            <ol id="aprop-drone-feed-log" style="max-width: 920px;"></ol>
        </div>
        <?php
    }

    public function ajax_prepare_sync(): void {
        $this->assert_ajax_permission();

        $products = $this->fetch_feed_products();
        if (is_wp_error($products)) {
            wp_send_json_error(['message' => $products->get_error_message()], 500);
        }

        $mode = (get_option(self::OPTION_INITIAL_SYNC_DONE) || $this->find_imported_product_ids()) ? 'resync' : 'initial';
        $sync_id = wp_generate_uuid4();
        set_transient(self::SYNC_TRANSIENT_PREFIX . $sync_id, [
            'mode' => $mode,
            'products' => $products,
        ], HOUR_IN_SECONDS);

        wp_send_json_success([
            'sync_id' => $sync_id,
            'total' => count($products),
            'mode' => $mode,
        ]);
    }

    public function ajax_sync_product(): void {
        $this->assert_ajax_permission();

        $sync_id = isset($_POST['sync_id']) ? sanitize_text_field(wp_unslash($_POST['sync_id'])) : '';
        $index = isset($_POST['index']) ? absint($_POST['index']) : 0;

        if ($sync_id === '') {
            wp_send_json_error(['message' => 'Missing sync id.'], 400);
        }

        $sync_data = get_transient(self::SYNC_TRANSIENT_PREFIX . $sync_id);
        if (!is_array($sync_data) || !isset($sync_data['products']) || !is_array($sync_data['products'])) {
            wp_send_json_error(['message' => 'Sync data expired. Start a new sync.'], 410);
        }

        $products = $sync_data['products'];
        $mode = $sync_data['mode'] ?? 'initial';

        if (!isset($products[$index])) {
            delete_transient(self::SYNC_TRANSIENT_PREFIX . $sync_id);
            update_option(self::OPTION_INITIAL_SYNC_DONE, '1', false);
            wp_send_json_success([
                'done' => true,
                'index' => $index,
                'total' => count($products),
                'message' => 'Sync completed.',
            ]);
        }

        $result = $this->import_product($products[$index], $mode);
        if (is_wp_error($result)) {
            wp_send_json_error([
                'message' => $result->get_error_message(),
                'index' => $index,
                'product' => $products[$index]['title'] ?? '',
            ], 500);
        }

        $next_index = $index + 1;
        if ($next_index >= count($products)) {
            delete_transient(self::SYNC_TRANSIENT_PREFIX . $sync_id);
            update_option(self::OPTION_INITIAL_SYNC_DONE, '1', false);
        }

        wp_send_json_success([
            'done' => $next_index >= count($products),
            'index' => $index,
            'next_index' => $next_index,
            'total' => count($products),
            'product_id' => $result['product_id'],
            'action' => $result['action'],
            'title' => $result['title'],
            'mode' => $mode,
        ]);
    }

    public function ajax_prepare_delete(): void {
        $this->assert_ajax_permission();

        $product_ids = $this->find_imported_product_ids();
        $delete_id = wp_generate_uuid4();
        set_transient(self::DELETE_TRANSIENT_PREFIX . $delete_id, $product_ids, HOUR_IN_SECONDS);

        wp_send_json_success([
            'delete_id' => $delete_id,
            'total' => count($product_ids),
        ]);
    }

    public function ajax_delete_product(): void {
        $this->assert_ajax_permission();

        $delete_id = isset($_POST['delete_id']) ? sanitize_text_field(wp_unslash($_POST['delete_id'])) : '';
        $index = isset($_POST['index']) ? absint($_POST['index']) : 0;

        if ($delete_id === '') {
            wp_send_json_error(['message' => 'Missing delete id.'], 400);
        }

        $product_ids = get_transient(self::DELETE_TRANSIENT_PREFIX . $delete_id);
        if (!is_array($product_ids)) {
            wp_send_json_error(['message' => 'Delete data expired. Start a new delete run.'], 410);
        }

        if (!isset($product_ids[$index])) {
            delete_transient(self::DELETE_TRANSIENT_PREFIX . $delete_id);
            wp_send_json_success([
                'done' => true,
                'index' => $index,
                'total' => count($product_ids),
                'message' => 'Delete completed.',
            ]);
        }

        $product_id = (int) $product_ids[$index];
        $title = get_the_title($product_id);
        $image_id = (int) get_post_thumbnail_id($product_id);

        $deleted = wp_delete_post($product_id, true);
        if (!$deleted) {
            wp_send_json_error([
                'message' => sprintf('Could not delete product #%d.', $product_id),
                'index' => $index,
            ], 500);
        }

        $image_deleted = false;
        if ($image_id && $this->is_imported_attachment($image_id) && !$this->posts_using_featured_image($image_id)) {
            $image_deleted = (bool) wp_delete_attachment($image_id, true);
        }

        $next_index = $index + 1;
        if ($next_index >= count($product_ids)) {
            delete_transient(self::DELETE_TRANSIENT_PREFIX . $delete_id);
            delete_option(self::OPTION_INITIAL_SYNC_DONE);
        }

        wp_send_json_success([
            'done' => $next_index >= count($product_ids),
            'index' => $index,
            'next_index' => $next_index,
            'total' => count($product_ids),
            'product_id' => $product_id,
            'title' => $title,
            'image_deleted' => $image_deleted,
        ]);
    }

    private function assert_ajax_permission(): void {
        if (!check_ajax_referer(self::NONCE_ACTION, 'nonce', false)) {
            wp_send_json_error(['message' => 'Security check failed. Refresh the page and try again.'], 403);
        }

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => 'Missing permission.'], 403);
        }

        if (!function_exists('wc_get_product')) {
            wp_send_json_error(['message' => 'WooCommerce is required.'], 500);
        }
    }

    private function fetch_feed_products() {
        $local_feed_path = plugin_dir_path(__FILE__) . self::LOCAL_FEED_FILE;
        $body = '';

        if (is_readable($local_feed_path)) {
            $body = (string) file_get_contents($local_feed_path);
        } else {
            $response = wp_remote_get(self::FEED_URL, [
                'timeout' => 45,
                'headers' => [
                    'Accept' => 'application/xml,text/xml,*/*',
                    'User-Agent' => 'ApropDroneFeedPlugin/0.1.0',
                ],
            ]);

            if (is_wp_error($response)) {
                return $response;
            }

            $status = wp_remote_retrieve_response_code($response);
            if ($status < 200 || $status >= 300) {
                return new WP_Error('feed_http_error', sprintf('Feed returned HTTP %d.', $status));
            }

            $body = wp_remote_retrieve_body($response);
        }

        if ($body === '') {
            return new WP_Error('feed_empty', 'Feed response is empty.');
        }

        $previous = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($body);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$xml) {
            $message = isset($errors[0]) ? trim($errors[0]->message) : 'Could not parse feed XML.';
            return new WP_Error('feed_parse_error', $message);
        }

        $items = $xml->xpath('//*[local-name()="item"]');
        if (!is_array($items)) {
            return new WP_Error('feed_items_missing', 'No feed items found.');
        }

        return array_map([$this, 'parse_feed_item'], $items);
    }

    private function parse_feed_item(SimpleXMLElement $item): array {
        $product = $this->parse_feed_children($item);

        foreach ($item->getDocNamespaces(true) as $namespace_uri) {
            foreach ($item->children($namespace_uri) as $child) {
                $product[$child->getName()] = $this->normalize_feed_value((string) $child);
            }
        }

        $product['specifications'] = $this->parse_feed_specifications($item);
        $product['included_products'] = $this->parse_feed_included_products($item);
        $product['gallery_images'] = $this->parse_feed_gallery_images($item);
        $product['web_categories'] = $this->parse_feed_web_categories($item);

        return $product;
    }

    private function parse_feed_specifications(SimpleXMLElement $item): array {
        $nodes = $item->xpath('./*[local-name()="specifications"]');
        if (!is_array($nodes) || empty($nodes[0])) {
            return [
                'count' => 0,
                'source_url' => '',
                'fetched_at' => '',
                'rows' => [],
            ];
        }

        $specs_node = $nodes[0];
        $rows = [];

        foreach ($specs_node->xpath('./*[local-name()="section"]') ?: [] as $section) {
            $section_name = (string) ($section['name'] ?? '');

            foreach ($section->xpath('./*[local-name()="spec"]') ?: [] as $spec) {
                $value_nodes = $spec->xpath('./*[local-name()="value"]');
                $rows[] = [
                    'section' => $this->normalize_feed_value($section_name),
                    'name' => $this->normalize_feed_value((string) ($spec['name'] ?? '')),
                    'value' => $this->normalize_feed_value(!empty($value_nodes[0]) ? (string) $value_nodes[0] : ''),
                ];
            }
        }

        return [
            'count' => count($rows),
            'source_url' => esc_url_raw((string) ($specs_node['source_url'] ?? '')),
            'fetched_at' => $this->normalize_feed_value((string) ($specs_node['fetched_at'] ?? '')),
            'rows' => $rows,
        ];
    }

    private function parse_feed_included_products(SimpleXMLElement $item): array {
        $nodes = $item->xpath('./*[local-name()="products"]');
        if (!is_array($nodes) || empty($nodes[0])) {
            return [
                'count' => 0,
                'source_url' => '',
                'fetched_at' => '',
                'rows' => [],
            ];
        }

        $products_node = $nodes[0];
        $rows = [];

        foreach ($products_node->xpath('./*[local-name()="product"]') ?: [] as $product) {
            $name_nodes = $product->xpath('./*[local-name()="name"]');
            $quantity_nodes = $product->xpath('./*[local-name()="quantity"]');
            $rows[] = [
                'name' => $this->normalize_feed_value(!empty($name_nodes[0]) ? (string) $name_nodes[0] : ''),
                'quantity' => $this->normalize_feed_value(!empty($quantity_nodes[0]) ? (string) $quantity_nodes[0] : ''),
            ];
        }

        return [
            'count' => count($rows),
            'source_url' => esc_url_raw((string) ($products_node['source_url'] ?? '')),
            'fetched_at' => $this->normalize_feed_value((string) ($products_node['fetched_at'] ?? '')),
            'rows' => $rows,
        ];
    }

    private function parse_feed_gallery_images(SimpleXMLElement $item): array {
        $nodes = $item->xpath('./*[local-name()="gallery"]');
        if (!is_array($nodes) || empty($nodes[0])) {
            return [
                'count' => 0,
                'source_url' => '',
                'fetched_at' => '',
                'images' => [],
            ];
        }

        $gallery_node = $nodes[0];
        $images = [];

        foreach ($gallery_node->xpath('./*[local-name()="image"]') ?: [] as $image) {
            $url = esc_url_raw((string) ($image['url'] ?? ''));
            if ($url === '') {
                continue;
            }

            $images[] = [
                'url' => $url,
                'alt' => $this->normalize_feed_value((string) ($image['alt'] ?? '')),
                'title' => $this->normalize_feed_value((string) ($image['title'] ?? '')),
            ];
        }

        return [
            'count' => count($images),
            'source_url' => esc_url_raw((string) ($gallery_node['source_url'] ?? '')),
            'fetched_at' => $this->normalize_feed_value((string) ($gallery_node['fetched_at'] ?? '')),
            'images' => $images,
        ];
    }

    private function parse_feed_web_categories(SimpleXMLElement $item): array {
        $nodes = $item->xpath('./*[local-name()="web_categories"]');
        if (!is_array($nodes) || empty($nodes[0])) {
            return [
                'count' => 0,
                'source_url' => '',
                'fetched_at' => '',
                'rows' => [],
            ];
        }

        $categories_node = $nodes[0];
        $rows = [];

        foreach ($categories_node->xpath('./*[local-name()="category"]') ?: [] as $category) {
            $path = $this->normalize_feed_value((string) ($category['path'] ?? ''));
            if ($path === '') {
                continue;
            }

            $rows[] = [
                'name' => $this->normalize_feed_value((string) ($category['name'] ?? '')),
                'path' => $path,
                'url' => esc_url_raw((string) ($category['url'] ?? '')),
                'product_title' => $this->normalize_feed_value((string) ($category['product_title'] ?? '')),
            ];
        }

        return [
            'count' => count($rows),
            'source_url' => $this->normalize_feed_value((string) ($categories_node['source_url'] ?? '')),
            'fetched_at' => $this->normalize_feed_value((string) ($categories_node['fetched_at'] ?? '')),
            'rows' => $rows,
        ];
    }

    private function parse_feed_children(SimpleXMLElement $item): array {
        $product = [];

        foreach ($item->children() as $child) {
            $product[$child->getName()] = $this->normalize_feed_value((string) $child);
        }

        return $product;
    }

    private function normalize_feed_value(string $value): string {
        return trim((string) preg_replace('/\s+/u', ' ', html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private function import_product(array $feed_product, string $mode = 'initial') {
        $feed_id = $feed_product['id'] ?? '';
        if ($feed_id === '') {
            return new WP_Error('missing_feed_id', 'Feed product is missing id.');
        }

        $product_id = $this->find_product_id_by_feed_id($feed_id);
        if (!$product_id && $mode === 'resync') {
            return [
                'product_id' => 0,
                'action' => 'skipped',
                'title' => $this->clean_enterra_suffix($feed_product['title'] ?? ''),
            ];
        }

        $action = $product_id ? 'updated' : 'created';
        $product = $product_id ? wc_get_product($product_id) : new WC_Product_Simple();

        if (!$product) {
            return new WP_Error('product_load_failed', sprintf('Could not load product for feed id %s.', $feed_id));
        }

        $title = $this->clean_enterra_suffix($feed_product['title'] ?? '');
        $description = $this->format_product_description($this->clean_enterra_suffix($feed_product['description'] ?? ''));
        $price = $this->parse_price($feed_product['price'] ?? '');
        $stock_status = $this->map_stock_status($feed_product['availability'] ?? '');

        try {
            $product->set_name($title);
            $product->set_status('publish');
            $product->set_catalog_visibility('visible');
            $product->set_description($description);
            $product->set_short_description(wp_trim_words(wp_strip_all_tags($description), 40));
            $product->set_sku($this->sku_from_feed_id($feed_id));
            $product->set_manage_stock(false);
            $product->set_stock_status($stock_status);
            $product->set_backorders($stock_status === 'onbackorder' ? 'yes' : 'no');

            if ($price !== null) {
                $product->set_regular_price((string) $price);
            }
        } catch (Exception $exception) {
            return new WP_Error('product_set_failed', $exception->getMessage());
        }

        $category_ids = $this->ensure_category_paths($this->category_paths_for_product($feed_product));
        if (is_wp_error($category_ids)) {
            return $category_ids;
        }

        if ($category_ids) {
            $product->set_category_ids($category_ids);
        }

        try {
            $product_id = $product->save();
        } catch (Exception $exception) {
            return new WP_Error('product_save_failed', $exception->getMessage());
        }
        update_post_meta($product_id, self::PRODUCT_META_IS_IMPORTED, '1');
        update_post_meta($product_id, self::PRODUCT_META_SOURCE, self::SOURCE_SLUG);
        update_post_meta($product_id, self::PRODUCT_META_FEED_ID, $feed_id);
        update_post_meta($product_id, self::PRODUCT_META_FEED_HASH, md5(wp_json_encode($feed_product)));
        update_post_meta($product_id, '_aprop_enterra_feed_url', self::LOCAL_FEED_FILE);
        $this->store_product_specifications($product_id, $feed_product['specifications'] ?? []);
        $this->store_included_products($product_id, $feed_product['included_products'] ?? []);

        if (!empty($feed_product['link'])) {
            update_post_meta($product_id, '_aprop_enterra_source_url', esc_url_raw($feed_product['link']));
        }

        if (!empty($feed_product['image_link'])) {
            $this->set_product_image($product_id, $feed_product['image_link'], $title);
        }

        $this->set_product_gallery($product_id, $feed_product['gallery_images'] ?? [], $feed_product['image_link'] ?? '', $title);

        return [
            'product_id' => $product_id,
            'action' => $action,
            'title' => $title,
        ];
    }

    private function clean_enterra_suffix(string $value): string {
        return trim(preg_replace('/\s*\|\s*Enterra\.sk\s*$/u', '', $value));
    }

    private function format_product_description(string $description): string {
        $description = trim($description);
        if ($description === '') {
            return '';
        }

        $description = preg_replace('/([.!?])(?=(?:\p{Lu}|\p{N}))/u', "$1\n\n", $description);
        $description = preg_replace("/[ \t]*\n[ \t]*/", "\n", (string) $description);
        $description = preg_replace("/\n{3,}/", "\n\n", (string) $description);

        return trim((string) $description);
    }

    private function parse_price(string $price): ?float {
        if ($price === '') {
            return null;
        }

        $normalized = preg_replace('/[^0-9.,]/', '', $price);
        if ($normalized === '') {
            return null;
        }

        return (float) str_replace(',', '.', $normalized);
    }

    private function map_stock_status(string $availability): string {
        switch ($availability) {
            case 'in_stock':
                return 'instock';
            case 'backorder':
                return 'onbackorder';
            case 'out_of_stock':
                return 'outofstock';
            default:
                return 'outofstock';
        }
    }

    private function sku_from_feed_id(string $feed_id): string {
        return 'enterra-' . sanitize_title($feed_id);
    }

    private function find_product_id_by_feed_id(string $feed_id): int {
        $query = new WP_Query([
            'post_type' => 'product',
            'post_status' => ['publish', 'draft', 'pending', 'private'],
            'fields' => 'ids',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => self::PRODUCT_META_FEED_ID,
                    'value' => $feed_id,
                ],
            ],
            'no_found_rows' => true,
        ]);

        if (!empty($query->posts[0])) {
            return (int) $query->posts[0];
        }

        $sku_id = wc_get_product_id_by_sku($this->sku_from_feed_id($feed_id));
        return $sku_id ? (int) $sku_id : 0;
    }

    private function find_imported_product_ids(): array {
        $query = new WP_Query([
            'post_type' => 'product',
            'post_status' => ['publish', 'draft', 'pending', 'private', 'trash'],
            'fields' => 'ids',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => self::PRODUCT_META_IS_IMPORTED,
                    'value' => '1',
                ],
                [
                    'key' => self::PRODUCT_META_SOURCE,
                    'value' => self::SOURCE_SLUG,
                ],
            ],
            'orderby' => 'ID',
            'order' => 'ASC',
            'no_found_rows' => true,
        ]);

        return array_map('intval', $query->posts);
    }

    private function store_product_specifications(int $product_id, array $specifications): void {
        $rows = [];
        if (isset($specifications['rows']) && is_array($specifications['rows'])) {
            $rows = $specifications['rows'];
        }

        $this->delete_generated_spec_meta($product_id);

        $meta_keys = [];
        $seen_keys = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $section = $this->normalize_feed_value((string) ($row['section'] ?? ''));
            $name = $this->normalize_feed_value((string) ($row['name'] ?? ''));
            $value = $this->normalize_feed_value((string) ($row['value'] ?? ''));

            if ($name === '' || $value === '') {
                continue;
            }

            $base_key = 'aprop_spec_' . sanitize_title($section . '_' . $name);
            $key = $base_key;
            $seen_keys[$base_key] = ($seen_keys[$base_key] ?? 0) + 1;
            if ($seen_keys[$base_key] > 1) {
                $key = $base_key . '_' . $seen_keys[$base_key];
            }

            update_post_meta($product_id, $key, $value);
            $meta_keys[] = [
                'key' => $key,
                'section' => $section,
                'name' => $name,
                'value' => $value,
            ];
        }

        update_post_meta($product_id, self::PRODUCT_META_SPECS_COUNT, count($meta_keys));
        update_post_meta($product_id, self::PRODUCT_META_SPECS_JSON, wp_json_encode([
            'count' => count($meta_keys),
            'source_url' => $specifications['source_url'] ?? '',
            'fetched_at' => $specifications['fetched_at'] ?? '',
            'rows' => array_values(array_filter($rows, 'is_array')),
        ], JSON_UNESCAPED_UNICODE));
        update_post_meta($product_id, self::PRODUCT_META_SPECS_SOURCE, esc_url_raw((string) ($specifications['source_url'] ?? '')));
        update_post_meta($product_id, self::PRODUCT_META_SPECS_KEYS, wp_json_encode($meta_keys, JSON_UNESCAPED_UNICODE));
    }

    private function store_included_products(int $product_id, array $included_products): void {
        $rows = isset($included_products['rows']) && is_array($included_products['rows'])
            ? array_values(array_filter($included_products['rows'], 'is_array'))
            : [];

        update_post_meta($product_id, self::PRODUCT_META_INCLUDED_PRODUCTS_JSON, wp_json_encode([
            'count' => count($rows),
            'source_url' => $included_products['source_url'] ?? '',
            'fetched_at' => $included_products['fetched_at'] ?? '',
            'rows' => $rows,
        ], JSON_UNESCAPED_UNICODE));
    }

    private function delete_generated_spec_meta(int $product_id): void {
        foreach (get_post_meta($product_id) as $key => $values) {
            if (strpos((string) $key, 'aprop_spec_') === 0 || strpos((string) $key, '_aprop_spec_') === 0) {
                delete_post_meta($product_id, $key);
            }
        }
    }

    private function category_paths_for_product(array $feed_product): array {
        $web_categories = $feed_product['web_categories']['rows'] ?? [];
        if (is_array($web_categories) && $web_categories) {
            $paths = [];
            foreach ($web_categories as $category) {
                if (!is_array($category)) {
                    continue;
                }

                $path = $this->normalize_feed_value((string) ($category['path'] ?? ''));
                if ($path !== '') {
                    $paths[] = $path;
                }
            }

            $paths = array_values(array_unique($paths));
            if ($paths) {
                return $paths;
            }
        }

        return [$feed_product['product_type'] ?? ''];
    }

    private function ensure_category_paths(array $category_paths) {
        $category_ids = [];

        foreach ($category_paths as $category_path) {
            $path_ids = $this->ensure_category_path((string) $category_path);
            if (is_wp_error($path_ids)) {
                return $path_ids;
            }

            $category_ids = array_merge($category_ids, $path_ids);
        }

        return array_values(array_unique(array_filter(array_map('intval', $category_ids))));
    }

    private function ensure_category_path(string $product_type) {
        $parts = array_values(array_filter(array_map(function (string $part): string {
            return trim(html_entity_decode($part, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }, explode('>', $product_type))));

        if (($parts[0] ?? '') === 'Home') {
            array_shift($parts);
        }

        $root = term_exists(self::PARENT_CATEGORY_ID, 'product_cat');
        if (!$root) {
            return new WP_Error(
                'missing_parent_category',
                sprintf('Required parent product category #%d does not exist.', self::PARENT_CATEGORY_ID)
            );
        }

        $parent_id = self::PARENT_CATEGORY_ID;
        $category_ids = [$parent_id];

        foreach ($parts as $part) {
            $term = term_exists($part, 'product_cat', $parent_id);

            if (!$term) {
                $term = wp_insert_term($part, 'product_cat', ['parent' => $parent_id]);
            }

            if (is_wp_error($term)) {
                return $term;
            }

            $term_id = is_array($term) ? (int) $term['term_id'] : (int) $term;
            $category_ids[] = $term_id;
            $parent_id = $term_id;
        }

        return array_values(array_unique(array_filter($category_ids)));
    }

    private function set_product_image(int $product_id, string $image_url, string $title): void {
        $current_image_id = (int) get_post_thumbnail_id($product_id);

        if ($current_image_id) {
            return;
        }

        $attachment_id = $this->sideload_product_image($product_id, $image_url, $title);
        if (is_wp_error($attachment_id)) {
            update_post_meta($product_id, '_aprop_enterra_image_error', $attachment_id->get_error_message());
            return;
        }

        set_post_thumbnail($product_id, (int) $attachment_id);
        $this->delete_unused_imported_attachment($current_image_id);
        delete_post_meta($product_id, '_aprop_enterra_image_error');
    }

    private function set_product_gallery(int $product_id, array $gallery, string $featured_image_url, string $product_title): void {
        $images = isset($gallery['images']) && is_array($gallery['images']) ? $gallery['images'] : [];
        $featured_image_url = esc_url_raw($featured_image_url);
        $attachment_ids = [];
        $seen_urls = [];
        $errors = [];

        foreach ($images as $image) {
            if (!is_array($image)) {
                continue;
            }

            $image_url = esc_url_raw((string) ($image['url'] ?? ''));
            if ($image_url === '' || $image_url === $featured_image_url || isset($seen_urls[$image_url])) {
                continue;
            }

            $seen_urls[$image_url] = true;
            $attachment_id = $this->sideload_product_image(
                $product_id,
                $image_url,
                $this->normalize_feed_value((string) ($image['title'] ?? '')) ?: $product_title,
                $this->normalize_feed_value((string) ($image['alt'] ?? ''))
            );

            if (is_wp_error($attachment_id)) {
                $errors[] = $attachment_id->get_error_message();
                continue;
            }

            $attachment_ids[] = (int) $attachment_id;
        }

        $product = wc_get_product($product_id);
        if ($product) {
            $product->set_gallery_image_ids($attachment_ids);
            $product->save();
        }

        update_post_meta($product_id, self::PRODUCT_META_GALLERY_COUNT, count($attachment_ids));
        update_post_meta($product_id, self::PRODUCT_META_GALLERY_JSON, wp_json_encode([
            'count' => count($attachment_ids),
            'source_url' => $gallery['source_url'] ?? '',
            'fetched_at' => $gallery['fetched_at'] ?? '',
            'images' => array_values(array_filter($images, 'is_array')),
            'attachment_ids' => $attachment_ids,
        ], JSON_UNESCAPED_UNICODE));

        if ($errors) {
            update_post_meta($product_id, '_aprop_enterra_gallery_error', implode("\n", $errors));
        } else {
            delete_post_meta($product_id, '_aprop_enterra_gallery_error');
        }
    }

    private function sideload_product_image(int $product_id, string $image_url, string $title, string $alt = '') {
        $existing_attachment_id = $this->find_attachment_id_by_image_url($image_url);
        if ($existing_attachment_id) {
            return $existing_attachment_id;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $attachment_id = media_sideload_image($image_url, $product_id, $title, 'id');
        if (is_wp_error($attachment_id)) {
            return $attachment_id;
        }

        $attachment_id = (int) $attachment_id;
        update_post_meta($attachment_id, '_aprop_enterra_image_url', esc_url_raw($image_url));
        update_post_meta($attachment_id, self::PRODUCT_META_SOURCE, self::SOURCE_SLUG);
        update_post_meta($attachment_id, self::PRODUCT_META_IS_IMPORTED, '1');

        if ($alt !== '') {
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);
        }

        return $attachment_id;
    }

    private function find_attachment_id_by_image_url(string $image_url): int {
        $query = new WP_Query([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'fields' => 'ids',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_aprop_enterra_image_url',
                    'value' => esc_url_raw($image_url),
                ],
            ],
            'no_found_rows' => true,
        ]);

        return !empty($query->posts[0]) ? (int) $query->posts[0] : 0;
    }

    private function is_imported_attachment(int $attachment_id): bool {
        return get_post_meta($attachment_id, self::PRODUCT_META_IS_IMPORTED, true) === '1'
            && get_post_meta($attachment_id, self::PRODUCT_META_SOURCE, true) === self::SOURCE_SLUG;
    }

    private function delete_unused_imported_attachment(int $attachment_id): void {
        if (!$attachment_id || !$this->is_imported_attachment($attachment_id)) {
            return;
        }

        if (!$this->posts_using_featured_image($attachment_id)) {
            wp_delete_attachment($attachment_id, true);
        }
    }

    private function posts_using_featured_image(int $image_id): int {
        $query = new WP_Query([
            'post_type' => 'any',
            'post_status' => ['publish', 'draft', 'pending', 'private', 'trash', 'future'],
            'fields' => 'ids',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_thumbnail_id',
                    'value' => (string) $image_id,
                ],
            ],
            'no_found_rows' => true,
        ]);

        return count($query->posts);
    }

    private function admin_js(): string {
        return <<<'JS'
(function () {
    const syncButton = document.getElementById('aprop-drone-feed-sync');
    const deleteButton = document.getElementById('aprop-drone-feed-delete');
    const status = document.getElementById('aprop-drone-feed-status');
    const progress = document.getElementById('aprop-drone-feed-progress');
    const log = document.getElementById('aprop-drone-feed-log');

    if (!syncButton || !deleteButton || !status || !progress || !log) {
        return;
    }

    function setStatus(message) {
        status.textContent = message;
    }

    function appendLog(message) {
        const item = document.createElement('li');
        item.textContent = message;
        log.prepend(item);
    }

    async function post(action, data) {
        const body = new URLSearchParams({
            action,
            nonce: syncButton.dataset.nonce,
            ...data
        });

        const response = await fetch(ajaxurl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
            body
        });

        const json = await response.json();
        if (!json.success) {
            throw new Error((json.data && json.data.message) || 'Request failed.');
        }

        return json.data;
    }

    async function syncProduct(syncId, index, total) {
        const data = await post('aprop_drone_feed_sync_product', {
            sync_id: syncId,
            index: String(index)
        });

        const current = data.next_index || total;
        progress.value = Math.round((current / total) * 100);
        setStatus(`Synced ${current} / ${total} products.`);
        if (data.action === 'skipped') {
            appendLog(`#${current}: skipped new product during resync - ${data.title}`);
        } else {
            appendLog(`#${current}: ${data.action} product ${data.product_id} - ${data.title}`);
        }
        return data;
    }

    async function deleteProduct(deleteId, index, total) {
        const data = await post('aprop_drone_feed_delete_product', {
            delete_id: deleteId,
            index: String(index)
        });

        const current = data.next_index || total;
        progress.value = Math.round((current / total) * 100);
        setStatus(`Deleted ${current} / ${total} imported products.`);
        appendLog(`#${current}: deleted product ${data.product_id} - ${data.title}${data.image_deleted ? ' (image deleted)' : ''}`);
        return data;
    }

    function setButtonsDisabled(disabled) {
        syncButton.disabled = disabled;
        deleteButton.disabled = disabled;
    }

    syncButton.addEventListener('click', async function () {
        setButtonsDisabled(true);
        log.innerHTML = '';
        progress.style.display = 'block';
        progress.value = 0;
        setStatus('Preparing feed...');

        try {
            const prepared = await post('aprop_drone_feed_prepare_sync', {});
            progress.max = 100;
            setStatus(`Feed loaded. Found ${prepared.total} products. Mode: ${prepared.mode}.`);

            let index = 0;
            while (index < prepared.total) {
                const result = await syncProduct(prepared.sync_id, index, prepared.total);
                if (result.done) {
                    break;
                }
                index = result.next_index;
            }

            progress.value = 100;
            setStatus(`Sync completed. ${prepared.total} products processed. Mode: ${prepared.mode}.`);
        } catch (error) {
            setStatus(`Sync failed: ${error.message}`);
            appendLog(`Error: ${error.message}`);
        } finally {
            setButtonsDisabled(false);
        }
    });

    deleteButton.addEventListener('click', async function () {
        if (!window.confirm('Delete all products imported by this feed plugin, including imported featured images?')) {
            return;
        }

        setButtonsDisabled(true);
        log.innerHTML = '';
        progress.style.display = 'block';
        progress.value = 0;
        setStatus('Preparing delete...');

        try {
            const prepared = await post('aprop_drone_feed_prepare_delete', {});
            progress.max = 100;
            setStatus(`Found ${prepared.total} imported products to delete.`);

            let index = 0;
            while (index < prepared.total) {
                const result = await deleteProduct(prepared.delete_id, index, prepared.total);
                if (result.done) {
                    break;
                }
                index = result.next_index;
            }

            progress.value = 100;
            setStatus(`Delete completed. ${prepared.total} imported products processed.`);
        } catch (error) {
            setStatus(`Delete failed: ${error.message}`);
            appendLog(`Error: ${error.message}`);
        } finally {
            setButtonsDisabled(false);
        }
    });
})();
JS;
    }
}

new Aprop_Drone_Feed_Sync();
