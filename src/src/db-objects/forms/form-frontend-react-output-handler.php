<?php
/**
 * Form frontend react output handler class
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Forms;

use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use awsmug\Torro_Forms\DB_Objects\Containers\Container;
use awsmug\Torro_Forms\Error;

/**
 * Class for handling the form frontend output in the react way.
 *
 * @since 1.1.0
 */
class Form_Frontend_React_Output_Handler extends Form_Frontend_Output_Handler {
    /**
	 * Renders the content for a given form.
	 *
	 * @since 1.1.0
	 *
	 * @param Form            $form       Form object.
	 * @param Submission|null $submission Optional. Submission object, or null if none available. Default null.
	 */
    public function render_form_content( $form, $submission ) {
        // The rest comes from React...
        echo '<div id="torro-forms-react-canvas"></div>';
    }

    /**
	 * Renders the content for a given form.
	 *
	 * @since 1.1.0
	 *
	 * @param Form            $form       Form object.
	 * @param Submission|null $submission Optional. Submission object, or null if none available. Default null.
	 */
    public function ajax_process_submission() {
        // Moves to submossion handler file!
    }

    /**
	 * Enqueues the frontend assets if necessary.
	 *
	 * @since 1.0.0
	 */
	public function maybe_enqueue_frontend_assets() {
        parent::maybe_enqueue_frontend_assets();

        // Check will come...
        $load_js = true;

        if ( $load_js ) {
			$this->form_manager->assets()->enqueue_script( 'frontend-bundle' );
		}
    }
}