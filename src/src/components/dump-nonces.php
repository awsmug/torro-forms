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
			implode( '', get_browser() ),
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
	 * @param int $form_id Form id.
	 * @return mixed Nonce string on success, false on failure.
	 *
	 * @since 1.1.0
	 */
	public static function create( $form_id ) {
		$nonce      = wp_hash( self::get_server_hash() . '|' . self::get_remote_hash() . '|' . wp_nonce_tick() );
		$meta_key   = '_dump_nonce_' . self::get_remote_hash();
		$meta_value = array(
			'value'     => $nonce,
			'timestamp' => time(),
		);

		if ( update_post_meta( $form_id, $meta_key, $meta_value ) ) {
			return $nonce;
		}

		return false;
	}

	/**
	 * Checking nonce and thwowing away
	 *
	 * @param int $form_id Form id.
	 * @param string $nonce Nonce string to check.
	 *
	 * @return bool
	 */
	public function check( $form_id, $nonce ) {
		self::cleanup( $form_id );

		$meta_key      = '_dump_nonce_' . self::get_remote_hash();
		$compare_nonce = get_post_meta( $form_id, $meta_key );

		if ( $compare_nonce['value'] === $nonce ) {
			delete_post_meta( $form_id, $meta_key );
			return true;
		}

		return false;
	}

	/**
	 * Cleaning up nonces.
	 *
	 * @since 1.1.0
	 *
	 * @param int $form_id Form id.
	 */
	private static function cleanup( $form_id ) {
		global $wpdb;

		$time_limit = strtotime( '-1 hours' );

		$cache_key = 'torro_dump_nonce';
		$results   = wp_cache_get( $cache_key );

		if ( false === $results ) {
			$sql     = $wpdb->prepare( 'SELECT * FROM %s WHERE %s LIKE %s', $wpdb->postmeta, 'meta_key', '_dump_nonce_%' );
			$results = $wpdb->get_results( $sql );
			wp_cache_set( $cache_key, $results );
		}

		foreach ( $results as $result ) {
			$meta_key   = $result['meta_key'];
			$meta_value = $result['meta_value'];

			$time = $meta_value['timestamp'];

			if ( $time < $time_limit ) {
				delete_post_meta( $form_id, $meta_key );
			}
		}
	}
}
