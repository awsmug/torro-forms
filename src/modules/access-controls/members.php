<?php
/**
 * Members access control class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Access_Controls;

use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use WP_Error;

/**
 * Class for an access control to restrict to members only.
 *
 * @since 1.0.0
 */
class Members extends Access_Control {

	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function bootstrap() {
		$this->slug        = 'members';
		$this->title       = __( 'Members', 'torro-forms' );
		$this->description = __( 'Allows you to make this form available to logged-in members only.', 'torro-forms' );
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

		$allowed_roles    = $this->get_form_option( $form->id, 'allowed_roles', array() );
		$allowed_user_ids = $this->get_form_option( $form->id, 'allowed_users', array() );
		$allowed_user_ids = array_map( 'absint', array_unique( array_filter( $allowed_user_ids ) ) );

		if ( empty( $allowed_roles ) && empty( $allowed_user_ids ) ) {
			return true;
		}

		if ( ! empty( $allowed_roles ) ) {
			$user = wp_get_current_user();

			$intersected_roles = array_intersect( array_values( $user->roles ), $allowed_roles );
			if ( ! empty( $intersected_roles ) ) {
				return true;
			}
		}

		if ( ! empty( $allowed_user_ids ) ) {
			if ( in_array( get_current_user_id(), $allowed_user_ids, true ) ) {
				return true;
			}
		}

		$message = $this->get_form_option( $form->id, 'not_selected_message' );
		if ( empty( $message ) ) {
			$message = $this->get_default_not_selected_message();
		}

		return new WP_Error( 'not_selected', $message );
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

		$role_choices = array();
		foreach ( wp_roles()->roles as $role => $details ) {
			$role_choices[ $role ] = translate_user_role( $details['name'] );
		}

		$meta_fields['allowed_roles'] = array(
			'type'        => count( $role_choices ) > 8 ? 'multiselect' : 'multibox',
			'label'       => __( 'Allowed Roles', 'torro-forms' ),
			'description' => __( 'If you select user roles here, only users with these roles are granted access to the form.', 'torro-forms' ),
			'choices'     => $role_choices,
		);

		$meta_fields['allowed_users'] = array(
			'type'          => 'autocomplete',
			'label'         => __( 'Allowed Users', 'torro-forms' ),
			'description'   => __( 'If you select users here, only these users are granted access to the form.', 'torro-forms' ),
			'input_classes' => array( 'regular-text' ),
			'repeatable'    => true,
			'autocomplete'  => array(
				'rest_placeholder_search_route' => 'wp/v2/users?search=%search%',
				'rest_placeholder_label_route'  => 'wp/v2/users/%value%',
				'value_generator'               => '%id%',
				/* translators: 1: user display name, 2: user ID */
				'label_generator'               => sprintf( __( '%1$s (User ID %2$s)', 'torro-forms' ), '%name%', '%id%' ),
			),
		);

		$meta_fields['not_selected_message'] = array(
			'type'          => 'text',
			'label'         => __( '&#8220;Not eligible&#8221; Message', 'torro-forms' ),
			'description'   => __( 'Enter the message to show to the user when they are logged in, but have not been selected to participate.', 'torro-forms' ),
			'default'       => $this->get_default_not_selected_message(),
			'input_classes' => array( 'regular-text' ),
		);

		/* TODO: back-compat with 'form_access_controls_allmembers_same_users' or 'form_access_controls_selectedmembers_same_users' (set to 'yes' or empty string) and 'to_be_logged_in_text' (string) and the old torro_participants table (add these users to the allowed_users array); if set, set 'enabled' too. */

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

	/**
	 * Returns the default message to display when the user has not been selected.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return string Message to display.
	 */
	protected function get_default_not_selected_message() {
		return __( 'You have not been selected to participate.', 'torro-forms' );
	}
}
