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
				</nav>


				<!-- Login & Cart -->
				<div class="header-tools">
					<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					    <label>
					        <input type="search" class="search-field"
					            placeholder="<?php echo esc_attr_x( 'Vyhľadávať', 'placeholder' ); ?>"
					            value="<?php echo get_search_query(); ?>" name="s" />
					    </label>
					    <button type="submit" class="search-submit" aria-label="Vyhľadávať">
					        <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/lupa.svg" alt="Hľadať" width="20" height="20" />
					    </button>
					</form>





					<a class="btn-primary btn-icon btn-black" href="/sluzby">Začni hneď</a>
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
