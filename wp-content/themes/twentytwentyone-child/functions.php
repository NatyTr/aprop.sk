<?php
function my_child_theme_enqueue_assets() {
    // Štýly
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style'));

    // Slick Slider CSS
    wp_enqueue_style('slick-css', get_stylesheet_directory_uri() . '/assets/slick/slick.css');
    wp_enqueue_style('slick-theme-css', get_stylesheet_directory_uri() . '/assets/slick/slick-theme.css');

    // JavaScript
    wp_enqueue_script('child-theme-js', get_stylesheet_directory_uri() . '/js/app.js', array('jquery'), filemtime(get_stylesheet_directory() . '/js/app.js'), true);

    // Slick Slider JS
    wp_enqueue_script('slick-js', get_stylesheet_directory_uri() . '/assets/slick/slick.min.js', array('jquery'), null, true);

}
add_action('wp_enqueue_scripts', 'my_child_theme_enqueue_assets');

wp_enqueue_style( 'custom-style', get_stylesheet_uri(), [], filemtime( get_stylesheet_directory() . '/style.css' ) );


add_filter('show_admin_bar', '__return_false');

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

add_action( 'acf/init', 'aprop_homepage_help_cards_acf_fields', 5 );

function aprop_homepage_help_cards_acf_fields() {
    if ( ! function_exists( 'acf_get_field_group' ) || ! function_exists( 'acf_update_field' ) ) {
        return;
    }

    if ( get_option( 'aprop_home_help_cards_fields_flat_migrated' ) ) {
        return;
    }

    $field_group = acf_get_field_group( 'group_6828be0395a04' );

    if ( empty( $field_group['ID'] ) ) {
        return;
    }

    $parent_id = (int) $field_group['ID'];
    $fields = array(
        array(
            'key' => 'field_aprop_home_help_cards_tab',
            'label' => 'Začnite tam, kde to potrebujete',
            'name' => '',
            'type' => 'tab',
            'placement' => 'top',
            'endpoint' => 0,
            'parent' => $parent_id,
            'menu_order' => 34,
        ),
        array(
            'key' => 'field_aprop_home_help_cards_label',
            'label' => 'Label sekcie',
            'name' => 'home_help_cards_label',
            'type' => 'text',
            'default_value' => 'S ČÍM POMÔŽEME',
            'parent' => $parent_id,
            'menu_order' => 35,
        ),
        array(
            'key' => 'field_aprop_home_help_cards_title',
            'label' => 'Nadpis sekcie',
            'name' => 'home_help_cards_title',
            'type' => 'text',
            'default_value' => 'Začnite tam, kde to potrebujete',
            'parent' => $parent_id,
            'menu_order' => 36,
        ),
    );

    $menu_order = 37;

    foreach ( range( 1, 4 ) as $card_number ) {
        $fields[] = array(
            'key' => 'field_aprop_home_help_card_' . $card_number . '_tab',
            'label' => 'Karta ' . sprintf( '%02d', $card_number ),
            'name' => '',
            'type' => 'message',
            'message' => '<strong>Karta ' . sprintf( '%02d', $card_number ) . '</strong>',
            'esc_html' => 0,
            'new_lines' => 'wpautop',
            'parent' => $parent_id,
            'menu_order' => $menu_order++,
        );

        $prefix = 'home_help_card_' . $card_number . '_';

        $fields[] = array(
            'key' => 'field_aprop_' . $prefix . 'index',
            'label' => 'Číslo',
            'name' => $prefix . 'index',
            'type' => 'text',
            'parent' => $parent_id,
            'menu_order' => $menu_order++,
            'wrapper' => array( 'width' => '20' ),
        );
        $fields[] = array(
            'key' => 'field_aprop_' . $prefix . 'category',
            'label' => 'Kategória',
            'name' => $prefix . 'category',
            'type' => 'text',
            'parent' => $parent_id,
            'menu_order' => $menu_order++,
            'wrapper' => array( 'width' => '30' ),
        );
        $fields[] = array(
            'key' => 'field_aprop_' . $prefix . 'title',
            'label' => 'Nadpis',
            'name' => $prefix . 'title',
            'type' => 'text',
            'parent' => $parent_id,
            'menu_order' => $menu_order++,
        );
        $fields[] = array(
            'key' => 'field_aprop_' . $prefix . 'description',
            'label' => 'Popis',
            'name' => $prefix . 'description',
            'type' => 'textarea',
            'rows' => 3,
            'parent' => $parent_id,
            'menu_order' => $menu_order++,
        );
        $fields[] = array(
            'key' => 'field_aprop_' . $prefix . 'button_text',
            'label' => 'Text tlačidla',
            'name' => $prefix . 'button_text',
            'type' => 'text',
            'parent' => $parent_id,
            'menu_order' => $menu_order++,
            'wrapper' => array( 'width' => '50' ),
        );
        $fields[] = array(
            'key' => 'field_aprop_' . $prefix . 'button_url',
            'label' => 'URL tlačidla',
            'name' => $prefix . 'button_url',
            'type' => 'url',
            'parent' => $parent_id,
            'menu_order' => $menu_order++,
            'wrapper' => array( 'width' => '50' ),
        );
        $fields[] = array(
            'key' => 'field_aprop_' . $prefix . 'image',
            'label' => 'Obrázok',
            'name' => $prefix . 'image',
            'type' => 'image',
            'return_format' => 'array',
            'preview_size' => 'medium',
            'library' => 'all',
            'parent' => $parent_id,
            'menu_order' => $menu_order++,
        );
    }

    foreach ( $fields as $field ) {
        acf_update_field( $field );
    }

    update_option( 'aprop_home_help_cards_fields_flat_migrated', 1, false );
}

add_action( 'acf/init', 'aprop_remove_homepage_help_cards_repeater_fields', 8 );

function aprop_remove_homepage_help_cards_repeater_fields() {
    if ( ! function_exists( 'acf_get_field' ) || ! function_exists( 'acf_delete_field' ) ) {
        return;
    }

    if ( get_option( 'aprop_home_help_cards_repeater_removed' ) ) {
        return;
    }

    $repeater = acf_get_field( 'field_aprop_home_help_cards_items' );

    if ( ! empty( $repeater ) ) {
        acf_delete_field( 'field_aprop_home_help_cards_items' );
    }

    update_option( 'aprop_home_help_cards_repeater_removed', 1, false );
}

add_action( 'acf/init', 'aprop_remove_homepage_help_cards_old_tab_fields', 9 );

function aprop_remove_homepage_help_cards_old_tab_fields() {
    return;
}

add_action( 'acf/init', 'aprop_repair_homepage_help_cards_single_tab_layout', 9 );

function aprop_repair_homepage_help_cards_single_tab_layout() {
    if ( ! function_exists( 'acf_get_field_group' ) || ! function_exists( 'acf_get_field' ) || ! function_exists( 'acf_update_field' ) || ! function_exists( 'acf_delete_field' ) ) {
        return;
    }

    if ( get_option( 'aprop_home_help_cards_single_tab_repaired' ) ) {
        return;
    }

    $field_group = acf_get_field_group( 'group_6828be0395a04' );

    if ( empty( $field_group['ID'] ) ) {
        return;
    }

    $parent_id = (int) $field_group['ID'];

    foreach ( range( 1, 4 ) as $card_number ) {
        $field_key = 'field_aprop_home_help_card_' . $card_number . '_tab';
        $field = acf_get_field( $field_key );

        if ( empty( $field ) ) {
            continue;
        }

        $field['label'] = 'Karta ' . sprintf( '%02d', $card_number );
        $field['name'] = '';
        $field['type'] = 'message';
        $field['message'] = '<strong>Karta ' . sprintf( '%02d', $card_number ) . '</strong>';
        $field['esc_html'] = 0;
        $field['new_lines'] = 'wpautop';
        $field['parent'] = $parent_id;

        acf_update_field( $field );
    }

    $repeater_keys = array(
        'field_aprop_home_help_cards_items',
        'field_aprop_home_help_cards_item_index',
        'field_aprop_home_help_cards_item_category',
        'field_aprop_home_help_cards_item_size',
        'field_aprop_home_help_cards_item_theme',
        'field_aprop_home_help_cards_item_title',
        'field_aprop_home_help_cards_item_description',
        'field_aprop_home_help_cards_item_button_text',
        'field_aprop_home_help_cards_item_button_url',
        'field_aprop_home_help_cards_item_image',
    );

    foreach ( $repeater_keys as $field_key ) {
        $field = acf_get_field( $field_key );

        if ( ! empty( $field ) ) {
            acf_delete_field( $field_key );
        }
    }

    global $wpdb;

    $duplicate_keys = array(
        'field_aprop_home_help_cards_tab',
        'field_aprop_home_help_cards_label',
        'field_aprop_home_help_cards_title',
    );

    foreach ( $duplicate_keys as $duplicate_key ) {
        $ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'acf-field' AND post_name = %s ORDER BY ID DESC",
                $duplicate_key
            )
        );

        if ( count( $ids ) < 2 ) {
            continue;
        }

        array_shift( $ids );

        foreach ( $ids as $post_id ) {
            wp_delete_post( (int) $post_id, true );
        }
    }

    update_option( 'aprop_home_help_cards_single_tab_repaired', 1, false );
}

add_action( 'acf/init', 'aprop_seed_homepage_help_cards_defaults', 20 );

function aprop_seed_homepage_help_cards_defaults() {
    if ( ! function_exists( 'get_field' ) || ! function_exists( 'update_field' ) || ! function_exists( 'aprop_get_homepage_id' ) || ! function_exists( 'aprop_get_homepage_help_cards_defaults' ) ) {
        return;
    }

    $page_id = aprop_get_homepage_id();

    if ( ! $page_id ) {
        return;
    }

    $defaults = aprop_get_homepage_help_cards_defaults();

    if ( empty( get_field( 'home_help_cards_label', $page_id ) ) ) {
        update_field( 'field_aprop_home_help_cards_label', $defaults['label'], $page_id );
    }

    if ( empty( get_field( 'home_help_cards_title', $page_id ) ) ) {
        update_field( 'field_aprop_home_help_cards_title', $defaults['title'], $page_id );
    }

    foreach ( $defaults['cards'] as $index => $card ) {
        $card_number = $index + 1;
        $prefix = 'home_help_card_' . $card_number . '_';

        $field_map = array(
            'index' => 'field_aprop_' . $prefix . 'index',
            'category' => 'field_aprop_' . $prefix . 'category',
            'title' => 'field_aprop_' . $prefix . 'title',
            'description' => 'field_aprop_' . $prefix . 'description',
            'button_text' => 'field_aprop_' . $prefix . 'button_text',
            'button_url' => 'field_aprop_' . $prefix . 'button_url',
            'image' => 'field_aprop_' . $prefix . 'image',
        );

        foreach ( $field_map as $key => $field_key ) {
            $current = get_field( $prefix . $key, $page_id );

            if ( ! empty( $current ) ) {
                continue;
            }

            if ( 'image' === $key ) {
                $attachment_id = attachment_url_to_postid( $card['image_url'] );

                if ( $attachment_id ) {
                    update_field( $field_key, $attachment_id, $page_id );
                }

                continue;
            }

            update_field( $field_key, $card[ $key ], $page_id );
        }
    }
}

add_action( 'acf/init', 'aprop_install_homepage_secondary_hero_acf_fields', 6 );

function aprop_install_homepage_secondary_hero_acf_fields() {
    static $has_run = false;

    if ( $has_run ) {
        return;
    }

    $has_run = true;

    if ( ! function_exists( 'acf_get_field_group' ) || ! function_exists( 'acf_get_field' ) || ! function_exists( 'acf_update_field' ) ) {
        return;
    }

    if ( get_option( 'aprop_home_secondary_hero_fields_installed' ) ) {
        return;
    }

    $field_group = acf_get_field_group( 'group_6828be0395a04' );

    if ( empty( $field_group['ID'] ) ) {
        return;
    }

    $parent_id = (int) $field_group['ID'];

    global $wpdb;

    $existing_field_id = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts}
            WHERE post_type = 'acf-field'
              AND post_parent = %d
              AND post_name = %s
            ORDER BY ID DESC
            LIMIT 1",
            $parent_id,
            'field_aprop_home_secondary_hero_tab'
        )
    );

    if ( $existing_field_id ) {
        update_option( 'aprop_home_secondary_hero_fields_installed', 1, false );
        return;
    }

    update_option( 'aprop_home_secondary_hero_fields_installed', 1, false );

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->posts}
            SET menu_order = menu_order + %d
            WHERE post_parent = %d
              AND post_type = 'acf-field'
              AND menu_order >= %d",
            13,
            $parent_id,
            6
        )
    );

    $fields = array(
        array(
            'key' => 'field_aprop_home_secondary_hero_tab',
            'label' => 'Title banner - nový',
            'name' => '',
            'type' => 'tab',
            'placement' => 'top',
            'endpoint' => 0,
            'parent' => $parent_id,
            'menu_order' => 6,
        ),
        array(
            'key' => 'field_aprop_home_secondary_hero_banner_image',
            'label' => 'Hlavný obrázok',
            'name' => 'secondary_hero_banner_image',
            'type' => 'image',
            'return_format' => 'array',
            'preview_size' => 'medium',
            'library' => 'all',
            'parent' => $parent_id,
            'menu_order' => 7,
        ),
        array(
            'key' => 'field_aprop_home_secondary_hero_eyebrow',
            'label' => 'Eyebrow',
            'name' => 'secondary_hero_eyebrow',
            'type' => 'text',
            'parent' => $parent_id,
            'menu_order' => 8,
        ),
        array(
            'key' => 'field_aprop_home_secondary_hero_title_line_1',
            'label' => 'Nadpis - 1. riadok',
            'name' => 'secondary_hero_title_line_1',
            'type' => 'text',
            'parent' => $parent_id,
            'menu_order' => 9,
        ),
        array(
            'key' => 'field_aprop_home_secondary_hero_title_line_2',
            'label' => 'Nadpis - 2. riadok',
            'name' => 'secondary_hero_title_line_2',
            'type' => 'text',
            'parent' => $parent_id,
            'menu_order' => 10,
        ),
        array(
            'key' => 'field_aprop_home_secondary_hero_description',
            'label' => 'Popis',
            'name' => 'secondary_hero_description',
            'type' => 'textarea',
            'rows' => 4,
            'parent' => $parent_id,
            'menu_order' => 11,
        ),
        array(
            'key' => 'field_aprop_home_secondary_hero_primary_button_text',
            'label' => 'Primárne tlačidlo - text',
            'name' => 'secondary_hero_primary_button_text',
            'type' => 'text',
            'parent' => $parent_id,
            'menu_order' => 12,
            'wrapper' => array( 'width' => '50' ),
        ),
        array(
            'key' => 'field_aprop_home_secondary_hero_primary_button_url',
            'label' => 'Primárne tlačidlo - URL',
            'name' => 'secondary_hero_primary_button_url',
            'type' => 'url',
            'parent' => $parent_id,
            'menu_order' => 13,
            'wrapper' => array( 'width' => '50' ),
        ),

        array(
            'key' => 'field_aprop_home_secondary_hero_secondary_button_text',
            'label' => 'Sekundárne tlačidlo - text',
            'name' => 'secondary_hero_secondary_button_text',
            'type' => 'text',
            'parent' => $parent_id,
            'menu_order' => 14,
            'wrapper' => array( 'width' => '50' ),
        ),
        array(
            'key' => 'field_aprop_home_secondary_hero_secondary_button_url',
            'label' => 'Sekundárne tlačidlo - URL',
            'name' => 'secondary_hero_secondary_button_url',
            'type' => 'url',
            'parent' => $parent_id,
            'menu_order' => 15,
            'wrapper' => array( 'width' => '50' ),
        ),
        array(
            'key' => 'field_aprop_home_secondary_hero_product_label',
            'label' => 'Karta produktu - label',
            'name' => 'secondary_hero_product_label',
            'type' => 'text',
            'parent' => $parent_id,
            'menu_order' => 16,
        ),
        array(
            'key' => 'field_aprop_home_secondary_hero_product_post',
            'label' => 'Karta produktu - produkt',
            'name' => 'secondary_hero_product_post',
            'type' => 'post_object',
            'post_type' => array( 'product' ),
            'return_format' => 'object',
            'ui' => 1,
            'parent' => $parent_id,
            'menu_order' => 17,
        ),
        array(
            'key' => 'field_aprop_home_secondary_hero_product_button_text',
            'label' => 'Karta produktu - text tlačidla',
            'name' => 'secondary_hero_product_button_text',
            'type' => 'text',
            'parent' => $parent_id,
            'menu_order' => 18,
        ),
    );

    foreach ( $fields as $field ) {
        acf_update_field( $field );
    }

    update_option( 'aprop_home_secondary_hero_fields_installed', 1, false );
}
