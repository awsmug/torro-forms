<?php
/**
 * Core: Torro_Element class
 *
 * @package TorroForms
 * @subpackage CoreModels
 * @version 1.0.0-beta.7
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Element base class
 *
 * @since 1.0.0-beta.1
 *
 * @property int    $container_id
 * @property string $label
 * @property int    $sort
 * @property string $type
 *
 * @property-read array $sections
 * @property-read array $answers
 * @property-read array $settings
 */
class Torro_Element extends Torro_Instance_Base {

	/**
	 * Element type
	 *
	 * @since 1.0.0
	 */
	protected $type = null;

	/**
	 * Element Label
	 *
	 * @since 1.0.0
	 */
	protected $label = null;

	/**
	 * Sort number where to display the Element
	 *
	 * @since 1.0.0
	 */
	protected $sort = 0;

	/**
	 * Sections for answers
	 *
	 * @since 1.0.0
	 */
	protected $sections = array();

	/**
	 * Element answers
	 *
	 * @since 1.0.0
	 */
	protected $answers = array();

	/**
	 * Contains all settings of the element
	 *
	 * @since 1.0.0
	 */
	protected $settings = array();

	/**
	 * Holds the element type object for this element.
	 *
	 * @since 1.0.0
	 */
	protected $type_obj = null;

	/**
	 * Contains users response for the element.
	 *
	 * @since 1.0.0
	 */
	protected $response = null;

	/**
	 * Contains response errors for the element.
	 *
	 * @since 1.0.0
	 */
	protected $errors = array();

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $id = null ) {
		parent::__construct( $id );

		$this->type_obj = torro()->element_types()->get_registered( $this->type );
		if ( is_wp_error( $this->type_obj ) ) {
			//TODO: handle error here
			return;
		}

		$this->prepopulate_settings();
	}

	/**
	 * Validate response data
	 *
	 * @param mixed $input Element input
	 *
	 * @return mixed|Torro_Error
	 * @since 1.0.0
	 */
	public function validate( $input ) {
		return $this->type_obj->validate( $input, $this );
	}

	/**
	 * Renders and returns the element HTML output.
	 *
	 * @since 1.0.0
	 *
	 * @param array $response
	 * @param array $errors
	 *
	 * @return string
	 */
	public function get_html( $response, $errors ) {
		ob_start();
		torro()->template( 'element', $this->to_json( $response, $errors ) );
		return ob_get_clean();
	}

	/**
	 * Prepares data to render the element HTML output.
	 *
	 * @since 1.0.0
	 *
	 * @param array $response
	 * @param array $errors
	 *
	 * @return array
	 */
	public function to_json( $response, $errors ) {
		if ( is_null( $errors ) ) {
			$errors = array();
		}

		$this->response = $response;
		$this->errors = $errors;

		$element_classes = array( 'torro-element', 'torro-element-' . $this->type );
		if ( is_array( $this->errors ) && 0 < count( $this->errors ) ) {
			$element_classes[] = 'error';
		}
		$element_classes = apply_filters( 'torro_element_classes', $element_classes, $this );

		$data = array(
			'template_suffix'	=> $this->type,
			'element_id'		=> $this->id,
			'label'				=> $this->label,
			'id'				=> 'torro_form_element_' . $this->id,
			'classes'			=> $element_classes,
			'errors'			=> $this->errors,
			'description'		=> '',
			'required'			=> false,
			'type'				=> $this->type_obj->to_json( $this ),
		);

		if ( isset( $this->settings['description'] ) && ! empty( $this->settings['description']->value ) ) {
			$data['description'] = $this->settings['description']->value;
		}

		if ( isset( $this->settings['required'] ) && 'yes' === $this->settings['required']->value ) {
			$data['required'] = true;
		}

		if ( isset( $this->settings['css_classes'] ) && strlen( $this->settings['css_classes']->value ) > 0 ) {
			$additional_classes = explode( ' ', esc_attr( $this->settings['css_classes']->value ) );
			$data['classes'] = array_merge( $data['classes'], $additional_classes );
		}

		/**
		 * Filters the data sent to the element template, based on the element type.
		 *
		 * This filter can be used by special element types if they need to adjust their wrapper template data.
		 *
		 * @since 1.0.0
		 */
		$data = apply_filters( 'torro_element_data_' . $this->type, $data, $this );

		return $data;
	}

	/**
	 * Is this element analyzable or not?
	 *
	 * @param obj $result_object
	 *
	 * @return boolean
	 * @since 1.0.0
	 */
	public function is_analyzable() {
		return $this->type_obj->is_analyzable();
	}

	/**
	 * Renders a value for display.
	 *
	 * @param mixed $value the unmodified value
	 *
	 * @return string the value ready to display as HTML
	 * @since 1.0.0
	 */
	public function render_value( $value ) {
		return $this->type_obj->render_value( $value );
	}

	/**
	 * Renders a value for export as XLS or CSV.
	 *
	 * @param mixed $value the unmodified value
	 *
	 * @return string the value ready for export
	 * @since 1.0.0
	 */
	public function render_value_for_export( $value ) {
		return $this->type_obj->render_value_for_export( $value );
	}

	/**
	 * Draws element box in Admin
	 *
	 * @return string $html The admin element HTML code
	 * @since 1.0.0
	 */
	public function get_admin_html() {
		return $this->type_obj->get_admin_html( $this );
	}

	/**
	 * Initializing
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->table_name = 'torro_elements';
		$this->superior_id_name = 'container_id';
		$this->manager_method = 'elements';
		$this->valid_args = array(
			'type'		=> 'string',
			'label'		=> 'string',
			'sort'		=> 'int',
		);
	}

	/**
	 * Populating element object with data
	 *
	 * @param int $id Element id
	 *
	 * @since 1.0.0
	 */
	protected function populate( $id ) {
		parent::populate( $id );

		if ( $this->id ) {
			$query_args = array(
				'element_id'	=> $this->id,
				'number'		=> -1,
			);
			$this->answers = torro()->element_answers()->query( array_merge( $query_args, array(
				'orderby'		=> 'sort',
				'order'			=> 'ASC',
			) ) );

			$settings = torro()->element_settings()->query( $query_args );
			$this->settings = array();
			foreach ( $settings as $setting ) {
				$this->settings[ $setting->name ] = $setting;
			}
		}
	}

	/**
	 * Populating settings
	 *
	 * @since 1.0.0
	 */
	protected function prepopulate_settings() {
		foreach ( $this->type_obj->settings_fields as $setting_name => $data ) {
			if ( ! isset( $this->settings[ $setting_name ] ) ) {
				$this->settings[ $setting_name ] = new stdClass();
				if ( isset( $data['default'] ) ) {
					$this->settings[ $setting_name ]->value = $data['default'];
				} else {
					$this->settings[ $setting_name ]->value = '';
				}
			}
		}
	}

	/**
	 * Delete element
	 *
	 * @return bool|false|int
	 * @since 1.0.0
	 */
	protected function delete_from_db(){
		$status = parent::delete_from_db();

		if ( $status && ! is_wp_error( $status ) ) {
			foreach ( $this->answers as $answer ) {
				torro()->element_answers()->delete( $answer->id );
			}

			foreach ( $this->settings as $setting ) {
				torro()->element_settings()->delete( $setting->id );
			}
		}

		return $status;
	}
}
