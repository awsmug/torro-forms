<?php
/*
 * Exporting data
 *
 * This class creates the export
 *
 * @author rheinschmiede.de, Author <kontakt@rheinschmiede.de>
 * @package PluginName/Admin
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
 
class SurveyVal_Export{
	/**
	 * Initializes the Component.
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'export' ), 10 );
		add_filter( 'post_row_actions', array( $this, 'add_export_link' ), 10, 2 );
	} // end constructor
	
	function add_export_link( $actions, $post ){
		if( 'surveyval' != $post->post_type )
			return $actions;
		
		$actions['export_results'] = sprintf( __( '<a href="%s">Export Results</a>', 'surveyval-locale' ), '?post_type=surveyval&export_survey_results=CSV&survey_id=' . $post->ID );
		
		return $actions;
	}
	
	function export(){
		global $wpdb, $surveyval_global;
		
		if( array_key_exists( 'export_survey_results', $_GET ) && is_array( $_GET ) ):
			$export_type = $_GET['export_survey_results'];
			$survey_id = $_GET['survey_id'];
			
			$survey = new SurveyVal_Survey( $survey_id );
			
			$export_filename = sanitize_title( $survey->title );
			
			header( "Pragma: public");
			header( "Expires: 0");
			header( "Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header( "Cache-Control: private", FALSE );
			header( "Content-Type: application/octet-stream");
			header( "Content-Disposition: attachment; filename=\"" . $export_filename . ".csv\";" );
			header( "Content-Transfer-Encoding: binary" );
			
			switch( $export_type ){
				case 'CSV':
					echo $this->get_csv( $survey );
					break;
				default:
					break;
			}
			
			exit;
			
		endif;
	}
	
	public function get_csv( $survey ){
		return $this->get_csv_headline( $survey ) . chr(13) . $this->get_csv_results( $survey );
	}

	public function get_csv_headline( $survey ){
		$headline = 'UserID;';
		
		foreach( $survey->elements AS $element ):
			if( $element->is_question )
				$headline.= $element->question . ';';
		endforeach;
		
		return $headline;
	}

	public function get_csv_results( $survey ){
		global $wpdb, $surveyval_global;
		
		$sql = $wpdb->prepare( "SELECT * FROM {$surveyval_global->tables->responds} WHERE surveyval_id=%d", $survey->id );
		$results = $wpdb->get_results( $sql );
		
		foreach( $results AS $result ):
			// Running each element
			$line = $result->user_id . ';';
			
			foreach( $survey->elements AS $element ):
				if( $element->is_question ):
					$line.= $this->get_csv_answer( $element->id, $result->id ) . ';';
				endif;
			endforeach;
			
			$content.= $line . chr(13);
			
		endforeach;
		
		return $content;
	}

	public function get_csv_answer( $question_id, $respond_id ){
		global $wpdb, $surveyval_global;
		
		$sql = $wpdb->prepare( "SELECT * FROM {$surveyval_global->tables->respond_answers} WHERE question_id=%d AND respond_id=%d", $question_id, $respond_id );
		$answers = $wpdb->get_results( $sql );
		
		$counter = 1;
		$count_answers = count( $answers );
		$answers_text = '';
		
		foreach( $answers AS $answer ):
			if( $counter < $count_answers ):
				$answers_text.=$answer->value. '|';
			else:
				$answers_text.=$answer->value;
			endif;
			$counter++;
		endforeach;
		
		return $answers_text;
	}
	
	
}
$SurveyVal_Export = new SurveyVal_Export();
