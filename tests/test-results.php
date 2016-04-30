<?php

class Torro_Tests_Results extends Torro_UnitTestCase {
	function test_parse_results_for_export() {
		$form_id = $this->create_full_form();
		$result_ids = $this->create_full_form_results( $form_id, 10 );

		$charts = Torro_Result_Charts_C3::instance();

		$results = $charts->parse_results_for_export( $form_id, 0, 5, 'raw', true );

		$this->assertEquals( 1, $results[0]['User'] );
		$this->assertCount( 5, $results );
	}
}
