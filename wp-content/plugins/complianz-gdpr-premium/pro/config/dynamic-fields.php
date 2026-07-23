<?php
defined('ABSPATH') or die("you do not have access to this page!");


/**
 * For saving purposes, types should be overridden at the earliest moment
 * @param array $fields
 * @return array
 */
function cmplz_filter_pro_field_types($fields){
	if (cmplz_get_value('use_country', false, 'settings')){
		$fields['regions']['type'] = 'multicheckbox';
	}

	/**
	 * premium option to set cookies across domains on multisite
	 */

	if (is_multisite()) {
		$fields['set_cookies_on_root'] = array(
			'source'  => 'settings',
			'step'    => 'cookie-blocker',
			'type'    => 'checkbox',
			'default' => false,
			'label'   => __( "Set cookiebanner cookies on the root domain",
				'complianz-gdpr' ),
			'help'    => __( "This is useful if you have a multisite, or several sites as subdomains on a main site",
				'complianz-gdpr' ),
			'table'   => true,
		);

		$fields['cookie_domain'] = array(
			'source'    => 'settings',
			'step'      => 'cookie-blocker',
			'type'      => 'text',
			'default'   => false,
			'label'     => __( "Domain to set the cookies on",
				'complianz-gdpr' ),
			'help'      => __( "This should be your main, root domain.",
				'complianz-gdpr' ),
			'table'     => true,
			'condition' => array( 'set_cookies_on_root' => true ),
		);
	}

	/**
	 * Add dynamic purposes
	 *
	 * */

	if (cmplz_has_region('eu') || cmplz_has_region('uk') || cmplz_has_region('za') || cmplz_has_region('br')) {
		foreach (COMPLIANZ::$config->purposes as $key => $label) {
			$fields = $fields + array(
					$key . '_data_purpose' => array(
						'master_label' => __("Purpose:", 'complianz-gdpr') . " " . $label,
						'step' => 1,
						'section' => 7,
						'source' => 'wizard',
						'type' => 'multicheckbox',
						'default' => '',
						'label' => __("What data do you collect for this purpose?", 'complianz-gdpr'),
						'required' => true,
						'callback_condition' => array(
							'privacy-statement' => 'generated',
							'purpose_personaldata' => $key
						),
						'options' => array(
							'1' => __('Name, Address and City', 'complianz-gdpr'),
							'2' => __('Marital status', 'complianz-gdpr'),
							'3' => __('Email address', 'complianz-gdpr'),
							'4' => __('Financial data', 'complianz-gdpr'),
							'5' => __('Birth date', 'complianz-gdpr'),
							'6' => __('Username, passwords and other account specific data', 'complianz-gdpr'),
							'7' => __('Sex', 'complianz-gdpr'),
							'8' => __('IP Address', 'complianz-gdpr'),
							'9' => __('Location', 'complianz-gdpr'),
							'10' => __('Medical data', 'complianz-gdpr'),
							'11' => __('Visitor behavior', 'complianz-gdpr'),
							'12' => __('Photos', 'complianz-gdpr'),
							'13' => __('Social media accounts', 'complianz-gdpr'),
							'14' => __('Criminal or legal data', 'complianz-gdpr'),
							'15' => __('Telephone number', 'complianz-gdpr'),
							'16' => __('Other:', 'complianz-gdpr'),
						),
					),

					$key . '_specify_data_purpose' => array(
						'step' => 1,
						'section' => 7,
						'source' => 'wizard',
						'type' => 'text',
						'translatable' => true,
						'default' => '',
						'required' => true,
						'label' => __("Specify the type of data you collect", 'complianz-gdpr'),
						'condition' => array($key . '_data_purpose' => 16),
						'callback_condition' => array('privacy-statement' => 'generated', 'purpose_personaldata' => $key),
					),

					$key . '_retain_data' => array(
						'step' => 1,
						'section' => 7,
						'source' => 'wizard',
						'type' => 'radio',
						'default' => '',
						'required' => true,
						'help' =>	__('How to determine the retention of specific data sets? ', 'complianz-gdpr').cmplz_read_more('https://complianz.io/data-retention', false),
						'label' => __("How long will you retain data for this specific purpose?", 'complianz-gdpr'),
						'options' => array(
							'1' => __('When the services are terminated or completed', 'complianz-gdpr'),
							'2' => __('When the services are terminated or completed, plus the duration specified below', 'complianz-gdpr'),
							'3' => __('Other period', 'complianz-gdpr'),
							'4' => __("I determine the retention period according to fixed objective criteria", 'complianz-gdpr'),
						),
						'callback_condition' => array('privacy-statement' => 'generated', 'purpose_personaldata' => $key),

					),
					$key . '_retain_wmy' => array(
						'step' => 1,
						'section' => 7,
						'source' => 'wizard',
						'type' => 'text',
						'default' => '',
						'required' => true,
						'label' => __("Retention period in weeks, months or years:", 'complianz-gdpr'),
						'condition' => array($key . '_retain_data' => '3'),
						'callback_condition' => array('privacy-statement' => 'generated', 'purpose_personaldata' => $key),

					),
					$key . '_retention_period_months' => array(
						'step' => 1,
						'section' => 7,
						'source' => 'wizard',
						'type' => 'text',
						'default' => '',
						'required' => true,
						'placeholder' => __('Retention period in months', 'complianz-gdpr'),
						'label' => __("Necessary retention period in months after completion:", 'complianz-gdpr'),
						'condition' => array($key . '_retain_data' => '2'),
						'callback_condition' => array(
							'privacy-statement' => 'generated',
							'purpose_personaldata' => $key),

					),

					$key . '_description_criteria_retention' => array(
						'step' => 1,
						'section' => 7,
						'source' => 'wizard',
						'type' => 'text',
						'default' => '',
						'required' => true,
						'label' => __("Describe these criteria in understandable terms:", 'complianz-gdpr'),
						'condition' => array($key . '_retain_data' => '4'),
						'callback_condition' => array('privacy-statement' => 'generated', 'purpose_personaldata' => $key),
					),

					$key . '_processing_data_lawfull' => array(
						'step' => 1,
						'section' => 7,
						'source' => 'wizard',
						'type' => 'radio',
						'default' => '',
						'required' => true,
						'options' => array(
							'1' => __('I obtain permission from the person concerned', 'complianz-gdpr'),
							'2' => __('It is necessary for the execution of an agreement with the person concerned', 'complianz-gdpr'),
							'3' => __('I am obligated by law', 'complianz-gdpr'),
							'4' => __('It is necessary to fulfilll a task concerning public law', 'complianz-gdpr'),
							'5' => __('It is necessary for my own legitimate interest, and that interest outweighs the interest of the person concerned', 'complianz-gdpr'),
							'6' => __('It is necessary to protect the life or physical safety of a person', 'complianz-gdpr'),
						),
						'label' => __("The processing of personal data always requires a lawful basis, which do you use?", 'complianz-gdpr'),
						'callback_condition' => array('privacy-statement' => 'generated', 'purpose_personaldata' => $key),
						'help' =>	__('Getting to know the lawful bases will be very helpful.', 'complianz-gdpr').cmplz_read_more('https://complianz.io/what-lawful-basis-for-data-processing'),
					),

					/**
					 * This is the field for the explanation about the relevant legislation for the answer: "It is necessary for credit protection".
					 */
					$key . '_credit_protection_relevant_legislation' => array(
						'step' => 1,
						'section' => 7,
						'source' => 'wizard',
						'type' => 'text',
						'default' => '',
						'required' => true,
						'placeholder' => __('Provisions of the relevant legislation', 'complianz-gdpr'),
						'label' => __("Please include the provisions of the relevant legislation:", 'complianz-gdpr'),
						'condition' => array($key . '_processing_data_lawfull' => '10'),
						'callback_condition' => array(
							'privacy-statement' => 'generated',
							'purpose_personaldata' => $key),
					),
			);
			if (cmplz_has_region('br') && cmplz_multiple_regions() == false) {
				/**
				 * For the Brazil privacy law there are some additional options. These should only be enabled when the only chosen region is Brazil.
				 */

				$fields[$key . '_processing_data_lawfull']['options']['7'] = __('It is necessary to carry out studies by a research body, ensuring, whenever possible, the anonymization of personal data', 'complianz-gdpr');
				$fields[$key . '_processing_data_lawfull']['options']['8'] = __('It is necessary for the regular exercise of rights in judicial, administrative or arbitration proceedings', 'complianz-gdpr');
				$fields[$key . '_processing_data_lawfull']['options']['9'] = __('It is necessary for the protection of health, exclusively, in a procedure performed by health professionals, health services or health authority', 'complianz-gdpr');
				$fields[$key . '_processing_data_lawfull']['options']['10'] = __('It is necessary for credit protection', 'complianz-gdpr');
			}
		}
	}

	return $fields;
}
add_filter('cmplz_fields_load_types', 'cmplz_filter_pro_field_types', 10, 1);

/**
 * Override fields here, except for types. Types should be overridden earlier, because of execution order
 *
 * If a field is on the first page after the field it is dependent on, use the plugins_loaded method as in config/dynamic_fields.php.
 * That way, the changes will get applied after the values have been saved.
 *
 */
add_filter('cmplz_fields', 'cmplz_filter_pro_fields', 10, 1);
function cmplz_filter_pro_fields($fields)
{
	if ( isset($fields['consent-mode']) ) {
		$fields['consent-mode']['disabled']=false;
		unset($fields['consent-mode']['comment']);
		$fields['consent-mode']['label'] = $fields['consent-mode']['label'].'&nbsp;('.__('Beta','complianz-gdpr').')';
	}

	$fields['placeholder_style']['disabled'] = false;
	$fields['placeholder_style']['comment'] =
		__( "Choose the style that best complements your website's design.", 'complianz-gdpr' ).'&nbsp;'.
	    __( "Custom placeholders are also possible.", 'complianz-gdpr' ).cmplz_read_more('https://complianz.io/changing-the-default-social-placeholders/');

	$fields['region_redirect']['default'] = 'yes';
	if ( cmplz_geoip_enabled() ) {
		$fields['region_redirect']['disabled'] = false;
		$fields['region_redirect']['comment'] = '';
	} else {
		$fields['region_redirect']['comment'] = __( "To use this feature, please enable GEO IP in the general settings", 'complianz-gdpr' );
	}

    if ( !cmplz_has_region('uk') || !cmplz_company_located_in_region( 'uk' ) ){
        unset($fields['dpo_or_gdpr']['options']['dpo_uk']);
    }

	if ( !cmplz_has_region('eu') || !cmplz_company_located_in_region( 'eu' ) ){
		unset($fields['dpo_or_gdpr']['options']['dpo']);
	}

	if ( !cmplz_has_region('eu') || cmplz_company_located_in_region( 'eu' ) ){
		unset($fields['dpo_or_gdpr']['options']['gdpr_rep']);
	}

	if ( !cmplz_has_region('uk') || cmplz_company_located_in_region( 'uk' ) ){
		unset($fields['dpo_or_gdpr']['options']['uk_gdpr_rep']);
	}

	$fields['records_of_consent']['disabled'] = false;
	$fields['records_of_consent']['label'] = __( "Do you want to enable Records of Consent?", 'complianz-gdpr' );
	$fields['records_of_consent']['tooltip'] = __( "This option is recommended in combination with TCF and will store consent data in your database.", 'complianz-gdpr' );
	$fields['respect_dnt']['disabled'] = false;
	$fields['respect_dnt']['label'] =  __("Do you want to respect Do Not Track & Global Privacy Control settings in browsers?", 'complianz-gdpr');

	unset($fields['records_of_consent']['comment']);
	unset($fields['financial-incentives-terms-url']['comment']);

	/**
	 * If TCF is enabled, disable some options for the cookie policy
	 */
	if ( cmplz_get_value('uses_ad_cookies_personalized') === 'tcf' ) {
		$fields['cookie-statement']['disabled'] = array(
			'custom',
			'url',
		);
	}

	/**
	 * enable TCF option, but only when the complianz cookie policy is used.
	 */
	if ( cmplz_get_value( 'cookie-statement' ) === 'generated' ) {
		unset($fields['uses_ad_cookies_personalized']['disabled']);
	}
	$fields['uses_ad_cookies_personalized']['options']['tcf'] = __("Enable TCF", "complianz-gdpr");

	//check if we have at least one TCF region selected. Otherwise, disable it
	$selected_tcf_regions = array_intersect(array_keys(cmplz_get_regions()), cmplz_tcf_regions());
	if ( count($selected_tcf_regions)==0 ) {
		$fields['uses_ad_cookies_personalized']['disabled'] = array('tcf');
		$fields['uses_ad_cookies_personalized']['comment'] = __("You have not selected a TCF region at the moment", 'complianz-gdpr');
	}

	/**
	 * if user has rsssl pro, comment should not be shown
	 */

	if (defined('rsssl_pro_version')) {
		$fields['which_personal_data_secure']['comment'] = false;
	}

    /**
     * When a user has both eu and uk regions, the settings field should always show, as it's possible the UK as categories and EU not
     */

    if (cmplz_uses_consenttype('optinstats') && cmplz_uses_consenttype('optin')) {
        unset($fields['revoke']['condition']);
    }

    /**
     * This overrides the privacy statement and disclaimer options in the free version
     * */

    $fields['privacy-statement']['disabled'] = false;
    $fields['privacy-statement']['default'] = 'generated';
    $fields['privacy-statement']['comment'] = '';
    $fields['privacy-statement']['required'] = true;
    $fields['privacy-statement']['tooltip'] = __("A Privacy Statement is required to inform your visitors about the way you deal with the privacy of website visitors. A link to this document is placed on your Cookie Banner.", 'complianz-gdpr');

	$fields['impressum']['disabled'] = false;
	$fields['impressum']['default'] = 'none';
	$fields['impressum']['comment'] = '';
	$fields['impressum']['required'] = true;
	$fields['impressum']['tooltip'] = __("An Imprint provides general contact information about the organization behind this website and might be required in your region.", 'complianz-gdpr');

	if ( cmplz_get_value('impressum', false, 'wizard') === 'generated') {
		$fields['telephone_company']['required'] = true;
	}

	$fields['disclaimer']['disabled'] = false;
    $fields['disclaimer']['default'] = 'generated';
    $fields['disclaimer']['required'] = true;
    $fields['disclaimer']['comment'] = '';
    $fields['disclaimer']['tooltip'] = __("A Disclaimer is commonly used to exclude or limit liability or to make statements about the content of the website. Having a Disclaimer is not legally required.", 'complianz-gdpr');

    /**
     * This overrides the free version of the geo ip option
     *
     * */

    $fields['use_country']['disabled'] = cmplz_get_value('records_of_consent') === 'yes' ? true : false;
    if ( cmplz_get_value('records_of_consent') === 'yes' ) {
		$fields['use_country']['comment'] = __('With records of consent enabled, GEO IP can not be turned off.', 'complianz-gdpr');
	} else {
		$fields['use_country']['comment'] = '';
	}

    $fields['use_country']['tooltip'] = __('If enabled, the cookie banner will not show for countries without a cookie law, and will adjust the warning type depending on supported privacy laws','complianz-gdpr');

    /**
     * This overrides the free version of the a/b testing option
     *
     * */

    if ( cmplz_tcf_active() ) {
	    $fields['a_b_testing']['comment'] = __('With TCF enabled, A/B testing is not possible.', 'complianz-gdpr');
    } else {
	    $fields['a_b_testing']['disabled'] = false;
	    $fields['a_b_testing']['comment'] = __('If enabled, the plugin will track which cookie banner has the best conversion rate.', 'complianz-gdpr');
    }

    /*
     * This overrides the free version of the regions option
     *
     *
     *
     */

    if (!cmplz_get_value('use_country', false, 'settings')){
        $fields['regions']['comment'] = cmplz_sprintf(__('To be able to select multiple regions, you should enable Geo IP in the %sgeneral settings%s','complianz-gdpr'),'<a href="'.admin_url('admin.php?page=cmplz-settings').'">','</a>');
    } else {
        $fields['regions']['comment'] = '';
	    $fields['regions']['label'] = __( "Which region(s) do you target with your website?","complianz-gdpr");
	    $fields['regions']['options'] = array(
		    'eu' => __( 'European Union (GDPR)',
			    'complianz-gdpr' ),
		    'uk' => __( 'United Kingdom (UK-GDPR, PECR, Data Protection Act)',
			    'complianz-gdpr' ),
		    'us' => __( 'United States', 'complianz-gdpr' ),
		    'ca' => __( 'Canada (PIPEDA)', 'complianz-gdpr' ),
		    'au' => __( 'Australia (Privacy Act 1988)', 'complianz-gdpr' ),
			'za' => __( 'South Africa (POPIA)', 'complianz-gdpr' ),
			'br' => __( 'Brazil (LGPD)', 'complianz-gdpr' ),
	    );
    }

    /*
     * This overrides the condition for the purpose in the free plugin
     * In the free version, the purpose is not necessary for EU. In the premium it is necessary if a privacy statement is needed.
     *
     *
     */
	if (cmplz_get_value('privacy-statement')==='generated'){
		unset($fields['purpose_personaldata']['callback_condition']);
	}


    /*
     * This overrides the free version of the import options
     *
     *
     *
     */
    $fields['import_settings']['comment'] = __('You can use this to import your settings from another site', 'complianz-gdpr');
    $fields['import_settings']['disabled'] = false;


    return $fields;

}
