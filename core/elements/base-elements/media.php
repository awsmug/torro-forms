<?php
/**
 * Text Form Element
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core/Elements
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

final class Torro_Form_Element_Media extends Torro_Form_Element {
	private static $instances = array();

	public static function instance( $id = null ) {
		$slug = $id;
		if ( null === $slug ) {
			$slug = 'CLASS';
		}
		if ( ! isset( self::$instances[ $slug ] ) ) {
			self::$instances[ $slug ] = new self( $id );
		}
		return self::$instances[ $slug ];
	}

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct( $id = null ) {
		parent::__construct( $id );
	}

	protected function init() {
		$this->type = $this->name = 'media';
		$this->title = __( 'Media', 'torro-forms' );
		$this->description = __( 'Add an Element to allow file uploads.', 'torro-forms' );
		$this->icon_url = torro()->get_asset_url( 'icon-media', 'png' );

		$this->upload = true;
	}

	public function input_html() {
		$html  = '<label for="' . $this->get_input_name() . '">' . esc_html( $this->label ) . '</label>';

		$html .= '<input type="file" name="' . $this->get_input_name() . '" />';

		if ( ! empty( $this->settings['description'] ) ) {
			$html .= '<small>';
			$html .= esc_html( $this->settings['description']->value );
			$html .= '</small>';
		}

		return $html;
	}

	public function settings_fields() {
		$_input_types = $this->get_input_types();
		$input_types = array();
		foreach ( $_input_types as $value => $data ) {
			if ( ! isset( $data['title'] ) || ! $data['title'] ) {
				continue;
			}
			$input_types[ $value ] = $data['title'];
		}

		$this->settings_fields = array(
			'description'	=> array(
				'title'			=> __( 'Description', 'torro-forms' ),
				'type'			=> 'textarea',
				'description'	=> __( 'The description will be shown after the Element.', 'torro-forms' ),
				'default'		=> ''
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
			'type_code'			=> __( 'Source code (.html, .js, ...)', 'torro-forms' ),
		);

		$file_extensions = array_keys( $this->get_file_mime_types() );

		$media_sub_types = array_combine( $file_extensions, array_map( array( $this, 'dotprefix' ), $file_extensions ) );

		return array_merge( array( 'any' => __( 'Any', 'torro-forms' ) ), $media_types, $media_sub_types );
	}

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

	protected function get_media_types() {
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

	public function validate( $input ) {
		if ( ! $input ) {
			// this denotes a non-existing attachment
			return 0;
		}

		$name = $this->get_input_name();

		if ( ! isset( $_FILES[ $name ] ) ) {
			return new Torro_Error( 'missing_file', __( 'No file was provided to upload.', 'torro-forms' ) );
		}

		$file_type = $this->settings['file_type']->value;

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

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		if ( ! function_exists( 'wp_read_image_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}
		if ( ! function_exists( 'media_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		$post_data = array(
			'post_title'	=> sprintf( __( 'Form Submission for %s', 'torro-forms' ), get_the_title() ),
			'post_status'	=> 'torro-forms-upload'
		);

		$overrides = array(
			'test_form'	=> false,
			'test_size'	=> true,
			'test_type'	=> true,
			'mimes'		=> $mimes,
		);

		$id = media_handle_upload( $name, 0, $post_data, $overrides );

		if ( is_wp_error( $id ) ) {
			return $id;
		} elseif ( $id < 1 ) {
			return new Torro_Error( 'unknown_error', __( 'An unknown error occurred while processing the file upload.', 'torro-forms' ) );
		}

		return $id;
	}

	private function dotprefix( $extension ) {
		return '.' . $extension;
	}
}

torro()->elements()->register( 'Torro_Form_Element_Media' );
