<?php
/**
 * Form edit page handler class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Forms;

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
	 * @access protected
	 * @var Form_Manager
	 */
	protected $form_manager;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Form_Manager $form_manager Form manager instance.
	 */
	public function __construct( $form_manager ) {
		$this->form_manager = $form_manager;
	}

	/**
	 * Renders form canvas if conditions are met.
	 *
	 * @since 1.0.0
	 * @access public
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

	/**
	 * Prints templates if conditions are met.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function maybe_print_templates() {
		$target_post_type = $this->form_manager->get_prefix() . $this->form_manager->get_singular_slug();

		if ( empty( $_GET['post_type'] ) || $target_post_type !== $_GET['post_type'] ) {
			if ( empty( $_GET['post'] ) || $target_post_type !== get_post_type( $_GET['post'] ) ) {
				return;
			}
		}

		$this->print_templates();
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
	 * Renders form canvas.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param Form $form Current form.
	 */
	private function render_form_canvas( $form ) {
		?>
		<div id="torro-form-canvas" class="torro-form-canvas">
			<div class="drag-drop-area is-empty">
				<div class="loader-content hide-if-no-js">
					<?php _e( 'Loading form builder...', 'torro-forms' ); ?>
					<span class="spinner is-active"></span>
				</div>
				<div class="torro-notice notice-warning hide-if-js">
					<p>
						<?php _e( 'It seems you have disabled JavaScript in your browser. Torro Forms requires JavaScript in order to edit your forms.', 'torro-forms' ); ?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Adds meta boxes to the page.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param Form $form Current form.
	 */
	private function add_meta_boxes( $form ) {
		// Empty method body.
	}

	/**
	 * Enqueues assets to load on the page.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function enqueue_assets() {
		$this->form_manager->assets()->enqueue_script( 'admin-form-builder' );
		$this->form_manager->assets()->enqueue_style( 'admin-form-builder' );
	}

	/**
	 * Prints templates to use in JavaScript.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function print_templates() {
		?>
		<script type="text/html" id="tmpl-torro-failure">
			<div class="torro-notice notice-error">
				<p>
					<strong><?php _e( 'Error:', 'torro-forms' ); ?></strong>
					{{ data.message }}
				</p>
			</div>
		</script>
		<?php
	}

	/**
	 * Handles a save request for the page.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param awsmug\Torro_Forms\DB_Objects\Forms $form Current form.
	 */
	private function handle_save_request( $form ) {
		$mappings = array(
			'forms'            => array( $form->id => $form->id ),
			'containers'       => array(),
			'elements'         => array(),
			'element_choices'  => array(),
			'element_settings' => array(),
		);

		$errors = new WP_Error();

		if ( isset( $_POST['containers'] ) ) {
			$mappings = $this->save_containers( wp_unslash( $_POST['containers'] ), $mappings, $errors );
		}

		if ( isset( $_POST['elements'] ) ) {
			$mappings = $this->save_elements( wp_unslash( $_POST['elements'] ), $mappings, $errors );
		}

		if ( isset( $_POST['element_choices'] ) ) {
			$mappings = $this->save_element_choices( wp_unslash( $_POST['element_choices'] ), $mappings, $errors );
		}

		if ( isset( $_POST['element_settings'] ) ) {
			$mappings = $this->save_element_settings( wp_unslash( $_POST['element_settings'] ), $mappings, $errors );
		}

		if ( isset( $_POST['deleted_containers'] ) ) {
			$this->delete_containers( array_map( 'absint', $_POST['deleted_containers'] ) );
		}

		if ( isset( $_POST['deleted_elements'] ) ) {
			$this->delete_elements( array_map( 'absint', $_POST['deleted_elements'] ) );
		}

		if ( isset( $_POST['deleted_element_choices'] ) ) {
			$this->delete_element_choices( array_map( 'absint', $_POST['deleted_element_choices'] ) );
		}

		if ( isset( $_POST['deleted_element_settings'] ) ) {
			$this->delete_element_settings( array_map( 'absint', $_POST['deleted_element_settings'] ) );
		}
	}

	/**
	 * Saves containers.
	 *
	 * @since 1.0.0
	 * @access private
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
				$errors->add( $status->get_error_code(), $status->get_error_message(), array(
					'id'   => $id,
					'data' => $data,
				) );
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
	 * @access private
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
				$errors->add( $status->get_error_code(), $status->get_error_message(), array(
					'id'   => $id,
					'data' => $data,
				) );
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
	 * @access private
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
				$errors->add( $status->get_error_code(), $status->get_error_message(), array(
					'id'   => $id,
					'data' => $data,
				) );
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
	 * @access private
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
				$errors->add( $status->get_error_code(), $status->get_error_message(), array(
					'id'   => $id,
					'data' => $data,
				) );
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
	 * @access private
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
	 * @access private
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
	 * @access private
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
	 * @access private
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
	 * @access private
	 *
	 * @param int $id Component ID.
	 * @return bool True if temporary ID, false otherwise.
	 */
	private function is_temp_id( $id ) {
		return is_string( $id ) && 'temp_id_' === substr( $id, 0, 8 );
	}
}
