<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Theme Options Field: Text
 *
 * Simple text field.
 *
 * @var   $name  string Field name
 * @var   $id    string Field ID
 * @var   $field array Field options
 *
 * @param $field ['title'] string Field title
 * @param $field ['description'] string Field title
 * @param $field ['placeholder'] string Field placeholder
 *
 * @var   $value string Current value
 */

// Hidden result field
$hidden_atts = array(
	'name' => $name,
	'type' => 'hidden',
	'value' => $value,
);

// Field for editing in Visual Composer
if ( isset( $field['us_vc_field'] ) ) {
	// Note: Through the field which has a class `wpb_vc_param_value` Visual Composer receives the final value
	$hidden_atts['class'] = 'wpb_vc_param_value';
}

// TODO: Move to `/usof/templates/field.php`
if ( is_array( $hidden_atts['value'] ) ) {
	$hidden_atts['value'] = rawurlencode( json_encode( $hidden_atts['value'] ) );
}

echo '<input'. us_implode_atts( $hidden_atts ) .'/>';

// By default we display the default value
if ( is_array( $value ) AND isset( $value['default'] ) ) {
	$value = $value['default'];
}

// Text input field
$input_atts = array(
	'type' => 'text',
	'value' => $value,
);

if ( ! empty( $field['placeholder'] ) ) {
	$input_atts['placeholder'] = $field['placeholder'];
}

echo '<input'. us_implode_atts( $input_atts ) .'/>';
