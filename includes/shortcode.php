<?php namespace WSUWP\Plugin\Graduate;

class Shortcode {
	
	public static function init() {

		add_shortcode( 'gsdegrees', array( __CLASS__, 'add_gs_shortcodes' ) );

	}

	/**
	 * Enqueue frontend scripts and styles when shortcode is used.
	 *
	 * @since 1.1.24
	 */
	public static function enqueue_frontend_scripts() {
		// This will be called by the shortcode when needed
		wp_enqueue_style( 
			'wsuwp-graduate-factsheets', 
			Plugin::get( 'url' ) . '/css/30-factsheets.css', 
			array(), 
			WSUWPPLUGINGRADUATEVERSION 
		);
		
		wp_enqueue_script( 
			'wsuwp-graduate-factsheets-az', 
			Plugin::get( 'url' ) . '/js/factsheet-az.min.js', 
			array(), 
			WSUWPPLUGINGRADUATEVERSION, 
			true 
		);
	}


	public static function add_gs_shortcodes( $atts, $content = '' ) {

		// Enqueue the CSS when shortcode is used
		self::enqueue_frontend_scripts();

		$factsheets = self::get_fact_sheets();

		ob_start();

		include Plugin::get( 'dir' ) . '/templates/az-index.php';

		$html_content = ob_get_clean();

		wp_reset_postdata();

		return $html_content;

	}


	protected static function get_fact_sheets() {

		$factsheets = array();

		$query_args = array(
			'post_type'      => 'gs-factsheet',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		);

		if ( ! empty( $_REQUEST['degree-search'] ) ) {

			$query_args['s'] = sanitize_term_field( $_REQUEST['degree-search'] );
		}


		$the_query = new \WP_Query( $query_args );

		if ( $the_query->have_posts() ) {

			while ( $the_query->have_posts() ) {

				$the_query->the_post();

				$factsheet_data = \WSUWP_Graduate_Degree_Programs::get_factsheet_data( get_the_ID() );

				if ( 'No' === $factsheet_data['public'] ) {
					continue;
				}
		
				$factsheet_data['permalink'] = get_the_permalink();
		
				if ( $gradfair ) {
					$factsheet_data['permalink'] = str_replace( '/degrees/factsheet/', '/wsugradfair/degrees/factsheet/', $factsheet_data['permalink'] );
				}
		
				$degree_types = wp_get_object_terms( get_the_ID(), 'gs-degree-type' );
				$program_name = wp_get_object_terms( get_the_ID(), 'gs-program-name' );
		
				$degree_classification = '';
				$degree_type = 'Other';
				if ( ! is_wp_error( $degree_types ) && 0 < count( $degree_types ) ) {
					$degree_classification = get_term_meta( $degree_types[0]->term_id, 'gs_degree_type_classification', true );
					$degree_type = $degree_types[0]->name;
				}
		
				if ( ! is_wp_error( $program_name ) && 0 < count( $program_name ) ) {
					$factsheet_data['program_name'] = $program_name[0]->name;
				} else {
					$factsheet_data['program_name'] = '';
				}
		
				$factsheet_data['degree_type'] = $degree_type;
		
				if ( empty( $degree_classification ) ) {
					$factsheet_data['degree_classification'] = 'other';
				} else {
					$factsheet_data['degree_classification'] = $degree_classification;
				}
		
				if ( ! empty( $factsheet_data['shortname'] ) ) {
					$factsheet_key = $factsheet_data['shortname'];
				} else {
					$factsheet_key = get_the_title();
				}
		
				if ( ! isset( $factsheets[ $factsheet_key ] ) ) {
					$factsheets[ $factsheet_key ] = array();
				}
		
				$factsheets[ $factsheet_key ][] = $factsheet_data;

			}

			ksort( $factsheets );

		}

		return $factsheets;


	}

}

Shortcode::init();
