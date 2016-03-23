<?php
/**
 * Settings handler for showing settings forms
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core/Settings
 * @version 1.0.0alpha1
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

class Torro_Settings_Handler {
	/**
	 * Handler name
	 *
	 * @var string
	 * @since 1.0.0
	 */
	var $name;

	/**
	 * Settings field array
	 *
	 * @var array
	 * @since 1.0.0
	 */
	var $fields = array();

	/**
	 * Settings type (options/post)
	 *
	 * @var string
	 * @since 1.0.0
	 */
	var $type = 'options';

	/**
	 * Values
	 *
	 * @var array
	 * @since 1.0.0
	 */
	var $values = array();

	/**
	 * Torro_Settings_Handler constructor
	 *
	 * @param string $settings_name
	 * @param array $settings_fields
	 * @param string $settings_type
	 * @since 1.0.0
	 */
	public function __construct( $settings_name, $settings_fields, $settings_type = 'options' ) {
		$this->name = $settings_name;
		$this->fields = $settings_fields;
		$this->type = $settings_type;
	}

	/**
	 * Getting a settings field with wrapper
	 *
	 * @return bool|string
	 * @since 1.0.0
	 */
	public function get() {
		if ( count( $this->fields ) == 0 ) {
			return false;
		}

		do_action( 'torro_settings_page_init' );

		$html  = '<div class="settings-table">';
		$html .= '<table class="form-table">';
		$html .= '<tbody>';
		foreach ( $this->fields as $name => $settings ) {
			$html .= $this->get_field( $name, $settings );
		}
		$html .= '</tbody>';
		$html .= '</table>';
		$html .= '<div class="clear"></div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Getting field html
	 *
	 * @param string $name
	 * @param array $settings
	 *
	 * @return string
	 * @since 1.0.0
	 */
	private function get_field( $name, $settings ) {
		global $post;

		if ( 'options' === $this->type ) {
			$default = '';
			if ( array_key_exists( 'default', $this->fields[ $name ] ) ) {
				$default = $this->fields[ $name ][ 'default' ];
			}

			$option_name = 'torro_settings_' . $this->name . '_' . $name;

			$value = get_option( $option_name, $default );
			// delete_option( $option_name );
		} elseif ( 'post' === $this->type ) {
			if ( property_exists( $post, 'ID' ) ) {
				$value = get_post_meta( $post->ID, $name, true );
			}
		}

		switch ( $settings[ 'type' ] ) {
			case 'text':
				$html = $this->get_textfield( $name, $settings, $value );
				break;
			case 'textarea':
				$html = $this->get_textarea( $name, $settings, $value );
				break;
			case 'wp_editor':
				$html = $this->get_wp_editor( $name, $settings, $value );
				break;
			case 'radio':
				$html = $this->get_radios( $name, $settings, $value );
				break;
			case 'checkbox':
				$html = $this->get_checkboxes( $name, $settings, $value );
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
	 * @since 1.0.0
	 */
	private function get_textfield( $name, $settings, $value ) {
		$html = '<tr>';
		$html .= '<th>' . esc_html( $settings['title'] ) . '</th>';
		$html .= '<td>';
		$html .= '<input type="text" name="' . $name . '" value="' . esc_attr( $value ) . '" />';

		ob_start();
		do_action( 'torro_settings_field_input_after_' . $name, $settings, $value );
		$html .= ob_get_clean();

		if ( isset( $settings['description'] ) ) {
			$html .= '<br /><small>' . $settings['description'] . '</small>';
		}
		$html .= '</td>';
		$html .= '</tr>';

		return $html;
	}

	/**
	 * Returns Textarea
	 *
	 * @param $name
	 * @param $value
	 *
	 * @return string
	 * @since 1.0.0
	 */
	private function get_textarea( $name, $settings, $value ) {
		$html = '<tr>';
		$html .= '<th>' . esc_html( $settings['title'] ) . '</th>';
		$html .= '<td>';
		$html .= '<textarea name="' . $name . '" rows="8">' . esc_html( $value ) . '</textarea>';

		ob_start();
		do_action( 'torro_settings_field_input_after_' . $name, $settings, $value );
		$html .= ob_get_clean();

		if ( isset( $settings['description'] ) ) {
			$html .= '<br /><small>' . $settings['description'] . '</small>';
		}
		$html .= '</td>';
		$html .= '</tr>';

		return $html;
	}

	/**
	 * Returns WP Editor
	 *
	 * @param $name
	 * @param $value
	 *
	 * @return string
	 * @since 1.0.0
	 */
	private function get_wp_editor( $name, $settings, $value ) {
		ob_start();
		wp_editor( $value, $name );
		$editor = ob_get_clean();

		$html = '<tr>';
		$html .= '<th>' . esc_html( $settings['title'] ) . '</th>';
		$html .= '<td>';
		$html .= $editor;
		if ( isset( $settings['description'] ) ) {
			$html .= '<br /><small>' . $settings['description'] . '</small>';
		}
		$html .= '</td>';
		$html .= '</tr>';

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
	 * @since 1.0.0
	 */
	private function get_radios( $name, $settings, $value ) {
		$html = '<tr>';
		$html .= '<th>' . esc_html( $settings['title'] ) . '</th>';
		$html .= '<td>';
		foreach ( $values as $field_key => $field_value ) {
			$checked = '';

			if ( $value == $field_key ) {
				$checked = ' checked="checked"';
			}

			$html .= '<div class="torro-radio"><input type="radio" name="' . $name . '" value="' . esc_attr( $field_key ) . '"' . $checked . ' /> ' . esc_html( $field_value ) . '</div>';
		}
		if ( isset( $settings['description'] ) ) {
			$html .= '<small>' . $settings['description'] . '</small>';
		}
		$html .= '</td>';
		$html .= '</tr>';

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
	 * @since 1.0.0
	 */
	private function get_checkboxes( $name, $settings, $value ) {
		$html = '<tr>';
		$html .= '<th>' . esc_html( $settings['title'] ) . '</th>';
		$html .= '<td>';
		foreach ( $settings['values'] as $field_key => $field_value ) {
			$checked = '';

			if ( is_array( $value ) && in_array( $field_key, $value ) ) {
				$checked = ' checked="checked"';
			}

			$html .= '<div class="torro-checkbox"><input type="checkbox" name="' . $name . '[]" value="' . esc_attr( $field_key ) . '"' . $checked . ' /> ' . esc_html( $field_value ) . '</div>';
		}
		if ( isset( $settings['description'] ) ) {
			$html .= '<small>' . $settings['description'] . '</small>';
		}
		$html .= '</td>';
		$html .= '</tr>';

		return $html;
	}

	/**
	 * Returns Textarea
	 *
	 * @param $name
	 * @param $value
	 *
	 * @return string
	 * @since 1.0.0
	 */
	private function get_title( $name, $settings ) {
		$html = '</tbody>';
		$html .= '</table>';
		$html .= '</div>';

		$html .= '<div class="settings-title">';
		$html .= '<h3>' . esc_html( $settings['title'] ) . '</h3>';

		if ( isset( $settings['description'] ) ) {
			$html .= '<p>' . $settings['description'] . '</p>';
		}
		$html .= '</div>';

		$html .= '<div class="settings-table">';
		$html .= '<table class="form-table">';
		$html .= '<tbody>';

		return $html;
	}

	/**
	 * Returns Textarea
	 *
	 * @param $name
	 * @param $value
	 *
	 * @return string
	 * @since 1.0.0
	 */
	private function get_disclaimer( $name, $settings ) {
		$html = '</tbody>';
		$html .= '</table>';
		$html .= '</div>';

		$html .= '<div class="settings-disclaimer">';
		$html .= '<h3>' . esc_html( $settings['title'] ) . '</h3>';

		if ( isset( $settings['description'] ) ) {
			$html .= '<p>' . $settings['description'] . '</p>';
		}
		$html .= '</div>';

		$html .= '<div class="settings-table">';
		$html .= '<table class="form-table">';
		$html .= '<tbody>';

		return $html;
	}

	/**
	 * Getting field values
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_field_values() {
		global $post;

		if ( 0 === count( $this->fields ) ) {
			return false;
		}

		foreach ( $this->fields as $name => $settings ) {
			$non_value_types = array( 'title', 'disclaimer' );

			if ( in_array( $settings[ 'type' ], $non_value_types, true ) ) {
				continue;
			}

			$option_name = 'torro_settings_' . $this->name . '_' . $name;

			if ( 'options' === $this->type ) {
				$default = '';
				if ( array_key_exists( 'default', $settings ) ) {
					$default = $settings[ 'default' ];
				}

				$this->values[ $name ] = get_option( $option_name, $default );
			}elseif( 'post' === $this->type ) {
				if ( property_exists( $post, 'ID' ) ) {
					$this->values[ $name ] = get_post_meta( $post->ID, $option_name );
				}
			}
		}

		return $this->values;
	}

	/**
	 * Saving settings fields
	 *
	 * @since 1.0.0
	 */
	public function save() {
		global $post;

		if ( 0 === count( $this->fields ) ) {
			return false;
		}

		// Running all settings fields
		foreach ( $this->fields as $name => $settings ) {
			$option_name = 'torro_settings_' . $this->name . '_' . $name;

			$value = '';
			if( isset( $_POST[ $name ] ) ) {
				$value = $_POST[ $name ];
			}

			if ( 'options' === $this->type ) {
				update_option( $option_name, $value );
			} elseif( 'post' === $this->type ) {
				if ( property_exists( $post, 'ID' ) ) {
					update_post_meta( $post->ID, $option_name, $value );
				}
			}
		}
	}
}
