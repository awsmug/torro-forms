<?php
/**
 * Submission export XLS class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Components;

use awsmug\Torro_Forms\DB_Objects\Forms\Form;

/**
 * Class for exporting submissions in XLS format.
 *
 * @since 1.0.0
 */
class Submission_Export_XLS extends Submission_Export {

	/**
	 * Generates the actual export from given data.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $columns Associative columns array of `$column_slug => $column_label` pairs.
	 * @param array $rows    Rows array where each row is an associative array of
	 *                       `$column_slug => $column_value` pairs.
	 * @param Form  $form    Form for which submissions are being exported.
	 */
	protected function generate_export_from_data( $columns, $rows, $form ) {
		$filename = sanitize_title( $form->title ) . '.xls';

		header( 'Content-Type: application/vnd.ms-excel' );
		header( 'Content-Disposition: attachment;filename="' . $filename . '"' );
		header( 'Cache-Control: max-age=0' );
		header( 'Cache-Control: max-age=1' ); // For IE9.
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header( 'Last-Modified: ' . gmdate('D, d M Y H:i:s' ) . ' GMT' );
		header( 'Cache-Control: cache, must-revalidate' ); // For HTTP 1.1.
		header( 'Pragma: public'); // For HTTP 1.0.

		$php_excel = new \PHPExcel();

		$php_excel->setActiveSheetIndex( 0 );

		$i = 0;
		foreach ( $columns as $slug => $label ) {
			$php_excel->getActiveSheet()->setCellValueByColumnAndRow( $i, 1, $label );
			$i++;
		}

		$php_excel->getActiveSheet()->fromArray( $rows, null, 'A2' );

		$writer = \PHPExcel_IOFactory::createWriter( $php_excel, 'Excel5' );
		$writer->save( 'php://output' );
		exit;
	}

	/**
	 * Bootstraps the export class by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function bootstrap() {
		$this->slug        = 'xls';
		$this->title       = _x( 'XLS', 'file extension', 'torro-forms' );
		$this->description = __( 'Exports submissions in XLS format, to be used by table processing software such as Excel.', 'torro-forms' );
	}
}
