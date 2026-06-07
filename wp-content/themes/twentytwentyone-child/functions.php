<?php
function my_child_theme_enqueue_assets() {
    // Štýly
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style(
        'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('parent-style'),
        filemtime(get_stylesheet_directory() . '/style.css')
    );

    // Slick Slider CSS
    wp_enqueue_style('slick-css', get_stylesheet_directory_uri() . '/assets/slick/slick.css');
    wp_enqueue_style('slick-theme-css', get_stylesheet_directory_uri() . '/assets/slick/slick-theme.css');

    // JavaScript
    wp_enqueue_script('child-theme-js', get_stylesheet_directory_uri() . '/js/app.js', array('jquery'), filemtime(get_stylesheet_directory() . '/js/app.js'), true);

    // Slick Slider JS
    wp_enqueue_script('slick-js', get_stylesheet_directory_uri() . '/assets/slick/slick.min.js', array('jquery'), null, true);

}
add_action('wp_enqueue_scripts', 'my_child_theme_enqueue_assets');


//add_filter('show_admin_bar', '__return_false');

require_once get_stylesheet_directory() . '/inc/shortcodes.php';
require_once get_stylesheet_directory() . '/inc/drones.php';


function my_child_theme_setup() {
    add_theme_support('post-thumbnails');
}
add_action('after_setup_theme', 'my_child_theme_setup');

require_once get_stylesheet_directory() . '/inc/options.php';


function force_featured_image_metabox() {
    add_meta_box(
        'postimagediv',
        __('Ilustračný obrázok'),
        'post_thumbnail_meta_box',
        'sluzby', // tu názov tvojho CPT
        'side',
        'low'
    );
}
add_action('do_meta_boxes', 'force_featured_image_metabox');


//menus
function register_my_menus() {
    register_nav_menus( array(
        'primary'        => 'Primary menu',
        'footer_main'    => 'Footer menu',
        'footer_socials' => 'Footer - Sociálne siete',
        // ďalšie menu si môžeš ľahko pridať sem
    ) );
}
add_action( 'after_setup_theme', 'register_my_menus' );




//custom post types Piliere
function register_custom_post_type_piliere() {
    $labels = array(
        'name'               => 'Piliere',
        'singular_name'      => 'Pilier',
        'menu_name'          => 'Piliere',
        'name_admin_bar'     => 'Pilier',
        'add_new'            => 'Pridať nový',
        'add_new_item'       => 'Pridať nový pilier',
        'new_item'           => 'Nový pilier',
        'edit_item'          => 'Upraviť pilier',
        'view_item'          => 'Zobraziť pilier',
        'all_items'          => 'Všetky piliere',
        'search_items'       => 'Hľadať piliere',
        'not_found'          => 'Žiadne piliere nenájdené.',
        'not_found_in_trash' => 'V koši sa nenachádzajú žiadne piliere.',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'rewrite'            => array('slug' => 'piliere'),
        'show_in_rest'       => true, // Ak chceš podporu pre Gutenberg
        'menu_position'      => 20,
        'menu_icon'          => 'dashicons-shield-alt',
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
    );

    register_post_type('piliere', $args);
}
add_action('init', 'register_custom_post_type_piliere');

//custom post types Benefity
function register_benefity_cpt() {
    $labels = array(
        'name'                  => 'Benefity',
        'singular_name'         => 'Benefit',
        'menu_name'             => 'Benefity',
        'name_admin_bar'        => 'Benefit',
        'add_new'               => 'Pridať nový',
        'add_new_item'          => 'Pridať nový benefit',
        'new_item'              => 'Nový benefit',
        'edit_item'             => 'Upraviť benefit',
        'view_item'             => 'Zobraziť benefit',
        'all_items'             => 'Všetky benefity',
        'search_items'          => 'Hľadať benefity',
        'not_found'             => 'Nič sa nenašlo',
        'not_found_in_trash'    => 'V koši sa nenašli žiadne benefity',
    );

    $args = array(
        'labels'                => $labels,
        'public'                => true,
        'has_archive'          => true,
        'rewrite'              => array('slug' => 'benefity'),
        'show_in_rest'         => true, // ak chceš podporu pre Gutenberg
        'supports'             => array('title', 'editor', 'thumbnail', 'excerpt'),
        'menu_icon'            => 'dashicons-heart', // môžeš zmeniť na iný ikon
    );

    register_post_type('benefity', $args);
}
add_action('init', 'register_benefity_cpt');


//custom post types Partneri
function register_partners_post_type() {
    $labels = array(
        'name'               => 'Partneri',
        'singular_name'      => 'Partner',
        'menu_name'          => 'Partneri',
        'name_admin_bar'     => 'Partner',
        'add_new'            => 'Pridať nového',
        'add_new_item'       => 'Pridať nového partnera',
        'new_item'           => 'Nový partner',
        'edit_item'          => 'Upraviť partnera',
        'view_item'          => 'Zobraziť partnera',
        'all_items'          => 'Všetci partneri',
        'search_items'       => 'Hľadať partnera',
        'not_found'          => 'Žiadni partneri nenájdení',
        'not_found_in_trash' => 'V koši sa nenachádzajú žiadni partneri',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'rewrite'            => array('slug' => 'partners'),
        'supports'           => array('title', 'editor', 'thumbnail'),
        'menu_position'      => 20,
        'menu_icon'          => 'dashicons-groups',
        'show_in_rest'       => true, // podpora pre Gutenberg
    );

    register_post_type('partners', $args);
}
add_action('init', 'register_partners_post_type');


//custom post type Služby
function register_sluzby_post_type() {

    $labels = array(
        'name'               => 'Služby',
        'singular_name'      => 'Služba',
        'menu_name'          => 'Služby',
        'name_admin_bar'     => 'Služba',
        'add_new'            => 'Pridať novú',
        'add_new_item'       => 'Pridať novú službu',
        'new_item'           => 'Nová služba',
        'edit_item'          => 'Upraviť službu',
        'view_item'          => 'Zobraziť službu',
        'all_items'          => 'Všetky služby',
        'search_items'       => 'Hľadať služby',
        'not_found'          => 'Žiadne služby nenájdené',
        'not_found_in_trash' => 'V koši sa nenašli žiadne služby',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'show_in_menu'       => true,
        'menu_icon'          => 'dashicons-hammer', // Ikona pre admin menu
        'has_archive'        => false,
        'rewrite'            => array('slug' => 'sluzby-detail'),
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
        'show_in_rest'       => true, // Podpora pre Gutenberg
    );

    register_post_type('sluzby', $args);
}
add_action('init', 'register_sluzby_post_type');



//add excerpt to products
function enable_excerpt_for_products() {
    add_post_type_support( 'product', 'excerpt' );
}
add_action( 'init', 'enable_excerpt_for_products' );

function aprop_product_category_uses_hover_background( $term_id ) {
    $enabled = get_term_meta( $term_id, 'aprop_use_hover_background', true );

    if ( $enabled !== '' ) {
        return $enabled === '1';
    }

    $term = get_term( $term_id, 'product_cat' );

    return $term instanceof WP_Term && in_array( $term->slug, array( 'drony', 'dji-drony' ), true );
}

function aprop_product_has_hover_background_category( $product_id ) {
    $terms = get_the_terms( $product_id, 'product_cat' );

    if ( empty( $terms ) || is_wp_error( $terms ) ) {
        return false;
    }

    foreach ( $terms as $term ) {
        if ( ! $term instanceof WP_Term ) {
            continue;
        }

        if ( aprop_product_category_uses_hover_background( $term->term_id ) ) {
            return true;
        }

        $ancestor_ids = get_ancestors( $term->term_id, 'product_cat', 'taxonomy' );

        foreach ( $ancestor_ids as $ancestor_id ) {
            if ( aprop_product_category_uses_hover_background( (int) $ancestor_id ) ) {
                return true;
            }
        }
    }

    return false;
}

function aprop_get_product_hover_image_url( $product_id ) {
    if ( aprop_product_has_hover_background_category( $product_id ) ) {
        return get_stylesheet_directory_uri() . '/images/produkty-pozadie.png';
    }

    return '';
}

function aprop_render_product_category_hover_background_field( $term ) {
    $value = aprop_product_category_uses_hover_background( $term->term_id ) ? 1 : 0;
    ?>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="aprop_use_hover_background">Hover pozadie produktov</label>
        </th>
        <td>
            <label for="aprop_use_hover_background">
                <input
                    type="checkbox"
                    name="aprop_use_hover_background"
                    id="aprop_use_hover_background"
                    value="1"
                    <?php checked( $value, 1 ); ?>
                />
                Použiť fixné hover pozadie `produkty-pozadie.png`
            </label>
            <p class="description">Keď je zapnuté, produkty z tejto kategórie použijú na hover fixný obrázok namiesto prvého obrázka z galérie.</p>
        </td>
    </tr>
    <?php
}

function aprop_render_product_category_hover_background_add_field() {
    ?>
    <div class="form-field">
        <label for="aprop_use_hover_background">Hover pozadie produktov</label>
        <label for="aprop_use_hover_background">
            <input
                type="checkbox"
                name="aprop_use_hover_background"
                id="aprop_use_hover_background"
                value="1"
            />
            Použiť fixné hover pozadie `produkty-pozadie.png`
        </label>
        <p>Keď je zapnuté, produkty z tejto kategórie použijú na hover fixný obrázok namiesto prvého obrázka z galérie.</p>
    </div>
    <?php
}

add_action( 'product_cat_add_form_fields', 'aprop_render_product_category_hover_background_add_field', 5 );

add_action(
    'product_cat_edit_form_fields',
    function( $term ) {
        aprop_render_product_category_hover_background_field( $term );
    },
    5
);

add_action(
    'edited_product_cat',
    function( $term_id ) {
        update_term_meta( $term_id, 'aprop_use_hover_background', isset( $_POST['aprop_use_hover_background'] ) ? '1' : '0' );
    }
);

add_action(
    'created_product_cat',
    function( $term_id ) {
        update_term_meta( $term_id, 'aprop_use_hover_background', isset( $_POST['aprop_use_hover_background'] ) ? '1' : '0' );
    }
);

add_action(
    'init',
    function() {
        foreach ( array( 'drony', 'dji-drony' ) as $default_term_slug ) {
            $default_term = get_term_by( 'slug', $default_term_slug, 'product_cat' );

            if ( $default_term instanceof WP_Term ) {
                $saved_value = get_term_meta( $default_term->term_id, 'aprop_use_hover_background', true );

                if ( $saved_value === '' ) {
                    update_term_meta( $default_term->term_id, 'aprop_use_hover_background', '1' );
                }
            }
        }
    }
);


function add_page_slug_to_body_class($classes) {
    if (is_singular() || is_page()) {
        global $post;
        if ($post) {
            $classes[] = 'page-' . $post->post_name; // pridá napr. "page-kontakt"
        }
    }
    return $classes;
}
add_filter('body_class', 'add_page_slug_to_body_class');

//odfiltruje balíčky a samostatné dronové produkty z hlavného produktového listingu
function exclude_package_category_from_shop( $query ) {
	if ( ! is_admin() && $query->is_main_query() && is_post_type_archive('product') ) {
		$tax_query = $query->get('tax_query');

		$tax_query[] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => array( 'package', 'polnohospodarske-drony' ),
			'operator' => 'NOT IN',
		);

		$query->set( 'tax_query', $tax_query );
	}
}
add_action( 'pre_get_posts', 'exclude_package_category_from_shop' );

function aprop_exclude_drone_products_from_sluzby_page( $query ) {
    if ( is_admin() || ! $query instanceof WP_Query ) {
        return;
    }

    if ( ! is_page( 'sluzby' ) ) {
        return;
    }

    $post_type = $query->get( 'post_type' );
    $is_product_query = $post_type === 'product' || ( is_array( $post_type ) && in_array( 'product', $post_type, true ) );

    if ( ! $is_product_query ) {
        return;
    }

    $tax_query = (array) $query->get( 'tax_query' );
    $drone_tax_query = array(
        'taxonomy' => 'product_cat',
        'operator' => 'NOT IN',
        'include_children' => true,
    );

    if ( function_exists( 'aprop_drone_category_id' ) ) {
        $drone_tax_query['field'] = 'term_id';
        $drone_tax_query['terms'] = array( aprop_drone_category_id() );
    } else {
        $drone_tax_query['field'] = 'slug';
        $drone_tax_query['terms'] = array( 'polnohospodarske-drony' );
    }

    $tax_query[] = $drone_tax_query;
    $query->set( 'tax_query', $tax_query );
}
add_action( 'pre_get_posts', 'aprop_exclude_drone_products_from_sluzby_page' );


// zmena textu tlačidla pridať do košíka
add_filter( 'woocommerce_product_single_add_to_cart_text', 'custom_single_product_button_text' );

function custom_single_product_button_text() {
    return 'Pridať do košíka'; // <-- Tvoj vlastný text
}

// posunúť tlačidlo
function move_add_to_cart_button_above_excerpt() {
	// Odstráň z pôvodnej pozície
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );

	// Pridaj na pozíciu 15 – nad excerpt (excerpt je na 20)
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 15 );
}
add_action( 'woocommerce_before_single_product', 'move_add_to_cart_button_above_excerpt' );


function insert_faqs_below_excerpt() {
	if ( is_singular( 'product' ) ) {
		echo do_shortcode('[product_faqs]');
	}
}
add_action( 'woocommerce_single_product_summary', 'insert_faqs_below_excerpt', 21 );


function insert_note_and_slider_after_product() {
	if ( is_singular( 'product' ) ) {
		echo do_shortcode('[product_note]');
    echo do_shortcode('[kurz_instructions]');
		echo do_shortcode('[related_products_shortcode]');
    echo do_shortcode('[didnt_find_banner]');
	}
}
add_action( 'woocommerce_after_single_product_summary', 'insert_note_and_slider_after_product', 25 );


remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

function aprop_get_enterra_product_specifications( $product_id ) {
    $specifications_json = get_post_meta( $product_id, '_aprop_enterra_specifications_json', true );
    $specifications = json_decode( (string) $specifications_json, true );
    $rows = array();

    if ( is_array( $specifications ) && ! empty( $specifications['rows'] ) && is_array( $specifications['rows'] ) ) {
        $rows = $specifications['rows'];
    }

    if ( empty( $rows ) ) {
        $meta_keys_json = get_post_meta( $product_id, '_aprop_enterra_specification_meta_keys', true );
        $meta_keys = json_decode( (string) $meta_keys_json, true );

        if ( is_array( $meta_keys ) ) {
            foreach ( $meta_keys as $meta_key ) {
                if ( ! is_array( $meta_key ) || empty( $meta_key['key'] ) ) {
                    continue;
                }

                $rows[] = array(
                    'section' => isset( $meta_key['section'] ) ? $meta_key['section'] : '',
                    'name'    => isset( $meta_key['name'] ) ? $meta_key['name'] : '',
                    'value'   => get_post_meta( $product_id, $meta_key['key'], true ),
                );
            }
        }
    }

    return array_values(
        array_filter(
            $rows,
            function ( $row ) {
                return is_array( $row ) && ! empty( $row['name'] ) && ! empty( $row['value'] );
            }
        )
    );
}

function aprop_get_product_card_specifications( $product_id, $limit = 3 ) {
    $specifications = array();

    for ( $index = 1; $index <= $limit; $index++ ) {
        $label = trim( (string) get_post_meta( $product_id, 'aprop_card_spec_' . $index . '_label', true ) );
        $value = trim( (string) get_post_meta( $product_id, 'aprop_card_spec_' . $index . '_value', true ) );

        if ( $label === '' || $value === '' ) {
            continue;
        }

        $specifications[] = array(
            'name' => $label,
            'value' => $value,
        );
    }

    return $specifications;
}

function aprop_show_enterra_product_specifications() {
    if ( ! is_singular( 'product' ) ) {
        return;
    }

    $rows = aprop_get_enterra_product_specifications( get_the_ID() );
    if ( empty( $rows ) ) {
        return;
    }
    ?>
    <section class="aprop-product-specifications" aria-labelledby="aprop-product-specifications-title">
        <h2 id="aprop-product-specifications-title"><?php echo esc_html__( 'Technické parametre', 'aprop' ); ?></h2>
        <dl class="aprop-product-specifications__list">
            <?php foreach ( $rows as $row ) : ?>
                <div class="aprop-product-specifications__row">
                    <dt><?php echo esc_html( $row['name'] ); ?></dt>
                    <dd><?php echo esc_html( $row['value'] ); ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
    </section>
    <?php
}
add_action( 'woocommerce_after_single_product_summary', 'aprop_show_enterra_product_specifications', 12 );


add_filter( 'woocommerce_product_tabs', 'rename_description_tab', 98 );
function rename_description_tab( $tabs ) {
    if ( isset( $tabs['description'] ) ) {
        $tabs['description']['title'] = 'Čo môžete očakávať';
    }
    return $tabs;
}



//Termíny kurzov
function kurz_terminy_shortcode() {
	if ( ! is_singular( 'product' ) ) return '';

	// 👉 NOVÉ
	$show = get_field('show_terminy_kurzov');
	if ($show === 'no') return '';

	$group = get_field('terminy_kurzov');

	// DEBUG (môžeš potom vymazať)
	echo '<script>console.log("ACF termíny:", ' . json_encode($group) . ');</script>';

	if (empty($group)) return '';

	$today = strtotime('today');
	$output  = '<div class="course-dates-wrapper">';
	$output .= '<label for="kurz_termin" class="course-dates"><strong>Vyberte si termín kurzu:</strong></label>';
	$output .= '<select name="kurz_termin" id="kurz_termin" required>';

	$has_valid_option = false;

	foreach ($group as $value) {
		if (empty($value)) continue;

		// 👉 opravený regex (dôležité!)
		if (preg_match('/(\d{2})\.(\d{2})\.(\d{4}).*?(\d{2}):(\d{2})/', $value, $matches)) {

			$day = $matches[1];
			$month = $matches[2];
			$year = $matches[3];
			$hour = $matches[4];
			$minute = $matches[5];

			$datetime = DateTime::createFromFormat('d.m.Y H:i', "$day.$month.$year $hour:$minute");

			if ($datetime && $datetime->getTimestamp() >= $today) {
				$has_valid_option = true;
				$output .= '<option value="' . esc_attr($value) . '">' . esc_html($value) . '</option>';
			}

		} else {
			$has_valid_option = true;
			$output .= '<option value="' . esc_attr($value) . '">' . esc_html($value) . '</option>';
		}
	}

	$output .= '</select>';
	$output .= '</div>';

	if (!$has_valid_option) return '';

	return $output;
}



// === Zobraziť select vo formulári pred tlačidlom
add_action('woocommerce_before_add_to_cart_button', 'custom_show_course_dates', 9);
function custom_show_course_dates() {
	echo kurz_terminy_shortcode(); // voláme rovnakú funkciu ako shortcode
}

// === Uložiť do položky v košíku
add_filter('woocommerce_add_cart_item_data', function($cart_item_data, $product_id) {
	if (isset($_POST['kurz_termin'])) {
		$cart_item_data['kurz_termin'] = sanitize_text_field($_POST['kurz_termin']);
		$cart_item_data['unique_key'] = md5(microtime().rand());
	}
	return $cart_item_data;
}, 10, 2);

// === Zobraziť v košíku a pokladni
add_filter('woocommerce_get_item_data', function($cart_data, $cart_item) {
	if (isset($cart_item['kurz_termin'])) {
		$cart_data[] = array(
			'name'  => 'Zvolený termín',
			'value' => $cart_item['kurz_termin'],
		);
	}
	return $cart_data;
}, 10, 2);

// === Pridať termín do objednávky
add_action('woocommerce_checkout_create_order_line_item', function($item, $cart_item_key, $values, $order) {
	if (isset($values['kurz_termin'])) {
		$item->add_meta_data('Zvolený termín', $values['kurz_termin']);
	}
}, 10, 4);


function woocommerce_button_proceed_to_checkout() {
    $checkout_url = WC()->cart->get_checkout_url();
    ?>
    <a href="<?php echo esc_url( $checkout_url ); ?>" class="btn-primary btn-black btn-icon">
        <?php esc_html_e( 'Prejsť do pokladne', 'woocommerce' ); ?>
    </a>
    <?php
}

add_action( 'wp_footer', 'custom_continue_shopping_button_text' );
function custom_continue_shopping_button_text() {
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Vyberie tlačidlá s textom "Continue shopping" a prepíše ich
            document.querySelectorAll('a.button, button').forEach(function(el) {
                if (el.textContent.trim() === 'Continue shopping') {
                    el.textContent = 'Späť do obchodu'; // zmeň podľa potreby
                }
            });
        });
    </script>
    <?php
}

add_filter( 'woocommerce_order_button_html', 'custom_place_order_button_html' );
function custom_place_order_button_html( $button ) {
    return '<button type="submit" class="btn-primary btn-black btn-icon" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( __( 'Place order', 'woocommerce' ) ) . '" data-value="' . esc_attr( __( 'Place order', 'woocommerce' ) ) . '">' . esc_html__( 'Place order', 'woocommerce' ) . '</button>';
}

add_action( 'woocommerce_review_order_before_payment', 'custom_checkout_payment_heading' );
function custom_checkout_payment_heading() {
    echo '<h3 class="checkout-payment-title">Platba</h3>';
}

add_filter( 'woocommerce_get_availability_text', 'aprop_translate_product_availability_text', 10, 2 );
function aprop_translate_product_availability_text( $availability, $product ) {
    if ( ! $product instanceof WC_Product ) {
        return $availability;
    }

    if ( $product->is_on_backorder( 1 ) ) {
        return 'Na objednávku';
    }

    if ( $product->is_in_stock() ) {
        return 'Skladom';
    }

    return 'Nie je skladom';
}

add_filter( 'woocommerce_cart_item_backorder_notification', 'aprop_translate_cart_backorder_notification' );
function aprop_translate_cart_backorder_notification( $notification ) {
    return '<p class="backorder_notification">' . esc_html__( 'Na objednávku', 'aprop' ) . '</p>';
}


add_filter( 'gettext', 'custom_translate_checkout_strings', 20, 3 );
function custom_translate_checkout_strings( $translated_text, $text, $domain ) {

    switch ( $translated_text ) {
        case 'Billing details':
            $translated_text = 'Fakturačné údaje';
            break;
        case 'Your order':
            $translated_text = 'Vaša objednávka';
            break;
        case 'Additional information':
            $translated_text = 'Dodatočné informácie';
            break;
        case 'Have a coupon?':
            $translated_text = 'Máte zľavový kód?';
            break;
        case 'Click here to enter your code':
            $translated_text = 'Kliknite sem pre zadanie kódu';
            break;
        case 'If you have a coupon code, please apply it below.':
            $translated_text = 'Ak máte zľavový kód, zadajte ho nižšie.';
            break;
        case 'Coupon code':
            $translated_text = 'Zľavový kód';
            break;
        case 'Apply coupon':
            $translated_text = 'Použiť kupón';
            break;
        case 'Place order':
            $translated_text = 'Odoslať objednávku';
            break;
        case 'Please read and accept the terms and conditions to proceed with your order.':
            $translated_text = 'Prosím, prečítajte si a prijmite obchodné podmienky, aby ste mohli pokračovať v objednávke.';
            break;
        case 'is not a valid postcode / ZIP.':
            $translated_text = 'nie je platné PSČ.';
            break;
    }

    return $translated_text;
}


add_filter( 'gettext', 'custom_translate_order_texts', 20, 3 );
function custom_translate_order_texts( $translated_text, $text, $domain ) {
    switch ( $translated_text ) {

        case 'Thank you. Your order has been received.':
            $translated_text = 'Ďakujeme. Vaša objednávka bola prijatá.';
            break;

        case 'Order number:':
            $translated_text = 'Číslo objednávky:';
            break;

        case 'Date:':
            $translated_text = 'Dátum:';
            break;

        case 'Email:':
            $translated_text = 'E‑mail:';
            break;

        case 'Total:':
            $translated_text = 'Celkom:';
            break;

        case 'Payment method:':
            $translated_text = 'Spôsob platby:';
            break;

        case 'Our bank details':
            $translated_text = 'Údaje k platbe';
            break;

        case 'Order details':
            $translated_text = 'Detaily objednávky';
            break;

        case 'Product':
            $translated_text = 'Produkt';
            break;

        case 'Subtotal:':
            $translated_text = 'Medzisúčet:';
            break;

        case 'Total: %s (includes %s VAT)':
            $translated_text = 'Celkom: %s (vrátane %s DPH)';
            break;

        case 'Billing address':
            $translated_text = 'Fakturačná adresa';
            break;

        case 'Coupon code':
            $translated_text = 'Zľavový kód';
            break;

        case 'Have a coupon?':
            $translated_text = 'Máte zľavový kód?';
            break;

        case 'Click here to enter your code':
            $translated_text = 'Kliknite sem pre zadanie kódu';
            break;

        case 'If you have a coupon code, please apply it below.':
            $translated_text = 'Ak máte zľavový kód, zadajte ho nižšie.';
            break;

        case 'Please read and accept the terms and conditions to proceed with your order.':
            $translated_text = 'Pre pokračovanie v objednávke je potrebné súhlasiť s obchodnými podmienkami.';
            break;

        case 'is not a valid postcode / ZIP.':
            $translated_text = 'nie je platné PSČ.';
            break;

        case 'Your cart is currently empty.':
            $translated_text = 'Váš košík je prázdny';
            break;

        case 'Return tu shop':
            $translated_text = 'Vrátiť sa do obchodu';
            break;
    }

    return $translated_text;
}


//blog article filter
add_action( 'wp_ajax_filter_blog_posts', 'ajax_filter_blog_posts' );
add_action( 'wp_ajax_nopriv_filter_blog_posts', 'ajax_filter_blog_posts' );

function ajax_filter_blog_posts() {
  $category = sanitize_text_field( $_POST['category'] );

  $args = array(
    'post_type' => 'post',
    'posts_per_page' => -1,
  );

  if ( ! empty( $category ) ) {
    $args['category_name'] = $category;
  }

  $query = new WP_Query( $args );

  ob_start();

  if ( $query->have_posts() ) :
    echo '<div class="post-grid">';
    while ( $query->have_posts() ) : $query->the_post();
      ?>
      <article class="post-card">
        <a href="<?php the_permalink(); ?>" class="post-thumbnail">
          <?php if ( has_post_thumbnail() ) {
            the_post_thumbnail( 'large' );
          } ?>
        </a>

        <div class="post-meta">
          <span class="post-category"><?php echo esc_html( get_the_category()[0]->name ); ?></span>
          <span class="post-date"><?php echo get_the_date(); ?></span>
        </div>

        <h2 class="post-title">
          <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h2>

        <div class="post-excerpt">
          <?php the_excerpt(); ?>
        </div>
      </article>
      <?php
    endwhile;
    echo '</div>';
  else :
    echo '<p>Žiadne články neboli nájdené.</p>';
  endif;

  wp_reset_postdata();

  echo ob_get_clean();
  wp_die();
}


add_action( 'wp_enqueue_scripts', function() {
  wp_enqueue_script( 'jquery' ); // ak už nie je
  wp_localize_script( 'jquery', 'ajaxurl', admin_url( 'admin-ajax.php' ) );
});



add_filter( 'wpcf7_validate_tel*', 'custom_tel_validation', 20, 2 );
function custom_tel_validation( $result, $tag ) {
    $name = $tag->name;
    $value = isset( $_POST[$name] ) ? trim( $_POST[$name] ) : '';

    // validujeme iba tie konkrétne polia, ktoré chceme
    if ( in_array( $name, ['tel-726', 'phone'] ) ) {
      if ( ! preg_match( '/^(\+421\d{9}|0\d{9})$/', $value ) ) {
        $result->invalidate( $tag, "Prosím zadajte platné slovenské číslo (+421xxxxxxxxx alebo 09xxxxxxxx)." );
      }

    }

    return $result;
}

add_action( 'acf/include_fields', 'aprop_product_card_acf_fields' );

function aprop_product_card_acf_fields() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group(
        array(
            'key' => 'group_aprop_product_card',
            'title' => 'Produktová karta',
            'fields' => array(
                array(
                    'key' => 'field_aprop_card_badge',
                    'label' => 'Badge',
                    'name' => 'aprop_card_badge',
                    'type' => 'text',
                ),
                array(
                    'key' => 'field_aprop_card_spec_1_label',
                    'label' => 'Položka 1 - label',
                    'name' => 'aprop_card_spec_1_label',
                    'type' => 'text',
                ),
                array(
                    'key' => 'field_aprop_card_spec_1_value',
                    'label' => 'Položka 1 - hodnota',
                    'name' => 'aprop_card_spec_1_value',
                    'type' => 'text',
                ),
                array(
                    'key' => 'field_aprop_card_spec_2_label',
                    'label' => 'Položka 2 - label',
                    'name' => 'aprop_card_spec_2_label',
                    'type' => 'text',
                ),
                array(
                    'key' => 'field_aprop_card_spec_2_value',
                    'label' => 'Položka 2 - hodnota',
                    'name' => 'aprop_card_spec_2_value',
                    'type' => 'text',
                ),
                array(
                    'key' => 'field_aprop_card_spec_3_label',
                    'label' => 'Položka 3 - label',
                    'name' => 'aprop_card_spec_3_label',
                    'type' => 'text',
                ),
                array(
                    'key' => 'field_aprop_card_spec_3_value',
                    'label' => 'Položka 3 - hodnota',
                    'name' => 'aprop_card_spec_3_value',
                    'type' => 'text',
                ),
                array(
                    'key' => 'field_aprop_card_hover_image',
                    'label' => 'Hover obrázok',
                    'name' => 'aprop_card_hover_image',
                    'type' => 'image',
                    'return_format' => 'id',
                    'preview_size' => 'medium',
                    'library' => 'all',
                ),
                array(
                    'key' => 'field_aprop_drone_display',
                    'label' => 'Filter - zobrazenie',
                    'name' => 'aprop_drone_display',
                    'type' => 'select',
                    'choices' => array(
                        'drony' => 'Drony',
                        'prislusenstvo' => 'Príslušenstvo',
                    ),
                    'default_value' => 'drony',
                    'ui' => true,
                ),
                array(
                    'key' => 'field_aprop_drone_purpose',
                    'label' => 'Filter - účel použitia',
                    'name' => 'aprop_drone_purpose',
                    'type' => 'select',
                    'choices' => array(
                        'postrek' => 'Postrek',
                        'rozmetanie' => 'Rozmetanie',
                        'monitoring-a-snimanie' => 'Monitoring a snímanie',
                        'mapovanie' => 'Mapovanie',
                    ),
                    'allow_null' => true,
                    'ui' => true,
                ),
                array(
                    'key' => 'field_aprop_drone_capacity',
                    'label' => 'Filter - nosnosť nádrže',
                    'name' => 'aprop_drone_capacity',
                    'type' => 'number',
                    'append' => 'L',
                    'min' => 10,
                    'max' => 100,
                    'step' => 10,
                ),
                array(
                    'key' => 'field_aprop_drone_availability',
                    'label' => 'Filter - dostupnosť',
                    'name' => 'aprop_drone_availability',
                    'type' => 'select',
                    'choices' => array(
                        'skladom' => 'Skladom',
                        'na-objednavku' => 'Na objednávku',
                    ),
                    'allow_null' => true,
                    'ui' => true,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'product',
                    ),
                ),
            ),
            'menu_order' => 20,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'active' => true,
        )
    );
}

add_action( 'acf/include_fields', 'aprop_benefits_block_acf_fields' );

function aprop_benefits_block_acf_fields() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group(
        array(
            'key' => 'group_aprop_benefits_block',
            'title' => 'Benefits block',
            'fields' => array(
                array(
                    'key' => 'field_aprop_benefits_block_label',
                    'label' => 'Label sekcie',
                    'name' => 'benefits_block_label',
                    'type' => 'text',
                    'default_value' => 'Čo potrebujete vedieť?',
                ),
                array(
                    'key' => 'field_aprop_benefits_block_title',
                    'label' => 'Nadpis sekcie',
                    'name' => 'benefits_block_title',
                    'type' => 'text',
                    'default_value' => 'Ako to funguje?',
                ),
                array(
                    'key' => 'field_aprop_benefits_block_intro_text',
                    'label' => 'Text pri nadpise',
                    'name' => 'benefits_block_intro_text',
                    'type' => 'textarea',
                    'rows' => 4,
                    'new_lines' => 'br',
                    'instructions' => 'Krátky text vpravo od nadpisu sekcie.',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'page',
                    ),
                ),
            ),
            'menu_order' => 30,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'active' => true,
        )
    );
}
