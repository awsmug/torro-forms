<?php
/**
 * Questions Post Class
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package Questions/Core
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

if( !defined( 'ABSPATH' ) ){
	exit;
}

abstract class Questions_Settings
{
	/**
	 * Slug of restriction
	 * @since 1.0.0
	 */
	public $slug;

	/**
	 * Title of restriction
	 * @since 1.0.0
	 */
	public $title;

	/**
	 * Description of restriction
	 * @since 1.0.0
	 */
	public $description;

	/**
	 * Already initialized?
	 * @since 1.0.0
	 */
	private $initialized = FALSE;

	/**
	 * Settings
	 * @since 1.0.0
	 */
	public $settings;

	/**
	 * Will be shown within settings tab
	 * @return mixed
	 */
	abstract function settings();

	/**
	 * Shows the Settings
	 * @return string
	 */
	public function show(){
		$settings_handler = new Questions_SettingsHandler( $this->slug, $this->settings );
		$html = $settings_handler->get();

		return $html;
	}

	/**
	 * Saving Settngs
	 */
	public function save_settings()
	{
		$settings_handler = new Questions_SettingsHandler( $this->slug, $this->settings );
		$settings_handler->save();

		do_action( 'questions_save_settings_' . $this->slug );
	}

	/**
	 * Function to register element in Questions
	 *
	 * After registerung was successfull the new element will be shown in the elements list.
	 *
	 * @return boolean $is_registered Returns TRUE if registering was succesfull, FALSE if not
	 * @since 1.0.0
	 */
	public function _register()
	{
		global $questions_global;

		if( TRUE == $this->initialized ){
			return FALSE;
		}

		if( !is_object( $questions_global ) ){
			return FALSE;
		}

		if( '' == $this->slug ){
			$this->slug = get_class( $this );
		}

		if( '' == $this->title ){
			$this->title = ucwords( get_class( $this ) );
		}

		if( '' == $this->description ){
			$this->description = esc_attr__( 'This is the Questions Responsehandler extension.', 'questions-locale' );
		}

		if( array_key_exists( $this->slug, $questions_global->settings ) ){
			return FALSE;
		}

		if( !is_array( $questions_global->settings ) ){
			$questions_global->settings = array();
		}

		$this->initialized = TRUE;

		$this->settings(); // Initializing settings

		add_action( 'init', array( $this, 'save_settings' ), 50 );

		return $questions_global->add_settings( $this->slug, $this );
	}
}

/**
 * Register a new Response handler
 *
 * @param $element_type_class name of the element type class.
 *
 * @return bool|null Returns false on failure, otherwise null.
 */
function qu_register_settings( $settings_handler_class )
{
	if( !class_exists( $settings_handler_class ) ){
		return FALSE;
	}

	add_action( 'init', create_function( '', '$extension = new ' . $settings_handler_class . '; add_action( "init", array( &$extension, "_register" ), 2 ); ' ), 1 );
}