<?php
/**
 * WYSIWYG field class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Fields;

use WP_Error;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Fields\WYSIWYG' ) ) :

	/**
	 * Class for a WYSIWYG field.
	 *
	 * @since 1.0.0
	 */
	class WYSIWYG extends Textarea {
		/**
		 * Field type identifier.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $slug = 'wysiwyg';

		/**
		 * Backbone view class name to use for this field.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $backbone_view = 'WYSIWYGFieldView';

		/**
		 * Whether to use wpautop() for the content.
		 *
		 * @since 1.0.0
		 * @var bool
		 */
		protected $wpautop = false;

		/**
		 * Whether to show buttons for adding media files.
		 *
		 * @since 1.0.0
		 * @var bool
		 */
		protected $media_buttons = false;

		/**
		 * The editor button mode. Either 'regular' or 'simple'. Default 'regular'.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $button_mode = 'regular';

		/**
		 * Stores the editor markup for internal usage.
		 *
		 * @since 1.0.0
		 * @var array()|null
		 */
		protected $editor_markup = null;

		/**
		 * Stores editor settings for internal usage.
		 *
		 * @since 1.0.0
		 * @var array|null
		 */
		protected $editor_settings = null;

		/**
		 * Stores TinyMCE settings for internal usage.
		 *
		 * @since 1.0.0
		 * @var array|null
		 */
		protected $tinymce_settings = null;

		/**
		 * Stores QuickTags settings for internal usage.
		 *
		 * @since 1.0.0
		 * @var array|null
		 */
		protected $quicktags_settings = null;

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
			if ( version_compare( $GLOBALS['wp_version'], '4.8', '>=' ) ) {
				$data_args = array(
					'wpautop'       => false,
					'media_buttons' => false,
					'button_mode'   => 'regular',
				);

				foreach ( $data_args as $data_arg => $default_value ) {
					$data_arg_name = 'data-' . str_replace( '_', '-', $data_arg );
					if ( isset( $args[ $data_arg_name ] ) && ! isset( $args[ $data_arg ] ) ) {
						if ( is_bool( $default_value ) ) {
							$args[ $data_arg ] = (bool) $args[ $data_arg_name ];
						} else {
							$args[ $data_arg ] = $args[ $data_arg_name ];
						}
					}

					if ( ! isset( $args[ $data_arg ] ) ) {
						$args[ $data_arg ] = $default_value;
					}

					if ( is_bool( $default_value ) ) {
						$args[ $data_arg_name ] = $args[ $data_arg ] ? '1' : '0';
					} else {
						$args[ $data_arg_name ] = $args[ $data_arg ];
					}
				}

				if ( isset( $args['input_classes'] ) ) {
					$args['input_classes'][] = 'wp-editor-area';
				} else {
					$args['input_classes'] = array( 'wp-editor-area' );
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

			if ( version_compare( $GLOBALS['wp_version'], '4.8', '>=' ) ) {
				wp_enqueue_editor();

				if ( ! empty( $GLOBALS['post'] ) ) {
					wp_enqueue_media( array( 'post' => $GLOBALS['post']->ID ) );
				} else {
					wp_enqueue_media();
				}

				$ret[0][] = 'editor';
				$ret[1]   = array_merge(
					$ret[1],
					array(
						'i18nWYSIWYGAddMediaButton' => __( 'Add Media' ),
					)
				);
			}

			return $ret;
		}

		/**
		 * Renders a single input for the field.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $current_value Current field value.
		 */
		protected function render_single_input( $current_value ) {
			if ( version_compare( $GLOBALS['wp_version'], '4.8', '>=' ) ) {
				parent::render_single_input( $current_value );
				return;
			}

			$input_attrs = $this->get_input_attrs( array(), false );
			$editor_id   = $input_attrs['id'];

			$this->setup_editor( $current_value );

			echo $this->editor_markup[ $editor_id ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			$this->render_repeatable_remove_button();
		}

		/**
		 * Prints a single input template.
		 *
		 * @since 1.0.0
		 */
		protected function print_single_input_template() {
			if ( version_compare( $GLOBALS['wp_version'], '4.8', '>=' ) ) {
				parent::print_single_input_template();
				return;
			}

			if ( user_can_richedit() ) {
				$switch_class = 'tmce-active';
				$autocomplete = ' autocomplete="off"';
			} else {
				$switch_class = 'html-active';
				$autocomplete = '';
			}

			?>
			<div id="wp-{{data.inputAttrs.id}}-wrap" class="<?php echo esc_attr( 'wp-core-ui wp-editor-wrap ' . $switch_class ); ?>">
				<?php if ( user_can_richedit() ) : ?>
					<div id="wp-{{data.inputAttrs.id}}-editor-tools" class="wp-editor-tools hide-if-no-js">
						<# if ( data.editorSettings.media_buttons ) { #>
							<div id="wp-{{data.inputAttrs.id}}-media-buttons" class="wp-media-buttons">
								<button type="button"%s class="button insert-media add_media" data-editor="{{data.inputAttrs.id}}">
									<span class="wp-media-buttons-icon"></span>
									<?php esc_html_e( 'Add Media' ); ?>
								</button>
							</div>
						<# } #>
						<div class="wp-editor-tabs">
							<button type="button" id="{{data.inputAttrs.id}}-tmce" class="wp-switch-editor switch-tmce" data-wp-editor-id="{{data.inputAttrs.id}}"><?php esc_html_e( 'Visual' ); ?></button>
							<button type="button" id="{{data.inputAttrs.id}}-html" class="wp-switch-editor switch-html" data-wp-editor-id="{{data.inputAttrs.id}}"><?php echo esc_html( _x( 'Text', 'Name for the Text editor tab (formerly HTML)' ) ); ?></button>
						</div>
					</div>
				<?php else : ?>
					<# if ( data.editorSettings.media_buttons ) { #>
						<div id="wp-{{data.inputAttrs.id}}-editor-tools" class="wp-editor-tools hide-if-no-js">
							<div id="wp-{{data.inputAttrs.id}}-media-buttons" class="wp-media-buttons">
								<button type="button"%s class="button insert-media add_media" data-editor="{{data.inputAttrs.id}}">
									<span class="wp-media-buttons-icon"></span>
									<?php esc_html_e( 'Add Media' ); ?>
								</button>
							</div>
						</div>
					<# } #>
				<?php endif; ?>

				<div id="wp-{{data.inputAttrs.id}}-editor-container" class="wp-editor-container">
					<div id="qt_{{data.inputAttrs.id}}_toolbar" class="quicktags-toolbar"></div>
					<textarea class="{{data.editorSettings.editor_class}} wp-editor-area" rows="{{data.editorSettings.textarea_rows}}"<?php echo $autocomplete; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?> cols="40" name="{{data.editorSettings.textarea_name}}" id="{{data.inputAttrs.id}}">{{ data.currentValue }}</textarea>
				</div>
			</div>

			<?php
			$this->print_repeatable_remove_button_template();
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
			if ( version_compare( $GLOBALS['wp_version'], '4.8', '>=' ) ) {
				$data = parent::single_to_json( $current_value );

				$data['wpautop']       = $this->wpautop;
				$data['media_buttons'] = $this->media_buttons;
				$data['button_mode']   = $this->button_mode;

				return $data;
			}

			$this->setup_editor( $current_value );

			$data = parent::single_to_json( $current_value );

			$data['editorSettings']    = $this->editor_settings;
			$data['tinyMCESettings']   = $this->tinymce_settings;
			$data['quickTagsSettings'] = $this->quicktags_settings;

			return $data;
		}

		/**
		 * Sets up the editor markup and settings.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $current_value Current field value.
		 */
		protected function setup_editor( $current_value ) {
			$input_attrs = $this->get_input_attrs( array(), false );
			$editor_id   = $input_attrs['id'];

			if ( ! isset( $this->editor_settings[ $editor_id ] ) ) {
				$this->editor_settings[ $editor_id ] = array(
					'textarea_name'  => $input_attrs['name'],
					'textarea_rows'  => isset( $input_attrs['rows'] ) ? $input_attrs['rows'] : 20,
					'editor_class'   => isset( $input_attrs['class'] ) ? $input_attrs['class'] : '',
					'default_editor' => user_can_richedit() ? 'tinymce' : 'html',
					'wpautop'        => $this->wpautop,
					'media_buttons'  => $this->media_buttons,
					'quicktags'      => array(
						'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,close',
					),
					'tinymce'        => array(
						'toolbar1' => 'bold,italic,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,spellchecker,wp_adv',
					),
				);
			}

			if ( ! isset( $this->editor_markup[ $editor_id ] ) || ! isset( $this->tinymce_settings[ $editor_id ] ) || ! isset( $this->quicktags_settings[ $editor_id ] ) ) {
				$accepted_args = 2;

				$tinymce_function   = array( $this, 'set_tinymce_settings' );
				$quicktags_function = array( $this, 'set_quicktags_settings' );

				$tinymce_filter = function() use ( $tinymce_function, $accepted_args ) {
					return call_user_func_array( $tinymce_function, array_slice( func_get_args(), 0, $accepted_args ) );
				};

				$quicktags_filter = function() use ( $quicktags_function, $accepted_args ) {
					return call_user_func_array( $quicktags_function, array_slice( func_get_args(), 0, $accepted_args ) );
				};

				add_filter( 'tiny_mce_before_init', $tinymce_filter, 10, $accepted_args );
				add_filter( 'quicktags_settings', $quicktags_filter, 10, $accepted_args );

				ob_start();
				wp_editor( $current_value, $editor_id, $this->editor_settings[ $editor_id ] );
				$this->editor_markup[ $editor_id ] = ob_get_clean();

				remove_filter( 'tiny_mce_before_init', $tinymce_filter, 10 );
				remove_filter( 'quicktags_settings', $quicktags_filter, 10 );
			}
		}

		/**
		 * Sets TinyMCE settings.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $settings  Array of TinyMCE settings.
		 * @param string $editor_id Editor ID the settings belong to.
		 */
		protected function set_tinymce_settings( $settings, $editor_id ) {
			$this->tinymce_settings[ $editor_id ] = $settings;

			return $settings;
		}

		/**
		 * Sets QuickTags settings.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $settings  Array of QuickTags settings.
		 * @param string $editor_id Editor ID the settings belong to.
		 */
		protected function set_quicktags_settings( $settings, $editor_id ) {
			$this->quicktags_settings[ $editor_id ] = $settings;

			return $settings;
		}

		/**
		 * Returns names of the properties that must not be set through constructor arguments.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of forbidden properties.
		 */
		protected function get_forbidden_keys() {
			return array( 'manager', 'dependency_resolver', 'id', 'slug', 'label_mode', 'input_attrs', 'backbone_view', 'index', 'editor_markup', 'editor_settings', 'tinymce_settings', 'quicktags_settings' );
		}
	}

endif;
