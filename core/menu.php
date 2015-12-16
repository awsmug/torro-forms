<?php
/**
 * Menus of WP Admin
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

if( !defined( 'ABSPATH' ) )
{
	exit;
}

class Torro_AdminMenu
{

	var $notices = array();

	/**
	 * Init in WordPress, run on constructor
	 *
	 * @return null
	 * @since 1.0.0
	 */
	public static function init()
	{
		if( !is_admin() )
		{
			return;
		}

		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'parent_file', array( __CLASS__, 'tax_menu_correction' ) );
	}

	/**
	 * Adds the Admin menu.
	 *
	 * @since 1.0.0
	 */
	public static function admin_menu()
	{
		add_menu_page( esc_attr__( 'Forms', 'torro-forms' ), esc_attr__( 'Forms', 'torro-forms' ), 'edit_posts', 'Torro_Admin', array( 'Torro_SettingsPage', 'show' ), '', 50 );
		add_submenu_page( 'Torro_Admin', esc_attr__( 'Create', 'torro-forms' ), esc_attr__( 'Create', 'torro-forms' ), 'edit_posts', 'post-new.php?post_type=torro-forms' );
		add_submenu_page( 'Torro_Admin', esc_attr__( 'Categories', 'torro-forms' ), esc_attr__( 'Categories', 'torro-forms' ), 'edit_posts', 'edit-tags.php?taxonomy=torro-forms' );
		add_submenu_page( 'Torro_Admin', esc_attr__( 'Settings', 'torro-forms' ), esc_attr__( 'Settings', 'torro-forms' ), 'edit_posts', 'Torro_Admin', array( 'Torro_SettingsPage', 'show' ) );
	}

	/**
	 * Fix for getting correct menu and display
	 *
	 * @since 1.0.0
	 */
	public static function tax_menu_correction( $parent_file )
	{
		global $current_screen;
		$taxonomy = $current_screen->taxonomy;

		if( $taxonomy == 'torro-forms-categories' )
		{
			$parent_file = 'Torro_Admin';
		}

		return $parent_file;
	}
}

Torro_AdminMenu::init();