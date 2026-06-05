<?php
/**
 * @package   Woo Comgate
 * @author    toret.cz
 * @license   GPL-2.0+
 * @link      https://toret.cz
 * @copyright 2019 Toret.cz
 */

class Woo_Comgate_Admin {

    /**
     * Instance of this class.
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected static $instance = null;

    /**
     * Slug of the plugin screen.
     *
     * @since    1.0.0
     *
     * @var      string
     */
    protected $plugin_screen_hook_suffix = null;

    /**
     * Initialize the plugin by loading admin scripts & styles and adding a
     * settings page and menu.
     *
     * @since     1.0.0
     */
    private function __construct() {

        $plugin = Woo_Comgate::get_instance();
        $this->plugin_slug = $plugin->get_plugin_slug();

        // Load admin style sheet and JavaScript.
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );

        // Add the options page and menu item.
        add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

        add_action( 'add_meta_boxes', array( $this, 'metabox' ) );
        add_filter( 'plugin_row_meta', array( $this, 'add_action_links' ), 10, 2 );


        /**
         *  Output fix
         */
        add_action('admin_init', array( $this, 'output_buffer' ) );



    }

    /**
     * Return an instance of this class.
     *
     * @since     1.0.0
     *
     * @return    object    A single instance of this class.
     */
    public static function get_instance() {

        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Register and enqueue admin-specific style sheet.
     *
     * @since     1.0.0
     *
     * @return    null    Return early if no settings page is registered.
     */
    public function enqueue_admin_styles() {
        wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), Woo_Comgate::VERSION );
    }

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {

        if (!defined('TORETMENU')) {

            add_menu_page(
                __( 'Toret plugins', $this->plugin_slug ),
                __( 'Toret plugins', $this->plugin_slug ),
                'manage_options',
                'toret-plugins',
                array( $this, 'display_toret_plugins_admin_page' ),
                ''
            );

            define( 'TORETMENU', true );
        }

        add_submenu_page(
            'toret-plugins',
            __( 'Woo Comgate Payment', $this->plugin_slug ),
            __( 'Woo Comgate Payment', $this->plugin_slug ),
            'manage_options',
            'woo-comgate-payment',
            array( $this, 'display_plugin_admin_page' )
        );

        add_submenu_page(
            'toret-plugins',
            __( 'Comgate log', $this->plugin_slug ),
            __( 'Comgate log', $this->plugin_slug ),
            'manage_woocommerce',
            'comgate-log',
            array( $this, 'display_plugin_log_page' )
        );

    }

    /**
     * Render the settings page for all plugins
     *
     * @since    1.0.0
     */
    public function display_toret_plugins_admin_page() {
        include_once( 'views/toret.php' );
    }

    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_page() {
        include_once( 'views/admin.php' );
    }

    /**
     * Render the settings page for this plugin.
     *
     * @since    1.2.4
     */
    public function display_plugin_log_page() {
        include_once( 'views/log.php' );
    }

    /**
     * Headers allready sent fix
     *
     * @since    1.0.0
     */
    public function output_buffer() {
        ob_start();
    }

    /**
     * Metabox for order detail
     *
     * @since    1.2.4
     */
    public function metabox() {

        global $post;

        $order = wc_get_order( $post->ID );
        if( !$order ){ return; }

        if( $order->get_payment_method() == 'comgate' ){

            include('includes/metabox.php');
            add_meta_box( 'comgate_log', 'Comgate Log', 'order_comgate_log_meta_box', 'shop_order', 'side', 'high' );

        }

    }

    /**
     * Add settings action link to the plugins page.
     */
    public function add_action_links( $meta, $file ){
        if ( $file == 'woo-comgate/woo-comgate.php' ) {
            $meta[] = '<a href="' . admin_url( 'admin.php?page=woo-comgate-payment' ) . '">' . __( 'Nastavení', $this->plugin_slug ) . '</a>';
            $meta[] = '<a href="https://documentation.toret.cz/woo-comgate/" target="_blank">' . __( 'Dokumentace', $this->plugin_slug ) . '</a>';
            $meta[] = '<a href="https://toret.cz/podpora/" target="_blank">' . __( 'Podpora', $this->plugin_slug ) . '</a>';
        }

        return $meta;
    }


}
