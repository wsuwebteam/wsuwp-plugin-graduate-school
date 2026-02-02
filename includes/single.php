<?php namespace WSUWP\Plugin\Graduate;

class Single{
	
	public static function init() {

		add_filter( 'the_content', array( __CLASS__, 'display_fact_sheet' ), 9 );

	}


	public static function display_fact_sheet( $content ) {

        remove_filter( 'the_content', array( __CLASS__, 'display_fact_sheet' ), 9 );

        if ( is_main_query() && is_singular( 'gs-factsheet' ) ) {

            $factsheet_data = \WSUWP_Graduate_Degree_Programs::get_factsheet_data( get_the_id() );

            ob_start();

            include Plugin::get( 'dir' ) . '/templates/factsheet.php';

            $content = ob_get_clean();

        }
		
		return $content;


	}

}

Single::init();
