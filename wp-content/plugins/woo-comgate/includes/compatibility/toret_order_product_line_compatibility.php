<?php
/**
 * @package   Toret Comaptibility
 * @author    toret.cz
 * @license   GPL-2.0+
 * @link      http://toret.cz
 * @copyright 2017 Toret.cz
 */



if( !class_exists( 'Toret_Order_Product_Line_Compatibility' ) ){

	class Toret_Order_Product_Line_Compatibility{


		/**
		 * Return shipping line item total
		 *
		 */
    	static public function get_lines( $order ){

    		$version = toret_check_wc_version();

        	if( $version === false ){

            	$lines = $order->get_items();
                    
        	}else{

	            $lines = $order->get_items();
							
        	}

        	return $lines;

    	}


		/**
		 * Return shipping line item total
		 *
		 */
    	static public function get_product_id( $item ){

    		$version = toret_check_wc_version();

        	if( $version === false ){

            	$product_id = $item['variation_id'] ? $item['variation_id'] : $item['product_id'];   
                    
        	}else{

	            $product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();          	

        	}

        	return $product_id;

    	}

    	/**
		 * Return line subtotal
		 *
		 */
    	static public function get_line_subtotal( $item ){

    		$version = toret_check_wc_version();

        	if( $version === false ){

            	$subtotal = $item['line_subtotal'];   
                    
        	}else{

	            $subtotal = $item->get_subtotal();
                if( empty( $subtotal) ){
                    $subtotal = 0;
                }
    					
        	}

        	return $subtotal;

    	}

    	/**
		 * Return line subtotal tax
		 *
		 */
    	static public function get_line_subtotal_tax( $item ){

    		$version = toret_check_wc_version();

        	if( $version === false ){

            	$subtotal_tax = $item['line_subtotal_tax'];   
                    
        	}else{

	            $subtotal_tax = $item->get_subtotal_tax();
                if( empty( $subtotal_tax) ){
                    $subtotal_tax = 0;
                }
    					
        	}

        	return $subtotal_tax;

    	}


	}

}