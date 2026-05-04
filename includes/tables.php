<?php
namespace WSUWP\Plugin\Graduate;

class Tables {

	const POST_TYPE = 'gs-table';
	const CAP_LIST = 'gs_tables_list';
	const CAP_ADD = 'gs_tables_add';
	const CAP_EDIT = 'gs_tables_edit';
	const CAP_DELETE = 'gs_tables_delete';
	const CAP_ADVANCED_OPTIONS = 'gs_tables_advanced_options';
	const META_LEGACY_SOURCE = '_gs_table_legacy_source';
	const META_LEGACY_ID = '_gs_table_legacy_id';
	const TOOLS_SLUG = 'gs-table-tools';
	const PREVIEW_SLUG = 'gs-table-preview';
	const LINK_TOKEN_PREFIX = '__GS_LINK__:';
	const META_FOOT_ROW = '_gs_table_foot_row';
	const META_ROW_HOVER = '_gs_table_row_hover';
	const META_PRINT_NAME = '_gs_table_print_name';
	const META_PRINT_NAME_POSITION = '_gs_table_print_name_position';
	const META_PRINT_DESCRIPTION = '_gs_table_print_description';
	const META_PRINT_DESCRIPTION_POSITION = '_gs_table_print_description_position';
	const META_EXTRA_CSS_CLASS = '_gs_table_extra_css_class';
	const META_VISITOR_FEATURES = '_gs_table_visitor_features';
	const META_SEARCH = '_gs_table_search';
	const META_PAGINATION = '_gs_table_pagination';
	const META_PAGINATION_LENGTH = '_gs_table_pagination_length';
	const META_PAGINATION_LENGTH_CHANGE = '_gs_table_pagination_length_change';
	const META_INFO = '_gs_table_info';
	const META_HORIZONTAL_SCROLLING = '_gs_table_horizontal_scrolling';
	const META_CUSTOM_COMMANDS = '_gs_table_custom_commands';
	const META_HIDDEN_ROWS = '_gs_table_hidden_rows';
	const META_HIDDEN_COLS = '_gs_table_hidden_cols';
	const META_CELL_SPANS = '_gs_table_cell_spans';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'init', array( __CLASS__, 'ensure_role_capabilities' ) );
		add_action( 'init', array( __CLASS__, 'register_shortcodes' ), 20 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'maybe_enqueue_preview_assets' ) );
		add_action( 'admin_post_gs_tables_import_tablepress', array( __CLASS__, 'handle_import_tablepress' ) );
		add_action( 'admin_post_gs_tables_export', array( __CLASS__, 'handle_export_tables' ) );
		add_action( 'admin_notices', array( __CLASS__, 'maybe_render_status_notice' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue_assets' ) );
	}

	public static function register_shortcodes() {
		remove_shortcode( 'table' );
		add_shortcode( 'table', array( __CLASS__, 'render_shortcode' ) );
		add_shortcode( 'gs_table', array( __CLASS__, 'render_shortcode' ) );
	}

	public static function register_post_type() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels' => array(
					'name'               => __( 'Tables', 'wsuwp-plugin-graduate-school' ),
					'singular_name'      => __( 'Table', 'wsuwp-plugin-graduate-school' ),
					'menu_name'          => __( 'Tables', 'wsuwp-plugin-graduate-school' ),
					'add_new'            => __( 'Add New Table', 'wsuwp-plugin-graduate-school' ),
					'add_new_item'       => __( 'Add New Table', 'wsuwp-plugin-graduate-school' ),
					'edit_item'          => __( 'Edit Table', 'wsuwp-plugin-graduate-school' ),
					'new_item'           => __( 'New Table', 'wsuwp-plugin-graduate-school' ),
					'view_item'          => __( 'View Table', 'wsuwp-plugin-graduate-school' ),
					'search_items'       => __( 'Search Tables', 'wsuwp-plugin-graduate-school' ),
					'not_found'          => __( 'No tables found', 'wsuwp-plugin-graduate-school' ),
					'not_found_in_trash' => __( 'No tables found in Trash', 'wsuwp-plugin-graduate-school' ),
				),
				'public'             => false,
				'show_ui'            => false,
				'show_in_menu'       => false,
				'show_in_nav_menus'  => false,
				'show_in_admin_bar'  => false,
				'publicly_queryable' => false,
				'exclude_from_search'=> true,
				'has_archive'        => false,
				'rewrite'            => false,
				'menu_icon'          => 'dashicons-table-col-after',
				'supports'           => array( 'title' ),
				'capability_type'    => array( 'gs_table', 'gs_tables' ),
				'map_meta_cap'       => false,
				'capabilities'       => array(
					'read'                   => 'read',
					'read_post'              => self::CAP_EDIT,
					'edit_post'              => self::CAP_EDIT,
					'delete_post'            => self::CAP_DELETE,
					'edit_posts'             => self::CAP_EDIT,
					'edit_others_posts'      => self::CAP_EDIT,
					'publish_posts'          => self::CAP_EDIT,
					'read_private_posts'     => self::CAP_EDIT,
					'create_posts'           => self::CAP_ADD,
					'delete_posts'           => self::CAP_DELETE,
					'delete_private_posts'   => self::CAP_DELETE,
					'delete_published_posts' => self::CAP_DELETE,
					'delete_others_posts'    => self::CAP_DELETE,
					'edit_private_posts'     => self::CAP_EDIT,
					'edit_published_posts'   => self::CAP_EDIT,
				),
			)
		);
	}

	public static function ensure_role_capabilities() {
		$roles = array( 'administrator', 'editor', 'author' );
		foreach ( $roles as $role_name ) {
			$role = get_role( $role_name );
			if ( ! $role ) {
				continue;
			}
			$role->add_cap( self::CAP_LIST );
			$role->add_cap( self::CAP_ADD );
			$role->add_cap( self::CAP_EDIT );
			$role->add_cap( self::CAP_DELETE );
		}

		$admin_role = get_role( 'administrator' );
		if ( $admin_role ) {
			$admin_role->add_cap( self::CAP_ADVANCED_OPTIONS );
		}
	}

	public static function register_meta_boxes() {
		add_meta_box(
			'gs-table-builder',
			__( 'Table Builder', 'wsuwp-plugin-graduate-school' ),
			array( __CLASS__, 'render_table_builder_metabox' ),
			self::POST_TYPE,
			'normal',
			'high'
		);

		add_meta_box(
			'gs-table-shortcode',
			__( 'Embed Table', 'wsuwp-plugin-graduate-school' ),
			array( __CLASS__, 'render_shortcode_metabox' ),
			self::POST_TYPE,
			'side',
			'high'
		);
	}

	public static function render_table_builder_metabox( $post ) {
		wp_nonce_field( 'gs_table_builder_save', 'gs_table_builder_nonce' );

		$caption = get_post_meta( $post->ID, '_gs_table_caption', true );
		$headers = get_post_meta( $post->ID, '_gs_table_headers', true );
		$rows    = get_post_meta( $post->ID, '_gs_table_rows', true );

		$sortable = (bool) get_post_meta( $post->ID, '_gs_table_sortable', true );
		$striped  = (bool) get_post_meta( $post->ID, '_gs_table_striped', true );
		$compact  = (bool) get_post_meta( $post->ID, '_gs_table_compact', true );
		$auto_link = self::get_saved_auto_link_setting( $post->ID );

		if ( ! is_array( $headers ) || empty( $headers ) ) {
			$headers = array( '', '' );
		}

		if ( ! is_array( $rows ) ) {
			$rows = array();
		}

		$column_count = max( 1, count( $headers ) );
		?>
		<div class="gs-editor-section">
			<label for="gs-table-caption" class="gs-editor-label"><?php esc_html_e( 'Caption', 'wsuwp-plugin-graduate-school' ); ?></label>
			<input type="text" id="gs-table-caption" name="gs_table_caption" value="<?php echo esc_attr( $caption ); ?>" class="widefat" />
		</div>

		<div class="gs-editor-section">
			<div class="gs-editor-section-head">
				<h3><?php esc_html_e( 'Columns', 'wsuwp-plugin-graduate-school' ); ?></h3>
				<button type="button" id="gs-add-header" class="button button-small"><?php esc_html_e( 'Add Column', 'wsuwp-plugin-graduate-school' ); ?></button>
			</div>
			<div id="gs-table-headers">
				<?php foreach ( $headers as $header ) : ?>
					<div class="gs-table-inline-row">
						<input type="text" name="gs_table_headers[]" value="<?php echo esc_attr( $header ); ?>" class="widefat" />
						<button type="button" class="button-link-delete gs-remove-header"><?php esc_html_e( 'Remove', 'wsuwp-plugin-graduate-school' ); ?></button>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="gs-editor-section">
			<div class="gs-editor-section-head">
				<h3><?php esc_html_e( 'Rows', 'wsuwp-plugin-graduate-school' ); ?></h3>
				<button type="button" id="gs-add-row" class="button button-small button-secondary"><?php esc_html_e( 'Add Row', 'wsuwp-plugin-graduate-school' ); ?></button>
			</div>
			<div id="gs-table-rows" data-columns="<?php echo esc_attr( (string) $column_count ); ?>">
				<?php foreach ( $rows as $row_index => $row ) : ?>
					<?php if ( ! is_array( $row ) ) { continue; } ?>
					<div class="gs-table-row">
						<?php for ( $i = 0; $i < $column_count; $i++ ) : ?>
							<?php
							$cell      = self::normalize_editor_cell( isset( $row[ $i ] ) ? $row[ $i ] : '' );
							$cell_text = isset( $cell['text'] ) ? (string) $cell['text'] : '';
							$cell_url  = isset( $cell['url'] ) ? (string) $cell['url'] : '';
							?>
							<div class="gs-table-cell-group">
								<input type="text" name="gs_table_rows[<?php echo esc_attr( (string) $row_index ); ?>][<?php echo esc_attr( (string) $i ); ?>][text]" value="<?php echo esc_attr( $cell_text ); ?>" class="widefat gs-table-cell" placeholder="<?php esc_attr_e( 'Cell text', 'wsuwp-plugin-graduate-school' ); ?>" />
								<button type="button" class="button-link gs-toggle-link-fields"><?php echo '' !== $cell_url ? esc_html__( 'Hide Link', 'wsuwp-plugin-graduate-school' ) : esc_html__( 'Link', 'wsuwp-plugin-graduate-school' ); ?></button>
								<div class="gs-table-link-fields<?php echo '' !== trim( $cell_url ) ? ' is-open' : ''; ?>">
									<input type="text" name="gs_table_rows[<?php echo esc_attr( (string) $row_index ); ?>][<?php echo esc_attr( (string) $i ); ?>][link_text]" value="<?php echo esc_attr( $cell_text ); ?>" class="widefat gs-table-cell-link-text" placeholder="<?php esc_attr_e( 'Link text', 'wsuwp-plugin-graduate-school' ); ?>" />
									<input type="url" name="gs_table_rows[<?php echo esc_attr( (string) $row_index ); ?>][<?php echo esc_attr( (string) $i ); ?>][url]" value="<?php echo esc_attr( $cell_url ); ?>" class="widefat gs-table-cell-link-url" placeholder="https://example.com" />
								</div>
							</div>
						<?php endfor; ?>
						<button type="button" class="button-link-delete gs-remove-row"><?php esc_html_e( 'Remove row', 'wsuwp-plugin-graduate-school' ); ?></button>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<details class="gs-editor-section gs-editor-options" open>
			<summary><?php esc_html_e( 'Display Options', 'wsuwp-plugin-graduate-school' ); ?></summary>
			<div class="gs-editor-options-grid">
				<label><input type="checkbox" name="gs_table_sortable" value="1" <?php checked( $sortable ); ?> /> <?php esc_html_e( 'Allow column sorting', 'wsuwp-plugin-graduate-school' ); ?></label>
				<label><input type="checkbox" name="gs_table_striped" value="1" <?php checked( $striped ); ?> /> <?php esc_html_e( 'Striped rows', 'wsuwp-plugin-graduate-school' ); ?></label>
				<label><input type="checkbox" name="gs_table_compact" value="1" <?php checked( $compact ); ?> /> <?php esc_html_e( 'Compact spacing', 'wsuwp-plugin-graduate-school' ); ?></label>
				<label><input type="checkbox" name="gs_table_auto_link" value="1" <?php checked( $auto_link ); ?> /> <?php esc_html_e( 'Auto-link valid URLs in cells', 'wsuwp-plugin-graduate-school' ); ?></label>
			</div>
		</details>
		<?php if ( current_user_can( self::CAP_ADVANCED_OPTIONS ) ) : ?>
			<hr />
			<p><strong><?php esc_html_e( 'Advanced Settings', 'wsuwp-plugin-graduate-school' ); ?></strong></p>
			<p class="description"><?php esc_html_e( 'Admin-only advanced table controls will appear here in a future update.', 'wsuwp-plugin-graduate-school' ); ?></p>
		<?php endif; ?>
		<?php
	}

	public static function render_shortcode_metabox( $post ) {
		$id_shortcode   = sprintf( '[gs_table id="%d"]', (int) $post->ID );
		$slug_shortcode = sprintf( '[gs_table slug="%s"]', esc_attr( $post->post_name ) );
		$preview_url    = self::get_preview_url( $post->ID );
		?>
		<p><?php esc_html_e( 'Use either shortcode in any page or post:', 'wsuwp-plugin-graduate-school' ); ?></p>
		<p><input type="text" readonly class="widefat" value="<?php echo esc_attr( $id_shortcode ); ?>" onclick="this.select();" /></p>
		<?php if ( ! empty( $post->post_name ) ) : ?>
			<p><input type="text" readonly class="widefat" value="<?php echo esc_attr( $slug_shortcode ); ?>" onclick="this.select();" /></p>
		<?php endif; ?>
		<p><a class="button button-secondary" href="<?php echo esc_url( $preview_url ); ?>"><?php esc_html_e( 'Preview Table', 'wsuwp-plugin-graduate-school' ); ?></a></p>
		<?php
	}

	public static function save_table_meta( $post_id ) {
		if ( ! isset( $_POST['gs_table_builder_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gs_table_builder_nonce'] ) ), 'gs_table_builder_save' ) ) {
			return;
		}

		if ( ! current_user_can( self::CAP_EDIT ) ) {
			return;
		}

		$caption = isset( $_POST['gs_table_caption'] ) ? sanitize_text_field( wp_unslash( $_POST['gs_table_caption'] ) ) : '';
		update_post_meta( $post_id, '_gs_table_caption', $caption );

		$headers = isset( $_POST['gs_table_headers'] ) ? (array) wp_unslash( $_POST['gs_table_headers'] ) : array();
		$headers = array_values(
			array_filter(
				array_map( 'sanitize_text_field', $headers ),
				static function( $header ) {
					return '' !== trim( $header );
				}
			)
		);
		update_post_meta( $post_id, '_gs_table_headers', $headers );

		$rows = isset( $_POST['gs_table_rows'] ) ? (array) wp_unslash( $_POST['gs_table_rows'] ) : array();
		$sanitized_rows = array();
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$sanitized_row = array();
			foreach ( $row as $cell ) {
				$sanitized_row[] = self::sanitize_editor_cell( $cell );
			}

			$has_content = false;
			foreach ( $sanitized_row as $cell ) {
				$normalized = self::normalize_editor_cell( $cell );
				if ( '' !== trim( $normalized['text'] ) || '' !== trim( $normalized['url'] ) ) {
					$has_content = true;
					break;
				}
			}

			if ( $has_content ) {
				$sanitized_rows[] = $sanitized_row;
			}
		}
		update_post_meta( $post_id, '_gs_table_rows', $sanitized_rows );

		$sortable = isset( $_POST['gs_table_sortable'] ) ? 1 : 0;
		$striped  = isset( $_POST['gs_table_striped'] ) ? 1 : 0;
		$compact  = isset( $_POST['gs_table_compact'] ) ? 1 : 0;
		$auto_link = isset( $_POST['gs_table_auto_link'] ) ? 1 : 0;
		$foot_row = isset( $_POST['gs_table_foot_row'] ) ? 1 : 0;
		$row_hover = isset( $_POST['gs_table_row_hover'] ) ? 1 : 0;
		$print_name = isset( $_POST['gs_table_print_name'] ) ? 1 : 0;
		$print_name_position = isset( $_POST['gs_table_print_name_position'] ) ? sanitize_key( wp_unslash( $_POST['gs_table_print_name_position'] ) ) : 'above';
		$print_description = isset( $_POST['gs_table_print_description'] ) ? 1 : 0;
		$print_description_position = isset( $_POST['gs_table_print_description_position'] ) ? sanitize_key( wp_unslash( $_POST['gs_table_print_description_position'] ) ) : 'above';
		$extra_css_class = isset( $_POST['gs_table_extra_css_class'] ) ? sanitize_html_class( wp_unslash( $_POST['gs_table_extra_css_class'] ) ) : '';
		$visitor_features = isset( $_POST['gs_table_visitor_features'] ) ? 1 : 0;
		$search = isset( $_POST['gs_table_search'] ) ? 1 : 0;
		$pagination = isset( $_POST['gs_table_pagination'] ) ? 1 : 0;
		$pagination_length = isset( $_POST['gs_table_pagination_length'] ) ? max( 1, absint( wp_unslash( $_POST['gs_table_pagination_length'] ) ) ) : 10;
		$pagination_length_change = isset( $_POST['gs_table_pagination_length_change'] ) ? 1 : 0;
		$info = isset( $_POST['gs_table_info'] ) ? 1 : 0;
		$horizontal_scrolling = isset( $_POST['gs_table_horizontal_scrolling'] ) ? 1 : 0;
		$custom_commands = isset( $_POST['gs_table_custom_commands'] ) ? wp_kses_post( wp_unslash( $_POST['gs_table_custom_commands'] ) ) : '';
		$hidden_rows = isset( $_POST['gs_table_hidden_rows'] ) ? array_values( array_filter( array_map( 'absint', (array) wp_unslash( $_POST['gs_table_hidden_rows'] ) ) ) ) : array();
		$hidden_cols = isset( $_POST['gs_table_hidden_cols'] ) ? array_values( array_filter( array_map( 'absint', (array) wp_unslash( $_POST['gs_table_hidden_cols'] ) ) ) ) : array();
		$cell_spans = isset( $_POST['gs_table_cell_spans'] ) ? (array) wp_unslash( $_POST['gs_table_cell_spans'] ) : array();
		$normalized_spans = array();
		foreach ( $cell_spans as $span_key => $span_value ) {
			if ( ! is_array( $span_value ) ) {
				continue;
			}
			$rowspan = isset( $span_value['rowspan'] ) ? absint( $span_value['rowspan'] ) : 1;
			$colspan = isset( $span_value['colspan'] ) ? absint( $span_value['colspan'] ) : 1;
			$normalized_spans[ sanitize_key( (string) $span_key ) ] = array(
				'rowspan' => max( 1, $rowspan ),
				'colspan' => max( 1, $colspan ),
			);
		}

		update_post_meta( $post_id, '_gs_table_sortable', $sortable );
		update_post_meta( $post_id, '_gs_table_striped', $striped );
		update_post_meta( $post_id, '_gs_table_compact', $compact );
		update_post_meta( $post_id, '_gs_table_auto_link', $auto_link );
		update_post_meta( $post_id, self::META_FOOT_ROW, $foot_row );
		update_post_meta( $post_id, self::META_ROW_HOVER, $row_hover );
		update_post_meta( $post_id, self::META_PRINT_NAME, $print_name );
		update_post_meta( $post_id, self::META_PRINT_NAME_POSITION, in_array( $print_name_position, array( 'above', 'below' ), true ) ? $print_name_position : 'above' );
		update_post_meta( $post_id, self::META_PRINT_DESCRIPTION, $print_description );
		update_post_meta( $post_id, self::META_PRINT_DESCRIPTION_POSITION, in_array( $print_description_position, array( 'above', 'below' ), true ) ? $print_description_position : 'above' );
		update_post_meta( $post_id, self::META_EXTRA_CSS_CLASS, $extra_css_class );
		update_post_meta( $post_id, self::META_VISITOR_FEATURES, $visitor_features );
		update_post_meta( $post_id, self::META_SEARCH, $search );
		update_post_meta( $post_id, self::META_PAGINATION, $pagination );
		update_post_meta( $post_id, self::META_PAGINATION_LENGTH, $pagination_length );
		update_post_meta( $post_id, self::META_PAGINATION_LENGTH_CHANGE, $pagination_length_change );
		update_post_meta( $post_id, self::META_INFO, $info );
		update_post_meta( $post_id, self::META_HORIZONTAL_SCROLLING, $horizontal_scrolling );
		update_post_meta( $post_id, self::META_CUSTOM_COMMANDS, $custom_commands );
		update_post_meta( $post_id, self::META_HIDDEN_ROWS, $hidden_rows );
		update_post_meta( $post_id, self::META_HIDDEN_COLS, $hidden_cols );
		update_post_meta( $post_id, self::META_CELL_SPANS, $normalized_spans );
	}

	public static function maybe_enqueue_assets() {
		global $post;
		if ( ! is_a( $post, 'WP_Post' ) ) {
			return;
		}

		$has_gs_table = has_shortcode( $post->post_content, 'gs_table' );
		$has_legacy_table = has_shortcode( $post->post_content, 'table' );
		if ( ! $has_gs_table && ! $has_legacy_table ) {
			return;
		}

		wp_enqueue_style(
			'gs-table',
			Plugin::get( 'url' ) . 'css/tables.css',
			array(),
			Plugin::get( 'version' )
		);

		wp_enqueue_script(
			'gs-table-sort',
			Plugin::get( 'url' ) . 'js/tables-sort.js',
			array(),
			Plugin::get( 'version' ),
			true
		);
	}

	public static function render_shortcode( $atts, $content = '', $shortcode_tag = 'gs_table' ) {
		$atts = shortcode_atts(
			array(
				'id'            => '',
				'slug'          => '',
				'sortable'      => '',
				'auto_link'     => '',
				'show_hidden'   => '',
				'class'         => '',
				'empty_message' => __( 'Table data is not available at this time.', 'wsuwp-plugin-graduate-school' ),
			),
			$atts,
			$shortcode_tag
		);

		$post = self::resolve_table_post( $atts['id'], $atts['slug'], $shortcode_tag );

		if ( ! $post ) {
			return self::render_fallback( $atts['empty_message'] );
		}

		$headers = get_post_meta( $post->ID, '_gs_table_headers', true );
		$rows    = get_post_meta( $post->ID, '_gs_table_rows', true );
		$caption = get_post_meta( $post->ID, '_gs_table_caption', true );

		if ( ! is_array( $headers ) || empty( $headers ) || ! is_array( $rows ) || empty( $rows ) ) {
			return self::render_fallback( $atts['empty_message'] );
		}

		$saved_sortable = (bool) get_post_meta( $post->ID, '_gs_table_sortable', true );
		$saved_auto_link = self::get_saved_auto_link_setting( $post->ID );
		$striped        = (bool) get_post_meta( $post->ID, '_gs_table_striped', true );
		$compact        = (bool) get_post_meta( $post->ID, '_gs_table_compact', true );
		$foot_row       = (bool) get_post_meta( $post->ID, self::META_FOOT_ROW, true );
		$row_hover      = (bool) get_post_meta( $post->ID, self::META_ROW_HOVER, true );
		$print_name     = (bool) get_post_meta( $post->ID, self::META_PRINT_NAME, true );
		$print_name_position = (string) get_post_meta( $post->ID, self::META_PRINT_NAME_POSITION, true );
		$print_description = (bool) get_post_meta( $post->ID, self::META_PRINT_DESCRIPTION, true );
		$print_description_position = (string) get_post_meta( $post->ID, self::META_PRINT_DESCRIPTION_POSITION, true );
		$extra_css_class = sanitize_html_class( (string) get_post_meta( $post->ID, self::META_EXTRA_CSS_CLASS, true ) );
		$visitor_features = (bool) get_post_meta( $post->ID, self::META_VISITOR_FEATURES, true );
		$search = (bool) get_post_meta( $post->ID, self::META_SEARCH, true );
		$pagination = (bool) get_post_meta( $post->ID, self::META_PAGINATION, true );
		$pagination_length = max( 1, (int) get_post_meta( $post->ID, self::META_PAGINATION_LENGTH, true ) );
		$pagination_length_change = (bool) get_post_meta( $post->ID, self::META_PAGINATION_LENGTH_CHANGE, true );
		$info = (bool) get_post_meta( $post->ID, self::META_INFO, true );
		$horizontal_scrolling = (bool) get_post_meta( $post->ID, self::META_HORIZONTAL_SCROLLING, true );
		$custom_commands = (string) get_post_meta( $post->ID, self::META_CUSTOM_COMMANDS, true );
		$hidden_rows = array_map( 'absint', (array) get_post_meta( $post->ID, self::META_HIDDEN_ROWS, true ) );
		$hidden_cols = array_map( 'absint', (array) get_post_meta( $post->ID, self::META_HIDDEN_COLS, true ) );

		$sortable_override = self::normalize_bool( $atts['sortable'], null );
		$sortable          = is_null( $sortable_override ) ? $saved_sortable : $sortable_override;
		$auto_link_override = self::normalize_bool( $atts['auto_link'], null );
		$auto_link          = is_null( $auto_link_override ) ? $saved_auto_link : $auto_link_override;
		$show_hidden_override = self::normalize_bool( $atts['show_hidden'], false );

		$classes = array( 'gs-table-wrap' );
		if ( $striped ) {
			$classes[] = 'gs-table-wrap--striped';
		}
		if ( $compact ) {
			$classes[] = 'gs-table-wrap--compact';
		}
		if ( $row_hover ) {
			$classes[] = 'gs-table-wrap--row-hover';
		}
		if ( $horizontal_scrolling ) {
			$classes[] = 'gs-table-wrap--horizontal-scroll';
		}
		if ( '' !== $extra_css_class ) {
			$classes[] = $extra_css_class;
		}

		$raw_classes   = preg_split( '/\s+/', (string) $atts['class'] );
		$extra_classes = array();
		if ( is_array( $raw_classes ) ) {
			foreach ( $raw_classes as $raw_class ) {
				$sanitized_class = sanitize_html_class( $raw_class );
				if ( '' !== $sanitized_class ) {
					$extra_classes[] = $sanitized_class;
				}
			}
		}
		$classes       = array_merge( $classes, $extra_classes );
		$table_attr    = $sortable ? ' data-gs-sortable="1"' : '';

		ob_start();
		?>
		<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<?php if ( $print_name && 'above' === $print_name_position ) : ?>
				<div class="gs-table-print-name"><?php echo esc_html( get_the_title( $post ) ); ?></div>
			<?php endif; ?>
			<?php if ( $print_description && 'above' === $print_description_position && ! empty( $caption ) ) : ?>
				<div class="gs-table-print-description"><?php echo esc_html( $caption ); ?></div>
			<?php endif; ?>
			<div class="gs-table-scroll">
				<table class="gs-table"<?php echo $table_attr; ?> data-gs-table-id="<?php echo esc_attr( (string) $post->ID ); ?>">
					<?php if ( ! empty( $caption ) ) : ?>
						<caption><?php echo esc_html( $caption ); ?></caption>
					<?php endif; ?>
					<thead>
						<tr>
							<?php foreach ( $headers as $index => $header ) : ?>
								<?php if ( ! $show_hidden_override && in_array( (int) $index, $hidden_cols, true ) ) { continue; } ?>
								<?php $is_sortable_col = $sortable && '' !== trim( $header ); ?>
								<th scope="col" <?php echo $is_sortable_col ? 'data-gs-sort-col="' . esc_attr( (string) $index ) . '"' : ''; ?>>
									<?php echo esc_html( $header ); ?>
									<?php if ( $is_sortable_col ) : ?>
										<span class="gs-table-sort-indicator" aria-hidden="true"></span>
									<?php endif; ?>
								</th>
							<?php endforeach; ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $rows as $row_index => $row ) : ?>
							<?php if ( ( ! $show_hidden_override && in_array( (int) $row_index, $hidden_rows, true ) ) || ! is_array( $row ) ) { continue; } ?>
							<tr>
								<?php foreach ( $headers as $i => $unused_header ) : ?>
									<?php if ( ! $show_hidden_override && in_array( (int) $i, $hidden_cols, true ) ) { continue; } ?>
									<td><?php echo self::render_cell_value( isset( $row[ $i ] ) ? $row[ $i ] : '', $auto_link ); ?></td>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
					</tbody>
					<?php if ( $foot_row && ! empty( $headers ) ) : ?>
						<tfoot>
							<tr>
								<?php foreach ( $headers as $index => $header ) : ?>
									<?php if ( ! $show_hidden_override && in_array( (int) $index, $hidden_cols, true ) ) { continue; } ?>
									<th scope="col"><?php echo esc_html( $header ); ?></th>
								<?php endforeach; ?>
							</tr>
						</tfoot>
					<?php endif; ?>
				</table>
			</div>
			<?php if ( $print_name && 'below' === $print_name_position ) : ?>
				<div class="gs-table-print-name"><?php echo esc_html( get_the_title( $post ) ); ?></div>
			<?php endif; ?>
			<?php if ( $print_description && 'below' === $print_description_position && ! empty( $caption ) ) : ?>
				<div class="gs-table-print-description"><?php echo esc_html( $caption ); ?></div>
			<?php endif; ?>
		</div>
		<?php if ( $visitor_features ) : ?>
			<script>
				window.gsTableConfig = window.gsTableConfig || {};
				window.gsTableConfig[<?php echo wp_json_encode( (string) $post->ID ); ?>] = {
					search: <?php echo $search ? 'true' : 'false'; ?>,
					paging: <?php echo $pagination ? 'true' : 'false'; ?>,
					pageLength: <?php echo (int) $pagination_length; ?>,
					lengthChange: <?php echo $pagination_length_change ? 'true' : 'false'; ?>,
					info: <?php echo $info ? 'true' : 'false'; ?>,
					customCommands: <?php echo wp_json_encode( $custom_commands ); ?>
				};
			</script>
		<?php endif; ?>
		<?php
		return ob_get_clean();
	}

	private static function resolve_table_post( $id_attr, $slug_attr, $shortcode_tag ) {
		$id_raw = is_scalar( $id_attr ) ? trim( (string) $id_attr ) : '';
		$slug   = sanitize_title( $slug_attr );
		$post   = null;

		// Legacy [table id="x"] should prioritize legacy ID mapping.
		if ( 'table' === $shortcode_tag && '' !== $id_raw ) {
			$post = self::get_gs_table_by_legacy_id( $id_raw );
		}

		// Resolve direct gs-table post ID when numeric.
		if ( ! $post && '' !== $id_raw && ctype_digit( $id_raw ) ) {
			$maybe_post = get_post( (int) $id_raw );
			if ( $maybe_post instanceof \WP_Post && self::POST_TYPE === $maybe_post->post_type && self::can_render_post( $maybe_post ) ) {
				$post = $maybe_post;
			}
		}

		// Resolve gs-table slug.
		if ( ! $post && '' !== $slug ) {
			$maybe_post = get_page_by_path( $slug, OBJECT, self::POST_TYPE );
			if ( $maybe_post instanceof \WP_Post && self::can_render_post( $maybe_post ) ) {
				$post = $maybe_post;
			}
		}

		// For [gs_table], also allow legacy ID as fallback.
		if ( ! $post && 'gs_table' === $shortcode_tag && '' !== $id_raw ) {
			$post = self::get_gs_table_by_legacy_id( $id_raw );
		}

		return $post;
	}

	private static function render_fallback( $message ) {
		return sprintf(
			'<div class="gs-table-fallback" role="status">%s</div>',
			esc_html( $message )
		);
	}

	private static function normalize_bool( $value, $default = false ) {
		if ( '' === $value || null === $value ) {
			return $default;
		}
		$normalized = strtolower( trim( (string) $value ) );
		if ( in_array( $normalized, array( '1', 'true', 'yes', 'on' ), true ) ) {
			return true;
		}
		if ( in_array( $normalized, array( '0', 'false', 'no', 'off' ), true ) ) {
			return false;
		}
		return $default;
	}

	private static function get_saved_auto_link_setting( $post_id ) {
		if ( ! metadata_exists( 'post', $post_id, '_gs_table_auto_link' ) ) {
			return true;
		}
		return (bool) get_post_meta( $post_id, '_gs_table_auto_link', true );
	}

	private static function render_cell_value( $value, $auto_link ) {
		$value = (string) $value;
		$token_link = self::decode_link_token( $value );
		if ( $token_link ) {
			return sprintf(
				'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
				esc_url( $token_link['url'] ),
				esc_html( $token_link['label'] )
			);
		}

		if ( ! $auto_link ) {
			return esc_html( $value );
		}

		$valid_url = self::get_valid_cell_url( $value );
		if ( ! $valid_url ) {
			return esc_html( $value );
		}

		return sprintf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
			esc_url( $valid_url ),
			esc_html( $value )
		);
	}

	private static function get_valid_cell_url( $value ) {
		$value = trim( (string) $value );
		if ( '' === $value ) {
			return false;
		}

		$maybe_url = $value;
		if ( ! preg_match( '#^https?://#i', $maybe_url ) && preg_match( '/^[A-Za-z0-9.-]+\.[A-Za-z]{2,}(\/.*)?$/', $maybe_url ) ) {
			$maybe_url = 'https://' . $maybe_url;
		}

		$validated_url = wp_http_validate_url( $maybe_url );
		if ( false === $validated_url ) {
			return false;
		}

		return $validated_url;
	}

	private static function encode_link_token( $label, $url ) {
		$payload = array(
			'label' => (string) $label,
			'url'   => (string) $url,
		);
		$json = wp_json_encode( $payload );
		if ( ! is_string( $json ) || '' === $json ) {
			return '';
		}
		return self::LINK_TOKEN_PREFIX . base64_encode( $json );
	}

	private static function decode_link_token( $value ) {
		$value = (string) $value;
		if ( 0 !== strpos( $value, self::LINK_TOKEN_PREFIX ) ) {
			return null;
		}

		$encoded = substr( $value, strlen( self::LINK_TOKEN_PREFIX ) );
		if ( '' === $encoded ) {
			return null;
		}

		$decoded = base64_decode( $encoded, true );
		if ( false === $decoded || '' === $decoded ) {
			return null;
		}

		$data = json_decode( $decoded, true );
		if ( ! is_array( $data ) || empty( $data['url'] ) ) {
			return null;
		}

		$url = esc_url_raw( (string) $data['url'] );
		$url = wp_http_validate_url( $url );
		if ( false === $url ) {
			return null;
		}

		$label = isset( $data['label'] ) ? sanitize_text_field( (string) $data['label'] ) : '';
		if ( '' === $label ) {
			$label = $url;
		}

		return array(
			'label' => $label,
			'url'   => $url,
		);
	}

	private static function normalize_editor_cell( $value ) {
		if ( is_array( $value ) ) {
			$text = isset( $value['text'] ) ? sanitize_text_field( (string) $value['text'] ) : '';
			$link_text = isset( $value['link_text'] ) ? sanitize_text_field( (string) $value['link_text'] ) : '';
			$url = isset( $value['url'] ) ? self::sanitize_editor_url( (string) $value['url'] ) : '';
			if ( '' !== $link_text ) {
				$text = $link_text;
			}
			return array(
				'text' => $text,
				'url'  => $url,
			);
		}

		$value = (string) $value;
		$token_link = self::decode_link_token( $value );
		if ( $token_link ) {
			return array(
				'text' => $token_link['label'],
				'url'  => $token_link['url'],
			);
		}

		return array(
			'text' => sanitize_text_field( $value ),
			'url'  => '',
		);
	}

	public static function get_editor_cell_data( $value ) {
		return self::normalize_editor_cell( $value );
	}

	private static function sanitize_editor_cell( $value ) {
		$cell = self::normalize_editor_cell( $value );
		if ( '' !== $cell['url'] ) {
			return self::encode_link_token( $cell['text'], $cell['url'] );
		}
		return $cell['text'];
	}

	private static function sanitize_editor_url( $url ) {
		$url = trim( (string) $url );
		if ( '' === $url ) {
			return '';
		}
		$url = esc_url_raw( $url );
		$validated = wp_http_validate_url( $url );
		return false === $validated ? '' : $validated;
	}

	private static function can_render_post( $post ) {
		if ( ! $post instanceof \WP_Post ) {
			return false;
		}
		if ( 'publish' === $post->post_status ) {
			return true;
		}
		return current_user_can( self::CAP_EDIT );
	}

	private static function get_gs_table_by_legacy_id( $legacy_id ) {
		$legacy_id = trim( (string) $legacy_id );
		if ( '' === $legacy_id ) {
			return null;
		}

		$posts = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
				'posts_per_page' => 1,
				'meta_key'       => self::META_LEGACY_ID,
				'meta_value'     => $legacy_id,
			)
		);

		if ( empty( $posts ) || ! isset( $posts[0] ) || ! $posts[0] instanceof \WP_Post ) {
			return null;
		}
		return self::can_render_post( $posts[0] ) ? $posts[0] : null;
	}

	public static function get_table_record( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post instanceof \WP_Post || self::POST_TYPE !== $post->post_type ) {
			return null;
		}

		return array(
			'post' => $post,
			'caption' => (string) get_post_meta( $post_id, '_gs_table_caption', true ),
			'headers' => (array) get_post_meta( $post_id, '_gs_table_headers', true ),
			'rows' => (array) get_post_meta( $post_id, '_gs_table_rows', true ),
			'sortable' => (bool) get_post_meta( $post_id, '_gs_table_sortable', true ),
			'striped' => (bool) get_post_meta( $post_id, '_gs_table_striped', true ),
			'compact' => (bool) get_post_meta( $post_id, '_gs_table_compact', true ),
			'auto_link' => self::get_saved_auto_link_setting( $post_id ),
			'foot_row' => (bool) get_post_meta( $post_id, self::META_FOOT_ROW, true ),
			'row_hover' => (bool) get_post_meta( $post_id, self::META_ROW_HOVER, true ),
			'print_name' => (bool) get_post_meta( $post_id, self::META_PRINT_NAME, true ),
			'print_name_position' => (string) get_post_meta( $post_id, self::META_PRINT_NAME_POSITION, true ),
			'print_description' => (bool) get_post_meta( $post_id, self::META_PRINT_DESCRIPTION, true ),
			'print_description_position' => (string) get_post_meta( $post_id, self::META_PRINT_DESCRIPTION_POSITION, true ),
			'extra_css_class' => (string) get_post_meta( $post_id, self::META_EXTRA_CSS_CLASS, true ),
			'visitor_features' => (bool) get_post_meta( $post_id, self::META_VISITOR_FEATURES, true ),
			'search' => (bool) get_post_meta( $post_id, self::META_SEARCH, true ),
			'pagination' => (bool) get_post_meta( $post_id, self::META_PAGINATION, true ),
			'pagination_length' => max( 1, (int) get_post_meta( $post_id, self::META_PAGINATION_LENGTH, true ) ),
			'pagination_length_change' => (bool) get_post_meta( $post_id, self::META_PAGINATION_LENGTH_CHANGE, true ),
			'info' => (bool) get_post_meta( $post_id, self::META_INFO, true ),
			'horizontal_scrolling' => (bool) get_post_meta( $post_id, self::META_HORIZONTAL_SCROLLING, true ),
			'custom_commands' => (string) get_post_meta( $post_id, self::META_CUSTOM_COMMANDS, true ),
			'hidden_rows' => (array) get_post_meta( $post_id, self::META_HIDDEN_ROWS, true ),
			'hidden_cols' => (array) get_post_meta( $post_id, self::META_HIDDEN_COLS, true ),
			'cell_spans' => (array) get_post_meta( $post_id, self::META_CELL_SPANS, true ),
		);
	}

	public static function duplicate_table( $post_id ) {
		$record = self::get_table_record( $post_id );
		if ( ! $record ) {
			return new \WP_Error( 'gs_table_not_found', __( 'Table not found.', 'wsuwp-plugin-graduate-school' ) );
		}

		$new_post_id = wp_insert_post(
			array(
				'post_type' => self::POST_TYPE,
				'post_status' => 'draft',
				'post_title' => $record['post']->post_title . ' (Copy)',
				'post_content' => '',
			),
			true
		);
		if ( is_wp_error( $new_post_id ) ) {
			return $new_post_id;
		}

		$all_meta = get_post_meta( $post_id );
		foreach ( $all_meta as $meta_key => $meta_values ) {
			foreach ( $meta_values as $meta_value ) {
				add_post_meta( $new_post_id, $meta_key, maybe_unserialize( $meta_value ) );
			}
		}
		return $new_post_id;
	}

	public static function delete_table( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post instanceof \WP_Post || self::POST_TYPE !== $post->post_type ) {
			return false;
		}
		return (bool) wp_delete_post( $post_id, true );
	}

	public static function update_table_options( $post_id, $options ) {
		if ( ! is_array( $options ) ) {
			return;
		}
		foreach ( $options as $meta_key => $meta_value ) {
			update_post_meta( $post_id, $meta_key, $meta_value );
		}
	}

	public static function register_tools_page() {
		add_submenu_page(
			'edit.php?post_type=' . self::POST_TYPE,
			__( 'Table Preview', 'wsuwp-plugin-graduate-school' ),
			__( 'Table Preview', 'wsuwp-plugin-graduate-school' ),
			self::CAP_EDIT,
			self::PREVIEW_SLUG,
			array( __CLASS__, 'render_preview_page' )
		);

		add_submenu_page(
			'edit.php?post_type=' . self::POST_TYPE,
			__( 'Table Migration & Export', 'wsuwp-plugin-graduate-school' ),
			__( 'Migration / Export', 'wsuwp-plugin-graduate-school' ),
			self::CAP_EDIT,
			self::TOOLS_SLUG,
			array( __CLASS__, 'render_tools_page' )
		);
	}

	public static function maybe_enqueue_preview_assets() {
		if ( ! is_admin() ) {
			return;
		}

		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		$post_type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : '';
		if ( self::PREVIEW_SLUG !== $page || self::POST_TYPE !== $post_type ) {
			return;
		}

		wp_enqueue_style(
			'gs-table',
			Plugin::get( 'url' ) . 'css/tables.css',
			array(),
			Plugin::get( 'version' )
		);

		wp_enqueue_script(
			'gs-table-sort',
			Plugin::get( 'url' ) . 'js/tables-sort.js',
			array(),
			Plugin::get( 'version' ),
			true
		);
	}

	public static function render_preview_page() {
		if ( ! current_user_can( self::CAP_EDIT ) ) {
			wp_die( esc_html__( 'You do not have permission to preview tables.', 'wsuwp-plugin-graduate-school' ) );
		}

		$table_id = isset( $_GET['table_id'] ) ? absint( wp_unslash( $_GET['table_id'] ) ) : 0;
		$nonce    = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		$valid_request = $table_id > 0 && wp_verify_nonce( $nonce, 'gs_table_preview_' . $table_id );

		$content = '';
		if ( ! $valid_request ) {
			$content = '<div class="notice notice-warning inline"><p>' . esc_html__( 'Open preview from a table edit screen to generate a secure preview link.', 'wsuwp-plugin-graduate-school' ) . '</p></div>';
		} else {
			$post = get_post( $table_id );
			if ( ! $post instanceof \WP_Post || self::POST_TYPE !== $post->post_type ) {
				$content = self::render_fallback( __( 'This table could not be found.', 'wsuwp-plugin-graduate-school' ) );
			} elseif ( ! self::can_render_post( $post ) ) {
				$content = self::render_fallback( __( 'You do not have permission to preview this table.', 'wsuwp-plugin-graduate-school' ) );
			} else {
				$content = self::render_shortcode(
					array(
						'id' => (string) $table_id,
					),
					'',
					'gs_table'
				);
			}
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Table Preview', 'wsuwp-plugin-graduate-school' ); ?></h1>
			<div class="gs-table-preview-wrap">
				<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
		<?php
	}

	public static function render_tools_page() {
		if ( ! current_user_can( self::CAP_EDIT ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wsuwp-plugin-graduate-school' ) );
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Table Migration & Export', 'wsuwp-plugin-graduate-school' ); ?></h1>
			<p><?php esc_html_e( 'Import from TablePress for seamless shortcode migration, or export gs-table records for backup and portability.', 'wsuwp-plugin-graduate-school' ); ?></p>

			<h2><?php esc_html_e( 'Import TablePress Tables', 'wsuwp-plugin-graduate-school' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'gs_tables_import_tablepress', 'gs_tables_import_nonce' ); ?>
				<input type="hidden" name="action" value="gs_tables_import_tablepress" />
				<p>
					<label>
						<input type="checkbox" name="overwrite_existing" value="1" />
						<?php esc_html_e( 'Overwrite existing mapped tables with latest TablePress data', 'wsuwp-plugin-graduate-school' ); ?>
					</label>
				</p>
				<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Run Import', 'wsuwp-plugin-graduate-school' ); ?></button></p>
			</form>

			<hr />

			<h2><?php esc_html_e( 'Export gs-table Data', 'wsuwp-plugin-graduate-school' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'gs_tables_export', 'gs_tables_export_nonce' ); ?>
				<input type="hidden" name="action" value="gs_tables_export" />
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="gs-export-format"><?php esc_html_e( 'Format', 'wsuwp-plugin-graduate-school' ); ?></label></th>
						<td>
							<select id="gs-export-format" name="format">
								<option value="csv"><?php esc_html_e( 'CSV', 'wsuwp-plugin-graduate-school' ); ?></option>
								<option value="json"><?php esc_html_e( 'JSON', 'wsuwp-plugin-graduate-school' ); ?></option>
							</select>
						</td>
					</tr>
				</table>
				<p><button type="submit" class="button"><?php esc_html_e( 'Download Export', 'wsuwp-plugin-graduate-school' ); ?></button></p>
			</form>
		</div>
		<?php
	}

	public static function maybe_render_status_notice() {
		if ( ! is_admin() || ! current_user_can( self::CAP_EDIT ) ) {
			return;
		}

		if ( ! isset( $_GET['post_type'] ) || self::POST_TYPE !== sanitize_key( wp_unslash( $_GET['post_type'] ) ) ) {
			return;
		}

		if ( empty( $_GET['gs_tables_notice'] ) ) {
			return;
		}

		$notice = sanitize_key( wp_unslash( $_GET['gs_tables_notice'] ) );
		$class  = 'notice notice-info';
		$text   = '';

		if ( 'import_success' === $notice ) {
			$created = isset( $_GET['created'] ) ? (int) $_GET['created'] : 0;
			$updated = isset( $_GET['updated'] ) ? (int) $_GET['updated'] : 0;
			$skipped = isset( $_GET['skipped'] ) ? (int) $_GET['skipped'] : 0;
			$text = sprintf(
				/* translators: 1: created count, 2: updated count, 3: skipped count */
				__( 'TablePress import completed. Created: %1$d, Updated: %2$d, Skipped: %3$d.', 'wsuwp-plugin-graduate-school' ),
				$created,
				$updated,
				$skipped
			);
		} elseif ( 'import_error' === $notice ) {
			$class = 'notice notice-error';
			$text = __( 'TablePress import failed. Please verify TablePress is active and try again.', 'wsuwp-plugin-graduate-school' );
		}

		if ( '' === $text ) {
			return;
		}
		?>
		<div class="<?php echo esc_attr( $class ); ?> is-dismissible"><p><?php echo esc_html( $text ); ?></p></div>
		<?php
	}

	public static function handle_import_tablepress() {
		if ( ! current_user_can( self::CAP_EDIT ) ) {
			wp_die( esc_html__( 'You do not have permission to import tables.', 'wsuwp-plugin-graduate-school' ) );
		}

		check_admin_referer( 'gs_tables_import_tablepress', 'gs_tables_import_nonce' );

		if ( ! class_exists( 'TablePress' ) || ! isset( \TablePress::$model_table ) ) {
			self::redirect_with_notice( 'import_error' );
		}

		$overwrite = isset( $_POST['overwrite_existing'] );
		$created = 0;
		$updated = 0;
		$skipped = 0;

		$table_ids = \TablePress::$model_table->load_all( false );
		foreach ( $table_ids as $table_id ) {
			$table = \TablePress::$model_table->load( $table_id, true, true );
			if ( ! is_array( $table ) || empty( $table['data'] ) || ! is_array( $table['data'] ) ) {
				$skipped++;
				continue;
			}

			$mapped = self::map_tablepress_table_to_gs( $table, (string) $table_id );
			if ( empty( $mapped['headers'] ) ) {
				$skipped++;
				continue;
			}

			$post = self::get_gs_table_by_legacy_id( (string) $table_id );
			$is_update = $post instanceof \WP_Post;
			if ( $is_update && ! $overwrite ) {
				$skipped++;
				continue;
			}

			$post_data = array(
				'post_type'   => self::POST_TYPE,
				'post_status' => 'publish',
				'post_title'  => $mapped['title'],
				'post_name'   => sanitize_title( $mapped['title'] ),
			);
			if ( $is_update ) {
				$post_data['ID'] = $post->ID;
				$post_id = wp_update_post( $post_data, true );
			} else {
				$post_id = wp_insert_post( $post_data, true );
			}

			if ( is_wp_error( $post_id ) || ! $post_id ) {
				$skipped++;
				continue;
			}

			update_post_meta( $post_id, '_gs_table_caption', $mapped['caption'] );
			update_post_meta( $post_id, '_gs_table_headers', $mapped['headers'] );
			update_post_meta( $post_id, '_gs_table_rows', $mapped['rows'] );
			update_post_meta( $post_id, '_gs_table_sortable', $mapped['sortable'] ? 1 : 0 );
			update_post_meta( $post_id, '_gs_table_striped', 0 );
			update_post_meta( $post_id, '_gs_table_compact', 0 );
			update_post_meta( $post_id, '_gs_table_auto_link', 1 );
			update_post_meta( $post_id, self::META_LEGACY_SOURCE, 'tablepress' );
			update_post_meta( $post_id, self::META_LEGACY_ID, (string) $table_id );

			if ( $is_update ) {
				$updated++;
			} else {
				$created++;
			}
		}

		self::redirect_with_notice(
			'import_success',
			array(
				'created' => $created,
				'updated' => $updated,
				'skipped' => $skipped,
			)
		);
	}

	private static function map_tablepress_table_to_gs( $table, $table_id ) {
		$data = isset( $table['data'] ) && is_array( $table['data'] ) ? $table['data'] : array();
		$options = isset( $table['options'] ) && is_array( $table['options'] ) ? $table['options'] : array();

		$headers = array();
		$rows = $data;
		$has_head = ! empty( $options['table_head'] );
		if ( $has_head && ! empty( $rows ) ) {
			$headers = array_shift( $rows );
		} elseif ( ! empty( $rows ) && is_array( $rows[0] ) ) {
			$column_count = count( $rows[0] );
			for ( $i = 1; $i <= $column_count; $i++ ) {
				$headers[] = sprintf( __( 'Column %d', 'wsuwp-plugin-graduate-school' ), $i );
			}
		}

		$headers = array_values( array_map( 'sanitize_text_field', is_array( $headers ) ? $headers : array() ) );
		$sanitized_rows = array();
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$sanitized_row = array();
			foreach ( $row as $cell ) {
				$sanitized_row[] = self::sanitize_import_cell( $cell );
			}
			$sanitized_rows[] = $sanitized_row;
		}

		$title = ! empty( $table['name'] ) ? sanitize_text_field( $table['name'] ) : sprintf( __( 'Imported Table %s', 'wsuwp-plugin-graduate-school' ), $table_id );
		$caption = ! empty( $table['description'] ) ? sanitize_text_field( $table['description'] ) : '';
		$sortable = ! empty( $options['use_datatables'] );

		return array(
			'title'    => $title,
			'caption'  => $caption,
			'headers'  => $headers,
			'rows'     => $sanitized_rows,
			'sortable' => (bool) $sortable,
		);
	}

	private static function sanitize_import_cell( $cell ) {
		$cell = (string) $cell;
		$parsed_link = self::extract_anchor_from_import_cell( $cell );
		if ( $parsed_link ) {
			return self::encode_link_token( $parsed_link['label'], $parsed_link['url'] );
		}
		return sanitize_text_field( wp_strip_all_tags( $cell ) );
	}

	private static function extract_anchor_from_import_cell( $cell ) {
		$cell = trim( (string) $cell );
		if ( '' === $cell || false === stripos( $cell, '<a' ) ) {
			return null;
		}

		if ( ! preg_match( '/<a\b[^>]*href\s*=\s*([\'"])(.*?)\1[^>]*>(.*?)<\/a>/is', $cell, $matches ) ) {
			return null;
		}

		$url = isset( $matches[2] ) ? (string) $matches[2] : '';
		$url = esc_url_raw( $url );
		$url = wp_http_validate_url( $url );
		if ( false === $url ) {
			return null;
		}

		$label_html = isset( $matches[3] ) ? (string) $matches[3] : '';
		$label = sanitize_text_field( wp_strip_all_tags( $label_html ) );
		if ( '' === $label ) {
			$label = $url;
		}

		return array(
			'url'   => $url,
			'label' => $label,
		);
	}

	public static function handle_export_tables() {
		if ( ! current_user_can( self::CAP_EDIT ) ) {
			wp_die( esc_html__( 'You do not have permission to export tables.', 'wsuwp-plugin-graduate-school' ) );
		}
		check_admin_referer( 'gs_tables_export', 'gs_tables_export_nonce' );

		$format = isset( $_POST['format'] ) ? sanitize_key( wp_unslash( $_POST['format'] ) ) : 'csv';
		$csv_delimiter = isset( $_POST['csv_delimiter'] ) ? sanitize_key( wp_unslash( $_POST['csv_delimiter'] ) ) : 'comma';
		$selected_ids = isset( $_POST['table_ids'] ) ? array_values( array_filter( array_map( 'absint', (array) wp_unslash( $_POST['table_ids'] ) ) ) ) : array();
		$zip_requested = isset( $_POST['zip'] ) ? 1 : 0;
		$tables = self::get_all_tables_for_export( $selected_ids );
		$delimiter = ',';
		if ( 'semicolon' === $csv_delimiter ) {
			$delimiter = ';';
		} elseif ( 'tabulator' === $csv_delimiter ) {
			$delimiter = "\t";
		}

		if ( 'json' === $format ) {
			if ( $zip_requested || count( $tables ) > 1 ) {
				self::stream_zip_export( $tables, 'json', $delimiter );
			}
			self::stream_json_export( $tables );
		}

		if ( 'html' === $format ) {
			if ( $zip_requested || count( $tables ) > 1 ) {
				self::stream_zip_export( $tables, 'html', $delimiter );
			}
			self::stream_html_export( $tables );
		}

		if ( $zip_requested || count( $tables ) > 1 ) {
			self::stream_zip_export( $tables, 'csv', $delimiter );
		}
		self::stream_csv_export( $tables, $delimiter );
	}

	private static function get_all_tables_for_export( $selected_ids = array() ) {
		$query_args = array(
			'post_type'      => self::POST_TYPE,
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		);
		if ( ! empty( $selected_ids ) ) {
			$query_args['post__in'] = $selected_ids;
			$query_args['orderby'] = 'post__in';
		}

		$posts = get_posts(
			$query_args
		);

		$tables = array();
		foreach ( $posts as $post ) {
			$tables[] = array(
				'id'            => (int) $post->ID,
				'slug'          => $post->post_name,
				'title'         => $post->post_title,
				'caption'       => (string) get_post_meta( $post->ID, '_gs_table_caption', true ),
				'headers'       => (array) get_post_meta( $post->ID, '_gs_table_headers', true ),
				'rows'          => (array) get_post_meta( $post->ID, '_gs_table_rows', true ),
				'sortable'      => (bool) get_post_meta( $post->ID, '_gs_table_sortable', true ),
				'striped'       => (bool) get_post_meta( $post->ID, '_gs_table_striped', true ),
				'compact'       => (bool) get_post_meta( $post->ID, '_gs_table_compact', true ),
				'auto_link'     => self::get_saved_auto_link_setting( $post->ID ),
				'legacy_source' => (string) get_post_meta( $post->ID, self::META_LEGACY_SOURCE, true ),
				'legacy_id'     => (string) get_post_meta( $post->ID, self::META_LEGACY_ID, true ),
			);
		}
		return $tables;
	}

	private static function stream_json_export( $tables ) {
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=gs-tables-export-' . gmdate( 'Ymd-His' ) . '.json' );
		echo wp_json_encode(
			array(
				'generated_at' => gmdate( 'c' ),
				'tables'       => $tables,
			),
			JSON_PRETTY_PRINT
		);
		exit;
	}

	private static function stream_html_export( $tables ) {
		nocache_headers();
		header( 'Content-Type: text/html; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=gs-tables-export-' . gmdate( 'Ymd-His' ) . '.html' );
		echo '<!doctype html><html><head><meta charset="utf-8"><title>gs table export</title></head><body>';
		foreach ( $tables as $table ) {
			echo '<h2>' . esc_html( $table['title'] ) . '</h2>';
			echo '<table border="1"><thead><tr>';
			foreach ( (array) $table['headers'] as $header ) {
				echo '<th>' . esc_html( $header ) . '</th>';
			}
			echo '</tr></thead><tbody>';
			foreach ( (array) $table['rows'] as $row ) {
				echo '<tr>';
				foreach ( (array) $row as $cell ) {
					echo '<td>' . esc_html( is_scalar( $cell ) ? (string) $cell : wp_json_encode( $cell ) ) . '</td>';
				}
				echo '</tr>';
			}
			echo '</tbody></table>';
		}
		echo '</body></html>';
		exit;
	}

	private static function stream_csv_export( $tables, $delimiter = ',' ) {
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=gs-tables-export-' . gmdate( 'Ymd-His' ) . '.csv' );

		$output = fopen( 'php://output', 'w' );
		fputcsv( $output, array( 'table_id', 'slug', 'title', 'caption', 'legacy_source', 'legacy_id', 'headers_json', 'row_json' ), $delimiter );
		foreach ( $tables as $table ) {
			if ( empty( $table['rows'] ) ) {
				fputcsv(
					$output,
					array(
						$table['id'],
						$table['slug'],
						$table['title'],
						$table['caption'],
						$table['legacy_source'],
						$table['legacy_id'],
						wp_json_encode( $table['headers'] ),
						wp_json_encode( array() ),
					)
					,
					$delimiter
				);
				continue;
			}
			foreach ( $table['rows'] as $row ) {
				fputcsv(
					$output,
					array(
						$table['id'],
						$table['slug'],
						$table['title'],
						$table['caption'],
						$table['legacy_source'],
						$table['legacy_id'],
						wp_json_encode( $table['headers'] ),
						wp_json_encode( $row ),
					)
					,
					$delimiter
				);
			}
		}

		fclose( $output );
		exit;
	}

	private static function stream_zip_export( $tables, $format, $delimiter = ',' ) {
		if ( ! class_exists( 'ZipArchive' ) ) {
			if ( 'json' === $format ) {
				self::stream_json_export( $tables );
			}
			if ( 'html' === $format ) {
				self::stream_html_export( $tables );
			}
			self::stream_csv_export( $tables, $delimiter );
		}

		$tmp_file = wp_tempnam( 'gs-tables-export.zip' );
		$zip = new \ZipArchive();
		if ( true !== $zip->open( $tmp_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE ) ) {
			wp_die( esc_html__( 'Could not create ZIP export file.', 'wsuwp-plugin-graduate-school' ) );
		}

		foreach ( $tables as $table ) {
			$safe_title = sanitize_file_name( ! empty( $table['title'] ) ? $table['title'] : 'table-' . $table['id'] );
			if ( 'json' === $format ) {
				$zip->addFromString(
					$safe_title . '.json',
					wp_json_encode( $table, JSON_PRETTY_PRINT )
				);
				continue;
			}
			if ( 'html' === $format ) {
				$html = '<table><thead><tr>';
				foreach ( (array) $table['headers'] as $header ) {
					$html .= '<th>' . esc_html( $header ) . '</th>';
				}
				$html .= '</tr></thead><tbody>';
				foreach ( (array) $table['rows'] as $row ) {
					$html .= '<tr>';
					foreach ( (array) $row as $cell ) {
						$html .= '<td>' . esc_html( is_scalar( $cell ) ? (string) $cell : wp_json_encode( $cell ) ) . '</td>';
					}
					$html .= '</tr>';
				}
				$html .= '</tbody></table>';
				$zip->addFromString( $safe_title . '.html', $html );
				continue;
			}

			$handle = fopen( 'php://temp', 'w+' );
			fputcsv( $handle, (array) $table['headers'], $delimiter );
			foreach ( (array) $table['rows'] as $row ) {
				fputcsv( $handle, (array) $row, $delimiter );
			}
			rewind( $handle );
			$csv_content = stream_get_contents( $handle );
			fclose( $handle );
			$zip->addFromString( $safe_title . '.csv', (string) $csv_content );
		}

		$zip->close();
		nocache_headers();
		header( 'Content-Type: application/zip' );
		header( 'Content-Disposition: attachment; filename=gs-tables-export-' . gmdate( 'Ymd-His' ) . '.zip' );
		readfile( $tmp_file );
		@unlink( $tmp_file );
		exit;
	}

	private static function redirect_with_notice( $notice, $args = array() ) {
		$target_page = class_exists( __NAMESPACE__ . '\\Tables_Admin' ) ? Tables_Admin::IMPORT_SLUG : self::TOOLS_SLUG;
		$query = array_merge(
			array(
				'page'             => $target_page,
				'gs_tables_notice' => $notice,
			),
			$args
		);
		wp_safe_redirect( add_query_arg( $query, admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function get_preview_url( $table_id ) {
		$table_id = absint( $table_id );
		$url = add_query_arg(
			array(
				'page'      => Tables_Admin::MENU_SLUG,
				'action'    => 'preview',
				'table_id'  => $table_id,
			),
			admin_url( 'admin.php' )
		);

		return wp_nonce_url( $url, 'gs_table_preview_' . $table_id );
	}

	public static function print_admin_editor_script() {
		$screen = get_current_screen();
		if ( ! $screen || self::POST_TYPE !== $screen->post_type ) {
			return;
		}
		?>
		<style>
			.gs-editor-section { margin: 0 0 16px; }
			.gs-editor-section-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
			.gs-editor-section-head h3 { margin: 0; font-size: 13px; line-height: 1.4; }
			.gs-editor-label { display: block; margin-bottom: 4px; font-weight: 600; }
			.gs-table-inline-row { display: grid; grid-template-columns: 1fr auto; gap: 6px; margin-bottom: 6px; align-items: center; }
			.gs-table-row {
				display: flex;
				flex-wrap: nowrap;
				align-items: flex-start;
				gap: 6px;
				margin-bottom: 8px;
				overflow-x: auto;
				padding-bottom: 4px;
			}
			.gs-table-cell-group {
				border: 1px solid #e3e5e8;
				border-radius: 4px;
				padding: 6px;
				background: #fff;
				margin-bottom: 0;
				min-width: 180px;
				flex: 1 1 180px;
			}
			.gs-remove-row {
				flex: 0 0 auto;
				white-space: nowrap;
				align-self: center;
			}
			.gs-table-cell { margin-bottom: 4px; }
			.gs-toggle-link-fields { font-size: 12px; }
			.gs-table-link-fields { display: none; margin-top: 4px; }
			.gs-table-link-fields.is-open { display: block; }
			.gs-table-link-fields input { margin-bottom: 4px; }
			.gs-editor-options summary { cursor: pointer; font-weight: 600; }
			.gs-editor-options-grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(180px,1fr)); gap: 6px 12px; margin-top: 8px; }
			.gs-editor-options-grid label { font-size: 12px; }
		</style>
		<script>
			(function() {
				const headersWrap = document.getElementById('gs-table-headers');
				const rowsWrap = document.getElementById('gs-table-rows');
				const addHeaderBtn = document.getElementById('gs-add-header');
				const addRowBtn = document.getElementById('gs-add-row');
				if (!headersWrap || !rowsWrap || !addHeaderBtn || !addRowBtn) return;

				function getColumnCount() {
					const inputs = headersWrap.querySelectorAll('input[name="gs_table_headers[]"]');
					return Math.max(inputs.length, 1);
				}

				function syncRowFieldNames(row, rowIndex) {
					const groups = row.querySelectorAll('.gs-table-cell-group');
					groups.forEach((group, colIndex) => {
						const textInput = group.querySelector('.gs-table-cell');
						const linkTextInput = group.querySelector('.gs-table-cell-link-text');
						const urlInput = group.querySelector('.gs-table-cell-link-url');
						if (textInput) textInput.name = 'gs_table_rows[' + rowIndex + '][' + colIndex + '][text]';
						if (linkTextInput) linkTextInput.name = 'gs_table_rows[' + rowIndex + '][' + colIndex + '][link_text]';
						if (urlInput) urlInput.name = 'gs_table_rows[' + rowIndex + '][' + colIndex + '][url]';
					});
				}

				function syncAllRowFieldNames() {
					const rows = rowsWrap.querySelectorAll('.gs-table-row');
					rows.forEach((row, rowIndex) => syncRowFieldNames(row, rowIndex));
				}

				function createCellGroup(rowIndex, colIndex, values = {}) {
					const text = values.text || '';
					const url = values.url || '';
					const group = document.createElement('div');
					group.className = 'gs-table-cell-group';
					group.innerHTML = ''
						+ '<input type="text" class="widefat gs-table-cell" name="gs_table_rows[' + rowIndex + '][' + colIndex + '][text]" placeholder="Cell text" />'
						+ '<button type="button" class="button-link gs-toggle-link-fields">' + (url ? 'Hide Link' : 'Link') + '</button>'
						+ '<div class="gs-table-link-fields' + (url ? ' is-open' : '') + '">'
						+ '<input type="text" class="widefat gs-table-cell-link-text" name="gs_table_rows[' + rowIndex + '][' + colIndex + '][link_text]" placeholder="Link text" />'
						+ '<input type="url" class="widefat gs-table-cell-link-url" name="gs_table_rows[' + rowIndex + '][' + colIndex + '][url]" placeholder="https://example.com" />'
						+ '</div>';
					group.querySelector('.gs-table-cell').value = text;
					group.querySelector('.gs-table-cell-link-text').value = text;
					group.querySelector('.gs-table-cell-link-url').value = url;
					return group;
				}

				function refreshRowsForColumnCount() {
					const colCount = getColumnCount();
					rowsWrap.dataset.columns = String(colCount);
					const rows = rowsWrap.querySelectorAll('.gs-table-row');
					rows.forEach((row) => {
						const cells = row.querySelectorAll('.gs-table-cell-group');
						if (cells.length < colCount) {
							for (let i = cells.length; i < colCount; i++) {
								const rowIndex = Array.from(rowsWrap.querySelectorAll('.gs-table-row')).indexOf(row);
								row.insertBefore(createCellGroup(rowIndex, i, {}), row.querySelector('.gs-remove-row'));
							}
						} else if (cells.length > colCount) {
							for (let i = cells.length - 1; i >= colCount; i--) {
								cells[i].remove();
							}
						}
					});
					syncAllRowFieldNames();
				}

				function addHeader(value = '') {
					const row = document.createElement('div');
					row.className = 'gs-table-inline-row';
					row.innerHTML = '<input type="text" name="gs_table_headers[]" class="widefat" />' +
						'<button type="button" class="button-link-delete gs-remove-header">Remove</button>';
					const input = row.querySelector('input');
					input.value = value;
					headersWrap.appendChild(row);
				}

				function addRow(values = []) {
					const colCount = getColumnCount();
					const rowIndex = rowsWrap.querySelectorAll('.gs-table-row').length;
					const row = document.createElement('div');
					row.className = 'gs-table-row';
					for (let i = 0; i < colCount; i++) {
						const rawValue = values[i] || {};
						let cellValues = { text: '', url: '' };
						if (typeof rawValue === 'object' && rawValue !== null) {
							cellValues.text = rawValue.text || '';
							cellValues.url = rawValue.url || '';
						} else {
							cellValues.text = String(rawValue);
						}
						row.appendChild(createCellGroup(rowIndex, i, cellValues));
					}
					const remove = document.createElement('button');
					remove.type = 'button';
					remove.className = 'button-link-delete gs-remove-row';
					remove.textContent = 'Remove row';
					row.appendChild(remove);
					rowsWrap.appendChild(row);
					syncAllRowFieldNames();
				}

				addHeaderBtn.addEventListener('click', function() {
					addHeader('');
					refreshRowsForColumnCount();
				});

				addRowBtn.addEventListener('click', function() {
					addRow([]);
				});

				headersWrap.addEventListener('click', function(event) {
					if (event.target.classList.contains('gs-remove-header')) {
						event.preventDefault();
						const row = event.target.closest('.gs-table-inline-row');
						if (row) row.remove();
						if (headersWrap.querySelectorAll('.gs-table-inline-row').length === 0) {
							addHeader('');
						}
						refreshRowsForColumnCount();
					}
				});

				rowsWrap.addEventListener('click', function(event) {
					if (event.target.classList.contains('gs-remove-row')) {
						event.preventDefault();
						const row = event.target.closest('.gs-table-row');
						if (row) {
							row.remove();
							syncAllRowFieldNames();
						}
					}
					if (event.target.classList.contains('gs-toggle-link-fields')) {
						event.preventDefault();
						const group = event.target.closest('.gs-table-cell-group');
						if (!group) return;
						const linkFields = group.querySelector('.gs-table-link-fields');
						if (!linkFields) return;
						const isOpen = linkFields.classList.toggle('is-open');
						event.target.textContent = isOpen ? 'Hide Link' : 'Link';
					}
				});

				syncAllRowFieldNames();
			})();
		</script>
		<?php
	}
}

Tables::init();
