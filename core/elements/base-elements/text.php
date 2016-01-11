<?php
/**
 * Description Form Element
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core/Elements
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Torro_Form_Element_Text extends Torro_Form_Element {
	public function init() {
		$this->name = 'text';
		$this->title = __( 'Text', 'torro-forms' );
		$this->description = __( 'Adds a text to the form.', 'torro-forms' );
		$this->icon_url = TORRO_URLPATH . 'assets/img/icon-text.png';

		$this->is_answerable = false;
	}

	public function input_html() {
		return wpautop( $this->label );
	}

	public function admin_content_html() {
		$widget_id = $this->admin_get_widget_id();

		$editor_id = 'wp_editor_' . substr( md5( rand() * time() ), 0, 5 );
		$field_name = 'elements[' . $widget_id . '][label]';

		$settings = array(
			'textarea_name' => $field_name,
		);

		$html = '<div class="torro-element-description">';
		ob_start();
		wp_editor( $this->label, $editor_id, $settings );
		$html .= ob_get_clean();
		$html .= '</div>';

		return $html;
	}
}

torro()->form_elements()->add( 'Torro_Form_Element_Text' );
