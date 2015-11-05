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

class AF_Results_Component extends AF_Component
{

	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	public function init()
	{
		$this->name = 'results';
		$this->title = __( 'Results', 'af-locale' );
		$this->description = __( 'Handling Results.', 'af-locale' );
	}

	public function includes()
	{
		$folder = AF_COMPONENTFOLDER . 'results/';

		// Loading abstract Classes
		require_once( $folder . 'abstract/class-result-handler.php' );
		require_once( $folder . 'abstract/class-chart-creator.php' );

		// Loading base functionalities
		require_once( $folder . 'settings.php' );
		require_once( $folder . 'form-builder-extension.php' );
		require_once( $folder . 'shortcodes.php' );

		// Data handling
		require_once( $folder . 'export.php' );

		// Results base Class
		require_once( $folder . 'class-form-results.php' );

		// Base Result Handlers
		require_once( $folder . 'base-result-handlers/entries.php' );
		require_once( $folder . 'base-result-handlers/charts.php' );

		// Charts API
		require_once( $folder . 'base-result-handlers/charts-dimple.php' );
	}
}

af_register_component( 'AF_Results_Component' );