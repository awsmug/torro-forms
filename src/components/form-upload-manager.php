<?php
/**
 * Form upload manager class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Components;

use Leaves_And_Love\Plugin_Lib\Service;
use Leaves_And_Love\Plugin_Lib\Traits\Container_Service_Trait;
use awsmug\Torro_Forms\DB_Objects\Taxonomy_Manager;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use WP_Error;

/**
 * Class for managing media files to upload for a form submission.
 *
 * @since 1.0.0
 */
class Form_Upload_Manager extends Service {
	use Container_Service_Trait;

	/**
	 * The taxonomy manager service definition.
	 *
	 * @since 1.0.0
	 * @static
	 * @var string
	 */
	protected static $service_taxonomies = Taxonomy_Manager::class;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $prefix   Instance prefix.
	 * @param array  $services {
	 *     Array of service instances.
	 *
	 *     @type Taxonomy_Manager $taxonomies    The taxonomy manager class instance.
	 *     @type Error_Handler    $error_handler The error handler instance.
	 * }
	 */
	public function __construct( $prefix, $services ) {
		$this->set_prefix( $prefix );
		$this->set_services( $services );
	}

	/**
	 * Uploads a new file for a specific form submission and element.
	 *
	 * @since 1.0.0
	 *
	 * @param string     $file_id          Identifier to look for in $_FILES.
	 * @param Submission $submission       Submission object.
	 * @param Form       $form             Form object.
	 * @param int        $element_id       Element ID.
	 * @param string     $field            Optional. Element field slug. Default is '_main'.
	 * @param array      $allowed_mimes    Optional. Allowed MIME types. Default are all MIME types that WordPress core allows.
	 * @param int        $allowed_filesize Optional. Allowed maximum file size. Default is no limit other than WordPress core restrictions.
	 * @return int|WP_Error Attachment ID for the new file, or error object on failure.
	 */
	public function upload_file( $file_id, $submission, $form, $element_id, $field = '_main', $allowed_mimes = null, $allowed_filesize = null ) {
		if ( ! isset( $_FILES[ $file_id ] ) ) {
			return new WP_Error( 'missing_file', __( 'No file was provided to upload.', 'torro-forms' ) );
		}

		$prefix = $this->get_prefix();

		if ( ! $field ) {
			$field = '_main';
		}

		$attachment_data = array(
			/* translators: 1: submission ID, 2: form title */
			'post_title' => sprintf( __( 'Form upload for submission #%1$s (form &#8220;%2$s&#8221;)', 'torro-forms' ), $submission->id, $form->title ),
			'meta_input' => array(
				$prefix . 'parent_submission_id' => $submission->id,
				$prefix . 'parent_form_id'       => $form->id,
				$prefix . 'parent_element_id'    => $element->id,
				$prefix . 'parent_element_field' => $field,
			),
		);

		$post_id = 0;
		if ( $this->should_set_parent_form( $form->id ) ) {
			$post_id = $form->id;
		}

		$overrides = array(
			'mimes'     => $allowed_mimes,
			'test_form' => false,
			'test_type' => true,
			'test_size' => true,
		);

		if ( $allowed_filesize ) {
			$filesize = isset( $_FILES[ $file_id ]['size'] ) ? (int) $_FILES[ $file_id ]['size'] : filesize( wp_unslash( $_FILES[ $file_id ]['tmp_name'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if ( (int) $filesize > (int) $allowed_filesize ) {
				return new WP_Error( 'upload_error', __( 'The file exceeds the maximum allowed size.', 'torro-forms' ) );
			}
		}

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		if ( ! function_exists( 'wp_read_image_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}
		if ( ! function_exists( 'media_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		$added_filter = false;
		if ( ! $this->should_generate_image_sizes( $form->id ) ) {
			$added_filter = true;
			add_filter( 'intermediate_image_sizes_advanced', '__return_empty_array', 9999 );
		}

		$attachment_id = media_handle_upload( $file_id, $post_id, $attachment_data, $overrides );

		if ( $added_filter ) {
			remove_filter( 'intermediate_image_sizes_advanced', '__return_empty_array', 9999 );
		}

		if ( is_wp_error( $attachment_id ) ) {
			// The following line has no textdomain on purpose as it's a WP core message.
			if ( $allowed_mimes && 'upload_error' === $attachment_id->get_error_code() && __( 'Sorry, this file type is not permitted for security reasons.' ) === $attachment_id->get_error_message() ) { // @codingStandardsIgnoreLine
				return new WP_Error( 'upload_error', __( 'The file type is not permitted.', 'torro-forms' ) );
			}

			return $attachment_id;
		}

		if ( ! $attachment_id ) {
			return new WP_Error( 'upload_error', __( 'The file could not be registered with the database.', 'torro-forms' ) );
		}

		$attachment_id = (int) $attachment_id;

		// Set the default attachment taxonomy term for form uploads.
		$taxonomy_slug = $this->taxonomies()->get_attachment_taxonomy_slug();
		if ( ! empty( $taxonomy_slug ) ) {
			$taxonomy_term_id = $this->taxonomies()->get_attachment_taxonomy_term_id();
			if ( ! empty( $taxonomy_term_id ) ) {
				wp_set_post_terms( $attachment_id, array( $taxonomy_term_id ), $taxonomy_slug );
			}
		}

		return $attachment_id;
	}

	/**
	 * Deletes old files for a specific form submission and element.
	 *
	 * This should be used when a user uploads a new file for a field, to prevent database
	 * clutter from the now unneeded files previously uploaded.
	 *
	 * @since 1.0.0
	 *
	 * @param Submission $submission Submission object.
	 * @param Form       $form       Form object.
	 * @param int        $element_id Element ID.
	 * @param string     $field      Optional. Element field slug. Default is '_main'.
	 * @param int        $ignore_id  Optional. New attachment ID, so that it is not deleted. Default none.
	 * @return array Array where each element is either the deleted attachment ID, or an error object indicating a deletion failure.
	 *               May also be empty in case nothing needed to be deleted.
	 */
	public function delete_old_files( $submission, $form, $element_id, $field = '_main', $ignore_id = 0 ) {
		$prefix = $this->get_prefix();

		if ( ! $field ) {
			$field = '_main';
		}

		$args = array(
			'fields'         => 'ids',
			'posts_per_page' => 20,
			'no_found_rows'  => true,
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'meta_query'     => array( // WPCS: Slow query OK.
				'relation' => 'AND',
				array(
					'key'   => $prefix . 'parent_submission_id',
					'value' => $submission->id,
					'type'  => 'UNSIGNED',
				),
				array(
					'key'   => $prefix . 'parent_element_id',
					'value' => $element->id,
					'type'  => 'UNSIGNED',
				),
				array(
					'key'   => $prefix . 'parent_element_field',
					'value' => $field,
					'type'  => 'CHAR',
				),
			),
		);

		if ( ! empty( $ignore_id ) ) {
			$args['post__not_in'] = array( $ignore_id );
		}

		$attachment_ids = get_posts( $args );

		$result = array();
		foreach ( $attachment_ids as $attachment_id ) {
			$post = wp_delete_attachment( $attachment_id, true );
			if ( ! $post ) {
				$result[] = new WP_Error( 'delete_error', __( 'The file could not be deleted.', 'torro-forms' ) );
			} else {
				$result[] = $post->ID;
			}
		}

		return $result;
	}

	/**
	 * Checks whether form uploads should have the form they're uploaded for set as their parent.
	 *
	 * @since 1.0.0
	 *
	 * @param int $form_id Form ID for which to check this.
	 * @return bool True if the form should be set as parent, false otherwise.
	 */
	protected function should_set_parent_form( $form_id ) {
		$result = false;

		/**
		 * Filters whether form uploads should have the form they're uploaded for set as their parent.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $result  True if the form should be set as parent, false otherwise. Default false.
		 * @param int  $form_id Form ID for which to check this.
		 */
		return apply_filters( "{$this->get_prefix()}form_uploads_should_set_parent_form", $result, $form_id );
	}

	/**
	 * Checks whether the typical image sizes should be generated for form uploads.
	 *
	 * @since 1.0.0
	 *
	 * @param int $form_id Form ID for which to check this.
	 * @return bool True if image sizes should be generated, false otherwise.
	 */
	protected function should_generate_image_sizes( $form_id ) {
		$result = false;

		/**
		 * Filters whether the typical image sizes should be generated for form upload images.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $result  True if image sizes should be generated, false otherwise. Default false.
		 * @param int  $form_id Form ID for which to check this.
		 */
		return apply_filters( "{$this->get_prefix()}form_uploads_should_generate_image_sizes", $result, $form_id );
	}
}
