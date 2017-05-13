/*!
 * Torro Forms Version 1.0.0-beta.8 (http://torro-forms.com)
 * Licensed under GNU General Public License v3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
window.torro = window.torro || {};

( function( torro, $, _, Backbone, wp, i18n ) {
	'use strict';

	var instanceCount = 0,
		callbacks = {};

	function Builder( selector ) {
		instanceCount++;
		callbacks[ 'builder' + instanceCount ] = [];

		this.instanceNumber = instanceCount;
		this.$el = $( selector );
	}

	_.extend( Builder.prototype, {
		init: function() {
			if ( ! this.$el.length ) {
				console.error( i18n.couldNotInitCanvas );
				return;
			}

			torro.api.init()
				.done( _.bind( function() {
					( new torro.api.models.Form({
						id: parseInt( $( '#post_ID' ).val(), 10 )
					}) ).fetch({
						data: { _embed: true },
						context: this,
						success: function( form ) {
							$( document ).ready( _.bind( function() {
								console.log( form );

								for ( var i in callbacks[ 'builder' + this.instanceCount ] ) {
									callbacks[ 'builder' + this.instanceCount ][ i ]( this );
								}

								delete callbacks[ 'builder' + this.instanceCount ];
							}, this ) );
						}
					});
				}, this ) )
				.fail( _.bind( function() {
					$( document ).ready( _.bind( function() {
						this.fail( i18n.couldNotLoadData );
					}, this ) );
				}, this ) );
		},

		onLoad: function( callback ) {
			if ( _.isUndefined( callbacks[ 'builder' + this.instanceCount ] ) ) {
				callback( this );
				return;
			}

			callbacks[ 'builder' + this.instanceCount ].push( callback );
		},

		fail: function( message ) {
			var compiled = torro.template( 'failure' );

			this.$el.find( '.drag-drop-area' ).addClass( 'is-empty' ).html( compiled({ message: message }) );
		}
	});

	var builder = new Builder( '#torro-form-canvas' );
	builder.init();

	torro.Builder = Builder;
	torro.Builder.instance = function() {
		return builder;
	};

}( window.torro, window.jQuery, window._, window.Backbone, window.wp, window.torroBuilderI18n ) );
