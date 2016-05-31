<?php
/**
 * Core: Torro_Element_Type_Multiplechoice class
 *
 * @package TorroForms
 * @subpackage CoreElements
 * @version 1.0.0-beta.3
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Element type class for a multiple choice input
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Element_Type_Multiplechoice extends Torro_Element_Type {
	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->name = 'multiplechoice';
		$this->title = __( 'Multiple Choice', 'torro-forms' );
		$this->description = __( 'Add an element which can be answered by selecting one ore more given answers.', 'torro-forms' );
		$this->icon_url = torro()->get_asset_url( 'icon-multiplechoice', 'png' );

		$this->input_answers = true;
		$this->answer_array = true;
		$this->input_answers = true;
	}

	protected function get_input_html( $element ) {
		$star_required = '';
		$aria_required = '';
		if ( isset( $element->settings['required'] ) && 'yes' === $element->settings['required']->value ) {
			$star_required = ' <span class="required">*</span>';
			$aria_required = ' aria-required="true"';
		}

		$html  = '<fieldset' . $aria_required . ' role="group">';
		$html .= '<legend>' . esc_html( $element->label ) . $star_required . '</legend>';

		$i = 0;
		foreach ( $element->answers as $answer ) {
			$checked = '';
			if ( is_array( $element->response ) && in_array( $answer->answer, $element->response, true ) ) {
				$checked = ' checked="checked"';
			}

			$html .= '<div class="torro_element_checkbox"><input id="' . $this->get_input_id( $element ) .'_' . $i . '" type="checkbox" aria-describedby="' . $this->get_input_id( $element ) . '_description ' . $this->get_input_id( $element ) . '_errors" name="' . $this->get_input_name( $element ) . '[]" value="' . esc_attr( $answer->answer ) . '" ' . $checked . ' /> <label for="' . $this->get_input_id( $element ) .'_' . $i . '">' . esc_html( $answer->answer ) . '</label></div>';
			$i++;
		}

		if ( ! empty( $element->settings['description']->value ) ) {
			$html .= '<div id="' . $this->get_input_id( $element ) . '_description" class="element-description">';
			$html .= esc_html( $element->settings['description']->value );
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

	public function validate( $input, $element ) {
		$min_answers = $element->settings['min_answers']->value;
		$max_answers = $element->settings['max_answers']->value;

		$input = (array) $input;

		$input = array_map( 'stripslashes', $input );

		if ( isset( $element->settings['required'] ) && 'yes' === $element->settings['required']->value && 0 === count( $input ) ) {
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

		if( is_array( $input ) ) {
			$input = array_map( 'stripslashes', $input );
		}

		return $input;
	}
}

torro()->element_types()->register( 'Torro_Element_Type_Multiplechoice' );
