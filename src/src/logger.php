<?php
/**
 * Assets manager class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Psr\Log\InvalidArgumentException;

/**
 * PSR-3-compatible logger class.
 *
 * It simply uses the PHP error handler as managed by WordPress.
 *
 * @since 1.0.0
 */
class Logger extends AbstractLogger {

	/**
	 * Mappings from log levels to PHP error constants.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $level_mappings = array();

	/**
	 * Constructor.
	 *
	 * Sets the level mappings from log levels to PHP error constants.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->level_mappings = array(
			LogLevel::EMERGENCY => E_USER_ERROR,
			LogLevel::ALERT     => E_USER_ERROR,
			LogLevel::CRITICAL  => E_USER_ERROR,
			LogLevel::ERROR     => E_USER_ERROR,
			LogLevel::WARNING   => E_USER_WARNING,
			LogLevel::NOTICE    => E_USER_NOTICE,
			LogLevel::INFO      => E_USER_NOTICE,
			LogLevel::DEBUG     => E_USER_NOTICE,
		);
	}

	/**
	 * Logs a message with an arbitrary level.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $level   Message level.
	 * @param string $message Message to log.
	 * @param array  $context Optional. Additional context data.
	 */
	public function log( $level, $message, array $context = array() ) {
		$message_type = $this->to_php_error_constant( $level );

		if ( E_USER_NOTICE === $message_type && ! empty( $context['deprecated'] ) ) {
			$message_type = E_USER_DEPRECATED;
		}

		trigger_error( $message, $message_type ); // WPCS: XSS OK.
	}

	/**
	 * Converts a PSR-3 log level to a PHP error constant to use for it.
	 *
	 * @since 1.0.0
	 *
	 * @param string $level PSR-3 log level.
	 * @return int PHP error constant.
	 *
	 * @throws InvalidArgumentException Thrown if log level is not defined.
	 */
	protected function to_php_error_constant( $level ) {
		if ( ! isset( $this->level_mappings[ $level ] ) ) {
			throw new InvalidArgumentException( sprintf( 'Level %1$s is not defined, use one of: %2$s', $level, implode( ', ', array_keys( $this->level_mappings ) ) ) );
		}

		return $this->level_mappings[ $level ];
	}
}
