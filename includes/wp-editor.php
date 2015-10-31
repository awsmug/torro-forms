<?php

/**
 * Created by PhpStorm.
 * User: svenw
 * Date: 26.08.15
 * Time: 00:21
 *
 * http://wordpress.stackexchange.com/questions/70548/load-tinymce-wp-editor-via-ajax
 */
class AF_WPEditorBox
{
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

	/**
	 * Instance
	 *
	 * @var object
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Singleton init
	 *
	 * @return AF_WPEditorBox|object
	 * @since 1.0.0
	 */
	public function init()
	{
		if (null === self::$_instance)
		{
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	/**
	 * Adding Scripts
	 *
	 * @since 1.0.0
	 */
	private function __construct()
	{
		add_filter( 'tiny_mce_before_init', array( __CLASS__, 'tiny_mce_before_init' ), 10, 2 );
		add_filter( 'quicktags_settings', array( __CLASS__, 'quicktags_settings' ), 10, 2 );

		add_action( 'wp_ajax_af_get_editor_html', array( __CLASS__, 'ajax_af_get_editor_html' ) );
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
	public static function editor( $content, $editor_id, $field_name = NULL )
	{
		$settings = array();

		if( NULL != $field_name )
		{
			$settings = array(
				'textarea_name' => $field_name
			);
		}

		add_filter( 'wp_default_editor', array( __CLASS__, 'std_editor_tinymce' ) ); // Dirty hack, but needed to prevent tab issues on editor

		ob_start();
		wp_editor( $content, $editor_id, $settings );
		$html = ob_get_clean();

		remove_filter( 'wp_default_editor', array( __CLASS__, 'std_editor_tinymce' ) ); // Dirty hack, but needed to prevent tab issues on editor

		$html.= self::editor_js( $editor_id );

		return $html;
	}

	/**
	 * Creating Editor JS
	 *
	 * @param $editor_id
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public static function editor_js( $editor_id )
	{
		if( '' == $editor_id )
		{
			return FALSE;
		}

		$mce_init = self::get_mce_init( $editor_id );
		$qt_init = self::get_qt_init( $editor_id );

		// Extending editor gobals
		$html = '<script type="text/javascript">
			jQuery(document).ready(function($) {
				var editor_id = "' . $editor_id . '";

				window.tinyMCEPreInit.mceInit = jQuery.extend( window.tinyMCEPreInit.mceInit, ' . $mce_init . ' );
	            window.tinyMCEPreInit.qtInit = jQuery.extend( window.tinyMCEPreInit.qtInit, ' . $qt_init . ' );

                tinymce.init( window.tinyMCEPreInit.mceInit[ editor_id ] );

	            try {
	                quicktags( tinyMCEPreInit.qtInit[ editor_id ] );
	            }
	            catch(e)
	            {
	                console.log( e );
	            }

	            QTags.instances["0"] = ""; // Dirty Hack, but needed to start second instance of quicktags in editor

	            console.log( window.tinyMCEPreInit.mceInit[ editor_id ]  );
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
	public static function quicktags_settings( $qtInit, $editor_id )
	{
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
	public static function tiny_mce_before_init( $mceInit, $editor_id )
	{
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
	public static function get_mce_init( $editor_id )
	{
		$mceInit = '';
		if( !empty( self::$mce_settings[ $editor_id ] ) )
		{
			$options = self::_parse_init( self::$mce_settings[ $editor_id ] );
			$mceInit .= "'$editor_id':{$options},";
			$mceInit = '{' . trim( $mceInit, ',' ) . '}';
		}
		else
		{
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
	public static function get_qt_init( $editor_id )
	{
		$qtInit = '';
		if( !empty( self::$qt_settings[ $editor_id ] ) )
		{
			$options = self::_parse_init( self::$qt_settings[ $editor_id ] );
			$qtInit .= "'$editor_id':{$options},";
			$qtInit = '{' . trim( $qtInit, ',' ) . '}';
		}
		else
		{
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
	private static function _parse_init( $init )
	{
		$options = '';

		foreach( $init as $k => $v )
		{
			if( is_bool( $v ) )
			{
				$val = $v ? 'true' : 'false';
				$options .= $k . ':' . $val . ',';
				continue;
			}
			elseif( !empty( $v ) && is_string( $v ) && ( ( '{' == $v{0} && '}' == $v{strlen( $v ) - 1} ) || ( '[' == $v{0} && ']' == $v{strlen( $v ) - 1} ) || preg_match( '/^\(?function ?\(/', $v ) ) )
			{
				$options .= $k . ':' . $v . ',';
				continue;
			}
			$options .= $k . ':"' . $v . '",';
		}

		return '{' . trim( $options, ' ,' ) . '}';
	}

	/**
	 * Function to set standard editor to tinymce prevent tab issues on editor
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public static function std_editor_tinymce(){
		return 'tinymce';
	}

	/**
	 * Getting Editor HTML by AJAX
	 */
	public static function ajax_af_get_editor_html()
	{
		$widget_id = $_POST[ 'widget_id' ];
		$editor_id = $_POST[ 'editor_id' ];
		$field_name = $_POST[ 'field_name' ];
		$message = $_POST[ 'message' ];

		$html = self::editor( $message, $editor_id, $field_name );

		$data = array(
			'widget_id' => $widget_id,
			'editor_id' => $editor_id,
			'html'      => $html
		);

		echo json_encode( $data );
		die();
	}
}
AF_WPEditorBox::init();

/**
 * Getting WP Editor
 *
 * @param $content
 * @param $editor_id
 * @param $field_name
 *
 * @return string
 * @since 1.0.0
 */
function af_wp_editor( $content, $editor_id, $field_name = NULL )
{
	return AF_WPEditorBox::editor( $content, $editor_id, $field_name );
}