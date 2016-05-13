<?php
/**
 * Components: Torro_Formbuilder_Actions_Extension class
 *
 * @package TorroForms
 * @subpackage Components
 * @version 1.0.0-beta.1
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Torro_Formbuilder_Actions_Extension {
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

		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ) );
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

		if ( in_array( $post_type, $post_types ) ) {
			add_meta_box( 'form-actions', __( 'Actions', 'torro-forms' ), array( __CLASS__, 'meta_box_actions' ), 'torro_form', 'normal', 'high' );
		}
	}

	/**
	 * Response Handlers box
	 *
	 * @since 1.0.0
	 */
	public static function meta_box_actions() {
		global $post;
		$form_id = $post->ID;

		$actions = torro()->actions()->get_all_registered();

		if ( ! is_array( $actions ) || 0 === count( $actions ) ){
			return;
		}

		$html = '<div id="actions" class="tabs">';

		$html .= '<ul id="action-tabs">';
		foreach ( $actions as $action ) {
			if ( !$action->has_option() ) {
				continue;
			}
			$html .= '<li class="tab"><a href="#' . $action->name . '">' . $action->title . '</a></option>';
		}
		$html .= '</ul>';

		foreach( $actions as $action ) {
			if ( ! $action->has_option() ) {
				continue;
			}
			$html .= '<div id="' . $action->name . '" class="tab-content action">' . $action->option_content( $form_id ) . '</div>';
		}

		$html .= '</div>';

		echo $html;
	}
}

Torro_Formbuilder_Actions_Extension::init();
