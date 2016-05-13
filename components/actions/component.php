<?php
/**
 * Components: Torro_Form_Actions_Component class
 *
 * @package TorroForms
 * @subpackage Components
 * @version 1.0.0-beta.1
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Torro_Form_Actions_Component extends Torro_Component {
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
	 * @return null|Torro_Form_Actions_Component
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->name = 'actions';
		$this->title = __( 'Actions', 'torro-forms' );
		$this->description = __( 'Actions are executed in the moment users submitting their form data.', 'torro-forms' );
	}

	/**
	 * Including files of component
	 *
	 * @since 1.0.0
	 */
	protected function includes() {
		$folder = torro()->get_path( 'components/actions/' );

		// Loading base functionalities
		require_once( $folder . 'settings.php' );
		require_once( $folder . 'form-builder-extension.php' );
		require_once( $folder . 'form-process-extension.php' );

		// Response Handlers API
		require_once( $folder . 'models/class-form-action.php' );
		require_once( $folder . 'base-actions/redirection.php' );
		require_once( $folder . 'base-actions/email-notifications.php' );
	}
}

torro()->components()->register( 'Torro_Form_Actions_Component' );
