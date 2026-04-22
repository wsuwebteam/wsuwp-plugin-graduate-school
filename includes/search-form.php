<?php

namespace WSUWP\Plugin\Graduate;

/**
 * Provides a [gs_search_form] shortcode that renders a search form with
 * WordPress category and tag filters. On submit the form redirects to
 * the site's native search results page (/?s=&category_name=&tag=),
 * mirroring the [wsuwp_search_form] pattern from WSUWP-Plugin-Embeds.
 *
 * @since 1.2.5
 */
class Search_Form {

	public static function init() {
		add_shortcode( 'gs_search_form', array( __CLASS__, 'render_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue_assets' ) );
	}

	public static function maybe_enqueue_assets() {
		global $post;

		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'gs_search_form' ) ) {
			self::enqueue_styles();
		}
	}

	private static function enqueue_styles() {
		if ( wp_style_is( 'gs-search-form', 'enqueued' ) ) {
			return;
		}

		\wp_enqueue_style(
			'gs-search-form',
			Plugin::get( 'url' ) . 'css/search-form.css',
			array(),
			Plugin::get( 'version' )
		);
	}

	/**
	 * Shortcode callback.
	 *
	 * Attributes:
	 *   site_category_slug – lock search to a WP category (e.g. gsvp, gscd)
	 *   tag_slug           – lock search to a WP tag
	 *
	 * When a slug is set the corresponding dropdown is replaced with a
	 * hidden input so the search is always scoped to that term.
	 *
	 * Usage:
	 *   [gs_search_form]
	 *   [gs_search_form site_category_slug="gsvp"]
	 *   [gs_search_form site_category_slug="gscd" tag_slug="fellowships"]
	 */
	public static function render_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'site_category_slug' => '',
			'tag_slug'           => '',
		), $atts, 'gs_search_form' );

		self::enqueue_styles();

		$locked_cat = ! empty( $atts['site_category_slug'] );
		$locked_tag =  empty( $atts['tag_slug'] );

		$categories = \get_terms( array(
			'taxonomy'   => 'category',
			'hide_empty' => true,
		) );

		$tags = \get_terms( array(
			'taxonomy'   => 'post_tag',
			'hide_empty' => true,
		) );

		ob_start();
		?>
		<div class="gs-search-form-wrap">
			<form class="gs-search-form" method="get" action="<?php echo esc_url( \trailingslashit( \get_home_url() ) ); ?>">
				<div class="gs-search-form-fields">
					<div class="gs-search-form-row gs-search-form-row--primary">
						<div class="gs-search-field gs-search-field--keyword">
							<label class="screen-reader-text" for="gs-s"><?php esc_html_e( 'Search for:', 'wsuwp-plugin-graduate-school' ); ?></label>
							<input type="text"
								   id="gs-s"
								   name="s"
								   value=""
								   placeholder="<?php esc_attr_e( 'Search', 'wsuwp-plugin-graduate-school' ); ?>" />
						</div>
						<div class="gs-search-form-actions">
							<input type="submit" id="gs-searchsubmit" class="gs-search-submit" value="<?php esc_attr_e( 'Search', 'wsuwp-plugin-graduate-school' ); ?>" />
						</div>
					</div>

					<?php if ( ! $locked_cat && ! is_wp_error( $categories ) && ! empty( $categories ) ) : ?>
						<div class="gs-search-field gs-search-field--taxonomy">
							<label for="gs-category"><?php esc_html_e( 'Category', 'wsuwp-plugin-graduate-school' ); ?></label>
							<select id="gs-category" name="category_name">
								<option value=""><?php esc_html_e( 'All Categories', 'wsuwp-plugin-graduate-school' ); ?></option>
								<?php foreach ( $categories as $term ) : ?>
									<option value="<?php echo esc_attr( $term->slug ); ?>">
										<?php echo esc_html( $term->name ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					<?php endif; ?>

					<?php if ( ! $locked_tag && ! is_wp_error( $tags ) && ! empty( $tags ) ) : ?>
						<div class="gs-search-field gs-search-field--taxonomy">
							<label for="gs-tag"><?php esc_html_e( 'Tag', 'wsuwp-plugin-graduate-school' ); ?></label>
							<select id="gs-tag" name="tag">
								<option value=""><?php esc_html_e( 'All Tags', 'wsuwp-plugin-graduate-school' ); ?></option>
								<?php foreach ( $tags as $term ) : ?>
									<option value="<?php echo esc_attr( $term->slug ); ?>">
										<?php echo esc_html( $term->name ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					<?php endif; ?>
				</div>

				<?php
				if ( $locked_cat ) {
					$cat_term = \get_term_by( 'slug', $atts['site_category_slug'], 'category' );
					if ( $cat_term ) {
						printf(
							'<input type="hidden" name="category_name" value="%s" />',
							esc_attr( $atts['site_category_slug'] )
						);
					}
				}

				if ( $locked_tag ) {
					$tag_term = \get_term_by( 'slug', $atts['tag_slug'], 'post_tag' );
					if ( $tag_term ) {
						printf(
							'<input type="hidden" name="tag" value="%s" />',
							esc_attr( $atts['tag_slug'] )
						);
					}
				}
				?>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}
}

Search_Form::init();
