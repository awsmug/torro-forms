<?php
/**
 * Textfield element type class
 *
 * @package TorroForms
 * @since 1.2.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Base;

use awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Element_Type;
use awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Length_Limits_Element_Type_Trait;
use awsmug\Torro_Forms\DB_Objects\Elements\Element;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use WP_Error;

/**
 * Class representing a text element type.
 *
 * @since 1.2.0
 */
class Range extends Element_Type {

	/**
	 * Bootstraps the element type by setting properties.
	 *
	 * @since 1.0.0
	 */
	protected function bootstrap() {
		$this->slug        = 'range';
		$this->title       = __( 'Range', 'torro-forms' );
		$this->description = __( 'A range element.', 'torro-forms' );
		$this->icon_svg_id = 'torro-icon-range';

		$this->add_placeholder_settings_field();
		$this->add_description_settings_field();
		$this->add_required_settings_field();

		$this->settings_fields['min_value'] = array(
			'section'       => 'settings',
			'type'          => 'number',
			'label'         => __( 'Minimum Value', 'torro-forms' ),
			'description'   => __( 'The minimum value to display.', 'torro-forms' ),
			'input_classes' => array( 'small-text' ),
			'min'           => 0,
			'default'       => 0,
			'step'          => 1,
		);

		$this->settings_fields['max_value'] = array(
			'section'       => 'settings',
			'type'          => 'number',
			'label'         => __( 'Maximum Value', 'torro-forms' ),
			'description'   => __( 'The maximum value to display.', 'torro-forms' ),
			'input_classes' => array( 'small-text' ),
			'default'       => 10,
			'step'          => 1,
		);

		$this->settings_fields['show_min_max'] = array(
			'section'     => 'settings',
			'type'        => 'select',
			'label'       => __( 'Show min/max values', 'torro-forms' ),
			'description' => __( 'Shows the minimum and maximum values at the range', 'torro-forms' ),
			'choices'     => array(
				'no'     => __( 'No', 'torro-forms' ),
				'before' => __( 'Before', 'torro-forms' ),
				'after'  => __( 'After', 'torro-forms' ),
			),
			'default'     => 'after',
		);

		$this->settings_fields['step'] = array(
			'section'       => 'settings',
			'type'          => 'text',
			'label'         => __( 'Step', 'torro-forms' ),
			'description'   => __( 'The granularity of the range. A number and also "any" is allowed.', 'torro-forms' ),
			'input_classes' => array( 'small-text' ),
			'step'          => 1,
		);

		$this->settings_fields['helper_input'] = array(
			'section'     => 'settings',
			'type'        => 'select',
			'label'       => __( 'Helper Input', 'torro-forms' ),
			'description' => __( 'Shows the current value in a text input to offer manual input if the value', 'torro-forms' ),
			'choices'     => array(
				'no'     => __( 'No', 'torro-forms' ),
				'before' => __( 'Before', 'torro-forms' ),
				'after'  => __( 'After', 'torro-forms' ),
			),
			'default'     => 'no',
		);

		$this->add_css_classes_settings_field();
	}

	/**
	 * Filters the array representation of a given element of this type.
	 *
	 * @since 1.2.0
	 *
	 * @param array           $data       Element data to filter.
	 * @param Element         $element    The element object to get the data for.
	 * @param Submission|null $submission Optional. Submission to get the values from, if available. Default null.
	 * @return array Array including all information for the element type.
	 */
	public function filter_json( $data, $element, $submission = null ) {
		$data     = parent::filter_json( $data, $element, $submission );
		$settings = $this->get_settings( $element );

		$data['input_attrs']['min']          = $settings['min_value'];
		$data['input_attrs']['max']          = $settings['max_value'];
		$data['input_attrs']['step']         = $settings['step'];
		$data['input_attrs']['show_min_max'] = $settings['show_min_max'];

		$lentgh[] = strlen( strval( $settings['min_value'] ) );
		$lentgh[] = strlen( strval( $settings['max_value'] ) );

		$max_length = max( $lentgh );

		$data['helper_input']       = $settings['helper_input'];
		$data['helper_input_attrs'] = array(
			'id'        => 'torro-range-helper-' . $element->id,
			'class'     => 'torro-range-helper',
			'size'      => $max_length,
			'maxlength' => $max_length,
		);

		return $data;
	}

	/**
	 * Validates a field value for an element.
	 *
	 * @since 1.2.0
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
			return $this->create_error( Element_Type::ERROR_CODE_REQUIRED, __( 'You must enter something here.', 'torro-forms' ), $value );
		}

		return $value;
	}
}
