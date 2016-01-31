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

final class Torro_Form_Element_Splitter extends Torro_Form_Element {
	private static $instances = array();

	public static function instance( $id = null ) {
		$slug = $id;
		if ( null === $slug ) {
			$slug = 'CLASS';
		}
		if ( ! isset( self::$instances[ $slug ] ) ) {
			self::$instances[ $slug ] = new self( $id );
		}
		return self::$instances[ $slug ];
	}

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
		$this->icon_url = torro()->get_asset_url( 'icon-split-form', 'png' );

		$this->splits_form = true;
		$this->is_answerable = false;
	}
}

torro()->elements()->register( 'Torro_Form_Element_Splitter' );






