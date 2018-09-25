<?php
/**
 * API connection base class
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Modules\Actions\API_Action\Connections;

use awsmug\Torro_Forms\Modules\Actions\API_Action\API_Action;

/**
 * Base class for an API connection.
 *
 * @since 1.1.0
 */
abstract class Connection {

	/**
	 * The connection slug.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $slug = '';

	/**
	 * The connection title.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $title = '';

	/**
	 * The API structure slug.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $structure = '';

	/**
	 * The parent API action.
	 *
	 * @since 1.1.0
	 * @var API_Action|null
	 */
	protected $api_action = null;

	/**
	 * Internal flag for whether read-only values have been set.
	 *
	 * @since 1.1.0
	 * @var bool
	 */
	protected $readonly_values_set = false;

	/**
	 * Constructor.
	 *
	 * Sets the connection data.
	 *
	 * @since 1.1.0
	 *
	 * @param API_Action $api_action Parent API action.
	 * @param array      $data       Connection data array.
	 */
	public function __construct( $api_action, $data ) {
		$this->api_action = $api_action;

		foreach ( $data as $key => $value ) {
			if ( property_exists( $this, $key ) ) {
				$this->$key = $value;
			}
		}

		if ( empty( $this->slug ) ) {
			$this->slug = sanitize_title( $this->title );
		}
	}

	/**
	 * Gets information about this connection.
	 *
	 * @since 1.1.0
	 *
	 * @param string $field Field slug to get the value for.
	 * @return string Value for the field, or empty string if invalid field.
	 */
	public function get( $field ) {
		if ( 'api_action' === $field || ! isset( $this->$field ) ) {
			return '';
		}

		return $this->$field;
	}

	/**
	 * Gets the array of authentication data.
	 *
	 * @since 1.1.0
	 *
	 * @return array Authentication data as $key => $value pairs.
	 */
	public function get_authentication_data() {
		if ( ! $this->readonly_values_set ) {
			$this->set_readonly_fields();
			$this->readonly_values_set = true;
		}

		$data   = get_object_vars( $this );
		$fields = static::get_authenticator_fields();

		return array_intersect_key( $data, $fields );
	}

	/**
	 * Gets the available routes, with field definition data for each one.
	 *
	 * @since 1.1.0
	 *
	 * @return array Array of $route_slug => $route_data pairs, where $route_data is an array with
	 *               $title and $fields keys, and $fields is an array of $field_slug => $field_data
	 *               pairs, where $field_slug maps to a (possibly nested) parameter in the API route
	 *               and $field_data is data from the API-API route.
	 */
	public function get_routes_with_fields() {
		$api_structure = $this->api_action->api_structure( $this->structure );
		$routes        = $this->api_action->get_available_routes( $this->structure );

		foreach ( $routes as $route_slug => $route_data ) {
			$orig_route_slug = $route_slug;

			$method = 'POST';
			if ( preg_match( '/^(GET|POST|PUT|PATCH|DELETE)\:/', $route_slug, $matches ) ) {
				$method     = $matches[1];
				$route_slug = substr( $route_slug, strlen( $method ) + 1 );
			}

			$api_route = $api_structure->get_route_object( $route_slug );
			$params    = $api_route->get_method_params( $method, true );

			$routes[ $orig_route_slug ]['fields'] = $this->fill_param_fields( array(), $route_data['fields'], $params );
		}

		return $routes;
	}

	/**
	 * Generates field data from API route parameter data.
	 *
	 * The resulting fields array is always one level deep, with nested route parameters being on the top-level
	 * with their parameter path as $field_slug, where each part is separated by '->'.
	 *
	 * This method is recursive in order to support arrays and objects.
	 *
	 * @since 1.1.0
	 *
	 * @param array $fields       Fields array to append to, initially empty.
	 * @param array $route_fields Route fields specified by the API action, to allow providing defaults and defining
	 *                            read-only fields with automated values.
	 * @param array $params       Actual route parameters from the API-API.
	 * @param array $parent_path  List of parent parameter slugs in their order, to use as a prefix for subparameters
	 *                            when the function is recursively called. Initially empty.
	 * @return array Route fields as $field_slug => $field_data pairs, where $field_slug maps to a (possibly nested)
	 *                            parameter in the API route and $field_data is data from the API-API route.
	 */
	protected function fill_param_fields( $fields, $route_fields, $params, $parent_path = array() ) {
		$parent_prefix = implode( '->', $parent_path );
		if ( ! empty( $parent_prefix ) ) {
			$parent_prefix .= '->';
		}

		foreach ( $params as $param => $param_info ) {
			$field_slug = $parent_prefix . $param;

			if ( isset( $route_fields[ $field_slug ]['value'] ) ) {
				$param_info['value']    = $route_fields[ $field_slug ]['value'];
				$param_info['readonly'] = true;
			} elseif ( ! empty( $route_fields[ $field_slug ]['value_callback'] ) ) {
				$param_info['value']    = $route_fields[ $field_slug ]['value_callback'];
				$param_info['readonly'] = true;
			}

			if ( isset( $route_fields[ $field_slug ]['default'] ) ) {
				$param_info['default'] = $route_fields[ $field_slug ]['default'];
			}

			switch ( $param_info['type'] ) {
				case 'array':
					$fields[ $field_slug ] = $param_info;

					if ( empty( $param_info['readonly'] ) && ! empty( $param_info['items'] ) ) {
						array_push( $parent_path, $param );

						$fields = $this->fill_param_fields(
							$fields,
							$route_fields,
							array( 'items' => $param_info['items'] ),
							$parent_path
						);

						array_pop( $parent_path );
					}
					break;
				case 'object':
					if ( empty( $param_info['readonly'] ) && ! empty( $param_info['properties'] ) ) {
						array_push( $parent_path, $param );

						$fields = $this->fill_param_fields(
							$fields,
							$route_fields,
							$param_info['properties'],
							$parent_path
						);

						array_pop( $parent_path );
					} else {
						$fields[ $field_slug ] = $param_info;
					}
					break;
				default:
					$fields[ $field_slug ] = $param_info;
			}
		}

		return $fields;
	}

	/**
	 * Sets the values for the read-only authentication data fields.
	 *
	 * This data is set based on the structure associated with the connection.
	 *
	 * @since 1.1.0
	 */
	protected function set_readonly_fields() {
		$api_structure           = $this->api_action->api_structure( $this->structure );
		$authentication_defaults = $api_structure->get_authentication_data_defaults();

		$fields = static::get_authenticator_fields();
		foreach ( $fields as $field_slug => $field_data ) {
			if ( empty( $field_data['readonly'] ) || ! property_exists( $this->$field_slug ) ) {
				continue;
			}

			if ( isset( $authentication_defaults[ $field_slug ] ) ) {
				$this->$field_slug = $authentication_defaults[ $field_slug ];
				continue;
			}

			$this->$field_slug = isset( $field_data['default'] ) ? $field_data['default'] : '';
		}
	}

	/**
	 * Gets the definitions for the fields required to provide authentication data.
	 *
	 * @since 1.1.0
	 *
	 * @return Array of $field_slug => $field_definition pairs.
	 */
	public static function get_authenticator_fields() {
		return array();
	}
}
