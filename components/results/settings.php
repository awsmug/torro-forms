<?php
/**
 * Components: Torro_Results_Settings class
 *
 * @package TorroForms
 * @subpackage Components
 * @version 1.0.0-beta.2
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Torro_Results_Settings extends Torro_Settings {
	/**
	 * Instance
	 *
	 * @var null|Torro_Results_Settings
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
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Initializing
	 *
	 * @since 1.0.0
	 */
	public function init() {
		$this->title = __( 'Result Handling', 'torro-forms' );
		$this->name = 'resulthandling';
	}
}

torro()->settings()->register( 'Torro_Results_Settings' );
