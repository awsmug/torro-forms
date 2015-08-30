<?php
/**
 * Questions Class for main global $questions_global
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package Questions/Core
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

if( !defined( 'ABSPATH' ) ){
	exit;
}

global $questions_global;

class Questions
{
	var $tables;
	var $components = array();
	var $settings = array();
	var $element_types = array();
	var $response_handlers = array();
	var $restrictions = array();
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

		$this->tables->questions = $wpdb->prefix . 'questions_questions';
		$this->tables->answers = $wpdb->prefix . 'questions_answers';
		$this->tables->responds = $wpdb->prefix . 'questions_responds';
		$this->tables->respond_answers = $wpdb->prefix . 'questions_respond_answers';
		$this->tables->settings = $wpdb->prefix . 'questions_settings';
		$this->tables->participiants = $wpdb->prefix . 'questions_participiants';
		$this->tables->email_notifications = $wpdb->prefix . 'questions_email_notifications';

		$this->tables = apply_filters( 'questions_tables', $this->tables );
	}

	public function add_component( $name, $object )
	{
		if( '' == $name ){
			return FALSE;
		}

		if( !is_object( $object ) && 'questions_Component' != get_parent_class( $object ) ){
			return FALSE;
		}

		$this->components[ $name ] = $object;

		return TRUE;
	}

	public function add_settings( $name, $object )
	{
		if( '' == $name ){
			return FALSE;
		}

		if( !is_object( $object ) && 'Questions_Settings' != get_parent_class( $object ) ){
			return FALSE;
		}

		$this->settings[ $name ] = $object;

		return TRUE;
	}

	public function add_form_element( $name, $object )
	{
		if( '' == $name ){
			return FALSE;
		}
		if( !is_object( $object ) && 'Questions_FormElement' != get_parent_class( $object ) ){
			return FALSE;
		}
		$this->element_types[ $name ] = $object;
		return TRUE;
	}

	public function add_restriction( $name, $object )
	{
		if( '' == $name ){
			return FALSE;
		}

		if( !is_object( $object ) && 'Questions_Restriction' != get_parent_class( $object ) ){
			return FALSE;
		}

		$this->restrictions[ $name ] = $object;

		return TRUE;
	}

	public function add_response_handler( $name, $object )
	{
		if( '' == $name ){
			return FALSE;
		}

		if( !is_object( $object ) && 'Questions_ResponseHandler' != get_parent_class( $object ) ){
			return FALSE;
		}

		$this->response_handlers[ $name ] = $object;

		return TRUE;
	}

	public function add_chartscreator( $name, $object )
	{
		if( '' == $name ){
			return FALSE;
		}

		if( !is_object( $object ) && 'Questions_ChartCreator' != get_parent_class( $object ) ){
			return FALSE;
		}

		$this->chart_creators[ $name ] = $object;

		return TRUE;
	}

	public function add_templatetags( $name, $object )
	{
		if( '' == $name ){
			return FALSE;
		}

		if( !is_object( $object ) && 'Questions_TemplateTags' != get_parent_class( $object ) ){
			return FALSE;
		}

		$this->templatetags[ $name ] = $object;

		return TRUE;
	}
}

$questions_global = new Questions();
