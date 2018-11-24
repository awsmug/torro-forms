<?php
/**
 * Trait for managers that support capabilities
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Traits;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Capabilities;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Capability_Manager_Trait' ) ) :

	/**
	 * Trait for managers.
	 *
	 * Include this trait for managers that support capabilities.
	 *
	 * @since 1.0.0
	 */
	trait Capability_Manager_Trait {
		/**
		 * The capabilities service definition.
		 *
		 * @since 1.0.0
		 * @static
		 * @var string
		 */
		protected static $service_capabilities = Capabilities::class;
	}

endif;
