<?php
/**
 * Components: Torro_Formbuilder_Form_Settings_Extension class
 *
 * @package TorroForms
 * @subpackage Components
 * @version 1.0.0-beta.6
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Torro_Formbuilder_Form_Settings_Extension {
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
			add_meta_box( 'form-settings', __( 'Settings', 'torro-forms' ), array( __CLASS__, 'meta_box_form_settings' ), 'torro_form', 'normal', 'low' );
		}
	}

	/**
	 * Form Restrictions box
	 *
	 * @since 1.0.0
	 */
	public static function meta_box_form_settings() {
		global $post;
		$form_id = $post->ID;

		$form_settings = torro()->form_settings()->get_all_registered();

		if ( ! is_array( $form_settings ) || 0 === count( $form_settings ) ) {
			return;
		}

		$html = '<div id="form-form-settings-tabs" class="section tabs">';

		$html .= '<ul class="settings-tabs">';
		foreach ( $form_settings as $form_setting ){
			if ( ! $form_setting->has_option() ) {
				continue;
			}
			$html .= '<li><a href="#' . $form_setting->name . '">' . $form_setting->title . '</a></option>';
		}
		$html .= '</ul>';

		$html .= '<div class="clear"></div>';

		foreach ( $form_settings as $form_setting ) {
			if ( ! $form_setting->has_option() ){
				continue;
			}
			$html .= '<div id="' . $form_setting->name . '" class="tab-content">' . $form_setting->option_content( $form_id ) . '</div>';
		}

		$html .= '</div>';

		echo $html;
	}
}

Torro_Formbuilder_Form_Settings_Extension::init();
