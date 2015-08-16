<?php
/**
 * Restrict form to all members of site and does some checks
 *
 * Motherclass for all Restrictions
 *
 * @author awesome.ug, Author <support@awesome.ug>
 * @package Questions/Restrictions
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

if ( !defined( 'ABSPATH' ) ) exit;

class Questions_Restriction_AllMembers extends Questions_Restriction{

    /**
     * Constructor
     */
    public function __construct(){
        $this->title = __( 'All Members', 'wcsc-locale' );
        $this->slug = 'allmembers';

        $this->option_name = __( 'All Members of site', 'wcsc-locale' );
    }

    /**
     * Adds content to the option
     */
    public function option_content(){
        return FALSE;
    }

    /**
     * Checks if the user can pass
     */
    public function check(){
        if ( ! is_user_logged_in() ){
            $this->add_message( 'error', esc_attr( 'You have to be logged in to participate.', 'wcsc-locale' ) );
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Has the user participated survey
     *
     * @param $questions_id
     * @param int $user_id
     * @return boolean $has_participated
     * @since 1.0.0
     */
    public function has_participated( $form_id, $user_id = NULL ) {

        global $wpdb, $current_user, $questions_global;

        // Setting up user ID
        if ( NULL == $user_id ):
            get_currentuserinfo();
            $user_id = $user_id = $current_user->ID;
        endif;

        // Setting up Form ID
        if ( NULL == $form_id ) {
            return FALSE;
        }

        $sql   = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$questions_global->tables->responds} WHERE questions_id=%d AND user_id=%s",
            $form_id, $user_id
        );
        $count = $wpdb->get_var( $sql );

        if ( 0 == $count ):
            return FALSE;
        else:
            return TRUE;
        endif;
    }

}
qu_register_restriction( 'Questions_Restriction_AllMembers' );
