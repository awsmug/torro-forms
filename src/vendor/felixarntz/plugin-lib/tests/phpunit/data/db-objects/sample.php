<?php

namespace Leaves_And_Love\Sample_DB_Objects;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Sitewide_Model_Trait;

class Sample extends Model {
	use Sitewide_Model_Trait;

	protected $id = 0;

	protected $title = '';

	protected $type = '';

	protected $status = '';

	protected $author_id = 0;

	protected $content = '';

	protected $parent_id = 0;

	protected $priority = 0.0;

	protected $active = false;

	public function __construct( $manager, $db_obj = null ) {
		parent::__construct( $manager, $db_obj );
	}
}
