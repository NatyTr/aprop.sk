<?php
defined('ABSPATH') or die("you do not have access to this page!");
/**
 * Vendorlist updates from:
 * https://github.com/InteractiveAdvertisingBureau/GDPR-Transparency-and-Consent-Framework/blob/master/TCFv2/IAB%20Tech%20Lab%20-%20Consent%20string%20and%20vendor%20list%20formats%20v2.md#the-global-vendor-list
 * https://vendor-list.consensu.org/v2/vendor-list.json
 * Translations: https://register.consensu.org/Translation
 *
 * CCPA vendorlist:
 * https://tools.iabtechlab.com/login?returnUrl=%2Flspa
 */

/**
 * Drop in for TCF integration
 */
$debug = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? time() : '';

/**
 * Conditionally initialize
 */
add_action('plugins_loaded', 'cmplz_tcf_init', 10);
add_action( 'complianz_after_save_wizard_option', 'cmplz_tcf_after_save_cookie_settings_option', 10, 4 );

function cmplz_tcf_init() {
	if ( cmplz_iab_is_enabled() ) {
		add_action( 'admin_init', 'cmplz_tcf_change_settings' );
		add_action( 'init', 'cmplz_init_vendorlist' );
		add_shortcode( 'cmplz-tcf-vendors', 'cmplz_tcf_vendors' );
		add_shortcode( 'cmplz-tcf-us-vendors', 'cmplz_tcf_us_vendors' );
		add_action( 'cmplz_enqueue_banner_editor', 'cmplz_tcf_enqueue_assets' , PHP_INT_MAX);
		add_action( 'wp_enqueue_scripts', 'cmplz_tcf_enqueue_stub', 0 );
		add_action( 'wp_enqueue_scripts', 'cmplz_tcf_enqueue_assets' );
		add_filter( 'cmplz_tcf_active', 'cmplz_front_end_iab_is_enabled' );
		add_filter( 'cmplz_fields_load_types', 'cmplz_tcf_edit_cookiebanner_settings', 20 );
		add_filter( 'cmplz_edit_banner_consenttypes', 'cmplz_tcf_cookiebanner');
		add_filter( 'cmplz_cookie_policy_snapshot_html' , 'cmplz_tcf_adjust_cookie_policy_snapshot_html' );
		add_filter( 'cmplz_cookiebanner_settings_front_end', 'cmplz_tcf_ajax_loaded_banner_data', 10, 2);
		add_filter( 'cmplz_cookiebanner_settings_html', 'cmplz_tcf_settings_html', 10, 2);
		add_filter( 'cmplz_banner_after_categories', 'cmplz_banner_after_categories', 10, 2);
		add_filter( 'cmplz_warning_types', 'cmplz_tcf_warnings_types' );
		add_action('cmplz_upgrade',  'cmplz_tcf_upgrade_premium', 10);
		add_filter( "auto_update_plugin", 'cmplz_tcf_override_auto_updates', 100, 2 );

	}
}

/**
 * Hooks in on the free plugin upgrade function
 * Runs when the plugin is updated in the dashboard.
 * @param $prev_version
 * @hooked cmplz_upgrade
 * @return void
 */

function cmplz_tcf_upgrade_premium($prev_version) {
	//for legint, we upgrade the active policy id
	if ( $prev_version && version_compare( $prev_version, '6.1.0.1', '<' ) ) {
		COMPLIANZ::$cookie_admin->upgrade_active_policy_id();
	}
}

/**
 * If TCF is enabled, force auto updates to on
 *
 * @param $update
 * @param $item
 *
 * @return false|mixed
 */
function cmplz_tcf_override_auto_updates( $update, $item ) {
	//skip if debug enabled
	if ( defined('WP_DEBUG') && WP_DEBUG ) {
		return $update;
	}

	if ( strpos($item->slug , 'complianz-gpdr-premium') !==false ) {
		return true;
	}
	return $update;
}

/**
 * Add the TCF elements to the banner
 */
function cmplz_banner_after_categories( )
{
	global $consent_type;
	if ( $consent_type === 'optin' ) {
		echo cmplz_get_template( "tcf-categories.php", array(), trailingslashit( cmplz_path ) . 'pro/templates/');
	}
}

/**
 * Make sure the vendorlist is initially downloaded, then run each month as backup for the cron
 */

function cmplz_init_vendorlist(){
	if ( !cmplz_iab_is_enabled() ) return;

	if ( !COMPLIANZ::$license->license_is_valid() ) {
		cmplz_update_option('wizard', 'uses_ad_cookies_personalized', 'yes' );
	}

	if ( !get_transient( 'cmplz_vendorlist_downloaded_once') ) {
		cmplz_update_json_files();
	}
}

/**
 * @param array $warnings
 * @return array
 */

function cmplz_tcf_warnings_types($warnings)
{
	$warnings = $warnings + array(
			'cmp-file-error' => array(
				'plus_one' => true,
				'warning_condition' => 'get_value_uses_ad_cookies_personalized==tcf',
				'success_conditions'  => array(
					'NOT cmplz_tcf_cmp_files_missing',
				),
				'dismissible' => false,
				'urgent' => __( "The CMP vendorlist files for TCF are not downloaded to, or reachable in the uploads folder yet. If you continue to see this message, contact support to update the files manually.", 'complianz-gdpr')
			),
		);


	$auto_updates = get_option('auto_update_plugins');
	if ( $auto_updates && is_array($auto_updates) ) {
		if ( !in_array( cmplz_plugin, $auto_updates )){
			$warnings += array(
					'auto-updates-not-enabled' => array(
						'plus_one' => true,
						'warning_condition' => '_true_',
						'urgent' => __( "Please enable auto updates for Complianz. This is mandatory when TCF is active, to be able to quickly adapt to new requirements by the IAB.", 'complianz-gdpr'  ).
						            cmplz_read_more('https://complianz.io/about-auto-updates/'),
						'dismissible' => false,
					),
				);
		}
	}

	return $warnings;
}

/**
 * Check if the cmp files are missing
 * @return bool
 */
function cmplz_tcf_cmp_files_missing(){
	$uploads    = wp_upload_dir();
	$upload_dir = $uploads['basedir'];
	$path       = $upload_dir . '/complianz/cmp/vendorlist/';

	if ( !file_exists($path.'vendor-list.json') || get_option('cmplz_fallback_cmp') ){
		return true;
	} else {
		return false;
	}
}
/**
 * On activation set some new settings in cookiebanner
 */
function cmplz_update_json_files() {
	if ( cmplz_iab_is_enabled() ) {
		$cmplzExistingLanguages = ['bg', 'ca', 'cs', 'da', 'de', 'el', 'es', 'et', 'fi', 'fr', 'hr', 'hu', 'it', 'ja', 'lt', 'lv', 'mt', 'nl', 'no', 'pl', 'pt', 'ro', 'ru', 'sk', 'sl', 'sr', 'sv', 'tr', 'zh',];
		$srcUrl = 'https://cookiedatabase.org/cmp/vendorlist/';
		cmplz_download_json_to_site($srcUrl.'vendor-list.json');
		foreach ($cmplzExistingLanguages as $lang ) {
			cmplz_download_json_to_site($srcUrl."purposes-$lang.json" );
		}

		if ( cmplz_get_value('california') === 'yes') {
			cmplz_download_json_to_site($srcUrl."lspa.json" );
		}
		set_transient( 'cmplz_vendorlist_downloaded_once', true, WEEK_IN_SECONDS );
		$uploads    = wp_upload_dir();
		$upload_url = $uploads['baseurl'];
		$url = $upload_url . '/complianz/'.'cmp/vendorlist/vendor-list.json';
		$response = wp_remote_get($url);
		if ( is_wp_error( $response ) ){
			update_option('cmplz_fallback_cmp', true );
			set_transient( 'cmplz_vendorlist_downloaded_once', true, HOUR_IN_SECONDS );
		} else {
			update_option('cmplz_fallback_cmp', false );
		}
	}
}

/**
 * Download a json file to this website
 *
 * @param string $src
 * @return string url
 *
 * @since 5.2.3
 */
function cmplz_download_json_to_site( $src ) {
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	$uploads    = wp_upload_dir();
	$upload_dir = $uploads['basedir'];

	if ( ! file_exists( $upload_dir ) ) {
		mkdir( $upload_dir );
	}

	if ( ! file_exists( $upload_dir . "/complianz" ) ) {
		mkdir( $upload_dir . "/complianz" );
	}

	if ( ! file_exists( $upload_dir . '/complianz/cmp' ) ) {
		mkdir( $upload_dir . '/complianz/cmp' );
	}

	if ( ! file_exists( $upload_dir . '/complianz/cmp/vendorlist' ) ) {
		mkdir( $upload_dir . '/complianz/cmp/vendorlist' );
	}

	//download file
	$tmpfile  = download_url( $src, $timeout = 25 );
	$file     = $upload_dir . "/complianz/cmp/vendorlist/" . basename( $src );

	//check for errors
	if ( !is_wp_error( $tmpfile ) ) {
		//remove current file
		if ( file_exists( $file ) ) {
			unlink( $file );
		}

		//in case the server prevents deletion, we check it again.
		if ( ! file_exists( $file ) ) {
			copy( $tmpfile, $file );
		}
	}

	if ( is_string( $tmpfile ) && file_exists( $tmpfile ) ) {
		unlink( $tmpfile );
	}
}


/**
 * On activation of TCF, do some initializiation
 */
function cmplz_tcf_change_settings() {
	if (!current_user_can('manage_options')) return;

	if ( !get_option("cmplz_tcf_initialized") ) {

		//set the color scheme
		$color_schemes = cmplz_banner_color_schemes();
		$color_scheme = $color_schemes['tcf'];

		//set banner to center variant
		$banners = cmplz_get_cookiebanners();
		if ( $banners ) {
			foreach ( $banners as $banner_item ) {
				$banner = new CMPLZ_COOKIEBANNER( $banner_item->ID );
				$banner->soft_cookiewall = false;
				$banner->banner_width = '600';
				$banner->use_box_shadow = true;
				$banner->position = 'center';
				$banner->revoke = array(
					'text' => $banner->revoke['text'],
					'show' => true,
				);
				foreach ($color_scheme as $fieldname => $value ){
					$banner->{$fieldname} = $value;
				}
				$banner->view_preferences = __('Manage options', 'complianz-gdpr');
				$banner->header               = array(
					'text' => __("Manage your privacy", 'complianz-gdpr'),
					'show' => true,
				);
				$banner->colorpalette_button_accept = array(
					'background'    => '#333',
					'border'        => '#333',
					'text'          => '#fff',
				);
				$banner->colorpalette_button_settings = array(
					'background'    => '#fff',
					'border'        => '#333',
					'text'          => '#333',
				);
				$banner->save();
			}
		}

		//set default values for the TCF features.
		cmplz_update_option('wizard', 'tcf_purposes', cmplz_tcf_get('purposes', true) );
		cmplz_update_option('wizard', 'tcf_specialPurposes', cmplz_tcf_get('specialPurposes', true) );
		cmplz_update_option('wizard', 'tcf_features', cmplz_tcf_get('features', true) );

		//deactivate a/b testing
		cmplz_update_option( 'settings', 'a_b_testing', false );
		/**
		 * Send an email
		 * but only once
		 */

		if ( !get_option('cmplz_tcf_mail_sent') ) {
			$from = get_option('admin_email');
			$site_url = site_url();
			$subject = "TCF enabled on ".$site_url;
			$to      = "tcf@really-simple-plugins.com";
			$headers = array();
			$message = "TCF was enabled on $site_url";
			add_filter( 'wp_mail_content_type', function ( $content_type ) {return 'text/html';} );
			$headers[] = "Reply-To: $from <$from>" . "\r\n";
			wp_mail( $to, $subject, $message, $headers );
			remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
			update_option( 'cmplz_tcf_mail_sent', true );
		}

		update_option("cmplz_tcf_initialized", true);
	}
}

/**
 * Generate default banner text
 * @return string
 */
function cmplz_get_default_banner_text(){
	$global = false;
	$str = '<p>'.__("To provide the best experiences, we and our partners use technologies like cookies to store and/or access device information. Consenting to these technologies will allow us and our partners to process personal data such as browsing behavior or unique IDs on this site. Not consenting or withdrawing consent, may adversely affect certain features and functions.", "complianz-gdpr").'</p>';
	$str .= '<p>'.__("Click below to consent to the above or make granular choices.", "complianz-gdpr");
	if ($global) {
		$str .= '&nbsp;'.__("Your choices will be applied globally.", "complianz-gdpr");
		$str .= '&nbsp;'.__("This means that your settings will be available on other sites that set your choices globally.", "complianz-gdpr");
	} else {
		$str .= '&nbsp;'.__("Your choices will be applied to this site only.", "complianz-gdpr");

	}
	$str .= '&nbsp;'.__("You can change your settings at any time, including withdrawing your consent, by using the toggles on the Cookie Policy, or by clicking on the manage consent button at the bottom of the screen.", "complianz-gdpr").
	        '</p>';
	return $str;
}

/**
 * Get items from the most recent vendor list, and cache it for one month
 * @param string $fieldname
 * @param bool $default_on
 * @return array
 */

function cmplz_tcf_get($fieldname, $default_on = false){

	//user locale
	$locale = substr(get_user_locale(), 0, 2);
	$existing_languages = array('bg', 'ca', 'cs', 'da', 'de', 'el', 'es', 'et', 'fi', 'fr', 'hr', 'hu', 'it', 'ja', 'lt', 'lv', 'mt', 'nl', 'no', 'pl', 'pt', 'ro', 'ru', 'sk', 'sl', 'sr', 'sv', 'tr', 'zh',);
	if ( !in_array($locale, $existing_languages)) {
		$locale = 'en';
	}
	$items = get_transient("cmplz_tcf_".$locale."_".$fieldname);
	if ( !$items ) {
		$uploads    = wp_upload_dir();
		$upload_url = $uploads['baseurl'];
		$cmp_url = $upload_url . '/complianz/';

		//get the purposes
		if ($locale === 'en' ) {
			$url = $cmp_url.'cmp/vendorlist/vendor-list.json';
			$fallback_url = 'https://cookiedatabase.org/cmp/vendorlist/vendor-list.json';
		} else {
			$url = $cmp_url.'cmp/vendorlist/purposes-'.$locale.'.json';
			$fallback_url = 'https://cookiedatabase.org/cmp/vendorlist/purposes-'.$locale.'.json';
		}
		$response = wp_remote_get($url);
		//fallback to complianz cmp data
		if ( is_wp_error( $response ) ){
			$response = wp_remote_get($fallback_url);
		}
		$items = array();
		if ( is_wp_error( $response ) ){
			error_log("error retrieving TCF data");
			set_transient("cmplz_tcf_".$locale."_".$fieldname, $items, DAY_IN_SECONDS);
			return array();
		} else {
			if (isset($response["response"]['code']) && $response["response"]['code'] === 200 && isset($response["body"])) {
				$json = json_decode($response["body"]);
				$remote_items = $json->{$fieldname};
				$items = array();
				if ( is_object($remote_items) ) {
					foreach ( $remote_items as $remote_item ) {
						$items[ $remote_item->id ] = $remote_item->name;
					}
				}
				set_transient("cmplz_tcf_".$locale."_".$fieldname, $items, MONTH_IN_SECONDS);
			}
		}
	}

	//for default value purposes
	if ( $default_on && is_array($items) ) {
		foreach ($items as $key => $item ){
			$items[$key] = 1;
		}
	}

	return $items;
}

/**
 * Add TCF section to WIZARD
 * @param $steps
 *
 * @return mixed
 */
function cmplz_tcf_add_step($steps){
	if ( cmplz_iab_is_enabled() ) {

		$steps['wizard'][ STEP_COOKIES ]['sections'][8] = array(
			'title' => __( 'Transparency Consent Framework', 'complianz-gdpr' ),
			'intro' => __( 'The below questions will help you configure a vendor list of your choosing. Only vendors that adhere to the purposes and special features you configure will be able to serve ads.',
					'complianz-gdpr' )
			           . cmplz_read_more( 'https://complianz.io/tcf/' ),
		);
	}
	return $steps;
}
add_filter( 'cmplz_steps', 'cmplz_tcf_add_step' );

/**
 * pass possible tcf regions to front end
 * @param array $data
 * @param CMPLZ_COOKIEBANNER $banner
 *
 * @return array
 */

function cmplz_tcf_ajax_loaded_banner_data($data, $banner){
	$data['tcf_regions'] = cmplz_tcf_regions();
	return $data;
}

/**
 * set default banner text
 * @param array $data
 * @param CMPLZ_COOKIEBANNER $banner
 *
 * @return mixed
 */

function cmplz_tcf_settings_html($data, $banner){
	$data['message_optin'] = cmplz_get_default_banner_text();
	return $data;
}

/**
 * Get regions where the TCF applies
 * As canada may have optin, we add Canada to the gdpr regions as well in that case
 * @return array
 */
function cmplz_tcf_regions(){
	$tcf_regions = array();
	$regions = COMPLIANZ::$config->regions;
	foreach ( $regions as $region => $region_data ) {
		if ( $region_data['tcf'] ) {
			$tcf_regions[] = $region;
		}
	}
	return $tcf_regions;
}

/**
 * Change settings options
 */

function cmplz_tcf_edit_cookiebanner_settings($fields){
	$fields['a_b_testing']['disabled'] = true;
	//we prevent all editing, as these options have to be the same for all regions.
	$fields['message_optin']['condition'] = array( 'hidden' => true, );
	unset($fields['color_scheme']);
	$fields['use_categories']['condition'] = array( 'hidden' => true, );
	unset( $fields['position']['options']['bottom-left']);
	unset( $fields['position']['options']['bottom-right']);

	$fields['colorpalette_background']['condition'] = array( 'hidden' => true, );
	$fields['border_width']['condition'] = array( 'hidden' => true, );

	$fields['colorpalette_text']['condition'] = array( 'hidden' => true, );
	$fields['colorpalette_toggles']['condition'] = array( 'hidden' => true, );
	$fields['colorpalette_border_radius']['condition'] = array( 'hidden' => true, );
	$fields['colorpalette_button_deny']['condition'] = array( 'hidden' => true, );
	$fields['save_preferences']['condition'] = array( 'hidden' => true, );
	$fields['dismiss']['condition'] = array( 'hidden' => true, );
	$fields['header']['help'] = __("Configuring your TCF cookie banner is limited due to IAB guidelines.", "complianz-gdpr") .
	                            cmplz_read_more('https://complianz.io/customizing-the-tcf-banner/');
	$fields['header']['help_status'] = 'warning';
	$fields['header']['condition'] = false;

	$fields['tcf_purposes'] = array(
		'step'               => STEP_COOKIES,
		'section'            => 8,
		'source'             => 'wizard',
		'translatable'       => false,
		'default'            => cmplz_tcf_get('purposes', true),
		'type'               => 'multicheckbox',
		'options'            => cmplz_tcf_get('purposes'),
		'label'              => __( "Your site will show vendors with the purposes selected here", 'complianz-gdpr' ),
		'help'              => __( "To get a better understanding of vendors, purposes and features please read this definitions guide.", 'complianz-gdpr' ).cmplz_read_more('https://complianz.io/definitions/what-are-vendors/'),
		'time'               => 5,
		'disabled'  => array(
			1,
		)
	);

	$fields['tcf_specialPurposes'] = array(
		'step'               => STEP_COOKIES,
		'section'            => 8,
		'source'             => 'wizard',
		'translatable'       => false,
		'default'            => cmplz_tcf_get('specialPurposes', true),
		'type'               => 'multicheckbox',
		'options'            => cmplz_tcf_get('specialPurposes'),
		'label'              => __( "Your site will show vendors with the special purposes selected here",
			'complianz-gdpr' ),
		'help'              => __( "These special purposes should be enabled for best performance. These purposes are set based on legitimate interest of the vendor, one of the legal bases of data processing.", 'complianz-gdpr' ).cmplz_read_more('https://complianz.io/definition/what-is-a-lawful-basis-for-data-processing/#legitimate-interest'),
		'time'               => 5,
	);

	$fields['tcf_features'] = array(
		'step'               => STEP_COOKIES,
		'section'            => 8,
		'source'             => 'wizard',
		'translatable'       => false,
		'default'            => cmplz_tcf_get('features', true ),
		'type'               => 'multicheckbox',
		'options'            => cmplz_tcf_get('features'),
		'label'              => __( "Your site will show vendors with the features selected here", 'complianz-gdpr' ),
		'time'               => 5,
	);

	$fields['tcf_specialFeatures'] = array(
		'step'               => STEP_COOKIES,
		'section'            => 8,
		'source'             => 'wizard',
		'translatable'       => false,
		'default'            => array(),
		'type'               => 'multicheckbox',
		'options'            => cmplz_tcf_get('specialFeatures'),
		'label'              => __( "Your site will show vendors with the special features selected here", 'complianz-gdpr' ),
		'time'               => 5,
	);

	$fields['tcf_lspact'] = array(
		'step'               => STEP_COOKIES,
		'section'            => 8,
		'source'             => 'wizard',
		'translatable'       => false,
		'default'            => 'no',
		'type'               => 'radio',
		'options'            => COMPLIANZ::$config->yes_no,
		'label'              => __( "Have you signed the IAB Privacy, LLC’s Limited Service Provider Agreement (LSPA)?", 'complianz-gdpr' ),
		'time'               => 5,
		'callback_condition' => array(
			'regions' => array('us'),
			'california' => 'yes',
		),
		'comment' => cmplz_read_more('https://complianz.io/tcf-ccpa/')
	);

	return $fields;
}

/**
 * @param string $fieldname
 *
 * @return array
 */
function cmplz_tcf_get_selected_array_keys( $fieldname ) {
	$values = cmplz_get_value("tcf_".$fieldname);
	if (!is_array($values)) $values = array($values);
	return  array_keys(array_filter($values));
}
/**
 * Enqueue scripts
 * @param $hook
 */

function cmplz_tcf_enqueue_stub( $hook ) {
	wp_enqueue_script( 'cmplz-tcf-stub', cmplz_url . "pro/tcf-stub/build/index.js", array(), cmplz_version, false );
}

/**
 * Enqueue scripts
 * @param $hook
 */

function cmplz_tcf_enqueue_assets( $hook ) {
	$asset_file = include( plugin_dir_path( __FILE__ ) . 'build/index.asset.php');
	wp_enqueue_script(
		'cmplz-tcf',
		cmplz_url . 'pro/tcf/build/index.js',
		$asset_file['dependencies'],
		$asset_file['version'],
		false
	);

	$isServiceSpecific = true;
	$purposes = cmplz_tcf_get_selected_array_keys('purposes');
	$specialPurposes = cmplz_tcf_get_selected_array_keys('specialPurposes');
	$features = cmplz_tcf_get_selected_array_keys('features');
	$specialFeatures = cmplz_tcf_get_selected_array_keys('specialFeatures');
	$uploads    = wp_upload_dir();
	$upload_url = $uploads['baseurl'];
	$cmp_url = $upload_url . '/complianz/';

	wp_localize_script(
		'cmplz-tcf',
		'cmplz_tcf',
		array(
			'cmp_url' => $cmp_url,
			'isServiceSpecific' => $isServiceSpecific,
			'purposes' => $purposes,
			'specialPurposes' => $specialPurposes,
			'features' => $features,
			'specialFeatures' => $specialFeatures,
			'publisherCountryCode' => cmplz_tcf_get_publisher_country_code(),
			'lspact' => cmplz_get_value('tcf_lspact') === 'yes' ? 'Y' : 'N',
			'ccpa_applies' => cmplz_get_value('california') === 'yes',
			'debug' => defined('SCRIPT_DEBUG') && SCRIPT_DEBUG,
			'prefix'=> COMPLIANZ::$cookie_admin->get_cookie_prefix(),

		)
	);
}

function cmplz_tcf_get_publisher_country_code() {
	$country_code = cmplz_get_value( 'country_company' );
	$country_code = substr(strtoupper($country_code),0,2);
	if ( empty($country_code) )  $country_code = 'EN';
	return $country_code;
}

function cmplz_tcf_us_vendors($atts = array(), $content = null, $tag = ''){
	$template =
		'<div class="cmplz-tcf-vendor-container cmplz-tcf-optout cmplz-tcf-checkbox-container">
				<label for="cmplz-tcf-vendor-{vendor_id}">
					{vendor_name}
				</label>
				<div class="cmplz-tcf-links">
					<div class="cmplz-tcf-optout-url"><a target="_blank" rel="noopener noreferrer nofollow" href="{optout_url}">'.__("Opt out","complianz-gdpr").'</a></div>
					<div class="cmplz-tcf-optout-string">{optout_string}</div>
				</div>
			</div>';

	$html =
		'<div id="cmplz-tcf-wrapper-nojavascript">'.__("The TCF vendorlist is not available when javascript is disabled, like on AMP.","complianz-gdpr").'</div>' .
		'<div id="cmplz-tcf-wrapper"><div id="cmplz-tcf-vendor-template" class="cmplz-tcf-template">'.$template.'</div><div id="cmplz-tcf-type-template" class="cmplz-tcf-template"></div>

	<p id="cmplz-tcf-us-vendor-container" class="cmplz-tcf-container"></p>
	<style>#cmplz-tcf-wrapper {
  display:none;
}</style>';


	return apply_filters('cmplz_tcf_us_container', $html);
}

/**
 *
 * Shortcode to insert IAB container in the Cookie Policy
 * @param array  $atts
 * @param null   $content
 * @param string $tag
 *
 * @return false|string
 */

function cmplz_tcf_vendors( $atts = array(), $content = null, $tag = '' ) {
	$template =
		'<div class="cmplz-tcf-vendor-container cmplz-tcf-optin cmplz-tcf-checkbox-container">
				<label for="cmplz-tcf-vendor-{vendor_id}">
					<input id="cmplz-tcf-{vendor_id}" class="cmplz-tcf-vendor-input" value="1" type="checkbox" name="cmplz-tcf-vendor-{vendor_id}">
					{vendor_name}
					<a href="#" class="cmplz-tcf-toggle-vendor cmplz-tcf-rm"></a>
				</label>
				<div class="cmplz-tcf-links">
					<div class="cmplz-tcf-policy-url"><a target="_blank" rel="noopener noreferrer nofollow" href="{privacy_policy}">'.__("Privacy Policy","complianz-gdpr").'</a></div>

				</div>
				<div class="cmplz-tcf-info">
					<div class="cmplz-tcf-info-content">
						<div class="cmplz-tcf-header">'.__("Legal bases", 'complianz-gdpr').'</div>
						<div class="cmplz-tcf-description">
							<label for="consent_{vendor_id}">
									<input type="checkbox" name="consent_{vendor_id}" class="cmplz-tcf-consent-input">
									<a target="_blank" rel="noopener noreferrer nofollow" href="https://cookiedatabase.org/tcf/consent">'.__("Consent", 'complianz-gdpr').'</a>
							</label>
							<label for="legitimate_interest_{vendor_id}" class="cmplz_tcf_legitimate_interest_checkbox">
									<input type="checkbox" name="legitimate_interest_{vendor_id}" class="cmplz-tcf-legitimate-interest-input">
									<a target="_blank" rel="noopener noreferrer nofollow" href="https://cookiedatabase.org/tcf/legitimate-interest">'.__("Legitimate interest", 'complianz-gdpr').'</a>
							</label>
						</div>
					</div>
					<div class="cmplz-tcf-info-content">
						<div class="cmplz-tcf-header">'.__("Maximum cookie expiration:", 'complianz-gdpr').'</div>
						<div class="cmplz-tcf-description">&nbsp;
							<span class="session-storage">'.__("Session Storage", "complianz-gdpr").'</span>
							<span class="retention_days">'. cmplz_sprintf(__("%s Days", "complianz-gdpr"), '{cookie_retention_days}').'</span>
							<span class="retention_seconds">'. cmplz_sprintf(__("%s Seconds", "complianz-gdpr"), '{cookie_retention_seconds}').'</span>
						</div>
					</div>
					<div class="cmplz-tcf-info-content">
						<div class="cmplz-tcf-header">'.__("Non-cookie storage and access:", 'complianz-gdpr').'</div>
						<div class="cmplz-tcf-description">&nbsp;<span class="non-cookie-storage-active">'.__("Yes", "complianz-gdpr").'</span><span class="non-cookie-storage-inactive">'.__("No", "complianz-gdpr").'</span></div>
					</div>
					<div class="cmplz-tcf-info-content">
						<div class="cmplz-tcf-header">'.__("Cookie Refresh:", 'complianz-gdpr').'</div>
						<div class="cmplz-tcf-description">&nbsp;<span class="non-cookie-refresh-active">'.__("Yes", "complianz-gdpr").'</span><span class="non-cookie-refresh-inactive">'.__("No", "complianz-gdpr").'</span></div>
					</div>
					<div class="cmplz-tcf-info-content">
						<div class="cmplz-tcf-header">'.__("Purposes", 'complianz-gdpr').'</div>
						<div class="cmplz-tcf-description">{purposes}</div>
					</div>
				</div>
			</div>';

	$type_template = cmplz_iab_getcheckbox_html();

	$buttons = '<div id="cmplz-tcf-buttons-template"><div class="cmplz-tcf-buttons"><button id="cmplz-tcf-selectall">'.__("Select all","complianz-gdpr").'</button><button id="cmplz-tcf-deselectall">'.__("Deselect all","complianz-gdpr").'</button></div></div>';

	$html =
		'<div id="cmplz-tcf-wrapper-nojavascript">'.__("The TCF vendorlist is not available when javascript is disabled, like on AMP.","complianz-gdpr").'</div>' .
		'<div id="cmplz-tcf-wrapper">'.$buttons.'<div id="cmplz-tcf-vendor-template" class="cmplz-tcf-template">'.$template.'</div><div id="cmplz-tcf-type-template" class="cmplz-tcf-template">'.$type_template.'</div>
	<p>'.
		__("These are the partners we share data with. By clicking into each partner, you can see which purposes they are requesting consent and/or which purposes they are claiming legitimate interest for.","complianz-gdpr").
		'</p><p>'.
		__("You can provide or withdraw consent, and object to legitimate interest purposes for processing your personal data. However, please note that by disabling all data processing, some site functionality may be affected.","complianz-gdpr").
		'</p>
	<p class="cmplz-subtitle">7.2.1 '.__("Consent","complianz-gdpr").'</p>
	<p>'.__("Below you can give and withdraw your consent on a per purpose basis.","complianz-gdpr").'</p>
	<b>'.__("Statistics","complianz-gdpr").'</b>
	<p id="cmplz-tcf-statistics-purpose_consents-container" class="cmplz-tcf-container"></p>
	<b>'.__("Marketing","complianz-gdpr").'</b>
	<p id="cmplz-tcf-marketing-purpose_consents-container" class="cmplz-tcf-container"></p>

	<p class="cmplz-subtitle">7.2.2 '.__("Legitimate Interest","complianz-gdpr").'</p>
    <p>'.__("Some Vendors set purposes with legitimate interest, a legal basis under the GDPR for data processing. You have the \"Right to Object\" to this data processing and can do so below per purpose.","complianz-gdpr").'</p>
	<b>'.__("Statistics","complianz-gdpr").'</b>
	<p id="cmplz-tcf-statistics-purpose_legitimate_interests-container" class="cmplz-tcf-container"></p>

	<b>'.__("Marketing","complianz-gdpr").'</b>
	<p id="cmplz-tcf-marketing-purpose_legitimate_interests-container" class="cmplz-tcf-container"></p>

	<p class="cmplz-subtitle">7.2.2 '.__("Special features and purposes","complianz-gdpr").'</p>
	<div id="cmplz-tcf-specialfeatures-wrapper">
		<b>'.__("Special features","complianz-gdpr").'</b>
		<p>'.__("For some of the purposes we and/or our partners use below features.", "complianz-gdpr").'</p>
		<p id="cmplz-tcf-specialfeatures-container" class="cmplz-tcf-container"></p>
	</div>

	<div id="cmplz-tcf-specialpurposes-wrapper">
		<b>'.__("Special purposes","complianz-gdpr").'</b>
		<p>' . __( "We and/or our partners have a legitimate interest for the following two purposes:", "complianz-gdpr" ) . '</p>
		<p id="cmplz-tcf-specialpurposes-container" class="cmplz-tcf-container"></p>
	</div>

	<div id="cmplz-tcf-features-wrapper">
		<b>'.__("Features","complianz-gdpr").'</b>
		<p>'.__("For some of the purposes above we and our partners", "complianz-gdpr").'</p>
		<p id="cmplz-tcf-features-container" class="cmplz-tcf-container"></p>
	</div>

	<p class="cmplz-subtitle">7.2.3 '.__("Vendors","complianz-gdpr").'</p>

	<div id="cmplz-tcf-vendor-container" class="cmplz-tcf-container"></div>
	<style>#cmplz-tcf-wrapper {
  display:none;
}</style>';


	return apply_filters('cmplz_tcf_container', $html);
}


function cmplz_iab_getcheckbox_html(  ) {

	return '<div class="cmplz-tcf-{type}-container cmplz-tcf-checkbox-container">
		<label for="cmplz-tcf-{type}-{type_id}">
			<input id="cmplz-tcf-{type}-{type_id}" class="cmplz-tcf-{type}-input cmplz-tcf-input" value="1" type="checkbox" name="cmplz-tcf-{type}-{type_id}">
			{type_name} <a href="#" class="cmplz-tcf-toggle cmplz-tcf-rm"></a>
			<div id="cmplz-tcf-{type}-{type_id}-desc" class="cmplz-tcf-type-description">{type_description}</div>
		</label>
	</div>';
}

/**
 * If the global settings is changed, we need to reset the text
 * @param $fieldname
 * @param $fieldvalue
 * @param $prev_value
 * @param $type
 */

function cmplz_tcf_after_save_cookie_settings_option($fieldname, $fieldvalue, $prev_value, $type){
	if (!current_user_can('manage_options')) return;
	//only run when changes have been made
	if ($fieldvalue === $prev_value) return;

	if ($fieldname==='uses_ad_cookies' && $fieldvalue === 'no' ) {
		//because the post values are already in a sanitized array, we can't stop the current tcf value from being saved.
		//we add another hook, which resets the value after the saving has completed
		add_action('cmplz_after_saved_all_fields', 'cmplz_tcf_reset_tcf', 10, 1 );
	}

	if ($fieldname==='uses_ad_cookies_personalized' && $fieldvalue === 'tcf' ) {
		update_option("cmplz_tcf_initialized", false);
	}
}

/**
 * After all fields have been saved, reset the tcf if 'uses_ad_cookies' has been set to no.
 * @param array $posted_fields
 */
function cmplz_tcf_reset_tcf($posted_fields){
	cmplz_update_option( 'wizard', 'uses_ad_cookies_personalized', 'no' );
}

/**
 * Check for IAB support, including the required files
 * Separate from the iab_is_enabled function, as this is used for the json files.
 * @return bool
 */
function cmplz_front_end_iab_is_enabled(){
	if ( cmplz_tcf_cmp_files_missing() ) {
		return false;
	}
	return cmplz_iab_is_enabled();
}

/**
 * Check if there are compatibility issues
 */

function cmplz_iab_is_enabled(){

	if ( !COMPLIANZ::$license->license_is_valid() ) {
		return false;
	}

	return cmplz_get_value('uses_ad_cookies_personalized', false, 'wizard') === 'tcf';
}

/**
 * Disable advertising integrations
 */
function cmplz_tcf_disable_integrations(){
	remove_filter( 'cmplz_known_script_tags', 'cmplz_advertising_script' );
	remove_filter( 'cmplz_known_script_tags', 'cmplz_advertising_iframetags' );
}
add_action( 'init', 'cmplz_tcf_disable_integrations' );

/**
 * With TCF, we need to hardcode some categories
 * @param $settings
 *
 * @return mixed
 */
function cmplz_tcf_adjust_cookie_policy_snapshot_settings($settings){
	unset($settings['categories']);

	return $settings;
}
add_filter( 'cmplz_cookie_policy_snapshot_settings' , 'cmplz_tcf_adjust_cookie_policy_snapshot_settings' );

/**
 * Add link to vendors overview
 * @param $html
 *
 * @return mixed
 */

function cmplz_tcf_adjust_cookie_policy_snapshot_html($html){
	$purposes = array_keys(array_filter(cmplz_get_value('tcf_purposes')));
	$special_purposes = array_keys(array_filter(cmplz_get_value('tcf_specialPurposes')));
	$features= array_keys(array_filter(cmplz_get_value('tcf_features')));
	$special_features = array_keys(array_filter(cmplz_get_value('tcf_specialFeatures')));
	$marker_marketing = '<p id="cmplz-tcf-marketing-purposes-container" class="cmplz-tcf-container"></p>';
	$marker_statistics = '<p id="cmplz-tcf-statistics-purposes-container" class="cmplz-tcf-container"></p>';
	$marker_specialfeatures = '<p id="cmplz-tcf-specialfeatures-container" class="cmplz-tcf-container"></p>';
	$marker_features = '<p id="cmplz-tcf-features-container" class="cmplz-tcf-container"></p>';
	$marker_specialpurposes = '<p id="cmplz-tcf-specialpurposes-container" class="cmplz-tcf-container"></p>';

	$p_labels = cmplz_tcf_get('purposes');
	$sp_labels = cmplz_tcf_get('specialPurposes');
	$f_labels = cmplz_tcf_get('features');
	$sf_labels = cmplz_tcf_get('specialFeatures');

	foreach ($p_labels as $key => $label ) {
		if (!in_array($key, $purposes) ) unset($p_labels[$key]);
	}
	foreach ($sp_labels as $key => $label ) {
		if (!in_array($key, $special_purposes) ) unset($sp_labels[$key]);
	}
	foreach ($f_labels as $key => $label ) {
		if (!in_array($key, $features) ) unset($f_labels[$key]);
	}
	foreach ($sf_labels as $key => $label ) {
		if (!in_array($key, $special_features) ) unset($sf_labels[$key]);
	}
	$stats_purposes = cmplz_tcf_filter_by_category($p_labels, 'statistics');
	$marketing_purposes = cmplz_tcf_filter_by_category($p_labels, 'marketing');
	$stats_purposes = '<div>'.implode('<br>', $stats_purposes).'</div>';
	$marketing_purposes = '<div>'.implode('<br>', $marketing_purposes).'</div>';
	$features = '<div>'.implode('<br>', $f_labels).'</div>';
	$special_features = '<div>'.implode('<br>', $sf_labels).'</div>';
	$special_purposes = '<div>'.implode('<br>', $sp_labels).'</div>';
	$html = str_replace( $marker_statistics, $stats_purposes, $html);
	$html = str_replace( $marker_marketing, $marketing_purposes, $html);

	$html = str_replace( $marker_specialfeatures, $special_features, $html);
	$html = str_replace( $marker_features, $features, $html);
	$html = str_replace( $marker_specialpurposes, $special_purposes, $html);

	$marker = '<div id="cmplz-tcf-vendor-template"';
	$add = cmplz_sprintf(__("The vendor list can be found at %s", "complianz-gdpr"),'<a href="https://cookiedatabase.org/cmp/vendorlist/vendor-list.json">cookiedatabase.org</a><br><br>');
	return str_replace($marker, $add . $marker, $html);
}


function cmplz_tcf_filter_by_category( $purposes, $category ) {
	$p['marketing'] = array(1, 2, 3, 4, 5, 6, 10);
	$p['statistics']  = array(1, 7, 8, 9);

	foreach ( $purposes as $key => $value ) {
		if ( !in_array( $key, $p[ $category ] )) unset($purposes[$key]);
	}

	return $purposes;
}

/**
 * remove other consenttypes from cookiebanner tabs when tcf active
 * @param array $tabs
 *
 * @return array
 */

function cmplz_tcf_cookiebanner($tabs){
	if (isset($tabs['optout'])) $tabs['optout'] = __("Opt-out", 'complianz-gdpr').' - '.__("TCF", "complianz-gdpr");
	if (isset($tabs['optin'])) $tabs['optin']  = __("Opt-in", 'complianz-gdpr').' - '.__("TCF", "complianz-gdpr");
	return $tabs;
}
