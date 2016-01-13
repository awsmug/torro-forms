<?php

/**
 * Adds form Templatetags
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
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

class Torro_FormTemplateTags extends Torro_TemplateTags {
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
		global $ar_form_id;

		$form = new Torro_Form( $ar_form_id );

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

		if ( ! isset( $torro_response[ $element_id ] ) ) {
			return;
		}

		$element = torro()->elements()->get( $element_id );

		/**
		 * Displaying elements
		 */
		if ( 0 < count( $element->sections ) ) {
			/**
			 * Elements with sections
			 */
			// @todo Checking if element had sections and giving them HTML > Try with Matrix

		} elseif ( is_array( $torro_response[ $element_id ] ) ) {
			/**
			 * Elements with multiple answers
			 */
			$html = '<ul>';
			foreach ( $torro_response[ $element_id ] as $response ) {
				$html .= '<li>' . $response . '</li>';
			}
			$html .= '</ul>';

			return $html;
		} else {
			/**
			 * Elements with string response value
			 */
			return $torro_response[ $element_id ];
		}
	}

	/**
	 * Shows the Element content
	 * @param $element_id
	 */
	public function allelements() {
		global $ar_form_id, $torro_response;

		$form = new Torro_Form( $ar_form_id );

		$html = '<table style="width:100%;">';
		foreach ( $form->get_elements() as $element ) {
			$html .= '<tr>';
			$html .= '<td>' . $element->label . '</td>';
			$html .= '<td>' . self::element_content( $element->id ) . '</td>';
			$html .= '</tr>';
		}
		$html .= '</table>';

		return $html;
	}
}

torro()->templatetags()->add( 'Torro_FormTemplateTags' );

/**
 * Live registering element templatetags
 *
 * @param $element_id
 * @param $element_name
 *
 * @return bool
 */
function torro_add_element_templatetag( $element_id, $element_name ) {
	$formtags = torro()->templatetags()->get( 'formtags' );
	if ( ! $formtags ) {
		return false;
	}

	return $formtags->add_element( $element_id, $element_name );
}
