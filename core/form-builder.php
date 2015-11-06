<?php
/**
 * Awesome Forms Form Builder
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package AwesomeForms/Core
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

if( !defined( 'ABSPATH' ) )
{
	exit;
}

class AF_FormBuilder
{

	/**
	 * Init in WordPress, run on constructor
	 *
	 * @return null
	 * @since 1.0.0
	 */
	public static function init()
	{
		if( !is_admin() )
		{
			return NULL;
		}

		add_action( 'edit_form_after_title', array( __CLASS__, 'droppable_area' ), 20 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ), 10 );

		add_action( 'save_post', array( __CLASS__, 'save_form' ) );
		add_action( 'delete_post', array( __CLASS__, 'delete_form' ) );

		add_action( 'wp_ajax_af_duplicate_form', array( __CLASS__, 'ajax_duplicate_form' ) );
		add_action( 'wp_ajax_af_delete_responses', array( __CLASS__, 'ajax_delete_responses' ) );

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
		global $post, $af_global;

		if( !af_is_formbuilder() )
		{
			return;
		}

		$html = '<div id="af-content" class="drag-drop">';
		$html .= '<div id="drag-drop-area" class="widgets-holder-wrap">';

		ob_start();
		do_action( 'form_drag_drop_top' );
		$html .= ob_get_clean();

		$html .= '<div id="drag-drop-inside">';
		$form = new AF_Form( $post->ID );

		// Running each Element
		if( count( $form->elements ) > 0 )
		{
			foreach( $form->elements AS $element )
			{
				$html .= $element->draw_admin();
				af_add_element_templatetag( $element->id, $element->label );
			}
		}
		else
		{
			$html .= '<div id="af-drop-elements-here">' . __( 'Drop your Elements here!', 'af-locale' ) . '</div>';
		}

		$html .= '</div>';

		$html .= '</div>';
		$html .= '</div>';

		$html .= '<div id="delete_formelement_dialog">' . esc_attr__( 'Do you really want to delete this element?', 'af-locale' ) . '</div>';
		$html .= '<div id="delete_answer_dialog">' . esc_attr__( 'Do you really want to delete this answer?', 'af-locale' ) . '</div>';
		$html .= '<div id="delete_results_dialog"><h3>' . esc_attr__( 'Attention!', 'af-locale' ) . '</h3><p>' . esc_attr__( 'This will erase all Answers who people given to this Form. Do you really want to delete all results of this Form?', 'af-locale' ) . '</p></div>';

		$html .= '<input type="hidden" id="deleted_formelements" name="form_deleted_formelements" value="">';
		$html .= '<input type="hidden" id="deleted_answers" name="form_deleted_answers" value="">';

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
		$post_types = array( 'af-forms' );

		if( in_array( $post_type, $post_types ) )
		{
			add_meta_box( 'form-elements',
			              esc_attr__( 'Elements', 'af-locale' ),
			              array( __CLASS__, 'meta_box_form_elements' ),
			              'af-forms', 'side', 'high' );

			add_meta_box( 'form-options',
			              esc_attr__( 'Options', 'af-locale' ),
			              array( __CLASS__, 'meta_box_options' ),
			              'af-forms', 'side', 'high' );

		}
	}

	/**
	 * Elements for dropping
	 *
	 * @since 1.0.0
	 */
	public static function meta_box_form_elements()
	{
		global $af_global;

		$html = '';

		foreach( $af_global->element_types AS $element )
		{
			$html .= $element->draw_admin();
		}

		echo $html;
	}

	/**
	 * General Form options
	 */
	public static function meta_box_options()
	{
		$html  = '<div class="notices misc-pub-section">';
		$html .= '</div>';

		/** Todo Adding this later!
		$html .= '<div class="misc-pub-section">';
		$html .= '<label for="form-actions-hide"><input id="form-actions-hide" class="hide-postbox-tog" type="checkbox" checked="checked" value="form-actions" name="form-actions-hide">Response Handling</label><br />';
		$html .= '<label for="form-results-hide"><input id="form-results-hide" class="hide-postbox-tog" type="checkbox" value="form-results" name="form-results-hide">Results</label><br />';
		$html .= '<label for="form-restrictions-hide"><input id="form-restrictions-hide" class="hide-postbox-tog" type="checkbox" value="form-restrictions" name="form-restrictions-hide">Restrictions</label><br />';
		$html .= '</div>';
		*/

		ob_start();
		do_action( 'af_form_options' );
		$html .= ob_get_clean();

		$html .= '<div class="section general-settings">';
		$html .= '<input id="form-duplicate-button" name="form-duplicate" type="button" class="button" value="' . esc_attr__( 'Duplicate Form', 'af-locale' ) . '" />';
		$html .= '</div>';

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
		global $af_global, $wpdb;

		if( !array_key_exists( 'elements', $_REQUEST ) )
		{
			return;
		}

		if( array_key_exists( 'form-duplicate', $_REQUEST ) )
		{
			return;
		}

		if( wp_is_post_revision( $form_id ) )
		{
			return;
		}

		if( !array_key_exists( 'post_type', $_POST ) )
		{
			return;
		}

		if( 'af-forms' != $_POST[ 'post_type' ] )
		{
			return;
		}

		$form_elements = $_POST[ 'elements' ];
		$form_deleted_formelements = $_POST[ 'form_deleted_formelements' ];
		$form_deleted_answers = $_POST[ 'form_deleted_answers' ];
		$form_show_results = $_POST[ 'show_results' ];

		/**
		 * Saving if results have to be shown after participating
		 */
		update_post_meta( $form_id, 'show_results', $form_show_results );

		$form_deleted_formelements = explode( ',', $form_deleted_formelements );

		/**
		 * Deleting deleted answers
		 */
		if( is_array( $form_deleted_formelements ) && count( $form_deleted_formelements ) > 0 ):
			foreach( $form_deleted_formelements AS $deleted_element ):
				$wpdb->delete( $af_global->tables->elements, array( 'id' => $deleted_element ) );
				$wpdb->delete( $af_global->tables->element_answers, array( 'element_id' => $deleted_element ) );
			endforeach;
		endif;

		$form_deleted_answers = explode( ',', $form_deleted_answers );

		/*
		 * Deleting deleted answers
		 */
		if( is_array( $form_deleted_answers ) && count( $form_deleted_answers ) > 0 ):
			foreach( $form_deleted_answers AS $deleted_answer ):
				$wpdb->delete( $af_global->tables->element_answers, array( 'id' => $deleted_answer ) );
			endforeach;
		endif;

		/*
		 * Saving elements
		 */
		foreach( $form_elements AS $key => $element ):
			if( 'widget_formelement_XXnrXX' == $key )
			{
				continue;
			}

			$element_id = (int) $element[ 'id' ];
			$label = '';
			$sort = (int) $element[ 'sort' ];
			$type = $element[ 'type' ];

			if( array_key_exists( 'label', $element ) )
			{
				$label = af_prepare_post_data( $element[ 'label' ] );
			}

			$answers = array();
			$settings = array();

			if( array_key_exists( 'answers', $element ) )
			{
				$answers = $element[ 'answers' ];
			}

			if( array_key_exists( 'settings', $element ) )
			{
				$settings = $element[ 'settings' ];
			}

			// Saving Elements
			if( '' != $element_id ):
				// Updating if Element already exists
				$wpdb->update( $af_global->tables->elements, array(
					'label' => $label,
					'sort'  => $sort,
					'type'  => $type
				), array( 'id' => $element_id ) );
			else:

				// Adding new Element
				$wpdb->insert( $af_global->tables->elements, array(
					'form_id' => $form_id,
					'label'   => $label,
					'sort'    => $sort,
					'type'    => $type
				) );

				$element_id = $wpdb->insert_id;
			endif;

			do_action( 'af_save_form_after_saving_question', $element, $element_id );

			/*
			 * Saving answers
			 */
			if( is_array( $answers ) && count( $answers ) > 0 ):
				foreach( $answers AS $answer ):
					$answer_id = (int) $answer[ 'id' ];
					$answer_text = af_prepare_post_data( $answer[ 'answer' ] );
					$answer_sort = (int) $answer[ 'sort' ];

					$answer_section = '';
					if( array_key_exists( 'section', $answer ) )
					{
						$answer_section = $answer[ 'section' ];
					}

					if( '' != $answer_id ):
						$wpdb->update( $af_global->tables->element_answers, array(
							'answer'  => $answer_text,
							'section' => $answer_section,
							'sort'    => $answer_sort
						), array( 'id' => $answer_id ) );
					else:
						$wpdb->insert( $af_global->tables->element_answers, array(
							'element_id' => $element_id,
							'answer'     => $answer_text,
							'section'    => $answer_section,
							'sort'       => $answer_sort
						) );
						$answer_id = $wpdb->insert_id;
					endif;

					do_action( 'af_save_form_after_saving_answer', $element, $answer_id );
				endforeach;
			endif;

			/*
			 * Saving Element Settings
			 */
			if( is_array( $settings ) && count( $settings ) > 0 ):
				foreach( $settings AS $name => $setting ):
					$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$af_global->tables->settings} WHERE element_id = %d AND name = %s", $element_id, $name );
					$count = $wpdb->get_var( $sql );

					if( $count > 0 ):
						$wpdb->update( $af_global->tables->settings, array( 'value' => af_prepare_post_data( $settings[ $name ] ) ), array(
							'element_id' => $element_id,
							'name'       => $name
						) );
					else:
						$wpdb->insert( $af_global->tables->settings, array(
							'name'       => $name,
							'element_id' => $element_id,
							'value'      => af_prepare_post_data( $settings[ $name ] )
						) );

					endif;
				endforeach;
			endif;

		endforeach;

		do_action( 'af_save_form', $form_id );

		// Preventing duplicate saving
		remove_action( 'save_post', array( __CLASS__, 'save_form' ), 50 );
	}

	/**
	 * Delete form
	 *
	 * @param int $form_id
	 *
	 * @since 1.0.0
	 */
	public static function delete_form( $form_id )
	{
		$form = new AF_Form( $form_id );
		$form->delete();
	}

	/**
	 * Duplicating form AJAX
	 *
	 * @since 1.0.0
	 */
	public static function ajax_duplicate_form()
	{

		$form_id = $_REQUEST[ 'form_id' ];
		$form = get_post( $form_id );

		if( 'af-forms' != $form->post_type )
		{
			return;
		}

		$form = new AF_Form( $form_id );
		$new_form_id = $form->duplicate( TRUE, TRUE, FALSE, TRUE, TRUE, TRUE, TRUE );

		$post = get_post( $new_form_id );

		$response = array(
			'form_id'    => $new_form_id,
			'post_title' => $post->post_title,
			'admin_url'  => site_url( '/wp-admin/post.php?post=' . $new_form_id . '&action=edit' )
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

		if( 'af-forms' != $form->post_type )
		{
			return;
		}

		$form = new AF_form( $form_id );
		$new_form_id = $form->delete_responses();

		$response = array( 'form_id' => $form_id, 'deleted' => TRUE );

		echo json_encode( $response );

		die();
	}

	/**
	 * Adds the message area to the edit post site
	 *
	 * @since 1.0.0
	 */
	public static function jquery_messages_area()
	{
		if( !af_is_formbuilder() )
		{
			return;
		}

		$max_input_vars = ini_get( 'max_input_vars' );
		$html = '<div id="form-messages" style="display:none;"><p class="form-message">This is a dummy messaget</p></div><input type="hidden" id="max_input_vars" value ="' . $max_input_vars . '">'; // Updated, error, notice
		echo $html;
	}

	/**
	 * Registers and enqueues admin-specific styles.
	 *
	 * @since 1.0.0
	 */
	public static function register_styles()
	{
		if( !af_is_formbuilder() )
		{
			return;
		}

		wp_enqueue_style( 'af-admin-styles', AF_URLPATH . 'core/includes/css/form-builder.css' );
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @since 1.0.0
	 */
	public static function enqueue_scripts()
	{
		if( !af_is_formbuilder() )
		{
			return;
		}

		$translation = array(
			'delete'                       => esc_attr__( 'Delete', 'af-locale' ),
			'yes'                          => esc_attr__( 'Yes', 'af-locale' ),
			'no'                           => esc_attr__( 'No', 'af-locale' ),
			'edit_form'                    => esc_attr__( 'Edit Form', 'af-locale' ),
			'max_fields_near_limit'        => esc_attr__( 'You are under 50 form fields away from reaching PHP max_num_fields!', 'af-locale' ),
			'max_fields_over_limit'        => esc_attr__( 'You are over the limit of PHP max_num_fields!', 'af-locale' ),
			'max_fields_todo'              => esc_attr__( 'Please increase the value by adding <code>php_value max_input_vars [NUMBER OF INPUT VARS]</code> in your htaccess or contact your hoster. Otherwise your form can not be saved correct.', 'af-locale' ),
			'of'                           => esc_attr__( 'of', 'af-locale' ),
			'duplicated_form_successfully' => esc_attr__( 'Form duplicated successfully!', 'af-locale' ),
			'deleted_results_successfully' => esc_attr__( 'Form results deleted successfully!', 'af-locale' ),
			'copied'                       => esc_attr__( 'Copied!', 'af-locale' )
		);

		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-ui-tabs' );

		wp_enqueue_script( 'admin-widgets' );
		wp_enqueue_script( 'wpdialogs-popup' );

		wp_enqueue_script( 'af-admin-forms-clipboard-js', AF_URLPATH . 'core/includes/js/clipboard.min.js' );

		wp_enqueue_script( 'af-admin-forms-post-type', AF_URLPATH . 'core/includes/js/form-builder.js' );
		wp_localize_script( 'af-admin-forms-post-type', 'translation_fb', $translation );

		if( wp_is_mobile() )
		{
			wp_enqueue_script( 'jquery-touch-punch' );
		}
	}
}

AF_FormBuilder::init();
