<?php
/**
 * @package   Toret Comaptibility
 * @author    toret.cz
 * @license   GPL-2.0+
 * @link      http://toret.cz
 * @copyright 2017 Toret.cz
 */



if( !class_exists( 'Toret_Order_Fee_Line_Compatibility' ) ){

	class Toret_Order_Fee_Line_Compatibility{


		/**
		 * Return shipping line item total
		 *
		 */
    	static public function get_lines( $order ){

    		$version = toret_check_wc_version();

        	if( $version === false ){

            	$lines = $order->get_fees();
                    
        	}else{

	            $lines = $order->get_items( 'fee' );
							
        	}

        	return $lines;

    	}

        /**
         * Return line total tax
         *
         */
        static public function get_line_title( $item ){

            $version = toret_check_wc_version();

            if( $version === false ){

                $title = $item['name'];   
                    
            }else{

                $title = $item->get_name();
                        
            }

            return $title;

        }

    	/**
		 * Return line total
		 *
		 */
    	static public function get_line_total( $item ){

    		$version = toret_check_wc_version();

        	if( $version === false ){

            	$total = $item['line_total'];   
                    
        	}else{

	            $total = $item->get_total();
                if( empty( $total) ){
                    $total = 0;
                }
    					
        	}

        	return $total;

    	}

    	/**
		 * Return line total tax
		 *
		 */
    	static public function get_line_total_tax( $item ){

    		$version = toret_check_wc_version();

        	if( $version === false ){

            	$total_tax = $item['line_tax'];   
                    
        	}else{

	            $total_tax = $item->get_total_tax();
                if( empty( $total_tax) ){
                    $total_tax = 0;
                }
    					
        	}

        	return $total_tax;

    	}


	}

}