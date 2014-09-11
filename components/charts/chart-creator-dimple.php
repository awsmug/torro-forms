<?php
/*
 * Shwowing Charts
 *
 * This class shows charts by charts.js
 *
 * @author rheinschmiede.de, Author <kontakt@rheinschmiede.de>
 * @package Surveyval/Data
 * @version 1.0.0
 * @since 1.0.0
 * @license GPL 2
 * 

  Copyright 2013 (kontakt@rheinschmiede.de)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */
 
if ( !defined( 'ABSPATH' ) ) exit;
 
class SurveyVal_ChartCreator_Dimple{
	/**
	 * Initializes the Component.
	 * @since 1.0.0
	 */
	public function __construct() {
	} // end constructor
	
	public static function show_bars( $title, $answers, $attr = array() ){
		$atts = array();
		
		$defaults = array(
			'id' => 'dimple' . md5( rand() ),
 			'width' => 600,
			'height' => 400,
			'title_tag' => 'h3',
		);
		$atts = wp_parse_args( $defaults, $atts );
		
		$id = $atts[ 'id' ];
		$width = $atts[ 'width' ];
		$height = $atts[ 'height' ];
		
		$answer_text = __( 'Answer', 'surveyval-locale' );
		$value_text = __( 'Votes', 'surveyval-locale' );
		
		$data = self::prepare_data( $answers, $answer_text, $value_text );
		
		$js = '';
		
		$js.= 'var svg = dimple.newSvg("#' . $id . '", ' . $width . ', ' . $height . '), data = [ ' . $data . ' ], chart=null, x=null;';
		$js.= 'chart = new dimple.chart( svg, data );';
		
		$js.= 'x = chart.addCategoryAxis("x", "' . $answer_text . '");';
		$js.= 'y = chart.addMeasureAxis("y", "' . $value_text . '");';
		
		$js.= 'chart.addSeries([ "' . $value_text . '", "'  . $answer_text . '" ], dimple.plot.bar);';
		$js.= 'chart.draw();';
		
		$js.= 'x.titleShape.text("' . $title . '");';
		$js.= 'x.titleShape.style( "font-size", "14px");';
		$js.= 'x.titleShape.style( "font-weight", "bold");';
		$js.= 'x.titleShape.style( "padding-top", "30px");';
		
		$html = self::show( $id, $js );
		
		return $html;;
	}

	private static function prepare_data( $answers, $answer_text, $value_text ){
		$rows = array();
		
		foreach( $answers AS $label => $value ):
			$rows[] = '{"' . $answer_text . '" : "' . $label . '", "' . $value_text . '" : ' . $value. '}';
		endforeach;
		
		$data = implode( ',', $rows );
		
		return $data;
	}
	
	private static function show( $id, $chart_js ){
		$html = '';
		
		$html.= '<div id="' . $id . '"></div>';
		$html.= '<script type="text/javascript">';
		$html.= $chart_js;
		$html.= '</script>';
		
		return $html;
	}
}