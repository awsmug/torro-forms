<?php
/**
 * Restrict form to solving a reCAPTCHA
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Restrictions
 * @version 1.0.0alpha1
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 awesome.ug (support@awesome.ug)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
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

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	protected $recaptcha_errors = array();

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();
	}

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

		add_action( 'torro_formbuilder_save', array( $this, 'save' ), 10, 1 );
		add_action( 'admin_notices', array( $this, 'check_settings' ), 1 );

		add_action( 'torro_form_send_button_before', array( $this, 'draw_placeholder_element' ), 10, 1 );
		add_filter( 'torro_response_status', array( $this, 'check_recaptcha_submission' ), 10, 5 );

		// compatibility with Contact Form 7
		remove_action( 'wpcf7_enqueue_scripts', 'wpcf7_recaptcha_enqueue_scripts' );
	}

	/**
	 * Checking if reCAPTCHA has been configured
	 */
	public function check_settings() {
		global $post;

		if ( ! torro_is_formbuilder() ) {
			return;
		}

		if ( ! isset( $post->ID ) ) {
			return;
		}

		$form_id = $post->ID;

		if ( $this->is_enabled( $form_id ) && ! $this->is_configured() ) {
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

		$html  = '<div id="form-access-controls-content-recaptcha" class="recaptcha">';

		$html .= '<h4>' . esc_html__( 'Google reCAPTCHA', 'torro-forms' ) . '</h4>';
		$html .= '<table class="form-table">';

		$html .= '<tr>';
		$html .= '<td><label for="recaptcha_enabled">' . esc_html__( 'Enable', 'torro-forms' ) . '</label></td>';
		$html .= '<td><input type="checkbox" id="recaptcha_enabled" name="recaptcha_enabled" value="1" ' . checked( $recaptcha_enabled, true, false ) . '/></td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<td><label for="recaptcha_type">' . esc_html__( 'Type', 'torro-forms' ) . '</label></td>';
		$html .= '<td><select id="recaptcha_type" name="recaptcha_type">';
		$html .= '<option value="image" ' . selected( $recaptcha_type, 'image', false ) . '>' . esc_html__( 'Image', 'torro-forms' ) . '</option>';
		$html .= '<option value="audio" ' . selected( $recaptcha_type, 'audio', false ) . '>' . esc_html__( 'Audio', 'torro-forms' ) . '</option>';
		$html .= '</select></td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<td><label for="recaptcha_size">' . esc_html__( 'Size', 'torro-forms' ) . '</label></td>';
		$html .= '<td><select id="recaptcha_size" name="recaptcha_size">';
		$html .= '<option value="normal" ' . selected( $recaptcha_size, 'normal', false ) . '>' . esc_html__( 'Normal', 'torro-forms' ) . '</option>';
		$html .= '<option value="compact" ' . selected( $recaptcha_size, 'compact', false ) . '>' . esc_html__( 'Compact', 'torro-forms' ) . '</option>';
		$html .= '</select></td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<td><label for="recaptcha_theme">' . esc_html__( 'Theme', 'torro-forms' ) . '</label></td>';
		$html .= '<td><select id="recaptcha_theme" name="recaptcha_theme">';
		$html .= '<option value="light" ' . selected( $recaptcha_theme, 'light', false ) . '>' . esc_html__( 'Light', 'torro-forms' ) . '</option>';
		$html .= '<option value="dark" ' . selected( $recaptcha_theme, 'dark', false ) . '>' . esc_html__( 'Dark', 'torro-forms' ) . '</option>';
		$html .= '</select></td>';
		$html .= '</tr>';
		$html .= '</table>';

		$html .= '</div>';

		return $html;
	}

	/**
	 * Detects whether reCAPTCHA is enabled for a specific form
	 */
	public function is_configured() {
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
	 * @return bool
	 */
	public function is_enabled( $form_id ) {
		if ( ! get_post_meta( $form_id, 'recaptcha_enabled', true ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Actually checks whether the user submitted a valid captcha
	 *
	 * This check is only performed on submitting the form (i.e. last page of the form)
	 */
	public function check_recaptcha_submission( $status, $form_id, $container_id, $is_submit = false ) {
		if ( $this->is_enabled( $form_id ) && $this->is_configured()  && $is_submit ) {
			if ( isset( $_POST['g-recaptcha-response'] ) && ! empty( $_POST['g-recaptcha-response'] ) ) {
				$verification = $this->verify_response( $_POST['g-recaptcha-response'] );
				try {
					$verification = json_decode( $verification, true );
				} catch ( Exception $e ) {
					$this->recaptcha_errors[ $form_id ] = __( 'An unknown error occurred processing the reCAPTCHA response.', 'torro-forms' );
					$status = false;
				}

				if ( is_array( $verification ) && ! $verification['success'] ) {
					if ( isset( $verification['error-codes'] ) && count( $verification['error-codes'] ) > 0 ) {
						switch ( $verification['error-codes'][0] ) {
							case 'missing-input-secret':
								$this->recaptcha_errors[ $form_id ] = __( 'The reCAPTCHA secret is missing.', 'torro-forms' );
								break;
							case 'invalid-input-secret':
								$this->recaptcha_errors[ $form_id ] = __( 'The reCAPTCHA secret is invalid or malformed.', 'torro-forms' );
								break;
							case 'missing-input-response':
								$this->recaptcha_errors[ $form_id ] = __( 'The reCAPTCHA response is missing.', 'torro-forms' );
								break;
							case 'invalid-input-response':
								$this->recaptcha_errors[ $form_id ] = __( 'The reCAPTCHA response is invalid or malformed.', 'torro-forms' );
								break;
							default:
						}
					} else {
						$this->recaptcha_errors[ $form_id ] = __( 'An unknown error occurred processing the reCAPTCHA response.', 'torro-forms' );
					}
					$status = false;
				}
			} else {
				$this->recaptcha_errors[ $form_id ] = __( 'Missing reCAPTCHA response.', 'torro-forms' );
				$status = false;
			}
		}

		return $status;
	}

	/**
	 * Verifies a reCAPTCHA response.
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
	 * Saving data
	 *
	 * @param int $form_id
	 *
	 * @since 1.0.0
	 */
	public static function save( $form_id ) {
		$recaptcha_enabled = isset( $_POST['recaptcha_enabled'] ) ? (bool) $_POST['recaptcha_enabled'] : false;
		$recaptcha_type = isset( $_POST['recaptcha_type'] ) ? esc_html( $_POST['recaptcha_type'] ) : 'image';
		$recaptcha_size = isset( $_POST['recaptcha_size'] ) ? esc_html( $_POST['recaptcha_size'] ) : 'normal';
		$recaptcha_theme = isset( $_POST['recaptcha_theme'] ) ? esc_html( $_POST['recaptcha_theme'] ) : 'light';

		/**
		 * Saving reCAPTCHA settings
		 */
		update_post_meta( $form_id, 'recaptcha_enabled', $recaptcha_enabled );
		update_post_meta( $form_id, 'recaptcha_type', $recaptcha_type );
		update_post_meta( $form_id, 'recaptcha_size', $recaptcha_size );
		update_post_meta( $form_id, 'recaptcha_theme', $recaptcha_theme );
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @since 1.0.0
	 */
	public function frontend_scripts() {
		global $post;

		$form_id = torro()->forms()->get_current_form_id();

		if ( ! $form_id ) {
			if ( ! $post || 'torro-forms' !== $post->post_type ) {
				// no form detected
				return;
			}
			$form_id = $post->ID;
		}

		if ( ! $this->is_enabled( $form_id ) || !$this->is_configured() ) {
			return;
		}

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
	 */
	public function handle_google_recaptcha_script_tag( $tag, $handle, $src ) {
		if ( 'google-recaptcha' == $handle ) {
			$tag = str_replace( '></script>', ' async defer></script>', $tag );
		}

		return $tag;
	}

	/**
	 * Creates the reCAPTCHA placeholder element and optionally prints errors
	 */
	public function draw_placeholder_element( $form_id ) {
		if ( ! $this->is_enabled( $form_id ) ) {
			return;
		}

		$error = '';
		if ( isset( $this->recaptcha_errors[ $form_id ] ) ) {
			$error = $this->recaptcha_errors[ $form_id ];
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

		?>
		<div class="torro-element">
			<div id="recaptcha-placeholder-<?php echo $form_id; ?>" class="recaptcha-placeholder" data-form-id="<?php echo $form_id; ?>" data-type="<?php echo $type; ?>" data-size="<?php echo $size; ?>" data-theme="<?php echo $theme; ?>" style="margin-bottom:20px;"></div>
			<?php if ( ! empty( $error ) ) : ?>
				<ul class="error-messages">
					<li><?php echo $error; ?></li>
				</ul>
			<?php endif; ?>
		</div>
		<?php
	}
}

torro()->form_settings()->register( 'Torro_Form_Setting_Spam_Protection' );
