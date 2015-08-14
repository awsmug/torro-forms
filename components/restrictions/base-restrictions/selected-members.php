<?php
/**
 * Restrict form to all selected members
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

class Questions_Restriction_SelectedMembers extends Questions_Restriction{

    /**
     * Constructor
     */
    public function __construct(){
        $this->title = __( 'Selected Members', 'wcsc-locale' );
        $this->slug = 'selectedmembers';

        $this->option_name = __( 'Selected Members of site', 'wcsc-locale' );

        add_action( 'admin_enqueue_scripts', array( $this , 'enqueue_scripts' ), 15 );
    }

    /**
     * Adds content to the option
     */
    public function option_content(){
        global $wpdb, $post, $questions_global;

        $form_id = $post->ID;

        $sql      = $wpdb->prepare(  "SELECT user_id FROM {$questions_global->tables->participiants} WHERE survey_id = %s", $form_id );
        $user_ids = $wpdb->get_col( $sql );

        $users = array();

        if ( is_array( $user_ids ) && count( $user_ids ) > 0 ) {
            $users = get_users(
                array(
                    'include' => $user_ids,
                    'orderby' => 'ID'
                )
            );
        }

        $options = apply_filters( 'questions_add_participiants_options',
                        array(
                            'all_members' => esc_attr__( 'Add all actual Members', 'questions-locale' )
                        )
        );

        $selected = '';

        // Participiants select method
        $html = '<div id="questions_participiants_select">';
            $html .= '<select name="questions_participiants_select" id="questions-participiants-select">';
            foreach ( $options AS $key => $value ):
                $html .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
            endforeach;
            $html .= '</select>';
        $html .= '</div>';

        // Action Buttons
        $html .= '<div id="questions-participiants-standard-options" class="questions-participiants-options-content">';
        $html .= '<div class="add">
                        <input type="button" class="questions-add-participiants button" id="questions-add-members-standard" value="' . esc_attr__('Add Participiants', 'questions-locale') . '" />
                        <a href="#" class="questions-remove-all-participiants">' . esc_attr__('Remove all Participiants', 'questions-locale') . '</a></div>
                  </div>';

        // Hooking in
        ob_start();
        do_action( 'questions_participiants_content_top' );
        $html .= ob_get_clean();

        // User statistic
        $html .= '<div id="questions-participiants-status" class="questions-participiants-status">';
        $html .= '<p>' . count( $users ) . ' ' . esc_attr__( 'participiant/s', 'questions-locale' ) . '</p>';
        $html .= '</div>';

        // Participiants list
        $html .= '<div id="questions-participiants-list">';
        $html .= '<table class="wp-list-table widefat">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>' . esc_attr__( 'ID', 'questions-locale' ) . '</th>';
        $html .= '<th>' . esc_attr__( 'User nicename', 'questions-locale' ) . '</th>';
        $html .= '<th>' . esc_attr__( 'Display name', 'questions-locale' ) . '</th>';
        $html .= '<th>' . esc_attr__( 'Email', 'questions-locale' ) . '</th>';
        $html .= '<th>' . esc_attr__( 'Status', 'questions-locale' ) . '</th>';
        $html .= '<th>&nbsp</th>';
        $html .= '</tr>';
        $html .= '</thead>';

        $html .= '<tbody>';

        $questions_participiants_value = '';

        if ( is_array( $users ) && count( $users ) > 0 ):

            foreach ( $users AS $user ):
                if ( qu_user_has_participated( $form_id, $user->ID ) ):
                    $user_css  = ' finished';
                    $user_text = esc_attr__( 'finished', 'questions-locale' );
                else:
                    $user_text = esc_attr__( 'new', 'questions-locale' );
                    $user_css  = ' new';
                endif;

                $html .= '<tr class="participiant participiant-user-' . $user->ID . $user_css . '">';
                $html .= '<td>' . $user->ID . '</td>';
                $html .= '<td>' . $user->user_nicename . '</td>';
                $html .= '<td>' . $user->display_name . '</td>';
                $html .= '<td>' . $user->user_email . '</td>';
                $html .= '<td>' . $user_text . '</td>';
                $html .= '<td><a class="button questions-delete-participiant" rel="' . $user->ID . '">' . esc_attr__(
                        'Delete', 'questions-locale'
                    ) . '</a></th>';
                $html .= '</tr>';
            endforeach;

            $questions_participiants_value = implode( ',', $user_ids );

        endif;

        $html .= '</tbody>';

        $html .= '</table>';

        $html .= '<input type="hidden" id="questions-participiants" name="questions_participiants" value="' . $questions_participiants_value . '" />';
        $html .= '<input type="hidden" id="questions-participiants-count" name="questions-participiants-count" value="' . count($users) . '" />';

        $html .= '</div>';

        return $html;
    }

    /**
     * Checks if the user can pass
     */
    public function check(){

        if ( ! is_user_logged_in() ){
            $this->add_message( 'error', esc_attr( 'You have to be logged in to participate.', 'questions-locale' ) );
            return FALSE;
        }

        if ( ! is_participiant() ) {
            $this->add_message('error', esc_attr('You can\'t participate this survey.', 'questions-locale'));
        }

        return TRUE;
    }

    /**
     * Checks if a user can participate
     *
     * @param int $form_id
     * @param int $user_id
     * @return boolean $can_participate
     * @since 1.0.0
     */
    public function is_participiant( $user_id = NULL ) {

        global $wpdb, $current_user, $questions_global, $questions_form_id;

        $is_participiant = FALSE;

        // Setting up user ID
        if ( NULL == $user_id ):
            get_currentuserinfo();
            $user_id = $user_id = $current_user->ID;
        endif;

        $sql = $wpdb->prepare( "SELECT user_id FROM {$questions_global->tables->participiants} WHERE survey_id = %d", $is_participiant );
        $user_ids = $wpdb->get_col( $sql );

        if( in_array( $user_id, $user_ids  ) )
            $is_participiant = TRUE;

        return apply_filters( 'questions_user_is_participiant', $is_participiant, $user_id );
    }

    /**
     * Enqueue Scripts
     */
    public function enqueue_scripts(){
        wp_enqueue_script( 'questions-selected-members', QUESTIONS_URLPATH . '/components/restrictions/base-restrictions/includes/js/selected-members.js' );
    }
}
qu_register_restriction( 'Questions_Restriction_SelectedMembers' );
