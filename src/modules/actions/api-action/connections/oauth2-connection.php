<?php
/**
 * OAuth2 API connection class
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Modules\Actions\API_Action\Connections;

/**
 * Class for an API connection via OAuth2.
 *
 * @since 1.1.0
 */
class OAuth2_Connection extends Connection {

	/**
	 * The consumer key.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $consumer_key = '';

	/**
	 * The consumer secret.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $consumer_secret = '';

	/**
	 * The token.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $token = '';

	/**
	 * The token secret.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $token_secret = '';

	/**
	 * Gets the definitions for the fields required to provide authentication data.
	 *
	 * @since 1.1.0
	 *
	 * @return Array of $field_slug => $field_definition pairs.
	 */
	public static function get_authenticator_fields() {
		return array(
			'consumer_key'    => array(
				'type'          => 'text',
				'label'         => __( 'Consumer Key', 'torro-forms' ),
				'description'   => __( 'Enter the consumer key for the API.', 'torro-forms' ),
				'input_classes' => array( 'regular-text' ),
			),
			'consumer_secret' => array(
				'type'          => 'text',
				'label'         => __( 'Consumer Secret', 'torro-forms' ),
				'description'   => __( 'Enter the consumer secret for the API.', 'torro-forms' ),
				'input_classes' => array( 'regular-text' ),
			),
			'token'           => array(
				'type'          => 'text',
				'label'         => __( 'Token', 'torro-forms' ),
				'description'   => __( 'Enter the token for the API.', 'torro-forms' ),
				'input_classes' => array( 'regular-text' ),
			),
			'token_secret'    => array(
				'type'          => 'text',
				'label'         => __( 'Token Secret', 'torro-forms' ),
				'description'   => __( 'Enter the token secret for the API.', 'torro-forms' ),
				'input_classes' => array( 'regular-text' ),
			),
		);
	}
}
