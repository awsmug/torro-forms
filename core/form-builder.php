<?php
/**
 * Torro Forms Form Builder
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Torro_Formbuilder {

	/**
	 * Init in WordPress, run on constructor
	 *
	 * @return null
	 * @since 1.0.0
	 */
	public static function init() {
		if ( ! is_admin() ) {
			return null;
		}

		add_action( 'edit_form_after_title', array( __CLASS__, 'droppable_area' ), 20 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ), 10 );

		add_action( 'save_post', array( __CLASS__, 'save_form' ) );
		add_action( 'delete_post', array( __CLASS__, 'delete_form' ) );

		add_action( 'wp_ajax_torro_duplicate_form', array( __CLASS__, 'ajax_duplicate_form' ) );
		add_action( 'wp_ajax_torro_delete_responses', array( __CLASS__, 'ajax_delete_responses' ) );

		add_action( 'admin_notices', array( __CLASS__, 'jquery_messages_area' ) );
		add_action( 'admin_print_styles', array( __CLASS__, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
	}

	/**
	 * Place to drop elements
	 *
	 * @since 1.0.0
	 */
	public static function droppable_area() {
		global $post;

		if ( ! torro_is_formbuilder() ) {
			return;
		}

		$form_id = $post->ID;

		$html = '<div id="torro-content" class="drag-drop">';
		$html .= '<div id="drag-drop-area" class="widgets-holder-wrap">';

		ob_start();
		do_action( 'torro_formbuilder_dragdrop_start', $form_id );
		$html .= ob_get_clean();

		$html .= '<div id="drag-drop-inside">';
		$form = new Torro_Form( $form_id );

		// Running each Element
		if ( count( $form->elements ) > 0 ) {
			foreach ( $form->elements as $element ) {
				$html .= $element->draw_admin();
				torro_add_element_templatetag( $element->id, $element->label );
			}
		} else {
			$html .= '<div id="torro-drop-elements-here">' . __( 'Drop your Elements here!', 'torro-forms' ) . '</div>';
		}

		$html .= '</div>';

		$html .= '</div>';
		$html .= '</div>';

		ob_start();
		do_action( 'torro_formbuilder_dragdrop_end', $form_id );
		$html .= ob_get_clean();

		$html .= '<div id="delete_formelement_dialog">' . esc_html__( 'Do you really want to delete this element?', 'torro-forms' ) . '</div>';
		$html .= '<div id="delete_answer_dialog">' . esc_html__( 'Do you really want to delete this answer?', 'torro-forms' ) . '</div>';
		$html .= '<div id="delete_results_dialog"><h3>' . esc_html__( 'Attention!', 'torro-forms' ) . '</h3><p>' . esc_html__( 'This will erase all Answers who people given to this Form. Do you really want to delete all results of this Form?', 'torro-forms' ) . '</p></div>';

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
	public static function meta_boxes( $post_type ) {
		$post_types = array( 'torro-forms' );

		if ( in_array( $post_type, $post_types, true ) ) {
			add_meta_box( 'form-elements', __( 'Elements', 'torro-forms' ), array( __CLASS__, 'meta_box_form_elements' ), 'torro-forms', 'side', 'high' );
			add_meta_box( 'form-options', __( 'Options', 'torro-forms' ), array( __CLASS__, 'meta_box_options' ), 'torro-forms', 'side', 'high' );
		}
	}

	/**
	 * Elements for dropping
	 *
	 * @since 1.0.0
	 */
	public static function meta_box_form_elements() {
		$html = '';

		$element_types = torro()->elements()->get_all();

		foreach ( $element_types as $element ) {
			$html .= $element->draw_admin();
		}

		echo $html;
	}

	/**
	 * General Form options
	 */
	public static function meta_box_options() {
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
		do_action( 'torro_formbuilder_options' );
		$html .= ob_get_clean();

		$html .= '<div class="section general-settings">';
		$html .= '<input id="form-duplicate-button" name="form-duplicate" type="button" class="button" value="' . esc_attr__( 'Duplicate Form', 'torro-forms' ) . '" />';
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
	public static function save_form( $form_id ) {
		global $wpdb;

		if ( ! array_key_exists( 'elements', $_REQUEST ) ) {
			return;
		}

		if ( array_key_exists( 'form-duplicate', $_REQUEST ) ) {
			return;
		}

		if ( wp_is_post_revision( $form_id ) ) {
			return;
		}

		if( !array_key_exists( 'post_type', $_POST ) ) {
			return;
		}

		if ( 'torro-forms' !== $_POST[ 'post_type' ] ) {
			return;
		}

		$form_elements = $_POST['elements'];
		$form_deleted_formelements = $_POST['form_deleted_formelements'];
		$form_deleted_answers = $_POST['form_deleted_answers'];
		$form_show_results = isset( $_POST['show_results'] ) ? $_POST['show_results'] : false;

		/**
		 * Saving if results have to be shown after participating
		 */
		update_post_meta( $form_id, 'show_results', $form_show_results );

		$form_deleted_formelements = explode( ',', $form_deleted_formelements );

		/**
		 * Deleting deleted answers
		 */
		if ( 0 < count( $form_deleted_formelements ) ) {
			foreach ( $form_deleted_formelements as $deleted_element ) {
				$wpdb->delete( $wpdb->torro_elements, array( 'id' => $deleted_element ) );
				$wpdb->delete( $wpdb->torro_element_answers, array( 'element_id' => $deleted_element ) );
			}
		}

		$form_deleted_answers = explode( ',', $form_deleted_answers );

		/*
		 * Deleting deleted answers
		 */
		if ( 0 < count( $form_deleted_answers ) ) {
			foreach ( $form_deleted_answers AS $deleted_answer ) {
				$wpdb->delete( $wpdb->torro_element_answers, array( 'id' => $deleted_answer ) );
			}
		}

		/*
		 * Saving elements
		 */
		foreach ( $form_elements AS $key => $element ) {
			if ( 'widget_formelement_XXnrXX' === $key ) {
				continue;
			}

			$element_id = (int) $element['id'];
			$label = '';
			$sort = (int) $element['sort'];
			$type = $element['type'];

			if ( array_key_exists( 'label', $element ) ) {
				$label = torro_prepare_post_data( $element[ 'label' ] );
			}

			$answers = array();
			$settings = array();

			if ( array_key_exists( 'answers', $element ) ) {
				$answers = $element[ 'answers' ];
			}

			if ( array_key_exists( 'settings', $element ) ) {
				$settings = $element[ 'settings' ];
			}

			// Saving Elements
			if ( 0 < $element_id )
			{
				// Updating if Element already exists
				$wpdb->update( $wpdb->torro_elements, array(
						'label'	=> $label,
						'sort'	=> $sort,
						'type'	=> $type
				), array( 'id' => $element_id ) );
			} else {
				// Adding new Element
				$wpdb->insert( $wpdb->torro_elements, array(
						'form_id'	=> $form_id,
						'label'		=> $label,
						'sort'		=> $sort,
						'type'		=> $type
				) );

				$element_id = $wpdb->insert_id;
			}

			do_action( 'torro_formbuilder_element_save', $form_id, $element, $element_id );

			/*
			 * Saving answers
			 */
			if ( is_array( $answers ) && 0 < count( $answers ) ) {
				foreach ( $answers as $answer ) {
					$answer_id = (int) $answer[ 'id' ];
					$answer_text = torro_prepare_post_data( $answer[ 'answer' ] );
					$answer_sort = (int) $answer[ 'sort' ];

					$answer_section = '';
					if ( array_key_exists( 'section', $answer ) ) {
						$answer_section = $answer[ 'section' ];
					}

					if ( 0 < $answer_id ) {
						$wpdb->update( $wpdb->torro_element_answers, array(
							'answer'  => $answer_text,
							'section' => $answer_section,
							'sort'    => $answer_sort,
						), array( 'id' => $answer_id ) );
					} else {
						$wpdb->insert( $wpdb->torro_element_answers, array(
							'element_id' => $element_id,
							'answer'     => $answer_text,
							'section'    => $answer_section,
							'sort'       => $answer_sort,
						) );
						$answer_id = $wpdb->insert_id;
					}

					do_action( 'torro_formbuilder_element_answer_save', $form_id, $element, $element_id, $answer, $answer_id );
				}
			}

			/*
			 * Saving Element Settings
			 */
			if ( is_array( $settings ) && 0 < count( $settings )) {
				foreach ( $settings as $name => $setting ) {
					$sql = $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->torro_settings WHERE element_id = %d AND name = %s", $element_id, $name );
					$count = absint( $wpdb->get_var( $sql ) );

					if( 0 < $count ) {
						$wpdb->update( $wpdb->torro_settings, array( 'value' => torro_prepare_post_data( $settings[ $name ] ) ), array(
							'element_id'	=> $element_id,
							'name'			=> $name,
						) );
					} else {
						$wpdb->insert( $wpdb->torro_settings, array(
							'name'			=> $name,
							'element_id'	=> $element_id,
							'value'			=> torro_prepare_post_data( $settings[ $name ] ),
						) );
					}
				}
			}
		}

		do_action( 'torro_formbuilder_save', $form_id );

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
	public static function delete_form( $form_id ) {
		$form = new Torro_Form( $form_id );
		$form->delete();
	}

	/**
	 * Duplicating form AJAX
	 *
	 * @since 1.0.0
	 */
	public static function ajax_duplicate_form() {

		$form_id = $_REQUEST[ 'form_id' ];
		$form = get_post( $form_id );

		if ( 'torro-forms' !== $form->post_type ) {
			return;
		}

		$form = new Torro_Form( $form_id );
		$new_form_id = $form->duplicate( true, true, false, true, true, true, true );

		$post = get_post( $new_form_id );

		$response = array(
			'form_id'    => $new_form_id,
			'post_title' => $post->post_title,
			'admin_url'  => admin_url( 'post.php?post=' . $new_form_id . '&action=edit' )
		);

		echo json_encode( $response );

		die();
	}

	/**
	 * Deleting form responses
	 *
	 * @since 1.0.0
	 */
	public static function ajax_delete_responses() {
		$form_id = absint( $_REQUEST[ 'form_id' ] );
		$form = get_post( $form_id );

		if ( 'torro-forms' !== $form->post_type ) {
			return;
		}

		$form = new Torro_form( $form_id );
		$new_form_id = $form->delete_responses();

		$entries = torro()->resulthandlers()->get( 'entries' );
		if ( is_wp_error( $entries ) ) {
			return;
		}

		$response = array(
			'form_id'	=> $form_id,
			'deleted'	=> true,
			'html'		=> $entries->show_not_found_notice(),
		);

		echo json_encode( $response );

		die();
	}

	/**
	 * Adds the message area to the edit post site
	 *
	 * @since 1.0.0
	 */
	public static function jquery_messages_area() {
		if ( ! torro_is_formbuilder() ) {
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
	public static function enqueue_styles() {
		if ( ! torro_is_formbuilder() ) {
			return;
		}

		wp_enqueue_style( 'torro-form-edit', torro()->asset_url( 'form-edit', 'css' ) );
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @since 1.0.0
	 */
	public static function enqueue_scripts() {
		if ( ! torro_is_formbuilder() ) {
			return;
		}

		$translation = array(
			'delete'						=> esc_attr__( 'Delete', 'torro-forms' ),
			'yes'							=> esc_attr__( 'Yes', 'torro-forms' ),
			'no'							=> esc_attr__( 'No', 'torro-forms' ),
			'edit_form'						=> esc_attr__( 'Edit Form', 'torro-forms' ),
			'max_fields_near_limit'			=> esc_attr__( 'You are under 50 form fields away from reaching PHP max_num_fields!', 'torro-forms' ),
			'max_fields_over_limit'			=> esc_attr__( 'You are over the limit of PHP max_num_fields!', 'torro-forms' ),
			'max_fields_todo'				=> esc_attr__( 'Please increase the value by adding <code>php_value max_input_vars [NUMBER OF INPUT VARS]</code> in your htaccess or contact your hoster. Otherwise your form can not be saved correct.', 'torro-forms' ),
			'of'							=> esc_attr__( 'of', 'torro-forms' ),
			'duplicated_form_successfully'	=> esc_attr__( 'Form duplicated successfully!', 'torro-forms' ),
			'deleted_results_successfully'	=> esc_attr__( 'Form results deleted successfully!', 'torro-forms' ),
			'copied'						=> esc_attr__( 'Copied!', 'torro-forms' )
		);

		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-ui-tabs' );

		wp_enqueue_script( 'admin-widgets' );
		wp_enqueue_script( 'wpdialogs-popup' );

		wp_enqueue_script( 'clipboard', torro()->asset_url( 'clipboard', 'vendor-js' ) );

		wp_enqueue_script( 'torro-form-edit', torro()->asset_url( 'form-edit', 'js' ), array( 'clipboard' ) );
		wp_localize_script( 'torro-form-edit', 'translation_fb', $translation );

		if ( wp_is_mobile() ) {
			wp_enqueue_script( 'jquery-touch-punch' );
		}
	}
}

Torro_Formbuilder::init();
