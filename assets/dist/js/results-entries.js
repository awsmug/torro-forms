/*!
 * Torro Forms Version 1.0.0-beta.3 (http://torro-forms.com)
 * Licensed under GNU General Public License v3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
( function( exports, wp, $, translations ) {
	/**
	 * Form_Builder constructor
	 */
	function Result_Entries( translations ) {
		this.translations = translations;

		this.selectors = {
			show_entry: '.torro-show-entry',
			hide_entry: '.torro-hide-entry',
			entries: '#torro-entries',
			entries_table: '#torro-entries-table',
			entry: '#torro-entry',
			entries_slider: '#torro-entries .torro-slider',
			entries_slider_start_content: '#torro-entries .torro-slider-middle',
			entries_slider_left: '#torro-entries .torro-slider-left',
			entries_slider_middle: '#torro-entries .torro-slider-middle',
			entries_slider_right: '#torro-entries .torro-slider-right',
			entries_nav: '.torro-nav-button'
		};
	}

	/**
	 * Torro_Entry class
	 */
	Result_Entries.prototype = {
		init: function() {
			this.init_show_entry();
			this.init_hide_entry();
			this.init_nav_link();
			this.init_results_deletion();
		},

		/**
		 * Shows clicked Entry
		 */
		init_show_entry: function() {
			var self = this;

			$( this.selectors.entries ).on( 'click', this.selectors.show_entry, function( e ) {
				var $button = $( this );

				e.preventDefault();

				if ( $button.hasClass( 'button' ) ) {
					$button.addClass( 'button-loading' );

					wp.ajax.post( 'torro_show_entry', {
						nonce: self.translations.nonce_show_entry,
						form_id: self.get_form_id(),
						result_id: $button.attr( 'rel' )
					}).done( function( response ) {
						$( self.selectors.entries_slider_middle ).animate({marginLeft: "-100%"});
						$( self.selectors.entries_slider_right ).html( response.html );
						$( self.selectors.entries_slider_right ).show();
						$( self.selectors.entries_slider_right ).animate({marginLeft: "0"});

						$button.removeClass('button-loading');

						self.init_hide_entry();
					}).fail( function( message ) {
						console.log( message );
					});
				} else {
					$button.addClass('button');
				}
			});
		},

		init_hide_entry: function() {
			var self = this;

			$( this.selectors.entries ).on( 'click', this.selectors.hide_entry, function( e ) {
				e.preventDefault();

				$( self.selectors.entries_slider_middle ).animate({ marginLeft: "0" });
				$( self.selectors.entries_slider_right ).animate({ marginLeft: "100%" });
				$( self.selectors.entries_slider_right ).hide();
			});
		},

		init_nav_link: function() {
			var self = this;

			$( this.selectors.entries ).on( 'click', this.selectors.entries_nav, function( e ) {
				var $button = $(this);
				$button.addClass('button-loading');

				e.preventDefault();

				var url = $( this ).attr( 'href' );

				wp.ajax.post( 'torro_show_entries', {
					nonce: self.translations.nonce_show_entries,
					form_id: self.get_form_id(),
					start: self.get_url_param_value( url, 'torro-entries-start' ),
					length: self.get_url_param_value( url, 'torro-entries-length' )
				}).done( function( response ) {
					$( self.selectors.entries_slider_start_content ).fadeOut( 500, function() {
						$( self.selectors.entries_slider_start_content ).html( response.html );
						$( self.selectors.entries_slider_start_content ).fadeIn( 500 );
					});

					$button.removeClass('button-loading');
				}).fail( function( message ) {
					console.log( message );
				});
			});
		},

		init_results_deletion: function() {
			var self = this;

			$( document ).on( 'torro.delete_results', function( e, data ) {
				$( self.selectors.entries_slider_middle ).html( data.html );
				$( self.selectors.entries_slider_middle ).animate({ marginLeft: "0" });
				$( self.selectors.entries_slider_right ).animate({ marginLeft: "100%" }).hide();
			});
		},

		/**
		 * Returns the current form ID
		 */
		get_form_id: function() {
			return $( '#post_ID' ).val();
		},

		/**
		 * Gets URL Param
		 */
		get_url_param_value: function( url, param ) {
			var variables = url.split( '?' );
			variables = variables[1];
			variables = variables.split( '&' );

			for ( var i = 0; i < variables.length; i++ ) {
				var param_name = variables[ i ].split( '=' );

				if ( param_name[0] == param ) {
					return param_name[1];
				}
			}
		}
	};

	exports.add_extension( 'result_entries', new Result_Entries( translations ) );
}( form_builder, wp, jQuery, translation_entries ) );
