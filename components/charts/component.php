<?php
/**
 * Awesome Forms Charts Component
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package AwesomeForms/Core
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

if( !defined( 'ABSPATH' ) )
	exit;

class AF_Charts extends AF_Component
{

	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		$this->name = 'questionscharts';
		$this->title = __( 'Charts', 'af-locale' );
		$this->description = __( 'Showing Charts form Forms.', 'af-locale' );
	}

	public function start()
	{
		$folder = QUESTIONS_COMPONENTFOLDER . 'charts/';

		// Loading base functionalities
		include( $folder . 'form-builder-extension.php' );
		include( $folder . 'shortcodes.php' );

		// Charts API
		include( $folder . 'class-chart-creator.php' );
		include( $folder . 'dimple/chart-creator.php' );
	}
}

af_register_component( 'AF_Charts' );