<?php
/*
 * Abstracting data
 *
 * This class abstracting data for further use
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
 
class Questions_AbstractData{
	/**
	 * Initializes the Component.
	 * @since 1.0.0
	 */
	public function __construct() {
	} // end constructor
	
	
	/**
	 * Prepare data to be used with charts
	 * @param array $response_array
	 * @return array $ordered_data
	 */
	public static function order_for_charting( $response_array ){
		global $wpdb, $questions_global;
		
		$ordered_data = array();
		
		$ordered_data[ 'questions' ] = array();
		$ordered_data[ 'data' ] = array();
		
		// Getting every entery of one 
		foreach( $response_array AS $key => $line ):
			$merged_data = array();
			
			$sql = $wpdb->prepare( "SELECT type FROM {$questions_global->tables->questions} WHERE id = %s", $key );
			$result = $wpdb->get_row( $sql );
			
			$element_class = 'Questions_SurveyElement_' . $result->type;
			
			if( !class_exists( $element_class ) )
				continue;
			
			$element = new $element_class( $key );
			
			if( !$element->is_analyzable )
				continue;
			
			$ordered_data[ 'questions' ][ $key ] = $line[ 'question' ];
			
			// Fill up missed answers with 0
			$sql = $wpdb->prepare( "SELECT * FROM {$questions_global->tables->answers} WHERE question_id = %s", $key );
			$results = $wpdb->get_results( $sql );
			
			$voted_answers = array_keys( $merged_data );
			foreach( $results AS $result ):
			 	$merged_data[ $result->answer ] = 0;
			endforeach;
			
			// Adding voted data
			$responses = $response_array[ $key ][ 'responses' ];
			
			foreach( $responses AS $response ):
				if( !$element->answer_is_multiple ):
					$merged_data[ $response ] += 1;
				else:
					foreach( $response AS $answer_option => $answer ):
						if( $answer == __( 'Yes', 'questions-locale' ) )
							$merged_data[ $answer_option ] += 1;
					endforeach;
				endif;
			endforeach;
			
			$ordered_data[ 'data' ][ $key ] = $merged_data;
		endforeach;
		
		return $ordered_data;		
	}
	
	
	public static function lines( $response_array ){
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
									$lines[ 0 ][ $question_id . '-' . $i++ ] = self::filter_string( $column ); 
								endforeach;
							else:	
								$column = $question[ 'question' ] . ' (' . $key . ')';
								$lines[ 0 ][ $question_id . '-' . $i++ ] = self::filter_string( $column ); 
							endif;
						endforeach;
						
						break;					
					endforeach;

				elseif( $question[ 'array' ] ):
					foreach( $question[ 'responses' ] AS $response ):
						$i = 0;
						foreach( $response AS $key => $value ):
							$column = $question[ 'question' ] . ' (' . $key . ')';
							$lines[ 0 ][ $question_id . '-' . $i++ ] = self::filter_string( $column ); 
						endforeach;
						break;					
					endforeach;
				else:
					$lines[ 0 ][ $question_id ] = self::filter_string( $question[ 'question' ] ); 
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
									$lines[ $response_id ][ $question_id . '-' . $i++ ] = self::filter_string( $key1 ); 
								endforeach;
							else:
								$lines[ $response_id ][ $question_id . '-' . $i++ ] = self::filter_string( $value ); 
							endif;
						endforeach;
						
					endforeach;
				elseif( $question[ 'array' ] ):
					foreach( $question[ 'responses' ] AS $response ):
						$i = 0;
						foreach( $response AS $key => $value ):
							$lines[ $response_id ][ $question_id . '-' . $i++ ] = self::filter_string( $value ); 
						endforeach;
					endforeach;
				else:
					foreach( $question[ 'responses' ]  AS $response_id => $value ):
						$lines[ $response_id ][ $question_id ] = self::filter_string( $value ); 
					endforeach;
				endif;
				
			endforeach;
			
			return $lines;
		else:
			return FALSE;
		endif;
	}

	public static function filter_string( $string ){
		$string = utf8_decode( $string );
		if( '' ==  $string )
			return '-';
		
		$string = self::remove_new_lines( $string );
		$string = str_replace( ';', '#', $string );
		
		return $string;
	}
	
	public static function remove_new_lines( $string ){
		return trim( preg_replace( '/\s\s+/', ' ', $string ) );
	}
}
