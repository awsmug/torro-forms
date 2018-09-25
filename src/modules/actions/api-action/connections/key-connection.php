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
	 * The API key parameter name.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $parameter_name = 'key';

	/**
	 * The API key.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $key = '';
}
