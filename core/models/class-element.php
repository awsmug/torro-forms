<?php
/**
 * Elements abstraction class
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 awesome.ug (support@awesome.ug)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Torro_Form_Element extends Torro_Base {
	/**
	 * ID of instanced element
	 *
	 * @since 1.0.0
	 */
	protected $id = null;

	/**
	 * Contains the form ID of the element
	 *
	 * @since 1.0.0
	 */
	protected $form_id = null;

	/**
	 * Contains the Container ID of the Element
	 *
	 * @since 1.0.0
	 */
	protected $container_id = null;

	/**
	 * Element type
	 *
	 * @since 1.0.0
	 */
	protected $type = null;

	/**
	 * Determines if element has an HTML input tag
	 *
	 * @since 1.0.0
	 */
	protected $input = true;

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
	 * Element Label
	 *
	 * @since 1.0.0
	 */
	protected $label = null;

	/**
	 * Sort number where to display the Element
	 *
	 * @since 1.0.0
	 */
	protected $sort = 0;

	/**
	 * Sections for answers
	 *
	 * @since 1.0.0
	 */
	protected $sections = array();

	/**
	 * Element answers
	 *
	 * @since 1.0.0
	 */
	protected $answers = array();

	/**
	 * Contains users response of an Element
	 *
	 * @since 1.0.0
	 */
	protected $response = array();

	/**
	 * Contains Admin tabs
	 *
	 * @since 1.0.0
	 */
	protected $admin_tabs = array();

	/**
	 * The settings fields
	 *
	 * @since 1.0.0
	 */
	protected $settings_fields = array();

	/**
	 * Contains all settings of the element
	 *
	 * @since 1.0.0
	 */
	protected $settings = array();

	/**
	 * Contains element validation errors
	 *
	 * @since 1.0.0
	 */
	protected $validate_errors = array();

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct( $id = null ) {
		parent::__construct();

		$this->populate( $id );
		$this->settings_fields();
	}

	/**
	 * Populating element object with data
	 *
	 * @param int $id Element id
	 *
	 * @since 1.0.0
	 */
	private function populate( $id ) {
		global $wpdb;

		if ( ! empty( $id ) ) {

			$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->torro_elements} WHERE id = %s", $id );
			$row = $wpdb->get_row( $sql );

			$this->id           = $id;
			$this->label        = $row->label;
			$this->form_id      = $row->form_id;
			$this->container_id = $row->container_id;
			$this->sort         = $row->sort;
			$this->type         = $row->type;

			// Todo: Move to own function
			$sql     = $wpdb->prepare( "SELECT * FROM {$wpdb->torro_element_answers} WHERE element_id = %s ORDER BY sort ASC", $id );
			$answers = $wpdb->get_results( $sql );
			if ( is_array( $answers ) ) {
				foreach ( $answers as $answer ) {
					$this->add_answer( $answer->answer, $answer->sort, $answer->id, $answer->section );
				}
			}

			// Todo: Move to own function
			$sql      = $wpdb->prepare( "SELECT id FROM $wpdb->torro_settings WHERE element_id = %s", $id );
			$settings_ids = $wpdb->get_col( $sql );
			if ( is_array( $settings_ids ) ) {
				foreach ( $settings_ids as $settings_id ) {
					$torro_setting = new Torro_Element_Setting( $settings_id );
					$this->add_setting( $torro_setting );
				}
			}
		}
	}

	/**
	 * Adding answer to object data
	 *
	 * @param string $text    Answer text
	 * @param int    $sort    Sort number
	 * @param int    $id      Answer ID from DB
	 * @param string $section Section of answer
	 *
	 * @return boolean $is_added true if answer was added, False if not
	 * @since 1.0.0
	 */
	protected function add_answer( $text, $sort = false, $id = null, $section = null ) {
		if ( '' === $text ) {
			return false;
		}

		$this->answers[ $id ] = array(
			'id'      => $id,
			'text'    => $text,
			'sort'    => $sort,
			'section' => $section,
		);

		return true;
	}

	/**
	 * Add setting to object data
	 *
	 * @param string $name  Name of setting
	 * @param string $value Value of setting
	 *
	 * @since 1.0.0
	 */
	protected function add_setting( $setting ) {
		$this->settings[ $setting->name ] = $setting;
	}

	/**
	 * Returns the admin name of an input element
	 *
	 * @return string $input_name The name of the input
	 * @since 1.0.0
	 */
	public function get_admin_input_name() {
		$element_id    = $this->get_admin_element_id();
		$container_id = $this->get_admin_cotainer_id();

		$input_name = 'containers[' . $container_id . '][elements][' . $element_id . ']';

		return $input_name;
	}

	/**
	 * Returns the widget id which will be used in HTML
	 *
	 * @return string $element_id The widget id
	 * @since 1.0.0
	 */
	protected function get_admin_element_id() {
		// Getting Widget ID
		if ( null === $this->id ) {
			// New Element
			$element_id = 'element_id';
		} else {
			// Existing Element
			$element_id = $this->id;
		}

		return $element_id;
	}

	/**
	 * Gets container ID for containers in Admin
	 *
	 * @return null|string
	 */
	protected function get_admin_cotainer_id() {
		// Getting Widget ID
		if ( null === $this->container_id ) {
			// New Element
			$container_id = 'container_id';
		} else {
			// Existing Element
			$container_id = $this->container_id;
		}

		return $container_id;
	}

	/**
	 * Settings fields - dummy function
	 */
	protected function settings_fields() {
	}

	/**
	 * Validate response data - dummy function
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function validate( $input ) {
		return true;
	}

	/**
	 * Drawing Element on frontend
	 *
	 * @return string $html Element HTML
	 * @since 1.0.0
	 */
	public function get_html() {
		$response_errors = torro()->forms()->get_response_errors();

		$errors = '';
		if ( is_array( $response_errors ) && array_key_exists( $this->id, $response_errors ) ) {
			$errors = $response_errors[ $this->id ];
		}

		$html = '';

		$element_classes = array( 'torro-element', 'torro-element-' . $this->id );
		$element_classes = apply_filters( 'torro_element_classes', $element_classes, $this );

		$html .= '<div class="' . esc_attr( implode( ' ', $element_classes ) ) . '">';

		ob_start();
		do_action( 'torro_form_element_start', $this->id );
		$html .= ob_get_clean();

		if ( is_array( $errors ) && 0 < count( $errors ) ) {
			$html .= '<div class="torro-element-error">';
			$html .= '<div class="torro-element-error-message">';
			$html .= '<ul class="torro-error-messages">';
			foreach ( $errors as $error ) {
				$html .= '<li>' . $error . '</li>';
			}
			$html .= '</ul></div>';

			$html = apply_filters( 'draw_element_errors', $html, $this );
		}

		$this->get_response();

		if ( 0 === count( $this->answers ) && true === $this->input_answers ) {
			$html .= '<p>' . esc_html__( 'You did not enter any answers. Please add some to display answers here.', 'torro-forms' ) . '</p>';
		} else {
			$html .= $this->input_html();
		}

		// End Echo Errors
		if ( is_array( $errors ) && 0 < count( $errors ) ) {
			$html .= '</div>';
		}

		ob_start();
		do_action( 'torro_form_element_end', $this->id );
		$html .= ob_get_clean();

		$html .= '</div>';

		return $html;
	}

	/**
	 * Getting element data from Session
	 *
	 * @return array $response The post response
	 * @since 1.0.0
	 */
	protected function get_response() {
		$form_id = torro()->forms()->get_current_form_id();

		$this->response = false;

		if ( ! empty( $form_id ) && isset( $_SESSION[ 'torro_response' ] ) && isset( $_SESSION[ 'torro_response' ][ $form_id ] ) && isset( $_SESSION[ 'torro_response' ][ $form_id ][ $this->id ] ) ) {
			$this->response = $_SESSION[ 'torro_response' ][ $form_id ][ $this->id ];
		} else {
			return false;
		}

		return $this->response;
	}

	/**
	 * Contains element HTML on frontend - Have to be overwritten by child classes
	 *
	 * @return string $html Element frontend HTML
	 * @since 1.0.0
	 */
	public function input_html() {
		return '<p>' . esc_html__( 'No HTML for Element given. Please check element sourcecode.', 'torro-forms' ) . '</p>';
	}

	/**
	 * Draws element box in Admin
	 *
	 * @return string $html The admin element HTML code
	 * @since 1.0.0
	 */
	public function get_admin_html() {
		$element_id = $this->get_admin_element_id();

		/**
		 * Widget
		 */
		if ( null === $this->id ) {
			$html = '<div data-element-id="' . $element_id . '" data-element-type="' . $this->type . '" class="formelement formelement-' . $this->type . '">';
		} else {
			$html = '<div data-element-id="' . $element_id . '" id="' . $element_id . '" data-element-type="' . $this->type . '" class="widget formelement formelement-' . $this->type . '">';
		}

		/**
		 * Widget head
		 */
		$title = empty( $this->label ) ? $this->title : $this->label;
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
		$element_id        = $this->get_admin_element_id();
		$jquery_element_id = str_replace( '#', '', $element_id );

		$html .= '<div class="widget-inside">';
		$html .= '<div class="widget-content">';

		/**
		 * Tab Navi
		 */
		$this->add_admin_tab( esc_attr__( 'Content', 'torro-forms' ), $this->admin_widget_content_tab() );

		$settings = $this->admin_widget_settings_tab();
		if ( false !== $settings ) {
			$this->add_admin_tab( esc_attr__( 'Settings', 'torro-forms' ), $settings );
		}

		$admin_tabs = apply_filters( 'torro_formbuilder_element_tabs', $this->admin_tabs );

		if ( 1 < count( $admin_tabs ) ) {
			$html .= '<div class="form_element_tabs">';
			$html .= '<ul class="tabs">';

			foreach ( $admin_tabs as $key => $tab ) {
				$html .= '<li><a href="#tab_' . $jquery_element_id . '_' . $key . '">' . $tab[ 'title' ] . '</a></li>';
			}

			$html .= '</ul>';
		}

		$html .= '<div class="clear"></div>'; // Underline of tabs

		/**
		 * Content of Tabs
		 */
		if ( 1 < count( $admin_tabs ) ) {
			foreach ( $admin_tabs as $key => $tab ) {
				$html .= '<div id="tab_' . $jquery_element_id . '_' . $key . '">';
				$html .= $tab[ 'content' ];
				$html .= '</div>';
			}

			$html .= '</div>';
		} else {
			foreach ( $admin_tabs as $key => $tab ) {
				$html .= $tab[ 'content' ];
			}
		}

		// Adding further content
		ob_start();
		do_action( 'torro_element_admin_tabs_content', $this );
		$html .= ob_get_clean();

		$html .= $this->admin_widget_action_buttons();

		// Adding content at the bottom
		ob_start();
		do_action( 'torro_element_admin_tabs_bottom', $this );
		$html .= ob_get_clean();

		$html .= '</div>';
		$html .= '</div>';

		$html .= $this->admin_widget_hidden_fields();

		$html .= '</div>';

		return $html;
	}

	/**
	 * Adds Tab for Element
	 *
	 * @param string $title
	 * @param string $content
	 */
	public function add_admin_tab( $title, $content ) {
		$this->admin_tabs[] = array(
			'title'   => $title,
			'content' => $content
		);
	}

	/**
	 * Content of the content tab
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	protected function admin_widget_content_tab() {
		$content_html     = $this->admin_content_html();
		$admin_input_name     = $this->get_admin_input_name();

		if ( false === $content_html ) {
			// Label
			$html = '<label for="' . $admin_input_name . '[label]">' . __( 'Label ', 'torro-forms' ) . '</label><input type="text" name="' . $admin_input_name . '[label]" value="' . $this->label . '" class="form-label" />';

			// Answers
			if ( $this->input_answers ) {
				// Answers have sections
				if ( property_exists( $this, 'sections' ) && is_array( $this->sections ) && 0 < count( $this->sections ) ) {
					foreach ( $this->sections as $section_key => $section_name ) {
						$html .= '<div class="element-section" id="section_' . $section_key . '">';
						$html .= '<p>' . esc_html( $section_name ) . '</p>';
						$html .= $this->admin_widget_content_answers( $section_key );
						$html .= '<input type="hidden" name="section_key" value="' . $section_key . '" />';
						$html .= '</div>';
					}
					// Answers without sections
				} else {
					$html .= '<p>' . esc_html__( 'Answer/s:', 'torro-forms' ) . '</p>';
					$html .= $this->admin_widget_content_answers();
				}
			}

			$html .= '<div class="clear"></div>';
		} else {
			$html = $content_html;
		}

		return $html;
	}

	/**
	 * Overwriting Admin Content HTML
	 *
	 * @return bool|string
	 * @since 1.0.0
	 */
	public function admin_content_html() {
		return false;
	}

	/**
	 * Content of the answers under the form element
	 *
	 * @param string $section Name of the section
	 *
	 * @return string $html The answers HTML
	 * @since 1.0.0
	 */
	protected function admin_widget_content_answers( $section = null ) {
		$element_id        = $this->get_admin_element_id();
		$admin_input_name  = $this->get_admin_input_name();

		$html = '';

		if ( is_array( $this->answers ) ) {
			$html .= '<div class="answers">';

			foreach ( $this->answers as $answer ) {
				if ( null !== $section ) {
					if ( $answer[ 'section' ] !== $section ) {
						continue;
					}
				}

				$html .= '<div class="answer" id="answer_' . $answer[ 'id' ] . '">';

				$html .= '<p><input type="text" name="' . $admin_input_name . '[answers][id_' . $answer[ 'id' ] . '][answer]" value="' . esc_attr( $answer[ 'text' ] ) . '" class="element-answer" /></p>';
				$html .= '<input type="button" value="' . esc_attr__( 'Delete', 'torro-forms' ) . '" class="delete_answer button answer_action">';

				$html .= '<input type="hidden" name="' . $admin_input_name . '[answers][id_' . $answer[ 'id' ] . '][id]" value="' . esc_attr( $answer[ 'id' ] ) . '" />';
				$html .= '<input type="hidden" name="' . $admin_input_name . '[answers][id_' . $answer[ 'id' ] . '][sort]" value="' . esc_attr( $answer[ 'sort' ] ) . '" />';
				$html .= null !== $section ? '<input type="hidden" name="' . $admin_input_name . '[answers][id_' . $answer[ 'id' ] . '][section]" value="' . esc_attr( $section ) . '" />' : '';

				$html .= '</div>';
			}

			$html .= '</div>';
			$html .= '<div class="clear"></div>';
		} else {
			if ( $this->input_answers ) {
				$param_arr[]    = $this->create_answer_syntax;
				$temp_answer_id = 'temp_id_' . time() * rand();

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

		$html .= '<a class="add-answer" data-container-id="' . $admin_input_name . '" data-element-id="' . $element_id . '">+ ' . esc_html__( 'Add Answer', 'torro-forms' ) . ' </a>';

		return $html;
	}

	/**
	 * Content of the settings tab
	 *
	 * @return string $html The settings tab HTML
	 * @since 1.0.0
	 */
	protected function admin_widget_settings_tab() {
		$html = '';

		if ( is_array( $this->settings_fields ) && 0 < count( $this->settings_fields ) ) {
			foreach ( $this->settings_fields as $name => $field ) {
				$html .= $this->admin_widget_settings_field( $name, $field );
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
	protected function admin_widget_settings_field( $name, $field ) {
		$value = '';

		if ( array_key_exists( $name, $this->settings ) ) {
			$id = $this->settings[ $name ]->id;
			$value = $this->settings[ $name ]->value;
			$name = $this->settings[ $name ]->name;
		}else{
			$id = 'temp_id_' . time() * rand();
		}

		if ( '' == $value ) {
			$value = $field[ 'default' ];
		}

		$base_name = $this->get_admin_input_name() . '[settings][' . $id . ']';
		$input_name = $base_name . '[value]';

		$input = '';
		switch ( $field[ 'type' ] ) {
			case 'text':
				$input = '<input type="text" name="' . $input_name . '" value="' . esc_attr( $value ) . '" />';
				break;
			case 'textarea':
				$input = '<textarea name="' . $input_name . '">' . esc_html( $value ) . '</textarea>';
				break;
			case 'wp_editor':
				$settings = array(
					'textarea_name' => $name
				);
				ob_start();
				wp_editor( $value, 'torro_wp_editor_' . substr( md5( time() * rand() ), 0, 7 ) . '_tinymce', $settings );
				$input = ob_get_clean();
				break;
			case 'radio':
				$input = '';
				foreach ( $field[ 'values' ] as $field_key => $field_value ) {
					$checked = '';
					if ( $value === $field_key ) {
						$checked = ' checked="checked"';
					}

					$input .= '<span class="torro-form-fieldset-input-radio"><input type="radio" name="' . $input_name . '" value="' . $field_key . '"' . $checked . ' /> ' . esc_html( $field_value ) . '</span>';
				}
				break;
		}

		$html = '<div class="torro-form-fieldset">';

		$html .= '<div class="torro-form-fieldset-title">';
		$html .= '<label for="' . $input_name . '">' . $field[ 'title' ] . '</label>';
		$html .= '</div>';

		$html .= '<div class="torro-form-fieldset-input">';
		$html .= $input . '<br />';
		$html .= '<input type="hidden" name="' . $base_name . '[id]" value="' . $id . '">';
		$html .= '<input type="hidden" name="' . $base_name . '[name]" value="' . $name . '">';
		$html .= '<small>' . $field[ 'description' ] . '</small>';
		$html .= '</div>';

		$html .= '<div class="clear"></div>';

		$html .= '</div>';

		return $html;
	}

	protected function admin_widget_action_buttons() {
		// Adding action Buttons
		$bottom_buttons = apply_filters( 'torro_element_bottom_actions', array(
			'delete_form_element' => array(
				'text'    => __( 'Delete element', 'torro-forms' ),
				'classes' => 'delete_form_element'
			)
		) );

		$html = '<div class="form-element-buttom">';
		$html .= '<ul>';
		foreach ( $bottom_buttons as $button ) {
			$html .= '<li><a class="' . $button[ 'classes' ] . ' form-element-bottom-action button">' . esc_html( $button[ 'text' ] ) . '</a></li>';
		}
		$html .= '</ul>';
		$html .= '</div>';

		return $html;
	}

	protected function admin_widget_hidden_fields() {
		$admin_input_name = $this->get_admin_input_name();

		$html = '<input type="hidden" name="' . $admin_input_name . '[id]" value="' . $this->id . '" />';
		$html .= '<input type="hidden" name=' . $admin_input_name . '[container_id]" value="' . $this->container_id . '" />';
		$html .= '<input type="hidden" name="' . $admin_input_name . '[sort]" value="' . $this->sort . '" />';
		$html .= '<input type="hidden" name="' . $admin_input_name . '[type]" value="' . $this->type . '" />';
		$html .= '<input type="hidden" name="' . $admin_input_name . '[has_answers]" value="' . ( $this->input_answers ? 'yes' : 'no' ) . '" />';
		$html .= '<input type="hidden" name="' . $admin_input_name . '[sections]" value="' . ( property_exists( $this, 'sections' ) && is_array( $this->sections ) && 0 < count( $this->sections ) ? 'yes' : 'no' ) . '" />';

		return $html;
	}

	/**
	 * Returns the name of an input element
	 *
	 * @return string $input_name The name of the input
	 * @since 1.0.0
	 */
	public function get_input_name() {
		return 'torro_response[' . $this->id . ']';
	}

	/**
	 * Returns the name of an input element
	 *
	 * @return string $input_name The name of the input
	 * @since 1.0.0
	 */
	public function get_input_name_selector() {
		return 'torro_response\\\[' . $this->id . '\\\]';
	}

	/**
	 * Function for adding own columns to result
	 *
	 * @param obj $result_object
	 * @return boolean|object
	 * @since 1.0.0
	 */
	public function add_result_columns( &$result_object ) {
		return false;
	}

	/**
	 * Saving Element
	 *
	 * @return false|int
	 * @since 1.0.0
	 */
	public function save(){
		global $wpdb;

		if( ! empty( $this->id ) ){
			$wpdb->update(
				$wpdb->torro_elements,
				array(
					'form_id' => $this->form_id,
					'container_id' => $this->container_id,
					'label' => $this->label,
					'sort' => $this->sort,
					'type' => $this->type
				),
				array(
					'id' => $this->id
				)
			);
			return $this->id;
		}else{
			return $wpdb->insert(
				$wpdb->torro_elements,
				array(
					'form_id' => $this->form_id,
					'container_id' => $this->container_id,
					'label' => $this->label,
					'sort' => $this->sort,
					'type' => $this->type
				)
			);

			return $wpdb->insert_id;
		}
	}

	/**
	 * Delete element
	 *
	 * @return bool|false|int
	 * @since 1.0.0
	 */
	public function delete(){
		global $wpdb;

		if ( ! empty( $this->id ) ){
			if( $this->input_answers && 0 !== count( $this->answers ) ) {
				foreach( $this->answers AS $answer ) {
					$answer = new Torro_Element_Answer( $answer[ 'id' ] );
					$answer->delete();
				}
			}

			return $wpdb->delete( $wpdb->torro_elements, array( 'id' => $this->id ) );
		}

		return false;
	}

	/**
	 * Replacing column name by element
	 *
	 * @param str $column_name
	 * @since 1.0.0
	 */
	public function replace_column_name( $column_name ) {
		return false;
	}

	/**
	 * Magic getter function
	 *
	 * @param $key
	 * @return null
	 * @since 1.0.0
	 */
	public function __get( $key ) {
		if ( property_exists( $this, $key ) ) {
			return $this->$key;
		}

		return null;
	}

	/**
	 * Magic getter function
	 *
	 * @param $key
	 * @param $value
	 * @return bool
	 * @since 1.0.0
	 */
	public function __set( $key, $value ) {
		switch ( $key ) {
			case 'id':
				return false;
				break;

			case 'sort':
				$value      = absint( $value );
				$this->$key = $value;
				break;

			default:
				if ( property_exists( $this, $key ) ) {
					$this->$key = $value;
				}
		}
	}

	/**
	 * Magic setter function
	 *
	 * @param $key
	 * @return bool
	 * @since 1.0.0
	 */
	public function __isset( $key ) {
		if ( property_exists( $this, $key ) ) {
			return true;
		}

		return false;
	}
}
