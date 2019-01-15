<?php
/**
 * Translations for the Site_Manager class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Translations;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Translations\Translations_Site_Manager' ) ) :

	/**
	 * Translations for the Site_Manager class.
	 *
	 * @since 1.0.0
	 */
	class Translations_Site_Manager extends Translations {
		/**
		 * Initializes the translation strings.
		 *
		 * @since 1.0.0
		 */
		protected function init() {
			$this->translations = array(
				'db_insert_error'            => $this->__translate( 'Could not insert site into the database.', 'textdomain' ),
				'db_update_error'            => $this->__translate( 'Could not update site in the database.', 'textdomain' ),
				/* translators: %s: meta key */
				'meta_delete_error'          => $this->__translate( 'Could not delete site metadata for key &#8220;%s&#8221;.', 'textdomain' ),
				/* translators: %s: meta key */
				'meta_update_error'          => $this->__translate( 'Could not update site metadata for key &#8220;%s&#8221;.', 'textdomain' ),
				'db_fetch_error_missing_id'  => $this->__translate( 'Could not fetch site from the database because it is missing an ID.', 'textdomain' ),
				'db_fetch_error'             => $this->__translate( 'Could not fetch site from the database.', 'textdomain' ),
				'db_delete_error_missing_id' => $this->__translate( 'Could not delete site from the database because it is missing an ID.', 'textdomain' ),
				'db_delete_error'            => $this->__translate( 'Could not delete site from the database.', 'textdomain' ),
				'meta_delete_all_error'      => $this->__translate( 'Could not delete the site metadata. The site itself was deleted successfully though.', 'textdomain' ),
			);
		}
	}

endif;
