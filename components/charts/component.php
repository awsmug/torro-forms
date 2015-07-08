<?php
/**
 * Questions Charts Component
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
	} // end constructor
	
	public function includes(){
        // Base classes
        include( QUESTIONS_COMPONENTFOLDER . '/charts/class-chart-creator.php' );

        // Base functions
        include( QUESTIONS_COMPONENTFOLDER . '/charts/shortcodes.php' );

        // Loading chart creators
        include( QUESTIONS_COMPONENTFOLDER . '/charts/charts.js/chart-creator.php' );
        include( QUESTIONS_COMPONENTFOLDER . '/charts/dimple/chart-creator.php' );

        do_action( 'questions_loading_chart_creators' );
	}

    /**
     * Loading component scripts
     */
	public function register_component_scripts() {

	}
}

$Questions_Charts = new Questions_Charts();
