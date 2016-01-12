<?php
/**
 * Torro Forms Main Component Class
 *
 * This class is the base for every Torro Forms Component.
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

abstract class Torro_Component extends Torro_Instance {
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
	}

	/**
	 * Checking and starting
	 *
	 * @since 1.0.0
	 */
	public function check_and_start() {
		$values = torro_get_settings( 'general' );

		if ( isset( $values['modules'] ) && is_array( $values['modules'] ) && ! in_array( $this->name, $values['modules'] ) ) {
			return;
		}
		//TODO: check requirements in manager
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
	protected function base_init() {
		if ( method_exists( $this, 'includes' ) ) {
			$this->includes();
		}
	}

	/**
	 * Adds a notice to
	 *
	 * @param        $message
	 * @param string $type
	 */
	protected function admin_notice( $message, $type = 'updated' ) {
		if ( WP_DEBUG ) {
			$message = $message . ' (in Module "' .  $this->name . '")';
		}
		Torro_Init::admin_notice( $message, $type );
	}
}
