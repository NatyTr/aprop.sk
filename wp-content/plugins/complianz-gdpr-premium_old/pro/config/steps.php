<?php
defined('ABSPATH') or die("you do not have access to this page!");

$this->steps['wizard'][STEP_COMPANY] = array(
    "id" => "company",
    "title" => __("General", 'complianz-gdpr'),
    'sections' => array(
	    1 => array(
		    'id' => 'visitors',
		    'title' => __('Visitors', 'complianz-gdpr'),
		    'intro' => '<p>'. _x('The Complianz Wizard will guide you through the necessary steps to configure your website for privacy legislation around the world. We designed the wizard to be comprehensible, without making concessions in legal compliance.','intro first step', 'complianz-gdpr') .
		               '&nbsp;'. _x('There are a few things to assist you during configuration:','intro first step', 'complianz-gdpr'). '</p>' .'<ul>'.
		               '<li>' . _x('Hover over the question mark behind certain questions for more information.', 'intro first step', 'complianz-gdpr').'</li>' .
                   '<li>' . _x('Important notices and relevant articles are shown in the right column.', 'intro first step', 'complianz-gdpr').'</li>' .
                   '<li>' . cmplz_sprintf(_x('Our %sinstructions manual%s contains more detailed background information about every section and question in the wizard.','intro first step', 'complianz-gdpr'),'<a target="_blank" href="https://complianz.io/manual/visitors/">', '</a>') .'</li>' .
                   '<li>' . cmplz_sprintf(_x('You can always %slog a support ticket%s if you need further assistance.','intro first step', 'complianz-gdpr'),'<a target="_blank" href="https://complianz.io/support">', '</a>') .'</li></ul>',

	    ),
        2 => array(
            'id' => 'general',
            'title' => __('Documents', 'complianz-gdpr'),
            'intro' => '<p>'._x('Here you can select which legal documents you want to generate with Complianz. You can also use existing legal documents.', 'intro company info', 'complianz-gdpr').'</p>',
        ),
        3 => array(
            'id' => 'company_info',
            'title' => __('Website information', 'complianz-gdpr'),
            'intro' => '<p>'.__('We need some information to be able to generate your documents and configure your cookie banner.', 'complianz-gdpr').'</p>',
        ),
	    4 => array(
		    'id' => 'impressum_info',
		    'title' => __('Imprint', 'complianz-gdpr'),
		    'intro' => '<p>'._x('We need some information to be able to generate your Imprint. Not all fields are required.', 'intro company info', 'complianz-gdpr').cmplz_read_more( 'https://complianz.io/impressum-required-information' ).'</p>',
	    ),
        5 => array(
            'id' => 'dpo',
            'title' => __('Data Protection Officer', 'complianz-gdpr'),
            'region' => array( 'eu', 'uk' ),
        ),
        6 => array(
            'id' => 'purpose',
            'title' => __('Purpose', 'complianz-gdpr'),
        ),
        7 => array(
            'region' => array('eu', 'uk', 'za', 'br'),
            'id' => 'details_per_purpose_eu',
            'title' => __('Details per purpose', 'complianz-gdpr'),
        ),
        8 => array(
            'region' => array('us', 'ca', 'au'),
            'id' => 'details_per_purpose_us',
            'title' => __('Details per purpose', 'complianz-gdpr'),
        ),
        9 => array(
            'region' => array('eu','us', 'uk', 'au', 'za', 'br'),
            'id' => 'sharing_of_data_eu',
            'title' => __('Sharing of data', 'complianz-gdpr'),
            'intro' => '<p>'._x('In this section, we need you to fill in information about Processors, Service Providers and Third Parties you’re working with.', 'intro third parties', 'complianz-gdpr').'</p>',
        ),

        11 => array(
            'title' => __('Security & Consent', 'complianz-gdpr'),
        ),
        12 => array(
            'region' => array('us'),
            'title' => __('Financial incentives', 'complianz-gdpr'),
        ),
        13 => array(
            'region' => array('us','uk', 'ca', 'au', 'za', 'br'),
            'law' => 'COPPA / UK-GDPR / Data Protection Act',
            'title' => __('Children', 'complianz-gdpr'),
        ),
        14 => array(
            'region' => array('us', 'au'),
            'law' => 'COPPA',
            'title' => __('Children: data processing purposes', 'complianz-gdpr'),
        ),
        15 => array('title' => __('Disclaimer', 'complianz-gdpr'),
            'intro' => '<p>'._x('Answers you will give below will be used to generate your Disclaimer.', 'intro disclaimer', 'complianz-gdpr').'</p>',
        ),
    )
);

$this->steps['wizard'][STEP_MENU] = array(

	"id"    => "menu",
	"title" => __( "Documents", 'complianz-gdpr' ),
	'intro' =>
		'<h1>' . _x( "Get ready to finish your configuration.",
			'intro menu', 'complianz-gdpr' ) . '</h1>' .
		'<p>'
		. _x( "Generate your documents, then you can add them to your menu directly or do it manually after the wizard is finished.",
			'intro menu', 'complianz-gdpr' ) . '</p>',
	'sections' => array(
		1 => array(
			'title' => __( 'Create documents', 'complianz-gdpr' ),
				),
		2 => array(
			'title' => __( 'Link to menu', 'complianz-gdpr' ),
		),
	),
);

$regions = cmplz_get_regions(false);
foreach ($regions as $region => $label) {
	$this->steps["processing-$region"] = array(
		1 => array(
			"title" => __("General", 'complianz-gdpr'),
		),
		2 => array("title" => __("Processing", 'complianz-gdpr'),
		           'sections' => array(
			           1 => array('title' => __('Data', 'complianz-gdpr'),
			                      'region' => array($region),
			           ),
			           2 => array('title' => __('Handling of requests', 'complianz-gdpr'),
			                      'region' => array($region),
			           ),
			           3 => array('title' => __('Right of audit', 'complianz-gdpr'),
			                      'region' => array($region),
			           ),
		           ),
		),
		3 => array("title" => __("Data breach", 'complianz-gdpr'),
		           'region' => array($region),
		),
		4 => array("title" => __("Finish", 'complianz-gdpr'),
		           'region' => array($region),
		),
	);
}
