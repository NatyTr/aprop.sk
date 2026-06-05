<?php
/**
 * Plugin Name:       Toret Comgate
 * Plugin URI:        https://toret.cz/produkt/woo-comgate/
 * Description:       WooCommerce integrační plugin pro napojení na platební bránu Comgate.
 * Version:           1.4.3
 * Author:            toret.cz
 * Author URI:        toret.cz
 * Text Domain:       woo-comgate
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * WC requires at least: 5.0
 * WC tested up to: 5.4.1
 * Requires PHP: 7.2
 * Requires at least: 5.4
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


define( 'WCPDIR', plugin_dir_path( __FILE__ ) );
define( 'WCPURL', plugin_dir_url( __FILE__ ) );
define( 'WCPLANG', 'woo-comgate' );
define( 'WOOCOMGATESLUG', 'toret-comgate' );
define( 'WOOCOMGATE', 2943 );
define( 'WOOCOMGATESETTINGS', 'admin.php?page=woo-comgate-payment' );
define( 'WOOCOMGATELIC', 'woo-comgate-licence' );

if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    return false;
}

require_once( plugin_dir_path( __FILE__ ) . 'includes/plugin-update-checker-master/plugin-update-checker.php' );
$MyUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    'http://update.toret.cz/wp-update-server-master/?action=get_metadata&slug=woo-comgate',
    __FILE__,
    'woo-comgate'
);

require_once( plugin_dir_path( __FILE__ ) . 'includes/compatibility/toret_compatibility.php' );

require_once( plugin_dir_path( __FILE__ ) . 'includes/class_comgate_define.php' );

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'admin/includes/setting.php' );
require_once( plugin_dir_path( __FILE__ ) . 'admin/includes/puc.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/class-comgate-library.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/class-wc-gateway-comgate.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/class-wc-gateway-comgate-bank.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/class-comgate-log.php' );

require_once( plugin_dir_path( __FILE__ ) . 'public/class-woo-comgate.php' );

register_activation_hook( __FILE__, array( 'Woo_Comgate', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Woo_Comgate', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'Woo_Comgate', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-woo-comgate-admin.php' );
	add_action( 'plugins_loaded', array( 'Woo_Comgate_Admin', 'get_instance' ) );

}

/**
 * Custom endpoint
 *
 */  
add_action( 'init', 'comgate_add_json_endpoint' ); 
function comgate_add_json_endpoint() {
    add_rewrite_endpoint( 'comgate', EP_ALL );
}


/**
 *  Add template redirect
 *
 */
add_action( 'template_redirect', 'comegate_json_template_redirect' );   
function comegate_json_template_redirect() {
    
    global $wp_query;
 
    if ( ! isset( $wp_query->query_vars['comgate'] ) )
        return;
 
    if($wp_query->query_vars['comgate'] == 'notify'){
        header("HTTP/1.1 200 OK");
        include plugin_dir_path( __FILE__ ) . 'includes/views/notify.php';
    }
    if($wp_query->query_vars['comgate'] == 'paid'){
        header("HTTP/1.1 200 OK");
        include plugin_dir_path( __FILE__ ) . 'includes/views/paid.php';
    }
    if($wp_query->query_vars['comgate'] == 'delete'){
        header("HTTP/1.1 200 OK");
        include plugin_dir_path( __FILE__ ) . 'includes/views/delete.php';
    }
    if($wp_query->query_vars['comgate'] == 'failed'){
        header("HTTP/1.1 200 OK");
        include plugin_dir_path( __FILE__ ) . 'includes/views/failed.php';
    }
    exit;
}


/**
 *
 * Get current gateway
 *
 */
if(!function_exists('get_current_gateway')){
function get_current_gateway(){

	$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
		
    $current_gateway = null;
		
    $default_gateway = get_option( 'woocommerce_default_gateway' );
		if ( ! empty( $available_gateways ) ) {

		   // Chosen Method
			if ( isset( WC()->session->chosen_payment_method ) && isset( $available_gateways[ WC()->session->chosen_payment_method ] ) ) {
				
        $current_gateway = $available_gateways[ WC()->session->chosen_payment_method ];
			
      } elseif ( isset( $available_gateways[ $default_gateway ] ) ) {
				$current_gateway = $available_gateways[ $default_gateway ];
			} else {
				$current_gateway = current( $available_gateways );
			}
		}
		if ( ! is_null( $current_gateway ) )
			return $current_gateway;
		else 
			return false;
}
}

  
/**
 *
 * Current payment gateway setting
 *
 */   
if(!function_exists('get_current_gateway_settings')){ 
function get_current_gateway_settings( ) {
		if ( $current_gateway = get_current_gateway() ) {
			$settings = $current_gateway->settings;
			return $settings;
		}
		return false;
	}
}  
       
       
 /**
  * Save notify log
  *
  * @since 1.0.0  
  */        
  function comgate_save_notify_log( $data ){
  
    $log = Woo_Comgate_Log::get_instance();
    $log->save_log( $data );
  
}       
  