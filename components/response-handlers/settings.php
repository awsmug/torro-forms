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

class Questions_ResponseSettings extends Questions_Settings
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $post;

		$this->title = __( 'Response Handling', 'wcsc-locale' );
		$this->slug = 'responsehandling';
	}

	public function settings(){

		$settings = array(
			'modules_title' => array(
				'title'       => esc_attr( 'Response Handling', 'questions-locale' ),
				'description' => esc_attr( 'Setup the Response Handling settings.', 'questions-locale' ),
				'type' => 'title'
			),

		);

		$settings_handler = new Questions_SettingsHandler( 'general', $settings );

		$html = $settings_handler->get();

		return $html;
	}
}
qu_register_settings( 'Questions_ResponseSettings' );