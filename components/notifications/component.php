<?php
/**
 * Questions Notifications component
 *
 * This class initializes the notifications component.
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package Questions/Notifications
 * @version 2015-04-16
 * @since   1.0.0
 * @license GPL 2
 *
 *
 * Copyright 2015 awesome.ug (support@awesome.ug)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if( !defined( 'ABSPATH' ) ){
	exit;
}

class Questions_Notificatons extends Questions_Component
{

	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		$this->name = 'QuestionsNotifications';
		$this->title = esc_attr__( 'Notifications', 'questions-locale' );
		$this->description = esc_attr__( 'Notifications for forms', 'questions-locale' );
		$this->turn_off = FALSE;

		$this->slug = 'questionsnotifications';

		parent::__construct();

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 15 );
	} // end constructor

	/**
	 * Including files of component
	 */
	public function includes()
	{
		// Loading form builder extension
		include_once( QUESTIONS_COMPONENTFOLDER . '/notifications/form-builder-extension.php' );
	}

	/**
	 * Enqueue Scripts
	 */
	public function enqueue_scripts()
	{
		// wp_enqueue_script( 'questions-restrictions', QUESTIONS_URLPATH . '/components/restrictions/includes/js/restrictions.js' );
	}

}

$Questions_Notificatons = new Questions_Notificatons();
