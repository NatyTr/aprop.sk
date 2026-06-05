<?php

add_filter('puc_request_info_result-woo-comgate', 'woo_comgate_refreshLicenseFromPluginInfo', 10, 2);

function woo_comgate_refreshLicenseFromPluginInfo($pluginInfo, $result) {
    //Verify that this is an OK response.
    if ( !is_wp_error($result)
        && isset($result['response']['code'])
        && ($result['response']['code'] == 200)
        && !empty($result['body'])
    ) {
        $apiResponse = json_decode($result['body']);

        if ( $apiResponse ) {
            if ( $apiResponse->licence_check && $apiResponse->licence_check != 'ok') {
                update_option( 'woo-comgate-licence-server-check', $apiResponse->licence_check );
            }else{
                update_option( 'woo-comgate-licence-server-check', '' );
            }
        }
    }
    //Return the plugin metadata unmodified.
    return $pluginInfo;
}

$MyUpdateChecker->addQueryArgFilter('woocomgate');
function woocomgate($queryArgs) {
    $licence = get_option('woo-comgate-licence-key');
    if ( !empty($licence) ) {
        $queryArgs['license_key'] = $licence;
    }
    return $queryArgs;
}

add_action( 'in_plugin_update_message-woo-comgate/woo-comgate.php', 'woo_comgate_addUpgradeMessageLink', 10,2 );

function woo_comgate_addUpgradeMessageLink($data, $response) {
    $licence = get_option('woo-comgate-licence-server-check', 'Pro více informací klikněte na odkaz "Zkontrolovat aktualizace".');
    echo $licence;
}

function woo_comgate_custom_cron_schedule( $schedules ) {
    $schedules['every_twelve_hours'] = array(
        'interval' => 43200, // Every 12 hours
        'display'  => __( 'Every 12 hours' ),
    );
    return $schedules;
}
add_filter( 'cron_schedules', 'woo_comgate_custom_cron_schedule' );

//Schedule an action if it's not already scheduled
if ( ! wp_next_scheduled( 'woo_comgate_cron_hook' ) ) {
    wp_schedule_event( time(), 'every_twelve_hours', 'woo_comgate_cron_hook' );
}

///Hook into that action that'll fire every twelve hours
add_action( 'woo_comgate_cron_hook', 'control_woo_comgate_licence_litecont' );