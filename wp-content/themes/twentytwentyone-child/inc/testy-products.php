<?php

function aprop_testy_products_category_slug() {
    return 'testy-pripravy';
}

function aprop_testy_products_category_name() {
    return 'Testy – prípravy';
}

function aprop_is_testy_product( $product_id = null ) {
    $product_id = $product_id ? (int) $product_id : get_the_ID();
    if ( ! $product_id ) {
        return false;
    }

    return has_term( aprop_testy_products_category_slug(), 'product_cat', $product_id );
}

function aprop_get_testy_product_definitions() {
    return array(
        'testy-a1-a3' => array(
            'name'              => 'A1/A3',
            'price'             => '0',
            'menu_order'        => 10,
            'layout'            => 'standard',
            'badge'             => 'ZAČNITE ZADARMO',
            'badge_style'       => 'green',
            'description'       => 'Pre budúcich pilotov, ktorí chcú zvládnuť základnú teóriu v otvorenej kategórii.',
            'stats'             => array(
                array( 'value' => '208 otázok', 'label' => 'v databáze' ),
                array( 'value' => 'Online', 'label' => 'vo vašom účte' ),
            ),
            'features_title'    => 'ČO SI PRECVIČÍTE',
            'features'          => array(
                'Pravidlá a bezpečnosť letu',
                'Vzdušný priestor a obmedzenia',
                'Základy prevádzky dronu',
            ),
            'price_label'       => 'Zadarmo',
            'price_note'        => 'Po registrácii',
            'button_text'       => 'Začať s A1/A3 zadarmo',
            'button_style'      => 'green',
            // Free CTA — no add-to-cart.
            'button_url'        => 'https://testy.aprop.sk',
        ),
        'testy-a2' => array(
            'name'              => 'A2',
            'price'             => '24.90',
            'menu_order'        => 20,
            'layout'            => 'standard',
            'badge'             => 'NADVIAŽTE NA ZÁKLADY',
            'badge_style'       => 'gray',
            'description'       => 'Pre pilotov, ktorí nadväzujú na A1/A3 a chcú zvládnuť špecifickú teóriu k skúške A2.',
            'stats'             => array(
                array( 'value' => '278 otázok', 'label' => 'v databáze' ),
                array( 'value' => '1 mesiac', 'label' => 'dĺžka prístupu' ),
            ),
            'features_title'    => 'ČO SI PRECVIČÍTE',
            'features'          => array(
                'Meteorológia',
                'Letové výkony dronu',
                'Znižovanie rizík na zemi',
            ),
            'price_label'       => '',
            'price_note'        => 'Jednorazový prístup na 1 mesiac',
            'button_text'       => 'Vybrať prípravu A2',
            'button_style'      => 'black',
        ),
        'testy-sts' => array(
            'name'              => 'STS',
            'price'             => '59.90',
            'menu_order'        => 30,
            'layout'            => 'standard',
            'badge'             => 'ŠTANDARDNÉ SCENÁRE',
            'badge_style'       => 'gray',
            'description'       => 'Pre pilotov, ktorí sa pripravujú na štandardné scenáre STS-01 a STS-02.',
            'stats'             => array(
                array( 'value' => '218 otázok', 'label' => 'v databáze' ),
                array( 'value' => '1 mesiac', 'label' => 'dĺžka prístupu' ),
            ),
            'features_title'    => 'ČO SI PRECVIČÍTE',
            'features'          => array(
                'Prevádzkové postupy',
                'Riziká na zemi aj vo vzduchu',
                'Predpisy a poznanie dronu',
            ),
            'price_label'       => '',
            'price_note'        => 'Jednorazový prístup na 1 mesiac',
            'button_text'       => 'Vybrať prípravu STS',
            'button_style'      => 'black',
        ),
        'testy-a2-sts' => array(
            'name'              => 'A2 + STS',
            'price'             => '69',
            'menu_order'        => 40,
            'layout'            => 'bundle',
            'badge'             => 'DVE PRÍPRAVY V JEDNOM',
            'badge_style'       => 'gray',
            'description'       => 'Dve databázy otázok a spoločný prístup. Pre tých, ktorí sa pripravujú na obe teoretické skúšky.',
            'stats'             => array(
                array( 'value' => '647 otázok', 'label' => '' ),
                array( 'value' => '3 mesiace', 'label' => '' ),
            ),
            'features_title'    => '',
            'features'          => array(),
            'price_label'       => '',
            'price_note'        => 'jednorazovo',
            'button_text'       => 'Vybrať A2 + STS',
            'button_style'      => 'black',
        ),
        'testy-komplet' => array(
            'name'              => 'Kompletná príprava.',
            'price'             => '99',
            'menu_order'        => 50,
            'layout'            => 'complete',
            'badge'             => 'VŠETKY PRÍPRAVY',
            'badge_style'       => 'green',
            'description'       => 'Všetky databázy. Viac času. Jeden prístup, ku ktorému sa môžete vracať.',
            'stats'             => array(),
            'features_title'    => '',
            'features'          => array(
                '704 otázok naprieč všetkými prípravami',
                'Online testy a prehľad vašich výsledkov',
            ),
            'includes'          => array( 'A1/A3', 'A2', 'STS' ),
            'price_label'       => '',
            'price_note'        => 'Jednorazový prístup',
            'access_title'      => '3 mesiace',
            'access_subtitle'   => 'na vašu prípravu',
            'button_text'       => 'Vybrať komplet',
            'button_style'      => 'black',
        ),
    );
}

function aprop_ensure_testy_products_category() {
    $slug = aprop_testy_products_category_slug();
    $term = get_term_by( 'slug', $slug, 'product_cat' );
    if ( $term && ! is_wp_error( $term ) ) {
        return (int) $term->term_id;
    }

    $result = wp_insert_term(
        aprop_testy_products_category_name(),
        'product_cat',
        array(
            'slug'        => $slug,
            'description' => 'Produkty online príprav – zobrazujú sa len na stránke /testy/.',
        )
    );

    if ( is_wp_error( $result ) ) {
        return 0;
    }

    return (int) $result['term_id'];
}

function aprop_sync_testy_product_card_meta( $product_id, $definition ) {
    $meta = array(
        '_testy_layout'         => $definition['layout'],
        '_testy_badge'          => $definition['badge'],
        '_testy_badge_style'    => $definition['badge_style'],
        '_testy_description'    => $definition['description'],
        '_testy_stats'          => wp_json_encode( $definition['stats'], JSON_UNESCAPED_UNICODE ),
        '_testy_features_title' => $definition['features_title'],
        '_testy_features'       => wp_json_encode( $definition['features'], JSON_UNESCAPED_UNICODE ),
        '_testy_price_label'    => $definition['price_label'],
        '_testy_price_note'     => $definition['price_note'],
        '_testy_button_text'    => $definition['button_text'],
        '_testy_button_style'    => $definition['button_style'],
        '_testy_button_url'      => isset( $definition['button_url'] ) ? $definition['button_url'] : '',
        '_testy_access_label'    => isset( $definition['access_label'] ) ? $definition['access_label'] : '',
        '_testy_access_title'    => isset( $definition['access_title'] ) ? $definition['access_title'] : '',
        '_testy_access_subtitle' => isset( $definition['access_subtitle'] ) ? $definition['access_subtitle'] : '',
        '_testy_includes'        => wp_json_encode( isset( $definition['includes'] ) ? $definition['includes'] : array(), JSON_UNESCAPED_UNICODE ),
    );

    foreach ( $meta as $key => $value ) {
        update_post_meta( $product_id, $key, $value );
    }
}

function aprop_install_testy_products() {
    if ( ! class_exists( 'WC_Product_Simple' ) ) {
        return;
    }

    $term_id = aprop_ensure_testy_products_category();
    if ( ! $term_id ) {
        return;
    }

    foreach ( aprop_get_testy_product_definitions() as $sku => $definition ) {
        $existing_id = wc_get_product_id_by_sku( $sku );
        if ( $existing_id ) {
            $product = wc_get_product( $existing_id );
            if ( $product ) {
                $product->set_name( $definition['name'] );
                $product->set_regular_price( $definition['price'] );
                $product->set_price( $definition['price'] );
                $product->set_menu_order( $definition['menu_order'] );
                $product->set_catalog_visibility( 'hidden' );
                $product->save();
                aprop_sync_testy_product_card_meta( $existing_id, $definition );
                wp_set_object_terms( $existing_id, array( $term_id ), 'product_cat', false );
            }
            continue;
        }

        $product = new WC_Product_Simple();
        $product->set_name( $definition['name'] );
        $product->set_slug( $sku );
        $product->set_sku( $sku );
        $product->set_status( 'publish' );
        $product->set_catalog_visibility( 'hidden' );
        $product->set_regular_price( $definition['price'] );
        $product->set_price( $definition['price'] );
        $product->set_virtual( true );
        $product->set_sold_individually( true );
        $product->set_manage_stock( false );
        $product->set_stock_status( 'instock' );
        $product->set_menu_order( $definition['menu_order'] );
        $product->set_category_ids( array( $term_id ) );
        $product_id = $product->save();

        if ( $product_id ) {
            aprop_sync_testy_product_card_meta( $product_id, $definition );
        }
    }

    update_option( 'aprop_testy_products_installed', time(), false );
}

add_action( 'init', 'aprop_maybe_install_testy_products', 30 );

function aprop_maybe_install_testy_products() {
    if ( is_admin() && ! wp_doing_ajax() ) {
        // Allow admin pages to install too.
    }

    if ( get_option( 'aprop_testy_products_installed' ) && ! isset( $_GET['aprop_reinstall_testy_products'] ) ) {
        // Still ensure products exist (e.g. after DB restore).
        $missing = false;
        foreach ( array_keys( aprop_get_testy_product_definitions() ) as $sku ) {
            if ( ! wc_get_product_id_by_sku( $sku ) ) {
                $missing = true;
                break;
            }
        }
        if ( ! $missing ) {
            return;
        }
    }

    aprop_install_testy_products();
}

function aprop_get_testy_products() {
    $products = array();
    foreach ( aprop_get_testy_product_definitions() as $sku => $definition ) {
        $product_id = wc_get_product_id_by_sku( $sku );
        if ( ! $product_id ) {
            continue;
        }
        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            continue;
        }

        $stats    = json_decode( (string) get_post_meta( $product_id, '_testy_stats', true ), true );
        $features = json_decode( (string) get_post_meta( $product_id, '_testy_features', true ), true );
        $includes = json_decode( (string) get_post_meta( $product_id, '_testy_includes', true ), true );

        $products[] = array(
            'id'             => $product_id,
            'sku'            => $sku,
            'product'        => $product,
            'name'           => $product->get_name(),
            'price'          => $product->get_price(),
            'price_html'     => $product->get_price_html(),
            'add_to_cart_url'=> add_query_arg(
                'add-to-cart',
                $product_id,
                function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/kosik/' )
            ),
            'layout'         => get_post_meta( $product_id, '_testy_layout', true ) ?: $definition['layout'],
            'badge'          => get_post_meta( $product_id, '_testy_badge', true ) ?: $definition['badge'],
            'badge_style'    => get_post_meta( $product_id, '_testy_badge_style', true ) ?: $definition['badge_style'],
            'description'    => get_post_meta( $product_id, '_testy_description', true ) ?: $definition['description'],
            'stats'          => is_array( $stats ) ? $stats : $definition['stats'],
            'features_title' => get_post_meta( $product_id, '_testy_features_title', true ) ?: $definition['features_title'],
            'features'       => is_array( $features ) ? $features : $definition['features'],
            'price_label'    => get_post_meta( $product_id, '_testy_price_label', true ) ?: $definition['price_label'],
            'price_note'     => get_post_meta( $product_id, '_testy_price_note', true ) ?: $definition['price_note'],
            'button_text'    => get_post_meta( $product_id, '_testy_button_text', true ) ?: $definition['button_text'],
            'button_style'   => get_post_meta( $product_id, '_testy_button_style', true ) ?: $definition['button_style'],
            'button_url'     => get_post_meta( $product_id, '_testy_button_url', true ) ?: ( isset( $definition['button_url'] ) ? $definition['button_url'] : '' ),
            'access_label'    => get_post_meta( $product_id, '_testy_access_label', true ) ?: ( isset( $definition['access_label'] ) ? $definition['access_label'] : '' ),
            'access_title'    => get_post_meta( $product_id, '_testy_access_title', true ) ?: ( isset( $definition['access_title'] ) ? $definition['access_title'] : '' ),
            'access_subtitle' => get_post_meta( $product_id, '_testy_access_subtitle', true ) ?: ( isset( $definition['access_subtitle'] ) ? $definition['access_subtitle'] : '' ),
            'includes'        => is_array( $includes ) ? $includes : ( isset( $definition['includes'] ) ? $definition['includes'] : array() ),
            'is_free'         => '' === (string) $product->get_price() || '0' === (string) $product->get_price(),
        );
    }

    return $products;
}

/**
 * Exclude testy products from shop / courses / drones / custom product loops.
 */
function aprop_exclude_testy_products_tax_query_clause() {
    return array(
        'taxonomy' => 'product_cat',
        'field'    => 'slug',
        'terms'    => array( aprop_testy_products_category_slug() ),
        'operator' => 'NOT IN',
    );
}

add_action( 'pre_get_posts', 'aprop_exclude_testy_products_from_queries', 25 );

function aprop_exclude_testy_products_from_queries( $query ) {
    if ( is_admin() || ! $query instanceof WP_Query ) {
        return;
    }

    $post_type = $query->get( 'post_type' );
    $is_product = $post_type === 'product'
        || ( is_array( $post_type ) && in_array( 'product', $post_type, true ) )
        || $query->is_post_type_archive( 'product' )
        || $query->get( 'wc_query' ) === 'product_query';

    if ( ! $is_product && ! $query->is_search() ) {
        return;
    }

    // Allow explicit inclusion via query flag from our shortcode.
    if ( $query->get( 'aprop_include_testy_products' ) ) {
        return;
    }

    $tax_query   = (array) $query->get( 'tax_query' );
    $tax_query[] = aprop_exclude_testy_products_tax_query_clause();
    $query->set( 'tax_query', $tax_query );
}

add_filter( 'woocommerce_product_related_posts_query', 'aprop_exclude_testy_from_related_products' );

function aprop_exclude_testy_from_related_products( $query ) {
    $term = get_term_by( 'slug', aprop_testy_products_category_slug(), 'product_cat' );
    if ( ! $term || is_wp_error( $term ) ) {
        return $query;
    }

    $query['where'] .= ' AND p.ID NOT IN (
        SELECT object_id FROM ' . $GLOBALS['wpdb']->term_relationships . '
        WHERE term_taxonomy_id = ' . (int) $term->term_taxonomy_id . '
    )';

    return $query;
}

/**
 * Block product detail pages for testy products – redirect to /testy/.
 */
add_action( 'template_redirect', 'aprop_redirect_testy_product_detail' );

function aprop_redirect_testy_product_detail() {
    if ( ! is_singular( 'product' ) ) {
        return;
    }

    if ( ! aprop_is_testy_product( get_the_ID() ) ) {
        return;
    }

    $target = home_url( '/testy/' );
    wp_safe_redirect( $target, 301 );
    exit;
}

/**
 * If anything still AJAX-adds a testy product and fails (e.g. already in cart),
 * send the user to the cart instead of the product page → /testy/ loop.
 */
add_filter( 'woocommerce_cart_redirect_after_error', 'aprop_testy_cart_redirect_after_error', 10, 2 );

function aprop_testy_cart_redirect_after_error( $url, $product_id ) {
    if ( ! aprop_is_testy_product( $product_id ) ) {
        return $url;
    }

    return function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/kosik/' );
}

/**
 * Hide from XML sitemaps.
 */
add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', 'aprop_exclude_testy_products_from_sitemap' );
add_filter( 'rank_math/sitemap/exclude_post_ids', 'aprop_exclude_testy_products_from_sitemap' );

function aprop_exclude_testy_products_from_sitemap( $ids ) {
    $ids = (array) $ids;
    foreach ( aprop_get_testy_product_definitions() as $sku => $_def ) {
        $product_id = wc_get_product_id_by_sku( $sku );
        if ( $product_id ) {
            $ids[] = $product_id;
        }
    }
    return array_unique( array_map( 'intval', $ids ) );
}
