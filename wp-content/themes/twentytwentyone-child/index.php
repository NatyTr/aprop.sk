<?php
/**
 * Custom blog listing
 * Child theme of Twenty Twenty-One
 */

get_header();

// Query all blog posts; category filtering has been removed from the blog.
$args = array(
    'post_type'      => 'post',
    'posts_per_page' => -1,
);

$query = new WP_Query( $args );
?>

<section class="hero-filter alignwide" aria-hidden="true"></section>

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
