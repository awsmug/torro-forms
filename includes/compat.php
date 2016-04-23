<?php

/**
 * Solving conflicts with other plugins
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
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

class Torro_Compatibility {
	/**
	 * @var The Single instance of the class
	 */
	protected static $_instance = null;

	/**
	 * Construct
	 */
	private function __construct() {
		$this->solve();
	}
	/**
	 * Main Instance
	 */
	public static function instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Solving Conflicts!
	 */
	private function solve() {
		if( ! torro_is_formbuilder() ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'trash_acf_datetimepicker_css' ), 999 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'trash_jquery_ui_css' ), 999 );
	}

	/**
	 * ACF Datetimepicker scripts from AF sites
	 */
	public static function trash_acf_datetimepicker_css() {
		if( torro_is_formbuilder() || torro_is_settingspage() ) {
			// ACF Date and Time Picker Field
			wp_dequeue_style( 'jquery-style' );
			wp_dequeue_style( 'timepicker' );
		}
	}

	/**
	 * Trashing all jQuery UI CSS from other plugins
	 */
	public static function trash_jquery_ui_css(){
		if( torro_is_formbuilder() || torro_is_settingspage() ) {
			wp_dequeue_style( 'jquery-ui-css' );
		}
	}

}

Torro_Compatibility::instance();
