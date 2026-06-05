(function ( $ ) {
	"use strict";

	$(function () {

   jQuery('body').on('click','.comgate_select',function(){
    
    jQuery(this).children('.comgate_select_input').prop('checked',true);
    
   });
		
	});

}(jQuery));