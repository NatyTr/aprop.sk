<?php
defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

$is_drone_category_listing = false;

if ( is_product_category() && function_exists( 'aprop_drone_category_id' ) ) {
	$term = get_queried_object();

	if ( $term instanceof WP_Term ) {
		$drone_category_id = aprop_drone_category_id();
		$is_drone_category_listing = (int) $term->term_id === (int) $drone_category_id || term_is_ancestor_of( $drone_category_id, $term->term_id, 'product_cat' );
	}
}

if ( $is_drone_category_listing ) {
	$product_id = $product->get_id();
	$permalink = get_permalink( $product_id );
	$badge = get_post_meta( $product_id, 'aprop_card_badge', true );
	$card_specifications = function_exists( 'aprop_get_product_card_specifications' ) ? aprop_get_product_card_specifications( $product_id, 3 ) : array();
	$hover_image_url = function_exists( 'aprop_get_product_hover_image_url' ) ? aprop_get_product_hover_image_url( $product_id ) : '';
	$card_classes = 'drone-product-card';
	$card_style = '';

	if ( $hover_image_url ) {
		$card_classes .= ' drone-product-card--has-hover-media';
		$card_style = '--drone-hover-image: url(' . esc_url( $hover_image_url ) . ');';
	}
	?>
	<article
		class="<?php echo esc_attr( $card_classes ); ?>"
		<?php echo $card_style ? 'style="' . esc_attr( $card_style ) . '"' : ''; ?>
		data-product_id="<?php echo esc_attr( $product_id ); ?>"
		data-product_name="<?php echo esc_attr( $product->get_name() ); ?>"
		data-product_price="<?php echo esc_attr( $product->get_price() ); ?>"
	>
		<a class="drone-product-card__link" href="<?php echo esc_url( $permalink ); ?>" aria-label="<?php echo esc_attr( $product->get_name() ); ?>">
			<?php if ( $badge ) : ?>
				<span class="drone-product-card__badge"><?php echo esc_html( $badge ); ?></span>
			<?php endif; ?>

			<div class="drone-product-card__media">
				<?php if ( $product->get_image_id() ) : ?>
					<?php echo $product->get_image( 'large', array( 'class' => 'drone-product-card__image drone-product-card__image--main' ) ); ?>
				<?php endif; ?>
			</div>

			<div class="drone-product-card__content">
				<div class="drone-product-card__title-price">
					<h2><?php echo esc_html( $product->get_name() ); ?></h2>
					<span class="drone-product-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
				</div>

				<?php if ( ! empty( $card_specifications ) ) : ?>
					<div class="drone-product-card__specs">
						<?php foreach ( $card_specifications as $specification ) : ?>
							<div class="drone-product-card__spec">
								<span class="drone-product-card__spec-label"><?php echo esc_html( $specification['name'] ); ?></span>
								<span class="drone-product-card__spec-value"><?php echo esc_html( $specification['value'] ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</a>
	</article>
	<?php
	return;
}
?>

<div class="product-card"
			data-product_id="<?php echo esc_attr( $product->get_id() ); ?>"
     data-product_name="<?php echo esc_attr( $product->get_name() ); ?>"
     data-product_price="<?php echo esc_attr( $product->get_price() ); ?>">
	<a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>">
		<?php if ( $product->get_image_id() ) {
			echo $product->get_image( 'full' );
		} ?>
	</a>
	<div class="product-content">
		<?php if ( $product->get_price() !== '' ) : ?>
			<span class="price"><?php echo $product->get_price_html(); ?></span>
			<?php woocommerce_template_loop_add_to_cart(); ?>
		<?php else : ?>
			<div class="fake-price">price</div>
			<a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>"
			   class="btn-circle-icon-white">
			</a>
		<?php endif; ?>

		<h4>
			<a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>">
				<?php echo esc_html( $product->get_name() ); ?>
			</a>
		</h4>
	</div>
</div>


<?php if ( is_shop() || is_product_category() ) : ?>
<script>
document.addEventListener("click", function(e) {
  // nájde najbližší button s classou add_to_cart_button
  let btn = e.target.closest(".add_to_cart_button");
  if (btn) {
    let productCard = btn.closest(".product-card");
    if (productCard && !btn.classList.contains("dl-sent")) {
      window.dataLayer = window.dataLayer || [];
      dataLayer.push({
        event: "add_to_cart",
        ecommerce: {
          currency: "EUR",
          items: [
            {
              item_id: productCard.dataset.product_id,
              item_name: productCard.dataset.product_name,
              price: productCard.dataset.product_price,
              quantity: 1
            }
          ]
        }
      });
      console.log("add_to_cart event odoslaný:", productCard.dataset.product_name);

      // pridáme značku aby sa znovu nespustilo na rovnaký klik
      btn.classList.add("dl-sent");
      setTimeout(() => btn.classList.remove("dl-sent"), 100); // o chvíľu reset
    }
  }
});

</script>
<?php endif; ?>
