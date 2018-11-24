( function( torro, _, wp ) {
	'use strict';

	var Toolbar = wp.media.view.Toolbar;
	var InsertElementToolbar;

	InsertElementToolbar = Toolbar.extend({
		initialize: function() {
			wp.media.view.Toolbar.prototype.initialize.apply( this, arguments );

			this.controller.state().on( 'change:selected', this.refresh, this );
		},

		refresh: function() {
			var selected = this.controller.state().get( 'selected' );

			_.each( this._views, function( button ) {
				var disabled = false;

				if ( ! button.model || ! button.options || ! button.options.requires ) {
					return;
				}

				if ( button.options.requires && button.options.requires.selected && ( ! selected || ! selected.length ) ) {
					disabled = true;
				}

				button.model.set( 'disabled', disabled );
			});
		}
	});

	torro.Builder.AddElement.View.InsertElementToolbar = InsertElementToolbar;

})( window.torro, window._, window.wp );
