<?php

class Torro_Tests_Results extends Torro_UnitTestCase {
	function test_parse_results_for_export() {
		$charts = Torro_Result_Charts_C3::instance();

		$results = $charts->parse_results_for_export( 5944, 0, 5, 'raw', true );

		$this->assertEquals( 291, $results[0]['ID'] );
		$this->assertEquals( 1, $results[0]['User'] );
		$this->assertEquals( 'Peter', $results[0]['Name'] );
		$this->assertEquals( 'Green', $results[0]['Whats your favourite color?'] );
		$this->assertEquals( 'yes', $results[0]['What are your Hobbies? - Fishing'] );
		$this->assertEquals( 'yes', $results[0]['What are your Hobbies? - Working'] );
		$this->assertEquals( 'no', $results[0]['What are your Hobbies? - Bycicling'] );
		$this->assertEquals( 'no', $results[0]['What are your Hobbies? - Reading'] );
		$this->assertEquals( 'no', $results[0]['What are your Hobbies? - Jogging'] );
		$this->assertCount( 5, $results );
	}
}
