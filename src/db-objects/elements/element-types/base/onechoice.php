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
	 * Bootstraps the element type by setting properties.
	 *
	 * @since 1.0.0
	 */
	protected function bootstrap() {
		$this->slug        = 'onechoice';
		$this->title       = __( 'One Choice', 'torro-forms' );
		$this->description = __( 'A radio group element to select a single value from.', 'torro-forms' );
		$this->icon_svg_id = 'torro-icon-onechoice';

		$this->add_choices_settings_field();
		$this->add_description_settings_field();
		$this->add_required_settings_field();
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
	 *
	 * @param mixed      $value      The value to validate. It is already unslashed when it arrives here.
	 * @param Element    $element    Element to validate the field value for.
	 * @param Submission $submission Submission the value belongs to.
	 * @return mixed|WP_Error Validated value, or error object on failure.
	 */
	public function validate_field( $value, $element, $submission ) {
		$settings = $this->get_settings( $element );
		$choices  = $this->get_choices_for_field( $element );

		$value = trim( (string) $value );

		if ( ! empty( $settings['required'] ) && 'no' !== $settings['required'] && empty( $value ) && ! in_array( $value, $choices, true ) ) {
			return $this->create_error( Element_Type::ERROR_CODE_REQUIRED, __( 'You must enter something here.', 'torro-forms' ), $value );
		}

		if ( ! empty( $value ) && ! in_array( $value, $choices, true ) ) {
			return $this->create_error( 'value_invalid_choice', __( 'You must select a valid value from the list.', 'torro-forms' ), $value );
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

		$fields[ $slug ]['type'] = 'radio';

		return $fields;
	}

	/**
	 * Checks whether a single export column should be used for all choices.
	 *
	 * By default, each choice has its own column.
	 *
	 * @since 1.0.6
	 *
	 * @param Element $element Element for which to check this flag.
	 * @return bool True if a single column should be used, false otherwise.
	 */
	protected function use_single_export_column_for_choices( $element ) {
		/**
		 * Filters whether to only render a single column for all choices when exporting submissions.
		 *
		 * If this filter returns true, there will only be one column for all choices. In case of an element
		 * where multiple choices are seletable, those values will be concatenated.
		 *
		 * By default, each choice has its own column.
		 *
		 * @since 1.0.6
		 *
		 * @param bool         $single_column Whether to only render a single column for all choices.
		 * @param Element_Type $element_type  Current element type.
		 * @param Element      $element       Current element.
		 */
		return apply_filters( "{$this->manager->get_prefix()}use_single_export_column_for_choices", true, $this, $element );
	}
}
