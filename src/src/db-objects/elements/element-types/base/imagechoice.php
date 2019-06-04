<?php
/**
 * Image choice element type class
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Base;

use awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Element_Type;
use awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Choice_Element_Type_Interface;
use awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Choice_Element_Type_Trait;
use awsmug\Torro_Forms\DB_Objects\Elements\Element;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use WP_Error;

/**
 * Class representing a image choice element type.
 *
 * @since 1.1.0
 */
class Imagechoice extends Element_Type implements Choice_Element_Type_Interface {
	use Choice_Element_Type_Trait;

	/**
	 * Bootstraps the element type by setting properties.
	 *
	 * @since 1.1.0
	 */
	protected function bootstrap() {
		$this->slug        = 'imagechoice';
		$this->title       = __( 'Image Choice', 'torro-forms' );
		$this->description = __( 'An image group element to select a single value from.', 'torro-forms' );
		$this->icon_svg_id = 'torro-icon-imagechoice';

		$this->add_choices_settings_field();
		$this->add_description_settings_field();
		$this->add_required_settings_field();
		$this->add_css_classes_settings_field();

		$image_sizes = array();

		foreach ( get_intermediate_image_sizes() as $image_size ) {
			$image_sizes[ $image_size ] = $image_size;
		}

		$this->settings_fields['image_size'] = array(
			'section'     => 'settings',
			'type'        => 'select',
			'label'       => __( 'Image Size', 'torro-forms' ),
			'description' => __( 'Image size to be used in frontend.', 'torro-forms' ),
			'choices'     => $image_sizes,
			'default'     => 'any',
		);

		$image_alignments = array(
			'horizontal' => __( 'Horizontal', 'torro-forms' ),
			'vertical'   => __( 'Vertical', 'torro-forms' ),
		);

		$this->settings_fields['image_alignment'] = array(
			'section'     => 'settings',
			'type'        => 'select',
			'label'       => __( 'Image Alignment', 'torro-forms' ),
			'description' => __( 'How to align images and text.', 'torro-forms' ),
			'choices'     => $image_alignments,
			'default'     => 'horizontal',
		);

		$this->settings_fields['title_after_image'] = array(
			'section'     => 'settings',
			'type'        => 'checkbox',
			'label'       => __( 'Show Title ', 'torro-forms' ),
			'description' => __( 'Show the title after the image.', 'torro-forms' ),
			'default'     => true,
		);
	}

	/**
	 * Filters the array representation of a given element of this type.
	 *
	 * @since 1.1.0
	 *
	 * @param array           $data       Element data to filter.
	 * @param Element         $element    The element object to get the data for.
	 * @param Submission|null $submission Optional. Submission to get the values from, if available. Default null.
	 * @return array Array including all information for the element type.
	 */
	public function filter_json( $data, $element, $submission = null ) {
		$data     = parent::filter_json( $data, $element, $submission );
		$settings = $this->get_settings( $element );

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

		$data['choice_attrs']['class']  = array( 'torro-toggle', 'torro-imagechoice' );
		$data['choices_attrs']['class'] = array( 'torro-imagechoices' );

		if ( 'horizontal' === $settings['image_alignment'] ) {
			$data['choices_attrs']['class'][] = 'torro-align-horizontal';
		} else {
			$data['choices_attrs']['class'][] = 'torro-align-vertical';
		}

		$data['choice_attrs']['class']  = implode( ' ', $data['choice_attrs']['class'] );
		$data['choices_attrs']['class'] = implode( ' ', $data['choices_attrs']['class'] );

		$images = array();

		foreach ( $data['choices'] as $choice ) {
			$images[ $choice ] = array(
				'img'   => wp_get_attachment_image( $choice, $settings['image_size'] ),
				'src'   => wp_get_attachment_image_src( $choice, $settings['image_size'] ),
				'title' => get_the_title( $choice ),
			);
		}

		$data['images']            = $images;
		$data['title_after_image'] = $settings['title_after_image'];

		return $data;
	}

	/**
	 * Validates a field value for an element.
	 *
	 * @since 1.1.0
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
	 * @since 1.1.0
	 *
	 * @param Element $element Element to get fields arguments for.
	 * @return array An associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_edit_submission_fields_args( $element ) {
		$fields = parent::get_edit_submission_fields_args( $element );

		$slug = $this->get_edit_submission_field_slug( $element->id );

		$fields[ $slug ]['type'] = 'image';

		return $fields;
	}

	/**
	 * Adds a settings field for specifying choices.
	 *
	 * @since 1.1.0
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
			'type'          => 'torroimagechoices',
			'label'         => __( 'Image Choices', 'torro-forms' ),
			'description'   => __( 'Specify the choices to select from.', 'torro-forms' ),
			'input_classes' => array( 'regular-text' ),
			'repeatable'    => true,
			'is_choices'    => $field,
		);
	}

	/**
	 * Checks whether a single export column should be used for all choices.
	 *
	 * By default, each choice has its own column.
	 *
	 * @since 1.1.0
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
		 * @since 1.1.0
		 *
		 * @param bool         $single_column Whether to only render a single column for all choices.
		 * @param Element_Type $element_type  Current element type.
		 * @param Element      $element       Current element.
		 */
		return apply_filters( "{$this->manager->get_prefix()}use_single_export_column_for_choices", true, $this, $element );
	}

	/**
	 * Formats values for an export.
	 *
	 * @since 1.1.0
	 *
	 * @param array   $values        Associative array of `$field => $value` pairs, with the main element field having the key '_main'.
	 * @param Element $element       Element the values belong to.
	 * @param string  $export_format Export format identifier. May be 'xls', 'csv', 'json', 'xml' or 'html'.
	 * @return array Associative array of `$column_slug => $column_value` pairs. The number of items and the column slugs
	 *               must match those returned from the get_export_columns() method.
	 */
	public function format_values_for_export( $values, $element, $export_format ) {
		if ( $this instanceof Choice_Element_Type_Interface && ! $this->use_single_export_column_for_choices( $element ) ) {
			$value  = isset( $values['_main'] ) ? (array) $values['_main'] : array();
			$yes_no = $this->get_export_column_choices_yes_no( $element );

			$columns = array();

			foreach ( $element->get_element_choices() as $element_choice ) {
				$choice_slug = sanitize_title( $element_choice->value );

				$columns[ 'element_' . $element->id . '__main_' . $choice_slug ] = in_array( $element_choice->value, $value ) ? $yes_no[0] : $yes_no[1]; // @codingStandardsIgnoreLine
			}

			return parent::format_values_for_export( $values, $element, $export_format );
		}

		$post_id = isset( $values['_main'] ) ? $values['_main'] : '';
		$post    = get_post( $post_id );
		$value   = $post->post_title;

		/**
		 * Filters the value for export
		 *
		 * @since 1.1.0
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
	 * Gets the columns required for an export.
	 *
	 * @since 1.1.0
	 *
	 * @param Element $element Element to export columns for.
	 * @return array Associative array of `$column_slug => $column_label` pairs.
	 */
	public function get_export_columns( $element ) {
		if ( $this instanceof Choice_Element_Type_Interface && ! $this->use_single_export_column_for_choices( $element ) ) {
			$columns = array();

			foreach ( $element->get_element_choices() as $element_choice ) {
				$post_id = $element_choice->value;
				$post    = get_post( $post_id );
				$title   = $post->post_title;

				$columns[ 'element_' . $element->id . '__main_' . $element_choice->value ] = $element->label . ' - ' . $title;
			}

			return $columns;
		}

		return array(
			'element_' . $element->id . '__main' => $element->label,
		);
	}
}
