/*
 * WP Media Picker - version 0.7.2
 *
 * Felix Arntz <felix-arntz@leaves-and-love.net>
 */

( function( $, wp, _ ) {

	var Select;
	var MediaPickerFrame;
	var MediaPicker;

	if ( 'undefined' === typeof wp || 'undefined' === typeof wp.media ) {
		// if wp.media is not loaded, scaffold the jQuery plugin function and abort
		$.fn.wpMediaPicker = function() {
			return this;
		};

		console.error( 'WP Media not found' );
		return;
	}

	/**
	 * Gets an attachment object via AJAX based on a given ID or URL.
	 *
	 * @since 0.7.0
	 *
	 * @private
	 *
	 * @param {number|string} val        Attachment ID or URL.
	 * @param {string}        valType    Whether 'val' was provided as 'id' or 'url'.
	 * @param {function}      onFound    Callback to pass the found attachment to.
	 * @param {function}      onNotFound Callback to run if no attachment was found.
	 *
	 * @return {void}
	 */
	function getAttachment( val, valType, onFound, onNotFound ) {
		var requestData;

		if ( ! val ) {
			onNotFound();
			return;
		}

		if ( 'url' === valType ) {
			requestData = {
				action: 'get-attachment-by-url',
				url: val
			};
		} else {
			requestData = {
				action: 'get-attachment',
				id: parseInt( val, 10 )
			};
		}

		wp.media.ajax({
			type: 'POST',
			data: requestData,
			success: onFound,
			error: onNotFound
		});
	}

	Select = wp.media.view.MediaFrame.Select;

	/**
	 * Custom media picker frame for selecting uploaded media.
	 *
	 * @since 0.7.0
	 *
	 * @class MediaPickerFrame
	 * @constructor
	 */
	MediaPickerFrame = Select.extend({

		/**
		 * Initializes the media frame.
		 *
		 * @since 0.7.0
		 *
		 * @returns {void}
		 */
		initialize: function() {
			_.defaults( this.options, {
				query: {},
				multiple: false,
				editable: true,
				filterable: 'all',
				searchable: true,
				displaySettings: false,
				displayUserSettings: false,
				editing: false,
				state: 'insert',
				metadata: {}
			});

			Select.prototype.initialize.apply( this, arguments );
		},

		/**
		 * Creates the default states.
		 *
		 * @since 0.7.0
		 *
		 * @returns {void}
		 */
		createStates: function() {
			this.states.add([
				new wp.media.controller.Library({
					id: 'insert',
					title: this.options.title,
					selection: this.options.selection,
					priority: 20,
					toolbar: 'main-insert',
					filterable: this.options.filterable,
					searchable: this.options.searchable,
					library: wp.media.query( this.options.query ),
					multiple: this.options.multiple,
					editable: this.options.editable,
					displaySettings: this.options.displaySettings,
					displayUserSettings: this.options.displayUserSettings
				}),

				new wp.media.controller.EditImage({ model: this.options.editImage })
			]);
		},

		/**
		 * Binds the necessary subview handlers.
		 *
		 * @since 0.7.0
		 *
		 * @returns {void}
		 */
		bindHandlers: function() {
			Select.prototype.bindHandlers.apply( this, arguments );

			this.on( 'toolbar:create:main-insert', this.createToolbar, this );

			this.on( 'content:render:edit-image', this.renderEditImageContent, this );
			this.on( 'toolbar:render:main-insert', this.renderMainInsertToolbar, this );
		},

		/**
		 * Renders the edit image content.
		 *
		 * @since 0.7.0
		 *
		 * @returns {void}
		 */
		renderEditImageContent: function() {
			var view = new wp.media.view.EditImage({
				controller: this,
				model: this.state().get( 'image' )
			}).render();

			this.content.set( view );

			view.loadEditor();
		},

		/**
		 * Renders the main insert toolbar.
		 *
		 * @since 0.7.0
		 *
		 * @param {wp.Backbone.View} view Toolbar view.
		 *
		 * @returns {void}
		 */
		renderMainInsertToolbar: function( view ) {
			var controller = this;

			view.set( 'insert', {
				style: 'primary',
				priority: 80,
				text: controller.options.buttonText,
				requires: { selection: true },
				click: function() {
					controller.close();
					controller.state().trigger( 'insert', controller.state().get( 'selection' ) ).reset();
				}
			});
		}
	});

	/**
	 * Media picker jQuery widget.
	 *
	 * @since 0.1.0
	 *
	 * @class $.widget.wp.wpMediaPicker
	 * @constructor
	 */
	MediaPicker = {
		options: {
			store: 'id',
			query: {},
			multiple: false,
			filterable: 'all',
			searchable: true,
			editable:   false,
			displaySettings: false,
			displayUserSettings: false,
			change: false,
			clear: false,
			label_add: wp.media.view.l10n.addMedia,
			label_replace: wp.media.view.l10n.replace,
			label_remove: wp.media.view.l10n.remove,
			label_modal: wp.media.view.l10n.addMedia,
			label_button: wp.media.view.l10n.addMedia
		},

		/**
		 * Creates the media picker markup and initializes the media frame for the element.
		 *
		 * @since 0.1.0
		 *
		 * @returns {void}
		 */
		_create: function() {
			var self = this;

			$.extend( self.options, self.element.data() );

			// Use the 'type' query attribute in favor of 'post_mime_type'.
			if ( self.options.query && self.options.query.post_mime_type ) {
				self.options.query.type = self.options.query.post_mime_type;
				delete self.options.query.post_mime_type;
			}

			self._content_id = 'wp-mediapicker-content-' + self.element.attr( 'id' );

			self.element.hide().wrap( '<div class="wp-mediapicker-container" />' );

			self._wrap          = self.element.parent();
			self._open_button   = $( '<button type="button" class="wp-mediapicker-open-button button" />' ).insertAfter( self.element );
			self._remove_button = $( '<button type="button" class="wp-mediapicker-remove-button button-link button-link-delete" />' ).hide().insertAfter( self._open_button ).text( self.options.label_remove );
			self._content_wrap  = $( '<div class="wp-mediapicker-content-wrap" />' ).insertAfter( self._remove_button );
			self._content       = $( '<div class="wp-mediapicker-content" />' ).appendTo( self._content_wrap ).attr( 'id', self._content_id );

			self._frame = new MediaPickerFrame({
				title: self.options.label_modal,
				buttonText: self.options.label_button,
				frame: 'select',
				state: 'insert',
				selection: new wp.media.model.Selection( [], {
					multiple: self.options.multiple
				}),
				query: self.options.query,
				multiple: self.options.multiple,
				filterable: self.options.filterable,
				searchable: self.options.searchable,
				editable: self.options.editable
			});

			self._setValue( self.element.val() );

			self._addListeners();
		},

		/**
		 * Adds event listeners for the media picker buttons and the media frame inserter.
		 *
		 * @since 0.1.0
		 *
		 * @returns {void}
		 */
		_addListeners: function() {
			var self = this;

			self._frame.on( 'insert', function() {
				var selection   = self._frame.state().get( 'selection' );
				var attachments = selection.models.map( function( model ) {
					return _.extend( {}, model.toJSON() );
				});
				var attachment  = _.extend( {}, selection.first().toJSON() );

				self._setAttachment( attachment );

				$( document ).trigger( 'wpMediaPicker.insert', [ attachments, self ] );
			});

			self._open_button.on( 'click', function() {
				var selection = self._frame.state( 'insert' ).get( 'selection' );
				selection.reset( self._attachment ? [ self._attachment ] : [] );

				self.open();
			});

			self._remove_button.on( 'click', function() {
				self._setAttachment( null );
			});
		},

		/**
		 * Creates the preview content markup for a given attachment.
		 *
		 * @since 0.1.0
		 *
		 * @param {object} attachment WordPress attachment data.
		 *
		 * @returns {void}
		 */
		_createContent: function( attachment ) {
			var self = this;

			self._attachment = attachment;

			self._open_button.text( self.options.label_replace );
			self._remove_button.show();

			var preview_content = '';
			if ( 'video' === attachment.type ) {
				// for video attachments, show the video player, optionally with the poster
				var poster = '';
				if ( attachment.image && attachment.image.src !== attachment.icon ) {
					poster = attachment.image.src;
				}
				preview_content += '<video class="wp-video-shortcode" preload="metadata"' + ( poster ? ' poster="' + poster + '"' : '' ) + ' controls><source type="' + attachment.mime + '" src="' + attachment.url + '" /></video>';
			} else if ( 'audio' === attachment.type ) {
				// for audio attachments, show the audio player, with either the cover or the mime type icon
				if ( attachment.image && attachment.image.src && attachment.image.src !== attachment.icon ) {
					preview_content += '<img class="wp-audio-cover" src="' + attachment.image.src + '" alt="' + attachment.filename + '" />';
				} else {
					preview_content += '<div class="mime-type-icon"><img src="' + attachment.icon + '" /><span>' + attachment.filename + '</span></div>';
				}
				preview_content += '<audio class="wp-audio-shortcode" width="100%" preload="none" controls><source type="' + attachment.mime + '" src="' + attachment.url + '" /></audio>';
			} else {
				var src = 'image' === attachment.type ? attachment.url : undefined;
				if ( attachment.sizes ) {
					if ( attachment.sizes.large ) {
						src = attachment.sizes.large.url;
					} else if ( attachment.sizes.full ) {
						src = attachment.sizes.full.url;
					}
				}

				if ( src ) {
					preview_content += '<img src="' + src + '" alt="' + attachment.alt + '" />';
				} else {
					preview_content += '<div class="mime-type-icon"><img src="' + attachment.icon + '" /><span>' + attachment.filename + '</span></div>';
				}
			}

			if ( 0 <= preview_content.search( '<img ' ) ) {
				self._content.addClass( 'size-auto' );
			} else {
				self._content.removeClass( 'size-auto' );
			}

			self._content.show().html( preview_content );
		},

		/**
		 * Resets the preview content markup.
		 *
		 * @since 0.1.0
		 *
		 * @returns {void}
		 */
		_resetContent: function() {
			var self = this;

			self._attachment = null;

			self._open_button.text( self.options.label_add );
			self._remove_button.hide();
			self._content.hide().empty().removeClass( 'size-auto' );
		},

		/**
		 * Gets the currently selected attachment.
		 *
		 * @since 0.7.0
		 *
		 * @returns {object} WordPress attachment data, or null.
		 */
		_getAttachment: function() {
			return this._attachment;
		},

		/**
		 * Sets the currently selected attachment.
		 *
		 * If the attachment is different from the current attachment,
		 * change events will be triggered.
		 *
		 * @since 0.7.0
		 *
		 * @param {object} attachment WordPress attachment data.
		 *
		 * @returns {void}
		 */
		_setAttachment: function( attachment ) {
			var noChange = ( attachment && this._attachment && attachment.id === this._attachment.id ) || ( ! attachment && ! this._attachment );

			if ( ! attachment ) {
				this._resetContent();

				if ( noChange ) {
					return;
				}

				this.element.val( null );
				this.element.trigger( 'change' );

				if ( 'function' === typeof this.options.clear ) {
					this.options.clear.call( this );
				}

				$( document ).trigger( 'wpMediaPicker.updateField', [ null, this ] );
				return;
			}

			this._createContent( attachment );

			if ( noChange ) {
				return;
			}

			if ( 'url' === this.options.store ) {
				this.element.val( attachment.url );
			} else {
				this.element.val( attachment.id );
			}
			this.element.trigger( 'change' );

			if ( 'function' === typeof this.options.change ) {
				this.options.change.call( this );
			}

			$( document ).trigger( 'wpMediaPicker.updateField', [ attachment, this ] );
		},

		/**
		 * Gets the current value of the element.
		 *
		 * This is not the attachment, but the ID or URL depending on the store option.
		 *
		 * @since 0.7.0
		 *
		 * @returns {number|string} Attachment ID, URL, or empty string if no attachment selected.
		 */
		_getValue: function() {
			if ( ! this._attachment ) {
				return '';
			}

			if ( 'url' === this.options.store ) {
				return this._attachment.url;
			}

			return this._attachment.id;
		},

		/**
		 * Sets the current value of the element.
		 *
		 * This is not the attachment, but the ID or URL depending on the store option.
		 *
		 * @since 0.7.0
		 *
		 * @param {number|string} val Attachment ID, URL, or empty string if no attachment selected.
		 *
		 * @returns {void}
		 */
		_setValue: function( val ) {
			var self = this;

			getAttachment(
				val,
				self.options.store,
				function( attachment ) {
					self._setAttachment( attachment );
				},
				function() {
					self._setAttachment( null );
				}
			);
		},

		/**
		 * Opens the media picker frame.
		 *
		 * @since 0.1.0
		 *
		 * @returns {void}
		 */
		open: function() {
			wp.media.frame = this._frame;

			this._frame.open();
			this._frame.$el.find( '.media-frame-menu .media-menu-item.active' ).focus();
		},

		/**
		 * Closes the media picker frame.
		 *
		 * @since 0.1.0
		 *
		 * @returns {void}
		 */
		close: function() {
			this._frame.close();
		},

		/**
		 * Gets or sets the currently selected attachment.
		 *
		 * @since 0.1.0
		 *
		 * @param {object|undefined} attachment WordPress attachment data, or undefined to get the attachment.
		 *
		 * @returns {void|object} WordPress attachment data if used as a getter.
		 */
		attachment: function( attachment ) {
			if ( 'undefined' === typeof attachment ) {
				return this._getAttachment();
			}

			this._setAttachment( attachment );
		},

		/**
		 * Gets or sets the current value of the element.
		 *
		 * @since 0.1.0
		 *
		 * @param {number|string|undefined} val Attachment ID, URL, or undefined to get the value.
		 *
		 * @returns {void|number|string} Attachment ID or URL if used as a getter.
		 */
		value: function( val ) {
			if ( 'undefined' === typeof val ) {
				return this._getValue();
			}

			this._setValue( val );
		},

		/**
		 * Gets the media frame instance used by the media picker.
		 *
		 * @since 0.7.0
		 *
		 * @returns {MediaPickerFrame} Media picker frame instance.
		 */
		frame: function() {
			return this._frame;
		}
	};

	$.widget( 'wp.wpMediaPicker', MediaPicker );

}( jQuery, wp, _ ) );
