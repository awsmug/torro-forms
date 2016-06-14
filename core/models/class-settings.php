<?php
/**
 * Core: Torro_Settings class
 *
 * @package TorroForms
 * @subpackage CoreModels
 * @version 1.0.0-beta.5
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms Settings Class
 *
 * @since 1.0.0-beta.1
 */
abstract class Torro_Settings extends Torro_Base {
	/**
	 * Settings
	 *
	 * @since 1.0.0
	 */
	protected $settings = array();

	/**
	 * Sub Settings
	 *
	 * @since 1.0.0
	 */
	protected $sub_settings = array();

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Adding settings field by array
	 *
	 * @param array $settings_fields
	 */
	public function add_settings_field_arr( $settings_field_array ) {
		$this->settings = array_merge( $this->settings, $settings_field_array );
	}

	/**
	 * Adding settings field by array
	 *
	 * @param array $settings_fields
	 */
	public function add_subsettings_field_arr( $setting_name, $setting_title, $settings_fields ) {
		$this->sub_settings[ $setting_name ] = array(
			'title'		=> $setting_title,
			'settings'	=> $settings_fields,
		);
	}
}

/**
 * @param $settings_name
 */
function torro_get_settings( $settings_name ) {
	$settings = torro()->settings()->get_registered( $settings_name );
	if ( is_wp_error( $settings ) ) {
		return false;
	}

	$settings_handler = new Torro_Settings_Handler( $settings_name, $settings->settings );
	$values = $settings_handler->get_field_values();

	return $values;
}
