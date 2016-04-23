<?php
/**
 * Helper functions for plugin
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
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

/**
 * Getting Plugin Template
 *
 * @param mixed $template_names
 * @param boolean $load
 * @param boolean $require_once
 *
 * @return string $located
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
 * @since 1.0.0
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
 * @param string $tab
 * @param string $section
 *
 * @return bool
 * @since 1.0.0
 */
function torro_is_settingspage( $tab = null, $section = null ) {
	if ( is_admin() && isset( $_GET[ 'page' ] ) && 'Torro_Admin' === $_GET[ 'page' ] ) {

		if( isset( $tab ) ){
			if( ( isset( $_GET[ 'tab' ] ) && $tab !== $_GET[ 'tab' ] ) || ! isset( $_GET[ 'tab' ] ) ){
				return false;
			}
		}

		if( isset( $section ) ){
			if( ( isset( $_GET[ 'section' ] ) && $section !== $_GET[ 'section' ] ) || ! isset( $_GET[ 'section' ] )  ){
				return false;
			}
		}

		return true;
	}

	return false;
}

/**
 * Checks if we are in a Torro Forms post type
 *
 * @return bool
 * @since 1.0.0
 */
function torro_is_form() {
	if ( ! empty( $_GET[ 'post' ] ) ) {
		$post = get_post( $_GET[ 'post' ] );

		if ( is_a( $post, 'WP_Post' ) && 'torro_form' === $post->post_type ) {
			return true;
		}
	}

	if ( ! empty( $_GET[ 'post_type' ] ) && 'torro_form' === $_GET[ 'post_type' ] && ! isset( $_GET[ 'page' ] ) ) {
		return true;
	}

	return false;
}

/**
 * Clipboard field for clipboard.js
 *
 * @param $label
 * @param $content
 *
 * @return string
 * @since 1.0.0
 */
function torro_clipboard_field( $label, $content ) {
	$id = torro_generate_temp_id();

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
 * @since 1.0.0
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
 * @since 1.0.0
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
 * @since 1.0.0
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
 * @since 1.0.0
 */
function torro_change_email_return_name() {
	global $torro_tmp_email_settings;

	if( ! empty( $torro_tmp_email_settings[ 'from_name' ] ) ){
		$from_name = $torro_tmp_email_settings[ 'from_name' ];
	}else{
		$from_name = torro_get_mail_settings( 'from_name' );
	}

	return $from_name;
}

/**
 * Shortener to get email return address from options
 *
 * @return string $from_email "From" email address
 * @since 1.0.0
 */
function torro_change_email_return_address() {
	global $torro_tmp_email_settings;

	if( ! empty( $torro_tmp_email_settings[ 'from_email' ] ) ){
		$from_email = $torro_tmp_email_settings[ 'from_email' ];
	}else{
		$from_email = torro_get_mail_settings( 'from_email' );
	}

	return $from_email;
}

/**
 * Own Email function
 *
 * @param string $to_email Mail address for sending to
 * @param string $subject  Subject of mail
 * @param string $content
 *
 * @return bool
 * @since 1.0.0
 */
function torro_mail( $to_email, $subject, $content, $from_name = null, $from_email = null ) {
	global $torro_tmp_email_settings;

	$torro_tmp_email_settings = array(
		'from_name' => $from_name,
		'from_email' => $from_email
	);

	add_filter( 'wp_mail_from_name', 'torro_change_email_return_name' );
	add_filter( 'wp_mail_from', 'torro_change_email_return_address' );

	$result = wp_mail( $to_email, $subject, $content );

	remove_filter( 'wp_mail_from_name', 'torro_change_email_return_name' );
	remove_filter( 'wp_mail_from', 'torro_change_email_return_address' );
	unset( $torro_tmp_email_settings );

	return $result;
}

/**
 * Generating temporary id
 *
 * @return string
 * @since 1.0.0
 */
function torro_generate_temp_id() {
	return substr( 'temp_id_' . time() * rand(), 0, 14 );
}

/**
 * Returns if id is temp id
 *
 * @param $id
 *
 * @return bool
 * @since 1.0.0
 */
function torro_is_temp_id( $id ) {
	return 'temp_id' === substr( $id, 0, 7 );
}

function torro_is_real_id( $id ) {
	return ! torro_is_temp_id( $id );
}

/**
 * Debugging helper function
 *
 * @param mixed $var
 * @param boolean $return
 *
 * @return $content
 * @since 1.0.0
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
