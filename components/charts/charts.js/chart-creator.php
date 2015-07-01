<?php
/**
 * Showing Charts with charts.js
 *
 * This class shows charts by charts.js
 *
 * @author awesome.ug, Author <support@awesome.ug>
 * @package Questions/Core
 * @version 1.0.0
 * @since 1.0.0
 * @license GPL 2

  Copyright 2015 awesome.ug (support@awesome.ug)

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

class Questions_ChartCreator_Chartsjs extends Questions_ChartCreator{
    /**
     * Initializes the Component.
     * @since 1.0.0
     */
    public function __construct() {
        parent::__construct(
            'Charts.js chart creator',
            'Creates charts with charts.js library',
            'chartsjs'
        );
    } // end constructor

    /**
     * Showing bars
     * @param string $title Title
     * @param array $answers
     * @param array $attr
     * @return mixed
     */
    public static function show_bars( $title, $answers, $attr = array() ){
        $id = qu_id();

        // Setting up Barchart
        $chart_width = 500;
        $chart_height = 500;

        $fill_color = 'rgba(151,187,205,0.5)';
        $stroke_color = 'rgba(151,187,205,0.8)';

        $html = '<h4>' . $title . '</h4>';
        $html.= '<canvas id="' . $id . '" width="' . $chart_width . '" height="' . $chart_height . '"></canvas>';

        // Preparing labels
        $labels = array_keys( $answers );
        foreach( $labels AS $key => $label ){ // Adding "
            $labels[ $key ] = '"' . $label . '"';
        }
        $labels = implode( ',', $labels );

        // Preparing values
        $values = array_values( $answers );
        $values = implode( ',', $values );

        // Creating javascript
        $html.= '<script language="JavaScript">';
        $html.= 'var data = {
                    labels: [' . $labels . '],
                    datasets: [
                        {
                            label: "' . $title . '",
                            fillColor: "' . $fill_color . '",
                            strokeColor: "' . $stroke_color . '",
                            highlightFill: "rgba(220,220,220,0.75)",
                            highlightStroke: "rgba(220,220,220,1)",
                            data: [' . $values . ']
                        }
                    ]
                };';

        $html.= 'var options = {
                        scaleShowHorizontalLines: false,
                        scaleShowVerticalLines: false,
                        barShowStroke: true,
                };';

        $html.= 'var context = document.getElementById("' . $id . '").getContext("2d");';
        $html.= 'var barchart_' . $id . ' = new Chart(context).Bar(data, options);';

        $html.= '</script>';
        return $html;
    }

    /**
     * Showing pies
     * @param string $title Title
     * @param array $answers
     * @param array $attr
     * @return mixed
     */
    public static function show_pie( $title, $answers, $attr = array() )
    {
        $id = qu_id();

        $standard_colors = array( '#F00', '#FF0', '#0F0', '#282E33', '#25373A', '#164852', '#495E67', '#FF3838' );

        // Setting up Barchart
        $chart_width = 500;
        $chart_height = 500;

        $datasets = array();

        $html = '<h4>' . $title . '</h4>';
        $html.= '<canvas id="' . $id . '" width="' . $chart_width . '" height="' . $chart_height . '"></canvas>';

        // Creating javascript
        $i = 0;
        $html.= '<script language="JavaScript">';
        $html.= 'var data = [';
        foreach( $answers AS $key => $answer ){
            $data = '{';
            $data.= 'value: ' . $answer . ',';
            $data.= 'label: "' . $key . '",';
            $data.= 'color: "' . $standard_colors[ $i++ ] . '",';
            $data.= '}';
            $datasets[] = $data;
        }
        $html.= implode( ',', $datasets );
        $html.= '];';

        $html.= 'var options = {
                        animateRotate: true,
                        legendTemplate : "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>"
                };';

        $html.= 'var context = document.getElementById("' . $id . '").getContext("2d");';
        $html.= 'var piechart_' . $id . ' = new Chart(context).Pie(data, options);';

        $html.= '</script>';
        return $html;
    }
    /**
     * Loading Scripts
     */
    public function load_scripts(){
        wp_enqueue_script( 'questions-chartsjs-js',  QUESTIONS_URLPATH . '/components/charts/charts.js/lib/chart.js' );
    }
}
qu_register_chart_creator( 'Questions_ChartCreator_Chartsjs' );