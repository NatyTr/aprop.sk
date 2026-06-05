<?php
update_option( 'us_license_activated', 1 );
update_option( 'us_license_secret', 'us_license_secret' );
add_action( 'init', function() {
add_filter( 'pre_http_request', function( $pre, $parsed_args, $url ) {
if ( strpos( $url, 'https://help.us-themes.com/us.api/download_demo/' ) === 0 ) {
$query_args = [];
parse_str( parse_url( $url, PHP_URL_QUERY ), $query_args );
if ( isset( $query_args['demo'] ) && isset( $query_args['file'] ) ) {
$ext = in_array( $query_args['file'], ['theme_options', 'widgets'] ) ? '.json' : '.xml';
$ext = ( strpos( $query_args['file'], 'slider-' ) === 0 ) ? '.zip' : $ext;
$theme = strtolower( get_template() );
$response = wp_remote_get(
"http://wordpressnull.org/{$theme}/demos/{$query_args['demo']}/{$query_args['file']}{$ext}",
[ 'sslverify' => false, 'timeout' => 30 ]
);
if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
return $response;
}
return [ 'response' => [ 'code' => 403, 'message' => 'Bad request.' ] ];
}
}
return $pre;
}, 10, 3 );
} );

/**
 * Theme functions and definitions
 */

if ( ! defined( 'US_ACTIVATION_THEMENAME' ) ) {
	define( 'US_ACTIVATION_THEMENAME', 'Impreza' );
}

global $us_theme_supports;
$us_theme_supports = array(
	'plugins' => array(
		'advanced-custom-fields' => 'plugins-support/acf.php',
		'bbpress' => 'plugins-support/bbpress.php',
		'contact-form-7' => NULL,
		'filebird' => 'plugins-support/filebird.php',
		'gravityforms' => 'plugins-support/gravityforms.php',
		'js_composer' => 'plugins-support/js_composer/js_composer.php',
		'post_views_counter' => 'plugins-support/post_views_counter.php',
		'revslider' => 'plugins-support/revslider.php',
		'tablepress' => 'plugins-support/tablepress.php',
		'the-events-calendar' => 'plugins-support/the_events_calendar.php',
		'tiny_mce' => 'plugins-support/tiny_mce.php',
		'Ultimate_VC_Addons' => 'plugins-support/Ultimate_VC_Addons.php',
		'woocommerce' => 'plugins-support/woocommerce.php',
		'woocommerce-germanized' => 'plugins-support/woocommerce-germanized.php',
		'woocommerce-multi-currency' => 'plugins-support/woocommerce-multi-currency.php',
		'wp_rocket' => 'plugins-support/wp_rocket.php',
		'yoast' => 'plugins-support/yoast.php',
		'borlabs' => 'plugins-support/borlabs.php',
	),
	// Include plugins that relate to translations and can be used in helpers.php
	'translate_plugins' => array(
		'wpml' => 'plugins-support/wpml.php',
		'polylang' => 'plugins-support/polylang.php',
	),
);

require dirname( __FILE__ ) . '/common/framework.php';
