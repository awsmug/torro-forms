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
	 * Internally cached slug for the attachment taxonomy to use for form uploads.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $attachment_taxonomy_slug = '';

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
	 * Gets the slug for the attachment taxonomy that should be used for form uploads.
	 *
	 * If a hierarchical attachment taxonomy has already been registered, the method will
	 * try its best to make a correct guess on which taxonomy to use. A filter is available
	 * to override this.
	 *
	 * @since 1.0.0
	 *
	 * @return string Taxonomy slug, or empty string if attachment taxonomies should not be used.
	 */
	public function get_attachment_taxonomy_slug() {
		$prefix = $this->get_prefix();

		if ( ! empty( $this->attachment_taxonomy_slug ) ) {
			$taxonomy_slug = $this->attachment_taxonomy_slug;
		} else {
			$taxonomy_slug = 'attachment_category';

			// If a hierarchical taxonomy has already been registered, make the best guess to use the right one.
			$attachment_taxonomies = get_object_taxonomies( 'attachment', 'objects' );
			if ( ! empty( $attachment_taxonomies ) ) {
				$attachment_taxonomies = array_keys( wp_list_filter( $attachment_taxonomies, array( 'hierarchical' => true ) ) );
				if ( ! empty( $attachment_taxonomies ) ) {
					if ( in_array( 'attachment_category', $attachment_taxonomies, true ) ) {
						$taxonomy_slug = 'attachment_category';
					} elseif ( in_array( 'category', $attachment_taxonomies, true ) ) {
						$taxonomy_slug = 'category';
					} else {
						$taxonomy_slug = $attachment_taxonomies[0];
					}
				}
			}
		}

		/**
		 * Filters the slug for the attachment taxonomy that should be used for form uploads.
		 *
		 * An empty string may be returned in order to not use attachment taxonomies at all.
		 *
		 * @since 1.0.0
		 *
		 * @param string $taxonomy_slug The taxonomy slug, or an empty string.
		 */
		return apply_filters( "{$prefix}get_attachment_taxonomy_slug", $taxonomy_slug );
	}

	/**
	 * Gets the ID for the attachment taxonomy term that should be used for form uploads.
	 *
	 * The ID identifies a term of the attachment taxonomy to use for form uploads.
	 *
	 * @since 1.0.0
	 *
	 * @see Taxonomy_Manager::get_attachment_taxonomy_slug()
	 *
	 * @return int Taxonomy term ID, or 0 if attachment taxonomies should not be used.
	 */
	public function get_attachment_taxonomy_term_id() {
		$taxonomy_slug = $this->get_attachment_taxonomy_slug();
		if ( empty( $taxonomy_slug ) ) {
			return 0;
		}

		$options = $this->options()->get( 'general_settings', array() );
		$term_id = ! empty( $options['attachment_taxonomy_term_id'] ) ? $options['attachment_taxonomy_term_id'] : 0;
		if ( empty( $term_id ) ) {
			return 0;
		}

		$term = get_term_by( 'id', $term_id, $taxonomy_slug );
		if ( ! $term ) {
			return 0;
		}

		return (int) $term->term_id;
	}

	/**
	 * Registers the form category taxonomy.
	 *
	 * @since 1.0.0
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
	 * Registers the attachment category taxonomy if necessary.
	 *
	 * @since 1.0.0
	 */
	protected function maybe_register_attachment_category_taxonomy() {
		$taxonomy_slug = $this->get_attachment_taxonomy_slug();
		if ( empty( $taxonomy_slug ) ) {
			return;
		}

		$this->attachment_taxonomy_slug = $taxonomy_slug;

		if ( taxonomy_exists( $taxonomy_slug ) ) {
			return;
		}

		$args = array(
			'public'                => false,
			'show_ui'               => true,
			'hierarchical'          => true,
			'show_in_menu'          => true,
			'show_in_nav_menus'     => false,
			'show_tagcloud'         => false,
			'show_admin_column'     => true,
			'capabilities'          => array(
				'manage_terms' => 'upload_files',
				'edit_terms'   => 'upload_files',
				'delete_terms' => 'upload_files',
				'assign_terms' => 'upload_files',
			),
			'rewrite'               => false,
			'update_count_callback' => '_update_generic_term_count',
		);

		$args['object_type'] = array( 'attachment' );

		$this->register( $taxonomy_slug, $args );
	}

	/**
	 * Creates the default attachment taxonomy term if necessary.
	 *
	 * @since 1.0.0
	 */
	protected function create_default_attachment_taxonomy_term() {
		$taxonomy_slug = $this->get_attachment_taxonomy_slug();
		if ( empty( $taxonomy_slug ) ) {
			return;
		}

		$options = $this->options()->get( 'general_settings', array() );
		if ( isset( $options['attachment_taxonomy_term_id'] ) ) {
			return;
		}

		$result = wp_insert_term( __( 'Form Upload', 'torro-forms' ), $taxonomy_slug );
		if ( is_wp_error( $result ) ) {
			return;
		}

		$options['attachment_taxonomy_term_id'] = (int) $result['term_id'];

		$this->options()->update( 'general_settings', $options );
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
				'callback' => array( $this, 'register_form_category_taxonomy' ),
				'priority' => 1,
				'num_args' => 0,
			),
			array(
				'name'     => 'init',
				'callback' => array( $this, 'maybe_register_attachment_category_taxonomy' ),
				'priority' => 9999,
				'num_args' => 0,
			),
			array(
				'name'     => "{$this->get_prefix()}install",
				'callback' => array( $this, 'create_default_attachment_taxonomy_term' ),
				'priority' => 10,
				'num_args' => 0,
			),
		);
	}
}
