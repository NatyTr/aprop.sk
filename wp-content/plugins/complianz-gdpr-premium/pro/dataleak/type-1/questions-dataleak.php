<?php
defined('ABSPATH') or die("you do not have access to this page!");

add_filter( 'cmplz_fields_load_types', 'cmplz_filter_dataleak_fields_1', 10, 1 );
function cmplz_filter_dataleak_fields_1( $fields ) {
	$regions = cmplz_get_regions_by_dataleak_type(1);
	foreach ($regions as $region) {

		$fields = $fields + array(
				'has-wizard-been-completed-dataleak-'. $region => array(
					'step' => 1,
					'source' => 'dataleak-'. $region,
					'type' => 'radio',
					'default' => '',
					'help' => cmplz_sprintf( __( "To learn more about dataleaks, please read this  %sarticle%s", 'complianz-gdpr' ),'<a href="https://complianz.io/what-are-dataleak-reports">', '</a>' ),
					'callback' => 'is_wizard_completed',
				),

				'type-of-dataloss-'. $region => array(
					'step' => 2,
					'source' => 'dataleak-'. $region,
					'type' => 'radio',
					'default' => '',
					'required' => true,
					'label' => __("Which situation applies to the incident.", 'complianz-gdpr'),
					'options' => array(
						'1' => __('Personal data is lost, without a recent copy or back-up.', 'complianz-gdpr'),
						'2' => __('It can not be excluded that unauthorized persons have gained access to the personal data', 'complianz-gdpr'),
						'3' => __('The above alternatives do not apply.', 'complianz-gdpr'),
					),
				),

				'reach-of-dataloss-'. $region => array(
					'step' => 2,
					'source' => 'dataleak-'. $region,
					'type' => 'radio',
					'default' => '',
					'required' => true,
					'label' => __("Which situation applies to the incident.", 'complianz-gdpr'),
					'condition' => array('type-of-dataloss-'. $region => 'NOT 3'),
					'options' => array(
						'1' => __('The data breach concerns more than 50 people.', 'complianz-gdpr'),
						'2' => __('The data breach concerns sensitive personal data.', 'complianz-gdpr'),
						'3' => __('The above alternatives do not apply.', 'complianz-gdpr'),
					),
				),

				'risk-of-data-loss-'. $region => array(
					'step' => 3,
					'source' => 'dataleak-'. $region,
					'type' => 'radio',
					'default' => '',
					'required' => true,
					'label' => __("Risk of dataloss", 'complianz-gdpr'),
					'callback_condition' => array(
						'type-of-dataloss-'. $region => 'NOT 3',
						'reach-of-dataloss-'. $region => 'NOT 3',
					),
					'options' => array(
						'1' => __('The data is encrypted in such a way that the data cannot be used in any way', 'complianz-gdpr'),
						'2' => __('Usage of the personal data is reduced or excluded directly after the breach to minimize damage.', 'complianz-gdpr'),
						'3' => __('The breached data presents a high risk for those involved.', 'complianz-gdpr'),
					),
				),
				'what-occurred-'. $region => array(
					'step' => 3,
					'source' => 'dataleak-'. $region,
					'type' => 'text',
					'translatable' => true,
					'default' => '',
					'required' => true,
					'label' => __("What has occurred exactly?", 'complianz-gdpr'),
					'callback_condition' => array(
						'type-of-dataloss-'. $region => 'NOT 3',
						'reach-of-dataloss-'. $region => 'NOT 3',
					),
					'condition' => array('risk-of-data-loss-'. $region => '3'),
				),
				'consequences-'. $region => array(
					'step' => 3,
					'source' => 'dataleak-'. $region,
					'type' => 'text',
					'translatable' => true,
					'required' => true,
					'default' => '',
					'label' => __("What are the possible consequences?", 'complianz-gdpr'),
					'callback_condition' => array(
						'type-of-dataloss-'. $region => 'NOT 3',
						'reach-of-dataloss-'. $region => 'NOT 3',
					),
					'condition' => array('risk-of-data-loss-'. $region => '3'),
				),
				'measures-'. $region => array(
					'step' => 3,
					'source' => 'dataleak-'. $region,
					'type' => 'text',
					'translatable' => true,
					'default' => '',
					'required' => true,
					'label' => __("What measures have been taken after the breach?", 'complianz-gdpr'),
					'callback_condition' => array(
						'type-of-dataloss-'. $region => 'NOT 3',
						'reach-of-dataloss-'. $region => 'NOT 3',
					),
					'condition' => array('risk-of-data-loss-'. $region => '3'),
				),
				'measures_by_person_involved-'. $region => array(
					'step' => 3,
					'source' => 'dataleak-'. $region,
					'type' => 'text',
					'translatable' => true,
					'default' => '',
					'required' => true,
					'label' => __("What measures could a person involved take to minimize damage?", 'complianz-gdpr'),
					'callback_condition' => array(
						'type-of-dataloss-'. $region => 'NOT 3',
						'reach-of-dataloss-'. $region => 'NOT 3',
					),
					'condition' => array('risk-of-data-loss-'. $region => '3'),
				),

				'conclusion-'. $region => array(
					'step' => 4,
					'source' => 'dataleak-'. $region,
					'callback' => 'dataleak_conclusion',
					'type' => 'text',
					'default' => '',
				),
			);


	}
	return $fields;
}
