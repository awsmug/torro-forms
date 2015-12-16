<?php
/**
 * Torro Forms Responses Component
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Restrictions
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

if( !defined( 'ABSPATH' ) )
{
	exit;
}

class Torro_Actions_Component extends Torro_Component
{

	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	public function init()
	{
		$this->name = 'actions';
		$this->title = esc_attr__( 'Actions', 'af-locale' );
		$this->description = esc_attr__( 'Actions are fired in the moment Users submitting their Form Data.', 'af-locale' );
	}

	/**
	 * Including files of component
	 */
	public function includes()
	{
		$folder = TORRO_COMPONENTFOLDER . 'actions/';

		// Loading base functionalities
		require_once( $folder . 'settings.php' );
		require_once( $folder . 'form-builder-extension.php' );
		require_once( $folder . 'form-process-extension.php' );

		// Response Handlers API
		require_once( $folder . 'abstract/class-action.php' );
		require_once( $folder . 'base-actions/email-notifications.php' );
	}
}

torro_register_component( 'Torro_Actions_Component' );
