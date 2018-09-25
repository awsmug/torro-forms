<?php
/**
 * OAuth1 API connection class
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Modules\Actions\API_Action\Connections;

/**
 * Class for an API connection via OAuth1.
 *
 * @since 1.1.0
 */
class OAuth1_Connection extends Connection {

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
}
