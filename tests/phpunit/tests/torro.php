<?php

namespace awsmug\Torro_Forms\Tests;

use Torro_Forms;

class Tests_Torro extends Unit_Test_Case {

	private $value = 0;

	public function test_torro() {
		$this->assertInstanceOf( Torro_Forms::class, torro() );
	}

	public function test_torro_load_on_hook() {
		$initial_value = $this->value;

		// Trick the plugin into thinking the action hasn't fired yet.
		$orig_action_count = $GLOBALS['wp_actions']['torro_loaded'];
		unset( $GLOBALS['wp_actions']['torro_loaded'] );

		torro_load( array( $this, 'action_increase_value' ) );

		// Reset original state.
		$GLOBALS['wp_actions']['torro_loaded'] = $orig_action_count;

		// Action had not fired, so callback was just queued, but not executed.
		$this->assertSame( $initial_value, $this->value );
	}

	public function test_torro_load_immediately() {
		$initial_value = $this->value;

		torro_load( array( $this, 'action_increase_value' ) );

		// Action had already fired, so callback was executed immediately.
		$this->assertSame( $initial_value + 1, $this->value );
	}

	public function action_increase_value() {
		$this->value++;
	}
}
