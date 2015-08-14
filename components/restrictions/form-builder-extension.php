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

        if ( '' == $selected_restriction ): // If there was selected nothing before
            $selected_restriction = 'allvisitors';
        endif;

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

        echo $html;
    }
}
Questions_FormBuilder_RestrictionsExtension::init();