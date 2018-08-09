<?php
/**
 * Privacy form setting class
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Modules\Form_Settings;
use awsmug\Torro_Forms\Components\Email;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use awsmug\Torro_Forms\Modules\Meta_Submodule_Trait;
use awsmug\Torro_Forms\Modules\Settings_Submodule_Trait;
use awsmug\Torro_Forms\Components\Template_Tag_Handler;
use Leaves_And_Love\Plugin_Lib\Fields\Field_Manager;

/**
 * Class for form settings for privacy.
 *
 * @since 1.1.0
 */
class Privacy extends Form_Setting {
	use Settings_Submodule_Trait, Meta_Submodule_Trait, Email;

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
	 * @since 1.1.0
	 */
	protected function bootstrap() {
		$this->slug        = 'privacy';
		$this->title       = __( 'Privacy', 'torro-forms' );
		$this->description = __( 'Form privacy settings.', 'torro-forms' );

		Field_Manager::register_field_type( 'autocompletewithbutton', Autocomplete_With_Button::class );

		$this->register_template_tag_handlers();
	}

	/**
	 * Returns the available meta box fields for the module.
	 *
	 * @since 1.1.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_meta_fields() {
		$prefix = $this->module->get_prefix();

		$meta_fields = array(
			'double_optin' => array(
				'type'         => 'checkbox',
				'label'        => __( 'Enable', 'torro-forms' ),
				'visual_label' => __( 'Double Opt-In', 'torro-forms' ),
				'description'  => __( 'Click to activate the double opt-in. After activation a double opt-in template variable {double-opt-in-link} will be available for email notifications and submissions will have an "checked" or "unchecked" status.', 'torro-forms' ),
			),
			'double_optin_email_from_name'   => array(
				'type'                 => 'templatetagtext',
				'label'                => __( 'From Name', 'torro-forms' ),
				'input_classes'        => array( 'regular-text' ),
				'template_tag_handler' => $this->template_tag_handler,
				'default'              => $this->get_default_email_from_name()
			),
			'double_optin_email_from_email'  => array(
				'type'                 => 'templatetagemail',
				'label'                => __( 'From Email', 'torro-forms' ),
				/* translators: %s: email address */
				'description'          => sprintf( __( 'This email address should contain the same domain like your website (e.g. %s).', 'torro-forms' ), 'email@' . $domain ),
				'input_classes'        => array( 'regular-text' ),
				'template_tag_handler' => $this->template_tag_handler_email_only,
				'default'              => $this->get_default_email_from_email()
			),
			'double_optin_email_to_email'    => array(
				'type'                 => 'templatetagemail',
				'label'                => __( 'To Email', 'torro-forms' ),
				'input_classes'        => array( 'regular-text' ),
				'description'          => _x( 'Please enter a field from the form that contains the email address.', 'To email in double opt-in email ','torro-forms' ),
				'template_tag_handler' => $this->template_tag_handler_email_only,
			),
			'double_optin_email_subject'     => array(
				'type'                 => 'templatetagtext',
				'label'                => __( 'Subject', 'torro-forms' ),
				'input_classes'        => array( 'regular-text' ),
				'template_tag_handler' => $this->template_tag_handler,
				'default'              => $this->get_default_email_subject()
			),
			'double_optin_email_message'     => array(
				'type'                 => 'templatetagwysiwyg',
				'label'                => __( 'Message', 'torro-forms' ),
				'media_buttons'        => true,
				'template_tag_handler' => $this->template_tag_handler,
				'default'              => $this->get_default_email_message()
			),
		);

		/**
		 * Filters the meta fields in the form settings metabox.
		 *
		 * @since 1.1.0
		 *
		 * @param array $fields Array of `$field_slug => $field_data` pairs.
		 */
		return apply_filters( "{$prefix}form_settings_privacy_meta_fields", $meta_fields );
	}

	/**
	 * Interrupting submission.
	 *
	 * Interrupts submission if the double opt-in option is selected for the form and sends out the email.
	 *
	 * @since 1.1.0
	 *
	 * @param bool       $should_complete Whether the completion process for the form submission should proceed. Default true.
	 * @param Submission $submission      Submission object.
	 * @param Form       $form            Form object.
	 *
	 * @return bool      $should_complete Whether the completion process for the form submission should proceed.
	 */
	public function interrupt_submission( $should_complete, $submission, $form ) {
		if( false === $this->get_form_option( $form->id, 'double_optin', false ) ) {
			return true;
		}

		$submission->optin_key = $this->create_torro_optin_key( $submission );
		$submission->sync_upstream();

		$this->send_optin_mail( $form, $submission );

		return false;
	}

	/**
	 * Showing success message after submission.
	 *
	 * @since 1.1.0
	 *
	 * @param string     $content    Form content to filter.
	 * @param string     $content    Form content to filter.
	 * @param string     $content    Form content to filter.
	 * @return string    $content Content to show.
	 */
	public function success_message( $content, $form, $submission ) {
		return '<div class="' . esc_attr( $this->get_notice_class( 'notice' ) ) . '"><p>' . __( 'Thank you for submitting!', 'torro-forms') . '</p></div>';
	}

	/**
	 * Gets the CSS class to use for notices.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Optional. Notice type. Either 'success', 'info', 'warning' or 'error'. Default 'warning'.
	 * @return string CSS class to use for notices of the given type.
	 */
	protected function get_notice_class( $type = 'warning' ) {
		/**
		 * Filters the CSS class to use for notices.
		 *
		 * @since 1.0.0
		 *
		 * @param string $notice_class Notice CSS class. Default 'torro-notice torro-{$type}-notice'.
		 * @param string $type         Notice type.
		 */
		return apply_filters( "{$this->module->get_prefix()}form_notice_class", "torro-notice torro-{$type}-notice", $type );
	}

	/**
	 * Creates a submission id.
	 *
	 * @since 1.1.0
	 *
	 * @param  Submission $submission      Submission object.
	 * @return string                      Submission ID.
	 */
	private function create_torro_optin_key( $submission ) {
		return md5( spl_object_hash( $submission ) );
	}

	/**
	 * Sends out email.
	 *
	 * @since 1.1.0
	 *
	 * @param Form       $form          Form object.
	 * @param Submission $submission    Submission object.
	 */
	private function send_optin_mail( $form, $submission ) {;
		$to_email = $this->get_email_to_email( $form, $submission );
		$from_email = $this->get_email_from_email ( $form, $submission );
		$from_name = $this->get_email_from_name ( $form, $submission );
		$subject = $this->get_email_subject( $form, $submission );
		$message = $this->get_email_message( $form, $submission );

		$this->setup_mail( $from_name, $from_email, $to_email, $subject, $message );
		$this->send_mail();
	}

	/**
	 * Get From Email Address.
	 *
	 * @since 1.1.0
	 *
	 * @param  Form       $form          Form object.
	 * @param  Submission $submission    Submission object.
	 * @return string     $email_address Email address.
	 */
	private function get_email_from_email( $form, $submission ) {
		$from_email = $this->get_form_option( $form->id, 'double_optin_email_from_email', false );
		$from_email = $this->template_tag_handler->process_content( $from_email, array( $form, $submission ) );

		return $from_email;
	}

	/**
	 * Get Email From Name.
	 *
	 * @since 1.1.0
	 *
	 * @param  Form       $form          Form object.
	 * @param  Submission $submission    Submission object.
	 * @return string     $email_address Email address.
	 */
	private function get_email_from_name( $form, $submission ) {
		$from_name = $this->get_form_option( $form->id, 'double_optin_email_from_name', $this->get_default_email_from_name() );
		$from_name = $this->template_tag_handler->process_content( $from_name, array( $form, $submission ) );

		return $from_name;
	}

	/**
	 * Get Email to address.
	 *
	 * @since 1.1.0
	 *
	 * @param  Form       $form          Form object.
	 * @param  Submission $submission    Submission object.
	 * @return string     $email_address Email address.
	 */
	private function get_email_to_email( $form, $submission ) {
		$email_element_id = $this->get_form_option( $form->id, 'double_optin_email_to_email', false );

		$data = $submission->get_element_values_data();
		$email_address = $data[ $email_element_id ]['_main'];

		return $email_address;
	}

	/**
	 * Get Email subject.
	 *
	 * @since 1.1.0
	 *
	 * @param  Form   $form    Form object.
	 * @return string $subject Email subject.
	 */
	private function get_email_subject( $form, $submission ) {
		$subject = $this->get_form_option( $form->id, 'double_optin_email_subject', $this->get_default_email_subject() );
		$subject = $this->template_tag_handler->process_content( $subject, array( $form, $submission ) );

		return $subject;
	}

	/**
	 * Get Email content.
	 *
	 * @since 1.1.0
	 *
	 * @param  Form   $form    Form object.
	 * @param  Submission $submission Submission object.
	 * @return string $content Email content.
	 */
	private function get_email_message( $form, $submission ) {
		$message = $this->get_form_option( $form->id, 'double_optin_email_message', $this->get_default_email_message() );
		$message = $this->template_tag_handler->process_content( $message, array( $form, $submission ) );

		return $message;
	}

	/**
	 * Returns the default from email for an double optin email, including placeholders.
	 *
	 * @since 1.0.0
	 *
	 * @return string Invitation email subject.
	 */
	protected function get_default_email_from_email() {
		return '{adminemail}';
	}

	/**
	 * Returns the default from name for an double optin email, including placeholders.
	 *
	 * @since 1.0.0
	 *
	 * @return string Invitation email subject.
	 */
	protected function get_default_email_from_name() {
		return '{sitetitle}';
	}

	/**
	 * Returns the default subject for an double optin email, including placeholders.
	 *
	 * @since 1.0.0
	 *
	 * @return string Invitation email subject.
	 */
	protected function get_default_email_subject() {
		return sprintf( __( 'Confirm your email address for &#8220;%s&#8221;', 'torro-forms' ), '{formtitle}' );
	}


	/**
	 * Returns the default email message for an double optin email, including placeholders.
	 *
	 * @since 1.1.0
	 *
	 * @return string Invitation email message.
	 */
	protected function get_default_email_message() {
		return _x( 'Dear user!\r\nThank you for your submission! To complete the process, you must click on the following link to confirm your identity:\n\n{doubleoptinurl}\n\nCheers,\n\n{sitetitle}', 'Message in double opt-in email ', 'torro-forms' );
	}

	/**
	 * Get Redirect URL.
	 *
	 * @since 1.1.0
	 *
	 * @param  Form   $form Form object.
	 * @return string $url  Redirect URL.
	 */
	private function get_redirect_url( $form ) {
		$redirect_page_id = $this->get_form_option( $form->id, 'double_optin_redirect_page_id', false );
		$url = get_permalink( $redirect_page_id );

		return $url;
	}

	/**
	 * Catch redirected url.
	 *
	 * Catches a link rom a double optin mail.
	 *
	 * @since 1.1.0
	 */
	public function catch_redirected_url() {
		if( ! isset( $_GET['torro_optin_key'] ) ) {
			return;
		}

		$optin_key = $_GET['torro_optin_key'];

		$query = array(
			'meta_query'  => array(
				array(
					'key'     => 'optin_key',
					'value'   => $optin_key,
					'compare' => '=',
				),
			),
		);

		$results = torro()->submissions()->query( $query );

		if( 0 === $results->get_total() ) {
			header('HTTP/1.1 301 Moved Permanently' );
			header('Location: ' . get_bloginfo('url') );
			exit();
		}

		$submission = $results->current();
		$form = $submission->get_form();

		torro()->forms()->frontend_submission_handler()->complete_form_submission( $submission, $form );
	}

	/**
	 * Sets up all action and filter hooks for the service.
	 *
	 * Hooks declared here must occur at some point after `init`.
	 *
	 * @since 1.1.0
	 */
	protected function setup_hooks() {
		$prefix = $this->module->get_prefix();

		$this->filters[] = array(
			'name'     => "{$prefix}should_complete_submission",
			'callback' => array( $this, 'interrupt_submission' ),
			'priority' => 10,
			'num_args' => 3,
		);

		$this->filters[] = array(
			'name'     => "{$prefix}render_form_content",
			'callback' => array( $this, 'success_message' ),
			'priority' => 10,
			'num_args' => 1,
		);

		$this->actions[] = array(
			'name'     => "wp_loaded",
			'callback' => array( $this, 'catch_redirected_url' ),
			'priority' => PHP_INT_MAX,
			'num_args' => 1,
		);
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
					$validated_ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP );
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
				'callback'    => function( $form, $submission ) {
					return $form->title;
				},
			),
			'formurl'         => array(
				'group'       => 'form',
				'label'       => __( 'Form URL', 'torro-forms' ),
				'description' => __( 'Inserts the URL to the form.', 'torro-forms' ),
				'callback'    => function( $form, $submission ) {
					return get_permalink( $form->id );
				},
			),
			'formediturl'     => array(
				'group'       => 'form',
				'label'       => __( 'Form Edit URL', 'torro-forms' ),
				'description' => __( 'Inserts the edit URL for the form.', 'torro-forms' ),
				'callback'    => function( $form, $submission ) {
					return get_edit_post_link( $form->id );
				},
			),
			'toemail'     => array(
				'group'       => 'form',
				'label'       => __( 'To email address', 'torro-forms' ),
				'description' => __( 'Inserts the to email address to the form.', 'torro-forms' ),
				'callback'    => function( $form, $submission ) {
					return $this->get_email_to_email( $form, $submission );
				},
			),
			'doubleoptinurl'     => array(
				'group'       => 'form',
				'label'       => __( 'Double Optin URL', 'torro-forms' ),
				'description' => __( 'Inserts the double optin URL for the form.', 'torro-forms' ),
				'callback'    => function( $form, $submission ) {
					return home_url( '/' ) . "?torro_optin_key=" . $submission->optin_key;
				},
			),
		);

		$groups = array(
			'global' => _x( 'Global', 'template tag group', 'torro-forms' ),
			'form'   => _x( 'Form', 'template tag group', 'torro-forms' ),
		);

		$this->template_tag_handler            = new Template_Tag_Handler(
			$this->slug,
			$tags,
			array( Form::class, Submission::class ),
			$groups
		);
		$this->template_tag_handler_email_only = new Template_Tag_Handler(
			$this->slug . '_email_only',
			array(
				'adminemail' => $tags['adminemail'],
			),
			array( Form::class, Submission::class ),
			array(
				'global' => $groups['global'],
			)
		);

		$this->module->manager()->template_tag_handlers()->register( $this->template_tag_handler );
		$this->module->manager()->template_tag_handlers()->register( $this->template_tag_handler_email_only );
	}
}
