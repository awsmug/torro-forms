<?php
/**
 * OAuth2 API connection class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Actions\API_Action;

/**
 * Class for an API connection via OAuth2.
 *
 * @since 1.0.0
 */
class OAuth2_Connection extends Connection {

	/**
	 * The consumer key.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $consumer_key = '';

	/**
	 * The consumer secret.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $consumer_secret = '';

	/**
	 * The token.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $token = '';

	/**
	 * The token secret.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $token_secret = '';
}
