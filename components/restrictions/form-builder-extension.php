<?php
/**
 * Torro Forms Restrictions Extension for the Formbuilder
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
 * @version 2015-04-16
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

class Torro_Formbuilder_RestrictionsExtension {
	/**
	 * Init in WordPress, run on constructor
	 *
	 * @return null
	 * @since 1.0.0
	 */
	public static function init() {
		if ( ! is_admin() ) {
			return null;
		}

		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ), 15 );
		add_action( 'torro_formbuilder_save', array( __CLASS__, 'save' ), 10, 1 );

		add_action( 'admin_print_styles', array( __CLASS__, 'register_admin_styles' ) );
	}

	/**
	 * Adding meta boxes
	 *
	 * @param string $post_type Actual post type
	 *
	 * @since 1.0.0
	 */
	public static function meta_boxes( $post_type ) {
		$post_types = array( 'torro-forms' );

		if ( in_array( $post_type, $post_types ) ) {
			add_meta_box( 'form-restrictions', __( 'Restrictions', 'torro-forms' ), array( __CLASS__, 'meta_box_restrictions' ), 'torro-forms', 'normal', 'low' );
		}
	}

	/**
	 * Form Restrictions box
	 *
	 * @since 1.0.0
	 */
	public static function meta_box_restrictions() {
		global $wpdb, $post, $torro_global;

		$form_id = $post->ID;
		$restrictions = $torro_global->restrictions;

		if ( ! is_array( $restrictions ) || 0 === count( $restrictions ) ) {
			return;
		}

		/**
		 * Select field for Restriction
		 */
		$restrictions_option = get_post_meta( $form_id, 'restrictions_option', true );

		if ( empty( $restrictions_option ) ) {
			$restrictions_option = 'allvisitors';
		}

		ob_start();
		do_action( 'form_restrictions_content_top' );
		$html = ob_get_clean();

		$html .= '<div class="section">';
		$html .= '<div id="form-restrictions-options">';
		$html .= '<label for"form_restrictions_option">' . esc_html__( 'Who has access to this form?', 'torro-forms' ) . '';
		$html .= '<select name="form_restrictions_option" id="form-restrictions-option">';
		foreach ( $restrictions as $name => $restriction ) {
			if ( ! $restriction->has_option() ) {
				continue;
			}
			$selected = '';
			if ( $name === $restrictions_option ) {
				$selected = ' selected="selected"';
			}
			$html .= '<option value="' . $name . '"' . $selected . '>' . $restriction->option_name . '</option>';
		}
		$html .= '</select></label>';
		$html .= '</div>';

		/**
		 * Option content
		 */
		foreach ( $restrictions as $name => $restriction ) {
			$option_content = $restriction->option_content();
			if ( ! $restriction->has_option() || ! $option_content ) {
				continue;
			}
			$html .= '<div id="form-restrictions-content-' . $restriction->name . '" class="form-restrictions-content form-restrictions-content-' . $restriction->name . '">' . $option_content . '</div>';
		}

		$html.= '</div>';

		ob_start();
		do_action( 'form_restrictions_content_bottom' );
		$html .= ob_get_clean();

		echo $html;
	}

	/**
	 * Saving data
	 *
	 * @param int $form_id
	 *
	 * @since 1.0.0
	 */
	public static function save( $form_id ) {
		/**
		 * Saving restriction options
		 */
		$restrictions_option = $_POST[ 'form_restrictions_option' ];
		update_post_meta( $form_id, 'restrictions_option', $restrictions_option );
	}

	/**
	 * Registers and enqueues admin-specific styles.
	 *
	 * @since 1.0.0
	 */
	public static function register_admin_styles() {
		if ( ! torro_is_formbuilder() ) {
			return;
		}

		wp_enqueue_style( 'torro-restrictions', TORRO_URLPATH . 'assets/css/restrictions.css' );
	}
}

Torro_Formbuilder_RestrictionsExtension::init();
