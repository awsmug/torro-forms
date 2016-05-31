<?php
/**
 * Includes: Utility functions
 *
 * @package TorroForms
 * @subpackage Includes
 * @version 1.0.0-beta.3
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generates a temporary id.
 *
 * @return string
 * @since 1.0.0
 */
function torro_generate_temp_id() {
	return substr( 'temp_id_' . time() * rand(), 0, 14 );
}

/**
 * Checks whether id is a temporary id
 *
 * @param $id
 *
 * @return bool
 * @since 1.0.0
 */
function torro_is_temp_id( $id ) {
	return 'temp_id' === substr( $id, 0, 7 );
}

/**
 * Checks whether id is a real id
 *
 * @param $id
 *
 * @return bool
 * @since 1.0.0
 */
function torro_is_real_id( $id ) {
	return ! torro_is_temp_id( $id );
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
function torro_clipboard_field( $label, $content, $classes = 'clipboardfield' ) {
	$id = torro_generate_temp_id();

	$html = '<div class="' . esc_attr( $classes ) . '">';
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

	if ( ! empty( $torro_tmp_email_settings[ 'from_email' ] ) ) {
		$from_email = $torro_tmp_email_settings[ 'from_email' ];
	} else {
		$from_email = torro_get_mail_settings( 'from_email' );
	}

	return $from_email;
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
