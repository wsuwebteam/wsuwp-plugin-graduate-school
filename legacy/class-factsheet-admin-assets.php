<?php
/**
 * Admin assets (CSS/JS) for the factsheet post type and degree type taxonomy.
 *
 * @package WSUWP_Graduate_School
 */

class WSUWP_Factsheet_Admin_Assets {

	/**
	 * Post type slug (e.g. gs-factsheet).
	 *
	 * @var string
	 */
	private $post_type_slug;

	/**
	 * Degree type taxonomy slug (e.g. gs-degree-type).
	 *
	 * @var string
	 */
	private $taxonomy_degree_type;

	/**
	 * @param string $post_type_slug       Post type slug.
	 * @param string $taxonomy_degree_type Degree type taxonomy slug.
	 */
	public function __construct( $post_type_slug, $taxonomy_degree_type = 'gs-degree-type' ) {
		$this->post_type_slug       = $post_type_slug;
		$this->taxonomy_degree_type = $taxonomy_degree_type;
	}

	/**
	 * Enqueue scripts and styles used in the admin for factsheets and degree types.
	 *
	 * @since 0.4.0
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		if ( in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) && $this->post_type_slug === $screen->id ) {
			wp_deregister_script( 'yoast-seo-post-scraper' );
			wp_deregister_script( 'yoast-seo-term-scraper' );
			wp_deregister_script( 'yoast-seo-featured-image' );

			wp_enqueue_style( 'gsdp-admin', WSUWP\Plugin\Graduate\Plugin::get( 'url' ) . '/css/factsheet-admin.css', array(), WSUWP_Graduate_School_Theme()->theme_version() );
			wp_register_script( 'gsdp-factsheet-admin', WSUWP\Plugin\Graduate\Plugin::get( 'url' ) . '/js/factsheet-admin.min.js', array( 'jquery', 'underscore', 'jquery-ui-autocomplete' ), WSUWP_Graduate_School_Theme()->theme_version(), true );
			wp_enqueue_script( 'gsdp-factsheet-admin' );
     
		}
        // List screen: load admin CSS so "Add Factsheet" can be hidden for contributors.
		if ( 'edit.php' === $hook_suffix && isset( $screen->post_type ) && $this->post_type_slug === $screen->post_type ) {
			wp_enqueue_style( 'gsdp-admin', WSUWP\Plugin\Graduate\Plugin::get( 'url' ) . '/css/factsheet-admin.css', array(), WSUWP_Graduate_School_Theme()->theme_version() );
		}

		if ( in_array( $hook_suffix, array( 'edit-tags.php', 'term.php', 'term-new.php' ), true ) && $this->taxonomy_degree_type === $screen->taxonomy ) {
			wp_enqueue_style( 'gsdp-faculty-admin', WSUWP\Plugin\Graduate\Plugin::get( 'url' ) . '/css/faculty-admin.css', array(), WSUWP_Graduate_School_Theme()->theme_version() );
		}
	}
}