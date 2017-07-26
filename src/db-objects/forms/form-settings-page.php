<?php
/**
 * Form settings page class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Forms;

use Leaves_And_Love\Plugin_Lib\Components\Tabbed_Settings_Page;
use Leaves_And_Love\Plugin_Lib\Fields\Field_Manager;
use Leaves_And_Love\Plugin_Lib\Components\Admin_Pages;

/**
 * Class representing the form settings page in the admin.
 *
 * @since 1.0.0
 */
class Form_Settings_Page extends Tabbed_Settings_Page {

	/**
	 * Form manager instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Form_Manager
	 */
	protected $form_manager;

	/**
	 * Array of sub-tabs as `$id => $args` pairs.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $subtabs = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string       $slug         Page slug.
	 * @param Admin_Pages  $manager      Admin page manager instance.
	 * @param Form_Manager $form_manager Form manager instance.
	 */
	public function __construct( $slug, $manager, $form_manager ) {
		$this->slug         = $slug;
		$this->manager      = $manager;
		$this->form_manager = $form_manager;

		$this->title = __( 'Settings', 'torro-forms' );
		$this->menu_title = $this->title;

		$this->capability = 'manage_' . $form_manager->get_prefix() . $form_manager->get_singular_slug() . '_settings';
	}

	/**
	 * Adds a tab to the settings page.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $id   Tab identifier.
	 * @param array  $args {
	 *     Optional. Tab arguments.
	 *
	 *     @type string $title Tab title.
	 * }
	 */
	public function add_tab( $id, $args = array() ) {
		$prefix = $this->manager->get_prefix();

		if ( 0 !== strpos( $id, $prefix ) ) {
			$id = $prefix . $id;
		}

		$this->tabs[ $id ] = wp_parse_args( $args, array(
			'title' => '',
		) );

		$services = array(
			'ajax'          => $this->manager->ajax(),
			'assets'        => $this->manager->assets(),
			'error_handler' => $this->manager->error_handler(),
		);

		$this->tabs[ $id ]['field_manager'] = new Field_Manager( $this->manager->get_prefix(), $services, array(
			'get_value_callback_args'    => array( $id ),
			'update_value_callback_args' => array( $id, '{value}' ),
			'name_prefix'                => $id,
		) );
	}

	/**
	 * Adds a sub-tab to the settings page.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $id   Sub-tab identifier.
	 * @param array  $args {
	 *     Optional. Sub-tab arguments.
	 *
	 *     @type string $title       Sub-tab title.
	 *     @type string $description Sub-tab description. Default empty.
	 *     @type string $tab         Identifier of the tab this sub-tab should belong to.
	 * }
	 */
	public function add_subtab( $id, $args = array() ) {
		if ( ! empty( $args['tab'] ) ) {
			$prefix = $this->manager->get_prefix();

			if ( 0 !== strpos( $args['tab'], $prefix ) ) {
				$args['tab'] = $prefix . $args['tab'];
			}
		}

		$this->subtabs[ $id ] = wp_parse_args( $args, array(
			'title'       => '',
			'description' => '',
			'tab'         => '',
		) );
	}

	/**
	 * Adds a section to the settings page.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $id   Section identifier.
	 * @param array  $args {
	 *     Optional. Section arguments.
	 *
	 *     @type string $title       Section title.
	 *     @type string $description Section description. Default empty.
	 *     @type string $subtab      Identifier of the sub-tab this section should belong to.
	 * }
	 */
	public function add_section( $id, $args = array() ) {
		$this->sections[ $id ] = wp_parse_args( $args, array(
			'title'       => '',
			'description' => '',
			'subtab'      => '',
		) );
	}

	/**
	 * Adds a field to the settings page.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $id      Field identifier.
	 * @param string $type    Identifier of the type.
	 * @param array  $args    {
	 *     Optional. Field arguments. See the field class constructor for further arguments.
	 *
	 *     @type string $section       Section identifier this field belongs to. Default empty.
	 *     @type string $label         Field label. Default empty.
	 *     @type string $description   Field description. Default empty.
	 *     @type mixed  $default       Default value for the field. Default null.
	 *     @type array  $input_classes Array of CSS classes for the field input. Default empty array.
	 *     @type array  $label_classes Array of CSS classes for the field label. Default empty array.
	 *     @type array  $input_attrs   Array of additional input attributes as `$key => $value` pairs.
	 *                                 Default empty array.
	 * }
	 */
	public function add_field( $id, $type, $args = array() ) {
		if ( ! isset( $args['section'] ) ) {
			return;
		}

		if ( ! isset( $this->sections[ $args['section'] ] ) ) {
			return;
		}

		if ( ! isset( $this->subtabs[ $this->sections[ $args['section'] ]['subtab'] ] ) ) {
			return;
		}

		if ( ! isset( $this->tabs[ $this->subtabs[ $this->sections[ $args['section'] ]['subtab'] ]['tab'] ] ) ) {
			return;
		}

		$tab_args = $this->tabs[ $this->subtabs[ $this->sections[ $args['section'] ]['subtab'] ]['tab'] ];
		$tab_args['field_manager']->add( $id, $type, $args );
	}

	/**
	 * Enqueues assets to load on the page.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function enqueue_assets() {
		parent::enqueue_assets();

		$this->manager->assets()->enqueue_script( 'admin-settings' );
		$this->manager->assets()->enqueue_style( 'admin-settings' );
	}

	/**
	 * Registers the settings, tabs, sections and fields for this page in WordPress.
	 *
	 * This method is only meant for internal usage.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function register() {
		$this->add_page_content();

		foreach ( $this->tabs as $id => $tab_args ) {
			register_setting( $id, $id );
			add_filter( "sanitize_option_{$id}", array( $this, 'validate' ), 10, 2 );

			foreach ( $tab_args['field_manager']->get_fields() as $field ) {
				if ( ! isset( $this->sections[ $field->section ] ) ) {
					continue;
				}

				if ( ! isset( $this->subtabs[ $this->sections[ $field->section ]['subtab'] ] ) ) {
					continue;
				}

				$tab_subtab_slug = $this->subtabs[ $this->sections[ $field->section ]['subtab'] ]['tab'] . '_' . $this->sections[ $field->section ]['subtab'];

				add_settings_field( $field->id, $field->label, array( $this, 'render_field' ), $tab_subtab_slug, $field->section, array(
					'label_for'      => $tab_args['field_manager']->make_id( $field->id ),
					'field_instance' => $field,
				) );
			}
		}

		foreach ( $this->sections as $id => $section_args ) {
			if ( ! isset( $this->subtabs[ $section_args['subtab'] ] ) ) {
				continue;
			}

			$tab_subtab_slug = $this->subtabs[ $section_args['subtab'] ]['tab'] . '_' . $section_args['subtab'];

			add_settings_section( $id, $section_args['title'], array( $this, 'render_section_description' ), $tab_subtab_slug );
		}
	}

	/**
	 * Validates field values for an array of fields.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array  $values Array of values.
	 * @param string $option Option name.
	 * @param array  $fields Array of field instances.
	 * @return array Array of validated values.
	 */
	protected function validate_values( $values, $option, $fields ) {
		$old_values = get_option( $option, array() );
		$new_values = parent::validate_values( $values, $option, $fields );

		if ( ! empty( $old_values['slug'] ) && ! empty( $new_values['slug'] ) && $new_values['slug'] !== $old_values['slug'] ) {
			// Deleting this option ensures that rewrite rules are flushed.
			$this->form_manager->options()->delete( 'rewrite_rules' );
		}

		return $new_values;
	}

	/**
	 * Renders the tab navigation.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $current_tab_id Identifier of the current tab.
	 */
	protected function render_tab_navigation( $current_tab_id ) {
		$tabs = array_intersect_key( $this->tabs, array_flip( wp_list_pluck( $this->subtabs, 'tab' ) ) );

		if ( count( $tabs ) > 1 ) : ?>
			<h2 class="nav-tab-wrapper">
				<?php foreach ( $tabs as $tab_id => $tab_args ) : ?>
					<a class="nav-tab<?php echo $tab_id === $current_tab_id ? ' nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( 'tab', $tab_id ); ?>">
						<?php echo $tab_args['title']; ?>
					</a>
				<?php endforeach; ?>
			</h2>
		<?php else : ?>
			<h2 class="screen-reader-text">
				<?php echo $tabs[ $current_tab_id ]['title']; ?>
			</h2>
		<?php endif;
	}

	/**
	 * Renders the settings page form.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $option Option name.
	 */
	protected function render_form( $option ) {
		$current_subtab_id = $this->get_current_subtab( $option );

		?>
		<form action="options.php" method="post" novalidate="novalidate">
			<?php settings_fields( $option ); ?>

			<?php $this->render_form_content( $option, $current_subtab_id ); ?>

			<?php submit_button(); ?>
		</form>
		<?php
	}

	/**
	 * Renders the settings page form content.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $current_tab_id    Identifier of the current tab.
	 * @param string $current_subtab_id Identifier of the current sub-tab.
	 */
	protected function render_form_content( $current_tab_id, $current_subtab_id ) {
		if ( $this->manager->get_prefix() . 'general_settings' === $current_tab_id ) {
			?>
			<div class="welcome-to-torro">
				<h3><?php _e( 'Welcome to Torro Forms!', 'torro-forms' ); ?></h3>
				<p><?php _e( 'You want to build forms in an easy way? Torro Forms will help you do it quickly, yet with tons of options.', 'torro-forms' ); ?></p>
			</div>
			<?php
		}

		$subtabs = wp_list_filter( $this->subtabs, array(
			'tab' => $current_tab_id,
		) );
		if ( empty( $subtabs ) ) {
			return;
		}

		$use_subtabs = count( $subtabs ) > 1;

		?>
		<div class="torro-form-content <?php echo $use_subtabs ? 'tabbed' : 'no-tabs'; ?>">

			<?php if ( $use_subtabs ) : ?>
				<div class="torro-subtab-wrapper" role="tablist">
					<?php foreach ( $subtabs as $subtab_id => $subtab_args ) :
						$url = add_query_arg( array(
							'tab'    => $current_tab_id,
							'subtab' => $subtab_id,
						), $this->url );

						?>
						<a id="<?php echo esc_attr( 'torro-subtab-label-' . $subtab_id ); ?>" class="torro-subtab" href="<?php echo esc_url( $url ); ?>" aria-controls="<?php echo esc_attr( 'torro-subtab-' . $subtab_id ); ?>" aria-selected="<?php echo $subtab_id === $current_subtab_id ? 'true' : 'false'; ?>" role="tab">
							<?php echo $subtab_args['title']; ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="screen-reader-text"><?php echo $subtabs[ $current_subtab_id ]['title']; ?></div>
			<?php endif; ?>

			<?php foreach ( $subtabs as $subtab_id => $subtab_args ) :
				$atts = $use_subtabs ? ' aria-labelledby="' . esc_attr( 'torro-subtab-label-' . $subtab_id ) . '" aria-hidden="' . ( $subtab_id === $current_subtab_id ? 'false' : 'true' ) . '" role="tabpanel"' : '';

				?>
				<div id="<?php echo esc_attr( 'torro-subtab-' . $subtab_id ); ?>" class="torro-subtab-panel"<?php echo $atts; ?>>

					<?php if ( ! empty( $subtab_args['description'] ) ) : ?>
						<p class="description"><?php echo $subtab_args['description']; ?></p>
					<?php endif; ?>

					<?php $this->do_settings_sections( $current_tab_id . '_' . $subtab_id ); ?>

				</div>
			<?php endforeach; ?>

		</div>
		<?php
	}

	/**
	 * Returns the identifier of the current sub-tab.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $current_tab_id Identifier of the current tab.
	 * @return string Identifier of the current sub-tab.
	 */
	protected function get_current_subtab( $current_tab_id ) {
		if ( isset( $_GET['subtab'] ) ) {
			$current_subtab_id = wp_unslash( $_GET['subtab'] );

			if ( isset( $this->subtabs[ $current_subtab_id ] ) && $current_tab_id === $this->subtabs[ $current_subtab_id ]['tab'] ) {
				return $current_subtab_id;
			}
		}

		foreach ( $this->subtabs as $slug => $args ) {
			if ( $current_tab_id === $args['tab'] ) {
				return $slug;
			}
		}

		return key( $this->subtabs );
	}

	/**
	 * Adds tabs, sub-tabs, sections and fields to this page.
	 *
	 * This method should call the methods `add_tab()`, `add_subtab()`, `add_section()` and
	 * `add_field()` to populate the page.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function add_page_content() {
		$tabs = $this->get_tabs();
		foreach ( $tabs as $slug => $args ) {
			$this->add_tab( $slug, $args );
		}

		$subtabs = $this->get_subtabs();
		foreach ( $subtabs as $slug => $args ) {
			$this->add_subtab( $slug, $args );
		}

		$sections = $this->get_sections();
		foreach ( $sections as $slug => $args ) {
			$this->add_section( $slug, $args );
		}

		$fields = $this->get_fields();
		foreach ( $fields as $slug => $args ) {
			$type = 'text';
			if ( isset( $args['type'] ) ) {
				$type = $args['type'];
				unset( $args['type'] );
			}

			$this->add_field( $slug, $type, $args );
		}

		/**
		 * Fires after the form settings page content has been registered.
		 *
		 * @since 1.0.0
		 *
		 * @param awsmug\Torro_Forms\DB_Objects\Forms\Form_Settings_Page $settings_page The settings page instance.
		 */
		do_action( "{$this->manager->get_prefix()}add_settings_content", $this );
	}

	/**
	 * Returns the available settings tabs.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Associative array of `$tab_slug => $tab_args` pairs.
	 */
	protected function get_tabs() {
		$tabs = array(
			'general_settings' => array(
				'title' => _x( 'General', 'form settings', 'torro-forms' ),
			),
			'extension_settings' => array(
				'title' => _x( 'Extensions', 'form settings', 'torro-forms' ),
			),
		);

		/**
		 * Filters the form settings tabs.
		 *
		 * @since 1.0.0
		 *
		 * @param array $tabs Associative array of `$tab_slug => $tab_args` pairs.
		 */
		return apply_filters( "{$this->manager->get_prefix()}settings_tabs", $tabs );
	}

	/**
	 * Returns the available settings sub-tabs.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Associative array of `$subtab_slug => $subtab_args` pairs.
	 */
	protected function get_subtabs() {
		$subtabs = array(
			'general' => array(
				'tab'   => 'general_settings',
				'title' => _x( 'General', 'form settings', 'torro-forms' ),
			),
		);

		/**
		 * Filters the form settings sub-tabs.
		 *
		 * @since 1.0.0
		 *
		 * @param array $tabs Associative array of `$subtab_slug => $subtab_args` pairs.
		 */
		return apply_filters( "{$this->manager->get_prefix()}settings_subtabs", $subtabs );
	}

	/**
	 * Returns the available settings sections.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Associative array of `$section_slug => $section_args` pairs.
	 */
	protected function get_sections() {
		$sections = array(
			'modules'       => array(
				'subtab' => 'general',
				'title'  => _x( 'Modules', 'form settings', 'torro-forms' ),
			),
			'form_behavior' => array(
				'subtab' => 'general',
				'title'  => __( 'Form Behavior', 'torro-forms' ),
			),
			'misc'          => array(
				'subtab' => 'general',
				'title'  => _x( 'Miscellaneous', 'form settings', 'torro-forms' ),
			),
		);

		/**
		 * Filters the form settings sections.
		 *
		 * @since 1.0.0
		 *
		 * @param array $tabs Associative array of `$section_slug => $section_args` pairs.
		 */
		return apply_filters( "{$this->manager->get_prefix()}settings_sections", $sections );
	}

	/**
	 * Returns the available settings fields.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	protected function get_fields() {
		$options = $this->form_manager->options()->get( 'general_settings', array() );

		$modules = array();
		foreach ( torro()->modules()->get_all() as $slug => $module ) {
			$modules[ $slug ] = $module->get_title();
		}
		$default_modules = array_keys( $modules );

		$default_slug = _x( 'forms', 'default form rewrite slug', 'torro-forms' );

		$fields = array(
			'modules'        => array(
				'section'     => 'modules',
				'type'        => 'multibox',
				'label'       => __( 'Active Modules', 'torro-forms' ),
				'description' => __( 'If you do not need all of these modules, you can disable them here.', 'torro-forms' ),
				'choices'     => $modules,
				'default'     => $default_modules,
			),
			'slug'           => array(
				'section'     => 'form_behavior',
				'type'        => 'text',
				'label'       => __( 'Slug', 'torro-forms' ),
				'description' => sprintf( __( 'The slug for permalinks (e.g. for a URL like %s).', 'torro-forms' ), home_url( '/' ) . '<strong id="torro-rewrite-slug-preview">' . ( ! empty( $options['slug'] ) ? $options['slug'] : $default_slug ) . '</strong>/my-contact-form/' ),
				'default'     => $default_slug,
				'required'    => true,
			),
			'frontend_css'   => array(
				'section' => 'misc',
				'type'    => 'checkbox',
				'label'   => __( 'Include Torro Forms CSS on frontend?', 'torro-forms' ),
				'default' => true,
			),
			'hard_uninstall' => array(
				'section'     => 'misc',
				'type'        => 'checkbox',
				'label'       => __( 'Perform a hard uninstall when the plugin is removed?', 'torro-forms' ),
				'description' => __( '<strong>Use this setting with extreme caution</strong> as, when it is enabled, removing the plugin will remove all form content from your site forever.', 'torro-forms' ),
				'default'     => false,
			),
		);

		/**
		 * Filters the form settings fields.
		 *
		 * @since 1.0.0
		 *
		 * @param array $tabs Associative array of `$field_slug => $field_args` pairs.
		 */
		return apply_filters( "{$this->manager->get_prefix()}settings_fields", $fields );
	}
}
