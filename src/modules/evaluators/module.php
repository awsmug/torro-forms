<?php
/**
 * Evaluators module class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Evaluators;

use awsmug\Torro_Forms\Modules\Module as Module_Base;
use awsmug\Torro_Forms\Modules\Submodule_Registry_Interface;
use awsmug\Torro_Forms\Modules\Submodule_Registry_Trait;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission_Manager;
use awsmug\Torro_Forms\Assets;
use awsmug\Torro_Forms\Modules\Assets_Submodule_Interface;

/**
 * Class for the Evaluators module.
 *
 * @since 1.0.0
 */
class Module extends Module_Base implements Submodule_Registry_Interface {
	use Submodule_Registry_Trait {
		Submodule_Registry_Trait::register_assets as protected _register_assets;
	}

	/**
	 * Bootstraps the module by setting properties.
	 *
	 * @since 1.0.0
	 */
	protected function bootstrap() {
		$this->slug        = 'evaluators';
		$this->title       = __( 'Evaluators', 'torro-forms' );
		$this->description = __( 'Evaluators allow evaluating form submissions, for example to generate charts and analytics.', 'torro-forms' );

		$this->submodule_base_class = Evaluator::class;
		$this->default_submodules   = array(
			'participation'     => Participation::class,
			'element_responses' => Element_Responses::class,
		);
	}

	/**
	 * Evaluates a specific form submission.
	 *
	 * @since 1.0.0
	 *
	 * @param Submission $submission Submission to evaluate.
	 * @param Form       $form       Form the submission applies to.
	 */
	protected function evaluate( $submission, $form ) {
		foreach ( $this->submodules as $slug => $evaluator ) {
			if ( ! $evaluator->enabled( $form ) ) {
				continue;
			}

			$aggregate_results = $evaluator->get_stats( $form->id );
			$aggregate_results = $evaluator->evaluate_single( $aggregate_results, $submission, $form );

			$evaluator->update_stats( $form->id, $aggregate_results );
		}
	}

	/**
	 * Shows evaluation results if a form ID is provided as GET parameter.
	 *
	 * @since 1.0.0
	 *
	 * @param Submission_Manager $submissions Submission manager instance.
	 */
	protected function maybe_show_evaluation_results( $submissions ) {
		if ( empty( $_GET['form_id'] ) ) { // WPCS: CSRF OK.
			return;
		}

		$form = $submissions->get_parent_manager( 'forms' )->get( (int) $_GET['form_id'] ); // WPCS: CSRF OK.
		if ( ! $form ) {
			return;
		}

		$tabs = array();

		foreach ( $this->submodules as $slug => $evaluator ) {
			if ( ! $evaluator->enabled( $form ) ) {
				continue;
			}

			$results = $evaluator->evaluate_all( $form );

			ob_start();
			$evaluator->show_results( $results, $form );

			$tabs[ $evaluator->get_slug() ] = array(
				'title'       => $evaluator->get_title(),
				'description' => $evaluator->get_description(),
				'content'     => ob_get_clean(),
			);
		}

		if ( empty( $tabs ) ) {
			return;
		}

		$screen = get_current_screen();
		$hidden = get_hidden_meta_boxes( $screen );
		$closed = get_user_option( 'closedpostboxes_' . $screen->id );

		if ( ! is_array( $closed ) ) {
			$closed = array();
		}

		$box_id    = 'torro-evaluations';
		$box_class = 'torro-evaluations-box postbox' . ( in_array( $box_id, $closed, true ) ? ' closed' : '' ) . ( in_array( $box_id, $hidden, true ) ? ' hide-if-js' : '' );

		$current_tab_slug = key( $tabs );

		echo '<input type="hidden" id="closedpostboxespage" value="' . esc_attr( $screen->id ) . '" />';
		wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
		?>
		<div id="<?php echo esc_attr( $box_id ); ?>" class="<?php echo esc_attr( $box_class ); ?>">
			<button type="button" class="handlediv" aria-expanded="true">
				<span class="screen-reader-text"><?php esc_html_e( 'Toggle panel: Evaluations', 'torro-forms' ); ?></span>
				<span class="toggle-indicator" aria-hidden="true"></span>
			</button>

			<h2 class="hndle">
				<span><?php esc_html_e( 'Evaluations', 'torro-forms' ); ?></span>
			</h2>

			<div class="inside">
				<div class="torro-evaluations-tabs" role="tablist">
					<?php foreach ( $tabs as $slug => $data ) : ?>
						<a id="<?php echo esc_attr( 'evaluations-tab-label-' . $slug ); ?>" class="torro-evaluations-tab" href="<?php echo esc_attr( '#evaluations-tab-' . $slug ); ?>" aria-controls="<?php echo esc_attr( 'evaluations-tab-' . $slug ); ?>" aria-selected="<?php echo $slug === $current_tab_slug ? 'true' : 'false'; ?>" role="tab">
							<?php echo esc_html( $data['title'] ); ?>
						</a>
					<?php endforeach; ?>
				</div>
				<div class="torro-evaluations-content">
					<?php foreach ( $tabs as $slug => $data ) : ?>
						<div id="<?php echo esc_attr( 'evaluations-tab-' . $slug ); ?>" class="torro-evaluations-tab-panel" aria-labelledby="<?php echo esc_attr( 'evaluations-tab-label-' . $slug ); ?>" aria-hidden="<?php echo $slug === $current_tab_slug ? 'false' : 'true'; ?>" role="tabpanel">
							<div class="torro-evaluations-description-wrap">
								<p class="description"><?php echo wp_kses_data( $data['description'] ); ?></p>
							</div>
							<div id="<?php echo esc_attr( 'torro-evaluations-results-' . $slug ); ?>" class="torro-evaluations-results">
								<?php echo $data['content']; // WPCS: XSS OK. ?>
							</div>
							<div class="torro-evaluations-shortcode">
								<label for="<?php echo esc_attr( 'torro-evaluations-shortcode-' . $slug ); ?>"><?php esc_html_e( 'Charts Shortcode:', 'torro-forms' ); ?></label>
								<input id="<?php echo esc_attr( 'torro-evaluations-shortcode-' . $slug ); ?>" class="clipboard-field regular-text" value="<?php echo esc_attr( sprintf( "[{$this->manager()->forms()->get_prefix()}form_charts id=&quot;%d&quot; mode=&quot;%s&quot;]", $form->id, $slug ) ); ?>" readonly="readonly" />
								<button type="button" class="clipboard-button button" data-clipboard-target="#<?php echo esc_attr( 'torro-evaluations-shortcode-' . $slug ); ?>">
									<?php $this->manager()->forms()->assets()->render_icon( 'torro-icon-clippy', __( 'Copy to clipboard', 'torro-forms' ) ); ?>
								</button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Registers the default evaluators.
	 *
	 * The function also executes a hook that should be used by other developers to register their own evaluators.
	 *
	 * @since 1.0.0
	 */
	protected function register_defaults() {
		foreach ( $this->default_submodules as $slug => $class_name ) {
			$this->register( $slug, $class_name );
		}

		/**
		 * Fires when the default evaluators have been registered.
		 *
		 * This action should be used to register custom evaluators.
		 *
		 * @since 1.0.0
		 *
		 * @param Module $evaluators Form setting manager instance.
		 */
		do_action( "{$this->get_prefix()}register_evaluators", $this );
	}

	/**
	 * Registers the available module scripts and stylesheets.
	 *
	 * @since 1.0.0
	 *
	 * @param Assets $assets Assets API instance.
	 */
	protected function register_assets( $assets ) {
		$assets->register_script(
			'admin-evaluations',
			'assets/dist/js/admin-evaluations.js',
			array(
				'deps'      => array( 'jquery' ),
				'ver'       => torro()->version(),
				'in_footer' => true,
			)
		);

		$assets->register_style(
			'admin-evaluations',
			'assets/dist/css/admin-evaluations.css',
			array(
				'deps' => array(),
				'ver'  => torro()->version(),
			)
		);

		$this->_register_assets( $assets );
	}

	/**
	 * Enqueues assets to load in the submissions list table view if conditions are met.
	 *
	 * @since 1.0.0
	 *
	 * @param Submission_Manager $submissions Submission manager instance.
	 */
	protected function maybe_enqueue_submission_results_assets( $submissions ) {
		if ( empty( $_GET['form_id'] ) ) { // WPCS: CSRF OK.
			return;
		}

		$form = $submissions->get_parent_manager( 'forms' )->get( (int) $_GET['form_id'] ); // WPCS: CSRF OK.
		if ( ! $form ) {
			return;
		}

		$assets = $this->manager()->assets();

		$has_enabled = false;
		foreach ( $this->submodules as $slug => $evaluator ) {
			if ( ! $evaluator->enabled( $form ) ) {
				continue;
			}

			$has_enabled = true;

			if ( ! is_a( $evaluator, Assets_Submodule_Interface::class ) ) {
				continue;
			}

			if ( ! is_callable( array( $evaluator, 'enqueue_submission_results_assets' ) ) ) {
				continue;
			}

			$evaluator->enqueue_submission_results_assets( $assets, $form );
		}

		if ( $has_enabled ) {
			$assets->enqueue_script( 'clipboard' );
			$assets->enqueue_style( 'clipboard' );
			$assets->enqueue_script( 'admin-evaluations' );
			$assets->enqueue_style( 'admin-evaluations' );
		}
	}

	/**
	 * Sets up all action and filter hooks for the service.
	 *
	 * @since 1.0.0
	 */
	protected function setup_hooks() {
		parent::setup_hooks();

		$this->actions[] = array(
			'name'     => "{$this->get_prefix()}complete_submission",
			'callback' => array( $this, 'evaluate' ),
			'priority' => 10,
			'num_args' => 2,
		);
		$this->actions[] = array(
			'name'     => "{$this->get_prefix()}before_submissions_list",
			'callback' => array( $this, 'maybe_show_evaluation_results' ),
			'priority' => 10,
			'num_args' => 1,
		);
		$this->actions[] = array(
			'name'     => "{$this->get_prefix()}list_submissions_enqueue_assets",
			'callback' => array( $this, 'maybe_enqueue_submission_results_assets' ),
			'priority' => 1,
			'num_args' => 1,
		);
		$this->actions[] = array(
			'name'     => 'init',
			'callback' => array( $this, 'register_defaults' ),
			'priority' => 100,
			'num_args' => 1,
		);
	}

	/**
	 * Handles the form charts shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts {
	 *     Array of shortcode attributes. In addition to the attributes specified here you may pass any arguments
	 *     that should be forwarded to the respective evaluator.
	 *
	 *     @type int    $id   Form ID. This must always be present.
	 *     @type string $mode Slug of the evaluator to use. The evaluator must exist and be enabled for the form.
	 *                        Default is 'element_responses'.
	 * }
	 * @return string Shortcode output.
	 */
	public function get_shortcode_content( $atts ) {
		$args = $atts;

		$atts = shortcode_atts(
			array(
				'id'   => '',
				'mode' => 'element_responses',
			),
			$atts,
			"{$this->get_prefix()}form_charts"
		);

		$args = array_diff_key( $args, $atts );

		$atts['id'] = absint( $atts['id'] );

		if ( empty( $atts['id'] ) ) {
			return __( 'Shortcode is missing a form ID!', 'torro-forms' );
		}

		if ( empty( $atts['mode'] ) ) {
			return __( 'Shortcode is missing a mode!', 'torro-forms' );
		}

		$form = torro()->forms()->get( $atts['id'] );
		if ( ! $form ) {
			return __( 'Shortcode is using an invalid form ID!', 'torro-forms' );
		}

		$evaluator = $this->get( $atts['mode'] );
		if ( is_wp_error( $evaluator ) ) {
			return __( 'Shortcode is using an invalid mode!', 'torro-forms' );
		}

		if ( ! $evaluator->enabled( $form ) ) {
			return __( 'Shortcode is using an invalid mode!', 'torro-forms' );
		}

		if ( is_a( $evaluator, Assets_Submodule_Interface::class ) && is_callable( array( $evaluator, 'enqueue_submission_results_assets' ) ) ) {
			$evaluator->enqueue_submission_results_assets( $this->manager()->assets(), $form );
		}

		$results = $evaluator->evaluate_all( $form );

		ob_start();

		echo '<div id="' . esc_attr( 'torro-evaluations-results-' . $evaluator->get_slug() ) . '" class="torro-evaluations-results">' . "\n";
		$evaluator->show_results( $results, $form, $args );
		echo "\n" . '</div>';

		$onclick = "var clickedLink=this;Array.from( clickedLink.parentElement.children ).forEach(function(link){clickedLink===link?link.setAttribute('aria-selected','true')||link.parentElement.parentElement.querySelector('#'+link.getAttribute('aria-controls')).setAttribute('aria-hidden','false'):link.setAttribute('aria-selected','false')||link.parentElement.parentElement.querySelector('#'+link.getAttribute('aria-controls')).setAttribute('aria-hidden','true')});return false";

		return str_replace( ' class="torro-evaluations-subtab"', ' class="torro-evaluations-subtab" onclick="' . esc_attr( $onclick ) . '"', ob_get_clean() );
	}

	/**
	 * Handles the deprecated form charts shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts {
	 *     Array of shortcode attributes.
	 *
	 *     @type int    $id   Form ID. This must always be present.
	 *     @type string $mode Slug of the evaluator to use. The evaluator must exist and be enabled for the form.
	 *                        Default is 'element_responses'.
	 * }
	 * @return string Shortcode output.
	 */
	public function get_deprecated_shortcode_content( $atts ) {
		$this->manager()->error_handler()->deprecated_shortcode( 'form_charts', '1.0.0-beta.9', "{$this->manager()->forms()->get_prefix()}form_charts" );

		$atts['mode'] = 'element_responses';

		return $this->get_shortcode_content( $atts );
	}

	/**
	 * Handles the deprecated element chart shortcode.
	 *
	 * Arguments are tweaked around inside it and passed on to the new shortcode to account for back-compat.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts {
	 *     Array of shortcode attributes.
	 *
	 *     @type int $id Element ID. This must always be present.
	 * }
	 * @return string Shortcode output.
	 */
	public function get_deprecated_element_chart_shortcode_content( $atts ) {
		$this->manager()->error_handler()->deprecated_shortcode( 'element_chart', '1.0.0-beta.9', "{$this->manager()->forms()->get_prefix()}form_charts" );

		$atts['mode'] = 'element_responses';

		if ( ! isset( $atts['id'] ) ) {
			return __( 'Shortcode is missing an element ID!', 'torro-forms' );
		}

		$atts['element_id'] = $atts['id'];
		unset( $atts['id'] );

		$element = $this->manager()->forms()->get_child_manager( 'containers' )->get_child_manager( 'elements' )->get( $atts['element_id'] );
		if ( ! $element ) {
			return __( 'Shortcode is using an invalid element ID!', 'torro-forms' );
		}

		$container = $element->get_container();
		if ( ! $container ) {
			return __( 'It looks like the container for this element has been removed. Please enter a different element ID into the shortcode.', 'torro-forms' );
		}

		$atts['id'] = $container->form_id;

		return $this->get_shortcode_content( $atts );
	}

	/**
	 * Adds the service hooks.
	 *
	 * @since 1.0.0
	 */
	public function add_hooks() {
		if ( ! $this->hooks_added ) {
			add_shortcode( "{$this->get_prefix()}form_charts", array( $this, 'get_shortcode_content' ) );
			add_shortcode( 'form_charts', array( $this, 'get_deprecated_shortcode_content' ) );
			add_shortcode( 'element_chart', array( $this, 'get_deprecated_element_chart_shortcode_content' ) );
		}

		return parent::add_hooks();
	}

	/**
	 * Removes the service hooks.
	 *
	 * @since 1.0.0
	 */
	public function remove_hooks() {
		if ( $this->hooks_added ) {
			remove_shortcode( "{$this->get_prefix()}form_charts" );
			remove_shortcode( 'form_charts' );
			remove_shortcode( 'element_chart' );
		}

		return parent::remove_hooks();
	}
}
