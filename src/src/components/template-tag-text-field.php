<?php
/**
 * Template tag text field class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Components;

use Leaves_And_Love\Plugin_Lib\Fields\Text;

/**
 * Class for a template tag text field.
 *
 * @since 1.0.0
 */
class Template_Tag_Text_Field extends Text {

	/**
	 * Field type identifier.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $slug = 'templatetagtext';

	/**
	 * Backbone view class name to use for this field.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $backbone_view = 'TemplatetagtextFieldView';

	/**
	 * Template tag handler for this field.
	 *
	 * @since 1.0.0
	 * @var Template_Tag_Handler
	 */
	protected $template_tag_handler = null;

	/**
	 * Whether scripts for template tag text fields have been enqueued.
	 *
	 * @since 1.0.0
	 * @static
	 * @var bool
	 */
	protected static $enqueued = false;

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

		if ( Template_Tag_WYSIWYG_Field::has_enqueued() ) {
			return $ret;
		}

		$assets = $this->manager->assets();

		$assets->enqueue_script( 'template-tag-fields' );
		$assets->enqueue_style( 'template-tag-fields' );

		self::$enqueued = true;

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
		$id = $this->get_id_attribute();

		$input_attrs = array(
			'type'  => $this->type,
			'value' => $current_value,
		);
		?>
		<input<?php echo $this->get_input_attrs( $input_attrs ); // WPCS: XSS OK. ?>>
		<div class="template-tag-list-wrap">
			<button type="button" class="template-tag-list-toggle button" aria-controls="<?php echo esc_attr( $id . '-template-tag-list' ); ?>" aria-expanded="false">
				<span aria-hidden="true">+</span>
				<span class="screen-reader-text"><?php esc_html_e( 'Insert template tag', 'torro-forms' ); ?></span>
			</button>
			<ul id="<?php echo esc_attr( $id . '-template-tag-list' ); ?>" class="template-tag-list" role="region" tabindex="-1">
				<?php foreach ( $this->template_tag_handler->get_groups() as $group_slug => $group_label ) : ?>
					<li class="template-tag-list-group <?php echo esc_attr( 'template-tag-list-group-' . $group_slug ); ?>">
						<span><?php echo esc_html( $group_label ); ?></span>
						<ul>
							<?php foreach ( $this->template_tag_handler->get_tag_labels( $group_slug ) as $tag_slug => $tag_label ) : ?>
								<li class="template-tag <?php echo esc_attr( 'template-tag-' . $tag_slug ); ?>">
									<button type="button" class="template-tag-button" data-tag="<?php echo esc_attr( $tag_slug ); ?>">
										<?php echo esc_html( $tag_label ); ?>
									</button>
								</li>
							<?php endforeach; ?>
						</ul>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
		$this->render_repeatable_remove_button();
	}

	/**
	 * Prints a single input template.
	 *
	 * @since 1.0.0
	 */
	protected function print_single_input_template() {
		?>
		<input type="<?php echo esc_attr( $this->type ); ?>"{{{ _.attrs( data.inputAttrs ) }}} value="{{ data.currentValue }}">
		<div class="template-tag-list-wrap">
			<button type="button" class="template-tag-list-toggle button" aria-controls="{{ data.inputAttrs.id }}-template-tag-list" aria-expanded="false">
				<span aria-hidden="true">+</span>
				<span class="screen-reader-text"><?php esc_html_e( 'Insert template tag', 'torro-forms' ); ?></span>
			</button>
			<ul id="{{ data.inputAttrs.id }}-template-tag-list" class="template-tag-list" role="region" tabindex="-1">
				<# _.each( data.templateTags, function( groupData, groupSlug ) { #>
					<li class="template-tag-list-group template-tag-list-group-{{ groupSlug }}">
						<span>{{ groupData.label }}</span>
						<ul>
							<# _.each( groupData.tags, function( tagLabel, tagSlug ) { #>
								<li class="template-tag">
									<button type="button" class="template-tag-button" data-tag="{{ tagSlug }}">
										{{ tagLabel }}
									</button>
								</li>
							<# } ) #>
						</ul>
					</li>
				<# } ) #>
			</ul>
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
		$data = parent::single_to_json( $current_value );

		$data['templateTags'] = array();
		foreach ( $this->template_tag_handler->get_groups() as $group_slug => $group_label ) {
			$data['templateTags'][ $group_slug ] = array(
				'label' => $group_label,
				'tags'  => $this->template_tag_handler->get_tag_labels( $group_slug ),
			);
		}

		return $data;
	}

	/**
	 * Checks whether the scripts for the template tag text field have been enqueued.
	 *
	 * @since 1.0.0
	 * @static
	 *
	 * @return bool True if enqueued, false otherwise.
	 */
	public static function has_enqueued() {
		return self::$enqueued;
	}
}
