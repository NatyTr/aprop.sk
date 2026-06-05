<?php
/**
 * @package   Toret Comaptibility
 * @author    toret.cz
 * @license   GPL-2.0+
 * @link      http://toret.cz
 * @copyright 2017 Toret.cz
 */



if( !class_exists( 'Toret_Order_Shipping_Line_Compatibility' ) ){

	class Toret_Order_Shipping_Line_Compatibility{


		/**
		 * Return shipping line item total
		 *
		 */
    	static public function get_shipping_exist( $order ){

    		$version = toret_check_wc_version();

        	if( $version === false ){

            	if( (int)$order->order_shipping > 0 ){

            		return true;

            	}
                    
        	}else{

	            $shippings = $order->get_items( 'shipping' );
				
				if( !empty( $shippings ) ){

					return true;
				}        	

        	}

        	return false;

    	}


		/**
		 * Return shipping line item total
		 *
		 */
    	static public function get_shipping_line_total( $order ){

    		$version = toret_check_wc_version();

        	if( $version === false ){

            	$total = $order->get_total_shipping();   
                    
        	}else{

	            $shippings = $order->get_items( 'shipping' );
				foreach(  $shippings as $shipping ){
    					
    				$total = $shipping->get_total();
    					
				}         	

        	}

        	return $total;

    	}

    	/**
		 * Return shipping line item tax
		 *
		 */
    	static public function get_shipping_line_total_tax( $order ){

    		$version = toret_check_wc_version();

        	if( $version === false ){

            	$tax = $order->get_shipping_tax();   
                    
        	}else{

	            $shippings = $order->get_items( 'shipping' );
				foreach(  $shippings as $shipping ){
    					
    				$tax = $shipping->get_total_tax();
    					
				}         	

        	}

        	return $tax;

    	}

    	/**
		 * Return shipping line item method title
		 *
		 */
    	static public function get_shipping_line_title( $order ){

    		$version = toret_check_wc_version();

        	if( $version === false ){

            	$title = $order->get_shipping_method();   
                    
        	}else{

	            $shippings = $order->get_items( 'shipping' );
				foreach(  $shippings as $shipping ){
    					
    				$title = $shipping->get_method_title();
    					
				}         	

        	}

        	return $title;

    	}
		

    

	}

}