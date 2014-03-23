<?php
/*
 * Surveyval Base Elements
 *
 * This class initializes the component.
 *
 * @author rheinschmiede.de, Author <kontakt@rheinschmiede.de>
 * @package PluginName/Admin
 * @version 1.0.0
 * @since 1.0.0
 * @license GPL 2
 * 

  Copyright 2013 (kontakt@rheinschmiede.de)

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

class SurveyVal_Elements extends SurveyVal_Component{
	/**
	 * Initializes the Component.
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->name = 'SurveyValElements';
		$this->title = __( 'Elements', 'surveyval-locale' );
		$this->description = __( 'Base Elements to put into surveys', 'surveyval-locale' );
		$this->turn_off = FALSE;
		
		$this->slug = 'surveyelements';
		
		parent::__construct();
		
	} // end constructor
	
	public function includes(){
		include( SURVEYVAL_COMPONENTFOLDER . '/elements/text.php' );
		include( SURVEYVAL_COMPONENTFOLDER . '/elements/textarea.php' );
		include( SURVEYVAL_COMPONENTFOLDER . '/elements/onechoice.php' );
		include( SURVEYVAL_COMPONENTFOLDER . '/elements/multiplechoice.php' );
		include( SURVEYVAL_COMPONENTFOLDER . '/elements/select.php' );
		include( SURVEYVAL_COMPONENTFOLDER . '/elements/range.php' );
		include( SURVEYVAL_COMPONENTFOLDER . '/elements/separator.php' );
		include( SURVEYVAL_COMPONENTFOLDER . '/elements/splitter.php' );
		include( SURVEYVAL_COMPONENTFOLDER . '/elements/description.php' );
	}
	
}
$SurveyVal_Elements = new SurveyVal_Elements();