<?php

defined('ABSPATH') or die("you do not have access to this page!");

if (!class_exists("cmplz_document_pro")) {
    class cmplz_document_pro extends cmplz_document
    {
        private static $_this;


        function __construct()
        {
            if (isset(self::$_this))
	            wp_die(sprintf('%s is a singleton class and you cannot create a second instance.', get_class($this)));

            self::$_this = $this;

            $this->init();

            add_action('cmplz_wp_privacy_policies', array($this, 'wp_privacy_policies'));
            add_action('cmplz_create_page', array($this, 'set_wp_privacy_policy'),10,3);

        }

        static function this()
        {
            return self::$_this;
        }


        public function get_permalink($type, $region, $auto_redirect_region=false)
        {
            $url = "#";
            $page_id = $this->get_shortcode_page_id($type, $region);
            if ($page_id) {
                $url = get_permalink($page_id);
            }

            if ($auto_redirect_region){
                $url = $url.'?cmplz_region_redirect=true';
            }

            return $url;
        }

	    /**
	     * Check if any plugin has changed the policy texts
	     *
	     * @return bool
	     */

        public function plugin_privacy_policies_changed(){
        	return WP_Privacy_Policy_Content::text_change_check();
        }

	    /**
	     * Callback field to add custom texts to the privacy policy
	     */
        public function wp_privacy_policies()
        {
            if (cmplz_get_value('privacy-statement') !== 'generated' ) {
	            cmplz_notice(__("You have chosen to generate your own Privacy Statement, which means the option to add custom text to it is not applicable.", 'complianz-gdpr' ), 'warning' );
                return;
            }

            global $wp_version;
            if (version_compare($wp_version, '4.9.6', '<')) {
                cmplz_notice(__('As of WordPress 4.9.6, plugins and themes can add their own suggested statements about cookie usage and privacy here. To use this functionality, please upgrade to the latest WordPress version.', 'complianz-gdpr') ,'warning');
                return;
            }

            if ( ! class_exists( 'WP_Privacy_Policy_Content' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/misc.php' );
            }

            $data = $this->get_wp_privacy_policy_data();
	        $key = array_search('WordPress', array_column($data, 'plugin_name'));
	        if ( $key !== false ) unset($data[$key]);
            if (!empty($data)) {

                echo '<div class="field-group">';
                echo '<div class="cmplz-field">';
                $consent_api_exists = function_exists('consent_api_registered');
                foreach ($data as $policy) {
	                if ( $policy['plugin_name'] === 'WordPress') continue;

	                $s_plugin_name = sanitize_text_field(str_replace(array('<h3>', '</h3>'), array('<h4>','</h4>'),$policy['plugin_name']));
                    $add_btn = '<span class="cmplz-add-to-policy" style="float:right">'
                                    .__('Add to annex of Privacy Statement', 'complianz-gdpr')
                              .'</span>';

                    $conforms_to_api = '';
                    if ($consent_api_exists) {
                    	$plugin_file = $this->get_plugin_by_name($s_plugin_name);
                    	$is_complianz = stripos($s_plugin_name, 'complianz') !== false;
                        if ($is_complianz || consent_api_registered( $plugin_file )) {
                            $conforms_to_api = '<div class="cmplz-circle-green"></div>';
                        } else {
                            $conforms_to_api = '<div class="cmplz-circle-red"></div>';
                        }
                    }

                    cmplz_panel($s_plugin_name, $policy['policy_text'], $add_btn, $conforms_to_api);
                }
                if ($consent_api_exists) {
                    echo '<div class="cmplz-legenda">
                            <span><div class="cmplz-circle-green"></div></span><span>'.__("Conforms to the Consent API", "complianz-gdpr").'</span>
                            <span><div class="cmplz-circle-red"></div></span><span>'.__("Does not conform with the Consent API", "complianz-gdpr").'</span>
                          </div>';
                }
                echo '</div>';

                echo '</div>';
            } else {
            	cmplz_notice(__('No plugins with suggested statements found.', 'complianz-gdpr'), 'warning' );
            }
        }

        public function get_wp_privacy_policy_data()
        {
            if ( ! class_exists( 'WP_Privacy_Policy_Content' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/misc.php' );
            }

            $data = WP_Privacy_Policy_Content::get_suggested_policy_text();
	        $data = array_filter($data, function ($v) {
		        return !isset($v['removed']) && $v['plugin_name'] != 'Complianz';
	        });
            return $data;
        }

        public function get_plugin_by_name($name){
	        $plugins         = get_option( 'active_plugins' );
	        foreach ($plugins as $plugin){
		        $plugin_data = get_plugin_data(WP_PLUGIN_DIR.'/'.$plugin);
		        if ($name === $plugin_data['Name']) {
		        	return $plugin;
		        }
	        }
	        return false;
        }



    }
} //class closure
