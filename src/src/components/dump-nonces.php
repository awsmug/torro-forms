<?php
/**
 * Dump Nonces for securing forms.
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Components;

/**
 * Class handling nonces.
 *
 * @since 1.1.0
 *
 * @property int    $form_id
 */
class Dump_Nonces {
	/**
	 * Getting a remote hash for use instead of user id.
	 *
	 * @since 1.1.0
	 *
	 * @return string Remote Hash.
	 */
	private static function get_remote_hash() {
		$base = array(
			filter_input( INPUT_SERVER, 'REMOTE_ADDR' ),
			filter_input( INPUT_SERVER, 'HTTP_USER_AGENT' ),
			filter_input( INPUT_SERVER, 'HTTP_COOKIE' ),
		);

		return wp_hash( implode( '', $base ) );
	}

	/**
	 * Getting a server hash for using as server id.
	 *
	 * @since 1.1.0
	 *
	 * @return string Remote Hash.
	 */
	private static function get_server_hash() {
		$base = array(
			filter_input( INPUT_SERVER, 'SCRIPT_FILENAME' ),
			filter_input( INPUT_SERVER, 'SERVER_SIGNATURE' ),
			php_uname(),
		);

		return wp_hash( implode( '', $base ) );
	}


	/**
	 * Cleaning dump nonce.
	 *
	 * @return mixed Nonce string on success, false on failure.
	 *
	 * @since 1.1.0
	 */
	public static function create() {
		self::cleanup();

		$nonce        = wp_hash( self::get_server_hash() . '|' . self::get_remote_hash() . '|' . microtime() );
		$option_name  = '_dump_nonce_' . self::get_remote_hash();
		$option_value = array(
			'value'     => $nonce,
			'timestamp' => time(),
		);

		if ( update_option( $option_name, $option_value ) ) {
			return $nonce;
		}

		return false;
	}

	/**
	 * Checking nonce and thwowing away
	 *
	 * @param string $nonce Nonce string to check.
	 *
	 * @return bool
	 */
	public function check( $nonce ) {
		$option_name   = '_dump_nonce_' . self::get_remote_hash();
		$compare_nonce = get_option( $option_name, $option_name );

		if ( $compare_nonce['value'] === $nonce ) {
			delete_option( $option_name );
			return true;
		}

		return false;
	}

	/**
	 * Cleaning up nonces.
	 *
	 * @since 1.1.0
	 */
	private static function cleanup() {
		global $wpdb;

		$time_limit = strtotime( '-1 hours' );

		$cache_key = 'torro_dump_nonce';
		$results   = wp_cache_get( $cache_key );

		if ( false === $results ) {
			$sql     = $wpdb->prepare( 'SELECT * FROM %s WHERE %s LIKE %s', $wpdb->options, 'option_name', '_dump_nonce_%' );
			$results = $wpdb->get_results( $sql );
			wp_cache_set( $cache_key, $results );
		}

		foreach ( $results as $result ) {
			$option_name   = $result['option_name'];
			$option_value  = $result['option_value'];

			$time = $option_value['timestamp'];

			if ( $time < $time_limit ) {
				delete_option( $option_name );
			}
		}
	}
}
