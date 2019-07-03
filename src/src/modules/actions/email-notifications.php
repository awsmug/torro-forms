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
use awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Base\Textfield;
use awsmug\Torro_Forms\Components\Template_Tag_Handler;
use awsmug\Torro_Forms\Modules\Assets_Submodule_Interface;
use Leaves_And_Love\Plugin_Lib\Fixes;
use WP_Error;

/**
 * Class for an action that sends email notifications.
 *
 * @since 1.0.0
 */
class Email_Notifications extends Action implements Assets_Submodule_Interface {

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
	 * Template tag handler for complex fields with more than one line.
	 *
	 * @since 1.0.0
	 * @var Template_Tag_Handler
	 */
	protected $template_tag_handler_complex;

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
	 * Temporary storage for PHPMailer error object.
	 *
	 * @since 1.0.0
	 * @var WP_Error|null
	 */
	private $phpmailer_error = null;

	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.0.0
	 */
	protected function bootstrap() {
		$this->slug  = 'email_notifications';
		$this->title = __( 'Email Notifications', 'torro-forms' );

		$this->register_template_tag_handlers();
	}

	/**
	 * Checks whether the access control is enabled for a specific form.
	 *
	 * @since 1.0.0
	 *
	 * @param Form $form Form object to check.
	 * @return bool True if the access control is enabled, false otherwise.
	 */
	public function enabled( $form ) {
		$notifications = $this->get_form_option( $form->id, 'notifications', array() );

		$notifications = array_filter(
			$notifications,
			function( $notification ) {
				return ! empty( $notification['to_email'] ) && ! empty( $notification['subject'] ) && ! empty( $notification['message'] );
			}
		);

		if ( ! empty( $notifications ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Handles the action for a specific form submission.
	 *
	 * @since 1.0.0
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

		$dynamic_template_tags = $this->get_dynamic_template_tags( $form, true );

		foreach ( $dynamic_template_tags as $slug => $data ) {
			if ( isset( $data['email_support'] ) ) {
				$email_support = (bool) $data['email_support'];

				unset( $data['email_support'] );

				if ( $email_support ) {
					$this->template_tag_handler_email_only->add_tag( $slug, $data );
				}
			}

			$this->template_tag_handler->add_tag( $slug, $data );
			$this->template_tag_handler_complex->add_tag( $slug, $data );
		}

		$error = new WP_Error();

		add_filter( 'wp_mail_content_type', array( $this, 'override_content_type' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'override_from_name' ) );
		add_filter( 'wp_mail_from', array( $this, 'override_from_email' ) );
		add_action( 'wp_mail_failed', array( $this, 'store_phpmailer_error' ) );

		$email_header_fields = array(
			'reply_email' => 'Reply-To',
			'cc_email'    => 'Cc',
			'bcc_email'   => 'Bcc',
		);

		foreach ( $notifications as $notification ) {
			if ( empty( $notification['to_email'] ) || empty( $notification['subject'] ) || empty( $notification['message'] ) ) {
				continue;
			}

			foreach ( $notification as $key => $value ) {
				switch ( $key ) {
					case 'from_email':
					case 'reply_email':
					case 'to_email':
					case 'cc_email':
					case 'bcc_email':
						$notification[ $key ] = $this->template_tag_handler_email_only->process_content( $value, array( $form, $submission ) );
						break;
					case 'message':
						$notification[ $key ] = $this->template_tag_handler_complex->process_content( $value, array( $form, $submission ) );
						break;
					default:
						$notification[ $key ] = $this->template_tag_handler->process_content( $value, array( $form, $submission ) );
				}
			}

			$notification['message'] = $this->wrap_message( wpautop( $notification['message'] ), $notification['subject'] );

			$this->from_name  = $notification['from_name'];
			$this->from_email = $notification['from_email'];

			$headers = array();
			foreach ( $email_header_fields as $field => $header ) {
				if ( ! empty( $notification[ $field ] ) ) {
					$headers[] = $header . ': ' . $notification[ $field ];
				}
			}

			$sent = wp_mail( $notification['to_email'], $notification['subject'], $notification['message'], $headers );
			if ( ! $sent ) {
				/* translators: %s: email address */
				$error_message = sprintf( __( 'Email notification to %s could not be sent.', 'torro-forms' ), $notification['to_email'] );
				if ( $this->phpmailer_error ) {
					/* translators: %s: error message */
					$error_message .= ' ' . sprintf( __( 'Original error message: %s', 'torro-forms' ), $this->phpmailer_error->get_error_message() );

					$this->phpmailer_error = null;
				}

				$error->add( 'email_notification_not_sent', $error_message );
			}
		}

		$this->from_name  = '';
		$this->from_email = '';

		remove_filter( 'wp_mail_content_type', array( $this, 'override_content_type' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'override_from_name' ) );
		remove_filter( 'wp_mail_from', array( $this, 'override_from_email' ) );
		remove_action( 'wp_mail_failed', array( $this, 'store_phpmailer_error' ) );

		foreach ( $dynamic_template_tags as $slug => $data ) {
			if ( isset( $data['email_support'] ) && $data['email_support'] ) {
				$this->template_tag_handler_email_only->remove_tag( $slug );
			}

			$this->template_tag_handler->remove_tag( $slug );
			$this->template_tag_handler_complex->remove_tag( $slug );
		}

		if ( ! empty( $error->errors ) ) {
			return $error;
		}

		return true;
	}

	/**
	 * Returns the available meta fields for the submodule.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_meta_fields() {
		$meta_fields = $this->_get_meta_fields();

		unset( $meta_fields['enabled'] );

		$domain = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
		if ( ! $domain ) {
			// Fall back to a random domain.
			$domain = 'yourwebsite.com';
		}

		$meta_fields['notifications'] = array(
			'type'       => 'group',
			'label'      => __( 'Notifications', 'torro-forms' ),
			'repeatable' => 8,
			'fields'     => array(
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
				'cc_email'    => array(
					'type'                 => 'templatetagemail',
					'label'                => _x( 'Cc', 'email', 'torro-forms' ),
					'input_classes'        => array( 'regular-text' ),
					'template_tag_handler' => $this->template_tag_handler_email_only,
				),
				'bcc_email'   => array(
					'type'                 => 'templatetagemail',
					'label'                => _x( 'Bcc', 'email', 'torro-forms' ),
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
					$validated_ip = Fixes::php_filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP );
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
			'submissionediturl'  => array(
				'group'       => 'submission',
				'label'       => __( 'Submission Edit URL', 'torro-forms' ),
				'description' => __( 'Inserts the edit URL for the submission.', 'torro-forms' ),
				'callback'    => function( $form, $submission ) {
					return add_query_arg(
						array(
							'post_type' => torro()->post_types()->get_prefix() . 'form',
							'page'      => torro()->admin_pages()->get_prefix() . 'edit_submission',
							'id'        => $submission->id,
						),
						admin_url( 'edit.php' )
					);
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
			'allelements' => array(
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

					$output = '<table style="width:100%;border-spacing:0; font-family: Arial, Helvetica, sans-serif;">';

					$i = 0;

					foreach ( $element_columns as $element_id => $data ) {
						$bg_color = ( $i % 2 ) === 1 ? '#ffffff' : '#f2f2f2';

						$values = isset( $element_values[ $element_id ] ) ? $element_values[ $element_id ] : array();

						$column_values = call_user_func( $data['callback'], $values );

						foreach ( $data['columns'] as $slug => $label ) {
							$output .= '<tr style="background-color:' . $bg_color . '"">';
							$output .= '<th scope="row" style="text-align:left;vertical-align: top; width:25%; padding: 10px;">' . esc_html( $label ) . '</th>';
							$output .= '<td style="padding: 10px;">' . wp_kses_post( $column_values[ $slug ] ) . '</td>';
							$output .= '</tr>';
						}

						$i++;
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
	 * Gets all the dynamic template tags for a form, consisting of the form's element value tags.
	 *
	 * @since 1.0.0
	 *
	 * @param Form $form        Form for which to get the dynamic template tags.
	 * @param bool $back_compat Optional. Whether to also include back-compat keys for Torro Forms before 1.0.0-beta.9. Default false.
	 * @return array Dynamic tags as `$slug => $data` pairs.
	 */
	protected function get_dynamic_template_tags( $form, $back_compat = false ) {
		$tags = array();

		foreach ( $form->get_elements() as $element ) {
			$element_type = $element->get_element_type();
			if ( ! $element_type ) {
				continue;
			}

			$tags[ 'value_element_' . $element->id ] = array(
				'group'       => 'submission',
				/* translators: %s: element label */
				'label'       => sprintf( __( 'Value for &#8220;%s&#8221;', 'torro-forms' ), $element->label ),
				/* translators: %s: element label */
				'description' => sprintf( __( 'Inserts the submission value for the element &#8220;%s&#8221;.', 'torro-forms' ), $element->label ),
				'callback'    => function( $form, $submission ) use ( $element, $element_type ) {
					$element_values = $submission->get_element_values_data();
					if ( ! isset( $element_values[ $element->id ] ) ) {
						return '';
					}

					add_filter( "{$this->module->manager()->get_prefix()}use_single_export_column_for_choices", '__return_true' );
					$export_values = $element_type->format_values_for_export( $element_values[ $element->id ], $element, 'html' );
					remove_filter( "{$this->module->manager()->get_prefix()}use_single_export_column_for_choices", '__return_true' );

					if ( ! isset( $export_values[ 'element_' . $element->id . '__main' ] ) ) {
						if ( count( $export_values ) !== 1 ) {
							return '';
						}

						return array_pop( $export_values );
					}

					return $export_values[ 'element_' . $element->id . '__main' ];
				},
			);

			// Add email support to text fields with input_type 'email_address'.
			if ( is_a( $element_type, Textfield::class ) ) {
				$settings = $element_type->get_settings( $element );
				if ( ! empty( $settings['input_type'] ) && 'email_address' === $settings['input_type'] ) {
					$tags[ 'value_element_' . $element->id ]['email_support'] = true;
				}
			}

			if ( $back_compat ) {
				$tags[ $element->label . ':' . $element->id ] = $tags[ 'value_element_' . $element->id ];
			}
		}

		return $tags;
	}

	/**
	 * Wraps the message in valid presentational HTML markup.
	 *
	 * @since 1.0.0
	 *
	 * @param string $message HTML message to wrap.
	 * @param string $title   Optional. String to use in the title tag. Default empty string for no title.
	 * @return string Wrapped HTML message.
	 */
	protected function wrap_message( $message, $title = '' ) {
		$before  = '<!DOCTYPE html>';
		$before .= '<html>';
		$before .= '<head>';
		$before .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
		if ( ! empty( $title ) ) {
			$before .= '<title>' . esc_html( $title ) . '</title>';
		}
		$before .= '</head>';
		$before .= '<body>';

		$after  = '</body>';
		$after .= '</html>';

		return $before . $message . $after;
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
	 * Stores an error object as the internal PHPMailer error.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Error $error Error object.
	 */
	public function store_phpmailer_error( $error ) {
		$this->phpmailer_error = $error;
	}

	/**
	 * Registers all assets the submodule provides.
	 *
	 * @since 1.0.0
	 *
	 * @param Assets $assets The plugin assets instance.
	 */
	public function register_assets( $assets ) {
		$template_tag_template  = '<li class="template-tag template-tag-%slug%">';
		$template_tag_template .= '<button type="button" class="template-tag-button" data-tag="%slug%">%label%</button>';
		$template_tag_template .= '</li>';

		$template_tag_group_template  = '<li class="template-tag-list-group template-tag-list-group-%slug%">';
		$template_tag_group_template .= '<span>%label%</span>';
		$template_tag_group_template .= '<ul></ul>';
		$template_tag_group_template .= '</li>';

		$assets->register_script(
			'admin-email-notifications',
			'assets/dist/js/admin-email-notifications.js',
			array(
				'deps'          => array( 'jquery', 'torro-template-tag-fields', 'torro-admin-form-builder' ),
				'in_footer'     => true,
				'localize_name' => 'torroEmailNotifications',
				'localize_data' => array(
					'templateTagGroupTemplate' => $template_tag_group_template,
					'templateTagTemplate'      => $template_tag_template,
					'templateTagSlug'          => 'value_element_%element_id%',
					'templateTagGroup'         => 'submission',
					'templateTagGroupLabel'    => _x( 'Submission', 'template tag group', 'torro-forms' ),
					/* translators: %s: element label */
					'templateTagLabel'         => sprintf( __( 'Value for &#8220;%s&#8221;', 'torro-forms' ), '%element_label%' ),
					/* translators: %s: element label */
					'templateTagDescription'   => sprintf( __( 'Inserts the submission value for the element &#8220;%s&#8221;.', 'torro-forms' ), '%element_label%' ),
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
		$assets->enqueue_script( 'admin-email-notifications' );
	}
}
