<?php
/**
 * Manager page base class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects;

use Leaves_And_Love\Plugin_Lib\Components\Admin_Page;
use Leaves_And_Love\Plugin_Lib\Components\Admin_Pages;
use Leaves_And_Love\Plugin_Lib\Fixes;
use WP_Error;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Manager_Page' ) ) :

	/**
	 * Class for any manager page.
	 *
	 * @since 1.0.0
	 */
	abstract class Manager_Page extends Admin_Page {

		/**
		 * The manager instance for the models.
		 *
		 * @since 1.0.0
		 * @var Manager
		 */
		protected $model_manager;

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
			parent::__construct( $slug, $manager );

			$this->model_manager = $model_manager;
		}

		/**
		 * Renders the list page content.
		 *
		 * @since 1.0.0
		 */
		public function render() {
			?>
			<div class="wrap">
				<?php $this->render_header(); ?>

				<?php $this->render_form(); ?>
			</div>
			<?php
		}

		/**
		 * Returns the current referer.
		 *
		 * @since 1.0.0
		 *
		 * @return string HTTP referer.
		 */
		protected function get_referer() {
			$referer = wp_get_referer();
			if ( ! $referer ) {
				$referer = $this->url;
			}

			return $referer;
		}

		/**
		 * Redirects to a clean URL if the referer is part of the current URL.
		 *
		 * @since 1.0.0
		 */
		protected function clean_referer() {
			if ( empty( $_REQUEST['_wp_http_referer'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
				return;
			}

			wp_safe_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), Fixes::php_filter_input( INPUT_SERVER, 'REQUEST_URI' ) ) );
			exit;
		}

		/**
		 * Appends a query variable for a feedback message to a redirect URL.
		 *
		 * @since 1.0.0
		 *
		 * @param string          $redirect_url Redirect URL.
		 * @param string|WP_Error $message      Message string or error object.
		 * @param string          $action_type  Optional. The action type. Default 'action'.
		 * @return string Redirect URL with query variable appended.
		 */
		protected function redirect_with_message( $redirect_url, $message, $action_type = 'action' ) {
			$result = 'true';
			if ( is_wp_error( $message ) ) {
				$result  = 'false';
				$message = $message->get_error_message();
			}

			$prefix      = $this->model_manager->get_prefix();
			$plural_slug = $this->model_manager->get_plural_slug();

			$transient_name = $prefix . $plural_slug . '_' . $action_type . '_result';

			set_transient( $transient_name, $message, 30 );

			return add_query_arg( $action_type . '_result', $result, $redirect_url );
		}

		/**
		 * Prints the current feedback message based on the query variable.
		 *
		 * @since 1.0.0
		 *
		 * @param string $action_type Optional. The action type. Default 'action'.
		 */
		protected function print_current_message( $action_type = 'action' ) {
			$action_result = filter_input( INPUT_GET, $action_type . '_result' );
			if ( empty( $action_result ) ) {
				return;
			}

			$prefix      = $this->model_manager->get_prefix();
			$plural_slug = $this->model_manager->get_plural_slug();

			$transient_name = $prefix . $plural_slug . '_' . $action_type . '_result';

			$message = get_transient( $transient_name );
			if ( false !== $message ) {
				delete_transient( $transient_name );

				$class = 'true' === $action_result ? 'notice-success' : 'notice-error';

				echo '<div id="message" class="' . esc_attr( 'notice ' . $class . ' is-dismissible' ) . '">' . wp_kses_post( wpautop( $message ) ) . '</div>';
			}

			$_SERVER['REQUEST_URI'] = remove_query_arg( array( $action_type . '_result' ), $_SERVER['REQUEST_URI'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		}

		/**
		 * Returns the nonce action name for a given action type and model ID.
		 *
		 * @since 1.0.0
		 *
		 * @param string $action_type Optional. Action type. Either 'bulk_action', 'row_action' or 'action'.
		 *                            Default 'action'.
		 * @param int    $model_id    Optional. Model ID. Default null.
		 * @return string Nonce action name.
		 */
		protected function get_nonce_action( $action_type = 'action', $model_id = null ) {
			return $this->model_manager->get_nonce_action( $action_type, $model_id );
		}

		/**
		 * Renders the page header.
		 *
		 * @since 1.0.0
		 */
		abstract protected function render_header();

		/**
		 * Renders the page form.
		 *
		 * @since 1.0.0
		 */
		abstract protected function render_form();
	}

endif;
