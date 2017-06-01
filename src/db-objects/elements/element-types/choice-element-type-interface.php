<?php
/**
 * Choice element type interface
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Elements\Element_Types;

use awsmug\Torro_Forms\DB_Objects\Elements\Element;

/**
 * Interface for element type that support choices.
 *
 * @since 1.0.0
 */
interface Choice_Element_Type_Interface {

	/**
	 * Returns the available choices.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Element $element Element to get choices for.
	 * @return array Associative array of `$field => $choices` pairs, with the main element field having the key '_main'.
	 */
	public function get_choices( $element );
}
