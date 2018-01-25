<?php

namespace awsmug\Torro_Forms\Tests;

use Psr\Log\AbstractLogger;

class Null_Logger extends AbstractLogger {

	public function log( $level, $message, array $context = array() ) {
		// Empty method body.
	}
}
