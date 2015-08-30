<?php
/**
 * Awesome Forms Processing Restrictions Extension
 *
 * This class adds restriction functions to form processing
 *
 * @author awesome.ug, Author <support@awesome.ug>
 * @package AwesomeForms/Core
 * @version 2015-08-16
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

class AF_FormBuilder_FormProcesExtension{

    /**
     * Init in WordPress, run on constructor
     *
     * @return null
     * @since 1.0.0
     */
    public static function init()
    {
        add_filter( 'af_show_form', array( __CLASS__, 'check' ), 1 );
    }

    /**
     * Checking restrictions
     */
    public static function check( $show_form ){
        global $af_global, $ar_form_id;

        $restrictions = $af_global->restrictions;

        if( !is_array( $restrictions ) || count( $restrictions ) == 0 ){
            return;
        }

        if( FALSE == apply_filters( 'af_additional_restrictions_check_start', TRUE ) ){
            return FALSE;
        }

        /**
         * Select field for Restriction
         */
        $restrictions_option = get_post_meta( $ar_form_id, 'restrictions_option', TRUE );
        $restriction = $restrictions[ $restrictions_option ];

        if( FALSE == $restriction->check() ){
            echo $restriction->messages();
            return FALSE;
        }

        return apply_filters( 'af_additional_restrictions_check_end', TRUE );
    }
}
AF_FormBuilder_FormProcesExtension::init();