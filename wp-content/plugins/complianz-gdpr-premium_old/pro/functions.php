<?php
defined('ABSPATH') or die("you do not have access to this page!");

function cmplz_site_uses_comments(){
    return COMPLIANZ::$comments->site_uses_comments();
}


function cmplz_dataleak_page_us(){
    COMPLIANZ::$dataleak->dataleak_page('us');
}

function cmplz_dataleak_page_eu(){
    COMPLIANZ::$dataleak->dataleak_page('eu');
}
function cmplz_dataleak_page_uk(){
    COMPLIANZ::$dataleak->dataleak_page('uk');
}
function cmplz_dataleak_page_ca(){
	COMPLIANZ::$dataleak->dataleak_page('ca');
}
function cmplz_dataleak_page_au(){
  COMPLIANZ::$dataleak->dataleak_page('au');
}
function cmplz_dataleak_page_za(){
	COMPLIANZ::$dataleak->dataleak_page('za');
}
function cmplz_dataleak_page_br(){
	COMPLIANZ::$dataleak->dataleak_page('br');
}
function cmplz_processing_page_us(){
    COMPLIANZ::$processing->processing_agreement_page('us');
}
function cmplz_processing_page_uk(){
    COMPLIANZ::$processing->processing_agreement_page('uk');
}
function cmplz_processing_page_eu(){
    COMPLIANZ::$processing->processing_agreement_page('eu');
}
function cmplz_processing_page_ca(){
	COMPLIANZ::$processing->processing_agreement_page('ca');
}
function cmplz_processing_page_au(){
  COMPLIANZ::$processing->processing_agreement_page('au');
}
function cmplz_processing_page_za(){
	COMPLIANZ::$processing->processing_agreement_page('za');
}
function cmplz_processing_page_br(){
	COMPLIANZ::$processing->processing_agreement_page('br');
}

function cmplz_dataleak_has_to_be_reported()
{
    return COMPLIANZ::$dataleak->dataleak_has_to_be_reported();
}
function cmplz_dataleak_has_to_be_reported_to_involved()
{
    return COMPLIANZ::$dataleak->dataleak_has_to_be_reported_to_involved();
}
function cmplz_get_regions_by_dataleak_type($dataleak_type){
	$regions       = COMPLIANZ::$config->regions;
	$type_regions = array();
	foreach ( $regions as $region_code => $region_data ) {
		if ($dataleak_type == $region_data['dataleak_type'] ) {
			$type_regions[] = $region_code;
		}
	}

	return $type_regions;
}

function cmplz_socialsecurity_or_driverslicense()
{
    $type = cmplz_get_value('name-what-us');

    if (isset($type['drivers-license']) && $type['drivers-license'] ==1) {
        return true;
    }
    if (isset($type['social-security-number']) && $type['social-security-number'] ==1) {
        return true;
    }

    return false;
}

/**
 * If free is active, we should deactivate it.
 *
 * */

add_action('admin_init', 'cmplz_check_for_free_version');
if ( !function_exists('cmplz_check_for_free_version') ) {
    function cmplz_check_for_free_version()
    {
		if ( !current_user_can('manage_options') ) {
			return;
		}

		if ( defined('cmplz_plugin_free') ) {
			deactivate_plugins(cmplz_plugin_free);
			add_action('admin_notices', 'cmplz_notice_free_active');
		//older method:
		} else if (defined('cmplz_free')) {
             $free = 'complianz-gdpr/complianz-gpdr.php';
             deactivate_plugins($free);
			 add_action('admin_notices', 'cmplz_notice_free_active');
         }
    }
}

if (!function_exists('cmplz_notice_free_active')) {
    function cmplz_notice_free_active()
    { ?>
       <div id="message" class="notice notice-success is-dismissible cmplz-dismiss-notice really-simple-plugins">
           <p>
               <?php echo __("You have installed Complianz Privacy Suite. We have deactivated and removed the free plugin.", 'complianz-gdpr'); ?>
           </p>
       </div>
       <?php
   }

}

if (!function_exists('cmplz_free_plugin_not_deleted')){
	function cmplz_free_plugin_not_deleted(){
		if ( file_exists(trailingslashit( WP_PLUGIN_DIR).'complianz-gdpr/complianz-gpdr.php' ) ){
			return true;
		} else {
			return false;
		}
	}
}
