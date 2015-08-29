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
			$html.= $this->get_field( $name, $settings );
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

				$html = $this->get_textfield( $name, $settings, $value );
				break;

			case 'textarea':

				$html = $this->get_textarea( $name, $settings, $value );
				break;

			case 'radio':

				$html = $this->get_radios( $name, $settings, $value );
				break;

			case 'checkbox':

				$html = $this->get_checkboxes( $name, $settings, $value, $settings[ 'values' ] );
				break;

			case 'title':

				$html = $this->get_title( $name, $settings );
				break;

			case 'disclaimer':

				$html = $this->get_disclaimer( $name, $settings );
				break;

		}
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
	private function get_textfield( $name, $settings, $value )
	{
		$html = '<tr>';
			$html.= '<th>' . $settings[ 'title' ] . '</th>';
			$html.= '<td>';
				$html.= '<input type="text" name="' . $name . '" value="' . $value . '" />';
				if( isset( $settings[ 'description' ] ) ){
					$html.= '<br /><small>' . $settings[ 'description' ] . '</small>';
				}
			$html.= '</td>';
		$html.= '</tr>';

		return $html;
	}

	/**
	 * Returns Textarea
	 *
	 * @param $name
	 * @param $value
	 *
	 * @return string
	 */
	private function get_textarea( $name, $settings, $value )
	{
		$html = '<tr>';
			$html.= '<th>' . $settings[ 'title' ] . '</th>';
			$html.= '<td>';
					$html.= '<textarea name="' . $name . '" cols="50" rows="8">' . $value . '</textarea>';
					if( isset( $settings[ 'description' ] ) ){
						$html .= '<br /><small>' . $settings[ 'description' ] . '</small>';
					}
			$html.= '</td>';
		$html.= '</tr>';

		return $html;
	}

	/**
	 * Returns Radio button
	 *
	 * @param $name
	 * @param $value
	 * @param $values
	 *
	 * @return string
	 */
	private function get_radios( $name, $settings, $value )
	{
		$html = '<tr>';
			$html.= '<th>' . $settings[ 'title' ] . '</th>';
			$html.= '<td>';
				foreach( $values AS $field_key => $field_value ):
					$checked = '';

					if( $value == $field_key ){
						$checked = ' checked="checked"';
					}


					$html .= '<div class="questions-radio"><input type="radio" name="' . $name . '" value="' . $field_key . '"' . $checked . ' /> ' . $field_value . '</div>';
				endforeach;
				if( isset( $settings[ 'description' ] ) ){
					$html .= '<small>' . $settings[ 'description' ] . '</small>';
				}
			$html.= '</td>';
		$html.= '</tr>';

		return $html;
	}

	/**
	 * Returns Checkboxes
	 *
	 * @param $name
	 * @param $value
	 * @param $values
	 *
	 * @return string
	 */
	private function get_checkboxes( $name, $settings, $value )
	{
		$html = '<tr>';
			$html.= '<th>' . $settings[ 'title' ] . '</th>';
			$html.= '<td>';
				foreach( $settings[ 'values' ] AS $field_key => $field_value ):
					$checked = '';

					if( $value == $field_key ){
						$checked = ' checked="checked"';
					}

					$html .= '<div class="questions-checkbox"><input type="checkbox" name="' . $name . '" value="' . $field_key . '"' . $checked . ' /> ' . $field_value .'</div>' ;
				endforeach;
				if( isset( $settings[ 'description' ] ) ){
					$html .= '<small>' . $settings[ 'description' ] . '</small>';
				}
			$html.= '</td>';
		$html.= '</tr>';

		return $html;
	}

	/**
	 * Returns Textarea
	 *
	 * @param $name
	 * @param $value
	 *
	 * @return string
	 */
	private function get_title( $name, $settings )
	{
		$html = '</tbody>';
		$html.= '</table>';


		$html.= '<h3>' . $settings[ 'title' ] . '</h3>';

		if( isset( $settings[ 'description' ] ) ){
			$html .= '<p>' . $settings[ 'description' ] . '</p>';
		}

		$html.= '<table class="form-table">';
		$html.= '<tbody>';

		return $html;
	}

	/**
	 * Returns Textarea
	 *
	 * @param $name
	 * @param $value
	 *
	 * @return string
	 */
	private function get_disclaimer( $name, $settings )
	{
		$html = '</tbody>';
		$html.= '</table>';

		$html.= '<div class="questions-settings-disclaimer">';
		$html.= '<h3>' . $settings[ 'title' ] . '</h3>';

		if( isset( $settings[ 'description' ] ) ){
			$html .= '<p>' . $settings[ 'description' ] . '</p>';
		}
		$html.= '</div>';

		$html.= '<table class="form-table">';
		$html.= '<tbody>';

		return $html;
	}
}