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
	 * Error code to use when the error says the field is required.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const ERROR_CODE_REQUIRED = 'value_required';

	/**
	 * The element type slug. Must match the slug when registering the element type.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $slug = '';

	/**
	 * The element type title.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $title = '';

	/**
	 * The element type description.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $description = '';

	/**
	 * The element type icon CSS class.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $icon_css_class = '';

	/**
	 * The element type icon SVG ID.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $icon_svg_id = '';

	/**
	 * The element type icon URL.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $icon_url = '';

	/**
	 * The element type settings sections.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $settings_sections = array();

	/**
	 * The element type settings fields.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $settings_fields = array();

	/**
	 * The element type manager instance.
	 *
	 * @since 1.0.0
	 * @var Element_Type_Manager
	 */
	protected $manager;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
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
				'section'       => 'content',
				'type'          => 'text',
				'label'         => __( 'Label', 'torro-forms' ),
				'description'   => __( 'Enter the form field label.', 'torro-forms' ),
				'input_classes' => array( 'regular-text' ),
				'is_label'      => true,
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
	 *
	 * @return string Element type description.
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Returns the element type icon CSS class.
	 *
	 * @since 1.0.0
	 *
	 * @return string Element type icon CSS class.
	 */
	public function get_icon_css_class() {
		return $this->icon_css_class;
	}

	/**
	 * Returns the element type icon SVG ID.
	 *
	 * @since 1.0.0
	 *
	 * @return string Element type icon SVG ID.
	 */
	public function get_icon_svg_id() {
		return $this->icon_svg_id;
	}

	/**
	 * Returns the element type icon URL.
	 *
	 * @since 1.0.0
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
	 *
	 * @param Element         $element    The element object to get values for.
	 * @param Submission|null $submission Optional. Submission to get the values from, if available. Default null.
	 * @return array Associative array of `$field => $value` pairs, with the main element field having the key '_main'.
	 */
	public function get_values( $element, $submission = null ) {
		$values = array();
		if ( $submission ) {
			$all_values = $submission->get_element_values_data();
			if ( isset( $all_values[ $element->id ] ) ) {
				$values = $all_values[ $element->id ];
			}
		}

		if ( has_filter( "{$this->manager->get_prefix()}allow_get_params" ) && isset( $_GET[ 'torro_input_value_' . $element->id ] ) && ( is_array( $_GET[ 'torro_input_value_' . $element->id ] ) || empty( $values['_main'] ) ) ) { // WPCS: CSRF OK.
			$container = $element->get_container();
			if ( $container ) {
				$form = $container->get_form();
				if ( $form ) {
					/**
					 * Filters whether to allow GET parameters to pre-populate form element values.
					 *
					 * @since 1.0.0
					 *
					 * @param bool $allow_get_paramss Whether to allow GET parameters. Default false.
					 * @param int  $element_id       Element ID for which GET parameters are being checked.
					 * @param int  $form_id          Form ID the element is part of.
					 */
					$allow_get_params = apply_filters( "{$this->manager->get_prefix()}allow_get_params", false, $element->id, $form->id );

					if ( $allow_get_params ) {
						$choices = is_a( $this, Choice_Element_Type_Interface::class ) ? $this->get_choices( $element ) : array();

						$get_params = wp_unslash( $_GET[ 'torro_input_value_' . $element->id ] ); // phpcs:ignore WordPress.Security
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

		return $values;
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
		if ( is_a( $this, Choice_Element_Type_Interface::class ) && ! $this->use_single_export_column_for_choices( $element ) ) {
			$value  = isset( $values['_main'] ) ? (array) $values['_main'] : array();
			$yes_no = $this->get_export_column_choices_yes_no( $element );

			$columns = array();

			foreach ( $element->get_element_choices() as $element_choice ) {
				$choice_slug = sanitize_title( $element_choice->value );

				$columns[ 'element_' . $element->id . '__main_' . $choice_slug ] = in_array( $element_choice->value, $value ) ? $yes_no[0] : $yes_no[1]; // @codingStandardsIgnoreLine
			}

			return $columns;
		}

		$value = isset( $values['_main'] ) ? $values['_main'] : '';

		if ( is_array( $value ) ) {
			$value = implode( ', ', $value );
		}

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
	 * Gets the columns required for an export.
	 *
	 * @since 1.0.0
	 *
	 * @param Element $element Element to export columns for.
	 * @return array Associative array of `$column_slug => $column_label` pairs.
	 */
	public function get_export_columns( $element ) {
		if ( is_a( $this, Choice_Element_Type_Interface::class ) && ! $this->use_single_export_column_for_choices( $element ) ) {
			$columns = array();

			foreach ( $element->get_element_choices() as $element_choice ) {
				$choice_slug = sanitize_title( $element_choice->value );

				$columns[ 'element_' . $element->id . '__main_' . $choice_slug ] = $element->label . ' - ' . $element_choice->value;
			}

			return $columns;
		}

		return array(
			'element_' . $element->id . '__main' => $element->label,
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
		$data['template_suffix'] = $this->slug;

		$settings = $this->get_settings( $element );
		$values   = $this->get_values( $element, $submission );

		$data['value'] = '';
		if ( isset( $values['_main'] ) && ( ! empty( $values['_main'] ) || is_numeric( $values['_main'] ) ) ) {
			$data['value'] = $values['_main'];
		}

		$placeholder = ! empty( $settings['placeholder'] ) ? $settings['placeholder'] : '';

		/**
		 * Filters the placeholder for an element field.
		 *
		 * @since 1.0.0
		 *
		 * @param string $placeholder Original placeholder.
		 * @param int    $element_id  Element ID.
		 */
		$placeholder = apply_filters( "{$this->manager->get_prefix()}input_placeholder", $placeholder, $element->id );

		if ( ! empty( $placeholder ) ) {
			$data['input_attrs']['placeholder'] = $placeholder;
		}

		if ( ! empty( $settings['description'] ) ) {
			$data['description'] = $settings['description'];

			$data['input_attrs']['aria-describedby'] = $data['description_attrs']['id'];
		}

		if ( ! empty( $settings['required'] ) && 'no' !== $settings['required'] ) {
			$required_indicator = '<span class="screen-reader-text">' . _x( '(required)', 'field required indicator', 'torro-forms' ) . '</span><span class="torro-required-indicator" aria-hidden="true">*</span>';

			/**
			 * Filters the required indicator for an element that must be filled.
			 *
			 * @since 1.0.0
			 *
			 * @param string $required_indicator Indicator HTML string. Default is a screen-reader-only
			 *                                   '(required)' text and an asterisk for visual appearance.
			 */
			$data['label_required'] = apply_filters( "{$this->manager->get_prefix()}required_indicator", $required_indicator );

			$data['input_attrs']['aria-required'] = 'true';
			$data['input_attrs']['required']      = true;
		}

		if ( ! empty( $settings['css_classes'] ) ) {
			if ( ! empty( $data['wrap_attrs']['class'] ) ) {
				$data['wrap_attrs']['class'] .= ' ';
			} else {
				$data['wrap_attrs']['class'] = '';
			}

			$data['wrap_attrs']['class'] .= $settings['css_classes'];
		}

		if ( $submission && $submission->has_errors( $element->id ) ) {
			$data['errors'] = $submission->get_errors( $element->id );

			$data['input_attrs']['aria-invalid'] = 'true';
		}

		$choices = array();
		if ( is_a( $this, Choice_Element_Type_Interface::class ) ) {
			$choices = $this->get_choices( $element );

			$data['choices'] = ! empty( $choices['_main'] ) ? $choices['_main'] : array();
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
	 *
	 * @param mixed      $value      The value to validate. It is already unslashed when it arrives here.
	 * @param Element    $element    Element to validate the field value for.
	 * @param Submission $submission Submission the value belongs to.
	 * @return mixed|array|WP_Error Validated value, or error object on failure. If an array is returned,
	 *                              the individual values will be stored in the database separately. The
	 *                              array may also contain error objects for cases where errors occurred.
	 */
	abstract public function validate_field( $value, $element, $submission );

	/**
	 * Gets the fields arguments for an element of this type when editing submission values in the admin.
	 *
	 * @since 1.0.0
	 *
	 * @param Element $element Element to get fields arguments for.
	 * @return array An associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_edit_submission_fields_args( $element ) {
		$settings = $this->get_settings( $element );

		$slug = $this->get_edit_submission_field_slug( $element->id );
		$args = array(
			'type'  => 'text',
			'label' => $element->label,
		);

		if ( ! empty( $settings['placeholder'] ) ) {
			$args['placeholder'] = $settings['placeholder'];
		}

		if ( ! empty( $settings['description'] ) ) {
			$args['description'] = $settings['description'];
		}

		if ( ! empty( $settings['required'] ) && 'no' !== $settings['required'] ) {
			$args['required'] = true;
		}

		if ( ! empty( $settings['css_classes'] ) ) {
			$args['input_classes'] = explode( ' ', $settings['css_classes'] );
		}

		if ( is_a( $this, Choice_Element_Type_Interface::class ) ) {
			$choices = $this->get_choices( $element );

			$args['choices'] = ! empty( $choices['_main'] ) ? array_combine( $choices['_main'], $choices['_main'] ) : array();
		}

		return array(
			$slug => $args,
		);
	}

	/**
	 * Bootstraps the element type by setting properties.
	 *
	 * @since 1.0.0
	 */
	abstract protected function bootstrap();

	/**
	 * Gets the two strings indicating 'Yes' and 'No' in an export column.
	 *
	 * By default, these are simply localized 'Yes' and 'No'.
	 *
	 * @since 1.0.0
	 *
	 * @param Element $element Element for which to use the strings.
	 * @return array Array with two elements where the first value is the 'Yes' string and the second is the 'No' string.
	 */
	protected function get_export_column_choices_yes_no( $element ) {
		$yes_no = array(
			__( 'Yes', 'torro-forms' ),
			__( 'No', 'torro-forms' ),
		);

		/**
		 * Filters the two strings to use for choice export columns indicating whether the choice was included in the submission or not.
		 *
		 * By default, the strings are a localized 'Yes' and 'No'.
		 *
		 * @since 1.0.0
		 *
		 * @param array        $yes_no        Array with two elements where the first value is the 'Yes' string and the second value
		 *                                    is the 'No' string.
		 * @param Element_Type $element_type  Current element type.
		 * @param Element      $element       Current element.
		 */
		return apply_filters( "{$this->manager->get_prefix()}export_column_choices_yes_no", $yes_no, $this, $element );
	}

	/**
	 * Checks whether a single export column should be used for all choices.
	 *
	 * By default, each choice has its own column.
	 *
	 * @since 1.0.0
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
		 * @since 1.0.0
		 *
		 * @param bool         $single_column Whether to only render a single column for all choices.
		 * @param Element_Type $element_type  Current element type.
		 * @param Element      $element       Current element.
		 */
		return apply_filters( "{$this->manager->get_prefix()}use_single_export_column_for_choices", false, $this, $element );
	}

	/**
	 * Escapes a single value for a specific export format.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $value         Value to escape.
	 * @param string $export_format Export format identifier. May be 'xls', 'csv', 'json', 'xml' or 'html'.
	 * @return mixed Escaped value, usually a string.
	 */
	protected function escape_single_value_for_export( $value, $export_format ) {
		switch ( $export_format ) {
			case 'xls':
			case 'csv':
				if ( is_array( $value ) && is_string( $value[ key( $value ) ] ) ) {
					$value = implode( ', ', $value );
				}

				if ( is_string( $value ) ) {
					if ( 'csv' === $export_format ) {
						// Replace CSV delimiter.
						$value = str_replace( ';', ',', $value );
					}
				}
				break;
			case 'json':
				break;
			case 'xml':
			case 'html':
				if ( is_array( $value ) && is_string( $value[ key( $value ) ] ) ) {
					$value = implode( ', ', $value );
				}

				$value = esc_html( $value );
		}

		return $value;
	}

	/**
	 * Creates a new error object.
	 *
	 * This method should be used to create the result to return in case
	 * submission value validation errors occur.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code            Error code.
	 * @param string $message         Error message.
	 * @param mixed  $validated_value Optional. Validated value to store in the database,
	 *                                regardless of it being invalid.
	 * @return WP_Error Error object to return.
	 */
	protected function create_error( $code, $message, $validated_value = null ) {
		$data = '';
		if ( null !== $validated_value ) {
			$data = array( 'validated_value' => $validated_value );
		}

		return new WP_Error( $code, $message, $data );
	}

	/**
	 * Gets the slug for a submission value edit field.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $element_id    Element ID the submission value is for.
	 * @param string $element_field Element field the submission value is for.
	 * @return string Edit field slug.
	 */
	protected function get_edit_submission_field_slug( $element_id, $element_field = '' ) {
		$element_field = ! empty( $element_field ) ? $element_field : '_main';

		return 'element_' . $element_id . '_' . $element_field . '_value';
	}

	/**
	 * Adds a settings field for specifying the element placeholder.
	 *
	 * @since 1.0.0
	 *
	 * @param string $section Optional. Settings section the settings field should be part of. Default 'settings'.
	 */
	protected function add_placeholder_settings_field( $section = 'settings' ) {
		$this->settings_fields['placeholder'] = array(
			'section'       => $section,
			'type'          => 'text',
			'label'         => __( 'Placeholder', 'torro-forms' ),
			'description'   => __( 'Placeholder text will be shown until data is being entered.', 'torro-forms' ),
			'input_classes' => array( 'regular-text' ),
		);
	}

	/**
	 * Adds a settings field for specifying the element description.
	 *
	 * @since 1.0.0
	 *
	 * @param string $section Optional. Settings section the settings field should be part of. Default 'settings'.
	 */
	protected function add_description_settings_field( $section = 'settings' ) {
		$this->settings_fields['description'] = array(
			'section'       => $section,
			'type'          => 'textarea',
			'label'         => __( 'Description', 'torro-forms' ),
			'description'   => __( 'The description will be shown below the element.', 'torro-forms' ),
			'input_classes' => array( 'widefat' ),
		);
	}

	/**
	 * Adds a settings field for specifying whether the element is required to be filled in.
	 *
	 * @since 1.0.0
	 *
	 * @param string $section Optional. Settings section the settings field should be part of. Default 'settings'.
	 */
	protected function add_required_settings_field( $section = 'settings' ) {
		$this->settings_fields['required'] = array(
			'section'     => $section,
			'type'        => 'radio',
			'label'       => __( 'Required?', 'torro-forms' ),
			'choices'     => array(
				'yes' => __( 'Yes', 'torro-forms' ),
				'no'  => __( 'No', 'torro-forms' ),
			),
			'description' => __( 'Whether the user must input something.', 'torro-forms' ),
			'default'     => 'no',
		);
	}

	/**
	 * Adds a settings field for specifying additional CSS classes for the input.
	 *
	 * @since 1.0.0
	 *
	 * @param string $section Optional. Settings section the settings field should be part of. Default 'settings'.
	 */
	protected function add_css_classes_settings_field( $section = 'settings' ) {
		$this->settings_fields['css_classes'] = array(
			'section'       => $section,
			'type'          => 'text',
			'label'         => __( 'CSS Classes', 'torro-forms' ),
			'description'   => __( 'Additional CSS Classes separated by whitespaces.', 'torro-forms' ),
			'input_classes' => array( 'regular-text' ),
		);
	}

	/**
	 * Sanitizes the settings sections.
	 *
	 * @since 1.0.0
	 */
	final protected function sanitize_settings_sections() {
		$defaults = array(
			'title' => '',
		);

		/**
		 * Filters the element_type settings sections.
		 *
		 * @since 1.0.5
		 *
		 * @param array        $settings_sections Array of settings sections.
		 * @param Element_Type $element_type      Element_Type object.
		 */
		$this->settings_sections = apply_filters( "{$this->manager->get_prefix()}element_type_settings_sections", $this->settings_sections, $this );

		foreach ( $this->settings_sections as $slug => $section ) {
			$this->settings_sections[ $slug ] = array_merge( $defaults, $section );
		}
	}

	/**
	 * Sanitizes the settings fields.
	 *
	 * @since 1.0.0
	 */
	final protected function sanitize_settings_fields() {
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

		/**
		 * Filters the element_type settings fields.
		 *
		 * @since 1.0.5
		 *
		 * @param array        $settings_fields Array of settings fields.
		 * @param Element_Type $element_type    Element_Type object.
		 */
		$this->settings_fields = apply_filters( "{$this->manager->get_prefix()}element_type_settings_fields", $this->settings_fields, $this );

		foreach ( $this->settings_fields as $slug => $field ) {
			if ( empty( $field['section'] ) || ! isset( $this->settings_sections[ $field['section'] ] ) ) {
				/* translators: %s: field section slug */
				$this->manager->error_handler()->doing_it_wrong( get_class( $this ) . '::bootstrap()', sprintf( __( 'Invalid element type field section %s.', 'torro-forms' ), esc_html( $field['section'] ) ), '1.0.0' );
				$invalid_fields[ $slug ] = true;
				continue;
			}

			if ( empty( $field['type'] ) || ! Field_Manager::is_field_type_registered( $field['type'] ) ) {
				/* translators: %s: field type slug */
				$this->manager->error_handler()->doing_it_wrong( get_class( $this ) . '::bootstrap()', sprintf( __( 'Invalid element type field type %s.', 'torro-forms' ), esc_html( $field['type'] ) ), '1.0.0' );
				$invalid_fields[ $slug ] = true;
				continue;
			}

			if ( in_array( $field['type'], array( 'multiselect', 'multibox', 'group' ), true ) || empty( $field['is_choices'] ) && 'torrochoices' === $field['type'] ) {
				/* translators: %s: field type slug */
				$this->manager->error_handler()->doing_it_wrong( get_class( $this ) . '::bootstrap()', sprintf( __( 'Disallowed element type field type %s.', 'torro-forms' ), esc_html( $field['type'] ) ), '1.0.0' );
				$invalid_fields[ $slug ] = true;
				continue;
			}

			if ( ! empty( $field['repeatable'] ) && empty( $field['is_choices'] ) ) {
				/* translators: %s: field type slug */
				$this->manager->error_handler()->doing_it_wrong( get_class( $this ) . '::bootstrap()', __( 'Disallowed repeatable element type field.', 'torro-forms' ), '1.0.0' );
				$invalid_fields[ $slug ] = true;
				continue;
			}

			$valid_sections[ $field['section'] ] = true;
			$this->settings_fields[ $slug ]      = array_merge( $defaults, $field );
		}

		$this->settings_fields   = array_diff_key( $this->settings_fields, $invalid_fields );
		$this->settings_sections = array_intersect_key( $this->settings_sections, $valid_sections );
	}
}
