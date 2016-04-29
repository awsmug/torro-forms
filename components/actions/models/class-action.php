<?php
/**
 * Responses abstraction class
 *
 * Motherclass for all Response handlers
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Actions
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

abstract class Torro_Action extends Torro_Base {
	/**
	 * Settings name
	 *
	 * @since 1.0.0
	 */
	protected $settings_name = 'actions';

	/**
	 * Contains the option_content
	 *
	 * @since 1.0.0
	 */
	protected $option_content = null;

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();

		add_action( 'torro_formbuilder_save', array( $this, 'save' ) );
	}

	/**
	 * Handles the data after user submitted the form
	 *
	 * @param int $form_id
	 * @param int $response_id
	 * @param array $response
	 *
	 * @return mixed
	 * since 1.0.0
	 */
	public function handle( $form_id, $response_id, $response ){
		return false;
	}

	/**
	 * Will be displayed on page after submitting data
	 *
	 * @param int $form_id
	 * @param $int $response_id
	 * @param array $response
	 *
	 * @return string $html
	 * since 1.0.0
	 */
	public function notification( $form_id, $response_id, $response ){
		return false;
	}

	/**
	 * Checks if there is an option content
	 */
	public function has_option() {
		$reflector = new ReflectionMethod( $this, 'option_content' ) ;
		return ( $reflector->getDeclaringClass()->getName() !== __CLASS__ );
	}

	/**
	 * Content of option in Form builder
	 *
	 * @param int $form_id
	 * @return string $html
	 *
	 * @since 1.0.0
	 */
	public function option_content( $form_id ) {
		return null;
	}

	/**
	 * Saving data from option_content
	 *
	 * @param $form_id
	 * @since 1.0.0
	 */
	public function save( $form_id ) {}
}
