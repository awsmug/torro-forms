<?php

use PHPUnit\Framework\TestCase;

class NullTest extends TestCase
{
	public function testAPIAPIFunction()
	{
		$api = apiapi( 'api_test' );
		$this->assertNull( $api );

		$config =  array(
			'transporter' => 'curl'
		);

		$api = apiapi( 'api_test', $config );
		$this->assertInstanceOf('APIAPI\Core\APIAPI', $api );
	}
}

