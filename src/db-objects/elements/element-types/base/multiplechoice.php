<?php
/**
 * Multiple choice element type class
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
 * Class representing a multiple choice element type.
 *
 * @since 1.0.0
 */
class Multiplechoice extends Element_Type implements Choice_Element_Type_Interface {
	use Choice_Element_Type_Trait;

	/**
	 * Bootstraps the element type by setting properties.
	 *
	 * @since 1.0.0
	 */
	protected function bootstrap() {
		$this->slug        = 'multiplechoice';
		$this->title       = __( 'Multiple Choice', 'torro-forms' );
		$this->description = __( 'A checkbox group element to select multiple values from.', 'torro-forms' );
		$this->icon_svg_id = 'torro-icon-multiplechoice';

		$this->add_choices_settings_field();
		$this->add_description_settings_field();
		$this->add_required_settings_field();
		$this->add_multiplechoices_settings_fields();
		$this->add_css_classes_settings_field();
	}

	/**
	 * Filters the array representation of a given element of this type.
	 *
	 * @since 1.0.0
	 *
	 * @param array           $data       Element data to filter.
	 * @param Element         $element    The element object to get the data for.
	 * @param Submission|null $submission Optional. Submission to get the values from, if available. Default null.
	 * @return array Array including all information for the element type.
	 */
	public function filter_json( $data, $element, $submission = null ) {
		$data = parent::filter_json( $data, $element, $submission );

		$data['value'] = (array) $data['value'];

		if ( ! empty( $data['input_attrs']['required'] ) ) {
			unset( $data['input_attrs']['required'] );
		}

		if ( ! empty( $data['input_attrs']['aria-required'] ) ) {
			unset( $data['input_attrs']['aria-required'] );
		}

		$data['legend_attrs'] = $data['label_attrs'];
		unset( $data['legend_attrs']['for'] );

		$data['input_attrs']['name'] .= '[]';
		$data['input_attrs']['id']    = str_replace( (string) $element->id, (string) $element->id . '-%index%', $data['input_attrs']['id'] );
		$data['label_attrs']['id']    = str_replace( (string) $element->id, (string) $element->id . '-%index%', $data['label_attrs']['id'] );
		$data['label_attrs']['for']   = str_replace( (string) $element->id, (string) $element->id . '-%index%', $data['label_attrs']['for'] );

		return $data;
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
		$settings = $this->get_settings( $element );

		$value = (array) $value;

		if ( ! empty( $settings['required'] ) && 'no' !== $settings['required'] && empty( $value ) ) {
			return $this->create_error( Element_Type::ERROR_CODE_REQUIRED, __( 'You must select at least a single value here.', 'torro-forms' ), $value );
		}

		if ( (int) $settings['min_choices'] > 0 && count( $value ) < (int) $settings['min_choices'] ) {
			/* translators: %s: number of minimum choices */
			return $this->create_error( 'value_too_few_choices', sprintf( _n( 'You must select at least %s value.', 'You must select at least %s values.', $settings['min_choices'], 'torro-forms' ), $settings['min_choices'] ), $value );
		}

		if ( (int) $settings['max_choices'] > 0 && count( $value ) > (int) $settings['max_choices'] ) {
			/* translators: %s: number of maximum choices */
			return $this->create_error( 'value_too_much_choices', sprintf( _n( 'You may select a maximum of %s value.', 'You may select a maximum of %s values.', $settings['max_choices'], 'torro-forms' ), $settings['max_choices'] ), $value );
		}

		if ( ! empty( $value ) ) {
			$choices = $this->get_choices_for_field( $element );

			foreach ( $value as $single_value ) {
				if ( ! in_array( $single_value, $choices, true ) ) {
					return $this->create_error( 'value_invalid_choice', __( 'You must select valid values from the list.', 'torro-forms' ), $value );
				}
			}
		}

		return $value;
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
		$fields = parent::get_edit_submission_fields_args( $element );

		$slug = $this->get_edit_submission_field_slug( $element->id );

		$fields[ $slug ]['type'] = 'multibox';

		return $fields;
	}

	/**
	 * Adds a settings field for specifying choices.
	 *
	 * @since 1.0.5
	 *
	 * @param string $section Optional. Settings section the settings field should be part of. Default 'content'.
	 */
	protected function add_multiplechoices_settings_fields( $section = 'settings' ) {
		$this->settings_fields['min_choices'] = array(
			'section'       => $section,
			'type'          => 'number',
			'label'         => __( 'Minimum Choices', 'torro-forms' ),
			'description'   => __( 'Specify the the minumum choices to select from.', 'torro-forms' ),
			'input_classes' => array( 'regular-text' ),
		);

		$this->settings_fields['max_choices'] = array(
			'section'       => $section,
			'type'          => 'number',
			'label'         => __( 'Maximum Choices', 'torro-forms' ),
			'description'   => __( 'Specify the the maximum choices to select from.', 'torro-forms' ),
			'input_classes' => array( 'regular-text' ),
		);
	}
}
