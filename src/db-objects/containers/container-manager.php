<?php
/**
 * Container manager class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Containers;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Manager;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Title_Manager_Trait;

/**
 * Manager class for containers.
 *
 * @since 1.0.0
 *
 * @method awsmug\Torro_Forms\DB                    db()
 * @method Leaves_And_Love\Plugin_Lib\Cache         cache()
 * @method Leaves_And_Love\Plugin_Lib\Error_Handler error_handler()
 */
class Container_Manager extends Manager {
	use Title_Manager_Trait;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string                                                         $prefix   The instance prefix.
	 * @param array                                                          $services {
	 *     Array of service instances.
	 *
	 *     @type awsmug\Torro_Forms\DB                    $db            The database instance.
	 *     @type Leaves_And_Love\Plugin_Lib\Cache         $cache         The cache instance.
	 *     @type Leaves_And_Love\Plugin_Lib\Error_Handler $error_handler The error handler instance.
	 * }
	 * @param awsmug\Torro_Forms\Translations\Translations_Container_Manager $translations Translations instance.
	 */
	public function __construct( $prefix, $services, $translations ) {
		$this->class_name            = Container::class;
		$this->collection_class_name = Container_Collection::class;
		$this->query_class_name      = Container_Query::class;

		$this->singular_slug = 'container';
		$this->plural_slug   = 'containers';

		$this->table_name  = $this->plural_slug;
		$this->cache_group = $this->plural_slug;

		$this->primary_property = 'id';
		$this->title_property   = 'label';

		parent::__construct( $prefix, $services, $translations );
	}

	/**
	 * Adds the database table.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function add_database_table() {
		$this->db()->add_table( $this->table_name, array(
			"id int(11) unsigned NOT NULL auto_increment",
			"form_id bigint(20) unsigned NOT NULL",
			"label text NOT NULL",
			"sort int(11) unsigned NOT NULL default '0'",
			"PRIMARY KEY  (id)",
			"KEY form_id (form_id)",
		) );
	}
}
