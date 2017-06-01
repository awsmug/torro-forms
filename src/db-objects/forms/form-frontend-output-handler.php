<?php
/**
 * Form frontend output handler class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Forms;

use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;

/**
 * Class for handling the form frontend output.
 *
 * @since 1.0.0
 */
class Form_Frontend_Output_Handler {

	/**
	 * Form manager instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Form_Manager
	 */
	protected $form_manager;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Form_Manager $form_manager Form manager instance.
	 */
	public function __construct( $form_manager ) {
		$this->form_manager = $form_manager;
	}

	/**
	 * Appends the content for a form if conditions are met.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $content Post content.
	 * @return string Post content including form content, if the current post is a form.
	 */
	public function maybe_get_form_content( $content ) {
		$form = $this->form_manager->get( get_the_ID() );
		if ( ! $form ) {
			return $content;
		}

		$submission = null;
		if ( isset( $_GET['torro_submission_id'] ) ) {
			$submission = $this->form_manager->get_child_manager( 'submissions' )->get( absint( $_GET['torro_submission_id'] ) );
			if ( $submission->form_id !== $form->id ) {
				$submission = null;
			}
		}

		ob_start();
		$this->render_form_content( $form, $submission );
		return ob_get_clean() . $content;
	}

	/**
	 * Handles the form shortcode.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $atts {
	 *     Array of shortcode attributes.
	 *
	 *     @type int    $id            Form ID. This must always be present.
	 *     @type string $show          How to display the form. Either 'direct' or 'iframe'. Default 'direct'.
	 *     @type string $iframe_width  If $show is set to 'iframe', this indicates the iframe width. Default '100%'.
	 *     @type string $iframe_height If $show is set to 'iframe', this indicates the iframe height. Default '100%'.
	 * }
	 */
	public function get_shortcode_content( $atts ) {
		$atts = shortcode_atts( array(
			'id'            => '',
			'show'          => 'direct',
			'iframe_width'  => '100%',
			'iframe_height' => '100%',
		), $atts );

		$atts['id'] = absint( $atts['id'] );

		if ( empty( $atts['id'] ) ) {
			return __( 'Shortcode is missing a form ID!', 'torro-forms' );
		}

		$form = $this->form_manager->get( $atts['id'] );
		if ( ! $form ) {
			return __( 'Shortcode is using an invalid form ID!', 'torro-forms' );
		}

		if ( 'iframe' === $atts['show'] ) {
			$url = get_permalink( $form->id );
			if ( isset( $_GET['torro_submission_id'] ) ) {
				$url = add_query_arg( 'torro_submission_id', absint( $_GET['torro_submission_id'] ), $url );
			}

			return '<iframe src="' . $url . '" style="width:' . esc_attr( $atts['iframe_width'] ) . ';height:' . esc_attr( $atts['iframe_height'] ) . ';"></iframe>';
		}

		$submission = null;
		if ( isset( $_GET['torro_submission_id'] ) ) {
			$submission = $this->form_manager->get_child_manager( 'submissions' )->get( absint( $_GET['torro_submission_id'] ) );
			if ( $submission->form_id !== $form->id ) {
				$submission = null;
			}
		}

		ob_start();
		$this->render_form_content( $form, $submission );
		return ob_get_clean();
	}

	/**
	 * Handles the deprecated form shortcode.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $atts {
	 *     Array of shortcode attributes.
	 *
	 *     @type int    $id            Form ID. This must always be present.
	 *     @type string $show          How to display the form. Either 'direct' or 'iframe'. Default 'direct'.
	 *     @type string $iframe_width  If $show is set to 'iframe', this indicates the iframe width. Default '100%'.
	 *     @type string $iframe_height If $show is set to 'iframe', this indicates the iframe height. Default '100%'.
	 * }
	 */
	public function get_deprecated_shortcode_content( $atts ) {
		$this->form_manager->error_handler()->deprecated_shortcode( 'form', '1.0.0-beta.9', "{$this->form_manager->get_prefix()}form" );

		return $this->get_shortcode_content( $atts );
	}

	/**
	 * Renders the content for a given form.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param Form            $form       Form object.
	 * @param Submission|null $submission Optional. Submission object, or null if none available. Default null.
	 */
	protected function render_form_content( $form, $submission = null ) {

	}
}
