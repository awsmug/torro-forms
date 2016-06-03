<?php
/**
 * Components: Torro_Form_Actions_FormProcessExtension class
 *
 * @package TorroForms
 * @subpackage Components
 * @version 1.0.0-beta.4
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Torro_Form_Actions_FormProcessExtension {
	/**
	 * Init in WordPress, run on constructor
	 *
	 * @return null
	 * @since 1.0.0
	 */
	public static function init() {
		add_action( 'torro_response_saved', array( __CLASS__, 'action' ), 10, 3 );
		add_filter( 'torro_response_saved_content', array( __CLASS__, 'notification' ), 10, 4 );
	}

	/**
	 * Handling all Actions
	 *
	 * @since 1.0.0
	 */
	public static function action( $form_id, $response_id, $response ) {
		$actions = torro()->actions()->get_all_registered();

		if ( 0 === count( $actions ) ) {
			return;
		}

		foreach ( $actions as $action ) {
			$action->handle( $form_id, $response_id, $response );
		}
	}

	/**
	 * Show notifcations for users
	 *
	 * @param int $form_id
	 * @param int $response_id
	 * @param array $response
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	public static function notification( $notification, $form_id, $response_id, $response ){
		$actions = torro()->actions()->get_all_registered();

		if ( 0 === count( $actions ) ) {
			return;
		}

		$html = '';

		foreach ( $actions as $action ) {
			if( false !== $action->notification( $form_id, $response_id, $response ) ) {
				$html .= $action->notification( $form_id, $response_id, $response );
			}
		}

		if( ! empty( $html ) ) {
			$notification = $html;
		}

		return $notification;
	}
}

Torro_Form_Actions_FormProcessExtension::init();
