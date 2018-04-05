/*!
 * Torro Forms Version 1.0.0-beta.9 (http://torro-forms.com)
 * Licensed under GNU General Public License v2 (or later) (http://www.gnu.org/licenses/gpl-2.0.html)
 */
( function( torro, fieldsAPI, $, data ) {
	'use strict';

	var builder = torro.Builder.getInstance();

	function removeTemplateTagForElement( model, $list ) {
		var templateTagSlug  = data.templateTagSlug.replace( '%element_id%', model.get( 'id' ) );
		var templateTagGroup = data.templateTagGroup;
		var $tag             = $list.find( '.template-tag-' + templateTagSlug );

		if ( $tag ) {
			$tag.remove();

			if ( ! $list.find( '.template-tag-list-group-' + templateTagGroup + ' > ul > li' ).length ) {
				$list.find( '.template-tag-list-group-' + templateTagGroup ).remove();
			}
		}
	}

	function addTemplateTagForElement( model, $list ) {
		var templateTag = {
			slug: data.templateTagSlug.replace( '%element_id%', model.get( 'id' ) ),
			group: data.templateTagGroup,
			label: data.templateTagLabel.replace( '%element_label%', model.get( 'label' ) ),
			description: data.templateTagDescription.replace( '%element_label%', model.get( 'label' ) )
		};

		var templateTagGroup = {
			slug: data.templateTagGroup,
			label: data.templateTagGroupLabel
		};

		var $tag = $( data.templateTagTemplate
			.replace( /%slug%/g, templateTag.slug )
			.replace( /%group%/g, templateTag.group )
			.replace( /%label%/g, templateTag.label )
			.replace( /%description%/g, templateTag.description ) );

		var $group = $list.find( '.template-tag-list-group-' + templateTag.group + ' > ul' );

		if ( ! $group.length ) {
			$group = $( data.templateTagGroupTemplate
				.replace( /%slug%/g, templateTagGroup.slug )
				.replace( /%label%/g, templateTagGroup.label ) );

			$list.append( $group );

			$group = $group.children( 'ul' );
		}

		$group.append( $tag );

		model.on( 'change:label', function( element, label ) {
			var newLabel = data.templateTagLabel.replace( '%element_label%', label );

			$tag.html( $tag.html().replace( templateTag.label, newLabel ) );

			templateTag.label = newLabel;
		});
	}

	builder.on( 'addElement', function( model ) {
		$( '.plugin-lib-repeatable-group-email_notifications__notifications-wrap .template-tag-list' ).each( function() {
			var $list       = $( this );
			var $emailInput = $list.parents( '.template-tag-list-wrap' ).prev( 'input[type="email"]' );

			var inputTypeSetting;

			if ( $emailInput.length ) {
				inputTypeSetting = model.element_settings.findWhere({
					name: 'input_type'
				});
				if ( inputTypeSetting ) {
					if ( 'email_address' === inputTypeSetting.get( 'value' ) ) {
						addTemplateTagForElement( model, $list );
					}

					inputTypeSetting.on( 'change:value', function( setting, value ) {
						if ( 'email_address' === value ) {
							addTemplateTagForElement( model, $list );
						} else {
							removeTemplateTagForElement( model, $list );
						}
					});
				}
			} else {
				addTemplateTagForElement( model, $list );
			}
		});
	});

	builder.on( 'removeElement', function( model ) {
		$( '.plugin-lib-repeatable-group-email_notifications__notifications-wrap .template-tag-list' ).each( function() {
			var $list       = $( this );

			removeTemplateTagForElement( model, $list );
		});
	});

}( window.torro, window.pluginLibFieldsAPI, window.jQuery, window.torroEmailNotifications ) );
