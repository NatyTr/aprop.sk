/**
 * USOF Fields: Text
 */
! function( $, undefined ) {
	"use strict";
	$( '.vc_ui-panel-window.vc_active .type_text' ).each( function() {
		( new $usof.field( this ) ).init( this );
	} );
}( jQuery );
