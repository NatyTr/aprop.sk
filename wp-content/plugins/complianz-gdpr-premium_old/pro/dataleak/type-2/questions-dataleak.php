<?php
defined('ABSPATH') or die("you do not have access to this page!");
add_filter( 'cmplz_fields_load_types', 'cmplz_filter_dataleak_fields_2', 10, 1 );
function cmplz_filter_dataleak_fields_2( $fields ) {

	$regions = cmplz_get_regions_by_dataleak_type(2);
	foreach ($regions as $region) {

		$fields = $fields + array(
				'has-wizard-been-completed-dataleak-'. $region => array(
					'step' => 1,
					'source' => 'dataleak-'. $region,
					'type' => 'radio',
					'default' => ' ',
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
						'1' => __('Encrypted personal data is lost, and it cannot be excluded that unauthorized persons have access to the encryption key or password.', 'complianz-gdpr'),
						'2' => __('It can not be excluded that unauthorized persons have gained access to unencrypted personal data', 'complianz-gdpr'),
						'3' => __('The above alternatives do not apply.', 'complianz-gdpr'),
					),
				),
				'what-information-was-involved-'. $region => array(
					'step' => 3,
					'source' => 'dataleak-'. $region,
					'type' => 'radio',
					'required' => true,
					'options' => array(
						'name' => __("An individual’s first name or first initial and last name in combination with any one or more of the data elements (as shown in the next question after selecting this option), when either the name or the data elements are not encrypted", 'complianz-gdpr'),
						'username-email' => __("A user name or email address, in combination with a password or security question and answer that would permit access to an online account.", 'complianz-gdpr'),
						'none' => __("None of the above", 'complianz-gdpr'),
					),
					'default' => '',
					'label' => __("What information was involved?", 'complianz-gdpr'),
					'callback_condition' => array(
						'type-of-dataloss-'. $region => 'NOT 3',
					),
				),

				'name-what-'. $region => array(
					'step' => 3,
					'source' => 'dataleak-'. $region,
					'type' => 'multicheckbox',
					'required' => true,
					'options' => array(
						'social-security-number' => __("Social security number.", 'complianz-gdpr'),
						'drivers-license' => __("Driver’s license number or identification card number.", 'complianz-gdpr'),
						'account-number' => __("Account number or credit or debit card number, in combination with any required security code, access code, or password that would permit access to an individual’s financial account.", 'complianz-gdpr'),
						'medical-info' => __("Medical information.", 'complianz-gdpr'),
						'health-insurance' => __("Health insurance information.", 'complianz-gdpr'),
						'data-collected' => __("Information or data collected through the use or operation of an automated license plate recognition system", 'complianz-gdpr'),
					),
					'callback_condition' => array(
						'type-of-dataloss-'. $region => 'NOT 3',
					),
					'condition' => array('what-information-was-involved-'. $region => 'name'),
					'default' => '',
					'label' => __("Data elements involved in the security breach:", 'complianz-gdpr'),
				),

				'toll-free-phone' => array(
					'step' => 3,
					'source' => 'dataleak-'. $region,
					'type' => 'text',
					'translatable' => false,
					'default' => '',
					'required' => true,
					'label' => __("Please enter the toll-free telephone number and addresses of the major credit reporting agencies:", 'complianz-gdpr'),
					'callback_condition' => array(
						'type-of-dataloss-'. $region => 'NOT 3',
						'reach-of-dataloss-'. $region => 'NOT 3',
					),
					'condition' => array(
						'name-what-'. $region => 'social-security-number OR drivers-license',
					),
				),

				'reach-of-dataloss-large-'. $region => array(
					'step' => 3,
					'source' => 'dataleak-'. $region,
					'type' => 'radio',
					'default' => '',
					'required' => true,
					'label' => __("Does the security breach affect a large number (500 or more) of people?", 'complianz-gdpr'),
					'callback_condition' => array(
						'type-of-dataloss-'. $region => 'NOT 3',
						'what-information-was-involved-'. $region => 'NOT none',
					),
					'condition' => array(
						'what-information-was-involved-'. $region => 'name OR username-email',
					),
					'options' => COMPLIANZ::$config->yes_no,
				),

				'california-visitors' => array(
					'step' => 3,
					'source' => 'dataleak-'. $region,
					'type' => 'radio',
					'default' => '',
					'required' => true,
					'label' => __("Does the databreach affect California residents?", 'complianz-gdpr'),
					'callback_condition' => array(
						'type-of-dataloss-'. $region => 'NOT 3',
						'what-information-was-involved-'. $region => 'NOT none',
					),
					'condition' => array('reach-of-dataloss-large-'. $region => 'yes'),
					'options' => COMPLIANZ::$config->yes_no,
				),

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
						'reach-of-dataloss-'. $region => 'NOT 3',
						'what-information-was-involved-'. $region => 'NOT none',

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
						'reach-of-dataloss-'. $region => 'NOT 3',
						'what-information-was-involved-'. $region => 'NOT none',
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
						'reach-of-dataloss-'. $region => 'NOT 3',
						'what-information-was-involved-'. $region => 'NOT none',

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
						'reach-of-dataloss-'. $region => 'NOT 3',
						'what-information-was-involved-'. $region => 'NOT none',

					),
				),

				'date-of-breach-'. $region => array(
					'step' => 4,
					'source' => 'dataleak-'. $region,
					'type' => 'text',
					'translatable' => false,
					'default' => '',
					'required' => true,
					'label' => __("What is the date, the approximate date, or the date range within which the security breach has occurred?", 'complianz-gdpr'),
					'callback_condition' => array(
						'type-of-dataloss-'. $region => 'NOT 3',
						'reach-of-dataloss-'. $region => 'NOT 3',
						'what-information-was-involved-'. $region => 'NOT none',
					),
				),

				'investigation' => array(
					'step' => 4,
					'source' => 'dataleak-'. $region,
					'type' => 'radio',
					'translatable' => false,
					'default' => '',
					'required' => true,
					'label' => __("Was the notification delayed as a result of a law enforcement investigation?", 'complianz-gdpr'),
					'callback_condition' => array(
						'type-of-dataloss-'. $region => 'NOT 3',
						'reach-of-dataloss-'. $region => 'NOT 3',
						'what-information-was-involved-'. $region => 'NOT none',
					),
					'options' => COMPLIANZ::$config->yes_no,
				),

				'phone-url-inquiries-'. $region => array(
					'step' => 4,
					'source' => 'dataleak-'. $region,
					'type' => 'text',
					'translatable' => false,
					'default' => '',
					'required' => true,
					'label' => __("Through which phone number, or which URL, can customers make inquiries about this security breach?", 'complianz-gdpr'),
					'callback_condition' => array(
						'type-of-dataloss-'. $region => 'NOT 3',
						'reach-of-dataloss-'. $region => 'NOT 3',
						'what-information-was-involved-'. $region => 'NOT none',
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

