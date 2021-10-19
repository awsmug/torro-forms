<?php
/**
 * Form frontend output handler class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Forms;

use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use awsmug\Torro_Forms\DB_Objects\Containers\Container;
use awsmug\Torro_Forms\Error;

/**
 * Class for handling the form frontend output.
 *
 * @since 1.0.0
 */
class Form_Frontend_Output_Handler {

	/**
	 * Form manager instance.
	 *
	 * @since 1.0.0
	 * @var Form_Manager
	 */
	protected $form_manager;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Form_Manager $form_manager Form manager instance.
	 */
	public function __construct( $form_manager ) {
		$this->form_manager = $form_manager;
	}

	/**
	 * Appends the content for a form if conditions are met.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content Post content.
	 * @return string Post content including form content, if the current post is a form.
	 */
	public function maybe_get_form_content( $content ) {
		$form = $this->form_manager->get( get_the_ID() );
		if ( ! $form ) {
			return $content;
		}

		if ( post_password_required() ) {
			return $content;
		}

		$submission = null;
		if ( isset( $_GET['torro_submission_id'] ) ) { // WPCS: CSRF OK.
			$submission = $this->form_manager->get_child_manager( 'submissions' )->get( absint( $_GET['torro_submission_id'] ) ); // WPCS: CSRF OK.
			if ( $submission && $submission->form_id !== $form->id ) {
				$submission = null;
			}
		}

		ob_start();
		$this->render_form_content( $form, $submission );
		return ob_get_clean() . $content;
	}

	/**
	 * Enqueues the frontend stylesheet if necessary.
	 *
	 * @since 1.0.0
	 */
	public function maybe_enqueue_frontend_assets() {
		$settings = $this->form_manager->options()->get( 'general_settings', array() );
		$load_css = isset( $settings['frontend_css'] ) ? (bool) $settings['frontend_css'] : true;

		/**
		 * Filters whether to enqueue the plugin's bundled CSS file for forms in the frontend.
		 *
		 * This filter can be used to more granularly disable loading the CSS file when necessary.
		 * By default it is enqueued on every page unless the setting is set to not enqueuing it.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $load_css Whether the frontend CSS file should be enqueued. Default depends on the setting.
		 */
		$load_css = apply_filters( "{$this->form_manager->get_prefix()}load_frontend_css", $load_css );

		if ( $load_css ) {
			$this->form_manager->assets()->enqueue_style( 'frontend' );
		}

		/**
		 * Fires when form assets should be enqueued in the frontend.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $load_css Whether CSS files should be enqueued or not.
		 */
		do_action( "{$this->form_manager->get_prefix()}enqueue_form_frontend_assets", $load_css );
	}

	/**
	 * Handles the form shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts {
	 *     Array of shortcode attributes.
	 *
	 *     @type int    $id            Form ID. This must always be present.
	 *     @type string $show          How to display the form. Either 'direct' or 'iframe'. Default 'direct'.
	 *     @type string $iframe_width  If $show is set to 'iframe', this indicates the iframe width. Default '100%'.
	 *     @type string $iframe_height If $show is set to 'iframe', this indicates the iframe height. Default '100%'.
	 * }
	 *
	 * @return string Shortcode content to show.
	 */
	public function get_shortcode_content( $atts ) {
		$atts = shortcode_atts(
			array(
				'id'            => '',
				'show'          => 'direct',
				'iframe_width'  => '100%',
				'iframe_height' => '100%',
			),
			$atts
		);

		$atts['id'] = absint( $atts['id'] );

		if ( empty( $atts['id'] ) ) {
			return __( 'Shortcode is missing a form ID!', 'torro-forms' );
		}

		$form = $this->form_manager->get( $atts['id'] );
		if ( ! $form ) {
			return __( 'Shortcode is using an invalid form ID!', 'torro-forms' );
		}

		if ( 'iframe' === $atts['show'] ) {
			$url = get_permalink( $form->id );
			if ( isset( $_GET['torro_submission_id'] ) ) { // WPCS: CSRF OK.
				$url = add_query_arg( 'torro_submission_id', absint( $_GET['torro_submission_id'] ), $url ); // WPCS: CSRF OK.
			}

			return '<iframe src="' . $url . '" style="width:' . esc_attr( $atts['iframe_width'] ) . ';height:' . esc_attr( $atts['iframe_height'] ) . ';"></iframe>';
		}

		$submission = null;
		if ( isset( $_GET['torro_submission_id'] ) ) { // WPCS: CSRF OK.
			$submission = $this->form_manager->get_child_manager( 'submissions' )->get( absint( $_GET['torro_submission_id'] ) ); // WPCS: CSRF OK.
			if ( $submission->form_id !== $form->id ) {
				$submission = null;
			}
		}

		ob_start();
		$this->render_form_content( $form, $submission );
		return ob_get_clean();
	}

	/**
	 * Handles the deprecated form shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts {
	 *     Array of shortcode attributes.
	 *
	 *     @type int    $id            Form ID. This must always be present.
	 *     @type string $show          How to display the form. Either 'direct' or 'iframe'. Default 'direct'.
	 *     @type string $iframe_width  If $show is set to 'iframe', this indicates the iframe width. Default '100%'.
	 *     @type string $iframe_height If $show is set to 'iframe', this indicates the iframe height. Default '100%'.
	 * }
	 *
	 * @return string Shortcode content to show.
	 */
	public function get_deprecated_shortcode_content( $atts ) {
		$this->form_manager->error_handler()->deprecated_shortcode( 'form', '1.0.0-beta.9', "{$this->form_manager->get_prefix()}form" );

		return $this->get_shortcode_content( $atts );
	}

	/**
	 * Renders the content for a given form.
	 *
	 * @since 1.0.0
	 *
	 * @param Form            $form       Form object.
	 * @param Submission|null $submission Optional. Submission object, or null if none available. Default null.
	 */
	protected function render_form_content( $form, $submission = null ) {
		$prefix = $this->form_manager->get_prefix();

		/**
		 * Filters whether a user can access a specific form, and optionally submission.
		 *
		 * @since 1.0.0
		 *
		 * @param bool|Error      $can_access_form Whether a user can access the form. Can be an error object to show a specific message to the user.
		 * @param Form            $form            Form object.
		 * @param Submission|null $submission      Submission object, or null if no submission is set.
		 */
		$can_access_form = apply_filters( "{$prefix}can_access_form", true, $form, $submission );

		if ( is_wp_error( $can_access_form ) ) {
			$this->print_notice( $can_access_form->get_error_message() );
			return;
		}

		if ( ! $can_access_form ) {
			$message = $submission ? __( 'You are not allowed to access this form submission.', 'torro-forms' ) : __( 'You are not allowed to access this form.', 'torro-forms' );
			$this->print_notice( $message );
			return;
		}

		if ( $submission && 'completed' === $submission->status ) {
			/**
			 * Filters the success message to display once a form submission has been completed.
			 *
			 * @since 1.0.0
			 *
			 * @param string $success_message Success message. Default 'Thank you for submitting!'.
			 * @param int    $form_id         Form ID.
			 */
			$success_message = apply_filters( "{$prefix}form_submission_success_message", __( 'Thank you for submitting!', 'torro-forms' ), $form->id );

			$this->print_notice( $success_message, 'success' );
			return;
		}

		$container = $this->get_current_container( $form, $submission );
		if ( ! $container ) {
			$this->print_notice( __( 'No container exists for this form.', 'torro-forms' ), 'error' );
			return;
		}

		$this->maybe_print_form_error( $form );

		if ( $submission ) {
			$this->maybe_print_submission_errors( $submission );
		}

		$template_data = $form->to_json( false );

		$template_data['hidden_fields']  = '<input type="hidden" name="torro_submission[nonce]" value="' . wp_create_nonce( $this->get_nonce_action( $form, $submission ) ) . '">';
		$template_data['hidden_fields'] .= '<input type="hidden" name="torro_submission[form_id]" value="' . esc_attr( $form->id ) . '">';
		if ( $submission ) {
			$template_data['hidden_fields'] .= '<input type="hidden" name="torro_submission[id]" value="' . esc_attr( $submission->id ) . '">';
		}
		if ( ! is_archive() && in_the_loop() && (int) get_the_ID() !== $form->id ) {
			$template_data['hidden_fields'] .= '<input type="hidden" name="torro_submission[original_form_id]" value="' . esc_attr( get_the_ID() ) . '">';
		}

		/**
		 * Filters the CSS class to use for every button for a form in the frontend.
		 *
		 * @since 1.0.0
		 *
		 * @param string $button_class Button CSS class. Default 'torro-button'.
		 */
		$button_class = apply_filters( "{$prefix}form_button_class", 'torro-button' );

		$template_data['navigation'] = array();
		if ( $this->has_next_container( $form, $submission ) ) {
			/**
			 * Filters the label for the Next button for a form in the frontend.
			 *
			 * @since 1.0.0
			 *
			 * @param string $next_button_label Next button label. Default 'Next Step'.
			 * @param int    $form_id           Form ID.
			 */
			$next_button_label = apply_filters( "{$prefix}form_button_next_step_label", _x( 'Next Step', 'button label', 'torro-forms' ), $form->id );

			$template_data['navigation']['next_button'] = array(
				'label' => $next_button_label,
				'attrs' => array(
					'type'  => 'submit',
					'name'  => 'torro_submission[action]',
					'value' => 'next',
					'class' => $button_class,
				),
			);
		} else {
			/**
			 * Filters the label for the Submit button for a form in the frontend.
			 *
			 * @since 1.0.0
			 *
			 * @param string $submit_button_label Submit button label. Default 'Submit'.
			 * @param int    $form_id             Form ID.
			 */
			$submit_button_label = apply_filters( "{$prefix}form_button_submit_label", _x( 'Submit', 'button label', 'torro-forms' ), $form->id );

			/**
			 * Filters the CSS class to use for a primary button for a form in the frontend.
			 *
			 * @since 1.0.0
			 *
			 * @param string $button_primary_class Primary button CSS class. Default 'torro-button-primary'.
			 */
			$button_primary_class = apply_filters( "{$prefix}form_button_primary_class", 'torro-button-primary' );

			$submit_button_before = '';
			if ( has_action( "{$prefix}form_submit_button_before" ) ) {
				ob_start();

				/**
				 * Allows to print additional content before the Submit button for a form in the frontend.
				 *
				 * @since 1.0.0
				 *
				 * @param int $form_id Form ID.
				 */
				do_action( "{$prefix}form_submit_button_before", $form->id );

				$submit_button_before = ob_get_clean();
			}

			$submit_button_after = '';
			if ( has_action( "{$prefix}form_submit_button_after" ) ) {
				ob_start();

				/**
				 * Allows to print additional content after the Submit button for a form in the frontend.
				 *
				 * @since 1.0.0
				 *
				 * @param int $form_id Form ID.
				 */
				do_action( "{$prefix}form_submit_button_after", $form->id );

				$submit_button_after = ob_get_clean();
			}

			$template_data['navigation']['submit_button'] = array(
				'label'  => $submit_button_label,
				'attrs'  => array(
					'type'  => 'submit',
					'name'  => 'torro_submission[action]',
					'value' => 'submit',
					'class' => $button_class . ' ' . $button_primary_class,
				),
				'before' => $submit_button_before,
				'after'  => $submit_button_after,
			);
		}
		if ( $this->has_previous_container( $form, $submission ) ) {
			/**
			 * Filters the label for the Previous button for a form in the frontend.
			 *
			 * @since 1.0.0
			 *
			 * @param string $prev_button_label Previous button label. Default 'Previous Step'.
			 * @param int    $form_id           Form ID.
			 */
			$prev_button_label = apply_filters( "{$prefix}form_button_prev_step_label", _x( 'Previous Step', 'button label', 'torro-forms' ), $form->id );

			$template_data['navigation']['prev_button'] = array(
				'label' => $prev_button_label,
				'attrs' => array(
					'type'  => 'submit',
					'name'  => 'torro_submission[action]',
					'value' => 'prev',
					'class' => $button_class,
				),
			);
		}

		$template_data['current_container'] = $container->to_json( false );

		$container_collection = $form->get_containers(
			array(
				'number'        => 2,
				'no_found_rows' => true,
			)
		);

		$show_container_title = $container_collection->get_total() > 1;

		/**
		 * Filters whether the container title should be displayed on the frontend.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $show_container_title Whether to show the title. Default is true if the current form has multiple containers,
		 *                                   or false otherwise.
		 * @param int  $form_id              Form ID.
		 * @param int  $container_id         Container ID.
		 */
		if ( ! apply_filters( "{$prefix}form_container_show_title", $show_container_title, $form->id, $container->id ) ) {
			$template_data['current_container']['label'] = '';
		}

		$template_data['current_container']['elements'] = array();
		foreach ( $container->get_elements() as $element ) {
			$template_data['current_container']['elements'][] = $element->to_json( false, $submission );
		}

		$this->form_manager->template()->get_partial( 'form', $template_data );
	}

	/**
	 * Gets the current container for a given form and submission.
	 *
	 * @since 1.0.0
	 *
	 * @param Form            $form       Form object.
	 * @param Submission|null $submission Optional. Submission object, or null if none available. Default null.
	 * @return Container|null Container object, or null on failure.
	 */
	protected function get_current_container( $form, $submission = null ) {
		if ( $submission ) {
			return $submission->get_current_container();
		}

		$container_collection = $form->get_containers(
			array(
				'number'        => 1,
				'orderby'       => array(
					'sort' => 'ASC',
				),
				'no_found_rows' => true,
			)
		);

		if ( 1 > count( $container_collection ) ) {
			return null;
		}

		return $container_collection[0];
	}

	/**
	 * Checks whether there is a next container.
	 *
	 * @since 1.0.0
	 *
	 * @param Form            $form       Form object.
	 * @param Submission|null $submission Submission object, or null if no submission is set.
	 * @return bool True if there is a next container, false otherwise.
	 */
	protected function has_next_container( $form, $submission = null ) {
		if ( $submission ) {
			$next_container = $submission->get_next_container();
			return null !== $next_container;
		}

		$containers = $form->get_containers();
		return 1 < count( $containers );
	}

	/**
	 * Checks whether there is a previous container.
	 *
	 * @since 1.0.0
	 *
	 * @param Form            $form       Form object.
	 * @param Submission|null $submission Submission object, or null if no submission is set.
	 * @return bool True if there is a previous container, false otherwise.
	 */
	protected function has_previous_container( $form, $submission = null ) {
		if ( $submission ) {
			$previous_container = $submission->get_previous_container();
			return null !== $previous_container;
		}

		return false;
	}

	/**
	 * Prints form errors in a notice if necessary.
	 *
	 * @since 1.0.0
	 *
	 * @param Form $form Form object.
	 */
	protected function maybe_print_form_error( $form ) {
		$key = $this->form_manager->get_prefix() . 'form_errors';

		if ( is_user_logged_in() ) {
			$errors = get_user_meta( get_current_user_id(), $key, true );
			if ( is_array( $errors ) && isset( $errors[ $form->id ] ) ) {
				$this->print_notice( $errors[ $form->id ], 'error' );

				if ( count( $errors ) === 1 ) {
					delete_user_meta( get_current_user_id(), $key );
				} else {
					unset( $errors[ $form->id ] );
					update_user_meta( get_current_user_id(), $key, $errors );
				}
			}
			return;
		}

		if ( isset( $_SESSION ) && isset( $_SESSION[ $key ] ) && isset( $_SESSION[ $key ][ $form->id ] ) ) {
			$this->print_notice( $_SESSION[ $key ][ $form->id ], 'error', true );

			if ( count( $_SESSION[ $key ] ) === 1 ) {
				unset( $_SESSION[ $key ] );
			} else {
				unset( $_SESSION[ $key ][ $form->id ] );
			}
		}
	}

	/**
	 * Prints submission errors in a notice if necessary.
	 *
	 * @since 1.0.0
	 *
	 * @param Submission $submission Submission object.
	 */
	protected function maybe_print_submission_errors( $submission ) {
		if ( $submission->has_errors( 0 ) ) {
			$global_errors = $submission->get_errors( 0 );
			if ( 1 === count( $global_errors ) ) {
				$error_key = key( $global_errors );
				$this->print_notice( $global_errors[ $error_key ], 'error' );
			} else {
				?>
				<div class="<?php echo esc_attr( $this->get_notice_class( 'error' ) ); ?>">
					<p><?php esc_html_e( 'Some errors occurred while trying to submit the form:', 'torro-forms' ); ?></p>
					<ul>
						<?php foreach ( $global_errors as $error_message ) : ?>
							<li><?php echo wp_kses_data( $error_message ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
				<?php
			}
		} elseif ( $submission->has_errors() ) {
			$this->print_notice( __( 'Some errors occurred while trying to submit the form.', 'torro-forms' ), 'error' );
		}
	}

	/**
	 * Prints a notice with a message to the user.
	 *
	 * @since 1.0.0
	 *
	 * @param string $message Message to show.
	 * @param string $type    Optional. Notice type. Either 'success', 'info', 'warning' or 'error'. Default 'warning'.
	 * @param bool   $escape  Optional. Whether to escape the message. This should be set to true if the source of the
	 *                        message is not a 100% trusted. Default false.
	 */
	protected function print_notice( $message, $type = 'warning', $escape = false ) {
		if ( $escape ) {
			$message = esc_html( $message );
		}

		?>
		<div class="<?php echo esc_attr( $this->get_notice_class( $type ) ); ?>">
			<p><?php echo wp_kses_data( $message ); ?></p>
		</div>
		<?php
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
		return apply_filters( "{$this->form_manager->get_prefix()}form_notice_class", "torro-notice torro-{$type}-notice", $type );
	}

	/**
	 * Returns the name of the nonce action to check.
	 *
	 * @since 1.0.0
	 *
	 * @param Form            $form       Form object.
	 * @param Submission|null $submission Optional. Submission object, or null if none available. Default null.
	 * @return string Nonce action name.
	 */
	protected function get_nonce_action( $form, $submission = null ) {
		if ( $submission && ! empty( $submission->id ) ) {
			return $this->form_manager->get_prefix() . 'form_' . $form->id . '_submission_' . $submission->id;
		}

		return $this->form_manager->get_prefix() . 'form_' . $form->id;
	}
}
