<?php
defined('ABSPATH') or die("you do not have acces to this page!");

if (!class_exists('CMPLZ_SL_Plugin_Updater')) {
    // load our custom updater
    include(dirname(__FILE__) . '/EDD_SL_Plugin_Updater.php');
}

if (!class_exists("cmplz_license")) {
    class cmplz_license
    {
        private static $_this;
        public $product_name;
        public $website;
        public $author;
		public $page_slug = "complianz";

        function __construct()
        {
            if (isset(self::$_this))
	            wp_die(sprintf('%s is a singleton class and you cannot create a second instance.', get_class($this)));

            self::$_this = $this;

            $this->product_name = 'Complianz GDPR premium';
            $this->website = 'https://complianz.io';
            $this->author = 'Complianz';

            if ( is_admin() || wp_doing_cron() ){
				add_action( 'init', array($this, 'plugin_updater') );
				add_filter( 'cmplz_warning_types', array( $this, 'add_license_warning'));
			}

            add_action( 'admin_init', array($this, 'activate_license'), 10, 3);
            add_action( 'admin_init', array($this, 'register_option'), 20, 3);
            add_action( 'admin_init', array($this, 'deactivate_license'), 30, 3);
            add_action( 'admin_init', array($this, 'ms_dismiss_license_notice_get'), 30, 3);
            add_action( "network_admin_notices", array($this, 'ms_show_notice_license'));
			add_action( 'cmplz_settings_items', array($this, 'settings_items'), 40, 1);
			add_action( 'network_admin_menu', array(&$this, 'add_multisite_menu'));
			add_action( 'network_admin_edit_cmplz_update_network_settings', array($this, 'activate_license'));
			add_filter( 'cmplz_shepherd_steps', array($this, 'add_shepherd_steps_premium' ));

			$plugin = cmplz_plugin;
			add_action( "in_plugin_update_message-{$plugin}", array( $this, 'plugin_update_message'), 10, 2 );
		}

		/**
		 * Add a major changes notice to the plugin updates message
		 * @param $plugin_data
		 * @param $response
		 */
		public function plugin_update_message($plugin_data, $response){
			if ( !$this->license_is_valid() ) {
				if ( is_multisite() && defined('cmplz_premium_multisite') ){
					$url =  network_admin_url( "settings.php?page=complianz");
				} else {
					$url   = add_query_arg(array("page" => 'cmplz-settings'), admin_url( "admin.php" ) ).'#license';
				}
				echo '&nbsp<a href="'.$url.'">'.__("Activate your license for automatic updates.", "complianz-gdpr").'</a>';
			}
		}

		public function add_shepherd_steps_premium($steps){
			if (COMPLIANZ::$license->license_is_valid()) {
				$license_text = __( "Great, your license is activated and valid!", 'complianz-gdpr' );
			} else {
				$license_text = __( "To unlock the wizard and future updates, please enter and activate your license.", 'complianz-gdpr' );
			}

			$license_step = array(
					'title' => __( 'Activate your license', 'complianz-gdpr' ),
					'text'  => $license_text,
					'attach' => '.cmplz-link-license',
					'position' => 'right',
			);

			if ( is_multisite() && defined('cmplz_premium_multisite') ){
				$license_step['link'] =  network_admin_url( "settings.php?page=complianz");
			} else {
				$license_step['link']   = add_query_arg(array("page" => 'cmplz-settings'), admin_url( "admin.php" ) );
			}

			$index = array_search('.cmplz-settings-link', array_column($steps, 'attach'))+1;
			$steps = array_merge(array_slice($steps, 0, $index, true),
					array($license_step) ,
					array_slice($steps, $index, count($steps)-$index, false));

			return $steps;
		}

        static function this()
        {
            return self::$_this;
        }

        public function add_license_warning( $warnings ){
        	$license_link = '<a href="'.add_query_arg(array('page' => 'cmplz-settings'), admin_url('admin.php')).'#license">';
        	$is_complianz_page = isset( $_GET['page'] ) && $_GET['page'] === 'complianz';
        	$clear_cache = $is_complianz_page;
        	$status = $this->get_license_status('check_license', $clear_cache );
			// empty => no license key yet
			// invalid, disabled, deactivated
			// revoked, missing, invalid, site_inactive, item_name_mismatch, no_activations_left
			//   inactive, expired, valid
        	if ( empty($status) ){
				$warnings['license']  = array(
					'conditions' => array('_true_'),
					'include_in_progress' => true,
					'urgent' => sprintf(__( 'Please %senter your license key%s to activate your license.', 'complianz-gdpr' ), $license_link, "</a>"),
					'dismissible' => false,
				);
			} else if ($status === 'valid') {
				$warnings['license']  = array(
						'conditions' => array('_true_'),
						'include_in_progress' => true,
						'completed'    => __( 'Your license is activated and valid.', 'complianz-gdpr' ),
				);
			} else {
				$warnings['license']  = array(
						'conditions' => array('_true_'),
						'include_in_progress' => true,
						'urgent' => sprintf(__( 'Please check your %slicense status%s.', 'complianz-gdpr' ), $license_link, "</a>"),
						'dismissible' => false,
				);
        	}
        	return $warnings;
		}

		/**
		 * Add the license block
		 * @param $items
		 *
		 * @return mixed
		 */
        public function settings_items($items){
			$items['license'] = array(
					'page' => 'license',
					'name' => 'license',
					'header' => __('License', 'complianz-gdpr'),
			);
        	return $items;
		}

		/**
		 * Get the license key
		 * @return string
		 */
		public function license_key(){
			return $this->encode( get_site_option('cmplz_license_key') );
		}

		/**
		 * Plugin updater
		 */

		public function plugin_updater()
		{
			$license = $this->maybe_decode(get_site_option('cmplz_license_key'));
			$edd_updater = new CMPLZ_SL_Plugin_Updater($this->website, cmplz_plugin_file, array(
					'version' => cmplz_version,
					'license' => $license,
					'item_id' => CMPLZ_ITEM_ID,
					'author' => $this->author,
				)
			);
		}

		/**
		 * Decode a license key
		 * @param string $string
		 *
		 * @return string
		 */

		public function maybe_decode( $string ) {
			if (strpos( $string , 'complianz_') !== FALSE ) {
				$key = $this->get_key();
				$string = str_replace('complianz_', '', $string);

				// To decrypt, split the encrypted data from our IV
				$ivlength = openssl_cipher_iv_length('aes-256-cbc');
				$iv = substr(base64_decode($string), 0, $ivlength);
				$encrypted_data = substr(base64_decode($string), $ivlength);

				$decrypted =  openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
				return $decrypted;
			}

			//not encoded, return
			return $string;
		}

		/**
		 * Get a decode/encode key
		 * @return false|string
		 */

		public function get_key() {
			return get_site_option( 'complianz_key' );
		}

		/**
		 * Set a new key
		 * @return string
		 */

		public function set_key(){
			update_site_option( 'complianz_key' , time() );
			return get_site_option('complianz_key');
		}

		/**
		 * Encode a license key
		 * @param string $string
		 * @return string
		 */

		public function encode( $string ) {
			if ( strlen(trim($string)) === 0 ) return $string;

			if (strpos( $string , 'complianz_') !== FALSE ) {
				return $string;
			}

			$key = $this->get_key();
			if ( !$key ) {
				$key = $this->set_key();
			}

			$ivlength = openssl_cipher_iv_length('aes-256-cbc');
			$iv = openssl_random_pseudo_bytes($ivlength);
			$ciphertext_raw = openssl_encrypt($string, 'aes-256-cbc', $key, 0, $iv);
			$key = base64_encode( $iv.$ciphertext_raw );

			return 'complianz_'.$key;
		}

		/**
		 * Sanitize the license
		 * @param $new
		 *
		 * @return mixed
		 */
		public function sanitize_license($new)
		{
			$old = $this->license_key();
			if ($old && $old != $new) {
				delete_site_transient('cmplz_license_status'); // new license has been entered, so must reactivate
			}
			return $new;
		}

		/**
		 * Activate the license key
		 */

		public function activate_license()
		{
			if (!current_user_can('manage_options')) return;
			if (isset($_POST['cmplz_license_activate']) || isset($_POST['cmplz_license_save'])) {
				if ( ! isset( $_POST['cmplz_nonce'] ) || ! wp_verify_nonce( $_POST['cmplz_nonce'], 'complianz_save' ) ) {
					return;
				}
				$license = trim($_POST['cmplz_license_key']);
				update_site_option('cmplz_license_key', $this->encode($license) );

				if ( isset($_POST['cmplz_license_activate']) ) {
					$this->get_license_status('activate_license', true );
				} else {
					$this->get_license_status('check_license', true );

				}
			}
		}

		/**
		 * Deactivate the license
		 * @return bool|void
		 */

		public function deactivate_license()
		{
			if (!current_user_can('manage_options')) return;
			if ( isset($_POST['cmplz_license_deactivate']) ) {
				if ( ! isset( $_POST['cmplz_nonce'] ) || ! wp_verify_nonce( $_POST['cmplz_nonce'], 'complianz_save' ) ) {
					return;
				}
				$this->get_license_status('deactivate_license', true);
			}
		}

		/**
		 * Check if license is valid
		 * @return bool
		 */

		public function license_is_valid()
		{
			return true;
			$status = $this->get_license_status();
			if ($status == "valid") {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Get latest license data from license key
		 * @param string $action
		 * @param bool $clear_cache
		 * @return string
		 *   empty => no license key yet
		 *   invalid, disabled, deactivated
		 *   revoked, missing, invalid, site_inactive, item_name_mismatch, no_activations_left
		 *   inactive, expired, valid
		 */

		public function get_license_status($action = 'check_license', $clear_cache = false )
		{
			return 'valid';
			$status = get_site_transient('cmplz_license_status');
			if ($clear_cache) {
				$status = false;
			}

			if ( !$status || get_site_option('cmplz_license_activation_limit') === FALSE ){
				$status = 'invalid';
				$license = $this->maybe_decode( $this->license_key() );
				if ( strlen($license) ===0 ) {
					set_site_transient('cmplz_license_status', 'error');
					delete_site_option('cmplz_license_expires' );
					update_site_option('cmplz_license_activation_limit', 'none');
					delete_site_option('cmplz_license_activations_left' );
					return 'empty';
				}

				$home_url = home_url();

				//the multisite plugin should activate for the main domain
				if ( defined('cmplz_premium_multisite') ) {
					$home_url = network_site_url();
				}

				// data to send in our API request
				$api_params = array(
					'edd_action' => $action,
					'license' => $license,
					'item_id' => CMPLZ_ITEM_ID,
					'url' => $home_url
				);
				$args = apply_filters('rsssl_license_verification_args', array('timeout' => 15, 'sslverify' => true, 'body' => $api_params) );
				$response = wp_remote_post($this->website, $args);
				if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
					set_site_transient('cmplz_license_status', 'error');
				} else {
					$license_data = json_decode(wp_remote_retrieve_body($response));
					if ( !$license_data || ($license_data->license === 'failed' ) ) {
						$status = 'empty';
						delete_site_option('cmplz_license_expires' );
						update_site_option('cmplz_license_activation_limit', 'none');
						delete_site_option('cmplz_license_activations_left' );
					} elseif ( isset($license_data->error) ){
						$status = $license_data->error; //revoked, missing, invalid, site_inactive, item_name_mismatch, no_activations_left
						if ($status==='no_activations_left') {
							update_site_option('cmplz_license_activations_left', 0);
						}
					} elseif ( $license_data->license === 'invalid' || $license_data->license === 'disabled' ) {
						$status = $license_data->license;
					} elseif ( true === $license_data->success ) {
						$status = $license_data->license; //inactive, expired, valid, deactivated
						if ($status === 'deactivated'){
							$left = get_site_option('cmplz_license_activations_left', 1 );
							$activations_left = is_numeric($left) ? $left + 1 : $left;
							update_site_option('cmplz_license_activations_left', $activations_left);
						}
					}

					if ( $license_data ) {
						$date = $license_data->expires;
						if ( $date !== 'lifetime' ) {
							if (!is_numeric($date)) $date = strtotime($date);
							$date = date(get_option('date_format'), $date);
						}
						update_site_option('cmplz_license_expires', $date);

						if ( isset($license_data->license_limit) ) update_site_option('cmplz_license_activation_limit', $license_data->license_limit);
						if ( isset($license_data->activations_left) ) update_site_option('cmplz_license_activations_left', $license_data->activations_left);
					}
				}

				set_site_transient('cmplz_license_status', $status, WEEK_IN_SECONDS);
			}
			return $status;
		}

		public function add_multisite_menu()
		{
			if ( !defined('cmplz_premium_multisite') ) {
				return;
			}

			$this->rsssl_network_admin_page = add_submenu_page( 'settings.php', "Complianz", "Complianz",
					'manage_options',
					$this->page_slug,
					array( &$this, 'ms_license_page' ) );
		}

		/**
		 * License page for MS
		 */

		public function ms_license_page()
		{
			$grid_items = array(
					'license' => array(
							'page' => 'license',
							'name' => 'license',
							'header' => __('License', 'complianz-gdpr'),
							'controls' => '',
					),
			);

			$grid_items = apply_filters( 'cmplz_ms_settings_items', $grid_items);

			echo cmplz_grid_container_settings(__( "Settings", 'complianz-gdpr' ), $grid_items);
		}

		/**
		 * Show a notice if the license is not activated
		 */
        public function ms_show_notice_license()
        {
			if ( !is_multisite() ) return;

            $screen = get_current_screen();
            if ($screen && $screen->parent_base === 'edit' ) return;

            $dismissed = get_option('cmplz_license_notice_dismissed');
			if (defined('cmplz_premium_multisite' )) {
				$link = add_query_arg(array('page'=>'complianz', 'cmplz_dismiss_license_notice'=>1), network_admin_url('settings.php') );
			} else {
				$link = add_query_arg(array('page'=>'cmplz-settings#license', 'cmplz_dismiss_license_notice'=>1), admin_url('admin.php') );
			}

            if ( !$this->license_is_valid() && !$dismissed )
            {
              ?>
              <style>
                .cmplz-container {
                  display: flex;
                  padding: 12px;
                }

                .cmplz-container .dashicons {
                  margin-left: 10px;
                  margin-right: 5px;
                }

                .cmplz-review-image img {
                  margin-top: 0.5em;
                }

                .cmplz-buttons-row {
                  margin-top: 10px;
                  display: flex;
                  align-items: center;
                }
              </style>
              <div id="message"
                   class="updated fade notice cmplz-license-notice really-simple-plugins"
                   style="border-left:4px solid #333">
                <div class="cmplz-container">
                  <div class="cmplz-review-image"><img width=80px"
                                                       src="<?php echo cmplz_url ?>assets/images/icon-logo.svg"
                                                       alt="logo">
                  </div>
                  <div style="margin-left:30px">
                    <p><?php printf( __( 'Welcome to Complianz Privacy Suite! Before you start, please activate your license. For more information, please check our documentation, or ask %ssupport%s.',
                        'complianz-gdpr' ),
                        '<a href="https://complianz.io/support" target="_blank">',
                        '</a>' ); ?></p>
                    <div class="cmplz-buttons-row">
                      <a class="button button-primary"
                      <a href="<?php echo esc_url_raw($link) ?>"><?php _e( 'Activate license',
                          'complianz-gdpr' ); ?></a>

                      <div class="dashicons dashicons-media-default"></div>
                      <?php printf( __( '%sDocumentation%s',
                          'complianz-gdpr' ),
                            '<a href="https://complianz.io/docs" target="_blank">',
                              '</a>' ); ?>
                    </div>
                  </div>
                </div>
              </div>
              <?php
            }
        }

	    /**
	     * Dismiss the license notice using $_GET
	     */

        public function ms_dismiss_license_notice_get(){
			if (isset($_GET['cmplz_dismiss_license_notice'])){
		        update_option('cmplz_license_notice_dismissed', true);
	        }
        }

		/**
		 * Show the license block
		 */

        public function license_page()
        {
			$grid_items = array(
				'license' => array(
					'page' => 'license',
					'name' => 'license',
					'header' => __('License', 'complianz-gdpr'),
				),
			);

			echo cmplz_grid_container_settings(__( "Settings", 'complianz-gdpr' ), $grid_items);
			?>
            <p>
                <?php printf(__("Complianz Privacy Suite includes GeoLite2 data created by MaxMind, available from %shttp://www.maxmind.com%s", 'complianz-gdpr'), '<a target="_blank" href="http://www.maxmind.com">','</a>')?>
            </p>
            <?php
        }


        public function register_option()
        {
            register_setting('cmplz_license', 'cmplz_license_key', array($this, 'sanitize_license'));
        }

		/**
		 * Get license status label
		 * @return string
		 */

		public function get_license_label(){
			$status = $this->get_license_status('check_license', true );
			$support_link = '<a target="_blank" href="https://complianz.io/support">';
			$account_link = '<a target="_blank" href="https://complianz.io/account">';
			$agency_link = '<a target="_blank" href="https://complianz.io/pricing#multisite">';

			$activation_limit = get_site_option('cmplz_license_activation_limit' ) === 0 ? __('unlimited', 'complianz-gdpr') : get_site_option('cmplz_license_activation_limit' );
			$activations_left = get_site_option('cmplz_license_activations_left' );
			$expires_date = get_site_option('cmplz_license_expires' );
			if ( !$expires_date ) {
				$expires_message = __("Not available");
			} else {
				$expires_message = $expires_date === 'lifetime' ? __( "You have a lifetime license.", 'complianz-gdpr' ) : sprintf( __( "Valid until %s.", "complianz-gdpr" ), $expires_date );
			}
			$next_upsell = '';
			if ( $activations_left == 0 && $activation_limit !=0 ) {
				switch ( $activation_limit ) {
					case 1:
						$next_upsell = sprintf(__( "Upgrade to a %s5 sites or Agency%s license.", "complianz-gdpr" ), $account_link, '</a>');
						break;
					case 5:
						$next_upsell = sprintf(__( "Upgrade to an %sAgency%s license.", "complianz-gdpr" ), $account_link, '</a>');
						break;
					default:
						$next_upsell = sprintf(__( "You can renew your license on your %saccount%s.", "complianz-gdpr" ), $account_link, '</a>');
				}
			}

			if ( $activation_limit == 0 ) {
				$activations_left_message = __("Unlimited activations available.", "complianz-gdpr").' '.$next_upsell;
			} else {
				$activations_left_message = sprintf(__("%s/%s activations available.", "complianz-gdpr"), $activations_left, $activation_limit ).' '.$next_upsell;
			}

			$messages = array();

			/**
			 * Some default messages, if the license is valid
			 */

			if ( $status === 'valid' || $status === 'inactive' || $status === 'deactivated' || $status === 'site_inactive' ) {

				$messages[] = array(
						'type' => 'success',
						'label' => __('Valid', "complianz-gdpr"),
						'message' => $expires_message,
				);

				$messages[] = array(
						'type' => 'premium',
						'label' => __('License', "complianz-gdpr"),
						'message' => sprintf(__("Valid license for %s.", "complianz-gdpr"), cmplz_product_name.' '.cmplz_version),
				);

				$messages[] = array(
						'type' => 'premium',
						'label' => __('License', "complianz-gdpr"),
						'message' => $activations_left_message,
				);

				if ( is_multisite() && !defined('cmplz_premium_multisite') ) {
					$messages[] = array(
							'type' => 'open',
							'label' => __('Multisite', "complianz-gdpr"),
							'message' => sprintf(__("Multisite detected. Please consider upgrading to %smultisite%s.", "complianz-gdpr"), $agency_link, '</a>' ),
					);
				}
			} else {
				//it is possible the site does not have an error status, and no activations left.
				//in this case the license is activated for this site, but it's the last one. In that case it's just a friendly reminder.
				//if it's unlimited, it's zero.
				//if the status is empty, we can't know the number of activations left. Just skip this then.
				if ( $status !== 'no_activations_left' && $status !== 'empty' && $activations_left == 0 ){
					$messages[] = array(
							'type' => 'open',
							'label' => __('License', "complianz-gdpr"),
							'message' => $activations_left_message,
					);
				}
			}
			switch ( $status ) {
				case 'error':
					$messages[] = array(
							'type' => 'open',
							'label' => __('No response', "complianz-gdpr"),
							'message' => sprintf(__("The license information could not be retrieved at this moment. Please try again at a later time.", "complianz-gdpr"), $account_link, '</a>'),
					);
					break;
				case 'empty':
					$messages[] = array(
							'type' => 'open',
							'label' => __('Open', "complianz-gdpr"),
							'message' => sprintf(__("Please enter your license key. Available in your %saccount%s.", "complianz-gdpr"), $account_link, '</a>'),
					);
					break;
				case 'inactive':
				case 'site_inactive':
				case 'deactivated':
					$messages[] = array(
							'type' => 'warning',
							'label' => __('Open', "complianz-gdpr"),
							'message' => sprintf(__("Please activate your license key.", "complianz-gdpr"), $account_link, '</a>'),
					);
					break;
				case 'revoked':
					$messages[] = array(
							'type' => 'warning',
							'label' => __('Warning', "complianz-gdpr"),
							'message' => sprintf(__("Your license has been revoked. Please contact %ssupport%s.", "complianz-gdpr"), $support_link, '</a>'),
					);
					break;
				case 'missing':
					$messages[] = array(
							'type' => 'warning',
							'label' => __('Warning', "complianz-gdpr"),
							'message' => sprintf(__("Your license could not be found in our system. Please contact %ssupport%s.", "complianz-gdpr"), $support_link, '</a>'),
					);
					break;
				case 'invalid':
				case 'disabled':
					$messages[] = array(
							'type' => 'warning',
							'label' => __('Warning', "complianz-gdpr"),
							'message' => sprintf(__("This license is not valid. Find out why on your %saccount%s.", "complianz-gdpr"), $account_link, '</a>'),
					);
					break;
				case 'item_name_mismatch':
					$messages[] = array(
							'type' => 'warning',
							'label' => __('Warning', "complianz-gdpr"),
							'message' => sprintf(__("This license is not valid for this product. Find out why on your %saccount%s.", "complianz-gdpr"), $account_link, '</a>'),
					);
					break;
				case 'no_activations_left':
					//can never be unlimited, for obvious reasons
					$messages[] = array(
							'type' => 'warning',
							'label' => __('License', "complianz-gdpr"),
							'message' => sprintf(__("%s/%s activations available.", "complianz-gdpr"), 0, $activation_limit ).' '.$next_upsell,
					);
					break;
				case 'expired':
					$messages[] = array(
							'type' => 'warning',
							'label' => __('Warning', "complianz-gdpr"),
							'message' => sprintf(__("Your license key has expired. Please renew your license key on your %saccount%s.", "complianz-gdpr"), $account_link, '</a>'),
					);
					break;
			}

			$html = '';
			foreach ( $messages as $message ) {
				$html .= $this->license_status_info( $message );
			}

			return $html;
		}

		/**
		 * Show a notice regarding the license
		 * @param array $message
		 *
		 * @return string
		 */

		public function license_status_info($message)
		{
			if ( !isset($message['message']) || $message['message'] == '') return '';
			ob_start();
			?>

			<div class="cmplz-status-info">
				<div class="cmplz-license-status-container">
					<span class="cmplz-license-status cmplz-<?php echo esc_attr($message['type']) ?>">
						<?php echo esc_html($message['label']) ?>
					</span>
				</div>
				<div class="cmplz-license-notice-text">
					<?php echo wp_kses_post($message['message']) ?>
				</div>
			</div>
			<?php
			return ob_get_clean();
		}

    }
} //class closure
