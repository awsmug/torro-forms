<?php
/**
 * Comment model class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects\Models;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Sitewide_Model_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Manager;
use WP_Comment;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Comment' ) ) :

	/**
	 * Model class for a comment
	 *
	 * This class represents a comment.
	 *
	 * @since 1.0.0
	 *
	 * @property int    $post_id
	 * @property string $author
	 * @property string $author_email
	 * @property string $author_url
	 * @property string $author_IP
	 * @property string $date
	 * @property string $date_gmt
	 * @property string $content
	 * @property int    $karma
	 * @property string $approved
	 * @property string $agent
	 * @property string $type
	 * @property int    $parent
	 * @property int    $user_id
	 *
	 * @property-read int $id
	 */
	class Comment extends Core_Model {
		use Sitewide_Model_Trait;

		/**
		 * Constructor.
		 *
		 * Sets the ID and fetches relevant data.
		 *
		 * @since 1.0.0
		 *
		 * @param Manager         $manager The manager instance for the model.
		 * @param WP_Comment|null $db_obj  Optional. The database object or null for a new instance.
		 */
		public function __construct( $manager, $db_obj = null ) {
			parent::__construct( $manager, $db_obj );

			$this->redundant_prefix = 'comment_';
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

			if ( 'post_id' === $property ) {
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
				return $this->original->comment_ID;
			}

			if ( 'post_id' === $property ) {
				return $this->original->comment_post_ID;
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

			if ( 'post_id' === $property ) {
				$this->set_value_type_safe( 'comment_post_ID', $value );
				return;
			}

			parent::__set( $property, $value );
		}

		/**
		 * Fills the $original property with a default object.
		 *
		 * This method is called if a new object has been instantiated.
		 *
		 * @since 1.0.0
		 */
		protected function set_default_object() {
			$this->original = new WP_Comment( new \stdClass() );
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
				'comment_ID',
				'comment_post_ID',
				'comment_author',
				'comment_author_email',
				'comment_author_url',
				'comment_author_IP',
				'comment_date',
				'comment_date_gmt',
				'comment_content',
				'comment_karma',
				'comment_approved',
				'comment_agent',
				'comment_type',
				'comment_parent',
				'user_id',
			);
		}
	}

endif;
