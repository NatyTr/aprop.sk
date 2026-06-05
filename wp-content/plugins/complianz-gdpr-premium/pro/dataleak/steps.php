<?php
defined('ABSPATH') or die("you do not have access to this page!");

add_filter('cmplz_steps', 'cmplz_filter_dataleak_steps' );

function cmplz_filter_dataleak_steps( $steps ){
	$regions_type_1 = cmplz_get_regions_by_dataleak_type(1);
	foreach ($regions_type_1 as $region) {
		$steps['dataleak-' . $region] = array(
			1 => array(
				"title" => __("General", 'complianz-gdpr'),
				'region' => array($region),
			),
			2 => array(
				"title" => __("Necessity", 'complianz-gdpr'),
				'region' => array($region),
			),
			3 => array("title" => __("Options", 'complianz-gdpr'),
				'region' => array($region),
			),
			4 => array("title" => __("Finish", 'complianz-gdpr'),
				'region' => array($region),
			),
		);
	}
	$regions_type_2 = cmplz_get_regions_by_dataleak_type(2);
	foreach ($regions_type_2 as $region) {
		$steps['dataleak-' . $region] = array(
			1 => array(
				"title" => __("General", 'complianz-gdpr'),
				'region' => array($region),
			),
			2 => array(
				"title" => __("Necessity", 'complianz-gdpr'),
				'region' => array($region),
			),
			3 => array(
				"title" => __("Options", 'complianz-gdpr'),
				'region' => array($region),
			),
			4 => array(
				"title" => __("Details", 'complianz-gdpr'),
				'region' => array($region),
			),
			5 => array(
				"title" => __("Finish", 'complianz-gdpr'),
				'region' => array($region),
			),
		);
	}
	$regions_type_3 = cmplz_get_regions_by_dataleak_type(3);
	foreach ($regions_type_3 as $region) {
		$steps['dataleak-' . $region] = array(
			1 => array(
				"title" => __("General", 'complianz-gdpr'),
				'region' => array($region),
			),
			2 => array(
				"title" => __("Necessity", 'complianz-gdpr'),
				'region' => array($region),
			),
			3 => array(
				"title" => __("Options", 'complianz-gdpr'),
				'region' => array($region),
			),
			4 => array(
				"title" => __("Details", 'complianz-gdpr'),
				'region' => array($region),
			),
			5 => array(
				"title" => __("Finish", 'complianz-gdpr'),
				'region' => array($region),
			),
		);
	}
	return $steps;
}
