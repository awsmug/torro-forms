<?php
/**
 * Separator Form Element
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

class Torro_Form_Element_Separator extends Torro_Form_Element {
	public function init() {
		$this->name = 'separator';
		$this->title = __( 'Separator', 'torro-forms' );
		$this->description = __( 'Adds a optical separator (<hr>) between elements.', 'torro-forms' );
		$this->icon_url = TORRO_URLPATH . 'assets/img/icon-separator.png';

		$this->has_content = false;
		$this->is_answerable = false;
	}

	public function input_html() {
		$html = '<hr />';

		return $html;
	}
}

torro_register_form_element( 'Torro_Form_Element_Separator' );
