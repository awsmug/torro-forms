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
	 * Sets the values for the read-only authentication data fields.
	 *
	 * This data is set based on the structure associated with the connection.
	 *
	 * @since 1.1.0
	 */
	protected function set_readonly_fields() {
		try {
			$api_structure           = $this->api_action->api_structure( $this->structure );
			$authentication_defaults = $api_structure->get_authentication_data_defaults();
		} catch ( Exception $e ) {
			$authentication_defaults = array();
		}

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
