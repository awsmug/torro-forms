<?php
/**
 * Core: Torro_Components_Manager class
 *
 * @package TorroForms
 * @subpackage CoreManagers
 * @version 1.0.0-beta.7
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms component manager class
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Components_Manager extends Torro_Manager {
	/**
	 * Instance
	 *
	 * @var null|Torro_Components_Manager
	 * @since 1.0.0
	 */
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	protected function allowed_modules() {
		$allowed = array(
			'components' => 'Torro_Component'
		);
		return $allowed;
	}

	protected function get_category() {
		return 'components';
	}
}
