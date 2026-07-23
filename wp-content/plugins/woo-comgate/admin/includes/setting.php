<?php 


add_action( 'admin_init', 'check_woo_comgate_licence' );
add_action( 'init', 'check_woo_comgate_licence' );

/**
 *
 * Check if licence is active
 *
 */
if(!function_exists('check_woo_comgate_licence')){
    function check_woo_comgate_licence(){
        $licence_status = get_option('woo-comgate-licence');
        if(!empty($licence_status)){
            if($licence_status=='active'){
                global $lic;
                $lic = 'active';
            }
        }
    }
}

/**
 *
 * Control licence
 *
 */
if(!function_exists('control_woo_comgate_licence')){
    function control_woo_comgate_licence($licence){

        $ip = $_SERVER['REMOTE_ADDR'];

        global $wpdb;
        $siteurl = $wpdb->get_row( "SELECT * FROM $wpdb->options WHERE option_name = 'siteurl'" );

        $api_params = array(
            'licence' => $licence,
            'ip' 	    => $ip,
            'url'     => $siteurl->option_value,
            'slug'    => 'woo-comgate'
        );

        // Call the custom API.
        $response = wp_remote_post( 'http://licence.toret.cz/wp-content/plugins/plc/heavycontrol.php', array( 'timeout' => 35, 'sslverify' => false, 'body' => $api_params ) );

        // make sure the response came back okay
        if ( is_wp_error( $response ) ){
            return false;
        }else{
            control_woo_comgate_licence_lic_cont($response['body'], $licence);
        }
    }
}

if(!function_exists('control_woo_comgate_licence_litecont')){
    function control_woo_comgate_licence_litecont(){
        $licence = get_option('woo-comgate-licence-key');

        global $wpdb;
        $siteurl = $wpdb->get_row( "SELECT * FROM $wpdb->options WHERE option_name = 'siteurl'" );

        $api_params = array(
            'licence' => $licence,
            'url'     => $siteurl->option_value,
            'slug'    => 'woo-comgate'
        );

        // Call the custom API.
        $response = wp_remote_post( 'http://licence.toret.cz/wp-content/plugins/plc/litecontrol.php', array( 'timeout' => 35, 'sslverify' => false, 'body' => $api_params ) );

        // make sure the response came back okay
        if ( is_wp_error( $response ) ){
            return false;
        }else{
            control_woo_comgate_licence_lic_cont($response['body'], $licence);
        }
    }
}

if(!function_exists('control_woo_comgate_licence_lic_cont')){
    function control_woo_comgate_licence_lic_cont($status, $licence){
        if($status=='ok'){
            update_option('woo-comgate-licence','active');
            update_option('woo-comgate-info', '<div class="notice-success updated toret-padding">' . __('Vaše licence byla aktivována.','woo-comgate') . '</div>');
            update_option('woo-comgate-licence-key',$licence);
        }elseif($status=='fail'){
            update_option('woo-comgate-info','<div class="notice error -licence-notice toret-padding">' . __('Neplatný licenční klíč.<br />Prosím, kontaktujte podporu na webu <a href="https://www.toret.cz">Toret.cz</a>.','woo-comgate') . '</div>');
            update_option('woo-comgate-licence','');
            update_option('woo-comgate-licence-key',$licence);
        }elseif($status=='double'){
            update_option('woo-comgate-info','<div class="notice error -licence-notice toret-padding">' . __('Zadaný licenční klíč neodpovídá URL webu. <br />Prosím, zkontrolujte si licenční klíč v sekci <a href="https://toret.cz/muj-ucet/">Můj účet</a>. V případě dalších problémů kontaktujte podporu na webu <a href="https://www.toret.cz">Toret.cz</a>.','woo-comgate') . '</div>');
            update_option('woo-comgate-licence','');
            update_option('woo-comgate-licence-key',$licence);
        }elseif($status=='empty'){
            update_option('woo-comgate-info','<div class="notice error -licence-notice toret-padding">' . __('Nemáte zadaný licenční klíč. <br />Prosím, kontaktujte podporu na webu <a href="https://www.toret.cz">Toret.cz</a>.','woo-comgate') . '</div>');
            update_option('woo-comgate-licence','');
            update_option('woo-comgate-licence-key',$licence);
        }else{
            update_option('woo-comgate-info','<div class="notice error -licence-notice toret-padding">' . __('Neplatný licenční klíč.<br />Prosím, kontaktujte podporu na webu <a href="https://www.toret.cz">Toret.cz</a>.','woo-comgate') . '</div>');
            update_option('woo-comgate-licence','');
            update_option('woo-comgate-licence-key',$licence);
        }
    }
}

