<?php
/**
 * Element type base class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Elements\Element_Types;

/**
 * Base class representing an element type.
 *
 * @since 1.0.0
 */
abstract class Element_Type {

	/**
	 * The element type slug. Must match the slug when registering the element type.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $slug = '';

	/**
	 * The element type title.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $title = '';

	/**
	 * The element type description.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $description = '';

	/**
	 * The element type icon URL.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $icon_url = '';

	/**
	 * The element type settings sections.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $settings_sections = array();

	/**
	 * The element type settings fields.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $settings_fields = array();

	/**
	 * The element type manager instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Element_Type_Manager
	 */
	protected $manager;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Element_Type_Manager $manager The element type manager instance.
	 */
	public function __construct( $manager ) {
		$this->manager = $manager;

		$this->settings_sections = array(
			'content'  => array(
				'title' => _x( 'Content', 'element type section', 'torro-forms' ),
			),
			'settings' => array(
				'title' => _x( 'Settings', 'element type section', 'torro-forms' ),
			),
		);

		$this->settings_fields = array(
			'label' => array(
				'section'     => 'content',
				'type'        => 'text',
				'label'       => __( 'Label', 'torro-forms' ),
				'description' => __( 'Enter the form field label.', 'torro-forms' ),
				'is_label'    => true,
			),
		);

		$this->bootstrap();

		$this->sanitize_settings_sections();
		$this->sanitize_settings_fields();
	}

	/**
	 * Returns the element type slug.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Element type slug.
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Returns the element type title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Element type title.
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Returns the element type description.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Element type description.
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Returns the element type icon URL.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Element type icon URL.
	 */
	public function get_icon_url() {
		return $this->icon_url;
	}

	/**
	 * Returns the element type settings sections.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Element type settings sections.
	 */
	public function get_settings_sections() {
		return $this->settings_sections;
	}

	/**
	 * Returns the element type settings fields.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Element type settings fields.
	 */
	public function get_settings_fields() {
		return $this->settings_fields;
	}

	/**
	 * Bootstraps the element type by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected abstract function bootstrap();

	/**
	 * Sanitizes the settings sections.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function sanitize_settings_sections() {
		$defaults = array(
			'title' => '',
		);

		foreach ( $this->settings_sections as $slug => $section ) {
			$this->settings_sections[ $slug ] = array_merge( $defaults, $section );
		}
	}

	/**
	 * Sanitizes the settings fields.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function sanitize_settings_fields() {
		$defaults = array(
			'section'     => '',
			'type'        => 'text',
			'label'       => '',
			'description' => '',
			'is_label'    => false,
			'is_choices'  => false,
		);

		$invalid_fields = array();
		$valid_sections = array();

		foreach ( $this->settings_fields as $slug => $field ) {
			if ( empty( $field['section'] ) || ! isset( $this->settings_sections[ $field['section'] ] ) ) {
				$invalid_fields[ $slug ] = true;
				continue;
			}

			$valid_sections[ $field['section'] ] = true;
			$this->settings_fields[ $slug ] = array_merge( $defaults, $field );
		}

		$this->settings_fields = array_diff_key( $this->settings_fields, $invalid_fields );
		$this->settings_sections = array_intersect_key( $this->settings_sections, $valid_sections );
	}
}
