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

		$data['value'] = (bool) $data['value'];

		return $data;
	}

	/**
	 * Formats values for an export.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array   $values        Associative array of `$field => $value` pairs, with the main element field having the key '_main'.
	 * @param Element $element       Element the values belong to.
	 * @param string  $export_format Export format identifier. May be 'xls', 'csv', 'json', 'xml' or 'html'.
	 * @return array Associative array of `$column_slug => $column_value` pairs. The number of items and the column slugs
	 *               must match those returned from the get_export_columns() method.
	 */
	public function format_values_for_export( $values, $element, $export_format ) {
		$value = isset( $values['_main'] ) && $values['_main'] ? __( 'Yes', 'torro-forms' ) : __( 'No', 'torro-forms' );

		return array(
			'element_' . $element->id . '__main' => $this->escape_single_value_for_export( $value, $export_format ),
		);
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

		$value = (bool) $value;

		if ( ! empty( $settings['required'] ) && 'no' !== $settings['required'] && ! $value ) {
			return $this->create_error( 'value_required', __( 'You must check this box.', 'torro-forms' ), $value );
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
		$this->slug        = 'checkbox';
		$this->title       = __( 'Checkbox', 'torro-forms' );
		$this->description = __( 'A single checkbox element to toggle a value.', 'torro-forms' );

		// TODO: Add this icon.
		$this->icon_url    = $this->manager->assets()->get_full_url( 'assets/dist/img/icon-checkbox.png' );

		$this->add_description_settings_field();
		$this->add_required_settings_field();
		$this->add_css_classes_settings_field();
	}
}