<?php
/**
 * Plugin initialization file
 *
 * @package TorroForms
 * @since 1.0.0
 *
 * @wordpress-plugin
 * Plugin Name: Torro Forms
 * Plugin URI:  https://torro-forms.com
 * Description: Torro Forms is an extendable WordPress form builder with Drag & Drop functionality, chart evaluation and more - with WordPress look and feel.
 * Version:     1.0.16
 * Author:      Awesome UG
 * Author URI:  https://www.awesome.ug
 * License:     GNU General Public License v2 (or later)
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: torro-forms
 * Tags:        forms, form builder, surveys, polls, votes, charts, api
 */

defined( 'ABSPATH' ) || exit;

/**
 * The main function to return the Torro Forms instance.
 *
 * Any extension can use this function to access the main plugin object or to simply check
 * whether the plugin is active and running. Example:
 *
 * `if ( function_exists( 'torro' ) && torro() ) {
 *     // Do custom extension stuff.
 * }
 * `
 *
 * @since 1.0.0
 *
 * @return Torro_Forms|null The Torro Forms instance, or null on failure.
 */
function torro() {
	if ( ! class_exists( 'Torro_Forms' ) ) {
		$main_file        = __FILE__;
		$basedir_relative = '';

		$file          = wp_normalize_path( $main_file );
		$mu_plugin_dir = wp_normalize_path( WPMU_PLUGIN_DIR );
		if ( preg_match( '#^' . preg_quote( $mu_plugin_dir, '#' ) . '/#', $file ) && file_exists( $mu_plugin_dir . '/torro-forms.php' ) ) {
			$basedir_relative = 'torro-forms/';
		}

		if ( ! class_exists( 'Leaves_And_Love_Plugin_Loader' ) ) {
			$locations = array(
				plugin_dir_path( $main_file ) . $basedir_relative . 'vendor/felixarntz/plugin-lib/plugin-loader.php',
				$mu_plugin_dir . '/plugin-lib/plugin-loader.php',
				dirname( ABSPATH ) . '/vendor/felixarntz/plugin-lib/plugin-loader.php',
				dirname( dirname( ABSPATH ) ) . '/vendor/felixarntz/plugin-lib/plugin-loader.php',
			);
			foreach ( $locations as $location ) {
				if ( file_exists( $location ) ) {
					require_once $location;
					break;
				}
			}
		}

		require_once plugin_dir_path( $main_file ) . $basedir_relative . 'src/torro-forms.php';

		Leaves_And_Love_Plugin_Loader::load( 'Torro_Forms', $main_file, $basedir_relative );
	}

	$torro = Leaves_And_Love_Plugin_Loader::get( 'Torro_Forms' );
	if ( is_wp_error( $torro ) ) {
		return null;
	}

	return $torro;
}

/**
 * Executes a callback after Torro Forms has been initialized.
 *
 * This function should be used by all Torro Forms extensions to initialize themselves.
 *
 * This doc block was added in the 1500th commit :)
 *
 * @since 1.0.0
 *
 * @param callable $callback Callback to bootstrap the extension.
 */
function torro_load( $callback ) {
	if ( did_action( 'torro_loaded' ) || doing_action( 'torro_loaded' ) ) {
		call_user_func( $callback, torro() );
		return;
	}

	add_action( 'torro_loaded', $callback, 10, 1 );
}

torro();
