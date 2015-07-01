<?php

/**
 * Survey base class
 *
 * Init Surveys with this class to get information about it
 *
 * @author  awesome.ug <contact@awesome.ug>
 * @package Questions
 * @version 2015-04-16
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 rheinschmiede (contact@awesome.ug)
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

class Questions_Survey {

	/**
	 * @var int $id Survey ID
	 * @since 1.0.0
	 */
	public $id;

	/**
	 * @var string $title Title of Survey
	 * @since 1.0.0
	 */
	public $title;

	/**
	 * @var array $elements All elements of the survey
	 * @since 1.0.0
	 */
	public $elements = array();

	/**
	 * @var int $splitter_count Counter for form splitters
	 * @since 1.0.0
	 */
	public $splitter_count = 0;

	/**
	 * Constructor
	 * @param int $id The id of the survey
	 * @since 1.0.0
	 */
	public function __construct( $id = NULL ) {

		if ( NULL != $id ) {
			$this->populate( $id );
		}
	}

	/**
	 * Populating class variables
	 * 
	 * @param int $id The id of the survey
	 * @since 1.0.0
	 */
	private function populate( $id ) {

		$this->elements = array();

		$survey = get_post( $id );

		$this->id    = $id;
		$this->title = $survey->post_title;

		$this->elements = $this->get_elements( $id );
	}

	/**
	 * Getting all element objects
	 * 
	 * @param int $id The id of the survey
	 * @return array $elements All element objects of the survey
	 * @since 1.0.0
	 */
	public function get_elements( $id = NULL ) {

		global $questions_global, $wpdb;

		if ( NULL == $id ) {
			$id = $this->id;
		}

		if ( '' == $id ) {
			return FALSE;
		}

		$sql     = $wpdb->prepare(
			"SELECT * FROM {$questions_global->tables->questions} WHERE questions_id = %s ORDER BY sort ASC", $id
		);
		$results = $wpdb->get_results( $sql );

		$elements = array();

		// Running all elements which have been found
		if ( is_array( $results ) ):
			foreach ( $results AS $result ):
				if ( class_exists( 'questions_SurveyElement_' . $result->type ) ):
					$class       = 'Questions_SurveyElement_' . $result->type;
					$object      = new $class( $result->id );
					$elements[ ] = $object; // Adding element

					if ( $object->splits_form ):
						$this->splitter_count ++;
					endif;
				else:
					// If class do not exist -> Put in Error message here
				endif;
			endforeach;
		endif;

		return $elements;
	}

	/**
	 * Getting responses of a survey
	 * 
	 * @param bool|int $element_id Get responses of a special element
	 * @param boolean  $userdata   Adding user specified data to response array
	 * @return array $responses
	 * @since 1.0.0
	 */
	public function get_responses( $element_id = FALSE, $userdata = TRUE ) {

		// If there are any elements
		if ( is_array( $this->elements ) ):
			$responses = array();

			// Adding user data
			if ( $userdata ):
				$responses[ '_user_id' ]  = $this->get_response_user_ids();
				$responses[ '_datetime' ] = $this->get_response_timestrings();
			endif;

			// Running each element of survey
			foreach ( $this->elements AS $element ):

				if ( FALSE != $element_id && $element_id != $element->id ) {
					continue;
				}

				if ( ! $element->is_question ) {
					continue;
				}

				$responses[ $element->id ] = $element->get_responses();
			endforeach;

			return $responses;
		else:
			return FALSE;
		endif;
	}

	/**
	 * Gettiung all user ids of a survey
	 * 
	 * @return array $responses All user ids formatted for response array
	 * @since 1.0.0
	 */
	private function get_response_user_ids() {

		global $wpdb, $questions_global;

		$sql     = $wpdb->prepare(
			"SELECT * FROM {$questions_global->tables->responds} WHERE questions_id = %s", $this->id
		);
		$results = $wpdb->get_results( $sql );

		$responses                = array();
		$responses[ 'question' ]  = __( 'User ID', 'questions-locale' );
		$responses[ 'sections' ]  = FALSE;
		$responses[ 'array' ]     = FALSE;
		$responses[ 'responses' ] = array();

		// Putting results in array
		if ( is_array( $results ) ):
			foreach ( $results AS $result ):
				$responses[ 'responses' ][ $result->id ] = $result->user_id;
			endforeach;
		endif;

		return $responses;
	}

	/**
	 * Gettiung all timestrings of a survey
	 * 
	 * @param string $timeformat
	 * @return array $responses All timestrings formatted for response array
	 * @since 1.0.0
	 */
	private function get_response_timestrings( $timeformat = 'd.m.Y H:i' ) {

		global $wpdb, $questions_global;

		$sql     = $wpdb->prepare(
			"SELECT * FROM {$questions_global->tables->responds} WHERE questions_id = %s", $this->id
		);
		$results = $wpdb->get_results( $sql );

		$responses                = array();
		$responses[ 'question' ]  = __( 'Date/Time', 'questions-locale' );
		$responses[ 'sections' ]  = FALSE;
		$responses[ 'array' ]     = FALSE;
		$responses[ 'responses' ] = array();

		// Putting results in array
		if ( is_array( $results ) ):
			foreach ( $results AS $result ):
				$responses[ 'responses' ][ $result->id ] = date_i18n( $timeformat, $result->timestamp );
			endforeach;
		endif;

		return $responses;
	}
}

/**
 * Checks if a survey exists
 * @param int $survey_id Survey id
 * @return boolean $exists TRUE if survey exists, FALSE if not
 */
function qu_survey_exists( $survey_id ) {

	global $wpdb;

	$sql = $wpdb->prepare(
		"SELECT COUNT( ID ) FROM {$wpdb->prefix}posts WHERE ID = %d and post_type = 'questions'", $survey_id
	);
	$var = $wpdb->get_var( $sql );

	if ( $var > 0 ) {
		return TRUE;
	}

	return FALSE;
}
