<?php

/**
 * Solving conflicts with other plugins
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

class AF_Conflicts
{
	/**
	 * @var The Single instance of the class
	 */
	protected static $_instance = NULL;

	/**
	 * Construct
	 */
	private function __construct()
	{
		$this->solve();
	}
	/**
	 * Main Instance
	 */
	public static function instance()
	{
		if( is_null( self::$_instance ) )
		{
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Solving Conflicts!
	 */
	private function solve()
	{
		if( !af_is_formbuilder() )
		{
			return;
		}

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'trash_acf_datetimepicker_css' ), 15 );
	}

	/**
	 * ACF Datetimepicker scripts from AF sites
	 */
	public static function trash_acf_datetimepicker_css()
	{
		// ACF Date and Time Picker Field
		wp_dequeue_style( 'jquery-style' );
		wp_dequeue_style( 'timepicker' );
	}

}
AF_Conflicts::instance();