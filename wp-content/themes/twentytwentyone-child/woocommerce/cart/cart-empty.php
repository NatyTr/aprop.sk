<?php
/**
 * Empty cart page
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;

$shop_url  = apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) );
$drones_url = home_url( '/drony/' );
?>

<div class="aprop-cart-empty">
	<h1 class="aprop-cart-empty__title"><?php esc_html_e( 'Váš košík je prázdny', 'aprop' ); ?></h1>
	<p class="aprop-cart-empty__text"><?php esc_html_e( 'Pozrite si drony a príslušenstvo a pridajte si niečo do košíka.', 'aprop' ); ?></p>

	<div class="aprop-cart-empty__actions">
		<?php if ( $shop_url ) : ?>
			<a class="btn-primary btn-black" href="<?php echo esc_url( $shop_url ); ?>">
				<?php esc_html_e( 'Späť do obchodu', 'aprop' ); ?>
			</a>
		<?php endif; ?>
		<a class="btn-primary" href="<?php echo esc_url( $drones_url ); ?>">
			<?php esc_html_e( 'Pozrieť drony', 'aprop' ); ?>
		</a>
	</div>
</div>

<?php
remove_action( 'woocommerce_cart_is_empty', 'wc_empty_cart_message', 10 );
do_action( 'woocommerce_cart_is_empty' );
?>
