<?php
/**
 * Submission export class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Components;

use awsmug\Torro_Forms\DB_Objects\Forms\Form;

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
	 * @access protected
	 * @var Submission_Export_Handler
	 */
	protected $handler;

	/**
	 * Submission export slug.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $slug = '';

	/**
	 * Submission export title.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $title = '';

	/**
	 * Submission export description.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $description = '';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct( $handler ) {
		$this->handler = $handler;

		$this->bootstrap();
	}

	/**
	 * Gets the submission export slug.
	 *
	 * @since 1.0.0
	 * @access public
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
	 * @access public
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
	 * @access public
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
	 * @access public
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

		// TODO: Create rows and pass them to the actual export instance.
		$submissions = $form->get_submissions( $args );
	}

	/**
	 * Gets submission columns for the export.
	 *
	 * These columns deal with the submission itself, not with individual
	 * submission values.
	 *
	 * @since 1.0.0
	 * @access protected
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
	 * @access protected
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
					return $element_type->format_values_for_export( $values, $element );
				},
			);
		}

		return $elements;
	}

	/**
	 * Bootstraps the export class by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected abstract function bootstrap();
}
