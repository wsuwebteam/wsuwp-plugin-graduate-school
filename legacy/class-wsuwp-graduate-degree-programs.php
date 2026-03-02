<?php

class WSUWP_Graduate_Degree_Programs {
	/**
	 * @var WSUWP_Factsheet_Redirects
	 */
	private $factsheet_redirects;
	/**
	 * @var WSUWP_Factsheet_Archive
	 */
	private $factsheet_archive;
	/**
	 * @since 0.4.0
	 *
	 * @var WSUWP_Graduate_Degree_Programs
	 */
	private static $instance;

	/**
	 * The slug used to register the factsheet post type.
	 *
	 * @since 0.4.0
	 *
	 * @var string
	 */
	public $post_type_slug = 'gs-factsheet';

	/**
	 * The slug used in pretty URLs.
	 *
	 * @since 0.10.0
	 *
	 * @var string
	 */
	public $archive_slug = 'degrees';

	/**
	 * A list of post meta keys associated with factsheets.
	 *
	 * @since 0.4.0
	 *
	 * @var array
	 */
	public $post_meta_keys = array(
		'gsdp_degree_shortname' => array(
			'description' => 'Factsheet display name',
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_field_callback' => array( __CLASS__, 'display_string_meta_field' ),
			'restricted' => true,
			'pre_html' => '<div class="factsheet-group">',
			'location' => 'primary',
		),
		// 'gsdp_degree_id' => array(
		// 	'description' => 'Factsheet degree ID',
		// 	'type' => 'int',
		// 	'sanitize_callback' => 'absint',
		// 	'meta_field_callback' => array( __CLASS__, 'display_int_meta_field' ),
		// 	'restricted' => true,
		// 	'location' => 'primary',
		// ),
		'gsdp_accepting_applications' => array(
			'description' => 'Accepting applications',
			'type' => 'bool',
			'sanitize_callback' => 'absint',
			'meta_field_callback' => array( __CLASS__, 'display_bool_meta_field' ),
			'location' => 'primary',
		),
		'gsdp_include_in_programs' => array(
			'description' => 'Include in programs list',
			'type' => 'bool',
			'sanitize_callback' => 'absint',
			'meta_field_callback' => array( __CLASS__, 'display_bool_meta_field' ),
			'restricted' => true,
			'location' => 'primary',
		),
		'gsdp_grad_students_total' => array(
			'description' => 'Total grad students',
			'type' => 'int',
			'sanitize_callback' => 'absint',
			'meta_field_callback' => array( __CLASS__, 'display_int_meta_field' ),
			'location' => 'primary',
		),
		'gsdp_grad_faculty_total' => array(
			'description' => 'Total Graduate Faculty',
			'type' => 'int',
			'sanitize_callback' => 'absint',
			'meta_field_callback' => array( __CLASS__, 'display_int_meta_field' ),
			'location' => 'primary',
		),

		'gsdp_grad_core_faculty_total' => array(
			'description' => 'Total Core Graduate Faculty',
			'type' => 'int',
			'sanitize_callback' => 'absint',
			'meta_field_callback' => array( __CLASS__, 'display_int_meta_field' ),
			'location' => 'primary',
		),
		'gsdp_grad_students_aided' => array(
			'description' => 'Aided grad students',
			'type' => 'int',
			'sanitize_callback' => 'absint',
			'meta_field_callback' => array( __CLASS__, 'display_int_meta_field' ),
			'location' => 'primary',
		),
		'gsdp_program_handbook_url' => array(
			'description' => 'Program Handbook URL',
			'type' => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'meta_field_callback' => array( __CLASS__, 'display_string_meta_field' ),
			'location' => 'primary',
		),
		'gsdp_student_learning_outcome_url' => array(
			'description' => 'Student Learning Outcomes URL',
			'type' => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'meta_field_callback' => array( __CLASS__, 'display_string_meta_field' ),
			'location' => 'primary',
		),
		'gsdp_application_url' => array(
			'description' => 'Application URL',
			'type' => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'meta_field_callback' => array( __CLASS__, 'display_string_meta_field' ),
			'location' => 'primary',
		),
		'gsdp_degree_url' => array(
			'description' => 'Degree home page',
			'type' => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'meta_field_callback' => array( __CLASS__, 'display_string_meta_field' ),
			'post_html' => '</div>',
			'location' => 'primary',
		),
		'gsdp_locations' => array(
			'description' => 'Locations',
			'type' => 'locations',
			'sanitize_callback' => 'WSUWP_Graduate_Degree_Programs::sanitize_locations',
			'meta_field_callback' => array( __CLASS__, 'display_locations_meta_field' ),
			'restricted' => true,
			'pre_html' => '<div class="factsheet-group">',
			
			'location' => 'primary',
		),
		'gsdp_global_URL' => array(
			'description' => 'Global Campus URL (if different from physical campus)',
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_field_callback' => array( __CLASS__, 'display_string_meta_field' ),
			'restricted' => true,
			'location' => 'primary',
			'post_html' => '</div>',
		),
		'gsdp_deadlines' => array(
			'description' => 'Deadlines',
			'type' => 'deadlines',
			'sanitize_callback' => 'WSUWP_Graduate_Degree_Programs::sanitize_deadlines',
			'meta_field_callback' => array( __CLASS__, 'display_deadlines_meta_field' ),
			'pre_html' => '<div class="factsheet-group">',
			'post_html' => '</div>',
			'location' => 'primary',
		),
		'gsdp_deadlines_prog' => array(
			'description' => 'Program Deadlines',
			'type' => 'deadlines_prog',
			'sanitize_callback' => 'WSUWP_Graduate_Degree_Programs::sanitize_deadlines_prog',
			'meta_field_callback' => array( __CLASS__, 'display_deadlines_prog_meta_field' ),
			'pre_html' => '<div class="factsheet-group">',
			'post_html' => '</div>',
			'location' => 'primary',
		),
		'gsdp_requirements' => array(
			'description' => 'Language Test Requirements',
			'type' => 'requirements',
			'sanitize_callback' => 'WSUWP_Graduate_Degree_Programs::sanitize_requirements',
			'meta_field_callback' => array( __CLASS__, 'display_requirements_meta_field' ),
			'pre_html' => '<div class="factsheet-group">',
			'post_html' => '</div>',
			'location' => 'primary',
		),
		'gsdp_requirements_gre' => array(
			'description' => 'GRE Requirements',
			'type' => 'requirements-gre',
			'sanitize_callback' => 'WSUWP_Graduate_Degree_Programs::sanitize_requirements_gre',
			'meta_field_callback' => array( __CLASS__, 'display_requirements_gre_meta_field' ),
			'pre_html' => '<div class="factsheet-group">',
			'post_html' => '</div>',
			'location' => 'primary',
		),
		'gsdp_contacts' => array(
			'description' => 'Contacts',
			'type' => 'gscontacts',
			'sanitize_callback' => 'WSUWP_Graduate_Degree_Programs::sanitize_contacts',
			'meta_field_callback' => array( __CLASS__, 'display_contacts_meta_field' ),
			'pre_html' => '<div class="factsheet-group">',
			'post_html' => '</div>',
			'location' => 'primary',
		),
		'gsdp_degree_description' => array(
			'description' => 'Description of the graduate degree',
			'type' => 'textarea',
			'sanitize_callback' => 'wp_kses_post',
			'meta_field_callback' => array( __CLASS__, 'display_textarea_meta_field' ),
			'location' => 'secondary',
		),
		'gsdp_admission_requirements' => array(
			'description' => 'Admission requirements',
			'type' => 'textarea',
			'sanitize_callback' => 'wp_kses_post',
			'meta_field_callback' => array( __CLASS__, 'display_textarea_meta_field' ),
			'location' => 'secondary',
		),
		'gsdp_student_opportunities' => array(
			'description' => 'Student opportunities',
			'type' => 'textarea',
			'sanitize_callback' => 'wp_kses_post',
			'meta_field_callback' => array( __CLASS__, 'display_textarea_meta_field' ),
			'location' => 'secondary',
		),
		'gsdp_career_opportunities' => array(
			'description' => 'Career opportunities',
			'type' => 'textarea',
			'sanitize_callback' => 'wp_kses_post',
			'meta_field_callback' => array( __CLASS__, 'display_textarea_meta_field' ),
			'location' => 'secondary',
		),
		'gsdp_career_placements' => array(
			'description' => 'Career placements',
			'type' => 'textarea',
			'sanitize_callback' => 'wp_kses_post',
			'meta_field_callback' => array( __CLASS__, 'display_textarea_meta_field' ),
			'location' => 'secondary',
		),

	);

	/**
	 * Maintain and return the one instance. Initiate hooks when
	 * called the first time.
	 *
	 * @since 0.4.0
	 *
	 * @return \WSUWP_Graduate_Degree_Programs
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSUWP_Graduate_Degree_Programs();
			self::$instance->setup_hooks();
		}
		return self::$instance;
	}

	/**
	 * Setup hooks to include.
	 *
	 * @since 0.4.0
	 */
	public function setup_hooks() {
		require_once dirname( __FILE__ ) . '/class-graduate-degree-program-name-taxonomy.php';
		require_once dirname( __FILE__ ) . '/class-graduate-degree-degree-type-taxonomy.php';
		require_once dirname( __FILE__ ) . '/class-factsheet-redirects.php';
		require_once dirname( __FILE__ ) . '/class-factsheet-archive.php';
		require_once dirname( __FILE__ ) . '/class-factsheet-data.php';
		require_once dirname( __FILE__ ) . '/class-factsheet-access.php';
		$this->factsheet_redirects = new WSUWP_Factsheet_Redirects( $this->post_type_slug, $this->archive_slug );
		$this->factsheet_archive = new WSUWP_Factsheet_Archive( $this->post_type_slug );


		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		add_action( 'init', array( $this, 'register_post_type' ), 15 );
		add_action( 'init', 'WSUWP_Graduate_Degree_Program_Name_Taxonomy', 15 );
		add_action( 'init', 'WSUWP_Graduate_Degree_Degree_Type_Taxonomy', 15 );

		add_filter( 'query_vars', array( $this->factsheet_archive, 'add_gradfair_query_var' ) );
		add_action( 'init', array( $this->factsheet_archive, 'register_mirror_menu' ) );

		add_action( 'init', array( $this, 'register_meta' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'add_meta_boxes', array( $this, 'remove_meta_boxes' ), 99 );
		add_action( "save_post_{$this->post_type_slug}", array( $this, 'save_factsheet' ), 10, 2 );

		// Capability mapping for team-based access is now handled by
		// \WSUWP\Plugin\Graduate\Factsheet_Team::map_meta_cap() at priority 150.

		// Several fields are restricted to full editors or admins.
		// Updated to use new filter format (since WP 4.9.8): auth_post_meta_{meta_key}_for_{post_type}
		// add_filter( "auth_post_meta_gsdp_degree_id_for_{$this->post_type_slug}", array( $this, 'can_edit_restricted_field' ), 100, 4 );
		add_filter( "auth_post_meta_gsdp_degree_shortname_for_{$this->post_type_slug}", array( 'WSUWP_Factsheet_Access', 'can_edit_restricted_field' ), 100, 4 );
		add_filter( "auth_post_meta_gsdp_student_learning_outcome_for_{$this->post_type_slug}", array( 'WSUWP_Factsheet_Access', 'can_edit_restricted_field' ), 100, 4 );
		add_filter( "auth_post_meta_gsdp_include_in_programs_for_{$this->post_type_slug}", array( 'WSUWP_Factsheet_Access', 'can_edit_restricted_field' ), 100, 4 );
		add_filter( 'wp_insert_post_data', array( $this, 'manage_factsheet_title_update' ), 10, 2 );

		add_action( 'pre_get_posts', array( $this->factsheet_archive, 'adjust_factsheet_archive_query' ) );
		add_action( 'template_redirect', array( $this->factsheet_redirects, 'redirect_old_factsheet_urls' ) );
		add_action( 'template_redirect', array( $this->factsheet_redirects, 'redirect_private_factsheets' ) );

		add_filter( 'spine_get_title', array( $this->factsheet_archive, 'filter_factsheet_archive_title' ), 10, 3 );
	}

	/**
	 * Enqueue scripts and styles used in the admin.
	 *
	 * @since 0.4.0
	 * @since 1.4.0 Added inline styles to disable title/permalink for restricted contributors.
	 *
	 * @param string $hook_suffix
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {
		if ( in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) && 'gs-factsheet' === get_current_screen()->id ) {
			wp_deregister_script( 'yoast-seo-post-scraper' );
			wp_deregister_script( 'yoast-seo-term-scraper' );
			wp_deregister_script( 'yoast-seo-featured-image' );

			wp_enqueue_style( 'gsdp-admin', WSUWP\Plugin\Graduate\Plugin::get('url'). '/css/factsheet-admin.css', array(), WSUWP_Graduate_School_Theme()->theme_version() );
			wp_register_script( 'gsdp-factsheet-admin', WSUWP\Plugin\Graduate\Plugin::get('url'). '/js/factsheet-admin.min.js', array( 'jquery', 'underscore', 'jquery-ui-autocomplete' ), WSUWP_Graduate_School_Theme()->theme_version(), true );

			wp_enqueue_script( 'gsdp-factsheet-admin' );

			// Disable title and permalink for restricted contributors (only on published posts, not new/draft ones)
			$user_id = get_current_user_id();
			$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$post_status = get_post_status( $post_id );
			$is_new_or_draft = empty( $post_id ) || in_array( $post_status, array( 'auto-draft', 'draft' ), true );

			// Only apply restrictions when editing published factsheets, not when creating new ones or editing drafts
			if ( ! $is_new_or_draft && WSUWP_Factsheet_Access::user_is_restricted_contributor( $user_id, $post_id ) ) {
				// Add inline CSS to visually disable restricted fields
				$custom_css = '
					/* Disable title field */
					#titlewrap input#title {
						pointer-events: none;
						background-color: #f0f0f0;
						color: #666;
						cursor: not-allowed;
					}
					/* Disable permalink edit */
					#edit-slug-box,
					#edit-slug-buttons,
					.edit-slug {
						pointer-events: none;
						opacity: 0.5;
					}
					#edit-slug-buttons {
						display: none;
					}
					/* Disable Program Names panel (greyed out) */
					#gs-program-namediv {
						pointer-events: none;
						opacity: 0.5;
					}
					#gs-program-namediv .inside {
						background-color: #f0f0f0;
					}
					#gs-program-namediv input,
					#gs-program-namediv select,
					#gs-program-namediv button {
						cursor: not-allowed;
					}
					/* Disable Degree Types panel (greyed out) */
					#tagsdiv-gs-degree-type {
						pointer-events: none;
						opacity: 0.5;
					}
					#tagsdiv-gs-degree-type .inside {
						background-color: #f0f0f0;
					}
					#tagsdiv-gs-degree-type input,
					#tagsdiv-gs-degree-type select,
					#tagsdiv-gs-degree-type button {
						cursor: not-allowed;
					}
				';
				wp_add_inline_style( 'gsdp-admin', $custom_css );

				// Add inline JS to make fields readonly/disabled
				$custom_js = '
					jQuery(document).ready(function($) {
						// Make title readonly
						$("#title").prop("readonly", true);
						// Disable permalink edit button
						$("#edit-slug-buttons .edit-slug").remove();
						$(".edit-slug").remove();
						// Disable inputs in Program Names panel
						$("#gs-program-namediv input, #gs-program-namediv select, #gs-program-namediv button").prop("disabled", true);
						// Disable inputs in Degree Types panel
						$("#tagsdiv-gs-degree-type input, #tagsdiv-gs-degree-type select, #tagsdiv-gs-degree-type button").prop("disabled", true);
					});
				';
				wp_add_inline_script( 'gsdp-factsheet-admin', $custom_js );
			}
			// Disable Editorial Access Manager (Factsheet Team) panel for non-admins
			if ( ! current_user_can( 'manage_options' ) ) {
				$eam_css = '
					/* Disable EAM / Factsheet Team panel (greyed out) for non-admins */
					#gsdp-team-members {
						pointer-events: none;
						opacity: 0.5;
					}
					#gsdp-team-members .inside {
						background-color: #f0f0f0;
					}
					#gsdp-team-members input,
					#gsdp-team-members select,
					#gsdp-team-members button {
						cursor: not-allowed;
					}
				';
				wp_add_inline_style( 'gsdp-admin', $eam_css );

				$eam_js = '
					jQuery(document).ready(function($) {
						$("#gsdp-team-members input, #gsdp-team-members select, #gsdp-team-members button").prop("disabled", true);
					});
				';
				wp_add_inline_script( 'gsdp-factsheet-admin', $eam_js );
			}
		}

		if ( in_array( $hook_suffix, array( 'edit-tags.php', 'term.php', 'term-new.php' ), true ) && in_array( get_current_screen()->taxonomy, array( 'gs-degree-type' ), true ) ) {
			wp_enqueue_style( 'gsdp-faculty-admin', WSUWP\Plugin\Graduate\Plugin::get('url'). '/css/faculty-admin.css', array(), WSUWP_Graduate_School_Theme()->theme_version() );
		}
	}


	/**
	 * Register the degree program factsheet post type.
	 *
	 * @since 0.4.0
	 */
	public function register_post_type() {
		$labels = array(
			'name' => 'Factsheets',
			'singular_name' => 'Factsheet',
			'all_items' => 'All Factsheets',
			'add_new_item' => 'Add Factsheet',
			'edit_item' => 'Edit Factsheet',
			'new_item' => 'New Factsheet',
			'view_item' => 'View Factsheet',
			'search_items' => 'Search Factsheets',
			'not_found' => 'No factsheets found',
			'not_found_in_trash' => 'No factsheets found in trash',
		);

		$args = array(
			'labels' => $labels,
			'description' => 'Graduate degree program factsheets',
			'public' => true,
			'hierarchical' => false,
			'menu_icon' => 'dashicons-groups',
			'supports' => array(
				'title',
				'revisions',
			),
			'has_archive' => false,
			'rewrite' => array(
				'slug' => 'degrees',
				//'with_front' => false,
			),
		);
		register_post_type( $this->post_type_slug, $args );
	}


	/**
	 * Register the meta keys used to store degree factsheet data.
	 *
	 * @since 0.4.0
	 */
	public function register_meta() {
		foreach ( $this->post_meta_keys as $key => $args ) {
			// We have several data types that are stored as strings.
			if ( 'float' === $args['type'] || 'deadlines' === $args['type'] || 'requirements' === $args['type'] ) {
				$args['type'] = 'string';
			}

			$args['show_in_rest'] = true;
			$args['single'] = true;
			register_meta( 'post', $key, $args );
		}
	}

	/**
	 * Add the meta boxes used to capture information about a degree factsheet.
	 *
	 * @since 0.4.0
	 *
	 * @param string $post_type
	 */
	public function add_meta_boxes( $post_type ) {
		if ( $this->post_type_slug !== $post_type ) {
			return;
		}

		add_meta_box( 'factsheet-primary', 'Factsheet Data', array( $this, 'display_factsheet_primary_meta_box' ), null, 'normal', 'high' );
		add_meta_box( 'factsheet-secondary', 'Factsheet Text Blocks', array( $this, 'display_factsheet_secondary_meta_box' ), null, 'normal', 'default' );
	}

	/**
	 * Removes unnecessary meta boxes from the factsheet screen.
	 *
	 * Note: Program Names and Degree Types taxonomy boxes are NOT removed here.
	 * They are greyed out (disabled) via CSS/JS in admin_enqueue_scripts() for
	 * restricted contributors.
	 *
	 * @since 0.7.0
	 * @since 1.4.0 Taxonomy boxes now greyed out instead of removed.
	 *
	 * @param string $post_type
	 */
	public function remove_meta_boxes( $post_type ) {
		if ( $this->post_type_slug !== $post_type ) {
			return;
		}

		remove_meta_box( 'wpseo_meta', $this->post_type_slug, 'normal' );

		// Team management is handled by Factsheet_Team; remove the EAM meta box if the plugin is active.
		remove_meta_box( 'eam_access_manager', $this->post_type_slug, 'side' );
	}

	/**
	 * Captures the main set of data about a degree factsheet.
	 *
	 * @since 0.4.0
	 *
	 * @param WP_Post $post The current post object.
	 */
	public function display_factsheet_primary_meta_box( $post ) {
		$data = get_registered_metadata( 'post', $post->ID );

		wp_nonce_field( 'save-gsdp-primary', '_gsdp_primary_nonce' );

		echo '<div class="factsheet-primary-inputs">';

		foreach ( $this->post_meta_keys as $key => $meta ) {
			if ( ! isset( $data[ $key ] ) || ! isset( $data[ $key ][0] ) ) {
				$data[ $key ] = array( false );
			}

			if ( 'primary' !== $meta['location'] ) {
				continue;
			}

			$this->output_meta_box_html( $meta, $data, $key );
		}

		echo '</div>'; // End factsheet-primary-inputs.
	}

	/**
	 * Converts an old faculty relationship structure to one that uses a generated
	 * unique ID to track uniqueness.
	 *
	 * @since 1.2.0
	 *
	 * @param array   $faculty_relationships
	 * @param WP_Term $faculty_member
	 * @param string  $unique_id
	 *
	 * @return array
	 */
	private function convert_old_faculty_relationship_structure( $faculty_relationships, $faculty_member, $unique_id ) {
		$old_hash = md5( $faculty_member->name );

		if ( isset( $faculty_relationships[ $old_hash ] ) ) {
			$faculty_relationships[ $unique_id ] = $faculty_relationships[ $old_hash ];
		} elseif ( isset( $faculty_relationships[ $faculty_member->term_id ] ) ) {
			$faculty_relationships[ $unique_id ] = $faculty_relationships[ $faculty_member->term_id ];
		} else {
			$faculty_relationships[ $unique_id ] = array();
		}

		return $faculty_relationships[ $unique_id ];
	}


	/**
	 * Captures the secondary set of data about a degree factsheet.
	 *
	 * @since 0.7.0
	 *
	 * @param WP_Post $post The current post object.
	 */
	public function display_factsheet_secondary_meta_box( $post ) {
		$data = get_registered_metadata( 'post', $post->ID );

		echo '<div class="factsheet-primary-inputs">';

		foreach ( $this->post_meta_keys as $key => $meta ) {
			if ( ! isset( $data[ $key ] ) || ! isset( $data[ $key ][0] ) ) {
				$data[ $key ] = array( false );
			}

			if ( 'secondary' !== $meta['location'] ) {
				continue;
			}

			$this->output_meta_box_html( $meta, $data, $key );
		}

		echo '</div>'; // End factsheet-primary-inputs.
	}

	/**
	 * Outputs the HTML associated with the primary and secondary meta boxes.
	 *
	 * @since 0.7.0
	 *
	 * @param $meta
	 * @param $data
	 * @param $key
	 */
	public function output_meta_box_html( $meta, $data, $key ) {
		if ( isset( $meta['pre_html'] ) ) {
			echo $meta['pre_html']; // @codingStandardsIgnoreLine (HTML is static in code)
		}
		?>
		<div class="factsheet-primary-input factsheet-<?php echo esc_attr( $meta['type'] ); ?>">
		<?php

		if ( isset( $meta['meta_field_callback'] ) && is_callable( $meta['meta_field_callback'] ) ) {
			call_user_func( $meta['meta_field_callback'], $meta, $key, $data );
		}

		echo '</div>'; // End factsheet-primary-input

		if ( isset( $meta['post_html'] ) ) {
			echo $meta['post_html']; // @codingStandardsIgnoreLine (HTML is static in code)
		}
	}

	/**
	 * Outputs the meta field HTML used to capture meta data stored as strings.
	 *
	 * @since 1.3.0
	 *
	 * @param array  $meta
	 * @param string $key
	 * @param array  $data
	 */
	public function display_string_meta_field( $meta, $key, $data ) {
		?>
		<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $meta['description'] ); ?>:</label>
		<?php

		// Check if field is restricted and user is a restricted contributor
		if ( isset( $meta['restricted'] ) && $meta['restricted'] && WSUWP_Factsheet_Access::user_is_restricted_contributor( wp_get_current_user()->ID, get_the_ID() ) ) {
			$disabled = 'disabled';
		} else {
			$disabled = '';
		}

		?>
		<input type="text" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $data[ $key ][0] ); ?>" <?php echo $disabled; // @codingStandardsIgnoreLine (HTML is static in code) ?> />
		<?php
	}

	/**
	 * Outputs the meta field HTML used to capture meta data stored as an integer.
	 *
	 * @since 1.3.0
	 *
	 * @param array  $meta
	 * @param string $key
	 * @param array  $data
	 */
	public function display_int_meta_field( $meta, $key, $data ) {
		?>
		<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $meta['description'] ); ?>:</label>
		<?php

		// Check if field is restricted and user is a restricted contributor
		if ( isset( $meta['restricted'] ) && $meta['restricted'] && WSUWP_Factsheet_Access::user_is_restricted_contributor( wp_get_current_user()->ID, get_the_ID() ) ) {
			$disabled = 'disabled';
		} else {
			$disabled = '';
		}

		?>
		<input type="text" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo absint( $data[ $key ][0] ); ?>" <?php echo $disabled; // @codingStandardsIgnoreLine (HTML is static in code) ?> />
		<?php
	}

	/**
	 * Outputs the meta field HTML used to capture meta data stored as boolean.
	 *
	 * @since 1.3.0
	 * @since 1.4.0 Added support for restricted fields that are disabled for contributors.
	 *
	 * @param array  $meta
	 * @param string $key
	 * @param array  $data
	 */
	public function display_bool_meta_field( $meta, $key, $data ) {
		// Check if field is restricted and user is a restricted contributor
		if ( isset( $meta['restricted'] ) && $meta['restricted'] && WSUWP_Factsheet_Access::user_is_restricted_contributor( wp_get_current_user()->ID, get_the_ID() ) ) {
			$disabled = 'disabled';
		} else {
			$disabled = '';
		}

		?>
		<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $meta['description'] ); ?>:</label>
		<select name="<?php echo esc_attr( $key ); ?>" <?php echo $disabled; // @codingStandardsIgnoreLine ?>>
			<option value="0" <?php selected( 0, absint( $data[ $key ][0] ) ); ?>>No</option>
			<option value="1" <?php selected( 1, absint( $data[ $key ][0] ) ); ?>>Yes</option>
		</select>
		<?php
	}

	/**
	 * Outputs the meta field HTML used to capture meta data stored as strings.
	 *
	 * @since 1.3.0
	 *
	 * @param array  $meta
	 * @param string $key
	 * @param array  $data
	 */
	public function display_textarea_meta_field( $meta, $key, $data ) {
		?>
		<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $meta['description'] ); ?>:</label>
		<?php

		if ( isset( $meta['restricted'] ) && $meta['restricted'] && WSUWP_Factsheet_Access::user_is_eam_user( wp_get_current_user()->ID, get_the_ID() ) ) {
			echo '<div id="' . esc_attr( $key ) . '" class="field-content">' . wp_kses_post( apply_filters( 'the_content', $data[ $key ][0] ) ) . '</div>';
			return;
		}

		$wp_editor_settings = array(
			'textarea_rows' => 10,
			'media_buttons' => false,
			'teeny' => true,
		);

		wp_editor( $data[ $key ][0], esc_attr( $key ), $wp_editor_settings );
	}

		/**
	 * Outputs the meta field HTML used to capture meta data stored as strings.
	 *
	 * @since 1.3.0
	 *
	 * @param array  $meta
	 * @param string $key
	 * @param array  $data
	 */
	public function display_deadlines_prog_meta_field( $meta, $key, $data ) {
		$field_data = maybe_unserialize( $data[ $key ][0] );

		if ( empty( $field_data ) ) {
			$field_data = array();
		}

		$default_field_data = array(
			'semester' => 'None',
			'deadline' => '',
			'international' => '',
		);
		$field_count = 0;

		?>
		<div class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-wrapper">
			<span class="factsheet-label"><strong>Program Deadlines:</strong></span>
			<?php

			foreach ( $field_data as $field_datum ) {
				$field_datum = wp_parse_args( $field_datum, $default_field_data );

				?>
				<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
					<select name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][semester]">
						<option value="None" <?php selected( 'None', $field_datum['semester'] ); ?>>Not selected</option>
						<option value="Fall" <?php selected( 'Fall', $field_datum['semester'] ); ?>>Fall</option>
						<option value="Spring" <?php selected( 'Spring', $field_datum['semester'] ); ?>>Spring</option>
						<option value="Summer" <?php selected( 'Summer', $field_datum['semester'] ); ?>>Summer</option>
					</select>
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][deadline]" value="<?php echo esc_attr( $field_datum['deadline'] ); ?>" />
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][international]" value="<?php echo esc_attr( $field_datum['international'] ); ?>" />
					<span class="remove-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">Remove</span>
				</span>
				<?php
				$field_count++;
			}

			// If no fields have been added, provide an empty field by default.
			if ( 0 === count( $field_data ) ) {
				?>
				<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
					<select name="<?php echo esc_attr( $key ); ?>[0][semester]">
						<option value="None">Not selected</option>
						<option value="Fall">Fall</option>
						<option value="Spring">Spring</option>
						<option value="Summer">Summer</option>
					</select>
					<input type="text" name="<?php echo esc_attr( $key ); ?>[0][deadline]" value="" />
					<input type="text" name="<?php echo esc_attr( $key ); ?>[0][international]" value="" />
				</span>
				<?php
			}

			// @codingStandardsIgnoreStart
			?>
			<script type="text/template" id="factsheet-deadlines_prog-template">
				<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
					<select name="<?php echo esc_attr( $key ); ?>[<%= form_count %>][semester]">
						<option value="None">Not selected</option>
						<option value="Fall">Fall</option>
						<option value="Spring">Spring</option>
						<option value="Summer">Summer</option>
					</select>
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<%= form_count %>][deadline]" value="" />
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<%= form_count %>][international]" value="" />
					<span class="remove-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">Remove</span>
				</span>
			</script>
			<input type="button" class="add-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field button" value="Add" />
			<input type="hidden" name="factsheet_deadlines_prog_form_count" id="factsheet_deadlines_prog_form_count" value="<?php echo esc_attr( $field_count ); ?>" />
		</div>
		<?php
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Outputs the meta field HTML used to capture meta data stored as strings.
	 *
	 * @since 1.3.0
	 *
	 * @param array  $meta
	 * @param string $key
	 * @param array  $data
	 */
	public function display_deadlines_meta_field( $meta, $key, $data ) {
		$field_data = maybe_unserialize( $data[ $key ][0] );

		if ( empty( $field_data ) ) {
			$field_data = array();
		}

		$default_field_data = array(
			'semester' => 'None',
			'deadline' => '',
			'international' => '',
		);
		$field_count = 0;

		?>
		<div class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-wrapper">
			<span class="factsheet-label"><strong>Priority Deadlines:</strong></span>
			<?php

			// If no fields have been added, provide an empty field by default.
			if ( 0 === count( $field_data ) ) {
				?>
				<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
					<select name="<?php echo esc_attr( $key ); ?>[0][semester]">
						<option value="None">Not selected</option>
						<option value="Fall">Fall</option>
						<option value="Spring">Spring</option>
						<option value="Summer">Summer</option>
					</select>
					<input type="text" name="<?php echo esc_attr( $key ); ?>[0][deadline]" value="" />
					<input type="text" name="<?php echo esc_attr( $key ); ?>[0][international]" value="" />
				</span>
				<?php
			}

			foreach ( $field_data as $field_datum ) {
				$field_datum = wp_parse_args( $field_datum, $default_field_data );

				?>
				<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
					<select name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][semester]">
						<option value="None" <?php selected( 'None', $field_datum['semester'] ); ?>>Not selected</option>
						<option value="Fall" <?php selected( 'Fall', $field_datum['semester'] ); ?>>Fall</option>
						<option value="Spring" <?php selected( 'Spring', $field_datum['semester'] ); ?>>Spring</option>
						<option value="Summer" <?php selected( 'Summer', $field_datum['semester'] ); ?>>Summer</option>
					</select>
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][deadline]" value="<?php echo esc_attr( $field_datum['deadline'] ); ?>" />
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][international]" value="<?php echo esc_attr( $field_datum['international'] ); ?>" />
					<span class="remove-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">Remove</span>
				</span>
				<?php
				$field_count++;
			}



			// @codingStandardsIgnoreStart
			?>
			<script type="text/template" id="factsheet-deadline-template">
				<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
					<select name="<?php echo esc_attr( $key ); ?>[<%= form_count %>][semester]">
						<option value="None">Not selected</option>
						<option value="Fall">Fall</option>
						<option value="Spring">Spring</option>
						<option value="Summer">Summer</option>
					</select>
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<%= form_count %>][deadline]" value="" />
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<%= form_count %>][international]" value="" />
					<span class="remove-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">Remove</span>
				</span>
			</script>
			<input type="button" class="add-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field button" value="Add" />
			<input type="hidden" name="factsheet_deadline_form_count" id="factsheet_deadline_form_count" value="<?php echo esc_attr( $field_count ); ?>" />
		</div>
		<?php
		// @codingStandardsIgnoreEnd
	}


		/**
	 * Outputs the meta field HTML used to capture meta data stored as strings.
	 *
	 * @since 1.3.0
	 *
	 * @param array  $meta
	 * @param string $key
	 * @param array  $data
	 */
	public function display_contacts_meta_field( $meta, $key, $data ) {
			$field_data = maybe_unserialize( $data[ $key ][0] );
	
			if ( empty( $field_data ) ) {
				$field_data = array();
			}
	
			$default_field_data = array(
				'name' => '',
				'email' => '',
			);
			$field_count = 0;
	
			?>
			<div class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-wrapper">
				<span class="factsheet-label"><strong>Contact Information: </strong></span>
				<?php
	
				foreach ( $field_data as $field_datum ) {
					$field_datum = wp_parse_args( $field_datum, $default_field_data );
	
					?>
					<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
						<label for="Name">Name: </label>
						<input type="text" id="Name" name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][name]" value="<?php echo esc_attr( $field_datum['name'] ); ?>" />
						<label for="Email">Email: </label>
						<input type="text" id ="Email" name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][email]" value="<?php echo esc_attr( $field_datum['email'] ); ?>" />
						<span class="remove-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">Remove</span>
					</span>
					<?php
					$field_count++;
				}
	
				// If no fields have been added, provide an empty field by default.
				if ( 0 === count( $field_data ) ) {
					?>
					<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
					<label for="Name">Name: </label>
	
					<input type="text" name="<?php echo esc_attr( $key ); ?>[0][name]" value="" /><br>
					<label for="Email">Email: </label>

						<input type="text" name="<?php echo esc_attr( $key ); ?>[0][email]" value="" />
					</span>
					<?php
				}
	
				// @codingStandardsIgnoreStart
				?>
				<script type="text/template" id="factsheet-gscontacts-template">
					<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
					<label for="Name">Name: </label>
						<input type="text" name="<?php echo esc_attr( $key ); ?>[<%= form_count %>][name]" value="" />
						<label for="Email">Email: </label>
						<input type="text" name="<?php echo esc_attr( $key ); ?>[<%= form_count %>][email]" value="" />
						<span class="remove-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">Remove</span>
					</span>
				</script>
				<input type="button" class="add-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field button" value="Add" />
				<input type="hidden" name="factsheet_gscontacts_count" id="factsheet_gscontacts_count" value="<?php echo esc_attr( $field_count ); ?>" />
			</div>
			<?php
			// @codingStandardsIgnoreEnd
		}
	


	/**
	 * Outputs the meta field HTML used to capture meta data stored as strings.
	 *
	 * @since 1.3.0
	 *
	 * @param array  $meta
	 * @param string $key
	 * @param array  $data
	 */
	public function display_requirements_gre_meta_field( $meta, $key, $data ) {
		$field_data = maybe_unserialize( $data[ $key ][0] );

		if ( empty( $field_data ) ) {
			$field_data = array();
		}

		$default_field_data = array(
			'required' => 'None',
			'test' => '',
		);
		$field_count = 0;

		?>
		<div class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-wrapper">
			<span class="factsheet-label"><strong>Additional Program Requirements:</strong></span>
			<?php

			// If no fields have been added, provide an empty field by default.
			if ( 0 === count( $field_data ) ) {
				?>
				<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
				<label for="test">Test Name (GRE, GMAT, etc.): </label>

					<input type="text" name="<?php echo esc_attr( $key ); ?>[0][test]" value="" />
					<label for="required">Required?:  </label>

					<select id="required" name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][required]">
						<option value="None" <?php selected( 'None', $default_field_data['required'] ); ?>>Not selected</option>
						<option value="Optional" <?php selected( 'Optional', $default_field_data['required'] ); ?>>Optional</option>
						<option value="Yes" <?php selected( 'Yes', $default_field_data['required'] ); ?>>Yes</option>
						<option value="No" <?php selected( 'No', $default_field_data['required'] ); ?>>No</option>
					</select>
				</span>
				<?php
				}

			foreach ( $field_data as $field_datum ) {
				$field_datum = wp_parse_args( $field_datum, $default_field_data );

				?>
				<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
				<label for="test">Test Name (GRE, GMAT, etc.): </label>
					<input type="text" id="test" name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][test]" value="<?php echo esc_attr( $field_datum['test'] ); ?>" />
					<label for="required">Required?:  </label>

					<select id="required" name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][required]">
						<option value="None" <?php selected( 'None', $field_datum['required'] ); ?>>Not selected</option>
						<option value="Optional" <?php selected( 'Optional', $field_datum['required'] ); ?>>Optional</option>
						<option value="Yes" <?php selected( 'Yes', $field_datum['required'] ); ?>>Yes</option>
						<option value="No" <?php selected( 'No', $field_datum['required'] ); ?>>No</option>
					</select>
					<span class="remove-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">Remove</span>
				</span>
				<?php
				$field_count++;
			}

			

			// @codingStandardsIgnoreStart
			?>
			<script type="text/template" id="factsheet-requirement-gre-template">
				<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
				<label for="test">Test Name (GRE, GMAT, etc.): </label>
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<%= form_count %>][test]" value="" />
				<label for="required">Required?:  </label>

					<select id="required" name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][required]">
						<option value="None" <?php selected( 'None', $field_datum['required'] ); ?>>Not selected</option>
						<option value="Optional" <?php selected( 'Optional', $field_datum['required'] ); ?>>Optional</option>
						<option value="Yes" <?php selected( 'Yes', $field_datum['required'] ); ?>>Yes</option>
						<option value="No" <?php selected( 'No', $field_datum['required'] ); ?>>No</option>
					</select>
					<span class="remove-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">Remove</span>
				</span>
			</script>
			<input type="button" class="add-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field button" value="Add" />
			<input type="hidden" name="factsheet_requirement_form_gre_count" id="factsheet_requirement_form_gre_count" value="<?php echo esc_attr( $field_count ); ?>" />
		</div>
		<?php
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Outputs the meta field HTML used to capture meta data stored as strings.
	 *
	 * @since 1.3.0
	 *
	 * @param array  $meta
	 * @param string $key
	 * @param array  $data
	 */
	public function display_requirements_meta_field( $meta, $key, $data ) {
		$field_data = maybe_unserialize( $data[ $key ][0] );

		if ( empty( $field_data ) ) {
			$field_data = array();
		}

		$default_field_data = array(
			'score' => '',
			'test' => '',
			'description' => '',
		);
		$field_count = 0;

		?>
		<div class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-wrapper">
			<span class="factsheet-label"><strong>Language Test Requirements:</strong></span>
			<?php

			foreach ( $field_data as $field_datum ) {
				$field_datum = wp_parse_args( $field_datum, $default_field_data );

				?>
				<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][score]" value="<?php echo esc_attr( $field_datum['score'] ); ?>" />
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][test]" value="<?php echo esc_attr( $field_datum['test'] ); ?>" />
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][description]" value="<?php echo esc_attr( $field_datum['description'] ); ?>" />
					<span class="remove-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">Remove</span>
				</span>
				<?php
				$field_count++;
			}

			// If no fields have been added, provide an empty field by default.
			if ( 0 === count( $field_data ) ) {
				?>
				<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
					<input type="text" name="<?php echo esc_attr( $key ); ?>[0][score]" value="" />
					<input type="text" name="<?php echo esc_attr( $key ); ?>[0][test]" value="" />
					<input type="text" name="<?php echo esc_attr( $key ); ?>[0][description]" value="" />
				</span>
				<?php
			}

			// @codingStandardsIgnoreStart
			?>
			<script type="text/template" id="factsheet-requirement-template">
				<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<%= form_count %>][score]" value="" />
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<%= form_count %>][test]" value="" />
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<%= form_count %>][description]" value="" />
					<span class="remove-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">Remove</span>
				</span>
			</script>
			<input type="button" class="add-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field button" value="Add" />
			<input type="hidden" name="factsheet_requirement_form_count" id="factsheet_requirement_form_count" value="<?php echo esc_attr( $field_count ); ?>" />
		</div>
		<?php
		// @codingStandardsIgnoreEnd
	}



	

	/**
	 * Outputs the meta field HTML used to capture meta data stored as strings.
	 *
	 * @since 1.3.0
	 *
	 * @param array  $meta
	 * @param string $key
	 * @param array  $data
	 */
	public function display_locations_meta_field( $meta, $key, $data ) {
		$field_data = maybe_unserialize( $data[ $key ][0] );

		if ( empty( $field_data ) ) {
			$field_data = array();
		}

		$default_field_data = array(
			'Pullman' => 'No',
			'Spokane' => 'No',
			'Tri-Cities' => 'No',
			'Vancouver' => 'No',
			'Everett' => 'No',
			'Global Campus (online)' => 'No',

		);
		$field_data = wp_parse_args( $field_data, $default_field_data );

		if ( isset( $meta['restricted'] ) && $meta['restricted'] && WSUWP_Factsheet_Access::user_is_eam_user( wp_get_current_user()->ID, get_the_ID() ) ) {
			$restricted = true;
		} else {
			$restricted = false;
		}

		?>
		<div class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-wrapper">
			<span class="factsheet-label"><strong>Locations:</strong></span>
			<?php

			foreach ( $field_data as $location => $location_status ) {
				?>
				<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
					<label for="location-<?php echo esc_attr( sanitize_key( $location ) ); ?>"><?php echo esc_html( $location ); ?></label>
					<?php
					if ( $restricted ) {
						echo '<span id="location-' . esc_attr( sanitize_key( $location ) ) . '" class="field-value">' . esc_attr( $location_status ) . '</span>';
					} else {
						?>
						<select id="location-<?php echo esc_attr( sanitize_key( $location ) ); ?>"
							name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $location ); ?>]">
							<option value="No" <?php selected( 'No', $location_status ); ?>>No</option>
							<option value="Yes" <?php selected( 'Yes', $location_status ); ?>>Yes</option>
							<option value="By Exception" <?php selected( 'By Exception', $location_status ); ?>>By Exception</option>
						</select>
						<?php
					}
					?>


				</span>
				<?php
			}
			?>
		</div>
		<?php
	}

	/**
	 * Prevents restricted contributors from editing a factsheet's title.
	 *
	 * Restricted contributors include:
	 * - Users assigned as factsheet team members
	 * - Users with WordPress contributor or author roles
	 *
	 * @since 1.1.0
	 * @since 1.4.0 Updated to use user_is_restricted_contributor() for role-based access.
	 *
	 * @param array $data
	 * @param array $postarr
	 *
	 * @return array
	 */
	public function manage_factsheet_title_update( $data, $postarr ) {
		$user = wp_get_current_user();

		if ( isset( $postarr['ID'] ) && WSUWP_Factsheet_Access::user_is_restricted_contributor( $user->ID, $postarr['ID'] ) ) {
			$existing_title = get_post_field( 'post_title', absint( $postarr['ID'] ) );
			if ( ! empty( $existing_title ) && $data['post_title'] !== $existing_title ) {
				$data['post_title'] = $existing_title;
			}
		}

		return $data;
	}

	/**
	 * Sanitizes a GPA value.
	 *
	 * @since 0.4.0
	 *
	 * @param string $gpa The unsanitized GPA.
	 *
	 * @return string The sanitized GPA.
	 */
	public static function sanitize_gpa( $gpa ) {
		$dot_count = substr_count( $gpa, '.' );

		if ( 0 === $dot_count ) {
			$gpa = absint( $gpa ) . '.0';
		} elseif ( 1 === $dot_count ) {
			$gpa = explode( '.', $gpa );
			$gpa = absint( $gpa[0] ) . '.' . absint( $gpa[1] );
		} else {
			$gpa = '0.0';
		}

		return $gpa;
	}

	/**
	 * Sanitizes a set of locations stored in a string.
	 *
	 * @since 0.10.0
	 *
	 * @param array $locations
	 *
	 * @return array
	 */
	public static function sanitize_locations( $locations ) {
		if ( ! is_array( $locations ) || 0 === count( $locations ) ) {
			$locations = array();
		}

		$location_names = array( 'Pullman', 'Spokane', 'Tri-Cities', 'Vancouver',  'Everett','Global Campus (online)', );
		$clean_locations = array();

		foreach ( $location_names as $location_name ) {
			if ( ! isset( $locations[ $location_name ] ) || ! in_array( $locations[ $location_name ], array( 'No', 'Yes', 'By Exception' ), true ) ) {
				$clean_locations[ $location_name ] = 'No';
			} else {
				$clean_locations[ $location_name ] = $locations[ $location_name ];
			}
		}

		return $clean_locations;
	}

	/**
	 * Sanitizes a set of deadlines stored in a string.
	 *
	 * @since 0.4.0
	 *
	 * @param array $deadlines
	 *
	 * @return string
	 */
	public static function sanitize_deadlines( $deadlines ) {
		if ( ! is_array( $deadlines ) || 0 === count( $deadlines ) ) {
			return '';
		}

		$clean_deadlines = array();

		foreach ( $deadlines as $deadline ) {
			$clean_deadline = array();

			if ( isset( $deadline['semester'] ) && in_array( $deadline['semester'], array( 'None', 'Fall', 'Spring', 'Summer' ), true ) ) {
				$clean_deadline['semester'] = $deadline['semester'];
			} else {
				$clean_deadline['semester'] = 'None';
			}

			if ( isset( $deadline['deadline'] ) ) {
				$clean_deadline['deadline'] = sanitize_text_field( $deadline['deadline'] );
			} else {
				$clean_deadline['deadline'] = '';
			}

			if ( isset( $deadline['international'] ) ) {
				$clean_deadline['international'] = sanitize_text_field( $deadline['international'] );
			} else {
				$clean_deadline['international'] = '';
			}

			$clean_deadlines[] = $clean_deadline;
		}

		return $deadlines;
	}


	/**
	 * Sanitizes a set of deadlines stored in a string.
	 *
	 * @since 0.4.0
	 *
	 * @param array $deadlines
	 *
	 * @return string
	 */
	public static function sanitize_deadlines_prog( $deadlines_prog ) {
		if ( ! is_array( $deadlines_prog ) || 0 === count( $deadlines_prog ) ) {
			return '';
		}

		$clean_deadlines_prog = array();

		foreach ( $deadlines_prog as $deadline_prog ) {
			$clean_deadline_prog = array();

			if ( isset( $deadline_prog['semester'] ) && in_array( $deadline_prog['semester'], array( 'None', 'Fall', 'Spring', 'Summer' ), true ) ) {
				$clean_deadline_prog['semester'] = $deadline_prog['semester'];
			} else {
				$clean_deadline_prog['semester'] = 'None';
			}

			if ( isset( $deadline_prog['deadline_prog'] ) ) {
				$clean_deadline_prog['deadline_prog'] = sanitize_text_field( $deadline_prog['deadline_prog'] );
			} else {
				$clean_deadline_prog['deadline_prog'] = '';
			}

			if ( isset( $deadline_prog['international'] ) ) {
				$clean_deadline_prog['international'] = sanitize_text_field( $deadline_prog['international'] );
			} else {
				$clean_deadline_prog['international'] = '';
			}

			$clean_deadlines_prog[] = $clean_deadline_prog;
		}

		return $deadlines_prog;
	}

		/**
	 * Sanitizes a set of requirements stored in a string.
	 *
	 * @since 0.4.0
	 *
	 * @param array $requirements_gre
	 *
	 * @return string
	 */
	public static function sanitize_contacts( $contacts ) {
		if ( ! is_array( $contacts ) || 0 === count( $contacts ) ) {
			return '';
		}

		$clean_contacts = array();

		foreach ( $contacts as $contact ) {
			$clean_contact = array();

			if ( isset( $contact['name'] ) ) {
				$clean_contact['name'] = sanitize_text_field( $contact['name'] );
			} else {
				$clean_contact['name'] = '';
			}

			if ( isset( $contact['email'] ) ) {
				$clean_contact['email'] = sanitize_text_field( $contact['email'] );
			} else {
				$clean_contact['email'] = '';
			}

			// if ( isset( $contact['required'] ) && in_array( $contact['required'], array('None','Optional', 'Yes', 'No'), true ) ) {
			// 	$clean_contact['required'] = $contact['required'];
			// } else {
			// 	$clean_contact['required'] = 'None';
			// }

			$clean_contacts[] = $clean_contact;
		}

		return $clean_contacts;
	}



	/**
	 * Sanitizes a set of requirements stored in a string.
	 *
	 * @since 0.4.0
	 *
	 * @param array $requirements_gre
	 *
	 * @return string
	 */
	public static function sanitize_requirements_gre( $requirements_gre ) {
		if ( ! is_array( $requirements_gre ) || 0 === count( $requirements_gre ) ) {
			return '';
		}

		$clean_requirements = array();

		foreach ( $requirements_gre as $requirement ) {
			$clean_requirement = array();

			// if ( isset( $requirement['score'] ) ) {
			// 	$clean_requirement['score'] = sanitize_text_field( $requirement['score'] );
			// } else {
			// 	$clean_requirement['score'] = '';
			// }

			if ( isset( $requirement['test'] ) ) {
				$clean_requirement['test'] = sanitize_text_field( $requirement['test'] );
			} else {
				$clean_requirement['test'] = '';
			}

			if ( isset( $requirement['required'] ) && in_array( $requirement['required'], array('None','Optional', 'Yes', 'No'), true ) ) {
				$clean_requirement['required'] = $requirement['required'];
			} else {
				$clean_requirement['required'] = 'None';
			}

			// if ( isset( $requirement['description'] ) ) {
			// 	$clean_requirement['description'] = sanitize_text_field( $requirement['description'] );
			// } else {
			// 	$clean_requirement['description'] = '';
			// }

			$clean_requirements[] = $clean_requirement;
		}

		return $clean_requirements;
	}



	/**
	 * Sanitizes a set of requirements stored in a string.
	 *
	 * @since 0.4.0
	 *
	 * @param array $requirements
	 *
	 * @return string
	 */
	public static function sanitize_requirements( $requirements ) {
		if ( ! is_array( $requirements ) || 0 === count( $requirements ) ) {
			return '';
		}

		$clean_requirements = array();

		foreach ( $requirements as $requirement ) {
			$clean_requirement = array();

			if ( isset( $requirement['score'] ) ) {
				$clean_requirement['score'] = sanitize_text_field( $requirement['score'] );
			} else {
				$clean_requirement['score'] = '';
			}

			if ( isset( $requirement['test'] ) ) {
				$clean_requirement['test'] = sanitize_text_field( $requirement['test'] );
			} else {
				$clean_requirement['test'] = '';
			}

			if ( isset( $requirement['description'] ) ) {
				$clean_requirement['description'] = sanitize_text_field( $requirement['description'] );
			} else {
				$clean_requirement['description'] = '';
			}

			$clean_requirements[] = $clean_requirement;
		}

		return $clean_requirements;
	}


	/**
	 * Save additional data associated with a factsheet.
	 *
	 * @since 0.4.0
	 *
	 * @param int     $post_id
	 * @param WP_Post $post
	 */
	public function save_factsheet( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( 'auto-draft' === $post->post_status ) {
			return;
		}

		// Do not overwrite existing information during an import.
		if ( defined( 'WP_IMPORTING' ) && WP_IMPORTING ) {
			return;
		}

		if ( ! isset( $_POST['_gsdp_primary_nonce'] ) || ! wp_verify_nonce( $_POST['_gsdp_primary_nonce'], 'save-gsdp-primary' ) ) {
			return;
		}

		$keys = get_registered_meta_keys( 'post' );

		foreach ( $this->post_meta_keys as $key => $meta ) {
			if ( isset( $_POST[ $key ] ) && isset( $keys[ $key ] ) && isset( $keys[ $key ]['sanitize_callback'] ) ) {
				if ( current_user_can( 'edit_post_meta', $post_id, $key ) ) {
					// Each piece of meta is registered with sanitization.
					update_post_meta( $post_id, $key, $_POST[ $key ] );
				}
			}
		}

		/**
		 * Added the following to force update the last modified date since that doesn't happen
		 * when you are updating post meta.
		 */
		remove_action( "save_post_{$this->post_type_slug}", array( $this, 'save_factsheet' ), 10, 2 );

		global $wpdb;

		//eg. time one year ago..
		$time = time();

		$mysql_time_format = 'Y-m-d H:i:s';

		$post_modified = gmdate( $mysql_time_format, $time );

		$post_modified_gmt = gmdate( $mysql_time_format, ( $time + get_option( 'gmt_offset' ) ) );

		$wpdb->query( $wpdb->prepare( "UPDATE %s SET post_modified = %s, post_modified_gmt = %s  WHERE ID = %d", array( $wpdb->posts, $post_modified, $post_modified_gmt, $post_id ) ) );

		// end last modified update.
	}

	/**
	 * Returns a usable subset of data for displaying a factsheet.
	 *
	 * @since 0.4.0
	 *
	 * @param int $post_id
	 *
	 * @return array
	 */
	public static function get_factsheet_data( $post_id ) {
		return WSUWP_Factsheet_Data::get_factsheet_data( $post_id );
	}

	/**
	 * Redirects a factsheet ID to its corresponding URL.
	 *
	 * @since 0.10.0
	 *
	 * @param int $degree_id
	 */
	public function redirect_factsheet_id( $degree_id ) {
		$this->factsheet_redirects->redirect_factsheet_id( $degree_id );
	}
}
