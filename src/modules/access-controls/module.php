<?php
/**
 * Access Controls module class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Access_Controls;

use awsmug\Torro_Forms\Modules\Module as Module_Base;
use awsmug\Torro_Forms\Modules\Submodule_Registry_Interface;
use awsmug\Torro_Forms\Modules\Submodule_Registry_Trait;
use awsmug\Torro_Forms\Modules\Meta_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Settings_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Assets_Submodule_Interface;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use awsmug\Torro_Forms\Assets;
use awsmug\Torro_Forms\Error;

/**
 * Class for the Access Controls module.
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
		$this->slug        = 'access_controls';
		$this->title       = __( 'Access Controls', 'torro-forms' );
		$this->description = __( 'Access controls allow to limit who has permissions to view and submit a form.', 'torro-forms' );

		$this->submodule_base_class = Access_Control::class;
		$this->default_submodules = array(
			'author_identification' => Author_Identification::class,
			'timerange'             => Timerange::class,
			'allmembers'            => All_Members::class,
			'submission_count'      => Submission_Count::class,
		);
		// TODO: Setup $default_submodules.
	}

	/**
	 * Determines whether the current user can access a specific form or submission.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param bool|Error      $result     Whether a user can access the form. Can be an error object to show a specific message to the user.
	 * @param Form            $form       Form object.
	 * @param Submission|null $submission Submission object, or null if no submission is set.
	 * @return bool|Error True if the form or submission can be accessed, false or error object otherwise.
	 */
	protected function can_access( $result, $form, $submission = null ) {
		if ( ! $result || is_wp_error( $result ) ) {
			return $result;
		}

		foreach ( $this->submodules as $slug => $access_control ) {
			if ( ! $access_control->enabled( $form ) ) {
				continue;
			}

			$sub_result = $access_control->can_access( $form, $submission );
			if ( ! $sub_result || is_wp_error( $sub_result ) ) {
				return $sub_result;
			}
		}

		return $result;
	}

	/**
	 * Sets additional data for a submission when it is created.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param Submission $submission New submission object.
	 * @param Form       $form       Form object the submission belongs to.
	 * @param array      $data       Submission POST data.
	 */
	protected function set_submission_data( $submission, $form, $data ) {
		foreach ( $this->submodules as $slug => $access_control ) {
			if ( ! is_a( $access_control, Submission_Modifier_Access_Control_Interface::class ) ) {
				continue;
			}

			if ( ! $access_control->enabled( $form ) ) {
				continue;
			}

			$access_control->set_submission_data( $submission, $form, $data );
		}
	}

	/**
	 * Registers the default access controls.
	 *
	 * The function also executes a hook that should be used by other developers to register their own access controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_defaults() {
		foreach ( $this->default_submodules as $slug => $access_control_class_name ) {
			$this->register( $slug, $access_control_class_name );
		}

		/**
		 * Fires when the default access controls have been registered.
		 *
		 * This action should be used to register custom access controls.
		 *
		 * @since 1.0.0
		 *
		 * @param Module $access_controls Form setting manager instance.
		 */
		do_action( "{$this->get_prefix()}register_access_controls", $this );
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

		foreach ( $this->submodules as $slug => $access_control ) {
			if ( ! is_a( $access_control, Meta_Submodule_Interface::class ) ) {
				continue;
			}

			$access_control_meta_identifier = $access_control->get_meta_identifier();
			$access_control_meta_fields = $access_control->get_meta_fields();
			if ( empty( $access_control_meta_fields ) ) {
				continue;
			}

			$tabs[ $access_control_meta_identifier ] = array(
				'title'       => $access_control->get_meta_title(),
				'description' => $access_control->get_meta_description(),
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

		foreach ( $this->submodules as $slug => $access_control ) {
			if ( ! is_a( $access_control, Meta_Submodule_Interface::class ) ) {
				continue;
			}

			$access_control_meta_identifier = $access_control->get_meta_identifier();

			$access_control_meta_fields = $access_control->get_meta_fields();
			foreach ( $access_control_meta_fields as $field_slug => $field_data ) {
				$field_slug        = $access_control_meta_identifier . '__' . $field_slug;
				$field_data['tab'] = $access_control_meta_identifier;

				if ( isset( $field_data['dependencies'] ) ) {
					$dependency_count = count( $field_data['dependencies'] );

					for ( $i = 0; $i < $dependency_count; $i++ ) {
						if ( isset( $field_data['dependencies'][ $i ]['fields'] ) ) {
							$field_count = count( $field_data['dependencies'][ $i ]['fields'] );

							for ( $j = 0; $j < $field_count; $j++ ) {
								$field_data['dependencies'][ $i ]['fields'][ $j ] = $access_control_meta_identifier . '__' . $field_data['dependencies'][ $i ]['fields'][ $j ];
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

		foreach ( $this->submodules as $slug => $access_control ) {
			if ( ! is_a( $access_control, Settings_Submodule_Interface::class ) ) {
				continue;
			}

			$access_control_settings_identifier = $access_control->get_settings_identifier();
			$access_control_settings_sections = $access_control->get_settings_sections();
			if ( empty( $access_control_settings_sections ) ) {
				continue;
			}

			$subtabs[ $access_control_settings_identifier ] = array(
				'title' => $access_control->get_settings_title(),
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

		foreach ( $this->submodules as $slug => $access_control ) {
			if ( ! is_a( $access_control, Settings_Submodule_Interface::class ) ) {
				continue;
			}

			$access_control_settings_identifier = $access_control->get_settings_identifier();

			$access_control_settings_sections = $access_control->get_settings_sections();
			foreach ( $access_control_settings_sections as $section_slug => $section_data ) {
				$section_data['subtab'] = $access_control_settings_identifier;

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

		foreach ( $this->submodules as $slug => $access_control ) {
			if ( ! is_a( $access_control, Settings_Submodule_Interface::class ) ) {
				continue;
			}

			$fields = array_merge( $fields, $access_control->get_settings_fields() );
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
		foreach ( $this->submodules as $slug => $access_control ) {
			if ( ! is_a( $access_control, Assets_Submodule_Interface::class ) ) {
				continue;
			}

			$access_control->register_assets( $assets );
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
		foreach ( $this->submodules as $slug => $access_control ) {
			if ( ! is_a( $access_control, Assets_Submodule_Interface::class ) ) {
				continue;
			}

			$access_control->enqueue_form_builder_assets( $assets );
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

		$this->filters[] = array(
			'name'     => "{$this->get_prefix()}can_access_form",
			'callback' => array( $this, 'can_access' ),
			'priority' => 10,
			'num_args' => 3,
		);
		$this->actions[] = array(
			'name'     => "{$this->get_prefix()}create_new_submission",
			'callback' => array( $this, 'set_submission_data' ),
			'priority' => 10,
			'num_args' => 3,
		);
		$this->actions[] = array(
			'name'     => 'init',
			'callback' => array( $this, 'register_defaults' ),
			'priority' => 100,
			'num_args' => 1,
		);
	}
}
