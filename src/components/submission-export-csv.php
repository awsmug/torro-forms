<?php
/**
 * Submission export CSV class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Components;

use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Class for exporting submissions in CSV format.
 *
 * @since 1.0.0
 */
class Submission_Export_CSV extends Submission_Export {

	/**
	 * Generates the actual export from given data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns Associative columns array of `$column_slug => $column_label` pairs.
	 * @param array $rows    Rows array where each row is an associative array of
	 *                       `$column_slug => $column_value` pairs.
	 * @param Form  $form    Form for which submissions are being exported.
	 */
	protected function generate_export_from_data( $columns, $rows, $form ) {
		$filename = sanitize_title( $form->title ) . '.csv';

		header( 'Content-type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$spreadsheet = new Spreadsheet();

		$spreadsheet->setActiveSheetIndex( 0 );

		$i = 0;
		foreach ( $columns as $slug => $label ) {
			$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow( $i, 1, $label );
			$i++;
		}

		$spreadsheet->getActiveSheet()->fromArray( $rows, null, 'A2' );

		$writer = IOFactory::createWriter( $spreadsheet, 'Csv' );
		$writer->setDelimiter( ';' );
		$writer->setEnclosure( '"' );
		$writer->setLineEnding( "\r\n" );
		$writer->setSheetIndex( 0 );
		$writer->save( 'php://output' );
		exit;
	}

	/**
	 * Bootstraps the export class by setting properties.
	 *
	 * @since 1.0.0
	 */
	protected function bootstrap() {
		$this->slug          = 'csv';
		$this->title         = _x( 'CSV', 'file extension', 'torro-forms' );
		$this->description   = __( 'Exports submissions in CSV format, raw data to be used for importing into other software.', 'torro-forms' );
		$this->export_format = 'csv';
	}
}
