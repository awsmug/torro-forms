<?php
/**
 * Awesome Forms Restrictions
 *
 * Component for the Restrictions API
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package AwesomeForms/Restrictions
 * @version 2015-04-16
 * @since   1.0.0
 * @license GPL 2
 *
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

if( !defined( 'ABSPATH' ) ){
	exit;
}

class AF_RestrictionsComponent extends AF_Component
{

	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		$this->name = 'restrictions';
		$this->title = esc_attr__( 'Restrictions', 'af-locale' );
		$this->description = esc_attr__( 'Restrictions if a user can fillout a form or not', 'af-locale' );
	}

	/**
	 * Including files of component
	 */
	public function start()
	{
		$folder = AF_COMPONENTFOLDER . 'restrictions/';

		// Loading base functionalities
		include_once( $folder . 'settings.php' );
		include_once( $folder . 'form-builder-extension.php' );
		include_once( $folder . 'form-process-extension.php' );

		// Restrictions API
		include_once( $folder . 'class-restrictions.php' );
		include_once( $folder . 'base-restrictions/all-visitors.php' );
		include_once( $folder . 'base-restrictions/all-members.php' );
		include_once( $folder . 'base-restrictions/selected-members.php' );
		include_once( $folder . 'base-restrictions/timerange.php' );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue Scripts
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script( 'af-restrictions', AF_URLPATH . 'components/restrictions/includes/js/restrictions.js' );
	}

}

af_register_component( 'AF_RestrictionsComponent' );