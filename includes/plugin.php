<?php namespace WSUWP\Plugin\Graduate;

class Plugin {


	public static function get( $property ) {

		switch ( $property ) {

			case 'version':
				return WSUWPPLUGINGRADUATEVERSION;

			case 'dir':
				return plugin_dir_path( dirname( __FILE__ ) );

			case 'url':
				return plugin_dir_url( dirname( __FILE__ ) );

			default:
				return '';

		}

	}



	public static function init() {

		require_once __DIR__ . '/shortcode.php';

		require_once __DIR__ . '/single.php';

	}


	public static function load_class( $slug ) {

		require_once self::get( 'dir' ) . "classes/class-{$slug}.php";

	}

	


}

Plugin::init();