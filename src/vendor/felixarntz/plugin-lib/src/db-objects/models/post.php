<?php
/**
 * Post model class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Models;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Sitewide_Model_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Manager;
use WP_Post;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Post' ) ) :

	/**
	 * Model class for a post
	 *
	 * This class represents a post.
	 *
	 * @since 1.0.0
	 *
	 * @property int    $author
	 * @property string $date
	 * @property string $date_gmt
	 * @property string $content
	 * @property string $title
	 * @property string $excerpt
	 * @property string $status
	 * @property string $comment_status
	 * @property string $ping_status
	 * @property string $password
	 * @property string $name
	 * @property string $to_ping
	 * @property string $pinged
	 * @property string $modified
	 * @property string $modified_gmt
	 * @property string $content_filtered
	 * @property int    $parent
	 * @property string $guid
	 * @property int    $menu_order
	 * @property string $type
	 * @property string $mime_type
	 *
	 * @property-read int $id
	 * @property-read int $comment_count
	 */
	class Post extends Core_Model {
		use Sitewide_Model_Trait;

		/**
		 * Constructor.
		 *
		 * Sets the ID and fetches relevant data.
		 *
		 * @since 1.0.0
		 *
		 * @param Manager      $manager The manager instance for the model.
		 * @param WP_Post|null $db_obj  Optional. The database object or null for a new instance.
		 */
		public function __construct( $manager, $db_obj = null ) {
			parent::__construct( $manager, $db_obj );

			$this->redundant_prefix = 'post_';
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
				'comment_count',
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

			// Do not permit access to the $filter property of WP_Post.
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
			$this->original = new WP_Post( new \stdClass() );
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
				'post_author',
				'post_date',
				'post_date_gmt',
				'post_content',
				'post_title',
				'post_excerpt',
				'post_status',
				'comment_status',
				'ping_status',
				'post_password',
				'post_name',
				'to_ping',
				'pinged',
				'post_modified',
				'post_modified_gmt',
				'post_content_filtered',
				'post_parent',
				'guid',
				'menu_order',
				'post_type',
				'post_mime_type',
				'comment_count',
			);
		}
	}

endif;
