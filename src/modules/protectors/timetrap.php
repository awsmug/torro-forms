<?php
/**
 * Timetrap protector class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Protectors;

use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use WP_Error;
use Exception;

/**
 * Class for a protector using a timetrap field.
 *
 * @since 1.0.0
 */
class Timetrap extends Protector {

	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.0.0
	 */
	protected function bootstrap() {
		$this->slug        = 'timetrap';
		$this->title       = __( 'Timetrap', 'torro-forms' );
		$this->description = __( 'Tries to detect bots by setting a minimum time that users need to fill in the form.', 'torro-forms' );
	}

	/**
	 * Verifies a request by ensuring that it is not spammy.
	 *
	 * @since 1.0.0
	 *
	 * @param array           $data       Submission POST data.
	 * @param Form            $form       Form object.
	 * @param Submission|null $submission Submission object, or null if a new submission.
	 * @return bool|WP_Error True if request is not spammy, false or error object otherwise.
	 */
	public function verify_request( $data, $form, $submission = null ) {
		$now       = current_time( 'timestamp' );
		$timestamp = filter_input( INPUT_POST, 'timestamp' );
		if ( empty( $timestamp ) ) {
			return new WP_Error( 'missing_timestamp', __( 'Internal error: Could not verify you are human. Please contact an administrator if you are.', 'torro-forms' ) );
		}

		$trigger = $this->get_form_option( $form->id, 'trigger', 3 );

		if ( $now - $timestamp < $trigger ) {
			return new WP_Error( 'timetrap_too_quickly', __( 'You filled this form too quickly to qualify as a human. We understand you possibly are in a hurry, but you really only have to wait a few seconds to send it.', 'torro-forms' ) );
		}

		return true;
	}

	/**
	 * Renders the output for the protector before the Submit button.
	 *
	 * @since 1.0.0
	 *
	 * @param Form $form Form object.
	 */
	public function render_output( $form ) {
		?>
		<input type="hidden" id="torro-timestamp" name="timestamp" value="<?php echo esc_attr( current_time( 'timestamp' ) ); ?>">
		<?php
	}

	/**
	 * Returns the available meta fields for the submodule.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_meta_fields() {
		$meta_fields = parent::get_meta_fields();

		$meta_fields['trigger'] = array(
			'type'         => 'number',
			'label'        => __( 'Timetrap Trigger', 'torro-forms' ),
			'description'  => __( 'Specify the number of minimum seconds a user needs to be on the page in order to qualify as a human.', 'torro-forms' ),
			'default'      => 3,
			'min'          => 1,
			'step'         => 1,
			'wrap_classes' => array( 'has-torro-tooltip-description' ),
		);

		return $meta_fields;
	}
}
