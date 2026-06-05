<?php

    $line = $_SERVER['REQUEST_URI'];
    

    $option = get_option('woocommerce_comgate_settings');
    if(!empty($option['test']) && $option['test'] == 'yes'){
        $test = true;
    }else{
        $test = false;      
    }
    $paymentsProtocol = new AgmoPaymentsSimpleProtocol(
              'https://payments.comgate.cz/v1.0/create',
              $option['merchant'],
              $test,
              $option['secure_key'] 
    );
 
        
            $order_id = apply_filters( 'toret_comgate_custom_id', $_POST['refId'] );
            $order_id = apply_filters( 'toret-sequential-order-id', $order_id );

            if( function_exists( 'wc_sequential_order_numbers' ) ){
                $order_id = wc_sequential_order_numbers()->find_order_by_order_number( $order_id );
            }
            //Compatibility for Sequential Number Pro
            if( function_exists( 'wc_seq_order_number_pro' ) ){
                $order_id = wc_seq_order_number_pro()->find_order_by_order_number( $order_id );
            }

            $data = array(
                'order_id' => $order_id,
                'log' => serialize( $line ),
                'context' => __( 'Url notifikace', 'woo-comgate' )
            );
            comgate_save_notify_log( $data );
            $data = array(
                'order_id' => $order_id,
                'log' => serialize( $_POST ),
                'context' => __( 'Notifikace', 'woo-comgate' )
            );
            comgate_save_notify_log( $data );

            $trans_id = get_post_meta( $order_id, 'comgate_trans_id', true );

            /**
             * Pokud je trans id již uloženo, proběhl návrat z platební brány, nebo již byla vykonána notifikace
             * V případě, že se uložené trans id neshoduje se zaslaným, jedná se o platu "sirotka" a je třeba říci Comgate
             * že tato platba nebyla a nebude provedena
             */
            if( !empty( $trans_id ) && $trans_id != $_POST['transId'] ){

                echo 'code=0&message=OK';
                header("HTTP/1.1 200 OK");
                exit();

            }

            $order = wc_get_order( $order_id );

            if( $order === false ){

                echo 'code=0&message=OK';
                header("HTTP/1.1 200 OK");
                exit();

            }

            if(($order->get_payment_method() == 'comgate')||($order->get_payment_method() == 'comgatebank')){

                $order_key = Toret_Order_Compatibility::get_order_key( $order );
                $order_status = Toret_Order_Compatibility::get_order_status( $order );

                //Veřejná data
                if( !empty( $_POST['method'] ) ){
                    update_post_meta($order_id, 'comgate_method', sanitize_text_field($_POST['method']));
                }
                if( !empty( $_POST['status'] ) ){
                    update_post_meta($order_id, 'comgate_status', sanitize_text_field($_POST['status']));
                }
                if( !empty( $_POST['transId'] ) ){
                    update_post_meta($order_id, 'comgate_trans_id', sanitize_text_field($_POST['transId']));
                }
                //Chráněná data
                if( !empty( $_POST['merchant'] ) ){            
                    update_post_meta($order_id, '_comgate_merchant', sanitize_text_field($_POST['merchant']));
                }
                if( !empty( $_POST['refId'] ) ){
                    update_post_meta($order_id, '_comgate_refId', sanitize_text_field($_POST['refId']));
                }

                if( !empty( $_POST['status'] ) ){

                    $default_blocked_statuses = array( 'processing', 'completed' );
                    $blocked_statuses = apply_filters( 'comgate_blocked_statuses', $default_blocked_statuses );

                    if ( !in_array($order_status, $blocked_statuses) ){ 

                        if( $_POST['status'] == 'PAID' ){
        

                                $items = $order->get_items();
                                $has_virtual = true;
                                foreach($items as $item){
                                    $product = wc_get_product( $item['variation_id'] ? $item['variation_id'] : $item['product_id'] );
                                    if( !$product->is_virtual() ){
                                        $has_virtual = false;
                                    }
                                }
                
                                if( $has_virtual ){
                                    $status = apply_filters( 'toret_comgate_custom_status_paid_virtual', 'completed' );
                                    $order->update_status($status);
                                    $data = array(
                                        'order_id' => $order_id,
                                        'log' => __( 'Stav objednávky Completed - objednávka má pouze virtuální produkty', 'woo-comgate' ),
                                        'context' => __( 'Zaplacená platba', 'woo-comgate' )
                                    );
                                    comgate_save_notify_log( $data );
                                }else{
                                    $status = apply_filters( 'toret_comgate_custom_status_paid', 'processing' );
                                    $order->update_status($status);
                                    $data = array(
                                        'order_id' => $order_id,
                                        'log' => __( 'Stav objednávky Processing - objednávka má i hmotné produkty', 'woo-comgate' ),
                                        'context' => __( 'Zaplacená platba', 'woo-comgate' )
                                    );
                                    comgate_save_notify_log( $data );
                                }
                
                                $version = toret_check_wc_version();

                                // Reduce stock levels
                                if( $version === false ){
                                    $order->reduce_order_stock();
                                }else{
                                    wc_reduce_stock_levels( $order_id );
                                }

                                //$order->update_status( 'completed', __( 'Comgate status = PAID', 'woocommerce' ) );
                        
                        }
                        elseif( $_POST['status'] == 'CANCELLED' ){
                            $status = apply_filters( 'toret_comgate_custom_status_notify_failed', 'failed' );
                            $order->update_status( $status, __( 'Comgate status = CANCELLED', 'woocommerce' ) );
                        }

                }



                echo 'code=0&message=OK';
                header("HTTP/1.1 200 OK");
                exit();

            }else{
                echo 'code=0&message=OK';
                header("HTTP/1.1 200 OK");
                exit();
            }                
 
        //$paymentsProtocol->checkTransactionStatus( $_POST );
        
    }else{
        // return ERROR
        //echo 'code=1&message='.urlencode( $e->getMessage() );
        echo 'code=0&message=OK';
        header("HTTP/1.1 200 OK");
        exit();

    }
  
  