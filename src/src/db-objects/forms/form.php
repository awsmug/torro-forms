<?php
/**
 * Form class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Forms;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Core_Model;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Sitewide_Model_Trait;
use Leaves_And_Love\Plugin_Lib\Fixes;
use awsmug\Torro_Forms\DB_Objects\Containers\Container_Collection;
use awsmug\Torro_Forms\DB_Objects\Containers\Element_Collection;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission_Collection;
use awsmug\Torro_Forms\DB_Objects\Participants\Participant_Collection;
use WP_Post;
use WP_Error;
use stdClass;

/**
 * Class representing a form.
 *
 * @since 1.0.0
 *
 * @property string $title
 * @property string $slug
 * @property int    $author
 * @property string $status
 * @property int    $timestamp
 * @property int    $timestamp_modified
 *
 * @property-read int $id
 */
class Form extends Core_Model {
	use Sitewide_Model_Trait;

	/**
	 * Constructor.
	 *
	 * Sets the ID and fetches relevant data.
	 *
	 * @since 1.0.0
	 *
	 * @param Form_Manager $manager The manager instance for the model.
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
		switch ( $property ) {
			case 'id':
			case 'slug':
			case 'author':
			case 'timestamp':
			case 'timestamp_modified':
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
		switch ( $property ) {
			case 'id':
				return (int) $this->original->ID;
			case 'slug':
				return $this->original->post_name;
			case 'author':
				return (int) $this->original->post_author;
			case 'timestamp':
				return (int) strtotime( $this->original->post_date_gmt );
			case 'timestamp_modified':
				return (int) strtotime( $this->original->post_modified_gmt );
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
		$found   = false;
		$changed = false;

		switch ( $property ) {
			case 'id':
				return;
			case 'slug':
				$found = true;
				if ( $this->original->post_name !== $value ) {
					$this->original->post_name = $value;
					$changed                   = true;
				}
				break;
			case 'author':
				$found = true;
				if ( (int) $this->original->post_author !== (int) $value ) {
					$this->original->post_author = (int) $value;
					$changed                     = true;
				}
				break;
			case 'timestamp':
				$found = true;
				if ( (int) strtotime( $this->original->post_date_gmt ) !== (int) $value ) {
					$this->original->post_date     = '0000-00-00 00:00:00';
					$this->original->post_date_gmt = date( 'Y-m-d H:i:s', $value );
					$changed                       = true;
				}
				break;
			case 'timestamp_modified':
				$found = true;
				if ( (int) strtotime( $this->original->post_modified_gmt ) !== (int) $value ) {
					$this->original->post_modified     = '0000-00-00 00:00:00';
					$this->original->post_modified_gmt = date( 'Y-m-d H:i:s', $value );
					$changed                           = true;
				}
				break;
		}

		if ( $found ) {
			if ( $changed && ! in_array( $property, $this->pending_properties, true ) ) {
				$this->pending_properties[] = $property;
			}

			return;
		}

		parent::__set( $property, $value );
	}

	/**
	 * Returns all containers that belong to the form.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Optional. Additional query arguments. Default empty array.
	 * @return Container_Collection List of containers.
	 */
	public function get_containers( $args = array() ) {
		if ( empty( $this->original->ID ) ) {
			return $this->manager->get_child_manager( 'containers' )->get_collection( array(), 0, 'objects' );
		}

		$args = wp_parse_args(
			$args,
			array(
				'number'  => -1,
				'form_id' => $this->original->ID,
			)
		);

		return $this->manager->get_child_manager( 'containers' )->query( $args );
	}

	/**
	 * Returns all elements that belong to the form.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Optional. Additional query arguments. Default empty array.
	 * @return Element_Collection List of elements.
	 */
	public function get_elements( $args = array() ) {
		if ( empty( $this->original->ID ) ) {
			return $this->manager->get_child_manager( 'containers' )->get_child_manager( 'elements' )->get_collection( array(), 0, 'objects' );
		}

		$args = wp_parse_args(
			$args,
			array(
				'number'  => -1,
				'form_id' => $this->original->ID,
			)
		);

		return $this->manager->get_child_manager( 'containers' )->get_child_manager( 'elements' )->query( $args );
	}

	/**
	 * Returns all submissions that belong to the form.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Optional. Additional query arguments. Default empty array.
	 * @return Submission_Collection List of submissions.
	 */
	public function get_submissions( $args = array() ) {
		if ( empty( $this->original->ID ) ) {
			return $this->manager->get_child_manager( 'submissions' )->get_collection( array(), 0, 'objects' );
		}

		$args = wp_parse_args(
			$args,
			array(
				'number'  => -1,
				'form_id' => $this->original->ID,
			)
		);

		return $this->manager->get_child_manager( 'submissions' )->query( $args );
	}

	/**
	 * Formats the form date and time.
	 *
	 * @since 1.0.0
	 *
	 * @param string $format Datetime format string. Will be localized.
	 * @param bool   $gmt    Optional. Whether to return as GMT. Default true.
	 * @return string Formatted date and time.
	 */
	public function format_datetime( $format, $gmt = true ) {
		$timestamp = $this->original->post_date_gmt;
		if ( ! $gmt ) {
			$timestamp = strtotime( get_date_from_gmt( date( 'Y-m-d H:i:s', $timestamp ) ) );
		}

		return date_i18n( $format, $timestamp );
	}

	/**
	 * Returns an array representation of the model.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $include_meta Optional. Whether to include metadata for each model in the collection.
	 *                           Default true.
	 * @return array Array including all information for the model.
	 */
	public function to_json( $include_meta = true ) {
		global $wp;

		$data = parent::to_json( $include_meta );

		/**
		 * Filters the form tag classes.
		 *
		 * @since 1.0.0
		 *
		 * @param array $form_classes Array of form classes.
		 * @param Form  $form         Form object.
		 */
		$form_classes = apply_filters( "{$this->manager->get_prefix()}form_classes", array( 'torro-form' ), $this );

		/**
		 * Filters the form action URL.
		 *
		 * @since 1.0.0
		 *
		 * @param string $form_action_url Form action URL.
		 * @param int    $form_id         Form ID.
		 */
		$form_action_url = apply_filters( 'torro_form_action_url', home_url( $wp->request ), (int) $this->original->ID );

		$data['form_attrs'] = array(
			'id'         => 'torro-form-' . $this->original->ID,
			'class'      => implode( ' ', $form_classes ),
			'action'     => $form_action_url,
			'method'     => 'post',
			'enctype'    => 'multipart/form-data',
			'novalidate' => true,
		);

		return $data;
	}

	/**
	 * Duplicates the form including all of its contents (except submissions).
	 *
	 * @since 1.0.0
	 *
	 * @param bool   $as_draft  Optional. Whether to set the new form post to 'draft' status initially. Default true.
	 * @param string $new_title Optional. New form title. Default is the original title prefixed with 'Copy of '.
	 * @return Form|WP_Error New form object on success, error object on failure.
	 */
	public function duplicate( $as_draft = true, $new_title = '' ) {
		if ( empty( $this->original->ID ) ) {
			return new WP_Error( 'form_post_not_exist', __( 'The form post does not exist in the database.', 'torro-forms' ) );
		}

		$post_data = get_post( $this->original->ID, ARRAY_A );
		if ( ! $post_data ) {
			return new WP_Error( 'form_post_not_exist', __( 'The form post does not exist in the database.', 'torro-forms' ) );
		}

		$post_data['ID']                = '';
		$post_data['post_date']         = '';
		$post_data['post_date_gmt']     = '';
		$post_data['post_modified']     = '';
		$post_data['post_modified_gmt'] = '';

		if ( ! empty( $new_title ) ) {
			$post_data['post_title'] = $new_title;
		} else {
			/* translators: %s: original form title */
			$post_data['post_title'] = sprintf( _x( 'Copy of %s', 'duplicated form title', 'torro-forms' ), $post_data['post_title'] );
		}

		unset( $post_data['post_name'] );

		if ( ! $as_draft ) {
			$post_data['post_status'] = 'publish';
		} else {
			$post_data['post_status'] = 'draft';
		}

		$new_form_id = wp_insert_post( $post_data, true );
		if ( is_wp_error( $new_form_id ) ) {
			return $new_form_id;
		}

		$this->duplicate_terms_for_form( $new_form_id );
		$this->duplicate_metadata_for_form( $new_form_id );
		$this->duplicate_comments_for_form( $new_form_id );

		foreach ( $this->get_containers() as $container ) {
			$container->duplicate( $new_form_id );
		}

		return $this->manager->get( $new_form_id );
	}

	/**
	 * Duplicates all taxonomy terms for this form and applies them to another given form.
	 *
	 * @since 1.0.0
	 *
	 * @param int $form_id New form ID to apply the taxonomy terms to.
	 */
	protected function duplicate_terms_for_form( $form_id ) {
		$taxonomies = get_object_taxonomies( get_post( $this->original->ID ), 'names' );
		foreach ( $taxonomies as $taxonomy ) {
			$terms = wp_get_object_terms( $this->original->ID, $taxonomy, array( 'fields' => 'ids' ) );
			wp_set_object_terms( $form_id, $terms, $taxonomy );
		}
	}

	/**
	 * Duplicates all metadata for this form and attaches it to another given form.
	 *
	 * @since 1.0.0
	 *
	 * @param int $form_id New form ID to attach the metadata to.
	 */
	protected function duplicate_metadata_for_form( $form_id ) {
		$forbidden_meta = array( '_edit_lock', '_edit_last' );

		$meta = get_post_meta( $this->original->ID );
		foreach ( $meta as $meta_key => $meta_values ) {
			if ( in_array( $meta_key, $forbidden_meta, true ) ) {
				continue;
			}

			foreach ( $meta_values as $meta_value ) {
				$meta_value = maybe_unserialize( $meta_value );

				add_post_meta( $form_id, $meta_key, $meta_value );
			}
		}
	}

	/**
	 * Duplicates all comments for this form and attaches them to another given form.
	 *
	 * @since 1.0.0
	 *
	 * @param int $form_id New form ID to attach the comments to.
	 */
	protected function duplicate_comments_for_form( $form_id ) {
		if ( ! post_type_supports( $this->manager->get_prefix() . 'form', 'comments' ) ) {
			return;
		}

		$mappings = array();

		$comments = get_comments(
			array(
				'post_id' => $this->original->ID,
			)
		);

		foreach ( $comments as $comment ) {
			$comment_data                    = get_comment( $comment, ARRAY_A );
			$comment_data['comment_post_ID'] = $form_id;

			$old_id = $comment_data['comment_ID'];
			unset( $comment_data['comment_ID'] );

			$mappings[ $old_id ] = wp_insert_comment( $comment_data );
		}

		foreach ( $mappings as $old_id => $new_id ) {
			$comment_data = get_comment( $new_id, ARRAY_A );
			if ( 0 === absint( $comment_data['comment_parent'] ) ) {
				continue;
			}

			if ( ! isset( $mappings[ $comment_data['comment_parent'] ] ) ) {
				continue;
			}

			$comment_data['comment_parent'] = $mappings[ $comment_data['comment_parent'] ];
			wp_update_comment( $comment_data );
		}
	}

	/**
	 * Returns all current values as $property => $value pairs.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $pending_only Whether to only return pending properties. Default false.
	 * @return array Array of $property => $value pairs.
	 */
	protected function get_property_values( $pending_only = false ) {
		$properties = array( 'id', 'title', 'slug', 'author', 'status', 'timestamp', 'timestamp_modified' );
		if ( $pending_only ) {
			$properties = $this->pending_properties;
		}

		$values = array();
		foreach ( $properties as $property ) {
			$values[ $property ] = $this->__get( $property );
		}

		return $values;
	}

	/**
	 * Fills the $original property with a default object.
	 *
	 * This method is called if a new object has been instantiated.
	 *
	 * @since 1.0.0
	 */
	protected function set_default_object() {
		$this->original = new WP_Post( new stdClass() );
	}

	/**
	 * Returns the names of all properties that should be accessible on the Core object.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of property names.
	 */
	protected function get_db_fields() {
		return array(
			'post_title',
			'post_author',
			'post_status',
		);
	}
}
