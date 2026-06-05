<?php
defined('ABSPATH') or die("you do not have access to this page!");

/*
 * condition: if a question should be dynamically shown or hidden, depending on another answer. Use NOT answer to hide if not answer.
 * callback_condition: if should be shown or hidden based on an answer in another screen.
 * callback roept action cmplz_$page_$callback aan
 * required: verplicht veld.
 * help: helptext die achter het veld getoond wordt.

                "fieldname" => '',
                "type" => 'text',
                "required" => false,
                'default' => '',
                'label' => '',
                'table' => false,
                'callback_condition' => false,
                'condition' => false,
                'callback' => false,
                'placeholder' => '',
                'optional' => false,

* */

// MY COMPANY SECTION
$this->fields = $this->fields + array(
        'ca_name_accountable_person' => array(
	        'step' => STEP_COMPANY,
	        'section' => 3,
	        'source' => 'wizard',
	        'type' => 'text',
	        'required' => true,
	        'default' => '',
	        'callback_condition' => array(
		        'privacy-statement' => 'generated',
		        'regions' => array('ca', 'au', 'za'),
	        ),
	        'label' => __("Person who is accountable for the organization’s policies and practices and to whom complaints or inquiries can be forwarded.", 'complianz-gdpr'),
        ),

        'ca_address_accountable_person' => array(
	        'step' => STEP_COMPANY,
	        'section' => 3,
	        'source' => 'wizard',
	        'type' => 'textarea',
	        'required' => true,
	        'default' => '',
	        'callback_condition' => array(
		        'privacy-statement' => 'generated',
		        'regions' => array('ca', 'au', 'za'),
	        ),
	        'label' => __("What is the address where complaints or inquiries can be forwarded?", 'complianz-gdpr'),
        ),
        'free_phonenr' => array(
	        'step' => STEP_COMPANY,
	        'section' => 3,
	        'source' => 'wizard',
	        'type' => 'phone',
	        'default' => '',
	        'required' => false,
	        'label' => __("Enter a toll free phone number for the submission of information requests", 'complianz-gdpr'),
	        'document_label' => 'Toll free phone number: ',
	        'callback_condition' => array(
		        'regions' => array('us'),
	        ),
	        'help' => __('For US based companies, you can provide a toll free phone number for inquiries.','complianz-gdpr').cmplz_read_more('https://complianz.io/toll-free-number/'),
        ),

		// IMPRINT
        'legal_form_imprint' => array(
	        'step'               => STEP_COMPANY,
	        'section'            => 4,
	        'source'             => 'wizard',
	        'type'               => 'text',
	        'required'           => false,
	        'tooltip'            => __( "Leave empty if not applicable", 'complianz-gdpr' ),
	        'placeholder'        => 'e.g. GMBH, Limited, SRL etc',
	        'label'              => __( "What is the legal form of your organization?", 'complianz-gdpr' ),
	        'callback_condition' => array(
		        'impressum' => 'generated',
	        ),
        ),

        'email_company_imprint' => array(
	        'step'               => STEP_COMPANY,
	        'section'            => 4,
	        'source'             => 'wizard',
	        'type'               => 'email',
	        'required'           => false,
	        'placeholder'        => 'hello@company.com',
	        'default'            => '',
	        'tooltip'            => __( "Your email address will be obfuscated on the front-end to prevent spidering.", 'complianz-gdpr' ),
	        'label'              => __( "What is the email address your visitors can use to contact you?", 'complianz-gdpr' ),
	        'callback_condition' => array(
		        'impressum' => 'generated',
	        ),
        ),

        'vat_company' => array(
	        'step'               => STEP_COMPANY,
	        'section'            => 4,
	        'source'             => 'wizard',
	        'type'               => 'text',
	        'placeholder'        => __( "Leave empty if not applicable", 'complianz-gdpr' ),
	        'tooltip'            => __( "If you do not have a VAT ID, you can leave this question unanswered", 'complianz-gdpr' ),
	        'label'              => __( "VAT ID of your company", 'complianz-gdpr' ),
	        'required'           => false,
	        'callback_condition' => array(
		        'impressum' => 'generated',
	        ),
        ),

        'register' => array(
	        'step'               => STEP_COMPANY,
	        'section'            => 4,
	        'source'             => 'wizard',
	        'type'               => 'text',
	        'label'              => __( "In which register of companies, associations, partnerships or cooperatives is your company registered?", 'complianz-gdpr' ),
	        'tooltip'            => __( "Generally the Chamber of Commerce or a local Court register, but other registers may apply. Leave blank if this does not apply to you.", 'complianz-gdpr' ),
	        'required'           => false,
	        'callback_condition' => array(
		        'impressum' => 'generated',
	        ),
        ),

        'business_id' => array(
	        'step'               => STEP_COMPANY,
	        'section'            => 4,
	        'source'             => 'wizard',
	        'type'               => 'text',
	        'placeholder'        => __( "Leave empty if not applicable", 'complianz-gdpr' ),
	        'label'              => __( "What is the registration number corresponding with the answer to the above question?", 'complianz-gdpr' ),
	        'required'           => false,
	        'callback_condition' => array(
		        'impressum' => 'generated',
	        ),
        ),

        'representative' => array(
	        'step'               => STEP_COMPANY,
	        'section'            => 4,
	        'source'             => 'wizard',
	        'type'               => 'text',
	        'label'              => __( "Name one or more person(s) who can legally represent the company or legal entity.", 'complianz-gdpr' ),
	        'tooltip'            => __( "This is generally an owner or director of the legal entity.", 'complianz-gdpr' ),
	        'required'           => false,
	        'callback_condition' => array(
		        'impressum' => 'generated',
	        ),
        ),

        'inspecting_authority' => array(
	        'step'               => STEP_COMPANY,
	        'section'            => 4,
	        'source'             => 'wizard',
	        'type'               => 'text',
	        'label'              => __( "If the service or product displayed on this website requires some sort of official approval, state the (inspecting) authority.", 'complianz-gdpr' ),
	        'tooltip'            => __( "For example, a website from a financial advisor might need permission from an inspecting authority.", 'complianz-gdpr' ),
	        'required'           => false,
	        'callback_condition' => array(
		        'impressum' => 'generated',
	        ),
        ),

        'professional_association' => array(
	        'step'               => STEP_COMPANY,
	        'section'            => 4,
	        'source'             => 'wizard',
	        'type'               => 'text',
	        'label'              => __( "Does your website display services or products that require registration with a professional association? If so, name the professional association.",
		        'complianz-gdpr' ),
	        'tooltip'            => __( "Registration heavily depends on specific national laws. In most countries this obligation applies to Doctors, Pharmacists, Architects, Consulting engineers, Notaries, Patent attorneys, Psychotherapists, Lawyers, Tax consultants, Veterinary surgeons, Auditors or Dentists.",
		        'complianz-gdpr' ),
	        'required'           => false,
	        'callback_condition' => array(
		        'impressum' => 'generated',
	        ),
        ),

        'legal_job_imprint' => array(
	        'step'               => STEP_COMPANY,
	        'section'            => 4,
	        'source'             => 'wizard',
	        'type'               => 'radio',
	        'options'            => $this->yes_no,
	        'default'            => 'no',
	        'label'              => __( "Does your profession or the activities displayed on the website require a certain diploma?", 'complianz-gdpr' ),
	        'tooltip'            => __( "Required for an activity under a professional title, in so far as the use of such a title is reserved to the holders of a diploma governed by laws, regulations or administrative provisions.",
		        'complianz-gdpr' ),
	        'required'           => false,
	        'callback_condition' => array(
		        'impressum' => 'generated',
	        ),
        ),

        'legal_job_title' => array(
	        'step'               => STEP_COMPANY,
	        'section'            => 4,
	        'source'             => 'wizard',
	        'type'               => 'text',
	        'label'              => __( "Name the legal job title", 'complianz-gdpr' ),
	        'placeholder'        => __( "Medical Doctor", 'complianz-gdpr' ),
	        'required'           => false,
	        'condition'          => array(
		        'legal_job_imprint' => 'yes'
	        ),
	        'callback_condition' => array(
		        'impressum' => 'generated',
	        ),
        ),

        'legal_job_country_imprint' => array(
	        'step'               => STEP_COMPANY,
	        'section'            => 4,
	        'source'             => 'wizard',
	        'options'            => $this->countries,
	        'type'               => 'select',
	        'label'              => __( "Name the country where the diploma was awarded", 'complianz-gdpr' ),
	        'required'           => false,
	        'condition'          => array(
		        'legal_job_imprint' => 'yes'
	        ),
	        'callback_condition' => array(
		        'impressum' => 'generated',
	        ),
        ),

        'professional_regulations' => array(
	        'step'               => STEP_COMPANY,
	        'section'            => 4,
	        'source'             => 'wizard',
	        'type'               => 'text',
	        'label'              => __( "Professional Regulations.", 'complianz-gdpr' ),
	        'tooltip'            => __( "If applicable, mention the professional regulations that may apply to your activities, and the URL where to find them.", 'complianz-gdpr' ),
	        'required'           => false,
	        'callback_condition' => array(
		        'impressum' => 'generated',
	        ),
        ),

        'professional_regulations_url' => array(
	        'step'               => STEP_COMPANY,
	        'section'            => 4,
	        'source'             => 'wizard',
	        'type'               => 'text',
	        'placeholder'        => __( "Leave empty if the above is not applicable", 'complianz-gdpr' ),
	        'label'              => __( "The URL to the regulations so website visitors know how to access them.", 'complianz-gdpr' ),
	        'required'           => false,
	        'callback_condition' => array(
		        'impressum' => 'generated',
	        ),
        ),

        'is_webshop' => array(
	        'step'               => STEP_COMPANY,
	        'section'            => 4,
	        'source'             => 'wizard',
	        'type'               => 'radio',
	        'options'            => $this->yes_no,
	        'label'              => __( "Do you sell products or services through your website?", 'complianz-gdpr' ),
	        'tooltip'            => __( "If this is a webshop, the Imprint should include a paragraph about dispute settlement.", 'complianz-gdpr' ),
	        'callback_condition' => array(
		        'impressum' => 'generated',
	        ),
        ),

        'has_webshop_obligation' => array(
	        'step'               => STEP_COMPANY,
	        'section'            => 4,
	        'source'             => 'wizard',
	        'type'               => 'radio',
	        'options'            => $this->yes_no,
	        'label'              => __( "Are you obliged or prepared to use Alternative Dispute Resolution?", 'complianz-gdpr' ),
	        'tooltip'            => __( "Alternate Dispute Resolution means settling disputes without lawsuit.", 'complianz-gdpr' ),
	        'required'           => true,
	        'callback_condition' => array(
		        'impressum' => 'generated',
	        ),
	        'condition'          => array(
		        'is_webshop' => 'yes'
	        ),
        ),

		// If Germany, Below Questions
        'german_imprint_appendix' => array(
	        'step'               => STEP_COMPANY,
	        'section'            => 4,
	        'source'             => 'wizard',
	        'type'               => 'radio',
	        'default'            => 'yes',
	        'options'            => $this->yes_no,
	        'label'              => __( "Do you target a German audience?", 'complianz-gdpr' ),
	        'tooltip'            => __( "This will enable questions specific to an Impressum", 'complianz-gdpr' ),
	        // 'required'           => true,
	        'callback_condition' => array(
		        'impressum' => 'generated',
                'eu_consent_regions' => 'yes',
	        ),
        ),

		// Verantwortlich
        'offers_editorial_content_imprint' => array(
	        'step'               => STEP_COMPANY,
	        'section'            => 4,
	        'source'             => 'wizard',
	        'type'               => 'radio',
	        'required'           => false,
	        'default'            => 'no',
	        'options'            => $this->yes_no,
	        'label'              => __( "Do you offer content for journalistic and editorial purposes?", 'complianz-gdpr' ),
	        'tooltip'            => __( "For example websites that run a blog, publish news articles or moderate an online community.", 'complianz-gdpr' ),
	        'condition'          => array(
		        'german_imprint_appendix' => 'yes',
	        ),
	        'callback_condition' => array(
		        'impressum' => 'generated',
	        ),
        ),

        'editorial_responsible_name_imprint' => array(
	        'step'               => STEP_COMPANY,
	        'section'            => 4,
	        'source'             => 'wizard',
	        'type'               => 'text',
	        'label'              => __( "State the full name of the person responsible for the content on this website.", 'complianz-gdpr' ),
	        'tooltip'            => __( "The person should be stated with first and last name.", 'complianz-gdpr' ),
	        'placeholder'        => "Max Mustermann",
	        'required'           => false,
	        'condition'          => array(
		        'offers_editorial_content_imprint' => 'yes',
		        'german_imprint_appendix'          => 'yes',
	        ),
	        'callback_condition' => array(
		        'impressum' => 'generated',
	        ),
        ),

        'editorial_responsible_residence_imprint' => array(
	        'step'               => STEP_COMPANY,
	        'section'            => 4,
	        'source'             => 'wizard',
	        'type'               => 'text',
	        'label'              => __( "What is the residence of the person responsible for the content on this website?", 'complianz-gdpr' ),
	        'placeholder'        => "Berlin",
	        'required'           => false,
	        'condition'          => array(
		        'offers_editorial_content_imprint' => 'yes',
		        'german_imprint_appendix'          => 'yes',
	        ),
	        'callback_condition' => array(
		        'impressum' => 'generated',
	        ),
        ),

		// End Verantwortlich
        'capital_stock' => array(
	        'step'               => STEP_COMPANY,
	        'section'            => 4,
	        'source'             => 'wizard',
	        'type'               => 'text',
	        'placeholder'        => '€ 100',
	        'label'              => __( "Capital Stock", 'complianz-gdpr' ),
	        'required'           => false,
	        'condition'          => array(
		        'german_imprint_appendix' => 'yes',
	        ),
	        'callback_condition' => array(
		        'impressum' => 'generated',
	        ),
        ),

        'liability_insurance_imprint' => array(
	        'step'               => STEP_COMPANY,
	        'section'            => 4,
	        'source'             => 'wizard',
	        'type'               => 'textarea',
	        'label'              => __( "What is the name, address, and geographical scope of your professional liability insurance?", 'complianz-gdpr' ),
	        'required'           => false,
	        'condition'          => array(
		        'german_imprint_appendix' => 'yes',
	        ),
	        'callback_condition' => array(
		        'impressum' => 'generated',
	        ),
        ),

        'open_field_imprint' => array(
	        'step'               => STEP_COMPANY,
	        'section'            => 4,
	        'source'             => 'wizard',
	        'type'               => 'textarea',
	        'label'              => __( "For additional information, please use this field.", 'complianz-gdpr' ),
	        'required'           => false,
	        'callback_condition' => array(
		        'impressum' => 'generated',
	        ),
        ),
		// END IMPRINT
        'dpo_or_gdpr' => array(
	        'step' => STEP_COMPANY,
	        'section' => 5,
	        'source' => 'wizard',
	        'type' => 'multicheckbox',
	        'default' => '',
	        'label' => __("Select all that applies.", 'complianz-gdpr'),
	        'options' => array(
		        'dpo' => __('We have registered a DPO with the Data Protection Authority in the EU.', 'complianz-gdpr'),
		        'dpo_uk' => __('We have registered a DPO with the Data Protection Authority in the UK.', 'complianz-gdpr'),
		        'gdpr_rep' => __('We have appointed a GDPR representative within the EU.', 'complianz-gdpr'),
		        'uk_gdpr_rep' => __('We have a UK-GDPR representative within the United Kingdom', 'complianz-gdpr'),
            ),
            'callback_condition' => array(
				'privacy-statement' => 'generated',
				'regions' => array('eu', 'uk', 'ca', 'au', 'za'),
			),
            'required' => false,
        ),

        'name_dpo' => array(
	        'master_label'  => __( "Data Protection Officer", 'complianz-gdpr' ),
	        'step' => STEP_COMPANY,
            'section' => 5,
            'source' => 'wizard',
            'type' => 'text',
            'required' => true,
            'default' => '',
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => 'eu',
            ),
            'label' => __("Name", 'complianz-gdpr'),
            'condition' => array('dpo_or_gdpr' => 'dpo'),
        ),

        'email_dpo' => array(
            'step' => STEP_COMPANY,
            'section' => 5,
            'source' => 'wizard',
            'type' => 'email',
            'default' => '',
            'required' => true,
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => 'eu',
            ),
            'label' => __("Email", 'complianz-gdpr'),
            'condition' => array('dpo_or_gdpr' => 'dpo'),
        ),
        'phone_dpo' => array(
            'step' => STEP_COMPANY,
            'section' => 5,
            'source' => 'wizard',
            'type' => 'phone',
            'default' => '',
            'required' => false,
            'label' => __("Phone number", 'complianz-gdpr'),
            'condition' => array('dpo_or_gdpr' => 'dpo'),
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => 'eu',
            ),
        ),

        'website_dpo' => array(
            'step' => STEP_COMPANY,
            'section' => 5,
            'source' => 'wizard',
            'type' => 'text',
            'default' => '',
            'required' => false,
            'placeholder' => __( "Leave empty if not applicable", 'complianz-gdpr' ),
            'label' => __("Website", 'complianz-gdpr'),
            'condition' => array('dpo_or_gdpr' => 'dpo'),
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => 'eu',
            ),
        ),

        'name_gdpr' => array(
	        'master_label'  => __( "Representative", 'complianz-gdpr' ),
	        'step' => STEP_COMPANY,
            'section' => 5,
            'source' => 'wizard',
            'type' => 'text',
            'default' => '',
            'required' => true,
            'label' => __("Name", 'complianz-gdpr'),
            'condition' => array('dpo_or_gdpr' => 'gdpr_rep'),
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => 'eu',
            ),
        ),

        'email_gdpr' => array(
            'step' => STEP_COMPANY,
            'section' => 5,
            'source' => 'wizard',
            'type' => 'email',
            'default' => '',
            'required' => true,
            'label' => __("Email", 'complianz-gdpr'),
            'condition' => array('dpo_or_gdpr' => 'gdpr_rep'),
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => 'eu',
            ),
        ),

        'phone_gdpr' => array(
            'step' => STEP_COMPANY,
            'section' => 5,
            'source' => 'wizard',
            'type' => 'phone',
            'default' => '',
            'required' => false,
            'label' => __("Phone number", 'complianz-gdpr'),
            'condition' => array('dpo_or_gdpr' => 'gdpr_rep'),
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => 'eu',
            ),
        ),

        'website_gdpr' => array(
	        'step' => STEP_COMPANY,
            'section' => 5,
            'source' => 'wizard',
            'type' => 'text',
            'default' => '',
            'required' => false,
            'placeholder' => __("Leave empty if not applicable", 'complianz-gdpr'),
            'label' => __("Website", 'complianz-gdpr'),
            'condition' => array('dpo_or_gdpr' => 'gdpr_rep'),
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => 'eu',
            ),
        ),

        'name_uk_dpo' => array(
	        'master_label'  => __( "Data Protection Officer", 'complianz-gdpr' ),
	        'step' => STEP_COMPANY,
            'section' => 5,
            'source' => 'wizard',
            'type' => 'text',
            'required' => true,
            'default' => '',
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => 'uk',
            ),
            'label' => __("Name", 'complianz-gdpr'),
            'condition' => array('dpo_or_gdpr' => 'dpo_uk'),
        ),
        'email_uk_dpo' => array(
            'step' => STEP_COMPANY,
            'section' => 5,
            'source' => 'wizard',
            'type' => 'email',
            'default' => '',
            'required' => true,
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => 'uk',
            ),
            'label' => __("Email", 'complianz-gdpr'),
            'condition' => array('dpo_or_gdpr' => 'dpo_uk'),
        ),
        'phone_uk_dpo' => array(
            'step' => STEP_COMPANY,
            'section' => 5,
            'source' => 'wizard',
            'type' => 'phone',
            'default' => '',
            'required' => false,
            'label' => __("Phone number", 'complianz-gdpr'),
            'condition' => array('dpo_or_gdpr' => 'dpo_uk'),
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => 'uk',
            ),
        ),

        'website_uk_dpo' => array(
            'step' => STEP_COMPANY,
            'section' => 5,
            'source' => 'wizard',
            'type' => 'text',
            'default' => '',
            'required' => false,
            'placeholder' => __("Leave empty if not applicable", 'complianz-gdpr'),
            'label' => __("Website", 'complianz-gdpr'),
            'condition' => array('dpo_or_gdpr' => 'dpo_uk'),
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => 'uk',
            ),
        ),

        'name_uk_gdpr' => array(
	        'master_label'  => __( "Representative", 'complianz-gdpr' ),
	        'step' => STEP_COMPANY,
            'section' => 5,
            'source' => 'wizard',
            'type' => 'text',
            'default' => '',
            'required' => true,
            'label' => __("Name", 'complianz-gdpr'),
            'condition' => array('dpo_or_gdpr' => 'uk_gdpr_rep'),
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => 'uk',
            ),
        ),

        'email_uk_gdpr' => array(
            'step' => STEP_COMPANY,
            'section' => 5,
            'source' => 'wizard',
            'type' => 'email',
            'default' => '',
            'required' => true,
            'label' => __("Email", 'complianz-gdpr'),
            'condition' => array('dpo_or_gdpr' => 'uk_gdpr_rep'),
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => 'uk',
            ),
        ),

        'phone_uk_gdpr' => array(
            'step' => STEP_COMPANY,
            'section' => 5,
            'source' => 'wizard',
            'type' => 'phone',
            'default' => '',
            'required' => false,
            'label' => __("Phone number", 'complianz-gdpr'),
            'condition' => array('dpo_or_gdpr' => 'uk_gdpr_rep'),
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => 'uk',
            ),
        ),

        'website_uk_gdpr' => array(
            'step' => STEP_COMPANY,
            'section' => 5,
            'source' => 'wizard',
            'type' => 'text',
            'default' => '',
            'required' => false,
            'placeholder' => __("Leave empty if not applicable", 'complianz-gdpr'),
            'label' => __("Website", 'complianz-gdpr'),
            'condition' => array('dpo_or_gdpr' => 'uk_gdpr_rep'),
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => 'uk',
            ),
        ),

        'automated_processes' => array(
	        'step' => STEP_COMPANY,
	        'section' => 6,
	        'source' => 'wizard',
	        'type' => 'radio',
	        'options' => $this->yes_no,
          'placeholder' => __("We use digital services to automate processes without human intervention to optimize our workflows. We make decisions based on the frequency of payments, customer contact, profile changes, and other user-related behavior to personalize the customer journey.", 'complianz-gdpr'),
	        'required' => true,
	        'tooltip' => __("The placeholder is a general example, please rewrite to your specific situation.", 'complianz-gdpr'),
	        'callback_condition' => array(
		        'privacy-statement' => 'generated',
		        'regions' => array('eu', 'uk', 'za'),
	        ),
	        'label' => __("Do you make decisions based on automated processes, such as profiling, that could have significant consequences for users?", 'complianz-gdpr'),
        ),

        'automated_processes_details' => array(
	        'step' => STEP_COMPANY,
	        'section' => 6,
	        'source' => 'wizard',
	        'type' => 'textarea',
	        'required' => true,
	        'condition'=> array(
		        'automated_processes'=>'yes',
	        ),
	        'callback_condition' => array(
		        'privacy-statement' => 'generated',
		        'regions' => array('eu', 'uk', 'za'),
	        ),
	        'label' => __("Specify what kind of decisions these are, what the consequences are, and what (in general terms) the logic behind these decisions is.", 'complianz-gdpr'),
        ),

        // Purpose
        'legal-obligations-description' => array(
            'step'               => STEP_COMPANY,
            'section'            => 6,
            'source'             => 'wizard',
            'type'               => 'textarea',
            'default'            => '',
            'label'              => __( "The collection is required or authorized by the following law or court/tribunal order:", 'complianz-gdpr' ),
            'required'           => true,
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => array( 'au', 'za' )
            ),
            'condition' => array( 'purpose_personaldata' => 'legal-obligations' ),

        ),

        //dynamic purposes here
        'share_data_bought_or_received' => array(
	        'step' => STEP_COMPANY,
	        'section' => 9,
	        'source' => 'wizard',
	        'type' => 'radio',
	        'default' => '',
	        'callback_condition' => array(
		        'privacy-statement' => 'generated',
		        'regions' => array('au', 'za', 'br'),
	        ),
	        'options' => array(
		        '1' => __('Yes', 'complianz-gdpr'),
		        '2' => __('No', 'complianz-gdpr'),
	        ),
	        'label' => __("Do you collect or have you collected personal information about an individual that you bought or received from a third party?", 'complianz-gdpr'),
	        'required' => true,
        ),

        'share_data_bought_or_received_description' => array(
	        'step' => STEP_COMPANY,
	        'section' => 9,
	        'source' => 'wizard',
	        'type' => 'textarea',
	        'default' => '',
	        'callback_condition' => array(
		        'privacy-statement' => 'generated',
		        'regions' => array('au' , 'za', 'br'),
	        ),
	        'label' => __("Please describe the circumstances under which that is being done.", 'complianz-gdpr'),
	        'required' => true,
	        'condition' => array('share_data_bought_or_received' => '1'),
        ),

        'data_disclosed_us' => array(
	        'step' => STEP_COMPANY,
	        'section' => 9,
	        'source' => 'wizard',
	        'type' => 'multicheckbox',
	        'tooltip' => __('Under CCPA you must show a list of the categories of personal information you have disclosed for a business purpose in the preceding 12 months.', 'complianz-gdpr'),
	        'default' => '',
	        'label' => __('Select which categories of personal data you have disclosed for a business purpose in the past 12 months', 'complianz-gdpr'),
	        'required' => false,
	        'callback_condition' => array(
		        'privacy-statement' => 'generated',
		        'regions' => array('us'),
		        'california' => 'yes',
	        ),
	        'options' => $this->details_per_purpose_us,
        ),

        'data_sold_us' => array(
	        'step' => STEP_COMPANY,
	        'section' => 9,
	        'source' => 'wizard',
	        'type' => 'multicheckbox',
	        'default' => '',
	        'tooltip' => __('You must Inform your visitors if you have sold any personal data in the last 12 months, and give them the possibility to opt-out of the future sale of personal information with Complianz.', 'complianz-gdpr'),
	        'label' => __('Select which categories of personal data you have sold to Third Parties in the past 12 months', 'complianz-gdpr'),
	        'required' => false,
	        'callback_condition' => array(
		        'california' => 'yes',
		        'privacy-statement' => 'generated',
		        'regions' => array('us','ca'),
		        'purpose_personaldata' => 'selling-data-thirdparty',
	        ),
	        'condition' => array(),
	        'options' => $this->details_per_purpose_us,
        ),
        // THIRD PARTIES
        'share_data_other' => array(
            'step' => STEP_COMPANY,
            'section' => 9,
            'source' => 'wizard',
            'type' => 'radio',
            'default' => '',
            'callback_condition' => array(
                'privacy-statement' => 'generated',
            ),
            'options' => array(
                '1' => __('Yes, both to Processors/Service Providers and other Third Parties, whereby the data subject must give permission', 'complianz-gdpr'),
                '2' => __('No', 'complianz-gdpr'),
                '3' => __('Limited: only with Processors/Service Providers that are necessary for the fulfillment of my service', 'complianz-gdpr'),
            ),
            'label' => __("Do you share personal data with other parties?", 'complianz-gdpr'),
            'required' => true,
            'tooltip' => __("A Service Provider is a legal entity that processes information on behalf of a business and to which the business discloses a consumer's personal information for a business purpose pursuant to a written contract.",'complianz-gdpr')
                         .'&nbsp;'
                         .__("Within the GDPR a ‘Processor’ means a natural or legal person, public authority, agency or other body which processes personal data on behalf of the Controller.", 'complianz-gdpr')
                         ." "
                         .__("A Third Party is every other entity which receives personal data, but does not fall within the definition of a Processor or Service Provider", 'complianz-gdpr'),
        ),

        'processor' => array(
            'step' => STEP_COMPANY,
            'section' => 9,
            'source' => 'wizard',
            'region' => 'eu',
            'type' => 'processors',
            'required' => false,
            'default' => '',
            'condition' => array('share_data_other' => 'NOT 2'),
            'callback_condition' => array(
                'privacy-statement' => 'generated',
            ),
            'label' => __("Processors & Service Providers", 'complianz-gdpr'),
        ),

        'thirdparty' => array(
            'step' => STEP_COMPANY,
            'section' => 9,
            'source' => 'wizard',
            'type' => 'thirdparties',
            'required' => false,
            'default' => '',
            'condition' => array('share_data_other' => '1'),
            'callback_condition' => array(
                'privacy-statement' => 'generated',
            ),
            'label' => __("Third Parties", 'complianz-gdpr'),
        ),

        /*
         * consent boxes
         * */

        'add_consent_to_forms' => array(
            'step' => STEP_COMPANY,
            'section' => 11,
            'source' => 'wizard',
            'type' => 'multicheckbox',
            'required' => false,
            'default' => '',
            'label' => __("For forms detected on your site, you can choose to add a consent checkbox", 'complianz-gdpr'),
            'options' => get_option('cmplz_detected_forms'),
            'callback_condition' => array(
                'contact_processing_data_lawfull'=>'1',
                'regions' => array('eu', 'uk', 'za'),
            ),//when permission is required, add consent box
            'help' => __('You have answered that you use webforms on your site. Not every form that collects personal data requires a checkbox.', 'complianz-gdpr').cmplz_read_more('https://complianz.io/how-to-implement-a-consent-box'),
        ),


        //  & SAFETY
        'secure_personal_data' => array(
            'step' => STEP_COMPANY,
            'section' => 11,
            'source' => 'wizard',
            'type' => 'radio',
            'required' => true,
            'default' => '',
            'label' => __("Do you want to provide a detailed list of security measures in your Privacy Statement?", 'complianz-gdpr'),
            'options' => array(
                '1' => __('No, provide a general explanation', 'complianz-gdpr'),
                '2' => __('Yes, we will list our security measures', 'complianz-gdpr')
            ),
            'callback_condition' => array(
                'privacy-statement' => 'generated',
            ),
        ),

        'which_personal_data_secure' => array(
            'step' => STEP_COMPANY,
            'section' => 11,
            'source' => 'wizard',
            'required' => true,
            'type' => 'multicheckbox',
            'default' => '',
            'label' => __("Which security measures did you take?", 'complianz-gdpr'),
            'options' => array(
                '1' => __('Username and Password', 'complianz-gdpr'),
                '2' => __('DNSSEC', 'complianz-gdpr'),
                '3' => __('TLS / SSL', 'complianz-gdpr'),
                '4' => __('DKIM, SPF en DMARC', 'complianz-gdpr'),
                '5' => __('Physical security measures of systems which contain personal  data.', 'complianz-gdpr'),
                '6' => __('Security software', 'complianz-gdpr'),
                '7' => __('ISO27001/27002 certified', 'complianz-gdpr'),
                '8' => 'HTTP Strict Transport Security',
                '9' => 'X-Content-Type-Options',
                '10' => 'X-XSS-Protection',
                '11' => 'X-Frame-Options',
                '12' => 'Expect-CT',
                '13' => 'No Referrer When Downgrade header',
                '14' => 'Content Security Policy',
                '15' => __('STARTTLS and DANE','complianz-gdpr'),
                '16' => 'WPA2 Enterprise',
                '17' => 'Permissions Policy',
            ),
            'comment' => cmplz_sprintf(__("Quickly want to implement most of these security headers? Check out %sReally Simple SSL Pro%s.", "complianz-gdpr"),'<a href="https://really-simple-ssl.com/pro/" target="_blank">','</a>'),
            'callback_condition' => array(
                'privacy-statement' => 'generated',
            ),
            'condition' => array('secure_personal_data' => '2'),
        ),

        'financial-incentives' => array(
            'step' => STEP_COMPANY,
            'section' => 12,
            'source' => 'wizard',
            'required' => true,
            'type' => 'radio',
            'default' => '',
            'label' => __("Do you offer financial incentives, including payments to consumers as compensation, for the collection of personal information, the sale of personal information, or the deletion of personal information?", 'complianz-gdpr'),
            'options' => $this->yes_no,
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => array('us'),
                'california' => 'yes',
            ),
        ),

        'financial-incentives-terms-url' => array(
            'step' => STEP_COMPANY,
            'section' => 12,
            'placeholder' => __('https://your-terms-page.com','complianz-gdpr'),
            'source' => 'wizard',
            'required' => true,
            'type' => 'url',
            'default' => '',
            'label' => __("Enter the URL of the terms & conditions page for the incentives", 'complianz-gdpr'),
            'comment' => cmplz_sprintf(__('Also see our free %sTerms & Conditions%s plugin', "complianz-gdpr"), '<a href="https://wordpress.org/plugins/complianz-terms-conditions/" target="_blank">', '</a>'),
            'condition' => array('financial-incentives' => 'yes'),
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => array('us'),
                'california' => 'yes',
            ),
            'help' => __("Please note that the consumer explicitly has to consent to these terms, and that the consumer must be able to revoke this consent. ", 'complianz-gdpr'),
        ),

        'targets-children' => array(
            'step' => STEP_COMPANY,
            'section' => 13,
            'source' => 'wizard',
            'required' => true,
            'type' => 'radio',
            'default' => '',
            'label' => __("Is your website designed to attract children and/or is it your intent to collect personal data from children under the age of 13?", 'complianz-gdpr'),
            'options' => $this->yes_no,
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => array('us','uk', 'ca', 'au', 'za', 'br'),

            ),
        ),

        'children-parent-consent-type' => array(
            'step' => STEP_COMPANY,
            'section' => 13,
            'source' => 'wizard',
            'required' => true,
            'type' => 'multicheckbox',
            'default' => '',
            'label' => __("How do you obtain verifiable parental consent for the collection, use, or disclosure of personal information from children?", 'complianz-gdpr'),
            'options' => array(
                'email' => __("We seek a parent or guardian's consent by email",'complianz-gdpr'),
                'creditcard' => __('We seek a high level of consent by asking for a creditcard verification','complianz-gdpr'),
                'phone-chat' => __('We use telephone or Videochat  to talk to the parent or guardian','complianz-gdpr'),
            ),
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => array('us','uk','ca', 'au', 'za'),
            ),
            'condition' => array('targets-children' => 'yes'),
        ),

        'children-safe-harbor' => array(
            'step' => STEP_COMPANY,
            'section' => 13,
            'source' => 'wizard',
            'required' => true,
            'type' => 'radio',
            'default' => '',
            'label' => __("Is your website included in a COPPA Safe Harbor Certification Program?", 'complianz-gdpr'),
            'options' => $this->yes_no,
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => 'us',
            ),
            'condition' => array(
                'targets-children' => 'yes'
            ),
        ),

        'children-name-safe-harbor' => array(
            'step' => STEP_COMPANY,
            'section' => 13,
            'source' => 'wizard',
            'required' => true,
            'type' => 'text',
            'default' => '',
            'label' => __("What is the name of the program?", 'complianz-gdpr'),
            'options' => $this->yes_no,
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => 'us',
            ),
            'condition' => array(
                'children-safe-harbor' => 'yes'
            ),
        ),

        'children-url-safe-harbor' => array(
            'step' => STEP_COMPANY,
            'section' => 13,
            'source' => 'wizard',
            'required' => true,
            'type' => 'url',
            'default' => '',
            'label' => __("What is the URL of the program?", 'complianz-gdpr'),
            'options' => $this->yes_no,
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => 'us',
            ),
            'condition' => array(
                'children-safe-harbor' => 'yes'
            ),
        ),

        'children-what-purposes' => array(
            'step' => STEP_COMPANY,
            'section' => 14,
            'source' => 'wizard',
            'required' => true,
            'type' => 'multicheckbox',
            'default' => '',
            'label' => __("For what potential activities on your website do you collect personal information from a child?", 'complianz-gdpr'),
            'options' => array(
                'registration' => __('Registration','complianz-gdpr'),
                'content-created-by-child' => __('Content created by a child and publicly shared','complianz-gdpr'),
                'chat' => __('Chat/messageboard','complianz-gdpr'),
                'email' => __('Email contact','complianz-gdpr'),
            ),
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => array('us','uk','ca', 'au', 'za', 'br'),
                'targets-children' => 'yes',
            ),
        ),

        'children-no-safe-harbor-notice' => array(
	        'step' => STEP_COMPANY,
	        'section' => 14,
	        'source' => 'wizard',
	        'required' => false,
	        'type' => 'notice',
	        'default' => '',
	        'help' => cmplz_sprintf(__("You have indicated that your website is not included in a COPPA Safe Harbor Certification Program. We recommend to check out %sPRIVO%s ,as you target children on your website.", 'complianz-gdpr'),'<a href="https://www.privo.com/" target="_blank">','</a>'),
	        'options' => $this->yes_no,
	        'callback_condition' => array(
		        'privacy-statement' => 'generated',
		        'targets-children' => 'yes',
		        'regions' => 'us',
		        'children-safe-harbor' => 'no',
	        ),
        ),

        'children-what-information-registration' => array(
            'step' => STEP_COMPANY,
            'section' => 14,
            'source' => 'wizard',
            'required' => true,
            'type' => 'multicheckbox',
            'default' => '',
            'label' => __("Information collected for registration ", 'complianz-gdpr'),
            'options' => $this->collected_info_children,
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => array('us','uk', 'ca', 'au', 'za', 'br'),
                'targets-children' => 'yes',

            ),
            'condition' => array(
                'children-what-purposes' => 'registration'),
        ),

        'children-what-information-content' => array(
            'step' => STEP_COMPANY,
            'section' => 14,
            'source' => 'wizard',
            'required' => true,
            'type' => 'multicheckbox',
            'default' => '',
            'label' => __("Information collected for content created by a child", 'complianz-gdpr'),
            'options' => $this->collected_info_children,
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => array('us','uk','ca', 'au', 'za', 'br'),
                'targets-children' => 'yes',
            ),
            'condition' => array(
                'children-what-purposes' => 'content-created-by-child'
            ),
        ),

        'children-what-information-chat' => array(
            'step' => STEP_COMPANY,
            'section' => 14,
            'source' => 'wizard',
            'required' => true,
            'type' => 'multicheckbox',
            'default' => '',
            'label' => __("Information collected for chat/messageboard", 'complianz-gdpr'),
            'options' => $this->collected_info_children,
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => array('us','uk','ca', 'au', 'za', 'br'),
                'targets-children' => 'yes',

            ),
            'condition' => array(
                'children-what-purposes' => 'chat'
            ),
        ),
        'children-what-information-email' => array(
            'step' => STEP_COMPANY,
            'section' => 14,
            'source' => 'wizard',
            'required' => true,
            'type' => 'multicheckbox',
            'default' => '',
            'label' => __("Information collected for email contact", 'complianz-gdpr'),
            'options' => $this->collected_info_children,
            'callback_condition' => array(
                'privacy-statement' => 'generated',
                'regions' => array('us','uk','ca', 'au', 'za', 'br'),
                'targets-children' => 'yes',

            ),
            'condition' => array(
                'children-what-purposes' => 'email'),
        ),



        //DISCLAIMER
        'themes' => array(
            'step' => STEP_COMPANY,
            'section' => 15,
            'source' => 'wizard',
            'type' => 'multicheckbox',
            'default' => '1',
            'label' => __("Which themes would you like to include in your Disclaimer?", 'complianz-gdpr'),
            'options' => array(
                '1' => __('Liability', 'complianz-gdpr'),
                '2' => __('Reference to terms of use', 'complianz-gdpr'),
                '3' => __('How you will answer inquiries', 'complianz-gdpr'),
                '4' => __('Privacy and reference to the privacy statement', 'complianz-gdpr'),
                '5' => __('Not liable when security is breached', 'complianz-gdpr'),
                '6' => __('Not liable for third-party content', 'complianz-gdpr'),
                '7' => __('Accessibility of the website for the disabled', 'complianz-gdpr'),
            ),
            'callback_condition' => array('disclaimer' => 'generated'),
            'required' => true,
        ),
        'terms_of_use_link' => array(
            'step' => STEP_COMPANY,
            'section' => 15,
            'source' => 'wizard',
            'type' => 'url',
            'default' => '',
            'label' => __("What is the URL of the Terms of Use?", 'complianz-gdpr'),
            'comment' => cmplz_sprintf(__('Also see our free %sTerms & Conditions%s plugin', "complianz-gdpr"), '<a href="https://wordpress.org/plugins/complianz-terms-conditions" target="_blank">', '</a>'),
            'condition' => array('themes' => '2'),
            'callback_condition' => array('disclaimer' => 'generated'),
            'required' => true,
        ),

        'wcag' => array(
            'step' => STEP_COMPANY,
            'section' => 15,
            'source' => 'wizard',
            'type' => 'radio',
            'default' => 'The WCAG documents explain how to make web content more accessible to people with disabilities.',
            'label' => __("Is your website built according to WCAG 2.1 level AA guidelines?", 'complianz-gdpr'),
            'options' => $this->yes_no,
            'condition' => array('themes' => '7'),
            'callback_condition' => array('disclaimer' => 'generated'),
            'required' => true,
            'help' => cmplz_read_more('https://complianz.io/wcag-2-0-what-is-it/', false),

        ),

        // AUTEURSRECHTEN disclaimer
        'development' => array(
            'step' => STEP_COMPANY,
            'section' => 15,
            'source' => 'wizard',
            'type' => 'radio',
            'default' => '',
            'label' => __("Who made the content of the website?", 'complianz-gdpr'),
            'options' => array(
                '1' => __('The content is being developed by ourselves', 'complianz-gdpr'),
                '2' => __('The content is being developed or posted by Third Parties', 'complianz-gdpr'),
                '3' => __('The content is being developed by ourselves and other parties', 'complianz-gdpr'),
            ),
            'callback_condition' => array('disclaimer' => 'generated'),
            'required' => true,
        ),

        'ip-claims' => array(
            'step' => STEP_COMPANY,
            'section' => 15,
            'source' => 'wizard',
            'type' => 'radio',
            'default' => '',
            'required' => true,
            'label' => __("What do you want to do with any intellectual property claims?", 'complianz-gdpr'),
            'options' => array(
                '1' => __('All rights reserved', 'complianz-gdpr'),
                '2' => __('No rights reserved', 'complianz-gdpr'),
                '3' => __('Creative Commons - Attribution', 'complianz-gdpr'),
                '4' => __('Creative Commons - Share a like', 'complianz-gdpr'),
                '5' => __('Creative Commons - No derivatives', 'complianz-gdpr'),
                '6' => __('Creative Commons - Noncommercial', 'complianz-gdpr'),
                '7' => __('Creative Commons - Share a like, noncommercial', 'complianz-gdpr'),
            ),
            'callback_condition' => array('disclaimer' => 'generated'),
            'help' => __("Creative Commons (CC) is an American non-profit organization devoted to expanding the range of creative works available for others to build upon legally and to share.", 'complianz-gdpr').cmplz_read_more('https://complianz.io/creative-commons'),
        ),

        'wp_privacy_policies' => array(
	        'label' => __('Privacy statements from plugins', "complianz-gdpr"),
	        'step' => STEP_COOKIES,
            'section' => 5,
            'source' => 'wizard',
            'type' => 'multiple',
            'callback' => 'wp_privacy_policies',
            'required' => false,
	        'help' => __('Please note that you should customize these texts for your website: the text should generally not be copied as is.', 'complianz-gdpr'),
	        'comment' =>__('Plugins and themes can add their own suggested privacy paragraphs here. You can choose to add these to the Annex of your Privacy Statement.', 'complianz-gdpr') .
	                    "&nbsp" . __('You can also add additional custom texts to the Annex of your Privacy Statement if you like.', 'complianz-gdpr'),
        ),



        'custom_privacy_policy_text' => array(
            'step' => STEP_COOKIES,
            'section' => 5,
            'translatable' => true,
            'source' => 'wizard',
            'type' => 'editor',
            'label' => __('Annex of your Privacy Statement', "complianz-gdpr"),
            'required' => false,
            'media' => false,
            'callback_condition' => array(
	            'privacy-statement' => 'generated'
            )
        ),
    );
