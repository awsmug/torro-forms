<?php
/**
 * Field_Manager class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Fields;

use Leaves_And_Love\Plugin_Lib\Service;
use Leaves_And_Love\Plugin_Lib\Fields\Interfaces\Field_Manager_Interface;
use Leaves_And_Love\Plugin_Lib\Traits\Container_Service_Trait;
use Leaves_And_Love\Plugin_Lib\Traits\Args_Service_Trait;
use Leaves_And_Love\Plugin_Lib\Translations\Translations_Field_Manager;
use Leaves_And_Love\Plugin_Lib\AJAX;
use Leaves_And_Love\Plugin_Lib\Assets;
use Leaves_And_Love\Plugin_Lib\Error_Handler;
use WP_Error;
use Exception;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Fields\Field_Manager' ) ) :

	/**
	 * Manager class for fields
	 *
	 * @since 1.0.0
	 */
	class Field_Manager extends Service implements Field_Manager_Interface {
		use Container_Service_Trait, Args_Service_Trait;

		/**
		 * Instance ID of this field manager. Used internally.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $instance_id = '';

		/**
		 * Array of fields that are part of this manager, grouped by their `$section`.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $field_instances = array();

		/**
		 * Section lookup map for field identifiers.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $section_lookup = array();

		/**
		 * Array of current values.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $current_values = array();

		/**
		 * Field manager instances.
		 *
		 * @since 1.0.0
		 * @static
		 * @var array
		 */
		protected static $instances = array();

		/**
		 * Instance count of field managers per prefix.
		 *
		 * @since 1.0.0
		 * @static
		 * @var array
		 */
		protected static $prefix_count = array();

		/**
		 * Array of registered field types, as `$type => $class_name` pairs.
		 *
		 * @since 1.0.0
		 * @static
		 * @var array
		 */
		protected static $field_types = array();

		/**
		 * Internal flag whether default types have been registered.
		 *
		 * @since 1.0.0
		 * @static
		 * @var bool
		 */
		protected static $defaults_registered = false;

		/**
		 * Internal flag for whether the current enqueue run is the first one.
		 *
		 * @since 1.0.0
		 * @static
		 * @var array
		 */
		protected static $first_enqueue_run = true;

		/**
		 * Internal flags for enqueueing field assets.
		 *
		 * @since 1.0.0
		 * @static
		 * @var array
		 */
		protected static $enqueued = array();

		/**
		 * Internal flags for JS templates printed.
		 *
		 * @since 1.0.0
		 * @static
		 * @var array
		 */
		protected static $templates_printed = array();

		/**
		 * The AJAX API service definition.
		 *
		 * @since 1.0.0
		 * @static
		 * @var string
		 */
		protected static $service_ajax = AJAX::class;

		/**
		 * The Assets API service definition.
		 *
		 * @since 1.0.0
		 * @static
		 * @var string
		 */
		protected static $service_assets = Assets::class;

		/**
		 * The Assets API service definition for the library itself.
		 *
		 * @since 1.0.0
		 * @static
		 * @var string
		 */
		protected static $service_library_assets = Assets::class;


		/**
		 * Translations to print to the user.
		 *
		 * @since 1.0.0
		 * @static
		 * @var Translations_Field_Manager
		 */
		protected static $translations;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string $prefix   Prefix.
		 * @param array  $services {
		 *     Array of service instances.
		 *
		 *     @type AJAX          $ajax          The AJAX API class instance.
		 *     @type Assets        $assets        The Assets API class instance.
		 *     @type Error_Handler $error_handler The error handler instance.
		 * }
		 * @param array  $args     {
		 *     Array of arguments.
		 *
		 *     @type callable $get_value_callback         Callback to get current values.
		 *     @type array    $get_value_callback_args    Arguments to pass to the `$get_value_callback`.
		 *                                                A placeholder `{id}` can be used to indicate that
		 *                                                this argument should be replaced by the field ID.
		 *     @type callable $update_value_callback      Callback to update the current values with new ones.
		 *     @type array    $update_value_callback_args Arguments to pass to the `$update_value_callback`.
		 *                                                One of these arguments must be a placeholder `{value}`.
		 *                                                Another placeholder `{id}` can also be used to indicate
		 *                                                that this argument should be replaced by the field ID.
		 *     @type string   $name_prefix                The name prefix to create name attributes for fields.
		 *     @type string   $render_mode                Render mode. Default 'form-table'.
		 *     @type string   $field_required_markup      HTML markup to render after a label for a required field.
		 *                                                Default empty string.
		 *     @type bool     $skip_js_initialization     Whether to skip field initialization in JavaScript.
		 *                                                Default false.
		 * }
		 */
		public function __construct( $prefix, $services, $args ) {
			if ( ! isset( $services['library_assets'] ) ) {
				$services['library_assets'] = Assets::get_library_instance();
			}

			$this->set_prefix( $prefix );
			$this->set_services( $services );
			$this->set_args( $args );

			if ( ! isset( self::$prefix_count[ $prefix ] ) ) {
				self::$prefix_count[ $prefix ] = 1;
			} else {
				self::$prefix_count[ $prefix ]++;
			}

			$this->instance_id = $prefix . self::$prefix_count[ $prefix ];

			self::$instances[ $this->instance_id ] = $this;

			self::register_default_field_types();
		}

		/**
		 * Adds a new field.
		 *
		 * @since 1.0.0
		 *
		 * @param string $id   Field identifier. Must be unique for this field manager.
		 * @param string $type Identifier of the type.
		 * @param array  $args Optional. Field arguments. See the field class constructor for supported
		 *                     arguments. Default empty array.
		 * @return bool True on success, false on failure.
		 */
		public function add( $id, $type, $args = array() ) {
			if ( ! self::is_field_type_registered( $type ) ) {
				return false;
			}

			if ( isset( $this->section_lookup[ $id ] ) ) {
				return false;
			}

			$section = isset( $args['section'] ) ? $args['section'] : '';

			$class_name     = self::get_registered_field_type( $type );
			$field_instance = new $class_name( $this, $id, $args );

			$this->section_lookup[ $id ] = $section;

			if ( ! isset( $this->field_instances[ $section ] ) ) {
				$this->field_instances[ $section ] = array();
			}

			$this->field_instances[ $section ][ $id ] = $field_instance;

			return true;
		}

		/**
		 * Gets a specific field.
		 *
		 * @since 1.0.0
		 *
		 * @param string $id Field identifier.
		 * @return Field|null Field instance, or null if it does not exist.
		 */
		public function get( $id ) {
			if ( ! $this->exists( $id ) ) {
				return null;
			}

			return $this->field_instances[ $this->section_lookup[ $id ] ][ $id ];
		}

		/**
		 * Checks whether a specific field exists.
		 *
		 * @since 1.0.0
		 *
		 * @param string $id Field identifier.
		 * @return bool True if the field exists, false otherwise.
		 */
		public function exists( $id ) {
			if ( ! isset( $this->section_lookup[ $id ] ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Removes an existing field.
		 *
		 * @since 1.0.0
		 *
		 * @param string $id Field identifier.
		 * @return bool True on success, false on failure.
		 */
		public function remove( $id ) {
			if ( ! $this->exists( $id ) ) {
				return false;
			}

			unset( $this->field_instances[ $this->section_lookup[ $id ] ][ $id ] );
			unset( $this->section_lookup[ $id ] );

			return true;
		}

		/**
		 * Enqueues the necessary assets for a list of fields.
		 *
		 * @since 1.0.0
		 */
		public function enqueue() {
			if ( ! $this->enqueued( '_core' ) ) {
				$this->library_assets()->register_style(
					'fields',
					'assets/dist/css/fields.css',
					array(
						'ver'     => \Leaves_And_Love_Plugin_Loader::VERSION,
						'enqueue' => true,
					)
				);

				$this->library_assets()->register_script(
					'fields',
					'assets/dist/js/fields.js',
					array(
						'deps'      => array( 'jquery', 'underscore', 'backbone', 'wp-util' ),
						'ver'       => \Leaves_And_Love_Plugin_Loader::VERSION,
						'in_footer' => true,
						'enqueue'   => true,
					)
				);

				$this->enqueued( '_core', true );
			}

			$prefixed_script_handle = str_replace( '_', '-', $this->library_assets()->get_prefix() ) . 'fields';

			$script = wp_scripts()->registered[ $prefixed_script_handle ];

			if ( ! isset( $script->extra['plugin_lib_data'] ) ) {
				$script->extra['plugin_lib_data'] = array(
					'field_managers' => array(),
				);
			}

			if ( ! isset( $script->extra['plugin_lib_templates'] ) ) {
				$script->extra['plugin_lib_templates'] = array();
			}

			$values = $this->get_values();

			$field_instances = $this->get_fields();

			/** This is run to verify there are no circular dependencies. */
			$this->resolve_dependency_order( $field_instances );

			$field_data     = array();
			$type_templates = array();
			foreach ( $field_instances as $id => $field_instance ) {
				$type = $field_instance->slug;

				if ( ! $this->enqueued( $type ) ) {
					list( $new_dependencies, $new_data ) = $field_instance->enqueue();

					if ( ! empty( $new_dependencies ) ) {
						$script->deps = array_merge( $script->deps, $new_dependencies );
					}

					if ( ! empty( $new_data ) ) {
						$script->extra['plugin_lib_data'] = array_merge( $script->extra['plugin_lib_data'], $new_data );
					}

					$this->enqueued( $type, true );
				}

				if ( ! $this->templates_printed( $type ) ) {
					$type_template = array();

					ob_start();
					$field_instance->print_label_template();
					$type_template['label'] = ob_get_clean();

					ob_start();
					$field_instance->print_content_template();
					$type_template['content'] = ob_get_clean();

					ob_start();
					$field_instance->print_repeatable_item_template();
					$type_template['repeatable_item'] = ob_get_clean();

					$type_templates[ $type ] = $type_template;

					$this->templates_printed( $type, true );
				}

				$value = isset( $values[ $id ] ) ? $values[ $id ] : $field_instance->default;

				$field_data[ $id ] = $field_instance->to_json( $value );
			}

			if ( ! empty( $field_data ) ) {
				$script->extra['plugin_lib_data']['field_managers'][ $this->instance_id ] = array(
					'skip_initialization' => $this->skip_js_initialization,
					'fields'              => $field_data,
				);
			}

			if ( ! empty( $type_templates ) ) {
				$script->extra['plugin_lib_templates'] = array_merge( $script->extra['plugin_lib_templates'], $type_templates );
			}

			if ( self::$first_enqueue_run ) {
				$data_hook_name      = is_admin() ? 'admin_enqueue_scripts' : 'wp_enqueue_scripts';
				$templates_hook_name = is_admin() ? 'admin_footer' : 'wp_footer';

				add_action(
					$data_hook_name,
					function() use ( &$script ) {
					$output = 'var pluginLibFieldsAPIData = ' . wp_json_encode( $script->extra['plugin_lib_data'] ) . ';';
					wp_scripts()->add_inline_script( $script->handle, $output, 'before' );
					},
					9999,
					0
				);
				add_action(
					$templates_hook_name,
					function() use ( &$script ) {
					foreach ( $script->extra['plugin_lib_templates'] as $type => $templates ) {
						?>
						<script type="text/html" id="<?php echo esc_attr( 'tmpl-plugin-lib-field-' . $type . '-label' ); ?>">
							<?php echo $templates['label']; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>
						</script>
						<script type="text/html" id="<?php echo esc_attr( 'tmpl-plugin-lib-field-' . $type . '-content' ); ?>">
							<?php echo $templates['content']; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>
						</script>
						<script type="text/html" id="<?php echo esc_attr( 'tmpl-plugin-lib-field-' . $type . '-repeatable-item' ); ?>">
							<?php echo $templates['repeatable_item']; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>
						</script>
						<?php
					}
					},
					1,
					0
				);

				self::$first_enqueue_run = false;
			}
		}

		/**
		 * Checks whether dependencies for a specific type have been enqueued.
		 *
		 * @since 1.0.0
		 *
		 * @param string    $type Type to check for.
		 * @param bool|null $set  Optional. A boolean in case the value should be set. Default null.
		 * @return bool True if the dependencies have been enqueued at the time of calling the function,
		 *              false otherwise.
		 */
		public function enqueued( $type, $set = null ) {
			$result = isset( self::$enqueued[ $type ] ) && self::$enqueued[ $type ];
			if ( null !== $set ) {
				self::$enqueued[ $type ] = (bool) $set;
			}

			return $result;
		}

		/**
		 * Checks whether templates for a specific type have been printed.
		 *
		 * @since 1.0.0
		 *
		 * @param string    $type Type to check for.
		 * @param bool|null $set  Optional. A boolean in case the value should be set. Default null.
		 * @return bool True if the templates have been printed at the time of calling the function,
		 *              false otherwise.
		 */
		public function templates_printed( $type, $set = null ) {
			$result = isset( self::$templates_printed[ $type ] ) && self::$templates_printed[ $type ];
			if ( null !== $set ) {
				self::$templates_printed[ $type ] = (bool) $set;
			}

			return $result;
		}

		/**
		 * Renders a list of fields.
		 *
		 * @since 1.0.0
		 *
		 * @param string|array|null $sections        Optional. Section identifier(s), to only render
		 *                                           fields that belong to this section. Default null.
		 * @param callable|null     $render_callback Optional. Callback to use for rendering a single
		 *                                           field. It will be passed the field instance and
		 *                                           the field's current value. Default is the callback
		 *                                           specified through the class' $render_mode argument.
		 */
		public function render( $sections = null, $render_callback = null ) {
			$field_instances = $this->get_fields( $sections );

			if ( ! $render_callback || ! is_callable( $render_callback ) ) {
				switch ( $this->render_mode ) {
					case 'divs':
						$render_callback = array( $this, 'render_divs_row' );
						break;
					case 'form-table':
					default:
						$render_callback = array( $this, 'render_form_table_row' );
				}
			}

			$values = $this->get_values();

			foreach ( $field_instances as $id => $field_instance ) {
				$value = isset( $values[ $id ] ) ? $values[ $id ] : $field_instance->default;

				call_user_func( $render_callback, $field_instance, $value );
			}
		}

		/**
		 * Gets the current values for all fields of this manager.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of values as `$id => $current_value` pairs.
		 */
		public function get_values() {
			if ( empty( $this->current_values ) ) {
				$field_instances = $this->get_fields();

				$id_key = array_search( '{id}', $this->get_value_callback_args, true );
				if ( false !== $id_key ) {
					$values = array();

					foreach ( $field_instances as $id => $field_instance ) {
						$args            = $this->get_value_callback_args;
						$args[ $id_key ] = $id;

						$values[ $id ] = call_user_func_array( $this->get_value_callback, $args );
					}

					$this->current_values = $values;
				} else {
					$this->current_values = call_user_func_array( $this->get_value_callback, $this->get_value_callback_args );
				}
			}

			return $this->current_values;
		}

		/**
		 * Updates the current values for a list of fields.
		 *
		 * @since 1.0.0
		 *
		 * @param array             $values   New values to validate and store, as `$id => $new_value` pairs.
		 * @param string|array|null $sections Optional. Section identifier(s), to only update values for
		 *                                    fields that belong to this section. Default null.
		 * @return bool|WP_Error True on success, or an error object if some fields produced validation errors.
		 *                       All fields that are not part of this error object have been updated successfully.
		 */
		public function update_values( $values, $sections = null ) {
			$field_instances = $this->get_fields( $sections );
			$field_instances = $this->resolve_dependency_order( $field_instances );

			$validated_values = $this->get_values();

			$errors = new WP_Error();

			$value_key = array_search( '{value}', $this->update_value_callback_args, true );

			$id_key = array_search( '{id}', $this->update_value_callback_args, true );
			if ( false !== $id_key ) {
				foreach ( $field_instances as $id => $field_instance ) {
					$validated_value = $this->validate_value( $field_instance, $values, $errors );
					if ( is_wp_error( $validated_value ) ) {
						continue;
					}

					$this->current_values[ $id ] = $validated_value;

					$args               = $this->update_value_callback_args;
					$args[ $id_key ]    = $id;
					$args[ $value_key ] = $validated_value;

					$update_result = call_user_func_array( $this->update_value_callback, $args );
					$this->process_update_result( $update_result, $errors );
				}
			} else {
				foreach ( $field_instances as $id => $field_instance ) {
					$validated_value = $this->validate_value( $field_instance, $values, $errors );
					if ( is_wp_error( $validated_value ) ) {
						continue;
					}

					$this->current_values[ $id ] = $validated_value;

					$validated_values[ $id ] = $validated_value;
				}

				$args               = $this->update_value_callback_args;
				$args[ $value_key ] = $validated_values;

				$update_result = call_user_func_array( $this->update_value_callback, $args );
				$this->process_update_result( $update_result, $errors );
			}

			if ( ! empty( $errors->errors ) ) {
				return $errors;
			}

			return true;
		}

		/**
		 * Returns an array of fields that are part of this field manager.
		 *
		 * @since 1.0.0
		 *
		 * @param string|array|null $sections Optional. Section identifier(s), to only return
		 *                                    fields that belong to this section. Default null.
		 * @return array Array of fields as `$id => $instance` pairs.
		 */
		public function get_fields( $sections = null ) {
			if ( null !== $sections ) {
				$sections = (array) $sections;
			} else {
				$sections = array_keys( $this->field_instances );
			}

			$all_field_instances = array();
			foreach ( $this->field_instances as $section => $field_instances ) {
				if ( ! in_array( $section, $sections, true ) ) {
					continue;
				}

				$all_field_instances = array_merge( $all_field_instances, $field_instances );
			}

			return $all_field_instances;
		}

		/**
		 * Creates the id attribute for a given field identifier.
		 *
		 * @since 1.0.0
		 *
		 * @param string          $id    Field identifier.
		 * @param int|string|null $index Optional. Index of the field, in case it is a repeatable field.
		 *                               Default null.
		 * @return string Field id attribute.
		 */
		public function make_id( $id, $index = null ) {
			$field_id = str_replace( '_', '-', $id );

			$instance_id = $this->get_instance_id();
			if ( $instance_id ) {
				$field_id = $instance_id . '_' . $field_id;
			}

			if ( null !== $index ) {
				if ( '%index%' === $index ) {
					$field_id .= '-%indexPlus1%';
				} else {
					$field_id .= '-' . ( $index + 1 );
				}
			}

			return $field_id;
		}

		/**
		 * Creates the name attribute for a given field identifier.
		 *
		 * @since 1.0.0
		 *
		 * @param string          $id    Field identifier.
		 * @param int|string|null $index Optional. Index of the field, in case it is a repeatable field.
		 *                               Default null.
		 * @return string Field name attribute.
		 */
		public function make_name( $id, $index = null ) {
			$name_prefix = $this->name_prefix;

			$field_name = $id;
			if ( ! empty( $this->name_prefix ) ) {
				$field_name = $this->name_prefix . '[' . $field_name . ']';
			}

			if ( null !== $index ) {
				$field_name .= '[' . $index . ']';
			}

			return $field_name;
		}

		/**
		 * Gets the HTML markup to indicate that a field is required.
		 *
		 * @since 1.0.0
		 *
		 * @return string HTML markup.
		 */
		public function get_field_required_markup() {
			if ( empty( $this->field_required_markup ) ) {
				return '<em>' . $this->get_message( 'field_required_indicator' ) . '</em>';
			}

			return $this->field_required_markup;
		}

		/**
		 * Returns the ID of this instance.
		 *
		 * @since 1.0.0
		 *
		 * @return string|null Instance ID.
		 */
		public function get_instance_id() {
			return $this->instance_id;
		}

		/**
		 * Returns a specific manager message.
		 *
		 * @since 1.0.0
		 *
		 * @param string $identifier Identifier for the message.
		 * @param bool   $noop       Optional. Whether this is a noop message. Default false.
		 * @return string|array Translated message, or array if $noop, or empty string if
		 *                      invalid identifier.
		 */
		public function get_message( $identifier, $noop = false ) {
			return self::$translations->get( $identifier, $noop );
		}

		/**
		 * Renders a field in form table mode.
		 *
		 * @since 1.0.0
		 *
		 * @param Field $field Field instance.
		 * @param mixed $value Current field value.
		 */
		protected function render_form_table_row( $field, $value ) {
			?>
			<tr<?php echo $field->get_wrap_attrs(); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>>
				<th scope="row">
					<?php $field->render_label(); ?>
				</th>
				<td>
					<?php $field->render_content( $value ); ?>
				</td>
			</tr>
			<?php
		}

		/**
		 * Renders a field in simple div markup with classes.
		 *
		 * @since 1.0.0
		 *
		 * @param Field $field Field instance.
		 * @param mixed $value Current field value.
		 */
		protected function render_divs_row( $field, $value ) {
			?>
			<div<?php echo $field->get_wrap_attrs(); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>>
				<div class="plugin-lib-label-wrap">
					<?php $field->render_label(); ?>
				</div>
				<div class="plugin-lib-control-wrap">
					<?php $field->render_content( $value ); ?>
				</div>
			</div>
			<?php
		}

		/**
		 * Validates a value.
		 *
		 * The $errors object passed will automatically receive any occurring errors.
		 *
		 * @since 1.0.0
		 *
		 * @param Field    $field  Field instance.
		 * @param array    $values Array of all values to validate.
		 * @param WP_Error $errors Error object to possibly fill.
		 * @return mixed|WP_Error Validated value on success, error object on failure.
		 */
		protected function validate_value( $field, $values, $errors ) {
			$value = isset( $values[ $field->id ] ) ? $values[ $field->id ] : null;

			$validated_value = $field->validate( $value );
			if ( is_wp_error( $validated_value ) ) {
				$error      = $validated_value;
				$error_data = $error->get_error_data();
				if ( isset( $error_data['validated'] ) ) {
					$validated_value = $error_data['validated'];
				}

				$errors->add( $error->get_error_code(), $error->get_error_message(), $error->get_error_data() );
			}

			return $validated_value;
		}

		/**
		 * Processes a callback result, merging into an existing WP_Error as necessary.
		 *
		 * @since 1.0.2
		 *
		 * @param mixed    $result Callback result. Is only treated if it is a WP_Error or a boolean.
		 * @param WP_Error $errors Error object to merge errors into.
		 */
		protected function process_update_result( $result, WP_Error $errors ) {
			if ( is_wp_error( $result ) ) {
				foreach ( $result->errors as $error_code => $error_messages ) {
					foreach ( $error_messages as $error_message ) {
						$errors->add( $error_code, $error_message );
					}
				}
				return;
			}

			if ( is_bool( $result ) && ! $result ) {
				$errors->add( 'values_cannot_update', $this->get_message( 'field_cannot_update' ) );
			}
		}

		/**
		 * Sorts field instances by their dependencies so that those can be resolved in the correct order.
		 *
		 * @since 1.0.0
		 *
		 * @param array $field_instances Array of field instances.
		 * @return array Array of field instances sorted by their dependencies.
		 */
		protected function resolve_dependency_order( $field_instances ) {
			$resolved = array();

			foreach ( $field_instances as $id => $field_instance ) {
				$resolved = $this->resolve_dependency_order_for_instance( $field_instance, $field_instances, $resolved, array() );
			}

			return $resolved;
		}

		/**
		 * Recursive helper method for sorting field instances by their dependencies.
		 *
		 * @since 1.0.0
		 *
		 * @param Field $field_instance Field instance to recursively add its dependencies and itself.
		 * @param array $all_instances  All field instances in the collection to sort.
		 * @param array $resolved       Results array to append to.
		 * @param array $queued_ids     Array of field identifiers that are currently queued for appending.
		 *                              This allows to detect circular dependencies.
		 * @return array Modified results array.
		 *
		 * @throws Exception Thrown if circular dependency is detected.
		 */
		protected function resolve_dependency_order_for_instance( $field_instance, $all_instances, $resolved, $queued_ids ) {
			if ( isset( $resolved[ $field_instance->id ] ) ) {
				return $resolved;
			}

			$dependency_resolver = $field_instance->dependency_resolver;
			if ( ! $dependency_resolver ) {
				$resolved[ $field_instance->id ] = $field_instance;
				return $resolved;
			}

			$dependency_ids = $dependency_resolver->get_dependency_field_identifiers();
			if ( empty( $dependency_ids ) ) {
				$resolved[ $field_instance->id ] = $field_instance;
				return $resolved;
			}

			$queued_ids[] = $field_instance->id;

			foreach ( $dependency_ids as $dependency_id ) {
				if ( ! isset( $all_instances[ $dependency_id ] ) ) {
					continue;
				}

				if ( in_array( $dependency_id, $queued_ids, true ) ) {
					throw new Exception( sprintf( 'Circular dependency detected in plugin-lib between fields &#8220;%1$s&#8221; and &#8220;%2$s&#8221;!', $field_instance->id, $dependency_id ) );
				}

				$resolved = $this->resolve_dependency_order_for_instance( $all_instances[ $dependency_id ], $all_instances, $resolved, $queued_ids );
			}

			$resolved[ $field_instance->id ] = $field_instance;

			return $resolved;
		}

		/**
		 * Registers a field type.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param string $type       Identifier of the type.
		 * @param string $class_name Name of the field type class.
		 * @return bool True on success, false on failure.
		 */
		public static function register_field_type( $type, $class_name ) {
			self::register_default_field_types();

			if ( self::is_field_type_registered( $type ) ) {
				return false;
			}

			// Do not allow registration of an existing class as a different type.
			if ( in_array( $class_name, self::$field_types, true ) ) {
				return false;
			}

			self::$field_types[ $type ] = $class_name;

			return true;
		}

		/**
		 * Retrieves the class name for a registered field type.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param string $type Identifier of the type.
		 * @return string Class name, or empty string if the type is not registered.
		 */
		public static function get_registered_field_type( $type ) {
			self::register_default_field_types();

			if ( ! self::is_field_type_registered( $type ) ) {
				return '';
			}

			return self::$field_types[ $type ];
		}

		/**
		 * Checks whether a field type is registered.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param string $type Identifier of the type.
		 * @return bool True if the type is registered, false otherwise.
		 */
		public static function is_field_type_registered( $type ) {
			self::register_default_field_types();

			return isset( self::$field_types[ $type ] );
		}

		/**
		 * Unregisters a field type.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param string $type Identifier of the type.
		 * @return bool True on success, false on failure.
		 */
		public static function unregister_field_type( $type ) {
			self::register_default_field_types();

			if ( ! self::is_field_type_registered( $type ) ) {
				return false;
			}

			unset( self::$field_types[ $type ] );

			return true;
		}

		/**
		 * Registers the default field types.
		 *
		 * @since 1.0.0
		 * @static
		 */
		protected static function register_default_field_types() {
			if ( self::$defaults_registered ) {
				return;
			}

			self::$defaults_registered = true;

			$default_field_types = array(
				'text'         => Text::class,
				'email'        => Email::class,
				'url'          => URL::class,
				'textarea'     => Textarea::class,
				'wysiwyg'      => WYSIWYG::class,
				'number'       => Number::class,
				'range'        => Range::class,
				'checkbox'     => Checkbox::class,
				'select'       => Select::class,
				'multiselect'  => Multiselect::class,
				'radio'        => Radio::class,
				'multibox'     => Multibox::class,
				'autocomplete' => Autocomplete::class,
				'datetime'     => Datetime::class,
				'color'        => Color::class,
				'media'        => Media::class,
				'map'          => Map::class,
				'group'        => Group::class,
			);

			foreach ( $default_field_types as $type => $class_name ) {
				self::register_field_type( $type, $class_name );
			}
		}

		/**
		 * Sets the translations instance.
		 *
		 * @since 1.0.0
		 *
		 * @param Translations_Field_Manager $translations Translations instance.
		 */
		public static function set_translations( $translations ) {
			self::$translations = $translations;
		}

		/**
		 * Gets an option.
		 *
		 * Default callback used for the `$get_value_callback` argument.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param string $id Field identifier.
		 * @return mixed Current value, or null if not set.
		 */
		protected static function get_option( $id ) {
			return get_option( $id, null );
		}

		/**
		 * Updates an option.
		 *
		 * Default callback used for the `$update_value_callback` argument.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param string $id    Field identifier.
		 * @param mixed  $value New value to set.
		 * @return bool True on success, false on failure.
		 */
		protected static function update_option( $id, $value ) {
			return update_option( $id, $value );
		}

		/**
		 * Parses the get value callback.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param mixed $value The input value.
		 * @return callable The parsed value.
		 */
		protected static function parse_arg_get_value_callback( $value ) {
			if ( ! is_callable( $value ) ) {
				return array( __CLASS__, 'get_option' );
			}

			return $value;
		}

		/**
		 * Parses the get value callback args.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param mixed $value The input value.
		 * @return array The parsed value.
		 */
		protected static function parse_arg_get_value_callback_args( $value ) {
			if ( ! is_array( $value ) ) {
				return array( '{id}' );
			}

			return $value;
		}

		/**
		 * Parses the update value callback.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param mixed $value The input value.
		 * @return callable The parsed value.
		 */
		protected static function parse_arg_update_value_callback( $value ) {
			if ( ! is_callable( $value ) ) {
				return array( __CLASS__, 'update_option' );
			}

			return $value;
		}

		/**
		 * Parses the update value callback args.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param mixed $value The input value.
		 * @return array The parsed value.
		 */
		protected static function parse_arg_update_value_callback_args( $value ) {
			if ( ! is_array( $value ) ) {
				return array( '{id}', '{value}' );
			}

			/* A '{value}' element must always be present. This is the worst way to verify it. */
			if ( ! in_array( '{value}', $value, true ) ) {
				$value[] = '{value}';
			}

			return $value;
		}

		/**
		 * Parses the name prefix.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param mixed $value The input value.
		 * @return string The parsed value.
		 */
		protected static function parse_arg_name_prefix( $value ) {
			if ( ! is_string( $value ) ) {
				return '';
			}

			return $value;
		}

		/**
		 * Parses the render mode.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param mixed $value The input value.
		 * @return string The parsed value.
		 */
		protected static function parse_arg_render_mode( $value ) {
			$valid_modes = array( 'form-table' );

			if ( ! is_string( $value ) || ! in_array( $value, $valid_modes, true ) ) {
				return 'form-table';
			}

			return $value;
		}

		/**
		 * Parses the HTML markup to indicate that a field is required.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param  mixed $value The input value.
		 * @return string The parsed value.
		 */
		protected static function parse_arg_field_required_markup( $value ) {
			if ( ! $value ) {
				return '';
			}

			return trim( (string) $value );
		}

		/**
		 * Parses whether JavaScript initialization should be skipped.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param mixed $value The input value.
		 * @return string The parsed value.
		 */
		protected static function parse_arg_skip_js_initialization( $value ) {
			return (bool) $value;
		}
	}

endif;
