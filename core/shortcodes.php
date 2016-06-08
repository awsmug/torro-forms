<?php
/**
 * Core: Torro_ShortCodes class
 *
 * @package TorroForms
 * @subpackage Core
 * @version 1.0.0-beta.4
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

		switch ( $atts[ 'show' ] ) {
			case 'iframe':
				$url = get_permalink( $id );
				$width = $atts['iframe_width'];
				$height = $atts['iframe_height'];

				$html = '<iframe src="' . $url . '" style="width:' . $width . ';height:' . $height . ';"></iframe>';
				break;
			default:
				$controler = Torro_Form_Controller::instance();
				$controler_form_id = $controler->get_form_id();

				if( ! empty( $controler_form_id ) ) {
					$html = do_shortcode( $controler->get_content() );
				} else {
					$html = do_shortcode( $form->get_html( $_SERVER['REQUEST_URI'] ) );
				}
				break;
		}

		return $html;
	}
}

Torro_ShortCodes::init();
