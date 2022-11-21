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

		//self::setup_classes();

		// Do plugin stuff here

		//require_once __DIR__ . '/scripts.php';
		//require_once __DIR__ . '/blocks.php';
		//require_once __DIR__ . '/block-categories.php';
		//require_once __DIR__ . '/rest-api.php';
		//require_once __DIR__ . '/make-to-gutenberg.php';
		///require_once __DIR__ . '/query.php';

		//require_once __DIR__ . '/disable-drop-cap.php'; // 5.6 method
	}


	public static function load_class( $slug ) {

		require_once self::get( 'dir' ) . "classes/class-{$slug}.php";

	}

	


}

Plugin::init();