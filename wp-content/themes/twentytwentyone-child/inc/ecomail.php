<?php
/**
 * Optional checkout newsletter checkbox → Ecomail list.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checked state survives the checkout fragment refresh (payment method change).
 */
function aprop_checkout_newsletter_is_checked() {
	if ( isset( $_POST['aprop_newsletter'] ) ) {
		return '1' === (string) wp_unslash( $_POST['aprop_newsletter'] );
	}

	if ( empty( $_POST['post_data'] ) || ! is_string( $_POST['post_data'] ) ) {
		return true;
	}

	$posted = array();
	parse_str( wp_unslash( $_POST['post_data'] ), $posted );

	return ! empty( $posted['aprop_newsletter'] );
}

add_action( 'woocommerce_review_order_before_submit', 'aprop_checkout_newsletter_field' );
function aprop_checkout_newsletter_field() {
	?>
	<p class="form-row aprop-newsletter">
		<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox" for="aprop_newsletter">
			<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="aprop_newsletter" id="aprop_newsletter" value="1" <?php checked( aprop_checkout_newsletter_is_checked() ); ?> />
			<span class="woocommerce-terms-and-conditions-checkbox-text"><?php esc_html_e( 'Chcem dostávať novinky a akcie e-mailom.', 'aprop' ); ?></span>
		</label>
	</p>
	<?php
}

add_action( 'woocommerce_checkout_order_processed', 'aprop_checkout_subscribe_ecomail', 20, 3 );
function aprop_checkout_subscribe_ecomail( $order_id, $posted_data, $order ) {
	if ( ! aprop_checkout_newsletter_is_checked() ) {
		return;
	}

	if ( ! $order instanceof WC_Order ) {
		$order = wc_get_order( $order_id );
	}

	if ( ! $order instanceof WC_Order ) {
		return;
	}

	$order->update_meta_data( '_aprop_newsletter', 'yes' );
	$order->save();

	$email = $order->get_billing_email();
	if ( ! is_email( $email ) ) {
		return;
	}

	$result = aprop_ecomail_subscribe(
		$email,
		array(
			'name'    => $order->get_billing_first_name(),
			'surname' => $order->get_billing_last_name(),
			'country' => $order->get_billing_country(),
			'source'  => 'checkout',
		)
	);

	if ( is_wp_error( $result ) ) {
		$order->add_order_note( 'Ecomail: prihlásenie na newsletter zlyhalo. ' . $result->get_error_message() );
		return;
	}

	$order->add_order_note( 'Ecomail: zákazník súhlasil s odberom noviniek a e-mail bol pridaný do zoznamu.' );
}

/**
 * @param string               $email
 * @param array<string,string> $subscriber
 * @return true|WP_Error
 */
function aprop_ecomail_subscribe( $email, $subscriber = array() ) {
	if ( ! is_email( $email ) ) {
		return new WP_Error( 'aprop_ecomail_email', 'Neplatný e-mail.' );
	}

	$api_key = defined( 'APROP_ECOMAIL_API_KEY' ) ? APROP_ECOMAIL_API_KEY : '';
	$list_id = defined( 'APROP_ECOMAIL_LIST_ID' ) ? (int) APROP_ECOMAIL_LIST_ID : 0;
	if ( $api_key === '' || $list_id < 1 ) {
		return new WP_Error( 'aprop_ecomail_config', 'Chýba API kľúč alebo ID zoznamu.' );
	}

	$subscriber = wp_parse_args(
		$subscriber,
		array(
			'name'    => '',
			'surname' => '',
			'country' => 'SK',
			'source'  => 'web',
		)
	);

	$response = wp_remote_post(
		'https://api2.ecomailapp.cz/lists/' . $list_id . '/subscribe',
		array(
			'timeout' => 12,
			'headers' => array(
				'key'          => $api_key,
				'Content-Type' => 'application/json',
			),
			'body'    => wp_json_encode(
				array(
					'subscriber_data'        => array(
						'email'   => $email,
						'name'    => $subscriber['name'],
						'surname' => $subscriber['surname'],
						'country' => $subscriber['country'],
						'source'  => $subscriber['source'],
					),
					'trigger_autoresponders' => true,
					'update_existing'        => true,
					'resubscribe'            => true,
				)
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$code = (int) wp_remote_retrieve_response_code( $response );
	if ( $code < 200 || $code >= 300 ) {
		return new WP_Error( 'aprop_ecomail_http', 'Ecomail vrátil HTTP ' . $code . '.' );
	}

	return true;
}

add_action( 'admin_post_nopriv_aprop_footer_newsletter', 'aprop_footer_newsletter_handle' );
add_action( 'admin_post_aprop_footer_newsletter', 'aprop_footer_newsletter_handle' );
function aprop_footer_newsletter_handle() {
	$redirect = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : home_url( '/' );
	$redirect = wp_validate_redirect( $redirect, home_url( '/' ) );
	$redirect = remove_query_arg( 'newsletter', $redirect );

	$finish = function ( $status ) use ( $redirect ) {
		$url = add_query_arg( 'newsletter', $status, $redirect );
		wp_safe_redirect( $url . '#aprop-newsletter-thanks' );
		exit;
	};

	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'aprop_footer_newsletter' ) ) {
		$finish( 'error' );
	}

	if ( ! empty( $_POST['aprop_website'] ) ) {
		$finish( 'ok' );
	}

	if ( empty( $_POST['aprop_newsletter'] ) ) {
		$finish( 'consent' );
	}

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	if ( ! is_email( $email ) ) {
		$finish( 'invalid' );
	}

	$result = aprop_ecomail_subscribe(
		$email,
		array(
			'country' => 'SK',
			'source'  => 'footer',
		)
	);

	$finish( is_wp_error( $result ) ? 'error' : 'ok' );
}
