<?php
/**
 * Torro Forms base class.
 *
 * This class is the base for every component-like class in the plugin.
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
 * @version 2015-04-16
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

abstract class Torro_Base {
	/**
	 * name of restriction
	 *
	 * @since 1.0.0
	 */
	protected $name;

	/**
	 * Title of restriction
	 *
	 * @since 1.0.0
	 */
	protected $title;

	/**
	 * Description of restriction
	 *
	 * @since 1.0.0
	 */
	protected $description;

	/**
	 * Settings name
	 *
	 * @since 1.0.0
	 */
	protected $settings_name = null;

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
	 * Whether this base has been initialized
	 *
	 * @since 1.0.0
	 */
	protected $initialized = false;

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		$this->init();
		$this->init_settings();
	}

	public function __set( $key, $value ) {
		switch ( $key ) {
			case 'name':
			case 'title':
			case 'description':
				if ( ! $this->initialized ) {
					$this->$key = $value;
				}
				break;
			case 'initialized':
				if ( ! $this->initialized ) {
					$this->initialized = $value;
				}
				break;
			default:
				if ( property_exists( $this, $key ) ) {
					$this->$key = $value;
				}
		}
	}

	public function __get( $key ) {
		if ( property_exists( $this, $key ) ) {
			return $this->$key;
		}
		return null;
	}

	public function __isset( $key ) {
		if ( property_exists( $this, $key ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Base Element Function
	 * @return mixed
	 */
	protected abstract function init();

	/**
	 * Add Settings to Settings Page
	 */
	private function init_settings() {
		if( null === $this->settings_name ){
			return false;
		}

		if ( 0 === count( $this->settings_fields ) || empty( $this->settings_fields ) ) {
			return false;
		}

		$headline = array(
			'headline' => array(
				'title'       => $this->title,
				'description' => sprintf( __( 'Setup "%s".', 'torro-forms' ), $this->title ),
				'type'        => 'title'
			)
		);

		$settings_fields = array_merge( $headline, $this->settings_fields );

		// Todo: Should not use API
		torro()->settings()->get_registered( $this->settings_name )->add_subsettings_field_arr( $this->name, $this->title, $settings_fields );

		$settings_name = 'extensions_' . $this->name;

		$settings_handler = new Torro_Settings_Handler( $settings_name, $this->settings_fields );
		$this->settings   = $settings_handler->get_field_values();
	}
}
