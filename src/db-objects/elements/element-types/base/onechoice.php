<?php
/**
 * One choice element type class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Base;

use awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Element_Type;
use awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Choice_Element_Type_Interface;
use awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Choice_Element_Type_Trait;
use awsmug\Torro_Forms\DB_Objects\Elements\Element;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use WP_Error;

/**
 * Class representing a one choice element type.
 *
 * @since 1.0.0
 */
class Onechoice extends Element_Type implements Choice_Element_Type_Interface {
	use Choice_Element_Type_Trait;

	/**
	 * Filters the array representation of a given element of this type.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array           $data       Element data to filter.
	 * @param Element         $element    The element object to get the data for.
	 * @param Submission|null $submission Optional. Submission to get the values from, if available. Default null.
	 * @return array Array including all information for the element type.
	 */
	public function filter_json( $data, $element, $submission = null ) {
		$data = parent::filter_json( $data, $element, $submission );

		if ( ! empty( $data['input_attrs']['required'] ) ) {
			unset( $data['input_attrs']['required'] );
		}

		if ( ! empty( $data['input_attrs']['aria-required'] ) ) {
			unset( $data['input_attrs']['aria-required'] );
		}

		$data['legend_attrs'] = $data['label_attrs'];
		unset( $data['legend_attrs']['for'] );

		$data['input_attrs']['id']  = str_replace( (string) $element->id, (string) $element->id . '-%index%', $data['input_attrs']['id'] );
		$data['label_attrs']['id']  = str_replace( (string) $element->id, (string) $element->id . '-%index%', $data['label_attrs']['id'] );
		$data['label_attrs']['for'] = str_replace( (string) $element->id, (string) $element->id . '-%index%', $data['label_attrs']['for'] );

		return $data;
	}

	/**
	 * Validates a field value for an element.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param mixed      $value      The value to validate. It is already unslashed when it arrives here.
	 * @param Element    $element    Element to validate the field value for.
	 * @param Submission $submission Submission the value belongs to.
	 * @return mixed|WP_Error Validated value, or error object on failure.
	 */
	public function validate_field( $value, $element, $submission ) {
		$settings = $this->get_settings( $element );

		$value = trim( (string) $value );

		if ( ! empty( $settings['required'] ) && 'no' !== $settings['required'] && empty( $value ) ) {
			return $this->create_error( 'value_required', __( 'You must select a value here.', 'torro-forms' ), $value );
		}

		$choices = $this->get_choices_for_field( $element );

		if ( ! in_array( $value, $choices, true ) ) {
			return $this->create_error( 'value_invalid_choice', __( 'You must select a valid value from the list.', 'torro-forms' ), $value );
		}

		return $value;
	}

	/**
	 * Bootstraps the element type by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function bootstrap() {
		$this->slug        = 'onechoice';
		$this->title       = __( 'One Choice', 'torro-forms' );
		$this->description = __( 'A radio group element to select a single value from.', 'torro-forms' );
		$this->icon_url    = $this->manager->assets()->get_full_url( 'assets/dist/img/icon-onechoice.png' );

		$this->add_choices_settings_field();
		$this->add_description_settings_field();
		$this->add_required_settings_field();
		$this->add_css_classes_settings_field();
	}
}
