<?php
/**
 * Plugin Name: Aprop Admin Access Fix
 * Description: Keeps wp-login/wp-admin usable with Complianz soft cookiewall.
 */

add_filter( 'cmplz_cookiewall_active', function ( $active ) {
	$request = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
	if ( is_admin() || strpos( $request, 'wp-login.php' ) !== false || strpos( $request, '/wp-admin' ) !== false ) {
		return false;
	}
	return $active;
}, 100 );
