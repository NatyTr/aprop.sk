<?php
defined('ABSPATH') or die("you do not have access to this page!");

if (!class_exists("cmplz_support")) {
	class cmplz_support
	{
		private static $_this;

		function __construct()
		{
			if (isset(self::$_this))
				wp_die(sprintf('%s is a singleton class and you cannot create a second instance.', get_class($this)));

			self::$_this = $this;
			add_action('admin_init', array($this, 'process_support_request'));
			add_filter( 'allowed_redirect_hosts' , array($this, 'allow_complianz_redirect') , 10 );
		}

		static function this()
		{
			return self::$_this;
		}

		/**
		 * post support request on really-simple-ssl.com
		 */

		public function process_support_request()
		{
			if (isset($_POST['cmplz_support_request']) ) {

				if (!wp_verify_nonce($_POST['cmplz-nonce'], 'cmplz-support-request')) return;
				$user_info = get_userdata(get_current_user_id());
				$email = urlencode($user_info->user_email);
				$name = urlencode($user_info->display_name);
				$support_request = urlencode(esc_html($_POST['cmplz_support_request']) );
				$website = site_url();
				$details = "\n";
				$console_errors = cmplz_get_console_errors();
				if (empty($console_errors)) $console_errors = "none found";
				$details .=  'Detected console errors: ' . $console_errors . "\n". "\n";

				$details .=  "General\n";
				$details .=  "Plugin version: " . cmplz_version . "\n";
				global $wp_version;
				$details .=  "WordPress version: " . $wp_version . "\n";
				$details .=  "PHP version: " . PHP_VERSION . "\n";
				$details .=  "Server: " . cmplz_get_server() . "\n";
				$multisite = is_multisite() ? 'yes' : 'no';
				$details .=  "Multisite: " . $multisite . "\n";
				$details .=  "\n";
				$plugins         = get_option( 'active_plugins' );
				$details .=  "Active plugins: " . "\n";
				$details .=  implode( "\n", $plugins ) . "\n";

				$settings = get_option( 'complianz_options_settings' );
				unset( $settings['custom_document_css'] );
				$details .=  "\n"."General settings" . "\n";
				$details .=  implode("\n", array_map(
					function ($v, $k) { return sprintf("%s : %s", $k, $v); },
					$settings,
					array_keys($settings)
				));

				$wizard   = get_option( 'complianz_options_wizard' );
				unset( $wizard['custom_document_css'] );

				$details .=  "\n\n"."Wizard settings" . "\n";
				$details .=  implode("\n", array_map(
					function ($v, $k) { return sprintf("%s : %s", $k, $v); },
					$wizard,
					array_keys($wizard)
				));

				$user_id = get_current_user_id();
				$license_key = COMPLIANZ::$license->license_key();

				if (get_option('cmplz_pro_disable_license_for_other_users') == 1 && get_option('cmplz_licensing_allowed_user_id') == $user_id) {
					$license_key = COMPLIANZ::$license->maybe_decode( $license_key );
				} elseif (!get_option('rsssl_pro_disable_license_for_other_users') ) {
					$license_key = COMPLIANZ::$license->maybe_decode( $license_key );
				} else {
					$license_key = 'protected';
				}

				$details = str_replace("\n", '--br--', $details );

				$url = "https://complianz.io/support/?question=$support_request&license=$license_key&email=$email&website=$website&user=$name&details=$details";

				wp_redirect($url);
				exit;
			}
		}

		public function allow_complianz_redirect($content){
			$content[] = 'complianz.io';
			return $content;
		}
	}
} //class closure
