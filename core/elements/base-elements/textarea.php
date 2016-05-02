<?php
/**
 * Core: Torro_Element_Textarea class
 *
 * @package TorroForms
 * @subpackage CoreElements
 * @version 1.0.0-beta.1
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Element class for a textarea input
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Element_Textarea extends Torro_Element {
	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		parent::init();

		$this->type = $this->name = 'textarea';
		$this->title = __( 'Textarea', 'torro-forms' );
		$this->description = __( 'Add an Element which can be answered within a text area.', 'torro-forms' );
		$this->icon_url = torro()->get_asset_url( 'icon-textarea', 'png' );
	}

	/**
	 * HTML of textara on front page
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	protected function get_input_html() {
		$star_required = '';
		$aria_required = '';
		if ( isset( $this->settings['required'] ) && 'yes' === $this->settings['required']->value ) {
			$star_required = ' <span class="required">*</span>';
			$aria_required = ' aria-required="true"';
		}

		$html  = '<label for="' . $this->get_input_id() . '">' . esc_html( $this->label ) . $star_required . '</label>';
		$html .= '<textarea id="' . $this->get_input_id() . '" aria-describedby="' . $this->get_input_id() . '_description ' . $this->get_input_id() . '_errors" name="' . $this->get_input_name() . '" maxlength="' . $this->settings[ 'max_length' ]->value . '" rows="' . $this->settings[ 'rows' ]->value . '" cols="' . $this->settings[ 'cols' ]->value . '"' . $aria_required . '>' . esc_html( $this->response ) . '</textarea>';

		if ( ! empty( $this->settings['description']->value ) ) {
			$html .= '<div id="' . $this->get_input_id() . '_description" class="element-description">';
			$html .= esc_html( $this->settings['description']->value );
			$html .= '</div>';
		}

		return $html;
	}

	/**
	 * Setting fields
	 *
	 * @since 1.0.0
	 */
	public function settings_fields() {
		$this->settings_fields = array(
			'description'	=> array(
				'title'			=> __( 'Description', 'torro-forms' ),
				'type'			=> 'textarea',
				'description'	=> __( 'The description will be shown after the field.', 'torro-forms' ),
				'default'		=> ''
			),
			'required'		=> array(
				'title'			=> __( 'Required?', 'torro-forms' ),
				'type'			=> 'radio',
				'values'		=> array(
					'yes'			=> __( 'Yes', 'torro-forms' ),
					'no'			=> __( 'No', 'torro-forms' ),
				),
				'description'	=> __( 'Whether the user must input something.', 'torro-forms' ),
				'default'		=> 'no',
			),
			'min_length'	=> array(
				'title'			=> __( 'Minimum length', 'torro-forms' ),
				'type'			=> 'text',
				'description'	=> __( 'Minimum number of chars which have to be typed in.', 'torro-forms' ),
				'default'		=> '0'
			),
			'max_length'	=> array(
				'title'			=> __( 'Maximum length', 'torro-forms' ),
				'type'			=> 'text',
				'description'	=> __( 'The maximum number of chars which can be typed in.', 'torro-forms' ),
				'default'		=> '1000'
			),
			'rows'			=> array(
				'title'			=> __( 'Rows', 'torro-forms' ),
				'type'			=> 'text',
				'description'	=> __( 'Number of rows for typing in  (can be overwritten by CSS).', 'torro-forms' ),
				'default'		=> '10'
			),
			'cols'			=> array(
				'title'			=> __( 'Columns', 'torro-forms' ),
				'type'			=> 'text',
				'description'	=> __( 'Number of columns for typing in (can be overwritten by CSS).', 'torro-forms' ),
				'default'		=> '75'
			),
		);
	}

	/**
	 * Validating input of user
	 *
	 * @param string $input
	 *
	 * @return string|Torro_Error $input
	 * @since 1.0.0
	 */
	public function validate( $input ) {
		$min_length = $this->settings[ 'min_length' ]->value;
		$max_length = $this->settings[ 'max_length' ]->value;

		$input = trim( stripslashes( $input ) );

		if ( isset( $this->settings['required'] ) && 'yes' === $this->settings['required']->value && empty( $input ) ) {
			return new Torro_Error( 'missing_input', __( 'You must input something.', 'torro-forms' ) );
		}

		if ( ! empty( $min_length ) ) {
			if ( strlen( $input ) < $min_length ) {
				return new Torro_Error( 'input_too_short', __( 'The input ist too short.', 'torro-forms' ) );
			}
		}

		if ( ! empty( $max_length ) ) {
			if ( strlen( $input ) > $max_length ) {
				return new Torro_Error( 'input_too_long', __( 'The input ist too long.', 'torro-forms' ) );
			}
		}

		return $input;
	}
}

torro()->elements()->register( 'Torro_Element_Textarea' );
