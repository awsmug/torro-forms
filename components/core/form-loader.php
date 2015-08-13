<?php
/**
 * Loading form
 *
 * This class will load the form
 *
 * @author awesome.ug, Author <support@awesome.ug>
 * @package Questions/Core
 * @version 1.0.0
 * @since 1.0.0
 * @license GPL 2

  Copyright 2015 awesome.ug (support@awesome.ug)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Questions_FormLoader
{

    /**
     * ID of processed form
     */
    var $form_id;

    /**
     * Form Process Object
     */
    var $form_process;


    /**
     * Initializes the Component.
     * @since 1.0.0
     */
    public static function init( $filter_the_content = FALSE ){
        add_action('parse_request', array(__CLASS__, 'process_response'), 10, 1 );

        if( TRUE == $filter_the_content ) {
            add_action('the_post', array(__CLASS__, 'add_post_filter')); // Just hooking in at the beginning of a loop
        }
    } // end constructor

    /**
     * Porcessing Response
     */
    public static function process_response( $response ){
        global $questions_form_id, $questions_process, $questions_form_id;

        // Getting Post ID
        $post_id = $response->query_vars[ 'p' ];
        $questions_form_id = $post_id;

        $post = get_post( $questions_form_id );

        if( 'questions' != $post->post_type ){
            return;
        }

        $questions_process_id = $post->ID;

        $questions_process = new Questions_FormProcess( $questions_form_id ); // @todo Doing it with globals and static class?
        $questions_process->process_response();
    }

    /**
     * Adding filter for the content to show Survey
     * @since 1.0.0
     */
    public static function add_post_filter()
    {
        add_filter('the_content', array( __CLASS__, 'the_content'));
    }

    /**
     * The filtered content gets a survey
     *
     * @param string $content
     * @return string $content
     * @since 1.0.0
     */
    public static function the_content($content){

        global $questions_process, $questions_form_id, $questions_response_errors;

        $post = get_post( $questions_form_id );

        if( 'questions' != $post->post_type ){
            return;
        }

        // @todo: Should move to processing
        // Set global message on top of page
        if (!empty($questions_response_errors)) {
            $html = '<div class="questions-element-error">';
            $html .= '<div class="questions-element-error-message"><p>';
            $html .= esc_attr__('There are open answers', 'questions-locale');
            $html .= '</p></div></div>';

            echo $html;
        }

        self::show();

        remove_filter('the_content', array( __CLASS__ , 'the_content')); // only show once

        return $content;
    }

    /**
     * Show form
     * @param int $post_id
     */
    public static function show( $post_id = 0 ){
        global $questions_process, $questions_form_id;

        if(0 != $post_id){
            $questions_form_id = $post_id;
            $questions_process = new Questions_FormProcess( $questions_form_id );
        }

        $questions_process->init_form();
    }
}
Questions_FormLoader::init( TRUE );