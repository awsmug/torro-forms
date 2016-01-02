<?php

require_once( '../../../../wp-load.php' );

class Torro_ResultsTests extends PHPUnit_Framework_TestCase {
	function testElementResults() {
		global $wpdb;

		$torro_results = new Torro_Form_Results( 5944 );

		// Number of Elements
		$filter = array(
			'start_row'		=> 0,
			'number_rows'	=> 5,
		);

		$results = $torro_results->results( $filter );

		$this->assertEquals( 291, $results[0]['result_id'] );
		$this->assertEquals( 1, $results[0]['user_id'] );
		$this->assertEquals( 'Peter', $results[0]['Name'] );
		$this->assertEquals( 'Green', $results[0]['Whats your favourite color?'] );
		$this->assertEquals( 'yes', $results[0]['What are your Hobbies? - Fishing'] );
		$this->assertEquals( 'yes', $results[0]['What are your Hobbies? - Working'] );
		$this->assertEquals( 'no', $results[0]['What are your Hobbies? - Bycicling'] );
		$this->assertEquals( 'no', $results[0]['What are your Hobbies? - Reading'] );
		$this->assertEquals( 'no', $results[0]['What are your Hobbies? - Jogging'] );
		$this->assertCount( 5, $results );

		// Element IDs
		$filter = array(
			'element_ids'	=> array( 528 )
		);

		$results = $torro_results->results( $filter );
		$this->assertCount( 8, $results[0] );

		// Filter
		$filter = array(
			'filter'							=> array(
				'What are your Hobbies? - Fishing'	=> 'no',
			)
		);

		$results = $torro_results->results( $filter );
		$this->assertCount( 10, $results );

		// Order
		$filter = array(
			'orderby'	=> 'Name'
		);

		$results = $torro_results->results( $filter );
		$this->assertEquals( 'Andrea', $results[0]['Name'] );

		$filter = array(
			'orderby'	=> 'Name',
			'order'		=> 'DESC'
		);

		$results = $torro_results->results( $filter );
		$this->assertEquals( 'Uli', $results[0]['Name'] );

		// Get element results
		$results = $torro_results->element( 528 );
		$this->assertCount( 8, $results[0] );

		// Adding an individual column
		$torro_results->add_column( 'username', "SELECT user_login FROM {$wpdb->prefix}users WHERE ID=row.user_id" );
		$results = $torro_results->results();

		$this->assertArrayHasKey( 'username', $results[0] );
	}
}
