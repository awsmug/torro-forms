<?php
/**
 * Form edit page handler class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Forms;

use awsmug\Torro_Forms\Assets;
use Leaves_And_Love\Plugin_Lib\Fields\Field_Manager;
use Leaves_And_Love\Plugin_Lib\Fixes;
use WP_Post;
use WP_Error;

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
	 * @var Form_Manager
	 */
	private $form_manager;

	/**
	 * Array of meta boxes as `$id => $args` pairs.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $meta_boxes = array();

	/**
	 * Array of tabs as `$id => $args` pairs.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $tabs = array();

	/**
	 * Current form storage.
	 *
	 * @since 1.0.0
	 * @var Form|null
	 */
	private $current_form = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Form_Manager $form_manager Form manager instance.
	 */
	public function __construct( $form_manager ) {
		$this->form_manager = $form_manager;
	}

	/**
	 * Adds a meta box to the edit page.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id   Meta box identifier.
	 * @param array  $args {
	 *     Optional. Meta box arguments.
	 *
	 *     @type string $title       Meta box title.
	 *     @type string $description Meta box description.
	 *     @type string $context     Meta box content. Either 'normal', 'advanced' or 'side'. Default 'advanced'.
	 *     @type string $priority    Meta box priority. Either 'high', 'core', 'default' or 'low'. Default 'default'.
	 * }
	 */
	public function add_meta_box( $id, $args ) {
		$prefix = $this->form_manager->get_prefix();

		if ( 0 !== strpos( $id, $prefix ) ) {
			$id = $prefix . $id;
		}

		$this->meta_boxes[ $id ] = wp_parse_args(
			$args,
			array(
				'title'       => '',
				'description' => '',
				'content'     => 'advanced',
				'priority'    => 'default',
			)
		);

		$services = array(
			'ajax'          => $this->form_manager->ajax(),
			'assets'        => $this->form_manager->assets(),
			'error_handler' => $this->form_manager->error_handler(),
		);

		$this->meta_boxes[ $id ]['field_manager'] = new Field_Manager(
			$prefix,
			$services,
			array(
				'get_value_callback'         => array( $this, 'get_meta_values' ),
				'get_value_callback_args'    => array( $id ),
				'update_value_callback'      => array( $this, 'update_meta_values' ),
				'update_value_callback_args' => array( $id, '{value}' ),
				'name_prefix'                => $id,
				'render_mode'                => 'form-table',
				'field_required_markup'      => '<span class="screen-reader-text">' . _x( '(required)', 'field required indicator', 'torro-forms' ) . '</span><span class="torro-required-indicator" aria-hidden="true">*</span>',
			)
		);
	}

	/**
	 * Adds a tab to the edit page.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id   Tab identifier.
	 * @param array  $args {
	 *     Optional. Tab arguments.
	 *
	 *     @type string $title       Tab title.
	 *     @type string $description Tab description.
	 *     @type string $meta_box    Identifier of the meta box this tab should belong to.
	 * }
	 */
	public function add_tab( $id, $args ) {
		if ( ! empty( $args['meta_box'] ) ) {
			$prefix = $this->form_manager->get_prefix();

			if ( 0 !== strpos( $args['meta_box'], $prefix ) ) {
				$args['meta_box'] = $prefix . $args['meta_box'];
			}
		}

		$this->tabs[ $id ] = wp_parse_args(
			$args,
			array(
				'title'       => '',
				'description' => '',
				'meta_box'    => '',
			)
		);
	}

	/**
	 * Adds a field to the edit page.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id      Field identifier.
	 * @param string $type    Identifier of the type.
	 * @param array  $args    {
	 *     Optional. Field arguments. See the field class constructor for further arguments.
	 *
	 *     @type string $tab           Tab identifier this field belongs to. Default empty.
	 *     @type string $label         Field label. Default empty.
	 *     @type string $description   Field description. Default empty.
	 *     @type mixed  $default       Default value for the field. Default null.
	 *     @type array  $input_classes Array of CSS classes for the field input. Default empty array.
	 *     @type array  $label_classes Array of CSS classes for the field label. Default empty array.
	 *     @type array  $input_attrs   Array of additional input attributes as `$key => $value` pairs.
	 *                                 Default empty array.
	 * }
	 */
	public function add_field( $id, $type, $args = array() ) {
		if ( isset( $args['tab'] ) ) {
			$args['section'] = $args['tab'];
			unset( $args['tab'] );
		}

		if ( ! isset( $args['section'] ) ) {
			return;
		}

		if ( ! isset( $this->tabs[ $args['section'] ] ) ) {
			return;
		}

		if ( ! isset( $this->meta_boxes[ $this->tabs[ $args['section'] ]['meta_box'] ] ) ) {
			return;
		}

		$meta_box_args = $this->meta_boxes[ $this->tabs[ $args['section'] ]['meta_box'] ];
		$meta_box_args['field_manager']->add( $id, $type, $args );
	}

	/**
	 * Renders form canvas if conditions are met.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post Current post.
	 */
	public function maybe_render_form_canvas( $post ) {
		$form = $this->form_manager->get( $post->ID );
		if ( ! $form ) {
			return;
		}

		$this->render_form_canvas( $form );
	}

	/**
	 * Adds meta boxes if conditions are met.
	 *
	 * @since 1.0.0
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
	 *
	 * @param string $hook_suffix Current hook suffix.
	 */
	public function maybe_enqueue_assets( $hook_suffix ) {
		if ( 'post-new.php' !== $hook_suffix && 'post.php' !== $hook_suffix ) {
			return;
		}

		$target_post_type = $this->form_manager->get_prefix() . $this->form_manager->get_singular_slug();

		$post_type = filter_input( INPUT_GET, 'post_type' );
		$post_id   = filter_input( INPUT_GET, 'post', FILTER_VALIDATE_INT );

		if ( empty( $post_type ) || $target_post_type !== $post_type ) {
			if ( empty( $post_id ) || get_post_type( $post_id ) !== $target_post_type ) {
				return;
			}
		}

		$this->enqueue_assets();
	}

	/**
	 * Prints templates if conditions are met.
	 *
	 * @since 1.0.0
	 */
	public function maybe_print_templates() {
		$target_post_type = $this->form_manager->get_prefix() . $this->form_manager->get_singular_slug();

		$post_type = filter_input( INPUT_GET, 'post_type' );
		$post_id   = filter_input( INPUT_GET, 'post', FILTER_VALIDATE_INT );

		if ( empty( $post_type ) || $target_post_type !== $post_type ) {
			if ( empty( $post_id ) || get_post_type( $post_id ) !== $target_post_type ) {
				return;
			}
		}

		$this->print_templates();
	}

	/**
	 * Handles a save request if conditions are met.
	 *
	 * @since 1.0.0
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
	 * Callback to get meta values for a specific meta box identifier.
	 *
	 * @since 1.0.0
	 *
	 * @param string $meta_box_id Meta box identifier.
	 * @return array Meta values stored for the meta box.
	 */
	public function get_meta_values( $meta_box_id ) {
		if ( ! $this->current_form ) {
			return array();
		}

		return $this->form_manager->get_meta( $this->current_form->id, $meta_box_id, true );
	}

	/**
	 * Callback to update meta values for a specific meta box identifier.
	 *
	 * @since 1.0.0
	 * @since 1.0.1 Added the return value.
	 *
	 * @param string $meta_box_id Meta box identifier.
	 * @param array  $values      Meta values to store for the meta box.
	 * @return bool|WP_Error True on success, error object on failure.
	 */
	public function update_meta_values( $meta_box_id, $values ) {
		if ( ! $this->current_form ) {
			return true;
		}

		$old_values = $this->form_manager->get_meta( $this->current_form->id, $meta_box_id, true );
		if ( $old_values === $values ) {
			return true;
		}

		if ( ! $this->form_manager->update_meta( $this->current_form->id, $meta_box_id, $values ) ) {
			$meta_box_title = ! empty( $this->meta_boxes[ $meta_box_id ]['title'] ) ? $this->meta_boxes[ $meta_box_id ]['title'] : $meta_box_id;

			/* translators: %s: meta box title */
			return new WP_Error( 'cannot_update_values', sprintf( __( 'An unknown error occurred while trying to save %s data.', 'torro-forms' ), $meta_box_title ) );
		}

		return true;
	}

	/**
	 * Handles the duplicate form action.
	 *
	 * Duplicates the form and redirects back to the referer URL.
	 *
	 * @since 1.0.0
	 */
	public function action_duplicate_form() {
		$nonce   = filter_input( INPUT_GET, '_wpnonce' );
		$form_id = filter_input( INPUT_GET, 'form_id', FILTER_VALIDATE_INT );

		if ( empty( $form_id ) ) {
			wp_die( esc_html__( 'Missing form ID.', 'torro-forms' ), '', 400 );
		}

		if ( empty( $nonce ) ) {
			wp_die( esc_html__( 'Missing nonce.', 'torro-forms' ), '', 400 );
		}

		if ( ! wp_verify_nonce( $nonce, $this->form_manager->get_prefix() . 'duplicate_form_' . $form_id ) ) {
			wp_die( esc_html__( 'Invalid nonce.', 'torro-forms' ), '', 403 );
		}

		$form = $this->form_manager->get( $form_id );
		if ( ! $form ) {
			wp_die( esc_html__( 'Invalid form ID.', 'torro-forms' ), '', 404 );
		}

		$new_form = $form->duplicate();
		if ( is_wp_error( $new_form ) ) {
			$feedback = array(
				'type'    => 'error',
				/* translators: 1: form title, 2: error message */
				'message' => sprintf( __( 'The form &#8220;%1$s&#8221; could not be duplicated: %2$s', 'torro-forms' ), $form->title, $new_form->get_error_message() ),
			);
		} else {
			$feedback = array(
				'type'    => 'success',
				/* translators: 1: form title, 2: new form edit URL */
				'message' => sprintf( __( 'The form &#8220;%1$s&#8221; was duplicated successfully. <a href="%2$s">View the duplicate</a>', 'torro-forms' ), $form->title, get_edit_post_link( $new_form->id ) ),
			);
		}

		$meta_key = $this->form_manager->get_prefix() . 'duplicate_feedback';

		$this->form_manager->update_meta( $form->id, $meta_key, $feedback );

		$redirect_url = add_query_arg( $meta_key, $form->id, wp_get_referer() );

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Displays feedback for errors that might have occurred during form save.
	 *
	 * @since 1.0.1
	 *
	 * @param WP_Post $post Post object.
	 */
	public function maybe_show_form_save_feedback( $post ) {
		$form = $this->form_manager->get( $post->ID );
		if ( ! $form ) {
			return;
		}

		$errors = get_transient( "{$this->form_manager->get_prefix()}_save_form_errors_{$form->id}" );
		if ( false === $errors ) {
			return;
		}

		delete_transient( "{$this->form_manager->get_prefix()}_save_form_errors_{$form->id}" );

		?>
		<div class="torro-notice notice notice-error">
			<p><?php esc_html_e( 'Some errors occurred while trying to save the form:', 'torro-forms' ); ?></p>
			<ul>
				<?php
				foreach ( $errors as $error_code => $error_messages ) {
					foreach ( $error_messages as $error_message ) {
						?>
						<li><?php echo wp_kses( $error_message, "{$this->form_manager->get_prefix()}error_message" ); ?></li>
						<?php
					}
				}
				?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Displays feedback from the duplicate form action when applicable.
	 *
	 * @since 1.0.0
	 */
	public function maybe_show_duplicate_form_feedback() {
		$meta_key = $this->form_manager->get_prefix() . 'duplicate_feedback';

		$form_id = filter_input( INPUT_GET, $meta_key, FILTER_VALIDATE_INT );
		if ( empty( $form_id ) ) {
			return;
		}

		unset( $_GET[ $meta_key ] );

		$feedback = $this->form_manager->get_meta( $form_id, $meta_key, true );
		if ( ! is_array( $feedback ) ) {
			return;
		}

		$this->form_manager->delete_meta( $form_id, $meta_key );

		?>
		<div class="notice notice-<?php echo esc_attr( $feedback['type'] ); ?>">
			<p>
				<?php
				echo wp_kses(
					$feedback['message'],
					array(
						'strong' => array(),
						'a'      => array(
							'href' => array(),
						),
					)
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Displays a button to duplicate a form when applicable.
	 *
	 * @since 1.0.0
	 *
	 * @param string  $output    Sample permalink HTML markup.
	 * @param int     $post_id   Post ID.
	 * @param string  $new_title New sample permalink title.
	 * @param string  $new_slug  New sample permalink slug.
	 * @param WP_Post $post      Post object.
	 * @return string Sample permalink HTML, possibly including the additional button.
	 */
	public function maybe_add_duplicate_button( $output, $post_id, $new_title, $new_slug, $post ) {
		$prefix = $this->form_manager->get_prefix();

		if ( $prefix . 'form' !== $post->post_type || 'auto-draft' === $post->post_status ) {
			return $output;
		}

		$nonce_action = $prefix . 'duplicate_form_' . $post->ID;
		$url          = wp_nonce_url( admin_url( 'admin.php?action=' . $prefix . 'duplicate_form&amp;form_id=' . $post->ID . '&amp;_wp_http_referer=' . rawurlencode( Fixes::php_filter_input( INPUT_SERVER, 'REQUEST_URI' ) ) ), $nonce_action );

		return $output . ' <a class="button button-small" href="' . esc_url( $url ) . '">' . esc_html( _x( 'Duplicate Form', 'action', 'torro-forms' ) ) . '</a>';
	}

	/**
	 * Displays a button to view form submissions when applicable.
	 *
	 * @since 1.0.0
	 *
	 * @param string  $output    Sample permalink HTML markup.
	 * @param int     $post_id   Post ID.
	 * @param string  $new_title New sample permalink title.
	 * @param string  $new_slug  New sample permalink slug.
	 * @param WP_Post $post      Post object.
	 * @return string Sample permalink HTML, possibly including the additional button.
	 */
	public function maybe_add_submissions_button( $output, $post_id, $new_title, $new_slug, $post ) {
		$prefix = $this->form_manager->get_prefix();

		if ( $prefix . 'form' !== $post->post_type || 'auto-draft' === $post->post_status ) {
			return $output;
		}

		$url = add_query_arg( 'form_id', $post_id, torro()->admin_pages()->get( 'list_submissions' )->url );

		return $output . ' <a class="button button-small" href="' . esc_url( $url ) . '">' . esc_html( _x( 'View Form Submissions', 'action', 'torro-forms' ) ) . '</a>';
	}

	/**
	 * Renders a read-only field containing the form shortcode markup for a post if applicable.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post Post object.
	 */
	public function maybe_render_shortcode( $post ) {
		$prefix = $this->form_manager->get_prefix();

		if ( $prefix . 'form' !== $post->post_type || 'auto-draft' === $post->post_status ) {
			return;
		}

		$this->form_manager->assets()->enqueue_script( 'clipboard' );
		$this->form_manager->assets()->enqueue_style( 'clipboard' );

		$id_attr = 'form-shortcode-' . $post->ID;

		?>
		<div class="misc-pub-section form-shortcode">
			<label for="<?php echo esc_attr( $id_attr ); ?>"><?php esc_html_e( 'Form Shortcode:', 'torro-forms' ); ?></label>
			<input id="<?php echo esc_attr( $id_attr ); ?>" class="clipboard-field" value="<?php echo esc_attr( sprintf( "[{$this->form_manager->get_prefix()}form id=&quot;%d&quot;]", $post->ID ) ); ?>" readonly="readonly" />
			<button type="button" class="clipboard-button button" data-clipboard-target="#<?php echo esc_attr( $id_attr ); ?>">
				<?php $this->form_manager->assets()->render_icon( 'torro-icon-clippy', __( 'Copy to clipboard', 'torro-forms' ) ); ?>
			</button>
		</div>
		<?php
	}

	/**
	 * Prints a 'novalidate' attribute for the post form if conditions are met.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post Post for which the form is currently being printed.
	 */
	public function maybe_print_post_form_novalidate( $post ) {
		$form = $this->form_manager->get( $post->ID );
		if ( ! $form ) {
			return;
		}

		echo ' novalidate="novalidate"';
	}

	/**
	 * Renders form canvas.
	 *
	 * @since 1.0.0
	 *
	 * @param Form $form Current form.
	 */
	private function render_form_canvas( $form ) {
		?>
		<div id="torro-form-canvas" class="torro-form-canvas">
			<div class="torro-form-canvas-header torro-form-canvas-tabs" role="tablist">
				<button type="button" class="torro-form-canvas-tab add-button is-active" disabled="disabled">
					<span aria-hidden="true">+</span><span class="screen-reader-text"><?php esc_html_e( 'Add New Container', 'torro-forms' ); ?></span>
				</button>
			</div>
			<div class="torro-form-canvas-content">
				<div class="drag-drop-area is-empty">
					<div class="content loader-content hide-if-no-js">
						<?php esc_html_e( 'Loading form builder...', 'torro-forms' ); ?>
						<span class="spinner is-active"></span>
					</div>
					<div class="torro-notice notice-warning hide-if-js">
						<p>
							<?php esc_html_e( 'It seems you have disabled JavaScript in your browser. Torro Forms requires JavaScript in order to edit your forms.', 'torro-forms' ); ?>
						</p>
					</div>
				</div>
			</div>
			<div class="torro-form-canvas-footer"></div>
		</div>
		<?php
	}

	/**
	 * Adds meta boxes to the page.
	 *
	 * @since 1.0.0
	 *
	 * @param Form $form Current form.
	 */
	private function add_meta_boxes( $form ) {
		$this->current_form = $form;

		if ( ! did_action( "{$this->form_manager->get_prefix()}add_form_meta_content" ) ) {
			/**
			 * Fires when meta boxes for the form edit page should be added.
			 *
			 * @since 1.0.0
			 *
			 * @param Form_Edit_Page_Handler $edit_page Form edit page.
			 */
			do_action( "{$this->form_manager->get_prefix()}add_form_meta_content", $this );
		}

		$meta_box_cb = function( $post, $box ) {
			$prefix = $this->form_manager->get_prefix();

			echo '<input type="hidden" id="' . esc_attr( $box['id'] . '-field-manager-instance' ) . '" value="' . esc_attr( $box['args']['field_manager']->get_instance_id() ) . '" />';

			if ( ! empty( $box['args']['description'] ) ) {
				echo '<p class="description">' . wp_kses_data( $box['args']['description'] ) . '</p>';
			}

			$tab_id_prefix      = 'metabox-' . $box['id'] . '-tab-';
			$tabpanel_id_prefix = 'metabox-' . $box['id'] . '-tabpanel-';

			$tabs = wp_list_filter( $this->tabs, array( 'meta_box' => $box['id'] ) );

			$first = true;

			/**
			 * Fires before a form meta box is rendered.
			 *
			 * The dynamic portion of the hook name refers to the meta box identifier.
			 *
			 * @since 1.0.0
			 *
			 * @param int $form_id Current form ID.
			 */
			do_action( "{$prefix}metabox_{$box['id']}_before", $this->current_form->id );

			?>
			<h3 class="torro-metabox-tab-wrapper" role="tablist">
				<?php foreach ( $tabs as $id => $args ) : ?>
					<a id="<?php echo esc_attr( $tab_id_prefix . $id ); ?>" class="torro-metabox-tab" href="<?php echo esc_attr( '#' . $tabpanel_id_prefix . $id ); ?>" aria-controls="<?php echo esc_attr( $tabpanel_id_prefix . $id ); ?>" aria-selected="<?php echo $first ? 'true' : 'false'; ?>" role="tab">
						<?php echo wp_kses_data( $args['title'] ); ?>
					</a>
					<?php $first = false; ?>
				<?php endforeach; ?>
			</h3>
			<?php $first = true; ?>
			<?php foreach ( $tabs as $id => $args ) : ?>
				<div id="<?php echo esc_attr( $tabpanel_id_prefix . $id ); ?>" class="torro-metabox-tab-panel" aria-labelledby="<?php echo esc_attr( $tab_id_prefix . $id ); ?>" aria-hidden="<?php echo $first ? 'false' : 'true'; ?>" role="tabpanel">
					<?php

					/**
					 * Fires before a form meta box tab is rendered.
					 *
					 * The dynamic portions of the hook name refer to the meta box identifier and
					 * tab identifier respectively.
					 *
					 * @since 1.0.0
					 *
					 * @param int $form_id Current form ID.
					 */
					do_action( "{$prefix}metabox_{$box['id']}_tab_{$id}_before", $this->current_form->id );

					?>
					<?php if ( ! empty( $args['description'] ) ) : ?>
						<p class="description"><?php echo wp_kses_data( $args['description'] ); ?></p>
					<?php endif; ?>
					<table class="form-table">
						<?php $box['args']['field_manager']->render( $id ); ?>
					</table>
					<?php

					/**
					 * Fires after a form meta box tab has been rendered.
					 *
					 * The dynamic portions of the hook name refer to the meta box identifier and
					 * tab identifier respectively.
					 *
					 * @since 1.0.0
					 *
					 * @param int $form_id Current form ID.
					 */
					do_action( "{$prefix}metabox_{$box['id']}_tab_{$id}_after", $this->current_form->id );

					?>
				</div>
				<?php $first = false; ?>
			<?php endforeach; ?>
			<?php

			/**
			 * Fires after a form meta box has been rendered.
			 *
			 * The dynamic portion of the hook name refers to the meta box identifier.
			 *
			 * @since 1.0.0
			 *
			 * @param int $form_id Current form ID.
			 */
			do_action( "{$prefix}metabox_{$box['id']}_after", $this->current_form->id );
		};

		foreach ( $this->meta_boxes as $id => $args ) {
			add_meta_box( $id, $args['title'], $meta_box_cb, null, $args['context'], $args['priority'], $args );
		}

		/**
		 * Fires when meta boxes for the form edit page should be added.
		 *
		 * @since 1.0.0
		 *
		 * @param Form $form Form that is being edited.
		 */
		do_action( "{$this->form_manager->get_prefix()}add_form_meta_boxes", $form );
	}

	/**
	 * Enqueues assets to load on the page.
	 *
	 * @since 1.0.0
	 */
	private function enqueue_assets() {
		wp_enqueue_media();

		$this->form_manager->assets()->enqueue_script( 'admin-fixed-sidebar' );

		$this->form_manager->assets()->enqueue_script( 'admin-tooltip-descriptions' );
		$this->form_manager->assets()->enqueue_style( 'admin-tooltip-descriptions' );

		$this->form_manager->assets()->enqueue_script( 'admin-unload' );

		$this->form_manager->assets()->enqueue_script( 'admin-form-builder' );
		$this->form_manager->assets()->enqueue_style( 'admin-form-builder' );

		if ( ! did_action( "{$this->form_manager->get_prefix()}add_form_meta_content" ) ) {
			/** This action is documented in src/db-objects/forms/form-edit-page-handler.php */
			do_action( "{$this->form_manager->get_prefix()}add_form_meta_content", $this );
		}

		foreach ( $this->meta_boxes as $args ) {
			$args['field_manager']->enqueue();
		}

		/**
		 * Fires after scripts and stylesheets for the form builder have been enqueued.
		 *
		 * @since 1.0.0
		 *
		 * @param Assets $assets The Assets API instance.
		 */
		do_action( "{$this->form_manager->get_prefix()}enqueue_form_builder_scripts", $this->form_manager->assets() );
	}

	/**
	 * Prints templates to use in JavaScript.
	 *
	 * @since 1.0.0
	 */
	private function print_templates() {
		?>
		<script type="text/html" id="tmpl-torro-failure">
			<div class="torro-notice notice-error">
				<p>
					<strong><?php esc_html_e( 'Error:', 'torro-forms' ); ?></strong>
					{{ data.message }}
				</p>
			</div>
		</script>

		<script type="text/html" id="tmpl-torro-form-canvas">
			<div class="torro-form-canvas-header torro-form-canvas-tabs">
				<button type="button" class="torro-form-canvas-tab add-button">
					<span aria-hidden="true">+</span><span class="screen-reader-text"><?php esc_html_e( 'Add New Container', 'torro-forms' ); ?></span>
				</button>
			</div>
			<div class="torro-form-canvas-content">
				<div class="torro-form-canvas-panel add-panel">
					<div class="drag-drop-area is-empty">
						<div class="content"><?php esc_html_e( 'Click the button above to add your first container', 'torro-forms' ); ?></div>
					</div>
				</div>
			</div>
			<div class="torro-form-canvas-footer"></div>
		</script>

		<script type="text/html" id="tmpl-torro-container-tab">
			<span>{{ data.label }}</span>
		</script>

		<script type="text/html" id="tmpl-torro-container-panel">
			<div class="drag-drop-area"></div>
			<div class="add-element-wrap">
				<div class="add-element-toggle-wrap">
					<button type="button" class="add-element-toggle button">
						<?php esc_html_e( 'Add element', 'torro-forms' ); ?>
					</button>
				</div>
			</div>

			<input type="hidden" name="<?php echo esc_attr( $this->form_manager->get_prefix() . 'containers[{{ data.id }}][form_id]' ); ?>" value="{{ data.form_id }}" />
			<input type="hidden" name="<?php echo esc_attr( $this->form_manager->get_prefix() . 'containers[{{ data.id }}][label]' ); ?>" value="{{ data.label }}" />
			<input type="hidden" name="<?php echo esc_attr( $this->form_manager->get_prefix() . 'containers[{{ data.id }}][sort]' ); ?>" value="{{ data.sort }}" />
		</script>

		<script type="text/html" id="tmpl-torro-container-footer-panel">
			<button type="button" class="button-link button-link-delete delete-container-button">
				<?php esc_html_e( 'Delete Page', 'torro-forms' ); ?>
			</button>
		</script>

		<script type="text/html" id="tmpl-torro-element">
			<div class="torro-element-header">
				<# if ( ! _.isEmpty( data.type.icon_css_class ) ) { #>
					<span class="torro-element-header-icon {{ data.type.icon_css_class }}" aria-hidden="true"></span>
				<# } else if ( ! _.isEmpty( data.type.icon_svg_id ) ) { #>
					<svg class="torro-icon torro-element-header-icon" aria-hidden="true" role="img">
						<use href="#{{ data.type.icon_svg_id }}" xlink:href="#{{ data.type.icon_svg_id }}"></use>
					</svg>
				<# } else { #>
					<img class="torro-element-header-icon" src="{{ data.type.icon_url }}" alt="">
				<# } #>
				<span class="torro-element-header-title">
					{{ ! _.isEmpty( data.elementHeader ) ? data.elementHeader : data.type.title }}
				</span>
				<button type="button" class="torro-element-expand-button" aria-controls="torro-element-{{ data.id }}-content" aria-expanded="{{ data.active ? 'true' : 'false' }}">
					<span class="torro-element-expand-button-icon" aria-hidden="true"></span><span class="screen-reader-text">{{ data.active ? '<?php esc_html_e( 'Hide Content', 'torro-forms' ); ?>' : '<?php esc_html_e( 'Show Content', 'torro-forms' ); ?>' }}</span>
				</button>
			</div>
			<div id="torro-element-{{ data.id }}-content" class="{{ data.active ? 'torro-element-content is-expanded' : 'torro-element-content' }}" role="region">
				<div class="torro-element-content-main">
					<div class="torro-element-content-tabs"></div>
					<div class="torro-element-content-panels"></div>
				</div>
				<div class="torro-element-content-footer">
					<button type="button" class="button-link button-link-delete delete-element-button">
						<?php esc_html_e( 'Delete Element', 'torro-forms' ); ?>
					</button>
				</div>
			</div>
			<input type="hidden" name="<?php echo esc_attr( $this->form_manager->get_prefix() . 'elements[{{ data.id }}][container_id]' ); ?>" value="{{ data.container_id }}" />
			<input type="hidden" name="<?php echo esc_attr( $this->form_manager->get_prefix() . 'elements[{{ data.id }}][type]' ); ?>" value="{{ data.type.slug }}" />
			<input type="hidden" name="<?php echo esc_attr( $this->form_manager->get_prefix() . 'elements[{{ data.id }}][sort]' ); ?>" value="{{ data.sort }}" />
		</script>

		<script type="text/html" id="tmpl-torro-element-section-tab">
			<button type="button" id="element-tab-{{ data.elementId }}-{{ data.slug }}" class="torro-element-content-tab torro-element-content-tab-{{ data.slug }}" data-slug="{{ data.slug }}" aria-controls="element-panel-{{ data.elementId }}-{{ data.slug }}" aria-selected="{{ data.active ? 'true' : 'false' }}" role="tab">
				{{ data.title }}
			</button>
		</script>

		<script type="text/html" id="tmpl-torro-element-section-panel">
			<div id="element-panel-{{ data.elementId }}-{{ data.slug }}" class="torro-element-content-panel torro-element-content-panel-{{ data.slug }}" aria-labelledby="element-tab-{{ data.elementId }}-{{ data.slug }}" aria-hidden="{{ data.active ? 'false' : 'true' }}" role="tabpanel">
				<table class="torro-element-fields form-table"></table>
			</div>
		</script>

		<script type="text/html" id="tmpl-torro-element-field">
			<tr{{{ _.attrs( data.wrapAttrs ) }}}>
				<th scope="row">
					<div id="{{ data.id }}-label-wrap" class="label-wrap"></div>
				</th>
				<td>
					<div id="{{ data.id }}-content-wrap" class="content-wrap"></div>
					<# if ( data._element_setting ) { #>
						<input type="hidden" name="<?php echo esc_attr( $this->form_manager->get_prefix() . 'element_settings[{{ data._element_setting.id }}][element_id]' ); ?>" value="{{ data._element_setting.element_id }}" />
						<input type="hidden" name="<?php echo esc_attr( $this->form_manager->get_prefix() . 'element_settings[{{ data._element_setting.id }}][name]' ); ?>" value="{{ data._element_setting.name }}" />
					<# } #>
				</td>
			</tr>
		</script>

		<script type="text/html" id="tmpl-torro-add-element-frame">
			<div class="media-frame-menu"></div>
			<div class="media-frame-title"></div>
			<div class="media-frame-content"></div>
			<div class="media-frame-toolbar"></div>
		</script>

		<script type="text/html" id="tmpl-torro-element-types-browser">
			<div class="torro-element-types">
				<# _.each( data.elementTypes, function( elementType ) { #>
					<div class="torro-element-type torro-element-type-{{ elementType.slug }}{{ elementType.slug === data.selectedElementType ? ' is-selected' : '' }}" data-slug="{{ elementType.slug }}" tabindex="0">
						<div class="torro-element-type-header">
							<# if ( ! _.isEmpty( elementType.icon_css_class ) ) { #>
								<span class="torro-element-type-header-icon {{ elementType.icon_css_class }}" aria-hidden="true"></span>
							<# } else if ( ! _.isEmpty( elementType.icon_svg_id ) ) { #>
								<svg class="torro-icon torro-element-type-header-icon" aria-hidden="true" role="img">
									<use href="#{{ elementType.icon_svg_id }}" xlink:href="#{{ elementType.icon_svg_id }}"></use>
								</svg>
							<# } else { #>
								<img class="torro-element-type-header-icon" src="{{ elementType.icon_url }}" alt="">
							<# } #>
							<span class="torro-element-type-header-title">
								{{ elementType.title }}
							</span>
						</div>
						<div class="torro-element-type-content">
							<p>{{ elementType.description }}</p>
						</div>
					</div>
				<# } ); #>
			</div>
		</script>
		<?php

		/**
		 * Fires after templates for the form builder have been printed.
		 *
		 * @since 1.0.0
		 */
		do_action( "{$this->form_manager->get_prefix()}print_form_builder_templates" );
	}

	/**
	 * Handles a save request for the page.
	 *
	 * @since 1.0.0
	 *
	 * @param Form $form Current form.
	 */
	private function handle_save_request( $form ) {
		$this->current_form = $form;

		$mappings = array(
			'forms'            => array(
				$form->id => $form->id,
			),
			'containers'       => array(),
			'elements'         => array(),
			'element_choices'  => array(),
			'element_settings' => array(),
		);

		$errors = new WP_Error();

		if ( isset( $_POST[ $this->form_manager->get_prefix() . 'containers' ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
			$mappings = $this->save_containers( wp_unslash( $_POST[ $this->form_manager->get_prefix() . 'containers' ] ), $mappings, $errors ); // phpcs:ignore WordPress.Security
		}

		if ( isset( $_POST[ $this->form_manager->get_prefix() . 'elements' ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
			$mappings = $this->save_elements( wp_unslash( $_POST[ $this->form_manager->get_prefix() . 'elements' ] ), $mappings, $errors ); // phpcs:ignore WordPress.Security
		}

		if ( isset( $_POST[ $this->form_manager->get_prefix() . 'element_choices' ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
			$mappings = $this->save_element_choices( wp_unslash( $_POST[ $this->form_manager->get_prefix() . 'element_choices' ] ), $mappings, $errors ); // phpcs:ignore WordPress.Security
		}

		if ( isset( $_POST[ $this->form_manager->get_prefix() . 'element_settings' ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
			$mappings = $this->save_element_settings( wp_unslash( $_POST[ $this->form_manager->get_prefix() . 'element_settings' ] ), $mappings, $errors ); // phpcs:ignore WordPress.Security
		}

		if ( filter_has_var( INPUT_POST, $this->form_manager->get_prefix() . 'deleted_containers' ) ) {
			$this->delete_containers( filter_input( INPUT_POST, $this->form_manager->get_prefix() . 'deleted_containers', FILTER_VALIDATE_INT, FILTER_FORCE_ARRAY ) );
		}

		if ( filter_has_var( INPUT_POST, $this->form_manager->get_prefix() . 'deleted_elements' ) ) {
			$this->delete_elements( filter_input( INPUT_POST, $this->form_manager->get_prefix() . 'deleted_elements', FILTER_VALIDATE_INT, FILTER_FORCE_ARRAY ) );
		}

		if ( filter_has_var( INPUT_POST, $this->form_manager->get_prefix() . 'deleted_element_choices' ) ) {
			$this->delete_element_choices( filter_input( INPUT_POST, $this->form_manager->get_prefix() . 'deleted_element_choices', FILTER_VALIDATE_INT, FILTER_FORCE_ARRAY ) );
		}

		if ( filter_has_var( INPUT_POST, $this->form_manager->get_prefix() . 'deleted_element_settings' ) ) {
			$this->delete_element_settings( filter_input( INPUT_POST, $this->form_manager->get_prefix() . 'deleted_element_settings', FILTER_VALIDATE_INT, FILTER_FORCE_ARRAY ) );
		}

		if ( ! did_action( "{$this->form_manager->get_prefix()}add_form_meta_content" ) ) {
			/** This action is documented in src/db-objects/forms/form-edit-page-handler.php */
			do_action( "{$this->form_manager->get_prefix()}add_form_meta_content", $this );
		}

		foreach ( $this->meta_boxes as $id => $args ) {
			if ( isset( $_POST[ $id ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
				$metabox_result = $args['field_manager']->update_values( wp_unslash( $_POST[ $id ] ) ); // phpcs:ignore WordPress.Security
				if ( is_wp_error( $metabox_result ) ) {
					foreach ( $metabox_result->errors as $error_code => $error_messages ) {
						foreach ( $error_messages as $error_message ) {
							$errors->add( $error_code, $error_message );
						}
					}
				}
			}
		}

		/**
		 * Fires after a form has been saved.
		 *
		 * @since 1.0.0
		 * @since 1.0.1 Added the $errors parameter.
		 *
		 * @param Form     $form     Form that has been saved.
		 * @param array    $mappings Array of ID mappings from the objects that have been saved.
		 * @param WP_Error $errors   Error object to add possible errors to.
		 */
		do_action( "{$this->form_manager->get_prefix()}save_form", $form, $mappings, $errors );

		// Store save errors in a transient.
		if ( ! empty( $errors->errors ) ) {
			set_transient( "{$this->form_manager->get_prefix()}_save_form_errors_{$form->id}", $errors->errors, 30 );
		}
	}

	/**
	 * Saves containers.
	 *
	 * @since 1.0.0
	 *
	 * @param array    $containers Array of `$container_id => $container_data` pairs.
	 * @param array    $mappings   Array of mappings to pass-through and modify.
	 * @param WP_Error $errors     Error object to append errors to.
	 * @return array Modified mappings.
	 */
	private function save_containers( $containers, $mappings, $errors ) {
		$container_manager = $this->form_manager->get_child_manager( 'containers' );

		foreach ( $containers as $id => $data ) {
			$data['form_id'] = key( $mappings['forms'] );

			if ( $this->is_temp_id( $id ) ) {
				$container = $container_manager->create();
			} else {
				$container = $container_manager->get( $id );
				if ( ! $container ) {
					$container = $container_manager->create();
				}
			}

			foreach ( $data as $key => $value ) {
				$container->$key = $value;
			}

			$status = $container->sync_upstream();
			if ( is_wp_error( $status ) ) {
				$errors->add(
					$status->get_error_code(),
					$status->get_error_message(),
					array(
						'id'   => $id,
						'data' => $data,
					)
				);
			} else {
				$mappings['containers'][ $id ] = $container->id;
			}
		}

		return $mappings;
	}

	/**
	 * Saves elements.
	 *
	 * @since 1.0.0
	 *
	 * @param array    $elements Array of `$element_id => $element_data` pairs.
	 * @param array    $mappings Array of mappings to pass-through and modify.
	 * @param WP_Error $errors   Error object to append errors to.
	 * @return array Modified mappings.
	 */
	private function save_elements( $elements, $mappings, $errors ) {
		$element_manager = $this->form_manager->get_child_manager( 'containers' )->get_child_manager( 'elements' );

		foreach ( $elements as $id => $data ) {
			if ( empty( $data['container_id'] ) || ! isset( $mappings['containers'][ $data['container_id'] ] ) ) {
				continue;
			}

			$data['container_id'] = $mappings['containers'][ $data['container_id'] ];

			if ( $this->is_temp_id( $id ) ) {
				$element = $element_manager->create();
			} else {
				$element = $element_manager->get( $id );
				if ( ! $element ) {
					$element = $element_manager->create();
				}
			}

			foreach ( $data as $key => $value ) {
				$element->$key = $value;
			}

			$status = $element->sync_upstream();
			if ( is_wp_error( $status ) ) {
				$errors->add(
					$status->get_error_code(),
					$status->get_error_message(),
					array(
						'id'   => $id,
						'data' => $data,
					)
				);
			} else {
				$mappings['elements'][ $id ] = $element->id;
			}
		}

		return $mappings;
	}

	/**
	 * Saves element choices.
	 *
	 * @since 1.0.0
	 *
	 * @param array    $element_choices Array of `$element_choice_id => $element_choice_data` pairs.
	 * @param array    $mappings        Array of mappings to pass-through and modify.
	 * @param WP_Error $errors          Error object to append errors to.
	 * @return array Modified mappings.
	 */
	private function save_element_choices( $element_choices, $mappings, $errors ) {
		$element_choice_manager = $this->form_manager->get_child_manager( 'containers' )->get_child_manager( 'elements' )->get_child_manager( 'element_choices' );

		foreach ( $element_choices as $id => $data ) {
			if ( empty( $data['element_id'] ) || ! isset( $mappings['elements'][ $data['element_id'] ] ) ) {
				continue;
			}

			$data['element_id'] = $mappings['elements'][ $data['element_id'] ];

			if ( $this->is_temp_id( $id ) ) {
				$element_choice = $element_choice_manager->create();
			} else {
				$element_choice = $element_choice_manager->get( $id );
				if ( ! $element_choice ) {
					$element_choice = $element_choice_manager->create();
				}
			}

			foreach ( $data as $key => $value ) {
				$element_choice->$key = $value;
			}

			$status = $element_choice->sync_upstream();
			if ( is_wp_error( $status ) ) {
				$errors->add(
					$status->get_error_code(),
					$status->get_error_message(),
					array(
						'id'   => $id,
						'data' => $data,
					)
				);
			} else {
				$mappings['element_choices'][ $id ] = $element_choice->id;
			}
		}

		return $mappings;
	}

	/**
	 * Saves element settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array    $element_settings Array of `$element_setting_id => $element_setting_data` pairs.
	 * @param array    $mappings        Array of mappings to pass-through and modify.
	 * @param WP_Error $errors          Error object to append errors to.
	 * @return array Modified mappings.
	 */
	private function save_element_settings( $element_settings, $mappings, $errors ) {
		$element_setting_manager = $this->form_manager->get_child_manager( 'containers' )->get_child_manager( 'elements' )->get_child_manager( 'element_settings' );

		foreach ( $element_settings as $id => $data ) {
			if ( empty( $data['element_id'] ) || ! isset( $mappings['elements'][ $data['element_id'] ] ) ) {
				continue;
			}

			$data['element_id'] = $mappings['elements'][ $data['element_id'] ];

			if ( $this->is_temp_id( $id ) ) {
				$element_setting = $element_setting_manager->create();
			} else {
				$element_setting = $element_setting_manager->get( $id );
				if ( ! $element_setting ) {
					$element_setting = $element_setting_manager->create();
				}
			}

			foreach ( $data as $key => $value ) {
				$element_setting->$key = $value;
			}

			$status = $element_setting->sync_upstream();
			if ( is_wp_error( $status ) ) {
				$errors->add(
					$status->get_error_code(),
					$status->get_error_message(),
					array(
						'id'   => $id,
						'data' => $data,
					)
				);
			} else {
				$mappings['element_settings'][ $id ] = $element_setting->id;
			}
		}

		return $mappings;
	}

	/**
	 * Deletes containers with specific IDs.
	 *
	 * @since 1.0.0
	 *
	 * @param array $container_ids Array of container IDs.
	 */
	private function delete_containers( $container_ids ) {
		$container_manager = $this->form_manager->get_child_manager( 'containers' );

		foreach ( $container_ids as $container_id ) {
			$container = $container_manager->get( $container_id );
			if ( ! $container ) {
				continue;
			}

			$container->delete();
		}
	}

	/**
	 * Deletes elements with specific IDs.
	 *
	 * @since 1.0.0
	 *
	 * @param array $element_ids Array of element IDs.
	 */
	private function delete_elements( $element_ids ) {
		$element_manager = $this->form_manager->get_child_manager( 'containers' )->get_child_manager( 'elements' );

		foreach ( $element_ids as $element_id ) {
			$element = $element_manager->get( $element_id );
			if ( ! $element ) {
				continue;
			}

			$element->delete();
		}
	}

	/**
	 * Deletes element choices with specific IDs.
	 *
	 * @since 1.0.0
	 *
	 * @param array $element_choice_ids Array of element choice IDs.
	 */
	private function delete_element_choices( $element_choice_ids ) {
		$element_choice_manager = $this->form_manager->get_child_manager( 'containers' )->get_child_manager( 'elements' )->get_child_manager( 'element_choices' );

		foreach ( $element_choice_ids as $element_choice_id ) {
			$element_choice = $element_choice_manager->get( $element_choice_id );
			if ( ! $element_choice ) {
				continue;
			}

			$element_choice->delete();
		}
	}

	/**
	 * Deletes element settings with specific IDs.
	 *
	 * @since 1.0.0
	 *
	 * @param array $element_setting_ids Array of element setting IDs.
	 */
	private function delete_element_settings( $element_setting_ids ) {
		$element_setting_manager = $this->form_manager->get_child_manager( 'containers' )->get_child_manager( 'elements' )->get_child_manager( 'element_settings' );

		foreach ( $element_setting_ids as $element_setting_id ) {
			$element_setting = $element_setting_manager->get( $element_setting_id );
			if ( ! $element_setting ) {
				continue;
			}

			$element_setting->delete();
		}
	}

	/**
	 * Checks whether a specific ID is a temporary ID.
	 *
	 * @since 1.0.0
	 *
	 * @param int $id Component ID.
	 * @return bool True if temporary ID, false otherwise.
	 */
	private function is_temp_id( $id ) {
		return is_string( $id ) && 'temp_id_' === substr( $id, 0, 8 );
	}
}
