<?php
/**
 * Protector base class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Api;

use awsmug\Torro_Forms\Modules\Submodule;
use awsmug\Torro_Forms\Modules\Meta_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Meta_Submodule_Trait;
use awsmug\Torro_Forms\Modules\Settings_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Settings_Submodule_Trait;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use WP_Error;

/**
 * Base class for an API submodule.
 *
 * @since 1.0.0
 */
abstract class API extends Submodule implements Meta_Submodule_Interface, Settings_Submodule_Interface {
	use Meta_Submodule_Trait, Settings_Submodule_Trait {
		Meta_Submodule_Trait::get_meta_fields as protected _get_meta_fields;
	}

	/**
	 * Checks whether the api is enabled for a specific form.
	 *
	 * @since 1.0.0
	 *
	 * @param Form $form Form object to check.
	 * @return bool True if the api submodule is enabled, false otherwise.
	 */
	public function enabled( $form ) {
		return $this->get_form_option( $form->id, 'enabled', false );
	}

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
}
