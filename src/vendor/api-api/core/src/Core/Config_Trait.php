<?php
/**
 * API-API Config trait
 *
 * @package APIAPI\Core
 * @since 1.0.0
 */

namespace APIAPI\Core;

if ( ! trait_exists( 'APIAPI\Core\Config_Trait' ) ) {

	/**
	 * Config trait for the API-API.
	 *
	 * @since 1.0.0
	 */
	trait Config_Trait {
		/**
		 * Configuration object.
		 *
		 * @since 1.0.0
		 * @var Config
		 */
		protected $config;

		/**
		 * Returns or sets the configuration object.
		 *
		 * @since 1.0.0
		 *
		 * @param Config|array|null $config Optional. Configuration object or associative array. Default null.
		 * @return Config Configuration object for the manager.
		 */
		public function config( $config = null ) {
			if ( ! is_null( $config ) ) {
				if ( is_a( $config, Config::class ) ) {
					$this->config = $config;
				} else {
					$this->config = new Config( $config );
				}
			}

			return $this->config;
		}
	}

}
