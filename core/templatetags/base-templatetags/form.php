<?php
/**
 * Core: Torro_TemplateTags_Form class
 *
 * @package TorroForms
 * @subpackage CoreTemplateTags
 * @version 1.0.0-beta.7
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms form template tags handler class
 *
 * Handles template tags for a specific form.
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Templatetags_Form extends Torro_TemplateTags {
	/**
	 * Instance
	 *
	 * @var null|Torro_Templatetags_Form
	 * @since 1.0.0
	 */
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();
	}

	protected function init() {
		$this->title = __( 'Form', 'torro-forms' );
		$this->name = 'formtags';
		$this->description = __( 'Form Templatetags', 'torro-forms' );
	}

	/**
	 * Adding all tags of class
	 */
	public function tags() {
		$this->add_tag( 'formtitle', __( 'Form Title', 'torro-forms' ), __( 'Shows the form title.', 'torro-forms' ), array( $this, 'formtitle' ) );
		$this->add_tag( 'allelements', __( 'All Elements', 'torro-forms' ), __( 'Shows all answers.', 'torro-forms' ), array( $this, 'allelements' ) );
	}

	/**
	 * %sitename%
	 */
	public function formtitle() {
		$form = torro()->forms()->get_current();
		if ( is_wp_error( $form ) ) {
			return '';
		}

		return $form->title;
	}

	/**
	 * Adding Element on the fly to taglist
	 * @param $element_id
	 * @param $element_name
	 */
	public function add_element( $element_id, $element_name ) {
		$this->add_tag( $element_name . ':' . $element_id, $element_name, __( 'Adds the element content.', 'torro-forms' ), array( $this, 'element_content' ), array( 'element_id' => $element_id ) );
	}

	/**
	 * Shows the Element content
	 *
	 * @param $element_id
	 */
	public function element_content( $element_id ) {
		global $torro_response;

		foreach( $torro_response[ 'containers' ] AS $container ) {
			$elements = $container[ 'elements' ];

			if( array_key_exists( $element_id, $elements ) ) {
				$value = $elements[ $element_id ];
			}
		}

		$element = torro()->elements()->get( $element_id );

		/**
		 * Displaying elements
		 */
		if ( count( $element->sections ) > 0 ) {
			/**
			 * Elements with sections
			 */
			// @todo Checking if element had sections and giving them HTML > Try with Matrix

		} elseif ( is_array( $value ) ) {
			/**
			 * Elements with multiple answers
			 */

			$html = apply_filters( 'torro_templatetags_element_content_array', implode( ', ', $value ), $value );

			return $html;
		} else {
			/**
			 * Elements with string response value
			 */
			if ( is_callable( array( $element, 'render_value' ) ) ) {
				return apply_filters( 'torro_templatetags_element_content_string', $element->render_value( $value ) );
			}

			return $value;
		}
	}

	/**
	 * Shows the Element content
	 * @param $element_id
	 */
	public function allelements() {
		global $torro_response;

		$form = torro()->forms()->get_current();
		if ( is_wp_error( $form ) ) {
			return '';
		}

		$html = '<table style="width:100%;">';
		foreach ( $form->elements as $element ) {
			$html .= '<tr>';
			$html .= '<td>' . $element->label . '</td>';
			$html .= '<td>' . self::element_content( $element->id ) . '</td>';
			$html .= '</tr>';
		}
		$html .= '</table>';

		return $html;
	}
}

torro()->templatetags()->register( 'Torro_Templatetags_Form' );
