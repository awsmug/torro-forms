<?php
/**
 * Restrict form to a timerange
 *
 * Motherclass for all Restrictions
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package AwesomeForms/Restrictions
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

if( !defined( 'ABSPATH' ) )
{
	exit;
}

class AF_Restriction_Timerange extends AF_Restriction
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->title = __( 'Timerange', 'af-locale' );
		$this->name = 'timerange';

		add_action( 'form_restrictions_content_bottom', array( $this, 'timerange_fields' ), 10 );
		add_action( 'af_save_form', array( $this, 'save' ), 10, 1 );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 15 );
		add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );

		add_action( 'af_additional_restrictions_check_start', array( $this, 'check' ) );
	}

	/**
	 * Timerange meta box
	 */
	public static function timerange_fields()
	{
		global $post;

		$form_id = $post->ID;

		$start_date = get_post_meta( $form_id, 'start_date', TRUE );
		$end_date = get_post_meta( $form_id, 'end_date', TRUE );

		$html = '<div id="form-restrictions-content-timerange" class="section general-settings">';
			$html .= '<table>';
			$html .= '<tr>';
			$html .= '<td>';
			$html .= '<label for="start_date">' . esc_attr__( 'Form input start:', 'af-locale' ) . '</label>';
			$html .= '</td>';
			$html .= '<td>';
				$html .= '<input type="text" id="start_date" name="start_date" value="' . $start_date . '"/>';
			$html .= '</td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td>';
				$html .= '<label for="end_date">' . esc_attr__( 'Form input end:', 'af-locale' ) . '</label>';
			$html .= '</td>';
			$html .= '<td>';
				$html .= '<input type="text" id="end_date" name="end_date" value="' . $end_date . '"/>';
			$html .= '</td>';
			$html .= '</tr>';
			$html .= '</table>';
		$html .= '</div>';

		echo $html;
	}

	/**
	 * Checks if the user can pass
	 */
	public function check()
	{
		global $ar_form_id;

		$actual_date = time();
		$start_date = strtotime( get_post_meta( $ar_form_id, 'start_date', TRUE ) );
		$end_date = strtotime( get_post_meta( $ar_form_id, 'end_date', TRUE ) );

		if( '' != $start_date && 0 != (int) $start_date && FALSE != $start_date && $actual_date < $start_date )
		{
			$this->add_message( 'error', esc_attr( 'The Form is not accessible at this time.', 'af-locale' ) );
			echo $this->messages();

			return FALSE;
		}

		if( '' != $end_date && 0 != (int) $end_date && FALSE != $end_date && '' != $end_date && $actual_date > $end_date )
		{
			$this->add_message( 'error', esc_attr( 'The Form is not accessible at this time.', 'af-locale' ) );
			echo $this->messages();

			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Has IP already participated
	 *
	 * @return bool $has_participated
	 * @since 1.0.0
	 *
	 */
	public function ip_has_participated()
	{

		global $wpdb, $af_global, $quesions_form_id;

		$remote_ip = $_SERVER[ 'REMOTE_ADDR' ];

		$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$af_global->tables->results} WHERE form_id_id=%d AND remote_addr=%s", $quesions_form_id, $remote_ip );
		$count = $wpdb->get_var( $sql );

		if( 0 == $count )
		{
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Saving data
	 *
	 * @param int $form_id
	 *
	 * @since 1.0.0
	 */
	public static function save( $form_id )
	{
		global $wpdb, $af_global;

		$start_date = $_POST[ 'start_date' ];
		$end_date = $_POST[ 'end_date' ];

		/**
		 * Saving start and end date
		 */
		update_post_meta( $form_id, 'start_date', $start_date );
		update_post_meta( $form_id, 'end_date', $end_date );
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts()
	{
		$translation_admin = array( 'dateformat' => esc_attr__( 'yy/mm/dd', 'af-locale' ),
		                            'min_sun'    => esc_attr__( 'Su', 'af-locale' ),
		                            'min_mon'    => esc_attr__( 'Mo', 'af-locale' ),
		                            'min_tue'    => esc_attr__( 'Tu', 'af-locale' ),
		                            'min_wed'    => esc_attr__( 'We', 'af-locale' ),
		                            'min_thu'    => esc_attr__( 'Th', 'af-locale' ),
		                            'min_fri'    => esc_attr__( 'Fr', 'af-locale' ),
		                            'min_sat'    => esc_attr__( 'Sa', 'af-locale' ),
		                            'january'    => esc_attr__( 'January', 'af-locale' ),
		                            'february'   => esc_attr__( 'February', 'af-locale' ),
		                            'march'      => esc_attr__( 'March', 'af-locale' ),
		                            'april'      => esc_attr__( 'April', 'af-locale' ),
		                            'may'        => esc_attr__( 'May', 'af-locale' ),
		                            'june'       => esc_attr__( 'June', 'af-locale' ),
		                            'july'       => esc_attr__( 'July', 'af-locale' ),
		                            'august'     => esc_attr__( 'August', 'af-locale' ),
		                            'september'  => esc_attr__( 'September', 'af-locale' ),
		                            'october'    => esc_attr__( 'October', 'af-locale' ),
		                            'november'   => esc_attr__( 'November', 'af-locale' ),
		                            'december'   => esc_attr__( 'December', 'af-locale' ), );

		wp_enqueue_script( 'jquery-ui-datepicker' );

		wp_enqueue_script( 'af-datepicker', AF_URLPATH . 'components/restrictions/base-restrictions/includes/js/timerange.js' );
		wp_localize_script( 'af-datepicker', 'translation_admin', $translation_admin );
	}

	/**
	 * Registers and enqueues admin-specific styles.
	 *
	 * @since 1.0.0
	 */
	public static function register_admin_styles()
	{
		wp_enqueue_style( 'af-timerange-styles', AF_URLPATH . 'components/restrictions/base-restrictions/includes/css/timerange.css' );
	}
}

af_register_restriction( 'AF_Restriction_Timerange' );
