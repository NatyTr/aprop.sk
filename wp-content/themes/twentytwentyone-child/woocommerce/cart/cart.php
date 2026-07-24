<?php
/**
 * Cart Page
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.8.0
 */

defined( 'ABSPATH' ) || exit;

remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cart_totals', 10 );

do_action( 'woocommerce_before_cart' );
?>

<div class="aprop-cart">
	<h1 class="aprop-cart__title"><?php esc_html_e( 'Môj košík', 'aprop' ); ?></h1>

	<div class="aprop-cart__layout">
		<div class="aprop-cart__main">
			<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
				<?php do_action( 'woocommerce_before_cart_table' ); ?>

				<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
					<thead>
						<tr>
							<th class="product-thumbnail"><span class="screen-reader-text"><?php esc_html_e( 'Obrázok', 'aprop' ); ?></span></th>
							<th class="product-name"><?php esc_html_e( 'Produkt', 'aprop' ); ?></th>
							<th class="product-price"><?php esc_html_e( 'Cena', 'woocommerce' ); ?></th>
							<th class="product-quantity"><?php esc_html_e( 'ks', 'aprop' ); ?></th>
							<th class="product-subtotal"><?php esc_html_e( 'Celkom', 'aprop' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php do_action( 'woocommerce_before_cart_contents' ); ?>

						<?php
						foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
							$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
							$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

							if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
								$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
								?>
								<tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

									<td class="product-thumbnail">
									<?php
									$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

									if ( ! $product_permalink ) {
										echo $thumbnail; // PHPCS: XSS ok.
									} else {
										printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ); // PHPCS: XSS ok.
									}
									?>
									</td>

									<td class="product-name" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
									<?php
									if ( ! $product_permalink ) {
										echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;' );
									} else {
										echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
									}

									do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );

									echo wc_get_formatted_cart_item_data( $cart_item ); // PHPCS: XSS ok.

									if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
										echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) );
									}

									if ( function_exists( 'aprop_render_cart_item_accessory_select' ) ) {
										aprop_render_cart_item_accessory_select( $cart_item, $cart_item_key );
									}
									?>
									</td>

									<td class="product-price" data-title="<?php esc_attr_e( 'Cena', 'woocommerce' ); ?>">
										<?php
											echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
										?>
									</td>

									<td class="product-quantity" data-title="<?php esc_attr_e( 'Množstvo', 'woocommerce' ); ?>">
									<?php
									if ( $_product->is_sold_individually() ) {
										$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
									} else {
										$product_quantity = woocommerce_quantity_input(
											array(
												'input_name'   => "cart[{$cart_item_key}][qty]",
												'input_value'  => $cart_item['quantity'],
												'max_value'    => $_product->get_max_purchase_quantity(),
												'min_value'    => '0',
												'product_name' => $_product->get_name(),
											),
											$_product,
											false
										);
									}

									echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // PHPCS: XSS ok.
									?>
									</td>

									<td class="product-subtotal" data-title="<?php esc_attr_e( 'Celkom s DPH', 'woocommerce' ); ?>">
										<span class="aprop-cart-line-total">
											<?php
												echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
											?>
										</span>
										<?php
											echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
												'woocommerce_cart_item_remove_link',
												sprintf(
													'<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s" data-product_name="%s" data-product_price="%s"></a>',
													esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
													esc_attr__( 'Odstrániť', 'aprop' ),
													esc_attr( $product_id ),
													esc_attr( $_product->get_sku() ),
													esc_attr( $_product->get_name() ),
													esc_attr( $_product->get_price() )
												),
												$cart_item_key
											);
										?>
									</td>

								</tr>
								<?php
								if ( function_exists( 'aprop_render_cart_item_accessory_row' ) ) {
									aprop_render_cart_item_accessory_row( $cart_item, $cart_item_key );
								}
							}
						}
						?>

						<?php do_action( 'woocommerce_cart_contents' ); ?>

						<tr class="aprop-cart__actions-row">
							<td colspan="6" class="actions">
								<?php if ( wc_coupons_enabled() ) { ?>
									<div class="coupon">
										<h3><?php esc_html_e( 'Zľavový kód', 'aprop' ); ?></h3>
										<label for="coupon_code"><?php esc_html_e( 'Coupon:', 'woocommerce' ); ?></label>
										<input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Vpíšte sem', 'woocommerce' ); ?>" />
										<button type="submit" class="btn-primary" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Uplatniť kupón', 'woocommerce' ); ?></button>
										<?php do_action( 'woocommerce_cart_coupon' ); ?>
									</div>
								<?php } ?>

								<button type="submit" class="btn-primary update-cart" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>" hidden aria-hidden="true"><?php esc_html_e( 'Aktualizovať košík', 'woocommerce' ); ?></button>

								<?php do_action( 'woocommerce_cart_actions' ); ?>

								<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
							</td>
						</tr>

						<?php do_action( 'woocommerce_after_cart_contents' ); ?>
					</tbody>
				</table>
				<?php do_action( 'woocommerce_after_cart_table' ); ?>
			</form>
		</div>

		<aside class="aprop-cart__sidebar">
			<?php woocommerce_cart_totals(); ?>
		</aside>
	</div>

	<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
  document.body.addEventListener("click", function(e) {
    var toggle = e.target.closest(".aprop-cart-acc__toggle");
    if (toggle) {
      e.preventDefault();
      var panelId = toggle.getAttribute("aria-controls");
      var row = panelId
        ? document.querySelector('.aprop-cart-acc-row[data-aprop-acc-row="' + panelId + '"]')
        : null;
      var wrap = toggle.closest(".aprop-cart-acc");
      if (!row || !wrap) return;

      var isOpen = toggle.getAttribute("aria-expanded") === "true";
      toggle.setAttribute("aria-expanded", isOpen ? "false" : "true");
      wrap.classList.toggle("is-open", !isOpen);
      row.hidden = isOpen;
      row.classList.toggle("is-open", !isOpen);
      return;
    }

    if (e.target && e.target.classList.contains("remove")) {
      e.preventDefault();

      let button = e.target;
      let removedProduct = {
        item_id: button.dataset.product_id,
        item_name: button.dataset.product_name,
        item_sku: button.dataset.product_sku,
        price: button.dataset.product_price,
        quantity: button.closest("tr").querySelector(".qty")
          ? parseInt(button.closest("tr").querySelector(".qty").value)
          : 1
      };

      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({
        event: "remove_from_cart",
        ecommerce: {
          items: [removedProduct]
        }
      });

      window.location.href = button.href;
    }
  });
});

(function ($) {
  if (typeof $ === "undefined") {
    return;
  }

  var qtyTimeout = null;

  function enhanceCartQuantity($scope) {
    ($scope || $(document)).find(".woocommerce-cart-form .quantity").each(function () {
      var $wrap = $(this);
      var $input = $wrap.find("input.qty");

      if (!$input.length || $wrap.hasClass("aprop-qty-enhanced")) {
        return;
      }

      $wrap.addClass("aprop-qty-enhanced");
      $input.before(
        '<button type="button" class="aprop-qty-btn aprop-qty-btn--minus" aria-label="Znížiť počet">−</button>'
      );
      $input.after(
        '<button type="button" class="aprop-qty-btn aprop-qty-btn--plus" aria-label="Zvýšiť počet">+</button>'
      );
    });
  }

  function queueCartUpdate($input) {
    var $form = $input.closest("form.woocommerce-cart-form");
    var $button = $form.find(':input[name="update_cart"]');

    if (!$form.length || !$button.length) {
      return;
    }

    $button.prop("disabled", false).attr("aria-disabled", "false");

    window.clearTimeout(qtyTimeout);
    qtyTimeout = window.setTimeout(function () {
      $form.find(':input[type="submit"]').removeAttr("clicked");
      $button.attr("clicked", "true");
      $form.trigger("submit");
    }, 450);
  }

  function changeQty($input, delta) {
    var step = parseFloat($input.attr("step")) || 1;
    var min = $input.attr("min") !== "" && $input.attr("min") != null
      ? parseFloat($input.attr("min"))
      : 0;
    var max = $input.attr("max") !== "" && $input.attr("max") != null
      ? parseFloat($input.attr("max"))
      : null;
    var current = parseFloat($input.val());

    if (isNaN(current)) {
      current = min;
    }

    var next = current + delta * step;
    if (next < min) {
      next = min;
    }
    if (max !== null && !isNaN(max) && next > max) {
      next = max;
    }

    if (next === current) {
      return;
    }

    $input.val(String(next)).trigger("change");
  }

  $(function () {
    enhanceCartQuantity();
  });

  $(document.body).on("updated_wc_div updated_cart_totals wc_fragments_refreshed", function () {
    enhanceCartQuantity();
  });

  $(document.body).on("click", ".woocommerce-cart-form .aprop-qty-btn", function (e) {
    e.preventDefault();
    var $btn = $(this);
    var $input = $btn.closest(".quantity").find("input.qty");
    if (!$input.length) {
      return;
    }
    changeQty($input, $btn.hasClass("aprop-qty-btn--plus") ? 1 : -1);
  });

  $(document.body).on("change input", ".woocommerce-cart-form input.qty", function () {
    queueCartUpdate($(this));
  });
})(window.jQuery);
</script>
