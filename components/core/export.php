<?php
/*
 * Exporting data
 *
 * This class creates the export
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
 
class SurveyVal_Export{
	/**
	 * Initializes the Component.
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'export' ), 10 );
		add_filter( 'post_row_actions', array( $this, 'add_export_link' ), 10, 2 );
	} // end constructor
	
	function add_export_link( $actions, $post ){
		if( 'surveyval' != $post->post_type )
			return $actions;
		
		$actions['view_poll_results'] = sprintf( __( '<a href="%s">Export Results</a>', 'surveyval-locale' ), '?post_type=surveyval&export_survey_id=' . $post->ID );
		
		return $actions;
	}
	
	function export(){
		if( array_key_exists( 'export_survey_id', $_GET ) && is_array( $_GET ) ):
			$survey_id = $_GET['export_survey_id'];
		endif;
	}
	
	
}
$SurveyVal_Export = new SurveyVal_Export();
