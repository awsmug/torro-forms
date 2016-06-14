<?php
/**
 * Core: Torro_Extensions_Settings class
 *
 * @package TorroForms
 * @subpackage CoreSettings
 * @version 1.0.0-beta.5
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms extensions settings class
 *
 * Handles settings for extensions.
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Extensions_Settings extends Torro_Settings {
	/**
	 * Instance
	 *
	 * @var null|Torro_Extensions_Settings
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
		$this->title = __( 'Extensions', 'torro-forms-conditional-logic' );
		$this->name = 'extensions';
	}
}

torro()->settings()->register( 'Torro_Extensions_Settings' );
