<?php
/**
 * Non-input element type trait
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Elements\Element_Types;

use awsmug\Torro_Forms\DB_Objects\Elements\Element;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use WP_Error;

/**
 * Trait for element type that does not expect any input.
 *
 * @since 1.0.0
 */
trait Non_Input_Element_Type_Trait {

	/**
	 * Returns the current values for the element fields, optionally for a specific submission.
	 *
	 * @since 1.0.0
	 *
	 * @param Element         $element    The element object to get values for.
	 * @param Submission|null $submission Optional. Submission to get the values from, if available. Default null.
	 * @return array Associative array of `$field => $value` pairs, with the main element field having the key '_main'.
	 */
	public function get_values( $element, $submission = null ) {
		return array();
	}

	/**
	 * Formats values for an export.
	 *
	 * @since 1.0.0
	 *
	 * @param array   $values        Associative array of `$field => $value` pairs, with the main element field having the key '_main'.
	 * @param Element $element       Element the values belong to.
	 * @param string  $export_format Export format identifier. May be 'xls', 'csv', 'json', 'xml' or 'html'.
	 * @return array Associative array of `$column_slug => $column_value` pairs. The number of items and the column slugs
	 *               must match those returned from the get_export_columns() method.
	 */
	public function format_values_for_export( $values, $element, $export_format ) {
		return array();
	}

	/**
	 * Gets the columns required for an export.
	 *
	 * @since 1.0.0
	 *
	 * @param Element $element Element to export columns for.
	 * @return array Associative array of `$column_slug => $column_label` pairs.
	 */
	public function get_export_columns( $element ) {
		return array();
	}

	/**
	 * Validates a field value for an element.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed      $value      The value to validate. It is already unslashed when it arrives here.
	 * @param Element    $element    Element to validate the field value for.
	 * @param Submission $submission Submission the value belongs to.
	 * @return mixed|WP_Error Validated value, or error object on failure.
	 */
	public function validate_field( $value, $element, $submission ) {
		return $this->create_error( 'value_not_accepted', __( 'No values are accepted here.', 'torro-forms' ) );
	}

	/**
	 * Gets the fields arguments for an element of this type when editing submission values in the admin.
	 *
	 * @since 1.0.0
	 *
	 * @param Element $element Element to get fields arguments for.
	 * @return array An associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_edit_submission_fields_args( $element ) {
		return array();
	}
}
