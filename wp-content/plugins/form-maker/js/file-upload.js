(function () {		
	jQuery(function() {
		isOpera = (!!window.opr && !!opr.addons) || !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0;
		isFirefox = typeof InstallTrigger !== 'undefined';
		isIE = /*@cc_on!@*/false || !!document.documentMode;
		
		var onUploadChange = function ( e ) {
			var status = jQuery(this).parent().parent().find('.file-upload-status').length ? jQuery(this).parent().parent().find('.file-upload-status') : jQuery('<span class="file-upload-status"></span>');
			if ( this.value ) {
				// IE shows the whole system path, we're reducing it 
				// to the filename for consistency
				var value = '';
				var files = this.files;
				for (var i = 0; i < files.length-1; i++)
					value = value+( isIE ? files[i].name.split('\\').pop() : files[i].name)+', ';
			
				i = files.length-1;	
				value = value+( isIE ? files[i].name.split('\\').pop() : files[i].name);
				status.html(value);
				
		
				status.insertAfter( jQuery(this).parent() );	
				// Only tween if we're responding to an event
				if ( e ) { 
					status.animate({
						opacity: 1,
					}, 500, function() {
					}); 
				}  
			}
			else if ( status && status.parent().length ) {
				status.remove();
			} 
		}
		
		/* var onUploadFocus = function () { 
			jQuery(this).parent().addClass( 'focus' ); 
		};
		
		var onUploadBlur = function () { 
			jQuery(this).parent().removeClass( 'focus' ); 
		}; */
		
		jQuery( '.file-upload input[type=file]' ).each( function ( index, field ) {
			// Create a status element, and store it
			
			jQuery( field ).bind({
				/* 'focus': function() {
					onUploadFocus.call( field );
				},
				'blur': function() {
					onUploadBlur.call( field );
				}, */
				'change': function( event ) {
					onUploadChange.call( field, event );
				}
			});
			onUploadChange.call( field );
			
			// Move the file input in Firefox / Opera so that the button part is
			// in the hit area. Otherwise we get a text selection cursor
			// which you cannot override with CSS	

      if ( isFirefox || isOpera ) {
        jQuery( this ).css({'left' : '-800px'});
      }
      else if ( isIE ) {
        // Minimizes the text input part in IE
        jQuery( this ).css({'width' : '0'});
      }
		});
	});
})();