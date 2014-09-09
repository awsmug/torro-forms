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
 
class SurveyVal_ChartsJS{
	/**
	 * Initializes the Component.
	 * @since 1.0.0
	 */
	public function __construct() {
	} // end constructor
	
	public static function show_bars( $question, $answers ){
		
		echo '<h3>' . $question . '</h3>';
		
		$labels = array();
		$data = array();
		foreach( $answers AS $data_name => $data_count ):
			$labels[] = '"' . utf8_encode( $data_name ) . '"';
			$data[] = $data_count;
		endforeach;
		
		$colors = array();
		$colors[] = 'fillColor: "rgba(220,220,220,0.5)"';
		$colors[] = 'strokeColor: "rgba(220,220,220,0.8)"';
		$colors[] = 'highlightFill: "rgba(220,220,220,0.75)"';
		$colors[] = 'highlightStroke: "rgba(220,220,220,1)"';
		
		$colors_data = implode( ',', $colors );
		
		$js_var_data = 'var data = {labels: [' . implode( ',', $labels ). '], datasets: [{ ' . $colors_data . ', data:[' . implode( ',', $data ) . ']}] };';
		
		self::show_chart( $js_var_data );
	}
	
	public static function show_chart( $data ){
		$id = md5( rand( ) );
		$html.= '<canvas id="' . $id . '" width="400" height="400"></canvas>';
		$html.= '<script language="javascript">';
		$html.= $data;
		$html.= 'var ctx = document.getElementById("' . $id . '").getContext("2d");';
		$html.= 'var myNewChart = new Chart(ctx).Bar( data );';
		$html.= '</script>';
		
		echo $html;
	}
}