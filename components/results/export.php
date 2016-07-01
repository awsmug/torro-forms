<?php
/**
 * Components: Torro_Export class
 *
 * @package TorroForms
 * @subpackage Components
 * @version 1.0.0-beta.6
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Torro_Export {
	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( ! class_exists( 'PHPExcel' ) ) {
			require_once( torro()->get_path( 'vendor/phpoffice/phpexcel/Classes/PHPExcel.php' ) );
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
	public function add_export_link( $actions, $post ) {
		if ( 'torro_form' !== $post->post_type || 'trash' === $post->post_status ) {
			return $actions;
		}

		$results_count = torro()->results()->query( array(
			'number'	=> -1,
			'count'		=> true,
			'form_id'	=> $post->ID,
		) );

		if ( 0 === $results_count ) {
			$actions['no_export'] = sprintf( __( 'There are no results to export', 'torro-forms' ) );
		} else {
			$actions['export'] = sprintf( __( 'Export as <a href="%s">XLS</a> | <a href="%s">CSV</a>', 'torro-forms' ), '?post_type=torro_form&torro_export=xls&form_id=' . $post->ID, '?post_type=torro_form&torro_export=csv&form_id=' . $post->ID );
		}

		return $actions;
	}

	/**
	 * Start exporting by evaluating $_GET variables
	 *
	 * @since 1.0.0
	 */
	function export() {
		if ( array_key_exists( 'torro_export', $_GET ) && is_array( $_GET ) ) {
			$export_type = $_GET['torro_export'];
			$form_id = absint( $_GET['form_id'] );

			$form = torro()->forms()->get( $form_id );
			$results = torro()->resulthandlers()->get_registered( 'entries' )->parse_results_for_export( $form_id, 0, -1, 'export', true );

			$filename = sanitize_title( $form->title );

			do_action( 'torro_export', $form_id, $filename );

			switch ( $export_type ) {
				case 'csv':
					$this->csv( $results, $filename );
					break;
				case 'xls':
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
	public function excel( $results, $filename ) {
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

		// Setting up Headlines
		$i = 0;
		foreach ( array_keys( $results[0] ) as $headline ) {
			$php_excel->setActiveSheetIndex(0)->setCellValueByColumnAndRow( $i++, 1, $headline );
		}

		// Setting up Content
		$php_excel->getActiveSheet()->fromArray( $results, null, 'A2' );
		$writer = PHPExcel_IOFactory::createWriter( $php_excel, 'Excel5' );
		$writer->save( 'php://output' );
		exit;
	}

	/**
	 * Serving Download for CSV export
	 *
	 * @param $results
	 * @param $filename
	 * @since 1.0.0
	 */
	public function csv( $results, $filename ) {
		header( 'Content-type: text/csv' );
		header( 'Content-Disposition: attachment; filename=' . $filename . '.csv');
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$php_excel = new PHPExcel();

		// Setting up Headlines
		$i = 0;
		foreach ( array_keys( $results[0] ) as $headline ) {
			$php_excel->setActiveSheetIndex(0)->setCellValueByColumnAndRow( $i++, 1, $headline );
		}

		$php_excel->getActiveSheet()->fromArray( $results, null, 'A2' );
		$writer = PHPExcel_IOFactory::createWriter( $php_excel, 'CSV' )->setDelimiter( ';' )
			->setEnclosure( '"' )
			->setLineEnding( "\r\n" )
			->setSheetIndex( 0 );

		$writer->save( 'php://output' );
		exit;
	}

	private function parse_results( $results ) {
		$parsed = array();
	}
}

$Torro_Export = new Torro_Export();
