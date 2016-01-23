<?php
/**
 * Restriction abstraction class
 *
 * Motherclass for all Restrictions
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Restrictions
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

abstract class Torro_Restriction extends Torro_Instance {
	/**
	 * Option name
	 *
	 * @since 1.0.0
	 */
	protected $option_name = false;

	/**
	 * Settings fields array
	 *
	 * @since 1.0.0
	 */
	protected $settings_fields = array();

	/**
	 * Settings
	 *
	 * @since 1.0.0
	 */
	protected $settings = array();

	/**
	 * Message
	 *
	 * @since 1.0.0
	 */
	protected $messages = array();

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Checks if the user can pass
	 */
	abstract function check();

	/**
	 * Printing out messages
	 */
	public function messages() {
		if ( 0 < count( $this->messages ) ) {
			$html = '';
			foreach ( $this->messages as $message ) {
				$html .= '<div class="form-message ' . $message['type'] . '">' . esc_html( $message['text'] ) . '</div>';
			}

			return $html;
		}

		return false;
	}

	/**
	 * Adding messages
	 *
	 * @param $type
	 * @param $text
	 */
	public function add_message( $type, $text ) {
		$this->messages[] = array(
			'type'	=> $type,
			'text'	=> $text
		);
	}

	/**
	 * Adds a Restriction option to the restrictions meta box
	 *
	 * @return bool
	 */
	public function has_option() {
		if ( false !== $this->option_name ) {
			return true;
		}

		return false;
	}

	/**
	 * Add Settings to Settings Page
	 */
	public function init_settings() {
		if ( 0 === count( $this->settings_fields ) || empty( $this->settings_fields ) ) {
			return false;
		}

		$headline = array(
			'headline'		=> array(
				'title'			=> $this->title,
				'description'	=> sprintf( __( 'Setup the "%s" Restriction.', 'torro-forms' ), $this->title ),
				'type'			=> 'disclaimer'
			)
		);

		$settings_fields = array_merge( $headline, $this->settings_fields );

		torro()->settings()->get( 'restrictions' )->add_subsettings_field_arr( $this->name, $this->title, $settings_fields );

		$settings_name = 'restrictions_' . $this->name;

		$settings_handler = new Torro_Settings_Handler( $settings_name, $this->settings_fields );
		$this->settings = $settings_handler->get_field_values();
	}

	/**
	 * Adds content to the option
	 */
	public function option_content() {
		return false;
	}
}
