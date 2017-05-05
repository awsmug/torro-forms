<?php
/**
 * Multi-Field element type interface
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Elements\Element_Types;

/**
 * Interface for element type that supports multiple fields.
 *
 * @since 1.0.0
 */
interface Multi_Field_Element_Type_Interface {

	/**
	 * Validates additional fields for an element.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array                                  $values  Associative arrays of `$field => $value` pairs.
	 * @param awsmug\Torro_Forms\DB_Objects\Elements $element Element to validate fields for.
	 * @return array Array of validated values or error objects.
	 */
	public function validate_additional_fields( $values, $element );
}
