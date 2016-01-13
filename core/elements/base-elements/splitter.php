<?php
/**
 * Splitter Form Element
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

class Torro_Form_Element_Splitter extends Torro_Form_Element {
	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct( $id = null ) {
		parent::__construct( $id );
	}

	protected function init() {
		$this->name = 'splitter';
		$this->title = __( 'Split Form', 'torro-forms' );
		$this->description = __( 'Splits a form into several steps', 'torro-forms' );
		$this->icon_url = torro()->asset_url( 'icon-split-form', 'png' );

		$this->has_content = false;
		$this->splits_form = true;
		$this->is_answerable = false;
	}
}

torro()->elements()->add( 'Torro_Form_Element_Splitter' );






