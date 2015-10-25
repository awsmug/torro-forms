<?php
/**
 * Charts Result Handler
 *
 * Adds Charts Result Handler
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package AwesomeForms/Results
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

if( !defined( 'ABSPATH' ) )
{
	exit;
}

class AF_ResultCharts extends AF_ResultHandler
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $post;

		$this->title = __( 'Charts', 'af-locale' );
		$this->name = 'charts';

		add_action( 'admin_print_styles', array( __CLASS__, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_scripts' ) );
		add_action( 'form_results_content_bottom', array( $this, 'show_results_after_submitting_fields' ), 10 );

		$this->settings_fields = array(
			'invitations'        => array(
				'title'       => esc_attr( 'Test', 'af-locale' ),
				'description' => esc_attr( 'Test XXX', 'af-locale' ),
				'type'        => 'text'
			),
		);
	}

	public function option_content()
	{
		return 'Charts are coming up!';
	}

	public function show_results_after_submitting_fields()
	{
		global $post;

		$form_id = $post->ID;
		$show_results = get_post_meta( $form_id, 'show_results', TRUE );

		if( '' == $show_results )
		{
			$show_results = 'no';
		}

		$checked_no = '';
		$checked_yes = '';

		if( 'no' == $show_results )
		{
			$checked_no = ' checked="checked"';
		}
		else
		{
			$checked_yes = ' checked="checked"';
		}

		$html = '<div class="form-options section general-settings">';
		$html .= '<table>';
		$html .= '<tr>';
		$html .= '<td><label for="show_results">' . esc_attr__( 'After finish Form:', 'af-locale' ) . '</label></td>';
		$html .= '<td>';
		$html .= '<input type="radio" name="show_results" value="yes"' . $checked_yes . '>' . esc_attr__( 'Show Charts' ) . '<br /> ';
		$html .= '<input type="radio" name="show_results" value="no"' . $checked_no . '>' . esc_attr__( 'Do not Show Charts' ) . '';
		$html .= '</td>';
		$html .= '</tr>';
		$html .= '</table>';
		$html .= '</div>';

		echo $html;
	}

	/**
	 * Enqueue admin scripts
	 */
	public static function enqueue_admin_scripts()
	{
		if( !af_is_formbuilder() )
			return;
	}

	/**
	 * Enqueue admin styles
	 */
	public static function enqueue_admin_styles()
	{
	}
}
af_register_result_handler( 'AF_ResultCharts' );