<?php
/*
 * Questions main class
 *
 * This should be used as parent class for Question-Answers.
 *
 * @author awesome.ug, Author <support@awesome.ug>
 * @package Questions/Core
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

global $questions_global;

class Questions{
	var $tables;
	var $components = array();
	var $element_types = array();
	
	public function __construct(){
		add_action( 'plugins_loaded', array( $this, 'tables' ) );
	}
	
	public function tables(){
		global $wpdb;
		
		$this->tables = new stdClass;
		
		$this->tables->questions = $wpdb->prefix . 'questions_questions';
		$this->tables->answers = $wpdb->prefix . 'questions_answers';
		$this->tables->responds = $wpdb->prefix . 'questions_responds';
		$this->tables->respond_answers = $wpdb->prefix . 'questions_respond_answers';
		$this->tables->settings = $wpdb->prefix . 'questions_settings';
		$this->tables->participiants = $wpdb->prefix . 'questions_participiants';
		
		$this->tables = apply_filters( 'questions_tables', $this->tables );
	}
	
	public function add_component( $slug, $object ){
		if( '' == $slug )
			return FALSE;
		
		if( !is_object( $object ) && 'questions_Component' != get_parent_class( $object ) )
			return FALSE;
		
		$this->components[ $slug ] = $object;
		
		return TRUE;
	}
	
	public function add_survey_element( $slug, $object ){
		if( '' == $slug )
			return FALSE;
		
		if( !is_object( $object ) && 'Questions_SurveyElement' != get_parent_class( $object ) )
			return FALSE;
		
		$this->element_types[ $slug ] = $object;
		
		return TRUE;
	}
}
$questions_global = new Questions();
