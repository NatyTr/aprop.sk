<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">

	<a class="skip-link screen-reader-text" href="#content">
		<?php esc_html_e( 'Preskočiť na obsah', 'custom-theme' ); ?>
	</a>

	<header id="masthead" class="custom-header">
		<div class="container">
			<div class="top-header">

				<!-- Logo -->
				<a href="/" class="header-logo">
					<?php
						$custom_logo_id = get_theme_mod( 'custom_logo' );
						if ( $custom_logo_id ) :
							$image = wp_get_attachment_image_src( $custom_logo_id, 'full' );
							if ( $image ) :
								?>
								<span class="custom-logo-link">
									<img src="<?php echo esc_url( $image[0] ); ?>" width="<?php echo esc_attr( $image[1] ); ?>" height="<?php echo esc_attr( $image[2] ); ?>" class="custom-logo" alt="" decoding="async" />
								</span>
							<?php
							endif;
						endif;
						?>
				</a>

			</div>
			<div class="header-container">

				<!-- Menu -->
				<nav id="site-navigation" class="header-menu">
					<?php
						wp_nav_menu( [
							'theme_location' => 'primary',
							'menu_id'        => 'primary-menu',
						] );
					?>

					<div class="mobile-menu-panel" aria-hidden="true">
						<div class="mobile-menu-header">
							<a href="/" class="mobile-menu-logo" aria-label="Aprop domov">
								<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/logo-green.svg" alt="APROP" />
							</a>
							<button type="button" class="mobile-menu-close">Zatvoriť</button>
						</div>

						<div class="mobile-menu-sections">
							<div class="mobile-menu-section">
								<p class="mobile-menu-label">Nakupovať</p>
								<div class="mobile-menu-links">
									<a href="/drony">Drony</a>
									<a href="/sluzby">Kurzy</a>
									<a href="/balicky">Balíčky kurzov</a>
								</div>
							</div>

							<div class="mobile-menu-section">
								<p class="mobile-menu-label">Navigácia</p>
								<div class="mobile-menu-links">
									<a href="/">Domov</a>
									<a href="/pre-firmy">Pre firmy</a>
									<a href="/o-nas">O nás</a>
									<a href="/blog">Blog</a>
									<a href="/kontakt">Kontakt</a>
								</div>
							</div>
						</div>

						<div class="mobile-menu-legal">
							<a href="/obchodne-podmienky">VOP</a>
							<a href="/cookies">Cookies</a>
							<a href="/gdpr">GDPR</a>
						</div>
					</div>
				</nav>


				<!-- Login & Cart -->
				<div class="header-tools">
					<div class="header-quick-links">
						<a class="header-pill-link" href="/blog">Blog</a>
						<a class="header-pill-link" href="/kontakt">Kontakt</a>
					</div>

					<div class="header-actions">
						<form class="header-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" role="search">
							<label class="screen-reader-text" for="header-search-input">Vyhľadávať</label>
							<button class="header-search" type="button" aria-label="Otvoriť vyhľadávanie" aria-expanded="false" aria-controls="header-search-panel">
								<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/lupa.svg?v2" alt="Hľadať" width="20" height="20" />
							</button>
							<div class="header-search-panel" id="header-search-panel">
								<input id="header-search-input" type="search" name="s" placeholder="Hľadať..." autocomplete="off" />
								<button class="header-search-submit" type="submit">Hľadať</button>
							</div>
						</form>

						<a class="btn-green-icon" href="/drony">Vybrať dron</a>
					</div>
					<?php if ( class_exists( 'WooCommerce' ) ) : ?>
						<a href="<?php echo wc_get_cart_url(); ?>" class="cart-link">
							<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/cart-white.svg" alt="Košík" width="24" height="24" />
						</a>
					<?php endif; ?>

					<button id="menu-toggle" class="mobile-toggle" aria-label="Menu">
						<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/aprop-toggle.svg" alt="toggle" />
					</button>
				</div>

			</div>
		</div>
	</header>


	<div id="content" class="site-content">
