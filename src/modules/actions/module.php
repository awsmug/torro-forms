<?php
/**
 * Actions module class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Actions;

use awsmug\Torro_Forms\Modules\Module as Module_Base;
use awsmug\Torro_Forms\Modules\Submodule_Registry_Interface;
use awsmug\Torro_Forms\Modules\Submodule_Registry_Trait;
use awsmug\Torro_Forms\Modules\Meta_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Settings_Submodule_Interface;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use awsmug\Torro_Forms\Assets;

/**
 * Class for the Actions module.
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
		$this->slug        = 'actions';
		$this->title       = __( 'Actions', 'torro-forms' );
		$this->description = __( 'Actions are executed in the moment users submit their form data.', 'torro-forms' );

		$this->submodule_base_class = Action::class;
		//TODO: Setup $default_submodules.
	}

	/**
	 * Handles the action for a specific form submission.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param Submission $submission Submission to handle by the action.
	 * @param Form       $form       Form the submission applies to.
	 */
	protected function handle( $submission, $form ) {
		foreach ( $this->submodules as $slug => $action ) {
			if ( ! $action->enabled( $form ) ) {
				continue;
			}

			$action_result = $action->handle( $submission, $form );
			//TODO: Log errors
		}
	}

	/**
	 * Registers the default actions.
	 *
	 * The function also executes a hook that should be used by other developers to register their own actions.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_defaults() {
		foreach ( $this->default_submodules as $slug => $action_class_name ) {
			$this->register( $slug, $action_class_name );
		}

		/**
		 * Fires when the default actions have been registered.
		 *
		 * This action should be used to register custom actions.
		 *
		 * @since 1.0.0
		 *
		 * @param Module $actions Action manager instance.
		 */
		do_action( "{$this->get_prefix()}register_actions", $this );
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

		foreach ( $this->submodules as $slug => $action ) {
			if ( ! is_a( $action, Meta_Submodule_Interface::class ) ) {
				continue;
			}

			$action_meta_identifier = $action->get_meta_identifier();
			$action_meta_fields = $action->get_meta_fields();
			if ( empty( $action_meta_fields ) ) {
				continue;
			}

			$tabs[ $action_meta_identifier ] = array(
				'title'       => $action->get_meta_title(),
				'description' => $action->get_meta_description(),
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

		foreach ( $this->submodules as $slug => $action ) {
			if ( ! is_a( $action, Meta_Submodule_Interface::class ) ) {
				continue;
			}

			$action_meta_identifier = $action->get_meta_identifier();

			$action_meta_fields = $action->get_meta_fields();
			foreach ( $action_meta_fields as $field_slug => $field_data ) {
				$field_slug        = $action_meta_identifier . '__' . $field_slug;
				$field_data['tab'] = $action_meta_identifier;

				if ( isset( $field_data['dependencies'] ) ) {
					for ( $i = 0; $i < count( $field_data['dependencies'] ); $i++ ) {
						if ( isset( $field_data['dependencies'][ $i ]['fields'] ) ) {
							for ( $j = 0; $j < count( $field_data['dependencies'][ $i ]['fields'] ); $j++ ) {
								$field_data['dependencies'][ $i ]['fields'][ $j ] = $action_meta_identifier . '__' . $field_data['dependencies'][ $i ]['fields'][ $j ];
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

		foreach ( $this->submodules as $slug => $action ) {
			if ( ! is_a( $action, Settings_Submodule_Interface::class ) ) {
				continue;
			}

			$action_settings_identifier = $action->get_settings_identifier();
			$action_settings_sections = $action->get_settings_sections();
			if ( empty( $action_settings_sections ) ) {
				continue;
			}

			$subtabs[ $action_settings_identifier ] = array(
				'title' => $action->get_settings_title(),
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

		foreach ( $this->submodules as $slug => $action ) {
			if ( ! is_a( $action, Settings_Submodule_Interface::class ) ) {
				continue;
			}

			$action_settings_identifier = $action->get_settings_identifier();

			$action_settings_sections = $action->get_settings_sections();
			foreach ( $action_settings_sections as $section_slug => $section_data ) {
				$section_data['subtab'] = $action_settings_identifier;

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

		foreach ( $this->submodules as $slug => $action ) {
			if ( ! is_a( $action, Settings_Submodule_Interface::class ) ) {
				continue;
			}

			$fields = array_merge( $fields, $action->get_settings_fields() );
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
		// Empty method body.
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
			'callback' => array( $this, 'handle' ),
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
