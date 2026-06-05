<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<header class="entry-header alignwide">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php twenty_twenty_one_post_thumbnail(); ?>
		<?php endif; ?>
	</header>

	<div class="entry-content">
	   <?php
	    the_content();

	    echo do_shortcode('
	    [products
	        category="drony-new"
	        limit="12"
	        columns="4"
	    ]');

	    wp_link_pages(
	        array(
	            'before'   => '<nav class="page-links" aria-label="' . esc_attr__( 'Page', 'twentytwentyone' ) . '">',
	            'after'    => '</nav>',
	            'pagelink' => esc_html__( 'Page %', 'twentytwentyone' ),
	        )
	    );
	    ?>
	</div>

	<?php if ( get_edit_post_link() ) : ?>
		<footer class="entry-footer default-max-width">
			<?php edit_post_link(
				sprintf(
					esc_html__( 'Edit %s', 'twentytwentyone' ),
					'<span class="screen-reader-text">' . get_the_title() . '</span>'
				),
				'<span class="edit-link">', '</span>'
			); ?>
		</footer>
	<?php endif; ?>
</article>
<!-- #post-<?php the_ID(); ?> -->
