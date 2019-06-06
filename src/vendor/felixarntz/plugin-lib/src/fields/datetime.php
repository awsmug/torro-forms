<?php
/**
 * Datetime field class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Fields;

use WP_Error;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Fields\Datetime' ) ) :

	/**
	 * Class for a text field.
	 *
	 * @since 1.0.0
	 */
	class Datetime extends Text_Base {
		/**
		 * Field type identifier.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $slug = 'datetime';

		/**
		 * Type attribute for the input.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $type = 'text';

		/**
		 * What type of data to store in the field value.
		 *
		 * Accepts either 'datetime', 'date' or 'time'.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $store = 'datetime';

		/**
		 * Backbone view class name to use for this field.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $backbone_view = 'DatetimeFieldView';

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
			if ( isset( $args['data-store'] ) ) {
				if ( ! isset( $args['store'] ) ) {
					$args['store'] = $args['data-store'];
				} else {
					$args['data-store'] = $args['store'];
				}
			}

			if ( isset( $args['store'] ) && ! in_array( $args['store'], array( 'date', 'time' ), true ) ) {
				$args['store'] = 'datetime';
			}

			if ( ! isset( $args['data-store'] ) && isset( $args['store'] ) ) {
				$args['data-store'] = $args['store'];
			} else {
				$args['data-store'] = $this->store;
			}

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

			$assets = $this->manager->library_assets();

			$datetimepicker_version = '2.5.20';

			$css_path = 'node_modules/jquery-datetimepicker/build/jquery.datetimepicker.min.css';
			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$css_path = 'node_modules/jquery-datetimepicker/jquery.datetimepicker.css';
			}

			$assets->register_style(
				'datetimepicker',
				$css_path,
				array(
					'ver'     => $datetimepicker_version,
					'enqueue' => true,
				)
			);

			$assets->register_script(
				'datetimepicker',
				'node_modules/jquery-datetimepicker/build/jquery.datetimepicker.full.js',
				array(
					'deps'      => array( 'jquery' ),
					'ver'       => $datetimepicker_version,
					'in_footer' => true,
					'enqueue'   => true,
				)
			);

			$ret[0][] = 'datetimepicker';
			$ret[1]   = array_merge(
				$ret[1],
				array(
					'language'       => substr( get_locale(), 0, 2 ),
					'datetimeFormat' => sprintf( $this->manager->get_message( 'field_datetime_format_concat' ), get_option( 'date_format' ), get_option( 'time_format' ) ),
					'dateFormat'     => get_option( 'date_format' ),
					'timeFormat'     => get_option( 'time_format' ),
					'startOfWeek'    => get_option( 'start_of_week' ),
				)
			);

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
			if ( in_array( $current_value, $this->get_emptyish_values(), true ) ) {
				$current_value = '';
			}

			$formatted_value = '';
			if ( ! empty( $current_value ) ) {
				$formatted_value = $this->format( $current_value );
			}

			$hidden_attrs = array(
				'type'  => 'hidden',
				'name'  => $this->get_name_attribute(),
				'value' => $current_value,
			);
			?>
			<input<?php echo $this->attrs( $hidden_attrs ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>>
			<?php
			parent::render_single_input( $formatted_value );
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
			echo str_replace( '"{{ data.currentValue }}"', '"{{ data.formattedValue }}"', ob_get_clean() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
			if ( in_array( $current_value, $this->get_emptyish_values(), true ) ) {
				$current_value = '';
			}

			$formatted_value = '';
			if ( ! empty( $current_value ) ) {
				$formatted_value = $this->format( $current_value );
			}

			$data                   = parent::single_to_json( $current_value );
			$data['formattedValue'] = $formatted_value;
			$data['store']          = $this->store;

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
			if ( in_array( $value, $this->get_emptyish_values(), true ) ) {
				$value = '';
			}

			$value = parent::validate_single( $value );
			if ( is_wp_error( $value ) ) {
				return $value;
			}

			if ( empty( $value ) ) {
				return '';
			}

			$timestamp = $this->parse_as_timestamp( $value );
			$value     = $this->parse( $timestamp );

			if ( ! empty( $this->input_attrs['min'] ) ) {
				$timestamp_min = $this->parse_as_timestamp( $this->input_attrs['min'] );
				if ( $timestamp < $timestamp_min ) {
					return new WP_Error( 'field_datetime_lower_than', sprintf( $this->manager->get_message( 'field_datetime_lower_than' ), $this->format( $timestamp ), $this->label, $this->format( $timestamp_min ) ) );
				}
			}

			if ( ! empty( $this->input_attrs['max'] ) ) {
				$timestamp_max = $this->parse_as_timestamp( $this->input_attrs['max'] );
				if ( $timestamp > $timestamp_max ) {
					return new WP_Error( 'field_datetime_greater_than', sprintf( $this->manager->get_message( 'field_datetime_greater_than' ), $this->format( $timestamp ), $this->label, $this->format( $timestamp_max ) ) );
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
		 * Parses a value based on the $store property.
		 *
		 * @since 1.0.0
		 *
		 * @param string|int $value Datetime string or timestamp.
		 * @return string Parsed date/time/datetime string.
		 */
		protected function parse( $value ) {
			$value = $this->parse_as_timestamp( $value );

			switch ( $this->store ) {
				case 'time':
					$value = date_i18n( 'H:i:s', $value );
					break;
				case 'date':
					$value = date_i18n( 'Y-m-d', $value );
					break;
				case 'datetime':
				default:
					$value = date_i18n( 'Y-m-d H:i:s', $value );
					break;
			}

			return $value;
		}

		/**
		 * Formats a value based on the $store property.
		 *
		 * @since 1.0.0
		 *
		 * @param string|int $value Datetime string or timestamp.
		 * @return string Formatted date/time/datetime string.
		 */
		protected function format( $value ) {
			$value = $this->parse_as_timestamp( $value );

			switch ( $this->store ) {
				case 'time':
					$value = date_i18n( get_option( 'time_format' ), $value );
					break;
				case 'date':
					$value = date_i18n( get_option( 'date_format' ), $value );
					break;
				case 'datetime':
				default:
					$value = date_i18n( sprintf( $this->manager->get_message( 'field_datetime_format_concat' ), get_option( 'date_format' ), get_option( 'time_format' ) ), $value );
					break;
			}

			return $value;
		}

		/**
		 * Parses a value into a timestamp.
		 *
		 * @since 1.0.0
		 *
		 * @param string|int $value Datetime string or timestamp.
		 * @return int Timestamp.
		 */
		protected function parse_as_timestamp( $value ) {
			if ( is_numeric( $value ) ) {
				return (int) $value;
			}

			return strtotime( $value );
		}

		/**
		 * Returns values to be considered as empty.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of empty-ish values.
		 */
		protected function get_emptyish_values() {
			return array( '0000-00-00 00:00:00', '0000-00-00', '00:00:00', '00:00' );
		}
	}

endif;
