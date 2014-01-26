<?php
/*
 * SurveyVal parent Question-Answer class
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

class SurveyVal_QA{
	var $slug;
	var $title;
	var $description;
	var $multiple_answers;
	
	/**
	 * Initializes the Question-Answer.
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->slug = get_class( $this );
		add_action( 'init', array( $this, 'includes' ), 0 );
		
		$this->title = ucfirst( $this->name );
		$this->description = __( 'This is a SurveyVal Question-Answer.', 'surveyval-locale' );
		
		$this->multiple_answers = FALSE;
		
	} // end constructor
	
	public function show(){
		
	}
	
	public function edit_screen(){
		
	}
	
	public function edit_save(){
		
	}
	
	public function submit(){
		
	}
	
}

function sv_register_qa( $class_name ){
	
}
#SOe