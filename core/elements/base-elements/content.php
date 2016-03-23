<?php
/**
 * Content Form Element
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core/Elements
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

final class Torro_Form_Element_Content extends Torro_Form_Element {
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
		$this->type = $this->name = 'content';
		$this->title = __( 'Content', 'torro-forms' );
		$this->description = __( 'Adds own content to the form.', 'torro-forms' );
		$this->icon_url = torro()->get_asset_url( 'icon-text', 'png' );

		$this->input = false;
	}

	public function input_html() {
		return wpautop( $this->label );
	}

	public function admin_content_html() {
		$element_id = $this->get_admin_element_id();
		$container_id = $this->get_admin_cotainer_id();
		$name = $this->get_admin_input_name();

		$html = '<div class="torro-element-content">';

		$editor_id = 'wp_editor_' . $element_id;
		$settings = array(
			'textarea_name' => $name . '[label]',
		);

		ob_start();
		wp_editor( $this->label, $editor_id, $settings );
		$html .= ob_get_clean();

		$html .= '</div>';

		return $html;
	}
}

torro()->elements()->register( 'Torro_Form_Element_Content' );
