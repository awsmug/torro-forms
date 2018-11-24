<?php
/**
 * Trait for managers that support view routing
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Traits;

use Leaves_And_Love\Plugin_Lib\DB_Objects\View_Routing;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\View_Routing_Manager_Trait' ) ) :

	/**
	 * Trait for managers.
	 *
	 * Include this trait for managers that support view routing.
	 *
	 * @since 1.0.0
	 */
	trait View_Routing_Manager_Trait {
		/**
		 * The view routing service definition.
		 *
		 * @since 1.0.0
		 * @static
		 * @var string
		 */
		protected static $service_view_routing = View_Routing::class;

		/**
		 * Renders buttons for frontend viewing and previewing for the minor publishing area of the model edit page.
		 *
		 * @since 1.0.0
		 *
		 * @param int|null $id    Current model ID, or null if new model.
		 * @param Model    $model Current model object.
		 */
		public function render_view_buttons( $id, $model ) {
			$view_routing = $this->view_routing();
			if ( ! $view_routing ) {
				return;
			}

			$preview_text = $id ? $this->get_message( 'edit_page_preview_changes' ) : $this->get_message( 'edit_page_preview' );

			$permalink = $view_routing->get_model_permalink( $model );

			if ( ! empty( $permalink ) ) : ?>
				<div id="view-action">
					<a class="button" href="<?php echo esc_url( $permalink ); ?>" target="_blank"><?php echo esc_html( $this->get_message( 'edit_page_view' ) ); ?></a>
				</div>
			<?php endif; ?>
			<div id="preview-action">
				<button type="button" id="post-preview" class="preview button"><?php echo esc_html( $preview_text ); ?></button>
			</div>
			<?php
		}
	}

endif;
