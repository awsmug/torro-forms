<?php
/**
 * Core: Torro_Element_Types_Manager class
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
 * Torro Forms element types manager class
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Element_Types_Manager extends Torro_Manager {

	/**
	 * Instance
	 *
	 * @var null|Torro_Element_Types_Manager
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

	public function get_class_name_by_type( $type ) {
		$element_types = $this->get_all_registered();

		$class_name = 'Torro_Element_' . ucfirst( $type );
		if ( isset( $element_types[ $type ] ) ) {
			$class_name = get_class( $element_types[ $type ] );
		}

		return apply_filters( 'torro_element_type_class_name', $class_name, $type );
	}

	protected function allowed_modules(){
		$allowed = array(
			'element_types' => 'Torro_Element_Type'
		);
		return $allowed;
	}

	protected function get_category() {
		return 'element_types';
	}
}
