<?php
/**
 * Element manager class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Elements;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Manager;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Title_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Capability_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\REST_API_Manager_Trait;
use awsmug\Torro_Forms\DB_Objects\Manager_With_Parents_Trait;
use awsmug\Torro_Forms\DB_Objects\Manager_With_Children_Trait;
use awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Element_Type_Manager;
use awsmug\Torro_Forms\Translations\Translations_Element_Manager;
use awsmug\Torro_Forms\DB;
use awsmug\Torro_Forms\Assets;
use Leaves_And_Love\Plugin_Lib\AJAX;
use Leaves_And_Love\Plugin_Lib\Cache;
use Leaves_And_Love\Plugin_Lib\Error_Handler;

/**
 * Manager class for elements.
 *
 * @since 1.0.0
 *
 * @method Element_Capabilities capabilities()
 * @method DB                   db()
 * @method Assets               assets()
 * @method AJAX                 ajax()
 * @method Cache                cache()
 * @method Error_Handler        error_handler()
 * @method Element              create()
 */
class Element_Manager extends Manager {
	use Title_Manager_Trait, Capability_Manager_Trait, REST_API_Manager_Trait, Manager_With_Parents_Trait, Manager_With_Children_Trait;

	/**
	 * The element type manager instance.
	 *
	 * @since 1.0.0
	 * @var Element_Type_Manager
	 */
	protected $types;

	/**
	 * The Assets API service definition.
	 *
	 * @since 1.0.0
	 * @static
	 * @var string
	 */
	protected static $service_assets = Assets::class;

	/**
	 * The AJAX API service definition.
	 *
	 * @since 1.0.0
	 * @static
	 * @var string
	 */
	protected static $service_ajax = AJAX::class;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string                       $prefix       The instance prefix.
	 * @param array                        $services     {
	 *     Array of service instances.
	 *
	 *     @type Element_Capabilities $capabilities  The capabilities instance.
	 *     @type DB                   $db            The database instance.
	 *     @type Assets               $assets        The assets instance.
	 *     @type AJAX                 $ajax          The AJAX instance.
	 *     @type Cache                $cache         The cache instance.
	 *     @type Error_Handler        $error_handler The error handler instance.
	 * }
	 * @param Translations_Element_Manager $translations Translations instance.
	 */
	public function __construct( $prefix, $services, $translations ) {
		$this->class_name                 = Element::class;
		$this->collection_class_name      = Element_Collection::class;
		$this->query_class_name           = Element_Query::class;
		$this->rest_controller_class_name = REST_Elements_Controller::class;

		$this->singular_slug = 'element';
		$this->plural_slug   = 'elements';

		$this->table_name  = $this->plural_slug;
		$this->cache_group = $this->plural_slug;

		$this->primary_property = 'id';
		$this->title_property   = 'label';

		$this->public = true;

		parent::__construct( $prefix, $services, $translations );

		$this->types = new Element_Type_Manager(
			$this->get_prefix(),
			array(
				'elements'      => $this,
				'assets'        => $this->assets(),
				'error_handler' => $this->error_handler(),
			)
		);

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			$command = new CLI_Elements_Command( $this );
			$command->add( str_replace( '_', ' ', $this->prefix ) . str_replace( '_', '-', $this->singular_slug ) );
		}
	}

	/**
	 * Returns the element type manager instance.
	 *
	 * @since 1.0.0
	 *
	 * @return Element_Type_Manager The element type manager instance.
	 */
	public function types() {
		return $this->types;
	}

	/**
	 * Adds the service hooks.
	 *
	 * @since 1.0.0
	 */
	public function add_hooks() {
		$result = parent::add_hooks();

		if ( $result ) {
			$this->types->add_hooks();
		}

		return $result;
	}

	/**
	 * Removes the service hooks.
	 *
	 * @since 1.0.0
	 */
	public function remove_hooks() {
		$result = parent::remove_hooks();

		if ( $result ) {
			$this->types->remove_hooks();
		}

		return $result;
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
				'container_id int(11) unsigned NOT NULL',
				'label text NOT NULL',
				"sort int(11) unsigned NOT NULL default '0'",
				'type char(50) NOT NULL',
				'PRIMARY KEY  (id)',
				'KEY container_id (container_id)',
				'KEY type (type)',
				'KEY type_container_id (type,container_id)',
			)
		);
	}
}
