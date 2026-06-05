<?php 

add_action( 'plugins_loaded', 'woocommerce_gateway_comgate_init', 0 );

function woocommerce_gateway_comgate_init(){

    if( !class_exists( 'WC_Payment_Gateway' ) ) return;
 
        class WC_Gateway_Woo_Comgate extends WC_Payment_Gateway{ 
   
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
            $this -> id = 'comgate';
            //Gateway method title
            $this -> medthod_title = __('ComGate platba kartou', 'woo-comgate');
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

            
            if(defined( 'ICL_LANGUAGE_CODE' )){
                $langwpml = apply_filters( 'wpml_active_languages', NULL, 'orderby=id&order=desc' ); 
                if(!empty($langwpml)){
                    foreach($langwpml as $key => $lang){
                    $this->{"merchant$key"}             = $this->settings['merchant' . $key];  
                    $this->{"secure_key$key"}             = $this->settings['secure_key' . $key];  
                    }
                }
              }            
            
            $this->test                 = $this->settings['test'];
            $this->enable_for_methods   = $this->get_option( 'enable_for_methods', array() );
            $this->enable_countries     = $this->get_option( 'enable_countries', array() );
            $this->product_category     = $this->settings['product_category'];
            $this->enable_comgate_methods     = $this->settings['enable_comgate_methods']; 
  

      
            //Check Woocommerce verison
            if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options' ) );
            } else {
                add_action( 'woocommerce_update_options_payment_gateways', array($this, 'process_admin_options' ) );
            }

            //Save cstom data
            add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_custom_data' ) );
                  
        }
	  
        /**
         * Form Fields For Payment Gateway Setting in Admin
         *
         * @since 1.0.0   
         */      
        public function init_form_fields(){

            
            $shipping_methods = array();
            $countries = WC()->countries->get_allowed_countries();
            $payment_methods  = Toret_Comgate_Define::comgate_payment_methods();

            delete_option('_transient_timeout_comgate_cz_payment_methods');
            delete_option('_transient_comgate_cz_payment_methods');

            if ( is_admin() ){
                foreach ( WC()->shipping->load_shipping_methods() as $method ) {
                    $shipping_methods[ $method->id ] = $method->get_method_title();
                }
            }
        
            $pole = array(
                'enabled' => array(
                    'title'       => __('Povolit/Zakázat', 'woo-comgate'),
                    'type'        => 'checkbox',
                    'label'       => __('Povolit Comgate platební kartu.', 'woo-comgate'),
                    'default'     => 'no'
                ),
                'title' => array(
                    'title'       => __('Titulek:', 'woo-comgate'),
                    'type'        => 'text',
                    'description' => __('Název platební metody. Zobrazí se při výběru platební metody.', 'woo-comgate'),
                    'default'     => __('Comgate platební karta', 'woo-comgate')
                ),
                'description' => array(
                    'title'       => __('Popis:', 'woo-comgate'),
                    'type'        => 'textarea',
                    'description' => __('Popis, který uživatel uvidí při výběru platební metody.', 'woo-comgate'),
                    'default'     => __('Zaplaťte kreditní kartou.', 'woo-comgate')
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
                    'description' =>  __('Aktivace/deaktivace testovacího prostředí Comgate.', 'woo-comgate'),
                    'default'     => 'yes'
                ),             
                'enable_comgate_methods' => array(
                    'title'         => __( 'Povolit Comgate platební metody', 'woo-comgate' ),
                    'type'          => 'multiselect',
                    'class'         => 'chosen_select',
                    'css'           => 'width: 450px;',
                    'default'       => '',
                    'description'   => __( 'Vyberte, které platební metody budou dostupné.', 'woo-comgate' ),
                    'options'       => $payment_methods,
                    'desc_tip'      => true,
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
				    'description' => __( 'Vyberte, pro které země bude ComGate dostupná.', 'woo-comgate' ),
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
            foreach($usp as $keyu => $u){
                $pole[$keyu] = $u;
                if(($keyu == 'secure_key')&&(defined( 'ICL_LANGUAGE_CODE' ))){
                    $langwpml = apply_filters( 'wpml_active_languages', NULL, 'orderby=id&order=desc' ); 
                    if(!empty($langwpml)){
                        foreach($langwpml as $key => $lang){
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
                
                if( is_admin() ) {
                    return false;
                }

                $enable = false;
                $enable  = $this->is_available_for_country();

                if($enable == true){

                    //return parent::is_available();

                    if ( ! empty( $this->enable_for_methods ) ) {
                        $enable = $this->is_available_for_shipping_method();
                    }

                }

                if( $enable == false ){
                    $enable = $this->is_downloadable_product_in_cart();
                }
                if( $enable == false ){
                    $enable = $this->is_virtual_product_in_cart();
                }

                if( $enable === false ){
                    return false;
                }else{
                    return parent::is_available();
                }

            }

        /**
         * Check if is downloadable product in cart
         *
         * return true or false
         */            
        public function is_downloadable_product_in_cart() {
    
            $has_downloadable_item = false;
            $cart_data = $this->get_cart_content();
            if( !empty( $cart_data ) ){
                foreach( $cart_data as $item ){
        
                    $product = wc_get_product( $item['product_id'] );
                    if ( $product->is_downloadable() ) {
                        $has_downloadable_item = true;
                    } 

                } 
            }
            return $has_downloadable_item;
        
        }
  
        /**
         * Check if is virtual product in cart
         *
         * return true or false
         * @since 1.1.4
         */            
        public function is_virtual_product_in_cart() {
   
            $has_virtual = true;
            $cart_data = $this->get_cart_content();
            if( !empty( $cart_data ) ){
                foreach( $cart_data as $item ){
        
                    $product = wc_get_product( $item['product_id'] );
                    if ( !$product->is_virtual() ) {
                        $has_virtual = false;
                    } 

                } 
            }
            return $has_virtual;
        }

        /**
         * Získáme obsah košíku
         *
         * @since 1.1.4
         */            
        private function get_cart_content() {
   
            if( !empty( WC()->session->cart->cart_contents ) ){
                $cart_data = WC()->session->cart->cart_contents;
            }else{
                $cart_data = WC()->session->cart;
            }
    
            return $cart_data;
   
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
                if( !in_array( $country,$this->enable_countries ) ){
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
      
            $check_method = $this->check_method( $chosen_shipping_methods );
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

            if (isset(WC()->session)) {
                $chosen_shipping_methods = WC()->customer->get_shipping_country();
                if (!empty($chosen_shipping_methods)) {
                    $chosen_shipping_methods = is_array($chosen_shipping_methods) ? array_unique( $chosen_shipping_methods ) : array();
                } else {
                    $chosen_shipping_methods = array();
                }
            }else{
                $chosen_shipping_methods = '';
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
            if ( is_page( wc_get_page_id( 'checkout' ) ) && ! empty( $wp->query_vars['order-pay'] ) ) {

				$order_id = absint( $wp->query_vars['order-pay'] );
				$order    = wc_get_order( $order_id );
        
                if ( $order->shipping_method ){ 
                    $check_method = $order->shipping_method; 
                }

			}elseif ( empty( $chosen_shipping_methods ) || sizeof( $chosen_shipping_methods ) > 1 ) {
      
				$check_method = false;
			
            }elseif ( sizeof( $chosen_shipping_methods ) == 1 ) {
			
                $check_method = $chosen_shipping_methods[0];
			
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
         * Process the payment and return the result
         *
         * @since 1.0.0        
         **/     
        function process_payment( $order_id ){    
        
            $order            = wc_get_order( $order_id );
            $order_key        = $order->get_order_key();
            $order_number     = $order->get_order_number();
            $comgate_order_id = apply_filters( 'comgate_order_id', $order_number, $order );

            /**
             * Set language and currency for gateway
             * With WMPL and Polylang compatibility     
             *
             */ 
            $order_currency = $order->get_currency();
            $language       = apply_filters( 'toret_comgate_gateway_language', Toret_Comgate_Define::get_eshop_lang(), $order ); 

            $price = $order->get_total();
        
            //Save order currency
            update_post_meta( $order_id,'comgate_order_currency', $order_currency );
            update_post_meta( $order_id,'comgate_order_lang', $language );
            update_post_meta( $order_id,'comgate_price', $price );

            //Set country
            $country = $order->get_billing_country();
            if( $country != 'CZ' || $country != 'SK' || $country != 'PL' ){
                $country = 'ALL';
            }
            
            if($this->test == 'yes'){
                $test = true;
            }else{
                $test = false;      
            }    

            if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
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

            try {       
            
                /**
                 *
                 *  Create Payment for COMGATE       
                 *
                 */                    
                $preauth  = false;
                global $wp;
                $product_category = $this->product_category;
                if( empty( $product_category ) ){ $product_category = 'PHYSICAL'; }

                $paymentchannel = get_post_meta( $order_id, '_selected_comgate_paymentchannel', true );
                if( empty( $paymentchannel ) ){ $paymentchannel = 'ALL'; }
                //Fix for order pay page
                if ( is_page( wc_get_page_id( 'checkout' ) ) && ! empty( $wp->query_vars['order-pay'] ) ) {
                    $paymentchannel = 'ALL';
                }
    
                $account = apply_filters( 'toret_comgate_send_account', '', $order ); 

                // create new payment transaction
                $paymentsProtocol->createTransaction(
                    $country,                                                       // country
                    $price,                                                         // price
                    $order_currency,                                                // currency
                    __( 'Objednávka č:', 'woo-comgate' ).' '.$comgate_order_id,     // label
                    $comgate_order_id,                                              // refId
                    NULL,                                                           // payerId
                    '',                                                             // vatPL
                    $product_category,                                              // category
                    $paymentchannel,                                                // method
                    $account,                                                       //account
                    $order->get_billing_email(),                                    //email
                    $order->get_billing_phone(),                                    //phone
                    __( 'Objednávka č:', 'woo-comgate' ).' '.$comgate_order_id,     //name
                    $language,                                                      //lang
                    false,                                                          //preauth 
                    false,                                                          //reccurring
                    null,                                                           //reccurring id
                    false,                                                          //eet report
                    null                                                            //eet data
                );

                $transId      = $paymentsProtocol->getTransactionId();
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

                update_post_meta( $order_id, 'comgate_lang', $language );
                update_post_meta( $order_id, 'comgate_currency', $order_currency );
                update_post_meta( $order_id, 'comgate_transaction_id', $transId );

                return array(
                    'result'   => 'success', 
                    'redirect' => $redirect_url
                );
                
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
                $location = add_query_arg( $url_args, $location ); 
                    header("Location: " . $location );
                exit;
            }   


        
            
		    
        
        
                                                 
        }
 
        /**
         * Reduce stock
         * @since 1.1.0   
         */ 
        public function reduce_stock( $order ) {
            
            $version = toret_check_wc_version();

            // Reduce stock levels
            if( $version === false ){
                $order->reduce_order_stock();
            }else{
                wc_reduce_stock_levels( $order_id );
            }

        } 

        /**
         * Přidání zobrazení výběru platebních metod v popisu platební metody
         * 
         * 1.1.4
         */        
        public function payment_fields() {
        
            if ( $description = $this->get_description() ) {
                echo wpautop( wptexturize( $description ) );
            }

            echo $this->comgate_payment_channel();

            if ( $this->supports( 'default_credit_card_form' ) ) {
                $this->credit_card_form();
            }

        }

        /**
         * Zobrazení výběru platebních metod v popisu platební metody
         * 
         * @sincce 1.4.0
         */     
        public function comgate_payment_channel(){
   
            $country = toret_get_customer_country();
            $html = '';
   
            if( $country == 'CZ' ){
                $payment_methods = Toret_Comgate_Define::comgate_cz_payment_methods();
            }elseif( $country == 'SK' ){
                $payment_methods = Toret_Comgate_Define::comgate_sk_payment_methods();
            }else{
            	$payment_methods = Toret_Comgate_Define::comgate_cz_payment_methods();
            }

            $enable_comgate_methods = apply_filters( 'comgate_checkout_enabled_methods', $this->enable_comgate_methods );
            
            if( !empty( $enable_comgate_methods ) ){
                
                $i = 1;
    
                foreach( $payment_methods as $key => $item ){
      
                    if( in_array( $key, $enable_comgate_methods ) ){
            
                        if( $i == 1 ){ $checked = 'checked="checked"'; }else{ $checked = ''; }
                        if($key == 'eu_gp_u'){
                            $url = WCPURL.'public/assets/images/eu_gp_u.jpg';

                            $url = apply_filters( 'toret_comgate_custom_image', $url, $key );

                            $html .= '
                                <label class="comgate_select">
                                    <input class="comgate_select_input" name="comgate_paymentchannel" type="radio" '.$checked.' id="eu_gp_kb" value="eu_gp_kb">
                                    <img src="' . $url . '" /> 
                                    <span>'.__( $item, 'woo-comgate' ).'</span>
                                </label>
                            ';  
                        }elseif($key == 'BANK_ALL'){
                            $url = WCPURL.'public/assets/images/BANK_ALL.jpg';

                            $url = apply_filters( 'toret_comgate_custom_image', $url, $key );

                            $html .= '
                                <label class="comgate_select">
                                    <input class="comgate_select_input" name="comgate_paymentchannel" type="radio" '.$checked.' id="BANK_ALL" value="BANK_ALL">
                                    <img src="'.$url.'" /> 
                                    <span>'.__( $item, 'woo-comgate' ).'</span>
                                </label>
                            ';  
                        }else{      
                            $url = 'https://payments.comgate.cz/assets/images/logos/'.$key.'.png?v=1.0';

                            $url = apply_filters( 'toret_comgate_custom_image', $url, $key );

                            $html .= '
                                <label class="comgate_select">
                                    <input class="comgate_select_input" name="comgate_paymentchannel" type="radio" '.$checked.' id="'.$key.'" value="'.$key.'">
                                    <img src="' . $url . '" /> 
                                    <span>'.__( $item, 'woo-comgate' ).'</span>
                                </label>
                            ';
                        }
                        $i++;
                    }
      
                }
            }else{
       
                $html .= '
                    <div class="comgate_select" style="display:none!important;">
                        <input class="comgate_select_input" name="comgate_paymentchannel" type="radio" checked="checked" id="ALL" value="ALL">
                    </div>
                ';
            }   
   
            return $html;
   
        }

        /**
         * Save custom order data
         * Save payment channel  
         *
         * @since 1.4.0
         */
        public function save_custom_data( $order_id ){
  
            if( !empty( $_POST['comgate_paymentchannel'] ) ){
                $selected_paymentchannel = sanitize_text_field( $_POST['comgate_paymentchannel'] );
                update_post_meta( $order_id, '_selected_comgate_paymentchannel', $selected_paymentchannel );
            }
  
        }   
  
    }//Class end
    
    
    /**
     *  Add the Gateway to WooCommerce
     *  @since 1.1.0     
     */             
    function woocommerce_add_woo_gateway_comgate($methods) {
        $methods[] = 'WC_Gateway_Woo_Comgate';
        return $methods;
    }
 
    //Woocommerce payment gateways filter
    add_filter('woocommerce_payment_gateways', 'woocommerce_add_woo_gateway_comgate' );
    
    
}//woocommerce_gateway_comgate_init end
    
