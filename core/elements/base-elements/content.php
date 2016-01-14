<?php
/**
 * Content Form Element
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
		$this->name = 'content';
		$this->title = __( 'Content', 'torro-forms' );
		$this->description = __( 'Adds own content to the form.', 'torro-forms' );
		$this->icon_url = torro()->asset_url( 'icon-text', 'png' );

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

		$html = '<div class="torro-element-content">';
		ob_start();
		wp_editor( $this->label, $editor_id, $settings );
		$html .= ob_get_clean();
		$html .= '</div>';

		return $html;
	}
}

torro()->elements()->add( 'Torro_Form_Element_Content' );
