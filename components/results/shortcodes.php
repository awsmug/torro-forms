<?php
/**
 * Components: Torro_ChartsShortcodes class
 *
 * @package TorroForms
 * @subpackage Components
 * @version 1.0.0-beta.6
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Torro_ChartsShortCodes {
	private static $is_shortcode = false;


	/**
	 * Adding Shortcodes and Actionhooks
	 */
	public static function init() {
		add_shortcode( 'form_charts', array( __CLASS__, 'form_charts' ) );

		add_shortcode( 'element_chart', array( __CLASS__, 'element_chart' ) );

		add_action( 'torro_result_charts_postbox_bottom', array( __CLASS__, 'show_form_result_shortcode' ) );
		add_action( 'torro_element_admin_tab_content', array( __CLASS__, 'show_element_result_shortcode' ), 10, 2 );

		add_action( 'wp', array( __CLASS__, 'detect_shortcode' ), 20 );
	}

	/**
	 * Detects if there are used shortcodes in displayed content
	 *
	 * @param $wp
	 *
	 * @since 1.0.0-beta.7
	 */
	public static function detect_shortcode( &$wp ) {
		global $wp_query;

		if ( ! isset( $wp_query ) ) {
			return;
		}

		// Checking singular posts (any post type)
		if ( is_singular() && ( $post = $wp_query->get_queried_object() ) && ( has_shortcode( $post->post_content, 'form_charts' ) || has_shortcode( $post->post_content, 'element_chart' ) ) ) {
			self::$is_shortcode = true;
			return;
		}

		// Checking post overviews (any post type)
		if ( is_array( $wp_query->posts ) ) {
			foreach ( $wp_query->posts as $post ) {
				if ( has_shortcode( $post->post_content, 'form_charts' ) || has_shortcode( $post->post_content, 'element_chart' ) ) {
					self::$is_shortcode = true;
					return;
				}
			}
		}

		// Checking forms
		if( torro()->is_form() ) {
			// Checking content of form
			if ( has_shortcode( Torro_Form_Controller::instance()->get_content(), 'form_charts' ) || has_shortcode( Torro_Form_Controller::instance()->get_content(), 'element_chart' ) ) {
				self::$is_shortcode = true;
				return;
			}

			// Checking elements content
			$form = torro()->forms()->get_current();
			foreach( $form->elements AS $element ) {
				if( 'content' === $element->type ) {
					if ( has_shortcode( $element->label, 'form_charts' ) || has_shortcode( $element->label, 'element_chart' ) ) {
						self::$is_shortcode = true;
						return;
					}
				}
			}
		}
	}

	/**
	 * Returns if there was chart shortcode used
	 *
	 * @return bool
	 * @since 1.0.0-beta.7
	 */
	public static function is_shortcode() {
		return self::$is_shortcode;
	}

	/**
	 * Showing all results of a form
	 *
	 * @param $atts
	 *
	 * @return string|void
	 */
	public static function form_charts( $atts ) {
		$atts = shortcode_atts( array( 'id' => '' ), $atts );
		$form_id = absint( $atts['id'] );

		if ( empty( $form_id ) || ! torro()->forms()->exists( $form_id ) ) {
			return __( 'Please enter a valid form id into the shortcode!', 'torro-forms' );
		}

		$html = torro()->resulthandlers()->get_registered( 'c3' )->show_form_charts( $form_id );

		self::$is_shortcode = true;

		return $html;
	}

	/**
	 * Showing results of an Element
	 *
	 * @param array $atts Arguments which can be added to the shortcode
	 *
	 * @return string $html HTML of results
	 */
	public static function element_chart( $atts ) {
		$atts = shortcode_atts( array( 'id' => '' ), $atts );

		$element = torro()->elements()->get( $atts['id'] );
		if ( is_wp_error( $element ) ) {
			return __( 'Please enter a valid element id into the shortcode.', 'torro-forms' );
		}

		$container = torro()->containers()->get( $element->container_id );
		if ( is_wp_error( $container ) ) {
			return __( 'It looks like the container for this element has been removed. Please enter a different element id into the shortcode.', 'torro-forms' );
		}

		$charts = torro()->resulthandlers()->get_registered( 'c3' );
		$results = $charts->parse_results_for_export( $container->form_id, 0, -1, 'raw', false );
		$results = $charts->format_results_by_element( $results );

		$html = $charts->bars( $element->label, $results[ 'element_' . $element->id ] );

		self::$is_shortcode = true;

		return $html;
	}

	/**
	 * Showing Form result shortcodes in Admin for copy&paste
	 *
	 * @param int $form_id Id of the form
	 *
	 * @return string $html HTML for shortcode summary in admon
	 */
	public static function show_form_result_shortcode() {
		global $post;

		if ( ! torro()->is_formbuilder() ) {
			return;
		}

		$html  = '<div class="in-postbox-one-third">' . torro_clipboard_field( __( 'Charts Shortcode', 'torro-forms' ), '[form_charts id=' . $post->ID . ']' ) . '</div>';

		echo $html;
	}

	/**
	 * Showing Element result shortcodes in admin area for copy&paste
	 *
	 * @param Torro_Element $element the form element to show the shortcode for
	 *
	 * @return string $html HTML for shortcode summary in admin
	 */
	public static function show_element_result_shortcode( $element_id, $element ) {
		if ( ! torro()->is_formbuilder() ) {
			return;
		}

		if ( ! $element->input_answers ) {
			return;
		}

		echo '<div class="torro-element-tab-additional-content torro-element-chart-shortcode">';
		echo '<hr />';
		echo torro_clipboard_field( __( 'Element Chart Shortcode', 'torro-forms' ), '[element_chart id=' .  $element_id . ']' );
		echo '</div>';
	}
}

Torro_ChartsShortCodes::init();
