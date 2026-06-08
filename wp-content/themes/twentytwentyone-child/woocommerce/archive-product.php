<?php
defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

$is_drone_category_listing = false;

if ( is_product_category() && function_exists( 'aprop_drone_category_id' ) ) {
    $term = get_queried_object();

    if ( $term instanceof WP_Term ) {
        $drone_category_id = aprop_drone_category_id();
        $is_drone_category_listing = (int) $term->term_id === (int) $drone_category_id || term_is_ancestor_of( $drone_category_id, $term->term_id, 'product_cat' );
    }
}

$archive_heading = $is_drone_category_listing ? 'Poľnohospodárske drony' : 'Kurzy a služby';
?>

<div class="shop-page-wrapper" style="display: flex; gap: 30px; align-items: flex-start;">

    <!-- Sidebar -->
		<!-- Sidebar s titulkom -->
    <aside class="shop-sidebar" style="width: 25%;">
            <h1 class="show-on-mobile"><?php echo esc_html( $archive_heading ); ?></h1>
          <div class="sidebar-section" id="sidebar-section">
              <div class="sidebar-toggle" id="toggle-filter">Filtruj podľa</div>
              <div class="sidebar-content" id="sidebar-filter">
                  <form method="get" class="custom-ordering-form">
                      <?php
                      // Možnosti zoradenia – vlastné zoradenie ako pole (poradie si môžeš upraviť)
                      $orderby_options = array(
                          'title'         => 'Abecedne, A-Z',
                          'title-desc'    => 'Abecedne, Z-A',
                          'menu_order'    => 'Odporúčané',
                          'popularity'    => 'Najlepšie predávané',
                          'price'         => 'Cena, od najnižšej',
                          'price-desc'    => 'Cena, od najvyššej',
                          'date'          => 'Dátum pridania, najnovšie',
                          'date-asc'      => 'Dátum pridania, najstaršie', // vlastné (nie v štandardnom Woo)
                      );

                      // Aktuálne zoradenie
                      $current_orderby = isset($_GET['orderby']) ? $_GET['orderby'] : 'menu_order';

                      foreach ( $orderby_options as $value => $label ) {
                          $checked = $current_orderby === $value ? 'checked' : '';
                          echo '<div class="custom-ordering-option">
                              <label class="custom-ordering-option__label">
                                  <input class="custom-ordering-option__input" type="radio" name="orderby" value="' . esc_attr($value) . '" ' . $checked . ' onchange="this.form.submit()" />
                                  <span class="custom-ordering-option__text">' . esc_html($label) . '</span>
                              </label>
                          </div>';
                      }

                      // Zachovanie ostatných GET parametrov (napr. stránka, filtre...)
                      foreach ( $_GET as $key => $val ) {
                          if ( 'orderby' === $key || is_array( $val ) ) {
                              continue;
                          }
                          echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '" />';
                      }
                      ?>
                  </form>
              </div>
          </div>
      </aside>




    <!-- Hlavný obsah -->
    <div class="shop-main" style="width: 75%;">
      <h1 class="show-on-desktop"><?php echo esc_html( $archive_heading ); ?></h1>
			<div class="category-title">
				<?php
					if ( is_product_category() ) {
							$term = get_queried_object();

							if ( $term && ! is_wp_error( $term ) ) {
									echo '<h2 class="product-category-title">' . esc_html( $term->name ) . '</h2>';

									$description = term_description( $term->term_id, 'product_cat' );
									if ( ! empty( $description ) ) {
											echo '<div class="product-category-description">' . wp_kses_post( wpautop( $description ) ) . '</div>';
									}
							}
					}
					?>
			</div>


        <?php
        /**
         * Hook: woocommerce_before_main_content.
         *
         * @hooked woocommerce_output_content_wrapper - 10
         * @hooked woocommerce_breadcrumb - 20
         * @hooked WC_Structured_Data::generate_website_data() - 30
         */
        //do_action( 'woocommerce_before_main_content' );

        /**
         * Hook: woocommerce_shop_loop_header.
         *
         * @since 8.6.0
         *
         * @hooked woocommerce_product_taxonomy_archive_header - 10
         */
        do_action( 'woocommerce_shop_loop_header' );

        if ( woocommerce_product_loop() ) {

            /**
             * Hook: woocommerce_before_shop_loop.
             *
             * @hooked woocommerce_output_all_notices - 10
             * @hooked woocommerce_result_count - 20
             * @hooked woocommerce_catalog_ordering - 30
             */

            if ( $is_drone_category_listing ) {
                echo '<div class="drone-products-grid drone-products-grid--category">';
            } else {
                woocommerce_product_loop_start();
            }

            if ( wc_get_loop_prop( 'total' ) ) {
                while ( have_posts() ) {
                    the_post();

                    /**
                     * Hook: woocommerce_shop_loop.
                     */
                    do_action( 'woocommerce_shop_loop' );

                    wc_get_template_part( 'content', 'product' );
                }
            }

            if ( $is_drone_category_listing ) {
                echo '</div>';
            } else {
                woocommerce_product_loop_end();
            }

							// Nastav hodnoty podľa toho, kde sa používateľ nachádza
							$item_list_id = is_product_category() ? 'kategoria-' . get_queried_object_id() : 'shop';
							$item_list_name = is_product_category() ? single_cat_title('', false) : 'Všetky produkty';

							// Ak sa nazbierali produkty z content-product.php, odpál event
							if ( isset($GLOBALS['products_in_list']) && is_array($GLOBALS['products_in_list']) ) {
								echo '<script>
								window.dataLayer = window.dataLayer || [];
								dataLayer.push({
								  event: "view_item_list",
								  ecommerce: {
								    item_list_id: "' . esc_js($item_list_id) . '",
								    item_list_name: "' . esc_js($item_list_name) . '",
								    items: ' . json_encode($GLOBALS['products_in_list'], JSON_UNESCAPED_UNICODE) . '
								  }
								});
								</script>';
							}



            /**
             * Hook: woocommerce_after_shop_loop.
             *
             * @hooked woocommerce_pagination - 10
             */
            do_action( 'woocommerce_after_shop_loop' );

        } else {
            /**
             * Hook: woocommerce_no_products_found.
             *
             * @hooked wc_no_products_found - 10
             */
            do_action( 'woocommerce_no_products_found' );
        }

        /**
         * Hook: woocommerce_after_main_content.
         *
         * @hooked woocommerce_output_content_wrapper_end - 10
         */
        do_action( 'woocommerce_after_main_content' );
        ?>
    </div>

</div>

<?php
// Zobrazí banner "Nenašli ste?"
echo do_shortcode('[didnt_find_banner]');
?>


<script>
document.addEventListener("DOMContentLoaded", function () {
    const toggle = document.getElementById("toggle-filter");
    const section = document.getElementById("sidebar-section");

    // Pridať triedu 'active' len ak obrazovka je širšia ako 1099px
    if (window.innerWidth > 1099) {
        section.classList.add("active");
    }

    // Kliknutie na toggle
    toggle.addEventListener("click", function () {
        section.classList.toggle("active");
    });
});
</script>


<?php
get_footer( 'shop' );
