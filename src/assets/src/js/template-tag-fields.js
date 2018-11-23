( function( fieldsAPI, $ ) {
	'use strict';

	function toggleTemplateTagList( $button ) {
		if ( 'true' === $button.attr( 'aria-expanded' ) ) {
			$button.attr( 'aria-expanded', 'false' );
		} else {
			$button.attr( 'aria-expanded', 'true' );
			$button.next( '.template-tag-list' ).focus();

			$( 'html' ).one( 'click', function() {
				$button.attr( 'aria-expanded', 'false' ).focus();
			});
		}
	}

	fieldsAPI.FieldView.TemplatetagtextFieldView = fieldsAPI.FieldView.extend({
		preRender: function( $el ) {
			$el.find( '.template-tag-list-toggle' ).off( 'click' );
			$el.find( '.template-tag-button' ).off( 'click' );
		},

		postRender: function( $el ) {
			var $input = $el.find( '.plugin-lib-control' );

			$el.find( '.template-tag-list-toggle' ).on( 'click', function( e ) {
				var $button = $( this );

				toggleTemplateTagList( $button );

				e.stopPropagation();
				e.preventDefault();
			});

			$el.on( 'click', '.template-tag-button', function() {
				var tag = '{' + $( this ).data( 'tag' ) + '}';

				$input.val( $input.val() + tag );
			});
		}
	});

	fieldsAPI.FieldView.TemplatetagwysiwygFieldView = fieldsAPI.FieldView.WYSIWYGFieldView.extend({
		preRender: function( $el ) {
			$el.find( '.template-tag-list-toggle' ).off( 'click' );
			$el.find( '.template-tag-button' ).off( 'click' );

			fieldsAPI.FieldView.WYSIWYGFieldView.prototype.preRender.apply( this, arguments );
		},

		postRender: function( $el ) {
			var $input = $el.find( '.plugin-lib-control' );
			var $listWrap = $el.find( '.template-tag-list-wrap' );

			$listWrap.remove();
			$input.one( 'editorInitialized', function() {
				if ( $el.find( '.wp-editor-wrap .wp-editor-tools .wp-media-buttons' ).length ) {
					$el.find( '.wp-editor-wrap .wp-editor-tools .wp-media-buttons' ).after( $listWrap );
				} else {
					$el.find( '.wp-editor-wrap .wp-editor-tools' ).prepend( $listWrap );
				}
			});

			fieldsAPI.FieldView.WYSIWYGFieldView.prototype.postRender.apply( this, arguments );

			$el.on( 'click', '.template-tag-list-toggle', function( e ) {
				var $button = $( this );

				toggleTemplateTagList( $button );

				e.stopPropagation();
				e.preventDefault();
			});

			$el.on( 'click', '.template-tag-button', function() {
				var tag = '{' + $( this ).data( 'tag' ) + '}';
				var editor = window.tinymce.get( $input.attr( 'id' ) );

				if ( editor ) {
					editor.insertContent( tag );
				}
			});
		}
	});

}( window.pluginLibFieldsAPI, window.jQuery ) );
