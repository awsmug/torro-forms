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

		$this->bootstrap();
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
	 * Bootstraps the element type by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected abstract function bootstrap();
}
