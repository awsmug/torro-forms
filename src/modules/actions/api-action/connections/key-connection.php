<?php
/**
 * Key API connection class
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Modules\Actions\API_Action\Connections;

/**
 * Class for an API connection via API key.
 *
 * @since 1.1.0
 */
class Key_Connection extends Connection {

	/**
	 * Connection type.
	 *
	 * @since 1.1.0
	 */
	const TYPE = 'key';

	/**
	 * The API key parameter name.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $parameter_name = '';

	/**
	 * The API key.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $key = '';

	/**
	 * Gets the definitions for the fields required to provide authentication data.
	 *
	 * @since 1.1.0
	 *
	 * @return Array of $field_slug => $field_definition pairs.
	 */
	public static function get_authenticator_fields() {
		return array(
			'parameter_name' => array(
				'type'        => 'text',
				'label'       => __( 'API Key Parameter Name', 'torro-forms' ),
				'description' => __( 'Enter the name of the request parameter that is sent to verify API requests.', 'torro-forms' ),
				'default'     => 'key',
				'readonly'    => true,
			),
			'key'            => array(
				'type'          => 'text',
				'label'         => __( 'API Key', 'torro-forms' ),
				'description'   => __( 'Enter the API key for the API.', 'torro-forms' ),
				'input_classes' => array( 'regular-text' ),
			),
		);
	}
}
