<?php
/**
 * Form edit page handler class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Forms;

/**
 * Class for handling form edit page behavior.
 *
 * @since 1.0.0
 */
class Form_Edit_Page_Handler {

	/**
	 * Form manager instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var awsmug\Torro_Forms\DB_Objects\Forms\Form_Manager
	 */
	protected $form_manager;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param awsmug\Torro_Forms\DB_Objects\Forms\Form_Manager $form_manager Form manager instance.
	 */
	public function __construct( $form_manager ) {
		$this->form_manager = $form_manager;
	}

	/**
	 * Handles a save request for the page.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param awsmug\Torro_Forms\DB_Objects\Forms $form Current form.
	 */
	public function handle_save_request( $form ) {
		// Empty method body.
	}

	/**
	 * Adds meta boxes to the page.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param awsmug\Torro_Forms\DB_Objects\Forms $form Current form.
	 */
	public function add_meta_boxes( $form ) {
		// Empty method body.
	}

	/**
	 * Enqueues assets to load on the page.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function enqueue_assets() {
		// Empty method body.
	}

	/**
	 * Handles a save request if conditions are met.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $post_id Current post ID.
	 */
	public function maybe_handle_save_request( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( is_multisite() && ms_is_switched() ) {
			return;
		}

		$form = $this->form_manager->get( $post_id );
		if ( ! $form ) {
			return;
		}

		$this->handle_save_request( $form );
	}

	/**
	 * Adds meta boxes if conditions are met.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param WP_Post $post Current post.
	 */
	public function maybe_add_meta_boxes( $post ) {
		$form = $this->form_manager->get( $post->ID );
		if ( ! $form ) {
			return;
		}

		$this->add_meta_boxes( $form );
	}

	/**
	 * Enqueues assets to load if conditions are met.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $hook_suffix Current hook suffix.
	 */
	public function maybe_enqueue_assets( $hook_suffix ) {
		if ( 'post-new.php' !== $hook_suffix && 'post.php' !== $hook_suffix ) {
			return;
		}

		$target_post_type = $this->form_manager->get_prefix() . $this->form_manager->get_singular_slug();

		if ( empty( $_GET['post_type'] ) || $target_post_type !== $_GET['post_type'] ) {
			if ( empty( $_GET['post'] ) || $target_post_type !== get_post_type( $_GET['post'] ) ) {
				return;
			}
		}

		$this->enqueue_assets();
	}
}
