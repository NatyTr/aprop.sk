<?php
/*
Plugin Name: ith-comgate-payment-gateway
Description: Jednoduchá platobná brána Comgate pre WooCommerce s overením stavu cez API.
Version: 1.5.2
*/

if (!defined('ABSPATH')) exit;

// Spracovanie server-to-server notifikácie z Comgate.
// Comgate posiela POST parameter "name", ktorý WordPress inak vyhodnotí ako query var
// a požiadavka /?comgate=notify skončí 404 ešte pred spracovaním platby.
add_action('init', 'ith_comgate_handle_notify_request', 20);

function ith_comgate_handle_notify_request()
{
    if (
        ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST'
        || empty($_GET['comgate'])
        || sanitize_key(wp_unslash($_GET['comgate'])) !== 'notify'
    ) {
        return;
    }

    if (!function_exists('wc_get_order')) {
        ith_comgate_notify_response(503, 'WooCommerce unavailable');
    }

    $settings = get_option('woocommerce_comgate_settings', []);
    $merchant = trim((string) ($settings['merchant'] ?? ''));
    $secret = trim((string) ($settings['secret'] ?? ''));
    $data = ith_comgate_notify_request_data();

    $request_merchant = trim((string) ($data['merchant'] ?? ''));
    $request_secret = trim((string) ($data['secret'] ?? ''));
    $status = strtoupper(sanitize_text_field((string) ($data['status'] ?? '')));
    $trans_id = sanitize_text_field((string) ($data['transId'] ?? ''));
    $ref_id = sanitize_text_field((string) ($data['refId'] ?? ''));

    if ($merchant === '' || $secret === '') {
        ith_comgate_notify_response(500, 'Comgate is not configured');
    }

    if ($request_merchant !== $merchant || !hash_equals($secret, $request_secret)) {
        ith_comgate_notify_response(403, 'Invalid Comgate credentials');
    }

    if ($status === '' || $trans_id === '' || $ref_id === '') {
        ith_comgate_notify_response(400, 'Missing Comgate notification data');
    }

    $order = ith_comgate_find_notify_order($ref_id, $trans_id);
    if (!$order) {
        ith_comgate_notify_response(404, 'Order not found');
    }

    if ($order->get_status() === 'trash') {
        ith_comgate_notify_response(200, 'OK');
    }

    $previous_status = (string) $order->get_meta('comgate_status');
    $order->update_meta_data('comgate_status', $status);
    $order->update_meta_data('comgate_trans_id', $trans_id);
    $order->update_meta_data('_comgate_refId', $ref_id);

    if ($previous_status !== $status) {
        switch ($status) {
            case 'PAID':
                if (!$order->is_paid()) {
                    $order->payment_complete($trans_id);
                }
                $order->add_order_note('Comgate notifikácia: platba úspešne zaplatená. (' . $trans_id . ')');
                break;
            case 'CANCELLED':
                if (!$order->is_paid()) {
                    $order->update_status('cancelled', 'Comgate notifikácia: platba bola zrušená. (' . $trans_id . ')');
                } else {
                    $order->add_order_note('Comgate notifikácia: prijatý stav CANCELLED pre už zaplatenú objednávku. (' . $trans_id . ')');
                }
                break;
            case 'PENDING':
                if (!$order->is_paid()) {
                    $order->update_status('on-hold', 'Comgate notifikácia: platba čaká na dokončenie. (' . $trans_id . ')');
                }
                break;
            case 'AUTHORIZED':
                if (!$order->is_paid()) {
                    $order->update_status('on-hold', 'Comgate notifikácia: platba bola autorizovaná. (' . $trans_id . ')');
                }
                break;
            default:
                $order->save();
                ith_comgate_notify_response(400, 'Unknown Comgate status');
        }
    } else {
        $order->save();
    }

    ith_comgate_notify_response(200, 'OK');
}

function ith_comgate_notify_request_data()
{
    if (!empty($_POST)) {
        return wp_unslash($_POST);
    }

    $content_type = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? ''));
    if (strpos($content_type, 'application/json') === false) {
        return [];
    }

    $raw_body = isset($GLOBALS['ith_comgate_notify_raw_body'])
        ? (string) $GLOBALS['ith_comgate_notify_raw_body']
        : file_get_contents('php://input');

    if ($raw_body === false || trim($raw_body) === '') {
        return [];
    }

    $decoded = json_decode($raw_body, true);
    return is_array($decoded) ? $decoded : [];
}

function ith_comgate_find_notify_order($ref_id, $trans_id)
{
    foreach (['comgate_trans_id', 'comgate_transaction_id'] as $meta_key) {
        $orders = wc_get_orders([
            'limit'      => 1,
            'return'     => 'objects',
            'meta_key'   => $meta_key,
            'meta_value' => $trans_id,
            'status'     => array_keys(wc_get_order_statuses()),
        ]);

        if (!empty($orders[0])) {
            return $orders[0];
        }
    }

    $orders = wc_get_orders([
        'limit'      => 1,
        'return'     => 'objects',
        'meta_key'   => '_comgate_refId',
        'meta_value' => $ref_id,
        'status'     => array_keys(wc_get_order_statuses()),
    ]);

    if (!empty($orders[0])) {
        return $orders[0];
    }

    $order_id = 0;
    if (preg_match('/^sechashcomg(\d+)$/', $ref_id, $matches)) {
        $order_id = (int) $matches[1];
    } elseif (ctype_digit($ref_id)) {
        $order_id = (int) $ref_id;
    }

    return $order_id ? wc_get_order($order_id) : false;
}

function ith_comgate_notify_response($status_code, $message)
{
    status_header($status_code);
    header('Content-Type: text/plain; charset=utf-8');
    echo $message;
    exit;
}

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
