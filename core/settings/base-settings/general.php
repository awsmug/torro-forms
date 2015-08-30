<?php
/**
 * General Settings Tab
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package Questions/Core
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
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

class AF_GeneralSettings extends AF_Settings
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $post;

		$this->title = __( 'General', 'wcsc-locale' );
		$this->name = 'general';
	}

	public function settings()
	{
		$this->settings = array(
			'disclaimer' => array(
				'title'       => esc_attr( 'Welcome to Awesome Forms!', 'questions-locale' ),
				'description' => esc_attr( 'You want to build any forms in a easy way? Awesome Forms will help you to do it in the very easy way with ton of options.', 'questions-locale' ),
				'type' => 'disclaimer'
			),
			'modules_title' => array(
				'title'       => esc_attr( 'Form Modules', 'questions-locale' ),
				'description' => esc_attr( 'Check the modules of Questions which have to be activated.', 'questions-locale' ),
				'type' => 'title'
			),
			'modules' => array(
				'title'       => esc_attr( 'Modules', 'questions-locale' ),
				'type' => 'checkbox',
				'values' => array(
					'charts' => esc_attr( 'Charts', 'questions-locale' ),
					'restrictions' => esc_attr( 'Restrictions', 'questions-locale' ),
					'response' => esc_attr( 'Response Handling', 'questions-locale' )
				),
				'description' => esc_attr( 'You don´t need some of these functions? Switch it off!', 'questions-locale' ),
				'defaults' => array( 'charts', 'restrictions', 'response' )
			)
		);
	}
}
qu_register_settings( 'AF_GeneralSettings' );