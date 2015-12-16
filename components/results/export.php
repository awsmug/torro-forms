<?php
/**
 * Exporting data
 *
 * This class creates the export
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

if( !defined( 'ABSPATH' ) )
{
	exit;
}

class Torro_Export
{

	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		if( !class_exists( 'PHPExcel' ) )
		{
			require_once( TORRO_FOLDER . 'vendor/PHPExcel.php' );
		}

		add_action( 'admin_init', array( $this, 'export' ), 10 );
		add_filter( 'post_row_actions', array( $this, 'add_export_link' ), 10, 2 );
	}

	/**
	 * Hooks in and adds export link to the overview page
	 *
	 * @param array  $actions
	 * @param object $post
	 * @return array $actions
	 * @since 1.0.0
	 */
	public function add_export_link( $actions, $post )
	{
		if( 'af-forms' != $post->post_type )
		{
			return $actions;
		}

		$results = new Torro_Form_Results( $post->ID );
		$results->results();

		if( 0 == $results->count() )
		{
			$actions[ 'no_export' ] = sprintf( __( 'There are no results to export', 'af-locale' ) );
		}
		else
		{
			$actions[ 'export' ] = sprintf( __( 'Export as <a href="%s">XLS</a> | <a href="%s">CSV</a>', 'af-locale' ), '?post_type=af-forms&af_export=xls&form_id=' . $post->ID, '?post_type=af-forms&export=csv&form_id=' . $post->ID );
		}

		return $actions;
	}

	/**
	 * Start exporting by evaluating $_GET variables
	 *
	 * @since 1.0.0
	 */
	function export()
	{
		if( array_key_exists( 'af_export', $_GET ) && is_array( $_GET ) )
		{
			$export_type = $_GET[ 'af_export' ];
			$form_id = $_GET[ 'form_id' ];

			$form = new Torro_Form( $form_id );
			$form_results = new Torro_Form_Results( $form_id );

			$filename = sanitize_title( $form->title );
			$results = $form_results->results( array( 'column_name' => 'label' ) );

			do_action( 'af_export', $form_id, $filename );

			switch ( $export_type )
			{
				case 'csv':
					$this->csv( $results, $filename );
					break;

				case 'xls':
					$this->excel( $results, $filename );
					break;

				default:

					$this->excel( $results, $filename );
					break;
			}
			exit;
		}
	}

	/**
	 * Serving Download for Excel export
	 *
	 * @param $results
	 * @param $filename
	 * @since 1.0.0
	 */
	public function excel( $results, $filename )
	{
		// Redirect output to a client’s web browser (Excel5)
		header( 'Content-Type: application/vnd.ms-excel' );
		header( 'Content-Disposition: attachment;filename="' . $filename . '.xls"' );
		header( 'Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header( 'Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' ); // Date in the past
		header( 'Last-Modified: '.gmdate('D, d M Y H:i:s' ).' GMT' ); // always modified
		header( 'Cache-Control: cache, must-revalidate' ); // HTTP/1.1
		header( 'Pragma: public'); // HTTP/1.0

		$php_excel = new PHPExcel();

		// Setting up Healines
		$i = 0;
		foreach( array_keys( $results[ 0 ] ) AS $headline )
		{
			$php_excel->setActiveSheetIndex(0)->setCellValueByColumnAndRow( $i++, 1, $headline );
		}

		// Setting up Content
		$php_excel->getActiveSheet()->fromArray( $results, NULL, 'A2' );
		$writer = PHPExcel_IOFactory::createWriter( $php_excel, 'Excel5' );
		$writer->save('php://output');
		exit;
	}

	/**
	 * Serving Download for CSV export
	 *
	 * @param $results
	 * @param $filename
	 * @since 1.0.0
	 */
	public function csv( $results, $filename )
	{
		header( 'Content-type: text/csv' );
		header( 'Content-Disposition: attachment; filename=' . $filename . '.csv');
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$php_excel = new PHPExcel();

		// Setting up Healines
		$i = 0;
		foreach( array_keys( $results[ 0 ] ) AS $headline )
		{
			$php_excel->setActiveSheetIndex(0)->setCellValueByColumnAndRow( $i++, 1, $headline );
		}

		$php_excel->getActiveSheet()->fromArray( $results, NULL, 'A2' );
		$writer = PHPExcel_IOFactory::createWriter( $php_excel, 'CSV' )->setDelimiter( ';' )
			->setEnclosure( '"' )
			->setLineEnding( "\r\n" )
			->setSheetIndex( 0 );

		$writer->save('php://output');
		exit;
	}
}
$Torro_Export = new Torro_Export();
