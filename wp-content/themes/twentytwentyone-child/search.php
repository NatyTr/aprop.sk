<?php
/**
 * Search results template.
 *
 * @package Twenty_Twenty_One_Child
 */

get_header();

$search_query = get_search_query();
$found_posts  = (int) $wp_query->found_posts;
?>

<main class="aprop-search">
	<div class="aprop-search__inner">
		<div class="aprop-search__header">
			<p class="aprop-search__eyebrow"><?php esc_html_e( 'Vyhľadávanie', 'aprop' ); ?></p>
			<h1 class="aprop-search__title">
				<?php
				printf(
					/* translators: %s: search term */
					esc_html__( 'Výsledky pre „%s“', 'aprop' ),
					esc_html( $search_query )
				);
				?>
			</h1>

			<form class="aprop-search__form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="screen-reader-text" for="aprop-search-input"><?php esc_html_e( 'Hľadať', 'aprop' ); ?></label>
				<input
					id="aprop-search-input"
					class="aprop-search__input"
					type="search"
					name="s"
					value="<?php echo esc_attr( $search_query ); ?>"
					placeholder="<?php esc_attr_e( 'Hľadať produkty a články…', 'aprop' ); ?>"
				/>
				<button class="aprop-search__submit" type="submit" aria-label="<?php esc_attr_e( 'Hľadať', 'aprop' ); ?>">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
						<circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"></circle>
						<path d="M20 20L16.5 16.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
					</svg>
				</button>
			</form>

			<?php if ( have_posts() ) : ?>
				<p class="aprop-search__count">
					<?php
					printf(
						esc_html(
							_n(
								'Našli sme %d výsledok',
								'Našli sme %d výsledkov',
								$found_posts,
								'aprop'
							)
						),
						$found_posts
					);
					?>
				</p>
			<?php endif; ?>
		</div>

		<?php if ( have_posts() ) : ?>
			<div class="aprop-search__results">
				<?php
				while ( have_posts() ) :
					the_post();

					if ( 'product' === get_post_type() && function_exists( 'aprop_render_drone_product_card' ) ) {
						echo aprop_render_drone_product_card( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							get_the_ID(),
							array(
								'title_tag' => 'h2',
								'article_class' => 'drone-product-card aprop-search__product-card',
							)
						);
						continue;
					}

					$post_type_object = get_post_type_object( get_post_type() );
					$type_label       = $post_type_object ? $post_type_object->labels->singular_name : __( 'Obsah', 'aprop' );
					?>
					<article <?php post_class( 'aprop-search__item' ); ?>>
						<a class="aprop-search__item-link" href="<?php the_permalink(); ?>">
							<span class="aprop-search__item-media">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 'medium_large', array( 'class' => 'aprop-search__item-image' ) ); ?>
								<?php else : ?>
									<span class="aprop-search__item-placeholder" aria-hidden="true"></span>
								<?php endif; ?>
							</span>
							<span class="aprop-search__item-content">
								<span class="aprop-search__item-type"><?php echo esc_html( $type_label ); ?></span>
								<h2 class="aprop-search__item-title"><?php the_title(); ?></h2>
								<?php if ( has_excerpt() || get_the_content() ) : ?>
									<span class="aprop-search__item-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></span>
								<?php endif; ?>
								<span class="aprop-search__item-cta"><?php esc_html_e( 'Pozrieť detail', 'aprop' ); ?></span>
							</span>
						</a>
					</article>
					<?php
				endwhile;
				?>
			</div>

			<div class="aprop-search__pagination">
				<?php
				global $wp_query;

				$pagination = paginate_links(
					array(
						'total'     => (int) $wp_query->max_num_pages,
						'current'   => max( 1, (int) get_query_var( 'paged' ) ),
						'mid_size'  => 1,
						'end_size'  => 1,
						'prev_text' => '<span aria-hidden="true">‹</span><span class="screen-reader-text">' . esc_html__( 'Predchádzajúca', 'aprop' ) . '</span>',
						'next_text' => '<span aria-hidden="true">›</span><span class="screen-reader-text">' . esc_html__( 'Ďalšia', 'aprop' ) . '</span>',
						'type'      => 'list',
					)
				);

				if ( $pagination ) {
					echo '<nav class="aprop-search-pagination" aria-label="' . esc_attr__( 'Stránkovanie výsledkov', 'aprop' ) . '">';
					echo $pagination; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '</nav>';
				}
				?>
			</div>
		<?php else : ?>
			<section class="aprop-search__empty">
				<h2><?php esc_html_e( 'Nič sme nenašli', 'aprop' ); ?></h2>
				<p><?php esc_html_e( 'Skúste iný výraz alebo si pozrite našu ponuku dronov a kurzov.', 'aprop' ); ?></p>
				<div class="aprop-search__empty-actions">
					<a class="btn-primary btn-black" href="<?php echo esc_url( home_url( '/drony' ) ); ?>">
						<?php esc_html_e( 'Pozrieť drony', 'aprop' ); ?>
					</a>
					<a class="btn-primary" href="<?php echo esc_url( home_url( '/sluzby' ) ); ?>">
						<?php esc_html_e( 'Pozrieť kurzy', 'aprop' ); ?>
					</a>
				</div>
			</section>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
