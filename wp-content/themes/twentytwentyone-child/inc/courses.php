<?php

function aprop_course_category_slugs() {
    return array(
        'legislativne-kurzy',
        'profesionalne-kurzy',
        'darcekove-poukazky',
    );
}

function aprop_course_category_terms() {
    static $terms = null;

    if ( null !== $terms ) {
        return $terms;
    }

    $terms = array();
    $found_terms = get_terms(
        array(
            'taxonomy'   => 'product_cat',
            'slug'       => aprop_course_category_slugs(),
            'hide_empty' => false,
            'orderby'    => 'include',
        )
    );

    if ( is_wp_error( $found_terms ) ) {
        return $terms;
    }

    $terms_by_slug = array();
    foreach ( $found_terms as $term ) {
        if ( $term instanceof WP_Term ) {
            $terms_by_slug[ $term->slug ] = $term;
        }
    }

    foreach ( aprop_course_category_slugs() as $slug ) {
        if ( isset( $terms_by_slug[ $slug ] ) ) {
            $terms[] = $terms_by_slug[ $slug ];
        }
    }

    return $terms;
}

function aprop_course_category_ids() {
    return array_map(
        'intval',
        wp_list_pluck( aprop_course_category_terms(), 'term_id' )
    );
}

/**
 * Returns whether a product is a course product.
 *
 * Course detail styling is based on the shared Kurzy category on production.
 * Drone products are explicitly excluded so their product detail layout and
 * specifications keep their existing design.
 */
function aprop_is_course_product( $product_id = null ) {
    $product_id = $product_id ? (int) $product_id : (int) get_the_ID();

    if ( ! $product_id ) {
        return false;
    }

    if ( function_exists( 'aprop_is_drone_product' ) && aprop_is_drone_product( $product_id ) ) {
        return false;
    }

    $course_term = get_term_by( 'slug', 'kurzy', 'product_cat' );

    if ( $course_term instanceof WP_Term ) {
        $terms = get_the_terms( $product_id, 'product_cat' );

        if ( empty( $terms ) || is_wp_error( $terms ) ) {
            return false;
        }

        foreach ( $terms as $term ) {
            if ( ! $term instanceof WP_Term ) {
                continue;
            }

            if (
                (int) $term->term_id === (int) $course_term->term_id
                || term_is_ancestor_of( $course_term->term_id, $term->term_id, 'product_cat' )
            ) {
                return true;
            }
        }

        return false;
    }

    // The local database does not contain the production Kurzy category yet.
    // Keep the local course detail preview usable until categories are synced.
    return in_array(
        $product_id,
        array( 3299, 5449, 7614, 10687, 10718 ),
        true
    );
}

function aprop_add_course_product_body_class( $classes ) {
    if ( is_singular( 'product' ) && aprop_is_course_product() ) {
        $classes[] = 'aprop-course-product';
    }

    return $classes;
}
add_filter( 'body_class', 'aprop_add_course_product_body_class' );

function aprop_course_selected_category_id() {
    $selected_id = isset( $_GET['course_category'] )
        ? absint( wp_unslash( $_GET['course_category'] ) )
        : 0;

    return in_array( $selected_id, aprop_course_category_ids(), true ) ? $selected_id : 0;
}

function aprop_course_tax_query( $selected_id = 0 ) {
    $category_ids = aprop_course_category_ids();

    if ( empty( $category_ids ) ) {
        return array();
    }

    $tax_query = array(
        array(
            'taxonomy'         => 'product_cat',
            'field'            => 'term_id',
            'terms'            => $category_ids,
            'operator'         => 'IN',
            'include_children' => false,
        ),
    );

    if ( $selected_id && in_array( $selected_id, $category_ids, true ) ) {
        $tax_query[] = array(
            'taxonomy'         => 'product_cat',
            'field'            => 'term_id',
            'terms'            => array( $selected_id ),
            'operator'         => 'IN',
            'include_children' => false,
        );
    }

    return $tax_query;
}

function aprop_course_category_count( $category_id ) {
    $query = new WP_Query(
        array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'tax_query'      => aprop_course_tax_query( (int) $category_id ),
        )
    );

    return (int) $query->found_posts;
}

function aprop_filter_courses_shop_query( $query ) {
    if ( is_admin() || ! $query instanceof WP_Query || ! $query->is_main_query() || ! aprop_is_sluzby_shop_page() ) {
        return;
    }

    $course_tax_query = aprop_course_tax_query( aprop_course_selected_category_id() );

    // Ak ešte lokálne kategórie nie sú synchronizované, listing necháme fungovať
    // pôvodným spôsobom, aby sa stránka nezobrazila prázdna.
    if ( empty( $course_tax_query ) ) {
        return;
    }

    $tax_query = (array) $query->get( 'tax_query' );
    $query->set( 'tax_query', array_merge( $tax_query, $course_tax_query ) );
}
add_action( 'pre_get_posts', 'aprop_filter_courses_shop_query', 20 );

function aprop_render_course_filter() {
    $terms = aprop_course_category_terms();
    $selected_id = aprop_course_selected_category_id();

    $shop_url = function_exists( 'wc_get_page_permalink' )
        ? wc_get_page_permalink( 'shop' )
        : get_permalink( wc_get_page_id( 'shop' ) );

    ob_start();
    ?>
    <aside id="course-products-filter" class="drone-products-filter" aria-label="Filter kurzov">
        <form method="get" class="drone-products-filter__form">
            <?php if ( ! empty( $terms ) ) : ?>
                <div class="drone-products-filter__group">
                    <h2>Kategória</h2>

                    <?php foreach ( $terms as $term ) : ?>
                        <?php $count = aprop_course_category_count( $term->term_id ); ?>
                        <label class="drone-products-filter__option">
                            <input
                                type="radio"
                                name="course_category"
                                value="<?php echo esc_attr( $term->term_id ); ?>"
                                <?php checked( $selected_id, (int) $term->term_id ); ?>
                                onchange="this.form.submit()"
                            />
                            <span class="drone-products-filter__option-label"><?php echo esc_html( $term->name ); ?></span>
                            <span class="drone-products-filter__count"><?php echo esc_html( $count ); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <a href="<?php echo esc_url( $shop_url ); ?>" class="drone-products-filter__reset">Zrušiť všetky filtre</a>
        </form>
    </aside>
    <?php

    return ob_get_clean();
}
