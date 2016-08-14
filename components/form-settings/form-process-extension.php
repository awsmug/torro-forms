<?php
/**
 * Components: Torro_Form_Settings_FormProcess_Extension class
 *
 * @package TorroForms
 * @subpackage Components
 * @version 1.0.0-beta.7
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Torro_Form_Settings_FormProcessExtension {

	/**
	 * Init in WordPress, run on constructor
	 *
	 * @return null
	 * @since 1.0.0
	 */
	public static function init() {
		add_filter( 'torro_form_show', array( __CLASS__, 'check' ) );
	}

	/**
	 * Checking access-controls
	 */
	public static function check( $form_show ) {
		$form_id = torro()->forms()->get_current_form_id();
		$access_controls = torro()->access_controls()->get_all_registered();

		if ( 0 === count( $access_controls ) ) {
			return $form_show;
		}

		if ( false === apply_filters( 'torro_additional_access_controls_check_start', true ) ) {
			return false;
		}

		/**
		 * Select field for Restriction
		 */
		$access_controls_option = get_post_meta( $form_id, 'access_controls_option', true );

		if ( ! empty( $access_controls_option ) && array_key_exists( $access_controls_option, $access_controls ) ) {
			$access_control = $access_controls[ $access_controls_option ];

			if ( false === $access_control->check( $form_id ) ) {
				return $access_control->messages();
			}
		}

		return apply_filters( 'torro_additional_access_controls_check_end', $form_show );
	}
}
Torro_Form_Settings_FormProcessExtension::init();
