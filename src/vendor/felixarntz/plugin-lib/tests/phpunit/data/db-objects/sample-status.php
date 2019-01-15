<?php

namespace Leaves_And_Love\Sample_DB_Objects;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Status;

class Sample_Status extends Model_Status {
	protected function set_args( $args ) {
		parent::set_args( $args );
	}

	protected function get_defaults() {
		return array(
			'label'       => $this->slug,
			'label_count' => array(
				'singular' => $this->slug . ' <span class="count">(%s)</span>',
				'plural'   => $this->slug . ' <span class="count">(%s)</span>',
				'context'  => null,
				'domain'   => 'default',
			),
			'public'  => false,
		);
	}
}
