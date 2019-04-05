<?php
/**
 * List page class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects;

use Leaves_And_Love\Plugin_Lib\Components\Admin_Pages;
use Leaves_And_Love\Plugin_Lib\Assets;
use WP_Screen;
use WP_Error;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models_List_Page' ) ) :

	/**
	 * Class for a models list page.
	 *
	 * @since 1.0.0
	 */
	abstract class Models_List_Page extends Manager_Page {
		/**
		 * The list table.
		 *
		 * @since 1.0.0
		 * @var Models_List_Table
		 */
		protected $list_table;

		/**
		 * The list table class name.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $list_table_class_name = Models_List_Table::class;

		/**
		 * The slug of the admin page to create or edit a model.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $edit_page_slug = '';

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string      $slug          Page slug.
		 * @param Admin_Pages $manager       Admin page manager instance.
		 * @param Manager     $model_manager Model manager instance.
		 */
		public function __construct( $slug, $manager, $model_manager ) {
			parent::__construct( $slug, $manager, $model_manager );

			if ( empty( $this->title ) ) {
				$this->title = $this->model_manager->get_message( 'list_page_items' );
			}

			if ( empty( $this->menu_title ) ) {
				$this->menu_title = $this->model_manager->get_message( 'list_page_items' );
			}

			if ( empty( $this->capability ) ) {
				$capabilities = $this->model_manager->capabilities();
				if ( $capabilities ) {
					$base_capabilities = $capabilities->get_capabilities( 'base' );

					$this->capability = $base_capabilities['edit_items'];
				}
			}

			if ( empty( $this->edit_page_slug ) ) {
				$this->edit_page_slug = $this->manager->get_prefix() . 'edit_' . $this->model_manager->get_singular_slug();
			}
		}

		/**
		 * Handles a request to the page.
		 *
		 * @since 1.0.0
		 */
		public function handle_request() {
			if ( ! $this->current_user_can() ) {
				wp_die( wp_kses_data( $this->model_manager->get_message( 'list_page_cannot_edit_items' ) ), '', 403 );
			}

			$this->setup_list_table();
			$this->handle_actions();
			$this->clean_referer();
			$this->prepare_list_table();
			$this->setup_screen( get_current_screen() );
		}

		/**
		 * Enqueues assets to load on the page.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_assets() {
			$assets = Assets::get_library_instance();

			$assets->register_script(
				'list-models',
				'assets/dist/js/list-models.js',
				array(
					'deps'          => array( 'jquery' ),
					'ver'           => \Leaves_And_Love_Plugin_Loader::VERSION,
					'in_footer'     => true,
					'enqueue'       => true,
					'localize_name' => 'pluginLibListModelsData',
					'localize_data' => array(
						'i18n' => array(
							'confirm_deletion' => $this->model_manager->get_message( 'list_page_confirm_deletion' ),
						),
					),
				)
			);

			$prefix      = $this->model_manager->get_prefix();
			$plural_slug = $this->model_manager->get_plural_slug();

			/**
			 * Fires when models list page assets should be enqueued.
			 *
			 * The dynamic parts of the hook name refer to the manager's prefix and
			 * its plural slug respectively.
			 *
			 * @since 1.0.0
			 *
			 * @param Manager $manager Model manager instance.
			 */
			do_action( "{$prefix}list_{$plural_slug}_enqueue_assets", $this->model_manager );
		}

		/**
		 * Renders the list page header.
		 *
		 * @since 1.0.0
		 */
		protected function render_header() {
			$capabilities = $this->model_manager->capabilities();

			$new_page_url = '';
			if ( ! empty( $this->edit_page_slug ) ) {
				$new_page_url = add_query_arg( 'page', $this->edit_page_slug, $this->url );
			}

			$search = filter_input( INPUT_GET, 's' );

			?>
			<h1 class="wp-heading-inline">
				<?php echo esc_html( $this->title ); ?>
			</h1>

			<?php if ( ! empty( $new_page_url ) && $capabilities && $capabilities->user_can_create() ) : ?>
				<a href="<?php echo esc_url( $new_page_url ); ?>" class="page-title-action"><?php echo esc_html( $this->model_manager->get_message( 'list_page_add_new' ) ); ?></a>
			<?php endif; ?>

			<?php if ( ! empty( $search ) ) : ?>
				<span class="subtitle"><?php printf( $this->model_manager->get_message( 'list_page_search_results_for' ), esc_html( $search ) ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?></span>
			<?php endif; ?>

			<hr class="wp-header-end">

			<?php

			$this->print_current_message( 'bulk_action' );
			$this->print_current_message( 'row_action' );
		}

		/**
		 * Renders the list page form.
		 *
		 * @since 1.0.0
		 */
		protected function render_form() {
			$this->list_table->views();

			?>
			<form id="<?php echo esc_attr( $this->model_manager->get_plural_slug() . '-filter' ); ?>" method="get">

				<?php $this->list_table->search_box( $this->model_manager->get_message( 'list_page_search_items' ), $this->model_manager->get_singular_slug() ); ?>

				<?php
				if ( $this->parent_slug && false !== strpos( $this->parent_slug, '?' ) ) {
					$query_string = wp_parse_url( self_admin_url( $this->parent_slug ), PHP_URL_QUERY );
					if ( $query_string ) {
						wp_parse_str( $query_string, $query_vars );
						foreach ( $query_vars as $key => $value ) {
							?>
							<input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" />
							<?php
						}
					}
				}
				?>

				<input type="hidden" name="page" value="<?php echo esc_attr( $this->slug ); ?>" />

				<?php
				if ( method_exists( $this->model_manager, 'get_author_property' ) ) {
					$author_property = $this->model_manager->get_author_property();
					$author          = filter_input( INPUT_GET, $author_property );
					if ( ! empty( $author ) ) {
						?>
						<input type="hidden" name="<?php echo esc_attr( $author_property ); ?>" value="<?php echo esc_attr( $author ); ?>" />
						<?php
					}
				}
				?>

				<?php $this->list_table->display(); ?>
			</form>
			<?php
		}

		/**
		 * Sets up the list table instance.
		 *
		 * @since 1.0.0
		 */
		protected function setup_list_table() {
			$class_name = $this->list_table_class_name;

			$edit_page_url = '';
			if ( ! empty( $this->edit_page_slug ) ) {
				$edit_page_url = add_query_arg( 'page', $this->edit_page_slug, $this->url );
			}

			$this->list_table = new $class_name(
				$this->model_manager,
				array(
					'screen'      => $this->hook_suffix,
					'models_page' => $this->url,
					'model_page'  => $edit_page_url,
				)
			);
		}

		/**
		 * Handles bulk actions when necessary.
		 *
		 * @since 1.0.0
		 */
		protected function handle_actions() {
			$doaction = $this->list_table->current_action();

			if ( ! $doaction ) {
				return;
			}

			check_admin_referer( $this->get_nonce_action( 'bulk_action' ) );

			$sendback = $this->get_referer();
			$sendback = add_query_arg( 'paged', $this->list_table->get_pagenum(), $sendback );

			$plural_slug = $this->model_manager->get_plural_slug();

			$ids = array();
			if ( isset( $_REQUEST[ $plural_slug ] ) ) {
				$ids = array_map( 'absint', $_REQUEST[ $plural_slug ] );
			}

			if ( empty( $ids ) ) {
				wp_safe_redirect( $sendback );
				exit;
			}

			$message = '';
			if ( method_exists( $this, 'bulk_action_' . $doaction ) ) {
				$message = call_user_func( array( $this, 'bulk_action_' . $doaction ), $ids );
			} else {
				$prefix = $this->model_manager->get_prefix();

				/**
				 * Fires when a custom bulk action should be handled.
				 *
				 * The hook callback should return a success message or an error object which
				 * will then be used to display feedback to the user.
				 *
				 * The dynamic parts of the hook name refer to the manager's prefix, its plural slug
				 * and the slug of the action to handle respectively.
				 *
				 * @since 1.0.0
				 *
				 * @param string                                        $message Empty message to be modified.
				 * @param array                                         $ids     Array of model IDs.
				 * @param Leaves_And_Love\Plugin_Lib\DB_Objects\Manager $manager The manager instance.
				 */
				$message = apply_filters( "{$prefix}{$plural_slug}_handle_bulk_action_{$doaction}", $message, $ids, $this->model_manager );
			}

			$sendback = remove_query_arg( array( 'action', 'action2', $plural_slug ), $sendback );

			if ( $message ) {
				$sendback = $this->redirect_with_message( $sendback, $message, 'bulk_action' );
			}

			wp_safe_redirect( $sendback );
			exit;
		}

		/**
		 * Prepares the models in the list table.
		 *
		 * @since 1.0.0
		 */
		protected function prepare_list_table() {
			$this->list_table->prepare_items();
		}

		/**
		 * Sets up the screen with screen reader content, options and help tabs.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_Screen $screen Current screen.
		 */
		protected function setup_screen( $screen ) {
			$screen->set_screen_reader_content(
				array(
					'heading_views'      => $this->model_manager->get_message( 'list_page_filter_items_list' ),
					'heading_pagination' => $this->model_manager->get_message( 'list_page_items_list_navigation' ),
					'heading_list'       => $this->model_manager->get_message( 'list_page_items_list' ),
				)
			);

			add_screen_option(
				'per_page',
				array(
					'default' => 20,
					'option'  => 'list_' . $this->model_manager->get_prefix() . $this->model_manager->get_plural_slug() . '_per_page',
				)
			);
		}

		/**
		 * Handles the 'delete' bulk action.
		 *
		 * @since 1.0.0
		 *
		 * @param array $ids IDs of the models to delete.
		 * @return string|WP_Error Feedback message, or error object on failure.
		 */
		protected function bulk_action_delete( $ids ) {
			$errors = new WP_Error();

			$capabilities   = $this->model_manager->capabilities();
			$title_property = method_exists( $this->model_manager, 'get_title_property' ) ? $this->model_manager->get_title_property() : '';

			foreach ( $ids as $id ) {
				$model = $this->model_manager->get( $id );
				if ( ! $model ) {
					continue;
				}

				$model_name = $id;
				if ( ! empty( $title_property ) ) {
					$model_name = $model->$title_property;
				}

				if ( ! $capabilities || ! $capabilities->user_can_delete( null, $id ) ) {
					$errors->add( 'bulk_action_cannot_delete_item', sprintf( $this->model_manager->get_message( 'bulk_action_cannot_delete_item' ), $model_name ) );
					continue;
				}

				$result = $model->delete();
				if ( is_wp_error( $result ) ) {
					$errors->add( 'bulk_action_delete_item_internal_error', sprintf( $this->model_manager->get_message( 'bulk_action_delete_item_internal_error' ), $model_name ) );
				}
			}

			$total_count = count( $ids );

			if ( ! empty( $errors->errors ) ) {
				$error_count = count( $errors->errors );

				$message  = '<p>' . sprintf( translate_nooped_plural( $this->model_manager->get_message( 'bulk_action_delete_has_errors', true ), $error_count ), number_format_i18n( $error_count ) ) . '</p>';
				$message .= '<ul>';
				foreach ( $errors->get_error_messages() as $error_message ) {
					$message .= '<li>' . $error_message . '</li>';
				}
				$message .= '</ul>';
				$message .= '<p>' . sprintf( translate_nooped_plural( $this->model_manager->get_message( 'bulk_action_delete_other_items_success', true ), $total_count - $error_count ), number_format_i18n( $total_count - $error_count ) ) . '</p>';

				return new WP_Error( 'bulk_action_delete_has_errors', $message );
			}

			return sprintf( translate_nooped_plural( $this->model_manager->get_message( 'bulk_action_delete_success', true ), $total_count ), number_format_i18n( $total_count ) );
		}
	}

endif;
