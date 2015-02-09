<?php
/*
 * Questions Base Elements
 *
 * This class initializes the component.
 *
 * @author awesome.ug, Author <support@awesome.ug>
 * @package Questions/Elements
 * @version 1.0.0
 * @since 1.0.0
 * @license GPL 2
 * 

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

class Questions_Elements extends Questions_Component{
	/**
	 * Initializes the Component.
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->name = 'QuestionsElements';
		$this->title = __( 'Elements', 'questions-locale' );
		$this->description = __( 'Base Elements to put into surveys', 'questions-locale' );
		$this->turn_off = FALSE;
		
		$this->slug = 'surveyelements';
		
		parent::__construct();
		
	} // end constructor
	
	public function includes(){
		include( QUESTIONS_COMPONENTFOLDER . '/elements/text.php' );
		include( QUESTIONS_COMPONENTFOLDER . '/elements/textarea.php' );
		include( QUESTIONS_COMPONENTFOLDER . '/elements/onechoice.php' );
		include( QUESTIONS_COMPONENTFOLDER . '/elements/multiplechoice.php' );
		include( QUESTIONS_COMPONENTFOLDER . '/elements/dropdown.php' );
		include( QUESTIONS_COMPONENTFOLDER . '/elements/separator.php' );
		include( QUESTIONS_COMPONENTFOLDER . '/elements/splitter.php' );
		include( QUESTIONS_COMPONENTFOLDER . '/elements/description.php' );
	}
	
}
$Questions_Elements = new Questions_Elements();