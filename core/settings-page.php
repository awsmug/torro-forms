<?php

/**
 * Torro Forms settings page
 *
 * This class shows and saves the settings page
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core/Settings
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Torro_SettingsPage {

	/**
	 * The current tab
	 *
	 * @var
	 */
	static $current_tab;

	/**
	 * The current section
	 *
	 * @var
	 */
	static $current_section;

	/**
	 * Init in WordPress, run on constructor
	 *
	 * @return null
	 * @since 1.0.0
	 */
	public static function init() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'init', array( __CLASS__, 'save' ), 20 );
		add_action( 'admin_print_styles', array( __CLASS__, 'register_styles' ) );
	}

	/**
	 * Show admin Settings
	 */
	public static function show() {
		self::init_tabs();

		$html = '<div class="wrap af">';
		$html .= '<form name="torro_settings" id="torro-settings" method="POST">';
		$html .= '<input type="hidden" id="torro_save_settings" name="torro_save_settings" value="' . wp_create_nonce( '_torro_save_settings_nonce' ) . '" />';

		$all_settings = torro()->settings()->get_all();
		if ( 0 < count( $all_settings ) ) {
			/**
			 * Tabs
			 */
			$html .= '<h2 class="nav-tab-wrapper">';
			foreach ( $all_settings AS $setting ) {
				// Discard Settings if there are no settings
				if ( 0 === count( $setting->settings ) && 0 === count( $setting->sub_settings ) ) {
					continue;
				}

				$css_classes = '';
				if ( $setting->name === self::$current_tab ) {
					$css_classes = ' nav-tab-active';
				}

				$html .= '<a href="' . admin_url( 'edit.php?post_type=torro-forms&page=Torro_Admin&tab=' . $setting->name ) . '" class="nav-tab' . $css_classes . '">' . $setting->title . '</a>';
			}
			$html .= '</h2>';

			/**
			 * Content
			 */
			$html .= '<div id="torro-settings-content" class="' . self::$current_tab . '">';

			$settings = $all_settings[ self::$current_tab ];
			$html .= $settings->show( self::$current_section );

			ob_start();
			do_action( 'torro_settings_' . self::$current_tab );
			$html .= ob_get_clean();

			$html .= '</div>';

			$html .= '<input name="torro_save_settings" type="submit" class="button-primary button-save-settings" value="' . esc_attr__( 'Save Settings', 'torro-forms' ) . '" />';
		} else {
			$html .= '<p>' . esc_html__( 'There are no settings available.', 'torro-forms' ) . '</p>';
		}

		$html .= '</form>';

		$html .= '</div>';
		$html .= '<div class="clear"></div>';

		echo $html;
	}

	/**
	 * Initializing Tabs
	 */
	public static function init_tabs() {
		if ( isset( $_GET['tab'] ) ) {
			self::$current_tab = $_GET['tab'];
		} else {
			self::$current_tab = 'general';
		}

		if ( isset( $_GET['section'] ) ) {
			self::$current_section = $_GET['section'];
		}
	}

	/**
	 * Saving settings
	 */
	public static function save() {
		if ( ! isset( $_POST['torro_save_settings'] ) ) {
			return;
		}

		$section = '';
		if ( isset( $_GET['section'] ) ) {
			$section = $_GET['section'];
		}

		do_action( 'torro_save_settings', $section );
	}

	/**
	 * Registers and enqueues admin-specific styles.
	 *
	 * @since 1.0.0
	 */
	public static function register_styles() {
		if ( ! torro_is_settingspage() ) {
			return;
		}

		wp_enqueue_style( 'torro-settings-page', torro()->asset_url( 'settings-page', 'css' ) );
	}
}

Torro_SettingsPage::init();
