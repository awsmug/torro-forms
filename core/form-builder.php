<?php
/**
 * Core: Torro_Formbuilder class
 *
 * @package TorroForms
 * @subpackage Core
 * @version 1.0.0-beta.4
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms form builder class
 *
 * Handles form building processes in the admin.
 *
 * @since 1.0.0-beta.1
 */
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

		if ( ! torro()->is_formbuilder() ) {
			return;
		}

		$form_id = $post->ID;

		$html = '<div id="torro-content" class="drag-drop">';
		$html .= '<div id="drag-drop-area" class="widgets-holder-wrap">';

		ob_start();
		do_action( 'torro_formbuilder_dragdrop_start', $form_id );
		$html .= ob_get_clean();

		$containers = torro()->forms()->get( $form_id )->containers;

		if ( 0 !== count( $containers ) ) {

			$html .= '<div id="containers" class="tabs">';

			$html .= '<ul class="container-tabs">';
			foreach ( $containers AS $container ) {
				$html .= '<li id="tab-container-' . $container->id . '" class="tab-container"><input class="txt" type="text"/><a href="#torro-container-' . $container->id . '">' . $container->label . '</a></li>';
			}
			$html .= '<li id="container-add">' . __( '+', 'torro-forms' ) . '</a></li>';
			$html .= '</ul>';

			foreach ( $containers AS $container ) {
				$elements = torro()->containers()->get( $container->id )->elements;

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
				$html .= '<a class="delete-button delete-container-button">' . __( 'Delete Page', 'torro-forms' ) . '</a>';
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
			$temp_id = torro_generate_temp_id();

			$html .= '<div id="containers" class="tabs">';
			$html .= '<ul class="container-tabs">';
			$html .= '<li class="tab-container"><input class="txt" type="text" /><a href="#torro-container-new">' . $label . '</a></li>';
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
		$html .= '<div id="delete_results_dialog"><h3>' . esc_html__( 'Attention!', 'torro-forms' ) . '</h3><p>' . esc_html__( 'This will erase all answers who people given to this form. Do you really want to delete all results of this form?', 'torro-forms' ) . '</p></div>';

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
		$post_types = array( 'torro_form' );

		if ( in_array( $post_type, $post_types, true ) ) {
			add_meta_box( 'form-elements', __( 'Elements', 'torro-forms' ), array(
				__CLASS__,
				'meta_box_form_elements'
			), 'torro_form', 'side', 'high' );
			add_meta_box( 'form-options', __( 'Options', 'torro-forms' ), array(
				__CLASS__,
				'meta_box_options'
			), 'torro_form', 'side', 'high' );
		}
	}

	/**
	 * Elements for dropping
	 *
	 * @since 1.0.0
	 */
	public static function meta_box_form_elements() {
		$html = '';

		$element_types = torro()->element_types()->get_all_registered();

		$dummy_element = new Torro_Element();

		foreach ( $element_types as $element_type ) {
			$html .= $element_type->get_admin_html( $dummy_element );
		}

		echo $html;
	}

	/**
	 * General Form options
	 */
	public static function meta_box_options() {
		global $post;

		$html = '<div class="misc-pub-section form-shortcode">';
		$html .= torro_clipboard_field( __( 'Form Shortcode', 'torro-forms' ), '[form id=' . $post->ID . ']' );
		$html .= '</div>';

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
		if ( ! isset( $_POST['containers'] ) ) {
			return;
		}

		if ( isset( $_POST['form-duplicate'] ) ) {
			return;
		}

		if ( wp_is_post_revision( $form_id ) ) {
			return;
		}

		if ( ! isset( $_POST['post_type'] ) ) {
			return;
		}

		if ( 'torro_form' !== wp_unslash( $_POST['post_type'] ) ) {
			return;
		}

		$containers              = wp_unslash( $_POST['containers'] );
		$deleted_container_ids   = wp_unslash( $_POST['deleted_container_ids'] );
		$deleted_element_ids     = wp_unslash( $_POST['deleted_element_ids'] );
		$deleted_answer_ids      = wp_unslash( $_POST['deleted_answer_ids'] );
		$show_results            = isset( $_POST['show_results'] ) ? (bool) $_POST['show_results'] : false;

		foreach ( $containers as $container ) {
			if( isset( $container['id'] ) && 'container_id' !== $container['id'] ) {
				if( torro_is_temp_id( $container['id'] )  ){
					$container['id'] = '';
				}

				$container_args = array(
					'label'		=> $container['label'],
					'sort'		=> $container['sort'],
				);

				$container_obj = null;
				if ( $container['id'] && torro()->containers()->exists( $container['id'] ) ) {
					$container_obj = torro()->containers()->update( $container['id'], $container_args );
				} else {
					$container_obj = torro()->containers()->create( $form_id, $container_args );
				}

				if ( is_wp_error( $container_obj ) ) {
					torro()->admin_notices()->add( $container_obj );
					continue;
				}

				$container_id = $container_obj->id;

				do_action( 'torro_formbuilder_container_save', $form_id, $container_id );

				if ( isset( $container['elements'] ) ) {
					$elements = $container['elements'];

					foreach ( $elements as $element ) {
						if( torro_is_temp_id( $element['id'] )  ){
							$element['id'] = '';
						}

						$element_args = array(
							'type'		=> $element['type'],
							'label'		=> $element['label'],
							'sort'		=> $element['sort'],
						);

						$element_obj = null;
						if ( $element['id'] && torro()->elements()->exists( $element['id'] ) ) {
							$element_obj = torro()->elements()->update( $element['id'], $element_args );
						} else {
							$element_obj = torro()->elements()->create( $container_id, $element_args );
						}
						if ( is_wp_error( $element_obj ) ) {
							torro()->admin_notices()->add( $element_obj );
							continue;
						}

						$element_id = $element_obj->id;

						do_action( 'torro_formbuilder_element_save', $form_id, $element_id );

						if ( isset( $element['answers'] ) ){
							$answers = $element['answers'];

							foreach ( $answers as $answer ){
								if ( isset( $answer['id'] ) ) {
									if ( torro_is_temp_id( $answer['id'] ) ) {
										$answer['id'] = '';
									}

									$answer_args = array(
										'answer'	=> $answer['answer'],
										'sort'		=> $answer['sort'],
										//'section'	=> '', //TODO: section has to be set if there is one
									);

									$answer_obj = null;
									if ( $answer['id'] && torro()->element_answers()->exists( $answer['id'] ) ) {
										$answer_obj = torro()->element_answers()->update( $answer['id'], $answer_args );
									} else {
										$answer_obj = torro()->element_answers()->create( $element_id, $answer_args );
									}
									if ( is_wp_error( $answer_obj ) ) {
										torro()->admin_notices()->add( $answer_obj );
										continue;
									}

									$answer_id = $answer_obj->id;

									do_action( 'torro_formbuilder_element_answer_save', $form_id, $answer_id );
								}
							}
						}

						if ( isset( $element['settings'] ) ) {
							$settings = $element['settings'];

							foreach ( $settings as $setting ){
								if ( torro_is_temp_id( $setting['id'] ) ) {
									$setting['id'] = '';
								}

								$setting_args = array(
									'name'		=> $setting['name'],
									'value'		=> $setting['value'],
								);

								$setting_obj = null;
								if ( $setting['id'] && torro()->element_settings()->exists( $setting['id'] ) ) {
									$setting_obj = torro()->element_settings()->update( $setting['id'], $setting_args );
								} else {
									$setting_obj = torro()->element_settings()->create( $element_id, $setting_args );
								}
								if ( is_wp_error( $setting_obj ) ) {
									torro()->admin_notices()->add( $setting_obj );
									continue;
								}

								$setting_id = $setting_obj->id;

								do_action( 'torro_formbuilder_element_setting_save', $form_id, $setting_id );
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
					$result = torro()->containers()->delete( $deleted_container_id );
					if ( is_wp_error( $result ) ) {
						torro()->admin_notices()->add( $result );
					}
				}
			}
		}
		if( ! empty( $deleted_element_ids ) ) {
			$deleted_element_ids = explode( ',', $deleted_element_ids );
			if ( 0 < count( $deleted_element_ids ) ) {
				foreach ( $deleted_element_ids as $deleted_element_id ) {
					$result = torro()->elements()->delete( $deleted_element_id );
					if ( is_wp_error( $result ) ) {
						torro()->admin_notices()->add( $result );
					}
				}
			}
		}
		if( ! empty( $deleted_answer_ids ) ) {
			$deleted_answer_ids = explode( ',', $deleted_answer_ids );
			if ( 0 < count( $deleted_answer_ids ) ) {
				foreach ( $deleted_answer_ids as $deleted_answer_id ) {
					$result = torro()->element_answers()->delete( $deleted_answer_id );
					if ( is_wp_error( $result ) ) {
						torro()->admin_notices()->add( $result );
					}
				}
			}
		}

		/**
		 * Saving if results have to be shown after participating
		 */
		update_post_meta( $form_id, 'show_results', $show_results );

		do_action( 'torro_formbuilder_save', $form_id );

		torro()->admin_notices()->store();

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
		if ( 'torro_form' !== get_post_type( $form_id ) ) {
			return;
		}

		torro()->containers()->delete_by_query( array(
			'form_id'	=> $form_id,
			'number'	=> -1,
		) );

		torro()->participants()->delete_by_query( array(
			'form_id'	=> $form_id,
			'number'	=> -1,
		) );

		torro()->results()->delete_by_query( array(
			'form_id'	=> $form_id,
			'number'	=> -1,
		) );
	}

	/**
	 * Adds the message area to the edit post site
	 *
	 * @since 1.0.0
	 */
	public static function jquery_messages_area() {
		if ( ! torro()->is_formbuilder() ) {
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
		if ( ! torro()->is_formbuilder() ) {
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
		if ( ! torro()->is_formbuilder() ) {
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
			'nonce_get_editor_html'        => torro()->ajax()->get_nonce( 'get_editor_html' ),
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
		)  );

		if ( wp_is_mobile() ) {
			wp_enqueue_script( 'jquery-touch-punch' );
		}
	}
}

Torro_Formbuilder::init();
