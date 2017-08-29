<?php
/**
 * Form list page handler class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Forms;

use WP_Post;
use WP_Error;

/**
 * Class for handling form list page behavior.
 *
 * @since 1.0.0
 */
class Form_List_Page_Handler {

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
	 * Adjusts the list table columns if conditions are met.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $columns Form list table columns.
	 * @return array Adjust columns.
	 */
	public function maybe_adjust_table_columns( $columns ) {
		$new_columns = array(
			'form_shortcode' => __( 'Shortcode', 'torro-forms' ),
		);

		return array_merge( array_slice( $columns, 0, 2, true ), $new_columns, array_slice( $columns, 2, count( $columns ) - 1, true ) );
	}

	/**
	 * Renders a custom list table column if conditions are met.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $column_name Name of the column to render.
	 * @param int    $post_id     Current post ID.
	 */
	public function maybe_render_custom_table_column( $column_name, $post_id ) {
		$form = $this->form_manager->get( $post_id );
		if ( ! $form ) {
			return;
		}

		$this->render_custom_list_table_column( $column_name, $form );
	}

	/**
	 * Adds the Duplicate row action to the list table if conditions are met.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array   $actions Original row actions.
	 * @param WP_Post $post    Post object.
	 * @return array Modified row actions.
	 */
	public function maybe_insert_duplicate_row_action( $actions, $post ) {
		$prefix = $this->form_manager->get_prefix();

		if ( $prefix . 'form' !== $post->post_type ) {
			return $actions;
		}

		$nonce_action = $prefix . 'duplicate_form_' . $post->ID;

		$actions[ $prefix . 'duplicate' ] = sprintf(
			'<a href="%1$s" aria-label="%2$s">%3$s</a>',
			wp_nonce_url( admin_url( 'admin.php?action=' . $prefix . 'duplicate_form&amp;form_id=' . $post->ID . '&amp;_wp_http_referer=' . urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ), $nonce_action ),
			/* translators: %s: form title */
			esc_attr( sprintf( __( 'Duplicate &#8220;%s&#8221;', 'torro-forms' ), get_the_title( $post ) ) ),
			_x( 'Duplicate', 'action', 'torro-forms' )
		);

		return $actions;
	}

	/**
	 * Renders a custom list table column.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $column_name Name of the column to render.
	 * @param Form   $form        Current form object.
	 */
	protected function render_custom_list_table_column( $column_name, $form ) {
		switch ( $column_name ) {
			case 'form_shortcode':
				$this->form_manager->assets()->enqueue_script( 'clipboard' );
				$this->form_manager->assets()->enqueue_style( 'clipboard' );

				$id_attr = 'form-shortcode-' . $form->id;

				?>
				<input id="<?php echo esc_attr( $id_attr ); ?>" class="clipboard-field" value="<?php echo esc_attr( sprintf( "[{$this->form_manager->get_prefix()}form id=&quot;%d&quot;]", $form->id ) ); ?>" readonly="readonly" />
				<button type="button" class="clipboard-button button" data-clipboard-target="#<?php echo esc_attr( $id_attr ); ?>">
					<img src="<?php echo esc_url( $this->form_manager->assets()->get_full_url( 'assets/dist/img/clippy.svg' ) ); ?>" alt="<?php esc_attr_e( 'Copy to clipboard', 'torro-forms' ); ?>" />
				</button>
				<?php
				break;
		}
	}
}
