<?php

class Test_Hook_Service_Trait_Class {
	use Leaves_And_Love\Plugin_Lib\Traits\Hook_Service_Trait;

	protected $hooks;

	public function __construct( $hooks = array() ) {
		$this->hooks = $hooks;
		$this->setup_hooks();
	}

	protected function setup_hooks() {
		foreach ( $this->hooks as $hook ) {
			$type = 'action';
			if ( isset( $hook['type'] ) ) {
				$type = $hook['type'];
				unset( $hook['type'] );
			}

			if ( 'filter' === $type ) {
				$hook['num_args'] = 1;
				$this->filters[] = $hook;
			} else {
				$hook['num_args'] = 0;
				$this->actions[] = $hook;
			}
		}
	}
}
