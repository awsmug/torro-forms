<?php
/**
 * Torro Forms Settings Class
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core/Settings
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Torro_Settings extends Torro_Instance {
	/**
	 * Settings
	 *
	 * @since 1.0.0
	 */
	protected $settings = array();

	/**
	 * Sub Settings
	 *
	 * @since 1.0.0
	 */
	protected $sub_settings = array();

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Adding settings field by array
	 *
	 * @param array $settings_fields
	 */
	public function add_settings_field_arr( $settings_field_array ) {
		$this->settings = array_merge( $this->settings, $settings_field_array );
	}

	/**
	 * Adding settings field by array
	 *
	 * @param array $settings_fields
	 */
	public function add_subsettings_field_arr( $setting_name, $setting_title, $settings_fields ) {
		$this->sub_settings[ $setting_name ] = array(
			'title'		=> $setting_title,
			'settings'	=> $settings_fields,
		);
	}

	/**
	 * Shows the Settings
	 *
	 * @param string $sub_setting_name
	 *
	 * @return string
	 */
	public function show( $sub_setting_name = '' ) {
		if ( 0 === count( $this->sub_settings ) ) {
			$settings_handler = new Torro_Settings_Handler( $this->name, $this->settings );
			$html = $settings_handler->get();
		} else {
			if ( is_array( $this->settings ) && 0 < count( $this->settings ) ) {
				// Adding General settings Page
				$sub_settings = array(
					'general' => array(
						'title'		=> __( 'General', 'torro-forms' ),
						'settings'	=> $this->settings,
					)
				);

				$sub_settings = array_merge( $sub_settings, $this->sub_settings );
			} else {
				$sub_settings = $this->sub_settings;

				if ( empty( $sub_setting_name ) ) {
					reset( $this->sub_settings );
					$sub_setting_name = key( $this->sub_settings );
				}
			}

			// Submenu
			$html = '<ul id="torro-settings-submenu">';
			foreach ( $sub_settings as $name => $settings ) {
				$css_classes = '';
				if ( $name === $sub_setting_name || ( '' === $sub_setting_name && 'general' === $name ) ) {
					$css_classes = ' active';
				}
				$html .= '<li class="submenu-tab' . $css_classes . '"><a href="' . admin_url( 'edit.php?post_type=torro-forms&page=Torro_Admin&tab=' . $this->name . '&section=' . $name ) . '">' . $settings[ 'title' ] . '</a></li>';
			}
			$html .= '</ul>';

			// Content of Submenu Tab
			$html .= '<div id="torro-settings-subcontent">';

			$settings_name = $this->name;
			if ( '' !== $sub_settings ) {
				$settings_name .= '_' . $sub_setting_name;
			}

			$settings = $sub_settings[ '' === $sub_setting_name ? 'general' : $sub_setting_name ];

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
	 * Saving Settngs
	 *
	 * @param string $sub_setting_name
	 */
	public function save_settings( $sub_setting_name = '' ) {
		if ( count( $this->sub_settings ) == 0 ) {
			$settings_handler = new Torro_Settings_Handler( $this->name, $this->settings );
			$settings_handler->save();

			do_action( 'torro_save_settings_' . $this->name );
		} else {
			$sub_settings = array(
				'general' => array(
					'title'		=> __( 'General', 'torro-forms' ),
					'settings'	=> $this->settings,
				)
			);

			$sub_settings = array_merge( $sub_settings, $this->sub_settings );

			$settings_name = $this->name;
			if ( '' !== $sub_setting_name ) {
				$settings_name .= '_' . $sub_setting_name;
			}

			$settings = $sub_settings[ '' === $sub_setting_name ? 'general' : $sub_setting_name ];

			$settings_handler = new Torro_Settings_Handler( $settings_name, $settings[ 'settings' ] );
			$settings_handler->save();
		}
	}
}

/**
 * @param $settings_name
 */
function torro_get_settings( $settings_name ) {
	$settings = torro()->settings()->get( $settings_name );
	if ( is_wp_error( $settings ) ) {
		return false;
	}

	$settings_handler = new Torro_Settings_Handler( $settings_name, $settings->settings );
	$values = $settings_handler->get_field_values();

	return $values;
}
