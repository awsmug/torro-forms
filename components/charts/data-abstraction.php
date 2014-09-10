<?php
/*
 * Abstracting data
 *
 * This class abstracting data for further use
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
 
class SurveyVal_AbstractData{
	/**
	 * Initializes the Component.
	 * @since 1.0.0
	 */
	public function __construct() {
	} // end constructor
	
	public static function order_for_charting( $response_array ){
		$lines = self::lines( $response_array );
		
		$ordered_data = array();
		
		// Getting Labels
		foreach( $lines[ 0 ] AS $key => $line ):
			if( $key != '_user_id' ):
				$ordered_data[ 'questions' ][ $key ] = $line;
			endif;
		endforeach;
		
		unset ( $lines[0] ); // Throw away headlines
		
		$ordered_data[ 'data' ] = array();
		
		// Getting every entery of one 
		foreach( $ordered_data[ 'questions' ] AS $key => $line ):
			$merged_data = array();
			
			foreach( $lines AS $response_id => $line ):
				if( !isset( $merged_data[ $line[ $key ] ] ) ) $merged_data[ $line[ $key ] ] = 0;
				$merged_data[ $line[ $key ] ]++;				
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
