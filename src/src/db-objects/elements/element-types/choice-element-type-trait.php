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

	/**
	 * Returns the available choices for a specific field.
	 *
	 * @since 1.0.0
	 *
	 * @param Element $element Element to get choices for.
	 * @param string  $field   Optional. Element field for which to get choices. Default empty string (main field).
	 * @return array Array of choices.
	 */
	public function get_choices_for_field( $element, $field = '' ) {
		if ( empty( $field ) ) {
			$field = '_main';
		}

		$choices = array();

		$element_choices = $element->get_element_choices();
		foreach ( $element_choices as $element_choice ) {
			$current_field = empty( $element_choice->field ) ? '_main' : $element_choice->field;

			if ( $current_field !== $field ) {
				continue;
			}

			$choices[] = $element_choice->value;
		}

		return $choices;
	}

	/**
	 * Adds a settings field for specifying choices.
	 *
	 * @since 1.0.0
	 *
	 * @param string $field   Optional. Element field to which the choices should apply. Default empty string (main field).
	 * @param string $section Optional. Settings section the settings field should be part of. Default 'content'.
	 */
	protected function add_choices_settings_field( $field = '', $section = 'content' ) {
		if ( empty( $field ) ) {
			$field = '_main';
		}

		$this->settings_fields[ 'choices_' . $field ] = array(
			'section'       => $section,
			'type'          => 'torrochoices',
			'label'         => __( 'Choices', 'torro-forms' ),
			'description'   => __( 'Specify the choices to select from.', 'torro-forms' ),
			'input_classes' => array( 'regular-text' ),
			'repeatable'    => true,
			'is_choices'    => $field,
		);
	}
}
