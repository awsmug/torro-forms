<?php
/**
 * Translations for the field manager class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Translations;

use Leaves_And_Love\Plugin_Lib\Translations\Translations_Field_Manager as Translations_Field_Manager_Base;

/**
 * Translations for the field manager class.
 *
 * @since 1.0.0
 */
class Translations_Field_Manager extends Translations_Field_Manager_Base {

	/**
	 * Initializes the translation strings.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->translations = array(
			'field_required_indicator'               => _x( '(required)', 'field required indicator', 'torro-forms' ),
			/* translators: %s: field label */
			'field_empty_required'                   => __( 'No value was given for the required field &#8220;%s&#8221;.', 'torro-forms' ),
			/* translators: %s: field label */
			'field_repeatable_not_array'             => __( 'The value for the repeatable field &#8220;%s&#8221; is not an array.', 'torro-forms' ),
			/* translators: %s: field label */
			'field_repeatable_has_errors'            => __( 'One or more errors occurred for the repeatable field &#8220;%s&#8221;.', 'torro-forms' ),
			/* translators: %s: field label */
			'field_repeatable_add_button'            => __( 'Add<span class="screen-reader-text"> another item to the &#8220;%s&#8221; list</span>', 'torro-forms' ),
			/* translators: %s: field label */
			'field_repeatable_remove_button'         => __( 'Remove<span class="screen-reader-text"> this item from the &#8220;%s&#8221; list</span>', 'torro-forms' ),
			/* translators: 1: incorrect value, 2: field label, 3: character limit */
			'field_text_too_long'                    => _n_noop( 'The value &#8220;%1$s&#8221; for the field &#8220;%2$s&#8221; is longer than the allowed limit of %3$s character.', 'The value &#8220;%1$s&#8221; for the field &#8220;%2$s&#8221; is longer than the allowed limit of %3$s characters.', 'torro-forms' ),
			/* translators: 1: incorrect value, 2: field label, 3: regex pattern */
			'field_text_no_pattern_match'            => __( 'The value &#8220;%1$s&#8221; for the field &#8220;%2$s&#8221; does not match the pattern %3$s.', 'torro-forms' ),
			/* translators: 1: incorrect value, 2: field label */
			'field_email_invalid'                    => __( 'The value &#8220;%1$s&#8221; for the field &#8220;%2$s&#8221; is not a valid email address.', 'torro-forms' ),
			/* translators: 1: incorrect value, 2: field label */
			'field_url_invalid'                      => __( 'The value &#8220;%1$s&#8221; for the field &#8220;%2$s&#8221; is not a valid URL.', 'torro-forms' ),
			/* translators: 1: incorrect value, 2: field label, 3: minimum allowed value */
			'field_number_lower_than'                => __( 'The value %1$s for the field &#8220;%2$s&#8221; is lower than the required minimum of %3$s.', 'torro-forms' ),
			/* translators: 1: incorrect value, 2: field label, 3: maximum allowed value */
			'field_number_greater_than'              => __( 'The value %1$s for the field &#8220;%2$s&#8221; is greater than the required maximum of %3$s.', 'torro-forms' ),
			/* translators: 1: incorrect value, 2: field label */
			'field_select_invalid'                   => __( 'The value &#8220;%1$s&#8221; for the field &#8220;%2$s&#8221; is not one of the available choices.', 'torro-forms' ),
			/* translators: 1: comma-separated list of values, 2: field label */
			'field_select_invalid_multi'             => __( 'Some of the values &#8220;%1$s&#8221; for the field &#8220;%2$s&#8221; are not part of the available choices.', 'torro-forms' ),
			/* translators: %s: field label */
			'field_autocomplete_missing_label_route' => __( 'No REST route for retrieving the label was specified for the field &#8220;%s&#8221;.', 'torro-forms' ),
			/* translators: 1: incorrect value, 2: field label */
			'field_autocomplete_invalid_value'       => __( 'The value %1$s for the field &#8220;%2$s&#8221; is invalid.', 'torro-forms' ),
			/* translators: 1: date format string, 2: time format string */
			'field_datetime_format_concat'           => _x( '%1$s %2$s', 'concatenating date and time format', 'torro-forms' ),
			/* translators: 1: incorrect value, 2: field label, 3: minimum allowed value */
			'field_datetime_lower_than'              => __( 'The value %1$s for the field &#8220;%2$s&#8221; is lower than the required minimum of %3$s.', 'torro-forms' ),
			/* translators: 1: incorrect value, 2: field label, 3: maximum allowed value */
			'field_datetime_greater_than'            => __( 'The value %1$s for the field &#8220;%2$s&#8221; is greater than the required maximum of %3$s.', 'torro-forms' ),
			/* translators: 1: incorrect value, 2: field label */
			'field_color_invalid_format'             => __( 'The value %1$s for the field &#8220;%2$s&#8221; is not a valid color format.', 'torro-forms' ),
			'field_cannot_update'                    => __( 'Could not update the value/s in the database.', 'torro-forms' ),
			'field_media_add_button'                 => _x( 'Choose a File', 'media button', 'torro-forms' ),
			'field_media_replace_button'             => _x( 'Choose another File', 'media button', 'torro-forms' ),
			'field_media_remove_button'              => _x( 'Remove', 'media button', 'torro-forms' ),
			'field_media_modal_heading'              => _x( 'Choose a File', 'media modal heading', 'torro-forms' ),
			'field_media_modal_button'               => _x( 'Insert File', 'media modal button', 'torro-forms' ),
			/* translators: 1: incorrect value, 2: field label */
			'field_media_invalid_url'                => __( 'The URL %1$s for the field &#8220;%2$s&#8221; does not point to a WordPress media file.', 'torro-forms' ),
			/* translators: 1: incorrect value, 2: field label */
			'field_media_invalid_post_type'          => __( 'The post with ID %1$s referenced in the field &#8220;%2$s&#8221; is not a valid WordPress media file.', 'torro-forms' ),
			/* translators: 1: incorrect value, 2: field label, 3: comma-separated list of valid formats */
			'field_media_invalid_mime_type'          => __( 'The media item with ID %1$s referenced in the field &#8220;%2$s&#8221; is neither of the valid formats (%3$s).', 'torro-forms' ),
		);
	}
}
