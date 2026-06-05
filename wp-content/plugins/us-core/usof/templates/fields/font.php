<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Theme Options Field: Font
 *
 * Select font
 *
 * @param $field ['title'] string Field title
 * @param $field ['description'] string Field title
 * @param $field ['preview'] array
 * @param $field ['preview']['text'] string Preview text
 * @param $field ['preview']['size'] string Font size in css format
 *
 * @var   $name  string Field name
 * @var   $id    string Field ID
 * @var   $field array Field options
 *
 * @var   $value array List of checked keys
 */

$output = '';
$font_value = explode( '|', $value, 2 );
if ( ! isset( $font_value[1] ) OR empty( $font_value[1] ) ) {
	$font_value[1] = '400,700';
}
$font_value[1] = explode( ',', $font_value[1] );

$output .= '<div class="usof-font">';

// Text Preview
if ( ! empty( $field['preview_text'] ) ) {
	$output .= us_load_template( 'usof/templates/fields/preview_text', $field['preview_text'] );
}

$output .= '<input type="hidden" name="' . $name . '" value="' . esc_attr( $value ) . '" />';

// Get all Google fonts
$all_fonts = us_get_all_google_fonts();

// The number of options for the first part of the output or receive through autocomplete
$font_limit = ( isset( $field['font_limit'] ) AND (int) $field['font_limit'] ) ? $field['font_limit'] : 50;
$font_options = array();

// Get the set number of options
$i = 0;
$isset_value = FALSE;
foreach ( $all_fonts as $item_value => $item_name ) {
	if ( is_array( $item_name ) ) {
		foreach ( $item_name as $_item_value => $_item_name ) {
			$font_options[ $item_value ][ $_item_value ] = $_item_name;
			$i++;
			if ( ! empty( $font_value[0] ) AND $font_value[0] === $_item_name ) {
				$isset_value = TRUE;
			}
			if ( $i >= $font_limit ) {
				break 2;
			}
		}
	} else {
		$i++;
		$font_options[ $item_value ] = $item_name;
	}
	if ( $i >= $font_limit ) {
		break;
	}
}

// Add the selected font to the first part of the data.
if ( ! $isset_value AND ! empty( $font_value[0] ) ) {
	foreach ( $all_fonts as $item_value => $item_name ) {
		if ( is_array( $item_name ) ) {
			foreach ( $item_name as $_item_value => $_item_name ) {
				if ( $font_value[0] === $_item_name ) {
					$font_options[ $item_value ][ $_item_value ] = $_item_name;
					break 2;
				}
			}
		}
	}
}

// Check is not set any value, font has no name get_h1 and current font name not contains in fonts options
if ( ! $isset_value AND $font_value[0] !== 'get_h1' AND array_search( $font_value[0], array_column( $font_options, $font_value[0] ) ) === FALSE ) {
	$font_value[0] = 'none';
}
unset( $all_fonts, $isset_value );

$fonts_group_keys = array(
	'websafe' => __( 'Web safe font combinations (do not need to be loaded)', 'us' ),
	'google' => __( 'Google Fonts (loaded from Google servers)', 'us' ),
	'uploaded' => __( 'Uploaded Fonts', 'us' ),
);

// Field for getting font name through autocomplete
$output .= '<div class="type_autocomplete" data-name="font_name"'. us_pass_data_to_js( $fonts_group_keys ) .'>';
$output .= us_get_template( 'usof/templates/fields/autocomplete', array(
	'value' => $font_value[0],
	'field' => array(
		'settings' => array(
			'nonce_name' => 'usof_ajax_all_fonts_autocomplete',
			'action' => 'usof_all_fonts_autocomplete',
			'font_limit' => $font_limit,
		),
		'params_separator' => '|', // to support ',' in values, add a non-existent separator
		'options' => $font_options,
	),
) );
$output .= '</div>';

// Font weights
if ( ! isset( $font_weights ) ) {
	$font_weights = array(
		'100' => '100 ' . __( 'thin', 'us' ),
		'100italic' => '100 ' . __( 'thin', 'us' ) . ' <i>' . __( 'italic', 'us' ) . '</i>',
		'200' => '200 ' . __( 'extra-light', 'us' ),
		'200italic' => '200 ' . __( 'extra-light', 'us' ) . ' <i>' . __( 'italic', 'us' ) . '</i>',
		'300' => '300 ' . __( 'light', 'us' ),
		'300italic' => '300 ' . __( 'light', 'us' ) . ' <i>' . __( 'italic', 'us' ) . '</i>',
		'400' => '400 ' . __( 'normal', 'us' ),
		'400italic' => '400 ' . __( 'normal', 'us' ) . ' <i>' . __( 'italic', 'us' ) . '</i>',
		'500' => '500 ' . __( 'medium', 'us' ),
		'500italic' => '500 ' . __( 'medium', 'us' ) . ' <i>' . __( 'italic', 'us' ) . '</i>',
		'600' => '600 ' . __( 'semi-bold', 'us' ),
		'600italic' => '600 ' . __( 'semi-bold', 'us' ) . ' <i>' . __( 'italic', 'us' ) . '</i>',
		'700' => '700 ' . __( 'bold', 'us' ),
		'700italic' => '700 ' . __( 'bold', 'us' ) . ' <i>' . __( 'italic', 'us' ) . '</i>',
		'800' => '800 ' . __( 'extra-bold', 'us' ),
		'800italic' => '800 ' . __( 'extra-bold', 'us' ) . ' <i>' . __( 'italic', 'us' ) . '</i>',
		'900' => '900 ' . __( 'ultra-bold', 'us' ),
		'900italic' => '900 ' . __( 'ultra-bold', 'us' ) . ' <i>' . __( 'italic', 'us' ) . '</i>',
	);
}
$show_weights = (array) us_config( 'google-fonts.' . $font_value[0] . '.variants', array() );

$output .= '<ul class="usof-checkbox-list">';
foreach ( $font_weights as $font_weight => $font_title ) {
	$font_weight = (string) $font_weight;
	$output .= '<li class="usof-checkbox' . ( in_array( $font_weight, $show_weights ) ? '' : ' hidden' ) . '" data-value="' . $font_weight . '">';
	$output .= '<label>';
	$output .= '<input type="checkbox" value="' . $font_weight . '"';
	$output .= ( array_search( $font_weight, $font_value[1], TRUE ) !== FALSE ? ' checked' : '' );
	$output .= '>';
	$output .= '<span class="usof-checkbox-text">';
	$output .= $font_title . '</span></label></li>';
}
$output .= '</ul>';

$output .= '</div>';

echo $output;
