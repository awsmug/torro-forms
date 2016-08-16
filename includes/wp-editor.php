<?php
/**
 * Includes: Torro_AJAX_WP_Editor class
 *
 * @package TorroForms
 * @subpackage Includes
 * @version 1.0.0-beta.7
 * @since 1.0.0-beta.1
 */

class Torro_AJAX_WP_Editor {
	private static $editor_id = '';

	/**
	 * MCE Settings
	 *
	 * @var array
	 * @since 1.0.0
	 */
	private static $mce_settings = array();

	/**
	 * Quicktags editor settings
	 *
	 * @var array
	 * @since 1.0.0
	 */
	private static $qt_settings = array();

	public static function init_defaults() {
		$screen = get_current_screen();
		if ( 'torro_form' !== $screen->post_type ) {
			return;
		}

		add_filter( 'wp_default_editor', array( __CLASS__, 'std_editor_tinymce' ) );
	}

	/**
	 * Getting Editor HTML
	 *
	 * @param $content
	 * @param $editor_id
	 * @param $field_name
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public static function get( $content, $editor_id, $settings = array() ) {
		add_filter( 'tiny_mce_before_init', array( __CLASS__, 'set_tinymce_settings' ), 10, 2 );
		add_filter( 'quicktags_settings', array( __CLASS__, 'set_quicktags_settings' ), 10, 1 );
		add_filter( 'wp_default_editor', array( __CLASS__, 'std_editor_tinymce' ) );

		ob_start();
		wp_editor( $content, $editor_id, $settings );
		$html = ob_get_clean();

		$data = array(
			'html'		=> $html,
			'editor_id'	=> self::get_editor_id(),
			'tinymce'	=> self::get_tinymce_settings(),
			'quicktags'	=> self::get_quicktags_settings(),
		);

		remove_filter( 'tiny_mce_before_init', array( __CLASS__, 'set_tinymce_settings' ), 10, 2 );
		remove_filter( 'quicktags_settings', array( __CLASS__, 'set_quicktags_settings' ), 10, 1 );
		remove_filter( 'wp_default_editor', array( __CLASS__, 'std_editor_tinymce' ) );

		self::reset();

		return $data;
	}

	public static function set_tinymce_settings( $settings, $editor_id ) {
		$editor_fields = array(
			'selector',
			'resize',
			'menubar',
			'wpautop',
			'indent',
			'toolbar1',
			'toolbar2',
			'toolbar3',
			'toolbar4',
			'tabfocus_elements',
			'body_class',
		);

		self::$mce_settings = array_intersect_key( $settings, array_flip( $editor_fields ) );
		self::$editor_id = $editor_id;

		return $settings;
	}

	public static function set_quicktags_settings( $settings ) {
		self::$qt_settings = $settings;

		return $settings;
	}

	public static function get_editor_id() {
		return self::$editor_id;
	}

	public static function get_tinymce_settings() {
		return self::$mce_settings;
	}

	public static function get_quicktags_settings() {
		return self::$qt_settings;
	}

	public static function reset() {
		self::$editor_id = '';
		self::$mce_settings = array();
		self::$qt_settings = array();
	}

	/**
	 * Function to set standard editor to tinymce prevent tab issues on editor
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public static function std_editor_tinymce() {
		return 'tinymce';
	}

	/**
	 * Creating Editor JS
	 *
	 * @param $editor_id
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public static function editor_js( $editor_id ) {
		if( '' == $editor_id ) {
			return FALSE;
		}

		$mce_init = self::get_mce_init( $editor_id );
		$qt_init = self::get_qt_init( $editor_id );

		// Extending editor gobals
		$html = '<script type="text/javascript">
	jQuery( document ).ready( function( $ ) {
		var editor_id = "' . $editor_id . '";

		window.tinyMCEPreInit.mceInit = jQuery.extend( window.tinyMCEPreInit.mceInit, ' . $mce_init . ' );
		window.tinyMCEPreInit.qtInit = jQuery.extend( window.tinyMCEPreInit.qtInit, ' . $qt_init . ' );

		tinymce.init( window.tinyMCEPreInit.mceInit[ editor_id ] );

		try {
			quicktags( tinyMCEPreInit.qtInit[ editor_id ] );
		} catch( e ) {
			console.log( e );
		}

		QTags.instances["0"] = ""; // Dirty Hack, but needed to start second instance of quicktags in editor
	});
</script>';

		return $html;
	}

	/**
	 * Getting Quicktags settings
	 *
	 * @param $qtInit
	 * @param $editor_id
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public static function quicktags_settings( $qtInit, $editor_id ) {
		self::$qt_settings[ $editor_id ] = $qtInit;

		return $qtInit;
	}

	/**
	 * Getting MCE Editor Settings
	 * @param $mceInit
	 * @param $editor_id
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public static function tiny_mce_before_init( $mceInit, $editor_id ) {
		self::$mce_settings[ $editor_id ] = $mceInit;

		return $mceInit;
	}

	/**
	 * Getting MCE Settings
	 * @param $editor_id
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public static function get_mce_init( $editor_id ) {
		$mceInit = '';
		if ( ! empty( self::$mce_settings[ $editor_id ] ) ) {
			$options = self::_parse_init( self::$mce_settings[ $editor_id ] );
			$mceInit .= "'$editor_id':{$options},";
			$mceInit = '{' . trim( $mceInit, ',' ) . '}';
		} else {
			$mceInit = '{}';
		}

		return $mceInit;
	}

	/**
	 * Get Quicktag editor settings
	 *
	 * @param $editor_id
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public static function get_qt_init( $editor_id ) {
		$qtInit = '';
		if ( ! empty( self::$qt_settings[ $editor_id ] ) ) {
			$options = self::_parse_init( self::$qt_settings[ $editor_id ] );
			$qtInit .= "'$editor_id':{$options},";
			$qtInit = '{' . trim( $qtInit, ',' ) . '}';
		} else {
			$qtInit = '{}';
		}

		return $qtInit;
	}

	/**
	 * Parsing Data
	 *
	 * @param $init
	 *
	 * @return string
	 * @since 1.0.0
	 */
	private static function _parse_init( $init ) {
		$options = '';

		foreach ( $init as $k => $v ) {
			if ( is_bool( $v ) ) {
				$val = $v ? 'true' : 'false';
				$options .= $k . ':' . $val . ',';
				continue;
			} elseif ( ! empty( $v ) && is_string( $v ) && ( ( '{' === $v{0} && '}' === $v{strlen( $v ) - 1} ) || ( '[' === $v{0} && ']' === $v{strlen( $v ) - 1} ) || preg_match( '/^\(?function ?\(/', $v ) ) ) {
				$options .= $k . ':' . $v . ',';
				continue;
			}
			$options .= $k . ':"' . $v . '",';
		}

		return '{' . trim( $options, ' ,' ) . '}';
	}
}

add_action( 'load-post.php', array( 'Torro_AJAX_WP_Editor', 'init_defaults' ) );
