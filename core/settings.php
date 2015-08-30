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

class AF_SettingsPage{

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
            return;
        }

        add_action( 'init', array( __CLASS__, 'save' ), 20 );
        add_action( 'admin_print_styles', array( __CLASS__, 'register_styles' ) );
    }

    /**
     * Show admin Settings
     */
    public static function show(){
        global $questions_global;

        self::init_tabs();

        $html = '<div class="wrap questions">';
            $html.= '<form name="questions_settings" id="questions-settings" method="POST">';
                $html.= '<input type="hidden" id="questions_save_settings" name="questions_save_settings" value="' . wp_create_nonce( '_questions_save_settings_nonce' ) . '" />';

                if( property_exists( $questions_global, 'settings' ) && count( $questions_global->settings ) > 0 ){

                    $html.= '<h2 class="nav-tab-wrapper">';
                    foreach( $questions_global->settings AS $setting ){

                        $css_classes = '';
                        if( $setting->name == self::$current_tab )
                            $css_classes = ' nav-tab-active';

                        $html.= '<a href="' . admin_url( 'admin.php?page=QuestionsAdmin&tab=' . $setting->name ) . '" class="nav-tab' . $css_classes . '">' . $setting->title . '</a>';
                    }
                    $html.= '</h2>';

                    $html.= '<div id="questions-settings-content">';
                    $html.= $questions_global->settings[ self::$current_tab ]->show();

                    ob_start();
                    do_action( 'questions_settings_' . self::$current_tab );
                    $html.= ob_get_clean();

                    $html.= '</div>';

                    $html.= '<input name="questions_save_settings" type="submit" class="button-primary button-save-settings" value="' . esc_attr( 'Save Settings', 'questions-locale' ) . '" />';

                }else{
                    $html.= '<p>' . esc_attr( 'There are no settings available', 'questions-locale' ) . '</p>';
                }

            $html.= '</form>';

        $html.= '</div>';

        echo $html;
    }

    /**
     * Saving settings
     */
    public static function save()
    {
        if( !isset( $_POST[ 'questions_save_settings' ] ) )
            return;

        do_action( 'questions_save_settings' );
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

        wp_enqueue_style( 'questions-admin-styles', QUESTIONS_URLPATH . '/core/includes/css/settings.css' );
    }
}
AF_SettingsPage::init();