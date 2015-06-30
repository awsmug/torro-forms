<?php
/*
 * Exporting data
 *
 * This class creates the export
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
 
class Questions_Export{
	
	/**
	 * Initializes the Component.
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'export' ), 10 );
		add_filter( 'post_row_actions', array( $this, 'add_export_link' ), 10, 2 );
	} // end constructor
	
	/**
	 * Add export link to the overview page
	 * @param array $actions Actions links in an array
	 * @param object $post Post object
	 * @since 1.0.0
	 */
	function add_export_link( $actions, $post ){
		if( 'questions' != $post->post_type )
			return $actions;
		
		$actions['export_results'] = sprintf( __( '<a href="%s">Export Results</a>', 'questions-locale' ), '?post_type=questions&export_survey_results=CSV&survey_id=' . $post->ID );
		
		return $actions;
	}
	
	/**
	 * Start exporting by evaluating $_GET variables
	 * @since 1.0.0
	 */
	function export(){
		global $wpdb, $questions_global;
		
		if( array_key_exists( 'export_survey_results', $_GET ) && is_array( $_GET ) ):
			$export_type = $_GET['export_survey_results'];
			$survey_id = $_GET['survey_id'];
			
			$survey = new Questions_Survey( $survey_id );
			$export_filename = sanitize_title( $survey->title );
			$export_data = $survey->get_responses();
			
			header( "Pragma: public" );
			header( "Expires: 0" );
			header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
			header( "Cache-Control: private", FALSE );

			switch( $export_type ){
				case 'CSV':
					$content = $this->get_csv( $export_data );
					$bytes = strlen( $content );
					$charset = get_bloginfo( 'blog_charset' );
					
					header( "Content-Length: " . $bytes );
					header( "Content-Type: Content-Type: text/csv; charset=" . $charset );
					header( "Content-Disposition: attachment; filename=\"" . $export_filename . ".csv\";" );
					
					echo $content;
					
					break;
				default:
					echo $this->get_csv( $survey->get_responses() );
					break;
			}
			exit;
			
		endif;
	}
	
	/**
	 * Getting CSV content
	 * @param array $response_array Response array of a survey
	 * @return string $output CSV content
	 */
	public function get_csv( $response_array ){
		
		$headlines = Questions_AbstractData::get_headlines( $response_array );
		$lines = Questions_AbstractData::get_lines( $response_array );
		
		$lines = array_merge( array( $headlines ), $lines );
		
		// Running each question (element without separators etc)
		if( is_array( $lines ) ):
			$output = '';
			foreach( $lines AS $response_id => $line ):
				$output.= implode( ';', $line ) . chr( 13 );
			endforeach;

			return $output;
		else:
			return FALSE;
		endif;
	}
}
$Questions_Export = new Questions_Export();
