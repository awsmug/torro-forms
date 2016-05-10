<?php
/**
 * Components: Torro_Form_Actions_Settings class
 *
 * @package TorroForms
 * @subpackage Components
 * @version 1.0.0-beta.1
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Torro_Form_Actions_Settings extends Torro_Settings {
	/**
	 * Instance
	 *
	 * @var null|Torro_Form_Actions_Settings
	 * @since 1.0.0
	 */
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();
	}

	public function init() {
		$this->title = __( 'Actions', 'torro-forms' );
		$this->name = 'actions';
	}
}

torro()->settings()->register( 'Torro_Form_Actions_Settings' );
