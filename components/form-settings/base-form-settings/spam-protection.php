<?php
/**
 * Components: Torro_Form_Setting_Spam_Protection class
 *
 * @package TorroForms
 * @subpackage Components
 * @version 1.0.0-beta.7
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Torro_Form_Setting_Spam_Protection extends Torro_Form_Setting {
	/**
	 * Instance
	 *
	 * @var null|Torro_Form_Setting_Spam_Protection
	 * @since 1.0.0
	 */
	private static $instance = null;

	/**
	 * Settings name
	 *
	 * @since 1.0.0
	 */
	protected $settings_name = 'form_settings';

	/**
	 * Minimum time the user have been on site not to be a bot
	 *
	 * @since 1.0.0
	 */
	protected $timetrap_time = 3;

	/**
	 * Number of maximum links for link count
	 *
	 * @since 1.0.0
	 */
	protected $linkcount_number = 3;

	/**
	 * Recaptcha errors
	 *
	 * @since 1.0.0
	 */
	protected $errors = array();

	/**
	 * Whether the script is already enqueued.
	 *
	 * @var bool
	 * @since 1.0.0
	 */
	protected $enqueued = false;

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initializing
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->option_name = $this->title = __( 'Spam Protection', 'torro-forms' );
		$this->name = 'spam_protection';

		$this->settings_fields = array(
			'recaptcha_sitekey'		=> array(
				'title'					=> __( 'Site Key', 'torro-forms' ),
				'description'			=> __( 'The public site key of your website for Google reCAPTCHA. You can get one <a href="http://www.google.com/recaptcha/admin" target="_blank">here</a>.', 'torro-forms' ),
				'type'					=> 'text',
			),
			'recaptcha_secret'		=> array(
				'title'					=> __( 'Secret', 'torro-forms' ),
				'description'			=> __( 'The secret key of your website for Google reCAPTCHA. You can get one <a href="http://www.google.com/recaptcha/admin" target="_blank">here</a>.', 'torro-forms' ),
				'type'					=> 'text',
			),
		);

		/**
		 * Filters the time of the timetrap
		 *
		 * @since 1.0.0
		 *
		 * @param int $timetrap_time Time in seconds
		 * @return int $timetrap_time Time in seconds filtered
		 */
		$this->timetrap_time = apply_filters( 'torro_form_timetrap_time', $this->timetrap_time );

		/**
		 * Filters the number of links which are allowed in a textfield
		 *
		 * @since 1.0.0
		 *
		 * @param int $linkcount_number Time number of links allowed in a textfield
		 * @return int $linkcount_number Time number of links allowed in a textfield
		 */
		$this->linkcount_number = apply_filters( 'torro_form_linkcount_number', $this->linkcount_number );

		add_action( 'admin_notices', array( $this, 'check_settings' ), 1 );

		add_action( 'torro_form_send_button_before', array( $this, 'draw_recaptcha_element' ), 10, 1 );
		add_action( 'torro_form_send_button_before', array( $this, 'draw_honeypot_element' ), 10, 1 );
		add_action( 'torro_form_send_button_before', array( $this, 'draw_timetrap_element' ), 10, 1 );

		add_filter( 'torro_response_status', array( $this, 'check_recaptcha_submission' ), 10, 4 );
		add_filter( 'torro_response_status', array( $this, 'check_honeypot_submission' ), 10, 4 );
		add_filter( 'torro_response_status', array( $this, 'check_timetrap_submission' ), 10, 4 );

		add_filter( 'torro_element_type_validate_input', array( $this, 'check_linkcount_submission' ), 10, 2 );

		// compatibility with Contact Form 7
		remove_action( 'wpcf7_enqueue_scripts', 'wpcf7_recaptcha_enqueue_scripts' );
	}

	/**
	 * Checking if reCAPTCHA has been configured
	 *
	 * @since 1.0.0
	 */
	public function check_settings() {
		global $post;

		if ( ! torro()->is_formbuilder() ) {
			return;
		}

		if ( ! isset( $post->ID ) ) {
			return;
		}

		$form_id = $post->ID;

		if ( $this->is_recaptcha_ebabled( $form_id ) && ! $this->is_recaptcha_configured() ) {
			torro()->admin_notices()->add( 'recaptcha_not_configured', sprintf( __( 'To use reCAPTCHA you have to enter a Sitekey and Secret in your <a href="%s">reCAPTCHA settings</a>.', 'torro-forms' ), admin_url( 'edit.php?post_type=torro_form&page=Torro_Admin&tab=form_settings&section=spam_protection' ) ), 'warning' );
		}
	}


	/**
	 * reCAPTCHA meta box
	 *
	 * @param int $form_id
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	public function option_content( $form_id ) {
		$recaptcha_enabled = get_post_meta( $form_id, 'recaptcha_enabled', true );

		if ( $recaptcha_enabled ) {
			$recaptcha_enabled = true;
		} else {
			$recaptcha_enabled = false;
		}

		$recaptcha_type = get_post_meta( $form_id, 'recaptcha_type', true );
		$recaptcha_size = get_post_meta( $form_id, 'recaptcha_size', true );
		$recaptcha_theme = get_post_meta( $form_id, 'recaptcha_theme', true );

		$html  = '<div id="form-access-controls-content-recaptcha" class="recaptcha torro-form-options">';

		$html .= '<h4>' . esc_html__( 'Google reCAPTCHA', 'torro-forms' ) . '</h4>';

		$html .= '<div class="flex-options" role="group">';
		$html .= '<label for="recaptcha_enabled">' . esc_html__( 'Enable', 'torro-forms' ) . '</label>';
		$html .= '<div>';
		$html .= '<input type="checkbox" id="recaptcha_enabled" name="recaptcha_enabled" value="1" ' . checked( $recaptcha_enabled, true, false ) . ' aria-describedby="enable-recaptcha-desc" />';
		$html .= '<div id="enable-recaptcha-desc">' .esc_html__( 'Enable Google reCAPTCHA.', 'torro-forms' ). '</div>';
		$html .= '</div>';
		$html .= '</div>';

		$html .= '<div class="flex-options" role="group">';
		$html .= '<label for="recaptcha_type">' . esc_html__( 'Type', 'torro-forms' ) . '</label>';
		$html .= '<div><select id="recaptcha_type" name="recaptcha_type">';
		$html .= '<option value="image" ' . selected( $recaptcha_type, 'image', false ) . '>' . esc_html__( 'Image', 'torro-forms' ) . '</option>';
		$html .= '<option value="audio" ' . selected( $recaptcha_type, 'audio', false ) . '>' . esc_html__( 'Audio', 'torro-forms' ) . '</option>';
		$html .= '</select></div>';
		$html .= '</div>';

		$html .= '<div class="flex-options" role="group">';
		$html .= '<label for="recaptcha_size">' . esc_html__( 'Size', 'torro-forms' ) . '</label>';
		$html .= '<div><select id="recaptcha_size" name="recaptcha_size">';
		$html .= '<option value="normal" ' . selected( $recaptcha_size, 'normal', false ) . '>' . esc_html__( 'Normal', 'torro-forms' ) . '</option>';
		$html .= '<option value="compact" ' . selected( $recaptcha_size, 'compact', false ) . '>' . esc_html__( 'Compact', 'torro-forms' ) . '</option>';
		$html .= '</select></div>';
		$html .= '</div>';

		$html .= '<div class="flex-options" role="group">';
		$html .= '<label for="recaptcha_theme">' . esc_html__( 'Theme', 'torro-forms' ) . '</label>';
		$html .= '<div><select id="recaptcha_theme" name="recaptcha_theme">';
		$html .= '<option value="light" ' . selected( $recaptcha_theme, 'light', false ) . '>' . esc_html__( 'Light', 'torro-forms' ) . '</option>';
		$html .= '<option value="dark" ' . selected( $recaptcha_theme, 'dark', false ) . '>' . esc_html__( 'Dark', 'torro-forms' ) . '</option>';
		$html .= '</select></div>';
		$html .= '</div>';

		$honeypot_enabled = get_post_meta( $form_id, 'honeypot_enabled', true );

		if ( $honeypot_enabled ) {
			$honeypot_enabled = true;
		} else {
			$honeypot_enabled = false;
		}

		$html .= '<h4>' . esc_html__( 'Torro Spam-Protection', 'torro-forms' ) . '</h4>';

		$html .= '<div class="flex-options" role="group">';
		$html .= '<label for="honeypot_enabled">' . esc_html__( 'Honeypot', 'torro-forms' ) . '</label>';
		$html .= '<div>';
		$html .= '<input type="checkbox" id="honeypot_enabled" name="honeypot_enabled" value="1" ' . checked( $honeypot_enabled, true, false ) . ' aria-describedby="enable-honeypot-desc" />';
		$html .= '<div id="enable-honeypot-desc">' .esc_html__( 'Enable Honeypot.', 'torro-forms' ). '</div>';
		$html .= '</div>';
		$html .= '</div>';


		$timetrap_enabled = get_post_meta( $form_id, 'timetrap_enabled', true );

		if ( $timetrap_enabled ) {
			$timetrap_enabled = true;
		} else {
			$timetrap_enabled = false;
		}

		$html .= '<div class="flex-options" role="group">';
		$html .= '<label for="timetrap_enabled">' . esc_html__( 'Timetrap', 'torro-forms' ) . '</label>';
		$html .= '<div>';
		$html .= '<input type="checkbox" id="timetrap_enabled" name="timetrap_enabled" value="1" ' . checked( $timetrap_enabled, true, false ) . ' aria-describedby="enable-timetrap-desc" />';
		$html .= '<div id="enable-timetrap-desc">' . sprintf( esc_html__( 'Enable Timetrap. All submissions within %s seconds are spam.', 'torro-forms' ), $this->timetrap_time ). '</div>';
		$html .= '</div>';
		$html .= '</div>';


		$linkcount_enabled = get_post_meta( $form_id, 'linkcount_enabled', true );

		if ( $linkcount_enabled ) {
			$linkcount_enabled = true;
		} else {
			$linkcount_enabled = false;
		}

		$html .= '<div class="flex-options" role="group">';
		$html .= '<label for="linkcount_enabled">' . esc_html__( 'Linkcount', 'torro-forms' ) . '</label>';
		$html .= '<div>';
		$html .= '<input type="checkbox" id="linkcount_enabled" name="linkcount_enabled" value="1" ' . checked( $linkcount_enabled, true, false ) . ' aria-describedby="enable-linkcount-desc" />';
		$html .= '<div id="enable-linkcount-desc">' . sprintf( esc_html__( 'Enable Linkcount. All submissions where text areas having more than %s links are spam.', 'torro-forms' ), $this->linkcount_number ). '</div>';
		$html .= '</div>';
		$html .= '</div>';


		$html .= '</div>';

		return $html;
	}

	/**
	 * Saving option
	 *
	 * @param int $form_id
	 *
	 * @since 1.0.0
	 */
	public function save( $form_id ) {
		$recaptcha_enabled = isset( $_POST['recaptcha_enabled'] ) ? (bool) $_POST['recaptcha_enabled'] : false;
		$recaptcha_type = isset( $_POST['recaptcha_type'] ) ? wp_unslash( $_POST['recaptcha_type'] ) : 'image';
		$recaptcha_size = isset( $_POST['recaptcha_size'] ) ? wp_unslash( $_POST['recaptcha_size'] ) : 'normal';
		$recaptcha_theme = isset( $_POST['recaptcha_theme'] ) ? wp_unslash( $_POST['recaptcha_theme'] ) : 'light';
		$honeypot_enabled = isset( $_POST['honeypot_enabled'] ) ? (bool) $_POST['honeypot_enabled'] : false;
		$timetrap_enabled = isset( $_POST['timetrap_enabled'] ) ? (bool) $_POST['timetrap_enabled'] : false;
		$linkcount_enabled = isset( $_POST['linkcount_enabled'] ) ? (bool) $_POST['linkcount_enabled'] : false;

		/**
		 * Saving settings
		 */
		update_post_meta( $form_id, 'recaptcha_enabled', $recaptcha_enabled );
		update_post_meta( $form_id, 'recaptcha_type', $recaptcha_type );
		update_post_meta( $form_id, 'recaptcha_size', $recaptcha_size );
		update_post_meta( $form_id, 'recaptcha_theme', $recaptcha_theme );
		update_post_meta( $form_id, 'honeypot_enabled', $honeypot_enabled );
		update_post_meta( $form_id, 'timetrap_enabled', $timetrap_enabled );
		update_post_meta( $form_id, 'linkcount_enabled', $linkcount_enabled );
	}

	/**
	 * Detects whether reCAPTCHA is enabled for a specific form
	 *
	 * @return boolean
	 * @since 1.0.0
	 */
	public function is_recaptcha_configured() {
		if ( empty( $this->settings['recaptcha_sitekey'] ) ) {
			return false;
		}

		if ( empty( $this->settings['recaptcha_secret'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Detects if reCAPTCHA is enabled for form
	 *
	 * @return boolean
	 * @since 1.0.0
	 */
	public function is_recaptcha_ebabled( $form_id ) {
		if ( ! get_post_meta( $form_id, 'recaptcha_enabled', true ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Detects if Honeypot is enabled for form
	 *
	 * @return boolean
	 * @since 1.0.0
	 */
	public function is_honeypot_enabled( $form_id ) {
		if ( ! get_post_meta( $form_id, 'honeypot_enabled', true ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Detects if Timetrap is enabled for form
	 *
	 * @return boolean
	 * @since 1.0.0
	 */
	public function is_timetrap_enabled( $form_id ) {
		if ( ! get_post_meta( $form_id, 'timetrap_enabled', true ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Detects if Linkcount is enabled for form
	 *
	 * @return boolean
	 * @since 1.0.0
	 */
	public function is_linkcount_enabled( $form_id ) {
		if ( ! get_post_meta( $form_id, 'linkcount_enabled', true ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Creates the reCAPTCHA placeholder element and optionally prints errors
	 *
	 * @param int $form_id
	 *
	 * @since 1.0.0
	 */
	public function draw_recaptcha_element( $form_id ) {
		if ( ! $this->is_recaptcha_ebabled( $form_id ) || ! $this->is_recaptcha_configured() ) {
			return;
		}

		$error = '';
		if ( isset( $this->errors[ $form_id ] ) ) {
			$error = $this->errors[ $form_id ];
		}

		$type = get_post_meta( $form_id, 'recaptcha_type', true );
		if ( ! $type ) {
			$type = 'image';
		}

		$size = get_post_meta( $form_id, 'recaptcha_size', true );
		if ( ! $size ) {
			$size = 'normal';
		}

		$theme = get_post_meta( $form_id, 'recaptcha_theme', true );
		if ( ! $theme ) {
			$theme = 'light';
		}

		torro()->template( 'recaptcha', array(
			'id'		=> 'recaptcha-placeholder-' . $form_id,
			'form_id'	=> $form_id,
			'type'		=> $type,
			'size'		=> $size,
			'theme'		=> $theme,
			'error'		=> $error,
		) );

		$this->enqueue_recaptcha_script( $form_id );
	}

	/**
	 * Actually checks whether the user submitted a valid captcha
	 *
	 * This check is only performed on submitting the form (i.e. last page of the form)
	 *
	 * @param boolean $status
	 * @param int $form_id
	 * @param int $container_id
	 * @param boolean $is_submit
	 *
	 * @return boolean
	 * @since 1.0.0
	 */
	public function check_recaptcha_submission( $status, $form_id, $container_id, $is_submit = false ) {
		if ( $this->is_recaptcha_ebabled( $form_id ) && $this->is_recaptcha_configured() && $is_submit ) {
			if ( isset( $_POST['g-recaptcha-response'] ) && ! empty( $_POST['g-recaptcha-response'] ) ) {
				$verification = $this->verify_response( $_POST['g-recaptcha-response'] );
				try {
					$verification = json_decode( $verification, true );
				} catch ( Exception $e ) {
					$this->errors[ $form_id ] = __( 'An unknown error occurred processing the reCAPTCHA response.', 'torro-forms' );
					$status = false;
				}

				if ( is_array( $verification ) && ! $verification['success'] ) {
					if ( isset( $verification['error-codes'] ) && count( $verification['error-codes'] ) > 0 ) {
						switch ( $verification['error-codes'][0] ) {
							case 'missing-input-secret':
								$this->errors[ $form_id ] = __( 'The reCAPTCHA secret is missing.', 'torro-forms' );
								break;
							case 'invalid-input-secret':
								$this->errors[ $form_id ] = __( 'The reCAPTCHA secret is invalid or malformed.', 'torro-forms' );
								break;
							case 'missing-input-response':
								$this->errors[ $form_id ] = __( 'The reCAPTCHA response is missing.', 'torro-forms' );
								break;
							case 'invalid-input-response':
								$this->errors[ $form_id ] = __( 'The reCAPTCHA response is invalid or malformed.', 'torro-forms' );
								break;
							default:
						}
					} else {
						$this->errors[ $form_id ] = __( 'An unknown error occurred processing the reCAPTCHA response.', 'torro-forms' );
					}
					$status = false;
				}
			} else {
				$this->errors[ $form_id ] = __( 'Missing reCAPTCHA response.', 'torro-forms' );
				$status = false;
			}
		}

		return $status;
	}

	/**
	 * Creates the honeypot email field
	 *
	 * @param int $form_id
	 *
	 * @since 1.0.0
	 */
	public function draw_honeypot_element( $form_id ) {
		if( ! $this->is_honeypot_enabled( $form_id ) ) {
			return;
		}

		$email = '';
		if( array_key_exists( 'email', $_REQUEST ) && ! empty( $_REQUEST[ 'email' ] ) ) {
			$email = $_REQUEST[ 'email' ];
		}
		$html = '<div class="torro-element torro-element-trap">';
		$html.= '<label for="email">' . esc_attr__( 'If you are a human, do not fill in this field.', 'torro-forms' ) . '</label>';
		$html.= '<input id="email" type="text" name="email" value="' . $email . '" />';
		$html.= '</div>';

		echo $html;
	}

	/**
	 * Actually checks whether the user has filled in the honeypot field
	 *
	 * @param boolean $status
	 * @param int $form_id
	 * @param int $container_id
	 * @param boolean $is_submit
	 *
	 * @return boolean
	 * @since 1.0.0
	 */
	public function check_honeypot_submission( $status, $form_id, $container_id, $is_submit = false ) {
		if ( $this->is_honeypot_enabled( $form_id )  ) {
			if( array_key_exists( 'email', $_REQUEST ) && ! empty( $_REQUEST[ 'email' ] ) ) {
				$this->errors[ $form_id ] = __( 'Go away!', 'torro-forms' );
				return false;
			}
		}
		return true;
	}

	/**
	 * Creates the timetrap time field
	 *
	 * @param int $form_id
	 *
	 * @since 1.0.0
	 */
	public function draw_timetrap_element( $form_id ) {
		if ( ! $this->is_timetrap_enabled( $form_id )  ) {
			return;
		}
		$html = '<input id="timestamp" type="hidden" name="timestamp" value="' . time() . '" />';
		echo $html;
	}

	/**
	 * Actually checks whether the user has filled in the honeypot field
	 *
	 * @param boolean $status
	 * @param int $form_id
	 * @param int $container_id
	 * @param boolean $is_submit
	 *
	 * @return boolean
	 * @since 1.0.0
	 */
	public function check_timetrap_submission( $status, $form_id, $container_id, $is_submit = false ) {
		if ( $this->is_timetrap_enabled( $form_id )  ) {
			if( array_key_exists( 'timestamp', $_REQUEST ) && ! empty( $_REQUEST[ 'timestamp' ] ) && ( time() - (int) $_REQUEST[ 'timestamp' ] <= (int) $this->timetrap_time ) ) {
				$this->errors[ $form_id ] = __( 'Go away!', 'torro-forms' );
				return false;
			}
		}
		return true;
	}

	/**
	 * Checking number of links in textareas
	 *
	 * @since 1.0.0
	 *
	 * @param string|array $input Input of the element
	 * @param Torro_Element $element
	 *
	 * @return string|array $input Filtered input of the element
	 */
	public function check_linkcount_submission( $input, $element ){
		if( 'textarea' !== $element->type ) {
			return $input;
		}

		preg_match_all('@https?://@' , $input, $matches );

		if( count( $matches[ 0 ] ) >  $this->linkcount_number ) {
			return new Torro_Error( 'too_many_links_in_textarea', sprintf( esc_attr__( 'You can not use more than %s links in this field.', 'torro-forms' ), $this->linkcount_number ) );
		}

		return $input;
	}

	/**
	 * Verifies a reCAPTCHA response.
	 *
	 * @param string $captcha_response
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function verify_response( $captcha_response ) {
		$peer_key = version_compare( phpversion(), '5.6.0', '<' ) ? 'CN_name' : 'peer_name';

		$options = array(
			'http'			=> array(
				'header'		=> "Content-type: application/x-www-form-urlencoded\r\n",
				'method'		=> 'POST',
				'content'		=> http_build_query( array(
					'secret'		=> $this->settings['recaptcha_secret'],
					'response'		=> $captcha_response,
				), '', '&' ),
				'verify_peer'	=> true,
				$peer_key		=> 'www.google.com',
			),
		);

		$context = stream_context_create( $options );

		return file_get_contents( 'https://www.google.com/recaptcha/api/siteverify', false, $context );
	}

	/**
	 * Adding scripts for recaptcha
	 *
	 * @param $form_id
	 *
	 * @since 1.0.0
	 */
	public function enqueue_recaptcha_script( $form_id ) {
		if ( ! $this->is_recaptcha_ebabled( $form_id ) || ! $this->is_recaptcha_configured() ) {
			return;
		}

		// reCAPTCHA only works for a single form per page.
		if ( $this->enqueued ) {
			return;
		}

		$this->enqueued = true;

		wp_enqueue_script( 'torro-forms-recaptcha', torro()->get_asset_url( 'frontend-recaptcha', 'js' ), array(), false, true );
		wp_localize_script( 'torro-forms-recaptcha', '_torro_recaptcha_settings', array(
			'sitekey'		=> $this->settings['recaptcha_sitekey'],
		) );

		$locale = str_replace( '_', '-', get_locale() );

		// list of reCAPTCHA locales that need to have the format 'xx-XX' (others have format 'xx')
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

		if ( ! in_array( $locale, $special_locales ) ) {
			$locale = substr( $locale, 0, 2 );
		}

		$recaptcha_script_url = 'https://www.google.com/recaptcha/api.js';
		$recaptcha_script_url = add_query_arg( array(
			'onload'	=> 'torro_reCAPTCHA_widgets_init',
			'render'	=> 'explicit',
			'hl'		=> $locale,
		), $recaptcha_script_url );

		wp_enqueue_script( 'google-recaptcha', $recaptcha_script_url, array( 'torro-forms-recaptcha' ), false, true );

		add_filter( 'script_loader_tag', array( $this, 'handle_google_recaptcha_script_tag' ), 10, 3 );
	}

	/**
	 * Adds 'async' and 'defer' attributes to the reCAPTCHA script tag
	 *
	 * @param string $tag
	 * @param string $handle
	 * @param string $src
	 *
	 * @return string $tag
	 *
	 * @since 1.0.0
	 */
	public function handle_google_recaptcha_script_tag( $tag, $handle, $src ) {
		if ( 'google-recaptcha' == $handle ) {
			$tag = str_replace( '></script>', ' async defer></script>', $tag );
		}

		return $tag;
	}
}

torro()->form_settings()->register( 'Torro_Form_Setting_Spam_Protection' );
