<?php
/**
 * Template tag handler manager class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Components;

use Leaves_And_Love\Plugin_Lib\Service;
use Leaves_And_Love\Plugin_Lib\Fields\Field_Manager;

/**
 * Class for managing template tag handlers.
 *
 * @since 1.0.0
 */
class Template_Tag_Handler_Manager extends Service {

	/**
	 * Registered template tag handlers.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $handlers = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $prefix Instance prefix.
	 */
	public function __construct( $prefix ) {
		$this->set_prefix( $prefix );

		Field_Manager::register_field_type( 'templatetagtext', Template_Tag_Text_Field::class );
		Field_Manager::register_field_type( 'templatetagemail', Template_Tag_Email_Field::class );
		Field_Manager::register_field_type( 'templatetagwysiwyg', Template_Tag_WYSIWYG_Field::class );
	}

	/**
	 * Registers a template tag handler.
	 *
	 * @since 1.0.0
	 *
	 * @param Template_Tag_Handler $template_tag_handler Template tag handler to register.
	 * @return bool True on success, false on failure.
	 */
	public function register( $template_tag_handler ) {
		$slug = $template_tag_handler->get_slug();

		if ( $this->exists( $slug ) ) {
			return false;
		}

		$this->handlers[ $slug ] = $template_tag_handler;

		return true;
	}

	/**
	 * Unregisters a template tag handler.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Template tag handler slug.
	 * @return bool True on success, false on failure.
	 */
	public function unregister( $slug ) {
		if ( ! $this->exists( $slug ) ) {
			return false;
		}

		unset( $this->handlers[ $slug ] );

		return true;
	}

	/**
	 * Checks whether a specific template tag handler is registered.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Template tag handler slug.
	 * @return bool True if handler is registered, false otherwise.
	 */
	public function exists( $slug ) {
		return isset( $this->handlers[ $slug ] );
	}

	/**
	 * Gets a registered template tag handler.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Template tag handler slug.
	 * @return Template_Tag_Handler|null Template tag handler, or null if not registered.
	 */
	public function get( $slug ) {
		if ( ! $this->exists( $slug ) ) {
			return null;
		}

		return $this->handlers[ $slug ];
	}
}
