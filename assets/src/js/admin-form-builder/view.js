( function( torroBuilder, $, _ ) {
	'use strict';

	/**
	 * The form builder view.
	 *
	 * @class
	 *
	 * @param {object} attributes Element type attributes.
	 */
	function View( $el, form, options ) {
		this.$el = $el;

		this.form = form;
		this.options = options || {};
	}

	_.extend( View.prototype, {
		initialize: function() {
			console.log( this.form );

			// TODO.
		}

		// TODO: functions here.
	});

	torroBuilder.View = View;

})( window.torro.Builder, window.jQuery, window._ );
