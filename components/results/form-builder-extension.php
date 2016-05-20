<?php
/**
 * Components: Torro_Formbuilder_Charts_Extension class
 *
 * @package TorroForms
 * @subpackage Components
 * @version 1.0.0-beta.2
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Torro_Formbuilder_Charts_Extension {
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

		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ), 15 );
		add_action( 'torro_formbuilder_save', array( __CLASS__, 'save' ), 10, 1 );
		// add_action( 'admin_print_styles', array( __CLASS__, 'register_admin_styles' ) );
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
			add_meta_box( 'form-results', __( 'Results', 'torro-forms' ), array( __CLASS__, 'meta_box_results' ), 'torro_form', 'normal', 'high' );
		}
	}

	/**
	 * Form Restrictions box
	 *
	 * @since 1.0.0
	 */
	public static function meta_box_results() {
		global $post;
		$form_id = $post->ID;

		$result_handlers = torro()->resulthandlers()->get_all_registered();

		if ( ! is_array( $result_handlers ) || 0 === count( $result_handlers ) ){
			return;
		}

		$html = '<div id="form-result-handlers-tabs" class="section tabs">';

		$html .= '<ul class="results-tabs">';
		foreach ( $result_handlers as $result_handler ){
			$html .= '<li><a href="#' . $result_handler->name . '">' . $result_handler->title . '</a></option>';
		}
		$html .= '</ul>';

		$html .= '<div class="clear"></div>';

		foreach ( $result_handlers as $result_handler ) {
			$html .= '<div id="' . $result_handler->name . '" class="tab-content">' . $result_handler->option_content( $form_id ) . '</div>';
		}

		$html .= '</div>';

		$html .= '<div class="section general-settings">';

		$delete_results_disabled = ' disabled"';

		$results_count = torro()->results()->query( array(
			'number'	=> -1,
			'count'		=> true,
			'form_id'	=> $form_id,
		) );

		if ( 0 < $results_count ) {
			$delete_results_disabled = '';
		}

		$html .= '<a id="form-delete-results" class="delete-button' . $delete_results_disabled . '">' . esc_html__( 'Delete Results', 'torro-forms' ) . '</a>';

		ob_start();
		do_action( 'torro_results_general_settings' );
		$html .= ob_get_clean();

		$html .= '</div>';

		echo $html;
	}

	/**
	 * Saving access-control options
	 *
	 * @param int $form_id
	 *
	 * @since 1.0.0
	 */
	public static function save( $form_id ) {
		$access_controls_option = wp_unslash( $_POST['form_access_controls_option'] );
		update_post_meta( $form_id, 'access_controls_option', $access_controls_option );
	}

	/**
	 * Registers and enqueues admin-specific styles.
	 *
	 * @since 1.0.0
	 */
	public static function register_admin_styles() {
		if ( ! torro()->is_formbuilder() ) {
			return;
		}

		wp_enqueue_style( 'torro-results', torro()->get_asset_url( 'results', 'css' ) );
	}
}

Torro_Formbuilder_Charts_Extension::init();
