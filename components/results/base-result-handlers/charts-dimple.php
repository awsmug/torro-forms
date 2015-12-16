<?php
/**
 * Showing Charts with Dimple.js
 *
 * This class shows charts by Dimple which is based on D3
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

class AF_Chart_Creator_Dimple extends AF_Chart_Creator
{
	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	public function init()
	{
		$this->name = 'dimple';
		$this->title = __( 'Dimple', 'af-locale' );
		$this->description = __( 'Chart creating with dimple.', 'af-locale' );
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

		$answer_text = __( 'Answers', 'af-locale' );
		$value_text = __( 'Votes', 'af-locale' );

		$data = self::prepare_data( $results, $answer_text, $value_text );

		$js = 'var svg = dimple.newSvg("#' . $id . '", "' . $width . '", "' . $height . '"  ), data = [ ' . $data . ' ], chart=null, x=null;';
		$js .= 'chart = new dimple.chart( svg, data );';

		$js .= 'x = chart.addCategoryAxis("x", "' . $answer_text . '");';
		$js .= 'y = chart.addMeasureAxis("y", "' . $value_text . '");';

		$js .= 'x.fontSize = "0.8em";';
		$js .= 'y.fontSize = "0.8em";';

		$js .= 'y.showGridlines = false;';
		$js .= 'y.ticks = 5;';

		$js .= 'var series = chart.addSeries([ "' . $value_text . '", "' . $answer_text . '" ], dimple.plot.bar);';

		// Adding order rule
		$bar_titles = array_keys( $results );
		foreach( $bar_titles AS $key => $bar_title )
		{
			$bar_titles[ $key ] = '"' . $bar_title . '"';
		}
		$bar_titles = implode( ',', $bar_titles );
		$js .= 'x.addOrderRule([' . $bar_titles . ']);';

		$js .= 'chart.draw();';

		// Drawing HTML Containers
		$html = '<div id="' . $id . '" class="af-dimplechart">';
		$html .= '<' . $params[ 'title_tag' ] . '>' . $title . '</' . $params[ 'title_tag' ] . '>';
		$html .= '<script type="text/javascript">';
		$html .= $js;
		$html .= '</script>';
		$html .= '<div style="clear:both;"></div></div>';

		return $html;
	}

	/**
	 * Preparing data for Dimple
	 *
	 * @param $answers
	 * @param $answer_text
	 * @param $value_text
	 *
	 * @return string
	 */
	private static function prepare_data( $answers, $answer_text, $value_text )
	{
		$rows = array();

		foreach( $answers AS $label => $value )
		{
			$rows[] = '{"' . $answer_text . '" : "' . $label . '", "' . $value_text . '" : ' . $value . '}';
		}

		$data = implode( ',', $rows );

		return $data;
	}

	public function pies( $title, $results, $params = array() )
	{
	}

	/**
	 * Loading Admin Scripts
	 */
	public function admin_scripts()
	{
		$d3_script_url = AF_URLPATH . 'assets/vendor/d3.min.js';
		wp_enqueue_script( 'd3', $d3_script_url );

		$dimple_script_url = AF_URLPATH . 'assets/vendor/dimple.min.js';
		wp_enqueue_script( 'dimplejs', $dimple_script_url );
	}

	/**
	 * Loading Frontend Scripts
	 */
	public function frontend_scripts()
	{
		$this->admin_scripts();
	}
}

af_register_chartcreator( 'AF_Chart_Creator_Dimple' );
