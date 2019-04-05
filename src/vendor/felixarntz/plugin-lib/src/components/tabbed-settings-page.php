<?php
/**
 * Tabbed settings page class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Components;

use Leaves_And_Love\Plugin_Lib\Fields\Field_Manager;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Components\Tabbed_Settings_Page' ) ) :

	/**
	 * Class for a tabbed settings page
	 *
	 * This class represents a settings menu page with tabs in the admin.
	 *
	 * @since 1.0.0
	 */
	abstract class Tabbed_Settings_Page extends Settings_Page {
		/**
		 * Array of tabs as `$id => $args` pairs.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $tabs = array();

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string      $slug    Page slug.
		 * @param Admin_Pages $manager Admin page manager instance.
		 */
		public function __construct( $slug, $manager ) {
			parent::__construct( $slug, $manager );

			/* The default field manager for the entire page is not required. */
			$this->field_manager = null;
		}

		/**
		 * Adds a tab to the settings page.
		 *
		 * @since 1.0.0
		 *
		 * @param string $id   Tab identifier.
		 * @param array  $args {
		 *     Optional. Tab arguments.
		 *
		 *     @type string $title       tab title.
		 *     @type string $description tab description. Default empty.
		 * }
		 */
		public function add_tab( $id, $args = array() ) {
			$prefix = $this->manager->get_prefix();

			if ( 0 !== strpos( $id, $prefix ) ) {
				$id = $prefix . $id;
			}

			$this->tabs[ $id ] = wp_parse_args(
				$args,
				array(
					'title'       => '',
					'description' => '',
				)
			);

			$services = array(
				'ajax'          => $this->manager->ajax(),
				'assets'        => $this->manager->assets(),
				'error_handler' => $this->manager->error_handler(),
			);

			$manager_args = array(
				'get_value_callback_args'    => array( $id ),
				'update_value_callback_args' => array( $id, '{value}' ),
				'name_prefix'                => $id,
			);

			$this->tabs[ $id ]['field_manager'] = new Field_Manager( $this->manager->get_prefix(), $services, $manager_args );
		}

		/**
		 * Adds a section to the settings page.
		 *
		 * @since 1.0.0
		 *
		 * @param string $id   Section identifier.
		 * @param array  $args {
		 *     Optional. Section arguments.
		 *
		 *     @type string $title       Section title.
		 *     @type string $description Section description. Default empty.
		 *     @type string $tab         Identifier of the tab this section should belong to.
		 * }
		 */
		public function add_section( $id, $args = array() ) {
			if ( ! empty( $args['tab'] ) ) {
				$prefix = $this->manager->get_prefix();

				if ( 0 !== strpos( $args['tab'], $prefix ) ) {
					$args['tab'] = $prefix . $args['tab'];
				}
			}

			$this->sections[ $id ] = wp_parse_args(
				$args,
				array(
					'title'       => '',
					'description' => '',
					'tab'         => '',
				)
			);
		}

		/**
		 * Adds a field to the settings page.
		 *
		 * @since 1.0.0
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

			if ( ! isset( $this->tabs[ $this->sections[ $args['section'] ]['tab'] ] ) ) {
				return;
			}

			$tab_args = $this->tabs[ $this->sections[ $args['section'] ]['tab'] ];
			$tab_args['field_manager']->add( $id, $type, $args );
		}

		/**
		 * Enqueues assets to load on the page.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_assets() {
			if ( empty( $this->tabs ) ) {
				return;
			}

			$current_tab_id = $this->get_current_tab();

			$this->tabs[ $current_tab_id ]['field_manager']->enqueue();
		}

		/**
		 * Renders the settings page content.
		 *
		 * @since 1.0.0
		 *
		 * @global string|null $parent_file Parent file for the current admin page.
		 */
		public function render() {
			global $parent_file;

			if ( 'options-general.php' !== $parent_file ) {
				require ABSPATH . 'wp-admin/options-head.php';
			}

			if ( empty( $this->tabs ) ) {
				?>
				<div class="wrap"><?php $this->render_header(); ?></div>
				<?php
				return;
			}

			$current_tab_id = $this->get_current_tab();

			$this->current_values = $this->tabs[ $current_tab_id ]['field_manager']->get_values();

			?>
			<div class="wrap">
				<?php $this->render_header(); ?>

				<?php $this->render_tab_navigation( $current_tab_id ); ?>

				<?php $this->render_tab_header( $current_tab_id ); ?>

				<?php $this->render_form( $current_tab_id ); ?>
			</div>
			<?php
		}

		/**
		 * Registers the settings, tabs, sections and fields for this page in WordPress.
		 *
		 * This method is only meant for internal usage.
		 *
		 * @since 1.0.0
		 */
		public function register() {
			$this->add_page_content();

			foreach ( $this->tabs as $id => $tab_args ) {
				register_setting( $id, $id );
				add_filter( "sanitize_option_{$id}", array( $this, 'validate' ), 10, 2 );

				foreach ( $tab_args['field_manager']->get_fields() as $field ) {
					add_settings_field(
						$field->id,
						$field->label,
						array( $this, 'render_field' ),
						$id,
						$field->section,
						array(
							'label_for'      => $tab_args['field_manager']->make_id( $field->id ),
							'field_instance' => $field,
						)
					);
				}
			}

			foreach ( $this->sections as $id => $section_args ) {
				add_settings_section( $id, $section_args['title'], array( $this, 'render_section_description' ), $section_args['tab'] );
			}
		}

		/**
		 * Validates the settings for the current tab.
		 *
		 * This method is only meant for internal usage.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $values Array of values.
		 * @param string $option Option name.
		 * @return array Array of validated values.
		 */
		public function validate( $values, $option ) {
			/* Perform a minimal sanity check. */
			if ( ! isset( $this->tabs[ $option ] ) ) {
				return null;
			}

			return $this->validate_values( $values, $option, $this->tabs[ $option ]['field_manager']->get_fields() );
		}

		/**
		 * Renders the tab navigation.
		 *
		 * @since 1.0.0
		 *
		 * @param string $current_tab_id Identifier of the current tab.
		 */
		protected function render_tab_navigation( $current_tab_id ) {
			if ( count( $this->tabs ) > 1 ) {
				?>
				<h2 class="nav-tab-wrapper">
					<?php foreach ( $this->tabs as $tab_id => $tab_args ) : ?>
						<a class="nav-tab<?php echo $tab_id === $current_tab_id ? ' nav-tab-active' : ''; ?>" href="<?php echo esc_attr( add_query_arg( 'tab', $tab_id ) ); ?>">
							<?php echo wp_kses_data( $tab_args['title'] ); ?>
						</a>
					<?php endforeach; ?>
				</h2>
				<?php
			} else {
				?>
				<h2 class="screen-reader-text"><?php echo wp_kses_data( $this->tabs[ $current_tab_id ]['title'] ); ?></h2>
				<?php
			}
		}

		/**
		 * Renders the tab header.
		 *
		 * @since 1.0.0
		 *
		 * @param string $current_tab_id Identifier of the current tab.
		 */
		protected function render_tab_header( $current_tab_id ) {
			if ( ! empty( $this->tabs[ $current_tab_id ]['description'] ) ) {
				?>
				<p class="description">
					<?php echo wp_kses_data( $this->tabs[ $current_tab_id ]['description'] ); ?>
				</p>
				<?php
			}
		}

		/**
		 * Returns the identifier of the current tab.
		 *
		 * @since 1.0.0
		 *
		 * @return string Identifier of the current tab.
		 */
		protected function get_current_tab() {
			$current_tab_id = filter_input( INPUT_GET, 'tab' );
			if ( ! empty( $current_tab_id ) && isset( $this->tabs[ $current_tab_id ] ) ) {
				return $current_tab_id;
			}

			$tab_keys = array_keys( $this->tabs );

			return $tab_keys[0];
		}

		/**
		 * Renders settings sections.
		 *
		 * This is a copy of the `do_settings_sections()` WordPress function, which is
		 * used to call the custom `do_settings_fields()` implementation and to print the heading
		 * as h3 instead of h2 to account for the additional tab navigation.
		 *
		 * @since 1.0.0
		 *
		 * @global $wp_settings_sections Storage array of all settings sections added to admin pages
		 * @global $wp_settings_fields Storage array of settings fields and info about their pages/sections
		 *
		 * @param string $page The slug name of the page whose settings sections should be output.
		 */
		protected function do_settings_sections( $page ) {
			global $wp_settings_sections, $wp_settings_fields;

			if ( ! isset( $wp_settings_sections[ $page ] ) ) {
				return;
			}

			foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
				if ( $section['title'] ) {
					echo '<h3>' . wp_kses_data( $section['title'] ) . '</h3>';
					echo "\n";
				}

				if ( $section['callback'] ) {
					call_user_func( $section['callback'], $section );
				}

				if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
					continue;
				}

				echo '<table class="form-table">';
				$this->do_settings_fields( $page, $section['id'] );
				echo '</table>';
			}
		}
	}

endif;
