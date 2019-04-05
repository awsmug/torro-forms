<?php
/**
 * Select field class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Fields;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Fields\Select' ) ) :

	/**
	 * Class for a select field.
	 *
	 * @since 1.0.0
	 */
	class Select extends Select_Base {
		/**
		 * Field type identifier.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $slug = 'select';

		/**
		 * Backbone view class name to use for this field.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $backbone_view = 'SelectFieldView';

		/**
		 * Option groups with choices to select from, if necessary.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $optgroups = array();

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
			parent::__construct( $manager, $id, $args );

			$this->optgroups_to_choices();
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

			$select2_version = '4.0.5';

			$assets->register_style(
				'select2',
				'node_modules/select2/dist/css/select2.css',
				array(
					'ver'     => $select2_version,
					'enqueue' => true,
				)
			);

			$assets->register_script(
				'select2',
				'node_modules/select2/dist/js/select2.js',
				array(
					'deps'      => array( 'jquery' ),
					'ver'       => $select2_version,
					'in_footer' => true,
					'enqueue'   => true,
				)
			);

			$ret[0][] = 'select2';

			$locale   = explode( '_', get_locale() );
			$locale   = $locale[0] . '-' . $locale[1];
			$language = substr( $locale, 0, 2 );

			if ( $assets->file_exists( 'node_modules/select2/dist/js/i18n/' . $locale . '.js' ) ) {
				$assets->register_script(
					'select2-locale',
					'node_modules/select2/dist/js/i18n/' . $locale . '.js',
					array(
						'deps'      => array( 'select2' ),
						'ver'       => $select2_version,
						'in_footer' => true,
						'enqueue'   => true,
					)
				);

				$ret[0][] = 'select2-locale';
			} elseif ( $assets->file_exists( 'node_modules/select2/dist/js/i18n/' . $language . '.js' ) ) {
				$assets->register_script(
					'select2-locale',
					'node_modules/select2/dist/js/i18n/' . $language . '.js',
					array(
						'deps'      => array( 'select2' ),
						'ver'       => $select2_version,
						'in_footer' => true,
						'enqueue'   => true,
					)
				);

				$ret[0][] = 'select2-locale';
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
			$current_value = array_map( 'strval', (array) $current_value );

			$input_attrs = array( 'multiple' => $this->multi );

			?>
			<select<?php echo $this->get_input_attrs( $input_attrs ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>>
				<?php if ( ! empty( $this->optgroups ) ) : ?>
					<?php foreach ( $this->optgroups as $optgroup ) : ?>
						<?php if ( ! empty( $optgroup['label'] ) ) : ?>
							<optgroup label="<?php echo esc_attr( $optgroup['label'] ); ?>">
						<?php endif; ?>

						<?php foreach ( $optgroup['choices'] as $value => $label ) : ?>
							<?php
							$option_attrs = array(
								'value'    => $value,
								'selected' => in_array( (string) $value, $current_value, true ),
							);
							?>
							<option<?php echo $this->attrs( $option_attrs ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>

						<?php if ( ! empty( $optgroup['label'] ) ) : ?>
							</optgroup>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php else : ?>
					<?php foreach ( $this->choices as $value => $label ) : ?>
						<?php
						$option_attrs = array(
							'value'    => $value,
							'selected' => in_array( (string) $value, $current_value, true ),
						);
						?>
						<option<?php echo $this->attrs( $option_attrs ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
			<?php
			$this->render_repeatable_remove_button();
		}

		/**
		 * Prints a single input template.
		 *
		 * @since 1.0.0
		 */
		protected function print_single_input_template() {
			if ( $this->multi ) {
				$multiple = ' multiple';
				$selected = '<# if ( _.isArray( data.currentValue ) && _.contains( data.currentValue, String( value ) ) ) { #> selected<# } #>';
			} else {
				$multiple = '';
				$selected = '<# if ( data.currentValue === String( value ) ) { #> selected<# } #>';
			}

			?>
			<select{{{ _.attrs( data.inputAttrs ) }}}<?php echo $multiple; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>>
				<# if ( ! _.isEmpty( data.optgroups ) ) { #>
					<# _.each( data.optgroups, function( optgroup ) { #>
						<# if ( ! _.isEmpty( optgroup.label ) ) { #>
							<optgroup label="{{ optgroup.label }}">
						<# } #>

						<# _.each( optgroup.choices, function( label, value ) { #>
							<option value="{{ value }}"<?php echo $selected; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>>{{ label }}</option>
						<# } ) #>

						<# if ( ! _.isEmpty( optgroup.label ) ) { #>
							</optgroup>
						<# } #>
					<# }) #>
				<# } else { #>
					<# _.each( data.choices, function( label, value ) { #>
						<option value="{{ value }}"<?php echo $selected; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>>{{ label }}</option>
					<# } ) #>
				<# } #>
			</select>
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
			$data              = parent::single_to_json( $current_value );
			$data['optgroups'] = $this->optgroups;

			return $data;
		}

		/**
		 * Resolves all dependencies of this field, if applicable.
		 *
		 * @since 1.0.0
		 *
		 * @return bool True if dependencies were resolved, false if nothing changed.
		 */
		protected function maybe_resolve_dependencies() {
			$result = parent::maybe_resolve_dependencies();
			if ( ! $result ) {
				return $result;
			}

			if ( in_array( 'optgroups', $this->dependency_resolver->get_dependency_props(), true ) ) {
				$this->optgroups_to_choices();
			}

			return $result;
		}

		/**
		 * Sets the choices property based on the optgroups property.
		 *
		 * @since 1.0.0
		 */
		protected function optgroups_to_choices() {
			if ( ! empty( $this->optgroups ) ) {
				$this->choices = array();

				foreach ( $this->optgroups as $optgroup ) {
					$this->choices = array_merge( $this->choices, $optgroup['choices'] );
				}
			}
		}
	}

endif;
