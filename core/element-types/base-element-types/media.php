<?php
/**
 * Core: Torro_Element_Type_Media class
 *
 * @package TorroForms
 * @subpackage CoreElements
 * @version 1.0.0-beta.6
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Element type class for a media upload field
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Element_Type_Media extends Torro_Element_Type {
	/**
	 * Initializing
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->name = 'media';
		$this->title = __( 'Media', 'torro-forms' );
		$this->description = __( 'Add an element to allow file uploads.', 'torro-forms' );
		$this->icon_url = torro()->get_asset_url( 'icon-upload', 'png' );

		$this->upload = true;
	}

	/**
	 * Prepares data to render the element type HTML output.
	 *
	 * @since 1.0.0
	 *
	 * @param Torro_Element $element
	 *
	 * @return array
	 */
	public function to_json( $element ) {
		$data = parent::to_json( $element );
		$data['type'] = 'file';

		return $data;
	}

	/**
	 * Settings fields
	 *
	 * @since 1.0.0
	 */
	protected function settings_fields() {
		$this->settings_fields = array(
			'description'	=> array(
				'title'			=> __( 'Description', 'torro-forms' ),
				'type'			=> 'textarea',
				'description'	=> __( 'The description will be shown after the element.', 'torro-forms' ),
				'default'		=> ''
			),
			'required'		=> array(
				'title'			=> __( 'Required?', 'torro-forms' ),
				'type'			=> 'radio',
				'values'		=> array(
					'yes'			=> __( 'Yes', 'torro-forms' ),
					'no'			=> __( 'No', 'torro-forms' ),
				),
				'description'	=> __( 'Whether the user must upload a file.', 'torro-forms' ),
				'default'		=> 'no',
			),
			'file_type'		=> array(
				'title'			=> __( 'Valid file types', 'torro-forms' ),
				'type'			=> 'select',
				'values'		=> $this->get_type_dropdown_options(),
				'description'	=> __( 'The file type that the user is allowed to upload.', 'torro-forms' ),
				'default'		=> 'any',
			),
		);
	}

	/**
	 * Dropdown options
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected function get_type_dropdown_options() {
		$media_types = array(
			'type_image'		=> __( 'Images (.jpg, .png, ...)', 'torro-forms' ),
			'type_audio'		=> __( 'Audio files (.wav, .mp3, ...)', 'torro-forms' ),
			'type_video'		=> __( 'Video files (.avi, .mpeg, ...)', 'torro-forms' ),
			'type_documents'	=> __( 'Documents (.doc, .pdf, ...)', 'torro-forms' ),
			'type_spreadsheet'	=> __( 'Spreadsheets (.xls, .numbers, ...)', 'torro-forms' ),
			'type_interactive'	=> __( 'Interactive (.ppt, .swf, ...)', 'torro-forms' ),
			'type_text'			=> __( 'Text files (.txt, .csv, ...)', 'torro-forms' ),
			'type_archive'		=> __( 'File archives (.zip, .rar, ...)', 'torro-forms' ),
			'type_code'			=> __( 'Sourcecode (.html, .js, ...)', 'torro-forms' ),
		);

		$file_extensions = array_keys( $this->get_file_mime_types() );

		$media_sub_types = array_combine( $file_extensions, array_map( array( $this, 'dotprefix' ), $file_extensions ) );

		return array_merge( array( 'any' => __( 'Any', 'torro-forms' ) ), $media_types, $media_sub_types );
	}

	/**
	 * Getting mime types
	 *
	 * @return array
	 * @since 1.0.0
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
	 * Getting media types
	 *
	 * @return mixed|null|void
	 * @since 1.0.0
	 */
	protected function get_media_types() {
		// WordPress >= 4.6 has this function
		if ( function_exists( 'wp_get_ext_types' ) ) {
			return wp_get_ext_types();
		}

		// for older versions, manually implement the function

		// this filter is a WP Core filter reapplied here (see `wp_ext2type()`)
		$ext2type = apply_filters( 'ext2type', array(
			'image'       => array( 'jpg', 'jpeg', 'jpe',  'gif',  'png',  'bmp',   'tif',  'tiff', 'ico' ),
			'audio'       => array( 'aac', 'ac3',  'aif',  'aiff', 'm3a',  'm4a',   'm4b',  'mka',  'mp1',  'mp2',  'mp3', 'ogg', 'oga', 'ram', 'wav', 'wma' ),
			'video'       => array( '3g2',  '3gp', '3gpp', 'asf', 'avi',  'divx', 'dv',   'flv',  'm4v',   'mkv',  'mov',  'mp4',  'mpeg', 'mpg', 'mpv', 'ogm', 'ogv', 'qt',  'rm', 'vob', 'wmv' ),
			'document'    => array( 'doc', 'docx', 'docm', 'dotm', 'odt',  'pages', 'pdf',  'xps',  'oxps', 'rtf',  'wp', 'wpd', 'psd', 'xcf' ),
			'spreadsheet' => array( 'numbers',     'ods',  'xls',  'xlsx', 'xlsm',  'xlsb' ),
			'interactive' => array( 'swf', 'key',  'ppt',  'pptx', 'pptm', 'pps',   'ppsx', 'ppsm', 'sldx', 'sldm', 'odp' ),
			'text'        => array( 'asc', 'csv',  'tsv',  'txt' ),
			'archive'     => array( 'bz2', 'cab',  'dmg',  'gz',   'rar',  'sea',   'sit',  'sqx',  'tar',  'tgz',  'zip', '7z' ),
			'code'        => array( 'css', 'htm',  'html', 'php',  'js' ),
		) );

		return $ext2type;
	}

	/**
	 * Validating input
	 *
	 * @param $input
	 *
	 * @return int|Torro_Error|WP_Error
	 * @since 1.0.0
	 */
	public function validate( $input, $element ) {
		if ( ( ! isset( $element->settings['required'] ) || 'no' === $element->settings['required']->value ) && ( ! $input || ! $input['name'] ) ) {
			// this denotes a non-existing attachment
			return 0;
		}

		$name = $this->get_input_name( $element );

		$file_type = 'any';
		if ( isset( $element->settings['file_type'] ) ) {
			$file_type = $element->settings['file_type']->value;
		}

		$mimes = array();

		if ( 0 === strpos( $file_type, 'type_' ) ) {
			$file_type = substr( $file_type, 5 );
			$media_types = $this->get_media_types();
			if ( isset( $media_types[ $file_type ] ) ) {
				$file_mime_types = $this->get_file_mime_types();
				foreach ( $media_types[ $file_type ] as $extension ) {
					if ( isset( $file_mime_types[ $extension ] ) ) {
						$mimes[ $extension ] = $file_mime_types[ $extension ];
					}
				}
			}
		} elseif ( 'any' !== $file_type ) {
			$file_mime_types = $this->get_file_mime_types();
			if ( isset( $file_mime_types[ $extension ] ) ) {
				$mimes[ $extension ] = $file_mime_types[ $extension ];
			}
		}

		return Torro_Form_Media::upload( $name, array(
			'mimes'		=> $mimes,
		) );
	}

	public function render_value( $value ) {
		if ( ! $value || ! ( $attachment = get_post( $value ) ) ) {
			return __( 'No file uploaded.', 'torro-forms' );
		}

		$output = wp_basename( get_attached_file( $attachment->ID ) );
		if ( 'image' === substr( $attachment->post_mime_type, 0, 5 ) ) {
			$src = wp_get_attachment_image_src( $attachment->ID, 'full' );
			if ( is_array( $src ) && $src[0] ) {
				$output = '<img src="' . $src[0] . '" style="max-width:300px;height:auto;" />';
			}
		}

		$url = get_edit_post_link( $attachment->ID );

		return '<a href="' . $url . '">' . $output . '</a>';
	}

	public function render_value_for_export( $value ) {
		if ( ! $value || ! ( $attachment = get_post( $value ) ) ) {
			return __( 'No file uploaded.', 'torro-forms' );
		}

		return wp_get_attachment_url( $attachment->ID );
	}

	/**
	 * Returning dot prefix
	 *
	 * @param $extension
	 *
	 * @return string
	 * @since 1.0.0
	 */
	private function dotprefix( $extension ) {
		return '.' . $extension;
	}
}

torro()->element_types()->register( 'Torro_Element_Type_Media' );
