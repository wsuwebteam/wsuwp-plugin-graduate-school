<?php

/**
 * Factsheet archive, query vars, and menus.
 *
 * @package WSUWP_Graduate_School
 */
class WSUWP_Factsheet_Archive {

	/**
	 * Post type slug (e.g. gs-factsheet).
	 *
	 * @var string
	 */
	private $post_type_slug;

	/**
	 * @param string $post_type_slug Post type slug.
	 */
	public function __construct( $post_type_slug ) {
		$this->post_type_slug = $post_type_slug;
	}

	/**
	 * Add our custom query variable to the set of default query variables.
	 *
	 * @since 1.4.0
	 *
	 * @param array $vars
	 *
	 * @return array
	 */
	public function add_gradfair_query_var( $vars ) {
		$vars[] = 'gradfair';
		return $vars;
	}

	/**
	 * Register a mirror navigation area for grad fair usage.
	 *
	 * @since 1.4.0
	 */
	public function register_mirror_menu() {
		register_nav_menus(
			array(
				'gradfair' => 'WSU Grad Fair',
			)
		);
	}

	/**
	 * Adjusts the archive query for factsheets to show all factsheets.
	 *
	 * @since 0.8.0
	 *
	 * @param \WP_Query $query
	 */
	public function adjust_factsheet_archive_query( $query ) {
		if ( is_post_type_archive( $this->post_type_slug ) ) {
			$query->set( 'posts_per_page', -1 );
		}
	}

	/**
	 * Alters the title displayed for the factsheets landing page.
	 *
	 * @since 1.1.0
	 *
	 * @param string $view_title
	 * @param string $site_title
	 * @param string $global_title
	 *
	 * @return string
	 */
	public function filter_factsheet_archive_title( $view_title, $site_title, $global_title ) {
		if ( is_post_type_archive( $this->post_type_slug ) ) {
			return 'Graduate Degree Programs | ' . $site_title . $global_title;
		}
		return $view_title;
	}
}