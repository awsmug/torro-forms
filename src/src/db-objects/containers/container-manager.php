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
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Capability_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\REST_API_Manager_Trait;
use awsmug\Torro_Forms\DB_Objects\Manager_With_Parents_Trait;
use awsmug\Torro_Forms\DB_Objects\Manager_With_Children_Trait;
use awsmug\Torro_Forms\Translations\Translations_Container_Manager;
use awsmug\Torro_Forms\DB;
use Leaves_And_Love\Plugin_Lib\Cache;
use Leaves_And_Love\Plugin_Lib\Error_Handler;

/**
 * Manager class for containers.
 *
 * @since 1.0.0
 *
 * @method Container_Capabilities capabilities()
 * @method DB                     db()
 * @method Cache                  cache()
 * @method Error_Handler          error_handler()
 * @method Container              create()
 */
class Container_Manager extends Manager {
	use Title_Manager_Trait, Capability_Manager_Trait, REST_API_Manager_Trait, Manager_With_Parents_Trait, Manager_With_Children_Trait;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string                         $prefix       The instance prefix.
	 * @param array                          $services     {
	 *     Array of service instances.
	 *
	 *     @type Container_Capabilities $capabilities  The capabilities instance.
	 *     @type DB                     $db            The database instance.
	 *     @type Cache                  $cache         The cache instance.
	 *     @type Error_Handler          $error_handler The error handler instance.
	 * }
	 * @param Translations_Container_Manager $translations Translations instance.
	 */
	public function __construct( $prefix, $services, $translations ) {
		$this->class_name                 = Container::class;
		$this->collection_class_name      = Container_Collection::class;
		$this->query_class_name           = Container_Query::class;
		$this->rest_controller_class_name = REST_Containers_Controller::class;

		$this->singular_slug = 'container';
		$this->plural_slug   = 'containers';

		$this->table_name  = $this->plural_slug;
		$this->cache_group = $this->plural_slug;

		$this->primary_property = 'id';
		$this->title_property   = 'label';

		$this->public = true;

		parent::__construct( $prefix, $services, $translations );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			$command = new CLI_Containers_Command( $this );
			$command->add( str_replace( '_', ' ', $this->prefix ) . str_replace( '_', '-', $this->singular_slug ) );
		}
	}

	/**
	 * Adds the database table.
	 *
	 * @since 1.0.0
	 */
	protected function add_database_table() {
		$this->db()->add_table(
			$this->table_name,
			array(
				'id int(11) unsigned NOT NULL auto_increment',
				'form_id bigint(20) unsigned NOT NULL',
				'label text NOT NULL',
				"sort int(11) unsigned NOT NULL default '0'",
				'PRIMARY KEY  (id)',
				'KEY form_id (form_id)',
			)
		);
	}
}
