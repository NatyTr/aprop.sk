<?php

    $line = $_SERVER['REQUEST_URI'];
    
    if( empty( $_GET['refId'] ) ){
        $data = array(
                'order_id' => 10000000,
                'log' => __( 'Error: prázdné id, nebo refId', 'woo-comgate' ),
                'context' => __( 'Zrušená platba', 'woo-comgate' )
            );
        comgate_save_notify_log( $data );
        $data = array(
                'order_id' => 10000000,
                'log' => serialize($line),
                'context' => __( 'Zrušená platba, data requestu', 'woo-comgate' )
            );
        comgate_save_notify_log( $data );
        die('Selhal pokus o kontrolu zrušené platby!');
    }
        
    if( !is_numeric($_GET['refId']) ){
        $data = array(
                'order_id' => 10000000,
                'log' => __( 'Error: refId není platné číslo objednávky', 'woo-comgate' ),
                'context' => __( 'Zrušená platba', 'woo-comgate' )
            );
        comgate_save_notify_log( $data );
        $data = array(
                'order_id' => 10000000,
                'log' => serialize($line),
                'context' => __( 'Zrušená platba, data requestu', 'woo-comgate' )
            );
        comgate_save_notify_log( $data );
        die('Error: refId není platné číslo objednávky');     
    }

    $order_id = apply_filters( 'toret_comgate_custom_id', $_GET['refId'] );

    if( function_exists( 'wc_sequential_order_numbers' ) ){
        $order_id = wc_sequential_order_numbers()->find_order_by_order_number( $order_id );
    }
    //Compatibility for Sequential Number Pro
    if( function_exists( 'wc_seq_order_number_pro' ) ){
        $order_id = wc_seq_order_number_pro()->find_order_by_order_number( $order_id );
    }
    
    $comgate_order = wc_get_order( $order_id );
    $order_key = Toret_Order_Compatibility::get_order_key( $comgate_order );
    $order_status = Toret_Order_Compatibility::get_order_status( $comgate_order );

    $data = array(
        'order_id' => $order_id,
        'log' => serialize( $line ),
        'context' => __( 'Zrušená platba, data requestu', 'woo-comgate' )
    );
    comgate_save_notify_log( $data );
          
        //Get order meta
        $order_meta = get_post_meta( $order_id) ;
      
        $url_args = array();
      
        $order = wc_get_order( $order_id );
        if(($order->get_payment_method() == 'comgate')||($order->get_payment_method() == 'comgatebank')){

            $status = apply_filters( 'toret_comgate_custom_status_delete', 'failed' );
            $comgate_order->update_status($status);
            
            $url_args['error-info'] = 'zrusena';
            $url_args['status']     = 'failed';
            $data = array(
                'order_id' => $order_id,
                'log' => __( 'Stav objednávky Failed - platba byla zrušena', 'woo-comgate' ),
                'context' => __( 'Zrušená platba', 'woo-comgate' )
            );
            comgate_save_notify_log( $data );
    
            $location = $comgate_order->get_checkout_order_received_url();
            $url_args['order-received']  = $order_id;
            $url_args['key']             = $order_key;
       
            $location = add_query_arg( $url_args, $location );
    
		        header('Location: ' . $location );
		        exit;      
        }
      
     