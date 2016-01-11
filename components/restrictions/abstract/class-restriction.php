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

abstract class Torro_Restriction {
	/**
	 * The Single instances of the components
	 *
	 * @var $_instaces
	 * @since 1.0.0
	 */
	protected static $_instances = array();

	/**
	 * Name of restriction
	 *
	 * @since 1.0.0
	 */
	var $name;

	/**
	 * Title of restriction
	 *
	 * @since 1.0.0
	 */
	var $title;

	/**
	 * Description of restriction
	 *
	 * @since 1.0.0
	 */
	var $description;

	/**
	 * Option name
	 *
	 * @since 1.0.0
	 */
	var $option_name = false;

	/**
	 * Already initialized?
	 *
	 * @since 1.0.0
	 */
	var $initialized = false;

	/**
	 * Settings fields array
	 *
	 * @since 1.0.0
	 */
	var $settings_fields = array();

	/**
	 * Settings
	 *
	 * @since 1.0.0
	 */
	var $settings = array();

	/**
	 * Message
	 *
	 * @var array
	 * @since 1.0.0
	 */
	var $messages = array();

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Main Instance
	 *
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( function_exists( 'get_called_class' ) ) {
			$class = get_called_class();
		} else {
			$class = self::php52_get_called_class();
		}

		if ( ! isset( self::$_instances[ $class ] ) ) {
			self::$_instances[ $class ] = new $class();
		}

		return self::$_instances[ $class ];
	}

	/**
	 * PHP 5.2 variant of `get_called_class()`
	 *
	 * Really ugly, but PHP 5.2 does not support late static binding.
	 * Using `debug_backtrace()` is the only way.
	 *
	 * This function must exist in every class that should use `get_called_class()`.
	 *
	 * @since 1.0.0
	 */
	private static function php52_get_called_class() {
		$arr = array();
		$arr_traces = debug_backtrace();
		foreach ( $arr_traces as $arr_trace ) {
			$class_name = '';
			if ( isset( $arr_trace['class'] ) ) {
				$class_name = $arr_trace['class'];
			} elseif ( isset( $arr_trace['function'] ) && isset( $arr_trace['args'] ) && isset( $arr_trace['args'][0] ) && is_array( $arr_trace['args'][0] ) ) {
				if ( 'call_user_func' == $arr_trace['function'] && 'instance' == $arr_trace['args'][0][1] && is_string( $arr_trace['args'][0][0] ) ) {
					$class_name = $arr_trace['args'][0][0];
				}
			}

			if ( $class_name && 0 == count( $arr ) || get_parent_class( $class_name ) == end( $arr ) ) {
				$arr[] = $class_name;
			}
		}
		return end( $arr );
	}

	/**
	 * Function for setting initial Data
	 */
	abstract function init();

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

		torro()->settings()->get( 'restrictions' )->add_settings_field( $this->name, $this->title, $settings_fields );

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
