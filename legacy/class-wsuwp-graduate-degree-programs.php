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
	 * @var WSUWP_Factsheet_Admin_Assets
	 */
	private $factsheet_admin_assets;

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
			'meta_field_callback' => array( 'WSUWP_Factsheet_Fields_Display', 'display_string_meta_field' ),
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
			'meta_field_callback' => array( 'WSUWP_Factsheet_Fields_Display', 'display_bool_meta_field' ),
			'location' => 'primary',
		),
		'gsdp_include_in_programs' => array(
			'description' => 'Include in programs list',
			'type' => 'bool',
			'sanitize_callback' => 'absint',
			'meta_field_callback' => array( 'WSUWP_Factsheet_Fields_Display', 'display_bool_meta_field' ),
			'restricted' => true,
			'location' => 'primary',
		),
		'gsdp_grad_students_total' => array(
			'description' => 'Total grad students',
			'type' => 'int',
			'sanitize_callback' => 'absint',
			'meta_field_callback' => array( 'WSUWP_Factsheet_Fields_Display', 'display_int_meta_field' ),
			'location' => 'primary',
		),
		'gsdp_grad_faculty_total' => array(
			'description' => 'Total Graduate Faculty',
			'type' => 'int',
			'sanitize_callback' => 'absint',
			'meta_field_callback' => array( 'WSUWP_Factsheet_Fields_Display', 'display_int_meta_field' ),
			'location' => 'primary',
		),

		'gsdp_grad_core_faculty_total' => array(
			'description' => 'Total Core Graduate Faculty',
			'type' => 'int',
			'sanitize_callback' => 'absint',
			'meta_field_callback' => array( 'WSUWP_Factsheet_Fields_Display', 'display_int_meta_field' ),
			'location' => 'primary',
		),
		'gsdp_grad_students_aided' => array(
			'description' => 'Aided grad students',
			'type' => 'int',
			'sanitize_callback' => 'absint',
			'meta_field_callback' => array( 'WSUWP_Factsheet_Fields_Display', 'display_int_meta_field' ),
			'location' => 'primary',
		),
		'gsdp_program_handbook_url' => array(
			'description' => 'Program Handbook URL',
			'type' => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'meta_field_callback' => array( 'WSUWP_Factsheet_Fields_Display', 'display_string_meta_field' ),
			'location' => 'primary',
		),
		'gsdp_student_learning_outcome_url' => array(
			'description' => 'Student Learning Outcomes URL',
			'type' => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'meta_field_callback' => array( 'WSUWP_Factsheet_Fields_Display', 'display_string_meta_field' ),
			'location' => 'primary',
		),
		'gsdp_application_url' => array(
			'description' => 'Application URL',
			'type' => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'meta_field_callback' => array( 'WSUWP_Factsheet_Fields_Display', 'display_string_meta_field' ),
			'restricted' => true,
			'location' => 'primary',
		),
		'gsdp_degree_url' => array(
			'description' => 'Degree home page',
			'type' => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'meta_field_callback' => array( 'WSUWP_Factsheet_Fields_Display', 'display_string_meta_field' ),
			'post_html' => '</div>',
			'location' => 'primary',
		),
		'gsdp_locations' => array(
			'description' => 'Locations',
			'type' => 'locations',
			'sanitize_callback' => 'WSUWP_Factsheet_Sanitizer::sanitize_locations',
			'meta_field_callback' => array( 'WSUWP_Factsheet_Fields_Display', 'display_locations_meta_field' ),
			'restricted' => true,
			'pre_html' => '<div class="factsheet-group">',
			
			'location' => 'primary',
		),
		'gsdp_global_URL' => array(
			'description' => 'Global Campus URL (if different from physical campus)',
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_field_callback' => array( 'WSUWP_Factsheet_Fields_Display', 'display_string_meta_field' ),
			'restricted' => true,
			'location' => 'primary',
			'post_html' => '</div>',
		),
		'gsdp_deadlines' => array(
			'description' => 'Deadlines',
			'type' => 'deadlines',
			'sanitize_callback' => 'WSUWP_Factsheet_Sanitizer::sanitize_deadlines',
			'meta_field_callback' => array( 'WSUWP_Factsheet_Fields_Display', 'display_deadlines_meta_field' ),
			'pre_html' => '<div class="factsheet-group">',
			'post_html' => '</div>',
			'location' => 'primary',
		),
		'gsdp_deadlines_prog' => array(
			'description' => 'Program Deadlines',
			'type' => 'deadlines_prog',
			'sanitize_callback' => 'WSUWP_Factsheet_Sanitizer::sanitize_deadlines_prog',
			'meta_field_callback' => array( 'WSUWP_Factsheet_Fields_Display', 'display_deadlines_prog_meta_field' ),
			'pre_html' => '<div class="factsheet-group">',
			'post_html' => '</div>',
			'location' => 'primary',
		),
		'gsdp_requirements' => array(
			'description' => 'Language Test Requirements',
			'type' => 'requirements',
			'sanitize_callback' => 'WSUWP_Factsheet_Sanitizer::sanitize_requirements',
			'meta_field_callback' => array( 'WSUWP_Factsheet_Fields_Display', 'display_requirements_meta_field' ),
			'pre_html' => '<div class="factsheet-group">',
			'post_html' => '</div>',
			'location' => 'primary',
		),
		'gsdp_requirements_gre' => array(
			'description' => 'GRE Requirements',
			'type' => 'requirements-gre',
			'sanitize_callback' => 'WSUWP_Factsheet_Sanitizer::sanitize_requirements_gre',
			'meta_field_callback' => array( 'WSUWP_Factsheet_Fields_Display', 'display_requirements_gre_meta_field' ),
			'pre_html' => '<div class="factsheet-group">',
			'post_html' => '</div>',
			'location' => 'primary',
		),
		'gsdp_contacts' => array(
			'description' => 'Contacts',
			'type' => 'gscontacts',
			'sanitize_callback' => 'WSUWP_Factsheet_Sanitizer::sanitize_contacts',
			'meta_field_callback' => array( 'WSUWP_Factsheet_Fields_Display', 'display_contacts_meta_field' ),
			'pre_html' => '<div class="factsheet-group">',
			'post_html' => '</div>',
			'location' => 'primary',
		),
		'gsdp_degree_description' => array(
			'description' => 'Description of the graduate degree',
			'type' => 'textarea',
			'sanitize_callback' => 'wp_kses_post',
			'meta_field_callback' => array( 'WSUWP_Factsheet_Fields_Display', 'display_textarea_meta_field' ),
			'location' => 'secondary',
		),
		'gsdp_admission_requirements' => array(
			'description' => 'Admission requirements',
			'type' => 'textarea',
			'sanitize_callback' => 'wp_kses_post',
			'meta_field_callback' => array( 'WSUWP_Factsheet_Fields_Display', 'display_textarea_meta_field' ),
			'location' => 'secondary',
		),
		'gsdp_student_opportunities' => array(
			'description' => 'Student opportunities',
			'type' => 'textarea',
			'sanitize_callback' => 'wp_kses_post',
			'meta_field_callback' => array( 'WSUWP_Factsheet_Fields_Display', 'display_textarea_meta_field' ),
			'location' => 'secondary',
		),
		'gsdp_career_opportunities' => array(
			'description' => 'Career opportunities',
			'type' => 'textarea',
			'sanitize_callback' => 'wp_kses_post',
			'meta_field_callback' => array( 'WSUWP_Factsheet_Fields_Display', 'display_textarea_meta_field' ),
			'location' => 'secondary',
		),
		'gsdp_career_placements' => array(
			'description' => 'Career placements',
			'type' => 'textarea',
			'sanitize_callback' => 'wp_kses_post',
			'meta_field_callback' => array( 'WSUWP_Factsheet_Fields_Display', 'display_textarea_meta_field' ),
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
		require_once dirname( __FILE__ ) . '/class-factsheet-admin-assets.php';
		require_once dirname( __FILE__ ) . '/class-factsheet-sanitizer.php';
		require_once dirname( __FILE__ ) . '/class-factsheet-fields-display.php';
		$this->factsheet_redirects = new WSUWP_Factsheet_Redirects( $this->post_type_slug, $this->archive_slug );
		$this->factsheet_archive = new WSUWP_Factsheet_Archive( $this->post_type_slug );
		$this->factsheet_admin_assets = new WSUWP_Factsheet_Admin_Assets( $this->post_type_slug );


		add_filter( 'admin_body_class', array( $this, 'add_factsheet_admin_body_classes' ) );
		add_action( 'admin_enqueue_scripts', array( $this->factsheet_admin_assets, 'admin_enqueue_scripts' ) );

		add_action( 'init', array( $this, 'register_post_type' ), 15 );
		add_action( 'init', 'WSUWP_Graduate_Degree_Program_Name_Taxonomy', 15 );
		add_action( 'init', 'WSUWP_Graduate_Degree_Degree_Type_Taxonomy', 15 );

		add_filter( 'query_vars', array( $this->factsheet_archive, 'add_gradfair_query_var' ) );
		add_action( 'init', array( $this->factsheet_archive, 'register_mirror_menu' ) );

		add_action( 'init', array( $this, 'register_meta' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'add_meta_boxes', array( $this, 'remove_meta_boxes' ), 99 );
		add_action( "save_post_{$this->post_type_slug}", array( $this, 'save_factsheet' ), 10, 2 );

		add_action( 'admin_menu', array( $this, 'remove_add_new_factsheet_for_contributor' ), 999 );

		// Capability mapping for team-based access is now handled by
		// \WSUWP\Plugin\Graduate\Factsheet_Team::map_meta_cap() at priority 150.

		// Several fields are restricted to full editors or admins.
		// Updated to use new filter format (since WP 4.9.8): auth_post_meta_{meta_key}_for_{post_type}
		// add_filter( "auth_post_meta_gsdp_degree_id_for_{$this->post_type_slug}", array( $this, 'can_edit_restricted_field' ), 100, 4 );
		add_filter( "auth_post_meta_gsdp_degree_shortname_for_{$this->post_type_slug}", array( 'WSUWP_Factsheet_Access', 'can_edit_restricted_field' ), 100, 4 );
		add_filter( "auth_post_meta_gsdp_student_learning_outcome_for_{$this->post_type_slug}", array( 'WSUWP_Factsheet_Access', 'can_edit_restricted_field' ), 100, 4 );
		add_filter( "auth_post_meta_gsdp_include_in_programs_for_{$this->post_type_slug}", array( 'WSUWP_Factsheet_Access', 'can_edit_restricted_field' ), 100, 4 );
		add_filter( "auth_post_meta_gsdp_application_url_for_{$this->post_type_slug}", array( 'WSUWP_Factsheet_Access', 'can_edit_restricted_field' ), 100, 4 );
		add_filter( 'wp_insert_post_data', array( $this, 'manage_factsheet_title_update' ), 10, 2 );

		add_action( 'pre_get_posts', array( $this->factsheet_archive, 'adjust_factsheet_archive_query' ) );
		add_action( 'template_redirect', array( $this->factsheet_redirects, 'redirect_old_factsheet_urls' ) );
		add_action( 'template_redirect', array( $this->factsheet_redirects, 'redirect_private_factsheets' ) );

		add_filter( 'spine_get_title', array( $this->factsheet_archive, 'filter_factsheet_archive_title' ), 10, 3 );
		add_filter( 'post_row_actions', array( $this, 'remove_quick_edit_for_contributors_on_factsheets' ), 10, 2 );
	}

	/**
	 * Remove "Quick Edit" from row actions on the Factsheets list for Contributors.
	 *
	 * @param string[] $actions Row action links.
	 * @param \WP_Post $post    Post object.
	 * @return string[]
	 */
	public function remove_quick_edit_for_contributors_on_factsheets( $actions, $post ) {
		if ( $this->post_type_slug !== $post->post_type ) {
			return $actions;
		}
		$user = wp_get_current_user();
		if ( ! $user->exists() || ! in_array( 'contributor', $user->roles, true ) ) {
			return $actions;
		}
		unset( $actions['inline hide-if-no-js'] );
		return $actions;
	}
	/**
	 * Remove "Add New" / "Add Factsheet" from the Factsheets menu for Contributors.
	 *
	 * @since 1.2.2
	 */
	public function remove_add_new_factsheet_for_contributor() {
		$user = wp_get_current_user();
		if ( ! $user->exists() || ! in_array( 'contributor', $user->roles, true ) ) {
			return;
		}
		remove_submenu_page(
			'edit.php?post_type=' . $this->post_type_slug,
			'post-new.php?post_type=' . $this->post_type_slug
		);
	}

	/**
	 * Add body classes on factsheet edit screen for conditional admin CSS/JS.
	 *
	 * @param string|array $classes Existing body classes (string in older WP, array in WP 5.9+).
	 * @return string|array
	 */
	public function add_factsheet_admin_body_classes( $classes ) {
		$is_string = is_string( $classes );
		if ( $is_string ) {
			$classes = array_filter( array_map( 'trim', explode( ' ', $classes ) ) );
		}

		$screen = get_current_screen();
		if ( ! $screen ) {
			return $is_string ? implode( ' ', $classes ) : $classes;
		}
		// List screen: hide "Add Factsheet" button for contributors only.
		$user = wp_get_current_user();
		if ( $user->exists() && in_array( 'contributor', $user->roles, true ) ) {
			$classes[] = 'gsdp-contributor-no-add';
		}

		if ( 'gs-factsheet' !== $screen->id || ! in_array( $screen->base, array( 'post', 'post-new' ), true ) ) {
			return $is_string ? implode( ' ', $classes ) : $classes;
		}

		$user_id = get_current_user_id();
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$post_status = $post_id ? get_post_status( $post_id ) : '';
		$is_new_or_draft = empty( $post_id ) || in_array( $post_status, array( 'auto-draft', 'draft' ), true );

		if ( ! $is_new_or_draft && WSUWP_Factsheet_Access::user_is_restricted_contributor( $user_id, $post_id ) ) {
			$classes[] = 'gsdp-restricted-contributor';
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			$classes[] = 'gsdp-eam-disabled';
		}

		return $is_string ? implode( ' ', $classes ) : $classes;
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
	 * They are greyed out (disabled) via CSS/JS in Factsheet_Admin_Assets::admin_enqueue_scripts() for
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

			WSUWP_Factsheet_Fields_Display::output_meta_box_html( $meta, $data, $key );
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

			WSUWP_Factsheet_Fields_Display::output_meta_box_html( $meta, $data, $key );
		}

		echo '</div>'; // End factsheet-primary-inputs.
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
