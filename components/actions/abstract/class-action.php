<?php
/**
 * Responses abstraction class
 *
 * Motherclass for all Response handlers
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Actions
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

abstract class Torro_Action
{
	/**
	 * The Single instances of the components
	 *
	 * @var $_instaces
	 * @since 1.0.0
	 */
	protected static $_instances = NULL;

	/**
	 * name of restriction
	 *
	 * @since 1.0.0
	 */
	public $name;

	/**
	 * Title of restriction
	 *
	 * @since 1.0.0
	 */
	public $title;

	/**
	 * Description of restriction
	 *
	 * @since 1.0.0
	 */
	public $description;

	/**
	 * Settings fields
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
	 * Contains the option_content
	 */
	public $option_content = '';

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
	 * Handles the data after user submitted the form
	 *
	 * @param $response_id
	 * @param $response
	 */
	abstract function handle( $response_id, $response );

	/**
	 * Checks if there is an option content
	 */
	public function has_option()
	{
		if( '' != $this->option_content )
		{
			return $this->option_content;
		}

		$this->option_content = $this->option_content();

		if( FALSE == $this->option_content )
		{
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Content of option in Form builder
	 */
	public function option_content(){}

	/**
	 * Add Settings to Settings Page
	 */
	public function init_settings()
	{
		global $torro_global;

		if( count( $this->settings_fields ) == 0 || '' == $this->settings_fields )
		{
			return FALSE;
		}

		$headline = array(
			'headline' => array(
				'title'       => $this->title,
				'description' => sprintf( esc_attr__( 'Setup the "%s" Action.', 'torro-forms' ), $this->title ),
				'type'        => 'title'
			)
		);

		$settings_fields = array_merge( $headline, $this->settings_fields );

		$torro_global->settings[ 'actions' ]->add_settings_field( $this->name, $this->title, $settings_fields );

		$settings_name = 'actions_' . $this->name;

		$settings_handler = new Torro_Settings_Handler( $settings_name, $this->settings_fields );
		$this->settings = $settings_handler->get_field_values();
	}

	/**
	 * Function to register element in Torro Forms
	 *
	 * After registerung was successfull the new element will be shown in the elements list.
	 *
	 * @return boolean $is_registered Returns TRUE if registering was succesfull, FALSE if not
	 * @since 1.0.0
	 */
	private function _register()
	{
		global $torro_global;

		if( !is_object( $torro_global ) )
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
			$this->description = esc_attr__( 'This is the Torro Forms Action  extension.', 'torro-forms' );
		}

		if( array_key_exists( $this->name, $torro_global->actions ) )
		{
			return FALSE;
		}

		if( !is_array( $torro_global->actions ) )
		{
			$torro_global->actions = array();
		}

		add_action( 'init', array( $this, 'init_settings' ), 15 );

		$this->initialized = TRUE;

		return $torro_global->add_action( $this->name, $this );
	}
}

/**
 * Register a new Response handler
 *
 * @param $element_type_class name of the element type class.
 *
 * @return bool|null Returns false on failure, otherwise null.
 */
function torro_register_action( $action_class )
{
	if( class_exists( $action_class ) )
	{
		return call_user_func( array( $action_class, 'instance' ) );
	}

	return FALSE;
}
