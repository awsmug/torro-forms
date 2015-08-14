<?php
/**
 * Questions Restrictions
 *
 * Component for the Restrictions API
 *
 * @author awesome.ug, Author <support@awesome.ug>
 * @package Questions/Restrictions
 * @version 2015-04-16
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Questions_Restrictions extends Questions_Component {

	/**
	 * Initializes the Component.
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->name        = 'QuestionsRestrictions';
		$this->title       = esc_attr__( 'Restrictions', 'questions-locale' );
		$this->description = esc_attr__( 'Restrictions if a user can fillout a form or not', 'questions-locale' );
		$this->turn_off    = FALSE;

		$this->slug = 'questionsrestrictions';

		parent::__construct();

	} // end constructor

    /**
     * Including files of component
     */
	public function includes() {
        // Loading form builder extension
        include_once( QUESTIONS_COMPONENTFOLDER . '/restrictions/form-builder-extension.php' );

        // Base class for restrictions
        include_once( QUESTIONS_COMPONENTFOLDER . '/restrictions/class-restrictions.php' );

        // Base restrictions
        include_once( QUESTIONS_COMPONENTFOLDER . '/restrictions/base-restrictions/all-visitors.php' );
        include_once( QUESTIONS_COMPONENTFOLDER . '/restrictions/base-restrictions/all-members.php' );
        include_once( QUESTIONS_COMPONENTFOLDER . '/restrictions/base-restrictions/selected-members.php' );
	}
}

$Questions_Restrictions = new Questions_Restrictions();
