<?php
/*
 * Shwowing Charts
 *
 * This class shows charts by charts.js
 *
 * @author rheinschmiede.de, Author <kontakt@rheinschmiede.de>
 * @package Questions/Data
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
 
class Questions_ChartCreator{
	/**
	 * Initializes the Component.
	 * @since 1.0.0
	 */
	public function __construct() {
	} // end constructor
	
	public static function show_bars( $title, $answers, $attr = array() ){
		$html = '';
		
		$defaults = array(
			'id' => md5( rand() ),
 			'width' => 600,
			'height' => 400,
			'title_tag' => 'h3',
		);
		$atts = wp_parse_args( $defaults, $atts );
		
		$html.= self::show( $atts[ 'id' ], $title, $atts[ 'title_tag' ], $answers, 'bar' );
		
		return $html;;
	}
	
	public static function show_pie( $title, $answers, $attr = array() ){
		$html = '';
		
		$defaults = array(
			'id' => md5( rand() ),
 			'width' => 600,
			'height' => 400,
			'title_tag' => 'h3',
		);
		$atts = wp_parse_args( $defaults, $atts );
		
		$html.= self::show( $atts[ 'id' ], $title, $atts[ 'title_tag' ], $answers, 'pie' );
		
		return $html;;
	}
	
	private static function show( $chart_id, $title, $title_tag, $answers, $type, $script = '' ){
		$html = '';
		
		$columns = array();
		foreach( $answers AS $label => $value ):
			$columns[] = '[ "' . $label . '", ' . $value . ' ]';
		endforeach;
		
		if( !empty( $title ) )
			$html.= '<' . $title_tag . '>' . $title . '</' . $title_tag . '>' ;
		
		$html.= '<div id="' . $chart_id . '"></div>';
		$html.= '<script>';
		$html.= 'var chart = c3.generate({
			 bindto: \'#'. $chart_id . '\',
			 data: { columns: [ ' . implode( ',', $columns )  .' ], type: "' . $type . '" },
			 axis: {
			 	x: {
			 		label: { text: \'' . __( 'Made with Questions', 'questions-locale' ) . '\', position: \'outer-middle\' }
				} 
			 }
			 ' . $script . '
		});';
		$html.= '</script>';
		
		return $html;
	}
}