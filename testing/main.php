<?php

include( '../../../../wp-load.php' );
require_once( ABSPATH . 'wp-admin/includes/user.php' );

class SurveyvalTests extends PHPUnit_Extensions_SeleniumTestCase{
		
	var $browser_url = 'http://localhost/surveyval/';
	var $is_local = FALSE;
	
	var $browser = '*firefox';
	var $users = array(
		'test1' => array( 
			'name' => 'test1',
			'pass' => '1234567890',
			'role' => 'administrator',
		),
		'test2' => array( 
			'name' => 'test2',
			'pass' => '1234567890',
			'role' => 'editor',
		),
		'test3' => array( 
			'name' => 'test3',
			'pass' => '1234567890',
			'role' => 'author',
		),
		'test4' => array( 
			'name' => 'test4',
			'pass' => '1234567890',
			'role' => 'contributor',
		),
		'test5' => array( 
			'name' => 'test5',
			'pass' => '1234567890',
			'role' => 'subscriber',
		),
		/*'test2' => 'test2',
		'test3' => 'test3',
		'test4' => 'test4',
		'test5' => 'test5',
		'test6' => 'test6',
		'test7' => 'test7',
		'test8' => 'test8',
		'test9' => 'test9',
		'test10' => 'test10',*/
	);
	
	
	var $survey_id = 4;
	var $con;
	
	// protected $screenshotPath = 'C:/xampp/htdocs/surveyval';
	protected $screenshotPath = '/Users/svenw/htdocs/surveyval/';
	protected $captureScreenshotOnFailure = TRUE;
	protected $screenshotUrl = 'http://localhost/surveyval';
	
	public function setUp(){
		$this->setBrowser( $this->browser );
		$this->setBrowserUrl( $this->browser_url );
	}
	
	public function test(){
		$user_ids = array();
		$this->generate_scripts();
		
		foreach( $this->users as $user ):
			// Create the user
			$user_data = array (
		        'user_login' => $user['name'],
		        'user_pass' => $user['pass'],     
		        'user_email' => $user['name'] . '@test.com',
		        'role' => $user['role']
		    );
    		
    		$user_id = wp_insert_user( $user_data );
			$this->add_user_to_survey( $user_id, $this->survey_id );
			
			$this->log_line( 'Added User ID: ' . $user_id . ' on Survey ID: ' . $survey_id );
			
			$this->open( "wp-login.php" );
			$this->type( "id=user_login", $user['name'] );
			$this->type( "id=user_pass", $user['pass'] );
			$this->click( "id=wp-submit" );
			sleep( 2 );
			
			$this->open("survey/check-melle/");
			$this->waitForPageToLoad("30000");
			
		    $this->run1( $user_id );
			
			wp_delete_user( $user_id );
			
		endforeach;
	}
	
	private function run1( $user_id ){
		$values = array();
		$values[ 101 ] = array( 8, 16, 24, 32, 40, 48 );
		
		foreach( $values[ 101 ] AS $value ):
		endforeach;
		$values[ 105 ] = '7';
		$values[ 106 ] = '5';
		$values[ 109 ] = '7';
		$values[ 110 ] = '5';
		$values[ 113 ] = '7';
		$values[ 114 ] = '5';
		$values[ 115 ] = '5';
		$values[ 117 ] = '180';
		$values[ 118 ] = '100';
		$values[ 119 ] = 'Ja';
		$values[ 120 ] = '21-30';
		$values[ 122 ] = array( 9, 18, 27, 36, 45, 54, 63, 72 );
		
		include( 'testrun.php' );
	}

	private function check_db_values( $sqls, $user_id ){
		global $wpdb;
		$results_log = '';
		
		foreach( $sqls AS $query ):
			if( is_array( $query ) ):
				foreach( $query  AS $subquery ):
					$wpdb->get_results( $subquery );
					if( $wpdb->num_rows === 0 ):
						$results_log .= 'FAILED: ' . $subquery . chr(13);
					else:
						$results_log .= 'MATCHED: ' . $subquery.  chr( 13 );
					endif;
				endforeach;
			else:
				$wpdb->get_results( $query );
				if( $wpdb->num_rows === 0 ):
					$results_log .= 'FAILED: ' . $query . chr(13);
				else:
					$results_log .= 'MATCHED: ' . $query . chr( 13 );
				endif;
			endif;
			
		endforeach;
		
		$file = fopen( 'results_user_' . $user_id . '.log', 'w' );
		fputs( $file, $results_log );
		fclose( $file );
		
	}
	
	private function add_user_to_survey( $user_id, $survey_id ){
		global $wpdb;
		
		$wpdb->insert( 
			$wpdb->prefix . 'surveyval_participiants', 
			array( 
				'survey_id' => $survey_id, 
				'user_id' => $user_id 
			), 
			array( 
				'%d', 
				'%d' 
			) 
		);
		
		$this->log_line( print_r( $wpdb, TRUE ) );

	}
	
	private function generate_scripts(){
		global $wpdb;
		
		$survey = new SurveyVal_Survey( $this->survey_id );
		
		$values_code = '$values = array();' . chr( 13 );
		$click_code = '';
		$select_code = '';
		
		foreach( $survey->elements AS $element ):
			
			print_r( $element );
			switch( get_class( $element ) ){
				case 'SurveyVal_SurveyElement_Text':
					$values_code.= '$values[ ' . $element->id .' ] = \'\';' . chr( 13 );
					$click_code.= '$this->type("name=surveyval_response[' . $element->id . ']",  $values[ ' . $element->id . ' ] );' . chr( 13 );
					$select_code.= '$sql[ ' . $element->id . ' ] = "SELECT * FROM ' . $wpdb->prefix. 'surveyval_respond_answers WHERE question_id=\'' . $element->id . '\' AND respond_id=\'" . $response_id. "\' AND value=\'" . $values[' . $element->id . '] . "\'";' . chr( 13 );
					break;
				case 'SurveyVal_SurveyElement_Textarea':
					$values_code.= '$values[ ' . $element->id .' ] = \'\';' . chr( 13 );
					$click_code.= '$this->type("name=surveyval_response[' . $element->id . ']",  $values[ ' . $element->id . ' ] );' . chr( 13 );
					$select_code.= '$sql[ ' . $element->id . ' ] = "SELECT * FROM ' . $wpdb->prefix. 'surveyval_respond_answers WHERE question_id=\'' . $element->id . '\' AND respond_id=\'" . $response_id. "\' AND value=\'" . $values[' . $element->id . '] . "\'";' . chr( 13 );
					break;
				case 'SurveyVal_SurveyElement_OneChoice':
					$values_code.= '$values[ ' . $element->id .' ] = \'\';' . chr( 13 );
					$click_code.= '$this->type("name=surveyval_response[' . $element->id . ']",  $values[ ' . $element->id . ' ] );' . chr( 13 );
					$select_code.= '$sql[ ' . $element->id . ' ] = "SELECT * FROM ' . $wpdb->prefix. 'surveyval_respond_answers WHERE question_id=\'' . $element->id . '\' AND respond_id=\'" . $response_id. "\' AND value=\'" . $values[' . $element->id . '] . "\'";' . chr( 13 );
					break;
					break;
				case 'SurveyVal_SurveyElement_MultipleChoice':
					$values_code.= '$values[ ' . $element->id .' ] = array(\'\');' . chr( 13 );
					
					$click_code.= '$this->click("name=surveyval_response[' . $element->id . '][]");' . chr( 13 );
					$click_code.= 'foreach( $values[ ' . $element->id . ' ] AS $value ) ';
					$click_code.= '$this->click("document.surveyval.elements[\'surveyval_response[' . $element->id . '][]\'][" . $value . "]");' . chr( 13 );
					
					break;
					
				case 'SurveyVal_SurveyElement_Dropdown':
					$values_code.= '$values[ ' . $element->id .' ] = \'\';' . chr( 13 );
					$click_code.= '$this->select("name=surveyval_response[' . $element->id . ']", "label=" . $values[ ' . $element->id . ' ]);' . chr( 13 );
					$select_code.= '$sql[ ' . $element->id . ' ] = "SELECT * FROM ' . $wpdb->prefix. 'surveyval_respond_answers WHERE question_id=\'' . $element->id . '\' AND respond_id=\'" . $response_id. "\' AND value=\'" . $values[' . $element->id . '] . "\'";' . chr( 13 );
					
					break;
				case 'SurveyVal_SurveyElement_Matrix':
					$values_code.= '$values[ ' . $element->id .' ] = array(\'\');' . chr( 13 );
					
					$combine_values = array();
					$columns = $element->get_columns();
					$rows = $element->get_rows();
					
					$i = 0;
					
					foreach( $rows AS $row ):
						foreach( $columns AS $column ):
							$combine_values[] = $i++ . ' => \'' . $row['id'] . ':' . $column['id'] . '\'';
						endforeach;
					endforeach;
					
					$select_code.= '$combined_values[ ' . $element->id .' ] = array( ' . implode( ', ', $combine_values ) . ');' . chr( 13 );
					
					$click_code.= '$this->click("name=surveyval_response[' . $element->id . '][]");' . chr( 13 );
					$click_code.= 'foreach( $values[ ' . $element->id . ' ] AS $value ) ';
					$click_code.= '$this->click("document.surveyval.elements[\'surveyval_response[' . $element->id . '][]\'][" . $value . "]");' . chr( 13 );
					
					$select_code.= 'foreach( $values[ ' . $element->id . ' ] AS $value ) ';
					$select_code.= '$sql[ ' . $element->id . ' ][] = "SELECT * FROM ' . $wpdb->prefix. 'surveyval_respond_answers WHERE question_id=\'' . $element->id . '\' AND respond_id=\'" . $response_id. "\' AND value=\'" . $combined_values[' . $element->id . '][ $value ] . "\'";' . chr( 13 );
					
					break;
				case 'SurveyVal_SurveyElement_Range':
					$values_code.= '$values[ ' . $element->id .' ] = \'\';' . chr( 13 );
					$click_code.= '$this->type("name=surveyval_response[' . $element->id . ']",  $values[ ' . $element->id . ' ] );' . chr( 13 );
					$select_code.= '$sql[ ' . $element->id . ' ] = "SELECT * FROM ' . $wpdb->prefix. 'surveyval_respond_answers WHERE question_id=\'' . $element->id . '\' AND respond_id=\'" . $response_id. "\' AND value=\'" . $values[' . $element->id . '] . "\'";' . chr( 13 );
					break;
				case 'SurveyVal_SurveyElement_RangeEmotional':
					$values_code.= '$values[ ' . $element->id .' ] = \'\';' . chr( 13 );
					$click_code.= '$this->type("name=surveyval_response[' . $element->id . ']",  $values[ ' . $element->id . ' ] );' . chr( 13 );
					$select_code.= '$sql[ ' . $element->id . ' ] = "SELECT * FROM ' . $wpdb->prefix. 'surveyval_respond_answers WHERE question_id=\'' . $element->id . '\' AND respond_id=\'" . $response_id. "\' AND value=\'" . $values[' . $element->id . '] . "\'";' . chr( 13 );
					break;
					
				case 'SurveyVal_SurveyElement_Splitter':
					$click_code.= '$this->click("name=surveyval_submission");' . chr( 13 );
					$click_code.= '$this->waitForPageToLoad("30000");' . chr( 13 );
					$click_code.= 'sleep(2);' . chr( 13 );
					break;

			}
		endforeach;
		
		$click_code.= '$this->click("name=surveyval_submission");' . chr( 13 );
		$click_code.= '$this->waitForPageToLoad("30000");' . chr( 13 );
		$click_code.= 'sleep(2);' . chr( 13 );
		
		$click_code.= '$response_id = $this->getValue( \'response_id\' );' . chr( 13 );
		
		$select_code.= '$this->check_db_values( $sql, $user_id );' . chr( 13 );
		
		$code = '<?php' . chr( 13 ) . chr( 13 );
		$code.= '/* You can fill in this value array within your test function' . chr( 13 ) . $values_code . '*/' . chr(13) . chr(13);
		$code.= $click_code . chr( 13 );
		$code.= $select_code . chr( 13 );
		
		$file = fopen( 'testrun.php', 'w' );
		fputs( $file, $code );
		fclose( $file );
	}

	private function log_line( $line ){
		$file = fopen( 'surveyval.log', 'w' );
		fputs( $file, $line . chr( 13 ) );
		fclose( $file );
	}
}
