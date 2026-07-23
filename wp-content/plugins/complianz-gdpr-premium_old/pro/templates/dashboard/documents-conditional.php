<?php defined( 'ABSPATH' ) or die( "you do not have access to this page!" );
$row = '';

$args = array(
	'status' => '',
	'title' => '<h3>'.__("Other documents", "complianz-gdpr").'</h3>',
	'control' => '',
);
echo cmplz_get_template('dashboard/documents-row-compact.php', $args);
$docs = get_posts(
	array(
		'post_type' => 'cmplz-processing',
		'post_status' => 'publish',
	)
);

$select = '<option value="">'.__("Select Processing Agreement", "complianz-gdpr").'</option>';

if (count($docs)===0) {
	$args = array(
		'status' => '',
		'title' => '<select><option value="">'.__("No processing agreements", "complianz-gdpr").'</option></select>',
		'control' => '<a class="button button-default" href="'.add_query_arg(array('post_type' => 'cmplz-processing'), admin_url('edit.php') ).'">'.__("Create", "complianz-gdpr").'</a>',
	);
} else {
	foreach ( $docs as $doc ) {
		$select .= '<option value="'.get_cmplz_document_download_url($doc->ID).'">'.$doc->post_title.'</option>';
	}
	$args = array(
		'status' => '',
		'title' => '<select class="cmplz-download-document-selector">'.$select.'</select>',
		'control' => '<button disabled class="button button-default cmplz-download-document">'.__("Download", "complianz-gdpr").'</button>',
	);
}
echo cmplz_get_template('dashboard/documents-row-compact.php', $args);

$docs = get_posts(
	array(
		'post_type' => 'cmplz-dataleak',
		'post_status' => 'publish',
	)
);

$select = '<option value="">'.__("Select Data Breach report", "complianz-gdpr").'</option>';
$nr_of_docs = count($docs);
foreach ( $docs as $doc ) {
	if ( !COMPLIANZ::$dataleak->dataleak_has_to_be_reported_to_involved($doc->ID) ) {
		$nr_of_docs--;
		continue;
	}
	$select .= '<option value="'.get_cmplz_document_download_url($doc->ID).'">'.$doc->post_title.'</option>';
}

if ( $nr_of_docs===0 ) {
	$args = array(
		'status' => '',
		'title' => '<select><option value="">'.__("No data breach reports", "complianz-gdpr").'</option></select>',
		'control' => '<a class="button button-default" href="'.add_query_arg(array('post_type' => 'cmplz-dataleak'), admin_url('edit.php') ).'">'.__("Create", "complianz-gdpr").'</a>',
	);
} else {
	$args = array(
		'status' => '',
		'title' => '<select class="cmplz-download-document-selector">'.$select.'</select>',
		'control' => '<button disabled class="button button-default">'.__("Download", "complianz-gdpr").'</button>',
	);
}

echo cmplz_get_template('dashboard/documents-row-compact.php', $args);
$docs = COMPLIANZ::$document->get_cookie_snapshot_list();
if (count($docs)===0) {
	$select = '<select><option value="">'.__("No proof of consent documents", "complianz-gdpr").'</option></select>';
	$args = array(
		'status' => '',
		'title' => $select,
		'control' => '<a href="'.add_query_arg(array('page' => 'cmplz-proof-of-consent'), admin_url('admin.php') ).'" class="button button-default">'.__("Generate", "complianz-gdpr").'</a>',
	);
} else {
	$select = '<option value="">'.__("Select Proof of Consent", "complianz-gdpr").'</option>';
	foreach ( $docs as $doc ) {
		$filename = $doc['file'];
		//strip everything before proof of consent
		$pos = strpos($filename, '-proof-of-consent-');//leave region in place
		$region = substr( $filename, $pos-2, 2 );
		$filename = strtoupper($region). ' - '.str_replace('-', ' ', substr( $filename, $pos ) );
		$select .= '<option value="'.$doc['url'].'">'.$filename.'</option>';
	}
	$select = '<select class="cmplz-download-document-selector">'.$select.'</select>';

	$args = array(
		'status' => '',
		'title' => $select,
		'control' => '<button disabled class="button button-default cmplz-download-document">'.__("Download", "complianz-gdpr").'</button>',
	);
}

echo cmplz_get_template('dashboard/documents-row-compact.php', $args);


