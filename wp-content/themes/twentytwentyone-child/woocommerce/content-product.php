<?php
defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
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
