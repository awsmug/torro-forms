<?php
/**
 * Access control base class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Frontends;

use awsmug\Torro_Forms\Modules\Submodule;
use awsmug\Torro_Forms\Modules\Meta_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Meta_Submodule_Trait;
use awsmug\Torro_Forms\Modules\Settings_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Settings_Submodule_Trait;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Forms\Form_Frontend_Output_Handler;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;

/**
 * Base class for an access control.
 *
 * @since 1.2.0
 */
abstract class Frontend extends Submodule implements Meta_Submodule_Interface, Settings_Submodule_Interface {
	use Meta_Submodule_Trait, Settings_Submodule_Trait {
		Meta_Submodule_Trait::get_meta_fields as protected _get_meta_fields;
	}

	/**
	 * Renders the content for a given form.
	 *
	 * @since 1.1.0
	 *
	 * @param Form_Frontend_Output_Handler $output_handler Form frontend output handler.
	 * @param Form                         $form           Form object.
	 * @param Submission|null              $submission     Optional. Submission object, or null if none available. Default null.
	 */
	abstract public function render_output( $output_handler, $form, $submission = null );

	/**
	 * Checks whether the access control is enabled for a specific form.
	 *
	 * @since 1.2.0
	 *
	 * @param Form $form Form object to check.
	 * @return bool True if the access control is enabled, false otherwise.
	 */
	public function enabled( $form ) {
		return $this->get_form_option( $form->id, 'enabled', false );
	}

	/**
	 * Returns the available meta fields for the submodule.
	 *
	 * @since 1.2.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_meta_fields() {
		$meta_fields = $this->_get_meta_fields();

		$meta_fields['enabled'] = array(
			'type'         => 'checkbox',
			'label'        => _x( 'Enable?', 'frontend', 'torro-forms' ),
			'visual_label' => _x( 'Status', 'frontend', 'torro-forms' ),
		);

		return $meta_fields;
	}
}
