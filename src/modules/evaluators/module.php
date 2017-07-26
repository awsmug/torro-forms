<?php
/**
 * Evaluators module class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Evaluators;

use awsmug\Torro_Forms\Modules\Module as Module_Base;
use awsmug\Torro_Forms\Modules\Submodule_Registry_Interface;
use awsmug\Torro_Forms\Modules\Submodule_Registry_Trait;
use awsmug\Torro_Forms\Modules\Meta_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Settings_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Assets_Submodule_Interface;
use awsmug\Torro_Forms\Assets;

/**
 * Class for the Evaluators module.
 *
 * @since 1.0.0
 */
class Module extends Module_Base implements Submodule_Registry_Interface {
	use Submodule_Registry_Trait;

	/**
	 * Bootstraps the module by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function bootstrap() {
		$this->slug        = 'evaluators';
		$this->title       = __( 'Evaluators', 'torro-forms' );
		$this->description = __( 'Evaluators allow evaluating form submissions, for example to generate charts and analytics.', 'torro-forms' );

		$this->submodule_base_class = Evaluator::class;
		// TODO: Setup $default_submodules.
	}

	/**
	 * Evaluates a specific form submission.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param Submission $submission Submission to evaluate.
	 * @param Form       $form       Form the submission applies to.
	 */
	protected function evaluate( $submission, $form ) {
		foreach ( $this->submodules as $slug => $evaluator ) {
			if ( ! $evaluator->enabled( $form ) ) {
				continue;
			}

			$evaluator_result = $evaluator->evaluate( $submission, $form );
			// TODO: Log errors.
		}
	}

	/**
	 * Registers the default evaluators.
	 *
	 * The function also executes a hook that should be used by other developers to register their own evaluators.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_defaults() {
		foreach ( $this->default_submodules as $slug => $evaluator_class_name ) {
			$this->register( $slug, $evaluator_class_name );
		}

		/**
		 * Fires when the default evaluators have been registered.
		 *
		 * This action should be used to register custom evaluators.
		 *
		 * @since 1.0.0
		 *
		 * @param Module $evaluators Form setting manager instance.
		 */
		do_action( "{$this->get_prefix()}register_evaluators", $this );
	}

	/**
	 * Returns the available meta box tabs for the module.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Associative array of `$tab_slug => $tab_args` pairs.
	 */
	protected function get_meta_tabs() {
		$tabs = array();

		foreach ( $this->submodules as $slug => $evaluator ) {
			if ( ! is_a( $evaluator, Meta_Submodule_Interface::class ) ) {
				continue;
			}

			$evaluator_meta_identifier = $evaluator->get_meta_identifier();
			$evaluator_meta_fields = $evaluator->get_meta_fields();
			if ( empty( $evaluator_meta_fields ) ) {
				continue;
			}

			$tabs[ $evaluator_meta_identifier ] = array(
				'title'       => $evaluator->get_meta_title(),
				'description' => $evaluator->get_meta_description(),
			);
		}

		return $tabs;
	}

	/**
	 * Returns the available meta box fields for the module.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	protected function get_meta_fields() {
		$fields = array();

		foreach ( $this->submodules as $slug => $evaluator ) {
			if ( ! is_a( $evaluator, Meta_Submodule_Interface::class ) ) {
				continue;
			}

			$evaluator_meta_identifier = $evaluator->get_meta_identifier();

			$evaluator_meta_fields = $evaluator->get_meta_fields();
			foreach ( $evaluator_meta_fields as $field_slug => $field_data ) {
				$field_slug        = $evaluator_meta_identifier . '__' . $field_slug;
				$field_data['tab'] = $evaluator_meta_identifier;

				if ( isset( $field_data['dependencies'] ) ) {
					$dependency_count = count( $field_data['dependencies'] );

					for ( $i = 0; $i < $dependency_count; $i++ ) {
						if ( isset( $field_data['dependencies'][ $i ]['fields'] ) ) {
							$field_count = count( $field_data['dependencies'][ $i ]['fields'] );

							for ( $j = 0; $j < $field_count; $j++ ) {
								$field_data['dependencies'][ $i ]['fields'][ $j ] = $evaluator_meta_identifier . '__' . $field_data['dependencies'][ $i ]['fields'][ $j ];
							}
						}
					}
				}

				$fields[ $field_slug ] = $field_data;
			}
		}

		return $fields;
	}

	/**
	 * Returns the available settings sub-tabs for the module.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Associative array of `$subtab_slug => $subtab_args` pairs.
	 */
	protected function get_settings_subtabs() {
		$subtabs = array();

		foreach ( $this->submodules as $slug => $evaluator ) {
			if ( ! is_a( $evaluator, Settings_Submodule_Interface::class ) ) {
				continue;
			}

			$evaluator_settings_identifier = $evaluator->get_settings_identifier();
			$evaluator_settings_sections = $evaluator->get_settings_sections();
			if ( empty( $evaluator_settings_sections ) ) {
				continue;
			}

			$subtabs[ $evaluator_settings_identifier ] = array(
				'title' => $evaluator->get_settings_title(),
			);
		}

		return $subtabs;
	}

	/**
	 * Returns the available settings sections for the module.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Associative array of `$section_slug => $section_args` pairs.
	 */
	protected function get_settings_sections() {
		$sections = array();

		foreach ( $this->submodules as $slug => $evaluator ) {
			if ( ! is_a( $evaluator, Settings_Submodule_Interface::class ) ) {
				continue;
			}

			$evaluator_settings_identifier = $evaluator->get_settings_identifier();

			$evaluator_settings_sections = $evaluator->get_settings_sections();
			foreach ( $evaluator_settings_sections as $section_slug => $section_data ) {
				$section_data['subtab'] = $evaluator_settings_identifier;

				$sections[ $section_slug ] = $section_data;
			}
		}

		return $sections;
	}

	/**
	 * Returns the available settings fields for the module.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	protected function get_settings_fields() {
		$fields = array();

		foreach ( $this->submodules as $slug => $evaluator ) {
			if ( ! is_a( $evaluator, Settings_Submodule_Interface::class ) ) {
				continue;
			}

			$fields = array_merge( $fields, $evaluator->get_settings_fields() );
		}

		return $fields;
	}

	/**
	 * Registers the available module scripts and stylesheets.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param Assets $assets Assets API instance.
	 */
	protected function register_assets( $assets ) {
		foreach ( $this->submodules as $slug => $evaluator ) {
			if ( ! is_a( $evaluator, Assets_Submodule_Interface::class ) ) {
				continue;
			}

			$evaluator->register_assets( $assets );
		}
	}

	/**
	 * Enqueues the module's form builder scripts and stylesheets.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param Assets $assets Assets API instance.
	 */
	protected function enqueue_form_builder_assets( $assets ) {
		foreach ( $this->submodules as $slug => $evaluator ) {
			if ( ! is_a( $evaluator, Assets_Submodule_Interface::class ) ) {
				continue;
			}

			$evaluator->enqueue_form_builder_assets( $assets );
		}
	}

	/**
	 * Sets up all action and filter hooks for the service.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function setup_hooks() {
		parent::setup_hooks();

		$this->actions[] = array(
			'name'     => "{$this->get_prefix()}complete_submission",
			'callback' => array( $this, 'evaluate' ),
			'priority' => 10,
			'num_args' => 2,
		);
		$this->actions[] = array(
			'name'     => 'init',
			'callback' => array( $this, 'register_defaults' ),
			'priority' => 100,
			'num_args' => 1,
		);
	}
}
