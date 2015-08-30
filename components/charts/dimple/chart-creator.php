<?php
/**
 * Showing Charts with charts.js
 *
 * This class shows charts by charts.js
 *
 * @author awesome.ug, Author <support@awesome.ug>
 * @package AwesomeForms/Core
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

class AF_ChartCreator_Dimple extends AF_ChartCreator{
    /**
     * Initializes the Component.
     * @since 1.0.0
     */
    public function __construct() {
        parent::__construct(
            'Dimple chart creator',
            'Creates charts with D3 Dimple library',
            'dimple'
        );
    }

    /**
     * Showing bars
     * @param string $title Title
     * @param array $answers
     * @param array $attr
     * @return mixed
     */
    public static function show_bars( $title, $answers, $attr = array() ){
        $atts = array();

        $defaults = array(
            'id' => 'dimple' . md5( rand() ),
            'width' => '100%',
            'height' => '100%',
            'title_tag' => 'h3',
        );
        $atts = wp_parse_args( $defaults, $atts );

        $id = $atts[ 'id' ];
        $width = $atts[ 'width' ];
        $height = $atts[ 'height' ];

        $answer_text = __( 'Answers', 'af-locale' );
        $value_text = __( 'Votes', 'af-locale' );

        $data = self::prepare_data( $answers, $answer_text, $value_text );

        $js = 'var svg = dimple.newSvg("#' . $id . '", "' . $width . '", "' . $height . '"  ), data = [ ' . $data . ' ], chart=null, x=null;';
        $js.= 'chart = new dimple.chart( svg, data );';

        $js.= 'x = chart.addCategoryAxis("x", "' . $answer_text . '");';
        $js.= 'y = chart.addMeasureAxis("y", "' . $value_text . '");';

        $js.= 'x.fontSize = "0.8em";';
        $js.= 'y.fontSize = "0.8em";';

        $js.= 'y.showGridlines = false;';
        $js.= 'y.ticks = 5;';

        $js.= 'var series = chart.addSeries([ "' . $value_text . '", "'  . $answer_text . '" ], dimple.plot.bar);';

        // Adding order rule
        $bar_titles = array_keys( $answers );
        foreach( $bar_titles AS $key => $bar_title ){
            $bar_titles[ $key ] = '"' . $bar_title . '"';
        }
        $bar_titles = implode(',', $bar_titles );
        $js.= 'x.addOrderRule([' . $bar_titles . ']);';

        $js.= 'chart.draw();';

        // Autosize charts
        $js.= 'jQuery( function ($) { ';
        $js.= 'var gcontainer = $( "#' . $id . ' g" );';
        $js.= 'var grect = gcontainer[0].getBoundingClientRect();';
        $js.= '$( "#' . $id . ' svg" ).height( grect.height + 15 );';
        $js.= '});';

        // Drawing HTML Containers
        $html = '<div id="' . $id . '" class="questions-dimplechart">';
        $html.= '<' . $atts[ 'title_tag' ] . '>' . $title . '</' . $atts[ 'title_tag' ] . '>';
        $html.= '<script type="text/javascript">';
        $html.= $js;
        $html.= '</script>';
        $html.= '<div style="clear:both;"></div></div>';

        return $html;
    }

    /**
     * Preparing data for Dimple
     * @param $answers
     * @param $answer_text
     * @param $value_text
     * @return string
     */
    private static function prepare_data( $answers, $answer_text, $value_text ){
        $rows = array();

        foreach( $answers AS $label => $value ):
            $rows[] = '{"' . $answer_text . '" : "' . $label . '", "' . $value_text . '" : ' . $value. '}';
        endforeach;

        $data = implode( ',', $rows );

        return $data;
    }

    /**
     * Loading Scripts
     */
    public function load_scripts(){
        wp_enqueue_script( 'questions-d3-js',  AF_URLPATH . '/components/charts/dimple/lib/d3.min.js' );
        wp_enqueue_script( 'questions-dimple-js',  AF_URLPATH . '/components/charts/dimple/lib/dimple.v2.1.2.min.js' );
    }
}
af_register_chartcreator( 'AF_ChartCreator_Dimple' );