<?php

if ( !defined( 'ABSPATH' ) ) exit;

/*
* Getting Plugin Template
* @since 1.0.0
*/
if( defined( 'SURVEYVAL_FOLDER') ):
	function sv_locate_template( $template_names, $load = FALSE, $require_once = TRUE ) {
	    $located = '';
		
	    $located = locate_template( $template_names, $load, $require_once );
	
	    if ( '' == $located ):
			foreach ( ( array ) $template_names as $template_name ):
			    if ( !$template_name )
					continue;
			    if ( file_exists( SURVEYVAL_FOLDER . '/templates/' . $template_name ) ):
					$located = SURVEYVAL_FOLDER . '/templates/' . $template_name;
					break;
				endif;
			endforeach;
		endif;
	
	    if ( $load && '' != $located )
		    load_template( $located, $require_once );
	
	    return $located;
	}
endif;

function sv_get_mail_template_text( $mailtext_title ){
	switch ( $mailtext_title ){
		case 'thankyou_participating':
			$text = stripslashes( get_option( 'surveyval_thankyou_participating_text_template' ) );
			if( empty( $text ) ):
				$text = __( 'Dear %username%,
	
thank you for participating on the survey "%survey_title%". Your survey data was saved successfully.

Best regards,

%site_name%', 'surveyval-locale' );
			endif;
			
			break;
			
		case 'invitation':
			$text = stripslashes( get_option( 'surveyval_invitation_text_template' ) );
			if( empty( $text ) ):
				$text = __( 'Dear %username%,
	
you have been invited to participate to the survey "%survey_title%". Participate here:

%survey_url%

Best regards,

%site_name%', 'surveyval-locale' );
			endif;
			
			break;
			
		case 'reinvitation':
			$text = stripslashes( get_option( 'surveyval_reinvitation_text_template' ) );
			if( empty( $text ) ):
				$text = __( 'Dear %username%,
	
the survey "%survey_title%" is not finished yet. Please fill out and finish the survey. Participate here:

%survey_url%

Best regards,

%site_name%', 'surveyval-locale' );
			endif;
			
			break;
			
	}

	return $text;
}

function sv_get_mail_template_subject( $mailtext_title ){
	switch ( $mailtext_title ){
		case 'thankyou_participating':
			$text = stripslashes( get_option( 'surveyval_thankyou_participating_subject_template' ) );
			if( empty( $text ) ):
				$text = __( 'Thank you for participating!', 'surveyval-locale' );
			endif;
			
			break;
			
		case 'invitation':
			$text = stripslashes( get_option( 'surveyval_invitation_subject_template' ) );
			if( empty( $text ) ):
				$text = __( 'You are invited to answer a survey', 'surveyval-locale' );
			endif;
			
			break;
			
		case 'reinvitation':
			$text = stripslashes( get_option( 'surveyval_reinvitation_subject_template' ) );
			if( empty( $text ) ):
				$text = __( 'Don´t forget to answer the Survey', 'surveyval-locale' );
			endif;
			
			break;
			
	}

	return $text;
}
