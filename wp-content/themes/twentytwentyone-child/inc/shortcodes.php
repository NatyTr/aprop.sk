
<?php


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
function render_all_products_shortcode() {
    // Nastavenie dotazu
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'tax_query'      => array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => array('package', 'polnohospodarske-drony'), // <- kategórie, ktoré nechceme v pôvodnom výpise
                'operator' => 'NOT IN',
            ),
        ),
    );


    $products = new WP_Query($args);

    if (!$products->have_posts()) {
        return '<p>Žiadne produkty neboli nájdené.</p>';
    }

    ob_start();
    ?>
    <div class="products-grid">
        <?php while ($products->have_posts()): $products->the_post(); global $product; ?>
            <div class="product-card">
                <a href="<?php the_permalink(); ?>">
                    <?php if (has_post_thumbnail()) {
                        the_post_thumbnail('medium');
                    } ?>
                </a>
                <div class="product-content">
                  <span class="price"><?php echo $product->get_price_html(); ?></span>
                  <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" class="btn-circle-icon-white">
                  </a>
                  <h4><?php the_title(); ?></h4>
                </div>
            </div>
        <?php endwhile; wp_reset_postdata(); ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('all_products', 'render_all_products_shortcode');

//block with products
function render_kurzy_blok_shortcode() {
    // Získať ACF polia zo stránky "Domov"
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
            <?php echo do_shortcode('[all_products]'); ?>
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


  //Ako to funguje? How it works
  function render_benefity_slider_shortcode() {
      $args = array(
          'post_type' => 'benefity',
          'posts_per_page' => -1,
          'orderby' => 'menu_order',
          'order' => 'ASC',
      );

      $query = new WP_Query($args);

      if (!$query->have_posts()) {
          return '<p>Žiadne benefity neboli nájdené.</p>';
      }

      ob_start();
      ?>
      <div class="benefits-block">
        <div class="benefits-titles">
          <span class="btn-primary">Čo potrebujete vedieť?</span>
          <h2>Ako to funguje?</h2>
        </div>

      <div class="benefits-slider">
          <?php while ($query->have_posts()): $query->the_post(); ?>
              <?php
                  $detail_photo = get_field('detail-photo');
                  $thumb_url = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'large') : '';

                  $data_detail = $detail_photo ? esc_url($detail_photo['url']) : '';
                  $data_thumb = esc_url($thumb_url);

                  $active_class = '';
                  $initial_bg = $query->current_post === 0 ? $data_detail : $data_thumb;
              ?>
              <div
                class="benefit-slide <?php echo $active_class; ?>"
                style="background-image: url('<?php echo $initial_bg; ?>'); background-size: cover; background-position: center;"
                data-detail="<?php echo $data_detail; ?>"
                data-thumb="<?php echo $data_thumb; ?>"
              >
                  <div class="benefit-slide-content">
                      <h3><?php the_title(); ?></h3>
                      <p><?php echo esc_html(get_the_excerpt()); ?></p>
                  </div>
                  <a href="/" class="btn-circle-icon-white"></a>
              </div>
          <?php endwhile; ?>
          <?php wp_reset_postdata(); ?>
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

    if (!$query->have_posts()) {
        return '<p>Žiadne balíčky neboli nájdené.</p>';
    }

    ob_start();
    ?>

    <div class="package-products-header">
        <?php if ($package_label): ?>
            <span class="btn-primary"><?php echo esc_html($package_label); ?></span>
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
                $purpose = get_field('purpose');
                $short_desc = get_the_excerpt();
                $price = $product->get_price_html();
                $tags = get_the_terms(get_the_ID(), 'product_tag');
                $first_tag = $tags && !is_wp_error($tags) ? $tags[0]->name : null;

                $special_icon = get_field('special_icon');
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
                            <?php if ($special_icon): ?>
                                <img src="<?php echo esc_url($special_icon['url']); ?>" alt="<?php echo esc_attr($special_icon['alt'] ?? ''); ?>" class="package-product__special-icon" />
                            <?php endif; ?>
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
                        <h3><?php the_title(); ?></h3>
                        
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
        <h2>Firmy, ktoré nám dôverujú</h2>
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
            <div>
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
      		<div class="faq-answer">
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

    $args = array(
        'post_type'      => 'sluzby',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    );

    $sluzby_query = new WP_Query($args);

    if ($sluzby_query->have_posts()) : ?>
        <div class="sluzby-section">
            <span class="btn-primary label">Naše služby</span>
            <div class="sluzby-wrapper">
                <?php while ($sluzby_query->have_posts()) : $sluzby_query->the_post(); ?>
                    <div class="sluzba-item">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="sluzba-image">
                                <?php the_post_thumbnail(); ?>
                            </div>
                        <?php endif; ?>
                        <h3 class="sluzba-title"><?php the_title(); ?></h3>
                    </div>
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

			<a href="/sluzby" class="btn-primary btn-black btn-icon">
				Pozrieť balíčky
			</a>
		</div>
	</section>

	<?php
	return ob_get_clean();
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

    ob_start();
    ?>
    <div class="courses-block discover-all-products">
        <div class="courses-header">
          <div>
            <span class="btn-primary label">Naše kurzy</span>
            <h2 class="courses-title">Objavte ďalšie produkty</h2>
            <?php if ($courses_text): ?>
                <div class="courses-text-mobile">
                    <?php echo wpautop(wp_kses_post($courses_text)); ?>
                </div>
            <?php endif; ?>

          </div>
            <a href="/sluzby" class="btn-primary btn-black btn-icon">Pozrieť všetky</a>
        </div>


        <div class="all-related-products">
            <?php echo do_shortcode('[all_products]'); ?>
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
        <div class="slider-progress">
            <div class="slider-progress-bar"></div>
        </div>
    </div>
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
