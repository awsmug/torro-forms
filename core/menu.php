<?php
/**
 * Core: Torro_AdminMenu class
 *
 * @package TorroForms
 * @subpackage Core
 * @version 1.0.0beta1
 * @since 1.0.0beta1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms admin menu class
 *
 * Adds items to the admin menu.
 *
 * @since 1.0.0beta1
 */
class Torro_AdminMenu {
	var $notices = array();

	/**
	 * Init in WordPress, run on constructor
	 *
	 * @return null
	 * @since 1.0.0
	 */
	public static function init() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
	}

	/**
	 * Adds the Admin menu.
	 *
	 * @since 1.0.0
	 */
	public static function admin_menu() {
		add_submenu_page( 'edit.php?post_type=torro_form', __( 'Settings', 'torro-forms' ), __( 'Settings', 'torro-forms' ), 'edit_posts', 'Torro_Admin', array( 'Torro_Settings_Page', 'show' ) );
	}
}

Torro_AdminMenu::init();
