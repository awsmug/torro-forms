<?php
/**
 * Core: Torro_ShortCodes class
 *
 * @package TorroForms
 * @subpackage Core
 * @version 1.0.0-beta.1
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms shortcodes class
 *
 * Adds and processes core shortcodes for the plugin.
 *
 * @since 1.0.0-beta.1
 */
class Torro_ShortCodes {
	var $tables;
	var $components = array();

	/**
	 * Loading all Shortcodes
	 */
	public static function init() {
		add_shortcode( 'form', array( __CLASS__, 'form' ) );

		add_action( 'torro_formbuilder_options', array( __CLASS__, 'show_form_shortcode' ), 15 );
	}

	public static function form( $atts ) {
		$defaults = array(
			'id'			=> '',
			'title'			=> __( 'Form', 'torro-forms' ),
			'show'			=> 'embed', // embed, iframe
			'iframe_width'	=> '100%',
			'iframe_height'	=> '100%',
		);

		$atts = shortcode_atts( $defaults, $atts );

		$id = absint( $atts[ 'id' ] );

		if ( 0 === $id ) {
			return __( 'Please enter an id in the form shortcode!', 'torro-forms' );
		}

		$form = torro()->forms()->get( $id );
		if ( is_wp_error( $form ) ) {
			return __( 'Form not found. Please enter another ID in your shortcode.', 'torro-forms' );
		}

		$action_url = $_SERVER['REQUEST_URI'];

		$html = '';

		switch ( $atts[ 'show' ] ) {
			case 'iframe':
				$url = get_permalink( $id );
				$width = $atts['iframe_width'];
				$height = $atts['iframe_height'];

				$html = '<iframe src="' . $url . '" style="width:' . $width . ';height:' . $height . ';"></iframe>';
				break;
			default:
				$html = $form->get_html( $action_url );
				break;
		}

		return $html;
	}

	public static function show_form_shortcode() {
		global $post;

		if ( ! torro_is_formbuilder() ) {
			return;
		}

		$html = '<div class="misc-pub-section form-options">';
		$html .= torro_clipboard_field( __( 'Form Shortcode:', 'torro-forms' ), '[form id=' . $post->ID . ']' );
		$html .= '</div>';

		echo $html;
	}
}

Torro_ShortCodes::init();
