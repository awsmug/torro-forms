<?php
/**
 * Includes: Torro_Form_Media class
 *
 * @package TorroForms
 * @subpackage Includes
 * @version 1.0.0-beta.3
 * @since 1.0.0-beta.1
 */

class Torro_Form_Media {
	const STATUS = 'torro-forms-upload';

	private static $aggregate_media = array();

	public static function init() {
		register_post_status( self::STATUS, array(
			'label'						=> __( 'Formular-Upload', 'torro-forms' ),
			'public'					=> false,
			'internal'					=> true,
			'exclude_from_search'		=> true,
			'show_in_admin_all_list'	=> false,
			'show_in_admin_status_list'	=> false,
			'label_count'				=> _n_noop( 'Formular-Upload (%s)', 'Formular-Uploads (%s)', 'torro-forms' ),
		) );

		add_action( 'torro_submission_has_errors', array( __CLASS__, 'delete_attachments_on_error' ), 10, 1 );

		add_action( 'wp_insert_attachment_data', array( __CLASS__, 'allow_post_status' ), 10, 2 );
		add_action( 'wp_insert_post_data', array( __CLASS__, 'disallow_post_status' ), 10, 2 );

		add_filter( 'ajax_query_attachments_args', array( __CLASS__, 'adjust_query_args' ), 10, 1 );

		add_action( 'wp_enqueue_media', array( __CLASS__, 'expose_post_status_js' ) );
	}

	public static function allow_post_status( $data, $raw_data ) {
		if ( isset( $raw_data['post_status'] ) && self::STATUS === $raw_data['post_status'] ) {
			$data['post_status'] = self::STATUS;
		}

		return $data;
	}

	public static function disallow_post_status( $data, $raw_data ) {
		if ( isset( $raw_data['post_status'] ) && self::STATUS === $raw_data['post_status'] ) {
			$data['post_status'] = 'private';
		}

		return $data;
	}

	public static function adjust_query_args( $query ) {
		if ( isset( $_REQUEST['query']['post_status'] ) && self::STATUS === $_REQUEST['query']['post_status'] ) {
			$query['post_status'] = self::STATUS;
		}

		return $query;
	}

	public static function expose_post_status_js() {
		wp_enqueue_script( 'torro-form-media', torro()->get_asset_url( 'form-media', 'js' ), array( 'media-views' ), false, true );
		wp_localize_script( 'torro-form-media', 'torro_media', array(
			'status'	=> self::STATUS,
			'l10n'		=> array(
				'name'		=> __( 'Form Uploads', 'torro-forms' ),
			),
		) );
	}

	public static function upload( $field_name, $args = array() ) {
		if ( ! isset( $_FILES[ $field_name ] ) ) {
			return new Torro_Error( 'missing_file', __( 'No file was provided to upload.', 'torro-forms' ) );
		}

		$post_id = 0;
		if ( isset( $args['post_id'] ) ) {
			$post_id = absint( $args['post_id'] );
			unset( $args['post_id'] );
		}

		$title = __( 'Form Submission', 'torro-forms' );
		if ( isset( $_POST['torro_form_id'] ) ) {
			$title = sprintf( __( 'Form Submission for %s', 'torro-forms' ), get_the_title( absint( $_POST['torro_form_id'] ) ) );
		}

		$attachment_data = array(
			'post_title'	=> $title,
		);

		if ( isset( $args['data'] ) ) {
			$attachment_data = array_merge( $attachment_data, $args['data'] );
			unset( $args['data'] );
		}

		$attachment_data['post_status'] = self::STATUS;

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		if ( ! function_exists( 'wp_read_image_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}
		if ( ! function_exists( 'media_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		if ( isset( $args['mimes'] ) && $args['mimes'] && ! isset( $args['test_type'] ) ) {
			$args['test_type'] = true;
		}

		$args = wp_parse_args( $args, array(
			'test_form'	=> false,
			'test_type'	=> false,
			'test_size'	=> true,
		) );

		if ( ! defined( 'TORRO_CREATE_FORM_UPLOAD_IMAGE_SIZES' ) || ! TORRO_CREATE_FORM_UPLOAD_IMAGE_SIZES ) {
			add_filter( 'intermediate_image_sizes_advanced', '__return_empty_array', 99 );
		}

		$id = media_handle_upload( $field_name, $post_id, $attachment_data, $args );

		if ( ! defined( 'TORRO_CREATE_FORM_UPLOAD_IMAGE_SIZES' ) || ! TORRO_CREATE_FORM_UPLOAD_IMAGE_SIZES ) {
			remove_filter( 'intermediate_image_sizes_advanced', '__return_empty_array', 99 );
		}

		if ( is_wp_error( $id ) ) {
			// the following line has no textdomain on purpose; it must use WP Core textdomain
			if ( 'upload_error' === $id->get_error_code() && __( 'Sorry, this file type is not permitted for security reasons.' ) === $id->get_error_message() ) {
				return new WP_Error( 'upload_error', __( 'This file type is not permitted.', 'torro-forms' ) );
			}
			return $id;
		} elseif ( $id < 1 ) {
			return new Torro_Error( 'unknown_error', __( 'An unknown error occurred while processing the file upload.', 'torro-forms' ) );
		}

		self::$aggregate_media[] = $id;

		return $id;
	}

	public static function delete_attachments_on_error( $errors ) {
		if ( 0 < count( self::$aggregate_media ) ) {
			foreach ( self::$aggregate_media as $id ) {
				wp_delete_attachment( $id, true );
			}
			self::$aggregate_media = array();
		}
	}
}

add_action( 'init', array( 'Torro_Form_Media', 'init' ) );
