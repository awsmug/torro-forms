<?php
/**
 * Map field class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Fields;

use WP_Error;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Fields\Map' ) ) :

	/**
	 * Class for a map field.
	 *
	 * @since 1.0.0
	 */
	class Map extends Text_Base {
		/**
		 * Field type identifier.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $slug = 'map';

		/**
		 * Backbone view class name to use for this field.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $backbone_view = 'MapFieldView';

		/**
		 * What type of data to store in the field value.
		 *
		 * Accepts either 'address' or 'coords'.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $store = 'address';

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

			if ( isset( $args['store'] ) && 'coords' !== $args['store'] ) {
				$args['store'] = 'address';
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

			$locale = explode( '_', get_locale() );
			$locale = $locale[0] . '-' . $locale[1];

			$gmaps_url  = 'https://maps.google.com/maps/api/js';
			$gmaps_args = array(
				'language' => $locale,
			);

			$api_key = self::get_api_key();
			if ( $api_key ) {
				$gmaps_args['key'] = $api_key;
			}

			$gmaps_url = add_query_arg( $gmaps_args, $gmaps_url );

			$assets->register_script(
				'google-maps',
				$gmaps_url,
				array(
					'in_footer' => true,
					'enqueue'   => true,
				)
			);

			$mappicker_version = '0.7.1';

			$assets->register_style(
				'wp-map-picker',
				'node_modules/wp-map-picker/wp-map-picker.css',
				array(
					'ver'     => $mappicker_version,
					'enqueue' => true,
				)
			);

			$assets->register_script(
				'wp-map-picker',
				'node_modules/wp-map-picker/wp-map-picker.js',
				array(
					'deps'      => array( 'jquery', 'jquery-ui-widget', 'jquery-ui-autocomplete', 'google-maps' ),
					'ver'       => $mappicker_version,
					'in_footer' => true,
					'enqueue'   => true,
				)
			);

			$ret[0][] = 'wp-map-picker';

			return $ret;
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
			$data          = parent::single_to_json( $current_value );
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
			$value = parent::validate_single( $value );
			if ( is_wp_error( $value ) ) {
				return $value;
			}

			if ( 'coords' === $this->store && ! empty( $value ) ) {
				$pattern = '([0-9\.]+)\|([0-9\.]+)';

				if ( ! preg_match( '/^' . $pattern . '$/', $value ) ) {
					return new WP_Error( 'field_text_no_pattern_match', sprintf( $this->manager->get_message( 'field_text_no_pattern_match' ), $value, $this->label, '<code>' . $pattern . '</code>' ) );
				}
			}

			return $value;
		}

		/**
		 * Returns the API key for Google Maps.
		 *
		 * @since 1.0.0
		 * @return string Google Maps API Key or an empty string
		 */
		public static function get_api_key() {
			$api_key = '';
			if ( defined( 'PLUGIN_LIB_GOOGLE_MAPS_API_KEY' ) ) {
				$api_key = PLUGIN_LIB_GOOGLE_MAPS_API_KEY;
			} elseif ( defined( 'GOOGLE_MAPS_API_KEY' ) ) {
				$api_key = GOOGLE_MAPS_API_KEY;
			}

			/**
			 * Filters the API key for Google Maps.
			 *
			 * @since 1.0.0
			 *
			 * @param string $api_key Defined API key or empty string to fill.
			 * @param int    $site_id Current site ID.
			 */
			return apply_filters( 'plugin_lib_google_maps_api_key', $api_key, get_current_blog_id() );
		}
	}

endif;
