<?php
/**
 * Textfield element type class
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
 * Class representing a text element type.
 *
 * @since 1.0.0
 */
class Textfield extends Element_Type {

	/**
	 * Bootstraps the element type by setting properties.
	 *
	 * @since 1.0.0
	 */
	protected function bootstrap() {
		$this->slug        = 'textfield';
		$this->title       = __( 'Text', 'torro-forms' );
		$this->description = __( 'A single text field element.', 'torro-forms' );
		$this->icon_svg_id = 'torro-icon-textfield';

		$input_types = array();
		foreach ( $this->get_input_types() as $slug => $data ) {
			if ( empty( $data['title'] ) ) {
				continue;
			}

			$input_types[ $slug ] = $data['title'];
		}

		$this->add_placeholder_settings_field();
		$this->add_description_settings_field();
		$this->add_required_settings_field();
		$this->settings_fields['min_length'] = array(
			'section'       => 'settings',
			'type'          => 'number',
			'label'         => __( 'Minimum length', 'torro-forms' ),
			'description'   => __( 'The minimum number of chars which can be typed in.', 'torro-forms' ),
			'input_classes' => array( 'small-text' ),
			'min'           => 0,
			'step'          => 1,
		);
		$this->settings_fields['max_length'] = array(
			'section'       => 'settings',
			'type'          => 'number',
			'label'         => __( 'Maximum length', 'torro-forms' ),
			'description'   => __( 'The maximum number of chars which can be typed in.', 'torro-forms' ),
			'input_classes' => array( 'small-text' ),
			'min'           => 0,
			'step'          => 1,
		);
		$this->settings_fields['input_type'] = array(
			'section'     => 'settings',
			'type'        => 'radio',
			'label'       => __( 'Input type', 'torro-forms' ),
			'choices'     => $input_types,
			/* translators: %s: HTML input type info URL */
			'description' => sprintf( __( '* Will be validated | Not all <a href="%s" target="_blank">HTML5 input types</a> are supported by browsers!', 'torro-forms' ), 'http://www.wufoo.com/html5/' ),
			'default'     => 'text',
		);
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
		$settings = $this->get_settings( $element );

		$input_type = ! empty( $settings['input_type'] ) ? $settings['input_type'] : 'text';
		$input_type = $this->get_input_type( $input_type );
		if ( $input_type && isset( $input_type['html_field_type'] ) ) {
			$input_type = $input_type['html_field_type'];
		} else {
			$input_type = 'text';
		}

		$data['input_attrs']['type'] = $input_type;

		$data = parent::filter_json( $data, $element, $submission );

		$limits_text = '';
		if ( ! empty( $settings['min_length'] ) && ! empty( $settings['max_length'] ) ) {
			/* translators: 1: minimum length, 2: maximum length */
			$limits_text = sprintf( __( 'Between %1$s and %2$s characters are required.', 'torro-forms' ), number_format_i18n( $settings['min_length'] ), number_format_i18n( $settings['max_length'] ) );
		} elseif ( ! empty( $settings['min_length'] ) ) {
			/* translators: %s: minimum length */
			$limits_text = sprintf( __( 'At least %s characters are required.', 'torro-forms' ), number_format_i18n( $settings['min_length'] ) );
		} elseif ( ! empty( $settings['max_length'] ) ) {
			/* translators: %s: maximum length */
			$limits_text = sprintf( __( 'A maximum of %s characters are allowed.', 'torro-forms' ), number_format_i18n( $settings['max_length'] ) );
		}

		if ( ! empty( $limits_text ) ) {
			if ( ! empty( $data['description'] ) ) {
				$data['description'] .= '<br>';
			} else {
				$data['description']                     = '';
				$data['input_attrs']['aria-describedby'] = $data['description_attrs']['id'];
			}

			$data['description'] .= $limits_text;
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
	 * @return mixed|WP_Error Validated value, or error object on failure.
	 */
	public function validate_field( $value, $element, $submission ) {
		$settings = $this->get_settings( $element );

		$value = trim( (string) $value );

		if ( ! empty( $settings['required'] ) && 'no' !== $settings['required'] && empty( $value ) ) {
			return $this->create_error( Element_Type::ERROR_CODE_REQUIRED, __( 'You must enter something here.', 'torro-forms' ), $value );
		}

		if ( ! empty( $value ) ) {
			if ( ! empty( $settings['min_length'] ) && strlen( $value ) < (int) $settings['min_length'] ) {
				return $this->create_error( 'value_too_short', __( 'The value you entered is too short.', 'torro-forms' ), $value );
			}

			if ( ! empty( $settings['max_length'] ) && strlen( $value ) > (int) $settings['max_length'] ) {
				return $this->create_error( 'value_too_long', __( 'The value you entered is too long.', 'torro-forms' ), $value );
			}

			if ( ! empty( $settings['input_type'] ) ) {
				$input_type = $this->get_input_type( $settings['input_type'] );
				if ( $input_type ) {
					$status = true;
					if ( isset( $input_type['callback'] ) && $input_type['callback'] && is_callable( $input_type['callback'] ) ) {
						$status = call_user_func( $input_type['callback'], $value );
					} elseif ( isset( $input_type['pattern'] ) && $input_type['pattern'] ) {
						$status = preg_match( '/' . $input_type['pattern'] . '/i', $value );
					}

					if ( ! $status ) {
						$message = ! empty( $input_type['error_message'] ) ? $input_type['error_message'] : __( 'The value you entered is invalid.', 'torro-forms' );

						return $this->create_error( 'value_invalid', $message, $value );
					}
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

		$slug     = $this->get_edit_submission_field_slug( $element->id );
		$settings = $this->get_settings( $element );

		$input_type = ! empty( $settings['input_type'] ) ? $settings['input_type'] : 'text';
		$input_type = $this->get_input_type( $input_type );
		if ( $input_type && isset( $input_type['html_field_type'] ) ) {
			switch ( $input_type['html_field_type'] ) {
				case 'datetime':
				case 'date':
				case 'time':
					$fields[ $slug ]['type']  = 'datetime';
					$fields[ $slug ]['store'] = $input_type['html_field_type'];
					break;
				default:
					$fields[ $slug ]['type'] = $input_type['html_field_type'];
			}
		} else {
			$fields[ $slug ]['type'] = 'text';
		}

		return $fields;
	}

	/**
	 * Returns the available input types for a text element field.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$slug => $data` pairs.
	 */
	protected function get_input_types() {
		$input_types = array(
			'text'           => array(
				'title'           => __( 'Standard Text', 'torro-forms' ),
				'html_field_type' => 'text',
			),
			'password'       => array(
				'title'           => __( 'Password', 'torro-forms' ),
				'html_field_type' => 'password',
			),
			'date'           => array(
				'title'           => __( 'Date', 'torro-forms' ),
				'html_field_type' => 'date',
			),
			'email_address'  => array(
				'title'           => __( 'Email-Address *', 'torro-forms' ),
				'html_field_type' => 'email',
				'callback'        => 'is_email',
				'error_message'   => __( 'The value you entered is not a valid email-address.', 'torro-forms' ),
			),
			'color'          => array(
				'title'           => __( 'Color', 'torro-forms' ),
				'html_field_type' => 'color',
			),
			'number'         => array(
				'title'           => __( 'Number *', 'torro-forms' ),
				'html_field_type' => 'number',
				'pattern'         => '^[0-9]{1,}$',
				'error_message'   => __( 'The value you entered is not a valid number.', 'torro-forms' ),
			),
			'number_decimal' => array(
				'title'           => __( 'Decimal Number *', 'torro-forms' ),
				'html_field_type' => 'number',
				'pattern'         => '^-?([0-9])+\.?([0-9])+$',
				'error_message'   => __( 'The value you entered is not a valid decimal number.', 'torro-forms' ),
			),
			'search'         => array(
				'title'           => __( 'Search', 'torro-forms' ),
				'html_field_type' => 'search',
			),
			'tel'            => array(
				'title'           => __( 'Telephone', 'torro-forms' ),
				'html_field_type' => 'tel',
			),
			'time'           => array(
				'title'           => __( 'Time', 'torro-forms' ),
				'html_field_type' => 'time',
			),
			'url'            => array(
				'title'           => __( 'URL *', 'torro-forms' ),
				'html_field_type' => 'url',
				'pattern'         => '\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]',
				'error_message'   => __( 'The value you entered is not a valid URL.', 'torro-forms' ),
			),
			'week'           => array(
				'title'           => __( 'Week', 'torro-forms' ),
				'html_field_type' => 'week',
			),
		);

		/**
		 * Filters the available input types for a text element field.
		 *
		 * @since 1.0.0
		 *
		 * @param array $input_types Associative array of `$slug => $data` pairs.
		 */
		return apply_filters( "{$this->manager->get_prefix()}element_textfield_input_types", $input_types );
	}

	/**
	 * Returns a specific input type for a text element field.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Input type identifier.
	 * @return array|false Input type data for the identifier, or false if not found.
	 */
	protected function get_input_type( $slug ) {
		$input_types = $this->get_input_types();

		if ( ! isset( $input_types[ $slug ] ) ) {
			return false;
		}

		return $input_types[ $slug ];
	}
}
