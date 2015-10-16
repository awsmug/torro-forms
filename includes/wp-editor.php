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
	static $mce_init;
	static $qt_init;
	private static $mce_settings = NULL;
	private static $qt_settings = NULL;

	public static function quicktags_settings( $qtInit, $editor_id )
	{
		self::$qt_settings = $qtInit;

		return $qtInit;
	}

	public static function tiny_mce_before_init( $mceInit, $editor_id )
	{
		self::$mce_settings = $mceInit;

		return $mceInit;
	}

	public function init()
	{
		$mce_init = self::get_mce_init( $_POST[ 'id' ] );
		$qt_init = self::get_qt_init( $_POST[ 'id' ] );
	}

	/*
	* Code coppied from _WP_Editors class (modified a little)
	*/

	public static function get_mce_init( $editor_id )
	{
		$mceInit = '';
		if( !empty( self::$mce_settings ) )
		{
			$options = self::_parse_init( self::$mce_settings );
			$mceInit .= "'$editor_id':{$options},";
			$mceInit = '{' . trim( $mceInit, ',' ) . '}';
		}
		else
		{
			$mceInit = '{}';
		}

		return $mceInit;
	}

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

	public static function get_qt_init( $editor_id )
	{
		$qtInit = '';
		if( !empty( self::$qt_settings ) )
		{
			$options = self::_parse_init( self::$qt_settings );
			$qtInit .= "'$editor_id':{$options},";
			$qtInit = '{' . trim( $qtInit, ',' ) . '}';
		}
		else
		{
			$qtInit = '{}';
		}

		return $qtInit;
	}
}