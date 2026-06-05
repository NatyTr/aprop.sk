<?php 

add_action('plugins_loaded', 'woocommerce_gateway_comgate_bank_init', 0);

function woocommerce_gateway_comgate_bank_init(){

    if(!class_exists('WC_Payment_Gateway')) return;
 
        class WC_Gateway_Woo_Comgate_Bank extends WC_Payment_Gateway{ 
   
            /**
	        * __construct function.
	        *
	        * @access public
	        * @return void
	        */
            public function __construct(){    
        
                $licence_status = get_option('woo-comgate-licence');
                if ( empty( $licence_status ) ) {
	               return false;
                }
      
                $this->supports = array( 
                    'products',
                    'pre-orders' 
                );
               
                //Unique id for gateway
                $this -> id = 'comgatebank';
                //Gateway method title
                $this -> medthod_title = __('ComGate platba převodem', 'woo-comgate');
                //True if gateway has fields shown on checkout
                $this -> has_fields = false;
                //Define form field for admin setting
                $this -> init_form_fields();
                //Get settings form database
                $this -> init_settings();
      

                $this->icon                 = $this->settings['icon'];
                $this->title                = $this->settings['title'];
                $this->description          = $this->settings['description'];
                $this->merchant             = $this->settings['merchant'];
                $this->secure_key           = $this->settings['secure_key'];

                if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
                  	$langwpml = apply_filters( 'wpml_active_languages', NULL, 'orderby=id&order=desc' ); 
      
                  	if ( !empty( $langwpml ) ) {
                    	foreach( $langwpml as $key => $lang ) {
                      		$this->{"merchant$key"}   = $this->settings['merchant' . $key];  
                      		$this->{"secure_key$key"} = $this->settings['secure_key' . $key];  
                    	}
                  	} 
                } 

                $this->test                 = $this->settings['test'];
                $this->product_category     = $this->settings['product_category'];
                $this->enable_for_methods   = $this->get_option( 'enable_for_methods', array() ); 
                $this->enable_countries     = $this->get_option( 'enable_countries', array() );
  
                //Check Woocommerce verison
                if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
                    add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options' ) );
                } else {
                    add_action( 'woocommerce_update_options_payment_gateways', array($this, 'process_admin_options' ) );
                }
      
                //Receipt page
                add_action('woocommerce_receipt_comgatebank',  array( $this, 'receipt_page' ) );
      
            }
	
  
            /**
             * Form Fields For Payment Gateway Setting in Admin
             *
             * @since 1.0.0   
             */      
            public function init_form_fields(){
                global $woocommerce;

                $shipping_methods = array();
                $countries = WC()->countries->get_allowed_countries();

                if ( is_admin() )
                    foreach ( $woocommerce->shipping->load_shipping_methods() as $method ) {
                        $shipping_methods[ $method->id ] = $method->get_method_title();
                    }
        
                global $payment_methods;
                $pole = array(
					'enabled' => array(
						'title'       => __('Povolit/Zakázat', 'woo-comgate'),
						'type'        => 'checkbox',
						'label'       => __('Povolit Comgate platbu převodem.', 'woo-comgate'),
						'default'     => 'no'
					),
					'title' => array(
						'title'       => __('Titulek:', 'woo-comgate'),
						'type'        => 'text',
						'description' => __('Název platební metody. Zobrazí se při výběru platební metody.', 'woo-comgate'),
						'default'     => __('Comgate platba převodem', 'woo-comgate')
					),
					'description' => array(
						'title'       => __('Popis:', 'woo-comgate'),
						'type'        => 'textarea',
						'description' => __('Popis, který uživatel uvidí při výběru platební metody.', 'woo-comgate'),
						'default'     => __('Zaplaťte převodem na bankovní účet.', 'woo-comgate')
					),
					'icon' => array(
						'title'       => __('Ikona platební metody:', 'woo-comgate'),
						'type'        => 'text',
						'description' => __('Url ikony zobrazené při výběru platební metody.', 'woo-comgate')
					),    
					'merchant' => array(
						'title'       => __('Id obchodu', 'woo-comgate'),
						'type'        => 'text',
						'description' => __('Identifikátor propojení obchodu.' . (defined( 'ICL_LANGUAGE_CODE' ) ? ' Základní pro všechny případy které nespadají pod jazyky.' : ''),'woo-comgate')
					),
					'secure_key' => array(
						'title'       => __('Secure key', 'woo-comgate'),
						'type'        => 'text',
						'description' =>  __('Secure key e-shopu, které vám bylo přiděleno.' . (defined( 'ICL_LANGUAGE_CODE' ) ? ' Základní pro všechny případy které nespadají pod jazyky.' : ''), 'woo-comgate')
					),
					'test' => array(
						'title'       => __('Test mód', 'woo-comgate'),
						'type'        => 'checkbox',
						'description' =>  __('Aktivace/deaktivace testovacího prostředí ComGate.', 'woo-comgate'),
						'default'     => 'yes'
					),
					'enable_for_methods' => array(
								'title' 		  => __( 'Povolit způsob dopravy', 'woo-comgate' ),
								'type' 			  => 'multiselect',
								'class'			  => 'chosen_select',
								'css'			    => 'width: 450px;',
								'default' 		=> '',
								'description' => __( 'Pro povolení všech způsobů dopravy, zanechte pole prázdné.', 'woo-comgate' ),
								'options'		  => $shipping_methods,
								'desc_tip'    => true,
						),
					'enable_countries' => array(
								'title' 		  => __( 'Povolit ComGate pro země', 'woo-comgate' ),
								'type' 			  => 'multiselect',
								'class'			  => 'chosen_select',
								'css'			    => 'width: 450px;',
								'default' 		=> '',
								'description' => __( 'Vyberte, pro které země bude GoPay dostupná.', 'woo-comgate' ),
								'options'		  => $countries,
								'desc_tip'    => true,
						),
					'product_category' => array(
						'title'           => __( 'Druh zboží', 'woo-comgate' ),
						'type'            => 'select',
						'class'           => 'chosen_select',
						'css'               => 'width: 450px;',
						'default'       => 'PHYSICAL',
						'description' => __( 'Vyberte, druh zboží, jenž máte povolené v Comgate.', 'woo-comgate' ),
						'options'         => array( 
												'DIGITAL' => __( 'Digitální zboží', 'woo-comgate' ),
												'PHYSICAL' => __( 'Fyzické zboží', 'woo-comgate' ),
											),
						'desc_tip'    => true,
					)
        		);
            
				$usp = $pole;
				$pole = array();
				foreach( $usp as $keyu => $u ) {
					$pole[$keyu] = $u;
					if ( ( $keyu == 'secure_key' ) && (defined( 'ICL_LANGUAGE_CODE' ) ) ) {
						$langwpml = apply_filters( 'wpml_active_languages', NULL, 'orderby=id&order=desc' ); 
						if ( !empty( $langwpml ) ) {  
							foreach( $langwpml as $key => $lang ) {
								$pole['merchant' . $key] = array(
									'title'       => __('Id obchodu - ' . $key, 'woo-comgate'),
									'type'        => 'text',
									'description' => __('Identifikátor propojení obchodu','woo-comgate')
								);
								$pole['secure_key' . $key] = array(
									'title'       => __('Secure key - ' . $key, 'woo-comgate'),
									'type'        => 'text',
									'description' =>  __('Secure key e-shopu, které vám bylo přiděleno.', 'woo-comgate')
								);
							}  
						}
					}
				}
				$this -> form_fields = $pole;
    		}

			/**
			 * Check If The Gateway Is Available For Use
			 *
			 * @return bool
			 * @since 1.0.0   
			 */
			public function is_available() {

				if( is_admin() ){ return false; }
				
				$enable = false;
				$enable  = $this->is_available_for_country();  
				if($enable == true){ 
					if ( ! empty( $this->enable_for_methods ) ) {
						$enable = $this->is_available_for_shipping_method();
					}
					if( $enable == false ){
						$enable = $this->is_downloadable_product_in_cart();
					}	
					if( $enable == false ){
						$enable = $this->is_virtual_product_in_cart();
					}
				}
			
				if( $enable == false ){
					return false;
				}else{
					return parent::is_available();
				}
			
			}
		
			/**
			 * Check if is downloadable product in cart
			 *
			 * return true or false
			 * @since 1.0.0   
			 */            
			public function is_downloadable_product_in_cart() {
				
				$has_downloadable_item = false;

                if (isset(WC()->session)) {
                    if(!empty(WC()->session->cart->cart_contents)){
                        $cart_data = WC()->session->cart->cart_contents;
                    }else{
                        $cart_data = WC()->session->cart;
                    }
                    if ( isset( $cart_data ) ) {
                        foreach($cart_data as $item){

                                $product = wc_get_product( $item['product_id'] );
                            if ( $product->is_downloadable() ) {
                                        $has_downloadable_item = true;
                                }
                        }
                    }
                }
				return $has_downloadable_item;
					
			}
			
			/**
			 * Check if is virtual product in cart
			 *
			 * return true or false
			 * @since 1.0.0   
			 */            
			public function is_virtual_product_in_cart() {
			
				$has_virtual = true;
                if (isset(WC()->session)) {
                    if(!empty(WC()->session->cart->cart_contents)){
                        $cart_data = WC()->session->cart->cart_contents;
                    }else{
                        $cart_data = WC()->session->cart;
                    }
                    foreach($cart_data as $item){

                            $product = wc_get_product( $item['product_id'] );
                        if ( !$product->is_virtual() ) {
                                    $has_virtual = false;
                            }
                    }
				}
				return $has_virtual;
			
			}
			
			/**
			 * Check is payment method available for selected country
			 *
			 * return true or false
			 * @since 1.0.0   
			 */            
			public function is_available_for_country() {
				$country = toret_get_customer_country();               
				if ( ! empty( $this->enable_countries ) ) {
					if(!in_array($country,$this->enable_countries)){
					return false; 
					}else{
					return true;
					}    
				}else{
					return true;
				} 
			}
			
			
			/**
			 * Check is payment method available for selected shippping
			 *
			 * return true or false
			 * @since 1.0.0   
			 */            
			public function is_available_for_shipping_method() {
			
				
				$chosen_shipping_methods = $this->get_choosen_shipping_method();
				
				$check_method = $this->check_method($chosen_shipping_methods);
				if ( ! $check_method ){ return false; }

						//Set found to false
				$found = false;

						foreach ( $this->enable_for_methods as $method_id ) {
							if ( strpos( $check_method, $method_id ) === 0 ) {
								$found = true;
								break;
							}
						}
				//If not found return false, or if found return true
						if ( ! $found ){
							return false;
				}else{
					return true;
				}
			}
			
			
			/**
			 * Get choosen shipping method
			 *
			 * @since 1.0.0  
			 */        
			private function get_choosen_shipping_method(){
				
				$chosen_shipping_methods_session = WC()->session->get( 'chosen_shipping_methods' );

						if ( isset( $chosen_shipping_methods_session ) ) {
							$chosen_shipping_methods = array_unique( $chosen_shipping_methods_session );
						} else {
							$chosen_shipping_methods = array();
						}
			
				return $chosen_shipping_methods;
			}
			
			/**
			 * Check shipping method
			 *
			 * @since 1.0.0  
			 */ 
			private function check_method($chosen_shipping_methods){
			
			$check_method = false;
			if( !empty( $wp->query_vars['order-pay'] ) ){
				
				$order_id = absint( $wp->query_vars['order-pay'] );

				$order    = wc_get_order( $order_id );

				if ( is_page( $order->get_checkout_payment_url() ) ) {
				
				if ( $order->shipping_method ){ $check_method = $order->shipping_method; }
			

					} elseif ( empty( $chosen_shipping_methods ) || sizeof( $chosen_shipping_methods ) > 1 ) {
			
						$check_method = false;
					
				} elseif ( sizeof( $chosen_shipping_methods ) == 1 ) {
					
				$check_method = $chosen_shipping_methods[0];
					
				}
			}
			
			return $check_method;
		
			}



			/**
			 * Display admin info
			 * @since 1.1.0
			 */              
			public function admin_options(){
				echo '<h3>'.__('Comgate platební brána', 'woo-comgate').'</h3>';
				echo '<p>'.__('Comgate je platební brána pro online platby v České republice.', 'woo-comgate').'</p>';
				echo '<table class="form-table">';
				$this -> generate_settings_html();
				echo '</table>';
		
			}
			
			/**
			 * Receipt Page
			 * @since 1.1.0   
			 */
			function receipt_page($order){
				$this->generate_comgate_payment($order,'');        
			}
			
			/**
			 * Generate payment
			 * @since 1.1.0
			 */        
			public function generate_comgate_payment( $order_id,$defaultPaymentChannel ){ 

				$order = wc_get_order( $order_id );
				$order_number = $order->get_order_number();
				$comgate_order_id = apply_filters( 'comgate_order_id', $order_number, $order );
				/**
				 * Set language and currency for gateway
				 * With WMPL and Polylang compatibility     
				 *
				 */ 
				$order_currency = $order->get_currency();
				$language       = apply_filters( 'toret_comgate_gateway_language', Toret_Comgate_Define::get_eshop_lang(), $order ); 
				$price          = $order->get_total();    
	
				//Save order currency
				update_post_meta( $order_id, 'comgate_order_currency', $order_currency );
				update_post_meta( $order_id, 'comgate_order_lang', $language );
				update_post_meta( $order_id, 'comgate_price', $price );
				//Set country
				$country = $order->get_billing_country();
				//Set country
				if( $country != 'CZ' || $country != 'SK' || $country != 'PL' ){
					$country = 'ALL';
				}
				if($this->test == 'yes'){
					$test = true;
				}else{
					$test = false;      
				} 

				if(defined( 'ICL_LANGUAGE_CODE' )){
					$curlang = ICL_LANGUAGE_CODE;
					$paymentsProtocol = new AgmoPaymentsSimpleProtocol(
					'https://payments.comgate.cz/v1.0/create',
					($this->{"merchant$curlang"} != '' ? $this->{"merchant$curlang"} : $this->merchant),
					$test,
					($this->{"secure_key$curlang"} != '' ? $this->{"secure_key$curlang"} : $this->secure_key)
					);
				}else{
					$paymentsProtocol = new AgmoPaymentsSimpleProtocol(
					'https://payments.comgate.cz/v1.0/create',
					$this->merchant,
					$test,
					$this->secure_key
					);
				}
				
				$data = array(
					'order_id' => $order_id,
					'log' => serialize( $paymentsProtocol ),
					'context' => __( 'Payments protocol', 'woo-comgate' )
				);
				comgate_save_notify_log( $data );

				$product_category = $this->product_category;
				if( empty( $product_category ) ){ $product_category = 'PHYSICAL'; }
	
				try {       

			
					/**
					 *
					 *  Create Payment for COMGATE       
					 *
					 */                    
					$preauth  = false;
					$account = apply_filters( 'toret_comgate_send_account_bank', '', $order ); 
	
					// create new payment transaction
					$paymentsProtocol->createTransaction(
						$country,                       // country
						$price,                         // price
						$order_currency, // currency
						'Objednávka č: '.$comgate_order_id,     // label
						$comgate_order_id,                      // refId
						NULL,                           // payerId
						'STANDARD',                     // vatPL
						$product_category,                     // category
						//'PHYSICAL',                     // category
						'BANK_ALL',                     // method
						$account,                             //account
						$order->get_billing_email(),          //email
						$order->get_billing_phone(),          //phone
						'Objednávka č: ' . $comgate_order_id,     //name
						$language,      //lang
						false,                                                          //preauth 
						false,                                                          //reccurring
						null,                                                           //reccurring id
						false,                                                          //eet report
						null                                                            //eet data
					);
					$transId = $paymentsProtocol->getTransactionId();
					$redirect_url = $paymentsProtocol->getRedirectUrl();

					$data = array(
						'order_id' => $order_id,
						'log' => $transId,
						'context' => __( 'Transaction ID', 'woo-comgate' )
					);
					comgate_save_notify_log( $data );
					$data = array(
						'order_id' => $order_id,
						'log' => $redirect_url,
						'context' => __( 'Redirect url', 'woo-comgate' )
					);
					comgate_save_notify_log( $data );

					update_post_meta($order_id,'comgate_lang',$language);
					update_post_meta($order_id,'comgate_currency',$order_currency);
					update_post_meta( $order_id, 'comgate_transaction_id', $transId );
	
					// redirect to agmo payments system
					header('location: '.$redirect_url);
					exit;

				} catch (Exception $e) {
	
					$data = array(
						'order_id' => $order_id,
						'log' => $e->getMessage(),
						'context' => __( 'ComGate exception', 'woo-comgate' )
					);
					comgate_save_notify_log( $data );
					$order_key = $order->get_order_key();
					/**
					 *  Exception rediretion
					 */ 
					$location = $order->get_checkout_order_received_url();
					$url_args = array(
						'order-pay'   => $order_id,
						'key'         => $order_key,
						'error-info'  => 'selhalo-vytvoreni-platby'
					);
					$location = add_query_arg($url_args,$location); 
					header("Location: " . $location );
					exit;
			
				}   

			}
	

			/**
			 * Process the payment and return the result
			 *
			 * @since 1.0.0        
			**/
	
			function process_payment( $order_id ){    
				$order = wc_get_order( $order_id );
				$order_key = $order->get_order_key();
				// Reduce stock levels
				if( $version === false ){
					$order->reduce_order_stock();
				}else{
					wc_reduce_stock_levels( $order_id );
				}
	
				return array(
					'result' => 'success', 
					'redirect' => add_query_arg(
						'order-pay',
						$order_id, 
						add_query_arg(
							'key', 
							$order_key, 
							get_the_permalink( wc_get_page_id( 'pay' ) )
						)
					)            
				);
	
												
			}

			/**
			 * Get order price for ComGate Payment Gateway
			 * with Multi Currency Switcher compatibility
			 *
			 * @since 1.0.0  
			 */
			private function get_payment_price($order_id,$curency_lang_data){
				
				$active_plugins = get_option('active_plugins');
				/**
				 * WOOCS fix
				 *
				 */              
				if(in_array('woocommerce-currency-switcher/index.php', $active_plugins)){
				
				$woocs = get_option('woocs');
				
				$rate      = $woocs[$curency_lang_data['currency']]['rate'];
				$sub_price = $this->get_order_total();
				
				if(!empty($rate)){ 
					$rate_price = $sub_price * $rate; 
				}else{ 
					$rate_price = $sub_price; 
				}
				
				$decimals   = get_option('woocommerce_price_num_decimals');
				$rate_price = round($rate_price,$decimals); 
				$price      = (int)($rate_price);
				
				}else{
				
				$price = (int)($this->get_order_total());
				
				}      
				return $price;     
			}                 
			
			
			/**
			 * Get WPML selected currency
			 * Check if 
			 *   
			 * @since 1.0.0  
			 */         
			private function get_wpml_currency(){
			
				global $woocommerce_wpml;
					if(!empty($woocommerce_wpml)){
					if(property_exists($woocommerce_wpml,'multi_currency_support')){
						$selected_currency = $woocommerce_wpml->multi_currency_support->get_client_currency();
					}else{
						$selected_currency = null;
					}
					}else{
					$selected_currency = null;
					}
				
				return $selected_currency;
				
			} 
			
			/**
			 * Get eshop currency
			 *
			 * @@since 1.0.0  
			 */        
			private function get_eshop_currency(){
				
				//Get WPML Currency
				$selected_wpml_currency = $this->get_wpml_currency();
				
				if($selected_wpml_currency != null){
					$currency = $selected_wpml_currency;
				}else{
					$currency = get_woocommerce_currency();
				}
				
				return $currency;
				
			} 													 
			
		}//Class end
    
    
   	/**
   	 *  Add the Gateway to WooCommerce
   	 *  @since 1.1.0     
   	 */             
  	function woocommerce_add_woo_gateway_comgate_bank($methods) {
        $methods[] = 'WC_Gateway_Woo_Comgate_Bank';
        return $methods;
    }
 
    //Woocommerce payment gateways filter
    add_filter('woocommerce_payment_gateways', 'woocommerce_add_woo_gateway_comgate_bank' );
    
    
}//woocommerce_gateway_comgate_init end
    
