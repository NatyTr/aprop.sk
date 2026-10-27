
<?php

function aprop_get_homepage_id() {
    $front_page_id = (int) get_option( 'page_on_front' );

    if ( $front_page_id > 0 ) {
        return $front_page_id;
    }

    $home_page = get_page_by_path( 'domov' );

    if ( $home_page instanceof WP_Post ) {
        return (int) $home_page->ID;
    }

    return 0;
}

function aprop_get_homepage_help_cards_defaults() {
    return array(
        'label' => 'S ČÍM POMÔŽEME',
        'title' => 'Začnite tam, kde to potrebujete',
        'cards' => array(
            array(
                'index' => '01',
                'category' => 'DRONY',
                'title' => 'Kúpiť dron',
                'description' => 'Profesionálne drony pre poľnohospodárstvo, monitoring a prácu v teréne.',
                'button_text' => 'Vybrať dron',
                'button_url' => home_url( '/drony/' ),
                'theme' => 'dark',
                'size' => 'large',
                'image_url' => content_url( '/uploads/2022/01/dron_dji_agras_t30_postrekovanie_pola_aprop_enterra.jpg' ),
            ),
            array(
                'index' => '02',
                'category' => 'KURZY',
                'title' => 'Vybrať kurz',
                'description' => 'Online aj v teréne. Od základov A1 po pokročilé STS certifikácie aj profi pilotov.',
                'button_text' => 'Pozrieť kurzy',
                'button_url' => home_url( '/sluzby/' ),
                'theme' => 'dark',
                'size' => 'large',
                'image_url' => content_url( '/uploads/2020/09/aprop-prakticke-skolenie-dron-fofoaparat-macbook-1-400x300.jpg' ),
            ),
            array(
                'index' => '03',
                'category' => 'LEGISLATÍVA',
                'title' => 'Vyriešiť legislatívu',
                'description' => '',
                'button_text' => '',
                'button_url' => home_url( '/clanok/legislativa-pravidla-lietanie-drony/' ),
                'theme' => 'light',
                'size' => 'small',
                'image_url' => content_url( '/uploads/2022/01/novelizacia-leteckeho-zakona-pravidla-drony-2024.png' ),
            ),
            array(
                'index' => '04',
                'category' => 'PRE FIRMY',
                'title' => 'Riešenie pre firmu',
                'description' => '',
                'button_text' => '',
                'button_url' => home_url( '/pre-firmy/' ),
                'theme' => 'light',
                'size' => 'small',
                'image_url' => content_url( '/uploads/2022/05/mapapobocky.jpg' ),
            ),
        ),
    );
}

function aprop_normalize_homepage_help_card_image( $image_url, $default_card ) {
    if ( empty( $image_url ) ) {
        return $default_card['image_url'];
    }

    $invalid_fragments = array(
        'ChatGPT-Image-21.-4.-2025-10_03_13',
    );

    foreach ( $invalid_fragments as $fragment ) {
        if ( false !== strpos( $image_url, $fragment ) ) {
            return $default_card['image_url'];
        }
    }

    return $image_url;
}

function aprop_get_homepage_help_cards_data() {
    $defaults = aprop_get_homepage_help_cards_defaults();
    $page_id = aprop_get_homepage_id();

    $label = $page_id ? get_field( 'home_help_cards_label', $page_id ) : '';
    $title = $page_id ? get_field( 'home_help_cards_title', $page_id ) : '';

    $cards = array();

    foreach ( $defaults['cards'] as $index => $default_card ) {
        $card_number = $index + 1;
        $field_prefix = 'home_help_card_' . $card_number . '_';
        $image = $page_id ? get_field( $field_prefix . 'image', $page_id ) : null;

        $field_values = array(
            'index' => $page_id ? get_field( $field_prefix . 'index', $page_id ) : '',
            'category' => $page_id ? get_field( $field_prefix . 'category', $page_id ) : '',
            'title' => $page_id ? get_field( $field_prefix . 'title', $page_id ) : '',
            'description' => $page_id ? get_field( $field_prefix . 'description', $page_id ) : '',
            'button_text' => $page_id ? get_field( $field_prefix . 'button_text', $page_id ) : '',
            'button_url' => $page_id ? get_field( $field_prefix . 'button_url', $page_id ) : '',
        );

        $cards[] = array(
            'index' => ! empty( $field_values['index'] ) ? $field_values['index'] : $default_card['index'],
            'category' => ! empty( $field_values['category'] ) ? $field_values['category'] : $default_card['category'],
            'title' => ! empty( $field_values['title'] ) ? $field_values['title'] : $default_card['title'],
            'description' => $field_values['description'] !== '' ? $field_values['description'] : $default_card['description'],
            'button_text' => $field_values['button_text'] !== '' ? $field_values['button_text'] : $default_card['button_text'],
            'button_url' => ! empty( $field_values['button_url'] ) ? $field_values['button_url'] : $default_card['button_url'],
            'theme' => $default_card['theme'],
            'size' => $default_card['size'],
            'image_url' => aprop_normalize_homepage_help_card_image( ! empty( $image['url'] ) ? $image['url'] : $default_card['image_url'], $default_card ),
            'image_alt' => ! empty( $image['alt'] ) ? $image['alt'] : $default_card['title'],
        );
    }

    return array(
        'label' => $label ? $label : $defaults['label'],
        'title' => $title ? $title : $defaults['title'],
        'cards' => $cards,
    );
}

function render_homepage_help_cards_shortcode() {
    $section = aprop_get_homepage_help_cards_data();

    if ( empty( $section['cards'] ) ) {
        return '';
    }

    $large_cards = array_slice( $section['cards'], 0, 2 );
    $small_cards = array_slice( $section['cards'], 2 );

    ob_start();
    ?>
    <section class="homepage-help-cards">
        <div class="homepage-help-cards__header">
            <?php if ( ! empty( $section['label'] ) ) : ?>
                <span class="homepage-help-cards__label label"><?php echo esc_html( $section['label'] ); ?></span>
            <?php endif; ?>

            <?php if ( ! empty( $section['title'] ) ) : ?>
                <h2 class="homepage-help-cards__title"><?php echo nl2br( esc_html( $section['title'] ) ); ?></h2>
            <?php endif; ?>
        </div>

        <div class="homepage-help-cards__grid">
            <?php foreach ( $large_cards as $card ) : ?>
                <article class="homepage-help-card homepage-help-card--large homepage-help-card--<?php echo esc_attr( $card['theme'] ); ?> homepage-help-card--index-<?php echo esc_attr( $card['index'] ); ?>" style="background-image: linear-gradient(180deg, rgba(7,7,7,0.02) 0%, rgba(7,7,7,0.14) 42%, rgba(7,7,7,0.72) 100%), url('<?php echo esc_url( $card['image_url'] ); ?>');">
                    <div class="homepage-help-card__meta"><?php echo esc_html( $card['index'] . ' • ' . $card['category'] ); ?></div>
                    <div class="homepage-help-card__content">
                        <h3 class="homepage-help-card__title"><?php echo esc_html( $card['title'] ); ?></h3>

                        <?php if ( ! empty( $card['description'] ) ) : ?>
                            <p class="homepage-help-card__description"><?php echo esc_html( $card['description'] ); ?></p>
                        <?php endif; ?>

                        <?php if ( ! empty( $card['button_text'] ) && ! empty( $card['button_url'] ) ) : ?>
                            <a class="homepage-help-card__button <?php echo '02' === $card['index'] ? 'btn-white-border-icon' : 'btn-green-icon'; ?>" href="<?php echo esc_url( $card['button_url'] ); ?>">
                                <?php echo esc_html( $card['button_text'] ); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>

            <div class="homepage-help-cards__stack">
                <?php foreach ( $small_cards as $card ) : ?>
                    <article class="homepage-help-card homepage-help-card--small homepage-help-card--<?php echo esc_attr( $card['theme'] ); ?> homepage-help-card--index-<?php echo esc_attr( $card['index'] ); ?>">
                        <div class="homepage-help-card__media">
                            <?php if ( ! empty( $card['image_url'] ) ) : ?>
                                <img src="<?php echo esc_url( $card['image_url'] ); ?>" alt="<?php echo esc_attr( $card['image_alt'] ); ?>" loading="lazy" />
                            <?php endif; ?>
                        </div>

                        <div class="homepage-help-card__meta"><?php echo esc_html( $card['index'] . ' • ' . $card['category'] ); ?></div>
                        <div class="homepage-help-card__content">
                            <h3 class="homepage-help-card__title"><?php echo esc_html( $card['title'] ); ?></h3>

                            <?php if ( ! empty( $card['button_url'] ) ) : ?>
                                <a class="homepage-help-card__icon-link btn-circle-icon-white" href="<?php echo esc_url( $card['button_url'] ); ?>" aria-label="<?php echo esc_attr( $card['title'] ); ?>"></a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php

    return ob_get_clean();
}

add_shortcode( 'homepage_help_cards', 'render_homepage_help_cards_shortcode' );

function render_why_aprop_block_shortcode() {
    $page_id = get_queried_object_id();

    if ( ! $page_id ) {
        return '';
    }

    $label = get_field( 'why_aprop_label', $page_id );
    $title = get_field( 'why_aprop_title', $page_id );
    $text = get_field( 'why_aprop_text', $page_id );
    $button_text = get_field( 'why_aprop_button_text', $page_id );
    $button_url = get_field( 'why_aprop_button_url', $page_id );
    $image_left = get_field( 'why_aprop_image_left', $page_id );
    $image_right = get_field( 'why_aprop_image_right', $page_id );

    if ( empty( $title ) && empty( $text ) && empty( $image_left ) && empty( $image_right ) ) {
        return '';
    }

    ob_start();
    ?>
    <section class="why-aprop-block">
        <?php if ( ! empty( $image_left['url'] ) ) : ?>
            <div class="why-aprop-block__deco why-aprop-block__deco--left" aria-hidden="true">
                <img src="<?php echo esc_url( $image_left['url'] ); ?>" alt="" loading="lazy" />
            </div>
        <?php endif; ?>

        <?php if ( ! empty( $image_right['url'] ) ) : ?>
            <div class="why-aprop-block__deco why-aprop-block__deco--right" aria-hidden="true">
                <img src="<?php echo esc_url( $image_right['url'] ); ?>" alt="" loading="lazy" />
            </div>
        <?php endif; ?>

        <div class="why-aprop-block__inner">
            <div class="why-aprop-block__content">
                <?php if ( ! empty( $label ) ) : ?>
                    <span class="label"><?php echo esc_html( $label ); ?></span>
                <?php endif; ?>

                <?php if ( ! empty( $title ) ) : ?>
                    <h2><?php echo nl2br( esc_html( $title ) ); ?></h2>
                <?php endif; ?>

                <?php if ( ! empty( $text ) ) : ?>
                    <div class="why-aprop-block__text p-24"><?php echo wp_kses_post( $text ); ?></div>
                <?php endif; ?>

                <?php if ( ! empty( $button_text ) && ! empty( $button_url ) ) : ?>
                    <a class="btn-green-icon" href="<?php echo esc_url( $button_url ); ?>">
                        <?php echo esc_html( $button_text ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php

    return ob_get_clean();
}
add_shortcode( 'why_aprop_block', 'render_why_aprop_block_shortcode' );

function render_aprop_features_block_shortcode() {
    $page_id = get_queried_object_id();

    if ( ! $page_id ) {
        return '';
    }

    $main_image = get_field( 'aprop_features_main_image', $page_id );
    $deco_image = get_field( 'aprop_features_deco_image', $page_id );
    $cards      = array();

    for ( $i = 1; $i <= 3; $i++ ) {
        $icon  = get_field( 'aprop_features_card_' . $i . '_icon', $page_id );
        $title = get_field( 'aprop_features_card_' . $i . '_title', $page_id );
        $text  = get_field( 'aprop_features_card_' . $i . '_text', $page_id );

        if ( empty( $icon ) && empty( $title ) && empty( $text ) ) {
            continue;
        }

        $cards[] = array(
            'icon'  => $icon,
            'title' => $title,
            'text'  => $text,
        );
    }

    if ( empty( $main_image ) && empty( $cards ) ) {
        return '';
    }

    ob_start();
    ?>
    <section class="aprop-features-block">
        <?php if ( ! empty( $deco_image['url'] ) ) : ?>
            <div class="aprop-features-block__deco" aria-hidden="true">
                <img src="<?php echo esc_url( $deco_image['url'] ); ?>" alt="" loading="lazy" />
            </div>
        <?php endif; ?>

        <div class="aprop-features-block__inner">
            <?php if ( ! empty( $main_image['url'] ) ) : ?>
                <div class="aprop-features-block__media">
                    <img src="<?php echo esc_url( $main_image['url'] ); ?>" alt="<?php echo esc_attr( $main_image['alt'] ?? 'Aprop tím' ); ?>" loading="lazy" />
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $cards ) ) : ?>
                <div class="aprop-features-block__cards">
                    <?php foreach ( $cards as $card ) : ?>
                        <article class="aprop-features-block__card">
                            <?php if ( ! empty( $card['icon']['url'] ) ) : ?>
                                <div class="aprop-features-block__icon">
                                    <img src="<?php echo esc_url( $card['icon']['url'] ); ?>" alt="<?php echo esc_attr( $card['icon']['alt'] ?? $card['title'] ); ?>" loading="lazy" />
                                </div>
                            <?php endif; ?>

                            <?php if ( ! empty( $card['title'] ) ) : ?>
                                <h3><?php echo esc_html( $card['title'] ); ?></h3>
                            <?php endif; ?>

                            <?php if ( ! empty( $card['text'] ) ) : ?>
                                <p><?php echo esc_html( $card['text'] ); ?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php

    return ob_get_clean();
}
add_shortcode( 'aprop_features_block', 'render_aprop_features_block_shortcode' );


//title banner / hero banner
function render_title_banner_shortcode() {
    // Načítanie hodnôt z ACF
    $banner_image = get_field('banner-image');
    $banner_main_title = get_field('banner-main-title');
    $banner_main_text = get_field('banner-main-text');
    $banner_boxes_main_text = get_field('banner-boxes-main-text');
    $banner_boxes_image = get_field('banner-boxex-image');

    // Inline background style
    $background_style = '';
    if ($banner_image && isset($banner_image['url'])) {
        $background_style = 'style="background-image: url(' . esc_url($banner_image['url']) . '); background-size: cover; background-position: center;"';
    }

    ob_start();
    ?>
    <div class="title-banner" <?php echo $background_style; ?>>
      <div class="title-banner-info">
        <div class="title-banner-info__box">
          <div class="box-img">
            <?php if ($banner_boxes_image): ?>
                <img src="<?php echo esc_url($banner_boxes_image['url']); ?>" alt="<?php echo esc_attr($banner_boxes_image['alt']); ?>">
            <?php endif; ?>

          </div>
            <?php if ($banner_boxes_main_text): ?>
                <?php echo wp_kses_post($banner_boxes_main_text); ?>
            <?php endif; ?>


            <a href="/sluzby" class="btn-primary btn-icon btn-white">Balíčky kurzov</a>
        </div>

        <div class="title-banner-info__main">
            <div class="box-img mobile-box-img">
              <?php if ($banner_boxes_image): ?>
                  <img src="<?php echo esc_url($banner_boxes_image['url']); ?>" alt="<?php echo esc_attr($banner_boxes_image['alt']); ?>">
              <?php endif; ?>
            </div>
            <?php if ($banner_main_title): ?>
                <h1><?php echo esc_html($banner_main_title); ?></h1>
            <?php endif; ?>

            <?php if ($banner_main_text): ?>
                <p><?php echo esc_html($banner_main_text); ?></p>
            <?php endif; ?>
            <a href="/sluzby" class="btn-primary btn-icon btn-white mobile-box-button">Balíčky kurzov</a>
        </div>
      </div>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('title_banner', 'render_title_banner_shortcode');


function render_secondary_hero_banner_shortcode() {
    $banner_image = get_field( 'secondary_hero_banner_image' );

    if ( empty( $banner_image ) || empty( $banner_image['url'] ) ) {
        return '';
    }

    $eyebrow = get_field( 'secondary_hero_eyebrow' );
    $title_line_1 = get_field( 'secondary_hero_title_line_1' );
    $title_line_2 = get_field( 'secondary_hero_title_line_2' );
    $description = get_field( 'secondary_hero_description' );

    $primary_button_text = get_field( 'secondary_hero_primary_button_text' );
    $primary_button_url = get_field( 'secondary_hero_primary_button_url' );
    $secondary_button_text = get_field( 'secondary_hero_secondary_button_text' );
    $secondary_button_url = get_field( 'secondary_hero_secondary_button_url' );

    $product_label = get_field( 'secondary_hero_product_label' );
    $product_posts_raw = get_field( 'secondary_hero_product_post' );
    $product_cards = array();

    // ACF multiple post object / IDs → normalize to list of cards
    $product_items = array();
    if ( ! empty( $product_posts_raw ) ) {
        $product_items = is_array( $product_posts_raw ) ? $product_posts_raw : array( $product_posts_raw );
    }

    foreach ( $product_items as $item ) {
        $post = null;

        if ( $item instanceof WP_Post ) {
            $post = $item;
        } elseif ( is_numeric( $item ) ) {
            $post = get_post( (int) $item );
        } elseif ( is_array( $item ) && ! empty( $item['ID'] ) ) {
            $post = get_post( (int) $item['ID'] );
        }

        if ( ! $post || $post->post_status !== 'publish' ) {
            continue;
        }

        $card = array(
            'title'        => get_the_title( $post ),
            'url'          => get_permalink( $post ),
            'price'        => '',
            'price_suffix' => '',
            'show_cta'     => false,
            'image'        => null,
        );

        $image_id = get_post_thumbnail_id( $post );
        if ( $image_id ) {
            $card['image'] = function_exists( 'acf_get_attachment' )
                ? acf_get_attachment( $image_id )
                : array(
                    'url' => wp_get_attachment_image_url( $image_id, 'large' ),
                    'alt' => get_post_meta( $image_id, '_wp_attachment_image_alt', true ),
                );
        }

        if ( function_exists( 'wc_get_product' ) ) {
            $product = wc_get_product( $post->ID );
            if ( $product ) {
                // Empty WooCommerce price = quote product → icon CTA instead of price.
                // wc_get_price_to_display() casts '' to 0, so check get_price() first.
                $raw_price = $product->get_price();
                if ( $raw_price === '' || $raw_price === null ) {
                    $card['show_cta'] = true;
                } else {
                    $card['price'] = number_format_i18n( (float) wc_get_price_to_display( $product ), 2 ) . ' €';
                    $card['price_suffix'] = 's DPH';
                }
            }
        }

        $product_cards[] = $card;
    }

    $background_style = sprintf(
        'style="background-image: linear-gradient(90deg, rgba(21,21,21,0.88) 0%%, rgba(21,21,21,0.70) 34%%, rgba(21,21,21,0.18) 68%%, rgba(21,21,21,0.08) 100%%), url(%s);"',
        esc_url( $banner_image['url'] )
    );

    $has_product_card = ! empty( $product_cards );
    $product_count = count( $product_cards );

    ob_start();
    ?>
    <div class="title-banner title-banner-secondary" <?php echo $background_style; ?>>
      <div class="title-banner-secondary__content">
        <div class="title-banner-secondary__text">
          <?php if ( $eyebrow ) : ?>
            <div class="title-banner-secondary__eyebrow">
              <span class="title-banner-secondary__eyebrow-dot"></span>
              <span><?php echo esc_html( $eyebrow ); ?></span>
            </div>
          <?php endif; ?>

          <?php if ( $title_line_1 || $title_line_2 ) : ?>
            <h2 class="title-banner-secondary__title">
              <?php if ( $title_line_1 ) : ?>
                <span><?php echo esc_html( $title_line_1 ); ?></span>
              <?php endif; ?>
              <?php if ( $title_line_2 ) : ?>
                <span class="is-muted"><?php echo esc_html( $title_line_2 ); ?></span>
              <?php endif; ?>
            </h2>
          <?php endif; ?>

          <?php if ( $description ) : ?>
            <p class="title-banner-secondary__description"><?php echo esc_html( $description ); ?></p>
          <?php endif; ?>

          <?php if ( ( $primary_button_text && $primary_button_url ) || ( $secondary_button_text && $secondary_button_url ) ) : ?>
            <div class="title-banner-secondary__actions">
              <?php if ( $primary_button_text && $primary_button_url ) : ?>
                <a class="btn-green-icon" href="<?php echo esc_url( $primary_button_url ); ?>">
                  <?php echo esc_html( $primary_button_text ); ?>
                </a>
              <?php endif; ?>

              <?php if ( $secondary_button_text && $secondary_button_url ) : ?>
                <a class="btn-white-border-icon" href="<?php echo esc_url( $secondary_button_url ); ?>">
                  <?php echo esc_html( $secondary_button_text ); ?>
                </a>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="title-banner-secondary__mobile-image" aria-hidden="true">
          <img src="<?php echo esc_url( $banner_image['url'] ); ?>" alt="<?php echo esc_attr( $banner_image['alt'] ?? '' ); ?>">
        </div>

        <?php if ( $has_product_card ) : ?>
          <div class="title-banner-secondary__card" data-hero-product-slider>
            <div class="title-banner-secondary__card-top">
              <?php if ( $product_label ) : ?>
                <span class="title-banner-secondary__card-label"><?php echo esc_html( $product_label ); ?></span>
              <?php endif; ?>

              <?php if ( $product_count > 1 ) : ?>
                <div class="title-banner-secondary__card-nav">
                  <button type="button" class="nav-arrow is-left" aria-label="Predchádzajúci produkt"></button>
                  <span class="nav-dots" role="tablist" aria-label="Produkty">
                    <?php foreach ( $product_cards as $index => $card ) : ?>
                      <button
                        type="button"
                        class="<?php echo $index === 0 ? 'is-active' : ''; ?>"
                        data-slide="<?php echo (int) $index; ?>"
                        aria-label="<?php echo esc_attr( sprintf( 'Produkt %d', $index + 1 ) ); ?>"
                      ></button>
                    <?php endforeach; ?>
                  </span>
                  <button type="button" class="nav-arrow is-right" aria-label="Nasledujúci produkt"></button>
                </div>
              <?php endif; ?>
            </div>

            <div class="title-banner-secondary__card-track">
              <?php foreach ( $product_cards as $index => $card ) : ?>
                <div class="title-banner-secondary__card-slide<?php echo $index === 0 ? ' is-active' : ''; ?>" data-slide="<?php echo (int) $index; ?>">
                  <a class="title-banner-secondary__card-body" href="<?php echo esc_url( $card['url'] ); ?>">
                    <div class="title-banner-secondary__card-copy">
                      <?php if ( ! empty( $card['title'] ) ) : ?>
                        <h3><?php echo esc_html( $card['title'] ); ?></h3>
                      <?php endif; ?>
                    </div>

                    <?php if ( ! empty( $card['show_cta'] ) ) : ?>
                      <span class="btn-green-icon-corner title-banner-secondary__card-cta" aria-hidden="true"></span>
                    <?php elseif ( ! empty( $card['price'] ) || ! empty( $card['price_suffix'] ) ) : ?>
                      <p class="title-banner-secondary__card-price">
                        <?php if ( ! empty( $card['price'] ) ) : ?>
                          <strong><?php echo esc_html( $card['price'] ); ?></strong>
                        <?php endif; ?>
                        <?php if ( ! empty( $card['price_suffix'] ) ) : ?>
                          <span><?php echo esc_html( $card['price_suffix'] ); ?></span>
                        <?php endif; ?>
                      </p>
                    <?php endif; ?>

                    <?php if ( ! empty( $card['image']['url'] ) ) : ?>
                      <div class="title-banner-secondary__card-image">
                        <img src="<?php echo esc_url( $card['image']['url'] ); ?>" alt="<?php echo esc_attr( $card['image']['alt'] ?? $card['title'] ); ?>">
                      </div>
                    <?php endif; ?>
                  </a>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <?php

    return ob_get_clean();
}

add_shortcode( 'secondary_hero_banner', 'render_secondary_hero_banner_shortcode' );


function aprop_get_testy_hero_defaults() {
    return array(
        'eyebrow'                => 'APROP / ONLINE TESTY',
        'title_line_1'           => 'Pripravte sa na skúšku pilota dronu.',
        'title_line_2'           => '',
        'description'            => 'Precvičujte otázky pre A1/A3, A2 a STS. Sledujte výsledky a pripravujte sa vlastným tempom.',
        'primary_button_text'    => 'Vybrať prípravu',
        'primary_button_url'     => '#testy-packages',
        'secondary_button_text'  => 'Vyskúšať A1/A3 zadarmo',
        'secondary_button_url'   => '/produkt/hobby-kurz-drony/',
        'stats_label'            => 'Váš najlepší výsledok',
        'stats_value'            => '93 %',
        'stats_caption'          => 'A2 · Ukážkové údaje',
    );
}

function aprop_get_testy_hero_data() {
    $page_id  = get_the_ID() ?: 14618;
    $defaults = aprop_get_testy_hero_defaults();

    $banner_image = get_field( 'testy_hero_banner_image', $page_id );
    if ( empty( $banner_image ) || empty( $banner_image['url'] ) ) {
        $banner_image = array(
            'url' => get_stylesheet_directory_uri() . '/images/testy-hero.jpg',
            'alt' => 'Online testy – príprava na skúšku pilota dronu',
        );
    }

    $value = function ( $key ) use ( $page_id, $defaults ) {
        $field = get_field( 'testy_hero_' . $key, $page_id );
        return ( $field !== null && $field !== false && $field !== '' ) ? $field : $defaults[ $key ];
    };

    $primary_url = $value( 'primary_button_url' );
    // Old default / ACF value pointed at /testy/ itself — scroll to packages instead.
    if ( aprop_testy_hero_url_is_packages_self_link( $primary_url ) ) {
        $primary_url = '#testy-packages';
    }

    return array(
        'banner_image'           => $banner_image,
        'eyebrow'                => $value( 'eyebrow' ),
        'title_line_1'           => $value( 'title_line_1' ),
        'title_line_2'           => $value( 'title_line_2' ),
        'description'            => $value( 'description' ),
        'primary_button_text'    => $value( 'primary_button_text' ),
        'primary_button_url'     => $primary_url,
        'secondary_button_text'  => $value( 'secondary_button_text' ),
        'secondary_button_url'   => $value( 'secondary_button_url' ),
        'stats_label'            => $value( 'stats_label' ),
        'stats_value'            => $value( 'stats_value' ),
        'stats_caption'          => $value( 'stats_caption' ),
    );
}

/**
 * True when hero primary CTA would just reload /testy/ instead of scrolling to packages.
 */
function aprop_testy_hero_url_is_packages_self_link( $url ) {
    if ( ! is_string( $url ) || '' === $url ) {
        return true;
    }

    if ( '#testy-packages' === $url || 'testy-packages' === ltrim( $url, '#' ) ) {
        return false;
    }

    $path = wp_parse_url( $url, PHP_URL_PATH );
    $path = is_string( $path ) ? untrailingslashit( $path ) : '';
    $fragment = wp_parse_url( $url, PHP_URL_FRAGMENT );

    if ( is_string( $fragment ) && '' !== $fragment && 'testy-packages' !== $fragment ) {
        return false;
    }

    return '' === $path
        || '/testy' === $path
        || (bool) preg_match( '#/testy$#', $path );
}

/** esc_url() drops bare hash links — keep #anchors intact. */
function aprop_esc_href( $url ) {
    if ( is_string( $url ) && isset( $url[0] ) && '#' === $url[0] ) {
        return esc_attr( $url );
    }

    return esc_url( $url );
}

function render_testy_hero_banner_shortcode() {
    $data = aprop_get_testy_hero_data();
    $banner_image = $data['banner_image'];

    if ( empty( $banner_image['url'] ) ) {
        return '';
    }

    $background_style = sprintf(
        'style="background-image: linear-gradient(90deg, rgba(21,21,21,0.88) 0%%, rgba(21,21,21,0.70) 34%%, rgba(21,21,21,0.18) 68%%, rgba(21,21,21,0.08) 100%%), url(%s);"',
        esc_url( $banner_image['url'] )
    );

    $has_stats = $data['stats_label'] || $data['stats_value'] || $data['stats_caption'];

    ob_start();
    ?>
    <div class="title-banner title-banner-secondary title-banner-secondary--stats" <?php echo $background_style; ?>>
      <div class="title-banner-secondary__content">
        <div class="title-banner-secondary__text">
          <?php if ( $data['eyebrow'] ) : ?>
            <div class="title-banner-secondary__eyebrow">
              <span class="title-banner-secondary__eyebrow-dot"></span>
              <span><?php echo esc_html( $data['eyebrow'] ); ?></span>
            </div>
          <?php endif; ?>

          <?php if ( $data['title_line_1'] || $data['title_line_2'] ) : ?>
            <h1 class="title-banner-secondary__title">
              <?php if ( $data['title_line_1'] ) : ?>
                <span><?php echo esc_html( $data['title_line_1'] ); ?></span>
              <?php endif; ?>
              <?php if ( $data['title_line_2'] ) : ?>
                <span class="is-muted"><?php echo esc_html( $data['title_line_2'] ); ?></span>
              <?php endif; ?>
            </h1>
          <?php endif; ?>

          <?php if ( $data['description'] ) : ?>
            <p class="title-banner-secondary__description"><?php echo esc_html( $data['description'] ); ?></p>
          <?php endif; ?>

          <?php if ( ( $data['primary_button_text'] && $data['primary_button_url'] ) || ( $data['secondary_button_text'] && $data['secondary_button_url'] ) ) : ?>
            <div class="title-banner-secondary__actions">
              <?php if ( $data['primary_button_text'] && $data['primary_button_url'] ) : ?>
                <a class="btn-green-icon" href="<?php echo aprop_esc_href( $data['primary_button_url'] ); ?>">
                  <?php echo esc_html( $data['primary_button_text'] ); ?>
                </a>
              <?php endif; ?>

              <?php if ( $data['secondary_button_text'] && $data['secondary_button_url'] ) : ?>
                <a class="btn-white-border-icon" href="<?php echo aprop_esc_href( $data['secondary_button_url'] ); ?>">
                  <?php echo esc_html( $data['secondary_button_text'] ); ?>
                </a>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="title-banner-secondary__mobile-image" aria-hidden="true">
          <img src="<?php echo esc_url( $banner_image['url'] ); ?>" alt="<?php echo esc_attr( $banner_image['alt'] ?? '' ); ?>">
        </div>

        <?php if ( $has_stats ) : ?>
          <div class="title-banner-secondary__stats">
            <?php if ( $data['stats_label'] ) : ?>
              <span class="title-banner-secondary__stats-label"><?php echo esc_html( $data['stats_label'] ); ?></span>
            <?php endif; ?>
            <?php if ( $data['stats_value'] ) : ?>
              <strong class="title-banner-secondary__stats-value"><?php echo esc_html( $data['stats_value'] ); ?></strong>
            <?php endif; ?>            <?php if ( $data['stats_caption'] ) : ?>
              <span class="title-banner-secondary__stats-caption"><?php echo esc_html( $data['stats_caption'] ); ?></span>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <?php

    return ob_get_clean();
}

add_shortcode( 'testy_hero_banner', 'render_testy_hero_banner_shortcode' );


function aprop_get_testy_process_defaults() {
    $theme_uri = get_stylesheet_directory_uri() . '/images/';

    return array(
        'eyebrow' => 'AKO TO PREBIEHA',
        'title'   => "Od prvého kliknutia\nk lepšej príprave.",
        'intro'   => 'Jasný postup. Vlastné tempo. Všetko, čo potrebujete na online prípravu, na jednom mieste.',
        'cards'   => array(
            array(
                'variant'     => 'light',
                'number'      => '01',
                'icon'        => $theme_uri . 'testy-process-compass.png',
                'label'       => 'VÝBER PRÍPRAVY',
                'title'       => 'Nájdite svoj smer.',
                'description' => 'Začínate, rozširujete si vedomosti alebo sa pripravujete na STS? Vyberte si prípravu podľa svojho cieľa.',
                'tags'        => array( 'A1/A3', 'A2', 'STS' ),
                'footer'      => '',
                'footer_type' => 'tags',
            ),
            array(
                'variant'     => 'green',
                'number'      => '02',
                'icon'        => $theme_uri . 'testy-process-key.png',
                'label'       => 'VÁŠ PRÍSTUP',
                'title'       => 'Všetko vo vašom účte.',
                'description' => 'Zaregistrujte sa a otvorte si vybranú prípravu. A1/A3 si môžete najprv vyskúšať úplne zadarmo.',
                'tags'        => array(),
                'footer'      => 'Vaša príprava na jednom mieste',
                'footer_type' => 'pill',
            ),
            array(
                'variant'     => 'dark',
                'number'      => '03',
                'icon'        => $theme_uri . 'testy-process-chart.png',
                'label'       => 'TRÉNING A VÝSLEDKY',
                'title'       => 'Každý pokus vás posúva.',
                'description' => 'Precvičujte otázky vlastným tempom. Pozrite si výsledok a sledujte, ako sa vám darí v ďalších pokusoch.',
                'tags'        => array(),
                'footer'      => 'Vlastné tempo',
                'footer_type' => 'bars',
            ),
        ),
    );
}

function aprop_get_testy_process_data() {
    $page_id  = get_the_ID() ?: 14618;
    $defaults = aprop_get_testy_process_defaults();
    $footer_types = array( 'tags', 'pill', 'bars' );

    $field = function ( $name, $fallback = '' ) use ( $page_id ) {
        $value = get_field( $name, $page_id );
        return ( $value !== null && $value !== false && $value !== '' ) ? $value : $fallback;
    };

    $cards = array();
    foreach ( $defaults['cards'] as $index => $default_card ) {
        $n = $index + 1;
        $icon = get_field( 'testy_process_card_' . $n . '_icon', $page_id );
        $tags_raw = $field( 'testy_process_card_' . $n . '_tags', implode( ', ', $default_card['tags'] ) );
        $tags = array_values( array_filter( array_map( 'trim', explode( ',', (string) $tags_raw ) ) ) );

        $cards[] = array(
            'variant'     => $default_card['variant'],
            'number'      => $field( 'testy_process_card_' . $n . '_number', $default_card['number'] ),
            'icon'        => ( ! empty( $icon['url'] ) ) ? $icon['url'] : $default_card['icon'],
            'label'       => $field( 'testy_process_card_' . $n . '_label', $default_card['label'] ),
            'title'       => $field( 'testy_process_card_' . $n . '_title', $default_card['title'] ),
            'description' => $field( 'testy_process_card_' . $n . '_description', $default_card['description'] ),
            'tags'        => $tags,
            'footer'      => $field( 'testy_process_card_' . $n . '_footer', $default_card['footer'] ),
            'footer_type' => $footer_types[ $index ],
        );
    }

    return array(
        'eyebrow' => $field( 'testy_process_eyebrow', $defaults['eyebrow'] ),
        'title'   => $field( 'testy_process_title', $defaults['title'] ),
        'intro'   => $field( 'testy_process_intro', $defaults['intro'] ),
        'cards'   => $cards,
    );
}

function render_testy_process_shortcode() {
    $section = aprop_get_testy_process_data();

    ob_start();
    ?>
    <section class="testy-process">
      <div class="testy-process__header">
        <div class="testy-process__heading">
          <?php if ( $section['eyebrow'] ) : ?>
            <span class="testy-process__eyebrow"><?php echo esc_html( $section['eyebrow'] ); ?></span>
          <?php endif; ?>
          <?php if ( $section['title'] ) : ?>
            <h2 class="testy-process__title"><?php echo nl2br( esc_html( $section['title'] ) ); ?></h2>
          <?php endif; ?>
        </div>
        <?php if ( $section['intro'] ) : ?>
          <p class="testy-process__intro"><?php echo esc_html( $section['intro'] ); ?></p>
        <?php endif; ?>
      </div>

      <div class="testy-process__grid">
        <?php foreach ( $section['cards'] as $card ) : ?>
          <article class="testy-process__card testy-process__card--<?php echo esc_attr( $card['variant'] ); ?>">
            <div class="testy-process__card-top">
              <span class="testy-process__number"><?php echo esc_html( $card['number'] ); ?></span>
              <?php if ( ! empty( $card['icon'] ) ) : ?>
                <span class="testy-process__icon" aria-hidden="true">
                  <img src="<?php echo esc_url( $card['icon'] ); ?>" alt="">
                </span>
              <?php endif; ?>
            </div>

            <div class="testy-process__card-body">
              <?php if ( $card['label'] ) : ?>
                <span class="testy-process__card-label"><?php echo esc_html( $card['label'] ); ?></span>
              <?php endif; ?>
              <?php if ( $card['title'] ) : ?>
                <h3 class="testy-process__card-title"><?php echo esc_html( $card['title'] ); ?></h3>
              <?php endif; ?>
              <?php if ( $card['description'] ) : ?>
                <p class="testy-process__card-text"><?php echo esc_html( $card['description'] ); ?></p>
              <?php endif; ?>

              <div class="testy-process__card-footer">
                <?php if ( $card['footer_type'] === 'tags' && ! empty( $card['tags'] ) ) : ?>
                  <div class="testy-process__tags">
                    <?php foreach ( $card['tags'] as $tag ) : ?>
                      <span class="testy-process__tag"><?php echo esc_html( $tag ); ?></span>
                    <?php endforeach; ?>
                  </div>
                <?php elseif ( $card['footer_type'] === 'pill' && $card['footer'] ) : ?>
                  <div class="testy-process__pill">
                    <img
                      class="testy-process__pill-check"
                      src="<?php echo esc_url( get_stylesheet_directory_uri() . '/images/testy-process-check.svg' ); ?>"
                      alt=""
                      aria-hidden="true"
                    >
                    <span><?php echo esc_html( $card['footer'] ); ?></span>
                  </div>
                <?php elseif ( $card['footer_type'] === 'bars' && $card['footer'] ) : ?>
                  <div class="testy-process__bars">
                    <img
                      class="testy-process__bars-chart"
                      src="<?php echo esc_url( get_stylesheet_directory_uri() . '/images/testy-process-progress.svg' ); ?>"
                      alt=""
                      aria-hidden="true"
                    >
                    <span class="testy-process__bars-label"><?php echo esc_html( $card['footer'] ); ?></span>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
    <?php

    return ob_get_clean();
}

add_shortcode( 'testy_process', 'render_testy_process_shortcode' );


function aprop_get_testy_try_defaults() {
    return array(
        'eyebrow'      => 'A1/A3 ZADARMO',
        'title'        => "Najprv si prípravu\nvyskúšajte.",
        'description'  => 'Zaregistrujte sa a začnite s A1/A3 zadarmo. Precvičte si otázky a pozrite si svoje výsledky.',
        'button_text'  => 'Vyskúšať A1/A3 zadarmo',
        'button_url'   => '/moj-ucet/',
        'note'         => 'Príprava A1/A3 · Po registrácii',
        'image_badge'  => 'OD TEÓRIE K PRAXI',
        'image'        => get_stylesheet_directory_uri() . '/images/testy-try-drone.jpg',
    );
}

function aprop_get_testy_try_data() {
    $page_id  = get_the_ID() ?: 14618;
    $defaults = aprop_get_testy_try_defaults();

    $field = function ( $name, $fallback = '' ) use ( $page_id ) {
        $value = get_field( $name, $page_id );
        return ( $value !== null && $value !== false && $value !== '' ) ? $value : $fallback;
    };

    $image = get_field( 'testy_try_image', $page_id );
    $image_url = ( ! empty( $image['url'] ) ) ? $image['url'] : $defaults['image'];
    $image_alt = ( ! empty( $image['alt'] ) ) ? $image['alt'] : 'Dron – online príprava A1/A3';

    return array(
        'eyebrow'      => $field( 'testy_try_eyebrow', $defaults['eyebrow'] ),
        'title'        => $field( 'testy_try_title', $defaults['title'] ),
        'description'  => $field( 'testy_try_description', $defaults['description'] ),
        'button_text'  => $field( 'testy_try_button_text', $defaults['button_text'] ),
        'button_url'   => $field( 'testy_try_button_url', $defaults['button_url'] ),
        'note'         => $field( 'testy_try_note', $defaults['note'] ),
        'image_badge'  => $field( 'testy_try_image_badge', $defaults['image_badge'] ),
        'image_url'    => $image_url,
        'image_alt'    => $image_alt,
    );
}

function render_testy_try_shortcode() {
    $data = aprop_get_testy_try_data();

    ob_start();
    ?>
    <section class="testy-try">
      <div class="testy-try__inner">
        <div class="testy-try__content">
          <?php if ( $data['eyebrow'] ) : ?>
            <span class="testy-try__eyebrow">
              <span class="testy-try__eyebrow-dot" aria-hidden="true"></span>
              <?php echo esc_html( $data['eyebrow'] ); ?>
            </span>
          <?php endif; ?>

          <?php if ( $data['title'] ) : ?>
            <h2 class="testy-try__title"><?php echo nl2br( esc_html( $data['title'] ) ); ?></h2>
          <?php endif; ?>

          <?php if ( $data['description'] ) : ?>
            <p class="testy-try__description"><?php echo esc_html( $data['description'] ); ?></p>
          <?php endif; ?>

          <?php if ( $data['button_text'] && $data['button_url'] ) : ?>
            <a class="btn-green-icon testy-try__button" href="<?php echo esc_url( $data['button_url'] ); ?>">
              <?php echo esc_html( $data['button_text'] ); ?>
            </a>
          <?php endif; ?>

          <?php if ( $data['note'] ) : ?>
            <p class="testy-try__note"><?php echo esc_html( $data['note'] ); ?></p>
          <?php endif; ?>
        </div>

        <?php if ( $data['image_url'] ) : ?>
          <div class="testy-try__media">
            <img src="<?php echo esc_url( $data['image_url'] ); ?>" alt="<?php echo esc_attr( $data['image_alt'] ); ?>">
            <?php if ( $data['image_badge'] ) : ?>
              <span class="testy-try__image-badge">
                <span class="testy-try__eyebrow-dot" aria-hidden="true"></span>
                <?php echo esc_html( $data['image_badge'] ); ?>
              </span>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
    <?php

    return ob_get_clean();
}

add_shortcode( 'testy_try', 'render_testy_try_shortcode' );


function aprop_get_testy_overview_defaults() {
    return array(
        'eyebrow'     => 'VAŠA ONLINE PRÍPRAVA',
        'title'       => "Každý pokus.\nLepší prehľad.",
        'description' => 'Otázky, výsledky aj história prípravy. Všetko na jednom mieste.',
        'disclaimer'  => 'Náhľady rozhrania a výsledky sú ilustračné.',
        'cards'       => array(
            array(
                'variant'     => 'light',
                'mockup'      => 'quiz',
                'number'      => '01',
                'title'       => 'Precvičujte otázky',
                'description' => 'Prehľadné testovanie vás prevedie prípravou krok za krokom.',
            ),
            array(
                'variant'     => 'dark',
                'mockup'      => 'result',
                'number'      => '02',
                'title'       => 'Pozrite si výsledok',
                'description' => 'Po dokončení testu uvidíte, ako sa vám darilo.',
            ),
            array(
                'variant'     => 'light',
                'mockup'      => 'history',
                'number'      => '03',
                'title'       => 'Sledujte svoj pokrok',
                'description' => 'Vráťte sa k výsledkom a porovnajte svoje pokusy.',
            ),
        ),
    );
}

function aprop_get_testy_overview_data() {
    $page_id  = get_the_ID() ?: 14618;
    $defaults = aprop_get_testy_overview_defaults();

    $field = function ( $name, $fallback = '' ) use ( $page_id ) {
        $value = get_field( $name, $page_id );
        return ( $value !== null && $value !== false && $value !== '' ) ? $value : $fallback;
    };

    $cards = array();
    foreach ( $defaults['cards'] as $index => $default_card ) {
        $n = $index + 1;
        $cards[] = array(
            'variant'     => $default_card['variant'],
            'mockup'      => $default_card['mockup'],
            'number'      => $field( 'testy_overview_card_' . $n . '_number', $default_card['number'] ),
            'title'       => $field( 'testy_overview_card_' . $n . '_title', $default_card['title'] ),
            'description' => $field( 'testy_overview_card_' . $n . '_description', $default_card['description'] ),
        );
    }

    return array(
        'eyebrow'     => $field( 'testy_overview_eyebrow', $defaults['eyebrow'] ),
        'title'       => $field( 'testy_overview_title', $defaults['title'] ),
        'description' => $field( 'testy_overview_description', $defaults['description'] ),
        'disclaimer'  => $field( 'testy_overview_disclaimer', $defaults['disclaimer'] ),
        'cards'       => $cards,
    );
}

function aprop_render_testy_overview_mockup( $type ) {
    if ( 'quiz' === $type ) {
        ?>
        <div class="testy-overview__preview-panel">
          <span class="testy-overview__quiz-meta">Otázka 1 z 30</span>
          <p class="testy-overview__quiz-question">Ako teplota ovplyvňuje výkonnosť dronu?</p>
          <div class="testy-overview__quiz-options">
            <div class="testy-overview__quiz-option"><strong>A</strong><span>Nezmení sa</span></div>
            <div class="testy-overview__quiz-option"><strong>B</strong><span>Zvýši sa</span></div>
            <div class="testy-overview__quiz-option is-active"><strong>C</strong><span>Zníži sa</span></div>
          </div>
        </div>
        <?php
        return;
    }

    if ( 'result' === $type ) {
        ?>
        <div class="testy-overview__preview-panel">
          <span class="testy-overview__result-label">Váš najlepší výsledok</span>
          <strong class="testy-overview__result-value">93 %</strong>
          <div class="testy-overview__result-rows">
            <div class="testy-overview__result-row">
              <span class="testy-overview__result-row-label">Posledný pokus</span>
              <span class="testy-overview__result-row-pct">93 %</span>
              <div class="testy-overview__result-bar"><span class="testy-overview__result-bar-fill" style="width:93%"></span></div>
            </div>
            <div class="testy-overview__result-row">
              <span class="testy-overview__result-row-label">Predchádzajúci pokus</span>
              <span class="testy-overview__result-row-pct">83 %</span>
              <div class="testy-overview__result-bar"><span class="testy-overview__result-bar-fill" style="width:83%"></span></div>
            </div>
          </div>
          <span class="testy-overview__sample">Ukážkové údaje</span>
        </div>
        <?php
        return;
    }

    ?>
    <div class="testy-overview__preview-panel">
      <h4 class="testy-overview__history-title">História testov</h4>
      <div class="testy-overview__history-list">
        <div class="testy-overview__history-item">
          <div class="testy-overview__history-copy">
            <span class="testy-overview__history-name">A2 · Tréningový test</span>
            <span class="testy-overview__history-when">Dnes</span>
          </div>
          <span class="testy-overview__history-pct is-best">93 %</span>
        </div>
        <div class="testy-overview__history-item">
          <div class="testy-overview__history-copy">
            <span class="testy-overview__history-name">A2 · Tréningový test</span>
            <span class="testy-overview__history-when">Včera</span>
          </div>
          <span class="testy-overview__history-pct">83 %</span>
        </div>
        <div class="testy-overview__history-item">
          <div class="testy-overview__history-copy">
            <span class="testy-overview__history-name">A2 · Tréningový test</span>
            <span class="testy-overview__history-when">Pred 3 dňami</span>
          </div>
          <span class="testy-overview__history-pct">70 %</span>
        </div>
      </div>
      <span class="testy-overview__sample">Ukážkové údaje</span>
    </div>
    <?php
}

function render_testy_overview_shortcode() {
    $section = aprop_get_testy_overview_data();

    ob_start();
    ?>
    <section class="testy-overview">
      <div class="testy-overview__header">
        <?php if ( $section['eyebrow'] ) : ?>
          <span class="label testy-overview__eyebrow"><?php echo esc_html( $section['eyebrow'] ); ?></span>
        <?php endif; ?>
        <?php if ( $section['title'] ) : ?>
          <h2 class="testy-overview__title"><?php echo nl2br( esc_html( $section['title'] ) ); ?></h2>
        <?php endif; ?>
        <?php if ( $section['description'] ) : ?>
          <p class="testy-overview__description"><?php echo esc_html( $section['description'] ); ?></p>
        <?php endif; ?>
      </div>

      <div class="testy-overview__grid">
        <?php foreach ( $section['cards'] as $card ) : ?>
          <article class="testy-overview__card testy-overview__card--<?php echo esc_attr( $card['variant'] ); ?>">
            <div class="testy-overview__preview">
              <?php aprop_render_testy_overview_mockup( $card['mockup'] ); ?>
            </div>
            <div class="testy-overview__body">
              <?php if ( $card['number'] ) : ?>
                <span class="testy-overview__number"><?php echo esc_html( $card['number'] ); ?></span>
              <?php endif; ?>
              <?php if ( $card['title'] ) : ?>
                <h3 class="testy-overview__card-title"><?php echo esc_html( $card['title'] ); ?></h3>
              <?php endif; ?>
              <?php if ( $card['description'] ) : ?>
                <p class="testy-overview__card-text"><?php echo esc_html( $card['description'] ); ?></p>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <?php if ( $section['disclaimer'] ) : ?>
        <p class="testy-overview__disclaimer"><?php echo esc_html( $section['disclaimer'] ); ?></p>
      <?php endif; ?>
    </section>
    <?php

    return ob_get_clean();
}

add_shortcode( 'testy_overview', 'render_testy_overview_shortcode' );


function aprop_get_testy_faq_defaults() {
    return array(
        'eyebrow'     => 'DOBRÉ VEDIEŤ',
        'title'       => "Ešte predtým,\nnež začnete.",
        'description' => 'Odpovede na otázky o prístupe a online príprave.',
        'items'       => array(
            array(
                'question' => 'Čo si môžem vyskúšať zadarmo?',
                'answer'   => 'Po registrácii získate prístup k príprave A1/A3. Môžete si vyskúšať testovanie a pozrieť svoje výsledky.',
            ),
            array(
                'question' => 'Kedy získam platený prístup?',
                'answer'   => 'Prihlasovacie údaje k testom posielame do 24 hodín od prijatia úhrady.',
            ),
            array(
                'question' => 'Ako dlho platí zakúpený prístup?',
                'answer'   => 'Prístup k plateným testom A2 alebo STS je na 1 mesiac. Pri zakúpení balíka testov A2 + STS, máte prístup až na 3 mesiace.',
            ),
            array(
                'question' => 'Získam absolvovaním testov osvedčenie?',
                'answer'   => 'Nie. Naše testy slúžia iba na prípravu na skúšky na Dopravnom úrade, ktorý Vám po ich úspešnom zvládnutí vydá platné osvedčenie pilota na diaľku.',
            ),
            array(
                'question' => '',
                'answer'   => '',
            ),
            array(
                'question' => 'Potrebujem aj výklad. Čo si mám vybrať?',
                'answer'   => 'V prípade potreby výkladu odporúčame zakúpiť jeden z našich kurzov, kde okrem prístupu k samotným testom, dostanete kvalitné online školenie od nášho certifikovaného inštruktora a zároveň aj prístup k študijným materiálom a aktuálnej mape zemepisných oblastí UAS.',
            ),
        ),
    );
}

function aprop_get_testy_faq_data() {
    $page_id  = get_the_ID() ?: 14618;
    $defaults = aprop_get_testy_faq_defaults();

    $field = function ( $name, $fallback = '' ) use ( $page_id ) {
        $value = get_field( $name, $page_id );
        return ( $value !== null && $value !== false && $value !== '' ) ? $value : $fallback;
    };

    $items = array();
    foreach ( $defaults['items'] as $index => $default_item ) {
        $n = $index + 1;
        $question = $field( 'testy_faq_q_' . $n, $default_item['question'] );
        $answer   = $field( 'testy_faq_a_' . $n, $default_item['answer'] );
        if ( ! $question || ! $answer ) {
            continue;
        }
        $items[] = array(
            'question' => $question,
            'answer'   => $answer,
        );
    }

    return array(
        'eyebrow'     => $field( 'testy_faq_eyebrow', $defaults['eyebrow'] ),
        'title'       => $field( 'testy_faq_title', $defaults['title'] ),
        'description' => $field( 'testy_faq_description', $defaults['description'] ),
        'items'       => $items,
    );
}

function render_testy_faq_shortcode() {
    $section = aprop_get_testy_faq_data();
    if ( empty( $section['items'] ) ) {
        return '';
    }

    ob_start();
    ?>
    <section class="testy-faq">
      <div class="testy-faq__inner">
        <div class="testy-faq__intro">
          <?php if ( $section['eyebrow'] ) : ?>
            <span class="testy-faq__eyebrow">
              <span class="testy-faq__eyebrow-dot" aria-hidden="true"></span>
              <?php echo esc_html( $section['eyebrow'] ); ?>
            </span>
          <?php endif; ?>
          <?php if ( $section['title'] ) : ?>
            <h2 class="testy-faq__title"><?php echo nl2br( esc_html( $section['title'] ), false ); ?></h2>
          <?php endif; ?>
          <?php if ( $section['description'] ) : ?>
            <p class="testy-faq__description"><?php echo esc_html( $section['description'] ); ?></p>
          <?php endif; ?>
        </div>

        <div class="testy-faq__list">
          <?php foreach ( $section['items'] as $index => $item ) : ?>
            <div class="faq-item<?php echo 0 === $index ? ' open' : ''; ?>">
              <button class="faq-question" type="button">
                <span class="faq-question-text"><?php echo esc_html( $item['question'] ); ?></span>
                <span class="faq-toggle" aria-hidden="true"></span>
              </button>
              <div class="faq-answer"<?php echo 0 === $index ? ' style="display:block"' : ''; ?>>
                <p><?php echo esc_html( $item['answer'] ); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php

    return ob_get_clean();
}

add_shortcode( 'testy_faq', 'render_testy_faq_shortcode' );


function aprop_get_testy_packages_defaults() {
    return array(
        'eyebrow'             => 'VYBERTE SI PRÍPRAVU',
        'title'               => "Vaša skúška.\nVáš ďalší krok.",
        'description'         => 'Začínate? Spoznajte základy s A1/A3. Máte konkrétny cieľ? Vyberte A2 alebo STS. Viac príprav nájdete v balíkoch nižšie.',
        'bundles_title'       => 'Chcete toho zvládnuť viac?',
        'bundles_description' => 'Spojte prípravy do jedného prístupu a ušetrite čas aj peniaze.',
    );
}

function aprop_get_testy_packages_data() {
    $page_id  = get_the_ID() ?: 14618;
    $defaults = aprop_get_testy_packages_defaults();

    $field = function ( $name, $fallback = '' ) use ( $page_id ) {
        $value = get_field( $name, $page_id );
        return ( $value !== null && $value !== false && $value !== '' ) ? $value : $fallback;
    };

    return array(
        'eyebrow'             => $field( 'testy_packages_eyebrow', $defaults['eyebrow'] ),
        'title'               => $field( 'testy_packages_title', $defaults['title'] ),
        'description'         => $field( 'testy_packages_description', $defaults['description'] ),
        'bundles_title'       => $field( 'testy_packages_bundles_title', $defaults['bundles_title'] ),
        'bundles_description' => $field( 'testy_packages_bundles_description', $defaults['bundles_description'] ),
    );
}

function aprop_format_testy_package_price( $price ) {
    $price    = (float) $price;
    $decimals = ( abs( $price - round( $price ) ) < 0.001 ) ? 0 : 2;
    return number_format_i18n( $price, $decimals ) . ' €';
}

function aprop_render_testy_package_price( $item ) {
    if ( $item['is_free'] || $item['price_label'] ) {
        $label = $item['price_label'] ? $item['price_label'] : 'Zadarmo';
        echo '<span class="testy-packages__price-value">' . esc_html( $label ) . '</span>';
    } else {
        echo '<span class="testy-packages__price-value">' . esc_html( aprop_format_testy_package_price( $item['price'] ) ) . '</span>';
    }
    if ( $item['price_note'] ) {
        echo '<span class="testy-packages__price-note">' . esc_html( $item['price_note'] ) . '</span>';
    }
}

function aprop_render_testy_package_button( $item, $extra_class = '' ) {
    $btn_class = 'green' === $item['button_style'] ? 'btn-green-icon' : 'btn-black-icon';
    // No ajax_add_to_cart — AJAX errors redirect to the product page, which
    // then 301s back to /testy/ and looks like a reload. Full navigation to
    // /kosik/?add-to-cart=ID adds the product (incl. 0 € A1/A3) and lands on cart.
    $classes = trim( $btn_class . ' testy-packages__btn ' . $extra_class );
    ?>
    <a
      href="<?php echo esc_url( $item['add_to_cart_url'] ); ?>"
      class="<?php echo esc_attr( $classes ); ?>"
      aria-label="<?php echo esc_attr( $item['button_text'] ); ?>"
      rel="nofollow"
    ><?php echo esc_html( $item['button_text'] ); ?></a>
    <?php
}

function aprop_render_testy_package_card_standard( $item ) {
    ?>
    <article class="testy-packages__card testy-packages__card--standard">
      <?php if ( $item['badge'] ) : ?>
        <span class="testy-packages__badge testy-packages__badge--<?php echo esc_attr( $item['badge_style'] ); ?>">
          <?php echo esc_html( $item['badge'] ); ?>
        </span>
      <?php endif; ?>

      <h3 class="testy-packages__card-title"><?php echo esc_html( $item['name'] ); ?></h3>

      <?php if ( $item['description'] ) : ?>
        <p class="testy-packages__card-desc"><?php echo esc_html( $item['description'] ); ?></p>
      <?php endif; ?>

      <?php if ( ! empty( $item['stats'] ) ) : ?>
        <div class="testy-packages__stats">
          <?php foreach ( $item['stats'] as $stat ) : ?>
            <div class="testy-packages__stat">
              <strong><?php echo esc_html( $stat['value'] ); ?></strong>
              <?php if ( ! empty( $stat['label'] ) ) : ?>
                <span><?php echo esc_html( $stat['label'] ); ?></span>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if ( ! empty( $item['features'] ) ) : ?>
        <div class="testy-packages__features">
          <?php if ( $item['features_title'] ) : ?>
            <p class="testy-packages__features-title"><?php echo esc_html( $item['features_title'] ); ?></p>
          <?php endif; ?>
          <ul>
            <?php foreach ( $item['features'] as $feature ) : ?>
              <li><?php echo esc_html( $feature ); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <div class="testy-packages__footer">
        <div class="testy-packages__price">
          <?php aprop_render_testy_package_price( $item ); ?>
        </div>
        <?php aprop_render_testy_package_button( $item ); ?>
      </div>
    </article>
    <?php
}

function aprop_render_testy_package_card_bundle( $item ) {
    ?>
    <article class="testy-packages__card testy-packages__card--bundle">
      <?php if ( $item['badge'] ) : ?>
        <span class="testy-packages__badge testy-packages__badge--<?php echo esc_attr( $item['badge_style'] ); ?>">
          <?php echo esc_html( $item['badge'] ); ?>
        </span>
      <?php endif; ?>

      <h3 class="testy-packages__card-title"><?php echo esc_html( $item['name'] ); ?></h3>

      <?php if ( $item['description'] ) : ?>
        <p class="testy-packages__card-desc"><?php echo esc_html( $item['description'] ); ?></p>
      <?php endif; ?>

      <?php if ( ! empty( $item['stats'] ) ) : ?>
        <div class="testy-packages__pills">
          <?php foreach ( $item['stats'] as $stat ) : ?>
            <span class="testy-packages__pill">
              <?php echo esc_html( trim( $stat['value'] . ' ' . ( isset( $stat['label'] ) ? $stat['label'] : '' ) ) ); ?>
            </span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="testy-packages__footer">
        <div class="testy-packages__price">
          <?php aprop_render_testy_package_price( $item ); ?>
        </div>
        <?php aprop_render_testy_package_button( $item ); ?>
      </div>
    </article>
    <?php
}

function aprop_render_testy_package_card_complete( $item ) {
    $theme_uri = get_stylesheet_directory_uri();
    ?>
    <article class="testy-packages__card testy-packages__card--complete">
      <div class="testy-packages__complete-main">
        <?php if ( $item['badge'] ) : ?>
          <span class="testy-packages__badge testy-packages__badge--<?php echo esc_attr( $item['badge_style'] ); ?>">
            <?php echo esc_html( $item['badge'] ); ?>
          </span>
        <?php endif; ?>

        <h3 class="testy-packages__card-title"><?php echo esc_html( $item['name'] ); ?></h3>

        <?php if ( $item['description'] ) : ?>
          <p class="testy-packages__card-desc"><?php echo esc_html( $item['description'] ); ?></p>
        <?php endif; ?>

        <?php if ( ! empty( $item['includes'] ) ) : ?>
          <div class="testy-packages__includes">
            <?php foreach ( $item['includes'] as $include ) : ?>
              <span class="testy-packages__include"><?php echo esc_html( $include ); ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if ( ! empty( $item['features'] ) ) : ?>
          <ul class="testy-packages__features testy-packages__features--light">
            <?php foreach ( $item['features'] as $feature ) : ?>
              <li><?php echo esc_html( $feature ); ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>

      <div class="testy-packages__complete-cta">
        <img class="testy-packages__stack-icon" src="<?php echo esc_url( $theme_uri . '/images/testy-stack.svg' ); ?>" alt="" width="40" height="40" aria-hidden="true">
        <?php if ( ! empty( $item['access_title'] ) || ! empty( $item['access_subtitle'] ) || ! empty( $item['access_label'] ) ) : ?>
          <p class="testy-packages__access">
            <?php if ( ! empty( $item['access_title'] ) ) : ?>
              <strong><?php echo esc_html( $item['access_title'] ); ?></strong>
              <?php if ( ! empty( $item['access_subtitle'] ) ) : ?>
                <span><?php echo esc_html( $item['access_subtitle'] ); ?></span>
              <?php endif; ?>
            <?php else : ?>
              <?php echo esc_html( $item['access_label'] ); ?>
            <?php endif; ?>
          </p>
        <?php endif; ?>
        <div class="testy-packages__price testy-packages__price--large">
          <span class="testy-packages__price-value"><?php echo esc_html( aprop_format_testy_package_price( $item['price'] ) ); ?></span>
        </div>
        <?php if ( $item['price_note'] ) : ?>
          <span class="testy-packages__price-note testy-packages__price-note--cta"><?php echo esc_html( $item['price_note'] ); ?></span>
        <?php endif; ?>
        <?php aprop_render_testy_package_button( $item ); ?>
      </div>
    </article>
    <?php
}

function render_testy_packages_shortcode() {
    if ( function_exists( 'aprop_install_testy_products' ) ) {
        aprop_install_testy_products();
    }

    $section  = aprop_get_testy_packages_data();
    $products = function_exists( 'aprop_get_testy_products' ) ? aprop_get_testy_products() : array();

    if ( empty( $products ) ) {
        return '';
    }

    $standard = array();
    $bundle   = null;
    $complete = null;

    foreach ( $products as $item ) {
        if ( 'complete' === $item['layout'] ) {
            $complete = $item;
        } elseif ( 'bundle' === $item['layout'] ) {
            $bundle = $item;
        } else {
            $standard[] = $item;
        }
    }

    ob_start();
    ?>
    <section class="testy-packages" id="testy-packages">
      <div class="testy-packages__inner">
        <div class="testy-packages__header">
          <?php if ( $section['eyebrow'] ) : ?>
            <span class="testy-packages__eyebrow"><?php echo esc_html( $section['eyebrow'] ); ?></span>
          <?php endif; ?>
          <?php if ( $section['title'] ) : ?>
            <h2 class="testy-packages__title"><?php echo nl2br( esc_html( $section['title'] ), false ); ?></h2>
          <?php endif; ?>
          <?php if ( $section['description'] ) : ?>
            <p class="testy-packages__description"><?php echo esc_html( $section['description'] ); ?></p>
          <?php endif; ?>
        </div>

        <?php if ( $standard ) : ?>
          <div class="testy-packages__grid testy-packages__grid--standard">
            <?php foreach ( $standard as $item ) : ?>
              <?php aprop_render_testy_package_card_standard( $item ); ?>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if ( $bundle || $complete ) : ?>
          <?php if ( $section['bundles_title'] ) : ?>
            <div class="testy-packages__bundles-intro">
              <h3 class="testy-packages__bundles-title"><?php echo esc_html( $section['bundles_title'] ); ?></h3>
            </div>
          <?php endif; ?>

          <div class="testy-packages__grid testy-packages__grid--bundles">
            <?php
            if ( $bundle ) {
                aprop_render_testy_package_card_bundle( $bundle );
            }
            if ( $complete ) {
                aprop_render_testy_package_card_complete( $complete );
            }
            ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
    <?php

    return ob_get_clean();
}

add_shortcode( 'testy_packages', 'render_testy_packages_shortcode' );


//about us + pillars
function render_piliere_slider_shortcode() {
    $args = array(
        'post_type' => 'piliere',
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC'
    );

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return '<p>Žiadne piliere neboli nájdené.</p>';
    }

    // Získať ID stránky "Domov" podľa slug-u (alebo môžeš použiť priamo ID)
    $home_page = get_page_by_path('domov');
    $home_id = $home_page ? $home_page->ID : null;

    // ACF polia z tejto stránky
    $about_img = get_field('about-us-image', $home_id);
    $about_title = get_field('about_us_title', $home_id);
    $about_text = get_field('about_us_text', $home_id);

    ob_start();
    ?>
    <div class="about-us-container">
        <div class="pillars">
            <div class="nav-section">
              <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/aprop-icon.svg" alt="Aprop svg logo">
              <div class="pillar-nav">
                  <?php foreach ($query->posts as $index => $post): ?>
                      <div class="pillar-nav-item btn-primary" data-slide="<?php echo $index; ?>">
                          <?php echo esc_html(get_the_title($post)); ?>
                      </div>
                  <?php endforeach; ?>
              </div>
            </div>

            <div class="pillar-slider">
                <?php foreach ($query->posts as $post): setup_postdata($post); ?>
                    <?php
                        $btn_url = get_field('button-url', $post->ID);
                        $bg_color = get_field('pillar-color', $post->ID);
                        $bg_style = $bg_color ? 'style="background-color: ' . esc_attr($bg_color) . ';"' : '';
                    ?>
                    <div class="pillar-slide" <?php echo $bg_style; ?>>
                        <div class="pillar-img">
                          <h2 class="slide-title-mobile"><?php echo esc_html(get_the_title($post)); ?></h2>
                            <?php if (has_post_thumbnail($post)) {
                                echo get_the_post_thumbnail($post, 'large');
                            } ?>
                        </div>
                        <div class="pillar-content">
                            <h2><?php echo esc_html(get_the_title($post)); ?></h2>
                            <p><?php echo esc_html(get_the_excerpt($post)); ?></p>
                            <?php if ($btn_url): ?>
                                <a href="<?php echo esc_url($btn_url); ?>" class="btn-primary btn-white btn-icon">Nakupovať kurzy</a>
                            <?php endif; ?>
                            <div class="pillar-counter"><span class="current">1</span> / <span class="total">0</span></div>
                        </div>
                    </div>
                <?php endforeach; wp_reset_postdata(); ?>
            </div>
        </div>

        <div class="about-section">
          <?php if ($about_title): ?>
              <h2><?php echo esc_html($about_title); ?></h2>
          <?php endif; ?>
            <div class="about-section--content">
              <?php if ($about_img): ?>
                  <img src="<?php echo esc_url($about_img['url']); ?>" alt="<?php echo esc_attr($about_img['alt']); ?>">
              <?php endif; ?>
              <div class="about-text">
                <?php if ($about_text): ?>
                  <?php echo wp_kses_post($about_text); ?>
                <?php endif; ?>
              </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('piliere_slider', 'render_piliere_slider_shortcode');


//show all products
function aprop_render_product_cards_query( $args, $with_wrapper = true ) {
    $products = new WP_Query($args);

    if (!$products->have_posts()) {
        return '<p>Žiadne produkty neboli nájdené.</p>';
    }

    ob_start();

    if ( $with_wrapper ) {
        echo '<div class="products-grid">';
    }

    while ($products->have_posts()): $products->the_post(); global $product; ?>
        <article class="course-product-card">
            <a class="course-product-card__link" href="<?php the_permalink(); ?>">
                <div class="course-product-card__media">
                    <?php if (has_post_thumbnail()) {
                        the_post_thumbnail('large', array('class' => 'course-product-card__image'));
                    } ?>
                </div>
                <div class="course-product-card__content">
                    <h3 class="course-product-card__title"><?php the_title(); ?></h3>
                    <div class="course-product-card__footer">
                        <span class="course-product-card__price"><?php echo $product->get_price_html(); ?></span>
                        <span class="course-product-card__detail">Pozrieť detail</span>
                    </div>
                </div>
            </a>
        </article>
    <?php endwhile; wp_reset_postdata();

    if ( $with_wrapper ) {
        echo '</div>';
    }

    return ob_get_clean();
}

function render_all_products_shortcode() {
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'ASC',
        'tax_query'      => array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => array('package', 'polnohospodarske-drony', 'testy-pripravy'), // <- kategórie, ktoré nechceme v pôvodnom výpise
                'operator' => 'NOT IN',
            ),
        ),
    );

    return aprop_render_product_cards_query( $args );
}
add_shortcode('all_products', 'render_all_products_shortcode');

/**
 * Products in Kurzy (incl. children), excluding Služby (incl. its children).
 * Falls back to "everything except drones" when Kurzy is not synced yet.
 */
function aprop_kurzy_products_tax_query() {
    $kurzy_term = get_term_by( 'slug', 'kurzy', 'product_cat' );
    if ( ! $kurzy_term instanceof WP_Term ) {
        $kurzy_term = get_term_by( 'name', 'Kurzy', 'product_cat' );
    }

    $sluzby_term = get_term_by( 'slug', 'sluzby', 'product_cat' );
    if ( ! $sluzby_term instanceof WP_Term ) {
        $sluzby_term = get_term_by( 'name', 'Služby', 'product_cat' );
    }

    if ( $kurzy_term instanceof WP_Term ) {
        $tax_query = array(
            'relation' => 'AND',
            array(
                'taxonomy'         => 'product_cat',
                'field'            => 'term_id',
                'terms'            => array( (int) $kurzy_term->term_id ),
                'operator'         => 'IN',
                'include_children' => true,
            ),
        );

        if ( $sluzby_term instanceof WP_Term ) {
            $tax_query[] = array(
                'taxonomy'         => 'product_cat',
                'field'            => 'term_id',
                'terms'            => array( (int) $sluzby_term->term_id ),
                'operator'         => 'NOT IN',
                'include_children' => true,
            );
        }

        return $tax_query;
    }

    // Local fallback until the production Kurzy category is synced.
    $drone_tax_query = array(
        'taxonomy'         => 'product_cat',
        'operator'         => 'NOT IN',
        'include_children' => true,
    );

    if ( function_exists( 'aprop_drone_category_id' ) ) {
        $drone_tax_query['field'] = 'term_id';
        $drone_tax_query['terms'] = array( aprop_drone_category_id() );
    } else {
        $drone_tax_query['field'] = 'slug';
        $drone_tax_query['terms'] = array( 'polnohospodarske-drony' );
    }

    return array( $drone_tax_query );
}

function aprop_render_kurzy_products( $with_wrapper = true ) {
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'menu_order title',
        'order'          => 'ASC',
        'tax_query'      => aprop_kurzy_products_tax_query(),
    );

    return aprop_render_product_cards_query( $args, $with_wrapper );
}

function render_all_products_excluding_drones_shortcode() {
    return aprop_render_kurzy_products( true );
}
add_shortcode('all_products_excluding_drones', 'render_all_products_excluding_drones_shortcode');

function aprop_render_all_products_excluding_drones_items() {
    return aprop_render_kurzy_products( false );
}

//block with products
function render_kurzy_blok_shortcode() {
    $home_page = get_page_by_path('domov');
    $home_id = $home_page ? $home_page->ID : null;

    $courses_label = get_field('courses-label', $home_id);
    $courses_title = get_field('courses-title', $home_id);
    $courses_text  = get_field('courses-text', $home_id);

    ob_start();
    ?>
    <div class="courses-block">
        <div class="courses-header">
            <div>
                <?php if ($courses_label): ?>
                    <span class="btn-primary label"><?php echo esc_html($courses_label); ?></span>
                <?php endif; ?>

                <?php if ($courses_title): ?>
                    <h2 class="courses-title"><?php echo esc_html($courses_title); ?></h2>
                <?php endif; ?>

                <?php if ($courses_text): ?>
                    <div class="courses-text-mobile">
                        <?php echo wpautop(wp_kses_post($courses_text)); ?>
                    </div>
                <?php endif; ?>
            </div>

            <a href="/sluzby" class="btn-primary btn-black btn-icon">Pozrieť všetky</a>
        </div>

        <div class="courses-product-list">
            <?php echo aprop_render_kurzy_products(); ?>
        </div>

        <?php if ($courses_text): ?>
            <div class="courses-text">
                <?php echo wpautop(wp_kses_post($courses_text)); ?>
                <a href="/sluzby" class="btn-primary btn-black btn-icon btn-mobile">Pozrieť všetky</a>
                <div class="slider-nav">
                    <button id="slider-prev" class="slick-prev">‹</button>
                    <button id="slider-next" class="slick-next">›</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode('kurzy_blok', 'render_kurzy_blok_shortcode');

function aprop_get_home_courses_slider_products( $page_id ) {
    $selected_products = (array) get_field( 'home_courses_slider_products', $page_id );
    $products = array();

    foreach ( $selected_products as $selected_product ) {
        if ( $selected_product instanceof WP_Post && 'product' === $selected_product->post_type ) {
            $products[] = $selected_product;
        }
    }

    return $products;
}

function aprop_get_home_courses_slider_data() {
    $page_id = aprop_get_homepage_id();

    if ( ! $page_id ) {
        return array(
            'title' => '',
            'button_text' => '',
            'button_url' => '',
            'products' => array(),
        );
    }

    return array(
        'title' => get_field( 'home_courses_slider_title', $page_id ),
        'button_text' => get_field( 'home_courses_slider_button_text', $page_id ),
        'button_url' => get_field( 'home_courses_slider_button_url', $page_id ),
        'products' => aprop_get_home_courses_slider_products( $page_id ),
    );
}

function render_home_courses_slider_shortcode() {
    $section = aprop_get_home_courses_slider_data();

    if ( empty( $section['products'] ) ) {
        return '';
    }

    $section_id = 'home-courses-slider-' . wp_unique_id();

    ob_start();
    ?>
    <section id="<?php echo esc_attr( $section_id ); ?>" class="home-courses-slider">
        <div class="home-courses-slider__header">
            <?php if ( ! empty( $section['title'] ) ) : ?>
                <h2 class="home-courses-slider__title"><?php echo esc_html( $section['title'] ); ?></h2>
            <?php endif; ?>

            <?php if ( ! empty( $section['button_text'] ) && ! empty( $section['button_url'] ) ) : ?>
                <a class="home-courses-slider__all-link btn-primary btn-black btn-icon" href="<?php echo esc_url( $section['button_url'] ); ?>">
                    <?php echo esc_html( $section['button_text'] ); ?>
                </a>
            <?php endif; ?>
        </div>

        <div class="home-courses-slider__track">
            <?php foreach ( $section['products'] as $selected_product ) : ?>
                <?php
                    $product = wc_get_product( $selected_product->ID );

                    if ( ! $product ) {
                        continue;
                    }
                ?>
                <article class="course-product-card">
                    <a class="course-product-card__link" href="<?php echo esc_url( get_permalink( $selected_product->ID ) ); ?>">
                        <div class="course-product-card__media">
                            <?php echo get_the_post_thumbnail( $selected_product->ID, 'large', array( 'class' => 'course-product-card__image' ) ); ?>
                        </div>
                        <div class="course-product-card__content">
                            <h3 class="course-product-card__title"><?php echo esc_html( get_the_title( $selected_product->ID ) ); ?></h3>
                            <div class="course-product-card__footer">
                                <span class="course-product-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
                                <span class="course-product-card__detail">Pozrieť detail</span>
                            </div>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="home-courses-slider__footer">
            <div class="home-courses-slider__arrows">
                <button type="button" class="modern-agro-slider__arrow modern-agro-slider__arrow--prev" aria-label="Predchádzajúci kurz"></button>
                <button type="button" class="modern-agro-slider__arrow modern-agro-slider__arrow--next" aria-label="Nasledujúci kurz"></button>
            </div>
            <div class="home-courses-slider__progress"><span></span></div>
        </div>

        <script>
        jQuery(function ($) {
            var section = $('#<?php echo esc_js( $section_id ); ?>');
            var track = section.find('.home-courses-slider__track');

            if (!track.length || track.hasClass('slick-initialized') || typeof $.fn.slick !== 'function') {
                return;
            }

            if (track.children().length <= 1) {
                section.find('.home-courses-slider__footer').hide();
                return;
            }

            function updateProgress(slick, currentSlide) {
                var slidesToShow = slick.options.slidesToShow;

                if (typeof slidesToShow !== 'number') {
                    slidesToShow = 1;
                }

                var totalSlides = slick.slideCount;
                var current = currentSlide || 0;
                var maxSteps = Math.max(totalSlides - Math.ceil(slidesToShow), 1);
                var progress = totalSlides <= slidesToShow ? 100 : Math.max((current / maxSteps) * 100, 10);

                section.find('.home-courses-slider__progress span').css('width', progress + '%');
            }

            track.on('init reInit afterChange', function (event, slick, currentSlide) {
                updateProgress(slick, currentSlide);
            });

            track.slick({
                slidesToShow: 3.8,
                slidesToScroll: 1,
                infinite: false,
                arrows: true,
                dots: false,
                prevArrow: section.find('.modern-agro-slider__arrow--prev'),
                nextArrow: section.find('.modern-agro-slider__arrow--next'),
                responsive: [
                    {
                        breakpoint: 1100,
                        settings: {
                            slidesToShow: 2.1
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 1.1
                        }
                    }
                ]
            });
        });
        </script>
    </section>
    <?php

    return ob_get_clean();
}
add_shortcode( 'home_courses_slider', 'render_home_courses_slider_shortcode' );

function aprop_get_online_tests_promo_defaults() {
    return array(
        'eyebrow'      => '05 · ONLINE TESTY',
        'title'        => 'Precvičte si otázky pred skúškou',
        'description'  => 'Online príprava A1/A3, A2 a STS. Vlastným tempom.',
        'button_text'  => 'Pozrieť testy',
        'button_url'   => home_url( '/testy/' ),
        'icon_url'     => get_stylesheet_directory_uri() . '/images/test-preparation.png',
    );
}

function aprop_get_online_tests_promo_data() {
    $page_id  = aprop_get_homepage_id() ?: get_queried_object_id();
    $defaults = aprop_get_online_tests_promo_defaults();

    if ( ! $page_id ) {
        return $defaults;
    }

    $icon = get_field( 'home_online_tests_icon', $page_id );
    $icon_url = '';

    if ( is_array( $icon ) && ! empty( $icon['url'] ) ) {
        $icon_url = $icon['url'];
    } elseif ( is_numeric( $icon ) ) {
        $icon_url = wp_get_attachment_image_url( (int) $icon, 'full' ) ?: '';
    } elseif ( is_string( $icon ) && $icon !== '' ) {
        $icon_url = $icon;
    }

    return array(
        'eyebrow'     => get_field( 'home_online_tests_eyebrow', $page_id ) ?: $defaults['eyebrow'],
        'title'       => get_field( 'home_online_tests_title', $page_id ) ?: $defaults['title'],
        'description' => get_field( 'home_online_tests_description', $page_id ) ?: $defaults['description'],
        'button_text' => get_field( 'home_online_tests_button_text', $page_id ) ?: $defaults['button_text'],
        'button_url'  => get_field( 'home_online_tests_button_url', $page_id ) ?: $defaults['button_url'],
        'icon_url'    => $icon_url ?: $defaults['icon_url'],
    );
}

function render_home_online_tests_promo_shortcode() {
    $section = aprop_get_online_tests_promo_data();

    if ( empty( $section['title'] ) ) {
        return '';
    }

    ob_start();
    ?>
    <section class="online-tests-promo">
        <div class="online-tests-promo__inner">
            <div class="online-tests-promo__main">
                <?php if ( ! empty( $section['icon_url'] ) ) : ?>
                    <div class="online-tests-promo__icon">
                        <img
                            src="<?php echo esc_url( $section['icon_url'] ); ?>"
                            alt=""
                            loading="lazy"
                            width="88"
                            height="88"
                        />
                    </div>
                <?php endif; ?>

                <div class="online-tests-promo__content">
                    <?php if ( ! empty( $section['eyebrow'] ) ) : ?>
                        <span class="online-tests-promo__eyebrow"><?php echo esc_html( $section['eyebrow'] ); ?></span>
                    <?php endif; ?>

                    <?php if ( ! empty( $section['title'] ) ) : ?>
                        <h2 class="online-tests-promo__title"><?php echo esc_html( $section['title'] ); ?></h2>
                    <?php endif; ?>

                    <?php if ( ! empty( $section['description'] ) ) : ?>
                        <p class="online-tests-promo__description"><?php echo esc_html( $section['description'] ); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ( ! empty( $section['button_text'] ) && ! empty( $section['button_url'] ) ) : ?>
                <a class="btn-primary btn-black btn-icon online-tests-promo__button" href="<?php echo esc_url( $section['button_url'] ); ?>">
                    <?php echo esc_html( $section['button_text'] ); ?>
                </a>
            <?php elseif ( ! empty( $section['button_text'] ) ) : ?>
                <span class="btn-primary btn-black btn-icon online-tests-promo__button"><?php echo esc_html( $section['button_text'] ); ?></span>
            <?php endif; ?>
        </div>
    </section>
    <?php

    return ob_get_clean();
}
add_shortcode( 'home_online_tests_promo', 'render_home_online_tests_promo_shortcode' );

  //Ako to funguje? How it works
  function render_benefity_slider_shortcode() {
      $page_id = get_queried_object_id();
      $benefits_label = $page_id ? get_field('benefits_block_label', $page_id) : '';
      $benefits_title = $page_id ? get_field('benefits_block_title', $page_id) : '';
      $benefits_intro_text = $page_id ? get_field('benefits_block_intro_text', $page_id) : '';

      $args = array(
          'post_type' => 'benefity',
          'posts_per_page' => -1,
          'orderby' => 'menu_order',
          'order' => 'ASC',
      );

      $query = new WP_Query($args);
      $total_slides = (int) $query->post_count;

      if (!$query->have_posts()) {
          return '<p>Žiadne benefity neboli nájdené.</p>';
      }

      ob_start();
      ?>
      <div class="benefits-block">
        <div class="benefits-titles">
          <div class="benefits-titles__main">
            <span class="label"><?php echo esc_html($benefits_label ?: 'Čo potrebujete vedieť?'); ?></span>
            <h2><?php echo esc_html($benefits_title ?: 'Ako to funguje?'); ?></h2>
          </div>

          <?php if ($benefits_intro_text): ?>
            <div class="benefits-titles__text p-24"><?php echo wp_kses_post($benefits_intro_text); ?></div>
          <?php endif; ?>
        </div>

      <div class="benefits-slider">
          <?php while ($query->have_posts()): $query->the_post(); ?>
              <?php
                  $detail_photo = get_field('detail-photo');
                  $thumb_url = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'large') : '';

                  $data_detail = $detail_photo ? esc_url($detail_photo['url']) : '';
                  $data_thumb = esc_url($thumb_url);

                  $active_class = '';
                  $initial_bg = $data_detail ?: $data_thumb;
                  $slide_number = str_pad((string) ($query->current_post + 1), 2, '0', STR_PAD_LEFT);
                  $slide_total = str_pad((string) $total_slides, 2, '0', STR_PAD_LEFT);
              ?>
              <div
                class="benefit-slide <?php echo $active_class; ?>"
                style="background-image: url('<?php echo $initial_bg; ?>'); background-size: cover; background-position: center;"
                data-detail="<?php echo $data_detail; ?>"
                data-thumb="<?php echo $data_thumb; ?>"
                data-step="<?php echo esc_attr($slide_number); ?>"
                data-total="<?php echo esc_attr($slide_total); ?>"
              >
                  <div class="benefit-slide-content">
                      <h3><?php the_title(); ?></h3>
                      <p><?php echo esc_html(get_the_excerpt()); ?></p>
                  </div>

                  <div class="benefit-slide__counter" aria-hidden="true">
                      <span class="benefit-slide__counter-current"><?php echo esc_html($slide_number); ?></span>
                      <span class="benefit-slide__counter-total"><?php echo esc_html($slide_total); ?></span>
                  </div>
              </div>
          <?php endwhile; ?>
          <?php wp_reset_postdata(); ?>
      </div>

      <div class="benefits-slider-footer">
          <div class="benefits-slider-footer__arrows slider-shared__arrows">
              <button type="button" class="benefits-slider-prev slider-shared__arrow slider-shared__arrow--prev" aria-label="Predchádzajúci krok"></button>
              <button type="button" class="benefits-slider-next slider-shared__arrow slider-shared__arrow--next" aria-label="Nasledujúci krok"></button>
          </div>

          <div class="benefits-slider-footer__progress" aria-hidden="true">
              <span></span>
          </div>
      </div>
  </div>

    <?php
    return ob_get_clean();
}
add_shortcode('benefity_slider', 'render_benefity_slider_shortcode');


function render_package_products_shortcode() {
    // Získame ACF polia z aktuálnej stránky
    $page_id = get_queried_object_id();
    $package_label = get_field('choose_package_label', $page_id);
    $package_title = get_field('choose_package_title', $page_id);
    $package_text = get_field('choose_package_text', $page_id);

    // Query produkty
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'tax_query'      => array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => array('package'),
                'operator' => 'IN',
            ),
        ),
    );

    $query = new WP_Query($args);
    $query->posts = array_reverse($query->posts);

    if (!$query->have_posts()) {
        return '<p>Žiadne balíčky neboli nájdené.</p>';
    }

    ob_start();
    ?>

    <div class="package-products-header">
        <?php if ($package_label): ?>
            <span class="btn-primary label"><?php echo esc_html($package_label); ?></span>
        <?php endif; ?>

        <?php if ($package_title): ?>
            <h2 class="package-products__title"><?php echo esc_html($package_title); ?></h2>
        <?php endif; ?>

        <?php if ($package_text): ?>
            <div class="p-24"><?php echo wp_kses_post($package_text); ?></div>
        <?php endif; ?>
    </div>

    <div class="package-products">
        <?php while ($query->have_posts()) : $query->the_post(); ?>
            <?php
                global $product;

                $thumb_url = get_the_post_thumbnail_url(get_the_ID(), 'large');
                $display_title = get_the_title();
                if ( function_exists( 'mb_strtoupper' ) && function_exists( 'mb_strtolower' ) && function_exists( 'mb_convert_case' ) ) {
                    if ( $display_title === mb_strtoupper( $display_title, 'UTF-8' ) ) {
                        $display_title = mb_convert_case( mb_strtolower( $display_title, 'UTF-8' ), MB_CASE_TITLE, 'UTF-8' );
                    }
                }
                $purpose = get_field('purpose');
                $short_desc = get_the_excerpt();
                $price = $product->get_price_html();
                $tags = get_the_terms(get_the_ID(), 'product_tag');
                $first_tag = $tags && !is_wp_error($tags) ? $tags[0]->name : null;

                $special_background = get_field('special_background');
                $special_class = $special_background ? ' special-color' : '';
                $background_style = $special_background ? 'style="background-color:' . esc_attr($special_background) . ';"' : '';
                $permalink = get_permalink();
            ?>
            <div class="package-product<?php echo esc_attr($special_class); ?>" <?php echo $background_style; ?>>
                <div class="package-product__top">
                    <?php if ($first_tag): ?>
                        <span class="package-product__tag btn-primary"><?php echo esc_html($first_tag); ?></span>
                    <?php endif; ?>

                    <?php if ($purpose): ?>
                        <span class="package-product__purpose">
                            <?php echo esc_html($purpose); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <?php if ($thumb_url): ?>
                    <a href="<?php echo esc_url($permalink); ?>" class="package-product__image-link" aria-label="Zobraziť <?php the_title_attribute(); ?>">
                        <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php the_title_attribute(); ?>" class="package-product__bg-img" loading="lazy" />
                    </a>
                <?php endif; ?>

                <div class="package-product__content">
                    <a href="<?php echo esc_url($permalink); ?>" class="package-product__link">
                        <h3><?php echo esc_html( $display_title ); ?></h3>

                    </a>
                </div>

                <div class="package-product__bottom">
                    <h3 class="package-product__price">
                        <?php echo wp_kses_post(strip_tags($price)); ?> <span class="p-24">jednorázovo</span>
                    </h3>
                    <a href="<?php echo esc_url($permalink); ?>" class="btn-circle-icon-black" aria-label="Zobraziť produkt"></a>
                </div>
            </div>
        <?php endwhile; ?>
        <?php wp_reset_postdata(); ?>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode('package_products', 'render_package_products_shortcode');



function render_partners_shortcode() {
    $args = array(
        'post_type'      => 'partners',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    );

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return '';
    }

    ob_start(); ?>
    <div class="partners-container">
        <span class="label">Dôvera v praxi</span>
        <h2>Firmy a inštitúcie, ktoré nám dôverujú</h2>
        <div class="partners-wrapper">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <?php if (has_post_thumbnail()) : ?>
                    <div class="partner">
                        <?php the_post_thumbnail('medium'); ?>
                    </div>
                <?php endif; ?>
            <?php endwhile; ?>
            <?php wp_reset_postdata(); ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('partners_list', 'render_partners_shortcode');



function shortcode_all_posts_slider() {
    // Získať ACF polia zo stránky "Domov"
    $home_page = get_page_by_path('domov');
    $home_id = $home_page ? $home_page->ID : null;

    $posts_label = get_field('posts-label', $home_id);
    $posts_title = get_field('posts_title', $home_id);

    ob_start();

    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    );

    $query = new WP_Query($args);
    ?>

    <div class="posts-block">
        <div class="posts-header">
            <div class="posts-header__main">
                <?php if ($posts_label): ?>
                    <span class="btn-primary btn-white btn-small label"><?php echo esc_html($posts_label); ?></span>
                <?php endif; ?>
                <?php if ($posts_title): ?>
                    <h2 class="posts-title"><?php echo esc_html($posts_title); ?></h2>
                <?php endif; ?>
            </div>
            <a href="/blog" class="btn-primary btn-white btn-icon">Pozrieť všetky</a>
        </div>

        <?php if ($query->have_posts()) : ?>
            <div class="all-posts-slider">
                <?php while ($query->have_posts()) : $query->the_post();
                    $image_url = get_the_post_thumbnail_url(get_the_ID(), 'large');
                    $categories = get_the_category();
                    ?>
                    <div class="post-item">
                        <article class="post-card">
                            <a href="<?php the_permalink(); ?>" class="post-card__image-link" aria-label="<?php the_title_attribute(); ?>">
                                <?php if (!empty($categories)) : ?>
                                    <span class="post-card__category btn-primary btn-white btn-small"><?php echo esc_html($categories[0]->name); ?></span>
                                <?php endif; ?>

                                <?php if ($image_url) : ?>
                                    <img class="post-card__image" src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
                                <?php endif; ?>
                            </a>

                            <div class="post-card__content">
                                <div class="post-card__bottom">
                                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                    <a class="btn-circle-icon-black" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>"></a>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endwhile; ?>
            </div>
            <div class="posts-slider-footer">
                <div class="posts-slider-footer__arrows slider-shared__arrows">
                    <button type="button" class="posts-slider-prev slider-shared__arrow slider-shared__arrow--prev" aria-label="Predchádzajúci článok"></button>
                    <button type="button" class="posts-slider-next slider-shared__arrow slider-shared__arrow--next" aria-label="Nasledujúci článok"></button>
                </div>
                <div class="posts-slider-footer__progress">
                    <span></span>
                </div>
            </div>
        <?php else : ?>
            <p>No posts found.</p>
        <?php endif; ?>

    </div>

    <?php
    wp_reset_postdata();
    return ob_get_clean();
}

add_shortcode('all_posts_slider', 'shortcode_all_posts_slider');


//title banner / hero banner PRE FIRMY
function render_page_banner_shortcode() {
    // Načítanie hodnôt z ACF
    $banner_image = get_field('banner-image');
    $banner_main_title = get_field('banner-main-title');
    $banner_main_text = get_field('banner-main-text');
    $banner_boxes_main_text = get_field('banner-boxes-main-text');
    $banner_boxes_image = get_field('banner-boxex-image');
    $hero_btn_text = get_field('hero_btn_text');
    $hero_btn_link = get_field('hero_btn_link');

    // Inline background style
    $background_style = '';
    if ($banner_image && isset($banner_image['url'])) {
        $background_style = 'style="background-image: url(' . esc_url($banner_image['url']) . '); background-size: cover; background-position: center;"';
    }

    ob_start();
    ?>
    <div class="title-banner page-banner" <?php echo $background_style; ?>>
      <div class="title-banner-info">

        <div class="title-banner-info__main">
            <div class="box-img mobile-box-img">
              <?php if ($banner_boxes_image): ?>
                  <img src="<?php echo esc_url($banner_boxes_image['url']); ?>" alt="<?php echo esc_attr($banner_boxes_image['alt']); ?>">
              <?php endif; ?>
            </div>
            <?php if ($banner_main_title): ?>
                <h1><?php echo esc_html($banner_main_title); ?></h1>
            <?php endif; ?>

            <?php if ($banner_main_text): ?>
                <p><?php echo esc_html($banner_main_text); ?></p>
            <?php endif; ?>
            <a class="btn-primary btn-icon btn-white" href="<?php echo esc_html($hero_btn_link); ?>"><?php echo esc_html($hero_btn_text); ?></a>
        </div>
      </div>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('page_banner', 'render_page_banner_shortcode');


function custom_collaboration_shortcode() {
	ob_start();

	// Získanie hodnôt ACF polí
	$title = get_field('collaboration_title');
	$text = get_field('collaboration_text');
	?>

	<div class="collaboration-container">
		<!-- Prvý element -->
		<div class="collaboration-intro">
			<span class="btn-primary label">Priebeh spolupráce</span>
      <h2><?php echo strip_tags( wp_kses_post($title), '<strong><em><span><br><a>' ); ?></h2>


			<div class="collaboration-text">
				<?php echo wp_kses_post($text); ?>
			</div>
		</div>

		<!-- Druhý element – FAQ sekcia -->
		<div class="collaboration-faqs">
      <?php for ( $i = 1; $i <= 8; $i++ ) :
      	$question = get_field("collaboration_question_$i");
      	$answer   = get_field("collaboration_answer_$i");
      	$image    = get_field("collaboration_img_$i");
      	$button   = get_field("collaboration_btn_$i");

      	if ( empty($question) || empty($answer) ) continue;

      	$number = str_pad($i, 2, '0', STR_PAD_LEFT); // 01, 02, ...
      	$is_first = ($i === 1) ? ' open' : ''; // otvorený prvý
      ?>
      	<div class="faq-item<?php echo $is_first; ?>">
      		<button class="faq-question" type="button">
      			<span class="faq-number"><?php echo $number; ?>.</span> <?php echo esc_html($question); ?>
      		</button>
      		<div class="faq-answer"<?php echo $is_first ? ' style="display:flex"' : ''; ?>>
      			<div class="faq-answer--info">
      				<?php echo wpautop( wp_kses_post($answer) ); ?>
      				<?php if ( !empty($button) ) : ?>
      					<a href="/kontakt" class="btn-primary btn-icon btn-black">
      						<?php echo esc_html($button); ?>
      					</a>
      				<?php endif; ?>
      			</div>

      			<?php if ( $image ) : ?>
      				<div class="faq-image">
      					<img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" />
      				</div>
      			<?php endif; ?>
      		</div>
      	</div>
      <?php endfor; ?>

		</div>
	</div>

	<?php
	return ob_get_clean();
}
add_shortcode( 'collaboration_block', 'custom_collaboration_shortcode' );


function dron_industries_shortcode() {
    ob_start();
    $post_id = get_the_ID();
    ?>
    <div class="dron-industries-section">
        <span class="btn-primary label">Oblasti nasadenia</span>
        <h2>Využitie dronov v rôznych odvetviach</h2>
        <div class="industries-wrapper">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <?php
                $group = get_field('industry_' . $i, $post_id);

                if (is_array($group)) {
                    $image = $group['industry_image'] ?? null;
                    $title = $group['industry_title'] ?? '';
                    $text  = $group['industry_text'] ?? '';

                    // 💡 Vyriešime ak je to ID, array alebo string
                    $image_url = '';
                    if (is_array($image) && isset($image['url'])) {
                        $image_url = $image['url'];
                    } elseif (is_int($image)) {
                        $image_url = wp_get_attachment_url($image);
                    } elseif (is_string($image)) {
                        $image_url = $image;
                    }

                    $extra_class = ($i >= 4) ? ' bigger_industry' : '';
                ?>
                    <div class="industry<?php echo esc_attr($extra_class); ?>"
                         <?php if ($image_url): ?>
                             style="background-image: url('<?php echo esc_url($image_url); ?>');"
                         <?php endif; ?>>
                         <div class="industry--content">
                           <h3><?php echo esc_html($title); ?></h3>
                           <p><?php echo esc_html($text); ?></p>
                         </div>
                    </div>
                <?php } ?>
            <?php endfor; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('dron_industries', 'dron_industries_shortcode');


function sluzby_shortcode() {
    ob_start();

    $services_term = get_term_by( 'slug', 'sluzby', 'product_cat' );

    if ( ! $services_term instanceof WP_Term ) {
        $services_term = get_term_by( 'name', 'Služby', 'product_cat' );
    }

    $is_product_listing = $services_term instanceof WP_Term;
    $args = $is_product_listing
        ? array(
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'menu_order title',
            'order'          => 'ASC',
            'tax_query'      => array(
                array(
                    'taxonomy'         => 'product_cat',
                    'field'            => 'term_id',
                    'terms'            => array( (int) $services_term->term_id ),
                    'include_children' => true,
                ),
            ),
        )
        : array(
            // Local fallback until the production Služby category is synced.
            'post_type'      => 'sluzby',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        );

    $sluzby_query = new WP_Query( $args );

    if ( $sluzby_query->have_posts() ) : ?>
        <div class="sluzby-section">
            <span class="btn-primary label">Naše služby</span>
            <div class="sluzby-wrapper">
                <?php while ( $sluzby_query->have_posts() ) : $sluzby_query->the_post(); ?>
                    <?php if ( $is_product_listing ) : ?>
                        <a class="sluzba-item" href="<?php echo esc_url( get_permalink() ); ?>">
                    <?php else : ?>
                        <div class="sluzba-item">
                    <?php endif; ?>
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="sluzba-image">
                                <?php the_post_thumbnail(); ?>
                            </div>
                        <?php endif; ?>
                        <h3 class="sluzba-title"><?php the_title(); ?></h3>
                    <?php echo $is_product_listing ? '</a>' : '</div>'; ?>
                <?php endwhile; ?>
            </div>
        </div>
    <?php
    endif;

    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('sluzby_vypis', 'sluzby_shortcode');

function kontakt_shortcode() {
    ob_start();
    ?>
    <div class="kontakt-section">
        <span class="btn-primary label">Kontakt</span>
        <h2>Ozvite sa nám</h2>
        <p class="kontakt-text">
            Máte otázky ku kurzom, spolupráci alebo legislatíve? Radi vám poradíme a pomôžeme urobiť prvý krok.
        </p>
        <div class="kontakt-form">
            <?php echo do_shortcode('[contact-form-7 id="117a4a5" title="Kontaktný formulár 1"]'); ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('kontakt_blok', 'kontakt_shortcode');



function render_didnt_find_banner_shortcode() {
	// Temporarily disabled site-wide (Elementor + PHP inserts).
	return '';

	/*
	$label  = get_option('banner_label');
	$title  = get_option('banner_title');
	$text   = get_option('banner_text');
	$image  = get_option('banner_image');

	// Default fallback
	if (empty($image)) {
		return ''; // nič nezobrazí, ak nie je nastavený obrázok
	}

	ob_start();
	?>

	<section class="didnt-find-banner" style="background-image: url('<?php echo esc_url($image); ?>'); background-size: cover; background-position: center;">
		<div class="banner-container" >
			<?php if ($label): ?>
				<p class="btn-primary label">
					<?php echo esc_html($label); ?>
				</p>
			<?php endif; ?>

			<?php if ($title): ?>
				<h2 class="banner-title">
					<?php echo esc_html($title); ?>
				</h2>
			<?php endif; ?>

			<?php if ($text): ?>
				<p class="banner-text">
					<?php echo esc_html($text); ?>
				</p>
			<?php endif; ?>

			<a href="/balicky/" class="btn-primary btn-black btn-icon">
				Pozrieť balíčky
			</a>
		</div>
	</section>

	<?php
	return ob_get_clean();
	*/
}
add_shortcode('didnt_find_banner', 'render_didnt_find_banner_shortcode');



function render_product_faqs_shortcode() {
	if ( ! is_singular( 'product' ) ) {
		return ''; // zobrazuj len na stránke produktu
	}

	$output = '<div class="product-faqs">';

	for ( $i = 1; $i <= 6; $i++ ) {
		$faq = get_field( 'faq_' . $i );

		if ( $faq && ! empty( $faq['question'] ) && ! empty( $faq['answer'] ) ) {
			$output .= '<div class="faq-item">';
			$output .= '<h4 class="faq-question">' . esc_html( $faq['question'] ) . '</h4>';
			$output .= '<div class="faq-answer">' . wp_kses_post( wpautop( $faq['answer'] ) ) . '</div>';
			$output .= '</div>';
		}
	}

	$output .= '</div>';

	return $output;
}
add_shortcode( 'product_faqs', 'render_product_faqs_shortcode' );



function render_product_note_shortcode() {
	if ( ! is_singular( 'product' ) ) {
		return ''; // zobraz len na stránke produktu
	}

	$note = get_field( 'product_note' );

	if ( empty( $note ) ) {
		return ''; // ak pole nie je vyplnené
	}

	return '<div class="product-note"><p>' . esc_html( $note ) . '</p></div>';
}
add_shortcode( 'product_note', 'render_product_note_shortcode' );





//block with products
function related_products_shortcode() {
    // Získať ACF polia zo stránky "Domov"
    $home_page = get_page_by_path('domov');
    $home_id = $home_page ? $home_page->ID : null;

    $courses_label = get_field('courses-label', $home_id);
    $courses_title = get_field('courses-title', $home_id);
    $courses_text  = get_field('courses-text', $home_id);
    $section_id = 'related-courses-slider-' . wp_unique_id();

    ob_start();
    ?>
    <section id="<?php echo esc_attr( $section_id ); ?>" class="courses-block home-courses-slider discover-all-products">
        <div class="courses-header home-courses-slider__header">
          <div>
            <h2 class="courses-title home-courses-slider__title">Objavte ďalšie produkty</h2>
            <?php if ($courses_text): ?>
                <div class="courses-text-mobile">
                    <?php echo wpautop(wp_kses_post($courses_text)); ?>
                </div>
            <?php endif; ?>

          </div>
            <a href="/sluzby" class="home-courses-slider__all-link btn-primary btn-black btn-icon">Pozrieť všetky</a>
        </div>


        <div class="all-related-products home-courses-slider__track">
            <?php echo aprop_render_all_products_excluding_drones_items(); ?>
        </div>

        <?php if ($courses_text): ?>
            <div class="courses-text">
                <?php echo wpautop(wp_kses_post($courses_text)); ?>
                <a href="/sluzby" class="btn-primary btn-black btn-icon btn-mobile">Pozrieť všetky</a>
            </div>
        <?php endif; ?>
        <div class="home-courses-slider__footer">
            <div class="home-courses-slider__arrows">
                <button type="button" class="modern-agro-slider__arrow modern-agro-slider__arrow--prev" aria-label="Predchádzajúci kurz"></button>
                <button type="button" class="modern-agro-slider__arrow modern-agro-slider__arrow--next" aria-label="Nasledujúci kurz"></button>
            </div>
            <div class="home-courses-slider__progress"><span></span></div>
        </div>
        <script>
        jQuery(function ($) {
            var section = $('#<?php echo esc_js( $section_id ); ?>');
            var track = section.find('.all-related-products.home-courses-slider__track');

            if (!track.length || track.hasClass('slick-initialized') || typeof $.fn.slick !== 'function') {
                return;
            }

            if (track.children().length <= 1) {
                section.find('.home-courses-slider__footer').hide();
                return;
            }

            function updateProgress(slick, currentSlide) {
                var slidesToShow = slick.options.slidesToShow;

                if (typeof slidesToShow !== 'number') {
                    slidesToShow = 1;
                }

                var totalSlides = slick.slideCount;
                var current = currentSlide || 0;
                var maxSteps = Math.max(totalSlides - Math.ceil(slidesToShow), 1);
                var progress = totalSlides <= slidesToShow ? 100 : Math.max((current / maxSteps) * 100, 10);

                section.find('.home-courses-slider__progress span').css('width', progress + '%');
            }

            track.on('init reInit afterChange', function (event, slick, currentSlide) {
                updateProgress(slick, currentSlide);
            });

            track.slick({
                slidesToShow: 3.8,
                slidesToScroll: 1,
                infinite: false,
                arrows: true,
                dots: false,
                prevArrow: section.find('.modern-agro-slider__arrow--prev'),
                nextArrow: section.find('.modern-agro-slider__arrow--next'),
                responsive: [
                    {
                        breakpoint: 1100,
                        settings: {
                            slidesToShow: 2.1
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 1.1
                        }
                    }
                ]
            });
        });
        </script>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('related_products_shortcode', 'related_products_shortcode');

function kurz_instructions_shortcode() {
	if ( ! is_singular('product') ) return;

	$fields = [
		'instruction_1',
		'instruction_2',
		'instruction_3',
		'instruction_4',
		'instruction_5',
		'instruction_6'
	];

	$output_items = [];

	foreach ( $fields as $field ) {
		$content = get_field($field);
		if ( !empty($content) ) {
			$output_items[] = '<div class="instruction-item">' . apply_filters('the_content', $content) . '</div>';
		}
	}

	if ( empty($output_items) ) return ''; // nič sa nevygeneruje

	$output  = '<div class="kurz-instructions">';
	$output .= implode('', $output_items);
	$output .= '</div>';

	return $output;
}
add_shortcode('kurz_instructions', 'kurz_instructions_shortcode');



//related articles for article detail
function related_articles_slider() {
    // Získať ACF polia zo stránky "Domov"
    $home_page = get_page_by_path('domov');
    $home_id = $home_page ? $home_page->ID : null;

    $posts_label = get_field('posts-label', $home_id);
    $posts_title = get_field('posts_title', $home_id);

    ob_start();

    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    );

    $query = new WP_Query($args);
    ?>

    <div class="posts-block">
        <div class="posts-header">
            <div>
                <h2 class="posts-title">Podobné články</h2>
            </div>
            <a href="/blog" class="btn-primary btn-white btn-icon">Pozrieť všetky</a>
        </div>

        <?php if ($query->have_posts()) : ?>
            <div class="all-posts-slider">
                <?php while ($query->have_posts()) : $query->the_post();
                    $image_url = get_the_post_thumbnail_url(get_the_ID(), 'large');
                    $categories = get_the_category();
                    ?>
                    <div class="post-item" style="background-image: url('<?php echo esc_url($image_url); ?>');">
                        <div class="post-content">
                            <?php if (!empty($categories)) : ?>
                                <span class="btn-primary btn-white btn-small"><?php echo esc_html($categories[0]->name); ?></span>
                            <?php endif; ?>

                            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                            <a class="btn-circle-icon-white" href="<?php the_permalink(); ?>"></a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <div class="slider-progress">
                <div class="slider-progress-bar"></div>
            </div>
        <?php else : ?>
            <p>No posts found.</p>
        <?php endif; ?>

    </div>

    <?php
    wp_reset_postdata();
    return ob_get_clean();
}

add_shortcode('related_articles_slider', 'related_articles_slider');




function shortcode_courses_table($atts) {
    $post_id = get_the_ID();

    // Cesty k obrázkom
    $yes_icon = get_stylesheet_directory_uri() . '/images/aprop-yes.svg';
    $no_icon  = get_stylesheet_directory_uri() . '/images/aprop-close.svg';

    // Začiatok tabuľky
    $html = '<table border="1" cellpadding="5" cellspacing="0" class="courses-table">';
    $html .= '<thead><tr><th></th><th>Starter</th><th>Advanced</th><th>Pro</th></tr></thead>';
    $html .= '<tbody>';

    for ($i = 1; $i <= 10; $i++) {
        $prefix = "course_$i";

        $course_name = get_field($prefix . '_course_name', $post_id);
        $starter     = get_field($prefix . '_starter', $post_id);
        $advanced    = get_field($prefix . '_advanced', $post_id);
        $pro         = get_field($prefix . '_pro', $post_id);

        if (!$course_name) continue;

        $html .= '<tr>';
        $html .= '<td>' . esc_html($course_name) . '</td>';
        $html .= '<td><img src="' . ($starter ? $yes_icon : $no_icon) . '" alt="Starter" /></td>';
        $html .= '<td><img src="' . ($advanced ? $yes_icon : $no_icon) . '" alt="Advanced" /></td>';
        $html .= '<td><img src="' . ($pro ? $yes_icon : $no_icon) . '" alt="Pro" /></td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table>';

    return $html;
}
add_shortcode('shortcode_courses_table', 'shortcode_courses_table');



function shortcode_which_package($atts) {
    $post_id = get_the_ID();

    // 1) Hlavný titulok
    $main_title = get_field('package_main_title', $post_id);

    $html = '<div class="which-package">';

    if ($main_title) {
        $html .= '<h2 class="which-package-title">' . esc_html($main_title) . '</h2>';
    }

    // 2) Jednotlivé balíčky
    for ($i = 1; $i <= 3; $i++) {
        $prefix    = "package_$i";
        $image_id  = get_field($prefix . '_image',     $post_id);
        $title     = get_field($prefix . '_title',     $post_id);
        $subtitle  = get_field($prefix . '_subtitle',  $post_id);
        $text      = get_field($prefix . '_text',      $post_id);
        $subtitle2 = get_field($prefix . '_subtitle_2',$post_id);
        $text2     = get_field($prefix . '_text_2',    $post_id);

        // Preskočí, ak nie je vyplnené aspoň jedno z hlavných polí
        if (! $title && ! $image_id && ! $text) {
            continue;
        }

        $html .= '<div class="package">';

        // Obrázok cez ID
        if ($image_id) {
            $img_url = wp_get_attachment_image_url($image_id, 'full');
            $img_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
            if ($img_url) {
                $html .= '<div class="package-image">';
                $html .= '<img src="' . esc_url($img_url) . '" alt="' . esc_attr($img_alt) . '">';
                $html .= '</div>';
            }
        }

        if ($title) {
            $html .= '<h3 class="package-title">' . esc_html($title) . '</h3>';
        }

        if ($subtitle) {
            $html .= '<h4 class="package-subtitle">' . esc_html($subtitle) . '</h4>';
        }

        if ($text) {
            $html .= '<div class="package-text">' . apply_filters('the_content', $text) . '</div>';
        }

        if ($subtitle2) {
            $html .= '<h4 class="package-subtitle-2">' . esc_html($subtitle2) . '</h4>';
        }

        if ($text2) {
            $html .= '<div class="package-text-2">' . apply_filters('the_content', $text2) . '</div>';
        }

        $html .= '</div>'; // .package
    }

    $html .= '</div>'; // .which-package

    return $html;
}
add_shortcode('shortcode_which_package', 'shortcode_which_package');

function aprop_get_home_modern_agro_products( $page_id ) {
    $selected_products = (array) get_field( 'home_modern_agro_products', $page_id );
    $products = array();

    foreach ( $selected_products as $selected_product ) {
        if ( ! $selected_product instanceof WP_Post ) {
            continue;
        }

        $products[] = $selected_product;
    }

    return $products;
}

function aprop_get_home_modern_agro_slider_data() {
    $page_id = aprop_get_homepage_id();

    if ( ! $page_id ) {
        return array(
            'title' => '',
            'button_text' => '',
            'button_url' => '',
            'products' => array(),
        );
    }

    return array(
        'title' => get_field( 'home_modern_agro_title', $page_id ),
        'button_text' => get_field( 'home_modern_agro_button_text', $page_id ),
        'button_url' => get_field( 'home_modern_agro_button_url', $page_id ),
        'products' => aprop_get_home_modern_agro_products( $page_id ),
    );
}

function render_home_modern_agro_slider_shortcode() {
    $section = aprop_get_home_modern_agro_slider_data();

    if ( empty( $section['products'] ) ) {
        return '';
    }

    $section_id = 'modern-agro-slider-' . wp_unique_id();

    ob_start();
    ?>
    <section id="<?php echo esc_attr( $section_id ); ?>" class="modern-agro-tabs modern-agro-slider">
        <div class="modern-agro-tabs__header modern-agro-slider__header">
            <?php if ( ! empty( $section['title'] ) ) : ?>
                <h2 class="modern-agro-tabs__title"><?php echo esc_html( $section['title'] ); ?></h2>
            <?php endif; ?>

            <?php if ( ! empty( $section['button_text'] ) && ! empty( $section['button_url'] ) ) : ?>
                <a class="modern-agro-slider__all-link btn-primary btn-black btn-icon" href="<?php echo esc_url( $section['button_url'] ); ?>">
                    <?php echo esc_html( $section['button_text'] ); ?>
                </a>
            <?php endif; ?>
        </div>

        <div class="modern-agro-slider__track">
            <?php foreach ( $section['products'] as $selected_product ) : ?>
                <?php
                    if ( ! $selected_product instanceof WP_Post ) {
                        continue;
                    }

                    $product = wc_get_product( $selected_product->ID );

                    if ( ! $product ) {
                        continue;
                    }
                ?>
                <div class="modern-agro-slider__slide">
                    <?php echo aprop_render_drone_product_card( $selected_product->ID, array( 'title_tag' => 'h3' ) ); ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="modern-agro-slider__footer">
            <div class="modern-agro-slider__arrows">
                <button type="button" class="modern-agro-slider__arrow modern-agro-slider__arrow--prev" aria-label="Predchádzajúci produkt"></button>
                <button type="button" class="modern-agro-slider__arrow modern-agro-slider__arrow--next" aria-label="Nasledujúci produkt"></button>
            </div>
            <div class="modern-agro-slider__progress"><span></span></div>
        </div>

        <script>
        jQuery(function ($) {
            var section = $('#<?php echo esc_js( $section_id ); ?>');
            var track = section.find('.modern-agro-slider__track');

            if (!track.length || track.hasClass('slick-initialized') || typeof $.fn.slick !== 'function') {
                return;
            }

            if (track.children().length <= 1) {
                section.find('.modern-agro-slider__footer').hide();
                return;
            }

            function updateProgress(slick, currentSlide) {
                var slidesToShow = slick.options.slidesToShow;

                if (typeof slidesToShow !== 'number') {
                    slidesToShow = 1;
                }

                var totalSlides = slick.slideCount;
                var current = currentSlide || 0;
                var maxSteps = Math.max(totalSlides - Math.ceil(slidesToShow), 1);
                var progress = totalSlides <= slidesToShow ? 100 : Math.max((current / maxSteps) * 100, 10);

                section.find('.modern-agro-slider__progress span').css('width', progress + '%');
            }

            track.on('init reInit afterChange', function (event, slick, currentSlide) {
                updateProgress(slick, currentSlide);
            });

            track.slick({
                slidesToShow: 3.8,
                slidesToScroll: 1,
                infinite: false,
                arrows: true,
                dots: false,
                prevArrow: section.find('.modern-agro-slider__arrow--prev'),
                nextArrow: section.find('.modern-agro-slider__arrow--next'),
                responsive: [
                    {
                        breakpoint: 1100,
                        settings: {
                            slidesToShow: 2.1
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 1.1
                        }
                    }
                ]
            });
        });
        </script>
    </section>
    <?php

    return ob_get_clean();
}
add_shortcode( 'home_modern_agro_slider', 'render_home_modern_agro_slider_shortcode' );
add_shortcode( 'home_modern_agro_tabs', 'render_home_modern_agro_slider_shortcode' );
