<?php
namespace WSUWP\Plugin\Graduate;

class Tables_Admin {

	const MENU_SLUG = 'gs-tables';
	const ADD_SLUG = 'gs-tables-new';
	const IMPORT_SLUG = 'gs-tables-import';
	const EXPORT_SLUG = 'gs-tables-export';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_redirect_legacy_screens' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_filter( 'parent_file', array( __CLASS__, 'highlight_parent_menu' ) );
		add_filter( 'submenu_file', array( __CLASS__, 'highlight_submenu' ) );

		add_action( 'admin_post_gs_tables_create', array( __CLASS__, 'handle_create' ) );
		add_action( 'admin_post_gs_tables_save', array( __CLASS__, 'handle_save' ) );
		add_action( 'admin_post_gs_tables_copy', array( __CLASS__, 'handle_copy' ) );
		add_action( 'admin_post_gs_tables_delete', array( __CLASS__, 'handle_delete' ) );
		add_action( 'admin_post_gs_tables_bulk', array( __CLASS__, 'handle_bulk' ) );
		add_action( 'admin_post_gs_tables_import', array( __CLASS__, 'handle_import' ) );
	}

	public static function register_menu() {
		add_menu_page(
			__( 'Tables', 'wsuwp-plugin-graduate-school' ),
			__( 'Tables', 'wsuwp-plugin-graduate-school' ),
			Tables::CAP_LIST,
			self::MENU_SLUG,
			array( __CLASS__, 'render_all_tables_page' ),
			'dashicons-list-view',
			26
		);
		add_submenu_page(
			self::MENU_SLUG,
			__( 'All Tables', 'wsuwp-plugin-graduate-school' ),
			__( 'All Tables', 'wsuwp-plugin-graduate-school' ),
			Tables::CAP_LIST,
			self::MENU_SLUG,
			array( __CLASS__, 'render_all_tables_page' )
		);
		add_submenu_page(
			self::MENU_SLUG,
			__( 'Add New Table', 'wsuwp-plugin-graduate-school' ),
			__( 'Add New Table', 'wsuwp-plugin-graduate-school' ),
			Tables::CAP_ADD,
			self::ADD_SLUG,
			array( __CLASS__, 'render_add_page' )
		);
		add_submenu_page(
			self::MENU_SLUG,
			__( 'Import a Table', 'wsuwp-plugin-graduate-school' ),
			__( 'Import a Table', 'wsuwp-plugin-graduate-school' ),
			Tables::CAP_EDIT,
			self::IMPORT_SLUG,
			array( __CLASS__, 'render_import_page' )
		);
		add_submenu_page(
			self::MENU_SLUG,
			__( 'Export a Table', 'wsuwp-plugin-graduate-school' ),
			__( 'Export a Table', 'wsuwp-plugin-graduate-school' ),
			Tables::CAP_EDIT,
			self::EXPORT_SLUG,
			array( __CLASS__, 'render_export_page' )
		);
	}

	public static function enqueue_assets( $hook ) {
		if ( false === strpos( (string) $hook, 'gs-tables' ) ) {
			return;
		}

		wp_enqueue_style(
			'gs-tables-admin',
			Plugin::get( 'url' ) . 'assets/css/tables-admin.css',
			array(),
			Plugin::get( 'version' )
		);
		wp_enqueue_script(
			'gs-tables-admin',
			Plugin::get( 'url' ) . 'assets/js/tables-admin.js',
			array( 'jquery' ),
			Plugin::get( 'version' ),
			true
		);
		wp_enqueue_media();
	}

	public static function maybe_redirect_legacy_screens() {
		if ( ! is_admin() ) {
			return;
		}

		$pagenow = isset( $GLOBALS['pagenow'] ) ? (string) $GLOBALS['pagenow'] : '';
		$post_type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : '';
		if ( 'edit.php' === $pagenow && Tables::POST_TYPE === $post_type ) {
			wp_safe_redirect( admin_url( 'admin.php?page=' . self::MENU_SLUG ) );
			exit;
		}

		if ( 'post-new.php' === $pagenow && Tables::POST_TYPE === $post_type ) {
			wp_safe_redirect( admin_url( 'admin.php?page=' . self::ADD_SLUG ) );
			exit;
		}

		if ( 'post.php' === $pagenow ) {
			$post_id = isset( $_GET['post'] ) ? absint( wp_unslash( $_GET['post'] ) ) : 0;
			$post = $post_id ? get_post( $post_id ) : null;
			$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';
			if ( $post instanceof \WP_Post && Tables::POST_TYPE === $post->post_type && 'edit' === $action ) {
				wp_safe_redirect(
					add_query_arg(
						array(
							'page' => self::MENU_SLUG,
							'action' => 'edit',
							'table_id' => $post_id,
						),
						admin_url( 'admin.php' )
					)
				);
				exit;
			}
		}
	}

	public static function highlight_parent_menu( $parent_file ) {
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		if ( 0 === strpos( $page, 'gs-tables' ) ) {
			return self::MENU_SLUG;
		}
		return $parent_file;
	}

	public static function highlight_submenu( $submenu_file ) {
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';
		if ( self::IMPORT_SLUG === $page ) {
			return self::IMPORT_SLUG;
		}
		if ( self::EXPORT_SLUG === $page ) {
			return self::EXPORT_SLUG;
		}
		if ( self::ADD_SLUG === $page ) {
			return self::ADD_SLUG;
		}
		if ( self::MENU_SLUG === $page && 'edit' === $action ) {
			return self::MENU_SLUG;
		}
		return $submenu_file;
	}

	public static function render_tabs( $active_slug ) {
		$tabs = array(
			self::MENU_SLUG => __( 'All Tables', 'wsuwp-plugin-graduate-school' ),
			self::ADD_SLUG => __( 'Add New', 'wsuwp-plugin-graduate-school' ),
			self::IMPORT_SLUG => __( 'Import', 'wsuwp-plugin-graduate-school' ),
			self::EXPORT_SLUG => __( 'Export', 'wsuwp-plugin-graduate-school' ),
		);
		echo '<h2 class="nav-tab-wrapper gs-admin-tabs">';
		foreach ( $tabs as $slug => $label ) {
			$class = self::tab_is_active( $slug, $active_slug ) ? ' nav-tab nav-tab-active' : ' nav-tab';
			echo '<a class="' . esc_attr( trim( $class ) ) . '" href="' . esc_url( admin_url( 'admin.php?page=' . $slug ) ) . '">' . esc_html( $label ) . '</a>';
		}
		echo '</h2>';
	}

	private static function tab_is_active( $tab_slug, $active_slug ) {
		if ( self::MENU_SLUG === $tab_slug && 'edit' === $active_slug ) {
			return true;
		}
		return $tab_slug === $active_slug;
	}

	public static function render_all_tables_page() {
		$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';
		if ( 'preview' === $action ) {
			Tables_Admin_Edit::render_preview_screen();
			return;
		}
		if ( 'edit' === $action ) {
			Tables_Admin_Edit::render_edit_screen();
			return;
		}
		Tables_Admin_List::render_page();
	}

	public static function render_add_page() {
		Tables_Admin_Edit::render_add_screen();
	}

	public static function render_import_page() {
		Tables_Admin_Import::render_page();
	}

	public static function render_export_page() {
		Tables_Admin_Export::render_page();
	}

	public static function handle_create() {
		Tables_Admin_Edit::handle_create();
	}

	public static function handle_save() {
		Tables_Admin_Edit::handle_save();
	}

	public static function handle_copy() {
		Tables_Admin_Edit::handle_copy();
	}

	public static function handle_delete() {
		Tables_Admin_Edit::handle_delete();
	}

	public static function handle_bulk() {
		Tables_Admin_List::handle_bulk();
	}

	public static function handle_import() {
		Tables_Admin_Import::handle_import();
	}
}

Tables_Admin::init();
