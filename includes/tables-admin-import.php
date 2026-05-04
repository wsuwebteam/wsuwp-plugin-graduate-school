<?php
namespace WSUWP\Plugin\Graduate;

class Tables_Admin_Import {

	public static function render_page() {
		if ( ! current_user_can( Tables::CAP_EDIT ) ) {
			wp_die( esc_html__( 'You do not have permission to import tables.', 'wsuwp-plugin-graduate-school' ) );
		}
		$tables = get_posts(
			array(
				'post_type' => Tables::POST_TYPE,
				'post_status' => array( 'publish', 'draft', 'pending', 'private' ),
				'posts_per_page' => -1,
				'orderby' => 'title',
				'order' => 'ASC',
			)
		);
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Import a Table', 'wsuwp-plugin-graduate-school' ); ?></h1>
			<?php Tables_Admin::render_tabs( Tables_Admin::IMPORT_SLUG ); ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" class="gs-import-form">
				<?php wp_nonce_field( 'gs_tables_import', 'gs_tables_import_nonce' ); ?>
				<input type="hidden" name="action" value="gs_tables_import" />
				<div class="postbox"><h2 class="hndle"><span><?php esc_html_e( 'Import Tables', 'wsuwp-plugin-graduate-school' ); ?></span></h2><div class="inside">
					<p>
						<label><input type="radio" name="import_source" value="upload" checked /> <?php esc_html_e( 'File Upload', 'wsuwp-plugin-graduate-school' ); ?></label>
						<label><input type="radio" name="import_source" value="url" /> <?php esc_html_e( 'URL', 'wsuwp-plugin-graduate-school' ); ?></label>
						<label><input type="radio" name="import_source" value="server" /> <?php esc_html_e( 'File on server', 'wsuwp-plugin-graduate-school' ); ?></label>
						<label><input type="radio" name="import_source" value="manual" /> <?php esc_html_e( 'Manual Input', 'wsuwp-plugin-graduate-school' ); ?></label>
					</p>
					<div class="gs-import-source gs-source-upload is-active">
						<input type="file" name="import_file" />
					</div>
					<div class="gs-import-source gs-source-url">
						<input type="url" name="import_url" class="regular-text" placeholder="https://example.com/table.csv" />
					</div>
					<div class="gs-import-source gs-source-server">
						<input type="text" name="import_server_path" class="regular-text" placeholder="/var/www/path/table.csv" />
					</div>
					<div class="gs-import-source gs-source-manual">
						<textarea name="import_manual" rows="10" class="large-text"></textarea>
					</div>

					<p>
						<label><input type="radio" name="import_mode" value="add" checked /> <?php esc_html_e( 'Add as new table', 'wsuwp-plugin-graduate-school' ); ?></label>
						<label><input type="radio" name="import_mode" value="replace" /> <?php esc_html_e( 'Replace existing table', 'wsuwp-plugin-graduate-school' ); ?></label>
						<label><input type="radio" name="import_mode" value="append" /> <?php esc_html_e( 'Append rows to existing table', 'wsuwp-plugin-graduate-school' ); ?></label>
					</p>
					<p>
						<select name="target_table_id">
							<option value=""><?php esc_html_e( 'Select table to replace or append', 'wsuwp-plugin-graduate-school' ); ?></option>
							<?php foreach ( $tables as $table_post ) : ?>
								<option value="<?php echo esc_attr( (string) $table_post->ID ); ?>"><?php echo esc_html( 'ID ' . $table_post->ID . ': ' . $table_post->post_title ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Import', 'wsuwp-plugin-graduate-school' ); ?></button></p>
				</div></div>
			</form>

			<div class="postbox"><h2 class="hndle"><span><?php esc_html_e( 'Import from TablePress (database)', 'wsuwp-plugin-graduate-school' ); ?></span></h2><div class="inside">
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'gs_tables_import_tablepress', 'gs_tables_import_nonce' ); ?>
					<input type="hidden" name="action" value="gs_tables_import_tablepress" />
					<label><input type="checkbox" name="overwrite_existing" value="1" /> <?php esc_html_e( 'Overwrite existing mapped tables with latest TablePress data', 'wsuwp-plugin-graduate-school' ); ?></label>
					<p><button type="submit" class="button"><?php esc_html_e( 'Run TablePress Import', 'wsuwp-plugin-graduate-school' ); ?></button></p>
				</form>
			</div></div>
		</div>
		<?php
	}

	public static function handle_import() {
		if ( ! current_user_can( Tables::CAP_EDIT ) ) {
			wp_die( esc_html__( 'You do not have permission to import tables.', 'wsuwp-plugin-graduate-school' ) );
		}
		check_admin_referer( 'gs_tables_import', 'gs_tables_import_nonce' );

		$source = isset( $_POST['import_source'] ) ? sanitize_key( wp_unslash( $_POST['import_source'] ) ) : 'upload';
		$mode = isset( $_POST['import_mode'] ) ? sanitize_key( wp_unslash( $_POST['import_mode'] ) ) : 'add';
		$target_id = isset( $_POST['target_table_id'] ) ? absint( wp_unslash( $_POST['target_table_id'] ) ) : 0;
		$raw = self::get_import_payload( $source );
		if ( '' === trim( $raw ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=' . Tables_Admin::IMPORT_SLUG . '&gs_tables_notice=import_empty' ) );
			exit;
		}

		$parsed = self::parse_payload( $raw );
		if ( empty( $parsed['headers'] ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=' . Tables_Admin::IMPORT_SLUG . '&gs_tables_notice=import_failed' ) );
			exit;
		}

		if ( 'replace' === $mode && $target_id > 0 ) {
			wp_update_post(
				array(
					'ID' => $target_id,
					'post_title' => $parsed['title'],
				)
			);
			update_post_meta( $target_id, '_gs_table_caption', $parsed['caption'] );
			update_post_meta( $target_id, '_gs_table_headers', $parsed['headers'] );
			update_post_meta( $target_id, '_gs_table_rows', $parsed['rows'] );
			wp_safe_redirect( admin_url( 'admin.php?page=' . Tables_Admin::MENU_SLUG . '&action=edit&table_id=' . $target_id . '&gs_tables_notice=import_replaced' ) );
			exit;
		}

		if ( 'append' === $mode && $target_id > 0 ) {
			$existing_rows = (array) get_post_meta( $target_id, '_gs_table_rows', true );
			$merged_rows = array_merge( $existing_rows, $parsed['rows'] );
			update_post_meta( $target_id, '_gs_table_rows', $merged_rows );
			wp_safe_redirect( admin_url( 'admin.php?page=' . Tables_Admin::MENU_SLUG . '&action=edit&table_id=' . $target_id . '&gs_tables_notice=import_appended' ) );
			exit;
		}

		$post_id = wp_insert_post(
			array(
				'post_type' => Tables::POST_TYPE,
				'post_status' => 'draft',
				'post_title' => $parsed['title'],
			),
			true
		);
		if ( is_wp_error( $post_id ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=' . Tables_Admin::IMPORT_SLUG . '&gs_tables_notice=import_failed' ) );
			exit;
		}
		update_post_meta( $post_id, '_gs_table_caption', $parsed['caption'] );
		update_post_meta( $post_id, '_gs_table_headers', $parsed['headers'] );
		update_post_meta( $post_id, '_gs_table_rows', $parsed['rows'] );
		wp_safe_redirect( admin_url( 'admin.php?page=' . Tables_Admin::MENU_SLUG . '&action=edit&table_id=' . $post_id . '&gs_tables_notice=import_created' ) );
		exit;
	}

	private static function get_import_payload( $source ) {
		if ( 'manual' === $source ) {
			return isset( $_POST['import_manual'] ) ? (string) wp_unslash( $_POST['import_manual'] ) : '';
		}
		if ( 'url' === $source ) {
			$url = isset( $_POST['import_url'] ) ? esc_url_raw( wp_unslash( $_POST['import_url'] ) ) : '';
			return $url ? (string) wp_remote_retrieve_body( wp_remote_get( $url ) ) : '';
		}
		if ( 'server' === $source ) {
			$path = isset( $_POST['import_server_path'] ) ? (string) wp_unslash( $_POST['import_server_path'] ) : '';
			return ( $path && file_exists( $path ) ) ? (string) file_get_contents( $path ) : '';
		}
		if ( ! empty( $_FILES['import_file']['tmp_name'] ) ) {
			return (string) file_get_contents( $_FILES['import_file']['tmp_name'] );
		}
		return '';
	}

	private static function parse_payload( $raw ) {
		$raw = trim( (string) $raw );
		$result = array(
			'title' => __( 'Imported Table', 'wsuwp-plugin-graduate-school' ),
			'caption' => '',
			'headers' => array(),
			'rows' => array(),
		);

		if ( '' === $raw ) {
			return $result;
		}

		if ( 0 === strpos( $raw, '{' ) || 0 === strpos( $raw, '[' ) ) {
			$data = json_decode( $raw, true );
			if ( is_array( $data ) && isset( $data['tables'][0] ) ) {
				$table = $data['tables'][0];
				$result['title'] = isset( $table['title'] ) ? sanitize_text_field( (string) $table['title'] ) : $result['title'];
				$result['caption'] = isset( $table['caption'] ) ? sanitize_text_field( (string) $table['caption'] ) : '';
				$result['headers'] = isset( $table['headers'] ) && is_array( $table['headers'] ) ? array_map( 'sanitize_text_field', $table['headers'] ) : array();
				$result['rows'] = isset( $table['rows'] ) && is_array( $table['rows'] ) ? $table['rows'] : array();
				return $result;
			}
		}

		if ( false !== stripos( $raw, '<table' ) ) {
			$dom = new \DOMDocument();
			libxml_use_internal_errors( true );
			$dom->loadHTML( $raw );
			libxml_clear_errors();
			$table = $dom->getElementsByTagName( 'table' )->item( 0 );
			if ( $table ) {
				$trs = $table->getElementsByTagName( 'tr' );
				foreach ( $trs as $row_index => $tr ) {
					$row = array();
					foreach ( $tr->childNodes as $cell ) {
						if ( ! in_array( strtolower( $cell->nodeName ), array( 'td', 'th' ), true ) ) {
							continue;
						}
						$row[] = sanitize_text_field( trim( $cell->textContent ) );
					}
					if ( 0 === $row_index ) {
						$result['headers'] = $row;
					} else {
						$result['rows'][] = $row;
					}
				}
				return $result;
			}
		}

		$lines = preg_split( '/\r\n|\r|\n/', $raw );
		$rows = array();
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( '' === $line ) {
				continue;
			}
			$delimiter = ( false !== strpos( $line, ';' ) ) ? ';' : ',';
			$rows[] = str_getcsv( $line, $delimiter );
		}
		if ( ! empty( $rows ) ) {
			$result['headers'] = array_map( 'sanitize_text_field', (array) array_shift( $rows ) );
			$result['rows'] = $rows;
		}
		return $result;
	}
}
