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
		$html = '';
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
		
		$rows = array();
		
		$answer_text = __( 'Answer', 'surveyval-locale' );
		$value_text = __( 'Votes', 'surveyval-locale' );
		
		foreach( $answers AS $label => $value ):
			$rows[] = '{"' . $answer_text . '" : "' . $label . '", "' . $value_text . '" : ' . $value. '}';
		endforeach;
		
		$data = implode( ',', $rows );
		
		$html.= '<div id="' . $id . '"></div>';
		
		$html.= '<script type="text/javascript">' . chr(13);
		
		$html.= 'var svg = dimple.newSvg("#' . $id . '", ' . $width . ', ' . $height . '), data = [ ' . $data . ' ], chart=null, x=null;';
		$html.= 'chart = new dimple.chart( svg, data );';
		
		$html.= 'x = chart.addCategoryAxis("x", "' . $answer_text . '");';
		$html.= 'y = chart.addMeasureAxis("y", "' . $value_text . '");';
		
		$html.= 'chart.addSeries([ "' . $value_text . '", "'  . $answer_text . '" ], dimple.plot.bar);';
		$html.= 'chart.draw();' . chr(13);
		
		$html.= 'x.titleShape.text("' . $title . '");';
		$html.= 'x.titleShape.style( "font-size", "14px");';
		$html.= 'x.titleShape.style( "font-weight", "bold");';
		$html.= 'x.titleShape.style( "padding-top", "30px");';
		
		$html.= '</script>';
		return $html;;
	}
	
	private static function show( $chart_id, $title, $title_tag, $answers, $type, $script = '' ){
		$html = '';
		
		$html.= '<div class="' . $chart_id . '"></div>';
		$html.= '<script type="text/javascript">' . chr(13);
		$html.= '<!--' . chr( 13 );
		$html.= 'var chart = c3.generate({
			 bindto: \'.'. $chart_id . '\',
			 data: { columns: [ ' . implode( ',', $columns )  .' ], type: "' . $type . '" }
			 ' . $script . '
		});';
		$html.= chr( 13 ) . '//-->' . chr( 13 );
		$html.= '</script>';
		
		return $html;
	}
	
	/*
	public static function show_pie( $title, $answers, $attr = array() ){
		$html = '';
		
		$defaults = array(
			'id' => substr( md5( rand() ), 2, 6 ),
 			'width' => 600,
			'height' => 400,
			'title_tag' => 'h3',
		);
		$atts = wp_parse_args( $defaults, $atts );
		
		$html.= self::show( $atts[ 'id' ], $title, $atts[ 'title_tag' ], $answers, 'pie' );
		
		return $html;;
	}
	 */
}