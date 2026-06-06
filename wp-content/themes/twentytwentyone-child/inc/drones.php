<?php

function aprop_drone_category_id() {
    return 211;
}

function aprop_drone_category_tree( $parent_id = null ) {
    if ( null === $parent_id ) {
        $parent_id = aprop_drone_category_id();
    }

    $terms = get_terms(
        array(
            'taxonomy'   => 'product_cat',
            'parent'     => $parent_id,
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        )
    );

    if ( is_wp_error( $terms ) ) {
        return array();
    }

    $tree = array();

    foreach ( $terms as $term ) {
        $tree[] = array(
            'term'     => $term,
            'children' => aprop_drone_category_tree( $term->term_id ),
        );
    }

    return $tree;
}

function aprop_drone_category_ids_from_tree( $tree ) {
    $ids = array();

    foreach ( $tree as $node ) {
        if ( empty( $node['term'] ) || ! ( $node['term'] instanceof WP_Term ) ) {
            continue;
        }

        $ids[] = (int) $node['term']->term_id;

        if ( ! empty( $node['children'] ) ) {
            $ids = array_merge( $ids, aprop_drone_category_ids_from_tree( $node['children'] ) );
        }
    }

    return $ids;
}

function aprop_drone_tree_has_selected_term( $node, $selected_term_id ) {
    if ( ! $selected_term_id || empty( $node['term'] ) || ! ( $node['term'] instanceof WP_Term ) ) {
        return false;
    }

    if ( (int) $node['term']->term_id === (int) $selected_term_id ) {
        return true;
    }

    if ( empty( $node['children'] ) ) {
        return false;
    }

    foreach ( $node['children'] as $child_node ) {
        if ( aprop_drone_tree_has_selected_term( $child_node, $selected_term_id ) ) {
            return true;
        }
    }

    return false;
}

function aprop_drone_filter_options() {
    return array(
        'display' => array(
            'all' => 'Všetko',
            'drony' => 'Drony',
            'prislusenstvo' => 'Príslušenstvo',
        ),
        'purpose' => array(
            'postrek' => 'Postrek',
            'rozmetanie' => 'Rozmetanie',
            'monitoring-a-snimanie' => 'Monitoring a snímanie',
            'mapovanie' => 'Mapovanie',
        ),
        'availability' => array(
            'skladom' => 'Skladom',
            'na-objednavku' => 'Na objednávku',
        ),
    );
}

function aprop_drone_sort_options() {
    return array(
        'recommended' => 'Odporúčané',
        'price_asc' => 'Cena od najnižšej',
        'price_desc' => 'Cena od najvyššej',
        'title_asc' => 'Názov A-Z',
        'newest' => 'Najnovšie',
    );
}

function aprop_drone_tax_query_from_filters( $filters, $exclude = '', $override = array() ) {
    $filters = array_merge( $filters, $override );
    $tax_query = array(
        array(
            'taxonomy'         => 'product_cat',
            'field'            => 'term_id',
            'terms'            => array( aprop_drone_category_id() ),
            'operator'         => 'IN',
            'include_children' => true,
        ),
    );

    if (
        ( 'category' !== $exclude || array_key_exists( 'category', $override ) )
        && ! empty( $filters['category'] )
    ) {
        $tax_query[] = array(
            'taxonomy'         => 'product_cat',
            'field'            => 'term_id',
            'terms'            => array( absint( $filters['category'] ) ),
            'operator'         => 'IN',
            'include_children' => true,
        );
    }

    return $tax_query;
}

function aprop_drone_meta_query_from_filters( $filters, $exclude = '', $override = array() ) {
    $filters = array_merge($filters, $override);
    $meta_query = array('relation' => 'AND');

    if ( ( 'display' !== $exclude || array_key_exists('display', $override) ) && isset($filters['display']) && 'all' !== $filters['display'] ) {
        $meta_query[] = array(
            'key'   => 'aprop_drone_display',
            'value' => $filters['display'],
        );
    }

    if ( ( 'purpose' !== $exclude || array_key_exists('purpose', $override) ) && ! empty($filters['purpose']) ) {
        $meta_query[] = array(
            'key'   => 'aprop_drone_purpose',
            'value' => $filters['purpose'],
        );
    }

    if ( ( 'availability' !== $exclude || array_key_exists('availability', $override) ) && ! empty($filters['availability']) ) {
        $meta_query[] = array(
            'key'   => 'aprop_drone_availability',
            'value' => $filters['availability'],
        );
    }

    if (
        ( 'capacity' !== $exclude || array_key_exists('capacity_max', $override) )
        && isset($filters['capacity_max'])
        && absint($filters['capacity_max']) < 100
    ) {
        $meta_query[] = array(
            'key'     => 'aprop_drone_capacity',
            'value'   => absint($filters['capacity_max']),
            'type'    => 'NUMERIC',
            'compare' => '<=',
        );
    }

    return $meta_query;
}

function aprop_drone_filter_count( $current_filters, $exclude = '', $override = array() ) {
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
        'tax_query'      => aprop_drone_tax_query_from_filters( $current_filters, $exclude, $override ),
    );

    $args['meta_query'] = aprop_drone_meta_query_from_filters($current_filters, $exclude, $override);

    $query = new WP_Query($args);

    return (int) $query->found_posts;
}

function aprop_render_drone_filter_radio( $name, $value, $label, $current, $count, $classes = '', $level = 0 ) {
    ob_start();
    ?>
    <label
        class="drone-products-filter__option<?php echo $classes ? ' ' . esc_attr( $classes ) : ''; ?>"
        style="--drone-filter-level: <?php echo esc_attr( (int) $level ); ?>;"
    >
        <input
            type="radio"
            name="<?php echo esc_attr($name); ?>"
            value="<?php echo esc_attr($value); ?>"
            <?php checked($current, $value); ?>
            onchange="this.form.submit()"
        />
        <span class="drone-products-filter__option-label"><?php echo esc_html($label); ?></span>
        <span class="drone-products-filter__count"><?php echo esc_html($count); ?></span>
    </label>
    <?php
    return ob_get_clean();
}

function aprop_render_drone_category_tree_options( $category_tree, $current_filters, $level = 0 ) {
    ob_start();

    foreach ( $category_tree as $node ) {
        if ( empty( $node['term'] ) || ! ( $node['term'] instanceof WP_Term ) ) {
            continue;
        }

        $term = $node['term'];
        $has_children = ! empty( $node['children'] );
        $selected_in_branch = aprop_drone_tree_has_selected_term( $node, $current_filters['category'] );
        $branch_id = 'drone-category-branch-' . (int) $term->term_id;

        ?><div class="drone-products-filter__tree-item<?php echo $has_children ? ' drone-products-filter__tree-item--has-children' : ''; ?>" style="--drone-filter-level: <?php echo esc_attr( (int) $level ); ?>;">
            <div class="drone-products-filter__tree-row">
                <label class="drone-products-filter__tree-select">
                    <input
                        type="radio"
                        name="drone_category"
                        value="<?php echo esc_attr( (string) $term->term_id ); ?>"
                        <?php checked( $current_filters['category'], (int) $term->term_id ); ?>
                        onchange="this.form.submit()"
                    />
                    <span class="drone-products-filter__tree-indicator" aria-hidden="true"></span>
                    <span class="screen-reader-text"><?php echo esc_html( sprintf( 'Vybrať kategóriu %s', $term->name ) ); ?></span>
                </label>

                <?php if ( $has_children ) : ?>
                    <button
                        type="button"
                        class="drone-products-filter__tree-trigger"
                        aria-expanded="<?php echo $selected_in_branch ? 'true' : 'false'; ?>"
                        aria-controls="<?php echo esc_attr( $branch_id ); ?>"
                    >
                        <span class="drone-products-filter__tree-label"><?php echo esc_html( $term->name ); ?></span>
                    </button>
                <?php else : ?>
                    <span class="drone-products-filter__tree-label drone-products-filter__tree-label--static"><?php echo esc_html( $term->name ); ?></span>
                <?php endif; ?>

                <span class="drone-products-filter__tree-count">
                    <?php
                        echo esc_html(
                            aprop_drone_filter_count(
                                $current_filters,
                                'category',
                                array( 'category' => (string) $term->term_id )
                            )
                        );
                    ?>
                </span>
            </div>

            <?php if ( $has_children ) : ?>
                <div id="<?php echo esc_attr( $branch_id ); ?>" class="drone-products-filter__tree-children"<?php echo $selected_in_branch ? '' : ' hidden'; ?>>
                    <?php echo aprop_render_drone_category_tree_options( $node['children'], $current_filters, $level + 1 ); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    return ob_get_clean();
}

function aprop_render_drone_filters( $current_filters ) {
    $options = aprop_drone_filter_options();
    $category_tree = aprop_drone_category_tree();
    $reset_url = get_permalink();

    ob_start();
    ?>
    <aside id="drone-products-filter" class="drone-products-filter" aria-label="Filter dronov">
        <form method="get" class="drone-products-filter__form">
            <input type="hidden" name="drone_sort" value="<?php echo esc_attr($current_filters['sort']); ?>" />

            <div class="drone-products-filter__group">
                <h2>Kategória</h2>
                <div class="drone-products-filter__tree-item drone-products-filter__tree-item--root">
                    <div class="drone-products-filter__tree-row">
                        <label class="drone-products-filter__tree-select">
                            <input
                                type="radio"
                                name="drone_category"
                                value=""
                                <?php checked( $current_filters['category'], 0 ); ?>
                                onchange="this.form.submit()"
                            />
                            <span class="drone-products-filter__tree-indicator" aria-hidden="true"></span>
                            <span class="screen-reader-text">Vybrať všetky kategórie</span>
                        </label>

                        <span class="drone-products-filter__tree-label drone-products-filter__tree-label--static">Všetky kategórie</span>
                        <span class="drone-products-filter__tree-count">
                            <?php echo esc_html( aprop_drone_filter_count( $current_filters, 'category', array( 'category' => '' ) ) ); ?>
                        </span>
                    </div>
                </div>

                <?php echo aprop_render_drone_category_tree_options( $category_tree, $current_filters ); ?>
            </div>

            <div class="drone-products-filter__group">
                <h2>Zobrazenie</h2>
                <?php
                    echo aprop_render_drone_filter_radio(
                        'drone_display',
                        'all',
                        $options['display']['all'],
                        $current_filters['display'],
                        aprop_drone_filter_count($current_filters, 'display', array('display' => 'all'))
                    );

                    foreach ( array('drony', 'prislusenstvo') as $value ) {
                        echo aprop_render_drone_filter_radio(
                            'drone_display',
                            $value,
                            $options['display'][$value],
                            $current_filters['display'],
                            aprop_drone_filter_count($current_filters, 'display', array('display' => $value))
                        );
                    }
                ?>
            </div>

            <div class="drone-products-filter__group">
                <h2>Účel použitia</h2>
                <?php foreach ( $options['purpose'] as $value => $label ) : ?>
                    <?php
                        echo aprop_render_drone_filter_radio(
                            'drone_purpose',
                            $value,
                            $label,
                            $current_filters['purpose'],
                            aprop_drone_filter_count($current_filters, 'purpose', array('purpose' => $value))
                        );
                    ?>
                <?php endforeach; ?>
            </div>

            <div class="drone-products-filter__group">
                <h2>Nosnosť nádrže</h2>
                <?php $capacity_progress = max(0, min(100, (($current_filters['capacity_max'] - 10) / 90) * 100)); ?>
                <input
                    type="range"
                    name="drone_capacity_max"
                    min="10"
                    max="100"
                    step="10"
                    value="<?php echo esc_attr($current_filters['capacity_max']); ?>"
                    style="--range-progress: <?php echo esc_attr($capacity_progress); ?>%;"
                    onchange="this.form.submit()"
                />
                <div class="drone-products-filter__range-labels">
                    <span>10 L</span>
                    <span>100 L+</span>
                </div>
            </div>

            <div class="drone-products-filter__group">
                <h2>Dostupnosť</h2>
                <?php foreach ( $options['availability'] as $value => $label ) : ?>
                    <?php
                        echo aprop_render_drone_filter_radio(
                            'drone_availability',
                            $value,
                            $label,
                            $current_filters['availability'],
                            aprop_drone_filter_count($current_filters, 'availability', array('availability' => $value))
                        );
                    ?>
                <?php endforeach; ?>
            </div>

            <a href="<?php echo esc_url($reset_url); ?>" class="drone-products-filter__reset">Zrušiť všetky filtre</a>
        </form>
    </aside>
    <?php
    return ob_get_clean();
}

function aprop_render_drone_sort( $current_filters ) {
    $sort_options = aprop_drone_sort_options();
    $current_label = $sort_options[$current_filters['sort']] ?? $sort_options['recommended'];

    $base_args = array(
        'drone_category' => $current_filters['category'],
        'drone_display' => $current_filters['display'],
        'drone_capacity_max' => $current_filters['capacity_max'],
    );

    if ( $current_filters['purpose'] ) {
        $base_args['drone_purpose'] = $current_filters['purpose'];
    }

    if ( $current_filters['availability'] ) {
        $base_args['drone_availability'] = $current_filters['availability'];
    }

    ob_start();
    ?>
    <div class="drone-products-sort">
        <details class="drone-products-sort__dropdown">
            <summary class="drone-products-sort__summary">
                <span class="drone-products-sort__label">Zoradiť podľa</span>
                <span class="drone-products-sort__current"><?php echo esc_html($current_label); ?></span>
            </summary>

            <div class="drone-products-sort__menu">
                <?php foreach ( $sort_options as $value => $label ) : ?>
                    <?php
                        $sort_url = add_query_arg(
                            array_merge($base_args, array('drone_sort' => $value)),
                            get_permalink()
                        );
                    ?>
                    <a
                        href="<?php echo esc_url($sort_url); ?>"
                        class="drone-products-sort__option<?php echo $current_filters['sort'] === $value ? ' is-active' : ''; ?>"
                    >
                        <?php echo esc_html($label); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </details>
    </div>
    <?php
    return ob_get_clean();
}

function aprop_render_drone_filter_toggle() {
    ob_start();
    ?>
    <button
        type="button"
        class="drone-products-filter-toggle"
        aria-controls="drone-products-filter-column"
        aria-expanded="true"
    >
        Skryť filtre
    </button>
    <?php
    return ob_get_clean();
}

function aprop_render_drone_filter_toggle_script() {
    ob_start();
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.querySelector('.drone-products-filter-toggle');
        var filterColumn = document.getElementById('drone-products-filter-column');
        var treeToggles = document.querySelectorAll('.drone-products-filter__tree-trigger');

        if (toggle && filterColumn) {
            toggle.addEventListener('click', function () {
                var isHidden = filterColumn.hasAttribute('hidden');

                if (isHidden) {
                    filterColumn.removeAttribute('hidden');
                    toggle.setAttribute('aria-expanded', 'true');
                    toggle.textContent = 'Skryť filtre';
                } else {
                    filterColumn.setAttribute('hidden', '');
                    toggle.setAttribute('aria-expanded', 'false');
                    toggle.textContent = 'Zobraziť filtre';
                }
            });
        }

        treeToggles.forEach(function (treeToggle) {
            treeToggle.addEventListener('click', function () {
                var childId = treeToggle.getAttribute('aria-controls');
                var childContainer = childId ? document.getElementById(childId) : null;

                if (!childContainer) {
                    return;
                }

                var isHidden = childContainer.hasAttribute('hidden');

                if (isHidden) {
                    childContainer.removeAttribute('hidden');
                    treeToggle.setAttribute('aria-expanded', 'true');
                } else {
                    childContainer.setAttribute('hidden', '');
                    treeToggle.setAttribute('aria-expanded', 'false');
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}

function render_drone_products_shortcode() {
    $options = aprop_drone_filter_options();
    $sort_options = aprop_drone_sort_options();
    $category_tree = aprop_drone_category_tree();
    $valid_category_ids = aprop_drone_category_ids_from_tree( $category_tree );
    $current_filters = array(
        'category'     => isset($_GET['drone_category']) ? absint($_GET['drone_category']) : 0,
        'display'      => isset($_GET['drone_display']) ? sanitize_key($_GET['drone_display']) : 'all',
        'purpose'      => isset($_GET['drone_purpose']) ? sanitize_key($_GET['drone_purpose']) : '',
        'availability' => isset($_GET['drone_availability']) ? sanitize_key($_GET['drone_availability']) : '',
        'capacity_max' => isset($_GET['drone_capacity_max']) ? absint($_GET['drone_capacity_max']) : 100,
        'sort'         => isset($_GET['drone_sort']) ? sanitize_key($_GET['drone_sort']) : 'recommended',
    );

    if ( $current_filters['category'] && ! in_array( $current_filters['category'], $valid_category_ids, true ) ) {
        $current_filters['category'] = 0;
    }

    if ( ! array_key_exists($current_filters['display'], $options['display']) ) {
        $current_filters['display'] = 'all';
    }

    if ( ! array_key_exists($current_filters['purpose'], $options['purpose']) ) {
        $current_filters['purpose'] = '';
    }

    if ( ! array_key_exists($current_filters['availability'], $options['availability']) ) {
        $current_filters['availability'] = '';
    }

    if ( $current_filters['capacity_max'] < 10 || $current_filters['capacity_max'] > 100 ) {
        $current_filters['capacity_max'] = 100;
    }

    if ( ! array_key_exists($current_filters['sort'], $sort_options) ) {
        $current_filters['sort'] = 'recommended';
    }

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'menu_order title',
        'order'          => 'ASC',
        'tax_query'      => aprop_drone_tax_query_from_filters( $current_filters ),
        'meta_query'     => aprop_drone_meta_query_from_filters($current_filters),
    );

    switch ( $current_filters['sort'] ) {
        case 'price_asc':
            $args['meta_key'] = '_price';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'ASC';
            break;
        case 'price_desc':
            $args['meta_key'] = '_price';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        case 'title_asc':
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
            break;
        case 'newest':
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
        case 'recommended':
        default:
            break;
    }

    $query = new WP_Query($args);

    ob_start();
    ?>
    <section class="drone-products-page shop-page-wrapper alignwide">
        <div class="drone-products-title">
            <h1>Poľnohospodárske drony</h1>
        </div>

        <div class="drone-products-toolbar">
            <div class="drone-products-toolbar__filter-toggle">
                <?php echo aprop_render_drone_filter_toggle(); ?>
            </div>

            <div class="drone-products-toolbar__sort">
                <?php echo aprop_render_drone_sort($current_filters); ?>
            </div>
        </div>

        <div class="drone-products-content">
            <div id="drone-products-filter-column" class="drone-products-content__filter">
                <?php echo aprop_render_drone_filters($current_filters); ?>
            </div>

            <div class="drone-products-content__products">
                <div class="drone-products-grid">
                    <?php if ($query->have_posts()) : ?>
                        <?php while ($query->have_posts()) : $query->the_post(); ?>
                        <?php
                            global $product;

                            $product_id = get_the_ID();
                            $permalink = get_permalink($product_id);
                            $badge = get_post_meta($product_id, 'aprop_card_badge', true);
                            $card_specifications = function_exists('aprop_get_product_card_specifications') ? aprop_get_product_card_specifications($product_id, 3) : array();
                            $hover_image_id = 0;

                            if ( $product instanceof WC_Product ) {
                                $gallery_image_ids = $product->get_gallery_image_ids();
                                if ( ! empty($gallery_image_ids) ) {
                                    $hover_image_id = (int) $gallery_image_ids[0];
                                }
                            }

                            $hover_image_url = $hover_image_id ? wp_get_attachment_image_url($hover_image_id, 'large') : '';
                            $card_classes = 'drone-product-card';
                            $card_style = '';

                            if ( $hover_image_url ) {
                                $card_classes .= ' drone-product-card--has-hover-media';
                                $card_style = '--drone-hover-image: url(' . esc_url($hover_image_url) . ');';
                            }
                        ?>

                        <article class="<?php echo esc_attr($card_classes); ?>"<?php echo $card_style ? ' style="' . esc_attr($card_style) . '"' : ''; ?>>
                            <a class="drone-product-card__link" href="<?php echo esc_url($permalink); ?>" aria-label="<?php the_title_attribute(); ?>">
                                <?php if ($badge): ?>
                                    <span class="drone-product-card__badge"><?php echo esc_html($badge); ?></span>
                                <?php endif; ?>

                                <div class="drone-product-card__media">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php the_post_thumbnail('large', array('class' => 'drone-product-card__image drone-product-card__image--main')); ?>
                                    <?php endif; ?>
                                </div>

                                <div class="drone-product-card__content">
                                    <div class="drone-product-card__title-price">
                                        <h2><?php the_title(); ?></h2>
                                        <span class="drone-product-card__price"><?php echo wp_kses_post($product->get_price_html()); ?></span>
                                    </div>

                                    <?php if ( ! empty($card_specifications) ) : ?>
                                        <div class="drone-product-card__specs">
                                            <?php foreach ($card_specifications as $specification) : ?>
                                                <div class="drone-product-card__spec">
                                                    <span class="drone-product-card__spec-label"><?php echo esc_html($specification['name']); ?></span>
                                                    <span class="drone-product-card__spec-value"><?php echo esc_html($specification['value']); ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </article>
                        <?php endwhile; ?>
                        <?php wp_reset_postdata(); ?>
                    <?php else : ?>
                        <p>Žiadne drony neboli nájdené.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php echo aprop_render_drone_filter_toggle_script(); ?>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('drone_products', 'render_drone_products_shortcode');
