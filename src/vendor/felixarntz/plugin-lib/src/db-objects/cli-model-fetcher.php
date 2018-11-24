<?php
/**
 * CLI model fetcher class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\CLI_Model_Fetcher' ) ) :

	/**
	 * Class for WP-CLI to fetch a model based on one of its attributes.
	 *
	 * @since 1.0.0
	 */
	class CLI_Model_Fetcher extends \WP_CLI\Fetchers\Base {
		/**
		 * The manager instance.
		 *
		 * @since 1.0.0
		 * @var Manager
		 */
		protected $manager;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param Manager $manager The manager instance.
		 */
		public function __construct( $manager ) {
			$this->manager = $manager;

			$this->msg = 'Could not find the ' . $this->manager->get_singular_slug() . ' with ID %d.';
		}

		/**
		 * Gets a model by ID.
		 *
		 * @since 1.0.0
		 *
		 * @param int $arg Model ID.
		 * @return Model|null Model object, or null if it does not exist.
		 */
		public function get( $arg ) {
			return $this->manager->get( $arg );
		}
	}

endif;
