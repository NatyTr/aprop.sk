<?php
defined('ABSPATH') or die("you do not have access to this page!");
/**
 * Checks if there are free translation files
 * @since 5.3.0
 */
function cmplz_translation_upgrade_check()
{
	if ( !get_transient('cmplz_checked_free_translation_files') ) {
		//remove free language files on upgrade to premium
		if (cmplz_has_free_translation_files()){
			cmplz_remove_free_translation_files();
		}
		set_transient('cmplz_checked_free_translation_files', DAY_IN_SECONDS );
	}

}
add_action( 'admin_init', 'cmplz_translation_upgrade_check' );

add_action('cmplz_notice_use_country', 'cmplz_notice_use_country');
function cmplz_notice_use_country(){

    if (COMPLIANZ::$geoip->geoip_library_error()) {

        $error = get_option('cmplz_geoip_import_error');
        $folder = "/complianz/maxmind";
        cmplz_sidebar_notice(cmplz_sprintf(__("You have enabled Geo IP, but the GEO IP database hasn't been downloaded automatically. If you continue to see this message, download the file from %sMaxMind%s and put it in the %s folder in your WordPress uploads directory", 'complianz-gdpr'),'<a href="https://cookiedatabase.org/maxmind/GeoLite2-Country.mmdb">',"</a>", $folder),'warning');
        if ($error){
            cmplz_sidebar_notice( cmplz_sprintf(__("The following error was reported: %s", 'complianz-gdpr'),$error),'warning');
        }
    }
}

add_action('cmplz_notice_dpo_or_gdpr', 'cmplz_dpo_or_gdpr');
function cmplz_dpo_or_gdpr(){
	if ( cmplz_has_region('eu') && ! cmplz_company_located_in_region( 'eu' ) ) {
		cmplz_sidebar_notice( __( "Your company is located outside the EU, so should appoint a GDPR representative in the EU.", 'complianz-gdpr' ) );
	}
	if ( cmplz_has_region('uk') ) {
		if ( !cmplz_company_located_in_region('uk') ){
			cmplz_sidebar_notice(__("Your company is located outside the United Kingdom, so you should appoint a UK-GDPR representative in the United Kingdom.", 'complianz-gdpr'));
		} else {
			cmplz_sidebar_notice(__("Your company is located in the United Kingdom, so you do not need to appoint a UK-GDPR representative in the United Kingdom.", 'complianz-gdpr'));
		}
	}

}

add_action('cmplz_notice_which_personal_data_secure', 'cmplz_notice_which_personal_data_secure');
function cmplz_notice_which_personal_data_secure(){
	if (defined('rsssl_pro_version')){
		cmplz_sidebar_notice( cmplz_sprintf(__("You're using Really Simple SSL Pro, headers that are enabled in Really Simple SSL Pro are checked already. You can manage them in the %ssettings%s", 'complianz-gdpr'),'<a href="'.admin_url('options-general.php?page=rlrsssl_really_simple_ssl&tab=security_headers').'">', '</a>'));
	}
}

add_action('cmplz_notice_share_data_other', 'cmplz_notice_share_data_other');
function cmplz_notice_share_data_other(){
	if ( COMPLIANZ::$cookie_admin->site_shares_data()
	) {
		cmplz_sidebar_notice( __( "Complianz detected settings that suggest your site shares data, which means the answer should probably be Yes, or Limited", 'complianz-gdpr' ) );
	}

	if ( cmplz_get_value('privacy-statement')==='generated' && ( cmplz_has_region('br') || cmplz_has_region('za') ) ){
		cmplz_sidebar_notice( __("Please note: in South Africa and Brazil, Operator will be used instead of Processor.", "complianz-gdpr") );
	}
}

add_filter('cmplz_default_value', 'cmplz_pro_set_default', 20, 2);
function cmplz_pro_set_default($value, $fieldname)
{
    if ($fieldname == 'financial-incentives-terms-url') {
        if ( defined( 'cmplz_tc_version' )) {
        	$page_id = COMPLIANZ_TC::$document->get_shortcode_page_id();
        	if ($page_id) {
		        return get_permalink($page_id);
	        }
        }
    }

	if ( $fieldname == 'dpo_or_gdpr' ) {
		if (cmplz_has_region('eu') &&  ! cmplz_company_located_in_region( 'eu' ) ) {
			return 'gdpr_rep';
		}
	}

    if ($fieldname == 'dpo_or_gdpr') {
        if (cmplz_has_region('uk') && !cmplz_company_located_in_region('uk')) {
            return 'uk_gdpr_rep';
        }
    }

	if ( $fieldname === 'is_webshop' ){
		if (class_exists( 'WooCommerce' ) || class_exists( 'Easy_Digital_Downloads' ) ) {
			$value = 'yes';
		}
	}


	if ($fieldname === 'which_personal_data_secure'){
	    if (!is_array($value)) $value = array();

	    if (is_ssl()){
		    $value['3'] = true;
	    }

    	if (defined('rsssl_pro_version')){
		    if ( get_option('rsssl_hsts') ) $value['8']=1;
		    if ( get_option('rsssl_x_content_type_options') ) $value['9']=1;
		    if ( get_option('rsssl_x_xss_protection') ) $value['10']=1;
		    if ( get_option('rsssl_x_frame_options') ) $value['11']=1;
		    if ( get_option('rsssl_expect_ct') ) $value['12']=1;
		    if ( get_option('rsssl_no_referrer_when_downgrade') ) $value['13']=1;
		    if ( get_option('rsssl_content_security_policy') === 'enforce' ) $value['14']=1;
	    }
    }

    return $value;
}
