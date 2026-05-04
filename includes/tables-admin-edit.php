<?php
namespace WSUWP\Plugin\Graduate;

class Tables_Admin_Edit {

	public static function render_add_screen() {
		if ( ! current_user_can( Tables::CAP_ADD ) ) {
			wp_die( esc_html__( 'You do not have permission to add tables.', 'wsuwp-plugin-graduate-school' ) );
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Add New Table', 'wsuwp-plugin-graduate-school' ); ?></h1>
			<?php Tables_Admin::render_tabs( Tables_Admin::ADD_SLUG ); ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="gs-add-table-form">
				<?php wp_nonce_field( 'gs_tables_create', 'gs_tables_create_nonce' ); ?>
				<input type="hidden" name="action" value="gs_tables_create" />
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="gs-table-name"><?php esc_html_e( 'Table Name', 'wsuwp-plugin-graduate-school' ); ?></label></th>
						<td><input id="gs-table-name" name="table_name" type="text" class="regular-text" required /></td>
					</tr>
					<tr>
						<th scope="row"><label for="gs-table-description"><?php esc_html_e( 'Description (optional)', 'wsuwp-plugin-graduate-school' ); ?></label></th>
						<td><textarea id="gs-table-description" name="table_description" rows="4" class="large-text"></textarea></td>
					</tr>
					<tr>
						<th scope="row"><label for="gs-table-rows"><?php esc_html_e( 'Number of Rows', 'wsuwp-plugin-graduate-school' ); ?></label></th>
						<td><input id="gs-table-rows" name="row_count" type="number" min="1" max="500" value="5" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="gs-table-cols"><?php esc_html_e( 'Number of Columns', 'wsuwp-plugin-graduate-school' ); ?></label></th>
						<td><input id="gs-table-cols" name="col_count" type="number" min="1" max="100" value="5" /></td>
					</tr>
				</table>
				<p><button class="button button-primary" type="submit"><?php esc_html_e( 'Add Table', 'wsuwp-plugin-graduate-school' ); ?></button></p>
			</form>
		</div>
		<?php
	}

	public static function handle_create() {
		if ( ! current_user_can( Tables::CAP_ADD ) ) {
			wp_die( esc_html__( 'You do not have permission to add tables.', 'wsuwp-plugin-graduate-school' ) );
		}
		check_admin_referer( 'gs_tables_create', 'gs_tables_create_nonce' );
		$name = isset( $_POST['table_name'] ) ? sanitize_text_field( wp_unslash( $_POST['table_name'] ) ) : '';
		$description = isset( $_POST['table_description'] ) ? sanitize_text_field( wp_unslash( $_POST['table_description'] ) ) : '';
		$row_count = isset( $_POST['row_count'] ) ? max( 1, absint( wp_unslash( $_POST['row_count'] ) ) ) : 5;
		$col_count = isset( $_POST['col_count'] ) ? max( 1, absint( wp_unslash( $_POST['col_count'] ) ) ) : 5;
		if ( '' === $name ) {
			wp_safe_redirect( admin_url( 'admin.php?page=' . Tables_Admin::ADD_SLUG . '&gs_tables_notice=create_missing_name' ) );
			exit;
		}

		$post_id = wp_insert_post(
			array(
				'post_type' => Tables::POST_TYPE,
				'post_status' => 'draft',
				'post_title' => $name,
			),
			true
		);
		if ( is_wp_error( $post_id ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=' . Tables_Admin::ADD_SLUG . '&gs_tables_notice=create_failed' ) );
			exit;
		}

		$headers = array();
		for ( $i = 0; $i < $col_count; $i++ ) {
			$headers[] = self::column_label( $i );
		}
		$rows = array();
		for ( $r = 0; $r < $row_count; $r++ ) {
			$rows[] = array_fill( 0, $col_count, '' );
		}

		update_post_meta( $post_id, '_gs_table_caption', $description );
		update_post_meta( $post_id, '_gs_table_headers', $headers );
		update_post_meta( $post_id, '_gs_table_rows', $rows );

		wp_safe_redirect( admin_url( 'admin.php?page=' . Tables_Admin::MENU_SLUG . '&action=edit&table_id=' . $post_id . '&gs_tables_notice=table_created' ) );
		exit;
	}

	public static function render_edit_screen() {
		if ( ! current_user_can( Tables::CAP_EDIT ) ) {
			wp_die( esc_html__( 'You do not have permission to edit tables.', 'wsuwp-plugin-graduate-school' ) );
		}
		$table_id = isset( $_GET['table_id'] ) ? absint( wp_unslash( $_GET['table_id'] ) ) : 0;
		$record = Tables::get_table_record( $table_id );
		if ( ! $record ) {
			wp_die( esc_html__( 'The requested table could not be found.', 'wsuwp-plugin-graduate-school' ) );
		}
		$post = $record['post'];
		$headers = is_array( $record['headers'] ) ? $record['headers'] : array();
		$rows = is_array( $record['rows'] ) ? $record['rows'] : array();
		$col_count = max( 1, count( $headers ) );
		if ( empty( $headers ) ) {
			$headers = array_fill( 0, $col_count, '' );
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_the_title( $post ) ); ?></h1>
			<?php Tables_Admin::render_tabs( 'edit' ); ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="gs-table-editor-form">
				<input type="hidden" name="action" value="gs_tables_save" />
				<input type="hidden" name="table_id" value="<?php echo esc_attr( (string) $post->ID ); ?>" />
				<?php wp_nonce_field( 'gs_tables_save_' . $post->ID, 'gs_tables_save_nonce' ); ?>
				<?php wp_nonce_field( 'gs_table_builder_save', 'gs_table_builder_nonce' ); ?>

				<div class="gs-toolbar-top">
					<a class="button" href="<?php echo esc_url( Tables::get_preview_url( $post->ID ) ); ?>" target="_blank"><?php esc_html_e( 'Preview', 'wsuwp-plugin-graduate-school' ); ?></a>
					<button class="button button-primary" type="submit"><?php esc_html_e( 'Save Changes', 'wsuwp-plugin-graduate-school' ); ?></button>
				</div>

				<div class="postbox"><h2 class="hndle"><span><?php esc_html_e( 'Table Information', 'wsuwp-plugin-graduate-school' ); ?></span></h2><div class="inside">
					<table class="form-table" role="presentation">
						<tr><th><?php esc_html_e( 'Table ID', 'wsuwp-plugin-graduate-school' ); ?></th><td><?php echo esc_html( (string) $post->ID ); ?></td></tr>
						<tr><th><?php esc_html_e( 'Shortcode', 'wsuwp-plugin-graduate-school' ); ?></th><td><input type="text" readonly value="<?php echo esc_attr( '[gs_table id="' . $post->ID . '"]' ); ?>" class="regular-text" /></td></tr>
						<tr><th><label for="gs-edit-title"><?php esc_html_e( 'Table Name', 'wsuwp-plugin-graduate-school' ); ?></label></th><td><input id="gs-edit-title" type="text" class="regular-text" name="post_title" value="<?php echo esc_attr( $post->post_title ); ?>" required /></td></tr>
						<tr><th><label for="gs-table-caption"><?php esc_html_e( 'Description', 'wsuwp-plugin-graduate-school' ); ?></label></th><td><textarea id="gs-table-caption" name="gs_table_caption" rows="4" class="large-text"><?php echo esc_textarea( $record['caption'] ); ?></textarea></td></tr>
						<tr><th><?php esc_html_e( 'Last Modified', 'wsuwp-plugin-graduate-school' ); ?></th><td><?php echo esc_html( get_date_from_gmt( $post->post_modified_gmt, 'M j, Y g:i a' ) ); ?></td></tr>
					</table>
				</div></div>

				<div class="postbox"><h2 class="hndle"><span><?php esc_html_e( 'Table Content', 'wsuwp-plugin-graduate-school' ); ?></span></h2><div class="inside">
					<div class="gs-grid-wrap">
						<table class="widefat striped gs-grid-table" data-gs-grid="1">
							<thead>
								<tr>
									<th class="gs-grid-index"></th>
									<?php foreach ( $headers as $index => $header ) : ?>
										<th data-col-index="<?php echo esc_attr( (string) $index ); ?>" contenteditable="true" class="gs-grid-header-cell"><?php echo esc_html( (string) $header ); ?></th>
									<?php endforeach; ?>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $rows as $row_index => $row ) : ?>
									<tr>
										<th class="gs-grid-index" data-row-index="<?php echo esc_attr( (string) $row_index ); ?>"><?php echo esc_html( (string) ( $row_index + 1 ) ); ?></th>
										<?php for ( $col = 0; $col < $col_count; $col++ ) : ?>
											<?php
											$cell = isset( $row[ $col ] ) ? $row[ $col ] : '';
											$cell_data = Tables::get_editor_cell_data( $cell );
											$cell_text = isset( $cell_data['text'] ) ? (string) $cell_data['text'] : '';
											?>
											<td contenteditable="true" data-row="<?php echo esc_attr( (string) $row_index ); ?>" data-col="<?php echo esc_attr( (string) $col ); ?>"><?php echo esc_html( $cell_text ); ?></td>
										<?php endfor; ?>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
					<div id="gs-grid-hidden-inputs">
						<?php foreach ( $headers as $header ) : ?>
							<input type="hidden" name="gs_table_headers[]" value="<?php echo esc_attr( (string) $header ); ?>" />
						<?php endforeach; ?>
						<?php foreach ( $rows as $row_index => $row ) : ?>
							<?php for ( $col = 0; $col < $col_count; $col++ ) : ?>
								<?php
								$cell = isset( $row[ $col ] ) ? $row[ $col ] : '';
								$cell_data = Tables::get_editor_cell_data( $cell );
								$cell_text = isset( $cell_data['text'] ) ? (string) $cell_data['text'] : '';
								$cell_url = isset( $cell_data['url'] ) ? (string) $cell_data['url'] : '';
								?>
								<input type="hidden" name="gs_table_rows[<?php echo esc_attr( (string) $row_index ); ?>][<?php echo esc_attr( (string) $col ); ?>][text]" value="<?php echo esc_attr( $cell_text ); ?>" />
								<input type="hidden" name="gs_table_rows[<?php echo esc_attr( (string) $row_index ); ?>][<?php echo esc_attr( (string) $col ); ?>][url]" value="<?php echo esc_attr( $cell_url ); ?>" />
							<?php endfor; ?>
						<?php endforeach; ?>
					</div>
				</div></div>

				<div class="postbox"><h2 class="hndle"><span><?php esc_html_e( 'Table Manipulation', 'wsuwp-plugin-graduate-school' ); ?></span></h2><div class="inside">
					<div class="gs-manipulation-grid">
						<div class="gs-manip-column">
							<div class="gs-manip-row">
								<span class="gs-manip-label"><?php esc_html_e( 'Selected cells:', 'wsuwp-plugin-graduate-school' ); ?></span>
								<div class="gs-manip-actions">
									<button type="button" class="button gs-action" data-gs-action="insert-link">Insert Link</button>
									<button type="button" class="button gs-action" data-gs-action="insert-image">Insert Image</button>
									<button type="button" class="button gs-action" data-gs-action="advanced-editor">Advanced Editor</button>
								</div>
							</div>
							<div class="gs-manip-row">
								<span class="gs-manip-label"><?php esc_html_e( 'Selected rows:', 'wsuwp-plugin-graduate-school' ); ?></span>
								<div class="gs-manip-actions">
									<button type="button" class="button gs-action" data-gs-action="duplicate-row">Duplicate</button>
									<button type="button" class="button gs-action" data-gs-action="insert-row">Insert</button>
									<button type="button" class="button gs-action" data-gs-action="delete-row">Delete</button>
								</div>
							</div>
							<div class="gs-manip-row">
								<span class="gs-manip-label"><?php esc_html_e( 'Selected rows:', 'wsuwp-plugin-graduate-school' ); ?></span>
								<div class="gs-manip-actions">
									<button type="button" class="button gs-action" data-gs-action="move-row-up">Move up</button>
									<button type="button" class="button gs-action" data-gs-action="move-row-down">Move down</button>
								</div>
							</div>
							<div class="gs-manip-row">
								<span class="gs-manip-label"><?php esc_html_e( 'Selected rows:', 'wsuwp-plugin-graduate-school' ); ?></span>
								<div class="gs-manip-actions">
									<button type="button" class="button gs-action" data-gs-action="hide-row">Hide</button>
									<button type="button" class="button gs-action" data-gs-action="show-row">Show</button>
								</div>
							</div>
							<div class="gs-manip-row">
								<span class="gs-manip-label"><?php esc_html_e( 'Add', 'wsuwp-plugin-graduate-school' ); ?></span>
								<div class="gs-manip-actions">
									<input type="number" value="1" min="1" class="small-text" id="gs-add-count-rows" />
									<span><?php esc_html_e( 'row(s)', 'wsuwp-plugin-graduate-school' ); ?></span>
									<button type="button" class="button gs-action" data-gs-action="append-rows">Add</button>
								</div>
							</div>
						</div>
						<div class="gs-manip-column">
							<div class="gs-manip-row">
								<span class="gs-manip-label"><?php esc_html_e( 'Selected cells:', 'wsuwp-plugin-graduate-school' ); ?></span>
								<div class="gs-manip-actions">
									<button type="button" class="button gs-action" disabled>Combine/Merge</button>
								</div>
							</div>
							<div class="gs-manip-row">
								<span class="gs-manip-label"><?php esc_html_e( 'Selected columns:', 'wsuwp-plugin-graduate-school' ); ?></span>
								<div class="gs-manip-actions">
									<button type="button" class="button gs-action" data-gs-action="duplicate-column">Duplicate</button>
									<button type="button" class="button gs-action" data-gs-action="insert-column">Insert</button>
									<button type="button" class="button gs-action" data-gs-action="delete-column">Delete</button>
								</div>
							</div>
							<div class="gs-manip-row">
								<span class="gs-manip-label"><?php esc_html_e( 'Selected columns:', 'wsuwp-plugin-graduate-school' ); ?></span>
								<div class="gs-manip-actions">
									<button type="button" class="button gs-action" data-gs-action="move-col-left">Move left</button>
									<button type="button" class="button gs-action" data-gs-action="move-col-right">Move right</button>
								</div>
							</div>
							<div class="gs-manip-row">
								<span class="gs-manip-label"><?php esc_html_e( 'Selected columns:', 'wsuwp-plugin-graduate-school' ); ?></span>
								<div class="gs-manip-actions">
									<button type="button" class="button gs-action" data-gs-action="hide-column">Hide</button>
									<button type="button" class="button gs-action" data-gs-action="show-column">Show</button>
								</div>
							</div>
							<div class="gs-manip-row">
								<span class="gs-manip-label"><?php esc_html_e( 'Add', 'wsuwp-plugin-graduate-school' ); ?></span>
								<div class="gs-manip-actions">
									<input type="number" value="1" min="1" class="small-text" id="gs-add-count-cols" />
									<span><?php esc_html_e( 'column(s)', 'wsuwp-plugin-graduate-school' ); ?></span>
									<button type="button" class="button gs-action" data-gs-action="append-columns">Add</button>
								</div>
							</div>
						</div>
					</div>
					<div class="gs-link-editor" id="gs-link-editor">
						<h4><?php esc_html_e( 'Selected Cell Link', 'wsuwp-plugin-graduate-school' ); ?></h4>
						<p class="description"><?php esc_html_e( 'Select a cell, then edit link text and URL.', 'wsuwp-plugin-graduate-school' ); ?></p>
						<p>
							<label for="gs-link-text"><?php esc_html_e( 'Link Text', 'wsuwp-plugin-graduate-school' ); ?></label><br />
							<input type="text" id="gs-link-text" class="regular-text" />
						</p>
						<p>
							<label for="gs-link-url"><?php esc_html_e( 'Link URL', 'wsuwp-plugin-graduate-school' ); ?></label><br />
							<input type="url" id="gs-link-url" class="large-text" placeholder="https://example.com" />
						</p>
						<p>
							<button type="button" class="button button-secondary" id="gs-link-apply"><?php esc_html_e( 'Apply Link', 'wsuwp-plugin-graduate-school' ); ?></button>
							<button type="button" class="button" id="gs-link-clear"><?php esc_html_e( 'Clear Link', 'wsuwp-plugin-graduate-school' ); ?></button>
						</p>
					</div>
					<input type="hidden" name="gs_table_hidden_rows[]" value="" />
					<input type="hidden" name="gs_table_hidden_cols[]" value="" />
				</div></div>

				<div class="postbox"><h2 class="hndle"><span><?php esc_html_e( 'Table Options', 'wsuwp-plugin-graduate-school' ); ?></span></h2><div class="inside">
					<label><input type="checkbox" name="gs_table_sortable" value="1" <?php checked( $record['sortable'] ); ?> /> <?php esc_html_e( 'Table Head Row', 'wsuwp-plugin-graduate-school' ); ?></label><br />
					<label><input type="checkbox" name="gs_table_foot_row" value="1" <?php checked( $record['foot_row'] ); ?> /> <?php esc_html_e( 'Table Foot Row', 'wsuwp-plugin-graduate-school' ); ?></label><br />
					<label><input type="checkbox" name="gs_table_striped" value="1" <?php checked( $record['striped'] ); ?> /> <?php esc_html_e( 'Alternating Row Colors', 'wsuwp-plugin-graduate-school' ); ?></label><br />
					<label><input type="checkbox" name="gs_table_row_hover" value="1" <?php checked( $record['row_hover'] ); ?> /> <?php esc_html_e( 'Row Hover Highlighting', 'wsuwp-plugin-graduate-school' ); ?></label><br />
					<label><input type="checkbox" name="gs_table_print_name" value="1" <?php checked( $record['print_name'] ); ?> /> <?php esc_html_e( 'Print Table Name', 'wsuwp-plugin-graduate-school' ); ?></label>
					<select name="gs_table_print_name_position"><option value="above" <?php selected( $record['print_name_position'], 'above' ); ?>><?php esc_html_e( 'above', 'wsuwp-plugin-graduate-school' ); ?></option><option value="below" <?php selected( $record['print_name_position'], 'below' ); ?>><?php esc_html_e( 'below', 'wsuwp-plugin-graduate-school' ); ?></option></select><br />
					<label><input type="checkbox" name="gs_table_print_description" value="1" <?php checked( $record['print_description'] ); ?> /> <?php esc_html_e( 'Print Table Description', 'wsuwp-plugin-graduate-school' ); ?></label>
					<select name="gs_table_print_description_position"><option value="above" <?php selected( $record['print_description_position'], 'above' ); ?>><?php esc_html_e( 'above', 'wsuwp-plugin-graduate-school' ); ?></option><option value="below" <?php selected( $record['print_description_position'], 'below' ); ?>><?php esc_html_e( 'below', 'wsuwp-plugin-graduate-school' ); ?></option></select><br />
					<p><label><?php esc_html_e( 'Extra CSS Class', 'wsuwp-plugin-graduate-school' ); ?> <input type="text" name="gs_table_extra_css_class" value="<?php echo esc_attr( $record['extra_css_class'] ); ?>" /></label></p>
				</div></div>

				<div class="postbox"><h2 class="hndle"><span><?php esc_html_e( 'Table Features for Site Visitors', 'wsuwp-plugin-graduate-school' ); ?></span></h2><div class="inside">
					<label><input type="checkbox" name="gs_table_visitor_features" value="1" <?php checked( $record['visitor_features'] ); ?> /> <?php esc_html_e( 'Enable visitor features', 'wsuwp-plugin-graduate-school' ); ?></label><br />
					<label><input type="checkbox" name="gs_table_search" value="1" <?php checked( $record['search'] ); ?> /> <?php esc_html_e( 'Search/Filtering', 'wsuwp-plugin-graduate-school' ); ?></label><br />
					<label><input type="checkbox" name="gs_table_pagination" value="1" <?php checked( $record['pagination'] ); ?> /> <?php esc_html_e( 'Pagination', 'wsuwp-plugin-graduate-school' ); ?></label><br />
					<label><?php esc_html_e( 'Pagination Length', 'wsuwp-plugin-graduate-school' ); ?> <input type="number" min="1" max="500" name="gs_table_pagination_length" value="<?php echo esc_attr( (string) $record['pagination_length'] ); ?>" class="small-text" /></label><br />
					<label><input type="checkbox" name="gs_table_pagination_length_change" value="1" <?php checked( $record['pagination_length_change'] ); ?> /> <?php esc_html_e( 'Pagination Length Change', 'wsuwp-plugin-graduate-school' ); ?></label><br />
					<label><input type="checkbox" name="gs_table_info" value="1" <?php checked( $record['info'] ); ?> /> <?php esc_html_e( 'Info text', 'wsuwp-plugin-graduate-school' ); ?></label><br />
					<label><input type="checkbox" name="gs_table_horizontal_scrolling" value="1" <?php checked( $record['horizontal_scrolling'] ); ?> /> <?php esc_html_e( 'Horizontal Scrolling', 'wsuwp-plugin-graduate-school' ); ?></label><br />
					<p><label><?php esc_html_e( 'Custom Commands', 'wsuwp-plugin-graduate-school' ); ?><br /><textarea name="gs_table_custom_commands" rows="4" class="large-text"><?php echo esc_textarea( $record['custom_commands'] ); ?></textarea></label></p>
				</div></div>

				<div class="gs-other-actions">
					<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=gs_tables_copy&table_id=' . $post->ID ), 'gs_tables_copy_' . $post->ID ) ); ?>"><?php esc_html_e( 'Copy Table', 'wsuwp-plugin-graduate-school' ); ?></a>
					<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=' . Tables_Admin::EXPORT_SLUG . '&table_ids=' . $post->ID ) ); ?>"><?php esc_html_e( 'Export Table', 'wsuwp-plugin-graduate-school' ); ?></a>
					<a class="button button-link-delete" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=gs_tables_delete&table_id=' . $post->ID ), 'gs_tables_delete_' . $post->ID ) ); ?>" onclick="return confirm('Delete this table permanently?');"><?php esc_html_e( 'Delete Table', 'wsuwp-plugin-graduate-school' ); ?></a>
				</div>
			</form>
		</div>
		<?php
	}

	public static function render_preview_screen() {
		if ( ! current_user_can( Tables::CAP_EDIT ) ) {
			wp_die( esc_html__( 'You do not have permission to preview tables.', 'wsuwp-plugin-graduate-school' ) );
		}

		$table_id = isset( $_GET['table_id'] ) ? absint( wp_unslash( $_GET['table_id'] ) ) : 0;
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		$valid_request = $table_id > 0 && wp_verify_nonce( $nonce, 'gs_table_preview_' . $table_id );
		$content = '';
		if ( ! $valid_request ) {
			$content = '<div class="notice notice-warning inline"><p>' . esc_html__( 'Open preview from a table edit screen to generate a secure preview link.', 'wsuwp-plugin-graduate-school' ) . '</p></div>';
		} else {
			$post = get_post( $table_id );
			if ( ! $post instanceof \WP_Post || Tables::POST_TYPE !== $post->post_type ) {
				$content = '<div class="notice notice-error inline"><p>' . esc_html__( 'This table could not be found.', 'wsuwp-plugin-graduate-school' ) . '</p></div>';
			} else {
				$content = Tables::render_shortcode(
					array(
						'id' => (string) $table_id,
						'show_hidden' => '1',
					),
					'',
					'gs_table'
				);
			}
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Table Preview', 'wsuwp-plugin-graduate-school' ); ?></h1>
			<?php Tables_Admin::render_tabs( Tables_Admin::MENU_SLUG ); ?>
			<div class="gs-table-preview-wrap">
				<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
		<?php
	}

	public static function handle_save() {
		$table_id = isset( $_POST['table_id'] ) ? absint( wp_unslash( $_POST['table_id'] ) ) : 0;
		if ( ! current_user_can( Tables::CAP_EDIT ) || ! $table_id ) {
			wp_die( esc_html__( 'You do not have permission to save this table.', 'wsuwp-plugin-graduate-school' ) );
		}
		check_admin_referer( 'gs_tables_save_' . $table_id, 'gs_tables_save_nonce' );
		$title = isset( $_POST['post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['post_title'] ) ) : '';
		if ( '' !== $title ) {
			wp_update_post(
				array(
					'ID' => $table_id,
					'post_title' => $title,
				)
			);
		}
		Tables::save_table_meta( $table_id );
		wp_safe_redirect( admin_url( 'admin.php?page=' . Tables_Admin::MENU_SLUG . '&action=edit&table_id=' . $table_id . '&gs_tables_notice=saved' ) );
		exit;
	}

	public static function handle_copy() {
		$table_id = isset( $_GET['table_id'] ) ? absint( wp_unslash( $_GET['table_id'] ) ) : 0;
		if ( ! current_user_can( Tables::CAP_EDIT ) || ! $table_id ) {
			wp_die( esc_html__( 'You do not have permission to copy this table.', 'wsuwp-plugin-graduate-school' ) );
		}
		check_admin_referer( 'gs_tables_copy_' . $table_id );
		$new_id = Tables::duplicate_table( $table_id );
		if ( is_wp_error( $new_id ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=' . Tables_Admin::MENU_SLUG . '&gs_tables_notice=copy_failed' ) );
			exit;
		}
		wp_safe_redirect( admin_url( 'admin.php?page=' . Tables_Admin::MENU_SLUG . '&action=edit&table_id=' . absint( $new_id ) . '&gs_tables_notice=copied' ) );
		exit;
	}

	public static function handle_delete() {
		$table_id = isset( $_GET['table_id'] ) ? absint( wp_unslash( $_GET['table_id'] ) ) : 0;
		if ( ! current_user_can( Tables::CAP_DELETE ) || ! $table_id ) {
			wp_die( esc_html__( 'You do not have permission to delete this table.', 'wsuwp-plugin-graduate-school' ) );
		}
		check_admin_referer( 'gs_tables_delete_' . $table_id );
		Tables::delete_table( $table_id );
		wp_safe_redirect( admin_url( 'admin.php?page=' . Tables_Admin::MENU_SLUG . '&gs_tables_notice=deleted' ) );
		exit;
	}

	private static function column_label( $index ) {
		$index = (int) $index;
		$label = '';
		do {
			$label = chr( 65 + ( $index % 26 ) ) . $label;
			$index = (int) floor( $index / 26 ) - 1;
		} while ( $index >= 0 );
		return $label;
	}
}
