<?php namespace WSUWP\Plugin\Graduate;

class Shortcode {

	/**
	 * Initialize the shortcode.
	 * Scripts and styles are enqueued when the shortcode is used.
	 *
	 * @since 1.2.2
	 */
	public static function init() {

		add_shortcode( 'gsdegrees', array( __CLASS__, 'add_gs_shortcodes' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue_assets' ) );

	}

	/**
	 * Sort degree classifications in the specified order.
	 *
	 * @since 1.2.2
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
			'global-campus',
			'administrator-credentials',
		);

		usort( $classifications, function( $a, $b ) use ( $order ) {
			$a_rank = ( $i = array_search( $a, $order, true ) ) === false ? 999 : $i;
			$b_rank = ( $i = array_search( $b, $order, true ) ) === false ? 999 : $i;

			return $a_rank - $b_rank;
		} );

		return $classifications;
	}

	/**
	 * Transform factsheets array to programs array for landing page
	 * Matches program-builder.ts: 1) De-duplicate by title|url 2) Group by program_name → shortname 3) Sort alphabetically, then by classification rank
	 */
	protected static function transform_factsheets_to_programs( $factsheets ) {
		$factsheets_by_key = array();
		foreach ( $factsheets as $factsheet_name => $entries ) {
			foreach ( $entries as $entry ) {
				$title = ! empty( $entry['title'] ) ? $entry['title'] : $factsheet_name;
				$url = $entry['permalink'];
				$shortname = ! empty( $entry['shortname'] ) ? $entry['shortname'] : $title;
				$program_name = ! empty( $entry['program_name'] ) ? $entry['program_name'] : $shortname;
				$key = $title . '|' . $url;
				if ( ! isset( $factsheets_by_key[ $key ] ) ) {
					$factsheets_by_key[ $key ] = array( 'title' => $title, 'url' => $url, 'shortname' => $shortname, 'program_name' => $program_name, 'classifications' => array() );
				}
				if ( isset( $entry['degree_classifications'] ) && is_array( $entry['degree_classifications'] ) ) {
					foreach ( $entry['degree_classifications'] as $class ) {
						if ( ! in_array( $class, $factsheets_by_key[ $key ]['classifications'], true ) ) {
							$factsheets_by_key[ $key ]['classifications'][] = $class;
						}
					}
				} elseif ( ! empty( $entry['degree_classification'] ) ) {
					if ( ! in_array( $entry['degree_classification'], $factsheets_by_key[ $key ]['classifications'], true ) ) {
						$factsheets_by_key[ $key ]['classifications'][] = $entry['degree_classification'];
					}
				}
			}
		}
		$by_program_and_shortname = array();
		foreach ( $factsheets_by_key as $entry ) {
			$prog = $entry['program_name'];
			$short = $entry['shortname'];
			if ( ! isset( $by_program_and_shortname[ $prog ] ) ) $by_program_and_shortname[ $prog ] = array();
			if ( ! isset( $by_program_and_shortname[ $prog ][ $short ] ) ) $by_program_and_shortname[ $prog ][ $short ] = array();
			$by_program_and_shortname[ $prog ][ $short ][] = array( 'title' => $entry['title'], 'url' => $entry['url'], 'shortname' => $entry['shortname'], 'classifications' => $entry['classifications'] );
		}
		$programs = array();
		$sorted_programs = array_keys( $by_program_and_shortname );
		sort( $sorted_programs );
		foreach ( $sorted_programs as $program_name ) {
			$shortname_groups = $by_program_and_shortname[ $program_name ];
			$sorted_shortnames = array_keys( $shortname_groups );
			sort( $sorted_shortnames );
			foreach ( $sorted_shortnames as $shortname ) {
				$entries = $shortname_groups[ $shortname ];
				usort( $entries, function( $a, $b ) {
					$a_rank = self::get_classification_rank( $a['classifications'] );
					$b_rank = self::get_classification_rank( $b['classifications'] );
					return $a_rank !== $b_rank ? $a_rank - $b_rank : strcasecmp( $a['title'], $b['title'] );
				} );
				$all_classifications = array();
				foreach ( $entries as $e ) {
					foreach ( $e['classifications'] as $class ) {
						if ( ! in_array( $class, $all_classifications, true ) ) $all_classifications[] = $class;
					}
				}
				$sorted_classifications = self::sort_classifications( $all_classifications );
				$primary_classification = ! empty( $sorted_classifications[0] ) ? $sorted_classifications[0] : 'other';
				$first_char = strtoupper( substr( $program_name, 0, 1 ) );
				if ( ! preg_match( '/^[A-Z]$/', $first_char ) ) $first_char = 'A';
				$programs[] = array( 'name' => $program_name, 'shortname' => $shortname, 'entries' => $entries, 'classification' => $primary_classification, 'classifications' => $sorted_classifications, 'letter' => $first_char );
			}
		}
		return $programs;
	}

	protected static function get_classification_rank( $classifications ) {
		$order = array( 'doctorate', 'masters', 'professional-masters', 'masters-4plus1', 'graduate-certificate', 'global-campus', 'administrator-credentials' );
		if ( empty( $classifications ) ) return count( $order );
		$ranks = array();
		foreach ( $classifications as $class ) {
			$index = array_search( $class, $order, true );
			if ( false !== $index ) $ranks[] = $index;
		}
		return ! empty( $ranks ) ? min( $ranks ) : count( $order );
	}

	/**
	 * Enqueue frontend scripts and styles when shortcode is used.
	 *
	 * @since 1.2.2
	 */
	public static function enqueue_frontend_scripts() {
		if ( wp_script_is( 'wsuwp-graduate-factsheets-landing', 'enqueued' ) ) {
			return;
		}
		// This will be called by the shortcode when needed
		wp_enqueue_style( 
			'wsuwp-graduate-factsheets-landing', 
			Plugin::get( 'url' ) . '/css/factsheet-landing.css', 
			array(), 
			WSUWPPLUGINGRADUATEVERSION 
		);
		
		wp_enqueue_script( 
			'wsuwp-graduate-factsheets-landing', 
			Plugin::get( 'url' ) . '/js/factsheet-landing.js', 
			array(), 
			WSUWPPLUGINGRADUATEVERSION, 
			true 
		);
	}


	public static function add_gs_shortcodes( $atts, $content = '' ) {
		/**
		 * Add the gsdegrees shortcode to the page.
		 *
		 * @param array $atts The attributes of the shortcode.
		 * @param string $content The content of the shortcode.
		 * @return string The HTML content of the shortcode.
		 * 
		 * @version 1.2.2
		 */

		// Enqueue the CSS when shortcode is used
		self::enqueue_frontend_scripts();

		$factsheets = self::get_fact_sheets( $atts );
		$programs_data  = self::transform_factsheets_to_programs( $factsheets );
		$config_data    = self::get_landing_config();
		$programs_json  = wp_json_encode( $programs_data );
		$json_size      = strlen( $programs_json );

		if ( $json_size > 100000 ) {
			error_log( sprintf( 'WSUWP Graduate Plugin: Large JSON payload (%d bytes, %d programs)', $json_size, count( $programs_data ) ) );
		}
		if ( $json_size > 1000000 ) {
			error_log( 'WSUWP Graduate Plugin: JSON payload exceeds 1MB, aborting render' );
			return '<!-- Error: Too many programs to display. Please contact site administrator. -->';
		}
	
		$template_path = Plugin::get( 'dir' ) . '/templates/az-landing.php';
		if ( ! file_exists( $template_path ) ) {
			error_log( 'WSUWP Graduate Plugin: Landing template not found at ' . $template_path );
			return '<!-- Error: Landing page template not found. Please check plugin files. -->';
		}

		ob_start();

		include $template_path;

		$html_content = ob_get_clean();

		wp_reset_postdata();

		return $html_content;

	}

	/**
	 * Maybe enqueue frontend scripts when the shortcode is used.
	 * Ensure css/js  are added when page containing [gs-degrees] shortcode is loaded not only when shortcode runs
	 *
	 * @since 1.2.2
	 */
	public static function maybe_enqueue_assets() {
		global $post;

		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'gsdegrees' ) ) {
			self::enqueue_frontend_scripts();
		}
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
					error_log( print_r( $degree_classification, true ) . ' ' . print_r( $degree_type, true ) );
					if ( empty( $degree_classification) || "other" === $degree_classification )  {
						$degree_classification = $degree_type->slug;
					}
					if ( '41-masters-entry' === $degree_classification ) {
						$degree_classification = 'masters-4plus1';
					}
					
					$entry = $factsheet_data;
					$entry['id'] = get_the_ID();
					$entry['title'] = get_the_title();
					$entry['degree_type'] = $degree_type->name;		
					$entry['program_name'] = $program_name_value;
					$entry['degree_classification'] = $degree_classification;
					$entry['factsheet_key'] = $factsheet_key;
					
					$all_entries[] = $entry;
				}
			} else {
				// Fallback for factsheets with no degree types
				$factsheet_data['id'] = get_the_ID();
				$factsheet_data['title'] = get_the_title();
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
			// Collect all masters classifications for this program (for badges)
			$all_masters_classifications = array();
			foreach ( $program_entries as $entry ) {
				if ( in_array( $entry['degree_classification'], array( 'masters', 'professional-masters', 'masters-4plus1' ) ) ) {
					$all_masters_classifications[] = $entry['degree_classification'];
				}
			}
			$all_masters_classifications = array_unique( $all_masters_classifications );
			$sorted_masters_classifications = self::sort_classifications( $all_masters_classifications );

			// Add ALL entries individually (no merging)
			foreach ( $program_entries as $entry ) {
				// For masters entries, attach all masters classifications so badges show correctly
				if ( in_array( $entry['degree_classification'], array( 'masters', 'professional-masters', 'masters-4plus1' ) ) ) {
					$entry['degree_classifications'] = $sorted_masters_classifications;
				}
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

	/**
	 * Get the landing config.
	 *
	 * @since 1.2.2
	 * @return array The landing config.
	 */
	protected static function get_landing_config() {

		return array(
			'badgeMap' => array(
				'doctorate' => array( 'text' => 'D', 'class' => 'doctorate', 'label' => 'Doctorate' ),
				'masters' => array( 'text' => 'M', 'class' => 'masters', 'label' => 'Masters' ),
				'professional-masters' => array( 'text' => 'PM', 'class' => 'professional-masters', 'label' => 'Professional Masters' ),
				'masters-4plus1' => array( 'text' => '4+1', 'class' => 'masters-entry', 'label' => '4+1 Entry' ),
				'graduate-certificate' => array( 'text' => 'GC', 'class' => 'graduate-cert', 'label' => 'Graduate Certificate' ),
				'global-campus' => array( 'text' => 'G', 'class' => 'global-campus', 'label' => 'Global Campus' ),
				'administrator-credentials' => array( 'text' => 'C', 'class' => 'credential', 'label' => 'Administrator Credentials' ),
			),
			'classificationOrder' => array( 'doctorate', 'masters', 'professional-masters', 'masters-4plus1', 'graduate-certificate', 'global-campus', 'administrator-credentials' ),
			'filterDefinitions' => array(
				array( 'type' => 'all', 'label' => 'All Programs' ),
				array( 'type' => 'doctorate', 'label' => 'Doctorate', 'badge' => 'D', 'badgeClass' => 'doctorate' ),
				array( 'type' => 'masters', 'label' => 'Masters', 'badge' => 'M', 'badgeClass' => 'masters' ),
				array( 'type' => 'professional-masters', 'label' => 'Professional Masters', 'badge' => 'PM', 'badgeClass' => 'professional-masters' ),
				array( 'type' => 'graduate-certificate', 'label' => 'Graduate Certificate', 'badge' => 'GC', 'badgeClass' => 'graduate-cert' ),
				array( 'type' => 'global-campus', 'label' => 'Global Campus', 'badge' => 'G', 'badgeClass' => 'global-campus' ),
				array( 'type' => 'administrator-credentials', 'label' => 'Credentials', 'badge' => 'C', 'badgeClass' => 'credential' ),
				array( 'type' => 'masters-4plus1', 'label' => '4+1 Entry', 'badge' => '4+1', 'badgeClass' => 'masters-entry' ),
			),
			
		);
	}
}

Shortcode::init();
