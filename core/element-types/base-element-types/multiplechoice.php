<?php
/**
 * Core: Torro_Element_Type_Multiplechoice class
 *
 * @package TorroForms
 * @subpackage CoreElements
 * @version 1.0.0-beta.7
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

	/**
	 * Prepares data to render the element type HTML output.
	 *
	 * @since 1.0.0
	 *
	 * @param Torro_Element $element
	 *
	 * @return array
	 */
	public function to_json( $element ) {
		$data = parent::to_json( $element );

		$data['limits_text'] = '';
		if ( ! empty( $element->settings['min_answers'] ) && ! empty( $element->settings['min_answers']->value ) && ! empty( $element->settings['max_answers'] ) && ! empty( $element->settings['max_answers']->value ) ) {
			if ( $element->settings['min_answers']->value === $element->settings['max_answers']->value ) {
				$data['limits_text'] = sprintf( _n( 'You must select %s answer.', 'You must select %s answers.', $element->settings['max_answers']->value, 'torro-forms' ), number_format_i18n( $element->settings['max_answers']->value ) );
			} else {
				$data['limits_text'] = sprintf( __( 'You must select between %1$s and %2$s answers.', 'torro-forms' ), number_format_i18n( $element->settings['min_answers']->value ), number_format_i18n( $element->settings['max_answers']->value ) );
			}
		} elseif ( ! empty( $element->settings['min_answers'] ) && ! empty( $element->settings['min_answers']->value ) ) {
			$data['limits_text'] = sprintf( _n( 'You must select at least %s answer.', 'You must select at least %s answers.', $element->settings['min_answers']->value, 'torro-forms' ), number_format_i18n( $element->settings['min_answers']->value ) );
		} elseif ( ! empty( $element->settings['max_answers'] ) && ! empty( $element->settings['max_answers']->value ) ) {
			$data['limits_text'] = sprintf( _n( 'You must select not more than %s answer.', 'You must select not more than %s answers.', $element->settings['max_answers']->value, 'torro-forms' ), number_format_i18n( $element->settings['max_answers']->value ) );
		}

		return $data;
	}

	protected function settings_fields() {
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
				'default'		=> ''
			),
			'max_answers'	=> array(
				'title'			=> __( 'Maximum Answers', 'torro-forms' ),
				'type'			=> 'text',
				'description'	=> __( 'The maximum number of answers which can be choosed.', 'torro-forms' ),
				'default'		=> ''
			),
			'css_classes'	=> array(
				'title'			=> __( 'CSS Classes', 'torro-forms' ),
				'type'			=> 'text',
				'description'	=> __( 'Additional CSS Classes separated by whitespaces.', 'torro-forms' ),
				'default'		=> ''
			),
		);
	}

	public function validate( $input, $element ) {
		$min_answers = $element->settings['min_answers']->value;
		$max_answers = $element->settings['max_answers']->value;

		if ( isset( $element->settings['required'] ) && 'yes' === $element->settings['required']->value && ( 0 === count( $input ) ||  ! is_array( $input ) ) ) {
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
