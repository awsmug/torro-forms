<?php
/**
 * User model class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Models;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Manager;
use WP_User;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models\User' ) ) :

	/**
	 * Model class for a post
	 *
	 * This class represents a post.
	 *
	 * @since 1.0.0
	 *
	 * @property string $login
	 * @property string $pass
	 * @property string $nicename
	 * @property string $email
	 * @property string $url
	 * @property string $registered
	 * @property string $activation_key
	 * @property int    $status
	 * @property string $display_name
	 *
	 * @property-read int $id
	 */
	class User extends Core_Model {
		/**
		 * Constructor.
		 *
		 * Sets the ID and fetches relevant data.
		 *
		 * @since 1.0.0
		 *
		 * @param Manager      $manager The manager instance for the model.
		 * @param WP_User|null $db_obj  Optional. The database object or null for a new instance.
		 */
		public function __construct( $manager, $db_obj = null ) {
			parent::__construct( $manager, $db_obj );

			$this->redundant_prefix = 'user_';
		}

		/**
		 * Magic isset-er.
		 *
		 * Checks whether a property is set.
		 *
		 * @since 1.0.0
		 *
		 * @param string $property Property to check for.
		 * @return bool True if the property is set, false otherwise.
		 */
		public function __isset( $property ) {
			if ( 'id' === $property ) {
				return true;
			}

			return parent::__isset( $property );
		}

		/**
		 * Magic getter.
		 *
		 * Returns a property value.
		 *
		 * @since 1.0.0
		 *
		 * @param string $property Property to get.
		 * @return mixed Property value, or null if property is not set.
		 */
		public function __get( $property ) {
			if ( 'id' === $property ) {
				return $this->original->ID;
			}

			return parent::__get( $property );
		}

		/**
		 * Magic setter.
		 *
		 * Sets a property value.
		 *
		 * @since 1.0.0
		 *
		 * @param string $property Property to set.
		 * @param mixed  $value    Property value.
		 */
		public function __set( $property, $value ) {
			$nowrite_properties = array(
				'id',
			);

			if ( in_array( $property, $nowrite_properties, true ) ) {
				return;
			}

			parent::__set( $property, $value );
		}

		/**
		 * Returns a list of internal properties that are not publicly accessible.
		 *
		 * When overriding this method, always make sure to merge with the parent result.
		 *
		 * @since 1.0.0
		 *
		 * @return array Property blacklist.
		 */
		protected function get_blacklist() {
			$blacklist = parent::get_blacklist();

			// Do not permit access to the $filter property of WP_User.
			$blacklist[] = 'filter';

			return $blacklist;
		}

		/**
		 * Fills the $original property with a default object.
		 *
		 * This method is called if a new object has been instantiated.
		 *
		 * @since 1.0.0
		 */
		protected function set_default_object() {
			$db_obj                      = new \stdClass();
			$db_obj->ID                  = 0;
			$db_obj->user_login          = '';
			$db_obj->user_pass           = '';
			$db_obj->user_nicename       = '';
			$db_obj->user_email          = '';
			$db_obj->user_url            = '';
			$db_obj->user_registered     = '';
			$db_obj->user_activation_key = '';
			$db_obj->user_status         = 0;
			$db_obj->display_name        = '';

			// The following are actually meta keys, but need to be set on the object.
			$db_obj->nickname             = '';
			$db_obj->first_name           = '';
			$db_obj->last_name            = '';
			$db_obj->description          = '';
			$db_obj->rich_editing         = '';
			$db_obj->comment_shortcuts    = '';
			$db_obj->admin_color          = '';
			$db_obj->use_ssl              = '';
			$db_obj->show_admin_bar_front = '';

			$this->original = new WP_User( $db_obj );
		}

		/**
		 * Returns the names of all properties that are part of the database object.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of property names.
		 */
		protected function get_db_fields() {
			return array(
				'ID',
				'user_login',
				'user_pass',
				'user_nicename',
				'user_email',
				'user_url',
				'user_registered',
				'user_activation_key',
				'user_status',
				'display_name',
				// The following are actually meta keys, but need to be set on the object.
				'nickname',
				'first_name',
				'last_name',
				'description',
				'rich_editing',
				'comment_shortcuts',
				'admin_color',
				'use_ssl',
				'show_admin_bar_front',
			);
		}
	}

endif;
