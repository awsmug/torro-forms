<?php
/**
 * Core: Torro_Element_Type class
 *
 * @package TorroForms
 * @subpackage CoreModels
 * @version 1.0.0-beta.7
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Element Type base class

 * @since 1.0.0-beta.1

 * @property int    $container_id
 * @property string $label
 * @property int    $sort
 * @property string $type

 * @property-read array $sections
 * @property-read array $answers
 * @property-read array $settings
 */
abstract class Torro_Element_Type extends Torro_Base {

	/**
	 * Determines if element has an HTML input tag
	 *
	 * @since 1.0.0
	 */
	protected $input = true;

	/**
	 * Determines if element has an HTML input tag
	 *
	 * @since 1.0.0
	 */
	protected $upload = false;

	/**
	 * Determines if input has answers (e.g. radiobuttons or checkboxes)
	 *
	 * @since 1.0.0
	 */
	protected $input_answers = false;

	/**
	 * Determines if answer is an array
	 *
	 * @since 1.0.0
	 */
	protected $answer_array = false;

	/**
	 * Icon URl of the Element
	 *
	 * @since 1.0.0
	 */
	protected $icon_url = null;

	/**
	 * The settings fields
	 *
	 * @since 1.0.0
	 */
	protected $settings_fields = array();

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		$this->settings_fields();
	}

	/**
	 * Validate response data - dummy function
	 *
	 * @return mixed|Torro_Error
	 * @since 1.0.0
	 */
	public function validate( $input, $element ) {
		return stripslashes( $input );
	}

	/**
	 * Renders and returns the element type HTML output.
	 *
	 * @since 1.0.0
	 *
	 * @param Torro_Element $element
	 *
	 * @return string
	 */
	public function get_html( $element ) {
		if ( ! $this->input ) {
			return '';
		}

		ob_start();
		torro()->template( 'element-type', $this->to_json( $element ) );
		return ob_get_clean();
	}

	/**
	 * Prepares data to render the element type HTML output.
	 *
	 * @since 1.0.0
	 *
	 * @param Torro_Element $element
	 *
	 * @return array
	 */
	public function to_json( $element ) {
		$id             = apply_filters( 'torro_input_id', $this->get_input_id( $element ), $element->id );
		$name           = apply_filters( 'torro_input_name',$this->get_input_name( $element ), $element->id );
		$input_classes  = apply_filters( 'torro_input_classes', array( 'torro-form-input' ), $this );

		// $container = torro()->containers()->get( $element->superior_id );
		$form_id = torro()->forms()->get_current_form_id();

		$allow_get_param = get_post_meta( $form_id, 'allow_get_param', true );

		$value = '';
		if( empty( $element->response ) && 'yes' === $allow_get_param ) {
			if( isset( $_GET['torro_input_value_' . $element->id ] ) ) {
				$value = $_GET['torro_input_value_' . $element->id ];
			}
		} else {
			$value = $element->response;
		}

		$value = apply_filters( 'torro_input_value', $value, $element->id );

		$data = array(
			'template_suffix'	=> $this->name,
			'element_id'		=> $element->id,
			'type'				=> 'text',
			'id'				=> $id,
			'name'				=> $name,
			'classes'			=> $input_classes,
			'description'		=> '',
			'required'			=> false,
			'answers'			=> array(),
			'response'		    => $value,
			'has_error'			=> false,
			'has_success'		=> false,
			'extra_attr'		=> '',
		);

		if ( isset( $element->settings['description'] ) && ! empty( $element->settings['description']->value ) ) {
			$data['description'] = $element->settings['description']->value;
		}

		if ( isset( $element->settings['required'] ) && 'yes' === $element->settings['required']->value ) {
			$data['required'] = true;
		}

		if ( 0 < count( $element->errors ) ) {
			$data['has_error'] = true;
		} elseif ( ! is_null( $element->response ) ) {
			$data['has_success'] = true;
		}

		if ( $this->input_answers && 0 < count( $element->answers ) ) {
			foreach ( $element->answers as $answer ) {
				if ( is_wp_error( $answer ) ) {
					// element answer is missing, skip
					continue;
				}

				$data['answers'][] = array(
					'answer_id'		=> $answer->id,
					'label'			=> $answer->answer,
					'value'			=> $answer->answer,
				);
			}
		}

		return $data;
	}

	/**
	 * Is this element analyzable or not?
	 *
	 * @param obj $result_object
	 *
	 * @return boolean
	 * @since 1.0.0
	 */
	public function is_analyzable() {
		if ( ! $this->input_answers ) {
			return false;
		}
		return true;
	}

	/**
	 * Renders a value for display.
	 *
	 * @param mixed $value the unmodified value
	 *
	 * @return string the value ready to display as HTML
	 * @since 1.0.0
	 */
	public function render_value( $value ) {
		if ( 'yes' === $value ) {
			return __( 'Yes', 'torro-forms' );
		}

		if( 'no' == $value ) {
			return __( 'No', 'torro-forms' );
		}

		return nl2br( $value );
	}

	/**
	 * Renders a value for export as XLS or CSV.
	 *
	 * @param mixed $value the unmodified value
	 *
	 * @return string the value ready for export
	 * @since 1.0.0
	 */
	public function render_value_for_export( $value ) {
		if ( 'yes' === $value ) {
			return __( 'Yes', 'torro-forms' );
		}

		if( 'no' == $value ) {
			return __( 'No', 'torro-forms' );
		}

		return $value;
	}

	/**
	 * Draws element box in Admin
	 *
	 * @return string $html The admin element HTML code
	 * @since 1.0.0
	 */
	public function get_admin_html( $element ) {
		$element_id = $this->get_admin_element_id( $element );

		/**
		 * Widget
		 */
		if ( null === $element->id ) {
			$html = '<div data-element-id="' . $element_id . '" data-element-type="' . $this->name . '" class="formelement formelement-' . $this->name . '">';
		} else {
			$html = '<div data-element-id="' . $element_id . '" id="element-' . $element_id . '" data-element-type="' . $this->name . '" class="widget formelement formelement-' . $this->name . '">';
		}

		/**
		 * Widget head
		 */
		$title = empty( $element->label ) ? $this->title : $element->label;
		$title = strip_tags( $title );

		if ( 120 < strlen( $title ) ) {
			$title = substr( $title, 0, 120 ) . '...';
		}

		$html .= '<div class="widget-top">';
		$html .= '<div class="widget-title-action"><a class="widget-action hide-if-no-js"></a></div>';
		$html .= '<div class="widget-title">';

		if ( ! empty( $this->icon_url ) ) {
			$html .= '<img class="form-elements-widget-icon" src="' . $this->icon_url . '" />';
		}
		$html .= '<h4>' . $title . '</h4>';

		$html .= '</div>';
		$html .= '</div>';

		/**
		 * Widget inside
		 */
		$element_id        = $this->get_admin_element_id( $element );
		$jquery_element_id = str_replace( '#', '', $element_id );

		$html .= '<div class="widget-inside">';
		$html .= '<div class="widget-content">';

		/**
		 * Tab Navi
		 */
		$admin_tabs = array(
			array(
				'slug'		=> 'content',
				'title'		=> __( 'Content', 'torro-forms' ),
				'content'	=> $this->admin_widget_content_tab( $element ),
			),
		);
		$settings = $this->admin_widget_settings_tab( $element );
		if ( false !== $settings ) {
			$admin_tabs[] = array(
				'slug'		=> 'settings',
				'title'		=> __( 'Settings', 'torro-forms' ),
				'content'	=> $settings,
			);
		}

		$admin_tabs = apply_filters( 'torro_formbuilder_element_tabs', $admin_tabs, $element );

		if ( 1 < count( $admin_tabs ) ) {
			$html .= '<div class="tabs element-tabs">';
			$html .= '<ul>';

			foreach ( $admin_tabs as $key => $tab ) {
				$html .= '<li><a href="#tab_' . $jquery_element_id . '_' . $key . '">' . $tab['title'] . '</a></li>';
			}

			$html .= '</ul>';
		}

		$html .= '<div class="clear"></div>'; // Underline of tabs

		/**
		 * Content of Tabs
		 */
		if ( 1 < count( $admin_tabs ) ) {
			foreach ( $admin_tabs as $key => $tab ) {
				$html .= '<div id="tab_' . $jquery_element_id . '_' . $key . '" class="element-tabs-content">';
				$html .= $tab['content'];

				ob_start();
				do_action( 'torro_element_admin_tab_content_' . $tab['slug'], $element_id, $this, $key );
				do_action( 'torro_element_admin_tab_content', $element_id, $this, $key );
				$html .= ob_get_clean();

				$html .= '</div>';
			}

			$html .= '</div>';
		} else {
			foreach ( $admin_tabs as $key => $tab ) {
				$html .= $tab['content'];
				// Adding further content
				ob_start();
				do_action( 'torro_element_admin_tab_content_' . $tab['slug'], $element_id, $this, $key );
				do_action( 'torro_element_admin_tab_content', $element_id, $this, $key );
				$html .= ob_get_clean();
			}
		}

		$html .= $this->admin_widget_action_buttons( $element );

		// Adding content at the bottom
		ob_start();
		do_action( 'torro_element_admin_tabs_bottom', $element_id, $this );
		$html .= ob_get_clean();

		$html .= '</div>';
		$html .= '</div>';

		$html .= $this->admin_widget_hidden_fields( $element );

		$html .= '</div>';

		return $html;
	}

	/**
	 * Overwriting Admin Content HTML
	 *
	 * @return bool|string
	 * @since 1.0.0
	 */
	protected function admin_content_html( $element ) {
		return false;
	}

	/**
	 * Returns the ID of an input element
	 *
	 * @return string $input_id The ID of the input
	 * @since 1.0.0
	 */
	protected function get_input_id( $element ) {
		return 'torro_form_element_' . $element->id;
	}

	/**
	 * Returns the name of an input element
	 *
	 * @return string $input_name The name of the input
	 * @since 1.0.0
	 */
	protected function get_input_name( $element ) {
		if ( $this->upload ) {
			return 'torro_response_containers_' . $element->superior_id . '_elements_' . $element->id;
		}
		return 'torro_response[containers][' . $element->superior_id . '][elements][' . $element->id . ']';
	}

	/**
	 * Returns the admin name of an input element
	 *
	 * @return string $input_name The name of the input
	 * @since 1.0.0
	 */
	protected function get_admin_input_name( $element ) {
		$element_id = $this->get_admin_element_id( $element );
		$container_id = $this->get_admin_container_id( $element );

		$input_name = 'containers[' . $container_id . '][elements][' . $element_id . ']';

		return $input_name;
	}

	/**
	 * Returns the widget id which will be used in HTML
	 *
	 * @return string $element_id The widget id
	 * @since 1.0.0
	 */
	protected function get_admin_element_id( $element ) {
		if ( ! $element->id ) {
			return $this->get_empty_element_id();
		}

		return $element->id;
	}

	/**
	 * Gets container ID for containers in Admin
	 *
	 * @return null|string
	 * @since 1.0.0
	 */
	protected function get_admin_container_id( $element ) {
		if ( ! $element->superior_id ) {
			return $this->get_empty_container_id();
		}

		return $element->superior_id;
	}

	protected function get_empty_element_id() {
		return 'replace_element_id';
	}

	protected function get_empty_container_id() {
		return 'replace_container_id';
	}

	/**
	 * Settings fields - dummy function
	 * @since 1.0.0
	 */
	protected function settings_fields() {}

	/**
	 * Content of the content tab
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	protected function admin_widget_content_tab( $element ) {
		$content_html = $this->admin_content_html( $element );
		$admin_input_name = $this->get_admin_input_name( $element );

		if ( false === $content_html ) {
			// Label
			$html = '<label for="' . $admin_input_name . '[label]">' . __( 'Label ', 'torro-forms' ) . '</label><input type="text" name="' . $admin_input_name . '[label]" value="' . $element->label . '" class="form-label" />';

			// Answers
			if ( $this->input_answers ) {
				// Answers have sections
				if ( property_exists( $element, 'sections' ) && is_array( $element->sections ) && 0 < count( $element->sections ) ) {
					foreach ( $element->sections as $section_key => $section_name ) {
						$html .= '<div class="element-section" id="section_' . $section_key . '">';
						$html .= '<p>' . esc_html( $section_name ) . '</p>';
						$html .= $this->admin_widget_content_answers( $element, $section_key );
						$html .= '<input type="hidden" name="section_key" value="' . $section_key . '" />';
						$html .= '</div>';
					}
					// Answers without sections
				} else {
					$html .= '<p>' . esc_html__( 'Answer/s:', 'torro-forms' ) . '</p>';
					$html .= $this->admin_widget_content_answers( $element );
				}
			}

			$html .= '<div class="clear"></div>';
		} else {
			$html = $content_html;
		}

		return $html;
	}

	/**
	 * Content of the answers under the form element
	 *
	 * @param string $section Name of the section
	 *
	 * @return string $html The answers HTML
	 * @since 1.0.0
	 */
	protected function admin_widget_content_answers( $element, $section = null ) {
		$element_id        = $this->get_admin_element_id( $element );
		$container_id      = $this->get_admin_container_id( $element );
		$admin_input_name  = $this->get_admin_input_name( $element );

		$html = '';

		if ( is_array( $element->answers ) ) {
			$html .= '<div class="answers">';

			foreach ( $element->answers as $answer ) {
				if ( null !== $section ) {
					if ( $answer->section !== $section ) {
						continue;
					}
				}

				$html .= '<div class="answer" id="answer_' . $answer->id . '">';

				$html .= '<p><input type="text" name="' . $admin_input_name . '[answers][id_' . $answer->id . '][answer]" value="' . esc_attr( $answer->answer ) . '" class="element-answer" /></p>';
				$html .= '<input type="button" value="' . esc_attr__( 'Delete', 'torro-forms' ) . '" class="delete_answer button answer_action">';

				$html .= '<input type="hidden" name="' . $admin_input_name . '[answers][id_' . $answer->id . '][id]" value="' . esc_attr( $answer->id ) . '" />';
				$html .= '<input type="hidden" name="' . $admin_input_name . '[answers][id_' . $answer->id . '][sort]" value="' . esc_attr( $answer->sort ) . '" />';
				$html .= null !== $section ? '<input type="hidden" name="' . $admin_input_name . '[answers][id_' . $answer->id . '][section]" value="' . esc_attr( $section ) . '" />' : '';

				$html .= '</div>';
			}

			$html .= '</div>';
			$html .= '<div class="clear"></div>';
		} else {
			if ( $this->input_answers ) {
				$param_arr[]    = $this->create_answer_syntax;
				$temp_answer_id = torro_generate_temp_id();

				$html .= '<div class="answers">';
				$html .= '<div class="answer" id="answer_' . $temp_answer_id . '">';
				$html .= '<p><input type="text" name="' . $admin_input_name . '[answers][' . $temp_answer_id . '][answer]" value="" class="element-answer" /></p>';
				$html .= ' <input type="button" value="' . esc_attr__( 'Delete', 'torro-forms' ) . '" class="delete_answer button answer_action">';
				$html .= '<input type="hidden" name="' . $admin_input_name . '[answers][' . $temp_answer_id . '][id]" value="" />';
				$html .= '<input type="hidden" name="' . $admin_input_name . '[answers][' . $temp_answer_id . '][sort]" value="0" />';

				if ( null !== $section ) {
					$html .= '<input type="hidden" name="' . $admin_input_name . '[answers][' . $temp_answer_id . '][section]" value="' . esc_attr( $section ) . '" />';
				}

				$html .= '</div>';
				$html .= '</div><div class="clear"></div>';
			}
		}

		$html .= '<a class="add-answer" data-container-id="' . $container_id . '" data-element-id="' . $element_id . '">+ ' . __( 'Add Answer', 'torro-forms' ) . ' </a>';

		return $html;
	}

	/**
	 * Content of the settings tab
	 *
	 * @return string $html The settings tab HTML
	 * @since 1.0.0
	 */
	protected function admin_widget_settings_tab( $element ) {
		$html = '';

		if ( is_array( $this->settings_fields ) && 0 < count( $this->settings_fields ) ) {
			foreach ( $this->settings_fields as $name => $field ) {
				$html .= $this->admin_widget_settings_field( $name, $field, $element );
			}

			return $html;
		}

		return false;
	}

	/**
	 * Creating a settings field
	 *
	 * @param string $name  Internal name of the field
	 * @param array  $field Field settings
	 *
	 * @return string $html The field HTML
	 * @since 1.0.0
	 */
	protected function admin_widget_settings_field( $name, $field, $element ) {
		$value = '';

		if ( isset( $element->settings[ $name ] ) && isset( $element->settings[ $name ]->id ) ) {
			$id = $element->settings[ $name ]->id;
			$name = $element->settings[ $name ]->name;
			$value = maybe_unserialize( $element->settings[ $name ]->value );
		} else {
			$id = torro_generate_temp_id();
		}

		if ( empty( $value ) ) {
			$value = $field['default'];
		}

		$html = '<div class="torro-form-fieldset">';

		$base_name = $this->get_admin_input_name( $element ) . '[settings][' . $id . ']';
		$input_name = $base_name . '[value]';
		$input_id = 'settings_' . $id;

		switch ( $field['type'] ) {
			case 'text':
				$html .= '<div class="torro-form-fieldset-title">';
				$html .= '<label for="' . $input_id . '">' . $field[ 'title' ] . '</label>';
				$html .= '</div>';

				$html .= '<div class="torro-form-fieldset-input">';
				$html .= '<input type="text" id="' . $input_id . '" name="' . $input_name . '" value="' . esc_attr( $value ) . '" />';
				$html .= '<input type="hidden" name="' . $base_name . '[id]" value="' . $id . '">';
				$html .= '<input type="hidden" name="' . $base_name . '[name]" value="' . $name . '">';
				$html .= '<small>' . $field[ 'description' ] . '</small>';
				$html .= '</div>';

				$html .= '<div class="clear"></div>';

				break;
			case 'textarea':
				$html .= '<div class="torro-form-fieldset-title">';
				$html .= '<label for="' . $input_id . '">' . $field[ 'title' ] . '</label>';
				$html .= '</div>';

				$html .= '<div class="torro-form-fieldset-input">';
				$html .= '<textarea id="' . $input_id . '" name="' . $input_name . '">' . esc_html( $value ) . '</textarea><br />';
				$html .= '<input type="hidden" name="' . $base_name . '[id]" value="' . $id . '">';
				$html .= '<input type="hidden" name="' . $base_name . '[name]" value="' . $name . '">';
				$html .= '<small>' . $field[ 'description' ] . '</small>';
				$html .= '</div>';

				$html .= '<div class="clear"></div>';
				break;
			case 'wp_editor':
				$html .= '<div class="torro-form-fieldset-title">';
				$html .= '<label for="' . $input_id . '">' . $field[ 'title' ] . '</label>';
				$html .= '</div>';

				$settings = array(
					'textarea_name' => $name
				);
				ob_start();
				wp_editor( $value, $input_id, $settings );
				$input = ob_get_clean();

				$html .= '<div class="torro-form-fieldset-input">';
				$html .= $input;
				$html .= '<input type="hidden" name="' . $base_name . '[id]" value="' . $id . '">';
				$html .= '<input type="hidden" name="' . $base_name . '[name]" value="' . $name . '">';
				$html .= '<small>' . $field[ 'description' ] . '</small>';
				$html .= '</div>';

				$html .= '<div class="clear"></div>';
				break;
			case 'select':
				$html .= '<div class="torro-form-fieldset-title">';
				$html .= '<label for="' . $input_id . '">' . $field[ 'title' ] . '</label>';
				$html .= '</div>';

				$input = '<select id="' . $input_id . '" name="' . $input_name . '">';
				foreach ( $field['values'] as $field_key => $field_value ) {
					$selected = '';
					if ( $value === $field_key ) {
						$selected = ' selected="selected"';
					}
					$input .= '<option value="' . $field_key . '"' . $selected . '>' . esc_html( $field_value ) . '</option>';
				}
				$input .= '</select>';

				$html .= '<div class="torro-form-fieldset-input">';
				$html .= $input;
				$html .= '<input type="hidden" name="' . $base_name . '[id]" value="' . $id . '">';
				$html .= '<input type="hidden" name="' . $base_name . '[name]" value="' . $name . '">';
				$html .= '<small>' . $field[ 'description' ] . '</small>';
				$html .= '</div>';

				$html .= '<div class="clear"></div>';

				break;
			case 'radio':
				$html .= '<fieldset>';
				$html .= '<div class="torro-form-fieldset-title">';
				$html .= '<legend>' . $field[ 'title' ] . '</legend>';
				$html .= '</div>';

				$i = 0;
				foreach ( $field['values'] as $field_key => $field_value ) {
					$checked = '';
					if ( $value === $field_key ) {
						$checked = ' checked="checked"';
					}

					$html .= '<span class="torro-form-fieldset-input-radio"><input type="radio" id="' . $input_id . '_' . $i . '" name="' . $input_name . '" value="' . $field_key . '"' . $checked . ' /> <label for="' . $input_id . '_' . $i . '">' . esc_html( $field_value ) . '</label></span>';
					$i++;
				}
				$html .= '<input type="hidden" name="' . $base_name . '[id]" value="' . $id . '">';
				$html .= '<input type="hidden" name="' . $base_name . '[name]" value="' . $name . '">';
				$html .= '</fieldset>';

				$html .= '<div class="clear"></div>';
				break;
			case 'checkbox':
				$html .= '<fieldset>';
				$html .= '<div class="torro-form-fieldset-title">';
				$html .= '<legend>' . $field[ 'title' ] . '</legend>';
				$html .= '</div>';

				$i = 0;
				foreach ( $field['values'] as $field_value => $field_label ) {
					$checked = '';
					if ( is_array( $value ) && in_array( $field_value, $value ) ) {
						$checked = ' checked="checked"';
					}

					$html .= '<span class="torro-form-fieldset-input-checkbox"><input type="checkbox" id="' . $input_id . '_' . $i . '" name="' . $input_name . '[]" value="' . $field_value . '"' . $checked . ' /> <label for="' . $input_id . '_' . $i . '">' . esc_html( $field_label ) . '</label></span>';
					$i++;
				}

				$html .= '<input type="hidden" name="' . $base_name . '[id]" value="' . $id . '">';
				$html .= '<input type="hidden" name="' . $base_name . '[name]" value="' . $name . '">';

				$html .= '</fieldset>';

				$html .= '<div class="clear"></div>';
				break;
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Adding form action buttons to elements
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	protected function admin_widget_action_buttons( $element ) {
		// Adding action Buttons
		$bottom_buttons = apply_filters( 'torro_element_bottom_actions', array(
			'delete_form_element' => array(
				'text'			=> __( 'Delete Element', 'torro-forms' ),
				'classes'		=> 'delete_form_element',
				'destructive'	=> true,
			),
		), $element );

		$html = '<div class="form-element-buttons">';
		$html .= '<ul>';
		foreach ( $bottom_buttons as $button ) {
			$base_class = ( isset( $button['destructive'] ) && $button['destructive'] ) ? 'delete-button' : 'button';
			$html .= '<li><a class="' . $button[ 'classes' ] . ' form-element-bottom-action ' . $base_class . '">' . esc_html( $button[ 'text' ] ) . '</a></li>';
		}
		$html .= '</ul>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Hidden fields
	 *
	 * @return string
	 * @since 1.0.0
	 */
	protected function admin_widget_hidden_fields( $element ) {
		$admin_input_name = $this->get_admin_input_name( $element );

		$html = '<input type="hidden" name="' . $admin_input_name . '[id]" value="' . $element->id . '" />';
		$html .= '<input type="hidden" name=' . $admin_input_name . '[container_id]" value="' . $element->superior_id . '" />';
		$html .= '<input type="hidden" name="' . $admin_input_name . '[sort]" value="' . $element->sort . '" />';
		$html .= '<input type="hidden" name="' . $admin_input_name . '[type]" value="' . $this->name . '" />';
		$html .= '<input type="hidden" name="' . $admin_input_name . '[has_answers]" value="' . ( $this->input_answers ? 'yes' : 'no' ) . '" />';
		$html .= '<input type="hidden" name="' . $admin_input_name . '[sections]" value="' . ( property_exists( $element, 'sections' ) && is_array( $element->sections ) && 0 < count( $element->sections ) ? 'yes' : 'no' ) . '" />';

		return $html;
	}
}
