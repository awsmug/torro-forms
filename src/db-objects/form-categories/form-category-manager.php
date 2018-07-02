<?php
/**
 * Form category manager class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Form_Categories;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Managers\Core_Manager;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Title_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Slug_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Meta_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Capability_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\REST_API_Manager_Trait;
use awsmug\Torro_Forms\DB_Objects\Manager_With_Parents_Trait;
use awsmug\Torro_Forms\Translations\Translations_Form_Category_Manager;
use awsmug\Torro_Forms\DB;
use Leaves_And_Love\Plugin_Lib\Cache;
use Leaves_And_Love\Plugin_Lib\Meta;
use Leaves_And_Love\Plugin_Lib\Error_Handler;

/**
 * Manager class for form categories.
 *
 * @since 1.0.0
 *
 * @method Form_Category_Capabilities capabilities()
 * @method DB                         db()
 * @method Cache                      cache()
 * @method Meta                       meta()
 * @method Error_Handler              error_handler()
 * @method Form_Category              create()
 */
class Form_Category_Manager extends Core_Manager {
	use Title_Manager_Trait, Slug_Manager_Trait, Meta_Manager_Trait, Capability_Manager_Trait, REST_API_Manager_Trait, Manager_With_Parents_Trait;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string                             $prefix       The instance prefix.
	 * @param array                              $services     {
	 *     Array of service instances.
	 *
	 *     @type Form_Category_Capabilities $capabilities  The capabilities instance.
	 *     @type DB                         $db            The database instance.
	 *     @type Cache                      $cache         The cache instance.
	 *     @type Meta                       $meta          The meta instance.
	 *     @type Error_Handler              $error_handler The error handler instance.
	 * }
	 * @param Translations_Form_Category_Manager $translations Translations instance.
	 */
	public function __construct( $prefix, $services, $translations ) {
		$this->class_name                 = Form_Category::class;
		$this->collection_class_name      = Form_Category_Collection::class;
		$this->query_class_name           = Form_Category_Query::class;
		$this->rest_controller_class_name = REST_Form_Categories_Controller::class;

		$this->singular_slug = 'form_category';
		$this->plural_slug   = 'form_categories';

		$this->table_name  = 'terms';
		$this->cache_group = 'terms';
		$this->meta_type   = 'term';

		$this->fetch_callback = array( $this, 'fetch_from_db' );

		$this->primary_property = 'id';
		$this->title_property   = 'title';
		$this->slug_property    = 'slug';

		$this->public = true;

		parent::__construct( $prefix, $services, $translations );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			$command = new CLI_Form_Categories_Command( $this );
			$command->add( str_replace( '_', ' ', $this->prefix ) . str_replace( '_', '-', $this->singular_slug ) );
		}
	}

	/**
	 * Internal method to insert a new form into the database.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Array of column => value pairs for the new database row.
	 * @return int|false The ID of the new form, or false on failure.
	 */
	protected function insert_into_db( $args ) {
		$args = $this->map_args( $args );

		if ( ! isset( $args['name'] ) || ! isset( $args['taxonomy'] ) ) {
			return false;
		}

		$name = $args['name'];
		unset( $args['name'] );

		$taxonomy = $args['taxonomy'];
		unset( $args['taxonomy'] );

		$result = wp_insert_term( $name, $taxonomy, $args );
		if ( is_wp_error( $result ) ) {
			return false;
		}

		return $result['term_id'];
	}

	/**
	 * Internal method to update an existing form in the database.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $form_category_id ID of the form to update.
	 * @param array $args             Array of column => value pairs to update in the database row.
	 * @return bool True on success, or false on failure.
	 */
	protected function update_in_db( $form_category_id, $args ) {
		$args = $this->map_args( $args );

		$taxonomy = $args['taxonomy'];
		unset( $args['taxonomy'] );

		$result = wp_update_term( $form_category_id, $taxonomy, $args );
		if ( is_wp_error( $result ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Internal method to delete a form from the database.
	 *
	 * @since 1.0.0
	 *
	 * @param int $form_category_id ID of the form to delete.
	 * @return bool True on success, or false on failure.
	 */
	protected function delete_from_db( $form_category_id ) {
		$term = $this->fetch_from_db( $form_category_id );
		if ( ! $term ) {
			return false;
		}

		$result = wp_delete_term( $form_category_id, $term->taxonomy );
		if ( ! $result || is_wp_error( $result ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Internal method to fetch a form from the database.
	 *
	 * @since 1.0.0
	 *
	 * @param int $form_category_id ID of the form to fetch.
	 * @return WP_Post|null Post object of the form, or null if not found.
	 */
	protected function fetch_from_db( $form_category_id ) {
		$term = get_term( $form_category_id );
		if ( ! $term || is_wp_error( $term ) || $this->get_prefix() . 'form_category' !== $term->taxonomy ) {
			return null;
		}

		return $term;
	}

	/**
	 * Maps form arguments to regular post arguments.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Arguments as `$property => $value` pairs.
	 * @return array Mapped arguments.
	 */
	protected function map_args( $args ) {
		$mapped_args = array();
		foreach ( $args as $property => $value ) {
			switch ( $property ) {
				case 'title':
					$mapped_args['name'] = $value;
					break;
				case 'slug':
				case 'description':
				case 'parent':
					$mapped_args[ $property ] = $value;
			}
		}

		$mapped_args['taxonomy'] = $this->get_prefix() . 'form_category';

		return $mapped_args;
	}

	/**
	 * Sets up all action and filter hooks for the service.
	 *
	 * This method must be implemented and then be called from the constructor.
	 *
	 * @since 1.0.0
	 */
	protected function setup_hooks() {
		parent::setup_hooks();

		if ( method_exists( $this, 'register_rest_routes' ) ) {
			$this->filters[] = array(
				'name'     => 'rest_api_init',
				'callback' => array( $this, 'register_rest_routes' ),
				'priority' => 10,
				'num_args' => 0,
			);
		}
	}
}
