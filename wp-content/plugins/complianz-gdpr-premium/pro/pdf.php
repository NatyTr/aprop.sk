<?php
# No need for the template engine
define('WP_USE_THEMES', false);

#find the base path
define('BASE_PATH', find_wordpress_base_path() . "/");

# Load WordPress Core
if ( !file_exists(BASE_PATH . 'wp-load.php') ) {
	die("WordPress not installed here");
}

require_once(BASE_PATH . 'wp-load.php');
require_once(BASE_PATH . 'wp-includes/class-phpass.php');
require_once(BASE_PATH . 'wp-admin/includes/image.php');

if (isset($_GET['nonce'])) {
    $nonce = $_GET['nonce'];
    if (!wp_verify_nonce($nonce, 'cmplz_pdf_nonce')) {
        die("invalid command");
    }
} else {
    die("invalid command");
}

if (!isset($_GET['post_id']) && !isset($_GET['page'])) {
    die('invalid command');
}


$region = isset($_GET['region']) ? sanitize_title($_GET['region']) : 'eu';
$save_to_file = isset($_GET['save']) ? true:false;

if (isset($_GET['post_id'])) {
    $post_id = intval($_GET['post_id']);
    $post_type = get_post_type($post_id);
    $type = str_replace('cmplz-', '', $post_type);
} else {
    $page = sanitize_title($_GET['page']);
    $region = cmplz_get_region_from_legacy_type($page);
    $type = str_replace('-'.$region, '', $page);
    $post_id = false;
}

COMPLIANZ::$document->generate_pdf($type, $region, $post_id, $save_to_file);
if (!$save_to_file) exit;

//==============================================================
//==============================================================
//==============================================================

function find_wordpress_base_path()
{
	$path = dirname(__FILE__);

	do {
        //it is possible to check for other files here
        if (file_exists($path . "/wp-config.php")) {
        	//check if the wp-load.php file exists here. If not, we assume it's in a subdir.
	        if ( file_exists( $path . '/wp-load.php') ) {
                return $path;
            } else {
	        	//wp not in this directory. Look in each folder to see if it's there.
		        if ( file_exists( $path ) && $handle = opendir( $path ) ) {
			        while ( false !== ( $file = readdir( $handle ) ) ) {
				        if ( $file != "." && $file != ".." ) {
					        $file = $path .'/' . $file;
					        if ( is_dir( $file ) && file_exists( $file . '/wp-load.php') ) {
						        $path = $file;
						        break;
					        }
				        }
			        }
			        closedir( $handle );
		        }
	        }

	        return $path;
        }
    } while ($path = realpath("$path/.."));

    return false;
}
