<?php
/**
 * Core: Torro_Element_Multiplechoice class
 *
 * @package TorroForms
 * @subpackage CoreElements
 * @version 1.0.0-beta.2
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Element class for a multiple choice input
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Element_Multiplechoice extends Torro_Element {
	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		parent::init();

		$this->type = $this->name = 'multiplechoice';
		$this->title = __( 'Multiple Choice', 'torro-forms' );
		$this->description = __( 'Add an Element which can be answered by selecting one ore more given answers.', 'torro-forms' );
		$this->icon_url = torro()->get_asset_url( 'icon-multiplechoice', 'png' );

		$this->input_answers = true;
		$this->answer_array = true;
		$this->input_answers = true;
	}

	protected function get_input_html() {
		$star_required = '';
		$aria_required = '';
		if ( isset( $this->settings['required'] ) && 'yes' === $this->settings['required']->value ) {
			$star_required = ' <span class="required">*</span>';
			$aria_required = ' aria-required="true"';
		}

		$html  = '<fieldset' . $aria_required . ' role="group">';
		$html .= '<legend>' . esc_html( $this->label ) . $star_required . '</legend>';

		$i = 0;
		foreach ( $this->answers as $answer ) {
			$checked = '';
			if ( is_array( $this->response ) && in_array( $answer->answer, $this->response, true ) ) {
				$checked = ' checked="checked"';
			}

			$html .= '<div class="torro_element_checkbox"><input id="' . $this->get_input_id() .'_' . $i . '" type="checkbox" aria-describedby="' . $this->get_input_id() . '_description ' . $this->get_input_id() . '_errors" name="' . $this->get_input_name() . '[]" value="' . esc_attr( $answer->answer ) . '" ' . $checked . ' /> <label for="' . $this->get_input_id() .'_' . $i . '">' . esc_html( $answer->answer ) . '</label></div>';
			$i++;
		}

		if ( ! empty( $this->settings['description']->value ) ) {
			$html .= '<div id="' . $this->get_input_id() . '_description" class="element-description">';
			$html .= esc_html( $this->settings['description']->value );
			$html .= '</div>';
		}

		$html .= '</fieldset>';

		return $html;
	}

	public function settings_fields() {
		$this->settings_fields = array(
			'description'	=> array(
				'title'			=> __( 'Description', 'torro-forms' ),
				'type'			=> 'textarea',
				'description'	=> __( 'The description will be shown after the question.', 'torro-forms' ),
				'default'		=> ''
			),
			'required'		=> array(
				'title'			=> __( 'Required?', 'torro-forms' ),
				'type'			=> 'radio',
				'values'		=> array(
					'yes'			=> __( 'Yes', 'torro-forms' ),
					'no'			=> __( 'No', 'torro-forms' ),
				),
				'description'	=> __( 'Whether the user must select a value.', 'torro-forms' ),
				'default'		=> 'yes',
			),
			'min_answers'	=> array(
				'title'			=> __( 'Minimum Answers', 'torro-forms' ),
				'type'			=> 'text',
				'description'	=> __( 'The minimum number of answers which have to be choosed.', 'torro-forms' ),
				'default'		=> '1'
			),
			'max_answers'	=> array(
				'title'			=> __( 'Maximum Answers', 'torro-forms' ),
				'type'			=> 'text',
				'description'	=> __( 'The maximum number of answers which can be choosed.', 'torro-forms' ),
				'default'		=> '3'
			),
		);
	}

	public function validate( $input ) {
		$min_answers = $this->settings['min_answers']->value;
		$max_answers = $this->settings['max_answers']->value;

		$input = (array) $input;

		$input = array_map( 'stripslashes', $input );

		if ( isset( $this->settings['required'] ) && 'yes' === $this->settings['required']->value && 0 === count( $input ) ) {
			return new Torro_Error( 'missing_choices', __( 'You did not select any value.', 'torro-forms' ) );
		}

		if ( ! empty( $min_answers ) ) {
			if ( ! is_array( $input ) || count( $input ) < $min_answers ) {
				return new Torro_Error( 'not_enough_choices', __( 'Not enough choices.', 'torro-forms' ) );
			}
		}

		if ( ! empty( $max_answers ) ) {
			if ( is_array( $input ) && count( $input ) > $max_answers ) {
				return new Torro_Error( 'too_many_choices', __( 'Too many choices.', 'torro-forms' ) );
			}
		}

		return $input;
	}
}

torro()->element_types()->register( 'Torro_Element_Multiplechoice' );
