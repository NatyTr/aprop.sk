/**
 * USOF Field: Text / Textarea
 */
! function( $, undefined ) {

	// Private variables that are used only in the context of this function, it is necessary to optimize the code.
	var _window = window,
		_undefined = undefined;

	if ( _window.$usof === _undefined ) {
		return;
	}

	$usof.field[ 'text' ] = {
		/**
		 * Initializes the object.
		 */
		init: function() {
			var self = this;

			/**
			 * @var {{}} Bondable events.
			 */
			self._events = {
				// Note: debounce is used to get the correct value when paste text.
				changeField: $ush.debounce( self._changeField.bind( self ) ),
				setExampleValue: self._setExampleValue.bind( self ),
				syncCurrentValue: self._syncCurrentValue.bind( self ),
			};

			// Elements
			self.$text = $( 'input[type=text]', self.$row ); // text or textarea

			// Events
			self.$row
				// Handler for set the value from the example
				.on( 'click', '.usof-example', self._events.setExampleValue )
				// Handler for changes in the current text field
				.on( 'change paste keyup', 'input[type=text]', self._events.changeField );

			if ( self.hasResponsive() ) {
				// Sync value for current screen
				self.on( 'setResponsiveState', self._events.syncCurrentValue );
			}
		},

		/**
		 * Handler for set the value from the example.
		 *
		 * @private
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM.
		 */
		_setExampleValue: function( e ) {
			var self = this,
				exampleValue =  ( $( e.target ).closest( '.usof-example' ).html() || '' );

			// Set current value
			self.$text.val( exampleValue );
			self.setCurrentValue( exampleValue );
		},

		/**
		 * Handler for changes in the current text field.
		 *
		 * @private
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM.
		 */
		_changeField: function( e ) {
			this.setCurrentValue( e.currentTarget.value );
		},

		/**
		 * Sync value for current screen.
		 *
		 * @private
		 * @event handler
		 */
		_syncCurrentValue: function() {
			var self = this;
			self.$text.val( self.getCurrentValue() );
		},

		/**
		 * Set the value.
		 *
		 * @param {String} value The value to be selected.
		 * @param {Boolean} quiet Sets in quiet mode without events.
		 */
		setValue: function( value, quiet ) {
			var self = this;

			// Set current value
			self.parentSetValue( '' + value ); // set parent value
			self._syncCurrentValue();
		}
	};

	// TODO: Add support for responsive values
	$usof.field[ 'textarea' ] = {
		/**
		 * Initializes the object.
		 */
		init: function() {
			var self = this;
			// Events
			self.$row.on( 'click', '.usof-example', self._setExampleValue.bind( self ) );
			// Note: debounce is used to get the correct value when paste text
			self.$input.on( 'change paste keyup', $ush.debounce( function() {
				self.trigger( 'change', [ self.getValue() ] );
			} ) );
		},

		/**
		 * Set example value
		 *
		 * @private
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 */
		_setExampleValue: function( e ) {
			this.setValue( $( e.target ).closest( '.usof-example' ).html() || '' );
		}
	};
}( jQuery );
