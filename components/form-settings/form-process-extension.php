<?php
/**
 * Torro Forms Processing Restrictions Extension
 *
 * This class adds access-control functions to form processing
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
 * @version 1.0.0alpha1
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Torro_Formbuilder_FormProcessExtension {

	/**
	 * Init in WordPress, run on constructor
	 *
	 * @return null
	 * @since 1.0.0
	 */
	public static function init() {
		add_filter( 'torro_form_show', array( __CLASS__, 'check' ), 1 );
	}

	/**
	 * Checking access-controls
	 */
	public static function check( $show_form ) {
		$form_id = torro()->forms()->get_current_form_id();
		$access_controls = torro()->access_controls()->get_all_registered();

		if ( 0 === count( $access_controls ) ) {
			return $show_form;
		}

		if ( false === apply_filters( 'torro_additional_access_controls_check_start', true ) ) {
			return false;
		}

		/**
		 * Select field for Restriction
		 */
		$access_controls_option = get_post_meta( $form_id, 'access_controls_option', true );

		if ( ! empty( $access_controls_option ) && array_key_exists( $access_controls_option, $access_controls ) ) {
			$access_control = $access_controls[ $access_controls_option ];

			if ( false === $access_control->check() ) {
				echo $access_control->messages();

				return false;
			}
		}

		return apply_filters( 'torro_additional_access_controls_check_end', true );
	}
}

Torro_Formbuilder_FormProcessExtension::init();
