<?php
/**
 * Timetrap protector class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Api;

/**
 * Class for a protector using a timetrap field.
 *
 * @since 1.0.0
 */
class Submissions extends API {

	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.0.0
	 */
	protected function bootstrap() {
		$this->slug        = 'submissions';
		$this->title       = __( 'Submissions', 'torro-forms' );
		$this->description = __( 'Adjust the submission part of the API.', 'torro-forms' );
	}

	/**
	 * Returns the available meta fields for the submodule.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_meta_fields() {
		$meta_fields = parent::get_meta_fields();

		$meta_fields['allow_submissions'] = array(
			'type'         => 'radio',
			'label'        => __( 'Allow Rest API submissions.', 'torro-forms' ),
			'description'  => __( 'Opens the Rest API submissions endpoint without user verification.', 'torro-forms' ),
			'choices'      => array(
				'yes' => __( 'Yes', 'torro-forms' ),
				'no'  => __( 'No', 'torro-forms' ),
			),
			'default'      => false,
			'wrap_classes' => array( 'has-torro-tooltip-description' ),
		);

		return $meta_fields;
	}
}
