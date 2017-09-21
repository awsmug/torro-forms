<?php
/**
 * Email notifications action class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Actions;

use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use awsmug\Torro_Forms\Components\Template_Tag_Handler;
use WP_Error;

/**
 * Class for an action that sends email notifications.
 *
 * @since 1.0.0
 */
class Email_Notifications extends Action {

	/**
	 * Template tag handler for email notifications.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Template_Tag_Handler
	 */
	protected $template_tag_handler;

	/**
	 * Template tag handler for email address fields.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Template_Tag_Handler
	 */
	protected $template_tag_handler_email_only;

	/**
	 * Template tag handler for complex fields with more than one line.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Template_Tag_Handler
	 */
	protected $template_tag_handler_complex;

	/**
	 * Temporary storage for email from name.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $from_name = '';

	/**
	 * Temporary storage for email from email.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $from_email = '';

	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function bootstrap() {
		$this->slug        = 'email_notifications';
		$this->title       = __( 'Email Notifications', 'torro-forms' );
		$this->description = __( 'Sends one or more email notifications to specific addresses.', 'torro-forms' );

		$this->register_template_tag_handlers();
	}

	/**
	 * Handles the action for a specific form submission.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Submission $submission Submission to handle by the action.
	 * @param Form       $form       Form the submission applies to.
	 * @return bool|WP_Error True on success, error object on failure.
	 */
	public function handle( $submission, $form ) {
		$notifications = $this->get_form_option( $form->id, 'notifications', array() );
		if ( empty( $notifications ) ) {
			return true;
		}

		add_filter( 'wp_mail_content_type', array( $this, 'override_content_type' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'override_from_name' ) );
		add_filter( 'wp_mail_from', array( $this, 'override_from_email' ) );

		foreach ( $notifications as $notification ) {
			if ( empty( $notification['to_email'] ) || empty( $notification['subject'] ) || empty( $notification['message'] ) ) {
				continue;
			}

			foreach ( $notification as $key => $value ) {
				switch ( $key ) {
					case 'from_email':
					case 'reply_email':
					case 'to_email':
						$notification[ $key ] = $this->template_tag_handler_email_only->process_content( $value, array( $form, $submission ) );
						break;
					case 'message':
						$notification[ $key ] = $this->template_tag_handler_complex->process_content( $value, array( $form, $submission ) );
						break;
					default:
						$notification[ $key ] = $this->template_tag_handler->process_content( $value, array( $form, $submission ) );
				}
			}

			$notification['message'] = wpautop( $notification['message'] );

			$this->from_name  = $notification['from_name'];
			$this->from_email = $notification['from_email'];

			// TODO: Handle errors here.
			wp_mail( $notification['to_email'], $notification['subject'], $notification['message'], array( 'Reply-To: ' . $notification['reply_email'] ) );
		}

		$this->from_name = '';
		$this->from_email = '';

		remove_filter( 'wp_mail_content_type', array( $this, 'override_content_type' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'override_from_name' ) );
		remove_filter( 'wp_mail_from', array( $this, 'override_from_email' ) );

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
		$meta_fields = $this->_get_meta_fields();

		$meta_fields['enabled'] = array(
			'type'       => 'checkbox',
			'label'      => _x( 'Enable?', 'action', 'torro-forms' ),
		);

		$domain = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
		if ( ! $domain ) {
			// Fall back to a random domain.
			$domain = 'yourwebsite.com';
		}

		$meta_fields['notifications'] = array(
			'type'        => 'group',
			'label'       => __( 'Notifications', 'torro-forms' ),
			'description' => __( 'Add email notifications to send.', 'torro-forms' ),
			'repeatable'  => 8,
			'fields'      => array(
				'from_name'   => array(
					'type'                 => 'templatetagtext',
					'label'                => __( 'From Name', 'torro-forms' ),
					'input_classes'        => array( 'regular-text' ),
					'template_tag_handler' => $this->template_tag_handler,
				),
				'from_email'  => array(
					'type'                 => 'templatetagemail',
					'label'                => __( 'From Email', 'torro-forms' ),
					/* translators: %s: email address */
					'description'          => sprintf( __( 'This email address should contain the same domain like your website (e.g. %s).', 'torro-forms' ), 'email@' . $domain ),
					'input_classes'        => array( 'regular-text' ),
					'template_tag_handler' => $this->template_tag_handler_email_only,
				),
				'reply_email' => array(
					'type'                 => 'templatetagemail',
					'label'                => __( 'Reply Email', 'torro-forms' ),
					'input_classes'        => array( 'regular-text' ),
					'template_tag_handler' => $this->template_tag_handler_email_only,
				),
				'to_email'    => array(
					'type'                 => 'templatetagemail',
					'label'                => __( 'To Email', 'torro-forms' ),
					'input_classes'        => array( 'regular-text' ),
					'template_tag_handler' => $this->template_tag_handler_email_only,
				),
				'subject'     => array(
					'type'                 => 'templatetagtext',
					'label'                => __( 'Subject', 'torro-forms' ),
					'input_classes'        => array( 'regular-text' ),
					'template_tag_handler' => $this->template_tag_handler,
				),
				'message'     => array(
					'type'                 => 'templatetagwysiwyg',
					'label'                => __( 'Message', 'torro-forms' ),
					'media_buttons'        => true,
					'template_tag_handler' => $this->template_tag_handler_complex,
				),
			),
		);

		return $meta_fields;
	}

	/**
	 * Registers the template tag handler for email notifications.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_template_tag_handlers() {
		$tags = array(
			'sitetitle'          => array(
				'group'       => 'global',
				'label'       => __( 'Site Title', 'torro-forms' ),
				'description' => __( 'Inserts the site title.', 'torro-forms' ),
				'callback'    => function() {
					return get_bloginfo( 'name' );
				},
			),
			'sitetagline'        => array(
				'group'       => 'global',
				'label'       => __( 'Site Tagline', 'torro-forms' ),
				'description' => __( 'Inserts the site tagline.', 'torro-forms' ),
				'callback'    => function() {
					return get_bloginfo( 'description' );
				},
			),
			'siteurl'            => array(
				'group'       => 'global',
				'label'       => __( 'Site URL', 'torro-forms' ),
				'description' => __( 'Inserts the site home URL.', 'torro-forms' ),
				'callback'    => function() {
					return home_url( '/' );
				},
			),
			'adminemail'         => array(
				'group'       => 'global',
				'label'       => __( 'Site Admin Email', 'torro-forms' ),
				'description' => __( 'Inserts the site admin email.', 'torro-forms' ),
				'callback'    => function() {
					return get_option( 'admin_email' );
				},
			),
			'userip'             => array(
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
			'refererurl'         => array(
				'group'       => 'global',
				'label'       => __( 'Referer URL', 'torro-forms' ),
				'description' => __( 'Inserts the current referer URL.', 'torro-forms' ),
				'callback'    => function() {
					return wp_get_referer();
				},
			),
			'formtitle'          => array(
				'group'       => 'form',
				'label'       => __( 'Form Title', 'torro-forms' ),
				'description' => __( 'Inserts the form title.', 'torro-forms' ),
				'callback'    => function( $form ) {
					return $form->title;
				},
			),
			'formurl'            => array(
				'group'       => 'form',
				'label'       => __( 'Form URL', 'torro-forms' ),
				'description' => __( 'Inserts the URL to the form.', 'torro-forms' ),
				'callback'    => function( $form ) {
					return get_permalink( $form->id );
				},
			),
			'formediturl'        => array(
				'group'       => 'form',
				'label'       => __( 'Form Edit URL', 'torro-forms' ),
				'description' => __( 'Inserts the edit URL for the form.', 'torro-forms' ),
				'callback'    => function( $form ) {
					return get_edit_post_link( $form->id );
				},
			),
			'submissionurl'      => array(
				'group'       => 'submission',
				'label'       => __( 'Submission URL', 'torro-forms' ),
				'description' => __( 'Inserts the URL to the submission.', 'torro-forms' ),
				'callback'    => function( $form, $submission ) {
					return add_query_arg( 'torro_submission_id', $submission->id, get_permalink( $form->id ) );
				},
			),
			'submissionediturl' => array(
				'group'       => 'submission',
				'label'       => __( 'Submission Edit URL', 'torro-forms' ),
				'description' => __( 'Inserts the edit URL for the submission.', 'torro-forms' ),
				'callback'    => function( $form, $submission ) {
					return add_query_arg( 'id', $submission->id, torro()->admin_pages()->get( 'edit_submission' )->url );
				},
			),
			'submissiondatetime' => array(
				'group'       => 'submission',
				'label'       => __( 'Submission Date and Time', 'torro-forms' ),
				'description' => __( 'Inserts the submission date and time.', 'torro-forms' ),
				'callback'    => function( $form, $submission ) {
					$date = $submission->format_datetime( get_option( 'date_format' ), false );
					$time = $submission->format_datetime( get_option( 'time_format' ), false );

					/* translators: 1: formatted date, 2: formatted time */
					return sprintf( _x( '%1$s at %2$s', 'concatenating date and time', 'torro-forms' ), $date, $time );
				},
			),
		);

		$complex_tags = array(
			'allelements'        => array(
				'group'       => 'submission',
				'label'       => __( 'All Element Values', 'torro-forms' ),
				'description' => __( 'Inserts all element values from the submission.', 'torro-forms' ),
				'callback'    => function( $form, $submission ) {
					$element_columns = array();
					foreach ( $form->get_elements() as $element ) {
						$element_type = $element->get_element_type();
						if ( ! $element_type ) {
							continue;
						}

						$element_columns[ $element->id ] = array(
							'columns'  => $element_type->get_export_columns( $element ),
							'callback' => function( $values ) use ( $element, $element_type ) {
								return $element_type->format_values_for_export( $values, $element, 'html' );
							},
						);
					}

					$element_values = $submission->get_element_values_data();

					$output = '<table style="width:100%;">';

					foreach ( $element_columns as $element_id => $data ) {
						$values = isset( $element_values[ $element_id ] ) ? $element_values[ $element_id ] : array();

						$column_values = call_user_func( $data['callback'], $values );

						foreach ( $data['columns'] as $slug => $label ) {
							$output .= '<tr>';
							$output .= '<th scope="row">' . esc_html( $label ) . '</th>';
							$output .= '<td>' . esc_html( $column_values[ $slug ] ) . '</td>';
							$output .= '</tr>';
						}
					}

					$output .= '</table>';

					return $output;
				},
			),
		);

		$groups = array(
			'global'     => _x( 'Global', 'template tag group', 'torro-forms' ),
			'form'       => _x( 'Form', 'template tag group', 'torro-forms' ),
			'submission' => _x( 'Submission', 'template tag group', 'torro-forms' ),
		);

		$this->template_tag_handler            = new Template_Tag_Handler( $this->slug, $tags, array( Form::class, Submission::class ), $groups );
		$this->template_tag_handler_email_only = new Template_Tag_Handler( $this->slug . '_email_only', array( 'adminemail' => $tags['adminemail'] ), array( Form::class, Submission::class ), array( 'global' => $groups['global'] ) );
		$this->template_tag_handler_complex    = new Template_Tag_Handler( $this->slug . '_complex', array_merge( $tags, $complex_tags ), array( Form::class, Submission::class ), $groups );

		$this->module->manager()->template_tag_handlers()->register( $this->template_tag_handler );
		$this->module->manager()->template_tag_handlers()->register( $this->template_tag_handler_email_only );
		$this->module->manager()->template_tag_handlers()->register( $this->template_tag_handler_complex );
	}

	/**
	 * Gets the email content type.
	 *
	 * @since 1.0.0
	 * @access public
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
	 * @access public
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
	 * @access public
	 *
	 * @return string Email from email.
	 */
	public function override_from_email() {
		return $this->from_email;
	}
}
