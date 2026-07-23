<?php
defined( 'ABSPATH' ) or die( "you do not have access to this page!" );

require_once(cmplz_path . 'pro/tcf/tcf.php');
require_once(cmplz_path . 'pro/class-geoip.php');
require_once(cmplz_path . 'pro/class-document.php');
require_once(cmplz_path . 'pro/class-statistics.php');
require_once(cmplz_path . 'pro/functions.php');
require_once(cmplz_path . 'pro/filters-actions.php');
require_once(cmplz_path . 'pro/cron.php');

if ( is_admin() || defined('CMPLZ_DOING_SYSTEM_STATUS') ) {
	require_once( cmplz_path . 'pro/callback-notices.php' );
	require_once( cmplz_path . 'pro/class-comments.php' );
	require_once( cmplz_path . 'pro/class-import.php' );
	require_once( cmplz_path . 'pro/class-support.php' );
}

if (is_admin() || cmplz_is_loading_pdf()) {
	require_once( cmplz_path . 'pro/class-processing.php' );
	require_once( cmplz_path . 'pro/dataleak/class-dataleak.php' );
	require_once( cmplz_path . 'pro/framework/post-types.php' );
}
