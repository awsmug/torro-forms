<?php
/**
 * Question settings page
 *
 * This class shows and saves the settings page
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

class Questions_SettingsPage{

    /**
     * The current tab
     * @var
     */
    static $current_tab;

    /**
     * Init in WordPress, run on constructor
     *
     * @return null
     * @since 1.0.0
     */
    public static function init()
    {
        if( !is_admin() ){
            return NULL;
        }

        self::init_tabs();

        add_action( 'admin_print_styles', array( __CLASS__, 'register_styles' ) );
    }

    /**
     * Show admin Settings
     */
    public static function show(){
        global $questions_global;

        $html = '<div class="wrap questions">';
            $html.= '<form name="questions_settings" id="questions-settings" method="POST">';
                $html.= '<input type="hidden" id="questions_save_settings" name="questions_save_settings" value="' . wp_create_nonce( '_questions_save_settings_nonce' ) . '" />';

                if( property_exists( $questions_global, 'settings' ) && count( $questions_global->settings ) > 0 ){

                    foreach( $questions_global->settings AS $setting ){

                        if( $setting->slug == self::$current_tab )
                            $css_classes = ' nav-tab-active';

                        $html.= '<h2 class="nav-tab-wrapper">';
                        $html.= '<a href="' . admin_url( 'admin.php?page=ComponentQuestionsAdmin&tab=' . $setting->slug ) . '" class="nav-tab' . $css_classes . '">' . $setting->title . '</a>';
                        $html.= '</h2>';
                    }

                    $html.= '<div id="questions-settings-content">';
                    $html.= $questions_global->settings[ self::$current_tab ]->settings();
                    $html.= '</div>';

                    $html.= '<input type="button" class="button" value="' . esc_attr( 'Save', 'questions-locale' ) . '" />';

                }else{
                    $html.= '<p>' . esc_attr( 'There are no settings available', 'questions-locale' ) . '</p>';
                    p( $questions_global->settings );
                }

            $html.= '</form>';

        $html.= '</div>';

        echo $html;
    }

    /**
     * Initializing Tabs
     */
    public static function init_tabs(){
        if( isset( $_GET[ 'tab' ]) ){
            self::$current_tab = $_GET[ 'tab' ];
        }else{
            self::$current_tab = 'general';
        }
    }

    /**
     * Registers and enqueues admin-specific styles.
     *
     * @since 1.0.0
     */
    public static function register_styles()
    {
        if( !qu_is_questions_settings() )
            return;

        wp_enqueue_style( 'questions-admin-styles', QUESTIONS_URLPATH . '/components/core/includes/css/settings.css' );
    }
}
Questions_SettingsPage::init();