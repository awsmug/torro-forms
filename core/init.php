<?php
/**
 * Core Component
 *
 * This class initializes the core component.
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 awesome.ug (support@awesome.ug)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Torro_Core {
	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		self::includes();

		add_action( 'init', array( __CLASS__, 'custom_post_types' ), 11 );
		add_filter( 'body_class', array( __CLASS__, 'add_body_class' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_admin_styles' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_plugin_styles' ) );
	}

	/**
	 * Including files of component
	 */
	public static function includes() {
		$core_folder = TORRO_FOLDER . 'core/';

		// Base classes
		include( $core_folder . 'torro.php' );
		include( $core_folder . 'class-post.php' );
		include( $core_folder . 'class-form.php' );

		// Abstract
		include( $core_folder . 'abstract/class-component.php' );
		include( $core_folder . 'abstract/class-element.php' );
		include( $core_folder . 'abstract/class-settings.php' );
		include( $core_folder . 'abstract/class-templatetags.php' );

		// Admin
		include( $core_folder . 'menu.php' );
		include( $core_folder . 'form-builder.php' );
		include( $core_folder . 'settings-page.php' );

		// Settings
		include( $core_folder . 'settings/class-settingshandler.php' );
		include( $core_folder . 'settings/base-settings/general.php' );

		// Form functions
		include( $core_folder . 'form-loader.php' );
		include( $core_folder . 'form-process.php' );

		// Base elements
		include( $core_folder . 'elements/base-elements/textfield.php' );
		include( $core_folder . 'elements/base-elements/textarea.php' );
		include( $core_folder . 'elements/base-elements/onechoice.php' );
		include( $core_folder . 'elements/base-elements/multiplechoice.php' );
		include( $core_folder . 'elements/base-elements/dropdown.php' );
		include( $core_folder . 'elements/base-elements/separator.php' );
		include( $core_folder . 'elements/base-elements/splitter.php' );
		include( $core_folder . 'elements/base-elements/text.php' );

		// Template tags

		include( $core_folder . 'templatetags/base-templatetags/global.php' );
		include( $core_folder . 'templatetags/base-templatetags/form.php' );

		// Shortcodes
		include( $core_folder . 'shortcodes.php' );
	}

	/**
	 * Creates Custom Post Types for Torro Forms
	 *
	 * @since 1.0.0
	 */
	public static function custom_post_types() {

		$settings = torro_get_settings( 'general' );

		$slug = 'forms';
		if ( array_key_exists( 'slug', $settings  ) ) {
			$slug = $settings[ 'slug' ];
		}

		/**
		 * Categories
		 */
		$args_taxonomy = array(
			'show_in_nav_menus'	=> true,
			'hierarchical'		=> true,
			'labels'			=> array(
				'name'				=> _x( 'Categories', 'taxonomy general name', 'torro-forms' ),
				'singular_name'		=> _x( 'Category', 'taxonomy singular name', 'torro-forms' ),
				'search_items'		=> __( 'Search Categories', 'torro-forms' ),
				'all_items'			=> __( 'All Categories', 'torro-forms' ),
				'parent_item'		=> __( 'Parent Category', 'torro-forms' ),
				'parent_item_colon'	=> __( 'Parent Category:', 'torro-forms' ),
				'edit_item'			=> __( 'Edit Category', 'torro-forms' ),
				'update_item'		=> __( 'Update Category', 'torro-forms' ),
				'add_new_item'		=> __( 'Add New Category', 'torro-forms' ),
				'new_item_name'		=> __( 'New Category', 'torro-forms' ),
				'menu_name'			=> __( 'Categories', 'torro-forms' ),
			),
			'show_ui'			=> true,
			'query_var'			=> true,
			'rewrite'			=> true,
		);

		register_taxonomy( 'torro-forms-categories', array( 'torro-forms' ), $args_taxonomy );

		/**
		 * Post Types
		 */
		$args_post_type = array(
			'labels'            => array(
				'name'               => __( 'Forms', 'torro-forms' ),
				'singular_name'      => __( 'Form', 'torro-forms' ),
				'all_items'          => __( 'All Forms', 'torro-forms' ),
				'add_new_item'       => __( 'Add new Form', 'torro-forms' ),
				'edit_item'          => __( 'Edit Form', 'torro-forms' ),
				'new_item'           => __( 'Add new Form', 'torro-forms' ),
				'view_item'          => __( 'View Form', 'torro-forms' ),
				'search_items'       => __( 'Search Forms', 'torro-forms' ),
				'not_found'          => __( 'No Form found', 'torro-forms' ),
				'not_found_in_trash' => __( 'No Form found in trash', 'torro-forms' )
			),
			'public'            => true,
			'has_archive'       => true,
			'supports'          => array( 'title' ),
			'show_in_menu'      => 'Torro_Admin',
			'show_in_nav_menus' => false,
			'rewrite'           => array( 'slug' => $slug, 'with_front' => true )
		);

		register_post_type( 'torro-forms', $args_post_type );
	}

	/**
	 * Adding CSS Classes to body
	 *
	 * @param array $classes Classes for body
	 *
	 * @return array $classes Classes for body
	 */
	public static function add_body_class( $classes ) {
		global $post;

		// Check if we are on the right place
		if ( ! is_a( $post, 'WP_Post' ) || 'torro-forms' !== $post->post_type ) {
			return $classes;
		}

		$classes[] = 'torro-form';
		$classes[] = 'torro-form-' . $post->ID;

		return $classes;
	}

	/**
	 * Registers and enqueues admin-specific styles.
	 *
	 * @since 1.0.0
	 */
	public static function register_admin_styles() {
		wp_enqueue_style( 'torro-icons', TORRO_URLPATH . 'assets/css/icons.css' );
	}

	/**
	 * Registers and enqueues plugin-specific styles.
	 *
	 * @since 1.0.0
	 */
	public static function register_plugin_styles() {
		wp_enqueue_style( 'torro-frontend', TORRO_URLPATH . 'assets/css/frontend.css' );
	}

}

Torro_Core::init();
