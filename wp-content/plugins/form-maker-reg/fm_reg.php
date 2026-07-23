<?php
/**
 * Plugin Name:     Form Maker Registration
 * Plugin URI:      https://10web.io/plugins/wordpress-form-maker#plugin_extensions
 * Description:      User Registration add-on integrates with Form maker forms allowing users to create accounts at your website.
 * Version:         1.2.7
 * Author:          Form Builder Team
 * Author URI:      https://10web.io/plugins/
 * License: GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

define('WD_FM_REG_PREFIX', 'form_maker');
define('WD_FM_REG_NICENAME', 'Registration');
define('WD_FM_REG', plugin_basename(__FILE__));

final class WDFMWR {

  public $plugin_dir = '';
  public $plugin_url = '';
  public $version = '1.2.7';
  public $key = 'WD_FM_REG';

  /**
   * WDFMWR Constructor.
   */
  public function __construct() {
    $this->plugin_dir = WP_PLUGIN_DIR . "/" . plugin_basename(dirname(__FILE__));
    $this->plugin_url = plugins_url(plugin_basename(dirname(__FILE__)));
    register_activation_hook(__FILE__, array($this, 'global_activate'));
    add_action('wpmu_new_blog', array($this, 'new_blog_added'), 10, 6);
    add_action('fm_init_addons', array($this, 'init'));
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
    if ( is_plugin_active_for_network( WD_FM_REG) ) {
      switch_to_blog($blog_id);
      $this->activate();
      restore_current_blog();
    }
  }

  function activate() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $query = "CREATE TABLE `" . $wpdb->prefix . "formmaker_reg` (
      `form_id` int(11) NOT NULL,
      `use_reg` tinyint(1) NOT NULL,
      `show_other_params` tinyint(1) NOT NULL,
      `username` varchar(50) NOT NULL,
      `first_name` varchar(50) NOT NULL,
      `last_name` varchar(50) NOT NULL,
      `role` text NOT NULL,
      `email` varchar(50) NOT NULL,
      `website` varchar(50) NOT NULL,
      `info` varchar(255) NOT NULL,
      `password` varchar(50) NOT NULL,
      `rep_password` varchar(50) NOT NULL,
      `another_params` text NOT NULL,
      PRIMARY KEY  (form_id)
    ) " . $charset_collate . ";";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($query);
  }

  function init() {
    add_filter('fm_get_addon_init', array($this, 'addon_tab'), 10, 2);
    add_action('fm_save_addon_init', array($this, 'save'));
    add_action('fm_duplicate_form', array( $this, 'duplicate_addon' ), 10, 2);
    add_action('fm_delete_addon_init', array($this, 'delete'));
    add_action('fm_addon_frontend_init', array($this, 'frontend'));
    add_action('admin_print_scripts', array($this, 'load_scripts'));
  }

  /**
   * Register add-on styles/scripts.
   */
  function load_scripts() {
    wp_enqueue_style('fm-registration', $this->plugin_url . '/css/fm-registration.css', array(), $this->version);
    wp_enqueue_script('fm-registration', $this->plugin_url . '/js/fm-registration.js', array('fm-form-options'), $this->version);
  }

  /**
   * Addon tab.
   *
   * @param $addons
   * @param $params
   * @return mixed
   *
   */
  function addon_tab( $addons = array(), $params = array() ) {
    require_once('controller.php');
    $controller = new WD_FM_REG_controller;
    ob_start();
    $controller->display($params);

    $addon_key = $this->key;
    $addons['tabs'][$addon_key] = __('Registration', WDFM()->prefix);
    $addons['html'][$addon_key] = ob_get_clean();
    return $addons;
  }

  /**
   * Save addon.
   *
   * @param int $id
   */
  function save( $id = 0 ) {
    require_once('controller.php');
    $controller = new WD_FM_REG_controller;
    $save = $controller->save($id);
  }

  /**
   * Duplicate addon.
   *
   * @param int $id
   * @param int $new_id
   */
  function duplicate_addon( $id, $new_id ) {
    require_once('controller.php');
    $controller = new WD_FM_REG_controller;
    $controller->duplicate($id, $new_id);
  }

  /**
   * Delete addon.
   *
   * @param int $id
   */
  function delete( $id = 0 ) {
    require_once('controller.php');
    $controller = new WD_FM_REG_controller;
    $controller->delete($id);
  }

  /**
   * Frontend.
   *
   * @param array $params
   *
   */
  function frontend( $params = array() ) {
    require_once('controller.php');
    $controller = new WD_FM_REG_controller;
    $controller->frontend($params);
  }
}

new WDFMWR();

add_filter('fm_addon_msg', 'fm_reg_message');
function fm_reg_message( $fm_addon_msg = array() ) {
  $fm_addon_msg[] = WD_FM_REG_NICENAME;

  return $fm_addon_msg;
}

add_action('plugins_loaded', 'fm_reg_form_maker_check');
// Call function when the add-on required version is the same with other add-ons required version.
function fm_reg_form_maker_check() {
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
    echo '<div class="error"><p>' . sprintf(_n($single, $plural, $count, 'form_maker'), 'Form Maker', $addon_names) . '</p></div>';
  }

  function fm_print_addon_msg() {
    do_action('fm_addon_print_msg');
  }

  add_action('admin_notices', 'fm_print_addon_msg');
}
