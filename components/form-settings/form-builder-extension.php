<?php
/**
 * Torro Forms Restrictions Extension for the Formbuilder
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
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

class Torro_Formbuilder_Form_Settings_Extension {
	/**
	 * Init in WordPress, run on constructor
	 *
	 * @return null
	 * @since 1.0.0
	 */
	public static function init() {
		if ( ! is_admin() ) {
			return null;
		}

		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ), 15 );
	}

	/**
	 * Adding meta boxes
	 *
	 * @param string $post_type Actual post type
	 *
	 * @since 1.0.0
	 */
	public static function meta_boxes( $post_type ) {
		$post_types = array( 'torro-forms' );

		if ( in_array( $post_type, $post_types ) ) {
			add_meta_box( 'form-settings', __( 'Settings', 'torro-forms' ), array( __CLASS__, 'meta_box_form_settings' ), 'torro-forms', 'normal', 'low' );
		}
	}

	/**
	 * Form Restrictions box
	 *
	 * @since 1.0.0
	 */
	public static function meta_box_form_settings() {
		$form_settings = torro()->form_settings()->get_all_registered();

		if ( ! is_array( $form_settings ) || 0 === count( $form_settings ) ) {
			return;
		}

		$html = '<div id="form-form-settings-tabs" class="section tabs">';

		$html .= '<ul class="settings-tabs">';
		foreach ( $form_settings as $form_setting ){
			if ( ! $form_setting->has_option() ) {
				continue;
			}
			$html .= '<li><a href="#' . $form_setting->name . '">' . $form_setting->title . '</a></option>';
		}
		$html .= '</ul>';

		$html .= '<div class="clear"></div>';

		foreach ( $form_settings as $form_setting ) {
			if ( ! $form_setting->has_option() ){
				continue;
			}
			$html .= '<div id="' . $form_setting->name . '" class="tab-content">' . $form_setting->option_content() . '</div>';
		}

		$html .= '</div>';

		echo $html;
	}
}

Torro_Formbuilder_Form_Settings_Extension::init();
