<?php
defined('ABSPATH') or die("you do not have access to this page!");

//https://dev.maxmind.com/geoip/geoip2/geolite2/
require cmplz_path . 'pro/assets/vendor/autoload.php';
use GeoIp2\Database\Reader;

/*
 * Hooked in hooks.php
 *
 * http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.tar.gz
 * */

if (!class_exists("cmplz_geoip")) {
    class cmplz_geoip
    {
        private static $_this;
        public $reader;
        //private $db_url = 'https://cookiedatabase.org/maxmind/GeoLite2-Country.tar.gz';
        private $db_url = 'https://cookiedatabase.org/maxmind/GeoLite2-Country.mmdb';
        public $initialized = false;

        function __construct()
        {
            if (isset(self::$_this))
	            wp_die(sprintf('%s is a singleton class and you cannot create a second instance.', get_class($this)));

            self::$_this = $this;
            $this->initialize();
            add_action('complianz_before_save_settings_option', array($this, 'before_save_general_settings_option'), 10, 4);
            add_action('complianz_after_save_wizard_option', array($this, 'before_save_wizard_settings_option'), 10, 4);
            add_filter('cmplz_geoip_enabled', array($this, 'geoip_enabled'));
        }

        static function this()
        {
            return self::$_this;
        }

	    /**
	     * Runs on saving of a field, to check if geoip was enabled. If so, import the library
	     *
	     * @hooked complianz_before_save_settings_option
	     * @param string $fieldname
	     * @param $fieldvalue
	     * @param $prev_value
	     * @param $type
	     */

	    public function before_save_wizard_settings_option($fieldname, $fieldvalue, $prev_value, $type){
		    if (!current_user_can('manage_options')) return;
		    //only run when changes have been made
		    if ( $fieldvalue === $prev_value ) return;

		    /**
		     * For records of consent, geo ip always needs to be enabled
		     */

		    if ( $fieldname === 'records_of_consent' && $fieldvalue === 'yes' ) {
			    cmplz_update_option('settings', 'use_country', true);
				$this->convert_regions(true);
		    }
	    }

        /**
         * Runs on saving of a field, to check if geoip was enabled. If so, import the library
         *
         * @hooked complianz_before_save_settings_option
         * @param string $fieldname
         * @param $fieldvalue
         * @param $prev_value
         * @param $type
         */

        public function before_save_general_settings_option($fieldname, $fieldvalue, $prev_value, $type){
            if (!current_user_can('manage_options')) return;

            //only run when changes have been made
            if ($fieldvalue === $prev_value) return;

            if ($fieldname==='use_country' && $fieldvalue) {
				$this->convert_regions($fieldvalue);
	        }

	        /**
	         * on change of the use_country variable, make sure all user's cache is cleared.
	         */
            if ($fieldname==='use_country') {
                cmplz_update_all_banners();
            }

            //disable region redirect if geo ip is disabled
            if ( $fieldname==='use_country' && !$fieldvalue){
            	cmplz_update_option('wizard', 'region_redirect', 'no');
            }

        }

		public function convert_regions($enabled){
			//if it's just enabled, run import
			update_option('cmplz_import_geoip_on_activation', true);

			//if geo ip is disabled or enabled, convert regions to array or vice versa
			$regions = cmplz_get_value('regions');
			if ( ! empty( $regions ) ) {
				//just enabled: covert to array
				if ( $enabled && ! is_array( $regions ) ) {
					$regions = array($regions => 1);
					cmplz_update_option( 'wizard', 'regions', $regions );
				} elseif (!$enabled && is_array( $regions ) ) {
					cmplz_update_option( 'wizard', 'regions', array_search( 1, $regions ) );
				}
			}
		}

        /**
         *
         * Check if there is an issue with the geo ip library
         * @since 2.0.3
         * @return bool
         */

        public function geoip_library_error(){

            if ($this->geoip_enabled() && (!get_option("cmplz_geo_ip_file") || !file_exists(get_option("cmplz_geo_ip_file")))){
	            update_option('cmplz_import_geoip_on_activation', true);
	            return true;
            }

            return false;
        }

        /**
         * initialize the geo ip library
         * @since 1.2
         */

        public function initialize()
        {
            if (!$this->geoip_enabled()) return;

            if (is_admin() && current_user_can('manage_options')) {
                if (!get_option("cmplz_geo_ip_file")) {
                    $uploads = wp_upload_dir();
                    $upload_dir = $uploads['basedir'];
                    update_option("cmplz_geo_ip_file", $upload_dir . "/complianz/maxmind/".basename($this->db_url));
                }

                if (get_option('cmplz_import_geoip_on_activation')) {
                    $this->get_geo_ip_database_file();
                    update_option('cmplz_import_geoip_on_activation', false);
                }
            }

            //check if file exists in cmplz folder
            $file_name = get_option("cmplz_geo_ip_file");

            if (file_exists($file_name)) {
	            try {
		            $this->reader = new Reader($file_name);
		            $this->initialized = true;
	            } catch (Exception $e) {
		            error_log("failed loading geo ip retry downloading");
		            update_option('cmplz_import_geoip_on_activation', true);
		            delete_option("cmplz_geo_ip_file");
		            delete_option('cmplz_last_update_geoip');
	            }
            } else {
	            update_option('cmplz_import_geoip_on_activation', true);
	            delete_option("cmplz_geo_ip_file");
	            delete_option('cmplz_last_update_geoip');
            }
        }

        /**
         * Get the region belonging to the currently visiting IP address. Must be one of the supported regions, i.e. us, eu.
         * @since 2.0.0
         *
         * @return string
         */

        public function region()
        {
            $country_code = $this->get_country_code();
            return cmplz_get_region_for_country($country_code);
        }


        /**
         * Get the region belonging to the currently visiting IP address. Must be one of the supported regions, i.e. us, eu.
         * @since 4.0.0
         *
         * @return string
         */

        public function consenttype()
        {
            $country_code = $this->get_country_code();
	        return cmplz_get_consenttype_for_country($country_code);
        }

        /**
         * Get the country code for the current visiting ip address returns false on failure
         * @since 1.2
         *
         * @return bool|string
         */

        public function get_country_code()
        {
            //if we don't have the geo ip database yet, we return default.
            if (!$this->initialized) return COMPLIANZ::$company->get_default_region();

            $ip = $this->get_current_ip();
            if (!$ip) return false;

            $country_code = false;

            try {
                $record = $this->reader->country($ip);
                $country_code = $record->country->isoCode;
            } catch (Exception $e) {
                error_log("failed retrieving country");
            }
            return $country_code;
        }

        /**
         * Get the ip for the current visitor. False on failure
         * @since 1.2
         *
         * @return bool|string
         */

        public function get_current_ip()
        {
            if (!$this->initialized) return false;

            //localhost testing
            if (strpos(home_url(), apply_filters("cmplz_debug_domain","localhost")) !== false) {
                $company_region = COMPLIANZ::$company->get_company_region_code();
                if ($company_region === 'us') {
                    $current_ip = "128.101.101.101";//US ip
                } elseif ($company_region === 'eu') {
                    $current_ip = "94.214.200.105"; //EU ip
                } elseif($company_region=='uk') {
                    $current_ip = '185.86.151.11';
                } elseif($company_region=='ca') {
                    $current_ip = '45.44.129.152';
                }else{
                    $current_ip = "189.189.111.174";     //Mexico
                }

//                $current_ip = "128.101.101.101";//us
//                $current_ip = "94.214.200.105"; //EU ip
                //$current_ip = "185.69.233.170";
                //$current_ip = '2a02:1812:1717:4a00:919f:4a7a:33be:3c54, 2a02:1812:1717:4a00:919f:4a7a:33be:3c54';
            } else{
                $current_ip = apply_filters('cmplz_client_ip', $this->clientIP() );
            }

            //sanitize
            if (filter_var($current_ip, FILTER_VALIDATE_IP)) {
                return apply_filters('cmplz_detected_ip', $current_ip);
            }

            return apply_filters('cmplz_detected_ip', false);
        }

        /**
         * Get the ip of visiting user
         * https://stackoverflow.com/questions/11452938/how-to-use-http-x-forwarded-for-properly
         *
         * @return string ip number
         */

        public function clientIP(){
            //least common types first
	        $variables = array(
		        'HTTP_CF_CONNECTING_IP',
		        'CF-IPCountry',
		        'HTTP_TRUE_CLIENT_IP',
		        'HTTP_X_CLUSTER_CLIENT_IP',
		        'HTTP_CLIENT_IP',
		        'HTTP_X_FORWARDED_FOR',
		        'HTTP_X_FORWARDED',
		        'HTTP_X_REAL_IP',
		        'HTTP_FORWARDED_FOR',
		        'HTTP_FORWARDED',
		        'REMOTE_ADDR'
	        );

			foreach($variables as $variable ) {
				$current_ip = $this->is_real_ip($variable);
				if ( $current_ip ) {
					break;
				}
			}

            //in some cases, multiple ip's get passed. split it to get just one.
            if (strpos($current_ip, ',') !== false) {
                $ips = explode(',', $current_ip);
                $current_ip = $ips[0];
            }

            return apply_filters("cmplz_visitor_ip", $current_ip);
        }

	    /**
	     * Get ip from var, and check if the found ip is a valid one
	     * @param string $var
	     *
	     * @return false|string
	     */

		private function is_real_ip($var) {
			$ip = getenv($var);
			if (!$ip || trim($ip)==='127.0.0.1') {
				return false;
			} else {
				return $ip;
			}
		}


        /**
         *
         * Check if geo ip is enabled on this site
         * @since 1.2
         *
         * @return bool
         */

        public function geoip_enabled($enabled=false)
        {
            return cmplz_get_value('use_country');
        }

        /**
         * Retrieve the MaxMind geo ip database file. Pass retrieve to force renewal of the file.
         * @since 2.0.3
         * @param bool $renew
         */

        private function get_geo_ip_database_file($renew=false)
        {
            if (!wp_doing_cron() && !current_user_can('manage_options')) return;

            //only run if it doesn't exist yet, or if it should renew
            if ($renew || !get_option("cmplz_geo_ip_file") || !file_exists(get_option("cmplz_geo_ip_file")) ) {
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                //set geo ip to not available
                $this->initialized = false;
                update_option("cmplz_geo_ip_file", false);

                $uploads = wp_upload_dir();
                $upload_dir = $uploads['basedir'];

                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir);
                }

                if (!file_exists($upload_dir . "/complianz")) {
                    mkdir($upload_dir . "/complianz");
                }

                if (!file_exists($upload_dir . "/complianz/maxmind")) {
                    mkdir($upload_dir . "/complianz/maxmind");
                }

                $unzipped = $upload_dir . "/complianz/maxmind/".basename($this->db_url);
                update_option("cmplz_geo_ip_file", $unzipped);

                //download file from maxmind
                $tmpfile = download_url($this->db_url, $timeout = 25);

                //check for errors
                if (is_wp_error($tmpfile)){
                    //store the error for use in the callback notice for geo ip
                    update_option('cmplz_geoip_import_error', $tmpfile->get_error_message());
                } else {
                    //remove current file
                    if (file_exists($unzipped)) unlink($unzipped);

                    //in case the server prevents deletion, we check it again.
                    if (!file_exists($unzipped)) copy($tmpfile, $unzipped);

                    //if there was an error saved previously, remove it
                    delete_option('cmplz_geoip_import_error');
                }

	            // must unlink afterwards
				if (is_string($tmpfile) && file_exists($tmpfile)) {
					unlink($tmpfile);
				}

                update_option('cmplz_last_update_geoip', time());
                $this->initialized = true;
            }
        }

        /**
         * Check if the geo ip database should be updated
         * @hooked cmplz_every_day_hook
         */

        public function cron_check_geo_ip_db(){

            if (!$this->geoip_enabled()) return;

            $now = time();
            $last_update = get_option('cmplz_last_update_geoip');
            $time_passed = $now - $last_update;

            //if file was never downloaded, or more than two months ago, redownload.
            if (!$last_update || $time_passed > 2 * MONTH_IN_SECONDS){
                $this->get_geo_ip_database_file(true);
            }
        }


    }

}
