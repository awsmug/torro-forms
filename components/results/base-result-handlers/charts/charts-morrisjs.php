<?php
/**
 * Showing Charts with morris.js
 *
 * This class shows charts by morris.js
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

class AF_Chart_Creator_Morrisjs extends AF_Chart_Creator
{
	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	public function init()
	{
		$this->name = 'morrisjs';
		$this->title = __( 'morris.js', 'af-locale' );
		$this->description = __( 'Chart creating with morris.js.', 'af-locale' );
	}

	/**
	 * Showing bars
	 *
	 * @param string $title Title
	 * @param array  $answers
	 * @param array  $attr
	 *
	 * @return mixed
	 */
	public function bars( $title, $results, $params = array() )
	{
		$defaults = array(
			'id'        => 'dimple' . md5( rand() ),
			'width'     => '500',
			'height'    => '400',
			'title_tag' => 'h3',
		);

		$params = wp_parse_args( $defaults, $params );

		$id = $params[ 'id' ];
		$width = $params[ 'width' ];
		$height = $params[ 'height' ];

		$data = array();
		foreach( $results AS $label => $value )
		{
			$data[] = array(
				'label' => $label,
				'value' => $value
			);
		}

		$json_data = json_encode( $data );

		// $html.= p( $results, TRUE );

		$html.= '<div id="' . $id . '" class="morris-chart"></div>';

		$html.= "<script>
			        jQuery(document).ready( function($){
					    Morris.Bar({
						  element: '{$id}',
						  data: {$json_data},
						  xkey: 'label',
						  ykeys: ['value'],
						  labels: ['Value'],
						  stacked: true,
						  xLabelAngle: 20,
						  resize: true,
						  hideHover: 'auto'
						});
					});
			    </script>";

		return $html;
	}

	public function pies( $title, $results, $params = array() )
	{
	}

	/**
	 * Loading Admin Styles
	 */
	public function admin_styles()
	{
		$morris_css = AF_URLPATH . 'components/results/base-result-handlers/charts/includes/css/morris.css';
		wp_enqueue_style( 'af-morrisjs-css', $morris_css );
	}

	/**
	 * Loading Admin Scripts
	 */
	public function frontend_styles()
	{
		$this->admin_styles();
	}



	/**
	 * Loading Admin Scripts
	 */
	public function admin_scripts()
	{
		wp_enqueue_script( 'jquery' );

		$raphael_js_url = AF_URLPATH . 'components/results/base-result-handlers/charts/includes/js/raphael.min.js';
		wp_enqueue_script( 'af-raphaeljs', $raphael_js_url );

		$morris_js_url = AF_URLPATH . 'components/results/base-result-handlers/charts/includes/js/morris.min.js';
		wp_enqueue_script( 'af-morrisjs', $morris_js_url );
	}

	/**
	 * Loading Frontend Scripts
	 */
	public function frontend_scripts()
	{
		$this->admin_scripts();
	}
}

af_register_chartcreator( 'AF_Chart_Creator_Morrisjs' );