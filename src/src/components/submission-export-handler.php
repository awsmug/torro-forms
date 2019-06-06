<?php
/**
 * Submission export handler class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Components;

use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission_Manager;
use Leaves_And_Love\Plugin_Lib\Service;

/**
 * Class for handling submission export.
 *
 * @since 1.0.0
 */
class Submission_Export_Handler extends Service {

	/**
	 * Submission manager instance.
	 *
	 * @since 1.0.0
	 * @var Submission_Manager
	 */
	protected $submission_manager;

	/**
	 * Submission export mode.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $modes = array();

	/**
	 * Nonce action to use.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $nonce_action = '';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string             $prefix             Instance prefix.
	 * @param Submission_Manager $submission_manager Submission manager instance.
	 */
	public function __construct( $prefix, $submission_manager ) {
		$this->set_prefix( $prefix );

		$this->submission_manager = $submission_manager;

		$this->modes = array(
			'xls' => new Submission_Export_XLS( $this ),
			'csv' => new Submission_Export_CSV( $this ),
		);

		$this->nonce_action = $this->get_prefix() . 'submission_export';
	}

	/**
	 * Gets the export admin action name.
	 *
	 * @since 1.0.0
	 *
	 * @return string Action name.
	 */
	public function get_export_action_name() {
		return $this->nonce_action;
	}

	/**
	 * Exports submissions for a form with a specific export mode.
	 *
	 * This method will terminate the current request.
	 *
	 * @since 1.0.0
	 *
	 * @param string $mode Export mode to use. Either 'xls' or 'csv'.
	 * @param Form   $form Form to export submissions for.
	 * @param array  $args Optional. Extra query arguments to pass to the submissions
	 *                     query.
	 */
	public function export_submissions( $mode, $form, $args = array() ) {
		if ( ! isset( $this->modes[ $mode ] ) ) {
			wp_die( esc_html__( 'Invalid submission export handler.', 'torro-forms' ) );
		}

		$this->modes[ $mode ]->export_submissions( $form, $args );
		exit;
	}

	/**
	 * Handles the export admin action.
	 *
	 * @since 1.0.0
	 */
	public function handle_export_action() {
		$nonce   = filter_input( INPUT_POST, '_wpnonce' );
		$form_id = filter_input( INPUT_POST, 'form_id', FILTER_VALIDATE_INT );
		$mode    = filter_input( INPUT_POST, 'mode' );

		if ( empty( $nonce ) ) {
			wp_die( esc_html__( 'Missing nonce.', 'torro-forms' ) );
		}

		if ( ! wp_verify_nonce( $nonce, $this->nonce_action ) ) {
			wp_die( esc_html__( 'Invalid nonce.', 'torro-forms' ) );
		}

		if ( empty( $form_id ) ) {
			wp_die( esc_html__( 'Missing form ID.', 'torro-forms' ) );
		}

		$capabilities = $this->submission_manager->capabilities();
		if ( ! $capabilities || ! $capabilities->user_can_read() ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'torro-forms' ) );
		}

		$form = $this->submission_manager->get_parent_manager( 'forms' )->get( $form_id );
		if ( ! $form ) {
			wp_die( esc_html__( 'Invalid form ID.', 'torro-forms' ) );
		}

		if ( empty( $mode ) ) {
			wp_die( esc_html__( 'Missing submission export handler.', 'torro-forms' ) );
		}

		$orderby = filter_input( INPUT_POST, 'orderby' );
		$order   = filter_input( INPUT_POST, 'order' );

		$args = array();
		if ( ! empty( $orderby ) && ! empty( $order ) ) {
			$orderby = in_array( $orderby, array( 'id', 'timestamp' ), true ) ? $orderby : 'id';
			$order   = in_array( $order, array( 'ASC', 'DESC' ), true ) ? $order : 'ASC';

			$args['orderby'] = array( $orderby => $order );
		} else {
			$args['orderby'] = array( 'id' => 'ASC' );
		}

		$onechoice_columns = filter_input( INPUT_POST, 'onechoice_columns' );
		if ( 'one' === $onechoice_columns ) {
			add_filter( "{$this->prefix}use_single_export_column_for_choices", '__return_true' );
		} else {
			add_filter( "{$this->prefix}use_single_export_column_for_choices", '__return_false' );
		}

		$this->export_submissions( $mode, $form, $args );
	}

	/**
	 * Renders the export form.
	 *
	 * @since 1.0.0
	 */
	public function render_export_form() {
		if ( ! isset( $_REQUEST['form_id'] ) ) { // WPCS: CSRF OK.
			return;
		}

		?>
		<form class="torro-export-form" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" method="POST">
			<input type="hidden" name="action" value="<?php echo esc_attr( $this->get_export_action_name() ); ?>" />
			<input type="hidden" name="form_id" value="<?php echo absint( $_REQUEST['form_id'] ); ?>" />
			<?php wp_nonce_field( $this->nonce_action ); ?>

			<h3><?php esc_html_e( 'Export Submissions', 'torro-forms' ); ?></h3>

			<p class="description">
				<?php esc_html_e( 'Here you can export all completed submissions in a file format of your choice.', 'torro-forms' ); ?>
			</p>

			<label for="torro-export-mode"><?php esc_html_e( 'Export as', 'torro-forms' ); ?></label>
			<select id="torro-export-mode" name="mode" style="margin-right:15px;">
				<?php foreach ( $this->modes as $slug => $mode ) : ?>
					<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $mode->get_title() ); ?></option>
				<?php endforeach; ?>
			</select>

			<label for="torro-export-orderby"><?php esc_html_e( 'Order by', 'torro-forms' ); ?></label>
			<select id="torro-export-orderby" name="orderby">
				<option value="id"><?php esc_html_e( 'ID', 'torro-forms' ); ?></option>
				<option value="timestamp"><?php esc_html_e( 'Date', 'torro-forms' ); ?></option>
			</select>

			<label for="torro-export-order" class="screen-reader-text"><?php esc_html_e( 'Order', 'torro-forms' ); ?></label>
			<select id="torro-export-order" name="order" style="margin-right:15px;">
				<option value="ASC"><?php esc_html_e( 'Ascending', 'torro-forms' ); ?></option>
				<option value="DESC"><?php esc_html_e( 'Descending', 'torro-forms' ); ?></option>
			</select>

			<label for="torro-export-onechoice-columns"><?php esc_html_e( 'One Choice items', 'torro-forms' ); ?></label>
			<select id="torro-export-onechoice-columns" name="onechoice_columns" style="margin-right:15px;">
				<option value="one"><?php esc_html_e( 'Export all values in one column', 'torro-forms' ); ?></option>
				<option value="multi"><?php esc_html_e( 'Export each value in a different column', 'torro-forms' ); ?></option>
			</select>

			<button type="submit" class="button"><?php esc_html_e( 'Export', 'torro-forms' ); ?></button>
		</form>
		<?php
	}
}
