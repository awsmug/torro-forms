<?php
/**
 * Submission export class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Submissions;

/**
 * Base class for exporting submissions.
 *
 * @since 1.0.0
 */
abstract class Submission_Export {

	public function export_submissions_for_form( $form, $args = array() ) {
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
}
