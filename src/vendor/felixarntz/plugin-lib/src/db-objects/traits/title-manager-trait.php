<?php
/**
 * Trait for managers that support titles
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Traits;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Title_Manager_Trait' ) ) :

	/**
	 * Trait for managers.
	 *
	 * Include this trait for managers that support titles.
	 *
	 * @since 1.0.0
	 */
	trait Title_Manager_Trait {
		/**
		 * The title property of the model.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $title_property = 'title';

		/**
		 * Returns the name of the title property in a model.
		 *
		 * @since 1.0.0
		 *
		 * @return string Name of the title property.
		 */
		public function get_title_property() {
			return $this->title_property;
		}
	}

endif;
