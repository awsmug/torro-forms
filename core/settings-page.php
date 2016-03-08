<?php

/**
 * Torro Forms settings page
 *
 * This class shows and saves the settings page
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core/Settings
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

class Torro_Settings_Page {

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
	 * Instance
	 */
	private static $instance = null;

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		add_action( 'init', array( __CLASS__, 'save' ), 20 );
		add_action( 'admin_print_styles', array( __CLASS__, 'register_styles' ) );
	}

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

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Show admin Settings
	 */
	public static function show() {
		self::init_tabs();

		$html = '<div class="wrap af">';
		$html .= '<form name="torro_settings" id="torro-settings" method="POST">';
		$html .= '<input type="hidden" id="torro_save_settings" name="torro_save_settings" value="' . wp_create_nonce( '_torro_save_settings_nonce' ) . '" />';

		$all_settings = torro()->settings()->get_all_registered();

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

			$html .= self::show_tab( torro()->settings()->get_registered( self::$current_tab ), self::$current_section );

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
		if ( isset( $_GET[ 'tab' ] ) ) {
			self::$current_tab = $_GET[ 'tab' ];
		} else {
			self::$current_tab = 'general';
		}

		if ( isset( $_GET[ 'section' ] ) ) {
			self::$current_section = $_GET[ 'section' ];
		}
	}

	/**
	 * Shows the Settings
	 *
	 * @param string $sub_setting_name
	 *
	 * @return string
	 */
	private static function show_tab( $settings, $section = '' ) {
		if ( 0 === count( $settings->sub_settings ) ) {
			$settings_handler = new Torro_Settings_Handler( $settings->name, $settings->settings );
			$html             = $settings_handler->get();
		} else {
			if ( is_array( $settings->settings ) && 0 < count( $settings->settings ) ) {
				// Adding General settings Page
				$sub_settings = array(
					'general' => array(
						'title'    => __( 'General', 'torro-forms' ),
						'settings' => $settings->settings,
					)
				);

				$sub_settings = array_merge( $sub_settings, $settings->sub_settings );
			} else {
				$sub_settings = $settings->sub_settings;

				if ( empty( $section ) ) {
					$section = key( $sub_settings );
				}
			}

			// Submenu
			$html = '<ul id="torro-settings-submenu">';
			foreach ( $sub_settings as $name => $setting ) {
				$css_classes = '';
				if ( $name === $section || ( '' === $section && 'general' === $name ) ) {
					$css_classes = ' active';
				}
				$html .= '<li class="submenu-tab' . $css_classes . '"><a href="' . admin_url( 'edit.php?post_type=torro-forms&page=Torro_Admin&tab=' . $settings->name . '&section=' . $name ) . '">' . $setting[ 'title' ] . '</a></li>';
			}
			$html .= '</ul>';

			// Content of Submenu Tab
			$html .= '<div id="torro-settings-subcontent">';

			$settings_name = $settings->name;
			if ( '' !== $sub_settings ) {
				$settings_name .= '_' . $section;
			}

			$settings = $sub_settings[ '' === $section ? 'general' : $section ];

			$settings_handler = new Torro_Settings_Handler( $settings_name, $settings[ 'settings' ] );
			$html .= $settings_handler->get();

			ob_start();
			do_action( $settings_name . '_content' );
			$html .= ob_get_clean();

			$html .= '</div>';
		}

		return $html;
	}

	/**
	 * Saving settings
	 */
	public static function save() {
		if ( ! isset( $_POST[ 'torro_save_settings' ] ) ) {
			return;
		}

		$tab = '';
		if ( isset( $_GET[ 'tab' ] ) ) {
			$tab = $_GET[ 'tab' ];
		}

		if( empty( $tab ) ){
			$tab = 'general';
		}

		$section = '';
		if ( isset( $_GET[ 'section' ] ) ) {
			$section = $_GET[ 'section' ];
		}

		$all_settings = torro()->settings()->get_all_registered();

		if ( 0 < count( $all_settings ) ) {
			/**
			 * Tabs
			 */
			foreach ( $all_settings AS $setting ) {
				if( $setting->name !== $tab ){
					continue;
				}

				if ( count( $setting->sub_settings ) == 0 ) {
					$settings_handler = new Torro_Settings_Handler( $setting->name, $setting->settings );
					$settings_handler->save();

					do_action( 'torro_save_settings_' . $setting->name );
				} else {
					$sub_settings = array(
						'general' => array(
							'title'    => __( 'General', 'torro-forms' ),
							'settings' => $setting->settings,
						)
					);

					$sub_settings = array_merge( $sub_settings, $setting->sub_settings );

					$settings_name = $setting->name;
					if ( '' !== $section ) {
						$settings_name .= '_' . $section;
					}

					$settings = $sub_settings[ '' === $section ? 'general' : $section ];

					$settings_handler = new Torro_Settings_Handler( $settings_name, $settings[ 'settings' ] );
					$settings_handler->save();
				}
			}
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

		wp_enqueue_style( 'torro-settings-page', torro()->get_asset_url( 'settings-page', 'css' ) );
	}
}

Torro_Settings_Page::init();
