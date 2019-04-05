<?php
/**
 * Autocomplete field class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Fields;

use WP_Error;
use WP_REST_Request;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Fields\Autocomplete' ) ) :

	/**
	 * Class for an autocomplete field.
	 *
	 * @since 1.0.0
	 */
	class Autocomplete extends Text_Base {
		/**
		 * Field type identifier.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $slug = 'autocomplete';

		/**
		 * Backbone view class name to use for this field.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $backbone_view = 'AutocompleteFieldView';

		/**
		 * Type attribute for the input.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $type = 'text';

		/**
		 * Autocomplete arguments.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $autocomplete = array();

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
		 *     @type mixed           $default       Default value for the field. Default null.
		 *     @type bool|int        $repeatable    Whether this should be a repeatable field. An integer can also
		 *                                          be passed to set the limit of repetitions allowed. Default false.
		 *     @type array           $input_classes Array of CSS classes for the field input. Default empty array.
		 *     @type array           $label_classes Array of CSS classes for the field label. Default empty array.
		 *     @type callable        $validate      Custom validation callback. Will be executed after doing the regular
		 *                                          validation if no errors occurred in the meantime. Default none.
		 *     @type callable|string $before        Callback or string that should be used to generate output that will
		 *                                          be printed before the field. Default none.
		 *     @type callable|string $after         Callback or string that should be used to generate output that will
		 *                                          be printed after the field. Default none.
		 * }
		 */
		public function __construct( $manager, $id, $args = array() ) {
			if ( ! isset( $args['autocomplete'] ) ) {
				$args['autocomplete'] = array();
			}

			$args['autocomplete'] = wp_parse_args(
				$args['autocomplete'],
				array(
					'rest_placeholder_search_route' => 'wp/v2/posts?search=%search%',
					'rest_placeholder_label_route'  => 'wp/v2/posts/%value%',
					'value_generator'               => '%id%',
					'label_generator'               => '%title.rendered%',
				)
			);

			$args['autocomplete']['rest_placeholder_search_route'] = ltrim( $args['autocomplete']['rest_placeholder_search_route'], '/' );
			$args['autocomplete']['rest_placeholder_label_route']  = ltrim( $args['autocomplete']['rest_placeholder_label_route'], '/' );

			parent::__construct( $manager, $id, $args );
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
			$ret = parent::enqueue();

			$ret[0][]            = 'jquery-ui-autocomplete';
			$ret[1]['restUrl']   = rest_url( '/' );
			$ret[1]['restNonce'] = wp_create_nonce( 'wp_rest' );

			return $ret;
		}

		/**
		 * Renders a single input for the field.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $current_value Current field value.
		 */
		protected function render_single_input( $current_value ) {
			$current_label = '';

			if ( ! empty( $current_value ) && ! empty( $this->autocomplete['rest_placeholder_label_route'] ) ) {
				$rest_url = rest_url( str_replace( '%value%', $current_value, $this->autocomplete['rest_placeholder_label_route'] ) );
				$request  = WP_REST_Request::from_url( $rest_url );
				if ( $request ) {
					$response = rest_do_request( $request );
					if ( ! is_wp_error( $response ) ) {
						$current_label = $this->replace_placeholders_with_data( $this->autocomplete['label_generator'], $response->get_data() );
					}
				}
			}

			$hidden_attrs = array(
				'type'  => 'hidden',
				'name'  => $this->get_name_attribute(),
				'value' => $current_value,
			);
			?>
			<input<?php echo $this->attrs( $hidden_attrs ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>>
			<?php
			parent::render_single_input( $current_label );
		}

		/**
		 * Prints a single input template.
		 *
		 * @since 1.0.0
		 */
		protected function print_single_input_template() {
			?>
			<input type="hidden" name="{{ data.name }}" value="{{ data.currentValue }}">
			<?php
			ob_start();
			parent::print_single_input_template();
			echo str_replace( '"{{ data.currentValue }}"', '"{{ data.currentLabel }}"', ob_get_clean() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
			$data = parent::single_to_json( $current_value );

			$data['autocomplete'] = array(
				'restPlaceholderSearchRoute' => $this->autocomplete['rest_placeholder_search_route'],
				'restPlaceholderLabelRoute'  => $this->autocomplete['rest_placeholder_label_route'],
				'valueGenerator'             => $this->autocomplete['value_generator'],
				'labelGenerator'             => $this->autocomplete['label_generator'],
			);

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
			$value = parent::validate_single( $value );
			if ( is_wp_error( $value ) ) {
				return $value;
			}

			if ( ! empty( $value ) ) {
				if ( empty( $this->autocomplete['rest_placeholder_label_route'] ) ) {
					return new WP_Error( 'field_autocomplete_missing_label_route', sprintf( $this->manager->get_message( 'field_autocomplete_missing_label_route' ), $this->label ) );
				}

				$rest_url = rest_url( str_replace( '%value%', $value, $this->autocomplete['rest_placeholder_label_route'] ) );
				$request  = WP_REST_Request::from_url( $rest_url );
				if ( ! $request ) {
					return new WP_Error( 'field_autocomplete_missing_label_route', sprintf( $this->manager->get_message( 'field_autocomplete_missing_label_route' ), $this->label ) );
				}

				$response = rest_do_request( $request );
				if ( is_wp_error( $response ) ) {
					return new WP_Error( 'field_autocomplete_invalid_value', sprintf( $this->manager->get_message( 'field_autocomplete_invalid_value' ), $value, $this->label ) );
				}
			}

			return $value;
		}

		/**
		 * Returns the attributes for the field's input.
		 *
		 * @since 1.0.0
		 *
		 * @param array $input_attrs Array of custom input attributes.
		 * @param bool  $as_string   Optional. Whether to return them as an attribute
		 *                           string. Default true.
		 * @return array|string Either an array of `$key => $value` pairs, or an
		 *                      attribute string if `$as_string` is true.
		 */
		protected function get_input_attrs( $input_attrs = array(), $as_string = true ) {
			$input_attrs = parent::get_input_attrs( $input_attrs, false );
			unset( $input_attrs['name'] );

			if ( $as_string ) {
				return $this->attrs( $input_attrs );
			}

			return $input_attrs;
		}

		/**
		 * Replaces a placeholder string with the actual data referenced in the placeholder fields.
		 *
		 * Placeholders can only be replaced with scalar values.
		 *
		 * @since 1.0.0
		 *
		 * @param string $placeholder_string Placeholder string.
		 * @param array  $data               Associative array of data.
		 * @return string String after the replacements, or an empty string if errors occurred while replacing.
		 */
		protected function replace_placeholders_with_data( $placeholder_string, $data ) {
			$replaced = preg_replace_callback(
				'/\%([A-Za-z0-9_\.]+)\%/',
				function( $matches ) use ( $data ) {
				$field_path = explode( '.', $matches[1] );

				$value = $this->get_data_field( $field_path, $data );
				if ( empty( $value ) || is_array( $value ) ) {
					return $matches[0];
				}

				return $value;
				},
				$placeholder_string
			);

			if ( false !== strpos( $replaced, '%' ) ) {
				return '';
			}

			return $replaced;
		}

		/**
		 * Retrieves the value of a specific field in a possibly nested array.
		 *
		 * @since 1.0.0
		 *
		 * @param array $field_path Path of field names to follow to the value.
		 * @param array $data       Associative array of data.
		 * @return mixed Field value, or null if not found.
		 */
		protected function get_data_field( $field_path, $data ) {
			if ( empty( $field_path ) ) {
				return $data;
			}

			if ( ! is_array( $data ) ) {
				return null;
			}

			$current_field = array_shift( $field_path );

			if ( ! isset( $data[ $current_field ] ) ) {
				return null;
			}

			return $this->get_data_field( $field_path, $data[ $current_field ] );
		}
	}

endif;
