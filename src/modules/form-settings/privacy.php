<?php
/**
 * Privacy form setting class
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Modules\Form_Settings;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use awsmug\Torro_Forms\Modules\Meta_Submodule_Trait;
use awsmug\Torro_Forms\Modules\Settings_Submodule_Trait;
use awsmug\Torro_Forms\Components\Template_Tag_Handler;
use Leaves_And_Love\Plugin_Lib\Fields\Field_Manager;

use WP_User;

/**
 * Class for form settings for privacy.
 *
 * @since 1.1.0
 */
class Privacy extends Form_Setting {
	use Settings_Submodule_Trait, Meta_Submodule_Trait;

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
			'double_optin_email_element_id'       => array(
				'type'        => 'text',
				'label'       => __( 'Email Element', 'torro-forms' ),
				'description' => __( 'Choose the element which will contains the email address.', 'torro-forms' ),
				'default'     => '',
			),
			'double_optin_redirect_page_id'       => array(
				'type'        => 'text',
				'label'       => __( 'Redirect Page', 'torro-forms' ),
				'description' => __( 'Choose a page where the user gets redirected after clicking link in email.', 'torro-forms' ),
				'default'     => '',
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

		$this->send_email( $form, $submission );

		return false;
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
	private function send_email( $form, $submission ) {;
		$to = $this->get_email_to_email( $form, $submission );
		$from_email = $this->get_email_from_email ( $form, $submission );
		$from_name = $this->get_email_from_name ( $form, $submission );
		$subject = $this->get_email_subject( $form, $submission );
		$message = $this->get_email_message( $form, $submission );

		wp_mail( $to, $subject, $message );
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
		$from_email = $this->get_option(  'double_optin_from_email', call_user_func( array( $this, 'get_default_from_email' ) ) );
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
		$from_name = $this->get_option(  'double_optin_from_name', call_user_func( array( $this, 'get_default_from_name' ) ) );
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
		$email_element_id = $this->get_form_option( $form->id, 'double_optin_email_element_id', false );

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
		$subject = $this->get_option(  'double_optin_subject', call_user_func( array( $this, 'get_default_subject' ) ) );
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
		$message = $this->get_option(  'double_optin_message', call_user_func( array( $this, 'get_default_message' ) ) );
		$message = $this->template_tag_handler->process_content( $message, array( $form, $submission ) );

		return $message;
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
	 * Returns the available settings sections for the submodule.
	 *
	 * @since 1.1.0
	 *
	 * @return array Associative array of `$section_slug => $section_args` pairs.
	 */
	public function get_settings_sections() {
		$settings_sections = parent::get_settings_sections();

		$settings_sections['double_optin'] = array(
			'title'       => __( 'Double Optin Email Template', 'torro-forms' ),
			'description' => __( 'Setup mail templates for double optin email to users.', 'torro-forms' ),
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

		$settings_fields['double_optin_from_name'] = array(
			'section'              => 'double_optin',
			'type'                 => 'templatetagtext',
			'label'                => __( 'From Name', 'torro-forms' ),
			'input_classes'        => array( 'regular-text' ),
			'default'              => $this->get_default_from_name(),
			'template_tag_handler' => $this->template_tag_handler,
		);

		$settings_fields['double_optin_from_email'] = array(
			'section'              => 'double_optin',
			'type'                 => 'templatetagemail',
			'label'                => __( 'From Email', 'torro-forms' ),
			/* translators: %s: email address */
			'description'          => sprintf( __( 'This email address should contain the same domain like your website (e.g. %s).', 'torro-forms' ), 'email@' . $domain ),
			'input_classes'        => array( 'regular-text' ),
			'default'              => $this->get_default_from_email(),
			'template_tag_handler' => $this->template_tag_handler_email_only,
		);

		$settings_fields['double_optin_subject'] = array(
			'section'              => 'double_optin',
			'type'                 => 'templatetagtext',
			'label'                => __( 'Subject', 'torro-forms' ),
			'input_classes'        => array( 'regular-text' ),
			'default'              => $this->get_default_subject(),
			'template_tag_handler' => $this->template_tag_handler,
		);

		$settings_fields['double_optin_message'] = array(
			'section'              => 'double_optin',
			'type'                 => 'templatetagwysiwyg',
			'label'                => __( 'Message', 'torro-forms' ),
			'rows'                 => 12,
			'media_buttons'        => true,
			'wpautop'              => true,
			'default'              => $this->get_default_message(),
			'template_tag_handler' => $this->template_tag_handler,
		);

		return $settings_fields;
	}

	/**
	 * Returns the default from email for an double optin email, including placeholders.
	 *
	 * @since 1.0.0
	 *
	 * @return string Invitation email subject.
	 */
	protected function get_default_from_email() {
		/* translators: %s: form title */
		return '{adminemail}';
	}

	/**
	 * Returns the default from name for an double optin email, including placeholders.
	 *
	 * @since 1.0.0
	 *
	 * @return string Invitation email subject.
	 */
	protected function get_default_from_name() {
		/* translators: %s: form title */
		return '{sitetitle}';
	}

	/**
	 * Returns the default subject for an double optin email, including placeholders.
	 *
	 * @since 1.0.0
	 *
	 * @return string Invitation email subject.
	 */
	protected function get_default_subject() {
		/* translators: %s: form title */
		return sprintf( __( 'Confirm your email address for &#8220;%s&#8221;', 'torro-forms' ), '{formtitle}' );
	}

	/**
	 * Returns the default message for an double optin email, including placeholders.
	 *
	 * @since 1.0.0
	 *
	 * @return string Invitation email message.
	 */
	protected function get_default_message() {
		/* translators: %s: user display name */
		$message = __( 'Dear User,', 'torro-forms' ) . "\n\n";

		/* translators: %s: form title */
		$message .= sprintf( __( 'We need to verify that {emailaddress} is your email address. Please confirm the address by clicking the following link:', 'torro-forms' ), '{formtitle}' ) . "\n\n";
		$message .= '<a href="{doubleoptinurl}">{doubleoptinurl}</a>' . "\n\n";
		$message .= __( 'Thanks in advance for participating!', 'torro-forms' ) . "\n\n";
		$message .= '{sitetitle}';

		return $message;
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
