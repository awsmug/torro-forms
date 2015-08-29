<?php
/**
 * General Settings Tab
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

class Questions_SettingsHandler
{
	/**
	 * @var string
	 */
	var $slug;

	/**
	 * Settings field array
	 * @var array
	 */
	var $fields = array();

	/**
	 * @var string
	 */
	var $type = 'options';

	public function __construct( $settings_slug, $settings_fields, $settings_type = 'options' )
	{
		$this->slug = $settings_slug;
		$this->fields = $settings_fields;
		$this->type = $settings_type;
	}

	public function get()
	{
		if( count( $this->fields ) == 0 ){
			return FALSE;
		}

		$html = '<table class="form-table">';
		$html.= '<tbody>';
		foreach( $this->fields AS $name => $settings ){
			$html.= '<tr>';
				$html.= '<th>' . $settings[ 'title' ] . '</th>';
				$html.= '<td>' . $this->get_field( $name, $settings ) . '</td>';
			$html.= '</tr>';
		}
		$html.= '</tbody>';
		$html.= '</table>';

		return $html;
	}

	/**
	 * Saving settings fields
	 */
	public function save()
	{

	}

	private function get_field( $name, $settings )
	{
		global $post;

		if( 'options' == $this->type )
		{
			$value = get_option( 'af_settings_' . $this->slug . '_' . $name );
		}
		elseif( 'post' == $this->type )
		{
			$value = get_post_meta ( $post->ID, $name, TRUE );
		}

		switch ( $settings[ 'type' ] ){

			case 'text':

				$input = $this->get_textfield( $name, $value );
				break;

			case 'textarea':

				$input = $this->get_textarea( $name, $value );
				break;

			case 'radio':

				$input = $this->get_radio( $name, $value, $settings[ 'values' ] );
				break;

		}

		$html = '<div class="questions-inputfield">';
		$html.= $input . '<br />';

		if( isset( $settings[ 'description' ] ) ){
			$html .= '<small>' . $settings[ 'description' ] . '</small>';
		}

		$html.= '</div>';

		return $html;
	}

	/**
	 * Returns Textfield
	 *
	 * @param $name
	 * @param $value
	 *
	 * @return string
	 */
	private function get_textfield( $name, $value )
	{
		$input = '<input type="text" name="' . $name . '" value="' . $value . '" />';

		return $input;
	}

	/**
	 * Returns Textarea
	 *
	 * @param $name
	 * @param $value
	 *
	 * @return string
	 */
	private function get_textarea( $name, $value )
	{
		$input = '<textarea name="' . $name . '">' . $value . '</textarea>';

		return $input;
	}

	/**
	 * Returns Textarea
	 *
	 * @param $name
	 * @param $value
	 * @param $values
	 *
	 * @return string
	 */
	private function get_radio( $name, $value, $values )
	{
		$input = '';

		foreach( $values AS $field_key => $field_value ):
			$checked = '';

			if( $value == $field_key ){
				$checked = ' checked="checked"';
			}

			$input .= '<span class="surveval-form-fieldset-input-radio"><input type="radio" name="' . $name . '" value="' . $field_key . '"' . $checked . ' /> ' . $field_value . '</span>';
		endforeach;

		return $input;
	}
}