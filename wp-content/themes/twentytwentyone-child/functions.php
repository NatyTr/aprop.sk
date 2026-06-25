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
require_once get_stylesheet_directory() . '/inc/product-card-migration.php';


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

function aprop_odstupenie_form_shortcode( $atts ) {
    $atts = shortcode_atts(
        array(
            'form_id' => '',
            'form_title' => '',
            'heading' => 'Odstúpenie od zmluvy',
        ),
        $atts,
        'odstupenie_form'
    );

    if ( empty( $atts['form_id'] ) ) {
        return '';
    }

    $form_shortcode = sprintf(
        '[contact-form-7 id="%s" title="%s"]',
        esc_attr( $atts['form_id'] ),
        esc_attr( $atts['form_title'] )
    );

    ob_start();
    ?>
    <div class="kontakt-section kontakt-section--odstupenie odstupenie-form-wrap">
        <h2 class="odstupenie-form-heading"><?php echo esc_html( $atts['heading'] ); ?></h2>
        <p class="odstupenie-form-intro">
            Vyplnením a odoslaním formulára uplatňujete právo na odstúpenie od zmluvy uzavretej na diaľku.
        </p>

        <div class="kontakt-form odstupenie-form-box">
            <?php echo do_shortcode( $form_shortcode ); ?>
        </div>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode( 'odstupenie_form', 'aprop_odstupenie_form_shortcode' );

function aprop_odstupenie_form_styles() {
    ?>
    <style>
        .kontakt-section--odstupenie {
            max-width: 576px;
            width: 100%;
            margin: 0 auto;
            padding-top: 0;
        }

        .kontakt-section--odstupenie .odstupenie-form-heading {
            text-align: center;
            margin-bottom: 20px;
        }

        .kontakt-section--odstupenie .odstupenie-form-intro {
            text-align: center;
            max-width: none;
            margin-bottom: 24px;
            padding: 0;
            font-size: clamp(16px, 1.041vw, 20px);
            line-height: clamp(24px, 1.562vw, 30px);
        }

        .kontakt-section--odstupenie .odstupenie-form-box .cf7-form-row {
            margin-bottom: 16px;
        }

        .kontakt-section--odstupenie .odstupenie-form-box .cf7-form-row.two-cols {
            display: block;
        }

        .kontakt-section--odstupenie form p {
            text-align: left;
            padding-bottom: 0;
            margin-bottom: 0;
        }

        .kontakt-section--odstupenie form p label {
            display: block;
            font-size: 18px;
            line-height: 20px;
            font-weight: 500;
            letter-spacing: -0.02em;
            color: #000;
            text-align: left;
        }

        .kontakt-section--odstupenie .odstupenie-form-box input:not([type="submit"]):not([type="checkbox"]),
        .kontakt-section--odstupenie .odstupenie-form-box textarea {
            width: 100%;
            margin-top: 10px;
            background: #f3f3f3;
            border: 1px solid #f3f3f3;
            border-radius: 16px;
            color: #000;
            padding: 18px;
            font-size: 16px;
            line-height: 17px;
            box-shadow: none;
            outline: none;
            -webkit-appearance: none;
            appearance: none;
        }

        .kontakt-section--odstupenie .odstupenie-form-box .cf7-form-field + .cf7-form-field {
            margin-top: 16px;
        }

        .kontakt-section--odstupenie .odstupenie-form-box .wpcf7-form-control-wrap {
            display: block;
        }

        .kontakt-section--odstupenie .odstupenie-form-box .cf7-form-field p > br {
            display: none;
        }

        .kontakt-section--odstupenie .odstupenie-form-box input:not([type="submit"]):not([type="checkbox"])::placeholder,
        .kontakt-section--odstupenie .odstupenie-form-box textarea::placeholder {
            color: rgba(0, 0, 0, 0.5);
            opacity: 1;
        }

        .kontakt-section--odstupenie .odstupenie-form-box input:not([type="submit"]):not([type="checkbox"]):focus,
        .kontakt-section--odstupenie .odstupenie-form-box textarea:focus {
            border-color: rgba(0, 0, 0, 0.5);
        }

        .kontakt-section--odstupenie .odstupenie-form-box textarea {
            min-height: 208px;
            resize: vertical;
        }

        .kontakt-section--odstupenie .odstupenie-form-box .wpcf7-acceptance .wpcf7-list-item {
            margin: 0;
        }

        .kontakt-section--odstupenie .odstupenie-form-box .wpcf7-acceptance label {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            width: 100%;
            text-align: left;
            font-size: 16px;
            line-height: 1.35;
            font-weight: 400;
            color: rgba(0, 0, 0, 0.5);
            letter-spacing: 0;
        }

        .kontakt-section--odstupenie .odstupenie-form-box .wpcf7-acceptance input {
            width: 20px !important;
            height: 20px !important;
            min-width: 20px;
            min-height: 20px !important;
            margin: 5px 0 0 0 !important;
            border: 1px solid #000;
            border-radius: 3px;
            background: #fff;
            flex-shrink: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .kontakt-section--odstupenie .odstupenie-form-box .wpcf7-acceptance .wpcf7-list-item-label {
            display: block;
            flex: 1;
        }

        .kontakt-section--odstupenie .odstupenie-form-box .wpcf7-list-item-label a:hover {
            text-decoration: underline;
        }

        .kontakt-section--odstupenie .odstupenie-form-box .wpcf7-submit {
            width: 100%;
            margin-top: 6px;
            border-radius: 1000px;
            font-size: 18px;
            line-height: normal;
            font-weight: 600;
            border: 1px solid #000;
            background: #000;
            color: #fff;
            padding: 16px 24px;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .kontakt-section--odstupenie .odstupenie-form-box .wpcf7-submit:hover {
            background: transparent;
            color: #000;
        }

        .kontakt-section--odstupenie .odstupenie-form-box .wpcf7-spinner {
            margin: 14px 0 0;
        }

        .kontakt-section--odstupenie .odstupenie-form-box .wpcf7-response-output {
            margin: 20px 0 0 !important;
            padding: 14px 16px !important;
            border-radius: 16px;
            font-size: 15px;
            line-height: 1.5;
        }

        .kontakt-section--odstupenie .odstupenie-form-box .wpcf7-not-valid-tip {
            margin-top: 8px;
            font-size: 14px;
            line-height: 1.4;
        }

        @media (max-width: 767px) {
            .kontakt-section--odstupenie {
                padding-top: 0;
            }

            .kontakt-section--odstupenie .odstupenie-form-heading {
                margin-bottom: 12px;
            }

            .kontakt-section--odstupenie .odstupenie-form-intro {
                margin-bottom: 20px;
                font-size: 14px;
                line-height: 20px;
            }

            .kontakt-section--odstupenie .odstupenie-form-box .cf7-form-row.two-cols {
                display: block;
            }

            .kontakt-section--odstupenie form p label {
                font-size: 14px;
                line-height: 15px;
            }

            .kontakt-section--odstupenie .odstupenie-form-box input:not([type="submit"]):not([type="checkbox"]),
            .kontakt-section--odstupenie .odstupenie-form-box textarea {
                border-radius: 8px;
                padding: 11px 9px;
                font-size: 12px;
                line-height: 13px;
            }

            .kontakt-section--odstupenie .odstupenie-form-box .cf7-form-field + .cf7-form-field {
                margin-top: 12px;
            }

            .kontakt-section--odstupenie .odstupenie-form-box .wpcf7-submit {
                font-size: 15px;
                line-height: 1.2;
            }

            .kontakt-section--odstupenie .odstupenie-form-box .wpcf7-acceptance label {
                font-size: 14px;
                line-height: 18px;
            }
        }
    </style>
    <?php
}
add_action( 'wp_head', 'aprop_odstupenie_form_styles' );

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

function aprop_render_drone_product_card( $product_id, $args = array() ) {
    $product_id = (int) $product_id;
    $product = wc_get_product( $product_id );

    if ( ! $product || ! $product->is_visible() ) {
        return '';
    }

    $args = wp_parse_args(
        $args,
        array(
            'title_tag' => 'h2',
            'article_class' => 'drone-product-card',
            'image_size' => 'large',
            'image_class' => 'drone-product-card__image drone-product-card__image--main',
            'article_attributes' => array(),
        )
    );

    $allowed_title_tags = array( 'h2', 'h3', 'h4' );
    $title_tag = in_array( $args['title_tag'], $allowed_title_tags, true ) ? $args['title_tag'] : 'h2';
    $article_class = trim( (string) $args['article_class'] );
    $badge = get_post_meta( $product_id, 'aprop_card_badge', true );
    $card_specifications = aprop_get_product_card_specifications( $product_id, 3 );
    $hover_image_url = aprop_get_product_hover_image_url( $product_id );
    $card_style = '';

    if ( $hover_image_url ) {
        $article_class .= ' drone-product-card--has-hover-media';
        $card_style = '--drone-hover-image: url(' . esc_url( $hover_image_url ) . ');';
    }

    $article_attributes = array(
        'class' => trim( $article_class ),
        'data-product_id' => (string) $product_id,
        'data-product_name' => $product->get_name(),
        'data-product_price' => (string) $product->get_price(),
    );

    if ( $card_style !== '' ) {
        $article_attributes['style'] = $card_style;
    }

    if ( ! empty( $args['article_attributes'] ) && is_array( $args['article_attributes'] ) ) {
        foreach ( $args['article_attributes'] as $attribute_name => $attribute_value ) {
            if ( $attribute_value === null || $attribute_value === '' ) {
                continue;
            }

            $article_attributes[ $attribute_name ] = (string) $attribute_value;
        }
    }

    $attributes_html = '';

    foreach ( $article_attributes as $attribute_name => $attribute_value ) {
        $attributes_html .= sprintf(
            ' %1$s="%2$s"',
            esc_attr( $attribute_name ),
            esc_attr( $attribute_value )
        );
    }

    ob_start();
    ?>
    <article<?php echo $attributes_html; ?>>
        <a class="drone-product-card__link" href="<?php echo esc_url( get_permalink( $product_id ) ); ?>" aria-label="<?php echo esc_attr( $product->get_name() ); ?>">
            <?php if ( $badge ) : ?>
                <span class="drone-product-card__badge"><?php echo esc_html( $badge ); ?></span>
            <?php endif; ?>

            <div class="drone-product-card__media">
                <?php if ( $product->get_image_id() ) : ?>
                    <?php echo wp_get_attachment_image( $product->get_image_id(), $args['image_size'], false, array( 'class' => $args['image_class'] ) ); ?>
                <?php endif; ?>
            </div>

            <div class="drone-product-card__content">
                <div class="drone-product-card__title-price<?php echo ! empty( $card_specifications ) ? ' drone-product-card__title-price--has-specs' : ''; ?>">
                    <<?php echo esc_html( $title_tag ); ?>><?php echo esc_html( $product->get_name() ); ?></<?php echo esc_html( $title_tag ); ?>>
                    <span class="drone-product-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
                </div>

                <?php if ( ! empty( $card_specifications ) ) : ?>
                    <div class="drone-product-card__specs">
                        <?php foreach ( $card_specifications as $specification ) : ?>
                            <div class="drone-product-card__spec">
                                <span class="drone-product-card__spec-label"><?php echo esc_html( $specification['name'] ); ?></span>
                                <span class="drone-product-card__spec-value"><?php echo esc_html( $specification['value'] ); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </a>
    </article>
    <?php

    return ob_get_clean();
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

function aprop_is_sluzby_shop_page() {
    if ( function_exists( 'is_shop' ) && is_shop() ) {
        return true;
    }

    $shop_page_id = (int) get_option( 'woocommerce_shop_page_id' );

    return $shop_page_id > 0 && is_page( $shop_page_id );
}

function aprop_sluzby_excluded_product_category_ids() {
    return array( 211 );
}

//odfiltruje balíčky a samostatné dronové produkty z hlavného produktového listingu
function exclude_package_category_from_shop( $query ) {
	if ( ! is_admin() && $query->is_main_query() && aprop_is_sluzby_shop_page() ) {
		$tax_query = $query->get('tax_query');

		$tax_query[] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => array( 'package', 'polnohospodarske-drony', 'drony', 'dji-drony', 'drony-new' ),
			'operator' => 'NOT IN',
		);

        $tax_query[] = array(
            'taxonomy'         => 'product_cat',
            'field'            => 'term_id',
            'terms'            => aprop_sluzby_excluded_product_category_ids(),
            'operator'         => 'NOT IN',
            'include_children' => true,
        );

		$query->set( 'tax_query', $tax_query );
	}
}
add_action( 'pre_get_posts', 'exclude_package_category_from_shop' );

function aprop_exclude_drone_products_from_sluzby_page( $query ) {
    if ( is_admin() || ! $query instanceof WP_Query ) {
        return;
    }

    if ( ! aprop_is_sluzby_shop_page() ) {
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

    $drone_tax_query['field'] = 'term_id';
    $drone_tax_query['terms'] = aprop_sluzby_excluded_product_category_ids();

    $tax_query[] = $drone_tax_query;
    $query->set( 'tax_query', $tax_query );
}
add_action( 'pre_get_posts', 'aprop_exclude_drone_products_from_sluzby_page' );


// zmena textu tlačidla pridať do košíka
add_filter( 'woocommerce_product_single_add_to_cart_text', 'custom_single_product_button_text' );

function custom_single_product_button_text() {
    return 'Pridať do košíka'; // <-- Tvoj vlastný text
}

add_filter(
    'woocommerce_get_price_html',
    function( $price_html, $product ) {
        if ( ! $product instanceof WC_Product || ! is_singular( 'product' ) ) {
            return $price_html;
        }

        if ( $price_html === '' ) {
            return $price_html;
        }

        return $price_html . '<span class="aprop-price-tax-note">Vrátane DPH</span>';
    },
    20,
    2
);

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

    $grouped_rows = array();

    foreach ( $rows as $row ) {
        $section = ! empty( $row['section'] ) ? (string) $row['section'] : __( 'Technické parametre', 'aprop' );
        $grouped_rows[ $section ][] = $row;
    }
    ?>
    <section class="aprop-product-specifications" aria-labelledby="aprop-product-specifications-title">
        <h2 id="aprop-product-specifications-title"><?php echo esc_html__( 'Technické parametre', 'aprop' ); ?></h2>
        <div class="aprop-product-specifications__groups">
            <?php $section_index = 0; ?>
            <?php foreach ( $grouped_rows as $section => $section_rows ) : ?>
                <details class="aprop-product-specifications__group" <?php echo $section_index === 0 ? 'open' : ''; ?>>
                    <summary>
                        <span><?php echo esc_html( $section ); ?></span>
                    </summary>
                    <dl class="aprop-product-specifications__list">
                        <?php foreach ( $section_rows as $row ) : ?>
                            <div class="aprop-product-specifications__row">
                                <dt><?php echo esc_html( $row['name'] ); ?></dt>
                                <dd><?php echo esc_html( $row['value'] ); ?></dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>
                </details>
                <?php $section_index++; ?>
            <?php endforeach; ?>
        </div>
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

add_filter( 'wpcf7_mail_components', 'aprop_add_submission_time_to_cf7_mail', 10, 3 );
function aprop_add_submission_time_to_cf7_mail( $components, $contact_form, $mail ) {
    if ( ! $contact_form instanceof WPCF7_ContactForm || ! $mail instanceof WPCF7_Mail ) {
        return $components;
    }

    if ( 'formulár - odstúpenie od zmluvy' !== $contact_form->title() ) {
        return $components;
    }

    $template_name = $mail->name();
    $template = $contact_form->prop( $template_name );
    $template_body = isset( $template['body'] ) ? (string) $template['body'] : '';

    if (
        false !== strpos( $template_body, '[_date]' ) ||
        false !== strpos( $template_body, '[_time]' ) ||
        false !== strpos( $template_body, 'Čas odoslania:' )
    ) {
        return $components;
    }

    $submission = WPCF7_Submission::get_instance();

    if ( ! $submission ) {
        return $components;
    }

    $timestamp = $submission->get_meta( 'timestamp' );

    if ( ! $timestamp ) {
        return $components;
    }

    $submitted_date = wp_date( get_option( 'date_format' ), $timestamp );
    $submitted_time = wp_date( get_option( 'time_format' ), $timestamp );

    $components['body'] .= sprintf(
        "\n\nDátum odoslania: %s\nČas odoslania: %s",
        $submitted_date,
        $submitted_time
    );

    return $components;
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
