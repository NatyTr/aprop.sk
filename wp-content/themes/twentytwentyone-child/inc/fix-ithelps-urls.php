<?php
/**
 * Rewrite leftover staging URLs (aprop.ithelps.sk) to the current site URL.
 * Fixes Google Ads "compromised / malicious content" flags after staging → production migration.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param mixed $value
 * @return mixed
 */
function aprop_rewrite_ithelps_urls( $value ) {
	if ( is_array( $value ) ) {
		foreach ( $value as $key => $item ) {
			$value[ $key ] = aprop_rewrite_ithelps_urls( $item );
		}
		return $value;
	}

	if ( ! is_string( $value ) || $value === '' || stripos( $value, 'aprop.ithelps.sk' ) === false ) {
		return $value;
	}

	// Host-only replace also covers Yoast JSON-LD escaped URLs (https:\/\/aprop.ithelps.sk\/...).
	$host = wp_parse_url( home_url(), PHP_URL_HOST );
	if ( ! is_string( $host ) || $host === '' ) {
		return $value;
	}

	return str_ireplace( 'aprop.ithelps.sk', $host, $value );
}

add_filter( 'option_footer_image', 'aprop_rewrite_ithelps_urls' );
add_filter( 'wp_get_attachment_url', 'aprop_rewrite_ithelps_urls' );
add_filter( 'wp_get_attachment_image_src', 'aprop_rewrite_ithelps_urls' );
add_filter( 'the_content', 'aprop_rewrite_ithelps_urls', 99 );
add_filter( 'widget_text', 'aprop_rewrite_ithelps_urls', 99 );
add_filter( 'style_loader_src', 'aprop_rewrite_ithelps_urls', 99 );
add_filter( 'script_loader_src', 'aprop_rewrite_ithelps_urls', 99 );
add_filter( 'wp_calculate_image_srcset', 'aprop_rewrite_ithelps_urls', 99 );

// Catch Elementor / ACF / leftover absolute URLs in final HTML (what Google Ads crawls).
add_action(
	'template_redirect',
	function () {
		if ( is_admin() ) {
			return;
		}
		ob_start( 'aprop_rewrite_ithelps_urls' );
	},
	0
);
