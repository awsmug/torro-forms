<?php

namespace Leaves_And_Love\Sample_DB_Objects;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type;

class Sample_Type extends Model_Type {
	protected function set_args( $args ) {
		parent::set_args( $args );

		if ( null === $this->args['show_ui'] ) {
			$this->args['show_ui'] = $this->args['public'];
		}
	}

	protected function get_defaults() {
		$singular = ucwords( str_replace( array( '_', '-' ), ' ', $this->slug ) );
		$plural = $singular . 's';

		$labels = array(
			'name'          => $plural,
			'singular_name' => $singular,
			'all_items'     => sprintf( 'All %s', $plural ),
		);

		return array(
			'labels'  => $labels,
			'public'  => false,
			'show_ui' => null,
		);
	}
}
