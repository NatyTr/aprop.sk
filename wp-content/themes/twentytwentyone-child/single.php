<?php
/**
 * The template for displaying all single posts
 */

get_header();

/* Start the Loop */
while ( have_posts() ) :
	the_post();

	// Získaj info
	$featured_img_url = get_the_post_thumbnail_url( get_the_ID(), 'full' );
	$categories       = get_the_category();
	$category_name    = ! empty( $categories ) ? esc_html( $categories[0]->name ) : '';
	$post_date        = get_the_date();
	$excerpt          = get_the_excerpt();
?>

	<section class="single-hero alignwide">
		<div class="hero-article-grid">
			<div class="hero-image">
				<?php if ( $featured_img_url ) : ?>
					<img src="<?php echo esc_url( $featured_img_url ); ?>" alt="<?php the_title_attribute(); ?>" />
				<?php endif; ?>
			</div>

			<div class="hero-content">
				<div class="meta">
					<span class="category"><?php echo $category_name; ?></span>
					<span class="date"><?php echo $post_date; ?></span>
				</div>

				<h1 class="title"><?php the_title(); ?></h1>

				<div class="excerpt">
					<?php echo esc_html( $excerpt ); ?>
				</div>
			</div>
		</div>
	</section>

	<?php
	// Obsah samotného článku
	get_template_part( 'template-parts/content/content-single' );

endwhile; ?>

<div class="related-articles-article">
	<?php echo do_shortcode('[related_articles_slider]'); ?>
</div>

<?php
get_footer();
