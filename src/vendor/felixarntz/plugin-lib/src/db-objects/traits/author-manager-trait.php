<?php
/**
 * Trait for managers that support authors
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Traits;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Author_Manager_Trait' ) ) :

	/**
	 * Trait for managers.
	 *
	 * Include this trait for managers that support authors.
	 *
	 * @since 1.0.0
	 */
	trait Author_Manager_Trait {
		/**
		 * The author property of the model.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $author_property = 'author';

		/**
		 * Returns the name of the author property in a model.
		 *
		 * @since 1.0.0
		 *
		 * @return string Name of the author property.
		 */
		public function get_author_property() {
			return $this->author_property;
		}

		/**
		 * Sets the author property on a model if it isn't set already.
		 *
		 * @since 1.0.0
		 *
		 * @param null  $pre   Null value from the pre-filter.
		 * @param Model $model The model to modify.
		 * @return null The unmodified pre-filter value.
		 */
		public function maybe_set_author_property( $pre, $model ) {
			$author_property = $this->get_author_property();

			if ( empty( $model->$author_property ) ) {
				$model->$author_property = get_current_user_id();
			}

			return $pre;
		}
	}

endif;
