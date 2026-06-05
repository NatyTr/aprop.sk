</main><!-- #main -->
</div><!-- #primary -->
</div><!-- #content -->

<?php
// Dáta z vlastnej options stránky (nie ACF)
$email      = get_option('footer_email');
$ig         = get_option('footer_ig');
$fb         = get_option('footer_fb');
$li         = get_option('footer_li');
$yt         = get_option('footer_yt');
$footer_img = get_option('footer_image'); // URL obrázka
?>

<footer id="colophon" class="site-footer">
	<div class="footer-container">
		<div class="footer-top">
			<div class="footer-left">
				<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/icon-green.svg" alt="Icon" class="footer-icon" />

				<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/logo-green.svg" alt="Icon" class="footer-logo" />
			</div>

			<div class="footer-right">
				<?php
					$locations = get_nav_menu_locations();

					if ( isset( $locations['footer_main'] ) ) {
						$menu_id = $locations['footer_main'];
						$menu_items = wp_get_nav_menu_items( $menu_id );

						if ( $menu_items ) :
							$half = ceil( count( $menu_items ) / 2 );
							$chunks = array_chunk( $menu_items, $half );
							?>
							<div class="footer-menu-columns">
								<?php foreach ( $chunks as $index => $chunk ) : ?>
									<ul class="footer-main-menu">
										<?php if ( $index === 0 ) : ?>
											<li><p class="footer-label">Website</p></li>
										<?php endif; ?>
										<?php foreach ( $chunk as $item ) : ?>
											<li><a href="<?php echo esc_url( $item->url ); ?>"><?php echo esc_html( $item->title ); ?></a></li>
										<?php endforeach; ?>
									</ul>
								<?php endforeach; ?>
							</div>
						<?php endif;
					}
					?>



				<div class="footer-columns">
					<?php if ( $email ) : ?>
						<div class="footer-column">
							<p class="footer-label">Kontakt</p>
							<a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
						</div>
					<?php endif; ?>

					<div class="footer-column">
						<ul class="footer-socials">
							<?php if ( $ig ) : ?><li><a class="ig" href="<?php echo esc_url($ig); ?>" target="_blank" rel="noopener"></a></li><?php endif; ?>
							<?php if ( $fb ) : ?><li><a class="fb" href="<?php echo esc_url($fb); ?>" target="_blank" rel="noopener"></a></li><?php endif; ?>
							<?php if ( $li ) : ?><li><a class="li" href="<?php echo esc_url($li); ?>" target="_blank" rel="noopener"></a></li><?php endif; ?>
							<?php if ( $yt ) : ?><li><a class="yt" href="<?php echo esc_url($yt); ?>" target="_blank" rel="noopener"></a></li><?php endif; ?>
						</ul>
					</div>
				</div>
			</div>
		</div>

		<div class="footer-bottom">
			<?php if ( $footer_img ) : ?>
				<img src="<?php echo esc_url($footer_img); ?>" alt="Footer Image" class="footer-bottom-image" />
			<?php endif; ?>
		</div>
	</div><!-- .footer-container -->
	<div class="footer-meta">
	<div class="footer-meta-left">
		<p>© 2025 Aprop</p>
	</div>

	<div class="footer-meta-center">
		<p>Website by <a href="https://pangan.sk" target="_blank" rel="noopener">Pangan</a></p>
	</div>

	<div class="footer-meta-right">
		<?php
		wp_nav_menu( array(
			'theme_location' => 'footer_socials',
			'menu_class'     => 'footer_socials',
			'container'      => false,
		) );
		?>
	</div>
</div>

</footer><!-- #colophon -->

<?php wp_footer(); ?>
</body>
</html>
