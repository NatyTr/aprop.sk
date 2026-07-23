<?php
defined('ABSPATH') or die("you do not have access to this page!");
add_filter( 'cmplz_fields_load_types', 'cmplz_filter_dataleak_fields_3', 10, 1 );
function cmplz_filter_dataleak_fields_3( $fields ) {

	$regions = cmplz_get_regions_by_dataleak_type(3);
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
						'1' => __('Personal data has been lost, and there is no up to date back-up', 'complianz-gdpr'),
						'2' => __('It can not be excluded that unauthorized persons have gained access to personal data', 'complianz-gdpr'),
						'3' => __('The above alternatives do not apply.', 'complianz-gdpr'),
					),
				),
				'risk-of-data-loss-'. $region => array(
					'step' => 3,
					'source' => 'dataleak-'. $region,
					'type' => 'radio',
					'required' => true,
					'options' => array(
						'1' => __("There is a real risk of significant harm, due to the probability that the personal information has been, is being or will be misused.", 'complianz-gdpr'),
						'2' => __("The databreach applies to (some) personal data that may be sensitive.", 'complianz-gdpr'),
						'3' => __("The data has been encrypted in such a way that it is not possible to abuse the data", 'complianz-gdpr'),
						'4' => __("The possible consequences have been minimized immediately, which effectively excludes the possibility of abuse by malicious parties", 'complianz-gdpr'),
					),
					'default' => '',
					'label' => __("What information was involved?", 'complianz-gdpr'),
					'callback_condition' => array(
						'type-of-dataloss-'. $region => 'NOT 3',
					),
				),
			);
		if ($region == 'ca'){
		$fields = $fields + array(
				'can-reduce-risk-'. $region => array(
					'step' => 4,
					'source' => 'dataleak-'. $region,
					'type' => 'radio',
					'required' => true,
					'options' => COMPLIANZ::$config->yes_no,
					'default' => '',
					'label' => __("Do you think any other organization, a government institution or a part of a government institution may be able to reduce the risk of harm from the breach or to mitigate that harm?", 'complianz-gdpr'),
					'callback_condition' => array(
						'type-of-dataloss-'. $region => 'NOT 3',
						'risk-of-data-loss-'. $region => array('NOT 3', 'NOT 4'),
					),

				),
			);
		}
		$fields = $fields + array(
				'what-occurred-'. $region => array(
					'step' => 4,
					'source' => 'dataleak-'. $region,
					'type' => 'text',
					'translatable' => false,
					'default' => '',
					'required' => true,
					'label' => __("What has occurred exactly?", 'complianz-gdpr'),
					'callback_condition' => array(
						'type-of-dataloss-'. $region => 'NOT 3',
						'risk-of-data-loss-'. $region => array('NOT 3', 'NOT 4'),
					),
				),
				'consequences-'. $region => array(
					'step' => 4,
					'source' => 'dataleak-'. $region,
					'type' => 'text',
					'translatable' => false,
					'required' => true,
					'default' => '',
					'label' => __("What are the possible consequences?", 'complianz-gdpr'),
					'callback_condition' => array(
						'type-of-dataloss-'. $region => 'NOT 3',
						'risk-of-data-loss-'. $region => array('NOT 3', 'NOT 4'),

					),
				),
				'measures-'. $region => array(
					'step' => 4,
					'source' => 'dataleak-'. $region,
					'type' => 'text',
					'translatable' => false,
					'default' => '',
					'required' => true,
					'label' => __("What measures have been taken after the breach?", 'complianz-gdpr'),
					'callback_condition' => array(
						'type-of-dataloss-'. $region => 'NOT 3',
						'risk-of-data-loss-'. $region => array('NOT 3', 'NOT 4'),

					),
				),

				'measures_by_person_involved-'. $region => array(
					'step' => 4,
					'source' => 'dataleak-'. $region,
					'type' => 'text',
					'translatable' => false,
					'default' => '',
					'required' => true,
					'label' => __("What measures could a person involved take to minimize damage?", 'complianz-gdpr'),
					'callback_condition' => array(
						'type-of-dataloss-'. $region => 'NOT 3',
						'risk-of-data-loss-'. $region => array('NOT 3', 'NOT 4'),

					),
				),

				'date-of-breach-'. $region => array(
					'step' => 4,
					'source' => 'dataleak-'. $region,
					'type' => 'text',
					'translatable' => false,
					'default' => '',
					'required' => true,
					'label' => __("Day on which, or period during which the breach occurred, or if neither is known, the approximate period.", 'complianz-gdpr'),
					'callback_condition' => array(
						'type-of-dataloss-'. $region => 'NOT 3',
						'risk-of-data-loss-'. $region => array('NOT 3', 'NOT 4'),

					),
				),


				'phone-url-inquiries-'. $region => array(
					'step' => 4,
					'source' => 'dataleak-'. $region,
					'type' => 'text',
					'translatable' => false,
					'default' => '',
					'required' => true,
					'label' => __("Through which email address can customers make inquiries about your system?", 'complianz-gdpr'),
					'callback_condition' => array(
						'type-of-dataloss-'. $region => 'NOT 3',
						'risk-of-data-loss-'. $region => array('NOT 3', 'NOT 4'),

					),
				),

				'conclusion-'. $region => array(
					'step' => 5,
					'source' => 'dataleak-'. $region,
					'callback' => 'dataleak_conclusion',
					'type' => 'text',
					'default' => '',
				),
			);
	}
	return $fields;
}
