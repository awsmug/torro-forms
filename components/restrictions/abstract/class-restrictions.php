<?php
/**
 * Restriction abstraction class
 *
 * Motherclass for all Restrictions
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package AwesomeForms/Restrictions
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

abstract class AF_Restriction
{
	/**
	 * The Single instances of the components
	 *
	 * @var $_instaces
	 * @since 1.0.0
	 */
	protected static $_instances = NULL;

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
	var $option_name = FALSE;

	/**
	 * Already initialized?
	 *
	 * @since 1.0.0
	 */
	var $initialized = FALSE;

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
	private function __construct()
	{
		$this->init();
	}

	/**
	 * Main Instance
	 *
	 * @since 1.0.0
	 */
	public static function instance()
	{
		if ( function_exists( 'get_called_class' ) ) {
			$class = get_called_class();
		} else {
			$class = self::php52_get_called_class();
		}

		if( !isset( self::$_instances[ $class ] ) )
		{
			self::$_instances[ $class ] = new $class();
			self::$_instances[ $class ]->_register();
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
	public function messages()
	{
		if( count( $this->messages ) > 0 )
		{
			$html = '';
			foreach( $this->messages AS $message )
			{
				$html .= '<div class="form-message ' . $message[ 'type' ] . '">' . $message[ 'text' ] . '</div>';
			}

			return $html;
		}

		return FALSE;
	}

	/**
	 * Adding messages
	 *
	 * @param $type
	 * @param $text
	 */
	public function add_message( $type, $text )
	{
		$this->messages[] = array(
			'type' => $type,
			'text' => $text
		);
	}

	/**
	 * Adds a Restriction option to the restrictions meta box
	 *
	 * @return bool
	 */
	public function has_option()
	{
		if( FALSE != $this->option_name )
		{
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Add Settings to Settings Page
	 */
	public function init_settings()
	{
		global $af_global;

		if( count( $this->settings_fields ) == 0 || '' == $this->settings_fields )
		{
			return FALSE;
		}

		$headline = array(
			'headline' => array(
				'title'       => $this->title,
				'description' => sprintf( esc_attr( 'Setup the "%s" Restriction.', 'af-locale' ), $this->title ),
				'type'        => 'disclaimer'
			)
		);

		$settings_fields = array_merge( $headline, $this->settings_fields );

		$af_global->settings[ 'restrictions' ]->add_settings_field( $this->name, $this->title, $settings_fields );

		$settings_name = 'restrictions_' . $this->name;

		$settings_handler = new AF_Settings_Handler( $settings_name, $this->settings_fields );
		$this->settings = $settings_handler->get_field_values();
	}

	/**
	 * Adds content to the option
	 */
	public function option_content()
	{
		return FALSE;
	}

	/**
	 * Function to register element in Awesome Forms
	 *
	 * After registerung was successfull the new element will be shown in the elements list.
	 *
	 * @return boolean $is_registered Returns TRUE if registering was succesfull, FALSE if not
	 * @since 1.0.0
	 */
	private function _register()
	{
		global $af_global;

		if( !is_object( $af_global ) )
		{
			return FALSE;
		}

		if( '' == $this->name )
		{
			$this->name = get_class( $this );
		}

		if( '' == $this->title )
		{
			$this->title = ucwords( get_class( $this ) );
		}

		if( '' == $this->description )
		{
			$this->description = esc_attr__( 'This is a Awesome Forms Restriction.', 'af-locale' );
		}

		if( array_key_exists( $this->name, $af_global->restrictions ) )
		{
			return FALSE;
		}

		if( !is_array( $af_global->restrictions ) )
		{
			$af_global->restrictions = array();
		}

		add_action( 'init', array( $this, 'init_settings' ), 15 );

		$this->initialized = TRUE;

		return $af_global->add_restriction( $this->name, $this );
	}
}

/**
 * Register a new Restriction
 *
 * @param $element_type_class name of the element type class.
 *
 * @return bool|null Returns false on failure, otherwise null.
 */
function af_register_restriction( $restriction_class )
{
	if( class_exists( $restriction_class ) )
	{
		return call_user_func( array( $restriction_class, 'instance' ) );
	}

	return FALSE;
}
