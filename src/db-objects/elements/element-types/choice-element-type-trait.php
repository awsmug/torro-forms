<?php
/**
 * Choice element type trait
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Elements\Element_Types;

use awsmug\Torro_Forms\DB_Objects\Elements\Element;

/**
 * Trait for element type that support choices.
 *
 * @since 1.0.0
 */
trait Choice_Element_Type_Trait {

	/**
	 * Returns the available choices.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Element $element Element to get choices for.
	 * @return array Associative array of `$field => $choices` pairs, with the main element field having the key '_main'.
	 */
	public function get_choices( $element ) {
		$choices = array();

		$element_choices = $element->get_element_choices();
		foreach ( $element_choices as $element_choice ) {
			$field = empty( $element_choice->field ) ? '_main' : $element_choice->field;

			if ( ! isset( $choices[ $field ] ) ) {
				$choices[ $field ] = array();
			}

			$choices[ $field ][] = $element_choice->value;
		}

		return $choices;
	}
}
