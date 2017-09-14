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
	 * @access protected
	 * @var Submission_Manager
	 */
	protected $submission_manager;

	/**
	 * Submission export mode.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $modes = array();

	/**
	 * Nonce action to use.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $nonce_action = '';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
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
	 * @access public
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
	 * @access public
	 *
	 * @param string $mode Export mode to use. Either 'xls' or 'csv'.
	 * @param Form   $form Form to export submissions for.
	 * @param array  $args Optional. Extra query arguments to pass to the submissions
	 *                     query.
	 */
	public function export_submissions( $mode, $form, $args = array() ) {
		if ( ! isset( $this->modes[ $mode ] ) ) {
			wp_die( __( 'Invalid submission export handler.', 'torro-forms' ) );
		}

		$this->modes[ $mode ]->export_submissions( $form, $args );
		exit;
	}

	/**
	 * Handles the export admin action.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function handle_export_action() {
		if ( ! isset( $_REQUEST['_wpnonce'] ) ) {
			wp_die( __( 'Missing nonce.', 'torro-forms' ) );
		}

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], $this->nonce_action ) ) {
			wp_die( __( 'Invalid nonce.', 'torro-forms' ) );
		}

		if ( ! isset( $_REQUEST['form_id'] ) ) {
			wp_die( __( 'Missing form ID.', 'torro-forms' ) );
		}

		$capabilities = $this->submission_manager->capabilities();
		if ( ! $capabilities || ! $capabilities->user_can_read() ) {
			wp_die( __( 'Insufficient permissions.', 'torro-forms' ) );
		}

		$form = $this->submission_manager->get_parent_manager( 'forms' )->get( (int) $_REQUEST['form_id'] );
		if ( ! $form ) {
			wp_die( __( 'Invalid form ID.', 'torro-forms' ) );
		}

		if ( ! isset( $_REQUEST['mode'] ) ) {
			wp_die( __( 'Missing submission export handler.', 'torro-forms' ) );
		}

		$this->export_submissions( wp_unslash( $_REQUEST['mode'] ), $form );
	}

	/**
	 * Renders the export form.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function render_export_form() {
		if ( ! isset( $_REQUEST['form_id'] ) ) {
			return;
		}

		?>
		<form class="torro-export-form" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" method="POST">
			<input type="hidden" name="action" value="<?php echo esc_attr( $this->get_export_action_name() ); ?>" />
			<input type="hidden" name="form_id" value="<?php echo absint( $_REQUEST['form_id'] ); ?>" />
			<?php wp_nonce_field( $this->nonce_action ); ?>

			<h3><?php _e( 'Export Submissions', 'torro-forms' ); ?></h3>

			<label for="torro-export-mode"><?php _e( 'Export as', 'torro-forms' ); ?></label>
			<select id="torro-export-mode">
				<?php foreach ( $this->modes as $slug => $mode ) : ?>
					<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $mode->get_title() ); ?></option>
				<?php endforeach; ?>
			</select>

			<button type="submit" class="button"><?php _e( 'Export', 'torro-forms' ); ?></button>
		</form>
		<?php
	}
}
