<?php
/**
 * Evaluator base class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Evaluators;

use awsmug\Torro_Forms\Modules\Submodule;
use awsmug\Torro_Forms\Modules\Meta_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Meta_Submodule_Trait;
use awsmug\Torro_Forms\Modules\Settings_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Settings_Submodule_Trait;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use WP_Error;

/**
 * Base class for an evaluator.
 *
 * @since 1.0.0
 */
abstract class Evaluator extends Submodule implements Meta_Submodule_Interface, Settings_Submodule_Interface {
	use Meta_Submodule_Trait, Settings_Submodule_Trait {
		Meta_Submodule_Trait::get_meta_fields as protected _get_meta_fields;
	}

	/**
	 * Checks whether the evaluator is enabled for a specific form.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Form $form Form object to check.
	 * @return bool True if the evaluator is enabled, false otherwise.
	 */
	public function enabled( $form ) {
		return $this->get_form_option( $form->id, 'enabled', false );
	}

	/**
	 * Gets aggregate form statistics for the evaluator.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $form_id Form ID.
	 * @return array Array of statistics, or empty array if nothing set yet.
	 */
	public function get_stats( $form_id ) {
		$stats = $this->module->manager()->meta()->get( 'post', $form_id, $this->module->manager()->get_prefix() . 'stats', true );
		if ( ! is_array( $stats ) ) {
			return array();
		}

		$stats_slug = $this->get_meta_identifier();
		if ( ! isset( $stats[ $stats_slug ] ) ) {
			return array();
		}

		return $stats[ $stats_slug ];
	}

	/**
	 * Updates aggregate form statistics for the evaluator.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int   $form_id Form ID.
	 * @param array $data    Array of statistics.
	 * @return bool True on success, false on failure.
	 */
	public function update_stats( $form_id, $data ) {
		$stats = $this->module->manager()->meta()->get( 'post', $form_id, $this->module->manager()->get_prefix() . 'stats', true );
		if ( ! is_array( $stats ) ) {
			$stats = array();
		}

		$stats_slug = $this->get_meta_identifier();

		$stats[ $stats_slug ] = $data;

		return (bool) $this->module->manager()->meta()->update( 'post', $form_id, $this->module->manager()->get_prefix() . 'stats', $stats );
	}

	/**
	 * Evaluates a specific form submission.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Submission $submission Submission to evaluate.
	 * @param Form       $form       Form the submission applies to.
	 * @return bool|WP_Error True on success, error object on failure.
	 */
	public abstract function evaluate( $submission, $form );

	/**
	 * Renders evaluation results for a specific form.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Form  $form Form to show results for.
	 * @param array $args Arguments to tweak the displayed results.
	 */
	public abstract function show_results( $form, $args = array() );

	/**
	 * Returns the available meta fields for the submodule.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_meta_fields() {
		$meta_fields = $this->_get_meta_fields();

		$meta_fields['enabled'] = array(
			'type'       => 'checkbox',
			'label'      => _x( 'Enable?', 'evaluator', 'torro-forms' ),
		);

		return $meta_fields;
	}
}
