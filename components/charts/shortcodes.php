<?php

function test_barchart( $atts ){
	$attributes = shortcode_atts( 
		array(
        	'id' => 0,
    	), 
    $atts );
	
	$survey = new SurveyVal_Survey( $attributes[ 'id' ] );
	$export_filename = sanitize_title( $survey->title );
			
	$prepared_data = SurveyVal_AbstractData::order_for_charting( $survey->get_responses_array() );
	
	foreach ( $prepared_data[ 'questions' ] as $question_id => $question ):
		echo SurveyVal_ChartCreator::show_pie( $question, $prepared_data['data'][ $question_id ] );
	endforeach;
}
add_shortcode( 'barchart', 'test_barchart' );