<?php
/**
 * Result Handler abstraction class
 *
 * Motherclass for all Result Handlers
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Restrictions
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

abstract class Torro_ResultHandler extends Torro_Instance {
	/**
	 * Settings fields
	 *
	 * @since 1.0.0
	 */
	protected $settings_fields = array();

	/**
	 * Settings
	 *
	 * @since 1.0.0
	 */
	protected $settings = array();

	/**
	 * Contains the option_content
	 *
	 * @since 1.0.0
	 */
	protected $option_content = '';

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Content of option in Form builder
	 *
	 * @since 1.0.0
	 */
	abstract function option_content();

	/**
	 * Checks if there is an option content
	 *
	 * @since 1.0.0
	 */
	public function has_option() {
		if ( ! empty( $this->option_content ) ) {
			return $this->option_content;
		}

		$this->option_content = $this->option_content();

		if ( FALSE === $this->option_content ) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Add Settings to Settings Page
	 */
	public function init_settings() {
		if ( 0 === count( $this->settings_fields ) || empty( $this->settings_fields ) ) {
			return FALSE;
		}

		$headline = array(
			'headline'		=> array(
				'title'			=> $this->title,
				'description'	=> sprintf( __( 'Setup the "%s" Result Handler.', 'torro-forms' ), $this->title ),
				'type'			=> 'title'
			)
		);

		$settings_fields = array_merge( $headline, $this->settings_fields );

		torro()->settings()->get( 'resulthandling' )->add_subsettings_field_arr( $this->name, $this->title, $settings_fields );

		$settings_name = 'resulthandling_' . $this->name;

		$settings_handler = new Torro_Settings_Handler( $settings_name, $this->settings_fields );
		$this->settings = $settings_handler->get_field_values();
	}
}
