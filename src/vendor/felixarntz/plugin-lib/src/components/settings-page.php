<?php
/**
 * Settings page class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Components;

use Leaves_And_Love\Plugin_Lib\Fields\Field_Manager;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Components\Settings_Page' ) ) :

	/**
	 * Class for a settings page
	 *
	 * This class represents a settings menu page in the admin.
	 *
	 * @since 1.0.0
	 */
	abstract class Settings_Page extends Admin_Page {
		/**
		 * Page description.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $description = '';

		/**
		 * Array of sections as `$id => $args` pairs.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $sections = array();

		/**
		 * Array of current values.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $current_values = array();

		/**
		 * Field manager instance.
		 *
		 * @since 1.0.0
		 * @var Field_Manager
		 */
		protected $field_manager = null;

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

			$services = array(
				'ajax'          => $this->manager->ajax(),
				'assets'        => $this->manager->assets(),
				'error_handler' => $this->manager->error_handler(),
			);

			$manager_args = array(
				'get_value_callback_args'    => array( $this->slug ),
				'update_value_callback_args' => array( $this->slug, '{value}' ),
				'name_prefix'                => $this->slug,
			);

			$this->field_manager = new Field_Manager( $this->manager->get_prefix(), $services, $manager_args );
		}

		/**
		 * Handles a request to the page.
		 *
		 * @since 1.0.0
		 */
		public function handle_request() {
			if ( ! $this->current_user_can() ) {
				wp_die( __( 'Cheatin&#8217; uh?' ), '', 403 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
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
		 * }
		 */
		public function add_section( $id, $args = array() ) {
			$this->sections[ $id ] = wp_parse_args(
				$args,
				array(
					'title'       => '',
					'description' => '',
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
			$this->field_manager->add( $id, $type, $args );
		}

		/**
		 * Enqueues assets to load on the page.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_assets() {
			$this->field_manager->enqueue();
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

			$this->current_values = $this->field_manager->get_values();

			?>
			<div class="wrap">
				<?php $this->render_header(); ?>

				<?php $this->render_form( $this->slug ); ?>
			</div>
			<?php
		}

		/**
		 * Registers the setting, sections and fields for this page in WordPress.
		 *
		 * This method is only meant for internal usage.
		 *
		 * @since 1.0.0
		 */
		public function register() {
			$this->add_page_content();

			register_setting( $this->slug, $this->slug );
			add_filter( "sanitize_option_{$this->slug}", array( $this, 'validate' ), 10, 2 );

			foreach ( $this->sections as $id => $section_args ) {
				add_settings_section( $id, $section_args['title'], array( $this, 'render_section_description' ), $this->slug );
			}

			foreach ( $this->field_manager->get_fields() as $id => $field ) {
				add_settings_field(
					$id,
					$field->label,
					array( $this, 'render_field' ),
					$this->slug,
					$field->section,
					array(
						'label_for'      => $this->field_manager->make_id( $id ),
						'field_instance' => $field,
					)
				);
			}
		}

		/**
		 * Validates the settings for the page.
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
			if ( $this->slug !== $option ) {
				return null;
			}

			return $this->validate_values( $values, $option, $this->field_manager->get_fields() );
		}

		/**
		 * Renders a section description.
		 *
		 * This method is only meant for internal usage.
		 *
		 * @since 1.0.0
		 *
		 * @param array $section_args Array of section arguments.
		 */
		public function render_section_description( $section_args ) {
			if ( ! isset( $this->sections[ $section_args['id'] ] ) ) {
				return;
			}

			if ( empty( $this->sections[ $section_args['id'] ]['description'] ) ) {
				return;
			}

			?>
			<p class="description">
				<?php echo wp_kses_data( $this->sections[ $section_args['id'] ]['description'] ); ?>
			</p>
			<?php
		}

		/**
		 * Renders a field.
		 *
		 * This method is only meant for internal usage.
		 *
		 * @since 1.0.0
		 *
		 * @param array $field_args Array of field arguments.
		 */
		public function render_field( $field_args ) {
			$field = $field_args['field_instance'];

			$value = isset( $this->current_values[ $field->id ] ) ? $this->current_values[ $field->id ] : $field->default;

			$field->render_content( $value );
		}

		/**
		 * Renders the settings page header.
		 *
		 * @since 1.0.0
		 */
		protected function render_header() {
			?>
			<h1>
				<?php echo wp_kses_data( $this->title ); ?>
			</h1>

			<?php if ( ! empty( $this->description ) ) : ?>
				<p class="description">
					<?php echo wp_kses_data( $this->description ); ?>
				</p>
			<?php endif; ?>
			<?php
		}

		/**
		 * Renders the settings page form.
		 *
		 * @since 1.0.0
		 *
		 * @param string $option Option name.
		 */
		protected function render_form( $option ) {
			?>
			<form action="options.php" method="post" novalidate="novalidate">
				<?php settings_fields( $option ); ?>
				<?php $this->do_settings_sections( $option ); ?>
				<?php submit_button(); ?>
			</form>
			<?php
		}

		/**
		 * Validates field values for an array of fields.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $values Array of values.
		 * @param string $option Option name.
		 * @param array  $fields Array of field instances.
		 * @return array Array of validated values.
		 */
		protected function validate_values( $values, $option, $fields ) {
			$validated_values = get_option( $option, array() );

			foreach ( $fields as $id => $field ) {
				$value = isset( $values[ $id ] ) ? $values[ $id ] : null;

				$validated_value = $field->validate( $value );
				if ( is_wp_error( $validated_value ) ) {
					add_settings_error( $option, $validated_value->get_error_code(), $validated_value->get_error_message(), 'error' );
					continue;
				}

				$validated_values[ $id ] = $validated_value;
			}

			return $validated_values;
		}

		/**
		 * Renders settings sections.
		 *
		 * This is a copy of the `do_settings_sections()` WordPress function, which is
		 * used to call the custom `do_settings_fields()` implementation.
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
					echo '<h2>' . wp_kses_data( $section['title'] ) . '</h2>';
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

		/**
		 * Renders settings fields.
		 *
		 * This is a copy of the `do_settings_fields()` WordPress function, which is
		 * used to print additional attributes in the `<tr>` wrapper of each field.
		 *
		 * @since 1.0.0
		 *
		 * @global $wp_settings_fields Storage array of settings fields and their pages/sections
		 *
		 * @param string $page    Slug title of the admin page who's settings fields should be shown.
		 * @param string $section Slug title of the settings section who's fields should be shown.
		 */
		protected function do_settings_fields( $page, $section ) {
			global $wp_settings_fields;

			if ( ! isset( $wp_settings_fields[ $page ][ $section ] ) ) {
				return;
			}

			foreach ( (array) $wp_settings_fields[ $page ][ $section ] as $field ) {
				$class = '';

				if ( ! empty( $field['args']['field_instance'] ) ) {
					$class = $field['args']['field_instance']->get_wrap_attrs();
				} elseif ( ! empty( $field['args']['class'] ) ) {
					$class = ' class="' . esc_attr( $field['args']['class'] ) . '"';
				}

				echo "<tr{$class}>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				if ( ! empty( $field['args']['field_instance'] ) ) {
					echo '<th scope="row">';
					$field['args']['field_instance']->render_label();
					echo '</th>';
				} elseif ( ! empty( $field['args']['label_for'] ) ) {
					echo '<th scope="row"><label for="' . esc_attr( $field['args']['label_for'] ) . '">' . wp_kses_data( $field['title'] ) . '</label></th>';
				} else {
					echo '<th scope="row">' . wp_kses_data( $field['title'] ) . '</th>';
				}

				echo '<td>';
				call_user_func( $field['callback'], $field['args'] );
				echo '</td>';
				echo '</tr>';
			}
		}

		/**
		 * Adds sections and fields to this page.
		 *
		 * This method should call the methods `add_section()` and `add_field()` to populate the page.
		 *
		 * @since 1.0.0
		 */
		abstract protected function add_page_content();
	}

endif;
