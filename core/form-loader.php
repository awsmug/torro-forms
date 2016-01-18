<?php
/**
 * Loading form
 *
 * This class will load the form
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Torro_Form_Loader {
	/**
	 * ID of processed form
	 */
	var $form_id;

	/**
	 * Form Process Object
	 */
	var $form_process;

	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	public static function init( $filter_the_content = false ) {
		add_action( 'parse_request', array( __CLASS__, 'process_response' ), 100, 1 );

		if ( true === $filter_the_content ) {
			add_action( 'the_post', array(
				__CLASS__,
				'add_post_filter'
			) ); // Just hooking in at the beginning of a loop
		}
	}

	/**
	 * Porcessing Response
	 */
	public static function process_response( $response ) {
		global $ar_form_id;

		if ( ! isset( $_SESSION ) ) {
			session_start();
		}

		// If there is no nothing submitted and there is no session data > exit
		if ( ! isset( $_POST[ 'torro_form_id' ] ) ) {
			return;
		}

		$ar_form_id = $_POST[ 'torro_form_id' ];

		// If form doesn't exists > exit
		if ( ! torro_form_exists( $ar_form_id ) ) {
			return;
		}

		do_action( 'torro_form_process' );

		$torro_form_process = new Torro_Form_Process( $ar_form_id );
		$torro_form_process->process_response();
	}

	/**
	 * Adding filter for the content to show Form
	 *
	 * @since 1.0.0
	 */
	public static function add_post_filter() {
		add_filter( 'the_content', array( __CLASS__, 'the_content' ) );
	}

	/**
	 * The filtered content gets a Form
	 *
	 * @param string $content
	 *
	 * @return string $content
	 * @since 1.0.0
	 */
	public static function the_content( $content ) {
		global $torro_form_process, $ar_form_id, $torro_response_errors;

		$post = get_post( $ar_form_id );
		$ar_form_id = $post->ID;

		if ( 'torro-forms' !== $post->post_type ) {
			return $content;
		}

		$html = self::get_form( $ar_form_id );

		remove_filter( 'the_content', array( __CLASS__, 'the_content' ) ); // only show once

		return $html;
	}

	/**
	 * Getting form
	 *
	 * @param $form_id
	 *
	 * @return string
	 */
	public static function get_form( $form_id ) {
		if ( isset( $_SESSION[ 'torro_response' ][ $form_id ][ 'finished' ] ) ) {
			$html = self::text_thankyou_for_participation( $form_id );
			session_destroy();
		} else {
			$torro_form_process = new Torro_Form_Process( $form_id );
			$html = $torro_form_process->show_form();
		}

		return $html;
	}

	/**
	 * Text which will be shown after a user has participated successful
	 *
	 * @param int $form_id
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	public static function text_thankyou_for_participation( $form_id ) {
		// @todo Should move to response handling
		$show_results = get_post_meta( $form_id, 'show_results', true );
		if ( 'yes' !== $show_results ) {
			$show_results = 'no';
		}

		$html = '<div id="torro-thank-submitting">';
		$html .= '<p>' . esc_html__( 'Thank you for submitting!', 'torro-forms' ) . '</p>';
		if ( 'yes' === $show_results ) {
			$html .= self::show_results( $form_id );
		}
		$html .= '</div>';

		return apply_filters( 'torro_text_thankyou_for_participation', $html, $form_id );
	}

	/**
	 * Showing results
	 *
	 * @param int $form_id
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	public static function show_results( $form_id ) {
		$html = '<p>' . __( 'This are the actual results:', 'torro-forms' ) . '</p>';
		$html .= do_shortcode( '[form_charts id="' . $form_id . '"]' );

		return apply_filters( 'torro_show_results', $html, $form_id );
	}
}

Torro_Form_Loader::init( true );
