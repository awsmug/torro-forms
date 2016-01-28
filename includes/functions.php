<?php
/**
 * Helper functions for plugin
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
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

/**
 * Getting Plugin Template
 *
 * @since 1.0.0
 */
function torro_locate_template( $template_names, $load = false, $require_once = true ) {

	$located = locate_template( $template_names, $load, $require_once );

	if ( '' === $located ) {
		foreach ( ( array ) $template_names as $template_name ) {
			if ( ! $template_name ) {
				continue;
			}
			$file = torro()->get_path( 'templates/' . $template_name );
			if ( file_exists( $file ) ) {
				$located = $file;
				break;
			}
		}
	}

	if ( $load && '' !== $located ) {
		load_template( $located, $require_once );
	}

	return $located;
}

/**
 * Checks if we are a Torro Forms post type in admin
 *
 * @return bool
 */
function torro_is_formbuilder() {
	if ( is_admin() && torro_is_form() ) {
		return true;
	}

	return false;
}

/**
 * Checks if we are on Torro Forms settings page
 *
 * @return bool
 */
function torro_is_settingspage() {
	if ( is_admin() && isset( $_GET[ 'page' ] ) && 'Torro_Admin' === $_GET[ 'page' ] ) {
		return true;
	}

	return false;
}

/**
 * Checks if we are in a Torro Forms post type
 *
 * @return bool
 */
function torro_is_form() {
	if ( ! empty( $_GET[ 'post' ] ) ) {
		$post = get_post( $_GET[ 'post' ] );

		if ( is_a( $post, 'WP_Post' ) && 'torro-forms' === $post->post_type ) {
			return true;
		}
	}

	if ( ! empty( $_GET[ 'post_type' ] ) && 'torro-forms' === $_GET[ 'post_type' ] && ! isset( $_GET[ 'page' ] ) ) {
		return true;
	}

	return false;
}

function torro_clipboard_field( $label, $content ) {
	$id = 'cb_' . torro_id();

	$html = '<div class="clipboardfield">';
	$html .= '<label for="' . $id . '">' . $label . '</label> ';
	$html .= '<input type="text" id="' . $id . '" value="' . $content . '" />';

	$html .= '<button class="clipboard button" type="button" data-clipboard-target="#' . $id . '">';
	$html .= '<img src="' . torro()->get_asset_url( 'clippy', 'svg' ) . '" alt=' . esc_attr__( 'Copy to clipboard', 'torro-forms' ) . '" />';
	$html .= '</button>';

	$html .= '<div style="clear:both;"></div>';
	$html .= '</div>';

	return $html;
}

/**
 * Getting standard mailtext strings
 *
 * @param string $mailtext_title Type of mailtext which have to be chosen
 *
 * @return string $mailtext Mailtext as String
 */
// @todo Getting to Mail class or API
function torro_get_mail_template_text( $mailtext_title ) {
	$text = '';

	switch ( $mailtext_title ) {
		case 'thankyou_participating':
			$text = stripslashes( get_option( 'questions_thankyou_participating_text_template' ) );
			if ( empty( $text ) ) {
				$text = __( 'Dear %username%,

thank you for participating on the survey "%survey_title%". Your survey data was saved successfully.

Best regards,

%site_name%', 'torro-forms' );
			}
			break;
		case 'invitation':
			$text = stripslashes( get_option( 'questions_invitation_text_template' ) );
			if ( empty( $text ) ) {
				$text = __( 'Dear %username%,

you have been invited to participate to the survey "%survey_title%". Participate here:

%survey_url%

Best regards,

%site_name%', 'torro-forms' );
			}
			break;
		case 'reinvitation':
			$text = stripslashes( get_option( 'questions_reinvitation_text_template' ) );
			if ( empty( $text ) ) {
				$text = __( 'Dear %username%,

the survey "%survey_title%" is not finished yet. Please fill out and finish the survey. Participate here:

%survey_url%

Best regards,

%site_name%', 'torro-forms' );
			}
			break;
	}

	return $text;
}

/**
 * Getting standard mailsubject strings
 *
 * @param string $mailtext_title Type of mail subject which have to be chosen
 *
 * @return string $mailtext Mail subject as String
 */
function torro_get_mail_template_subject( $mailsubject_title ) {
	$text = '';

	switch ( $mailsubject_title ) {
		case 'thankyou_participating':
			$text = stripslashes( get_option( 'questions_thankyou_participating_subject_template' ) );
			if ( empty( $text ) ) {
				$text = __( 'Thank you for submitting!', 'torro-forms' );
			}
			break;
		case 'invitation':
			$text = stripslashes( get_option( 'questions_invitation_subject_template' ) );
			if ( empty( $text ) ) {
				$text = __( 'You are invited to answer a survey', 'torro-forms' );
			}
			break;
		case 'reinvitation':
			$text = stripslashes( get_option( 'questions_reinvitation_subject_template' ) );
			if ( empty( $text ) ) {
				$text = __( 'Don´t forget to answer the Survey', 'torro-forms' );
			}
			break;
	}

	return $text;
}

/**
 * Get mail sender settings
 *
 * @param $option string Mail sender option to get
 *
 * @return mixed $setting Setting
 */
function torro_get_mail_settings( $option ) {
	$setting = '';

	switch ( $option ) {
		case 'from_name':
			$setting = stripslashes( get_option( 'questions_mail_from_name' ) );
			if ( empty( $setting ) ) {
				$setting = get_option( 'blogname' );
			}
			break;
		case 'from_email':
			$setting = stripslashes( get_option( 'questions_mail_from_email' ) );
			if ( empty( $setting ) ) {
				$setting = get_option( 'admin_email' );
			}
			break;
	}

	return $setting;
}

/**
 * Shortener to get email return name from options
 *
 * @return string $from_name "From" name
 */
function torro_change_email_return_name() {
	return torro_get_mail_settings( 'from_name' );
}

/**
 * Shortener to get email return address from options
 *
 * @return string $from_email "From" email address
 */
function torro_change_email_return_address() {
	return torro_get_mail_settings( 'from_email' );
}

/**
 * Own Email function
 *
 * @param string $to_email Mail address for sending to
 * @param string $subject  Subject of mail
 * @param string $content
 *
 * @return bool
 */
function torro_mail( $to_email, $subject, $content ) {
	add_filter( 'wp_mail_from_name', 'torro_change_email_return_name' );
	add_filter( 'wp_mail_from', 'torro_change_email_return_address' );

	$result = wp_mail( $to_email, $subject, $content );

	// Logging
	$content = str_replace( chr( 13 ), '', strip_tags( $content ) );
	torro_create_log_entry( array(
		                        $to_email,
		                        $subject,
		                        $content,
	                        ) );

	remove_filter( 'wp_mail_from_name', 'torro_change_email_return_name' );
	remove_filter( 'wp_mail_from', 'torro_change_email_return_address' );

	return $result;
}

/**
 * Base logging function
 *
 * @param array $values The values which have to be saved
 */
function torro_create_log_entry( $values ) {
	if ( ! is_array( $values ) ) {
		return;
	}

	$line = date( 'Y-m-d;H:i:s;' );

	foreach ( $values as $value ) {
		$line .= $value . ';';
	}

	$line = str_replace( array(
		                     "\r\n",
		                     "\n\r",
		                     "\n",
		                     "\r",
	                     ), ' ', $line );

	$line .= chr( 13 );

	$logdir = WP_CONTENT_DIR . '/logs/';

	if ( ! file_exists( $logdir ) ) {
		mkdir( $logdir );
	}

	$logfile = $logdir . 'awesome-forms.log';

	$file = fopen( $logfile, 'a' );
	fwrite( $file, $line );
	fclose( $file );
}

/**
 * Preparing input data
 *
 * @param string $data
 *
 * @return string $data
 */
function torro_prepare_post_data( $data ) {
	// Do not preparing objects or arrays
	if ( is_object( $data ) || is_array( $data ) ) {
		return $data;
	}

	$data = trim( $data );
	$data = stripslashes( $data );

	return $data;
}

/**
 * Creates a random id
 *
 * @return string $id ID string
 */
function torro_id() {
	$id = md5( rand() );

	return $id;
}

/**
 * Debugging helper function
 */
if ( ! function_exists( 'p' ) ) {
	function p( $var, $return = false ) {
		$content = '<pre>';
		$content .= print_r( $var, true );
		$content .= '</pre>';

		if ( ! $return ) {
			echo $content;
		}

		return $content;
	}
}