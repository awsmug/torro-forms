<?php
/**
 * Restrict form to solving a reCAPTCHA
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package AwesomeForms/Restrictions
 * @version 1.0.0
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

class AF_Restriction_Recaptcha extends AF_Restriction {

	/**
	 * Constructor
	 */
	public function init() {
		$this->title = __( 'Google reCAPTCHA', 'af-locale' );
		$this->name = 'recaptcha';

		$this->settings_fields = array(
			'recaptcha_sitekey'		=> array(
				'title'					=> __( 'Google reCAPTCHA Site Key', 'af-locale' ),
				'description'			=> __( 'The public site key of your website for Google reCAPTCHA. You can get one <a href="http://www.google.com/recaptcha/admin">here</a>.', 'af-locale' ),
				'type'					=> 'text',
			),
			'recaptcha_secret'		=> array(
				'title'					=> __( 'Google reCAPTCHA Secret', 'af-locale' ),
				'description'			=> __( 'The secret key of your website for Google reCAPTCHA. You can get one <a href="http://www.google.com/recaptcha/admin">here</a>.', 'af-locale' ),
				'type'					=> 'text',
			),
		);

		add_action( 'form_restrictions_content_bottom', array( $this, 'recaptcha_fields' ), 10 );
		add_action( 'af_formbuilder_save', array( $this, 'save' ), 10, 1 );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 15 );
		add_action( 'af_submit_button_before', array( $this, 'draw_placeholder_element' ), 10, 2 );

		add_action( 'af_additional_restrictions_check_start', array( $this, 'check' ) );

		// compatibility with Contact Form 7
		remove_action( 'wpcf7_enqueue_scripts', 'wpcf7_recaptcha_enqueue_scripts' );
	}

	/**
	 * Timerange meta box
	 */
	public static function recaptcha_fields()
	{
		global $post;

		$form_id = $post->ID;

		$recaptcha_enabled = get_post_meta( $form_id, 'recaptcha_enabled', true );
		if ( $recaptcha_enabled ) {
			$recaptcha_enabled = true;
		} else {
			$recaptcha_enabled = false;
		}

		$recaptcha_type = get_post_meta( $form_id, 'recaptcha_type', true );
		$recaptcha_size = get_post_meta( $form_id, 'recaptcha_size', true );
		$recaptcha_theme = get_post_meta( $form_id, 'recaptcha_theme', true );

		$html = '<div id="form-restrictions-content-recaptcha" class="section general-settings">';
		$html .= '<table>';
		$html .= '<tr>';
		$html .= '<td>';
		$html .= '<label for="recaptcha_enabled">' . esc_html__( 'Enable Google reCAPTCHA?', 'af-locale' ) . '</label>';
		$html .= '</td>';
		$html .= '<td>';
		$html .= '<input type="checkbox" id="recaptcha_enabled" name="recaptcha_enabled" value="1" ' . checked( $recaptcha_enabled, true, false ) . '/>';
		$html .= '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td>';
		$html .= '<label for="recaptcha_type">' . esc_html__( 'reCAPTCHA Type', 'af-locale' ) . '</label>';
		$html .= '</td>';
		$html .= '<td>';
		$html .= '<select id="recaptcha_type" name="recaptcha_type">';
		$html .= '<option value="image" ' . selected( $recaptcha_type, 'image', false ) . '>' . esc_html__( 'Image', 'af-locale' ) . '</option>';
		$html .= '<option value="audio" ' . selected( $recaptcha_type, 'audio', false ) . '>' . esc_html__( 'Audio', 'af-locale' ) . '</option>';
		$html .= '</select>';
		$html .= '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td>';
		$html .= '<label for="recaptcha_size">' . esc_html__( 'reCAPTCHA Size', 'af-locale' ) . '</label>';
		$html .= '</td>';
		$html .= '<td>';
		$html .= '<select id="recaptcha_size" name="recaptcha_size">';
		$html .= '<option value="normal" ' . selected( $recaptcha_size, 'normal', false ) . '>' . esc_html__( 'Normal', 'af-locale' ) . '</option>';
		$html .= '<option value="compact" ' . selected( $recaptcha_size, 'compact', false ) . '>' . esc_html__( 'Compact', 'af-locale' ) . '</option>';
		$html .= '</select>';
		$html .= '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td>';
		$html .= '<label for="recaptcha_theme">' . esc_html__( 'reCAPTCHA Theme', 'af-locale' ) . '</label>';
		$html .= '</td>';
		$html .= '<td>';
		$html .= '<select id="recaptcha_theme" name="recaptcha_theme">';
		$html .= '<option value="light" ' . selected( $recaptcha_theme, 'light', false ) . '>' . esc_html__( 'Light', 'af-locale' ) . '</option>';
		$html .= '<option value="dark" ' . selected( $recaptcha_theme, 'dark', false ) . '>' . esc_html__( 'Dark', 'af-locale' ) . '</option>';
		$html .= '</select>';
		$html .= '</td>';
		$html .= '</tr>';
		$html .= '</table>';
		$html .= '</div>';

		echo $html;
	}

	public function is_enabled( $form_id ) {
		if ( empty( $this->settings['recaptcha_sitekey'] ) ) {
			return false;
		}

		if ( empty( $this->settings['recaptcha_secret'] ) ) {
			return false;
		}

		if ( ! get_post_meta( $form_id, 'recaptcha_enabled', true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if the user can pass
	 */
	public function check() {
		global $ar_form_id;

		// check must happen after submitting the form, so just return true here

		return true;
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
	public function enqueue_scripts() {
		global $ar_form_id, $post;

		if ( ! $ar_form_id ) {
			if ( ! $post || 'af-forms' != $post->post_type ) {
				// no form detected
				return;
			}
			$ar_form_id = $post->ID;
		}

		if ( ! $this->is_enabled( $ar_form_id ) ) {
			return;
		}

		wp_enqueue_script( 'af-recaptcha', AF_URLPATH . 'components/restrictions/base-restrictions/includes/js/recaptcha.js', array(), false, true );
		wp_localize_script( 'af-recaptcha', '_af_recaptcha_settings', array(
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
			'onload'	=> 'af_reCAPTCHA_widgets_init',
			'render'	=> 'explicit',
			'hl'		=> $locale,
		), $recaptcha_script_url );

		wp_enqueue_script( 'google-recaptcha', $recaptcha_script_url, array( 'af-recaptcha' ), false, true );

		add_filter( 'script_loader_tag', array( $this, 'handle_google_recaptcha_script_tag' ), 10, 3 );
	}

	public function handle_google_recaptcha_script_tag( $tag, $handle, $src ) {
		if ( 'google-recaptcha' == $handle ) {
			$tag = str_replace( '></script>', ' async defer></script>', $tag );
		}

		return $tag;
	}

	public function draw_placeholder_element( $form_id, $actual_step ) {
		if ( ! $this->is_enabled( $form_id ) ) {
			return;
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
		<div id="recaptcha-placeholder-<?php echo $form_id; ?>" class="recaptcha-placeholder" data-form-id="<?php echo $form_id; ?>" data-type="<?php echo $type; ?>" data-size="<?php echo $size; ?>" data-theme="<?php echo $theme; ?>" style="margin-bottom:20px;"></div>
		<?php
	}
}

af_register_restriction( 'AF_Restriction_Recaptcha' );
