<?php
/**
 * Trait for managers that support content
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Traits;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Content_Manager_Trait' ) ) :

	/**
	 * Trait for managers.
	 *
	 * Include this trait for managers that support content.
	 *
	 * @since 1.0.0
	 */
	trait Content_Manager_Trait {
		/**
		 * The content property of the model.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $content_property = 'content';

		/**
		 * Returns the name of the content property in a model.
		 *
		 * @since 1.0.0
		 *
		 * @return string Name of the content property.
		 */
		public function get_content_property() {
			return $this->content_property;
		}
	}

endif;
