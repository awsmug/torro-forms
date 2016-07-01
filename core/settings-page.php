<?php
/**
 * Core: Torro_Settings_Page class
 *
 * @package TorroForms
 * @subpackage Core
 * @version 1.0.0-beta.6
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms settings page class
 *
 * Renders the settings page for the plugin and its extensions.
 *
 * @since 1.0.0-beta.1
 */
class Torro_Settings_Page {

	/**
	 * The current tab
	 *
	 * @var string
	 * @since 1.0.0
	 */
	static $current_tab;

	/**
	 * The current section
	 *
	 * @var string
	 * @since 1.0.0
	 */
	static $current_section;

	/**
	 * Instance
	 *
	 * @var object
	 * @since 1.0.0
	 */
	private static $instance = null;

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		add_action( 'init', array( __CLASS__, 'maybe_flush_rewrite_rules' ), 1 );
		add_action( 'init', array( __CLASS__, 'save' ), 20 );
		add_action( 'admin_print_styles', array( __CLASS__, 'register_styles' ) );
	}

	/**
	 * Init in WordPress, run on constructor
	 *
	 * @return null
	 * @since 1.0.0
	 */
	public static function init() {
		if ( ! is_admin() ) {
			return;
		}

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Show admin settings
	 *
	 * @since 1.0.0
	 */
	public static function show() {
		self::init_tabs();

		$html = '<div class="wrap af">';
		$html .= '<form name="torro_settings" id="torro-settings" method="POST">';
		$html .= '<input type="hidden" id="torro_save_settings" name="torro_save_settings" value="' . wp_create_nonce( '_torro_save_settings_nonce' ) . '" />';

		$all_settings = torro()->settings()->get_all_registered();

		if ( 0 < count( $all_settings ) ) {
			/**
			 * Tabs
			 */
			$html .= '<h2 class="nav-tab-wrapper">';
			foreach ( $all_settings AS $setting ) {
				// Discard Settings if there are no settings
				if ( 0 === count( $setting->settings ) && 0 === count( $setting->sub_settings ) ) {
					continue;
				}

				$css_classes = '';
				if ( $setting->name === self::$current_tab ) {
					$css_classes = ' nav-tab-active';
				}

				$html .= '<a href="' . admin_url( 'edit.php?post_type=torro_form&page=Torro_Admin&tab=' . $setting->name ) . '" class="nav-tab' . $css_classes . '">' . $setting->title . '</a>';
			}
			$html .= '</h2>';

			/**
			 * Content
			 */
			$html .= '<div id="torro-settings-content" class="' . self::$current_tab . '">';
			$html .= self::show_tab( torro()->settings()->get_registered( self::$current_tab ), self::$current_section );
			ob_start();
			do_action( 'torro_settings_' . self::$current_tab );
			$html .= ob_get_clean();
			$html .= '</div>';

			$html .= '<input name="torro_save_settings" type="submit" class="button-primary button-save-settings" value="' . esc_attr__( 'Save Settings', 'torro-forms' ) . '" />';
		} else {
			$html .= '<p>' . esc_html__( 'There are no settings available.', 'torro-forms' ) . '</p>';
		}

		$html .= '</form>';

		$html .= '</div>';
		$html .= '<div class="clear"></div>';

		echo $html;
	}

	/**
	 * Initializing Tabs
	 *
	 * @since 1.0.0
	 */
	public static function init_tabs() {
		if ( isset( $_GET[ 'tab' ] ) ) {
			self::$current_tab = $_GET[ 'tab' ];
		} else {
			self::$current_tab = 'general';
		}

		if ( isset( $_GET[ 'section' ] ) ) {
			self::$current_section = $_GET[ 'section' ];
		}
	}

	/**
	 * Shows the Settings
	 *
	 * @param string $sub_setting_name
	 *
	 * @return string
	 * @since 1.0.0
	 */
	private static function show_tab( $settings, $section = '' ) {
		if ( 0 === count( $settings->sub_settings ) ) {
			/**
			 * Page without subsettings
			 */
			$settings_handler = new Torro_Settings_Handler( $settings->name, $settings->settings );
			$html             = $settings_handler->get();
		} else {

			/**
			 * Setting up settings array
			 */
			if ( is_array( $settings->settings ) && count( $settings->settings ) > 0 ) {
				$sub_settings = array(
					'general' => array(
						'title'    => __( 'General', 'torro-forms' ),
						'settings' => $settings->settings,
					)
				);

				$sub_settings = array_merge( $sub_settings, $settings->sub_settings );
			} else {
				$sub_settings = $settings->sub_settings;

				if ( empty( $section ) ) {
					$section = key( $sub_settings );
				}
			}

			/**
			 * Get active settings name
			 */
			$settings_name = $settings->name;
			if ( ! empty( $sub_settings ) ) {
				$settings_name .= '_' . $section;
			}

			if ( empty( $section ) ) {
				if( array_key_exists( 'general', $sub_settings ) ) {
					$active_settings = 'general';
				} else {
					reset( $sub_settings );
					$active_settings = key( $sub_settings );
				}
			} else {
				$active_settings = $section;
			}

			/**
			 * Submenu
			 */
			$html = '<ul id="torro-settings-submenu">';
			foreach ( $sub_settings as $name => $setting ) {
				$css_classes = '';
				if ( $name === $active_settings ) {
					$css_classes = ' active';
				}
				$html .= '<li class="submenu-tab' . $css_classes . '"><a href="' . admin_url( 'edit.php?post_type=torro_form&page=Torro_Admin&tab=' . $settings->name . '&section=' . $name ) . '">' . $setting[ 'title' ] . '</a></li>';
			}
			$html .= '</ul>';

			/**
			 * Subsettings content
			 */
			$html .= '<div id="torro-settings-subcontent">';

			$settings = $sub_settings[ $active_settings ];
			$settings_handler = new Torro_Settings_Handler( $settings_name, $settings[ 'settings' ] );
			$html .= $settings_handler->get();

			ob_start();
			do_action( 'torro_setting_' . $settings_name . '_content' );
			$html .= ob_get_clean();

			$html .= '</div>';
		}

		return $html;
	}

	/**
	 * Saving settings
	 *
	 * @since 1.0.0
	 */
	public static function save() {
		if ( ! isset( $_POST['torro_save_settings'] ) ) {
			return;
		}

		$all_settings = torro()->settings()->get_all_registered();

		$tab = '';
		if ( isset( $_GET['tab'] ) ) {
			$tab = $_GET['tab'];
		}
		if( empty( $tab ) ){
			$tab = 'general';
		}

		$section = '';
		if ( isset( $_GET['section'] ) ) {
			$section = $_GET['section'];
		}
		if( empty( $section ) && 'general' !== $tab ){
			if ( count( $all_settings[ $tab ]->settings ) == 0 ){
				$actual_settings = $all_settings[ $tab ]->sub_settings;
				reset( $actual_settings );
				$section = key( $actual_settings );
			}
		}

		if ( 0 < count( $all_settings ) ) {
			/**
			 * Running all registered settings
			 */
			foreach ( $all_settings AS $setting ) {
				if( $setting->name !== $tab ){
					continue;
				}

				if ( count( $setting->sub_settings ) == 0 ) {
					/**
					 * Page without subsettings
					 */
					$settings_handler = new Torro_Settings_Handler( $setting->name, $setting->settings );
					$settings_handler->save();

					do_action( 'torro_settings_save_' . $setting->name );
				} else {
					/**
					 * Page with subsettings
					 */
					$settings_name = $setting->name;

					if ( isset( $section ) ) {
						$settings_name .= '_' . $section;
					}


					$sub_settings = array(
						'general' => array(
							'title'    => __( 'General', 'torro-forms' ),
							'settings' => $setting->settings,
						)
					);

					$sub_settings = array_merge( $sub_settings, $setting->sub_settings );



					$settings = $sub_settings[ '' === $section ? 'general' : $section ];

					$settings_handler = new Torro_Settings_Handler( $settings_name, $settings[ 'settings' ] );
					$settings_handler->save();
				}
			}
		}
		do_action( 'torro_settings_save', $section );
	}

	/**
	 * Save rewrite slug option early and flush rewrite rules.
	 *
	 * @since 1.0.0
	 */
	public static function maybe_flush_rewrite_rules() {
		if ( ! isset( $_POST['torro_save_settings'] ) ) {
			return;
		}

		$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
		if ( 'general' !== $tab ) {
			return;
		}

		if ( ! isset( $_POST['slug'] ) ) {
			return;
		}

		$value = wp_unslash( $_POST['slug'] );
		$old_value = get_option( 'torro_settings_general_slug' );
		if ( $value === $old_value ) {
			return;
		}

		update_option( 'torro_settings_general_slug', $value );
		add_action( 'torro_settings_saved', 'flush_rewrite_rules' );
	}

	/**
	 * Registers and enqueues admin-specific styles.
	 *
	 * @since 1.0.0
	 */
	public static function register_styles() {
		if ( ! torro()->is_settingspage() ) {
			return;
		}

		wp_enqueue_style( 'torro-settings-page', torro()->get_asset_url( 'settings-page', 'css' ) );
		wp_enqueue_style( 'torro-templatetags', torro()->get_asset_url( 'templatetags', 'css' ) ); // Todo: This is a workaround! Why isn't it working anymore in other scripts?
	}
}
Torro_Settings_Page::init();
