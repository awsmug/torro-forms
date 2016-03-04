<?php
/**
 * Result Handler abstraction class
 *
 * Motherclass for all Result Handlers
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Restrictions
 * @version 1.0.0alpha1
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

abstract class Torro_Result_Handler extends Torro_Base {
	/**
	 * Settings name
	 *
	 * @since 1.0.0
	 */
	protected $settings_name = 'resulthandling';

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
}
