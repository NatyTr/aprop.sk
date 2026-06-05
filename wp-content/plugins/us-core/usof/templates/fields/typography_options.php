<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Typography Options
 *
 * @var $name string Field name
 * @var $id string Field ID
 * @var $field array Field options
 *
 * @var $value string
 */

// Implement support for responsive values at the global level
if ( is_array( $value ) ) {
	$value = rawurlencode( json_encode( $value ) );
}

// Input atts
$input_atts = array(
	'type' => 'hidden',
	'name' => $name,
	'value' => $value,
);

// Output the HTML
echo '<input' . us_implode_atts( $input_atts ) . '>';
if ( isset( $field['fields'] ) AND is_array( $field['fields'] ) ) {
	foreach ( $field['fields'] as $prop_name => $item_field ) {
		echo us_get_template(
			'usof/templates/field', array(
				'context' => $context,
				'field' => $item_field,
				'id' => sprintf( '%s_%s_%s', $context, $item_field['type'], $prop_name ),
				'name' => $prop_name,
			)
		);
	}
}

// Output the text for non-existed font weight
echo '<div class="us-font-weight-not-exists-text hidden"> &mdash; ' . __( 'doesn\'t exist for selected Google font', 'us' ) . '</div>';

// Get font weights and styles
global $us_google_fonts;
if ( empty( $us_google_fonts ) ) {
	foreach ( us_config( 'google-fonts' ) as $font_family => $font_options ) {
		$us_google_fonts[ $font_family ] = ! empty( $font_options[ 'variants' ] )
			? implode( ',', $font_options[ 'variants' ] )
			: '';
	}
}

// Export Google Fonts to global data object
echo '<script>
	$usof = window.$usof || { _$$data: {} };
	$usof._$$data.googleFonts = \''. json_encode( $us_google_fonts ) .'\';
	$usof.googlefontEndpoint = "' . sprintf( '%s://fonts.googleapis.com/css', is_ssl() ? 'https' : 'http' ) . '";
</script>';
