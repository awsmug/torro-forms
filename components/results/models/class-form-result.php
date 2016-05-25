<?php
/**
 * Components: Torro_Form_Result class
 *
 * @package TorroForms
 * @subpackage Components
 * @version 1.0.0-beta.3
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Torro_Form_Result extends Torro_Base {
	/**
	 * Settings name
	 *
	 * @since 1.0.0
	 */
	protected $settings_name = 'resulthandling';

	/**
	 * Contains the option_content
	 *
	 * @since 1.0.0
	 */
	protected $option_content = '';

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Content of option in Form builder
	 *
	 * @param int $form_id
	 * @since 1.0.0
	 */
	abstract function option_content( $form_id );

	public function get_columns() {
		return apply_filters( 'torro_result_columns', array(
			'id'				=> array(
				'title'				=> __( 'ID', 'torro-forms' ),
				'raw_callback'		=> array( $this, 'render_id' ),
				'class'				=> 'column-one',
			),
			'user_id'			=> array(
				'title'				=> __( 'User', 'torro-forms' ),
				'callback'			=> array( $this, 'render_user' ),
				'export_callback'	=> array( $this, 'render_user' ),
				'raw_callback'		=> array( $this, 'render_user_id' ),
			),
			'timestamp'			=> array(
				'title'				=> __( 'Date', 'torro-forms' ),
				'callback'			=> array( $this, 'render_date' ),
				'raw_callback'		=> array( $this, 'render_timestamp' ),
			),
		) );
	}

	public function parse_results_for_export( $form_id, $start, $length, $filter = 'raw', $headlines = false ) {
		$results = torro()->results()->query( array(
			'number'	=> $length,
			'offset'	=> $start,
			'form_id'	=> $form_id,
		) );

		if ( 0 === count( $results ) ) {
			return array();
		}

		$parsed = array();
		$columns = $this->get_columns();
		$value_columns = array();

		foreach ( $results[0]->values as $result_value ) {
			$element_id = $result_value->element_id;
			$slug = 'element_' . $element_id;
			$label = $result_value->element->label;

			if ( $result_value->element->input_answers && $result_value->element->answer_array ) {
				foreach ( $result_value->element->answers as $answer ) {
					$value_columns[ $slug . '_' . $answer->id ] = array(
						'title'			=> $label . ' - ' . $answer->answer,
						'element_id'	=> $element_id,
						'answer'		=> $answer->answer,
					);
					if ( is_callable( array( $result_value->element, 'render_value' ) ) ) {
						$value_columns[ $slug . '_' . $answer->id ]['callback'] = array( $result_value->element, 'render_value' );
					}
					if ( is_callable( array( $result_value->element, 'render_value_for_export' ) ) ) {
						$value_columns[ $slug . '_' . $answer->id ]['export_callback'] = array( $result_value->element, 'render_value_for_export' );
					}
				}
			} else {
				$value_columns[ $slug ] = array(
					'title'			=> $label,
					'element_id'	=> $element_id,
				);
				if ( is_callable( array( $result_value->element, 'render_value' ) ) ) {
					$value_columns[ $slug ]['callback'] = array( $result_value->element, 'render_value' );
				}
				if ( is_callable( array( $result_value->element, 'render_value_for_export' ) ) ) {
					$value_columns[ $slug ]['export_callback'] = array( $result_value->element, 'render_value_for_export' );
				}
			}
		}
		unset( $result_value );

		foreach ( $results as $result ) {
			$element_values = array();

			foreach ( $result->values as $result_value ) {
				if ( ! isset( $element_values[ $result_value->element_id ] ) ) {
					$element_values[ $result_value->element_id ] = array();
				}
				$element_values[ $result_value->element_id ][] = $result_value->value;
			}

			$current = array();
			foreach ( $columns as $key => $data ) {
				$current_key = $headlines ? $data['title'] : $key;
				if ( 'export' === $filter && isset( $data['export_callback'] ) && is_callable( $data['export_callback'] ) ) {
					$current[ $current_key ] = call_user_func( $data['export_callback'], $result );
				} elseif ( 'display' === $filter && isset( $data['callback'] ) && is_callable( $data['callback'] ) ) {
					$current[ $current_key ] = call_user_func( $data['callback'], $result );
				} elseif( isset( $data['raw_callback'] ) && is_callable( $data['raw_callback'] ) ) {
					$current[ $current_key ] = call_user_func( $data['raw_callback'], $result );
				} else { // invalid or missing callbacks, skip
					$current[ $current_key ] = '';
				}
			}
			unset( $data );

			foreach ( $value_columns as $key => $data ) {
				$current_key = $headlines ? $data['title'] : $key;
				if ( ! isset( $element_values[ $data['element_id'] ] ) ) {
					$current[ $current_key ] = ''; // element missing from this result, skip
				} elseif ( isset( $data['answer'] ) ) {
					if ( in_array( $data['answer'], $element_values[ $data['element_id'] ], true ) ) {
						$current[ $current_key ] = 'raw' !== $filter ? __( 'Yes', 'torro-forms' ) : 'yes';
					} else {
						$current[ $current_key ] = 'raw' !== $filter ? __( 'No', 'torro-forms' ) : 'no';
					}
				} else {
					if ( 'export' === $filter && isset( $data['export_callback'] ) && is_callable( $data['export_callback'] ) ) {
						$current[ $current_key ] = call_user_func( $data['export_callback'], $element_values[ $data['element_id'] ][0] );
					} elseif ( 'display' === $filter && isset( $data['callback'] ) && is_callable( $data['callback'] ) ) {
						$current[ $current_key ] = call_user_func( $data['callback'], $element_values[ $data['element_id'] ][0] );
					} else {
						$current[ $current_key ] = $element_values[ $data['element_id'] ][0];
					}
				}
			}
			unset( $data );

			$parsed[] = $current;
		}
		unset( $result );

		return $parsed;
	}

	public function __call( $method, $args ) {
		if ( method_exists( $this, $method ) ) {
			call_user_func_array( $method, $args );
		}
	}

	protected function render_id( $result ) {
		return $result->id;
	}

	protected function render_user( $result ) {
		if ( ! $result->user_id ) {
			return __( 'not available', 'torro-forms' );
		}

		$user = get_user_by( 'id', $result->user_id );
		if ( ! $user || ! $user->exists() ) {
			return __( 'not available', 'torro-forms' );
		}

		return $user->display_name;
	}

	protected function render_user_id( $result ) {
		return $result->user_id;
	}

	protected function render_date( $result ) {
		return date_i18n( get_option( 'date_format' ), $result->timestamp );
	}

	protected function render_timestamp( $result ) {
		return $result->timestamp;
	}
}
