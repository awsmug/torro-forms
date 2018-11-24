<?php
/**
 * Group field class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Fields;

use Leaves_And_Love\Plugin_Lib\Fields\Interfaces\Field_Manager_Interface;
use Leaves_And_Love\Plugin_Lib\AJAX;
use Leaves_And_Love\Plugin_Lib\Assets;
use WP_Error;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Fields\Group' ) ) :

	/**
	 * Class for a group field.
	 *
	 * @since 1.0.0
	 */
	class Group extends Field implements Field_Manager_Interface {
		/**
		 * Field type identifier.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $slug = 'group';

		/**
		 * Backbone view class name to use for this field.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $backbone_view = 'GroupFieldView';

		/**
		 * Fields that are part of this group.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $fields = array();

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param Field_Manager $manager Field manager instance.
		 * @param string        $id      Field identifier.
		 * @param array         $args    {
		 *     Optional. Field arguments. Anything you pass in addition to the default supported arguments
		 *     will be used as an attribute on the input. Default empty array.
		 *
		 *     @type string          $section       Section identifier this field belongs to. Default empty.
		 *     @type string          $label         Field label. Default empty.
		 *     @type string          $description   Field description. Default empty.
		 *     @type array           $fields        Sub-fields that should be part of this group. Must be an array where
		 *                                          each key is the identifier of a sub-field and the value is an
		 *                                          array of field arguments, including an additional `type` argument.
		 *                                          Default empty.
		 *     @type bool|int        $repeatable    Whether this should be a repeatable field. An integer can also
		 *                                          be passed to set the limit of repetitions allowed. Default false.
		 *     @type callable        $validate      Custom validation callback. Will be executed after doing the regular
		 *                                          validation if no errors occurred in the meantime. Default none.
		 *     @type callable|string $before        Callback or string that should be used to generate output that will
		 *                                          be printed before the field. Default none.
		 *     @type callable|string $after         Callback or string that should be used to generate output that will
		 *                                          be printed after the field. Default none.
		 * }
		 */
		public function __construct( $manager, $id, $args = array() ) {
			$fields = array();
			if ( isset( $args['fields'] ) ) {
				$fields = $args['fields'];
				unset( $args['fields'] );
			}

			parent::__construct( $manager, $id, $args );

			foreach ( $fields as $field_id => $field_args ) {
				$type = 'text';
				if ( isset( $field_args['type'] ) ) {
					$type = $field_args['type'];
					unset( $field_args['type'] );
				}

				if ( 'group' === $type || ! Field_Manager::is_field_type_registered( $type ) ) {
					continue;
				}

				// Sub-fields have some additional argument restrictions.
				$field_args['repeatable'] = false;
				$field_args['before']     = null;
				$field_args['after']      = null;
				if ( isset( $field_args['dependencies'] ) ) {
					unset( $field_args['dependencies'] );
				}

				$class_name                = Field_Manager::get_registered_field_type( $type );
				$this->fields[ $field_id ] = new $class_name( $this, $field_id, $field_args );
			}

			// The slug must contain the ID to ensure all required templates are printed.
			$this->slug .= '-' . $this->id;

			// Labels are not actual labels, but rather headings for their groups.
			$this->label_mode = 'no_assoc';
		}

		/**
		 * Proxies the manager's AJAX API class instance.
		 *
		 * @since 1.0.0
		 *
		 * @return AJAX AJAX API class instance.
		 */
		public function ajax() {
			return $this->manager->ajax();
		}

		/**
		 * Proxies the manager's Assets API class instance.
		 *
		 * @since 1.0.0
		 *
		 * @return Assets Assets API class instance.
		 */
		public function assets() {
			return $this->manager->assets();
		}

		/**
		 * Proxies the manager's library assets API class instance.
		 *
		 * @since 1.0.0
		 *
		 * @return Assets Library assets API class instance.
		 */
		public function library_assets() {
			return $this->manager->library_assets();
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
			$field_id = $this->manager->make_id( $this->id, $this->index );

			return $field_id . '-' . str_replace( '_', '-', $id );
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
			$field_name = $this->manager->make_name( $this->id, $this->index );

			return $field_name . '[' . $id . ']';
		}

		/**
		 * Gets the HTML markup to indicate that a field is required.
		 *
		 * @since 1.0.0
		 *
		 * @return string HTML markup.
		 */
		public function get_field_required_markup() {
			return $this->manager->get_field_required_markup();
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
			return $this->manager->get_message( $identifier, $noop );
		}

		/**
		 * Enqueues the necessary assets for the field.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array where the first element is an array of script handles and the second element
		 *               is an associative array of data to pass to the main script.
		 */
		public function enqueue() {
			$deps = array();
			$data = array();

			foreach ( $this->fields as $id => $field_instance ) {
				$type = $field_instance->slug;

				if ( ! $this->manager->enqueued( $type ) ) {
					list( $new_dependencies, $new_data ) = $field_instance->enqueue();

					if ( ! empty( $new_dependencies ) ) {
						$deps = array_merge( $deps, $new_dependencies );
					}

					if ( ! empty( $new_data ) ) {
						$data = array_merge( $data, $new_data );
					}

					$this->manager->enqueued( $type, true );
				}
			}

			return array( $deps, $data );
		}

		/**
		 * Renders a single input for the field.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $current_value Current field value.
		 */
		protected function render_single_input( $current_value ) {
			$group_id = $this->get_id_attribute();

			$class = '';
			if ( ! empty( $this->input_classes ) ) {
				$class = ' class="' . esc_attr( implode( ' ', $this->input_classes ) ) . '"';
			}

			?>
			<div id="<?php echo esc_attr( $group_id ); ?>"<?php echo $class; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>>
				<?php $this->render_repeatable_remove_button(); ?>
				<?php foreach ( $this->fields as $id => $field_instance ) : ?>
					<?php
					$partial_value = isset( $current_value[ $id ] ) ? $current_value[ $id ] : $field_instance->default;
					?>
					<div<?php echo $field_instance->get_wrap_attrs(); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>>
						<?php $field_instance->render_label(); ?>
						<?php $field_instance->render_content( $partial_value ); ?>
					</div>
				<?php endforeach; ?>
			</div>
			<?php
		}

		/**
		 * Prints a single input template.
		 *
		 * @since 1.0.0
		 */
		protected function print_single_input_template() {
			?>
			<div id="{{ data.id }}"<# if ( data.inputAttrs.class ) { #> class="{{ data.inputAttrs.class }}"<# } #>>
				<?php $this->print_repeatable_remove_button_template(); ?>
				<?php foreach ( $this->fields as $id => $field_instance ) : ?>
					<# _.alias( data.fields.<?php echo esc_attr( $id ); ?>, function( data ) { #>
						<div{{{ _.attrs( data.wrapAttrs ) }}}>
							<?php $field_instance->print_label_template(); ?>
							<?php $field_instance->print_content_template(); ?>
						</div>
					<# } ) #>
				<?php endforeach; ?>
			</div>
			<?php
		}

		/**
		 * Transforms single field data into an array to be passed to JavaScript applications.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $current_value Current value of the field.
		 * @return array Field data to be JSON-encoded.
		 */
		protected function single_to_json( $current_value ) {
			if ( ! is_array( $current_value ) ) {
				$current_value = (array) $current_value;
			}

			$data           = parent::single_to_json( $current_value );
			$data['fields'] = array();

			foreach ( $this->fields as $id => $field_instance ) {
				$partial_value = is_array( $current_value ) && isset( $current_value[ $id ] ) ? $current_value[ $id ] : $field_instance->default;

				$data['fields'][ $id ] = $field_instance->to_json( $partial_value );
			}

			return $data;
		}

		/**
		 * Validates a single value for the field.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $value Value to validate. When null is passed, the method
		 *                     assumes no value was sent.
		 * @return mixed|WP_Error The validated value on success, or an error
		 *                        object on failure.
		 */
		protected function validate_single( $value = null ) {
			if ( empty( $value ) ) {
				return array();
			}

			$result = array();
			$errors = new WP_Error();

			foreach ( $this->fields as $id => $field_instance ) {
				$partial_value = is_array( $value ) && isset( $value[ $id ] ) ? $value[ $id ] : null;

				$validated_value = $field_instance->validate( $partial_value );
				if ( is_wp_error( $validated_value ) ) {
					$error      = $validated_value;
					$error_data = $error->get_error_data();
					if ( isset( $error_data['validated'] ) ) {
						$result[ $id ] = $error_data['validated'];
					}

					$errors->add( $error->get_error_code(), $error->get_error_message() );
					continue;
				}

				$result[ $id ] = $validated_value;
			}

			if ( ! empty( $errors->errors ) ) {
				if ( ! empty( $result ) ) {
					$main_code                        = $errors->get_error_code();
					$errors->error_data[ $main_code ] = array( 'validated' => $result );
				}

				return $errors;
			}

			return $result;
		}

		/**
		 * Checks whether a value is considered empty.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $value Value to check whether its empty.
		 * @return bool True if the value is considered empty, false otherwise.
		 */
		protected function is_value_empty( $value ) {
			foreach ( $this->fields as $id => $field ) {
				$partial_value = is_array( $value ) && isset( $value[ $id ] ) ? $value[ $id ] : $field->default;

				if ( is_string( $partial_value ) ) {
					$partial_value = trim( $partial_value );
				}

				if ( ! empty( $partial_value ) ) {
					return false;
				}
			}

			return true;
		}
	}

endif;
