<?php
/**
 * Torro Forms basic Instance class.
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

abstract class Torro_Instance {
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
	 * Is Element initialized
	 *
	 * @var bool
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
	}

	public function __set( $key, $value ) {
		switch ( $key ) {
			case 'initialized':
				if ( $value ) {
					$this->initialized = true;
				}
				break;
			case 'name':
			case 'title':
			case 'description':
				if ( ! $this->initialized ) {
					$this->$key = $value;
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
	 * Function for enqueuing Admin Scripts - Have to be overwritten by Child Class.
	 */
	public function admin_scripts() {}

	/**
	 * Function for enqueuing Admin Styles - Have to be overwritten by Child Class.
	 */
	public function admin_styles() {}

	/**
	 * Function for enqueuing Frontend Scripts - Have to be overwritten by Child Class.
	 */
	public function frontend_scripts() {}

	/**
	 * Function for enqueuing Frontend Styles - Have to be overwritten by Child Class.
	 */
	public function frontend_styles() {}
}
