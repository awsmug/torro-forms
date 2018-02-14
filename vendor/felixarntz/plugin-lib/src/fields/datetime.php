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
		 * Locale data.
		 *
		 * @since 1.0.0
		 * @static
		 * @var array
		 */
		protected static $locale_data = array();

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

			if ( ! isset( $args['data-store'] ) ) {
				$args['data-store'] = $args['store'];
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

			$datetimepicker_version = '2.5.4';

			$css_path = 'node_modules/jquery-datetimepicker/build/jquery.datetimepicker.min.css';
			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$css_path = 'node_modules/jquery-datetimepicker/jquery.datetimepicker.css';
			}

			$assets->register_style( 'datetimepicker', $css_path, array(
				'ver'     => $datetimepicker_version,
				'enqueue' => true,
			) );

			$assets->register_script( 'datetimepicker', 'node_modules/jquery-datetimepicker/build/jquery.datetimepicker.full.js', array(
				'deps'      => array( 'jquery' ),
				'ver'       => $datetimepicker_version,
				'in_footer' => true,
				'enqueue'   => true,
			) );

			$ret[0][] = 'datetimepicker';
			$ret[1] = array_merge( $ret[1], array(
				'language'       => substr( get_locale(), 0, 2 ),
				'datetimeFormat' => sprintf( $this->manager->get_message( 'field_datetime_format_concat' ), get_option( 'date_format' ), get_option( 'time_format' ) ),
				'dateFormat'     => get_option( 'date_format' ),
				'timeFormat'     => get_option( 'time_format' ),
				'startOfWeek'    => get_option( 'start_of_week' ),
			) );

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

			if ( ! empty( $current_value ) ) {
				$current_value = $this->format( $current_value );
			}

			parent::render_single_input( $current_value );
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

			if ( ! empty( $current_value ) ) {
				$current_value = $this->format( $current_value );
			}

			$data = parent::single_to_json( $current_value );
			$data['store'] = $this->store;

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

			if ( ! empty( $this->input_attrs['min'] ) && $timestamp < ( $timestamp_min = $this->parse_as_timestamp( $this->input_attrs['min'] ) ) ) {
				return new WP_Error( 'field_datetime_lower_than', sprintf( $this->manager->get_message( 'field_datetime_lower_than' ), $this->format( $timestamp ), $this->label, $this->format( $timestamp_min ) ) );
			}

			if ( ! empty( $this->input_attrs['max'] ) && $timestamp > ( $timestamp_max = $this->parse_as_timestamp( $this->input_attrs['max'] ) ) ) {
				return new WP_Error( 'field_datetime_greater_than', sprintf( $this->manager->get_message( 'field_datetime_greater_than' ), $this->format( $timestamp ), $this->label, $this->format( $timestamp_max ) ) );
			}

			return $value;
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

			if ( 'time' !== $this->store ) {
				$value = $this->untranslate( $value );
			}

			return strtotime( $value );
		}

		/**
		 * Untranslates a date format string.
		 *
		 * WordPress localizes date format strings. This method translates such a formatted string
		 * back to English so that it can be used by PHP's date functions.
		 *
		 * @since 1.0.0
		 *
		 * @param string $value Date format string to untranslate into an English date format string.
		 * @return string Untranslated date format string.
		 */
		protected function untranslate( $value ) {
			self::maybe_init_locale_data();

			return preg_replace_callback( '/[A-Za-zÄäÖöÜüßÁáÀàÉéÈèÍíÌìÓóÒòÚúÙùÃãÕõ]+/', array( $this, 'untranslate_replace' ), $value );
		}

		/**
		 * Callback for preg_replace() used by the untranslate() method.
		 *
		 * It looks at the localized parts of the date format string and replaces them by their English equivalents.
		 *
		 * @since 1.0.0
		 *
		 * @param array $matches Regular expression matches.
		 * @return string Replacement.
		 */
		protected function untranslate_replace( $matches ) {
			$term = $matches[0];

			if ( $key = array_search( $term, self::$locale_data['weekday_initial'], true ) ) {
				if ( $key = array_search( $key, self::$locale_data['weekday'], true ) ) {
					return $key;
				}
			}

			if ( $key = array_search( $term, self::$locale_data['weekday_abbrev'], true ) ) {
				if ( $key = array_search( $key, self::$locale_data['weekday'], true ) ) {
					return $key;
				}
			}

			if ( $key = array_search( $term, self::$locale_data['weekday'], true ) ) {
				return $key;
			}

			if ( $key = array_search( $term, self::$locale_data['month_abbrev'], true ) ) {
				if ( $key = array_search( $key, self::$locale_data['month'], true ) ) {
					return $key;
				}
			}

			if ( $key = array_search( $term, self::$locale_data['month'], true ) ) {
				return $key;
			}

			return $term;
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

		/**
		 * Sets up the $locale_data property if it has not been setup prior.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @global WP_Locale $wp_locale WordPress locale object.
		 */
		protected static function maybe_init_locale_data() {
			global $wp_locale;

			if ( ! empty( self::$locale_data ) ) {
				return;
			}

			self::$locale_data = array(
				'weekday'         => array(
					'Sunday'          => $wp_locale->weekday[0],
					'Monday'          => $wp_locale->weekday[1],
					'Tuesday'         => $wp_locale->weekday[2],
					'Wednesday'       => $wp_locale->weekday[3],
					'Thursday'        => $wp_locale->weekday[4],
					'Friday'          => $wp_locale->weekday[5],
					'Saturday'        => $wp_locale->weekday[6],
				),
				'weekday_initial' => $wp_locale->weekday_initial,
				'weekday_abbrev'  => $wp_locale->weekday_abbrev,
				'month'           => array(
					'January'         => $wp_locale->month['01'],
					'February'        => $wp_locale->month['02'],
					'March'           => $wp_locale->month['03'],
					'April'           => $wp_locale->month['04'],
					'May'             => $wp_locale->month['05'],
					'June'            => $wp_locale->month['06'],
					'July'            => $wp_locale->month['07'],
					'August'          => $wp_locale->month['08'],
					'September'       => $wp_locale->month['09'],
					'October'         => $wp_locale->month['10'],
					'November'        => $wp_locale->month['11'],
					'December'        => $wp_locale->month['12'],
				),
				'month_abbrev'    => $wp_locale->month_abbrev,
			);
		}
	}

endif;
