<?php
/**
 * Multi-Field element type interface
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Elements\Element_Types;

use awsmug\Torro_Forms\DB_Objects\Elements\Element;

/**
 * Interface for element type that supports multiple fields.
 *
 * @since 1.0.0
 */
interface Multi_Field_Element_Type_Interface {

	/**
	 * Returns an array representation for each additional field of an element.
	 *
	 * @since 1.0.0
	 * @access public
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
	 * @access public
	 *
	 * @param array   $values  Associative arrays of `$field => $value` pairs.
	 * @param Element $element Element to validate fields for.
	 * @return array Array of validated values or error objects.
	 */
	public function validate_additional_fields( $values, $element );
}
