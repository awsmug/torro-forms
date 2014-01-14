<?php
/*
 * Facebook Fanpage Import Showdata Component.
 *
 * This class initializes the component.
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

class SurveyValComponent{
	var $name;
	
	/**
	 * Initializes the Component.
	 * @since 1.0.0
	 */
	function __construct() {
		$this->name = get_class( $this );
		$this->includes();
	} // end constructor
	
	/**
	 * Including needed Files.
	 * @since 1.0.0
	 */	
	private function includes(){
	}
}