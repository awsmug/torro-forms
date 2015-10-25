<?php
/**
 * Awesome Forms Results Component
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
{
	exit;
}

class AF_ResultsComponent extends AF_Component
{

	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		$this->name = 'results';
		$this->title = __( 'Results', 'af-locale' );
		$this->description = __( 'Handling Results.', 'af-locale' );
	}

	public function start()
	{
		$folder = AF_COMPONENTFOLDER . 'results/';

		// Loading base functionalities
		include( $folder . 'form-builder-extension.php' );
		include( $folder . 'shortcodes.php' );

		// Data handling
		include( $folder . 'export.php' );

		// Results base Class
		include( $folder . 'class-form-results.php' );

		// Charts API
		include( $folder . 'abstract/class-chart-creator.php' );
		include( $folder . 'dimple/chart-creator.php' );
	}
}

af_register_component( 'AF_ResultsComponent' );