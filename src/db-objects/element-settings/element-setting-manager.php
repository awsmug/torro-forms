<?php
/**
 * Element setting manager class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Element_Settings;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Manager;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Title_Manager_Trait;

/**
 * Manager class for element settings.
 *
 * @since 1.0.0
 *
 * @method awsmug\Torro_Forms\DB                    db()
 * @method Leaves_And_Love\Plugin_Lib\Cache         cache()
 * @method Leaves_And_Love\Plugin_Lib\Error_Handler error_handler()
 */
class Element_Setting_Manager extends Manager {
	use Title_Manager_Trait;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string                                                               $prefix   The instance prefix.
	 * @param array                                                                $services {
	 *     Array of service instances.
	 *
	 *     @type awsmug\Torro_Forms\DB                    $db            The database instance.
	 *     @type Leaves_And_Love\Plugin_Lib\Cache         $cache         The cache instance.
	 *     @type Leaves_And_Love\Plugin_Lib\Error_Handler $error_handler The error handler instance.
	 * }
	 * @param awsmug\Torro_Forms\Translations\Translations_Element_Setting_Manager $translations Translations instance.
	 */
	public function __construct( $prefix, $services, $translations ) {
		$this->class_name            = Element_Setting::class;
		$this->collection_class_name = Element_Setting_Collection::class;
		$this->query_class_name      = Element_Setting_Query::class;

		$this->singular_slug = 'element_setting';
		$this->plural_slug   = 'element_settings';

		$this->table_name  = $this->plural_slug;
		$this->cache_group = $this->plural_slug;

		$this->primary_property = 'id';
		$this->title_property   = 'name';

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
			"element_id int(11) unsigned NOT NULL",
			"name text NOT NULL",
			"value text NOT NULL",
			"PRIMARY KEY  (id)",
			"KEY element_id (element_id)",
		) );
	}
}
