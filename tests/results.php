<?php

require_once( '../../../../wp-load.php' );

class AF_ResultsTests extends PHPUnit_Framework_TestCase
{
	function testElementResults()
	{
		global $wpdb;

		$af_results = new AF_Form_Results( 5889 );

		$filter = array(
			'response_ids' => array( 282 )
		);

		$result = $af_results->get_element_results( 474, $filter );

		print_r( $result );

		$results = $af_results->get_results();

		print_r( $results );
	}
}
