<?php

/**
 * Response base class
 *
 * Class for handling responses from database
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

class Questions_Results{
    var $form_id;

    /**
     * Initializes the Class.
     * @since 1.0.0
     */
    public function __construct( $form_id ) {
        $this->form_id = $form_id;

        $this->populate();
    }

    /**
     * Populating class variables
     *
     * @since 1.0.0
     */
    private function populate() {
        $this->responses = array();

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

        $form = new Questions_Form( $this->form_id );

        // If there are any elements
        if ( is_array( $form->elements ) ):
            $responses = array();

            // Adding user data
            if ( $userdata ):
                $responses[ '_user_id' ]  = $this->get_response_user_ids();
                $responses[ '_username' ]  = $this->get_response_user_names();
                $responses[ '_datetime' ] = $this->get_response_timestrings();
            endif;

            // Running each element of survey
            foreach ( $form->elements AS $element ):

                // If only one element have to be shown, skip the others
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
    public function get_response_user_ids() {

        global $wpdb, $questions_global;

        $sql     = $wpdb->prepare(
            "SELECT * FROM {$questions_global->tables->responds} WHERE questions_id = %s", $this->form_id
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
     * Gettiung all user names of a survey
     *
     * @return array $responses All user names formatted for response array
     * @since 1.0.0
     */
    public function get_response_user_names() {

        global $wpdb, $questions_global;

        $sql     = $wpdb->prepare(
            "SELECT * FROM {$questions_global->tables->responds} WHERE questions_id = %s", $this->form_id
        );
        $results = $wpdb->get_results( $sql );

        $responses                = array();
        $responses[ 'question' ]  = __( 'Username', 'questions-locale' );
        $responses[ 'sections' ]  = FALSE;
        $responses[ 'array' ]     = FALSE;
        $responses[ 'responses' ] = array();

        // Putting results in array
        if ( is_array( $results ) ):
            foreach ( $results AS $result ):
                $user = get_user_by( 'id', $result->user_id );
                $responses[ 'responses' ][ $result->id ] = $user->user_login;
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
    public function get_response_timestrings( $timeformat = 'd.m.Y H:i' ) {

        global $wpdb, $questions_global;

        $sql     = $wpdb->prepare(
            "SELECT * FROM {$questions_global->tables->responds} WHERE questions_id = %s", $this->form_id
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