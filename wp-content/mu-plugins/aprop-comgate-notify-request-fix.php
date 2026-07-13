<?php
/**
 * Plugin Name: Aprop Comgate Notify Request Fix
 * Description: Prevents Comgate notification POST fields from being interpreted as WordPress query vars.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Comgate sends POST fields named "name" and "cat". WordPress treats both as
 * public query vars when parsing the main request, which can turn
 * /?comgate=notify into a 404 before the gateway plugin can process it.
 */
add_action(
    'init',
    function () {
        if (
            ( $_SERVER['REQUEST_METHOD'] ?? '' ) !== 'POST'
            || ! isset( $_GET['comgate'] )
            || sanitize_key( wp_unslash( $_GET['comgate'] ) ) !== 'notify'
        ) {
            return;
        }

        $GLOBALS['aprop_comgate_notify_original_post'] = $_POST;

        foreach ( array( 'name', 'cat' ) as $key ) {
            if ( array_key_exists( $key, $_POST ) ) {
                unset( $_POST[ $key ] );
            }

            if ( array_key_exists( $key, $_REQUEST ) && ! isset( $_GET[ $key ] ) ) {
                unset( $_REQUEST[ $key ] );
            }
        }
    },
    0
);
