<?php
/*
 * SurveyVal main class
 *
 * This should be used as parent class for Question-Answers.
 *
 * @author rheinschmiede.de <kontakt@rehinschmiede.de>, Sven Wagener <sven.wagener@rehinschmiede.de>
 * @package Facebook Fanpage Import/Admin
 * @version 1.0.0
 * @since 1.0.0
 * @license GPL 2

  Copyright 2013 rheinschmiede (kontakt@rheinschmiede.de)

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

global $surveyval_global;

class SurveyVal{
	var $tables;
	var $components = array();
	var $element_types = array();
	
	public function __construct(){
		$this->tables();
	}
	
	private function tables(){
		global $wpdb;
		
		$this->tables = new stdClass;
		
		$this->tables->questions = $wpdb->prefix . 'surveyval_questions';
		$this->tables->answers = $wpdb->prefix . 'surveyval_answers';
		$this->tables->responds = $wpdb->prefix . 'surveyval_responds';
		$this->tables->respond_answers = $wpdb->prefix . 'surveyval_respond_answers';
		$this->tables->settings = $wpdb->prefix . 'surveyval_settings';
		$this->tables->participiants = $wpdb->prefix . 'surveyval_participiants';
	}
	
	public function add_component( $slug, $object ){
		if( '' == $slug )
			return FALSE;
		
		if( !is_object( $object ) && 'SurveyVal_Component' != get_parent_class( $object ) )
			return FALSE;
		
		$this->components[ $slug ] = $object;
		
		return TRUE;
	}
	
	public function add_survey_element( $slug, $object ){
		if( '' == $slug )
			return FALSE;
		
		if( !is_object( $object ) && 'SurveyVal_SurveyElement' != get_parent_class( $object ) )
			return FALSE;
		
		$this->element_types[ $slug ] = $object;
		
		return TRUE;
	}
}
$surveyval_global = new SurveyVal();
