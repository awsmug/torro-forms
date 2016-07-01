<?php
/**
 * Components: Torro_Form_Settings_Component class
 *
 * @package TorroForms
 * @subpackage Components
 * @version 1.0.0-beta.6
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Torro_Form_Settings_Component extends Torro_Component {
	/**
	 * Instance
	 *
	 * @var null|Torro_Form_Settings_Component
	 * @since 1.0.0
	 */
	private static $instance = null;

	/**
	 * Singleton
	 *
	 * @return null|Torro_Form_Settings_Component
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
	 * Initializes the Component
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->name = 'form-settings';
		$this->title = __( 'Form Settings', 'torro-forms' );
		$this->description = __( 'Form Settings Component', 'torro-forms' );
	}

	/**
	 * Including files of component
	 *
	 * @since 1.0.0
	 */
	protected function includes() {
		$folder = torro()->get_path( 'components/form-settings/' );

		// Loading base functionalities
		require_once( $folder . 'settings.php' );
		require_once( $folder . 'form-builder-extension.php' );
		require_once( $folder . 'form-process-extension.php' );

		// Models
		require_once( $folder . 'models/class-form-setting.php' );
		require_once( $folder . 'models/class-form-access-control.php' );

		// Settings
		require_once( $folder . 'base-form-settings/general.php' );
		require_once( $folder . 'base-form-settings/access-control.php' );
		require_once( $folder . 'base-form-settings/timerange.php' );
		require_once( $folder . 'base-form-settings/recaptcha.php' );

		// Visitors
		require_once( $folder . 'base-form-settings/access-controls/all-visitors.php' );
		require_once( $folder . 'base-form-settings/access-controls/all-members.php' );
		require_once( $folder . 'base-form-settings/access-controls/selected-members.php' );


	}
}

torro()->components()->register( 'Torro_Form_Settings_Component' );
