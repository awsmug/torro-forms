<?php
/**
 * Awesome Forms Main Component Class
 *
 * This class is the base for every Awesome Forms Component.
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package AwesomeForms/Core
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

abstract class AF_Component
{
	/**
	 * The Single instances of the components
	 *
	 * @var $_instaces
	 * @since 1.0.0
	 */
	protected static $_instances = NULL;

	/**
	 * Title
	 *
	 * @since 1.0.0
	 */
	var $title;

	/**
	 * Name
	 *
	 * @since 1.0.0
	 */
	var $name;

	/**
	 * Description
	 *
	 * @since 1.0.0
	 */
	var $description;

	/**
	 * Settings
	 *
	 * @since 1.0.0
	 */
	var $settings;

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	private function __construct()
	{
		global $af_global;

		$this->init();

		if( empty( $this->name ) )
		{
			$this->name = get_class( $this );
		}
		if( empty( $this->title ) )
		{
			$this->title = ucfirst( $this->name );
		}
		if( empty( $this->name ) )
		{
			$this->description = esc_attr__( 'This is an Awesome Forms component.', 'af-locale' );
		}

		if( !is_object( $af_global ) )
		{
			self::admin_notice( esc_attr__( 'Missing Global', 'af-global' ), 'error' );
			return FALSE;
		}

		$af_global->components[ $this->name ] = $this;

		return TRUE;
	}

	/**
	 * Function for setting initial Data
	 */
	abstract function init();

	/**
	 * Main Instance
	 *
	 * @since 1.0.0
	 */
	public static function instance()
	{
		$class = get_called_class();
		if( !isset( self::$_instances[ $class ] ) )
		{
			self::$_instances[ $class ] = new $class();
			add_action( 'plugins_loaded', array( self::$_instances[ $class ], 'check_and_start' ), 20 );
		}

		return self::$_instances[ $class ];
	}

	/**
	 * Checking and starting
	 *
	 * @since 1.0.0
	 */
	public function check_and_start()
	{

		$values = af_get_settings( 'general' );

		if( is_array( $values[ 'modules' ] ) && !in_array( $this->name, $values[ 'modules' ] ) )
		{
			return;
		}

		$class = get_called_class();
		if( TRUE == self::$_instances[ $class ]->check_requirements() )
		{
			self::$_instances[ $class ]->base_init();
			$this->settings = af_get_settings( $this->name );
		}
	}

	/**
	 * Function for Checks
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	protected function check_requirements()
	{
		return TRUE;
	}

	/**
	 * Running Scripts if functions are existing in child Class
	 *
	 * @since 1.0.0
	 */
	private function base_init()
	{
		if( method_exists( $this, 'includes' ) )
		{
			$this->includes();
		}

		// Scriptloaders
		if( is_admin() )
		{
			add_action( 'admin_print_styles', array( $this, 'admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		}
		else
		{
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
		}
	}

	/**
	 * Function for enqueuing Admin Scripts - Have to be overwritten by Child Class.
	 */
	public function admin_scripts(){}

	/**
	 * Function for enqueuing Admin Styles - Have to be overwritten by Child Class.
	 */
	public function admin_styles(){}

	/**
	 * Function for enqueuing Frontend Scripts - Have to be overwritten by Child Class.
	 */
	public function frontend_scripts(){}

	/**
	 * Function for enqueuing Frontend Styles - Have to be overwritten by Child Class.
	 */
	public function frontend_styles(){}

	/**
	 * Adds a notice to
	 *
	 * @param        $message
	 * @param string $type
	 */
	protected function admin_notice( $message, $type = 'updated' )
	{
		if( WP_DEBUG )
		{
			$message = $message . ' (in Module "' .  $this->name . '")';
		}
		AF_Init::admin_notice( $message , $type );
	}
}

/**
 * Registering component
 *
 * @param $component_name
 * @return mixed
 */
function af_register_component( $component_class )
{
	if( class_exists( $component_class ) )
	{
		return $component_class::instance();
	}

	return FALSE;
}