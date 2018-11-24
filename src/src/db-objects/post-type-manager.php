<?php
/**
 * Post type manager class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type_Managers\Post_Type_Manager as Post_Type_Manager_Base;
use Leaves_And_Love\Plugin_Lib\Traits\Container_Service_Trait;
use Leaves_And_Love\Plugin_Lib\Traits\Hook_Service_Trait;
use Leaves_And_Love\Plugin_Lib\Options;

/**
 * Class for managing post types.
 *
 * @since 1.0.0
 */
class Post_Type_Manager extends Post_Type_Manager_Base {
	use Container_Service_Trait, Hook_Service_Trait;

	/**
	 * The Option API service definition.
	 *
	 * @since 1.0.0
	 * @static
	 * @var string
	 */
	protected static $service_options = Options::class;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
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
	 * Registers the form post type.
	 *
	 * @since 1.0.0
	 */
	protected function register_form_post_type() {
		$options      = $this->options()->get( 'general_settings', array() );
		$rewrite_slug = ! empty( $options['slug'] ) ? $options['slug'] : _x( 'forms', 'default form rewrite slug', 'torro-forms' );

		$menu_icon = '<svg viewBox="0 -750 1500 1500" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M680 161 l0 -158 -74 -6 c-83 -6 -132 -27 -148 -63 -11 -24 -5 -94 19 -238 l6 -39 -79 54 c-109 75 -201 164 -231 222 -51 102 -38 214 32 276 46 40 175 80 335 105 14 2 51 4 83 5 l57 1 0 -159z m395 130 c175 -42 237 -81 265 -167 17 -50 7 -131 -23 -191 -30 -58 -122 -147 -231 -222 l-79 -54 6 39 c24 144 30 214 19 238 -16 36 -65 57 -148 63 l-74 6 0 160 0 160 93 -7 c50 -3 128 -15 172 -25z"/></svg>';

		$args = array(
			'labels'              => array(
				'name'                  => __( 'Forms', 'torro-forms' ),
				'singular_name'         => __( 'Form', 'torro-forms' ),
				'add_new'               => _x( 'Add New', 'form label', 'torro-forms' ),
				'add_new_item'          => __( 'Add New Form', 'torro-forms' ),
				'edit_item'             => __( 'Edit Form', 'torro-forms' ),
				'new_item'              => __( 'New Form', 'torro-forms' ),
				'view_item'             => __( 'View Form', 'torro-forms' ),
				'view_items'            => __( 'View Forms', 'torro-forms' ),
				'search_items'          => __( 'Search Forms', 'torro-forms' ),
				'not_found'             => __( 'No forms found.', 'torro-forms' ),
				'not_found_in_trash'    => __( 'No forms found in Trash.', 'torro-forms' ),
				'parent_item_colon'     => __( 'Parent Form:', 'torro-forms' ),
				'all_items'             => __( 'All Forms', 'torro-forms' ),
				'archives'              => __( 'Form Archives', 'torro-forms' ),
				'attributes'            => __( 'Form Attributes', 'torro-forms' ),
				'insert_into_item'      => __( 'Insert into form', 'torro-forms' ),
				'uploaded_to_this_item' => __( 'Uploaded to this form', 'torro-forms' ),
				'filter_items_list'     => __( 'Filter forms list', 'torro-forms' ),
				'items_list_navigation' => __( 'Forms list navigation', 'torro-forms' ),
				'items_list'            => __( 'Forms list', 'torro-forms' ),
				'menu_name'             => __( 'Forms', 'torro-forms' ),
			),
			'public'              => true,
			'hierarchical'        => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'menu_position'       => 50,
			'menu_icon'           => 'data:image/svg+xml;base64,' . base64_encode( $menu_icon ),
			'capability_type'     => array( $this->get_prefix() . 'form', $this->get_prefix() . 'forms' ),
			'map_meta_cap'        => true,
			'supports'            => array( 'title' ),
			'has_archive'         => false,
			'rewrite'             => array(
				'slug'       => $rewrite_slug,
				'with_front' => false,
				'ep_mask'    => EP_PERMALINK,
			),
		);

		$this->register( $this->get_prefix() . 'form', $args );

		$this->unregister_map_meta_caps();
	}

	/**
	 * Unregisters capabilities from being used in map_meta_cap().
	 *
	 * Those capabilities are already handled by the capability manager.
	 *
	 * @since 1.0.0
	 *
	 * @global array $post_type_meta_caps Used to store meta capabilities.
	 */
	protected function unregister_map_meta_caps() {
		global $post_type_meta_caps;

		$meta_caps = array(
			'read_' . $this->get_prefix() . 'form',
			'edit_' . $this->get_prefix() . 'form',
			'delete_' . $this->get_prefix() . 'form',
		);

		foreach ( $meta_caps as $meta_cap ) {
			if ( isset( $post_type_meta_caps[ $meta_cap ] ) ) {
				unset( $post_type_meta_caps[ $meta_cap ] );
			}
		}
	}

	/**
	 * Sets up all action and filter hooks for the service.
	 *
	 * This method must be implemented and then be called from the constructor.
	 *
	 * @since 1.0.0
	 */
	protected function setup_hooks() {
		$this->actions = array(
			array(
				'name'     => 'init',
				'callback' => array( $this, 'register_form_post_type' ),
				'priority' => 1,
				'num_args' => 0,
			),
		);
	}
}
