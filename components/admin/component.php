<?php
/*
 * Display Admin Class
 *
 * This class initializes the component.
 *
 * @author awesome.ug, Author <support@awesome.ug>
 * @package Questions/Core
 * @version 2015-04-16
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Questions_Admin extends Questions_Component {

	var $notices = array();

	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->name        = 'QuestionsAdmin';
		$this->title       = esc_attr__( 'Admin', 'questions-locale' );
		$this->description = esc_attr__( 'Setting up Questions in WordPress Admin.', 'questions-locale' );
		$this->required    = TRUE;
		$this->capability  = 'edit_posts';

        parent::__construct();

	} // end constructor

    /**
     * Including files of component
     */
    public function includes(){
        // Base class for elements
        echo 'Including!';
        include( QUESTIONS_COMPONENTFOLDER . '/admin/menu.php');
        include( QUESTIONS_COMPONENTFOLDER . '/admin/post-type-questions.php');
    }
}

$Questions_Admin = new Questions_Admin();
