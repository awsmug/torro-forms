<?php
/**
 * Form manager class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Forms;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Managers\Core_Manager;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Title_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Slug_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Author_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Meta_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Capability_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\REST_API_Manager_Trait;
use awsmug\Torro_Forms\DB_Objects\Manager_With_Children_Trait;

/**
 * Manager class for forms.
 *
 * @since 1.0.0
 *
 * @method awsmug\Torro_Forms\DB_Objects\Forms\Form_Capabilities capabilities()
 * @method Leaves_And_Love\Plugin_Lib\Options                    options()
 * @method awsmug\Torro_Forms\DB                                 db()
 * @method Leaves_And_Love\Plugin_Lib\Cache                      cache()
 * @method Leaves_And_Love\Plugin_Lib\Meta                       meta()
 * @method Leaves_And_Love\Plugin_Lib\Error_Handler              error_handler()
 */
class Form_Manager extends Core_Manager {
	use Title_Manager_Trait, Slug_Manager_Trait, Author_Manager_Trait, Meta_Manager_Trait, Capability_Manager_Trait, REST_API_Manager_Trait, Manager_With_Children_Trait;

	/**
	 * The form edit page handler.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var awsmug\Torro_Forms\DB_Objects\Forms\Form_Edit_Page_Handler
	 */
	protected $edit_page_handler;

	/**
	 * The Option API service definition.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 * @var string
	 */
	protected static $service_options = 'Leaves_And_Love\Plugin_Lib\Options';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string                                                    $prefix   The instance prefix.
	 * @param array                                                     $services {
	 *     Array of service instances.
	 *
	 *     @type awsmug\Torro_Forms\DB_Objects\Forms\Form_Capabilities $capabilities  The capabilities instance.
	 *     @type Leaves_And_Love\Plugin_Lib\Options                    $options       The options instance.
	 *     @type awsmug\Torro_Forms\DB                                 $db            The database instance.
	 *     @type Leaves_And_Love\Plugin_Lib\Cache                      $cache         The cache instance.
	 *     @type Leaves_And_Love\Plugin_Lib\Meta                       $meta          The meta instance.
	 *     @type Leaves_And_Love\Plugin_Lib\Error_Handler              $error_handler The error handler instance.
	 * }
	 * @param awsmug\Torro_Forms\Translations\Translations_Form_Manager $translations Translations instance.
	 */
	public function __construct( $prefix, $services, $translations ) {
		$this->class_name                 = Form::class;
		$this->collection_class_name      = Form_Collection::class;
		$this->query_class_name           = Form_Query::class;
		$this->rest_controller_class_name = REST_Forms_Controller::class;

		$this->singular_slug = 'form';
		$this->plural_slug   = 'forms';

		$this->table_name  = 'posts';
		$this->cache_group = 'posts';
		$this->meta_type   = 'post';

		$this->fetch_callback = array( $this, 'fetch_from_db' );

		$this->primary_property = 'id';
		$this->title_property   = 'title';
		$this->slug_property    = 'slug';
		$this->author_property  = 'author';

		$this->edit_page_handler = new Form_Edit_Page_Handler( $this );

		parent::__construct( $prefix, $services, $translations );
	}

	/**
	 * Returns the form edit page handler.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return awsmug\Torro_Forms\DB_Objects\Forms\Form_Edit_Page_Handler Edit page handler instance.
	 */
	public function edit_page_handler() {
		return $this->edit_page_handler;
	}

	/**
	 * Returns the name of the property which identifies the form status.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Status property name.
	 */
	public function get_status_property() {
		return 'status';
	}

	/**
	 * Internal method to insert a new form into the database.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $args Array of column => value pairs for the new database row.
	 * @return int|false The ID of the new form, or false on failure.
	 */
	protected function insert_into_db( $args ) {
		$args = $this->map_args( $args );

		$result = wp_insert_post( $args, true );
		if ( is_wp_error( $result ) ) {
			return false;
		}

		return $result;
	}

	/**
	 * Internal method to update an existing form in the database.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int   $form_id ID of the form to update.
	 * @param array $args    Array of column => value pairs to update in the database row.
	 * @return bool True on success, or false on failure.
	 */
	protected function update_in_db( $form_id, $args ) {
		$args = $this->map_args( $args );
		$args['ID'] = $form_id;

		$result = wp_update_post( $args, true );
		if ( is_wp_error( $result ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Internal method to delete a form from the database.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int $form_id ID of the form to delete.
	 * @return bool True on success, or false on failure.
	 */
	protected function delete_from_db( $form_id ) {
		return (bool) wp_delete_post( $form_id, true );
	}

	/**
	 * Internal method to fetch a form from the database.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int $form_id ID of the form to fetch.
	 * @return WP_Post|null Post object of the form, or null if not found.
	 */
	protected function fetch_from_db( $form_id ) {
		$post = get_post( $form_id );
		if ( ! $post || $this->get_prefix() . 'form' !== $post->post_type ) {
			return null;
		}

		return $post;
	}

	/**
	 * Maps form arguments to regular post arguments.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $args Arguments as `$property => $value` pairs.
	 * @return array Mapped arguments.
	 */
	protected function map_args( $args ) {
		$mapped_args = array();
		foreach ( $args as $property => $value ) {
			switch ( $property ) {
				case 'slug':
					$mapped_args['post_name'] = $value;
					break;
				case 'timestamp':
					$mapped_args['post_date'] = '0000-00-00 00:00:00';
					$mapped_args['post_date_gmt'] = date( 'Y-m-d H:i:s', $value );
					break;
				case 'timestamp_modified':
					$mapped_args['post_modified'] = '0000-00-00 00:00:00';
					$mapped_args['post_modified_gmt'] = date( 'Y-m-d H:i:s', $value );
					break;
				case 'title':
				case 'author':
				case 'status':
					$mapped_args[ 'post_' . $property ] = $value;
			}
		}

		$mapped_args['post_type'] = $this->get_prefix() . 'form';

		return $mapped_args;
	}

	/**
	 * Sets up all action and filter hooks for the service.
	 *
	 * This method must be implemented and then be called from the constructor.
	 *
	 * @since 1.0.0
	 * @access protected
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

		$this->actions[] = array(
			'name'     => "save_post_{$this->get_prefix()}form",
			'callback' => array( $this->edit_page_handler, 'maybe_handle_save_request' ),
			'priority' => 10,
			'num_args' => 1,
		);
		$this->actions[] = array(
			'name'     => "add_meta_boxes_{$this->get_prefix()}form",
			'callback' => array( $this->edit_page_handler, 'maybe_add_meta_boxes' ),
			'priority' => 10,
			'num_args' => 1,
		);
		$this->actions[] = array(
			'name'     => 'admin_enqueue_scripts',
			'callback' => array( $this->edit_page_handler, 'maybe_enqueue_assets' ),
			'priority' => 10,
			'num_args' => 1,
		);
	}
}
