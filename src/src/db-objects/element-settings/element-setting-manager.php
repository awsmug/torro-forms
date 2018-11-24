<?php
/**
 * Element setting manager class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Element_Settings;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Manager;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Capability_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\REST_API_Manager_Trait;
use awsmug\Torro_Forms\DB_Objects\Manager_With_Parents_Trait;
use awsmug\Torro_Forms\Translations\Translations_Element_Setting_Manager;
use awsmug\Torro_Forms\DB;
use Leaves_And_Love\Plugin_Lib\Cache;
use Leaves_And_Love\Plugin_Lib\Error_Handler;

/**
 * Manager class for element settings.
 *
 * @since 1.0.0
 *
 * @method Element_Setting_Capabilities capabilities()
 * @method DB                           db()
 * @method Cache                        cache()
 * @method Error_Handler                error_handler()
 * @method Element_Setting              create()
 */
class Element_Setting_Manager extends Manager {
	use Capability_Manager_Trait, REST_API_Manager_Trait, Manager_With_Parents_Trait;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string                               $prefix       The instance prefix.
	 * @param array                                $services     {
	 *     Array of service instances.
	 *
	 *     @type Element_Setting_Capabilities $capabilities  The capabilities instance.
	 *     @type DB                           $db            The database instance.
	 *     @type Cache                        $cache         The cache instance.
	 *     @type Error_Handler                $error_handler The error handler instance.
	 * }
	 * @param Translations_Element_Setting_Manager $translations Translations instance.
	 */
	public function __construct( $prefix, $services, $translations ) {
		$this->class_name                 = Element_Setting::class;
		$this->collection_class_name      = Element_Setting_Collection::class;
		$this->query_class_name           = Element_Setting_Query::class;
		$this->rest_controller_class_name = REST_Element_Settings_Controller::class;

		$this->singular_slug = 'element_setting';
		$this->plural_slug   = 'element_settings';

		$this->table_name  = $this->plural_slug;
		$this->cache_group = $this->plural_slug;

		$this->primary_property = 'id';

		$this->public = true;

		parent::__construct( $prefix, $services, $translations );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			$command = new CLI_Element_Settings_Command( $this );
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
				'element_id int(11) unsigned NOT NULL',
				'name text NOT NULL',
				'value text NOT NULL',
				'PRIMARY KEY  (id)',
				'KEY element_id (element_id)',
			)
		);
	}
}
