<?php
/**
 * Form list page handler class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Forms;

use Leaves_And_Love\Plugin_Lib\Fixes;
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
	 * @var Form_Manager
	 */
	protected $form_manager;

	/**
	 * Internal submission count storage.
	 *
	 * @since 1.0.0
	 * @var array|null
	 */
	protected $submission_counts = null;

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
	 * Adjusts the list table columns if conditions are met.
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns Form list table columns.
	 * @return array Adjusted columns.
	 */
	public function maybe_adjust_table_columns( $columns ) {
		$new_columns = array(
			'form_shortcode'   => __( 'Shortcode', 'torro-forms' ),
			'submission_count' => __( 'Submission Count', 'torro-forms' ),
		);

		return array_merge( array_slice( $columns, 0, 2, true ), $new_columns, array_slice( $columns, 2, count( $columns ) - 1, true ) );
	}

	/**
	 * Renders a custom list table column if conditions are met.
	 *
	 * @since 1.0.0
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
	 * Adjusts row actions if conditions are met.
	 *
	 * @since 1.0.0
	 *
	 * @param array   $actions Original row actions.
	 * @param WP_Post $post    Current post object.
	 * @return array Possibly modified row actions.
	 */
	public function maybe_adjust_row_actions( $actions, $post ) {
		$form = $this->form_manager->get( $post->ID );
		if ( ! $form ) {
			return $actions;
		}

		return $this->insert_custom_row_actions( $actions, $form );
	}

	/**
	 * Inserts custom row actions for the list table.
	 *
	 * @since 1.0.0
	 *
	 * @param array $actions Original row actions.
	 * @param Form  $form    Current form object.
	 * @return array Modified row actions.
	 */
	public function insert_custom_row_actions( $actions, $form ) {
		$prefix = $this->form_manager->get_prefix();

		if ( 'trash' === $form->status ) {
			return $actions;
		}

		$nonce_action = $prefix . 'duplicate_form_' . $form->id;

		$actions[ $prefix . 'view_submissions' ] = sprintf(
			'<a href="%1$s" aria-label="%2$s">%3$s</a>',
			add_query_arg( 'form_id', $form->id, torro()->admin_pages()->get( 'list_submissions' )->url ),
			/* translators: %s: form title */
			esc_attr( sprintf( __( 'View submissions for &#8220;%s&#8221;', 'torro-forms' ), get_the_title( $form->id ) ) ),
			_x( 'View Submissions', 'action', 'torro-forms' )
		);

		$actions[ $prefix . 'duplicate' ] = sprintf(
			'<a href="%1$s" aria-label="%2$s">%3$s</a>',
			wp_nonce_url( admin_url( 'admin.php?action=' . $prefix . 'duplicate_form&amp;form_id=' . $form->id . '&amp;_wp_http_referer=' . rawurlencode( Fixes::php_filter_input( INPUT_SERVER, 'REQUEST_URI' ) ) ), $nonce_action ),
			/* translators: %s: form title */
			esc_attr( sprintf( __( 'Duplicate &#8220;%s&#8221;', 'torro-forms' ), get_the_title( $form->id ) ) ),
			_x( 'Duplicate', 'action', 'torro-forms' )
		);

		return $actions;
	}

	/**
	 * Renders a custom list table column.
	 *
	 * @since 1.0.0
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
					<?php $this->form_manager->assets()->render_icon( 'torro-icon-clippy', __( 'Copy to clipboard', 'torro-forms' ) ); ?>
				</button>
				<?php
				break;
			case 'submission_count':
				$count = $this->get_submission_count( $form->id );

				$output = esc_html( $count );
				if ( $count > 0 ) {
					$output = '<a href="' . esc_url( add_query_arg( 'form_id', $form->id, torro()->admin_pages()->get( 'list_submissions' )->url ) ) . '">' . $output . '</a>';
				}

				echo $output; // WPCS: XSS OK.
				break;
		}
	}

	/**
	 * Gets the submission count for a given form ID.
	 *
	 * This method should be used since it makes a single aggregated API call to count submissions over each form.
	 *
	 * @since 1.0.0
	 *
	 * @param int $form_id Form ID.
	 * @return int Submission count for the form.
	 */
	protected function get_submission_count( $form_id ) {
		if ( ! isset( $this->submission_counts ) ) {
			$submission_manager = $this->form_manager->get_child_manager( 'submissions' );

			$results = $this->form_manager->db()->get_results( "SELECT form_id, COUNT( * ) AS num_submissions FROM %{$submission_manager->get_table_name()}% GROUP BY form_id" );

			$this->submission_counts = array();
			foreach ( $results as $row ) {
				$this->submission_counts[ $row->form_id ] = (int) $row->num_submissions;
			}
		}

		if ( ! isset( $this->submission_counts[ $form_id ] ) ) {
			return 0;
		}

		return $this->submission_counts[ $form_id ];
	}
}
