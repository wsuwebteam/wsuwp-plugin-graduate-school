<?php
/**
 * Plugin Name: WSUWP Graduate School Plugin
 * Plugin URI: https://github.com/wsuwebteam/wsuwp-plugin-graduate-school
 * Description: Describe the plugin
 * Version: 1.1.23
 * Requires PHP: 7.3
 * Author: Washington State University, Danial Bleile
 * Author URI: https://web.wsu.edu/
 * Text Domain: wsuwp-plugin-graduate-school
 */



// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WSUWPPLUGINGRADUATEVERSION', '1.1.23' );

add_action( 'after_setup_theme', 'wsuwp_plugin_graduate_init' );

function wsuwp_plugin_graduate_init() {

		// Initiate plugin
		require_once __DIR__ . '/includes/plugin.php';

}


require_once dirname( __FILE__ ) . '/legacy/class-wsuwp-graduate-school-theme.php';
require_once dirname( __FILE__ ) . '/legacy/class-wsuwp-graduate-degree-programs.php';

add_action( 'after_setup_theme', 'WSUWP_Graduate_School_Theme' );
/**
 * Starts the main class controlling the theme.
 *
 * @since 0.5.0
 *
 * @return \WSUWP_Graduate_School_Theme
 */
function WSUWP_Graduate_School_Theme() {
	return WSUWP_Graduate_School_Theme::get_instance();
}

add_action( 'after_setup_theme', 'WSUWP_Graduate_Degree_Programs' );
/**
 * Starts the Graduate School degree programs functionality.
 *
 * @since 0.4.0
 *
 * @return \WSUWP_Graduate_Degree_Programs
 */
function WSUWP_Graduate_Degree_Programs() {
	return WSUWP_Graduate_Degree_Programs::get_instance();
}

/**
 * Retrieve the instance of the graduate degree faculty taxonomy.
 *
 * @since 0.4.0
 *
 * @return WSUWP_Graduate_Degree_Faculty_Taxonomy
 */
function WSUWP_Graduate_Degree_Faculty_Taxonomy() {
	return WSUWP_Graduate_Degree_Faculty_Taxonomy::get_instance();
}

/**
 * Retrieves the instance of the graduate degree program name taxonomy.
 *
 * @since 0.4.0
 *
 * @return WSUWP_Graduate_Degree_Program_Name_Taxonomy
 */
function WSUWP_Graduate_Degree_Program_Name_Taxonomy() {
	return WSUWP_Graduate_Degree_Program_Name_Taxonomy::get_instance();
}

/**
 * Retrieves the instance of the graduate degree degree type taxonomy.
 *
 * @since 0.4.0
 *
 * @return WSUWP_Graduate_Degree_Degree_Type_Taxonomy
 */
function WSUWP_Graduate_Degree_Degree_Type_Taxonomy() {
	return WSUWP_Graduate_Degree_Degree_Type_Taxonomy::get_instance();
}

/**
 * Retrieves the instance of the contact taxonomy.
 *
 * @since 0.4.0
 *
 * @return WSUWP_Graduate_Degree_Contact_Taxonomy
 */
function WSUWP_Graduate_Degree_Contact_Taxonomy() {
	return WSUWP_Graduate_Degree_Contact_Taxonomy::get_instance();
}
