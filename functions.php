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

if( !defined( 'ABSPATH' ) ){
	exit;
}

/**
 * Getting Plugin Template
 *
 * @since 1.0.0
 */
if( defined( 'QUESTIONS_FOLDER' ) ):
	function qu_locate_template( $template_names, $load = FALSE, $require_once = TRUE )
	{

		$located = locate_template( $template_names, $load, $require_once );

		if( '' == $located ):
			foreach( ( array ) $template_names as $template_name ):
				if( !$template_name ){
					continue;
				}
				if( file_exists( QUESTIONS_FOLDER . '/templates/' . $template_name ) ):
					$located = QUESTIONS_FOLDER . '/templates/' . $template_name;
					break;
				endif;
			endforeach;
		endif;

		if( $load && '' != $located ){
			load_template( $located, $require_once );
		}

		return $located;
	}
endif;

/**
 * Checks if we are a questions post type in admin
 * @return bool
 */
function qu_is_questions_formbuilder(){
	global $post;

	if( is_admin() && qu_is_questions() )
		return TRUE;

	return FALSE;
}
/**
 * Checks if we are on questions settings page
 * @return bool
 */
function qu_is_questions_settings(){
	if( is_admin() && isset( $_GET[ 'page' ] ) && 'QuestionsAdmin' == $_GET[ 'page' ] )
		return TRUE;

	return FALSE;
}
/**
 * Checks if we are in a questions post type
 * @return bool
 */
function qu_is_questions(){
	global $post;

	if( is_object( $post ) && get_class( $post ) == 'WP_Post' && 'questions' == $post->post_type  )
		return TRUE;

	return FALSE;
}


/**
 * Getting standard mailtext strings
 *
 * @param string $mailtext_title Type of mailtext which have to be chosen
 *
 * @return string $mailtext Mailtext as String
 */
// @todo Getting to Mail class or API
function qu_get_mail_template_text( $mailtext_title )
{

	$text = '';
	switch ( $mailtext_title ){
		case 'thankyou_participating':
			$text = stripslashes( get_option( 'questions_thankyou_participating_text_template' ) );
			if( empty( $text ) ):
				$text = esc_attr__( 'Dear %username%,

thank you for participating on the survey "%survey_title%". Your survey data was saved successfully.

Best regards,

%site_name%', 'questions-locale' );
			endif;

			break;

		case 'invitation':
			$text = stripslashes( get_option( 'questions_invitation_text_template' ) );
			if( empty( $text ) ):
				$text = esc_attr__( 'Dear %username%,

you have been invited to participate to the survey "%survey_title%". Participate here:

%survey_url%

Best regards,

%site_name%', 'questions-locale' );
			endif;

			break;

		case 'reinvitation':
			$text = stripslashes( get_option( 'questions_reinvitation_text_template' ) );
			if( empty( $text ) ):
				$text = esc_attr__( 'Dear %username%,

the survey "%survey_title%" is not finished yet. Please fill out and finish the survey. Participate here:

%survey_url%

Best regards,

%site_name%', 'questions-locale' );
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
function qu_get_mail_template_subject( $mailsubject_title )
{

	$text = '';
	switch ( $mailsubject_title ){
		case 'thankyou_participating':
			$text = stripslashes( get_option( 'questions_thankyou_participating_subject_template' ) );
			if( empty( $text ) ):
				$text = esc_attr__( 'Thank you for participating!', 'questions-locale' );
			endif;

			break;

		case 'invitation':
			$text = stripslashes( get_option( 'questions_invitation_subject_template' ) );
			if( empty( $text ) ):
				$text = esc_attr__( 'You are invited to answer a survey', 'questions-locale' );
			endif;

			break;

		case 'reinvitation':
			$text = stripslashes( get_option( 'questions_reinvitation_subject_template' ) );
			if( empty( $text ) ):
				$text = esc_attr__( 'Don´t forget to answer the Survey', 'questions-locale' );
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
function qu_get_mail_settings( $option )
{

	$setting = '';
	switch ( $option ){
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
function qu_change_email_return_name()
{

	return qu_get_mail_settings( 'from_name' );
}

/**
 * Shortener to get email return address from options
 *
 * @return string $from_email "From" email address
 */
function qu_change_email_return_address()
{

	return qu_get_mail_settings( 'from_email' );
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
function qu_mail( $to_email, $subject, $content )
{

	add_filter( 'wp_mail_from_name', 'qu_change_email_return_name' );
	add_filter( 'wp_mail_from', 'qu_change_email_return_address' );

	$result = wp_mail( $to_email, $subject, $content );

	// Logging
	$content = str_replace( chr( 13 ), '', strip_tags( $content ) );
	qu_create_log_entry( array(
		                     $to_email,
		                     $subject,
		                     $content ) );

	remove_filter( 'wp_mail_from_name', 'qu_change_email_return_name' );
	remove_filter( 'wp_mail_from', 'qu_change_email_return_address' );

	return $result;
}

/**
 * Base logging function
 *
 * @param array $values The values which have to be saved
 */
function qu_create_log_entry( $values )
{

	if( !is_array( $values ) ){
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
		                     "\r" ), ' ', $line );

	$line .= chr( 13 );

	$logdir = WP_CONTENT_DIR . '/logs/';

	if( !file_exists( $logdir ) ){
		mkdir( $logdir );
	}

	$logfile = $logdir . 'questions.log';

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
function qu_prepare_post_data( $data )
{
	// Do not preparing objects or arrays	
	if( is_object( $data ) || is_array( $data ) ){
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
function qu_id()
{
	$id = md5( rand() );

	return $id;
}

/**
 * Debugging helper function
 */
if( !function_exists( 'p' ) ){
	function p( $var )
	{
		echo '<pre>';
		print_r( $var );
		echo '</pre>';
	}
}
