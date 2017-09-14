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
	public function __construct() {
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

		// TODO.
		$columns = array(
			'id' => array(
				'label'    => __( 'ID', 'torro-forms' ),
				'callback' => null,
			),
		);

		// Only export completed submissions.
		$args['status'] = 'completed';

		$submissions = $form->get_submissions( $args );
	}

	/**
	 * Bootstraps the export class by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected abstract function bootstrap();
}
