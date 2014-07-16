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
			
			//echo $this->get_csv( $survey->get_responses_array() );
			// exit;
			
			header( "Pragma: public");
			header( "Expires: 0");
			header( "Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header( "Cache-Control: private", FALSE );
			header( "Content-Type: Content-Type: text/html; charset=UTF-8");
			header( "Content-Disposition: attachment; filename=\"" . $export_filename . ".csv\";" );
			
			switch( $export_type ){
				case 'CSV':
					echo $this->get_csv( $survey->get_responses_array() );
					break;
				default:
					echo $this->get_csv( $survey->get_responses_array() );
					break;
			}
			
			exit;
			
		endif;
	}
	
	public function get_csv( $response_array ){
		/*echo '<pre>';
		print_r( $response_array );
		echo '</pre>';
		*/
		$lines = array();
		
		// Running each question (element without separators etc)
		if( is_array( $response_array ) && count( $response_array ) > 0 ):
			
			// Getting Headlines
			foreach( $response_array AS $question_id => $question ):
				if( $question[ 'sections' ] ):
					foreach( $question[ 'responses' ] AS $response ):
						$i = 0;
						
						foreach( $response AS $key => $values ):
							foreach( $values AS $key2 => $value):
								$lines[ 0 ][ $question_id . '-' . $i++ ] = $question[ 'question' ] . '(' . $key . ' / ' . $key2 . ')'; 
							endforeach;
						endforeach;
						
						break;					
					endforeach;
				elseif( $question[ 'array' ] ):
					foreach( $question[ 'responses' ] AS $response ):
						$i = 0;
						foreach( $response AS $key => $value ):
							$lines[ 0 ][ $question_id . '-' . $i++ ] = $question[ 'question' ] . '(' . $key . ')'; 
						endforeach;
						break;					
					endforeach;
				else:
					$lines[ 0 ][ $question_id ] = $question[ 'question' ]; 
				endif;
			endforeach;
			
			// Getting Content	
			foreach( $response_array AS $question_id => $question ):
				if( $question[ 'sections' ] ):
					foreach( $question[ 'responses' ] AS $response_id => $response ):
						$i = 0;
						
						foreach( $response AS $key => $values ):
							foreach( $values AS $key2 => $value):
								$lines[ $response_id ][ $question_id . '-' . $i++ ] = $this->remove_new_lines( $value ); 
							endforeach;
						endforeach;
						
					endforeach;
				elseif( $question[ 'array' ] ):
					foreach( $question[ 'responses' ] AS $response ):
						$i = 0;
						foreach( $response AS $key => $value ):
							$lines[ $response_id ][ $question_id . '-' . $i++ ] = $this->remove_new_lines( $value ); 
						endforeach;
					endforeach;
				else:
					foreach( $question[ 'responses' ]  AS $response_id => $value ):
						$lines[ $response_id ][ $question_id ] = $this->remove_new_lines( $value ); 
					endforeach;
				endif;
				
			endforeach;
			
			$output = '';
			foreach( $lines AS $response_id => $line ):
				$output.= implode( ';', $line ) . chr( 13 );
			endforeach;
			
			return $output;			
			
			echo '<pre>';
			print_r( $output );
			echo '</pre>';
		else:
			return FALSE;
		endif;
	}

	function remove_new_lines( $string ){
		return trim( preg_replace( '/\s\s+/', ' ', $string ) );
	}
}
$SurveyVal_Export = new SurveyVal_Export();
