<?php
/**
 * Custom blog listing with category filter
 * Child theme of Twenty Twenty-One
 */

get_header();

// ID kategórií, ktoré chceme skryť
$excluded_categories = array(85, 88, 87, 65, 80);

// Získaj kategórie
$categories = get_terms( array(
    'taxonomy'   => 'category',
    'orderby'    => 'name',
    'order'      => 'ASC',
    'hide_empty' => true,
    'exclude'    => array(1),
    'exclude'    => array_merge( array(1), $excluded_categories ), // pridáme 1 + ostatné do exclude
) );


// Aktuálne vybraná kategória
$current_category_slug = isset( $_GET['category'] ) ? sanitize_text_field( $_GET['category'] ) : '';
$current_category_name = 'Všetko';

if ( $current_category_slug ) {
    $cat = get_category_by_slug( $current_category_slug );
    if ( $cat ) {
        $current_category_name = $cat->name;
    }
}
?>

<section class="hero-filter alignwide">
	<button id="close-filter" class="close-filter">
	  Zavrieť filtre <span class="close-icon">✕</span>
	</button>
  <!-- Základný blok -->
  <div class="filter-toggle">
		<button id="open-filter">
		  <span>Zobraziť:</span> <span class="selected-category"><?php echo esc_html( $current_category_name ); ?></span>
		</button>
  </div>

  <!-- Rozbalený filter -->
  <div id="filter-box" class="filter-box" style="display:none;">
    <div class="filter-title">Filtruj podľa</div>
    <div class="filter-options">
			<a href="<?php echo esc_url( home_url() ); ?>" class="filter-option <?php echo $current_category_slug === '' ? 'selected' : ''; ?>">
			  Všetko
			</a>
			<?php foreach ( $categories as $category ) : ?>
			  <button class="filter-option" data-category="<?php echo esc_attr( $category->slug ); ?>">
			    <?php echo esc_html( $category->name ); ?>
			  </button>
			<?php endforeach; ?>

    </div>
  </div>
</section>

<?php
// Prispôsobený WP_Query podľa kategórie
$args = array(
    'post_type'      => 'post',
    'posts_per_page' => -1,
);

if ( $current_category_slug ) {
    $args['category_name'] = $current_category_slug;
}

$query = new WP_Query( $args );
?>

<section class="blog-posts alignwide" style="margin-bottom: 4rem;">
  <?php if ( $query->have_posts() ) : ?>
		<div id="post-results">
	    <div class="post-grid">
	      <?php while ( $query->have_posts() ) : $query->the_post(); ?>
	        <article class="post-card">
	          <a href="<?php the_permalink(); ?>" class="post-thumbnail">
	            <?php if ( has_post_thumbnail() ) {
	              the_post_thumbnail( 'large' );
	            } ?>
	          </a>

	          <div class="post-meta">
	            <span class="post-category">
	              <?php
	              $post_categories = get_the_category();
	              if ( ! empty( $post_categories ) ) {
	                echo esc_html( $post_categories[0]->name );
	              }
	              ?>
	            </span>
	            <span class="post-date"><?php echo get_the_date(); ?></span>
	          </div>

	          <h2 class="post-title">
	            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
	          </h2>

	          <div class="post-excerpt">
	            <?php the_excerpt(); ?>
	          </div>
	        </article>
	      <?php endwhile; ?>
	    </div>
		</div>

  <?php else : ?>
    <p>Žiadne články neboli nájdené.</p>
  <?php endif; ?>
  <?php wp_reset_postdata(); ?>
</section>

<?php get_footer(); ?>
