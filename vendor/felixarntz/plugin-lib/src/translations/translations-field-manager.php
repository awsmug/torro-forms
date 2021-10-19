<?php
/**
 * Translations for the Field_Manager class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Translations;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Translations\Translations_Field_Manager' ) ) :

	/**
	 * Translations for the Field_Manager class.
	 *
	 * @since 1.0.0
	 */
	class Translations_Field_Manager extends Translations {
		/**
		 * Initializes the translation strings.
		 *
		 * @since 1.0.0
		 */
		protected function init() {
			$this->translations = array(
				'field_required_indicator'               => $this->_xtranslate( '(required)', 'field required indicator', 'textdomain' ),
				/* translators: %s: field label */
				'field_empty_required'                   => $this->__translate( 'No value was given for the required field &#8220;%s&#8221;.', 'textdomain' ),
				/* translators: %s: field label */
				'field_repeatable_not_array'             => $this->__translate( 'The value for the repeatable field &#8220;%s&#8221; is not an array.', 'textdomain' ),
				/* translators: %s: field label */
				'field_repeatable_has_errors'            => $this->__translate( 'One or more errors occurred for the repeatable field &#8220;%s&#8221;.', 'textdomain' ),
				/* translators: %s: field label */
				'field_repeatable_add_button'            => $this->__translate( 'Add<span class="screen-reader-text"> another item to the &#8220;%s&#8221; list</span>', 'textdomain' ),
				/* translators: %s: field label */
				'field_repeatable_remove_button'         => $this->__translate( 'Remove<span class="screen-reader-text"> this item from the &#8220;%s&#8221; list</span>', 'textdomain' ),
				/* translators: 1: incorrect value, 2: field label, 3: minimum length */
				'field_text_too_short'                   => $this->_n_nooptranslate( 'The value &#8220;%1$s&#8221; for the field &#8220;%2$s&#8221; is shorter than the allowed minimum of %3$s character.', 'The value &#8220;%1$s&#8221; for the field &#8220;%2$s&#8221; is shorter than the allowed minimum %3$s characters.', 'textdomain' ),
				/* translators: 1: incorrect value, 2: field label, 3: maximum length */
				'field_text_too_long'                    => $this->_n_nooptranslate( 'The value &#8220;%1$s&#8221; for the field &#8220;%2$s&#8221; is longer than the allowed maximum of %3$s character.', 'The value &#8220;%1$s&#8221; for the field &#8220;%2$s&#8221; is longer than the allowed maximum of %3$s characters.', 'textdomain' ),
				/* translators: 1: incorrect value, 2: field label, 3: regex pattern */
				'field_text_no_pattern_match'            => $this->__translate( 'The value &#8220;%1$s&#8221; for the field &#8220;%2$s&#8221; does not match the pattern %3$s.', 'textdomain' ),
				/* translators: 1: incorrect value, 2: field label */
				'field_email_invalid'                    => $this->__translate( 'The value &#8220;%1$s&#8221; for the field &#8220;%2$s&#8221; is not a valid email address.', 'textdomain' ),
				/* translators: 1: incorrect value, 2: field label */
				'field_url_invalid'                      => $this->__translate( 'The value &#8220;%1$s&#8221; for the field &#8220;%2$s&#8221; is not a valid URL.', 'textdomain' ),
				/* translators: 1: incorrect value, 2: field label, 3: minimum allowed value */
				'field_number_lower_than'                => $this->__translate( 'The value %1$s for the field &#8220;%2$s&#8221; is lower than the required minimum of %3$s.', 'textdomain' ),
				/* translators: 1: incorrect value, 2: field label, 3: maximum allowed value */
				'field_number_greater_than'              => $this->__translate( 'The value %1$s for the field &#8220;%2$s&#8221; is greater than the required maximum of %3$s.', 'textdomain' ),
				/* translators: 1: incorrect value, 2: field label */
				'field_select_invalid'                   => $this->__translate( 'The value &#8220;%1$s&#8221; for the field &#8220;%2$s&#8221; is not one of the available choices.', 'textdomain' ),
				/* translators: 1: comma-separated list of values, 2: field label */
				'field_select_invalid_multi'             => $this->__translate( 'Some of the values &#8220;%1$s&#8221; for the field &#8220;%2$s&#8221; are not part of the available choices.', 'textdomain' ),
				/* translators: %s: field label */
				'field_autocomplete_missing_label_route' => $this->__translate( 'No REST route for retrieving the label was specified for the field &#8220;%s&#8221;.', 'textdomain' ),
				/* translators: 1: incorrect value, 2: field label */
				'field_autocomplete_invalid_value'       => $this->__translate( 'The value %1$s for the field &#8220;%2$s&#8221; is invalid.', 'textdomain' ),
				/* translators: 1: date format string, 2: time format string */
				'field_datetime_format_concat'           => $this->_xtranslate( '%1$s %2$s', 'concatenating date and time format', 'textdomain' ),
				/* translators: 1: incorrect value, 2: field label, 3: minimum allowed value */
				'field_datetime_lower_than'              => $this->__translate( 'The value %1$s for the field &#8220;%2$s&#8221; is lower than the required minimum of %3$s.', 'textdomain' ),
				/* translators: 1: incorrect value, 2: field label, 3: maximum allowed value */
				'field_datetime_greater_than'            => $this->__translate( 'The value %1$s for the field &#8220;%2$s&#8221; is greater than the required maximum of %3$s.', 'textdomain' ),
				/* translators: 1: incorrect value, 2: field label */
				'field_color_invalid_format'             => $this->__translate( 'The value %1$s for the field &#8220;%2$s&#8221; is not a valid color format.', 'textdomain' ),
				'field_cannot_update'                    => $this->__translate( 'Could not update the value/s in the database.', 'textdomain' ),
				'field_media_add_button'                 => $this->_xtranslate( 'Choose a File', 'media button', 'textdomain' ),
				'field_media_replace_button'             => $this->_xtranslate( 'Choose another File', 'media button', 'textdomain' ),
				'field_media_remove_button'              => $this->_xtranslate( 'Remove', 'media button', 'textdomain' ),
				'field_media_modal_heading'              => $this->_xtranslate( 'Choose a File', 'media modal heading', 'textdomain' ),
				'field_media_modal_button'               => $this->_xtranslate( 'Insert File', 'media modal button', 'textdomain' ),
				/* translators: 1: incorrect value, 2: field label */
				'field_media_invalid_url'                => $this->__translate( 'The URL %1$s for the field &#8220;%2$s&#8221; does not point to a WordPress media file.', 'textdomain' ),
				/* translators: 1: incorrect value, 2: field label */
				'field_media_invalid_post_type'          => $this->__translate( 'The post with ID %1$s referenced in the field &#8220;%2$s&#8221; is not a valid WordPress media file.', 'textdomain' ),
				/* translators: 1: incorrect value, 2: field label, 3: comma-separated list of valid formats */
				'field_media_invalid_mime_type'          => $this->__translate( 'The media item with ID %1$s referenced in the field &#8220;%2$s&#8221; is neither of the valid formats (%3$s).', 'textdomain' ),
			);
		}
	}

endif;
