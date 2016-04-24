<?php

/**
 * Adds form Templatetags
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

final class Torro_Templatetags_Form extends Torro_TemplateTags {
	/**
	 * Instance
	 *
	 * @var null|Torro_Templatetags_Form
	 * @since 1.0.0
	 */
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();
	}

	protected function init() {
		$this->title = __( 'Form', 'torro-forms' );
		$this->name = 'formtags';
		$this->description = __( 'Form Templatetags', 'torro-forms' );
	}

	/**
	 * Adding all tags of class
	 */
	public function tags() {
		$this->add_tag( 'formtitle', __( 'Form Title', 'torro-forms' ), __( 'Shows the Form Title', 'torro-forms' ), array( $this, 'formtitle' ) );
		$this->add_tag( 'allelements', __( 'All Elements', 'torro-forms' ), __( 'Shows all Answers', 'torro-forms' ), array( $this, 'allelements' ) );
	}

	/**
	 * %sitename%
	 */
	public function formtitle() {
		$form = torro()->forms()->get_current();
		if ( is_wp_error( $form ) ) {
			return '';
		}

		return $form->title;
	}

	/**
	 * Adding Element on the fly to taglist
	 * @param $element_id
	 * @param $element_name
	 */
	public function add_element( $element_id, $element_name ) {
		$this->add_tag( $element_name . ':' . $element_id, $element_name, __( 'Adds the Element Content', 'torro-forms' ), array( $this, 'element_content' ), array( 'element_id' => $element_id ) );
	}

	/**
	 * Shows the Element content
	 *
	 * @param $element_id
	 */
	public function element_content( $element_id ) {
		global $torro_response;

		if ( ! isset( $torro_response[ 'container_id' ] ) ) {
			return;
		}

		$container_id = $torro_response[ 'container_id' ];

		if ( ! isset( $torro_response[ 'containers' ][ $container_id ][ 'elements' ][ $element_id ] ) ) {
			return;
		}

		$value = $torro_response[ 'containers' ][ $container_id ][ 'elements' ][ $element_id ];
		$element = torro()->elements()->get( $element_id );

		/**
		 * Displaying elements
		 */
		if ( count( $element->sections ) > 0 ) {
			/**
			 * Elements with sections
			 */
			// @todo Checking if element had sections and giving them HTML > Try with Matrix

		} elseif ( is_array( $value ) ) {
			/**
			 * Elements with multiple answers
			 */
			$html = '<ul>';
			foreach ( $value as $response ) {
				$html .= '<li>' . $response . '</li>';
			}
			$html .= '</ul>';

			return $html;
		} else {
			/**
			 * Elements with string response value
			 */

			$value_new = $element->replace_column_value( $value );
			if ( $value_new || is_string( $value_new ) ) {
				$value = $value_new;
			}
			
			return $value;
		}
	}

	/**
	 * Shows the Element content
	 * @param $element_id
	 */
	public function allelements() {
		global $torro_response;

		$form = torro()->forms()->get_current();
		if ( is_wp_error( $form ) ) {
			return '';
		}

		$html = '<table style="width:100%;">';
		foreach ( $form->elements as $element ) {
			$html .= '<tr>';
			$html .= '<td>' . $element->label . '</td>';
			$html .= '<td>' . self::element_content( $element->id ) . '</td>';
			$html .= '</tr>';
		}
		$html .= '</table>';

		return $html;
	}
}

torro()->templatetags()->register( 'Torro_Templatetags_Form' );
