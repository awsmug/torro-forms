<?php
/**
 * Core: Torro_Container class
 *
 * @package TorroForms
 * @subpackage CoreModels
 * @version 1.0.0-beta.6
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Container class
 *
 * @since 1.0.0-beta.1
 *
 * @property int    $form_id
 * @property string $label
 * @property int    $sort
 *
 * @property-read array $elements
 */
class Torro_Container extends Torro_Instance_Base {

	/**
	 * Label of container
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $label = null;

	/**
	 * Sort number of container
	 *
	 * @var int
	 * @since 1.0.0
	 */
	protected $sort = null;

	/**
	 * ID of container
	 *
	 * @var Torro_Element[]
	 * @since 1.0.0
	 */
	protected $elements = array();

	/**
	 * Torro_Container constructor.
	 *
	 * @param int $id
	 *
	 * @since 1.0.0
	 */
	public function __construct( $id = null ) {
		parent::__construct( $id );
	}

	/**
	 * Renders and returns the container HTML output.
	 *
	 * @since 1.0.0
	 *
	 * @param array $response
	 * @param array $errors
	 *
	 * @return string
	 */
	public function get_html( $response = array(), $errors = array() ) {
		ob_start();
		torro()->template( 'container', $this->to_json( $response, $errors ) );
		return ob_get_clean();
	}

	/**
	 * Prepares data to render the container HTML output.
	 *
	 * @since 1.0.0
	 *
	 * @param array $response
	 * @param array $errors
	 *
	 * @return array
	 */
	public function to_json( $response = array(), $errors = array() ) {
		$hidden_fields = '<input type="hidden" name="torro_response[container_id]" value="' . $this->id . '">';

		$data = array(
			'container_id'	=> $this->id,
			'title'			=> false,
			'hidden_fields'	=> $hidden_fields,
			'elements'		=> array(),
		);

		if ( apply_filters( 'torro_form_container_show_title', true, $this->superior_id, $this->id ) ) {
			$data['title'] = $this->label;
		}

		foreach ( $this->elements as $element ) {
			if ( is_wp_error( $element ) ) {
				// element is missing, skip
				continue;
			}

			$element_response = isset( $response[ $element->id ] ) ? $response[ $element->id ] : null;
			$element_errors = isset( $errors[ $element->id ] ) ? $errors[ $element->id ] : null;

			$data['elements'][] = $element->to_json( $element_response, $element_errors );
		}

		return $data;
	}

	public function move( $form_id ) {
		return parent::move( $form_id );
	}

	public function copy( $form_id ) {
		return parent::copy( $form_id );
	}

	protected function init() {
		$this->table_name = 'torro_containers';
		$this->superior_id_name = 'form_id';
		$this->manager_method = 'containers';
		$this->valid_args = array(
			'label' 	=> 'string',
			'sort' 		=> 'int',
		);
	}

	/**
	 * Populating object
	 *
	 * @param int $id
	 *
	 * @since 1.0.0
	 */
	protected function populate( $id ) {
		parent::populate( $id );

		if ( $this->id ) {
			$this->elements = torro()->elements()->query( array(
				'container_id'	=> $this->id,
				'number'		=> -1,
				'orderby'		=> 'sort',
				'order'			=> 'ASC',
			) );
		}
	}

	protected function delete_from_db(){
		$status = parent::delete_from_db();

		if ( $status && ! is_wp_error( $status ) ) {
			foreach ( $this->elements as $element ) {
				torro()->elements()->delete( $element->id );
			}
		}

		return $status;
	}
}
