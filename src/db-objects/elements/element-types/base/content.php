<?php
/**
 * Content element type class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Base;

use awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Element_Type;
use awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Non_Input_Element_Type_Interface;
use awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Non_Input_Element_Type_Trait;
use awsmug\Torro_Forms\DB_Objects\Elements\Element;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use WP_Error;

/**
 * Class representing a content element type.
 *
 * @since 1.0.0
 */
class Content extends Element_Type implements Non_Input_Element_Type_Interface {
	use Non_Input_Element_Type_Trait;

	/**
	 * Filters the array representation of a given element of this type.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array           $data       Element data to filter.
	 * @param Element         $element    The element object to get the data for.
	 * @param Submission|null $submission Optional. Submission to get the values from, if available. Default null.
	 * @return array Array including all information for the element type.
	 */
	public function filter_json( $data, $element, $submission = null ) {
		$data = parent::filter_json( $data, $element, $submission );

		if ( isset( $data['input_attrs']['name'] ) ) {
			unset( $data['input_attrs']['name'] );
		}

		$data['label'] = wpautop( $data['label'] );
		$data['label'] = shortcode_unautop( $data['label'] );
		$data['label'] = do_shortcode( $data['label'] );

		return $data;
	}

	/**
	 * Bootstraps the element type by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function bootstrap() {
		$this->slug        = 'content';
		$this->title       = __( 'Content', 'torro-forms' );
		$this->description = __( 'A non-input element to display custom content.', 'torro-forms' );
		$this->icon_url    = $this->manager->assets()->get_full_url( 'assets/dist/img/icon-text.png' );

		$this->settings_fields['label']['type'] = 'wysiwyg';
		$this->settings_fields['label']['label'] = __( 'Content', 'torro-forms' );
		$this->settings_fields['label']['description'] = __( 'Enter the content to display.', 'torro-forms' );
		$this->add_css_classes_settings_field();
	}
}
