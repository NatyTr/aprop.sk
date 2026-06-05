<?php 

/**
 * @package   Toret Comgate
 * @author    toret.cz
 * @license   GPL-2.0+
 * @link      http://toret.cz
 * @copyright 2016 Toret.cz
 */

class Toret_Comgate_Define {

	/**
	 * Pole CZ platebních metod, používaných v Comgate
	 *
	 */
	static public function comgate_cz_payment_methods(){

		if ( false === ( $comgate_payment_methods = get_transient( 'comgate_cz_payment_methods' ) ) ) {

			$data = get_option('woocommerce_comgate_settings', array());
			$merchant = $data['merchant'];
			$sec = $data['secure_key'];
			$payment_methods= self::comgate_get_payment_methods( $merchant, $sec, 'https://payments.comgate.cz/v1.0/methods?merchant=', 'CZ' );

			if ( $payment_methods ) {
				update_option( 'woocommerce_comgate_cz_payment_methods', $payment_methods );
			}
			
			set_transient( 'comgate_cz_payment_methods', $payment_methods, MONTH_IN_SECONDS );

		} else {
		
			$payment_methods = get_option( 'woocommerce_comgate_cz_payment_methods' );
		
		}
	 
		return apply_filters( 'toret_comgate_cz_payment_methods', $payment_methods );
		
	}

	/**
	 * Pole SK platebních metod, používaných v Comgate
	 *
	 */
	static public function comgate_sk_payment_methods(){

        $data = get_option('woocommerce_comgate_settings', array());
        $merchant = $data['merchant'];
        $sec = $data['secure_key'];
        $payment_methods= self::comgate_get_payment_methods( $merchant, $sec, 'https://payments.comgate.cz/v1.0/methods?merchant=', 'SK' );

        if ( $payment_methods ) {
            update_option( 'woocommerce_comgate_sk_payment_methods', $payment_methods );
        }
	 
		return apply_filters( 'toret_comgate_sk_payment_methods', $payment_methods );
		
	}

	/**
	 * Pole všech platebních metod, používaných v Comgate
	 *
	 */
	static public function comgate_payment_methods(){

        $data = get_option('woocommerce_comgate_settings', array());
        if(isset($data)){
            $merchant = $data['merchant'];
            $sec = $data['secure_key'];
            $payment_methods = self::comgate_get_payment_methods( $merchant, $sec, 'https://payments.comgate.cz/v1.0/methods?merchant=' );

            if ( $payment_methods ) {
                update_option( 'woocommerce_comgate_payment_methods', $payment_methods );
            }

            return apply_filters( 'toret_comgate_payment_methods', $payment_methods );
        }else{
            $payment_methods = array(
                'CARD_ALL'          => __( 'Platební karta', WCPLANG ),
                'BANK_ALL'          => __( 'Bankovní platby', WCPLANG )
            );
            return apply_filters( 'toret_comgate_payment_methods', $payment_methods );
        }
	}

	/**
	 * Request to Comgate API
	 *
	 */
	static public function comgate_get_payment_methods( $merchant, $sec, $request_url, $country = false ) {

		$payment_methods = array(
			'CARD_ALL'          => __( 'Platební karta', WCPLANG ),
			'BANK_ALL'          => __( 'Bankovní platby', WCPLANG )
		);

		if ( false === $country ) {
			$country = '';
		} else {
			$country = '&country=' . $country;
		}

		$url = $request_url . $merchant . '&secret=' . $sec . '&type=xml' . $country;
		
		$xml = simplexml_load_file( $url );
		
		foreach( $xml->method as $met ) {
			$payment_methods[(string)$met->id] = __( ( string )$met->name, WCPLANG );
		}
	 
		return $payment_methods;

	}

	/**
	 * Get eshop language
	 *
	 * @since 1.3.3
	 * @return string
	 */           
	static public function get_eshop_lang(){

		if ( function_exists( 'icl_object_id' ) ) {
			$lang = ICL_LANGUAGE_CODE;
		}else{
			$lang = get_locale();
		}

		if ( $lang == 'cs_CZ' ) {
			$data_lang = 'cs';
		} elseif ( $lang == 'no_NO' ) {
			$data_lang     = 'no';
		} elseif ( $lang == 'sv_SE' ) {
			$data_lang     = 'se';
		} elseif ( $lang == 'sk_SK' ) {
			$data_lang     = 'sk';
		} elseif ( $lang == 'en_GB' ) {
			$data_lang     = 'en';
		} elseif ( $lang == 'en_US' ) {
			$data_lang     = 'en';
		} elseif ( $lang == 'pl_PL' ) {
			$data_lang     = 'pl';
		} elseif ( $lang == 'fr_FR' ) {
			$data_lang     = 'fr';
		} elseif ( $lang == 'ro_RO' ) {
			$data_lang     = 'ro';
		} elseif ( $lang == 'de_DE' ) {
			$data_lang     = 'de';
		} elseif ( $lang == 'de_AT' ) {
			$data_lang     = 'de';
		} elseif ( $lang == 'hu_HU' ) {
			$data_lang     = 'hu';
		} elseif ( $lang == 'sl_SI' ) {
			$data_lang     = 'si';
		} elseif ( $lang == 'hr_HR' ) {
			$data_lang     = 'hr';
		} elseif ( $lang == 'cs' ) {
			$data_lang     = 'cs';
		} else {
			$data_lang     = 'en';
		}

		return $data_lang;

	}   
	
}