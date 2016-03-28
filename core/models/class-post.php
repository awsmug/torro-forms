<?php
/**
 * Torro Forms Post Class
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
 * @version 1.0.0alpha1
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 awesome.ug (support@awesome.ug)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Torro_Post {
	private $id;
	private $post;
	private $meta;
	private $terms;
	private $comments;

	/**
	 * Initializes the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $post_id ) {
		if ( empty( $post_id ) ) {
			return false;
		}

		$this->id = $post_id;
		$this->post = get_post( $post_id );
		$this->meta = get_post_meta( $post_id );
		$this->comments = get_comments( array( 'post_id' => $post_id ) );
	}

	/**
	 * Duplicating posts
	 *
	 * @param bool $copy_meta
	 * @param bool $copy_comments
	 * @param bool $copy_taxonomies
	 * @param bool $draft
	 *
	 * @return int $post_id The id of the new post
	 */
	public function duplicate( $copy_meta = true, $copy_taxonomies = true, $copy_comments = true, $draft = false ) {
		$copy = clone $this->post;
		$copy->ID = '';

		if ( $draft ) {
			$copy->post_status = 'draft';
		}

		$copy->post_date = '';
		$copy->post_modified = '';
		$copy->post_date_gmt = '';
		$copy->post_modified_gmt = '';

		$post_id = wp_insert_post( $copy );

		if ( $copy_meta ) {
			$this->duplicate_meta( $post_id );
		}

		if ( $copy_taxonomies ) {
			$this->duplicate_taxonomies( $post_id );
		}

		if ( $copy_comments ) {
			$this->duplicate_comments( $post_id );
		}

		return $post_id;
	}

	/**
	 * Duplicates comments of a post
	 *
	 * @param $post_id The ID of the post
	 *
	 * @return bool
	 */
	public function duplicate_meta( $post_id ) {
		if ( empty( $post_id ) ) {
			return false;
		}

		$forbidden_keys = apply_filters( 'torro_duplicate_forbidden_terms', array( '_edit_lock', '_edit_last' ) );

		foreach ( $this->meta as $meta_key => $meta_value ) {
			if ( ! in_array( $meta_key, $forbidden_keys, true ) ) {
				foreach ( $meta_value as $value ) {
					add_post_meta( $post_id, $meta_key, $value );
				}
			}
		}

		return true;
	}

	/**
	 * Duplicating taxonomies of a post
	 *
	 * @param $post_id
	 */
	public function duplicate_taxonomies( $post_id ) {
		global $wpdb;

		if ( empty( $post_id ) ) {
			return false;
		}

		$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->term_relationships} WHERE object_id=%d", $this->id );
		$results = $wpdb->get_results( $sql );

		if ( count( $results ) > 0 ) {
			foreach ( $results as $result ) {
				$wpdb->insert( $wpdb->term_relationships, array(
					'object_id'			=> $post_id,
					'term_taxonomy_id'	=> $result->term_taxonomy_id,
					'term_order'		=> $result->term_order
				), array(
					'%d',
					'%d',
					'%d',
				) );
			}
		}

		return true;
	}

	/**
	 * Duplicates comments of a post
	 *
	 * @param int $post_id The ID of the post
	 *
	 * @return bool
	 */
	public function duplicate_comments( $post_id ) {
		$comment_transfer = array();

		if ( empty( $post_id ) ) {
			return false;
		}

		foreach ( $this->comments as $comment ) {
			$comment = (array) $comment;
			$comment['comment_post_ID'] = $post_id;
			$old_comment_id = $comment['comment_ID'];
			$new_comment_id = wp_insert_comment( $comment );
			$comment_transfer[ $old_comment_id ] = $new_comment_id;
		}

		// Running all new comments and updating parents
		foreach ( $comment_transfer as $old_comment_id => $new_comment_id ) {
			$comment = get_comment( $new_comment_id, ARRAY_A );

			// If comment has parent comment
			if( 0 !== absint( $comment['comment_parent'] ) ) {
				$comment['comment_parent'] = $comment_transfer[ $comment['comment_parent'] ];
				wp_update_comment( $comment );
			}
		}

		return true;
	}
}
