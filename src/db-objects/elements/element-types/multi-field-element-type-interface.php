<?php
/**
 * Multi-Field element type interface
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Elements\Element_Types;

use awsmug\Torro_Forms\DB_Objects\Elements\Element;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;

/**
 * Interface for element type that supports multiple fields.
 *
 * @since 1.0.0
 */
interface Multi_Field_Element_Type_Interface {

	/**
	 * Returns the slugs for the additional fields of this type.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of field slugs.
	 */
	public function get_additional_field_slugs();

	/**
	 * Returns an array representation for each additional field of an element.
	 *
	 * @since 1.0.0
	 *
	 * @param Element         $element    The element object to get the additional fields data for.
	 * @param Submission|null $submission Optional. Current submission object, if available. Default null.
	 * @param array           $choices    Optional. Array of `$field => $choices` pairs, if available. Default empty array.
	 * @param array           $settings   Optional. Array of `$setting_name => $setting_value` pairs. Default empty array.
	 * @param array           $values     Optional. Array of current values as `$field => $value` pairs. Default empty array.
	 * @return array Array including all additioinal fields information for the element type.
	 */
	public function additional_fields_to_json( $element, $submission = null, $choices = array(), $settings = array(), $values = array() );

	/**
	 * Validates additional fields for an element.
	 *
	 * @since 1.0.0
	 *
	 * @param array      $values     Associative arrays of `$field => $value` pairs.
	 * @param Element    $element    Element to validate fields for.
	 * @param Submission $submission Submission the values belong to.
	 * @return array Associative array of `$field => $validated_value` pairs. Each validated
	 *               value must be either the validated value, or an error object on failure.
	 *               It may also be an array, in which case the individual values will be stored
	 *               in the database separately. That array may also contain error objects for
	 *               cases where errors occurred.
	 */
	public function validate_additional_fields( $values, $element, $submission );
}
