<?php

class Tests_Torro extends Torro_UnitTestCase {
	public function test_torro() {
		$this->assertInstanceOf( 'Torro_Forms', torro() );
	}
}
