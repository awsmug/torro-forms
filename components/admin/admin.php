<?php
/*
 * ComponentName Core Class TODO
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

class SurveyValAdmin extends SurveyValComponent{
	/**
	 * Initializes the Component.
	 * @since 1.0.0
	 */
	function __construct() {
		$this->name = 'SurveyValAdmin';
		$this->title = __( 'Admin', 'surveyval_locale' );
		$this->description = __( 'Setting up SurveyVal in WordPress Admin.', 'surveyval-locale' );
		$this->turn_off = FALSE;
		$this->capability = 'edit_posts';
		
	    // Functions in Admin
	    if( is_admin() ):
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'parent_file', array( $this, 'tax_menu_correction' ) );
		// Functions not in Admin
		else:
		endif;
	} // end constructor
	
	/**
	 * Adds the Admin menu.
	 * @since 1.0.0
	 */	
	public function admin_menu(){
		add_menu_page( __( 'SurveyVal', 'surveyval-locale' ), __( 'SurveyVal', 'surveyval-locale' ), $this->capability, 'Component' . $this->name , array( $this, 'admin_page' ), '', 50 );
		add_submenu_page( 'Component' . $this->name, __( 'Categories', 'surveyval-locale' ), __( 'Categories', 'surveyval-locale' ), $this->capability, 'edit-tags.php?taxonomy=surveyval-categories' );
		add_submenu_page( 'Component' . $this->name, __( 'Settings', 'surveyval-locale' ), __( 'Settings', 'surveyval-locale' ), $this->capability, 'Component' . $this->name, array( $this, 'admin_page' ) );
	}
	
	// highlight the proper top level menu
	public function tax_menu_correction( $parent_file ) {
		global $current_screen;
		$taxonomy = $current_screen->taxonomy;
			
		if ( $taxonomy == 'surveyval-categories' )
			$parent_file = 'Component' . $this->name;
		
		return $parent_file;
	}
	
	
	/**
	 * Content of the admin page.
	 * @since 1.0.0
	 */
	public function admin_page(){
		echo '<div class="wrap">';
		echo '<div id="icon-edit" class="icon32 icon32-posts-post"></div>';
		echo '<h2>Menu Page example</h2>';
		echo '<p>Here comes the content.</p>';
		// locate_PluginName_template( 'example.php', TRUE );
		echo '</div>';
	}
	
	/**
	 * Content of a sub page.
	 * @since 1.0.0
	 */
	public function admin_sub_page(){
		echo '<div class="wrap">';
		echo '<div id="icon-edit" class="icon32 icon32-posts-post"></div>';
		echo '<h2>Submenu Page example</h2>';
		echo '<p>Here comes the content.</p>';
		echo '</div>';
	}
}

$SurveyValAdmin = new SurveyValAdmin();
