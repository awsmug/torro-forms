<?php
/**
 * Elements abstraction class
 *
 * Motherclass for element creation
 *
 * @author awesome.ug, Author <support@awesome.ug>
 * @package Questions/Core
 * @version 1.0.0
 * @since 1.0.0
 * @license GPL 2

Copyright 2015 awesome.ug (support@awesome.ug)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */

if ( !defined( 'ABSPATH' ) ) exit;

abstract class Questions_FormElement {
	
	/**
	 * ID of instanced Element
	 * @since 1.0.0
	 */
	var $id = NULL;
	
	/**
	 * Contains the Survey ID of the element
	 * @since 1.0.0
	 */	
	var $survey_id;
	
	/**
	 * Slug of element
	 * @since 1.0.0
	 */
	var $slug;

	/**
	 * Title of element which will be shown in admin
	 * @since 1.0.0
	 */
	var $title;

	/**
	 * Description of element
	 * @since 1.0.0
	 */
	var $description;

	/**
	 * Icon URl of element
	 * @since 1.0.0
	 */
	var $icon_url;
	
	/**
	 * Question text
	 * @since 1.0.0
	 */
	var $question;

	/**
	 * Sort number where to display element
	 * @since 1.0.0
	 */
	var $sort = 0;

	/**
	 * Contains if this element a question or is this element for anything else (like description element or something else)
	 * @since 1.0.0
	 */
	var $is_question = TRUE;

	/**
	 * If value is true, Questions will try to create charts from results
	 * @since 1.0.0
	 */
	var $is_analyzable = FALSE;
	
	/**
	 * Does this elements has own answers? For example on multiple choice or one choice has answers.
	 * @since 1.0.0
	 */
	var $has_answers = FALSE;
	
	
	var $splits_form = FALSE;
	

	/**
	 * Sections for answers
	 * @since 1.0.0
	 */
	var $sections = array();
	
	/**
	 * If elements has answers they will be stored here after populating object.
	 * @since 1.0.0
	 */
	var $answers = array();
	
	/**
	 * Contains users response of element after submitting
	 * @since 1.0.0
	 */
	var $response;
	
	/**
	 * The settings field setup
	 * @since 1.0.0
	 */
	var $settings_fields = array();

	/**
	 * Contains all settings of the element
	 * @since 1.0.0
	 */
	var $settings = array();
	
	/**
	 * HTML template for answer
	 * @since 1.0.0
	 */
	var $create_answer_syntax;

	/**
	 * Parameters which have to be added on answer
	 * @since 1.0.0
	 */
	var $create_answer_params = array();
	
	var $answer_is_multiple = FALSE;
	
	/**
	 * Control variable if element is already initialized
	 * @since 1.0.0
	 */
	var $initialized = FALSE;

	/**
	 * Constructor
	 * @param int $id ID of the element
	 * @since 1.0.0
	 */
	public function __construct( $id = NULL ) {

		if ( NULL != $id && '' != $id ) {
			$this->populate( $id );
		}

		$this->settings_fields();
	}
	
	/**
	 * Function to register element in Questions
	 * 
	 * After registerung was successfull the new element will be shown in the elements list.
	 * 
	 * @return boolean $is_registered Returns TRUE if registering was succesfull, FALSE if not
	 * @since 1.0.0
	 */
	public function _register() {

		global $questions_global;

		if ( TRUE == $this->initialized ) {
			return FALSE;
		}

		if ( ! is_object( $questions_global ) ) {
			return FALSE;
		}

		if ( '' == $this->slug ) {
			$this->slug = get_class( $this );
		}

		if ( '' == $this->title ) {
			$this->title = ucwords( get_class( $this ) );
		}

		if ( '' == $this->description ) {
			$this->description = esc_attr__( 'This is a Questions Survey Element.', 'questions-locale' );
		}

		if ( array_key_exists( $this->slug, $questions_global->element_types ) ) {
			return FALSE;
		}

		if ( ! is_array( $questions_global->element_types ) ) {
			$questions_global->element_types = array();
		}

		$this->initialized = TRUE;

		return $questions_global->add_form_element( $this->slug, $this );
	}

	/**
	 * Populating element object with data
	 * @param int $id Element id
	 * @since 1.0.0
	 */
	private function populate( $id ) {

		global $wpdb, $questions_global;

		$this->question = '';
		$this->answers  = array();

		$sql = $wpdb->prepare( "SELECT * FROM {$questions_global->tables->questions} WHERE id = %s", $id );
		$row = $wpdb->get_row( $sql );
		

		$this->id = $id;
		$this->set_question( $row->question );
		$this->questions_id = $row->questions_id;

		$this->sort = $row->sort;

		$sql     = $wpdb->prepare(
			"SELECT * FROM {$questions_global->tables->answers} WHERE question_id = %s ORDER BY sort ASC", $id
		);
		$results = $wpdb->get_results( $sql );

		if ( is_array( $results ) ):
			foreach ( $results AS $result ):
				$this->add_answer( $result->answer, $result->sort, $result->id, $result->section );
			endforeach;
		endif;

		$sql     = $wpdb->prepare( "SELECT * FROM {$questions_global->tables->settings} WHERE question_id = %s", $id );
		$results = $wpdb->get_results( $sql );

		if ( is_array( $results ) ):
			foreach ( $results AS $result ):
				$this->add_setting( $result->name, $result->value );
			endforeach;
		endif;
	}
	
	/**
	 * Setting question for element
	 * @param string $question Question text
	 * @since 1.0.0
	 */
	private function set_question( $question, $order = NULL ) {

		if ( '' == $question ) {
			return FALSE;
		}

		if ( NULL != $order ) {
			$this->sort = $order;
		}

		$this->question = $question;
		
		return TRUE;
	}

	/**
	 * Adding answer to object data
	 * @param string $text Answer text
	 * @param int $sort Sort number
	 * @param int $id Answer ID from DB
	 * @param string $section Section of answer
	 * @return boolean $is_added TRUE if answer was added, False if not
	 * @since 1.0.0
	 */
	private function add_answer( $text, $sort = FALSE, $id = NULL, $section = NULL ) {

		if ( '' == $text ) {
			return FALSE;
		}

		$this->answers[ $id ] = array(
			'id'      => $id,
			'text'    => $text,
			'sort'    => $sort,
			'section' => $section
		);

		return TRUE;
	}

	/**
	 * Add setting to object data
	 * @param string $name Name of setting
	 * @param string $value Value of setting
	 * @since 1.0.0
	 */
	private function add_setting( $name, $value ) {

		$this->settings[ $name ] = $value;
	}

	public function settings_fields() {
	}

	/**
	 * Validate user input - Have to be overwritten by child classes if element needs validation
	 * @return bool
	 * @since 1.0.0
	 */
	public function validate( $input ) {

		return TRUE;
	}

	/**
	 * Drawing Element on frontend
	 * @return string $html Element HTML
	 * @since 1.0.0
	 */
	public function draw() {
	
		global $questions_response_errors;

		$errors = '';
		if ( is_array( $questions_response_errors ) && array_key_exists( $this->id, $questions_response_errors ) ) {
			$errors = $questions_response_errors[ $this->id ];
		}
	
		$html = '';
	
		$html = apply_filters( 'questions_draw_element_outer_start', $html, $this );
	
		$element_classes = array( 'survey-element', 'survey-element-' . $this->id );
		$element_classes = apply_filters( 'questions_element_classes', $element_classes, $this );
	
		$html .= '<div class="' . implode( ' ', $element_classes ) . '">';
	
		$html = apply_filters( 'questions_draw_element_inner_start', $html, $this );
	
		// Echo Errors
		if ( is_array( $errors ) && count( $errors ) > 0 ):
			$html .= '<div class="questions-element-error">';
			$html .= '<div class="questions-element-error-message">';
			$html .= '<ul class="questions-error-messages">';
			foreach ( $errors AS $error ):
				$html .= '<li>' . $error . '</li>';
			endforeach;
			$html = apply_filters( 'questions_draw_element_errors', $html, $this );
			$html .= '</ul></div>';
		endif;
	
		if ( ! empty( $this->question ) ):
			$html .= '<h5>' . $this->question . '</h5>';
		endif;
		
		// Adding description
		if ( ! empty( $this->settings[ 'description' ] ) ):
			$html .= '<div class="questions-element-description">';
			$html .= $this->settings[ 'description' ];
			$html .= '</div>';
		endif;
		
		// Fetching user response data
		$this->get_response();
	
		$html .= '<div class="answer">';

        if ( 0 == count( $this->answers ) && $this->has_answers == TRUE ) {
            $html.= '<p>' . esc_attr__('You don´t entered any answers. Please add some to display answers here.', 'questions-locale') . '</p>';
        }else {
            $html .= $this->input_html();
        }

		$html .= '</div>';
	
		// End Echo Errors
		if ( is_array( $errors ) && count( $errors ) > 0 ):
			$html .= '</div>';
		endif;
	
		$html = apply_filters( 'questions_draw_element_inner_end', $html, $this );
	
		$html .= '</div>';
	
		$html = apply_filters( 'questions_draw_element_outer_end', $html, $this );
	
		return $html;
	}
	
	/**
	 * Contains element HTML on frontend - Have to be overwritten by child classes
	 * @return string $html Element frontend HTML
	 */
	public function input_html() {
	
		return '<p>' . esc_attr__( 'No HTML for Element given. Please check element sourcecode.', 'questions-locale' ) . '</p>';
	}

	/**
	 * Returns the widget id which will be used in HTML
	 * @return string $widget_id The widget id
	 * @since 1.0.0
	 */
	private function admin_get_widget_id() {
	
		// Getting Widget ID
		if ( NULL == $this->id ):
			// New Element
			$widget_id = 'widget_formelement_XXnrXX';
		else:
			// Existing Element
			$widget_id = 'widget_formelement_' . $this->id;
		endif;
	
		return $widget_id;
	}
	
	/**
	 * Draws element box in Admin
	 * @return string $html The admin element HTML code
	 * @since 1.0.0
	 */
	public function draw_admin() {
	
		// Getting id string
		if ( NULL == $this->id ):
			// New Element
			$id_name = ' id="widget_formelement_XXnrXX"';
		else:
			// Existing Element
			$id_name = ' id="widget_formelement_' . $this->id . '"';
		endif;
	
		/**
		 * Widget
		 */
		$html = '<div class="widget formelement"' . $id_name . '>';
		
			/**
			 * Widget head
			 */
			$title = empty( $this->question ) ? $this->title : $this->question;
		
			$html .= '<div class="widget-top questions-admin-qu-text">';
			$html .= '<div class="widget-title-action"><a class="widget-action hide-if-no-js"></a></div>';
			$html .= '<div class="widget-title">';
		
			if ( '' != $this->icon_url ):
				$html .= '<img class="questions-widget-icon" src ="' . $this->icon_url . '" />';
			endif;
			$html .= '<h4>' . $title . '</h4>';
		
			$html .= '</div>';
			$html .= '</div>';
			
			/**
			 * Widget inside
			 */
			$widget_id        = $this->admin_get_widget_id();
			$jquery_widget_id = str_replace( '#', '', $widget_id );
		
			$html .= '<div class="widget-inside">';
			$html .= '<div class="widget-content">';
			$html .= '<div class="form_element_tabs">';
		
			/**
			 * Tab Navi
			 */
			$html .= '<ul class="tabs">';
			// If Element is Question > Show question tab
			if ( $this->is_question ) {
				$html .= '<li><a href="#tab_' . $jquery_widget_id . '_questions">' . esc_attr__(
						'Question', 'questions-locale'
					) . '</a></li>';
			}
		
			// If Element has settings > Show settings tab
			if ( is_array( $this->settings_fields ) && count( $this->settings_fields ) > 0 ) {
				$html .= '<li><a href="#tab_' . $jquery_widget_id . '_settings">' . esc_attr__(
						'Settings', 'questions-locale'
					) . '</a></li>';
			}
		
			// Adding further tabs
			ob_start();
			do_action( 'questions_element_admin_tabs', $this );
			$html .= ob_get_clean();
		
			$html .= '</ul>';
		
			$html .= '<div class="clear tabs_underline"></div>'; // Underline of tabs
		
			/**
			 * Content of Tabs
			 */
		
			// Adding question tab
			if ( $this->is_question ):
				$html .= '<div id="tab_' . $jquery_widget_id . '_questions" class="tab_questions_content">';
				$html .= $this->admin_widget_question_tab();
				$html .= '</div>';
			endif;
		
			// Adding settings tab
			if ( is_array( $this->settings_fields ) && count( $this->settings_fields ) > 0 ):
				$html .= '<div id="tab_' . $jquery_widget_id . '_settings" class="tab_settings_content">';
				$html .= $this->admin_widget_settings_tab();
				$html .= '</div>';
			endif;
		
			// Adding further content
			ob_start();
			do_action( 'questions_element_admin_tabs_content', $this );
			$html .= ob_get_clean();
		
			$html .= $this->admin_widget_action_buttons();
			$html .= $this->admin_widget_hidden_fields();
		
			$html .= '</div>';
			$html .= '</div>';
			$html .= '</div>';
			
		$html .= '</div>';
	
		return $html;
	}
	
	/**
	 * Content of the question tab
	 * @since 1.0.0
	 */
	private function admin_widget_question_tab() {
	
		$widget_id = $this->admin_get_widget_id();
	
		// Question
		$html = '<p><input type="text" name="questions[' . $widget_id . '][question]" value="'
			. $this->question . '" class="questions-question" /><p>';
	
		// Answers
		if ( $this->has_answers ):
	
			// Answers have sections
			if ( property_exists( $this, 'sections' ) && is_array( $this->sections ) && count( $this->sections ) > 0 ):
				foreach ( $this->sections as $section_key => $section_name ):
					$html .= '<div class="questions-section" id="section_' . $section_key . '">';
					$html .= '<p>' . $section_name . '</p>';
					$html .= $this->admin_widget_question_answers( $section_key );
					$html .= '<input type="hidden" name="section_key" value="' . $section_key . '" />';
					$html .= '</div>';
				endforeach;
			// Answers without sections
			else:
				$html .= '<p>' . esc_attr__( 'Answer/s:', 'questions-locale' ) . '</p>';
				$html .= $this->admin_widget_question_answers();
			endif;
	
		endif;
	
		$html .= '<div class="clear"></div>';
	
		return $html;
	}
	
	/**
	 * Content of the answers under the question
	 * @param string $section Name of the section
	 * @return string $html The answers HTML
	 * @since 1.0.0
	 */
	private function admin_widget_question_answers( $section = NULL ) {
	
		$widget_id = $this->admin_get_widget_id();
	
		$html = '';
	
		if ( is_array( $this->answers ) ):
	
			$html .= '<div class="answers">';
	
			foreach ( $this->answers AS $answer ):
	
				// If there is a section
				if ( NULL != $section ) {
					if ( $answer[ 'section' ] != $section ) // Continue if answer is not of the section
					{
						continue;
					}
				}
	
				$param_arr    = array();
				$param_arr[ ] = $this->create_answer_syntax;
	
				$param_value = '';
				foreach ( $this->create_answer_params AS $param ):
	
					switch ( $param ) {
						case 'name':
							$param_value = 'questions[' . $widget_id . '][answers][id_' . $answer[ 'id' ] . '][answer]';
							break;
	
						// @todo Why this var, where you set this, currently is the var always unseated
						case 'value':
							$param_value = $value;
							break;
	
						case 'answer';
							$param_value = $answer[ 'text' ];
							break;
					}
					$param_arr[ ] = $param_value;
				endforeach;
	
				$html .= '<div class="answer" id="answer_' . $answer[ 'id' ] . '">';
				$html .= call_user_func_array( 'sprintf', $param_arr );
				$html .= ' <input type="button" value="' . esc_attr__( 'Delete', 'questions-locale' ) . '" class="delete_answer button answer_action">';
				$html .= '<input type="hidden" name="questions[' . $widget_id . '][answers][id_' . $answer[ 'id' ] . '][id]" value="' . $answer[ 'id' ] . '" />';
				$html .= '<input type="hidden" name="questions[' . $widget_id . '][answers][id_' . $answer[ 'id' ] . '][sort]" value="' . $answer[ 'sort' ] . '" />';
	
				if ( NULL != $section ) {
					$html .= '<input type="hidden" name="questions[' . $widget_id . '][answers][id_' . $answer[ 'id' ] . '][section]" value="' . $section . '" />';
				}
	
				$html .= '</div>';
	
			endforeach;
	
			$html .= '</div><div class="clear"></div>';
	
		else:
			if ( $this->has_answers ):
	
				$param_arr[ ]   = $this->create_answer_syntax;
				$temp_answer_id = 'id_' . time() * rand();
	
				$param_value = '';
				foreach ( $this->create_answer_params AS $param ):
					switch ( $param ) {
						case 'name':
							$param_value = 'questions[' . $widget_id . '][answers][' . $temp_answer_id . '][answer]';
							break;
	
						case 'value':
							$param_value = '';
							break;
	
						case 'answer';
							$param_value = '';
							break;
					}
					$param_arr[ ] = $param_value;
				endforeach;
	
				$html .= '<div class="answers">';
				$html .= '<div class="answer" id="answer_' . $temp_answer_id . '">';
				$html .= call_user_func_array( 'sprintf', $param_arr );
				$html .= ' <input type="button" value="' . esc_attr__(
						'Delete', 'questions-locale'
					) . '" class="delete_answer button answer_action">';
				$html .= '<input type="hidden" name="questions[' . $widget_id . '][answers][' . $temp_answer_id . '][id]" value="" />';
				$html .= '<input type="hidden" name="questions[' . $widget_id . '][answers][' . $temp_answer_id . '][sort]" value="0" />';
				if ( NULL != $section ) {
					$html .= '<input type="hidden" name="questions[' . $widget_id . '][answers]['
						. $temp_answer_id . '][section]" value="' . $section . '" />';
				}
	
				$html .= '</div>';
				$html .= '</div><div class="clear"></div>';
	
			endif;
	
		endif;
	
		$html .= '<a class="add-answer" rel="' . $widget_id . '">+ ' . esc_attr__( 'Add Answer', 'questions-locale' ) . ' </a>';
		
		return $html;
	}
	
	/**
	 * Content of the settings tab
	 * @return string $html The settings tab HTML
	 * @since 1.0.0
	 */
	private function admin_widget_settings_tab() {
	
		$html = '';
		
		// Running each setting field
		foreach ( $this->settings_fields AS $name => $field ):
			$html .= $this->admin_widget_settings_field( $name, $field );
		endforeach;
	
		return $html;
	}
	
	/**
	 * Creating a settings field
	 * @param string $name Internal name of the field
	 * @param array $field Field settings
	 * @return string $html The field HTML
	 * @since 1.0.0
	 */
	private function admin_widget_settings_field( $name, $field ) {
	
		$widget_id = $this->admin_get_widget_id();
		$value     = '';
	
		if ( array_key_exists( $name, $this->settings ) ) {
			$value = $this->settings[ $name ];
		}
	
		if ( '' == $value ) {
			$value = $field[ 'default' ];
		}
	
		$name = 'questions[' . $widget_id . '][settings][' . $name . ']';
	
		$input = '';
		switch ( $field[ 'type' ] ) {
			case 'text':
	
				$input = '<input type="text" name="' . $name . '" value="' . $value . '" />';
				break;
	
			case 'textarea':
	
				$input = '<textarea name="' . $name . '">' . $value . '</textarea>';
				break;
				
			/* @todo Get WP Editor working in droppables
			 * Problems with the droppable. Editor ist not working anymore after dropping.
			case 'wp_editor':
				$settings = array(
					'textarea_name' => $name
				);
				ob_start();
				wp_editor( $value, 'qu_wp_editor_' . substr( md5( time() * rand() ), 0, 7 ) . '_tinymce', $settings );
				$input = ob_get_clean();
				break;
			*/
			case 'radio':
	
				$input = '';
	
				foreach ( $field[ 'values' ] AS $field_key => $field_value ):
					$checked = '';
	
					if ( $value == $field_key ) {
						$checked = ' checked="checked"';
					}
	
					$input .= '<span class="surveval-form-fieldset-input-radio"><input type="radio" name="'
						. $name . '" value="' . $field_key . '"' . $checked . ' /> ' . $field_value . '</span>';
				endforeach;
	
				break;
		}
	
		$html = '<div class="surveval-form-fieldset">';
	
		$html .= '<div class="surveval-form-fieldset-title">';
		$html .= '<label for="' . $name . '">' . $field[ 'title' ] . '</label>';
		$html .= '</div>';
	
		$html .= '<div class="surveval-form-fieldset-input">';
		$html .= $input . '<br />';
		$html .= '<small>' . $field[ 'description' ] . '</small>';
		$html .= '</div>';
	
		$html .= '<div class="clear"></div>';
	
		$html .= '</div>';
	
		return $html;
	}
	
	private function admin_widget_action_buttons() {
	
		// Adding action Buttons
		$bottom_buttons = apply_filters(
			'qu_element_bottom_actions', array(
				                           'delete_survey_element' => array(
					                           'text'    => esc_attr__( 'Delete element', 'questions-locale' ),
					                           'classes' => 'delete_survey_element'
				                           )
			                           )
		);
	
		$html = '<ul class="survey-element-bottom">';
		foreach ( $bottom_buttons AS $button ):
			$html .= '<li><a class="' . $button[ 'classes' ] . ' survey-element-bottom-action button">' . $button[ 'text' ] . '</a></li>';
		endforeach;
		$html .= '</ul>';
	
		return $html;
	}
	
	private function admin_widget_hidden_fields() {
	
		$widget_id = $this->admin_get_widget_id();
	
		// Adding hidden Values for element
		$html  = '<input type="hidden" name="questions[' . $widget_id . '][id]" value="' . $this->id . '" />';
		$html .= '<input type="hidden" name="questions[' . $widget_id . '][sort]" value="' . $this->sort . '" />';
		$html .= '<input type="hidden" name="questions[' . $widget_id . '][type]" value="' . $this->slug . '" />';
		$html .= '<input type="hidden" name="questions[' . $widget_id . '][has_answers]" value="' . ( $this->has_answers ? 'yes' : 'no' ) . '" />';
		$html .= '<input type="hidden" name="questions[' . $widget_id . '][sections]" value="' . ( property_exists( $this, 'sections' ) && is_array( $this->sections ) && count( $this->sections ) > 0 ? 'yes' : 'no' ) . '" />';
	
		return $html;
	}
	
	/**
	 * Getting element data from Session
	 * @return array $response The post response
	 * @since 1.0.0
	 */
	private function get_response() {
	
		global $questions_form_id;
	
		$this->response = FALSE;
	
		// Getting value/s
		if ( ! empty( $questions_form_id ) ):
			if ( isset( $_SESSION[ 'questions_response' ] ) ):
				if ( isset( $_SESSION[ 'questions_response' ][ $questions_form_id ] ) ):
					if ( isset( $_SESSION[ 'questions_response' ][ $questions_form_id ][ $this->id ] ) ):
						$this->response = $_SESSION[ 'questions_response' ][ $questions_form_id ][ $this->id ];
					endif;
				endif;
			endif;
		endif;
	
		return $this->response;
	}
	
	/**
	 * Returns the name of an input element
	 * @return string $input_name The name of the input
	 * @since 1.0.0
	 */
	public function get_input_name() {
		$input_name = 'questions_response[' . $this->id . ']';
		return $input_name;
	}
	
	/**
	 * Get all saved responses of an element
	 * @return mixed $responses The responses as array or FALSE if failed to get responses
	 * @since 1.0.0
	 */
	public function get_responses() {
	
		global $wpdb, $questions_global;
	
		$sql       = $wpdb->prepare(
			"SELECT * FROM {$questions_global->tables->responds} AS r, {$questions_global->tables->respond_answers} AS a WHERE r.id=a.respond_id AND a.question_id=%d",
			$this->id
		);
		$responses = $wpdb->get_results( $sql );
	
		$result_answers               = array();
		$result_answers[ 'question' ] = $this->question;
		$result_answers[ 'sections' ] = FALSE;
		$result_answers[ 'array' ]    = $this->answer_is_multiple;
	
		if ( is_array( $this->answers ) && count( $this->answers ) > 0 ):
			// If element has predefined answers
			foreach ( $this->answers AS $answer_id => $answer ):
				$value = FALSE;
				if ( $this->answer_is_multiple ):
					foreach ( $responses AS $response ):
						if ( $answer[ 'text' ] == $response->value ):
							$result_answers[ 'responses' ][ $response->respond_id ][ $answer[ 'text' ] ] = esc_attr__(
								'Yes'
							);
						elseif ( ! isset( $result_answers[ 'responses' ][ $response->respond_id ][ $answer[ 'text' ] ] ) ):
							$result_answers[ 'responses' ][ $response->respond_id ][ $answer[ 'text' ] ] = esc_attr__(
								'No'
							);
						endif;
					endforeach;
				else:
					foreach ( $responses AS $response ):
						if ( $answer[ 'text' ] == $response->value ):
							$result_answers[ 'responses' ][ $response->respond_id ] = $response->value;
						endif;
					endforeach;
				endif;
	
			endforeach;
		else:
			// If element has no predefined answers
			if ( is_array( $responses ) && count( $responses ) > 0 ):
				foreach ( $responses AS $response ):
					$result_answers[ 'responses' ][ $response->respond_id ] = $response->value;
				endforeach;
			endif;
		endif;
	
		if ( is_array( $result_answers ) && count( $result_answers ) > 0 ) {
			return $result_answers;
		} else {
			return FALSE;
		}
	}
}

/**
 * Register a new Group Extension.
 *
 * @param $element_type_class name of the element type class.
 *
 * @return bool|null Returns false on failure, otherwise null.
 */
function qu_register_survey_element( $element_type_class ) {

	if ( ! class_exists( $element_type_class ) ) {
		return FALSE;
	}

	// Register the group extension on the bp_init action so we have access
	// to all plugins.
	add_action(
		'init',
		create_function(
			'', '$extension = new ' . $element_type_class . ';
			add_action( "init", array( &$extension, "_register" ), 2 ); '
		), 1
	);
}
