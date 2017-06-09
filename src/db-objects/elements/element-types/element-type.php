<?php
/**
 * Element type base class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Elements\Element_Types;

use Leaves_And_Love\Plugin_Lib\Fields\Field_Manager;
use awsmug\Torro_Forms\DB_Objects\Elements\Element;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use WP_Error;

/**
 * Base class representing an element type.
 *
 * @since 1.0.0
 */
abstract class Element_Type {

	/**
	 * The element type slug. Must match the slug when registering the element type.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $slug = '';

	/**
	 * The element type title.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $title = '';

	/**
	 * The element type description.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $description = '';

	/**
	 * The element type icon URL.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $icon_url = '';

	/**
	 * The element type settings sections.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $settings_sections = array();

	/**
	 * The element type settings fields.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $settings_fields = array();

	/**
	 * The element type manager instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Element_Type_Manager
	 */
	protected $manager;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Element_Type_Manager $manager The element type manager instance.
	 */
	public function __construct( $manager ) {
		$this->manager = $manager;

		$this->settings_sections = array(
			'content'  => array(
				'title' => _x( 'Content', 'element type section', 'torro-forms' ),
			),
			'settings' => array(
				'title' => _x( 'Settings', 'element type section', 'torro-forms' ),
			),
		);

		$this->settings_fields = array(
			'label' => array(
				'section'     => 'content',
				'type'        => 'text',
				'label'       => __( 'Label', 'torro-forms' ),
				'description' => __( 'Enter the form field label.', 'torro-forms' ),
				'is_label'    => true,
			),
		);

		$this->bootstrap();

		$this->sanitize_settings_sections();
		$this->sanitize_settings_fields();
	}

	/**
	 * Returns the element type slug.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Element type slug.
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Returns the element type title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Element type title.
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Returns the element type description.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Element type description.
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Returns the element type icon URL.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Element type icon URL.
	 */
	public function get_icon_url() {
		return $this->icon_url;
	}

	/**
	 * Returns the element type settings sections.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Element type settings sections.
	 */
	public function get_settings_sections() {
		return $this->settings_sections;
	}

	/**
	 * Returns the element type settings fields.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Element type settings fields.
	 */
	public function get_settings_fields() {
		return $this->settings_fields;
	}

	/**
	 * Returns the available settings.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Element $element Element to get settings for.
	 * @return array Associative array of `$setting_name => $setting_value` pairs.
	 */
	public function get_settings( $element ) {
		$settings = array();

		$element_settings = $element->get_element_settings();
		foreach ( $element_settings as $element_setting ) {
			$settings[ $element_setting->name ] = $element_setting->value;
		}

		return $settings;
	}

	/**
	 * Returns the current values for the element fields, optionally for a specific submission.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Element         $element    The element object to get values for.
	 * @param Submission|null $submission Optional. Submission to get the values from, if available. Default null.
	 * @return array Associative array of `$field => $value` pairs, with the main element field having the key '_main'.
	 */
	public function get_values( $element, $submission = null ) {
		$values = array();
		if ( $submission ) {
			$submission_values = $submission->get_submission_values( array( 'element_id' => $element->id ) );
			foreach ( $submission_values as $submission_value ) {
				$field = empty( $submission_value->field ) ? '_main' : $submission_value->field;

				$values[ $field ] = $submission_value->value;
			}
		}

		if ( isset( $_GET[ 'torro_input_value_' . $element->id ] ) && ( is_array( $_GET[ 'torro_input_value_' . $element->id ] ) || empty( $values['_main'] ) ) ) {
			$container = $element->get_container();
			if ( $container ) {
				$form = $container->get_form();
				if ( $form ) {
					$allow_get_param = get_post_meta( $form->id, 'allow_get_param', true );
					if ( $allow_get_param && 'no' !== $allow_get_param ) {
						$choices = is_a( $this, Choice_Element_Type_Interface::class ) ? $this->get_choices( $element ) : array();

						$get_params = wp_unslash( $_GET[ 'torro_input_value_' . $element->id ] );
						if ( is_array( $get_params ) ) {
							foreach ( $get_params as $field => $value ) {
								if ( empty( $values[ $field ] ) ) {
									if ( ! empty( $choices[ $field ] ) ) {
										if ( isset( $choices[ $field ][ $value ] ) ) {
											$values[ $field ] = $choices[ $field ][ $value ];
										} elseif ( in_array( $value, $choices[ $field ], true ) ) {
											$values[ $field ] = $value;
										}

										continue;
									}

									$values[ $field ] = $value;
								}
							}
						} elseif ( empty( $values['_main'] ) ) {
							if ( ! empty( $choices['_main'] ) ) {
								if ( isset( $choices['_main'][ $get_params ] ) ) {
									$values['_main'] = $choices['_main'][ $get_params ];
								} elseif ( in_array( $get_params, $choices['_main'], true ) ) {
									$values[ $field ] = $get_params;
								}
							} else {
								$values['_main'] = $get_params;
							}
						}
					}
				}
			}
		}
	}

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
		$settings = $this->get_settings( $element );
		$values   = $this->get_values( $element, $submission );

		$data['value'] = ! empty( $values['_main'] ) ? $values['_main'] : '';

		if ( ! empty( $settings['description'] ) ) {
			$data['description'] = $settings['description'];

			$data['input_attrs']['aria-describedby'] = $data['description_attrs']['id'];
		}

		if ( ! empty( $settings['required'] ) ) {
			$required_indicator = '<span class="screen-reader-text">' . __( '(required)', 'torro-forms' ) . '</span><span class="torro-required-indicator" aria-hidden="true">*</span>';

			/**
			 * Filters the required indicator for an element that must be filled.
			 *
			 * @since 1.0.0
			 *
			 * @param string $required_indicator Indicator HTML string. Default is a screen-reader-only
			 *                                   '(required)' text and an asterisk for visual appearance.
			 */
			$data['label_required'] .= ' ' . apply_filters( "{$this->manager->get_prefix()}required_indicator", $required_indicator );

			$data['input_attrs']['aria-required'] = 'true';
			$data['input_attrs']['required'] = true;
		}

		if ( $submission && $submission->has_errors( $element->id ) ) {
			$data['errors'] = $submission->get_errors( $element->id );

			$data['input_attrs']['aria-invalid'] = 'true';
		}

		$choices = array();
		if ( is_a( $this, Choice_Element_Type_Interface::class ) ) {
			$choices = $this->get_choices( $element );

			$data['choices'] = $choices;
		}

		if ( is_a( $this, Multi_Field_Element_Type_Interface::class ) ) {
			$data['additional_fields'] = $this->additional_fields_to_json( $element, $submission, $choices, $settings, $values );
		}

		return $data;
	}

	/**
	 * Validates a field value for an element.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param mixed   $value   The value to validate.
	 * @param Element $element Element to validate the field value for.
	 * @return mixed|WP_Error Validated value, or error object on failure.
	 */
	public abstract function validate_field( $value, $element );

	/**
	 * Bootstraps the element type by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected abstract function bootstrap();

	/**
	 * Sanitizes the settings sections.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function sanitize_settings_sections() {
		$defaults = array(
			'title' => '',
		);

		foreach ( $this->settings_sections as $slug => $section ) {
			$this->settings_sections[ $slug ] = array_merge( $defaults, $section );
		}
	}

	/**
	 * Sanitizes the settings fields.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function sanitize_settings_fields() {
		$defaults = array(
			'section'     => '',
			'type'        => 'text',
			'label'       => '',
			'description' => '',
			'is_label'    => false,
			'is_choices'  => false,
		);

		$invalid_fields = array();
		$valid_sections = array();

		foreach ( $this->settings_fields as $slug => $field ) {
			if ( empty( $field['section'] ) || ! isset( $this->settings_sections[ $field['section'] ] ) ) {
				$this->manager->error_handler()->doing_it_wrong( get_class( $this ) . '::bootstrap()', sprintf( __( 'Invalid element type field section %s.', 'torro-forms' ), esc_html( $field['section'] ) ), '1.0.0' );
				$invalid_fields[ $slug ] = true;
				continue;
			}

			if ( empty( $field['type'] ) || ! Field_Manager::is_field_type_registered( $field['type'] ) ) {
				$this->manager->error_handler()->doing_it_wrong( get_class( $this ) . '::bootstrap()', sprintf( __( 'Invalid element type field type %s.', 'torro-forms' ), esc_html( $field['type'] ) ), '1.0.0' );
				$invalid_fields[ $slug ] = true;
				continue;
			}

			$valid_sections[ $field['section'] ] = true;
			$this->settings_fields[ $slug ] = array_merge( $defaults, $field );
		}

		$this->settings_fields = array_diff_key( $this->settings_fields, $invalid_fields );
		$this->settings_sections = array_intersect_key( $this->settings_sections, $valid_sections );
	}
}
