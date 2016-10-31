<?php
/**
 * Core: Torro_Component class
 *
 * @package TorroForms
 * @subpackage CoreModels
 * @version 1.0.0-beta.7
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms Main Component Class
 *
 * This class is the base for every Torro Forms Component.
 *
 * @since 1.0.0-beta.1
 */
abstract class Torro_Component extends Torro_Base {
	/**
	 * Settings
	 *
	 * @since 1.0.0
	 */
	protected $settings;

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();

		$this->check_and_start();
	}

	/**
	 * Checking and starting
	 *
	 * @since 1.0.0
	 */
	private function check_and_start() {
		$values = torro_get_settings( 'general' );

		if ( isset( $values['modules'] ) && is_array( $values['modules'] ) && ! in_array( $this->name, $values['modules'] ) ) {
			return;
		}

		if ( true === $this->check_requirements() ) {
			$this->base_init();
			$this->settings = torro_get_settings( $this->name );
		}
	}

	/**
	 * Function for Checks
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	protected function check_requirements() {
		return true;
	}

	/**
	 * Running Scripts if functions are existing in child Class
	 *
	 * @since 1.0.0
	 */
	private function base_init() {
		$this->includes();
	}

	/**
	 * Optionally add includes.
	 *
	 * Override in child class if needed.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function includes() {
		// Override in child class to add includes.
	}
}
