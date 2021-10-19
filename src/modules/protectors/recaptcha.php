<?php
/**
 * Google reCAPTCHA protector class
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
 * Class for a protector using Google's reCAPTCHA.
 *
 * @since 1.0.0
 */
class reCAPTCHA extends Protector { // @codingStandardsIgnoreLine

	/**
	 * Internal flag for whether the reCAPTCHA script has been hooked in.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected $script_hooked = false;

	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.0.0
	 */
	protected function bootstrap() {
		$this->slug  = 'recaptcha';
		$this->title = __( 'reCAPTCHA', 'torro-forms' );
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
		if ( empty( $_POST['g-recaptcha-response'] ) ) { // phpcs:ignore WordPress.Security
			return new WP_Error( 'missing_recaptcha', __( 'Missing reCAPTCHA response. Please check the reCAPTCHA checkbox to verify you are human.', 'torro-forms' ) );
		}

		$verification = $this->verify_response_input( $_POST['g-recaptcha-response'] ); // phpcs:ignore WordPress.Security

		try {
			$verification = json_decode( $verification, true );
		} catch ( Exception $e ) {
			return new WP_Error( 'cannot_process_recaptcha', __( 'An unknown error occurred processing the reCAPTCHA response.', 'torro-forms' ) );
		}

		if ( ! is_array( $verification ) ) {
			return new WP_Error( 'cannot_process_recaptcha', __( 'An unknown error occurred processing the reCAPTCHA response.', 'torro-forms' ) );
		}

		if ( empty( $verification['success'] ) ) {
			$error_code = ! empty( $verification['error-codes'] ) ? $verification['error-codes'][0] : '';
			switch ( $error_code ) {
				case 'missing-input-secret':
					return new WP_Error( 'missing_recaptcha_secret', __( 'Internal error: The reCAPTCHA secret is missing. Please contact an administrator.', 'torro-forms' ) );
				case 'invalid-input-secret':
					return new WP_Error( 'invalid_recaptcha_secret', __( 'Internal error: The reCAPTCHA secret is invalid or malformed. Please contact an administrator.', 'torro-forms' ) );
				case 'missing-input-response':
					return new WP_Error( 'missing_recaptcha_response', __( 'Internal error: The reCAPTCHA response is missing. Please contact an administrator.', 'torro-forms' ) );
				case 'invalid-input-response':
					return new WP_Error( 'invalid_recaptcha_response', __( 'Internal error: The reCAPTCHA response is invalid or malformed. Please contact an administrator.', 'torro-forms' ) );
				default:
					return new WP_Error( 'unknown_recaptcha_error', __( 'Internal error: An unknown reCAPTCHA error occurred. Please contact an administrator.', 'torro-forms' ) );
			}
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
		if ( ! $this->is_configured() ) {
			?>
			<div class="torro-notice torro-error-notice">
				<p><?php esc_html_e( 'You should actually be able to see a reCAPTCHA field here, but it is not correctly configured. Please contact an administrator.', 'torro-forms' ); ?></p>
			</div>
			<?php
			return;
		}

		$key   = $this->get_option( 'site_key' );
		$type  = $this->get_form_option( $form->id, 'type', 'image' );
		$size  = $this->get_form_option( $form->id, 'size', 'normal' );
		$theme = $this->get_form_option( $form->id, 'theme', 'light' );

		?>
		<div class="torro-recaptcha torro-element-wrap">
			<div id="<?php echo esc_attr( 'recaptcha-placeholder-' . $form->id ); ?>" class="torro-recaptcha-placeholder" data-form-id="<?php echo absint( $form->id ); ?>" data-sitekey="<?php echo esc_attr( $key ); ?>" data-type="<?php echo esc_attr( $type ); ?>" data-size="<?php echo esc_attr( $size ); ?>" data-theme="<?php echo esc_attr( $theme ); ?>"></div>
		</div>
		<?php

		if ( ! $this->script_hooked ) {
			$this->script_hooked = true;
			add_action( 'wp_footer', array( $this, 'print_script' ), 10, 0 );
		}
	}

	/**
	 * Prints the reCAPTCHA script.
	 *
	 * This method must only be called once per request.
	 *
	 * @since 1.0.0
	 */
	public function print_script() {
		$locale = str_replace( '_', '-', get_locale() );

		// List of reCAPTCHA locales that need to have the format 'xx-XX' (others have format 'xx').
		$special_locales = array(
			'zh-HK',
			'zh-CN',
			'zh-TW',
			'en-GB',
			'fr-CA',
			'de-AT',
			'de-CH',
			'pt-BR',
			'pt-PT',
			'es-419',
		);

		if ( ! in_array( $locale, $special_locales, true ) ) {
			$locale = substr( $locale, 0, 2 );
		}

		$callback_name = 'torroReCAPTCHAWidgetsInit';

		$recaptcha_script_url = add_query_arg(
			array(
				'onload' => $callback_name,
				'render' => 'explicit',
				'hl'     => $locale,
			),
			'https://www.google.com/recaptcha/api.js'
		);

		// @codingStandardsIgnoreStart
		?>
		<script type="text/javascript">
			var <?php echo esc_js( $callback_name ); ?> = function() {
				var captchaTags = document.getElementsByClassName( 'torro-recaptcha-placeholder' );
				var captchaTag, captchaWidgetId, captchaForm, i;

				for ( i = 0; i < captchaTags.length; i++ ) {
					captchaTag = captchaTags[ i ];

					captchaWidgetId = window.grecaptcha.render( captchaTag.getAttribute( 'id' ), {
						sitekey: captchaTag.getAttribute( 'data-sitekey' ),
						type: captchaTag.getAttribute( 'data-type' ),
						size: captchaTag.getAttribute( 'data-size' ),
						theme: captchaTag.getAttribute( 'data-theme' )
					});
				}
			};
		</script>
		<script src="<?php echo esc_url( $recaptcha_script_url ); ?>" async defer></script>
		<?php
		// @codingStandardsIgnoreEnd
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

		$meta_fields['enabled'] = array(
			'type'         => 'checkbox',
			'label'        => _x( 'Add captcha at the end of the form.', 'protector', 'torro-forms' ),
			'visual_label' => _x( 'Google reCAPTCHA', 'protector', 'torro-forms' ),
		);

		if ( ! $this->is_configured() ) {
			$meta_fields['enabled']['description'] = sprintf( __( 'reCaptcha is not configured properly. Please add a site key & secret to the <a href="%s">recaptcha settings page</a>.', 'torro-forms' ), admin_url( 'edit.php?post_type=torro_form&page=torro_form_settings&tab=torro_module_protectors' ) );
		}

		$meta_fields['type'] = array(
			'type'    => 'select',
			'label'   => _x( 'Type', 'reCAPTCHA', 'torro-forms' ),
			'choices' => array(
				'image' => _x( 'Image', 'reCAPTCHA type', 'torro-forms' ),
				'audio' => _x( 'Audio', 'reCAPTCHA type', 'torro-forms' ),
			),
		);

		$meta_fields['size'] = array(
			'type'    => 'select',
			'label'   => _x( 'Size', 'reCAPTCHA', 'torro-forms' ),
			'choices' => array(
				'normal'  => _x( 'Normal', 'reCAPTCHA size', 'torro-forms' ),
				'compact' => _x( 'Compact', 'reCAPTCHA size', 'torro-forms' ),
			),
		);

		$meta_fields['theme'] = array(
			'type'    => 'select',
			'label'   => _x( 'Theme', 'reCAPTCHA', 'torro-forms' ),
			'choices' => array(
				'light' => _x( 'Light', 'reCAPTCHA theme', 'torro-forms' ),
				'dark'  => _x( 'Dark', 'reCAPTCHA theme', 'torro-forms' ),
			),
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

		$settings_sections['credentials'] = array(
			'title' => _x( 'Credentials', 'reCAPTCHA', 'torro-forms' ),
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

		$settings_fields['site_key'] = array(
			'section'       => 'credentials',
			'type'          => 'text',
			'label'         => _x( 'Site Key', 'reCAPTCHA', 'torro-forms' ),
			/* translators: %s: URL to Google reCAPTCHA console */
			'description'   => sprintf( __( 'The public site key of your website for Google reCAPTCHA. You can get one <a href="%s" target="_blank">here</a>.', 'torro-forms' ), 'https://www.google.com/recaptcha/admin' ),
			'input_classes' => array( 'regular-text' ),
		);

		$settings_fields['secret_key'] = array(
			'section'       => 'credentials',
			'type'          => 'text',
			'label'         => _x( 'Secret', 'reCAPTCHA', 'torro-forms' ),
			/* translators: %s: URL to Google reCAPTCHA console */
			'description'   => sprintf( __( 'The secret key of your website for Google reCAPTCHA. You can get one <a href="%s" target="_blank">here</a>.', 'torro-forms' ), 'https://www.google.com/recaptcha/admin' ),
			'input_classes' => array( 'regular-text' ),
		);

		return $settings_fields;
	}

	/**
	 * Verifies a reCAPTCHA response with Google's API.
	 *
	 * @since 1.0.0
	 *
	 * @param string $response reCAPTCHA response input.
	 * @return string Raw response as a JSON-formatted string.
	 */
	protected function verify_response_input( $response ) {
		$peer_key = version_compare( phpversion(), '5.6.0', '<' ) ? 'CN_name' : 'peer_name';

		$options = array(
			'http' => array(
				'header'      => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'      => 'POST',
				'content'     => http_build_query(
					array(
						'secret'   => $this->get_option( 'secret_key' ),
						'response' => $response,
					),
					'',
					'&'
				),
				'verify_peer' => true,
				$peer_key     => 'www.google.com',
			),
		);

		$context = stream_context_create( $options );

		return file_get_contents( 'https://www.google.com/recaptcha/api/siteverify', false, $context ); // @codingStandardsIgnoreLine
	}

	/**
	 * Performs a basic check whether reCAPTCHA is configured with a public key and secret key.
	 *
	 * It does not check whether these are actually correct though.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if reCAPTCHA is configured, false otherwise.
	 */
	protected function is_configured() {
		$options = $this->get_options();

		return ! empty( $options['site_key'] ) && ! empty( $options['secret_key'] );
	}
}
