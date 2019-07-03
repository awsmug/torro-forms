<?php
/**
 * Members access control class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Access_Controls;

use awsmug\Torro_Forms\Modules\Assets_Submodule_Interface;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use awsmug\Torro_Forms\Components\Template_Tag_Handler;
use Leaves_And_Love\Plugin_Lib\Fields\Field_Manager;
use Leaves_And_Love\Plugin_Lib\Fixes;
use WP_Error;
use WP_User;

/**
 * Class for an access control to restrict to members only.
 *
 * @since 1.0.0
 */
class Members extends Access_Control implements Assets_Submodule_Interface {

	/**
	 * Template tag handler for email notifications.
	 *
	 * @since 1.0.0
	 * @var Template_Tag_Handler
	 */
	protected $template_tag_handler;

	/**
	 * Template tag handler for email address fields.
	 *
	 * @since 1.0.0
	 * @var Template_Tag_Handler
	 */
	protected $template_tag_handler_email_only;

	/**
	 * Temporary storage for email from name.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $from_name = '';

	/**
	 * Temporary storage for email from email.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $from_email = '';

	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.0.0
	 */
	protected function bootstrap() {
		$this->slug        = 'members';
		$this->title       = __( 'Members', 'torro-forms' );
		$this->description = __( 'Allows you to make this form available to logged-in members only.', 'torro-forms' );

		Field_Manager::register_field_type( 'autocompletewithbutton', Autocomplete_With_Button::class );

		$this->register_template_tag_handlers();

		$this->module->manager()->ajax()->register_action( 'invite_member', array( $this, 'ajax_invite_member' ) );
	}

	/**
	 * Determines whether the current user can access a specific form or submission.
	 *
	 * @since 1.0.0
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
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_meta_fields() {
		$meta_fields = parent::get_meta_fields();

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
			'type'          => 'autocompletewithbutton',
			'label'         => __( 'Allowed Users', 'torro-forms' ),
			'description'   => __( 'If you select users here, only these users are granted access to the form.', 'torro-forms' ),
			'repeatable'    => true,
			'autocomplete'  => array(
				'rest_placeholder_search_route' => 'wp/v2/users?search=%search%',
				'rest_placeholder_label_route'  => 'wp/v2/users/%value%',
				'value_generator'               => '%id%',
				/* translators: 1: user display name, 2: user ID */
				'label_generator'               => sprintf( __( '%1$s (User ID %2$s)', 'torro-forms' ), '%name%', '%id%' ),
			),
			'input_classes' => array( 'torro-member-invitation-input' ),
			'button_label'  => __( 'Send Invitation', 'torro-forms' ),
			'button_attrs'  => array(
				'class'    => 'button-link torro-send-invitation',
				'disabled' => true,
			),
		);

		$meta_fields['login_required_message'] = array(
			'type'          => 'text',
			'label'         => __( '&#8220;Login required&#8221; Message', 'torro-forms' ),
			'description'   => __( 'Enter the message to show to the user in case they are not logged in.', 'torro-forms' ),
			'default'       => $this->get_default_login_required_message(),
			'input_classes' => array( 'regular-text' ),
			'wrap_classes'  => array( 'has-torro-tooltip-description' ),
		);

		$meta_fields['not_selected_message'] = array(
			'type'          => 'text',
			'label'         => __( '&#8220;Not eligible&#8221; Message', 'torro-forms' ),
			'description'   => __( 'Enter the message to show to the user when they are logged in, but have not been selected to participate.', 'torro-forms' ),
			'default'       => $this->get_default_not_selected_message(),
			'input_classes' => array( 'regular-text' ),
			'wrap_classes'  => array( 'has-torro-tooltip-description' ),
		);

		return $meta_fields;
	}

	/**
	 * Returns the available settings sections for the submodule.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$section_slug => $section_args` pairs.
	 */
	public function get_settings_sections() {
		$settings_sections = parent::get_settings_sections();

		$settings_sections['invitation_email'] = array(
			'title'       => __( 'Invitation Email Template', 'torro-forms' ),
			'description' => __( 'Setup mail templates for inviting a user to a form.', 'torro-forms' ),
		);

		$settings_sections['reinvitation_email'] = array(
			'title'       => __( 'Reinvitation Email Template', 'torro-forms' ),
			'description' => __( 'Setup mail templates for reinviting a user to a form.', 'torro-forms' ),
		);

		return $settings_sections;
	}

	/**
	 * Returns the available settings fields for the submodule.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_settings_fields() {
		$settings_fields = parent::get_settings_fields();

		$domain = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
		if ( ! $domain ) {
			// Fall back to a random domain.
			$domain = 'yourwebsite.com';
		}

		$settings_fields['invitation_from_name'] = array(
			'section'              => 'invitation_email',
			'type'                 => 'templatetagtext',
			'label'                => __( 'From Name', 'torro-forms' ),
			'input_classes'        => array( 'regular-text' ),
			'default'              => '{sitetitle}',
			'template_tag_handler' => $this->template_tag_handler,
		);

		$settings_fields['invitation_from_email'] = array(
			'section'              => 'invitation_email',
			'type'                 => 'templatetagemail',
			'label'                => __( 'From Email', 'torro-forms' ),
			/* translators: %s: email address */
			'description'          => sprintf( __( 'This email address should contain the same domain like your website (e.g. %s).', 'torro-forms' ), 'email@' . $domain ),
			'input_classes'        => array( 'regular-text' ),
			'default'              => '{adminemail}',
			'template_tag_handler' => $this->template_tag_handler_email_only,
		);

		$settings_fields['invitation_subject'] = array(
			'section'              => 'invitation_email',
			'type'                 => 'templatetagtext',
			'label'                => __( 'Subject', 'torro-forms' ),
			'input_classes'        => array( 'regular-text' ),
			'default'              => $this->get_default_invitation_subject(),
			'template_tag_handler' => $this->template_tag_handler,
		);

		$settings_fields['invitation_message'] = array(
			'section'              => 'invitation_email',
			'type'                 => 'templatetagwysiwyg',
			'label'                => __( 'Message', 'torro-forms' ),
			'rows'                 => 12,
			'media_buttons'        => true,
			'wpautop'              => true,
			'default'              => $this->get_default_invitation_message(),
			'template_tag_handler' => $this->template_tag_handler,
		);

		$settings_fields['reinvitation_from_name'] = array(
			'section'              => 'reinvitation_email',
			'type'                 => 'templatetagtext',
			'label'                => __( 'From Name', 'torro-forms' ),
			'input_classes'        => array( 'regular-text' ),
			'default'              => '{sitetitle}',
			'template_tag_handler' => $this->template_tag_handler,
		);

		$settings_fields['reinvitation_from_email'] = array(
			'section'              => 'reinvitation_email',
			'type'                 => 'templatetagemail',
			'label'                => __( 'From Email', 'torro-forms' ),
			/* translators: %s: email address */
			'description'          => sprintf( __( 'This email address should contain the same domain like your website (e.g. %s).', 'torro-forms' ), 'email@' . $domain ),
			'input_classes'        => array( 'regular-text' ),
			'default'              => '{adminemail}',
			'template_tag_handler' => $this->template_tag_handler_email_only,
		);

		$settings_fields['reinvitation_subject'] = array(
			'section'              => 'reinvitation_email',
			'type'                 => 'templatetagtext',
			'label'                => __( 'Subject', 'torro-forms' ),
			'input_classes'        => array( 'regular-text' ),
			'default'              => $this->get_default_reinvitation_subject(),
			'template_tag_handler' => $this->template_tag_handler,
		);

		$settings_fields['reinvitation_message'] = array(
			'section'              => 'reinvitation_email',
			'type'                 => 'templatetagwysiwyg',
			'label'                => __( 'Message', 'torro-forms' ),
			'rows'                 => 12,
			'media_buttons'        => true,
			'wpautop'              => true,
			'default'              => $this->get_default_reinvitation_message(),
			'template_tag_handler' => $this->template_tag_handler,
		);

		return $settings_fields;
	}

	/**
	 * Registers all assets the submodule provides.
	 *
	 * @since 1.0.0
	 *
	 * @param Assets $assets The plugin assets instance.
	 */
	public function register_assets( $assets ) {
		$assets->register_script(
			'admin-member-invitations',
			'assets/dist/js/admin-member-invitations.js',
			array(
				'deps'          => array( 'wp-util', 'jquery' ),
				'in_footer'     => true,
				'localize_name' => 'torroMemberInvitations',
				'localize_data' => array(
					'ajaxPrefix'            => $this->module->manager()->ajax()->get_prefix(),
					'ajaxInviteMemberNonce' => $this->module->manager()->ajax()->get_nonce( 'invite_member' ),
				),
			)
		);
	}

	/**
	 * Enqueues scripts and stylesheets on the form editing screen.
	 *
	 * @since 1.0.0
	 *
	 * @param Assets $assets The plugin assets instance.
	 */
	public function enqueue_form_builder_assets( $assets ) {
		$assets->enqueue_script( 'admin-member-invitations' );
	}

	/**
	 * Sends an email invitation to a member.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data AJAX data, including $userId and $formId arguments.
	 * @return array|WP_Error AJAX response array, or error object in case of failure.
	 */
	public function ajax_invite_member( $data ) {
		if ( empty( $data['userId'] ) || empty( $data['formId'] ) ) {
			return new WP_Error( 'missing_parameters', __( 'Missing request parameters.', 'torro-forms' ) );
		}

		$user = get_user_by( 'id', $data['userId'] );
		if ( ! $user || ! $user->exists() ) {
			return new WP_Error( 'cannot_find_user', __( 'Could not find user.', 'torro-forms' ) );
		}

		$form = $this->module->manager()->forms()->get( $data['formId'] );
		if ( ! $form ) {
			return new WP_Error( 'cannot_find_form', __( 'Could not find form.', 'torro-forms' ) );
		}

		$mode = $this->is_user_invited( (int) $user->ID, $form->id ) ? 'reinvitation' : 'invitation';

		$from_name  = $this->get_option( $mode . '_from_name', '{sitetitle}' );
		$from_email = $this->get_option( $mode . '_from_email', '{adminemail}' );
		$subject    = $this->get_option( $mode . '_subject', call_user_func( array( $this, 'get_default_' . $mode . '_subject' ) ) );
		$message    = $this->get_option( $mode . '_message', call_user_func( array( $this, 'get_default_' . $mode . '_message' ) ) );

		$from_name  = $this->template_tag_handler->process_content( $from_name, array( $form, $user ) );
		$from_email = $this->template_tag_handler_email_only->process_content( $from_email, array( $form, $user ) );
		$subject    = $this->template_tag_handler->process_content( $subject, array( $form, $user ) );
		$message    = $this->template_tag_handler->process_content( $message, array( $form, $user ) );

		$message = wpautop( $message );

		add_filter( 'wp_mail_content_type', array( $this, 'override_content_type' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'override_from_name' ) );
		add_filter( 'wp_mail_from', array( $this, 'override_from_email' ) );

		$this->from_name  = $from_name;
		$this->from_email = $from_email;

		$result = wp_mail( $user->user_email, $subject, $message );

		$this->from_name  = '';
		$this->from_email = '';

		remove_filter( 'wp_mail_content_type', array( $this, 'override_content_type' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'override_from_name' ) );
		remove_filter( 'wp_mail_from', array( $this, 'override_from_email' ) );

		if ( ! $result ) {
			if ( 'reinvitation' === $mode ) {
				/* translators: %s: user display name */
				return new WP_Error( 'cannot_reinvite_user', sprintf( __( 'User %s could not be reinvited.', 'torro-forms' ), $user->display_name ) );
			}

			/* translators: %s: user display name */
			return new WP_Error( 'cannot_invite_user', sprintf( __( 'User %s could not be invited.', 'torro-forms' ), $user->display_name ) );
		}

		$this->set_user_invited( (int) $user->ID, $form->id );

		if ( 'reinvitation' === $mode ) {
			return array(
				/* translators: %s: user display name */
				'message' => sprintf( __( 'User %s was successfully reinvited.', 'torro-forms' ), $user->display_name ),
			);
		}

		return array(
			/* translators: %s: user display name */
			'message' => sprintf( __( 'User %s was successfully invited.', 'torro-forms' ), $user->display_name ),
		);
	}

	/**
	 * Gets the email content type.
	 *
	 * @since 1.0.0
	 *
	 * @return string Email content type.
	 */
	public function override_content_type() {
		return 'text/html';
	}

	/**
	 * Gets the email from name.
	 *
	 * @since 1.0.0
	 *
	 * @return string Email from name.
	 */
	public function override_from_name() {
		return $this->from_name;
	}

	/**
	 * Gets the email from email.
	 *
	 * @since 1.0.0
	 *
	 * @return string Email from email.
	 */
	public function override_from_email() {
		return $this->from_email;
	}

	/**
	 * Checks whether a user has been previously invited to a form.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id User ID.
	 * @param int $form_id Form ID.
	 * @return bool True if the user has been invited, false otherwise.
	 */
	protected function is_user_invited( $user_id, $form_id ) {
		$meta_key = $this->module->manager()->get_prefix() . 'invited_users';

		$data = $this->module->manager()->forms()->get_meta( $form_id, $meta_key );
		if ( ! is_array( $data ) ) {
			return false;
		}

		$data = array_map( 'absint', $data );

		if ( ! in_array( $user_id, $data, true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Sets a user as invited to a form.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id User ID.
	 * @param int $form_id Form ID.
	 */
	protected function set_user_invited( $user_id, $form_id ) {
		if ( $this->is_user_invited( $user_id, $form_id ) ) {
			return;
		}

		$meta_key = $this->module->manager()->get_prefix() . 'invited_users';

		$this->module->manager()->forms()->add_meta( $form_id, $meta_key, $user_id );
	}

	/**
	 * Returns the default message to display when the user is not logged in.
	 *
	 * @since 1.0.0
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
	 *
	 * @return string Message to display.
	 */
	protected function get_default_not_selected_message() {
		return __( 'You have not been selected to participate.', 'torro-forms' );
	}

	/**
	 * Returns the default subject for an invitation email, including placeholders.
	 *
	 * @since 1.0.0
	 *
	 * @return string Invitation email subject.
	 */
	protected function get_default_invitation_subject() {
		/* translators: %s: form title */
		return sprintf( __( 'Invitation to the form &#8220;%s&#8221;', 'torro-forms' ), '{formtitle}' );
	}

	/**
	 * Returns the default subject for a reinvitation email, including placeholders.
	 *
	 * @since 1.0.0
	 *
	 * @return string Reinvitation email subject.
	 */
	protected function get_default_reinvitation_subject() {
		/* translators: %s: form title */
		return sprintf( __( 'Reinvitation to the form &#8220;%s&#8221;', 'torro-forms' ), '{formtitle}' );
	}

	/**
	 * Returns the default message for an invitation email, including placeholders.
	 *
	 * @since 1.0.0
	 *
	 * @return string Invitation email message.
	 */
	protected function get_default_invitation_message() {
		/* translators: %s: user display name */
		$message = sprintf( __( 'Dear %s,', 'torro-forms' ), '{userdisplayname}' ) . "\n\n";

		/* translators: %s: form title */
		$message .= sprintf( __( 'You have been invited to participate in the form &#8220;%s&#8221; which you can find at the following URL:', 'torro-forms' ), '{formtitle}' ) . "\n\n";
		$message .= '<a href="{formurl}">{formurl}</a>' . "\n\n";
		$message .= __( 'Thanks in advance for participating!', 'torro-forms' ) . "\n\n";
		$message .= '{sitetitle}';

		return $message;
	}

	/**
	 * Returns the default message for a reinvitation email, including placeholders.
	 *
	 * @since 1.0.0
	 *
	 * @return string Reinvitation email message.
	 */
	protected function get_default_reinvitation_message() {
		/* translators: %s: user display name */
		$message = sprintf( __( 'Dear %s,', 'torro-forms' ), '{userdisplayname}' ) . "\n\n";

		/* translators: %s: form title */
		$message .= sprintf( __( 'As a reminder, a while ago you have been invited to participate in the form &#8220;%s&#8221; which you can find at the following URL:', 'torro-forms' ), '{formtitle}' ) . "\n\n";
		$message .= '<a href="{formurl}">{formurl}</a>' . "\n\n";
		$message .= __( 'If you have already participated by now, please ignore this email. Thanks!', 'torro-forms' ) . "\n\n";
		$message .= '{sitetitle}';

		return $message;
	}

	/**
	 * Registers the template tag handler for member invitations.
	 *
	 * @since 1.0.0
	 */
	protected function register_template_tag_handlers() {
		$tags = array(
			'sitetitle'       => array(
				'group'       => 'global',
				'label'       => __( 'Site Title', 'torro-forms' ),
				'description' => __( 'Inserts the site title.', 'torro-forms' ),
				'callback'    => function() {
					return get_bloginfo( 'name' );
				},
			),
			'sitetagline'     => array(
				'group'       => 'global',
				'label'       => __( 'Site Tagline', 'torro-forms' ),
				'description' => __( 'Inserts the site tagline.', 'torro-forms' ),
				'callback'    => function() {
					return get_bloginfo( 'description' );
				},
			),
			'siteurl'         => array(
				'group'       => 'global',
				'label'       => __( 'Site URL', 'torro-forms' ),
				'description' => __( 'Inserts the site home URL.', 'torro-forms' ),
				'callback'    => function() {
					return home_url( '/' );
				},
			),
			'adminemail'      => array(
				'group'       => 'global',
				'label'       => __( 'Site Admin Email', 'torro-forms' ),
				'description' => __( 'Inserts the site admin email.', 'torro-forms' ),
				'callback'    => function() {
					return get_option( 'admin_email' );
				},
			),
			'userip'          => array(
				'group'       => 'global',
				'label'       => __( 'User IP', 'torro-forms' ),
				'description' => __( 'Inserts the current user IP address.', 'torro-forms' ),
				'callback'    => function() {
					$validated_ip = Fixes::php_filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP );
					if ( empty( $validated_ip ) ) {
						return '0.0.0.0';
					}
					return $validated_ip;
				},
			),
			'refererurl'      => array(
				'group'       => 'global',
				'label'       => __( 'Referer URL', 'torro-forms' ),
				'description' => __( 'Inserts the current referer URL.', 'torro-forms' ),
				'callback'    => function() {
					return wp_get_referer();
				},
			),
			'formtitle'       => array(
				'group'       => 'form',
				'label'       => __( 'Form Title', 'torro-forms' ),
				'description' => __( 'Inserts the form title.', 'torro-forms' ),
				'callback'    => function( $form ) {
					return $form->title;
				},
			),
			'formurl'         => array(
				'group'       => 'form',
				'label'       => __( 'Form URL', 'torro-forms' ),
				'description' => __( 'Inserts the URL to the form.', 'torro-forms' ),
				'callback'    => function( $form ) {
					return get_permalink( $form->id );
				},
			),
			'formediturl'     => array(
				'group'       => 'form',
				'label'       => __( 'Form Edit URL', 'torro-forms' ),
				'description' => __( 'Inserts the edit URL for the form.', 'torro-forms' ),
				'callback'    => function( $form ) {
					return get_edit_post_link( $form->id );
				},
			),
			'useremail'       => array(
				'group'       => 'user',
				'label'       => __( 'User Email', 'torro-forms' ),
				'description' => __( 'Inserts the email address for the user.', 'torro-forms' ),
				'callback'    => function( $form, $user ) {
					return $user->user_email;
				},
			),
			'username'        => array(
				'group'       => 'user',
				'label'       => __( 'Username', 'torro-forms' ),
				'description' => __( 'Inserts the username.', 'torro-forms' ),
				'callback'    => function( $form, $user ) {
					return $user->user_login;
				},
			),
			'userdisplayname' => array(
				'group'       => 'user',
				'label'       => __( 'User Display Name', 'torro-forms' ),
				'description' => __( 'Inserts the full display name the user has chosen to be addressed with.', 'torro-forms' ),
				'callback'    => function( $form, $user ) {
					return $user->display_name;
				},
			),
		);

		$groups = array(
			'global' => _x( 'Global', 'template tag group', 'torro-forms' ),
			'form'   => _x( 'Form', 'template tag group', 'torro-forms' ),
			'user'   => _x( 'User', 'template tag group', 'torro-forms' ),
		);

		$this->template_tag_handler            = new Template_Tag_Handler(
			$this->slug,
			$tags,
			array( Form::class, WP_User::class ),
			$groups
		);
		$this->template_tag_handler_email_only = new Template_Tag_Handler(
			$this->slug . '_email_only',
			array(
				'adminemail' => $tags['adminemail'],
				'useremail'  => $tags['useremail'],
			),
			array( Form::class, WP_User::class ),
			array(
				'global' => $groups['global'],
				'user'   => $groups['user'],
			)
		);

		$this->module->manager()->template_tag_handlers()->register( $this->template_tag_handler );
		$this->module->manager()->template_tag_handlers()->register( $this->template_tag_handler_email_only );
	}
}
