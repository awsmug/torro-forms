<?php
/**
 * Awesome Forms Settings Class
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package AwesomeForms/Core/Settings
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

abstract class AF_Settings
{
	/**
	 * name of restriction
	 * @since 1.0.0
	 */
	public $name;

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
	public $settings = array();

	/**
	 * Sub Settings
	 * @since 1.0.0
	 */
	public $sub_settings = array();

	/**
	 * Will be shown within settings tab
	 * @return mixed
	 */
	abstract function settings();

	/**
	 * Adding settings field by array
	 * @param array $settings_field_array
	 */
	public function add_settings_field_arr( $settings_field_array )
	{
		$this->settings = array_merge( $this->settings, $settings_field_array );
	}

	/**
	 * Adding settings field by array
	 * @param array $settings_field_array
	 */
	public function add_subsettings_field_arr( $sub_setting_name, $settings_field_array )
	{
		$this->sub_settings[ $sub_setting_name ] = $settings_field_array;
	}

	/**
	 * Shows the Settings
	 * @param string $sub_setting_name
	 * @return string
	 */
	public function show( $sub_setting_name = '' )
	{
		if( count( $this->sub_settings ) == 0 )
		{
			$settings_handler = new AF_SettingsHandler( $this->name, $this->settings );
			$html = $settings_handler->get();
		}
		else
		{
			$sub_settings = array(
				'general' => array(
					'title' => esc_attr( 'General', 'af-locale' ),
					'settings' => $this->settings
				)
			);

			$sub_settings = array_merge( $sub_settings, $this->sub_settings );

			$html = '<ul id="af-settings-submenu">';
			foreach( $sub_settings AS $name => $settings )
			{
				$css_classes = '';
				if( $name == $sub_setting_name || ( '' == $sub_setting_name && 'general' == $name ) )
				{
					$css_classes =  ' active';
				}
				$html.= '<li class="submenu-tab' . $css_classes . '"><a href="' . admin_url( 'admin.php?page=AF_Admin&tab=' . $this->name . '&section=' . $name  ) . '">' . $settings[ 'title' ] . '</a></li>';
			}
			$html.= '</ul>';

			$html.= '<div id="af-settings-subcontent">';

			$settings_name = $this->name;
			if( '' != $sub_settings )
			{
				$settings_name.= '_' . $sub_setting_name;
			}

			$settings = $sub_settings[ '' == $sub_setting_name ? 'general' : $sub_setting_name ];

			$settings_handler = new AF_SettingsHandler( $settings_name, $settings[ 'settings' ] );
			$html.= $settings_handler->get();

			ob_start();
			do_action( $settings_name . '_content' );
			$html.= ob_get_clean();

			$html.= '</div>';
		}

		return $html;
	}

	/**
	 * Saving Settngs
	 * @param string $sub_setting_name
	 */
	public function save_settings( $sub_setting_name = '' )
	{
		if( count( $this->sub_settings ) == 0 )
		{
			$settings_handler = new AF_SettingsHandler( $this->name, $this->settings );
			$settings_handler->save();

			do_action( 'af_save_settings_' . $this->name );
		}
		else{

			$sub_settings = array(
				'general' => array(
					'title' => esc_attr( 'General', 'af-locale' ),
					'settings' => $this->settings
				)
			);

			$sub_settings = array_merge( $sub_settings, $this->sub_settings );

			$settings_name = $this->name;
			if( '' != $sub_setting_name )
			{
				$settings_name.= '_' . $sub_setting_name;
			}

			$settings = $sub_settings[ '' == $sub_setting_name ? 'general' : $sub_setting_name ];

			$settings_handler = new AF_SettingsHandler( $settings_name, $settings[ 'settings' ] );
			$settings_handler->save();
		}
	}

	/**
	 * Function to register element in Awesome Forms
	 *
	 * After registerung was successfull the new element will be shown in the elements list.
	 *
	 * @return boolean $is_registered Returns TRUE if registering was succesfull, FALSE if not
	 * @since 1.0.0
	 */
	public function _register()
	{
		global $af_global;

		if( TRUE == $this->initialized ){
			return FALSE;
		}

		if( !is_object( $af_global ) ){
			return FALSE;
		}

		if( '' == $this->name ){
			$this->name = get_class( $this );
		}

		if( '' == $this->title ){
			$this->title = ucwords( get_class( $this ) );
		}

		if( '' == $this->description ){
			$this->description = esc_attr__( 'This is the Awesome Forms Responsehandler extension.', 'af-locale' );
		}

		if( array_key_exists( $this->name, $af_global->settings ) ){
			return FALSE;
		}

		if( !is_array( $af_global->settings ) ){
			$af_global->settings = array();
		}

		$this->initialized = TRUE;

		$this->settings(); // Initializing settings

		add_action( 'af_save_settings', array( $this, 'save_settings' ), 10, 1 );

		return $af_global->add_settings( $this->name, $this );
	}
}

/**
 * Register a new Response handler
 *
 * @param $element_type_class name of the element type class.
 *
 * @return bool|null Returns false on failure, otherwise null.
 */
function af_register_settings( $settings_handler_class )
{
	if( class_exists( $settings_handler_class ) ){
		$settings_handler = new $settings_handler_class();
		return $settings_handler->_register();
	}
	return FALSE;
}