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

abstract class Torro_Form_Element extends Torro_Instance {
	/**
	 * ID of instanced Element
	 *
	 * @since 1.0.0
	 */
	protected $id = null;

	/**
	 * Contains the Form ID of the Element
	 *
	 * @since 1.0.0
	 */
	protected $form_id;

	/**
	 * Icon URl of the Element
	 *
	 * @since 1.0.0
	 */
	protected $icon_url;

	/**
	 * Element Label
	 *
	 * @since 1.0.0
	 */
	protected $label;

	/**
	 * Sort number where to display the Element
	 *
	 * @since 1.0.0
	 */
	protected $sort = 0;

	/**
	 * Does this element have a content tab
	 *
	 * @since 1.0.0
	 */
	protected $has_content = true;

	/**
	 * If value is true, Torro Forms will try to create charts from results
	 *
	 * @todo  is_analyzable: Is this a self spelling name?
	 * @since 1.0.0
	 */
	protected $is_analyzable = false;

	/**
	 * Does this elements has own answers? For example on multiple choice or one choice has answers.
	 *
	 * @todo  has_answers: Is this a self spelling name?
	 * @since 1.0.0
	 */
	protected $is_answerable = true;

	/**
	 * Does this elements has own answers? For example on multiple choice or one choice has answers.
	 *
	 * @todo  has_answers: Is this a self spelling name?
	 * @since 1.0.0
	 */
	protected $has_answers = false;

	/**
	 * Only for Form splitter Element!
	 *
	 * @protected bool
	 */
	protected $splits_form = false;

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
	protected $response;

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
	 * Has element moltiple Answers?
	 *
	 * @since 1.0.0
	 */
	protected $answer_is_multiple = false;

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct( $id = null ) {
		parent::__construct();

		if ( null !== $id && '' !== $id ) {
			$this->populate( $id );
		}

		$this->settings_fields();
	}

	/**
	 * Populating element object with data
	 *
	 * @param int $id Element id
	 *
	 * @since 1.0.0
	 */
	protected function populate( $id ) {
		global $wpdb;

		$this->label = '';
		$this->answers = array();

		$sql = $wpdb->prepare( "SELECT * FROM $wpdb->torro_elements WHERE id = %s", $id );
		$row = $wpdb->get_row( $sql );

		$this->id = $id;
		$this->set_label( $row->label );
		$this->form_id = $row->form_id;

		$this->sort = $row->sort;

		$sql = $wpdb->prepare( "SELECT * FROM $wpdb->torro_element_answers WHERE element_id = %s ORDER BY sort ASC", $id );
		$results = $wpdb->get_results( $sql );

		if ( is_array( $results ) ) {
			foreach ( $results as $result ) {
				$this->add_answer( $result->answer, $result->sort, $result->id, $result->section );
			}
		}

		$sql = $wpdb->prepare( "SELECT * FROM $wpdb->torro_settings WHERE element_id = %s", $id );
		$results = $wpdb->get_results( $sql );

		if ( is_array( $results ) ) {
			foreach ( $results as $result ) {
				$this->add_setting( $result->name, $result->value );
			}
		}
	}

	/**
	 * Setting Label for Element
	 *
	 * @param string $label
	 *
	 * @since 1.0.0
	 * @return boolean
	 */
	protected function set_label( $label, $order = null ) {
		if( '' === $label ) {
			return false;
		}

		if ( null !== $order ) {
			$this->sort = $order;
		}

		$this->label = $label;

		return true;
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
			'id'		=> $id,
			'text'		=> $text,
			'sort'		=> $sort,
			'section'	=> $section,
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
	protected function add_setting( $name, $value ) {
		$this->settings[ $name ] = $value;
	}

	/**
	 * Settings fields
	 */
	public function settings_fields() {}

	/**
	 * Validate user input - Have to be overwritten by child classes if element needs validation
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
	public function draw() {
		global $torro_response_errors;

		$errors = '';
		if ( is_array( $torro_response_errors ) && array_key_exists( $this->id, $torro_response_errors ) ) {
			$errors = $torro_response_errors[ $this->id ];
		}

		$html = '';

		$element_classes = array( 'torro-element', 'torro-element-' . $this->id );
		$element_classes = apply_filters( 'torro_element_classes', $element_classes, $this );

		$html .= '<div class="' . esc_attr( implode( ' ', $element_classes ) ) . '">';

		ob_start();
		do_action( 'torro_form_element_start', $this->id );
		$html .= ob_get_clean();

		// Echo Errors
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

		// Fetching user response data
		$this->get_response();

		if ( 0 === count( $this->answers ) && true === $this->has_answers ) {
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
		global $torro_form_id;

		$this->response = false;

		if ( ! empty( $torro_form_id ) && isset( $_SESSION['torro_response'] ) && isset( $_SESSION['torro_response'][ $torro_form_id ] ) && isset( $_SESSION['torro_response'][ $torro_form_id ][ $this->id ] ) ) {
			$this->response = $_SESSION['torro_response'][ $torro_form_id ][ $this->id ];
		}

		return $this->response;
	}

	/**
	 * Contains element HTML on frontend - Have to be overwritten by child classes
	 *
	 * @return string $html Element frontend HTML
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
	public function draw_admin() {
		$id_name = $this->admin_get_widget_id();

		/**
		 * Widget
		 */
		if ( null === $this->id ) {
			$html = '<div data-element-id="' . $id_name . '" data-element-type="' . $this->name . '" class="formelement formelement-' . $this->name . '">';
		} else {
			$html = '<div data-element-id="' . $id_name . '" id="' . $id_name . '" data-element-type="' . $this->name . '" class="widget formelement formelement-' . $this->name . '">';
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
		$widget_id = $this->admin_get_widget_id();
		$jquery_widget_id = str_replace( '#', '', $widget_id );

		$html .= '<div class="widget-inside">';
		$html .= '<div class="widget-content">';

		/**
		 * Tab Navi
		 */
		$this->admin_add_tab( esc_attr__( 'Content', 'torro-forms' ), $this->admin_widget_content_tab() );

		$settings = $this->admin_widget_settings_tab();
		if ( false !== $settings ) {
			$this->admin_add_tab(  esc_attr__( 'Settings', 'torro-forms' ), $settings );
		}

		$admin_tabs = apply_filters( 'torro_formbuilder_element_tabs', $this->admin_tabs );

		if ( 1 < count( $admin_tabs ) ) {
			$html .= '<div class="form_element_tabs">';
			$html .= '<ul class="tabs">';

			foreach ( $admin_tabs as $key => $tab ) {
				$html .= '<li><a href="#tab_' . $jquery_widget_id . '_' . $key .  '">' . $tab[ 'title' ] . '</a></li>';
			}

			$html .= '</ul>';
		}

		$html .= '<div class="clear"></div>'; // Underline of tabs

		/**
		 * Content of Tabs
		 */
		if ( 1 < count( $admin_tabs ) ) {
			foreach ( $admin_tabs as $key => $tab ) {
				$html .= '<div id="tab_' . $jquery_widget_id . '_' . $key .   '">';
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
	 * @param $title
	 * @param $content
	 */
	public function admin_add_tab( $title, $content ) {
		$this->admin_tabs[] = array(
			'title'		=> $title,
			'content'	=> $content
		);
	}

	/**
	 * Returns the widget id which will be used in HTML
	 *
	 * @return string $widget_id The widget id
	 * @since 1.0.0
	 */
	protected function admin_get_widget_id() {
		// Getting Widget ID
		if ( null === $this->id ) {
			// New Element
			$widget_id = 'widget_formelement_XXnrXX';
		} else {
			// Existing Element
			$widget_id = 'widget_formelement_' . $this->id;
		}

		return $widget_id;
	}

	/**
	 * Content of the content tab
	 *
	 * @since 1.0.0
	 */
	protected function admin_widget_content_tab() {
		$widget_id = $this->admin_get_widget_id();
		$content_html = $this->admin_content_html();

		if ( false === $content_html ) {
			// Label
			$html = '<label for="elements[' . $widget_id . '][label]">' . __( 'Label ', 'torro-forms' ) . '</label><input type="text" name="elements[' . $widget_id . '][label]" value="' . $this->label . '" class="form-label" />';

			// Answers
			if ( $this->has_answers ) {
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
	 * Overwriting Admin Content HTML for totally free editing Element
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
		$widget_id = $this->admin_get_widget_id();

		$html = '';

		if ( is_array( $this->answers ) ) {
			$html .= '<div class="answers">';

			foreach ( $this->answers as $answer ) {
				// If there is a section
				if ( null !== $section ) {
					if ( $answer['section'] !== $section ) {
						// Continue if answer is not of the section
						continue;
					}
				}

				$html .= '<div class="answer" id="answer_' . $answer[ 'id' ] . '">';
				$html .= '<p><input type="text" name="elements[' . $widget_id . '][answers][id_' . $answer[ 'id' ] . '][answer]" value="' . esc_attr( $answer[ 'text' ] ) . '" class="element-answer" /></p>';
				$html .= '<input type="button" value="' . esc_attr__( 'Delete', 'torro-forms' ) . '" class="delete_answer button answer_action">';
				$html .= '<input type="hidden" name="elements[' . $widget_id . '][answers][id_' . $answer[ 'id' ] . '][id]" value="' . esc_attr( $answer[ 'id' ] ) . '" />';
				$html .= '<input type="hidden" name="elements[' . $widget_id . '][answers][id_' . $answer[ 'id' ] . '][sort]" value="' . esc_attr( $answer[ 'sort' ] ) . '" />';

				if ( null !== $section ) {
					$html .= '<input type="hidden" name="elements[' . $widget_id . '][answers][id_' . $answer[ 'id' ] . '][section]" value="' . esc_attr( $section ) . '" />';
				}
				$html .= '</div>';
			}

			$html .= '</div><div class="clear"></div>';
		} else {
			if ( $this->has_answers ) {
				$param_arr[] = $this->create_answer_syntax;
				$temp_answer_id = 'id_' . time() * rand();

				$html .= '<div class="answers">';
				$html .= '<div class="answer" id="answer_' . $temp_answer_id . '">';
				$html .= '<p><input type="text" name="elements[' . $widget_id . '][answers][' . $temp_answer_id . '][answer]" value="" class="element-answer" /></p>';
				$html .= ' <input type="button" value="' . esc_attr__( 'Delete', 'torro-forms' ) . '" class="delete_answer button answer_action">';
				$html .= '<input type="hidden" name="elements[' . $widget_id . '][answers][' . $temp_answer_id . '][id]" value="" />';
				$html .= '<input type="hidden" name="elements[' . $widget_id . '][answers][' . $temp_answer_id . '][sort]" value="0" />';
				if ( null !== $section ) {
					$html .= '<input type="hidden" name="elements[' . $widget_id . '][answers][' . $temp_answer_id . '][section]" value="' . esc_attr( $section ) . '" />';
				}

				$html .= '</div>';
				$html .= '</div><div class="clear"></div>';
			}
		}

		$html .= '<a class="add-answer" rel="' . $widget_id . '">+ ' . esc_html__( 'Add Answer', 'torro-forms' ) . ' </a>';

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

		// Running each setting field
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
		// @todo Handle with class-settingsform.php
		$widget_id = $this->admin_get_widget_id();
		$value = '';

		if ( array_key_exists( $name, $this->settings ) ) {
			$value = $this->settings[ $name ];
		}

		if ( '' == $value ) {
			$value = $field[ 'default' ];
		}

		$name = 'elements[' . $widget_id . '][settings][' . $name . ']';

		$input = '';
		switch ( $field[ 'type' ] ) {
			case 'text':
				$input = '<input type="text" name="' . $name . '" value="' . esc_attr( $value ) . '" />';
				break;
			case 'textarea':
				$input = '<textarea name="' . $name . '">' . esc_html( $value ) . '</textarea>';
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

					$input .= '<span class="torro-form-fieldset-input-radio"><input type="radio" name="' . $name . '" value="' . $field_key . '"' . $checked . ' /> ' . esc_html( $field_value ) . '</span>';
				}
				break;
		}

		$html = '<div class="torro-form-fieldset">';

		$html .= '<div class="torro-form-fieldset-title">';
		$html .= '<label for="' . $name . '">' . $field[ 'title' ] . '</label>';
		$html .= '</div>';

		$html .= '<div class="torro-form-fieldset-input">';
		$html .= $input . '<br />';
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
		$widget_id = $this->admin_get_widget_id();

		// Adding hidden Values for element
		$html = '<input type="hidden" name="elements[' . $widget_id . '][id]" value="' . $this->id . '" />';
		$html .= '<input type="hidden" name="elements[' . $widget_id . '][sort]" value="' . $this->sort . '" />';
		$html .= '<input type="hidden" name="elements[' . $widget_id . '][type]" value="' . $this->name . '" />';
		$html .= '<input type="hidden" name="elements[' . $widget_id . '][has_answers]" value="' . ( $this->has_answers ? 'yes' : 'no' ) . '" />';
		$html .= '<input type="hidden" name="elements[' . $widget_id . '][sections]" value="' . ( property_exists( $this, 'sections' ) && is_array( $this->sections ) && 0 < count( $this->sections ) ? 'yes' : 'no' ) . '" />';

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
	public function get_selector_input_name() {
		return 'torro_response\\\[' . $this->id . '\\\]';
	}

	/**
	 * Function for adding own columns to result
	 *
	 * @param obj $result_object
	 */
	public function add_result_columns( &$result_object ) {
		return false;
	}

	/**
	 * Replacing column name by element
	 *
	 * @param str $column_name
	 */
	public function replace_column_name( $column_name ) {
		return false;
	}

	/**
	 * Get all saved results of an element
	 *
	 * @return mixed $responses The results as array or false if failed to get responses
	 * @since 1.0.0
	 */
	public function get_results() {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT * FROM $wpdb->torro_results AS r, $wpdb->torro_result_answers AS a WHERE r.id=a.result_id AND a.element_id=%d", $this->id );
		$responses = $wpdb->get_results( $sql );

		$result_answers = array();
		$result_answers['label'] = $this->label;
		$result_answers['sections'] = false;
		$result_answers['array'] = $this->answer_is_multiple;

		if ( is_array( $this->answers ) && 0 < count( $this->answers ) ) {
			// If element has predefined answers
			foreach ( $this->answers as $answer_id => $answer ) {
				$value = false;
				if ( $this->answer_is_multiple ) {
					foreach ( $responses as $response ) {
						if ( $answer['text'] === $response->value ) {
							$result_answers['responses'][ $response->respond_id ][ $answer['text'] ] = __( 'Yes' );
						} elseif ( ! isset( $result_answers['responses'][ $response->respond_id ][ $answer[ 'text' ] ] ) ) {
							$result_answers['responses'][ $response->respond_id ][ $answer['text'] ] = __( 'No' );
						}
					}
				} else {
					foreach ( $responses as $response ) {
						if ( $answer['text'] === $response->value ) {
							$result_answers['responses'][ $response->respond_id ] = $response->value;
						}
					}
				}
			}
		} else {
			// If element has no predefined answers
			if( is_array( $responses ) && 0 < count( $responses ) ) {
				foreach ( $responses as $response ) {
					$result_answers['responses'][ $response->respond_id ] = $response->value;
				}
			}
		}

		return $result_answers;
	}
}
