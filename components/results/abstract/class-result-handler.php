<?php
/**
 * Result Handler abstraction class
 *
 * Motherclass for all Result Handlers
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

if( !defined( 'ABSPATH' ) )
{
	exit;
}

abstract class Torro_ResultHandler
{
	/**
	 * Name of Result Component
	 *
	 * @since 1.0.0
	 */
	var $name;

	/**
	 * Title of Result Component
	 *
	 * @since 1.0.0
	 */
	var $title;

	/**
	 * Description of Result Component
	 *
	 * @since 1.0.0
	 */
	var $description;

	/**
	 * Already initialized?
	 *
	 * @since 1.0.0
	 */
	private $initialized = FALSE;

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
	 *
	 * @since 1.0.0
	 */
	public $option_content = '';

	/**
	 * Content of option in Form builder
	 *
	 * @since 1.0.0
	 */
	abstract function option_content();

	/**
	 * Checks if there is an option content
	 *
	 * @since 1.0.0
	 */
	public function has_option()
	{
		if( '' != $this->option_content )
		{
			return $this->option_content;
		}

		$this->option_content = $this->option_content();

		if( FALSE === $this->option_content )
		{
			return FALSE;
		}

		return TRUE;
	}

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
				'description' => sprintf( esc_attr__( 'Setup the "%s" Result Handler.', 'torro-forms' ), $this->title ),
				'type'        => 'title'
			)
		);

		$settings_fields = array_merge( $headline, $this->settings_fields );

		$torro_global->settings[ 'resulthandling' ]->add_settings_field( $this->name, $this->title, $settings_fields );

		$settings_name = 'resulthandling_' . $this->name;

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
	public function _register()
	{
		global $torro_global;

		if( TRUE == $this->initialized )
		{
			return FALSE;
		}

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
			$this->description = esc_attr__( 'This is a Torro Forms Result Handler.', 'torro-forms' );
		}

		if( array_key_exists( $this->name, $torro_global->restrictions ) )
		{
			return FALSE;
		}

		if( !is_array( $torro_global->result_handlers ) )
		{
			$torro_global->result_handlers = array();
		}

		add_action( 'init', array( $this, 'init_settings' ), 15 );

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

		$this->initialized = TRUE;

		return $torro_global->add_result_handler( $this->name, $this );
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
}

/**
 * Register a new Result Handler
 *
 * @param $restriction_class
 *
 * @return bool|null Returns false on failure, otherwise null.
 */
function torro_register_result_handler( $result_handler__class )
{
	if( class_exists( $result_handler__class ) )
	{
		$result_handler = new $result_handler__class();

		return $result_handler->_register();
	}

	return FALSE;
}