<?php
/**
 * Assets manager class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms;

use Leaves_And_Love\Plugin_Lib\Assets as Assets_Base;
use Leaves_And_Love\Plugin_Lib\Traits\Hook_Service_Trait;

/**
 * Class for managing assets.
 *
 * @since 1.0.0
 */
class Assets extends Assets_Base {
	use Hook_Service_Trait;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $prefix The prefix for all AJAX actions.
	 * @param array  $args   {
	 *     Array of arguments.
	 *
	 *     @type callable $path_callback Callback to create a full plugin path from a relative path.
	 *     @type callable $url_callback  Callback to create a full plugin URL from a relative path.
	 * }
	 */
	public function __construct( $prefix, $args ) {
		parent::__construct( $prefix, $args );

		$this->setup_hooks();
	}

	/**
	 * Transforms a relative asset path into a full URL.
	 *
	 * The method also automatically handles loading a minified vs non-minified file.
	 *
	 * @since 1.0.0
	 *
	 * @param string $src Relative asset path.
	 * @return string|bool Full asset URL, or false if the path
	 *                     is requested for a full $src URL.
	 */
	public function get_full_url( $src ) {
		return $this->get_full_path( $src, true );
	}

	/**
	 * Transforms a relative asset path into a full path.
	 *
	 * The method also automatically handles loading a minified vs non-minified file.
	 *
	 * @since 1.0.0
	 *
	 * @param string $src Relative asset path.
	 * @param bool   $url Whether to return the URL instead of the path. Default false.
	 * @return string|bool Full asset path or URL, depending on the $url parameter, or false
	 *                     if the path is requested for a full $src URL.
	 */
	public function get_full_path( $src, $url = false ) {
		if ( preg_match( '/^(http|https):\/\//', $src ) || 0 === strpos( $src, '//' ) ) {
			if ( $url ) {
				return $src;
			}

			return false;
		}

		if ( '.js' !== substr( $src, -3 ) && '.css' !== substr( $src, -4 ) ) {
			if ( $url ) {
				return call_user_func( $this->url_callback, $src );
			}

			return call_user_func( $this->path_callback, $src );
		}

		return parent::get_full_path( $src, $url );
	}

	/**
	 * Renders an SVG icon.
	 *
	 * @since 1.0.0
	 *
	 * @param string $icon_id ID of the SVG icon to use.
	 * @param string $title   Optional. Alternative text for the SVG. If not, the element will be
	 *                        ignored by screen readers. Default empty string.
	 * @param string $class   Optional. Additional CSS class to use on the SVG element. Default
	 *                        empty string.
	 */
	public function render_icon( $icon_id, $title = '', $class = '' ) {
		$aria_hidden     = ' aria-hidden="true"';
		$aria_labelledby = '';

		if ( ! empty( $title ) ) {
			$unique_id = uniqid();

			$aria_hidden     = '';
			$aria_labelledby = ' aria-labelledby="title-' . esc_attr( $unique_id ) . '"';
		}

		?>
		<svg class="torro-icon <?php echo esc_attr( $class ); ?>"<?php echo $aria_hidden . $aria_labelledby; // WPCS: XSS OK. ?> role="img">
			<?php if ( ! empty( $title ) ) : ?>
				<title id="title-<?php echo esc_attr( $unique_id ); ?>"><?php echo esc_html( $title ); ?></title>
			<?php endif; ?>
			<use href="#<?php echo esc_attr( $icon_id ); ?>" xlink:href="#<?php echo esc_attr( $icon_id ); ?>"></use>
		</svg>
		<?php
	}

	/**
	 * Registers all default plugin assets.
	 *
	 * @since 1.0.0
	 */
	protected function register_assets() {
		$this->register_style(
			'frontend',
			'assets/dist/css/frontend.css',
			array(
				'deps' => array(),
				'ver'  => $this->plugin_version,
			)
		);

		$this->register_script(
			'util',
			'assets/dist/js/util.js',
			array(
				'deps'      => array( 'jquery', 'underscore', 'wp-util', 'wp-api' ),
				'ver'       => $this->plugin_version,
				'in_footer' => true,
			)
		);

		$this->register_style(
			'admin-icons',
			'assets/dist/css/admin-icons.css',
			array(
				'deps' => array(),
				'ver'  => $this->plugin_version,
			)
		);

		$this->register_script(
			'admin-fixed-sidebar',
			'assets/dist/js/admin-fixed-sidebar.js',
			array(
				'deps'      => array( 'jquery' ),
				'ver'       => $this->plugin_version,
				'in_footer' => true,
			)
		);

		$this->register_script(
			'admin-tooltip-descriptions',
			'assets/dist/js/admin-tooltip-descriptions.js',
			array(
				'deps'      => array( 'jquery' ),
				'ver'       => $this->plugin_version,
				'in_footer' => true,
			)
		);

		$this->register_style(
			'admin-tooltip-descriptions',
			'assets/dist/css/admin-tooltip-descriptions.css',
			array(
				'deps' => array( 'dashicons' ),
				'ver'  => $this->plugin_version,
			)
		);

		$this->register_script(
			'admin-unload',
			'assets/dist/js/admin-unload.js',
			array(
				'deps'      => array( 'jquery', 'post' ),
				'ver'       => $this->plugin_version,
				'in_footer' => true,
			)
		);

		$this->register_script(
			'admin-form-builder',
			'assets/dist/js/admin-form-builder.js',
			array(
				'deps'          => array( $this->prefix_handle( 'util' ), 'jquery', 'underscore', 'backbone', 'wp-backbone', 'plugin-lib-fields', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-dialog', 'media-editor' ),
				'ver'           => $this->plugin_version,
				'in_footer'     => true,
				'localize_name' => 'torroBuilderI18n',
				'localize_data' => array(
					'couldNotInitCanvas'         => __( 'Could not initialize form canvas as the selector points to an element that does not exist.', 'torro-forms' ),
					'couldNotLoadData'           => __( 'Could not load form builder data. Please verify that the REST API is correctly enabled on your site.', 'torro-forms' ),
					/* translators: %s: container index number */
					'defaultContainerLabel'      => __( 'Page %s', 'torro-forms' ),
					/* translators: %s: element choice index number */
					'elementChoiceLabel'         => __( 'Choice %s', 'torro-forms' ),
					'showContent'                => __( 'Show Content', 'torro-forms' ),
					'hideContent'                => __( 'Hide Content', 'torro-forms' ),
					'yes'                        => __( 'Yes', 'torro-forms' ),
					'no'                         => __( 'No', 'torro-forms' ),
					'confirmDeleteContainer'     => __( 'Do you really want to delete this page?', 'torro-forms' ),
					'confirmDeleteElement'       => __( 'Do you really want to delete this element?', 'torro-forms' ),
					'confirmDeleteElementChoice' => __( 'Do you really want to delete this choice?', 'torro-forms' ),
					'selectElementType'          => __( 'Select Element Type', 'torro-forms' ),
					'insertIntoContainer'        => __( 'Insert into container', 'torro-forms' ),
				),
			)
		);

		$this->register_style(
			'admin-form-builder',
			'assets/dist/css/admin-form-builder.css',
			array(
				'deps' => array(),
				'ver'  => $this->plugin_version,
			)
		);

		$this->register_script(
			'admin-settings',
			'assets/dist/js/admin-settings.js',
			array(
				'deps'      => array( 'jquery' ),
				'ver'       => $this->plugin_version,
				'in_footer' => true,
			)
		);

		$this->register_style(
			'admin-settings',
			'assets/dist/css/admin-settings.css',
			array(
				'deps' => array(),
				'ver'  => $this->plugin_version,
			)
		);

		$this->register_script(
			'clipboard',
			'assets/dist/js/clipboard.js',
			array(
				'deps'      => array(),
				'ver'       => $this->plugin_version,
				'in_footer' => true,
			)
		);

		$this->register_style(
			'clipboard',
			'assets/dist/css/clipboard.css',
			array(
				'deps' => array(),
				'ver'  => $this->plugin_version,
			)
		);

		$this->register_script(
			'template-tag-fields',
			'assets/dist/js/template-tag-fields.js',
			array(
				'deps'      => array( 'plugin-lib-fields', 'jquery' ),
				'ver'       => $this->plugin_version,
				'in_footer' => true,
			)
		);

		$this->register_style(
			'template-tag-fields',
			'assets/dist/css/template-tag-fields.css',
			array(
				'deps' => array( 'plugin-lib-fields' ),
				'ver'  => $this->plugin_version,
			)
		);

		$this->register_script(
			'd3',
			'node_modules/d3/d3.js',
			array(
				'deps'      => array(),
				'ver'       => '3.5.17',
				'in_footer' => true,
			)
		);

		$this->register_script(
			'c3',
			'node_modules/c3/c3.js',
			array(
				'deps'      => array( 'd3' ),
				'ver'       => '0.4.11',
				'in_footer' => true,
			)
		);

		$c3_script = <<<JAVASCRIPT
( function( c3 ) {
	var c3Definitions = document.getElementsByClassName( 'c3-chart-data' );
	var c3Definition, i;

	function parseFormatFunction( format ) {
		var search = [
			'%value%',
			'%percentage%',
			'%id%'
		];
		var replace, replaced, percentage, i;

		function formatter( value, id ) {
			value = Math.round( value * 100 ) / 100;

			if ( ! format.template ) {
				return '' + value;
			}

			percentage = ( ( format.aggregate && format.aggregate > 0 ) ? value / format.aggregate : 0.0 ) * 100.0;

			replace = [
				value,
				Math.round( percentage * 100 ) / 100,
				id
			];

			replaced = format.template;

			for ( i = 0; i < replace.length; i++ ) {
				replaced = replaced.replace( search[ i ], replace[ i ] );
			}

			return replaced;
		};

		return formatter;
	}

	for ( i = 0; i < c3Definitions.length; i++ ) {
		c3Definition = JSON.parse( c3Definitions[ i ].innerHTML );

		if ( 'object' === typeof c3Definition.data && 'object' === typeof c3Definition.data.labels && 'object' === typeof c3Definition.data.labels.format ) {
			c3Definition.data.labels.format = parseFormatFunction( c3Definition.data.labels.format );
		}

		c3.generate( c3Definition );
	}
}( window.c3 ) );
JAVASCRIPT;

		wp_add_inline_script( 'c3', $c3_script );

		$this->register_style(
			'c3',
			'node_modules/c3/c3.css',
			array(
				'deps' => array(),
				'ver'  => '0.4.11',
			)
		);

		/**
		 * Fires after all default plugin assets have been registered.
		 *
		 * Do not use this action to actually enqueue any assets, as it is only
		 * intended for registering them.
		 *
		 * @since 1.0.0
		 *
		 * @param Assets $assets The assets manager instance.
		 */
		do_action( "{$this->get_prefix()}register_assets", $this );
	}

	/**
	 * Enqueues the icons stylesheet.
	 *
	 * @since 1.0.0
	 */
	protected function enqueue_icons() {
		$this->enqueue_style( 'admin-icons' );
	}

	/**
	 * Adds utility CSS classes to the admin body tag.
	 *
	 * @since 1.0.0
	 *
	 * @param string $classes Optional. Admin body classes. Default empty string.
	 * @return string Modified admin body classes.
	 */
	protected function add_admin_utility_body_classes( $classes = '' ) {
		if ( ! empty( $classes ) ) {
			$classes .= ' ';
		}

		$classes .= 'no-clipboard';

		return $classes;
	}

	/**
	 * Prints the SVG icons to the page so that they are available to use.
	 *
	 * @since 1.0.0
	 */
	protected function load_icons() {
		$svg_icons = $this->get_full_path( 'assets/dist/img/icons.svg' );

		if ( file_exists( $svg_icons ) ) {
			require_once $svg_icons;
		}
	}

	/**
	 * Sets up all action and filter hooks for the service.
	 *
	 * This method must be implemented and then be called from the constructor.
	 *
	 * @since 1.0.0
	 */
	protected function setup_hooks() {
		$this->actions = array(
			array(
				'name'     => 'wp_enqueue_scripts',
				'callback' => array( $this, 'register_assets' ),
				'priority' => 1,
				'num_args' => 0,
			),
			array(
				'name'     => 'admin_enqueue_scripts',
				'callback' => array( $this, 'register_assets' ),
				'priority' => 1,
				'num_args' => 0,
			),
			array(
				'name'     => 'admin_enqueue_scripts',
				'callback' => array( $this, 'enqueue_icons' ),
				'priority' => 10,
				'num_args' => 0,
			),
			array(
				'name'     => 'admin_footer',
				'callback' => array( $this, 'load_icons' ),
				'priority' => 10,
				'num_args' => 0,
			),
		);

		$this->filters = array(
			array(
				'name'     => 'admin_body_class',
				'callback' => array( $this, 'add_admin_utility_body_classes' ),
				'priority' => 1,
				'num_args' => 1,
			),
		);
	}

	/**
	 * Parses the plugin version number.
	 *
	 * @since 1.0.0
	 * @static
	 *
	 * @param mixed $value The input value.
	 * @return string The parsed value.
	 */
	protected static function parse_arg_plugin_version( $value ) {
		if ( ! $value ) {
			return false;
		}

		return $value;
	}
}
