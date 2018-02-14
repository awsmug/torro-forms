<?php
/*
Plugin Name: Plugin Lib Sample Plugin
Plugin URI:  https://leaves-and-love.net
Description: Sample plugin for the Leaves & Love Plugin Library.
Version:     1.0.0
Author:      Felix Arntz
Author URI:  https://leaves-and-love.net
License:     GNU General Public License v3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: sample-plugin
*/

require_once plugin_dir_path( __FILE__ ) . 'vendor/felixarntz/plugin-lib/plugin-loader.php';
require_once plugin_dir_path( __FILE__ ) . 'src/sp-main.php';

Leaves_And_Love_Plugin_Loader::load( 'SP_Main', __FILE__, '' );
