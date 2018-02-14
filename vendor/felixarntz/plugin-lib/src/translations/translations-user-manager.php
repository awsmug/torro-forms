<?php
/**
 * Translations for the User_Manager class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Translations;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Translations\Translations_User_Manager' ) ) :

	/**
	 * Translations for the User_Manager class.
	 *
	 * @since 1.0.0
	 */
	class Translations_User_Manager extends Translations {
		/**
		 * Initializes the translation strings.
		 *
		 * @since 1.0.0
		 */
		protected function init() {
			$this->translations = array(
				'db_insert_error'            => $this->__translate( 'Could not insert user into the database.', 'textdomain' ),
				'db_update_error'            => $this->__translate( 'Could not update user in the database.', 'textdomain' ),
				/* translators: %s: meta key */
				'meta_delete_error'          => $this->__translate( 'Could not delete user metadata for key &#8220;%s&#8221;.', 'textdomain' ),
				/* translators: %s: meta key */
				'meta_update_error'          => $this->__translate( 'Could not update user metadata for key &#8220;%s&#8221;.', 'textdomain' ),
				'db_fetch_error_missing_id'  => $this->__translate( 'Could not fetch user from the database because it is missing an ID.', 'textdomain' ),
				'db_fetch_error'             => $this->__translate( 'Could not fetch user from the database.', 'textdomain' ),
				'db_delete_error_missing_id' => $this->__translate( 'Could not delete user from the database because it is missing an ID.', 'textdomain' ),
				'db_delete_error'            => $this->__translate( 'Could not delete user from the database.', 'textdomain' ),
				'meta_delete_all_error'      => $this->__translate( 'Could not delete the user metadata. The user itself was deleted successfully though.', 'textdomain' ),
			);
		}
	}

endif;
