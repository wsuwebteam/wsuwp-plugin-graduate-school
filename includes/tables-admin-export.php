<?php
namespace WSUWP\Plugin\Graduate;

class Tables_Admin_Export {

	public static function render_page() {
		if ( ! current_user_can( Tables::CAP_EDIT ) ) {
			wp_die( esc_html__( 'You do not have permission to export tables.', 'wsuwp-plugin-graduate-school' ) );
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
		$preselected = isset( $_GET['table_ids'] ) ? array_filter( array_map( 'absint', explode( ',', (string) wp_unslash( $_GET['table_ids'] ) ) ) ) : array();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Export a Table', 'wsuwp-plugin-graduate-school' ); ?></h1>
			<?php Tables_Admin::render_tabs( Tables_Admin::EXPORT_SLUG ); ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="gs-export-form">
				<?php wp_nonce_field( 'gs_tables_export', 'gs_tables_export_nonce' ); ?>
				<input type="hidden" name="action" value="gs_tables_export" />
				<div class="postbox"><h2 class="hndle"><span><?php esc_html_e( 'Export Tables', 'wsuwp-plugin-graduate-school' ); ?></span></h2><div class="inside">
					<p><label><input type="checkbox" id="gs-export-select-all" /> <?php esc_html_e( 'Select all', 'wsuwp-plugin-graduate-school' ); ?></label></p>
					<select name="table_ids[]" multiple size="12" class="large-text code" id="gs-export-table-select">
						<?php foreach ( $tables as $table ) : ?>
							<option value="<?php echo esc_attr( (string) $table->ID ); ?>" <?php selected( in_array( $table->ID, $preselected, true ) ); ?>><?php echo esc_html( 'ID ' . $table->ID . ': ' . $table->post_title ); ?></option>
						<?php endforeach; ?>
					</select>
					<p>
						<label for="gs-export-format"><?php esc_html_e( 'Export Format', 'wsuwp-plugin-graduate-school' ); ?></label>
						<select name="format" id="gs-export-format">
							<option value="csv"><?php esc_html_e( 'CSV - Character-Separated Values', 'wsuwp-plugin-graduate-school' ); ?></option>
							<option value="html"><?php esc_html_e( 'HTML - Hypertext Markup Language', 'wsuwp-plugin-graduate-school' ); ?></option>
							<option value="json"><?php esc_html_e( 'JSON - JavaScript Object Notation', 'wsuwp-plugin-graduate-school' ); ?></option>
						</select>
					</p>
					<p id="gs-export-delimiter-wrap">
						<label for="gs-export-delimiter"><?php esc_html_e( 'CSV Delimiter', 'wsuwp-plugin-graduate-school' ); ?></label>
						<select name="csv_delimiter" id="gs-export-delimiter">
							<option value="comma"><?php esc_html_e( ', (comma)', 'wsuwp-plugin-graduate-school' ); ?></option>
							<option value="semicolon"><?php esc_html_e( '; (semicolon)', 'wsuwp-plugin-graduate-school' ); ?></option>
							<option value="tabulator"><?php esc_html_e( 'tabulator', 'wsuwp-plugin-graduate-school' ); ?></option>
						</select>
					</p>
					<p><label><input type="checkbox" name="zip" id="gs-export-zip" value="1" /> <?php esc_html_e( 'Create a ZIP archive', 'wsuwp-plugin-graduate-school' ); ?></label></p>
					<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Download Export File', 'wsuwp-plugin-graduate-school' ); ?></button></p>
				</div></div>
			</form>
		</div>
		<?php
	}
}
