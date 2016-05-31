<?php
/**
 * Includes: Torro_Compatibility class
 *
 * @package TorroForms
 * @subpackage Includes
 * @version 1.0.0-beta.3
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Torro_Compatibility {
	/**
	 * @var The Single instance of the class
	 */
	protected static $_instance = null;

	/**
	 * Construct
	 */
	private function __construct() {
		$this->solve();
	}
	/**
	 * Main Instance
	 */
	public static function instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Solving Conflicts!
	 */
	private function solve() {
		if( ! torro()->is_formbuilder() ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'trash_acf_datetimepicker_css' ), 999 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'trash_jquery_ui_css' ), 999 );
	}

	/**
	 * ACF Datetimepicker scripts from AF sites
	 */
	public static function trash_acf_datetimepicker_css() {
		if( torro()->is_formbuilder() || torro()->is_settingspage() ) {
			// ACF Date and Time Picker Field
			wp_dequeue_style( 'jquery-style' );
			wp_dequeue_style( 'timepicker' );
		}
	}

	/**
	 * Trashing all jQuery UI CSS from other plugins
	 */
	public static function trash_jquery_ui_css(){
		if( torro()->is_formbuilder() || torro()->is_settingspage() ) {
			wp_dequeue_style( 'jquery-ui-css' );
		}
	}

}

Torro_Compatibility::instance();
