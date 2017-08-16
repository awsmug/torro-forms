<?php
/**
 * Taxonomy manager class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type_Managers\Taxonomy_Manager as Taxonomy_Manager_Base;
use Leaves_And_Love\Plugin_Lib\Traits\Container_Service_Trait;
use Leaves_And_Love\Plugin_Lib\Traits\Hook_Service_Trait;
use Leaves_And_Love\Plugin_Lib\Options;

/**
 * Class for managing taxonomies.
 *
 * @since 1.0.0
 */
class Taxonomy_Manager extends Taxonomy_Manager_Base {
	use Container_Service_Trait, Hook_Service_Trait;

	/**
	 * The Option API service definition.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 * @var string
	 */
	protected static $service_options = Options::class;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $prefix   The instance prefix.
	 * @param array  $services {
	 *     Array of service instances.
	 *
	 *     @type Options       $options       The Option API class instance.
	 *     @type Error_Handler $error_handler The error handler instance.
	 * }
	 */
	public function __construct( $prefix, $services ) {
		parent::__construct( $prefix );

		$this->set_services( $services );
		$this->setup_hooks();
	}

	/**
	 * Registers the form category taxonomy.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_form_category_taxonomy() {
		$rewrite_slug = _x( 'form-categories', 'default form category rewrite slug', 'torro-forms' );

		$args = array(
			'labels'            => array(
				'name'                       => __( 'Categories', 'torro-forms' ),
				'singular_name'              => __( 'Category', 'torro-forms' ),
				'search_items'               => __( 'Search Categories', 'torro-forms' ),
				'popular_items'              => __( 'Popular Categories', 'torro-forms' ),
				'all_items'                  => __( 'All Categories', 'torro-forms' ),
				'parent_item'                => __( 'Parent Category', 'torro-forms' ),
				'parent_item_colon'          => __( 'Parent Category:', 'torro-forms' ),
				'edit_item'                  => __( 'Edit Category', 'torro-forms' ),
				'view_item'                  => __( 'View Category', 'torro-forms' ),
				'update_item'                => __( 'Update Category', 'torro-forms' ),
				'add_new_item'               => __( 'Add New Category', 'torro-forms' ),
				'new_item_name'              => __( 'New Category Name', 'torro-forms' ),
				'separate_items_with_commas' => __( 'Separate categories with commas', 'torro-forms' ),
				'add_or_remove_items'        => __( 'Add or remove categories', 'torro-forms' ),
				'choose_from_most_used'      => __( 'Choose from the most used categories', 'torro-forms' ),
				'not_found'                  => __( 'No categories found.', 'torro-forms' ),
				'no_terms'                   => __( 'No categories', 'torro-forms' ),
				'items_list_navigation'      => __( 'Categories list navigation', 'torro-forms' ),
				'items_list'                 => __( 'Categories list', 'torro-forms' ),
				'menu_name'                  => __( 'Categories', 'torro-forms' ),
			),
			'public'            => false,
			'show_ui'           => true,
			'hierarchical'      => true,
			'show_in_menu'      => true,
			'show_in_nav_menus' => false,
			'show_tagcloud'     => false,
			'show_admin_column' => true,
			'capabilities'      => array(
				'manage_terms' => 'manage_' . $this->get_prefix() . 'form_categories',
				'edit_terms'   => 'edit_' . $this->get_prefix() . 'form_categories',
				'delete_terms' => 'delete_' . $this->get_prefix() . 'form_categories',
				'assign_terms' => 'edit_' . $this->get_prefix() . 'forms',
			),
			'rewrite'           => array(
				'slug'       => $rewrite_slug,
				'with_front' => false,
				'ep_mask'    => EP_NONE,
			),
		);

		$args['object_type'] = array( $this->get_prefix() . 'form' );

		$this->register( $this->get_prefix() . 'form_category', $args );
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
		$this->actions = array(
			array(
				'name'     => 'init',
				'callback' => array( $this, 'register_form_category_taxonomy' ),
				'priority' => 1,
				'num_args' => 0,
			),
		);
	}
}
