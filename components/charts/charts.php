<?php
/*
 * Display Admin Class
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

class SurveyVal_Charts extends SurveyVal_Component{
	var $notices = array();
	
	/**
	 * Initializes the Component.
	 * @since 1.0.0
	 */
	function __construct() {
		parent::__construct();
		
		$this->name = 'SurveyValCharts';
		$this->title = __( 'Charts', 'surveyval-locale' );
		$this->description = __( 'Showing Charts in SurveyVal.', 'surveyval-locale' );
		$this->required = TRUE;
		$this->capability = 'edit_posts';
		
		if( is_admin() ):
			add_action( 'admin_enqueue_scripts', array( $this, 'register_component_scripts' ) );
		else:
			add_action( 'wp_enqueue_scripts', array( $this, 'register_component_scripts' ) );
		endif;
		
	} // end constructor
	
	public function includes(){
		include( SURVEYVAL_COMPONENTFOLDER . '/charts/data-abstraction.php' );
		include( SURVEYVAL_COMPONENTFOLDER . '/charts/chart-creator-dimple.php' );
		include( SURVEYVAL_COMPONENTFOLDER . '/charts/shortcodes.php' );
	}
	
	public function register_component_scripts() {
		wp_enqueue_script( 'surveyval-d3-js',  SURVEYVAL_URLPATH . '/components/charts/includes/3rdparty/d3/d3.js' );
		wp_enqueue_script( 'surveyval-dimple-js',  SURVEYVAL_URLPATH . '/components/charts/includes/3rdparty/dimple/dimple.v2.1.0.js' );
	}
}

$SurveyVal_Charts = new SurveyVal_Charts();
