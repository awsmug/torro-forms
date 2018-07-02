<?php
/**
 * Link Count protector class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Protectors;

use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use WP_Error;
use Exception;

/**
 * Class for a protector using a link count.
 *
 * @since 1.0.0
 */
class Linkcount extends Protector {

	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.0.0
	 */
	protected function bootstrap() {
		$this->slug        = 'linkcount';
		$this->title       = __( 'Link Count', 'torro-forms' );
		$this->description = __( 'Tries to detect bots by the amount of links in the form submission data.', 'torro-forms' );
	}

	/**
	 * Verifies a request by ensuring that it is not spammy.
	 *
	 * @since 1.0.0
	 *
	 * @param array           $data       Submission POST data.
	 * @param Form            $form       Form object.
	 * @param Submission|null $submission Submission object, or null if a new submission.
	 * @return bool|WP_Error True if request is not spammy, false or error object otherwise.
	 */
	public function verify_request( $data, $form, $submission = null ) {
		$trigger = $this->get_form_option( $form->id, 'trigger', 3 );

		foreach ( $data['values'] as $element_id => $fields ) {
			foreach ( $fields as $field_slug => $value ) {
				if ( empty( $value ) ) {
					continue;
				}

				if ( ! is_string( $value ) ) {
					continue;
				}

				preg_match_all( '@https?://@', $value, $matches );

				if ( count( $matches[0] ) < $trigger ) {
					continue;
				}

				return new WP_Error( 'too_many_links', __( 'Your submission contains too many links and was therefore considered spam.', 'torro-forms' ) );
			}
		}

		return true;
	}

	/**
	 * Renders the output for the protector before the Submit button.
	 *
	 * @since 1.0.0
	 *
	 * @param Form $form Form object.
	 */
	public function render_output( $form ) {
		// This does not need any output.
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

		$meta_fields['trigger'] = array(
			'type'         => 'number',
			'label'        => __( 'Link Count Trigger', 'torro-forms' ),
			'description'  => __( 'Specify the maximum number of links a field is allowed to contain before it is considered spam.', 'torro-forms' ),
			'default'      => 3,
			'min'          => 1,
			'step'         => 1,
			'wrap_classes' => array( 'has-torro-tooltip-description' ),
		);

		return $meta_fields;
	}
}
