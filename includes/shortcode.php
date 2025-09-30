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

			// Get program name once (used for all degree types)
			if ( ! is_wp_error( $program_name ) && 0 < count( $program_name ) ) {
				$program_name_value = $program_name[0]->name;
			} else {
				$program_name_value = '';
			}

			// Get factsheet key once (used for all degree types)
			if ( ! empty( $factsheet_data['shortname'] ) ) {
				$factsheet_key = $factsheet_data['shortname'];
			} else {
				$factsheet_key = get_the_title();
			}

			if ( ! isset( $factsheets[ $factsheet_key ] ) ) {
				$factsheets[ $factsheet_key ] = array();
			}

			// Process each degree type separately to create multiple entries
			if ( ! is_wp_error( $degree_types ) && 0 < count( $degree_types ) ) {
				foreach ( $degree_types as $degree_type ) {
					$degree_classification = get_term_meta( $degree_type->term_id, 'gs_degree_type_classification', true );
					
					// Create a copy of factsheet data for this degree type
					$factsheet_entry = $factsheet_data;
					$factsheet_entry['degree_type'] = $degree_type->name;
					$factsheet_entry['program_name'] = $program_name_value;

					if ( empty( $degree_classification ) ) {
						$factsheet_entry['degree_classification'] = 'other';
					} else {
						$factsheet_entry['degree_classification'] = $degree_classification;
					}

					$factsheets[ $factsheet_key ][] = $factsheet_entry;
				}
			} else {
				// Fallback for factsheets with no degree types
				$factsheet_data['degree_type'] = 'Other';
				$factsheet_data['program_name'] = $program_name_value;
				$factsheet_data['degree_classification'] = 'other';
				$factsheets[ $factsheet_key ][] = $factsheet_data;
			}

			}

			ksort( $factsheets );

		}

		return $factsheets;


	}

}

Shortcode::init();
