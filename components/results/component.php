<?php
/**
 * Components: Torro_Results_Component class
 *
 * @package TorroForms
 * @subpackage Components
 * @version 1.0.0-beta.5
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Torro_Results_Component extends Torro_Component {
	/**
	 * Instance
	 *
	 * @var null|Torro_Form_Actions_Component
	 * @since 1.0.0
	 */
	private static $instance = null;

	/**
	 * Singleton
	 *
	 * @return null|Torro_Results_Component
	 * @since 1.0.0
	 */
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

	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->name = 'results';
		$this->title = __( 'Results', 'torro-forms' );
		$this->description = __( 'Handling Results', 'torro-forms' );
	}

	/**
	 * Including files of component
	 *
	 * @since 1.0.0
	 */
	protected function includes() {
		$folder = torro()->get_path( 'components/results/' );

		// Models
		require_once( $folder . 'models/class-form-result.php' );
		require_once( $folder . 'models/class-charts.php' );

		// Loading base functionalities
		require_once( $folder . 'settings.php' );
		require_once( $folder . 'form-builder-extension.php' );
		require_once( $folder . 'shortcodes.php' );

		// Data handling
		require_once( $folder . 'export.php' );

		// Results base Class

		// Base Result Handlers
		require_once( $folder . 'base-result-handlers/entries.php' );
		require_once( $folder . 'base-result-handlers/charts-c3.php' );
	}
}

torro()->components()->register( 'Torro_Results_Component' );
