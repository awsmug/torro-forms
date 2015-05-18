<?php
/*
 * Display Admin Class
 *
 * This class initializes the component.
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

class Questions_Charts extends Questions_Component{
	var $notices = array();
	
	/**
	 * Initializes the Component.
	 * @since 1.0.0
	 */
	function __construct() {
		parent::__construct();
		
		$this->name = 'QuestionsCharts';
		$this->title = __( 'Charts', 'questions-locale' );
		$this->description = __( 'Showing Charts in Questions.', 'questions-locale' );
		$this->required = TRUE;
		$this->capability = 'edit_posts';
		
		if( is_admin() ):
			add_action( 'admin_enqueue_scripts', array( $this, 'register_component_scripts' ) );
		else:
			add_action( 'wp_enqueue_scripts', array( $this, 'register_component_scripts' ) );
		endif;
		
	} // end constructor
	
	public function includes(){
		include( QUESTIONS_COMPONENTFOLDER . '/charts/chart-creator-dimple.php' );
		include( QUESTIONS_COMPONENTFOLDER . '/charts/shortcodes.php' );
	}
	
	public function register_component_scripts() {
		wp_enqueue_script( 'questions-d3-js',  QUESTIONS_URLPATH . '/components/charts/includes/3rdparty/d3/d3.js' );
		wp_enqueue_script( 'questions-dimple-js',  QUESTIONS_URLPATH . '/components/charts/includes/3rdparty/dimple/dimple.v2.1.2.min.js' );
	}
}

$Questions_Charts = new Questions_Charts();
