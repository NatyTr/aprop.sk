<?php
/**
 * @package   Toret Comaptibility
 * @author    toret.cz
 * @license   GPL-2.0+
 * @link      http://toret.cz
 * @copyright 2017 Toret.cz
 */



if( !class_exists( 'Toret_Order_Compatibility' ) ){

	class Toret_Order_Compatibility{


		/**
		 * Return order id
		 *
		 */
    	static public function get_order_id( $order ){

    		$version = toret_check_wc_version();

        	if( $version === false ){

            	$order_id = $order->id;   
                    
        	}else{

	            $order_id = $order->get_id();            	

        	}

        	return $order_id;

    	}

    	/**
		 * Return order total
		 *
		 */
    	static public function get_order_total( $order ){

    		$version = toret_check_wc_version();

        	if( $version === false ){

            	$order_total = $order->get_total();   
                    
        	}else{

	            $order_total = $order->get_total();            	

        	}

        	return $order_total;

    	}

    	/**
		 * Return order currency
		 *
		 */
    	static public function get_order_currency( $order ){

    		$version = toret_check_wc_version();

        	if( $version === false ){

            	$currency = $order->get_order_currency();   
                    
        	}else{

	            $currency = $order->get_currency();            	

        	}

        	return $currency;

    	}

    	/**
		 * Return order status
		 *
		 */
    	static public function get_order_status( $order ){

    		$version = toret_check_wc_version();

        	if( $version === false ){

            	$status = $order->get_status();   
                    
        	}else{

	            $status = $order->get_status();            	

        	}

        	return $status;

    	}

    	/**
		 * Return payment title
		 *
		 */
    	static public function get_payment_title( $order ){

    		$version = toret_check_wc_version();

        	if( $version === false ){

            	$title = $order->payment_method_title;   
                    
        	}else{
        		$order_data = $order->get_data();

	            $title = $order_data ['payment_method_title'];            	

        	}

        	return $title;

    	}
    	/**
		 * Return payment title
		 *
		 */
    	static public function get_payment( $order ){

    		$version = toret_check_wc_version();

        	if( $version === false ){

            	$payment = get_post_meta( $order->id, '_payment_method', true );   
                    
        	}else{

        		$order_data = $order->get_data();

	            $payment = $order_data ['payment_method'];            	

        	}

        	return $payment;

    	}

        /**
         * Return billing email
         *
         */
        static public function get_billing_email( $order ){

            $version = toret_check_wc_version();

            if( $version === false ){

                $email = $order->billing_email;
                    
            }else{
                
                $order_data = $order->get_data();

                $email = $order_data['billing']['email'];              

            }

            return $email;

        }

        /**
         * Return billing country
         *
         */
        static public function get_billing_country( $order ){

            $version = toret_check_wc_version();

            if( $version === false ){

                $email = $order->billing_country;
                    
            }else{
                
                $order_data = $order->get_data();

                $email = $order_data['billing']['country'];              

            }

            return $email;

        }
        /**
         * Return billing email
         *
         */
        static public function get_billing_data( $order ){

            $version = toret_check_wc_version();
            $data = array();

            if( $version === false ){

                $data['first_name']     = $order->billing_first_name;
                $data['last_name']      = $order->billing_last_name;
                $data['full_name']      = $order->billing_first_name.' '.$order->billing_last_name;
                $data['city']           = $order->billing_city;
                $data['address_1']      = $order->billing_address_1;
                $data['postcode']            = $order->billing_zip;
                $data['email']          = $order->billing_email;
                $data['phone']          = $order->billing_phone;
                                    
            }else{
                
                $order_data = $order->get_data();

                $data['first_name']     = $order_data['billing']['first_name'];
                $data['last_name']      = $order_data['billing']['last_name'];
                $data['full_name']      = $order_data['billing']['first_name'].' '.$order_data['billing']['last_name'];
                $data['city']           = $order_data['billing']['city'];
                $data['address_1']      = $order_data['billing']['address_1'];
                $data['postcode']            = $order_data['billing']['postcode'];
                $data['email']          = $order_data['billing']['email'];
                $data['phone']          = $order_data['billing']['phone'];             

            }

            return $data;

        }

        /**
         * Return order date created
         *
         */
        static public function get_date_created( $order ){

            $version = toret_check_wc_version();

            if( $version === false ){

                $date = $order->order_date;   
                    
            }else{
                
                 $date = $order->get_date_created();              

            }

            return $date;

        }

        /*
         * Return order key
         *
         */
        static public function get_order_key( $order ){

            $version = toret_check_wc_version();

            if( $version === false ){

                $date = $order->order_key;   
                    
            }else{
                
                 $date = $order->get_order_key();              

            }

            $date = apply_filters( 'comgate_custom_order_id', $date, $order  );
            return $date;

        }


    

	}

}