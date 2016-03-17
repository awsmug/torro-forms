<?php
/**
 * Torro Forms Form Builder
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
 * @version 1.0.0alpha1
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

		add_action( 'save_post', array( __CLASS__, 'save' ) );
		add_action( 'delete_post', array( __CLASS__, 'delete' ) );

		add_action( 'admin_notices', array( __CLASS__, 'jquery_messages_area' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_styles' ) );
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

		$containers = torro()->forms()->get( $form_id )->get_containers();

		if ( 0 !== count( $containers ) ) {

			$html .= '<div id="containers" class="tabs">';

			$html .= '<ul class="container-tabs">';
			foreach ( $containers AS $container ) {
				$html .= '<li id="tab-container-' . $container->id . '" class="tab-container"><input class="txt" type="text"/><a href="#torro-container-' . $container->id . '">' . $container->label . '</a></li>';
			}
			$html .= '<li id="container-add">' . __( '+', 'torro-forms' ) . '</a></li>';
			$html .= '</ul>';

			foreach ( $containers AS $container ) {
				$elements = torro()->containers()->get( $container->id )->get_elements();

				$html .= '<div id="torro-container-' . $container->id . '" class="tab-content torro-container">';
				$html .= '<div class="torro-drag-drop-inside">';


				foreach ( $elements AS $element ) {
					if( is_wp_error( $element ) ){
						$html .= $element->get_error_message() . '<br />';
						continue;
					}
					$html .= $element->get_admin_html();
					torro()->templatetags()->get_registered( 'formtags' )->add_element( $element->id, $element->label );
				}
				$html .= '<div class="drop-elements-here">' . __( 'Drop your elements here', 'torro-forms' ) . '</div>';
				$html .= '</div>';
				$html .= '<div class="container-buttons">';
				$html .= '<input type="button" name="delete_container" value="' . __( 'Delete Page', 'torro-forms' ) . '" class="button delete-container-button" />';
				$html .= '</div>';
				$html .= '<input type="hidden" name="container_id" value="' . $container->id . '" />';
				$html .= '<input type="hidden" name="containers[' . $container->id . '][id]" value="' . $container->id . '" />';
				$html .= '<input type="hidden" name="containers[' . $container->id . '][label]" value="' . $container->label . '" />';
				$html .= '<input type="hidden" name="containers[' . $container->id . '][sort]" value="' . $container->sort . '" />';
				$html .= '</div>';

			}

			$html .= '</div>';
		}else{
			$label =  esc_attr( 'Page', 'torro-forms' ) . ' 1';
			$temp_id = 'temp_id_' . time() * rand();

			$html .= '<div id="containers" class="tabs">';
			$html .= '<ul class="container-tabs">';
			$html .= '<li><input class="txt" type="text"/><a href="#torro-container-new">' . $label . '</a></li>';
			$html .= '<li id="container-add">' . __( '+', 'torro-forms' ) . '</a></li>';
			$html .= '</ul>';
			$html .= '<div id="torro-container-new" class="tab-content torro-container">';
			$html .= '<div class="torro-drag-drop-inside">';
			$html .= '<div class="drop-elements-here">' . __( 'Drop your elements here', 'torro-forms' ) . '</div>';
			$html .= '</div>';
			$html .= '<input type="hidden" name="container_id" value="' . $temp_id . '" />';
			$html .= '<input type="hidden" name="containers[' . $temp_id . '][id]" value="' . $temp_id . '" />';
			$html .= '<input type="hidden" name="containers[' . $temp_id . '][label]" value="' . $label . '" />';
			$html .= '<input type="hidden" name="containers[' . $temp_id . '][sort]" value="0" />';
			$html .= '</div>';
			$html .= '</div>';
		}

		$html .= '</div>';
		$html .= '</div>';

		ob_start();
		do_action( 'torro_formbuilder_dragdrop_end', $form_id );
		$html .= ob_get_clean();

		$html .= '<div id="delete_container_dialog">' . esc_html__( 'Do you really want to delete this page?', 'torro-forms' ) . '</div>';
		$html .= '<div id="delete_formelement_dialog">' . esc_html__( 'Do you really want to delete this element?', 'torro-forms' ) . '</div>';
		$html .= '<div id="delete_answer_dialog">' . esc_html__( 'Do you really want to delete this answer?', 'torro-forms' ) . '</div>';
		$html .= '<div id="delete_results_dialog"><h3>' . esc_html__( 'Attention!', 'torro-forms' ) . '</h3><p>' . esc_html__( 'This will erase all Answers who people given to this Form. Do you really want to delete all results of this Form?', 'torro-forms' ) . '</p></div>';

		$html .= '<input type="hidden" id="deleted_containers" name="deleted_container_ids" value="">';
		$html .= '<input type="hidden" id="deleted_formelements" name="deleted_element_ids" value="">';
		$html .= '<input type="hidden" id="deleted_answers" name="deleted_answer_ids" value="">';

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
			add_meta_box( 'form-elements', __( 'Elements', 'torro-forms' ), array(
				__CLASS__,
				'meta_box_form_elements'
			), 'torro-forms', 'side', 'high' );
			add_meta_box( 'form-options', __( 'Options', 'torro-forms' ), array(
				__CLASS__,
				'meta_box_options'
			), 'torro-forms', 'side', 'high' );
		}
	}

	/**
	 * Elements for dropping
	 *
	 * @since 1.0.0
	 */
	public static function meta_box_form_elements() {
		$html = '';

		$element_types = torro()->elements()->get_all_registered();

		foreach ( $element_types as $element ) {
			$html .= $element->get_admin_html();
		}

		echo $html;
	}

	/**
	 * General Form options
	 */
	public static function meta_box_options() {
		$html = '<div class="notices misc-pub-section">';
		$html .= '</div>';

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
	public static function save( $form_id ) {
		if ( ! array_key_exists( 'containers', $_REQUEST ) ) {
			return;
		}

		if ( array_key_exists( 'form-duplicate', $_REQUEST ) ) {
			return;
		}

		if ( wp_is_post_revision( $form_id ) ) {
			return;
		}

		if ( ! array_key_exists( 'post_type', $_POST ) ) {
			return;
		}

		if ( 'torro-forms' !== $_POST[ 'post_type' ] ) {
			return;
		}

		$containers              = $_POST[ 'containers' ];
		$deleted_container_ids   = $_POST[ 'deleted_container_ids' ];
		$deleted_element_ids     = $_POST[ 'deleted_element_ids' ];
		$deleted_answer_ids      = $_POST[ 'deleted_answer_ids' ];
		$show_results            = isset( $_POST[ 'show_results' ] ) ? $_POST[ 'show_results' ] : false;

		foreach ( $containers AS $container ) {
			if( isset( $container[ 'id' ] ) && 'container_id' !== $container[ 'id' ] ) {
				if( 'temp_id' === substr( $container[ 'id' ], 0, 7 )  ){
					$container[ 'id' ] = '';
				}

				$container_obj = torro()->containers()->get( $container['id'] );
				if ( is_wp_error( $container_obj ) ) {
					$container_obj = torro()->containers()->create_raw();
				}

				$container_obj->form_id = $form_id;
				$container_obj->label = $container['label'];
				$container_obj->sort = $container['sort'];

				$container_obj = torro()->containers()->update( $container_obj );
				if ( is_wp_error( $container_obj ) ) {
					//TODO: handle error here
					continue;
				}

				$container_id = $container_obj->id;

				do_action( 'torro_formbuilder_container_save', $form_id, $container_id );

				if ( isset( $container[ 'elements' ] ) ) {
					$elements = $container[ 'elements' ];

					foreach ( $elements AS $element ) {
						if( 'temp_id' === substr( $element[ 'id' ], 0, 7 )  ){
							$element[ 'id' ] = '';
						}

						$element_obj = torro()->elements()->get( $element['id'] );
						if ( is_wp_error( $element_obj ) ) {
							$element_obj = torro()->elements()->create_raw( $element['type'] );
						}

						$element_obj->form_id = $form_id;
						$element_obj->container_id = $container_id;
						$element_obj->label = $element['label'];
						$element_obj->sort = $element['sort'];
						$element_obj->type = $element['type'];

						$element_obj = torro()->elements()->update( $element_obj );
						if ( is_wp_error( $element_obj ) ) {
							//TODO: handle error here
							continue;
						}

						$element_id = $element_obj->id;

						do_action( 'torro_formbuilder_element_save', $form_id, $element_id );

						if ( isset( $element[ 'answers' ] ) ){
							$answers = $element[ 'answers' ];

							foreach( $answers AS $answer ){
								if( isset( $answer[ 'id' ] ) ){
									if( 'temp_id' === substr( $answer[ 'id' ], 0, 7 )  ){
										$answer[ 'id' ] = '';
									}

									$element_answer = torro()->element_answers()->get( $answer['id'] );
									if ( is_wp_error( $element_answer ) ) {
										$element_answer = torro()->element_answers()->create_raw();
									}

									$element_answer->element_id = $element_id;
									$element_answer->label = $answer['answer'];
									$element_answer->sort = $answer['sort'];
									// $element_answer->section = ''; // todo: Section have to be set if there is one

									$element_answer = torro()->element_answers()->update( $element_answer );
									if ( is_wp_error( $element_answer ) ) {
										//TODO: handle error here
										continue;
									}

									$element_answer_id = $element_answer->id;

									do_action( 'torro_formbuilder_element_answer_save', $form_id, $element_answer_id );
								}
							}
						}

						if( isset( $element[ 'settings' ] ) ){
							$settings = $element[ 'settings' ];

							foreach( $settings AS $setting ){
								if( 'temp_id' === substr( $setting[ 'id' ], 0, 7 )  ){
									$setting[ 'id' ] = '';
								}

								$element_setting = torro()->element_settings()->get( $setting['id'] );
								if ( is_wp_error( $element_setting ) ) {
									$element_setting = torro()->element_settings()->create_raw();
								}

								$element_setting->element_id = $element_id;
								$element_setting->name = $setting['name'];
								$element_setting->value = $setting['value'];

								$element_setting = torro()->element_settings()->update( $element_setting );
								if ( is_wp_error( $element_setting ) ) {
									//TODO: handle error here
									continue;
								}

								$element_setting_id = $element_setting->id;

								do_action( 'torro_formbuilder_element_answer_save', $form_id, $element_setting_id );
							}
						}
					}
				}
			}
		}

		/**
		 * Deleting old things
		 */
		if( ! empty( $deleted_container_ids ) ) {
			$deleted_container_ids = explode( ',', $deleted_container_ids );
			if ( 0 < count( $deleted_container_ids ) ) {
				foreach ( $deleted_container_ids as $deleted_container_id ) {
					torro()->containers()->get( $deleted_container_id )->delete();
				}
			}
		}
		if( ! empty( $deleted_element_ids ) ) {
			$deleted_element_ids = explode( ',', $deleted_element_ids );
			if ( 0 < count( $deleted_element_ids ) ) {
				foreach ( $deleted_element_ids as $deleted_element_id ) {
					torro()->elements()->get( $deleted_element_id )->delete();
				}
			}
		}
		if( ! empty( $deleted_answer_ids ) ) {
			$deleted_answer_ids = explode( ',', $deleted_answer_ids );
			if ( 0 < count( $deleted_answer_ids ) ) {
				foreach ( $deleted_answer_ids AS $deleted_answer_id ) {
					torro()->element_answers()->get( $deleted_answer_id )->delete();
				}
			}
		}

		/**
		 * Saving if results have to be shown after participating
		 */
		update_post_meta( $form_id, 'show_results', $show_results );

		do_action( 'torro_formbuilder_save', $form_id );

		remove_action( 'save_post', array( __CLASS__, 'save' ), 50 );
	}

	/**
	 * Delete form
	 *
	 * @param int $form_id
	 *
	 * @since 1.0.0
	 */
	public static function delete( $form_id ) {
		$form = new Torro_Form( $form_id );
		$form->delete();
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
		$html           = '<div id="form-messages" style="display:none;"><p class="form-message">This is a dummy messaget</p></div><input type="hidden" id="max_input_vars" value ="' . $max_input_vars . '">'; // Updated, error, notice
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

		wp_enqueue_style( 'torro-form-edit', torro()->get_asset_url( 'form-edit', 'css' ) );
		wp_enqueue_style( 'torro-templatetags', torro()->get_asset_url( 'templatetags', 'css' ) );
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
			'page'                         => __( 'Page', 'torro-forms' ),
			'delete_page'                  => __( 'Delete Page', 'torro-forms' ),
			'delete'                       => __( 'Delete', 'torro-forms' ),
			'yes'                          => __( 'Yes', 'torro-forms' ),
			'no'                           => __( 'No', 'torro-forms' ),
			'edit_form'                    => __( 'Edit Form', 'torro-forms' ),
			'drop_elements_here'           => __( 'Drop your elements here', 'torro-forms' ),
			'max_fields_near_limit'        => __( 'You are under 50 form fields away from reaching PHP max_num_fields!', 'torro-forms' ),
			'max_fields_over_limit'        => __( 'You are over the limit of PHP max_num_fields!', 'torro-forms' ),
			'max_fields_todo'              => __( 'Please increase the value by adding <code>php_value max_input_vars [NUMBER OF INPUT VARS]</code> in your htaccess or contact your hoster. Otherwise your form can not be saved correct.', 'torro-forms' ),
			'of'                           => __( 'of', 'torro-forms' ),
			'duplicated_form_successfully' => __( 'Form duplicated successfully!', 'torro-forms' ),
			'deleted_results_successfully' => __( 'Form results deleted successfully!', 'torro-forms' ),
			'copied'                       => __( 'Copied!', 'torro-forms' ),
			'nonce_duplicate_form'         => torro()->ajax()->get_nonce( 'duplicate_form' ),
			'nonce_delete_responses'       => torro()->ajax()->get_nonce( 'delete_responses' ),
		);

		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-ui-tabs' );

		wp_enqueue_script( 'admin-widgets' );
		wp_enqueue_script( 'wpdialogs-popup' );

		wp_enqueue_script( 'clipboard', torro()->get_asset_url( 'clipboard/dist/clipboard', 'vendor-js' ) );

		wp_enqueue_script( 'torro-form-edit', torro()->get_asset_url( 'form-edit', 'js' ), array(
			'wp-util',
			'clipboard',
		) );
		wp_localize_script( 'torro-form-edit', 'translation_fb', $translation );

		wp_enqueue_script( 'torro-templatetags', torro()->get_asset_url( 'templatetags', 'js' ), array(
			'torro-form-edit',
			'tiny'
		)  );

		if ( wp_is_mobile() ) {
			wp_enqueue_script( 'jquery-touch-punch' );
		}
	}
}

Torro_Formbuilder::init();
