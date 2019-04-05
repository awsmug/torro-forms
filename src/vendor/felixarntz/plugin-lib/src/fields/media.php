<?php
/**
 * Media field class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Fields;

use WP_Error;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Fields\Media' ) ) :

	/**
	 * Class for a media field.
	 *
	 * @since 1.0.0
	 */
	class Media extends Text_Base {
		/**
		 * Field type identifier.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $slug = 'media';

		/**
		 * Backbone view class name to use for this field.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $backbone_view = 'MediaFieldView';

		/**
		 * What type of data to store in the field value.
		 *
		 * Accepts either 'id' or 'url'.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $store = 'id';

		/**
		 * MIME Types to allow.
		 *
		 * @since 1.0.0
		 * @var string|array
		 */
		protected $mime_types = 'all';

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param Field_Manager $manager Field manager instance.
		 * @param string        $id      Field identifier.
		 * @param array         $args    {
		 *     Optional. Field arguments. Anything you pass in addition to the default supported arguments
		 *     will be used as an attribute on the input. Default empty array.
		 *
		 *     @type string          $section       Section identifier this field belongs to. Default empty.
		 *     @type string          $label         Field label. Default empty.
		 *     @type string          $description   Field description. Default empty.
		 *     @type mixed           $default       Default value for the field. Default null.
		 *     @type bool|int        $repeatable    Whether this should be a repeatable field. An integer can also
		 *                                          be passed to set the limit of repetitions allowed. Default false.
		 *     @type array           $input_classes Array of CSS classes for the field input. Default empty array.
		 *     @type array           $label_classes Array of CSS classes for the field label. Default empty array.
		 *     @type callable        $validate      Custom validation callback. Will be executed after doing the regular
		 *                                          validation if no errors occurred in the meantime. Default none.
		 *     @type callable|string $before        Callback or string that should be used to generate output that will
		 *                                          be printed before the field. Default none.
		 *     @type callable|string $after         Callback or string that should be used to generate output that will
		 *                                          be printed after the field. Default none.
		 * }
		 */
		public function __construct( $manager, $id, $args = array() ) {
			if ( isset( $args['data-store'] ) && ! isset( $args['store'] ) ) {
				$args['store'] = $args['data-store'];
			}

			if ( isset( $args['store'] ) && 'url' !== $args['store'] || ! isset( $args['store'] ) ) {
				$args['store'] = 'id';
			}

			$args['data-store'] = $args['store'];

			if ( isset( $args['mime_types'] ) ) {
				$mime_types = $this->verify_mime_types( $args['mime_types'] );
				if ( ! empty( $mime_types ) ) {
					if ( isset( $args['data-query'] ) ) {
						$args['data-query']['post_mime_type'] = $mime_types;
					} else {
						$args['data-query'] = array( 'post_mime_type' => $mime_types );
					}
				}
			}

			parent::__construct( $manager, $id, $args );
		}

		/**
		 * Enqueues the necessary assets for the field.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array where the first element is an array of script handles and the second element
		 *               is an associative array of data to pass to the main script.
		 */
		public function enqueue() {
			$ret = parent::enqueue();

			$assets = $this->manager->library_assets();

			if ( ! empty( $GLOBALS['post'] ) ) {
				wp_enqueue_media( array( 'post' => $GLOBALS['post']->ID ) );
			} else {
				wp_enqueue_media();
			}

			$mediapicker_version = '0.7.2';

			$assets->register_style(
				'wp-media-picker',
				'node_modules/wp-media-picker/wp-media-picker.css',
				array(
					'ver'     => $mediapicker_version,
					'enqueue' => true,
				)
			);

			$assets->register_script(
				'wp-media-picker',
				'node_modules/wp-media-picker/wp-media-picker.js',
				array(
					'deps'      => array( 'jquery', 'jquery-ui-widget', 'media-editor' ),
					'ver'       => $mediapicker_version,
					'in_footer' => true,
					'enqueue'   => true,
				)
			);

			$ret[0][] = 'wp-media-picker';
			$ret[1]   = array_merge(
				$ret[1],
				array(
					'i18nMediaAddButton'     => $this->manager->get_message( 'field_media_add_button' ),
					'i18nMediaReplaceButton' => $this->manager->get_message( 'field_media_replace_button' ),
					'i18nMediaRemoveButton'  => $this->manager->get_message( 'field_media_remove_button' ),
					'i18nMediaModalHeading'  => $this->manager->get_message( 'field_media_modal_heading' ),
					'i18nMediaModalButton'   => $this->manager->get_message( 'field_media_modal_button' ),
				)
			);

			return $ret;
		}

		/**
		 * Transforms single field data into an array to be passed to JavaScript applications.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $current_value Current value of the field.
		 * @return array Field data to be JSON-encoded.
		 */
		protected function single_to_json( $current_value ) {
			$data = parent::single_to_json( $current_value );

			$data['store']     = $this->store;
			$data['mimeTypes'] = $this->mime_types;

			return $data;
		}

		/**
		 * Validates a single value for the field.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $value Value to validate. When null is passed, the method
		 *                     assumes no value was sent.
		 * @return mixed|WP_Error The validated value on success, or an error
		 *                        object on failure.
		 */
		protected function validate_single( $value = null ) {
			$value = parent::validate_single( $value );
			if ( is_wp_error( $value ) ) {
				return $value;
			}

			$orig_value = $value;
			if ( 'url' === $this->store && ! empty( $value ) ) {
				$orig_value = esc_url_raw( $orig_value );

				$value = attachment_url_to_postid( $orig_value );
				if ( ! $value ) {
					return new WP_Error( 'field_media_invalid_url', sprintf( $this->manager->get_message( 'field_media_invalid_url' ), $orig_value, $this->label ) );
				}
			} else {
				$orig_value = absint( $orig_value );
				$value      = $orig_value;
			}

			if ( 'attachment' !== get_post_type( $value ) ) {
				return new WP_Error( 'field_media_invalid_post_type', sprintf( $this->manager->get_message( 'field_media_invalid_post_type' ), $value, $this->label ) );
			}

			if ( ! $this->check_filetype( $value, $this->mime_types ) ) {
				$valid_formats = is_array( $this->mime_types ) ? implode( ', ', $this->mime_types ) : $this->mime_types;
				return new WP_Error( 'field_media_invalid_mime_type', sprintf( $this->manager->get_message( 'field_media_invalid_mime_type' ), $value, $this->label, $valid_formats ) );
			}

			return $orig_value;
		}

		/**
		 * Checks a filetype of an attachment.
		 *
		 * @since 1.0.0
		 *
		 * @param int          $id             Attachment ID.
		 * @param string|array $accepted_types Optional. One or more accepted types. Default 'all' to allow everything.
		 * @return bool True if the filetype is valid, false otherwise.
		 */
		protected function check_filetype( $id, $accepted_types = 'all' ) {
			$extension = $this->get_attachment_extension( $id );

			if ( $extension ) {
				return $this->check_extension( $extension, $accepted_types );
			}

			return false;
		}

		/**
		 * Returns the file extension of an attachment.
		 *
		 * @since 1.0.0
		 *
		 * @param int $id Attachment ID.
		 * @return string|bool File extension, or false if none could be detected.
		 */
		protected function get_attachment_extension( $id ) {
			$filename = get_attached_file( $id );

			if ( $filename ) {
				$extension = wp_check_filetype( $filename );
				$extension = $extension['ext'];
				if ( $extension ) {
					return $extension;
				}
			}

			return false;
		}

		/**
		 * Checks the extension of an attachment.
		 *
		 * @since 1.0.0
		 *
		 * @param string       $extension      Attachment file extension.
		 * @param string|array $accepted_types Optional. One or more accepted types. Default 'all' to allow everything.
		 * @return bool True if the file extension is valid, false otherwise.
		 */
		protected function check_extension( $extension, $accepted_types = 'all' ) {
			if ( 'all' === $accepted_types || ! $accepted_types ) {
				return true;
			}

			if ( ! is_array( $accepted_types ) ) {
				$accepted_types = array( $accepted_types );
			}

			// Check the file extension.
			if ( in_array( strtolower( $extension ), $accepted_types, true ) ) {
				return true;
			}

			// Check the file type.
			$type = wp_ext2type( $extension );
			if ( null !== $type && in_array( $type, $accepted_types, true ) ) {
				return true;
			}

			// Check the file MIME type.
			$allowed_mime_types = $this->get_all_mime_types();
			if ( isset( $allowed_mime_types[ $extension ] ) ) {
				if ( in_array( $allowed_mime_types[ $extension ], $accepted_types, true ) ) {
					return true;
				}

				// Check the general file MIME type.
				$general_type = explode( '/', $allowed_mime_types[ $extension ] )[0];
				if ( in_array( $general_type, $accepted_types, true ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Verifies a list of passed MIME types for usage in a query.
		 *
		 * The function ensures that only valid MIME types (full or only general) are provided.
		 * File extensions are parsed into their MIME types while invalid MIME types are stripped out.
		 *
		 * @since 1.0.0
		 *
		 * @param string|array $accepted_types One or more accepted types. Providing 'all' will allow everything, returning an empty array.
		 * @return array An array of valid MIME types, or an empty array if there are no restrictions.
		 */
		protected function verify_mime_types( $accepted_types ) {
			if ( 'all' === $accepted_types ) {
				return array();
			}

			$validated_mime_types = array();

			if ( ! is_array( $accepted_types ) ) {
				$accepted_types = array( $accepted_types );
			}

			$allowed_mime_types = $this->get_all_mime_types();

			foreach ( $accepted_types as $mime_type ) {
				if ( false === strpos( $mime_type, '/' ) ) {
					switch ( $mime_type ) {
						case 'document':
						case 'spreadsheet':
						case 'interactive':
						case 'archive':
							// Documents, spreadsheets, interactive and archive are always MIME type application.
							$validated_mime_types[] = 'application';
							break;
						case 'code':
							// Code is always MIME type text.
							$validated_mime_types[] = 'text';
							break;
						case 'image':
						case 'audio':
						case 'video':
						case 'text':
						case 'application':
							// A valid MIME type.
							$validated_mime_types[] = $mime_type;
							break;
						default:
							if ( isset( $allowed_mime_types[ $mime_type ] ) ) {
								// A MIME type for a file extension.
								$validated_mime_types[] = $allowed_mime_types[ $mime_type ];
							}
					}
				} elseif ( in_array( $mime_type, $allowed_mime_types, true ) ) {
					// A fully qualified MIME type (with subtype).
					$validated_mime_types[] = $mime_type;
				}
			}

			return array_unique( $validated_mime_types );
		}

		/**
		 * Returns all MIME types allowed by WordPress.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of allowed MIME types as `$extension => $mime_type` pairs.
		 */
		protected function get_all_mime_types() {
			$mime_types = array();

			$_mime_types = wp_get_mime_types();

			foreach ( $_mime_types as $_extensions => $_mime_type ) {
				$extensions = explode( '|', $_extensions );
				foreach ( $extensions as $extension ) {
					$mime_types[ $extension ] = $_mime_type;
				}
			}

			return $mime_types;
		}
	}

endif;
