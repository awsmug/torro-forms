<?php
/**
 * Question Form Builder Restrictions extension
 *
 * This class adds question post type functions.
 *
 * @author awesome.ug, Author <support@awesome.ug>
 * @package Questions/Core
 * @version 2015-04-16
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

class Questions_FormBuilder_RestrictionsExtension{
    /**
     * Init in WordPress, run on constructor
     *
     * @return null
     * @since 1.0.0
     */
    public static function init() {

        if ( ! is_admin() )
            return NULL;

        add_action( 'admin_enqueue_scripts', array( __CLASS__ , 'enqueue_scripts' ), 15 );
        add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ), 15 );

        add_action( 'save_post', array( __CLASS__, 'save_form' ) );
        add_action( 'delete_post', array( __CLASS__, 'delete_form' ) );

        add_action( 'wp_ajax_questions_add_members_standard', array( __CLASS__, 'ajax_add_members' ) );
        add_action( 'wp_ajax_questions_invite_participiants', array( __CLASS__, 'ajax_invite_participiants' ) );
        add_action( 'wp_ajax_questions_delete_responses', array( __CLASS__, 'ajax_delete_responses' ) );
    }

    /**
     * Adding meta boxes
     *
     * @param string $post_type Actual post type
     * @since 1.0.0
     */
    public static function meta_boxes( $post_type ) {

        $post_types = array( 'questions' );

        if ( in_array( $post_type, $post_types ) ):
            add_meta_box(
                'form-restrictions',
                esc_attr__( 'Restrictions', 'questions-locale' ),
                array( __CLASS__, 'meta_box_form_restrictions' ),
                'questions',
                'normal',
                'high'
            );
        endif;
    }

    /**
     * Form Restrictions box
     * @since 1.0.0
     */
    public static function meta_box_form_restrictions() {
        global $wpdb, $post, $questions_global;

        $form_id = $post->ID;
        $restrictions = $questions_global->restrictions;

        if( !is_array( $restrictions ) || count( $restrictions ) == 0 ){
            return;
        }

        /**
         * Select field for Restriction
         */
        $selected_restriction = get_post_meta( $form_id, 'participiant_restrictions', TRUE );

        $html  = '<div id="questions_restrictions">';
        $html .= '<select name="questions_restrictions_select" id="questions-restrictions-select">';
        foreach( $restrictions AS $slug => $restriction ) {
            if( !$restriction->has_option() ){
                continue;
            }
            $selected = '';
            if ( $slug == $selected_restriction ) {
                $selected = ' selected="selected"';
            }
            $html .= '<option value="' . $slug . '"' . $selected . '>' . $restriction->option_name . '</option>';
        }
        $html .= '</select>';
        $html .= '</div>';

        /**
         * Option content
         */
        foreach( $restrictions AS $slug => $restriction ) {
            $option_content = $restriction->option_content();
            if( !$restriction->has_option() || !$option_content ){
                continue;
            }
            $html .= '<div id="questions_restrictions_content_' . $restriction->slug . '" class="questions-restrictions-content questions-restrictions-content-' . $restriction->slug . '">' . $option_content . '</div>';
        }


        /*

        $form_id = $post->ID;

        $sql      = $wpdb->prepare(  "SELECT user_id FROM {$questions_global->tables->participiants} WHERE survey_id = %s", $form_id );
        $user_ids = $wpdb->get_col( $sql );

        $users = array();

        if ( is_array( $user_ids ) && count( $user_ids ) > 0 ):
            $users = get_users(
                array(
                    'include' => $user_ids,
                    'orderby' => 'ID'
                )
            );
        endif;

        $disabled = '';
        $selected = '';

        $participiant_restrictions = get_post_meta( $form_id, 'participiant_restrictions', TRUE );

        $restrictions = apply_filters(
            'questions_post_type_participiant_restrictions',
            array(
                'all_visitors'     => esc_attr__(
                    'All visitors of the site can participate',
                    'questions-locale'
                ),
                'all_members'      => esc_attr__(
                    'All members of the site can participate',
                    'questions-locale'
                ),
                'selected_members' => esc_attr__(
                    'Only selected members can participate',
                    'questions-locale'
                ),
                'no_restrictions'     => esc_attr__(
                    'No restrictions',
                    'questions-locale'
                )
            )
        );

        if ( '' == $participiant_restrictions && count( $users ) > 0 ): // If there are participiants and nothing was selected before
            $participiant_restrictions = 'selected_members';
        elseif ( '' == $participiant_restrictions ): // If there was selected nothing before
            $participiant_restrictions = 'all_visitors';
        endif;

        $html = '<div id="questions_participiants_select_restrictions">';
        $html .= '<select name="questions_participiants_restrictions_select" id="questions-participiants-restrictions-select"' . $disabled . '>';
        foreach ( $restrictions AS $key => $value ):
            $selected = '';
            if ( $key == $participiant_restrictions ) {
                $selected = ' selected="selected"';
            }
            $html .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
        endforeach;
        $html .= '</select>';
        $html .= '</div>';

        $options = apply_filters(
            'questions_post_type_add_participiants_options',
            array(
                'all_members' => esc_attr__(
                    'Add all actual Members', 'questions-locale'
                ),
            )
        );

        $html .= '<div id="questions_selected_members">';

        $disabled = '';
        $selected = '';

        $html .= '<div id="questions_participiants_select">';
        $html .= '<select name="questions_participiants_select" id="questions-participiants-select"' . $disabled . '>';
        foreach ( $options AS $key => $value ):
            $html .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
        endforeach;
        $html .= '</select>';
        $html .= '</div>';

        $html .= '<div id="questions-participiants-standard-options" class="questions-participiants-options-content">';
        $html .= '<div class="add"><input type="button" class="questions-add-participiants button" id="questions-add-members-standard" value="'
            . esc_attr__(
                'Add Participiants', 'questions-locale'
            ) . '" /><a href="#" class="questions-remove-all-participiants">'
            . esc_attr__(
                'Remove all Participiants', 'questions-locale'
            ) . '</a></div>';
        $html .= '</div>';

        ob_start();
        do_action( 'questions_post_type_participiants_content_top' );
        $html .= ob_get_clean();

        $html .= '<div id="questions-participiants-status" class="questions-participiants-status">';
        $html .= '<p>' . count( $users ) . ' ' . esc_attr__( 'participiant/s', 'questions-locale' ) . '</p>';
        $html .= '</div>';

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
        $html .= '<input type="hidden" id="questions-participiants-count" name="questions-participiants-count" value="' . count(
                $users
            ) . '" />';

        $html .= '</div>';

        $html .= '</div>';


        */
        echo $html;
    }
}
Questions_FormBuilder_RestrictionsExtension::init();