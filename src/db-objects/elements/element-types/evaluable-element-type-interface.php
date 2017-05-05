<?php
/**
 * Evaluable element type interface
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Elements\Element_Types;

/**
 * Interface for element type that supports evaluation.
 *
 * @since 1.0.0
 */
interface Evaluable_Element_Type_Interface {

	/**
	 * Evaluates a list of submission values and creates statistics.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array                                  $submission_values Submission values to take into account.
	 * @param awsmug\Torro_Forms\DB_Objects\Elements $element           Element evaluate values for.
	 * @param string                                 $field             Optional. Field to evaluate. If empty,
	 *                                                                  the default field is evaluated. Default
	 *                                                                  empty.
	 * @return array Array of statistics.
	 */
	public function evaluate_values( $submission_values, $element, $field = '' );
}
