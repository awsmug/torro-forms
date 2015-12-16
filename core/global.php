<?php
/**
 * Torro Forms Class for main global $af_global
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

global $af_global;

class AF_Global
{
	var $tables;
	var $components = array();
	var $settings = array();
	var $element_types = array();
	var $actions = array();
	var $restrictions = array();
	var $result_handlers = array();
	var $chart_creators = array();
	var $templatetags = array();

	public function __construct()
	{
		$this->tables();
	}

	public function tables()
	{
		global $wpdb;

		$this->tables = new stdClass;

		$this->tables->elements = $wpdb->prefix . 'af_elements';
		$this->tables->element_answers = $wpdb->prefix . 'af_element_answers';
		$this->tables->results = $wpdb->prefix . 'af_results';
		$this->tables->result_values = $wpdb->prefix . 'af_result_values';
		$this->tables->settings = $wpdb->prefix . 'af_settings';
		$this->tables->participiants = $wpdb->prefix . 'af_participiants';
		$this->tables->email_notifications = $wpdb->prefix . 'af_email_notifications';

		$this->tables = apply_filters( 'af_forms_tables', $this->tables );
	}

	public function add_component( $name, $object )
	{
		if( '' == $name )
		{
			return FALSE;
		}

		if( !is_object( $object ) && 'AF_Component' != get_parent_class( $object ) )
		{
			return FALSE;
		}

		$this->components[ $name ] = $object;

		return TRUE;
	}

	public function add_settings( $name, $object )
	{
		if( '' == $name )
		{
			return FALSE;
		}

		if( !is_object( $object ) && 'AF_Settings' != get_parent_class( $object ) )
		{
			return FALSE;
		}

		$this->settings[ $name ] = $object;

		return TRUE;
	}

	public function add_form_element( $name, $object )
	{
		if( '' == $name )
		{
			return FALSE;
		}
		if( !is_object( $object ) && 'AF_Form_Element' != get_parent_class( $object ) )
		{
			return FALSE;
		}
		$this->element_types[ $name ] = $object;

		return TRUE;
	}

	public function add_restriction( $name, $object )
	{
		if( '' == $name )
		{
			return FALSE;
		}

		if( !is_object( $object ) && 'AF_Restriction' != get_parent_class( $object ) )
		{
			return FALSE;
		}

		$this->restrictions[ $name ] = $object;

		return TRUE;
	}

	public function add_action( $name, $object )
	{
		if( '' == $name )
		{
			return FALSE;
		}

		if( !is_object( $object ) && 'AF_Action' != get_parent_class( $object ) )
		{
			return FALSE;
		}

		$this->actions[ $name ] = $object;

		return TRUE;
	}

	public function add_result_handler( $name, $object )
	{
		if( '' == $name )
		{
			return FALSE;
		}

		if( !is_object( $object ) && 'AF_Action' != get_parent_class( $object ) )
		{
			return FALSE;
		}

		$this->result_handlers[ $name ] = $object;

		return TRUE;
	}

	public function add_chartscreator( $name, $object )
	{
		if( '' == $name )
		{
			return FALSE;
		}

		if( !is_object( $object ) && 'AF_Chart_Creator' != get_parent_class( $object ) )
		{
			return FALSE;
		}

		$this->chart_creators[ $name ] = $object;

		return TRUE;
	}

	public function add_templatetags( $name, $object )
	{
		if( '' == $name )
		{
			return FALSE;
		}

		if( !is_object( $object ) && 'AF_TemplateTags' != get_parent_class( $object ) )
		{
			return FALSE;
		}

		$this->templatetags[ $name ] = $object;

		return TRUE;
	}
}

$af_global = new AF_Global();
