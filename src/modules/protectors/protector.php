<?php
/**
 * Protector base class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Protectors;

use awsmug\Torro_Forms\Modules\Submodule;
use awsmug\Torro_Forms\Modules\Meta_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Meta_Submodule_Trait;
use awsmug\Torro_Forms\Modules\Settings_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Settings_Submodule_Trait;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use WP_Error;

/**
 * Base class for a protector.
 *
 * @since 1.0.0
 */
abstract class Protector extends Submodule implements Meta_Submodule_Interface, Settings_Submodule_Interface {
	use Meta_Submodule_Trait, Settings_Submodule_Trait {
		Meta_Submodule_Trait::get_meta_fields as protected _get_meta_fields;
	}

	/**
	 * Checks whether the protector is enabled for a specific form.
	 *
	 * @since 1.0.0
	 *
	 * @param Form $form Form object to check.
	 * @return bool True if the protector is enabled, false otherwise.
	 */
	public function enabled( $form ) {
		return $this->get_form_option( $form->id, 'enabled', false );
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
	abstract public function verify_request( $data, $form, $submission = null );

	/**
	 * Renders the output for the protector before the Submit button.
	 *
	 * @since 1.0.0
	 *
	 * @param Form $form Form object.
	 */
	abstract public function render_output( $form );

	/**
	 * Returns the available meta fields for the submodule.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_meta_fields() {
		$meta_fields = $this->_get_meta_fields();

		$meta_fields['enabled'] = array(
			'type'         => 'checkbox',
			'label'        => _x( 'Enable?', 'protector', 'torro-forms' ),
			'visual_label' => _x( 'Status', 'protector', 'torro-forms' ),
		);

		return $meta_fields;
	}

	/**
	 * Wraps a non-prefixed form input name attribute so that it will be properly included the submission POST data.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name_attr Form input name.
	 * @return string Wrapped form input name ready to print on an input element.
	 */
	protected function wrap_form_name( $name_attr ) {
		if ( false === strpos( $name_attr, '[' ) ) {
			return 'torro_submission[' . $name_attr . ']';
		}

		$parts = explode( '[', str_replace( ']', '', $name_attr ) );
		return 'torro_submission[' . implode( '][', $parts ) . ']';
	}
}
