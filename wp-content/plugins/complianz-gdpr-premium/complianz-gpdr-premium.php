<?php
/**
 * Plugin Name: Complianz Privacy Suite (GDPR/CCPA) premium
 * Plugin URI: https://complianz.io/pricing
 * Description: Plugin to help you make your website GDPR/CCPa compliant
 * Version: 6.1.3.1
 * Text Domain: complianz-gdpr
 * Domain Path: /languages
 * Author: Really Simple Plugins
 * Author URI: https://complianz.io
 */

/*
    Copyright 2018-2022 Complianz.io (email : support@complianz.io)
    This product includes GeoLite2 data created by MaxMind, available from
    http://www.maxmind.com.
*/
update_site_option('cmplz_license_key', 'cmplz_license_key');
update_option('cmplz_license_key', 'cmplz_license_key');
defined('ABSPATH') or die("you do not have access to this page!");
if (!defined('cmplz_premium') ) define('cmplz_premium', true);

if (!function_exists('cmplz_activation_check')) {
	/**
	 * Checks if the plugin can safely be activated, at least php 5.6 and wp 4.6
	 * @since 2.1.5
	 */
    function cmplz_activation_check()
    {
        if (version_compare(PHP_VERSION, '5.6', '<')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(__('Complianz cannot be activated. The plugin requires PHP 5.6 or higher', 'complianz-gdpr'));
        }

        global $wp_version;
        if (version_compare($wp_version, '4.6', '<')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(__('Complianz cannot be activated. The plugin requires WordPress 4.6 or higher', 'complianz-gdpr'));
        }
    }
	register_activation_hook( __FILE__, 'cmplz_activation_check' );
}

/**
 * Instantiate plugin
 */
if (!class_exists('COMPLIANZ')) {
    class COMPLIANZ
    {
        public static $instance;
        public static $config;
        public static $company;
        public static $review;
        public static $admin;
        public static $field;
        public static $wizard;
        public static $export_settings;
        public static $tour;
        public static $rsp_upgrade_to_pro;
        public static $comments;
        public static $processing;
        public static $dataleak;
        public static $import_settings;
        public static $license;
        public static $cookie_admin;
        public static $geoip;
        public static $statistics;
        public static $document;
        public static $cookie_blocker;
	    public static $DNSMPD;
	    public static $support;
	    public static $proof_of_consent;
	    public static $records_of_consent;

	    private function __construct()
        {
	        self::setup_constants();
	        self::includes();
	        self::load_translation();
	        self::hooks();

	        self::$config = new cmplz_config();
	        self::$company = new cmplz_company();

	        if (cmplz_has_region('us')) {
	        	self::$DNSMPD = new cmplz_DNSMPD();
	        }

	        if ( is_admin() || defined('CMPLZ_DOING_SYSTEM_STATUS') ) {
		        self::$review          = new cmplz_review();
		        self::$admin           = new cmplz_admin();
		        self::$field           = new cmplz_field();
		        self::$wizard          = new cmplz_wizard();
		        self::$export_settings = new cmplz_export_settings();
		        self::$tour            = new cmplz_tour();

		        /* pro instances */
		        self::$comments        = new cmplz_comments();
		        self::$import_settings = new cmplz_import_settings();
		        self::$support         = new cmplz_support();
	        }

	        self::$license         = new cmplz_license();

	        if ( is_admin() || cmplz_is_loading_pdf() ) {
		        /* pro instances, maybe need pdf generation*/
		        self::$processing      = new cmplz_processing();
		        self::$dataleak        = new cmplz_dataleak();
	        }

	        self::$records_of_consent = new cmplz_records_of_consent();
	        self::$proof_of_consent   = new cmplz_proof_of_consent();
			//@todo: change this order (cookie blocker before cookie_admin)  in free & premium too.
	        self::$cookie_blocker = new cmplz_cookie_blocker();
	        self::$cookie_admin       = new cmplz_cookie_admin();
	        self::$geoip              = new cmplz_geoip();
	        self::$statistics         = new cmplz_statistics();
	        //in the free version, the document() class is loaded instead.
	        self::$document       = new cmplz_document_pro();
        }

	    /**
	     * Instantiate the class.
	     *
	     * @since 1.0.0
	     *
	     * @return COMPLIANZ
	     */

	    public static function get_instance() {
		    if ( ! isset( self::$instance ) && ! ( self::$instance instanceof COMPLIANZ ) ) {
			    self::$instance = new self();
		    }

		    return self::$instance;
	    }

        private function setup_constants()
        {
	        define('CMPLZ_COOKIEDATABASE_URL', 'https://cookiedatabase.org/wp-json/cookiedatabase/');
            define('CMPLZ_MAIN_MENU_POSITION', 40);
            define('CMPLZ_PROCESSING_MENU_POSITION', 41);
            define('CMPLZ_DATALEAK_MENU_POSITION', 42);

            //default region code
            if (!defined('CMPLZ_DEFAULT_REGION')) {
            	define('CMPLZ_DEFAULT_REGION',  'us');
            }

            /*statistics*/
            if (!defined('CMPLZ_AB_TESTING_DURATION')) {
            	define('CMPLZ_AB_TESTING_DURATION', 30); //Days
            }

            define('STEP_COMPANY', 1);
            define('STEP_COOKIES', 2);
            define('STEP_MENU',    3);
            define('STEP_FINISH',  4);
            define('cmplz_url', plugin_dir_url(__FILE__));
            define('cmplz_path', plugin_dir_path(__FILE__) );
            define('cmplz_plugin', plugin_basename(__FILE__));
            define('cmplz_plugin_file', __FILE__);
            $debug = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? time() : '';
            define('cmplz_version', '6.1.3.1' . $debug);
            define('cmplz_product_name', 'Complianz GDPR/CCPA Premium');
	        define('CMPLZ_ITEM_ID', 994);
        }

        private function includes()
        {
            require_once(cmplz_path . 'class-document.php');
            require_once(cmplz_path . 'cookie/class-cookie.php');
            require_once(cmplz_path . 'cookie/class-service.php');
            require_once(cmplz_path . 'integrations/integrations.php');
	        require_once(cmplz_path . 'cron/cron.php');

	        /* Gutenberg block */
            if (cmplz_uses_gutenberg()) {
                require_once plugin_dir_path(__FILE__) . 'gutenberg/block.php';
            }
            require_once plugin_dir_path( __FILE__ ) . 'rest-api/rest-api.php';
            require_once( cmplz_path . '/pro/includes.php' );

	        if ( is_admin() || defined('CMPLZ_DOING_SYSTEM_STATUS') ) {
                require_once(cmplz_path . '/assets/icons.php');
                require_once(cmplz_path . 'class-admin.php');
                require_once(cmplz_path . 'class-review.php');
                require_once(cmplz_path . 'class-field.php');
                require_once(cmplz_path . 'class-wizard.php');
                require_once(cmplz_path . 'callback-notices.php');
                require_once(cmplz_path . 'cookiebanner/cookiebanner.php');
                require_once(cmplz_path . 'class-export.php');
	            require_once( cmplz_path . 'shepherd/tour.php' );
	            require_once( cmplz_path . 'grid/grid.php' );
		        if ( isset($_GET['install_pro'])) {
			        require_once( cmplz_path . 'upgrade/upgrade-to-pro.php' );
		        }
	        }

	        if (is_admin() || wp_doing_cron() ) {
		        require_once( cmplz_path . 'upgrade.php' );
	        }

	        require_once( cmplz_path . 'pro/class-licensing.php' );
	        require_once( cmplz_path . 'proof-of-consent/class-proof-of-consent.php' );
            require_once(cmplz_path . 'cookiebanner/class-cookiebanner.php');
            require_once(cmplz_path . 'cookie/class-cookie-admin.php');
            require_once(cmplz_path . 'class-company.php');
            require_once(cmplz_path . 'DNSMPD/class-DNSMPD.php');
            require_once(cmplz_path . 'config/class-config.php');
            require_once(cmplz_path . 'class-cookie-blocker.php');
        }

	    /**
	     * Load plugin translations.
	     *
	     * @since 1.0.0
	     *
	     * @return void
	     */
	    private function load_translation() {
		    load_plugin_textdomain('complianz-gdpr', FALSE, dirname(cmplz_plugin) . '/languages/');
	    }

        private function hooks()
        {
	        //has to be wp, because of AMP plugin
	        add_action('wp', 'cmplz_init_cookie_blocker');
	        load_plugin_textdomain( 'complianz-gdpr' );
        }
    }

	/**
	 * Load the plugins main class.
	 */
	add_action(
		'plugins_loaded',
		function() {
			COMPLIANZ::get_instance();
		},
		9
	);
}

require_once(plugin_dir_path(__FILE__) . 'functions.php');

/**
 * Handle some initializations when plugin is activated
 */
if (!function_exists('cmplz_activation_premium')) {
	function cmplz_activation_premium(){
		//only run once
		if ( !get_option('cmplz_run_premium_install') ) {
			update_option('cmplz_run_premium_install', 'start' );
		}
		//run always
		update_option('cmplz_run_premium_upgrade', true );

	}
	register_activation_hook( __FILE__, 'cmplz_activation_premium' );
}


if (!function_exists('cmplz_start_tour')){
	/**
	 * start tour for plugin
	 */
	function cmplz_start_tour(){
		if (!get_option('cmplz_show_terms_conditions_notice')) {
			update_option('cmplz_show_terms_conditions_notice', time());
		}
		if (!get_site_option('cmplz_tour_shown_once')){
			update_site_option('cmplz_tour_started', true);
		}
	}
	register_activation_hook( __FILE__, 'cmplz_start_tour' );
}
