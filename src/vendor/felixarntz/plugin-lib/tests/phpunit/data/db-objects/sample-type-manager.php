<?php

namespace Leaves_And_Love\Sample_DB_Objects;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type_Manager;

class Sample_Type_Manager extends Model_Type_Manager {
	public function __construct( $prefix ) {
		$this->item_class_name = 'Leaves_And_Love\Sample_DB_Objects\Sample_Type';

		parent::__construct( $prefix );
	}

	protected function register_defaults() {

	}
}
