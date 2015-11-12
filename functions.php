<?php
/**
 * Helper functions for plugin
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package AwesomeForms/Core
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

if( !defined( 'ABSPATH' ) )
{
	exit;
}

/**
 * Getting Plugin Template
 *
 * @since 1.0.0
 */
if( defined( 'AF_FOLDER' ) ):
	function af_locate_template( $template_names, $load = FALSE, $require_once = TRUE )
	{

		$located = locate_template( $template_names, $load, $require_once );

		if( '' == $located ):
			foreach( ( array ) $template_names as $template_name ):
				if( !$template_name )
				{
					continue;
				}
				if( file_exists( AF_FOLDER . '/templates/' . $template_name ) ):
					$located = AF_FOLDER . '/templates/' . $template_name;
					break;
				endif;
			endforeach;
		endif;

		if( $load && '' != $located )
		{
			load_template( $located, $require_once );
		}

		return $located;
	}
endif;

/**
 * Checks if we are a Awesome Forms post type in admin
 *
 * @return bool
 */
function af_is_formbuilder()
{
	if( is_admin() && af_is_form() )
	{
		return TRUE;
	}

	return FALSE;
}

/**
 * Checks if we are on Awesome Forms settings page
 *
 * @return bool
 */
function af_is_settingspage()
{
	if( is_admin() && isset( $_GET[ 'page' ] ) && 'AF_Admin' == $_GET[ 'page' ] )
	{
		return TRUE;
	}

	return FALSE;
}

/**
 * Checks if we are in a Awesome Forms post type
 *
 * @return bool
 */
function af_is_form()
{
	if( empty( $_GET[ 'post' ] ) || empty( $_GET[ 'action' ] )  )
	{
		return FALSE;
	}
	
	$post_id = $_GET[ 'post' ];
	$post = get_post( $post_id );

	if( !is_object( $post ) || get_class( $post ) != 'WP_Post' || 'af-forms' != $post->post_type )
	{
		return FALSE;
	}

	return TRUE;
}

function af_clipboard_field( $label, $content )
{
	$id = 'cb_' . af_id();

	$html = '<div class="clipboardfield">';
		$html .= '<label for="' . $id . '">' . $label . '</label> ';
		$html .= '<input type="text" id="' . $id . '" value="' . $content . '" />';

		$html .= '<button class="clipboard button" type="button" data-clipboard-target="#' . $id . '">';
		$html .= '<img src="' . AF_URLPATH . 'assets/images/clippy.svg" alt=' . esc_attr__( 'Copy to clipboard', 'af-locale' ) . '" />';
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
function af_get_mail_template_text( $mailtext_title )
{

	$text = '';
	switch ( $mailtext_title )
	{
		case 'thankyou_participating':
			$text = stripslashes( get_option( 'questions_thankyou_participating_text_template' ) );
			if( empty( $text ) ):
				$text = esc_attr__( 'Dear %username%,

thank you for participating on the survey "%survey_title%". Your survey data was saved successfully.

Best regards,

%site_name%', 'af-locale' );
			endif;

			break;

		case 'invitation':
			$text = stripslashes( get_option( 'questions_invitation_text_template' ) );
			if( empty( $text ) ):
				$text = esc_attr__( 'Dear %username%,

you have been invited to participate to the survey "%survey_title%". Participate here:

%survey_url%

Best regards,

%site_name%', 'af-locale' );
			endif;

			break;

		case 'reinvitation':
			$text = stripslashes( get_option( 'questions_reinvitation_text_template' ) );
			if( empty( $text ) ):
				$text = esc_attr__( 'Dear %username%,

the survey "%survey_title%" is not finished yet. Please fill out and finish the survey. Participate here:

%survey_url%

Best regards,

%site_name%', 'af-locale' );
			endif;

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
function af_get_mail_template_subject( $mailsubject_title )
{

	$text = '';
	switch ( $mailsubject_title )
	{
		case 'thankyou_participating':
			$text = stripslashes( get_option( 'questions_thankyou_participating_subject_template' ) );
			if( empty( $text ) ):
				$text = esc_attr__( 'Thank you for participating!', 'af-locale' );
			endif;

			break;

		case 'invitation':
			$text = stripslashes( get_option( 'questions_invitation_subject_template' ) );
			if( empty( $text ) ):
				$text = esc_attr__( 'You are invited to answer a survey', 'af-locale' );
			endif;

			break;

		case 'reinvitation':
			$text = stripslashes( get_option( 'questions_reinvitation_subject_template' ) );
			if( empty( $text ) ):
				$text = esc_attr__( 'Don´t forget to answer the Survey', 'af-locale' );
			endif;

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
function af_get_mail_settings( $option )
{

	$setting = '';
	switch ( $option )
	{
		case 'from_name':
			$setting = stripslashes( get_option( 'questions_mail_from_name' ) );

			if( empty( $setting ) ):
				$setting = get_option( 'blogname' );
			endif;

			break;

		case 'from_email':
			$setting = stripslashes( get_option( 'questions_mail_from_email' ) );

			if( empty( $setting ) ):
				$setting = get_option( 'admin_email' );
			endif;

			break;
	}

	return $setting;
}

/**
 * Shortener to get email return name from options
 *
 * @return string $from_name "From" name
 */
function af_change_email_return_name()
{
	return af_get_mail_settings( 'from_name' );
}

/**
 * Shortener to get email return address from options
 *
 * @return string $from_email "From" email address
 */
function af_change_email_return_address()
{
	return af_get_mail_settings( 'from_email' );
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
function af_mail( $to_email, $subject, $content )
{
	add_filter( 'wp_mail_from_name', 'af_change_email_return_name' );
	add_filter( 'wp_mail_from', 'af_change_email_return_address' );

	$result = wp_mail( $to_email, $subject, $content );

	// Logging
	$content = str_replace( chr( 13 ), '', strip_tags( $content ) );
	af_create_log_entry( array(
		                     $to_email,
		                     $subject,
		                     $content
	                     ) );

	remove_filter( 'wp_mail_from_name', 'af_change_email_return_name' );
	remove_filter( 'wp_mail_from', 'af_change_email_return_address' );

	return $result;
}

/**
 * Base logging function
 *
 * @param array $values The values which have to be saved
 */
function af_create_log_entry( $values )
{

	if( !is_array( $values ) )
	{
		return;
	}

	$line = date( 'Y-m-d;H:i:s;' );

	foreach( $values AS $value ):
		$line .= $value . ';';
	endforeach;

	$line = str_replace( array(
		                     "\r\n",
		                     "\n\r",
		                     "\n",
		                     "\r"
	                     ), ' ', $line );

	$line .= chr( 13 );

	$logdir = WP_CONTENT_DIR . '/logs/';

	if( !file_exists( $logdir ) )
	{
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
function af_prepare_post_data( $data )
{
	// Do not preparing objects or arrays	
	if( is_object( $data ) || is_array( $data ) )
	{
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
function af_id()
{
	$id = md5( rand() );

	return $id;
}

/**
 * Debugging helper function
 */
if( !function_exists( 'p' ) )
{
	function p( $var, $return = FALSE )
	{
		$content = '<pre>';
		$content .= print_r( $var, TRUE );
		$content .= '</pre>';

		if( !$return )
		{
			echo $content;
		}

		return $content;
	}
}
