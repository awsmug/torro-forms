<?php

namespace Leaves_And_Love\Sample_DB_Objects\Translations;

use Leaves_And_Love\Plugin_Lib\Translations\Translations;

class Translations_Sample_Manager extends Translations {
	private $name = '';

	public function __construct( $name ) {
		$this->name = $name;

		parent::__construct();
	}

	protected function init() {
		$this->translations = array(
			'db_insert_error'            => $this->__translate( 'Could not insert ' . $this->name . ' into the database.', 'textdomain' ),
			'db_update_error'            => $this->__translate( 'Could not update ' . $this->name . ' in the database.', 'textdomain' ),
			'meta_delete_error'          => $this->__translate( 'Could not delete ' . $this->name . ' metadata for key %s.', 'textdomain' ),
			'meta_update_error'          => $this->__translate( 'Could not update ' . $this->name . ' metadata for key %s.', 'textdomain' ),
			'db_fetch_error_missing_id'  => $this->__translate( 'Could not fetch ' . $this->name . ' from the database because it is missing an ID.', 'textdomain' ),
			'db_fetch_error'             => $this->__translate( 'Could not fetch ' . $this->name . ' from the database.', 'textdomain' ),
			'db_delete_error_missing_id' => $this->__translate( 'Could not delete ' . $this->name . ' from the database because it is missing an ID.', 'textdomain' ),
			'db_delete_error'            => $this->__translate( 'Could not delete ' . $this->name . ' from the database.', 'textdomain' ),
			'meta_delete_all_error'      => $this->__translate( 'Could not delete the ' . $this->name . ' metadata. The ' . $this->name . ' itself was deleted successfully though.', 'textdomain' ),
		);
	}
}
