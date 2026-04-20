<?php
namespace WSUWP\Plugin\Graduate;

class Tables {

	const POST_TYPE = 'gs-table';
	const CAP_LIST = 'gs_tables_list';
	const CAP_ADD = 'gs_tables_add';
	const CAP_EDIT = 'gs_tables_edit';
	const CAP_DELETE = 'gs_tables_delete';
	const CAP_ADVANCED_OPTIONS = 'gs_tables_advanced_options';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'init', array( __CLASS__, 'ensure_role_capabilities' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'register_meta_boxes' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'save_table_meta' ) );
		add_action( 'admin_footer-post-new.php', array( __CLASS__, 'print_admin_editor_script' ) );
		add_action( 'admin_footer-post.php', array( __CLASS__, 'print_admin_editor_script' ) );

		add_shortcode( 'gs_table', array( __CLASS__, 'render_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue_assets' ) );
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
				'show_ui'            => true,
				'show_in_menu'       => true,
				'show_in_nav_menus'  => false,
				'show_in_admin_bar'  => true,
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
		<p>
			<label for="gs-table-caption"><strong><?php esc_html_e( 'Caption (optional)', 'wsuwp-plugin-graduate-school' ); ?></strong></label>
			<input type="text" id="gs-table-caption" name="gs_table_caption" value="<?php echo esc_attr( $caption ); ?>" class="widefat" />
		</p>

		<p><strong><?php esc_html_e( 'Columns', 'wsuwp-plugin-graduate-school' ); ?></strong></p>
		<div id="gs-table-headers">
			<?php foreach ( $headers as $header ) : ?>
				<div class="gs-table-inline-row">
					<input type="text" name="gs_table_headers[]" value="<?php echo esc_attr( $header ); ?>" class="widefat" />
					<button type="button" class="button-link-delete gs-remove-header"><?php esc_html_e( 'Remove', 'wsuwp-plugin-graduate-school' ); ?></button>
				</div>
			<?php endforeach; ?>
		</div>
		<p>
			<button type="button" id="gs-add-header" class="button"><?php esc_html_e( 'Add Column', 'wsuwp-plugin-graduate-school' ); ?></button>
		</p>

		<hr />

		<p><strong><?php esc_html_e( 'Rows', 'wsuwp-plugin-graduate-school' ); ?></strong></p>
		<div id="gs-table-rows" data-columns="<?php echo esc_attr( (string) $column_count ); ?>">
			<?php foreach ( $rows as $row ) : ?>
				<?php if ( ! is_array( $row ) ) { continue; } ?>
				<div class="gs-table-row">
					<?php for ( $i = 0; $i < $column_count; $i++ ) : ?>
						<?php $value = isset( $row[ $i ] ) ? $row[ $i ] : ''; ?>
						<input type="text" name="gs_table_rows[][<?php echo esc_attr( (string) $i ); ?>]" value="<?php echo esc_attr( $value ); ?>" class="widefat gs-table-cell" />
					<?php endfor; ?>
					<button type="button" class="button-link-delete gs-remove-row"><?php esc_html_e( 'Remove row', 'wsuwp-plugin-graduate-school' ); ?></button>
				</div>
			<?php endforeach; ?>
		</div>
		<p>
			<button type="button" id="gs-add-row" class="button button-secondary"><?php esc_html_e( 'Add Row', 'wsuwp-plugin-graduate-school' ); ?></button>
		</p>

		<hr />

		<p><strong><?php esc_html_e( 'Display Options', 'wsuwp-plugin-graduate-school' ); ?></strong></p>
		<p>
			<label>
				<input type="checkbox" name="gs_table_sortable" value="1" <?php checked( $sortable ); ?> />
				<?php esc_html_e( 'Allow column sorting', 'wsuwp-plugin-graduate-school' ); ?>
			</label>
		</p>
		<p>
			<label>
				<input type="checkbox" name="gs_table_striped" value="1" <?php checked( $striped ); ?> />
				<?php esc_html_e( 'Striped rows', 'wsuwp-plugin-graduate-school' ); ?>
			</label>
		</p>
		<p>
			<label>
				<input type="checkbox" name="gs_table_compact" value="1" <?php checked( $compact ); ?> />
				<?php esc_html_e( 'Compact spacing', 'wsuwp-plugin-graduate-school' ); ?>
			</label>
		</p>
		<p>
			<label>
				<input type="checkbox" name="gs_table_auto_link" value="1" <?php checked( $auto_link ); ?> />
				<?php esc_html_e( 'Auto-link valid URLs in cells', 'wsuwp-plugin-graduate-school' ); ?>
			</label>
		</p>
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
		?>
		<p><?php esc_html_e( 'Use either shortcode in any page or post:', 'wsuwp-plugin-graduate-school' ); ?></p>
		<p><input type="text" readonly class="widefat" value="<?php echo esc_attr( $id_shortcode ); ?>" onclick="this.select();" /></p>
		<?php if ( ! empty( $post->post_name ) ) : ?>
			<p><input type="text" readonly class="widefat" value="<?php echo esc_attr( $slug_shortcode ); ?>" onclick="this.select();" /></p>
		<?php endif; ?>
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
				$sanitized_row[] = sanitize_text_field( $cell );
			}

			$has_content = false;
			foreach ( $sanitized_row as $cell ) {
				if ( '' !== trim( $cell ) ) {
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

		update_post_meta( $post_id, '_gs_table_sortable', $sortable );
		update_post_meta( $post_id, '_gs_table_striped', $striped );
		update_post_meta( $post_id, '_gs_table_compact', $compact );
		update_post_meta( $post_id, '_gs_table_auto_link', $auto_link );
	}

	public static function maybe_enqueue_assets() {
		global $post;
		if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'gs_table' ) ) {
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

	public static function render_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'id'            => 0,
				'slug'          => '',
				'sortable'      => '',
				'auto_link'     => '',
				'class'         => '',
				'empty_message' => __( 'Table data is not available at this time.', 'wsuwp-plugin-graduate-school' ),
			),
			$atts,
			'gs_table'
		);

		$post = null;
		$id   = absint( $atts['id'] );
		$slug = sanitize_title( $atts['slug'] );

		if ( $id > 0 ) {
			$maybe_post = get_post( $id );
			if ( $maybe_post instanceof \WP_Post && self::POST_TYPE === $maybe_post->post_type && self::can_render_post( $maybe_post ) ) {
				$post = $maybe_post;
			}
		}

		if ( ! $post && ! empty( $slug ) ) {
			$maybe_post = get_page_by_path( $slug, OBJECT, self::POST_TYPE );
			if ( $maybe_post instanceof \WP_Post && self::can_render_post( $maybe_post ) ) {
				$post = $maybe_post;
			}
		}

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

		$sortable_override = self::normalize_bool( $atts['sortable'], null );
		$sortable          = is_null( $sortable_override ) ? $saved_sortable : $sortable_override;
		$auto_link_override = self::normalize_bool( $atts['auto_link'], null );
		$auto_link          = is_null( $auto_link_override ) ? $saved_auto_link : $auto_link_override;

		$classes = array( 'gs-table-wrap' );
		if ( $striped ) {
			$classes[] = 'gs-table-wrap--striped';
		}
		if ( $compact ) {
			$classes[] = 'gs-table-wrap--compact';
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
			<div class="gs-table-scroll">
				<table class="gs-table"<?php echo $table_attr; ?>>
					<?php if ( ! empty( $caption ) ) : ?>
						<caption><?php echo esc_html( $caption ); ?></caption>
					<?php endif; ?>
					<thead>
						<tr>
							<?php foreach ( $headers as $index => $header ) : ?>
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
						<?php foreach ( $rows as $row ) : ?>
							<?php if ( ! is_array( $row ) ) { continue; } ?>
							<tr>
								<?php foreach ( $headers as $i => $unused_header ) : ?>
									<td><?php echo self::render_cell_value( isset( $row[ $i ] ) ? $row[ $i ] : '', $auto_link ); ?></td>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php
		return ob_get_clean();
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

	private static function can_render_post( $post ) {
		if ( ! $post instanceof \WP_Post ) {
			return false;
		}
		if ( 'publish' === $post->post_status ) {
			return true;
		}
		return current_user_can( self::CAP_EDIT );
	}

	public static function print_admin_editor_script() {
		$screen = get_current_screen();
		if ( ! $screen || self::POST_TYPE !== $screen->post_type ) {
			return;
		}
		?>
		<style>
			.gs-table-inline-row { display: grid; grid-template-columns: 1fr auto; gap: 8px; margin-bottom: 8px; }
			.gs-table-row { border: 1px solid #dcdcde; border-radius: 4px; padding: 10px; margin-bottom: 10px; }
			.gs-table-row .gs-table-cell { margin-bottom: 8px; }
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

				function refreshRowsForColumnCount() {
					const colCount = getColumnCount();
					rowsWrap.dataset.columns = String(colCount);
					const rows = rowsWrap.querySelectorAll('.gs-table-row');
					rows.forEach((row) => {
						const cells = row.querySelectorAll('input.gs-table-cell');
						if (cells.length < colCount) {
							for (let i = cells.length; i < colCount; i++) {
								const input = document.createElement('input');
								input.type = 'text';
								input.className = 'widefat gs-table-cell';
								input.name = 'gs_table_rows[][' + i + ']';
								row.insertBefore(input, row.querySelector('.gs-remove-row'));
							}
						} else if (cells.length > colCount) {
							for (let i = cells.length - 1; i >= colCount; i--) {
								cells[i].remove();
							}
						}
					});
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
					const row = document.createElement('div');
					row.className = 'gs-table-row';
					for (let i = 0; i < colCount; i++) {
						const input = document.createElement('input');
						input.type = 'text';
						input.className = 'widefat gs-table-cell';
						input.name = 'gs_table_rows[][' + i + ']';
						input.value = values[i] || '';
						row.appendChild(input);
					}
					const remove = document.createElement('button');
					remove.type = 'button';
					remove.className = 'button-link-delete gs-remove-row';
					remove.textContent = 'Remove row';
					row.appendChild(remove);
					rowsWrap.appendChild(row);
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
						if (row) row.remove();
					}
				});
			})();
		</script>
		<?php
	}
}

Tables::init();
