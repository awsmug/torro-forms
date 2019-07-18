<?php
/**
 * React frontend class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Frontends;

use awsmug\Torro_Forms\Modules\Hooks_Submodule_Interface;
use Leaves_And_Love\Plugin_Lib\Traits\Hook_Service_Trait;

/**
 * Class for an access control to restrict based on a time range.
 *
 * @since 1.2.0
 */
class React extends Frontend implements Hooks_Submodule_Interface {
	use Hook_Service_Trait;

	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.2.0
	 */
	protected function bootstrap() {
		$this->slug        = 'react';
		$this->title       = __( 'React (Experimental!)', 'torro-forms' );
		$this->description = __( 'Allows you to use a react frontend.', 'torro-forms' );

		$this->setup_hooks();
	}

	/**
	 * Filter form data
	 *
	 * @since 1.2.0
	 *
	 * @param array $data    Array with form model Data.
	 * @param int   $form_id Form Id.
	 *
	 * @return array $data   Filtered array with form model Data.
	 */
	public function filter_form_data( $data, $form_id ) {
		$data['redirect_type'] = torro()->modules()->get( 'actions' )->get( 'redirection' )->get_form_option( $form_id, 'type' );

		if ( 'redirect_url' === $data['redirect_type'] ) {
			$data['redirect_url'] = torro()->modules()->get( 'actions' )->get( 'redirection' )->get_form_option( $form_id, 'url' );
		}
		if ( 'redirect_page' === $data['redirect_type'] ) {
			$page_id              = torro()->modules()->get( 'actions' )->get( 'redirection' )->get_form_option( $form_id, 'page' );
			$data['redirect_url'] = get_permalink( $page_id );
		}

		$data['show_container_title']  = torro()->modules()->get( 'form_settings' )->get( 'labels' )->get_form_option( $form_id, 'show_container_title' );
		$data['required_fields_text']  = torro()->modules()->get( 'form_settings' )->get( 'labels' )->get_form_option( $form_id, 'required_fields_text' );
		$data['previous_button_label'] = torro()->modules()->get( 'form_settings' )->get( 'labels' )->get_form_option( $form_id, 'previous_button_label' );
		$data['next_button_label']     = torro()->modules()->get( 'form_settings' )->get( 'labels' )->get_form_option( $form_id, 'next_button_label' );
		$data['submit_button_label']   = torro()->modules()->get( 'form_settings' )->get( 'labels' )->get_form_option( $form_id, 'submit_button_label' );
		$data['success_message']       = torro()->modules()->get( 'form_settings' )->get( 'labels' )->get_form_option( $form_id, 'success_message' );

		return $data;
	}

	/**
	 * Sets up all action and filter hooks for the service.
	 *
	 * This method must be implemented and then be called from the constructor.
	 *
	 * @since 1.2.0
	 */
	protected function setup_hooks() {
		$this->filters = array(
			array(
				'name'     => "{$this->module->get_prefix()}form_model_data",
				'callback' => array( $this, 'filter_form_data' ),
				'priority' => 1,
				'num_args' => 2,
			),
		);
	}
}
