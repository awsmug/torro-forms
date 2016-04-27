<?php
/**
 * Core: Torro_Container class
 *
 * @package TorroForms
 * @subpackage CoreModels
 * @version 1.0.0-beta.1
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
	 * @var Torro_Form_Element[]
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
	 * Getting form html of container
	 *
	 * @param array $response
	 * @param array $errors
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_html( $response = array(), $errors = array() ) {
		$html = sprintf( '<input type="hidden" name="torro_response[container_id]" value="%d" />', $this->id );

		foreach ( $this->elements as $element ) {
			if ( is_wp_error( $element ) ) {
				$html .= $element->get_error_message();
				continue;
			}

			if ( ! isset( $response[ $element->id ] ) ) {
				$response[ $element->id ] = null;
			}
			if ( ! isset( $errors[ $element->id ] ) ) {
				$errors[ $element->id ] = null;
			}
			$html .= $element->get_html( $response[ $element->id ], $errors[ $element->id ] );
		}

		return $html;
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
