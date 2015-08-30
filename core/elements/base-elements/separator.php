<?php
/**
 * Separator Form Element
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package AwesomeForms/Core/Elements
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

// No direct access is allowed
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AF_FormElement_Separator extends AF_FormElement {

	public function __construct( $id = NULL ) {

		$this->name        = 'Separator';
		$this->title       = esc_attr__( 'Separator', 'af-locale' );
		$this->description = esc_attr__( 'Adds a optical separator (<hr>) between questions.', 'af-locale' );
		$this->icon_url        = QUESTIONS_URLPATH . '/assets/images/icon-separator.png';

		$this->is_question = FALSE;

		parent::__construct( $id );
	}

	public function input_html() {

		$html = '<div class="survey-element survey-element-' . $this->id . '">';

		if ( ! empty( $this->settings[ 'header' ] ) ) {
			$html .= '<h3>' . $this->settings[ 'header' ] . '</h3>';
		}

		$html .= '<hr /></div>';

		return $html;
	}

	public function settings_fields() {

		$this->settings_fields = array(
			'header' => array(
				'title'       => esc_attr__( 'Headline', 'af-locale' ),
				'type'        => 'textarea',
				'description' => esc_attr__( 'Text which will be shown above the separator', 'af-locale' ),
				'default'     => ''
			)
		);
	}
}

af_register_survey_element( 'AF_FormElement_Separator' );
