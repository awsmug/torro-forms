<?php
/**
 * All members access control class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Access_Controls;

use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use WP_Error;

/**
 * Class for an access control to restrict to all members only.
 *
 * @since 1.0.0
 */
class All_Members extends Access_Control {

	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function bootstrap() {
		$this->slug        = 'allmembers';
		$this->title       = __( 'All Members', 'torro-forms' );
		$this->description = __( 'Allows you make this form available to all logged-in members only.', 'torro-forms' );
	}

	/**
	 * Determines whether the current user can access a specific form or submission.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Form            $form       Form object.
	 * @param Submission|null $submission Submission object, or null if no submission is set.
	 * @return bool|WP_Error True if the form or submission can be accessed, false or error object otherwise.
	 */
	public function can_access( $form, $submission = null ) {
		if ( ! is_user_logged_in() ) {
			$message = $this->get_form_option( $form->id, 'login_required_message' );
			if ( empty( $message ) ) {
				$message = $this->get_default_login_required_message();
			}

			return new WP_Error( 'must_be_logged_in', $message );
		}

		return true;
	}

	/**
	 * Returns the available meta fields for the submodule.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_meta_fields() {
		$meta_fields = parent::get_meta_fields();

		$meta_fields['login_required_message'] = array(
			'type'          => 'text',
			'label'         => __( '&#8220;Login required&#8221; Message', 'torro-forms' ),
			'description'   => __( 'Enter the message to show to the user in case they are not logged in.', 'torro-forms' ),
			'default'       => $this->get_default_login_required_message(),
			'input_classes' => array( 'regular-text' ),
		);

		/* TODO: back-compat with 'form_access_controls_allmembers_same_users' (set to 'yes' or empty string) and 'to_be_logged_in_text' (string); if set, set 'enabled' too. */

		return $meta_fields;
	}

	/**
	 * Returns the default message to display when the user is not logged in.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return string Message to display.
	 */
	protected function get_default_login_required_message() {
		return __( 'You have to be logged in to participate.', 'torro-forms' );
	}
}
