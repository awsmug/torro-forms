<?php
/**
 * Honeypot protector class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Protectors;

use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use WP_Error;
use Exception;

/**
 * Class for a protector using a honeypot field.
 *
 * @since 1.0.0
 */
class Honeypot extends Protector {

	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.0.0
	 */
	protected function bootstrap() {
		$this->slug        = 'honeypot';
		$this->title       = __( 'Honeypot', 'torro-forms' );
		$this->description = __( 'Uses a Honeypot field that users must not fill in to recognize bots.', 'torro-forms' );
	}

	/**
	 * Verifies a request by ensuring that it is not spammy.
	 *
	 * @since 1.0.0
	 *
	 * @param array           $data       Submission POST data.
	 * @param Form            $form       Form object.
	 * @param Submission|null $submission Submission object, or null if a new submission.
	 * @return bool|WP_Error True if request is not spammy, false or error object otherwise.
	 */
	public function verify_request( $data, $form, $submission = null ) {
		if ( ! empty( $_POST['email'] ) ) { // WPCS: CSRF OK.
			return new WP_Error( 'honeypot_filled', __( 'You entered something into the field that is used to detect whether you are human. Please leave it blank.', 'torro-forms' ) );
		}

		return true;
	}

	/**
	 * Renders the output for the protector before the Submit button.
	 *
	 * @since 1.0.0
	 *
	 * @param Form $form Form object.
	 */
	public function render_output( $form ) {
		$prefix = $this->module->manager()->get_prefix();

		/** This filter is documented in src/db-objects/elements/element.php */
		$input_classes = apply_filters( "{$prefix}element_input_classes", array( 'torro-element-input' ) );

		/** This filter is documented in src/db-objects/elements/element.php */
		$label_classes = apply_filters( "{$prefix}element_label_classes", array( 'torro-element-label' ) );

		/** This filter is documented in src/db-objects/elements/element.php */
		$wrap_classes = apply_filters( "{$prefix}element_wrap_classes", array( 'torro-element-wrap' ) );

		/** This filter is documented in src/db-objects/elements/element.php */
		$description_classes = apply_filters( "{$prefix}element_description_classes", array( 'torro-element-description' ) );

		/** This filter is documented in src/db-objects/elements/element.php */
		$errors_classes = apply_filters( "{$prefix}element_errors_classes", array( 'torro-element-errors' ) );

		$label = $this->get_form_option( $form->id, 'skip_field_label' );
		if ( empty( $label ) ) {
			$label = $this->get_default_skip_field_label();
		}

		$data = array(
			'id'                => 0,
			'container_id'      => 0,
			'label'             => $label,
			'sort'              => 0,
			'type'              => 'honeypot',
			'value'             => '',
			'input_attrs'       => array(
				'id'    => 'torro-email',
				'name'  => 'email',
				'class' => implode( ' ', $input_classes ),
			),
			'label_required'    => '',
			'label_attrs'       => array(
				'id'    => 'torro-email-label',
				'class' => implode( ' ', $label_classes ),
				'for'   => 'torro-email',
			),
			'wrap_attrs'        => array(
				'id'    => 'torro-email-wrap',
				'class' => implode( ' ', $wrap_classes ),
			),
			'description'       => '',
			'description_attrs' => array(
				'id'    => 'torro-email-description',
				'class' => implode( ' ', $description_classes ),
			),
			'errors'            => array(),
			'errors_attrs'      => array(
				'id'    => 'torro-email-errors',
				'class' => implode( ' ', $errors_classes ),
			),
			'before'            => '',
			'after'             => '',
		);

		torro()->template()->get_partial( 'element', $data );
	}

	/**
	 * Returns the available meta fields for the submodule.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_meta_fields() {
		$meta_fields = parent::get_meta_fields();

		$meta_fields['skip_field_label'] = array(
			'type'          => 'text',
			'label'         => __( 'Skip Field Label', 'torro-forms' ),
			'description'   => __( 'Enter the label to show for the honeypot field. This should indicate that the user must not fill it in.', 'torro-forms' ),
			'default'       => $this->get_default_skip_field_label(),
			'input_classes' => array( 'regular-text' ),
			'wrap_classes'  => array( 'has-torro-tooltip-description' ),
		);

		return $meta_fields;
	}

	/**
	 * Returns the default message to display when the user is not logged in.
	 *
	 * @since 1.0.0
	 *
	 * @return string Message to display.
	 */
	protected function get_default_skip_field_label() {
		return __( 'If you are a human, do not fill in this field.', 'torro-forms' );
	}
}
