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
			
			header( "Pragma: public" );
			header( "Expires: 0" );
			header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
			header( "Cache-Control: private", FALSE );

			switch( $export_type ){
				case 'CSV':
					$content = $this->get_csv( $survey->get_responses_array() );
					$bytes = strlen( $content );
					$charset = 'UTF-8';
					
					header( "Content-Length: " . $bytes );
					header( "Content-Type: Content-Type: text/csv; charset=" . $charset );
					header( "Content-Disposition: attachment; filename=\"" . $export_filename . ".csv\";" );
					
					echo $content;
					
					break;
				default:
					echo $this->get_csv( $survey->get_responses_array() );
					break;
			}
			exit;
			
		endif;
	}
	
	public function get_csv( $response_array ){
		
		$lines = array();
		
		// Running each question (element without separators etc)
		if( is_array( $response_array ) ):
			
			// Getting Headlines
			foreach( $response_array AS $question_id => $question ):
				
				if( $question[ 'sections' ] ):
					foreach( $question[ 'responses' ] AS $response ):
						$i = 0;
						
						foreach( $response AS $key => $value ):
							if( is_array( $value ) ):
								foreach( $value AS $key2 => $key1 ):
									$column = $question[ 'question' ] . ' (' . $key . ' / ' . $key2 . ')';
									$lines[ 0 ][ $question_id . '-' . $i++ ] = $this->filter_csv_output( $column ); 
								endforeach;
							else:	
								$column = $question[ 'question' ] . ' (' . $key . ')';
								$lines[ 0 ][ $question_id . '-' . $i++ ] = $this->filter_csv_output( $column ); 
							endif;
						endforeach;
						
						break;					
					endforeach;

				elseif( $question[ 'array' ] ):
					foreach( $question[ 'responses' ] AS $response ):
						$i = 0;
						foreach( $response AS $key => $value ):
							$column = $question[ 'question' ] . ' (' . $key . ')';
							$lines[ 0 ][ $question_id . '-' . $i++ ] = $this->filter_csv_output( $column ); 
						endforeach;
						break;					
					endforeach;
				else:
					$lines[ 0 ][ $question_id ] = $this->filter_csv_output( $question[ 'question' ] ); 
				endif;
			endforeach;
			
			// Getting Content
			foreach( $response_array AS $question_id => $question ):
				
				if( $question[ 'sections' ] ):
					foreach( $question[ 'responses' ] AS $response_id => $response ):
						$i = 0;
						
						foreach( $response AS $key => $value ):
							if( is_array( $value ) ):
								foreach( $value AS $key2 => $key1 ):
									$lines[ $response_id ][ $question_id . '-' . $i++ ] = $this->filter_csv_output( $key1 ); 
								endforeach;
							else:
								$lines[ $response_id ][ $question_id . '-' . $i++ ] = $this->filter_csv_output( $value ); 
							endif;
						endforeach;
						
					endforeach;
				elseif( $question[ 'array' ] ):
					foreach( $question[ 'responses' ] AS $response ):
						$i = 0;
						foreach( $response AS $key => $value ):
							$lines[ $response_id ][ $question_id . '-' . $i++ ] = $this->filter_csv_output( $value ); 
						endforeach;
					endforeach;
				else:
					foreach( $question[ 'responses' ]  AS $response_id => $value ):
						$lines[ $response_id ][ $question_id ] = $this->filter_csv_output( $value ); 
					endforeach;
				endif;
				
			endforeach;
			
			$output = '';
			foreach( $lines AS $response_id => $line ):
				$output.= implode( ';', $line ) . chr( 13 );
			endforeach;
			
			return $output;			
		else:
			return FALSE;
		endif;
	}

	private function filter_csv_output( $string ){
		$string = utf8_decode( $string );
		if( '' ==  $string )
			return '-';
		
		$string = $this->remove_new_lines( $string );
		$string = str_replace( ';', '#', $string );
		
		return $string;
	}

	private function remove_new_lines( $string ){
		return trim( preg_replace( '/\s\s+/', ' ', $string ) );
	}
}
$SurveyVal_Export = new SurveyVal_Export();
