<?php
/**
 * Charts abstraction class
 *
 * Motherclass for chart creation
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package AwesomeForms/Core
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 awesome.ug (support@awesome.ug)
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

if( !defined( 'ABSPATH' ) )
{
    exit;
}

abstract class AF_ChartCreator
{

    /**
     * Title of ChartCreator which will be shown in admin
     *
     * @since 1.0.0
     */
    var $title;

    /**
     * Description of ChartCreator
     *
     * @since 1.0.0
     */
    var $description;

    /**
     * Name of ChartCreator
     *
     * @since 1.0.0
     */
    var $name;

    /**
     * Control variable if ChartCreator is already initialized
     *
     * @since 1.0.0
     */
    var $initialized = FALSE;

    /**
     * Chart Types
     * @var array
     * @since 1.0.0
     */
    var $chart_types = array();

    /**
     * Initializing
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        // Standard values
        $this->name = get_class( $this );
        $this->title = ucfirst( $this->name );
        $this->description = esc_attr__( 'This is an Awesome Forms Chart Creator.', 'af-locale' );

        $this->register_chart_type( 'bars', esc_attr( 'Bars', 'af-locale' ), array( $this, 'bars' ) );
        $this->register_chart_type( 'pies', esc_attr( 'Pies', 'af-locale' ), array( $this, 'pies' ) );

        if( is_admin() ):
            add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );
        else:
            add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
        endif;
    }

    abstract function bars( $title, $results, $params = array() );

    abstract function pies( $title, $results, $params = array() );

    /**
     * Register Chart types
     *
     * @param $name
     * @param $display_name
     * @param $callback
     */
    protected function register_chart_type( $name, $display_name, $callback )
    {
        $this->chart_types[] = array(
            'name' => $name,
            'display_name' => $display_name,
            'callback' =>$callback
        );
    }

    /**
     * Function to register Charts creation module
     *
     * @return boolean $is_registered Returns TRUE if registering was succesfull, FALSE if not
     * @since 1.0.0
     */
    public function _register()
    {

        global $af_global;

        if( TRUE == $this->initialized )
        {
            return FALSE;
        }

        if( !is_object( $af_global ) )
        {
            return FALSE;
        }

        if( '' == $this->name )
        {
            $this->name = get_class( $this );
        }

        if( '' == $this->title )
        {
            $this->title = ucwords( get_class( $this ) );
        }

        if( '' == $this->description )
        {
            $this->description = esc_attr__( 'This is a Awesome Forms Form Element.', 'af-locale' );
        }

        if( array_key_exists( $this->name, $af_global->chart_creators ) )
        {
            return FALSE;
        }

        if( !is_array( $af_global->element_types ) )
        {
            $af_global->element_types = array();
        }

        $this->initialized = TRUE;

        return $af_global->add_chartscreator( $this->name, $this );
    }

    /**
     * Function to register library files
     */
    public function load_scripts()
    {
    }
}

/**
 * Register a new Chart creator
 *
 * @param $element_type_class name of the element type class.
 *
 * @return bool|null Returns false on failure, otherwise null.
 */
function af_register_chartcreator( $chart_creator_class )
{
    if( class_exists( $chart_creator_class ) )
    {
        $chart_creator = new $chart_creator_class();

        return $chart_creator->_register();
    }

    return FALSE;
}