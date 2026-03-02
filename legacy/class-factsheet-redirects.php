<?php

/**
 * Class Factsheet_Redirects
 *
 * @package WSUWP_Graduate_School
 */
class WSUWP_Factsheet_Redirects  {
	/**
	 * Post type slug (e.g. gs-factsheet).
	 *
	 * @var string
	 */
	private $post_type_slug;

	/**
	 * Archive URL slug (e.g. degrees).
	 *
	 * @var string
	 */
	private $archive_slug;

	/**
	 * @param string $post_type_slug Post type slug.
	 * @param string $archive_slug   Archive slug for redirects.
	 */
	public function __construct( $post_type_slug, $archive_slug ) {
		$this->post_type_slug = $post_type_slug;
		$this->archive_slug   = $archive_slug;
	}

	/**
	 * Redirects a given degree ID to either the current degree URL or
	 * to the degrees landing page.
	 * ...
	 */
	public function redirect_factsheet_id( $degree_id ) {
		$matches = get_posts( array(
			'post_type' => $this->post_type_slug,
			'meta_key' => 'gsdp_degree_id',
			'meta_value' => $degree_id,
		) );

		if ( 0 !== count( $matches ) ) {
			$redirect_url = get_permalink( $matches[0]->ID );
			wp_safe_redirect( $redirect_url, 301 );
			exit();
		} else {
			wp_safe_redirect( home_url( '/' . $this->archive_slug . '/' ), 302 );
			exit();
		}
	}

	/**
	 * Redirects old factsheet ID URLs to their new URL or to the
	 * factsheets landing page.
	 *
	 * @since 1.0.0
	 *
	 * @global WP_Query $wp_query
	 */
	public function redirect_old_factsheet_urls() {
		global $wp_query;

		if ( $wp_query->is_404() && isset( $wp_query->query['post_type'] ) && $this->post_type_slug === $wp_query->query['post_type'] ) {
			if ( is_numeric( $wp_query->query[ $this->post_type_slug ] ) ) {
				$degree_id = absint( $wp_query->query[ $this->post_type_slug ] );
				$this->redirect_factsheet_id( $degree_id );
			}
		}
	}

	/**
	 * Redirects published factsheets that are set to not be included in the
	 * program list. If the factsheet is a draft, then it can be previewed by
	 * those who have access.
	 *
	 * @since 0.10.0
	 */
	public function redirect_private_factsheets() {
		if ( ! is_singular( $this->post_type_slug ) ) {
			return;
		}

		if ( 'draft' === get_post_status( get_the_ID() ) ) {
			return;
		}

		// if ( 1 !== absint( get_post_meta( get_the_ID(), 'gsdp_include_in_programs', true ) ) ) {
		// 	wp_redirect( home_url( '/' . $this->archive_slug . '/' ) );
		// 	exit();
		// }
	}
}