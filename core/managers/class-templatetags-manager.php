<?php
/**
 * Core: Torro_TemplateTags_Manager class
 *
 * @package TorroForms
 * @subpackage CoreManagers
 * @version 1.0.0-beta.5
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms template tags manager class
 *
 * @since 1.0.0-beta.1
 */
final class Torro_TemplateTags_Manager extends Torro_Manager {

	/**
	 * Instance
	 *
	 * @var null|Torro_TemplateTags_Manager
	 * @since 1.0.0
	 */
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	protected function __construct() {
		parent::__construct();
	}

	protected function allowed_modules(){
		$allowed = array(
			'templatetags' => 'Torro_Templatetags'
		);
		return $allowed;
	}

	protected function after_instance_added( $instance ) {
		$instance->tags();
		return $instance;
	}

	protected function get_category() {
		return 'templatetags';
	}
}
