<?php
  
    $line = $_SERVER['REQUEST_URI'];
    
    if( empty( $_GET['refId'] ) ){
        $data = array(
                'order_id' => 10000000,
                'log' => __( 'Error: prázdné id, nebo refId', 'woo-comgate' ),
                'context' => __( 'Zaplacená platba', 'woo-comgate' )
            );
        comgate_save_notify_log( $data );
        $data = array(
                'order_id' => 10000000,
                'log' => serialize( $line ),
                'context' => __( 'Zaplacená platba, data requestu', 'woo-comgate' )
            );
        comgate_save_notify_log( $data );
        //die('Selhal pokus o kontrolu zaplacené platby!');
    }
        
    $order_id = apply_filters( 'toret_comgate_custom_id', $_GET['refId'] );
    $order_id = apply_filters( 'toret-sequential-order-id', $order_id );
    
    //Compatibility for Sequential Number
    if( function_exists( 'wc_sequential_order_numbers' ) ){
        $order_id = wc_sequential_order_numbers()->find_order_by_order_number( $order_id );
    }
    //Compatibility for Sequential Number Pro
    if( function_exists( 'wc_seq_order_number_pro' ) ){
        $order_id = wc_seq_order_number_pro()->find_order_by_order_number( $order_id );
    }
    
    $comgate_order = wc_get_order( $order_id );
    $order_key     = $comgate_order->get_order_key();
    $order_status  = $comgate_order->get_status();
  
    $data = array(
        'order_id' => $order_id,
        'log' => serialize( $line ),
        'context' => __( 'Zaplacená platba, data requestu', 'woo-comgate' )
    );
    comgate_save_notify_log( $data );
  
    /**
     * Zpracovat pouze objednavku, ktera jeste nebyla zaplacena
     *     
	 */
       
    if ( $order_status != 'processing' AND $order_status != 'completed' ) {        
  
        //Get order meta
        $order_meta = get_post_meta( $order_id );
      
        if( empty( $order_meta['comgate_transaction_id'][0] ) ){

            $data = array(
                'order_id' => $order_id,
                'log' => __( 'Error: prázdné id platby', 'woo-comgate' ),
                'context' => __( 'Zaplacená platba', 'woo-comgate' )
            );
            comgate_save_notify_log( $data );
            
            $url_args = array(
                'order-received' => $order_id,
                'key'            => $order_key,
                'error-info'     => 'neplatne-cislo-platby'
            );
            $location = $comgate_order->get_checkout_order_received_url();
            $location = add_query_arg($url_args, $location); 
            header("Location: " . $location );
            exit;
        } 
      
      
        $url_args = array();
        /**
         * Set order status but must check virtual or not virtual
         *       
         */             
      
        $items = $comgate_order->get_items();
        $has_virtual = true;
        foreach($items as $item){
            $product = wc_get_product( $item['variation_id'] ? $item['variation_id'] : $item['product_id'] );
            if( !$product->is_virtual() ){
                $has_virtual = false;
            }
        }
        
        if( $has_virtual ){
            $status = apply_filters( 'toret_comgate_custom_status_paid_virtual', 'completed' );
            $comgate_order->update_status($status);
            $url_args['success-info'] = 'zaplacena';
            $url_args['status']       = 'completed';
            $data = array(
                'order_id' => $order_id,
                'log' => __( 'Stav objednávky Completed - objednávka má pouze virtuální produkty', 'woo-comgate' ),
                'context' => __( 'Zaplacená platba', 'woo-comgate' )
            );
            comgate_save_notify_log( $data );
        }else{
            $status = apply_filters( 'toret_comgate_custom_status_paid', 'processing' );
            $comgate_order->update_status($status);
            $url_args['success-info'] = 'zaplacena';
            $url_args['status']       = 'processing';   
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
            $comgate_order->reduce_order_stock();
        }else{
            wc_reduce_stock_levels( $order_id );
        }
      

  
        $location = $comgate_order->get_checkout_order_received_url();
        $url_args['order-received']  = $order_id;
        $url_args['key']             = $order_key;
       
        $location = add_query_arg( $url_args, $location );
    
		header('Location: ' . $location );
		exit;      
        
    }else{
        $data = array(
            'order_id' => $order_id,
            'log' => __( 'Error: objednávka není ve stavu pro zpracování', 'woo-comgate' ),
            'context' => __( 'Zaplacená platba', 'woo-comgate' )
        );
        comgate_save_notify_log( $data );
        $url_args = array(
            'order-received' => $order_id,
            'key'            => $order_key,
            'error-info'     => 'neplatny-stav-objednavky'
        );
        $location = $comgate_order->get_checkout_order_received_url();
        $location = add_query_arg($url_args,$location); 
	    header("Location: " . $location );
	    exit; 
      
    }
      
      