<?php namespace WSUWP\Plugin\Graduate;

class Shortcode {
	
	public static function init() {

		add_shortcode( 'gsdegrees', array( __CLASS__, 'add_gs_shortcodes' ) );

	}

	/**
	 * Sort degree classifications in the specified order.
	 *
	 * @since 1.1.29
	 *
	 * @param array $classifications Array of classification strings.
	 * @return array Sorted array of classifications.
	 */
	protected static function sort_classifications( $classifications ) {
		$order = array(
			'doctorate',
			'masters',
			'professional-masters',
			'masters-4plus1',
			'graduate-certificate',
			'administrator-credentials',
		);

		usort( $classifications, function( $a, $b ) use ( $order ) {
			$pos_a = array_search( $a, $order );
			$pos_b = array_search( $b, $order );

			// If not found in order array, put at end
			if ( false === $pos_a ) {
				$pos_a = 999;
			}
			if ( false === $pos_b ) {
				$pos_b = 999;
			}

			return $pos_a - $pos_b;
		} );

		return $classifications;
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

		$factsheets = self::get_fact_sheets( $atts );

		ob_start();

		include Plugin::get( 'dir' ) . '/templates/az-index.php';

		$html_content = ob_get_clean();

		wp_reset_postdata();

		return $html_content;

	}


	protected static function get_fact_sheets( $atts = array() ) {

		$factsheets = array();
		$all_entries = array(); // Collect all entries first
		
		$gradfair = isset( $atts['gradfair'] ) && $atts['gradfair'];

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

			// Process each degree type and collect all entries
			if ( ! is_wp_error( $degree_types ) && 0 < count( $degree_types ) ) {
				foreach ( $degree_types as $degree_type ) {
					$degree_classification = get_term_meta( $degree_type->term_id, 'gs_degree_type_classification', true );
					
					if ( empty( $degree_classification ) ) {
						$degree_classification = 'other';
					}
					
					$entry = $factsheet_data;
					$entry['degree_type'] = $degree_type->name;
					$entry['program_name'] = $program_name_value;
					$entry['degree_classification'] = $degree_classification;
					$entry['factsheet_key'] = $factsheet_key;
					
					$all_entries[] = $entry;
				}
			} else {
				// Fallback for factsheets with no degree types
				$factsheet_data['degree_type'] = 'Other';
				$factsheet_data['program_name'] = $program_name_value;
				$factsheet_data['degree_classification'] = 'other';
				$factsheet_data['factsheet_key'] = $factsheet_key;
				$all_entries[] = $factsheet_data;
			}

			}

		}

		// Group entries by program name first
		$grouped_by_program = array();
		foreach ( $all_entries as $entry ) {
			$group_key = $entry['factsheet_key'];
			if ( ! isset( $grouped_by_program[ $group_key ] ) ) {
				$grouped_by_program[ $group_key ] = array();
			}
			$grouped_by_program[ $group_key ][] = $entry;
		}
		
		// Now process each program group
		foreach ( $grouped_by_program as $program_key => $program_entries ) {
			$masters_entries = array();
			$non_masters_entries = array();
			
			// Separate masters from non-masters for this program
			foreach ( $program_entries as $entry ) {
				if ( in_array( $entry['degree_classification'], array( 'masters', 'professional-masters', 'masters-4plus1' ) ) ) {
					$masters_entries[] = $entry;
				} else {
					$non_masters_entries[] = $entry;
				}
			}
			
			// Create grouped masters entry if there are any masters degrees
			if ( ! empty( $masters_entries ) ) {
				$grouped_masters = $masters_entries[0]; // Use first as base
				$classifications = array_column( $masters_entries, 'degree_classification' );
				$grouped_masters['degree_classifications'] = self::sort_classifications( $classifications );
				$grouped_masters['degree_types'] = array_column( $masters_entries, 'degree_type' );
				$grouped_masters['degree_classification'] = 'masters';
				$grouped_masters['degree_type'] = 'Masters'; // Always use generic "Masters" for grouped entries
				
				$factsheets[ $program_key ][] = $grouped_masters;
			}
			
			// Add non-masters entries
			foreach ( $non_masters_entries as $entry ) {
				$factsheets[ $program_key ][] = $entry;
			}
			
			// Sort all entries in this factsheet by classification order
			if ( isset( $factsheets[ $program_key ] ) && is_array( $factsheets[ $program_key ] ) ) {
				usort( $factsheets[ $program_key ], function( $a, $b ) {
					$order = array(
						'doctorate',
						'masters',
						'professional-masters',
						'masters-4plus1',
						'graduate-certificate',
						'administrator-credentials',
					);
					
					// For grouped masters, use the first (highest priority) classification from sorted array
					if ( isset( $a['degree_classifications'] ) && is_array( $a['degree_classifications'] ) && ! empty( $a['degree_classifications'] ) ) {
						$class_a = $a['degree_classifications'][0];
					} else {
						$class_a = $a['degree_classification'];
					}
					
					if ( isset( $b['degree_classifications'] ) && is_array( $b['degree_classifications'] ) && ! empty( $b['degree_classifications'] ) ) {
						$class_b = $b['degree_classifications'][0];
					} else {
						$class_b = $b['degree_classification'];
					}
					
					$pos_a = array_search( $class_a, $order );
					$pos_b = array_search( $class_b, $order );
					
					// If not found in order array, put at end
					if ( false === $pos_a ) {
						$pos_a = 999;
					}
					if ( false === $pos_b ) {
						$pos_b = 999;
					}
					
					return $pos_a - $pos_b;
				} );
			}
		}

		ksort( $factsheets );

		return $factsheets;


	}

}

Shortcode::init();
