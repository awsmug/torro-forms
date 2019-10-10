<?php
/**
 * Media element type class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Base;

use awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Element_Type;
use awsmug\Torro_Forms\DB_Objects\Elements\Element;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use WP_Error;

/**
 * Class representing a media element type.
 *
 * @since 1.0.0
 */
class Media extends Element_Type {

	/**
	 * Bootstraps the element type by setting properties.
	 *
	 * @since 1.0.0
	 */
	protected function bootstrap() {
		$this->slug        = 'media';
		$this->title       = __( 'Media', 'torro-forms' );
		$this->description = __( 'An element to upload a file to the media library.', 'torro-forms' );
		$this->icon_svg_id = 'torro-icon-media';

		$this->add_description_settings_field();
		$this->add_required_settings_field();
		$this->settings_fields['file_type'] = array(
			'section'     => 'settings',
			'type'        => 'select',
			'label'       => __( 'Valid file types', 'torro-forms' ),
			'description' => __( 'The file type the user is allowed to upload.', 'torro-forms' ),
			'choices'     => $this->get_type_dropdown_options(),
			'default'     => 'any',
		);
		$this->add_css_classes_settings_field();
	}

	/**
	 * Filters the array representation of a given element of this type.
	 *
	 * @since 1.0.0
	 *
	 * @param array           $data       Element data to filter.
	 * @param Element         $element    The element object to get the data for.
	 * @param Submission|null $submission Optional. Submission to get the values from, if available. Default null.
	 * @return array Array including all information for the element type.
	 */
	public function filter_json( $data, $element, $submission = null ) {
		$data = parent::filter_json( $data, $element, $submission );

		$data['value'] = absint( $data['value'] );

		$data['hidden_name']         = $data['input_attrs']['name'];
		$data['input_attrs']['name'] = $this->get_file_id( $element );

		return $data;
	}

	/**
	 * Formats values for an export.
	 *
	 * @since 1.0.0
	 *
	 * @param array   $values        Associative array of `$field => $value` pairs, with the main element field having the key '_main'.
	 * @param Element $element       Element the values belong to.
	 * @param string  $export_format Export format identifier. May be 'xls', 'csv', 'json', 'xml' or 'html'.
	 * @return array Associative array of `$column_slug => $column_value` pairs. The number of items and the column slugs
	 *               must match those returned from the get_export_columns() method.
	 */
	public function format_values_for_export( $values, $element, $export_format ) {
		$value = isset( $values['_main'] ) ? (int) $values['_main'] : 0;

		$skip_escaping = false;

		if ( ! empty( $value ) ) {
			$attachment = get_post( $value );

			if ( $attachment ) {
				$attachment_url = wp_get_attachment_url( $attachment->ID );

				/**
				 * Filters the URL to use when generating the export output for an attachment.
				 *
				 * @since 1.0.0
				 *
				 * @param string  $attachment_url URL to use for the attachment. Default is the edit URL.
				 * @param WP_Post $attachment     Attachment object the URL is for.
				 */
				$attachment_url = apply_filters( "{$this->manager->get_prefix()}export_attachment_url", $attachment_url, $attachment );
				$output = $attachment_url;

				if ( 'html' === $export_format ) {
					$skip_escaping = true;

					if ( 'image' === substr( $attachment->post_mime_type, 0, 5 ) ) {
						$image_url = wp_get_attachment_image_url( $attachment->ID, 'full' );
						$output = '<img src="' . $image_url . '" style="max-width:300px;height:auto;" />';
					}

					$output = '<a href="' . esc_url( $attachment_url ) . '">' . $output . '</a>';
				}
			} else {
				$output = __( 'File deleted.', 'torro-forms' );
			}
		} else {
			$output = __( 'No file uploaded.', 'torro-forms' );
		}

		/**
		 * Filters the value for export
		 *
		 * @since 1.0.5
		 *
		 * @param string  $value    Value to filter.
		 * @param Element $element  Element object.
		 */
		$output = apply_filters( "{$this->manager->get_prefix()}export_value", $output, $element );

		if ( ! $skip_escaping ) {
			$output = $this->escape_single_value_for_export( $output, $export_format );
		}

		return array(
			'element_' . $element->id . '__main' => $output,
		);
	}

	/**
	 * Validates a field value for an element.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed      $value      The value to validate. It is already unslashed when it arrives here.
	 * @param Element    $element    Element to validate the field value for.
	 * @param Submission $submission Submission the value belongs to.
	 * @return mixed|WP_Error Validated value, or error object on failure.
	 */
	public function validate_field( $value, $element, $submission ) {
		$settings = $this->get_settings( $element );

		$value = absint( $value );

		$file_id = $this->get_file_id( $element );

		$has_file = ! empty( $_FILES[ $file_id ] ) && ( empty( $_FILES[ $file_id ]['error'] ) || 4 !== (int) $_FILES[ $file_id ]['error'] );

		if ( ! empty( $settings['required'] ) && 'no' !== $settings['required'] && empty( $value ) && ! $has_file ) {
			return $this->create_error( Element_Type::ERROR_CODE_REQUIRED, __( 'You must upload a file.', 'torro-forms' ), $value );
		}

		if ( $has_file ) {
			$form = $submission->get_form();

			$mimes = null;
			if ( ! empty( $settings['file_type'] ) && 'any' !== $settings['file_type'] ) {
				$mimes = array();

				if ( 0 === strpos( $settings['file_type'], 'type_' ) ) {
					$settings['file_type'] = substr( $settings['file_type'], 5 );

					$media_types = $this->get_media_types();
					if ( isset( $media_types[ $settings['file_type'] ] ) ) {
						$file_mime_types = $this->get_file_mime_types();

						foreach ( $media_types[ $settings['file_type'] ] as $extension ) {
							if ( isset( $file_mime_types[ $extension ] ) ) {
								$mimes[ $extension ] = $file_mime_types[ $extension ];
							}
						}
					}
				} else {
					$file_mime_types = $this->get_file_mime_types();

					if ( isset( $file_mime_types[ $extension ] ) ) {
						$mimes[ $extension ] = $file_mime_types[ $extension ];
					}
				}
			}

			$attachment_id = torro()->form_uploads()->upload_file( $file_id, $submission, $form, $element->id, '_main', $mimes );

			if ( ! empty( $value ) ) {
				if ( is_wp_error( $attachment_id ) ) {
					$attachment_id->add_data( array( 'validated_value' => $value ) );
				} else {
					wp_delete_attachment( $value, true );
				}
			}

			return $attachment_id;
		}

		return $value;
	}

	/**
	 * Gets the fields arguments for an element of this type when editing submission values in the admin.
	 *
	 * @since 1.0.0
	 *
	 * @param Element $element Element to get fields arguments for.
	 * @return array An associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_edit_submission_fields_args( $element ) {
		$fields = parent::get_edit_submission_fields_args( $element );

		$slug = $this->get_edit_submission_field_slug( $element->id );

		$fields[ $slug ]['type']  = 'media';
		$fields[ $slug ]['store'] = 'id';

		$settings = $this->get_settings( $element );

		if ( ! empty( $settings['file_type'] ) && 'any' !== $settings['file_type'] ) {
			if ( 0 === strpos( $settings['file_type'], 'type_' ) ) {
				$field[ $slug ]['mime_types'] = substr( $settings['file_type'], 5 );
			} else {
				$field[ $slug ]['mime_types'] = $settings['file_type'];
			}
		}

		return $fields;
	}

	/**
	 * Gets the ID under which to store the upload in $_FILES.
	 *
	 * @since 1.0.0
	 *
	 * @param Element $element Element for which to get the file ID.
	 * @return string File ID.
	 */
	protected function get_file_id( $element ) {
		return $this->manager->get_prefix() . 'media_upload_' . $element->id . '__main';
	}

	/**
	 * Gets the available dropdown options to specify the file type.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of `$value => $label` pairs.
	 */
	protected function get_type_dropdown_options() {
		$media_types = array(
			'type_image'       => __( 'Images (.jpg, .png, ...)', 'torro-forms' ),
			'type_audio'       => __( 'Audio files (.wav, .mp3, ...)', 'torro-forms' ),
			'type_video'       => __( 'Video files (.avi, .mpeg, ...)', 'torro-forms' ),
			'type_document'    => __( 'Documents (.doc, .pdf, ...)', 'torro-forms' ),
			'type_spreadsheet' => __( 'Spreadsheets (.xls, .numbers, ...)', 'torro-forms' ),
			'type_interactive' => __( 'Interactive (.ppt, .swf, ...)', 'torro-forms' ),
			'type_text'        => __( 'Text files (.txt, .csv, ...)', 'torro-forms' ),
			'type_archive'     => __( 'File archives (.zip, .rar, ...)', 'torro-forms' ),
			'type_code'        => __( 'Source code (.html, .js, ...)', 'torro-forms' ),
		);

		$file_extensions = array_keys( $this->get_file_mime_types() );

		$media_sub_types = array_combine(
			$file_extensions,
			array_map(
				function( $extension ) {
					return '.' . $extension;
				},
				$file_extensions
			)
		);

		return array_merge( array( 'any' => __( 'Any', 'torro-forms' ) ), $media_types, $media_sub_types );
	}

	/**
	 * Gets the available file mime types.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of `$file_extension => $mime_type` pairs.
	 */
	protected function get_file_mime_types() {
		$mime_types = wp_get_mime_types();

		$file_types = array();
		foreach ( $mime_types as $extensions => $mime_type ) {
			$extensions = explode( '|', $extensions );
			foreach ( $extensions as $extension ) {
				$file_types[ $extension ] = $mime_type;
			}
		}

		return $file_types;
	}

	/**
	 * Gets the available media types.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of `$media_type => $file_extensions` pairs.
	 */
	protected function get_media_types() {
		return wp_get_ext_types();
	}
}
