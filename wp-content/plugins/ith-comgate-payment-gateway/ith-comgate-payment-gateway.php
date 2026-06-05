<?php
/*
Plugin Name: ith-comgate-payment-gateway
Description: Jednoduchá platobná brána Comgate pre WooCommerce s overením stavu cez API.
Version: 1.5.2
*/

if (!defined('ABSPATH')) exit;

// Registrácia platobnej brány
add_filter('woocommerce_payment_gateways', function ($gateways) {
    $gateways[] = 'WC_Gateway_Comgate';
    return $gateways;
});

// Definícia platobnej brány
add_action('plugins_loaded', function () {
    if (!class_exists('WC_Payment_Gateway')) return;

    class WC_Gateway_Comgate extends WC_Payment_Gateway
    {
        public function __construct()
        {
            $this->id = 'comgate';
            $this->method_title = 'Comgate platba';
            $this->method_description = 'Platba cez Comgate bránu.';
            $this->has_fields = false;
            $this->supports = ['products'];

            $this->init_form_fields();
            $this->init_settings();

            $this->title      = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->enabled    = $this->get_option('enabled');
            $this->merchant   = $this->get_option('merchant');
            $this->secret     = $this->get_option('secret');
            $this->test_mode  = $this->get_option('test_mode') === 'yes';

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        }

        public function is_available()
        {
            return 'yes' === $this->enabled;
        }

        public function init_form_fields()
        {
            $this->form_fields = [
                'enabled' => [
                    'title'   => 'Povoliť',
                    'type'    => 'checkbox',
                    'label'   => 'Povoliť Comgate platobnú bránu',
                    'default' => 'yes',
                ],
                'title' => [
                    'title'   => 'Názov platby',
                    'type'    => 'text',
                    'default' => 'Comgate platba',
                    'desc_tip'=> true,
                ],
                'description' => [
                    'title'   => 'Popis',
                    'type'    => 'textarea',
                    'default' => 'Zaplaťte jednoducho cez Comgate.',
                ],
                'merchant' => [
                    'title'   => 'Merchant ID',
                    'type'    => 'text',
                    'default' => '',
                ],
                'secret' => [
                    'title'   => 'Secret kľúč',
                    'type'    => 'text',
                    'default' => '',
                ],
                'test_mode' => [
                    'title'   => 'Testovací režim',
                    'type'    => 'checkbox',
                    'label'   => 'Povoliť testovací režim',
                    'default' => 'yes',
                ],
            ];
        }

        public function process_payment($order_id)
        {
            $order = wc_get_order($order_id);
            $price = intval($order->get_total() * 100); // cents

            $base_url = home_url('/');
            $url_paid = add_query_arg(['comgatereturn' => 'paid', 'order_id' => $order->get_id()], $base_url);
            $url_cancelled = add_query_arg(['comgatereturn' => 'cancelled', 'order_id' => $order->get_id()], $base_url);
            $url_pending = add_query_arg(['comgatereturn' => 'pending', 'order_id' => $order->get_id()], $base_url);

            $params = [
                'test' => $this->test_mode ? 1 : 0,
                'price' => $price,
                'curr' => get_woocommerce_currency(),
                'label' => 'Objednávka ' . $order->get_order_number(),
                'refId' => 'sechashcomg' . $order->get_id(),
                'method' => 'ALL',
                'email' => $order->get_billing_email(),
                'fullName' => $order->get_formatted_billing_full_name(),
                'delivery' => 'HOME_DELIVERY',
                'category' => 'PHYSICAL_GOODS_ONLY',
                'urlPaid' => $url_paid . '&refId=${refId}',
                'urlCancel' => $url_cancelled . '&refId=${refId}',
                'urlPending' => $url_pending . '&refId=${refId}',
            ];

            // Ensure merchant and secret are trimmed to avoid whitespace issues
            $merchant = trim($this->merchant);
            $secret = trim($this->secret);
            $headers = [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($merchant . ':' . $secret),
            ];

            $response = wp_remote_post('https://payments.comgate.cz/v2.0/payment.json', [
                'body'    => json_encode($params),
                'headers' => $headers,
            ]);

            if (is_wp_error($response)) {
                wc_add_notice('Chyba pripojenia ku Comgate.', 'error');
                return;
            }

            $body = json_decode(wp_remote_retrieve_body($response), true);

            if (!empty($body) && isset($body['code']) && $body['code'] === 0 && isset($body['redirect'])) {
                $transId = sanitize_text_field($body['transId']);
                $redirectUrl = esc_url_raw($body['redirect']);

                $order->add_order_note('Comgate transakcia vytvorená. ID: ' . $transId);
                $order->update_meta_data('comgate_trans_id', $transId);
                $order->save();

                return [
                    'result'   => 'success',
                    'redirect' => $redirectUrl,
                ];
            } else {
                $error = $body['message'] ?? 'Neznáma chyba pri vytváraní platby.';
                wc_add_notice('Chyba vytvorenia platby: ' . esc_html($error), 'error');
                return;
            }
        }

        public function get_payment_status($transId)
        {
            $data = [
                'merchant' => $this->merchant,
                'transId'  => $transId,
                'secret'   => $this->secret,
            ];

            $response = wp_remote_post('https://payments.comgate.cz/v1.0/status', [
                'body'    => http_build_query($data),
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept'       => 'application/x-www-form-urlencoded',
                ],
                'timeout' => 20,
            ]);

            if (is_wp_error($response)) {
                return false;
            }

            parse_str(wp_remote_retrieve_body($response), $result);

            if (isset($result['status'])) {
                return strtoupper($result['status']);
            }

            return false;
        }
    }
});

// Spracovanie návratu z Comgate (s overením cez API)
add_action('template_redirect', function () {
    if (!empty($_GET['comgatereturn']) && !empty($_GET['order_id'])) {

        $order_id = intval($_GET['order_id']);
        $order = wc_get_order($order_id);

        if (!$order) {
            wp_redirect(home_url('/noorder?stav=chyba'));
            exit;
        }

        $transId = $order->get_meta('comgate_trans_id');
        if (empty($transId)) {
            wp_redirect(home_url('/notransid?stav=chyba'));
            exit;
        }

        // Získaj platobnú bránu
        $gateways = WC()->payment_gateways->payment_gateways();
        if (empty($gateways['comgate'])) {
            wp_redirect(home_url('/nocomgate?stav=chyba'));
            exit;
        }

        /** @var WC_Gateway_Comgate $comgate */
        $comgate = $gateways['comgate'];

        $payment_status = $comgate->get_payment_status($transId);
        if (!$payment_status) {
            wp_redirect(home_url('/paymentst?stav=chyba'));
            exit;
        }

        $thankUrl = $order->get_checkout_order_received_url();

        switch ($payment_status) {
            case 'PAID':
                $order->payment_complete();
                $order->add_order_note('Platba cez Comgate úspešne zaplatená. (' . $transId . ')');
                wp_redirect(add_query_arg('payment_status', 'success', $thankUrl));
                break;
            case 'CANCELLED':
                $order->update_status('cancelled', 'Platba cez Comgate bola zrušená. (' . $transId . ')');
                wp_redirect(add_query_arg('payment_status', 'cancelled', $thankUrl));
                break;
            case 'PENDING':
                $order->update_status('on-hold', 'Platba cez Comgate je čakajúca. (' . $transId . ')');
                wp_redirect(add_query_arg('payment_status', 'pending', $thankUrl));
                break;
            case 'AUTHORIZED':
                $order->update_status('on-hold', 'Platba cez Comgate bola autorizovaná. (' . $transId . ')');
                wp_redirect(add_query_arg('payment_status', 'authorized', $thankUrl));
                break;
            default:
                wp_redirect(home_url('/moje-objednavky?stav=nezname'));
                break;
        }

        exit;
    }
});
