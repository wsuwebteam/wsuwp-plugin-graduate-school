<?php
namespace WSUWP\Plugin\Graduate;

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class GS_Tables_List_Table extends \WP_List_Table {

	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'gs_table',
				'plural'   => 'gs_tables',
				'ajax'     => false,
			)
		);
	}

	public function get_columns() {
		return array(
			'cb' => '<input type="checkbox" />',
			'id' => __( 'ID', 'wsuwp-plugin-graduate-school' ),
			'name' => __( 'Table Name', 'wsuwp-plugin-graduate-school' ),
			'description' => __( 'Description', 'wsuwp-plugin-graduate-school' ),
			'author' => __( 'Author', 'wsuwp-plugin-graduate-school' ),
			'modified' => __( 'Last Modified', 'wsuwp-plugin-graduate-school' ),
		);
	}

	protected function get_sortable_columns() {
		return array(
			'id' => array( 'ID', true ),
			'name' => array( 'title', false ),
			'author' => array( 'author', false ),
			'modified' => array( 'modified', false ),
		);
	}

	protected function get_bulk_actions() {
		return array(
			'copy' => __( 'Copy', 'wsuwp-plugin-graduate-school' ),
			'export' => __( 'Export', 'wsuwp-plugin-graduate-school' ),
			'delete' => __( 'Delete', 'wsuwp-plugin-graduate-school' ),
		);
	}

	protected function column_cb( $item ) {
		return '<input type="checkbox" name="table_ids[]" value="' . esc_attr( (string) $item->ID ) . '" />';
	}

	public function column_name( $item ) {
		$edit_url = add_query_arg(
			array(
				'page' => Tables_Admin::MENU_SLUG,
				'action' => 'edit',
				'table_id' => $item->ID,
			),
			admin_url( 'admin.php' )
		);
		$preview_url = Tables::get_preview_url( $item->ID );
		$row_actions = array(
			'edit' => '<a href="' . esc_url( $edit_url ) . '">' . esc_html__( 'Edit', 'wsuwp-plugin-graduate-school' ) . '</a>',
			'gs_preview' => '<a href="' . esc_url( $preview_url ) . '">' . esc_html__( 'Preview', 'wsuwp-plugin-graduate-school' ) . '</a>',
			'copy' => '<a href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=gs_tables_copy&table_id=' . $item->ID ), 'gs_tables_copy_' . $item->ID ) ) . '">' . esc_html__( 'Copy', 'wsuwp-plugin-graduate-school' ) . '</a>',
			'export' => '<a href="' . esc_url( admin_url( 'admin.php?page=' . Tables_Admin::EXPORT_SLUG . '&table_ids=' . $item->ID ) ) . '">' . esc_html__( 'Export', 'wsuwp-plugin-graduate-school' ) . '</a>',
			'delete' => '<a href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=gs_tables_delete&table_id=' . $item->ID ), 'gs_tables_delete_' . $item->ID ) ) . '">' . esc_html__( 'Delete', 'wsuwp-plugin-graduate-school' ) . '</a>',
		);

		return '<a href="' . esc_url( $edit_url ) . '"><strong>' . esc_html( $item->post_title ) . '</strong></a>' . $this->row_actions( $row_actions );
	}

	public function column_id( $item ) {
		return (string) $item->ID;
	}

	public function column_description( $item ) {
		return esc_html( (string) get_post_meta( $item->ID, '_gs_table_caption', true ) );
	}

	public function column_author( $item ) {
		$author = get_userdata( (int) $item->post_author );
		return $author ? esc_html( $author->display_name ) : '';
	}

	public function column_modified( $item ) {
		return esc_html( get_date_from_gmt( $item->post_modified_gmt, 'M j, Y g:i a' ) );
	}

	public function prepare_items() {
		$per_page = 20;
		$current_page = $this->get_pagenum();
		$search = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
		$order_by = isset( $_REQUEST['orderby'] ) ? sanitize_key( wp_unslash( $_REQUEST['orderby'] ) ) : 'ID';
		$order = isset( $_REQUEST['order'] ) ? sanitize_key( wp_unslash( $_REQUEST['order'] ) ) : 'DESC';
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable, 'name' );

		$query_args = array(
			'post_type' => Tables::POST_TYPE,
			'post_status' => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => $per_page,
			'paged' => $current_page,
			'orderby' => $order_by,
			'order' => in_array( strtoupper( $order ), array( 'ASC', 'DESC' ), true ) ? strtoupper( $order ) : 'DESC',
		);
		if ( '' !== $search ) {
			$query_args['s'] = $search;
		}

		$query = new \WP_Query( $query_args );
		$this->items = $query->posts;
		$this->set_pagination_args(
			array(
				'total_items' => (int) $query->found_posts,
				'per_page' => $per_page,
			)
		);
	}
}

class Tables_Admin_List {
	public static function render_page() {
		if ( ! current_user_can( Tables::CAP_LIST ) ) {
			wp_die( esc_html__( 'You do not have permission to view tables.', 'wsuwp-plugin-graduate-school' ) );
		}
		$list_table = new GS_Tables_List_Table();
		$list_table->prepare_items();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'All Tables', 'wsuwp-plugin-graduate-school' ); ?></h1>
			<?php Tables_Admin::render_tabs( Tables_Admin::MENU_SLUG ); ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php?action=gs_tables_bulk' ) ); ?>">
				<?php wp_nonce_field( 'gs_tables_bulk', 'gs_tables_bulk_nonce' ); ?>
				<?php $list_table->search_box( __( 'Search Tables', 'wsuwp-plugin-graduate-school' ), 'gs-tables-search' ); ?>
				<?php $list_table->display(); ?>
			</form>
		</div>
		<?php
	}

	public static function handle_bulk() {
		if ( ! current_user_can( Tables::CAP_EDIT ) ) {
			wp_die( esc_html__( 'You do not have permission to manage tables.', 'wsuwp-plugin-graduate-school' ) );
		}
		check_admin_referer( 'gs_tables_bulk', 'gs_tables_bulk_nonce' );

		$table_ids = isset( $_POST['table_ids'] ) ? array_values( array_filter( array_map( 'absint', (array) wp_unslash( $_POST['table_ids'] ) ) ) ) : array();
		$bulk_action = isset( $_POST['action'] ) ? sanitize_key( wp_unslash( $_POST['action'] ) ) : '';
		if ( '-1' === $bulk_action ) {
			$bulk_action = isset( $_POST['action2'] ) ? sanitize_key( wp_unslash( $_POST['action2'] ) ) : '';
		}

		if ( empty( $table_ids ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=' . Tables_Admin::MENU_SLUG ) );
			exit;
		}

		if ( 'delete' === $bulk_action ) {
			foreach ( $table_ids as $table_id ) {
				Tables::delete_table( $table_id );
			}
			wp_safe_redirect( admin_url( 'admin.php?page=' . Tables_Admin::MENU_SLUG . '&gs_tables_notice=bulk_deleted' ) );
			exit;
		}

		if ( 'copy' === $bulk_action ) {
			foreach ( $table_ids as $table_id ) {
				Tables::duplicate_table( $table_id );
			}
			wp_safe_redirect( admin_url( 'admin.php?page=' . Tables_Admin::MENU_SLUG . '&gs_tables_notice=bulk_copied' ) );
			exit;
		}

		if ( 'export' === $bulk_action ) {
			$export_url = add_query_arg(
				array(
					'page' => Tables_Admin::EXPORT_SLUG,
					'table_ids' => implode( ',', $table_ids ),
				),
				admin_url( 'admin.php' )
			);
			wp_safe_redirect( $export_url );
			exit;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=' . Tables_Admin::MENU_SLUG ) );
		exit;
	}
}
