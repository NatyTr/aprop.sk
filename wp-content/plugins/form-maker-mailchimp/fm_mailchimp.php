<?php
 /**
 * Plugin Name: Form Maker Mailchimp Integration
 * Plugin URI: https://10web.io/plugins/wordpress-form-maker#plugin_extensions
 * Description: This add-on is an integration of the Form Maker with MailChimp which allows to add contacts to your subscription lists just from submitted forms.  
 * Version: 1.1.9
 * Author: Form Builder Team
 * Author URI: https://10web.io/plugins/
 * License: GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

define('WD_FM_MAILCHIMP_VERSION', '1.1.9');
define('WD_FM_MAILCHIMP_PREFIX', 'form_maker');
define('WD_FM_MAILCHIMP', plugin_basename(__FILE__));
define('WD_FM_MAILCHIMP_DIR', WP_PLUGIN_DIR . "/" . plugin_basename(dirname(__FILE__)));
define('WD_FM_MAILCHIMP_URL', plugins_url(plugin_basename(dirname(__FILE__))));
define('WD_FM_MAILCHIMP_NICENAME', 'Mailchimp Integration');

final class WDFMMAILCHIMP {
	/**
	* WDFMPG Constructor.
	*/
	public function __construct() {
    register_activation_hook(__FILE__, array($this, 'global_activate'));
    add_action('wpmu_new_blog', array($this, 'new_blog_added'), 10, 6);
		add_action('fm_init_addons', array($this, 'get_actions'));
	}

  /*
  * Global activate.
  *
  * @param $networkwide
  */
  public function global_activate($networkwide) {
    if ( function_exists('is_multisite') && is_multisite() ) {
      // Check if it is a network activation - if so, run the activation function for each blog id.
      if ( $networkwide ) {
        global $wpdb;
        // Get all blog ids.
        $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
        foreach ( $blogids as $blog_id ) {
          switch_to_blog($blog_id);
          $this->activate();
          restore_current_blog();
        }

        return;
      }
    }
    $this->activate();
  }

  public function new_blog_added( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
    if ( is_plugin_active_for_network( WD_FM_MAILCHIMP ) ) {
      switch_to_blog($blog_id);
      $this->activate();
      restore_current_blog();
    }
  }
  
	function activate() {
		delete_transient( 'fm_update_check' );
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$create_table = 'CREATE TABLE `' . $wpdb->prefix . 'formmaker_mailchimp` (
		  `form_id` int(11) NOT NULL,
		  `use_mailchimp` tinyint(1) NOT NULL,
		  `mailchimp_apikey` varchar(100) NOT NULL,
		  `mailchimp_listid` varchar(50) NOT NULL,
		  `mailchimp_action` tinyint(1) NOT NULL,
		  `mailchimp_email_type` varchar(10) NOT NULL,
		  `mailchimp_mergevars` text NOT NULL
		) ' . $charset_collate . ';';

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta( $create_table );
	}

  function get_actions() {
    add_filter('fm_get_addon_init', array( $this, 'addon_tab' ), 10, 2);
    add_action('fm_save_addon_init', array( $this, 'save_addon' ));
    add_action('fm_duplicate_form', array( $this, 'duplicate_addon' ), 10, 2);
    add_action('fm_delete_addon_init', array( $this, 'delete_addon' ));

    add_action('WD_FM_MAILCHIMP_init', array( $this, 'init' ));
    add_action('wp_ajax_WD_FM_MAILCHIMP_init', array( $this, 'init' ));
    add_action('fm_admin_print_scripts', array( $this, 'load_scripts' ));

    add_action('fm_addon_frontend_init', array( $this, 'frontend' ));
  }

  /**
   * Add-on tab.
   *
   * @param array $addons
   * @param array $params
   *
   * @return array
   */
	function addon_tab( $addons = array(), $params = array() ) {
		require_once('controller.php');
		$controller = new WD_FM_MAILCHIMP_controller($params['form_id']);
		ob_start();
		$controller->display($params);
		
		$addon_key = 'WD_FM_MAILCHIMP';
		$addons['tabs'][$addon_key] = __('Mailchimp', WDFM()->prefix);
		$addons['html'][$addon_key] = ob_get_clean();

		return $addons;
	}

  /**
   * Save add-on.
   *
   * @param int $form_id
   */
  function save_addon( $form_id = 0 ) {
		require_once('controller.php');		
		$controller = new WD_FM_MAILCHIMP_controller($form_id);
		$controller->save();
	}

  /**
   * Duplicate addon.
   *
   * @param int $id
   * @param int $new_id
   */
  function duplicate_addon( $id, $new_id ) {
    require_once('controller.php');
    $controller = new WD_FM_MAILCHIMP_controller($new_id);
    $controller->duplicate($id, $new_id);
  }

  /**
   * Delete add-on.
   *
   * @param int $form_id
   */
	function delete_addon( $form_id = 0 ) {
		require_once('controller.php');
		$controller = new WD_FM_MAILCHIMP_controller($form_id);
		$controller->delete();
	}

  /**
   * Frontend.
   *
   * @param array $params
   */
	function frontend( $params = array() ) {
		require_once( WDFM()->plugin_dir . '/framework/WDW_FM_Library.php');
		require_once('controller.php');
		$controller = new WD_FM_MAILCHIMP_controller($params['form_id']);

		$controller->frontend($params);
	}

	function init() {
    require_once('controller.php');
    $form_id = WDW_FM_Library::get("form_id");
    $controller = new WD_FM_MAILCHIMP_controller($form_id);
    $task = WDW_FM_Library::get("addon_task");

    $controller->execute($task);
	}

  function load_scripts() {
    wp_enqueue_style('fm-mailchimp', WD_FM_MAILCHIMP_URL . '/css/fm_mailchimp.css', array(), WDFM()->plugin_version);
    wp_enqueue_script('fm-mailchimp', WD_FM_MAILCHIMP_URL . '/js/fm_mailchimp.js', array( 'fm-form-options' ), WDFM()->plugin_version);
  }
}

new WDFMMAILCHIMP();

add_filter('fm_addon_msg', 'fm_mailchimp_message');
function fm_mailchimp_message( $fm_addon_msg = array() ) {
  $fm_addon_msg[] = WD_FM_MAILCHIMP_NICENAME;

  return $fm_addon_msg;
}

add_action('plugins_loaded', 'fm_mailchimp_form_maker_check');
// Call function when the add-on required version is the same with other add-ons required version.
function fm_mailchimp_form_maker_check() {
  if ( !class_exists('WDFM') || version_compare(WDFM()->db_version, '2.13.0') == -1 ) {
    add_action('fm_addon_print_msg', 'fm_addon_get_msg');
  }
}

if ( !function_exists('fm_addon_get_msg') ) {
  // Call this function for output message.
  function fm_addon_get_msg() {
    $fm_addon_msg = apply_filters('fm_addon_msg', array());
    $addon_names = implode($fm_addon_msg, ', ');
    $count = count($fm_addon_msg);
    $single = __('Please install %s plugin version 2.13.0 and higher to start using %s add-on.', 'form_maker');
    $plural = __('Please install %s plugin version 2.13.0 and higher to start using %s add-ons.', 'form_maker');
    echo '<div class="error"><p>' . sprintf(_n($single, $plural, $count, 'form_maker'), 'Form Maker',  $addon_names) . '</p></div>';
  }

  function fm_print_addon_msg() {
    do_action('fm_addon_print_msg');
  }

  add_action('admin_notices', 'fm_print_addon_msg');
}
