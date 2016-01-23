<?php
/**
 * Torro Forms Settings Class
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core/Settings
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 awesome.ug (support@awesome.ug)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Torro_Settings extends Torro_Instance {
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
