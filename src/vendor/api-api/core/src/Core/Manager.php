<?php
/**
 * API-API Manager class
 *
 * @package APIAPI\Core
 * @since 1.0.0
 */

namespace APIAPI\Core;

use APIAPI\Core\Exception\Invalid_Argument_Exception;

if ( ! class_exists( 'APIAPI\Core\Manager' ) ) {

	/**
	 * Manager class for the API-API.
	 *
	 * This class manages the different API-API instances.
	 *
	 * @since 1.0.0
	 */
	class Manager {

		/**
		 * @const string Version number of the API-API
		 */
		const VERSION = '1.0.0-beta.1';

		/**
		 * The API-API instances.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		private $instances;

		/**
		 * The transporters container.
		 *
		 * @since 1.0.0
		 * @var Transporters
		 */
		private $transporters;

		/**
		 * The structures container.
		 *
		 * @since 1.0.0
		 * @var Structures
		 */
		private $structures;

		/**
		 * The authenticators container.
		 *
		 * @since 1.0.0
		 * @var Authenticators
		 */
		private $authenticators;

		/**
		 * The storages container.
		 *
		 * @since 1.0.0
		 * @var Storages
		 */
		private $storages;

		/**
		 * The hooks instance.
		 *
		 * @since 1.0.0
		 * @var Hooks
		 */
		private $hooks;

		/**
		 * Instance holder.
		 *
		 * @since 1.0.0
		 * @static
		 * @var Manager
		 */
		private static $instance = null;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		private function __construct() {
			$this->instances = array();

			$this->transporters   = new Transporters( $this );
			$this->structures     = new Structures( $this );
			$this->authenticators = new Authenticators( $this );
			$this->storages       = new Storages( $this );

			$this->hooks = new Hooks();

			$this->hooks->trigger( 'apiapi.manager.started', $this );
		}

		/**
		 * Creates a new API-API instance.
		 *
		 * @since 1.0.0
		 *
		 * @param string       $name   Unique slug for the instance.
		 * @param Config|array $config Optional. Configuration object or associative array. Default empty array.
		 *
		 * @throws Invalid_Argument_Exception Thrown when the instance name is invalid or a duplicate.
		 */
		public function create_instance( $name, $config = array() ) {
			if ( 'manager' === $name ) {
				throw new Invalid_Argument_Exception( 'The name manager is not allowed for an instance.' );
			}

			if ( isset( $this->instances[ $name ] ) ) {
				throw new Invalid_Argument_Exception( sprintf( 'Instance name %s already exists!', $name ) );
			}

			$this->instances[ $name ] = new APIAPI( $name, $this, $config );
		}

		/**
		 * Returns a specific API-API instance.
		 *
		 * @since 1.0.0
		 *
		 * @param string            $name  Unique slug of the instance.
		 * @param Config|array|bool $force Optional. Whether to create the instance if it does not exist.
		 *                                 Can also be a configuration object or array to fill the set up
		 *                                 the new instance with this configuration. Default false.
		 * @return APIAPI|null The API-API instance, or null if it does not exist.
		 */
		public function get_instance( $name, $force = false ) {
			if ( ! isset( $this->instances[ $name ] ) ) {
				if ( ! $force ) {
					return null;
				}

				$config = array();
				if ( is_a( $force, Config::class ) || is_array( $force ) ) {
					$config = $force;
				}

				$this->create_instance( $name, $config );
			}

			return $this->instances[ $name ];
		}

		/**
		 * Returns the transporters container.
		 *
		 * @since 1.0.0
		 *
		 * @return Transporters The transporters container.
		 */
		public function transporters() {
			return $this->transporters;
		}

		/**
		 * Returns the structures container.
		 *
		 * @since 1.0.0
		 *
		 * @return Transporters The structures container.
		 */
		public function structures() {
			return $this->structures;
		}

		/**
		 * Returns the authenticators container.
		 *
		 * @since 1.0.0
		 *
		 * @return Transporters The authenticators container.
		 */
		public function authenticators() {
			return $this->authenticators;
		}

		/**
		 * Returns the storages container.
		 *
		 * @since 1.0.0
		 *
		 * @return Storages The storages container.
		 */
		public function storages() {
			return $this->storages;
		}

		/**
		 * Returns the hooks instance.
		 *
		 * @since 1.0.0
		 *
		 * @return Hooks The hooks instance.
		 */
		public function hooks() {
			return $this->hooks;
		}

		/**
		 * Returns the canonical API-API instance.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @return Manager
		 */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}

}
