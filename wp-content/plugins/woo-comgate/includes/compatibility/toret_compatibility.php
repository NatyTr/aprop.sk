<?php
/**
 * @package   Toret Comaptibility
 * @author    toret.cz
 * @license   GPL-2.0+
 * @link      http://toret.cz
 * @copyright 2017 Toret.cz
 */

/**
 *
 * Check WooCommerce version
 *
 */ 
if( !function_exists( 'toret_check_wc_version' ) ){

    function toret_check_wc_version( $version = '2.6.14' ){
        if ( function_exists( 'WC' ) && ( version_compare( WC()->version, $version, ">" ) ) ) {
            return true;
        }else{
            return false;
        }
    }   
}


/**
 * Get customer country
 *
 * return $country
 */ 
if( !function_exists( 'toret_get_customer_country' ) ){
	
	function toret_get_customer_country(){

  		$version = toret_check_wc_version();

  		if( $version === false ){

			$country = WC()->customer->__get('country');   
					
		}else{

  		    if (is_null(WC()->customer)){

  		        $country = get_option("woocommerce_default_country");

            }else{

                $shipping_country = WC()->customer->get_shipping_country();

                if( !empty( $shipping_country ) ){

                    $country = WC()->customer->get_shipping_country();

                }else{

                    $country = WC()->customer->get_billing_country();

                }

            }

		}

		return $country;

  	}

}

include( 'toret_order_compatibility.php' );
include( 'toret_product_compatibility.php' );

include( 'toret_order_product_line_compatibility.php' );
include( 'toret_order_shipping_line_compatibility.php' );
include( 'toret_order_fee_line_compatibility.php' );

