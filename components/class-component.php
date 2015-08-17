<?php
/**
 * Questions main component class
 *
 * This class is the base for every Questions Component.
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package Questions/Core
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

if( !defined( 'ABSPATH' ) ){
	exit;
}

abstract class Questions_Component
{

	/**
	 * Title
	 *
	 * @since 1.0.0
	 */
	var $title;

	/**
	 * Name
	 *
	 * @since 1.0.0
	 */
	var $name;

	/**
	 * Description
	 *
	 * @since 1.0.0
	 */
	var $description;

	var $capability;

	var $required;

	/**
	 * Initialiing the Component.
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		$this->name = get_class( $this );
		add_action( 'plugins_loaded', array(
			$this,
			'includes' ), 0 );

		$this->title = ucfirst( $this->name );
		$this->description = esc_attr__( 'This is a Questions component.', 'questions-locale' );
		$this->capability = 'read';
		$this->required = TRUE;
	} // end constructor
}
