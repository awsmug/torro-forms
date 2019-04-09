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
class Imagechoice extends Element_Type implements Choice_Element_Type_Interface {
	use Choice_Element_Type_Trait;

	/**
	 * Bootstraps the element type by setting properties.
	 *
	 * @since 1.0.0
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
	 * @since 1.0.0
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

		$fields[ $slug ]['type'] = 'image';

		return $fields;
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
			'type'          => 'torroimagechoices',
			'label'         => __( 'Image Choices', 'torro-forms' ),
			'description'   => __( 'Specify the choices to select from.', 'torro-forms' ),
			'input_classes' => array( 'regular-text' ),
			'repeatable'    => true,
			'is_choices'    => $field,
		);
	}
}
