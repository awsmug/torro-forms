<?php
/**
 * Question Form Builder
 *
 * This class adds question post type functions.
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package Questions/Core
 * @version 2015-04-16
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

if( !defined( 'ABSPATH' ) ){
	exit;
}

class Questions_FormBuilder
{

	/**
	 * Init in WordPress, run on constructor
	 *
	 * @return null
	 * @since 1.0.0
	 */
	public static function init()
	{
		if( !is_admin() ){
			return NULL;
		}

		add_action( 'edit_form_after_title', array( __CLASS__, 'droppable_area' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ), 10 );

		add_action( 'save_post', array( __CLASS__, 'save_form' ) );
		add_action( 'delete_post', array( __CLASS__, 'delete_form' ) );

		add_action( 'wp_ajax_questions_duplicate_form', array( __CLASS__, 'ajax_duplicate_form' ) );
		add_action( 'wp_ajax_questions_delete_responses', array( __CLASS__, 'ajax_delete_responses' ) );

		add_action( 'admin_notices', array( __CLASS__, 'jquery_messages_area' ) );

		add_action( 'admin_print_styles', array( __CLASS__, 'register_styles' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
	}

	/**
	 * Place to drop elements
	 *
	 * @since 1.0.0
	 */
	public static function droppable_area()
	{
		global $post, $questions_global;

		if( !self::is_questions_post_type() ){
			return;
		}

		$html = '<div id="questions-content" class="drag-drop">';
		$html .= '<div id="drag-drop-area" class="widgets-holder-wrap">';
		$html .= '<div id="drag-drop-inside">';
		$form = new Questions_Form( $post->ID );

		// Running each Element
		foreach( $form->elements AS $element ):
			$html .= $element->draw_admin();
		endforeach;

		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';

		$html .= '<div id="delete_formelement_dialog">' . esc_attr__( 'Do you really want to delete this element?', 'questions-locale' ) . '</div>';
		$html .= '<div id="delete_answer_dialog">' . esc_attr__( 'Do you really want to delete this answer?', 'questions-locale' ) . '</div>';
		$html .= '<div id="delete_responses_dialog"><h3>' . esc_attr__( 'Attention!', 'questions-locale' ) . '</h3><p>' . esc_attr__( 'This will erase all Answers who people given to this survey. Do you really want to delete all results of this survey?', 'questions-locale' ) . '</p></div>';

		$html .= '<input type="hidden" id="deleted_formelements" name="questions_deleted_formelements" value="">';
		$html .= '<input type="hidden" id="deleted_answers" name="questions_deleted_answers" value="">';

		echo $html;
	}

	/**
	 * Adding meta boxes
	 *
	 * @param string $post_type Actual post type
	 *
	 * @since 1.0.0
	 */
	public static function meta_boxes( $post_type )
	{
		$post_types = array( 'questions' );

		if( in_array( $post_type, $post_types ) ):
			add_meta_box( 'form-options', esc_attr__( 'Options', 'questions-locale' ), array( __CLASS__,
			                                                                                  'meta_box_form_options'
			), 'questions', 'side' );
			add_meta_box( 'form-functions', esc_attr__( 'Form Functions', 'questions-locale' ), array( __CLASS__,
			                                                                                             'meta_box_form_functions'
			), 'questions', 'side' );
			add_meta_box( 'form-elements', esc_attr__( 'Elements', 'questions-locale' ), array( __CLASS__,
			                                                                                    'meta_box_form_elements'
			), 'questions', 'side', 'high' );
			add_meta_box( 'form-results', esc_attr__( 'Results', 'questions-locale' ), array( __CLASS__,
			                                                                                  'meta_box_form_results'
			), 'questions', 'normal', 'low' );
		endif;
	}

	/**
	 * Elements for dropping
	 *
	 * @since 1.0.0
	 */
	public static function meta_box_form_elements()
	{
		global $questions_global;

		$html = '';

		foreach( $questions_global->element_types AS $element ):
			$html .= $element->draw_admin();
		endforeach;

		echo $html;
	}

	/**
	 * Showing form results in admin
	 *
	 * @since 1.0.0
	 */
	public static function meta_box_form_results()
	{
		global $wpdb, $post, $questions_global;

		$form_id = $post->ID;

		$html = do_shortcode( '[form_results id="' . $form_id . '"]' );

		echo $html;
	}

	/**
	 * Form options
	 *
	 * @since 1.0.0
	 */
	public static function meta_box_form_options()
	{
		global $post;

		$form_id = $post->ID;
		$show_results = get_post_meta( $form_id, 'show_results', TRUE );

		if( '' == $show_results ){
			$show_results = 'no';
		}

		$checked_no = '';
		$checked_yes = '';

		if( 'no' == $show_results ){
			$checked_no = ' checked="checked"';
		}else{
			$checked_yes = ' checked="checked"';
		}

		$html = '<div class="questions-options">';
		$html .= '<p><label for="show_results">' . esc_attr__( 'Show results after finishing form:', 'questions-locale' ) . '</label></p>';
		$html .= '<input type="radio" name="show_results" value="yes"' . $checked_yes . '>' . esc_attr__( 'Yes' ) . ' ';
		$html .= '<input type="radio" name="show_results" value="no"' . $checked_no . '>' . esc_attr__( 'No' ) . '<br>';
		$html .= '</div>';

		ob_start();
		do_action( 'questions_form_options', $form_id );
		$html .= ob_get_clean();

		echo $html;
	}

	/**
	 * Invitations box
	 *
	 * @since 1.0.0
	 */
	public static function meta_box_form_functions()
	{
		global $post;

		// Dublicate survey
		$html = '<div class="questions-function-element">';
		$html .= '<input id="questions-duplicate-button" name="questions-duplicate-survey" type="button" class="button" value="' . esc_attr__( 'Dublicate Form', 'questions-locale' ) . '" />';
		$html .= '</div>';

		// Delete results
		$html .= '<div class="questions-function-element">';
		$html .= '<input id="questions-delete-results-button" name="questions-delete-results" type="button" class="button" value="' . esc_attr__( 'Delete Form results', 'questions-locale' ) . '" />';
		$html .= '</div>';

		ob_start();
		do_action( 'questions_functions' );
		$html .= ob_get_clean();

		echo $html;
	}

	/**
	 * Saving data
	 *
	 * @param int $form_id
	 *
	 * @since 1.0.0
	 */
	public static function save_form( $form_id )
	{
		global $questions_global, $wpdb;

		if( !array_key_exists( 'questions', $_REQUEST ) ){
			return;
		}

		if( array_key_exists( 'questions-duplicate-survey', $_REQUEST ) ){
			return;
		}

		if( wp_is_post_revision( $form_id ) ){
			return;
		}

		if( !array_key_exists( 'post_type', $_POST ) ){
			return;
		}

		if( 'questions' != $_POST[ 'post_type' ] ){
			return;
		}

		$survey_elements = $_POST[ 'questions' ];
		$survey_deleted_formelements = $_POST[ 'questions_deleted_formelements' ];
		$survey_deleted_answers = $_POST[ 'questions_deleted_answers' ];
		$survey_show_results = $_POST[ 'show_results' ];

		/**
		 * Saving if results have to be shown after participating
		 */
		update_post_meta( $form_id, 'show_results', $survey_show_results );

		$survey_deleted_formelements = explode( ',', $survey_deleted_formelements );

		/**
		 * Deleting deleted answers
		 */
		if( is_array( $survey_deleted_formelements ) && count( $survey_deleted_formelements ) > 0 ):
			foreach( $survey_deleted_formelements AS $deleted_question ):
				$wpdb->delete( $questions_global->tables->questions, array( 'id' => $deleted_question ) );
				$wpdb->delete( $questions_global->tables->answers, array( 'question_id' => $deleted_question ) );
			endforeach;
		endif;

		$survey_deleted_answers = explode( ',', $survey_deleted_answers );

		/*
		 * Deleting deleted answers
		 */
		if( is_array( $survey_deleted_answers ) && count( $survey_deleted_answers ) > 0 ):
			foreach( $survey_deleted_answers AS $deleted_answer ):
				$wpdb->delete( $questions_global->tables->answers, array( 'id' => $deleted_answer ) );
			endforeach;
		endif;

		/*
		 * Saving elements
		 */
		foreach( $survey_elements AS $key => $survey_question ):
			if( 'widget_formelement_XXnrXX' == $key ){
				continue;
			}

			$question_id = (int) $survey_question[ 'id' ];
			$question = '';
			$sort = (int) $survey_question[ 'sort' ];
			$type = $survey_question[ 'type' ];

			if( array_key_exists( 'question', $survey_question ) ){
				$question = qu_prepare_post_data( $survey_question[ 'question' ] );
			}

			$answers = array();
			$settings = array();

			if( array_key_exists( 'answers', $survey_question ) ){
				$answers = $survey_question[ 'answers' ];
			}

			if( array_key_exists( 'settings', $survey_question ) ){
				$settings = $survey_question[ 'settings' ];
			}

			// Saving question
			if( '' != $question_id ):
				// Updating if question already exists
				$wpdb->update( $questions_global->tables->questions, array( 'question' => $question, 'sort' => $sort,
				                                                            'type'     => $type
				), array( 'id' => $question_id ) );
			else:

				// Adding new question
				$wpdb->insert( $questions_global->tables->questions, array( 'questions_id' => $form_id,
				                                                            'question'     => $question,
				                                                            'sort'         => $sort, 'type' => $type
				) );

				$question_id = $wpdb->insert_id;
			endif;

			do_action( 'questions_save_form_after_saving_question', $survey_question, $question_id );

			/*
			 * Saving answers
			 */
			if( is_array( $answers ) && count( $answers ) > 0 ):
				foreach( $answers AS $answer ):
					$answer_id = (int) $answer[ 'id' ];
					$answer_text = qu_prepare_post_data( $answer[ 'answer' ] );
					$answer_sort = (int) $answer[ 'sort' ];

					$answer_section = '';
					if( array_key_exists( 'section', $answer ) ){
						$answer_section = $answer[ 'section' ];
					}

					if( '' != $answer_id ):
						$wpdb->update( $questions_global->tables->answers, array( 'answer'  => $answer_text,
						                                                          'section' => $answer_section,
						                                                          'sort'    => $answer_sort
						), array( 'id' => $answer_id ) );
					else:
						$wpdb->insert( $questions_global->tables->answers, array( 'question_id' => $question_id,
						                                                          'answer'      => $answer_text,
						                                                          'section'     => $answer_section,
						                                                          'sort'        => $answer_sort
						) );
						$answer_id = $wpdb->insert_id;
					endif;

					do_action( 'questions_save_form_after_saving_answer', $survey_question, $answer_id );
				endforeach;
			endif;

			/*
			 * Saving question settings
			 */
			if( is_array( $settings ) && count( $settings ) > 0 ):
				foreach( $settings AS $name => $setting ):
					$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$questions_global->tables->settings} WHERE question_id = %d AND name = %s", $question_id, $name );
					$count = $wpdb->get_var( $sql );

					if( $count > 0 ):
						$wpdb->update( $questions_global->tables->settings, array( 'value' => qu_prepare_post_data( $settings[ $name ] ) ), array( 'question_id' => $question_id,
						                                                                                                                           'name'        => $name
						) );
					else:
						$wpdb->insert( $questions_global->tables->settings, array( 'name'        => $name,
						                                                           'question_id' => $question_id,
						                                                           'value'       => qu_prepare_post_data( $settings[ $name ] )
						) );

					endif;
				endforeach;
			endif;

		endforeach;

		do_action( 'questions_save_form', $form_id );

		// Preventing duplicate saving
		remove_action( 'save_post', array( __CLASS__, 'save_form' ), 50 );
	}

	/**
	 * Delete form
	 *
	 * @param int $form_id
	 * @since 1.0.0
	 */
	public static function delete_form( $form_id )
	{
		$form = new Questions_Form( $form_id );
		$form->delete();
	}

	/**
	 * Dublicating form AJAX
	 * @since 1.0.0
	 */
	public static function ajax_duplicate_form()
	{

		$form_id = $_REQUEST[ 'form_id' ];
		$form = get_post( $form_id );

		if( 'questions' != $form->post_type ){
			return;
		}

		$form = new Questions_Form( $form_id );
		$new_form_id = $form->duplicate( TRUE, TRUE, FALSE, TRUE, TRUE, TRUE, TRUE );

		$post = get_post( $new_form_id );

		$response = array( 'survey_id' => $new_form_id, 'post_title' => $post->post_title,
		                   'admin_url' => site_url( '/wp-admin/post.php?post=' . $new_form_id . '&action=edit' )
		);

		echo json_encode( $response );

		die();
	}

	/**
	 * Deleting form responses
	 *
	 * @since 1.0.0
	 */
	public static function ajax_delete_responses()
	{

		$form_id = $_REQUEST[ 'form_id' ];
		$form = get_post( $form_id );

		if( 'questions' != $form->post_type ){
			return;
		}

		$form = new Questions_form( $form_id );
		$new_form_id = $form->delete_responses();

		$response = array( 'form_id' => $form_id, 'deleted' => TRUE );

		echo json_encode( $response );

		die();
	}

	/**
	 * Cheks if we are in correct post type
	 *
	 * @return boolean $is_questions_post_type
	 * @since 1.0.0
	 */
	private static function is_questions_post_type()
	{
		global $post;

		// If there is no post > stop adding scripts
		if( !isset( $post ) ){
			return FALSE;
		}

		// If post type is wrong > stop adding scripts
		if( 'questions' != $post->post_type ){
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Adds the message area to the edit post site
	 *
	 * @since 1.0.0
	 */
	public static function jquery_messages_area()
	{
		$max_input_vars = ini_get( 'max_input_vars' );
		$html = '<div id="questions-messages" style="display:none;"><p class="questions-message">This is a dummy messaget</p></div><input type="hidden" id="max_input_vars" value ="' . $max_input_vars . '">'; // Updated, error, notice
		echo $html;
	}

	/**
	 * Registers and enqueues admin-specific styles.
	 *
	 * @since 1.0.0
	 */
	public static function register_styles()
	{
		if( !self::is_questions_post_type() ){
			return;
		}

		wp_enqueue_style( 'questions-admin-styles', QUESTIONS_URLPATH . '/components/admin/includes/css/form-builder.css' );
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @since 1.0.0
	 */
	public static function enqueue_scripts()
	{
		if( !self::is_questions_post_type() ){
			return;
		}

		$translation = array( 'delete'                       => esc_attr__( 'Delete', 'questions-locale' ),
		                      'yes'                          => esc_attr__( 'Yes', 'questions-locale' ),
		                      'no'                           => esc_attr__( 'No', 'questions-locale' ),
		                      'edit_form'                    => esc_attr__( 'Edit Form', 'questions-locale' ),
		                      'max_fields_near_limit'        => esc_attr__( 'You are under 50 form fields away from reaching PHP max_num_fields!', 'questions-locale' ),
		                      'max_fields_over_limit'        => esc_attr__( 'You are over the limit of PHP max_num_fields!', 'questions-locale' ),
		                      'max_fields_todo'              => esc_attr__( 'Please increase the value by adding <code>php_value max_input_vars [NUMBER OF INPUT VARS]</code> in your htaccess or contact your hoster. Otherwise your form can not be saved correct.', 'questions-locale' ),
		                      'of'                           => esc_attr__( 'of', 'questions-locale' ),
		                      'duplicated_form_successfully' => esc_attr__( 'Form duplicated successfully!', 'questions-locale' ),
		                      'deleted_results_successfully' => esc_attr__( 'Form results deleted successfully!', 'questions-locale' )
		);

		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-ui-tabs' );

		wp_enqueue_script( 'admin-widgets' );
		wp_enqueue_script( 'wpdialogs-popup' );

		wp_enqueue_script( 'admin-questions-post-type', QUESTIONS_URLPATH . '/components/admin/includes/js/form-builder.js' );
		wp_localize_script( 'admin-questions-post-type', 'translation_fb', $translation );

		if( wp_is_mobile() ){
			wp_enqueue_script( 'jquery-touch-punch' );
		}
	}
}

Questions_FormBuilder::init();