<?php
/**
 * Checkbox element type class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Base;

use awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Element_Type;
use awsmug\Torro_Forms\DB_Objects\Elements\Element;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use WP_Error;

/**
 * Class representing a checkbox element type.
 *
 * @since 1.0.0
 */
class Checkbox extends Element_Type {

	/**
	 * Bootstraps the element type by setting properties.
	 *
	 * @since 1.0.0
	 */
	protected function bootstrap() {
		$this->slug        = 'checkbox';
		$this->title       = __( 'Checkbox', 'torro-forms' );
		$this->description = __( 'A single checkbox element to toggle a value.', 'torro-forms' );
		$this->icon_svg_id = 'torro-icon-checkbox';

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

		$data['value'] = (bool) $data['value'];

		return $data;
	}

	/**
	 * Formats values for an export.
	 *
	 * @since 1.0.0
	 *
	 * @param array   $values        Associative array of `$field => $value` pairs, with the main element field having the key '_main'.
	 * @param Element $element       Element the values belong to.
	 * @param string  $export_format Export format identifier. May be 'xls', 'csv', 'json', 'xml' or 'html'.
	 * @return array Associative array of `$column_slug => $column_value` pairs. The number of items and the column slugs
	 *               must match those returned from the get_export_columns() method.
	 */
	public function format_values_for_export( $values, $element, $export_format ) {
		$yes_no = $this->get_export_column_choices_yes_no( $element );

		$value = isset( $values['_main'] ) && $values['_main'] ? $yes_no[0] : $yes_no[1];

		/**
		 * Filters the value for export
		 *
		 * @since 1.0.5
		 *
		 * @param string  $value    Value to filter.
		 * @param Element $element  Element object.
		 */
		$value = apply_filters( "{$this->manager->get_prefix()}export_value", $value, $element );

		return array(
			'element_' . $element->id . '__main' => $this->escape_single_value_for_export( $value, $export_format ),
		);
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

		$value = (bool) $value;

		if ( ! empty( $settings['required'] ) && 'no' !== $settings['required'] && ! $value ) {
			return $this->create_error( Element_Type::ERROR_CODE_REQUIRED, __( 'You must check this box.', 'torro-forms' ), $value );
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

		$fields[ $slug ]['type'] = 'checkbox';

		return $fields;
	}
}
