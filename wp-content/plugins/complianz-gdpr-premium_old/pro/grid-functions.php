<?php

add_filter('cmplz_controls_documents', 'cmplz_add_documents_dropdown');
function cmplz_add_documents_dropdown( $html ){
	//we want to look at all regions, so we can show which pages are obsolete
	$regions        = COMPLIANZ::$config->regions;
	$regions['all'] = array(
		'label' => __("General", "complianz-gdpr")
	);
	foreach ($regions as $key => $region ) {
		$regions[$key] = $region['label'];
	}
	$default = COMPLIANZ::$company->get_default_region();
	$html = COMPLIANZ::$admin->grid_dropdown('region', $regions, $default);
	return $html;
}



