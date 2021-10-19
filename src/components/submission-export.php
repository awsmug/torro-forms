<?php
/**
 * Submission export class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Components;

use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission_Collection;

/**
 * Base class for exporting submissions.
 *
 * @since 1.0.0
 */
abstract class Submission_Export {

	/**
	 * Submission export handler instance.
	 *
	 * @since 1.0.0
	 * @var Submission_Export_Handler
	 */
	protected $handler;

	/**
	 * Submission export slug.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $slug = '';

	/**
	 * Submission export title.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $title = '';

	/**
	 * Submission export description.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $description = '';

	/**
	 * Submission export format slug.
	 *
	 * Usually matches the $slug property, but may be different.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $export_format = '';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Submission_Export_Handler $handler Submission export handler instance.
	 */
	public function __construct( $handler ) {
		$this->handler = $handler;

		$this->bootstrap();
	}

	/**
	 * Gets the submission export slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string Submission export slug.
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Gets the submission export title.
	 *
	 * @since 1.0.0
	 *
	 * @return string Submission export title.
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Gets the submission export description.
	 *
	 * @since 1.0.0
	 *
	 * @return string Submission export description.
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Exports submissions for a form.
	 *
	 * @since 1.0.0
	 *
	 * @param Form  $form Form to export submissions for.
	 * @param array $args Optional. Extra query arguments to pass to the submissions
	 *                    query.
	 */
	public function export_submissions( $form, $args = array() ) {
		$elements = $form->get_elements();

		$submission_columns = $this->get_submission_columns( $form );
		$element_columns    = $this->get_element_columns( $elements );

		// Only export completed submissions.
		$args['status'] = 'completed';

		$submissions = $form->get_submissions( $args );

		$columns = $this->get_columns( $submission_columns, $element_columns );
		$rows    = $this->get_rows( $submissions, $submission_columns, $element_columns );

		$this->generate_export_from_data( $columns, $rows, $form );
	}

	/**
	 * Gets all columns for the export.
	 *
	 * @since 1.0.0
	 *
	 * @param array $submission_columns Submission columns definition.
	 * @param array $element_columns    Element columns definition for submission values.
	 * @return array Associative columns array of `$column_slug => $column_label` pairs.
	 */
	protected function get_columns( $submission_columns, $element_columns ) {
		$columns = array();

		foreach ( $submission_columns as $slug => $data ) {
			$columns[ $slug ] = $data['label'];
		}

		foreach ( $element_columns as $element_id => $data ) {
			$columns = array_merge( $columns, $data['columns'] );
		}

		return $columns;
	}

	/**
	 * Gets all rows for the export.
	 *
	 * @since 1.0.0
	 *
	 * @param Submission_Collection $submissions        Submissions to create rows for.
	 * @param array                 $submission_columns Submission columns definition.
	 * @param array                 $element_columns    Element columns definition for submission values.
	 * @return array Rows array where each row is an associative array of `$column_slug => $column_value` pairs.
	 */
	protected function get_rows( $submissions, $submission_columns, $element_columns ) {
		$rows = array();

		foreach ( $submissions as $submission ) {
			$row = array();

			foreach ( $submission_columns as $slug => $data ) {
				$row[ $slug ] = call_user_func( $data['callback'], $submission );
			}

			$element_values = $submission->get_element_values_data();

			foreach ( $element_columns as $element_id => $data ) {
				$values = isset( $element_values[ $element_id ] ) ? $element_values[ $element_id ] : array();

				$column_values = call_user_func( $data['callback'], $values );
				foreach ( $data['columns'] as $slug => $label ) {
					$row[ $slug ] = $column_values[ $slug ];
				}
			}

			$rows[] = $row;
		}

		return $rows;
	}

	/**
	 * Gets submission columns for the export.
	 *
	 * These columns deal with the submission itself, not with individual
	 * submission values.
	 *
	 * @since 1.0.0
	 *
	 * @param Form $form Form for which submissions are being exported.
	 * @return array Associative array of `$column_slug => $column_data` pairs
	 *               where each `$column_data` must be an array containing 'label'
	 *               and 'callback' keys. The callback must accept a submission
	 *               object.
	 */
	protected function get_submission_columns( $form ) {
		$submission_columns = array(
			'id'   => array(
				'label'    => __( 'ID', 'torro-forms' ),
				'callback' => function( $submission ) {
					return $submission->id;
				},
			),
			'user' => array(
				'label'    => __( 'User', 'torro-forms' ),
				'callback' => function( $submission ) {
					if ( ! $submission->user_id ) {
						return __( 'not available', 'torro-forms' );
					}

					$user = get_user_by( 'id', $submission->user_id );
					if ( ! $user || ! $user->exists() ) {
						return __( 'not available', 'torro-forms' );
					}

					return $user->display_name;
				},
			),
			'date' => array(
				'label'    => __( 'Date', 'torro-forms' ),
				'callback' => function( $submission ) {
					/* translators: 1: date format string, 2: time format string */
					$format = sprintf( _x( '%1$s %2$s', 'concatenating date and time format', 'torro-forms' ), get_option( 'date_format' ), get_option( 'time_format' ) );

					/**
					 * Filters the time format for a submission when exporting.
					 *
					 * @since 1.0.4
					 *
					 * @param string $format Date format like with PHP date format (https://secure.php.net/manual/function.date.php).
					 */
					$format = apply_filters( "{$this->handler->get_prefix()}submission_export_time_format", $format );

					return $submission->format_datetime( $format, false );
				},
			),
		);

		/**
		 * Filters the columns to display for a submission when exporting.
		 *
		 * @since 1.0.0
		 *
		 * @param array $submission_columns Associative array of `$column_slug => $column_data` pairs
		 *                                  where `$column_data must be an array with 'label' amd 'callback'
		 *                                  keys. The callback must accept a submission object.
		 * @param Form  $form               Form object for which submissions are being exported.
		 */
		return apply_filters( "{$this->handler->get_prefix()}submission_export_columns", $submission_columns, $form );
	}

	/**
	 * Gets element columns for the export.
	 *
	 * @since 1.0.0
	 *
	 * @param Element_Collection $elements Elements for which to get columns.
	 * @return array Associative array of `$element_id => $element_data` pairs where
	 *               each `$element_data` must be an array with 'columns' and 'callback'
	 *               keys. The callback must accept a $values array of `$field => $value`
	 *               pairs.
	 */
	protected function get_element_columns( $elements ) {
		$element_columns = array();
		foreach ( $elements as $element ) {
			$element_type = $element->get_element_type();
			if ( ! $element_type ) {
				continue;
			}

			$element_columns[ $element->id ] = array(
				'columns'  => $element_type->get_export_columns( $element ),
				'callback' => function( $values ) use ( $element, $element_type ) {
					return $element_type->format_values_for_export( $values, $element, $this->export_format );
				},
			);
		}

		/**
		 * Filters the columns to display for a submission when exporting.
		 *
		 * @since 1.0.8
		 *
		 * @param array $element_columns Associative array of `$element_id => $element_data` pairs where
		 *                               each `$element_data` must be an array with 'columns' and 'callback'
		 *                               keys. The callback must accept a $values array of `$field => $value`
		 *                               pairs.
		 * @param array $elements        Element_Collection $elements Elements for which to get columns.
		 */
		return apply_filters( "{$this->handler->get_prefix()}element_export_columns", $element_columns, $elements );
	}

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
	abstract protected function generate_export_from_data( $columns, $rows, $form );

	/**
	 * Bootstraps the export class by setting properties.
	 *
	 * @since 1.0.0
	 */
	abstract protected function bootstrap();
}
