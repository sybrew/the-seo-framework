<?php
/**
 * @package The_SEO_Framework\Classes
 * @subpackage Classes\Deprecated
 */

namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Class The_SEO_Framework\Deprecated
 *
 * Contains all deprecated functions.
 *
 * @since 2.8.0
 * @since 3.1.0: Removed all methods deprecated in 3.0.0.
 * @since 3.3.0: Removed all methods deprecated in 3.1.0.
 * @ignore
 */
final class Deprecated {

	/**
	 * Constructor. Does nothing.
	 */
	public function __construct() { }

	/**
	 * Returns a filterable sequential array of default scripts.
	 *
	 * @since 3.2.2
	 * @since 3.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return array
	 */
	public function get_default_scripts() {

		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->get_default_scripts()', '3.3.0' );

		return array_merge(
			\The_SEO_Framework\Bridges\Scripts::get_tsf_scripts(),
			\The_SEO_Framework\Bridges\Scripts::get_tt_scripts()
		);
	}

	/**
	 * Enqueues Gutenberg-related scripts.
	 *
	 * @since 3.2.0
	 * @since 3.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return void Early if already enqueued.
	 */
	public function enqueue_gutenberg_compat_scripts() {

		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->enqueue_gutenberg_compat_scripts()', '3.3.0' );

		if ( \The_SEO_Framework\_has_run( __METHOD__ ) ) return;

		\The_SEO_Framework\Builders\Scripts::register(
			\The_SEO_Framework\Bridges\Scripts::get_gutenberg_compat_scripts()
		);
	}

	/**
	 * Enqueues Media Upload and Cropping scripts.
	 *
	 * @since 3.1.0
	 * @since 3.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return void Early if already enqueued.
	 */
	public function enqueue_media_scripts() {

		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->enqueue_media_scripts()', '3.3.0' );

		if ( \The_SEO_Framework\_has_run( __METHOD__ ) ) return;

		$args = [];
		if ( $tsf->is_post_edit() ) {
			$args['post'] = $tsf->get_the_real_admin_ID();
		}
		\wp_enqueue_media( $args );

		\The_SEO_Framework\Builders\Scripts::register(
			\The_SEO_Framework\Bridges\Scripts::get_media_scripts()
		);
	}

	/**
	 * Enqueues Primary Term Selection scripts.
	 *
	 * @since 3.1.0
	 * @since 3.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return void Early if already enqueued.
	 */
	public function enqueue_primaryterm_scripts() {

		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->enqueue_primaryterm_scripts()', '3.3.0' );

		if ( \The_SEO_Framework\_has_run( __METHOD__ ) ) return;

		\The_SEO_Framework\Builders\Scripts::register(
			\The_SEO_Framework\Bridges\Scripts::get_primaryterm_scripts()
		);
	}

	/**
	 * Includes the necessary sortable metabox scripts.
	 *
	 * @since 2.2.2
	 */
	public function metabox_scripts() {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->metabox_scripts()', '3.3.0', '\The_SEO_Framework\Bridges\Scripts::prepare_metabox_scripts()' );
		\The_SEO_Framework\Bridges\Scripts::prepare_metabox_scripts();
	}

	/**
	 * Returns the SEO Bar.
	 *
	 * @since 3.0.4
	 * @since 3.3.0 Deprecated
	 * @staticvar string $type
	 * @deprecated
	 *
	 * @param string $column the current column : If it's a taxonomy, this is empty
	 * @param int    $post_id the post id       : If it's a taxonomy, this is the column name
	 * @param string $tax_id this is empty      : If it's a taxonomy, this is the taxonomy id
	 */
	public function get_seo_bar( $column, $post_id, $tax_id ) {

		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->post_status()', '3.3.0', 'the_seo_framework()->get_generated_seo_bar()' );

		$type = \get_post_type( $post_id );

		if ( false === $type || '' !== $tax_id ) {
			$type = $tsf->get_current_taxonomy();
		}

		if ( '' !== $tax_id ) {
			$column  = $post_id;
			$post_id = $tax_id;
		}

		return $tsf->post_status( $post_id, $type );
	}

	/**
	 * Renders post status. Caches the output.
	 *
	 * @since 2.1.9
	 * @staticvar string $post_i18n The post type slug.
	 * @staticvar bool $is_term If we're dealing with TT pages.
	 * @since 2.8.0 Third parameter `$echo` has been put into effect.
	 * @since 3.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int    $post_id The Post ID or taxonomy ID.
	 * @param string $type The content type.
	 * @param bool   $echo Whether to echo the value. Does not eliminate return.
	 * @return string|void $content The post SEO status. Void if $echo is true.
	 */
	public function post_status( $post_id, $type = '', $echo = false ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->post_status()', '3.3.0', 'the_seo_framework()->get_generated_seo_bar()' );

		if ( ! $post_id )
			$post_id = $tsf->get_the_real_ID();

		if ( 'inpost' === $type || ! $type ) {
			$type = \get_post_type( $post_id );
		}

		if ( $tsf->is_post_type_page( $type ) ) {
			$post_i18n = $tsf->get_post_type_label( $type );
			$post_type = $type;
		} else {
			$taxonomy  = $tsf->get_current_taxonomy();
			$post_type = $tsf->get_admin_post_type();
		}

		$bar = $tsf->get_generated_seo_bar( [
			'id'        => $post_id,
			'post_type' => $post_type,
			'taxonomy'  => $taxonomy,
		] );

		if ( $echo ) {
			echo $bar; // phpcs:ignore -- XSS ok.
		} else {
			return $bar;
		}
	}

	/**
	 * Returns the static scripts class object.
	 *
	 * The first letter of the method is capitalized, to indicate it's a class caller.
	 *
	 * @since 3.1.0
	 * @since 3.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The scripts class name.
	 */
	public function Scripts() { // phpcs:ignore -- Deprecated.
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->Scripts()', '3.3.0', '\The_SEO_Framework\Builders\Scripts::class' );
		return \The_SEO_Framework\Builders\Scripts::class;
	}

	/**
	 * Determines if we're doing ajax.
	 *
	 * @since 2.9.0
	 * @since 3.3.0 1. Now uses wp_doing_ajax()
	 *              2. Deprecated.
	 * @deprecated
	 *
	 * @return bool True if AJAX
	 */
	public function doing_ajax() {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->doing_ajax()', '3.3.0', 'wp_doing_ajax' );
		return \wp_doing_ajax();
	}

	/**
	 * Whether to lowercase the noun or keep it UCfirst.
	 * Depending if language is German.
	 *
	 * @since 2.6.0
	 * @since 3.3.0 Deprecated
	 * @deprecated
	 * @staticvar array $lowercase Contains nouns.
	 *
	 * @param string $noun The noun to lowercase.
	 * @return string The maybe lowercase noun.
	 */
	public function maybe_lowercase_noun( $noun ) {

		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->maybe_lowercase_noun()', '3.3.0' );

		static $lowercase = [];

		if ( isset( $lowercase[ $noun ] ) )
			return $lowercase[ $noun ];

		return $lowercase[ $noun ] = \the_seo_framework()->check_wp_locale( 'de' ) ? $noun : strtolower( $noun );
	}

	/**
	 * Detect WordPress language.
	 * Considers en_UK, en_US, en, etc.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Removed caching.
	 *
	 * @param string $locale Required, the locale.
	 * @return bool Whether the input $locale is in the current WordPress locale.
	 */
	public function check_wp_locale( $locale = '' ) {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->check_wp_locale()', '3.3.0' );
		return false !== strpos( \get_locale(), $locale );
	}

	/**
	 * Initializes term meta data filters and functions.
	 *
	 * @since 2.7.0
	 * @since 3.0.0 No longer checks for admin query.
	 * @since 3.3.0 Deprecated.
	 * @deprecated
	 */
	public function initialize_term_meta() {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->initialize_term_meta()', '3.3.0', '\the_seo_framework()->init_term_meta()' );
		\the_seo_framework()->init_term_meta();
	}

	/**
	 * Ping search engines on post publish.
	 *
	 * @since 2.2.9
	 * @since 2.8.0 Only worked when the blog was not public...
	 * @since 3.1.0 Now allows one ping per language.
	 *              @uses $this->add_cache_key_suffix()
	 * @since 3.2.3 1. Now works as intended again.
	 *              2. Removed Easter egg.
	 * @since 3.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return void Early if blog is not public.
	 */
	public static function ping_searchengines() {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->ping_searchengines()', '3.3.0', '\The_SEO_Framework\Bridges\Ping::ping_search_engines()' );
		\The_SEO_Framework\Bridges\Ping::ping_search_engines();
	}

	/**
	 * Pings the sitemap location to Google.
	 *
	 * @since 2.2.9
	 * @since 3.1.0 Updated ping URL. Old one still worked, too.
	 * @since 3.3.0 Deprecated.
	 * @deprecated
	 * @link https://support.google.com/webmasters/answer/6065812?hl=en
	 */
	public static function ping_google() {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->ping_google()', '3.3.0', '\The_SEO_Framework\Bridges\Ping::ping_google()' );
		\The_SEO_Framework\Bridges\Ping::ping_google();
	}

	/**
	 * Pings the sitemap location to Bing.
	 *
	 * @since 2.2.9
	 * @since 3.2.3 Updated ping URL. Old one still worked, too.
	 * @since 3.3.0 Deprecated.
	 * @deprecated
	 * @link https://www.bing.com/webmaster/help/how-to-submit-sitemaps-82a15bd4
	 */
	public static function ping_bing() {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->ping_bing()', '3.3.0', '\The_SEO_Framework\Bridges\Ping::ping_bing()' );
		\The_SEO_Framework\Bridges\Ping::ping_bing();
	}

	/**
	 * Returns the stylesheet XSL location URL.
	 *
	 * @since 2.8.0
	 * @since 3.0.0 1: No longer uses home URL from cache. But now uses `get_home_url()`.
	 *              2: Now takes query parameters (if any) and restores them correctly.
	 * @global \WP_Rewrite $wp_rewrite
	 *
	 * @return string URL location of the XSL stylesheet. Unescaped.
	 */
	public function get_sitemap_xsl_url() {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->get_sitemap_xsl_url()', '3.3.0', '\The_SEO_Framework\Bridges\Sitemap::get_instance()->get_expected_sitemap_endpoint_url(\'xsl-stylesheet\')' );
		return \The_SEO_Framework\Bridges\Sitemap::get_instance()->get_expected_sitemap_endpoint_url( 'xsl-stylesheet' );
	}

	/**
	 * Returns the sitemap XML location URL.
	 *
	 * @since 2.9.2
	 * @since 3.0.0 1: No longer uses home URL from cache. But now uses `get_home_url()`.
	 *              2: Now takes query parameters (if any) and restores them correctly.
	 * @global \WP_Rewrite $wp_rewrite
	 *
	 * @return string URL location of the XML sitemap. Unescaped.
	 */
	public function get_sitemap_xml_url() {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->get_sitemap_xml_url()', '3.3.0', '\The_SEO_Framework\Bridges\Sitemap::get_instance()->get_expected_sitemap_endpoint_url()' );
		return \The_SEO_Framework\Bridges\Sitemap::get_instance()->get_expected_sitemap_endpoint_url();
	}

	/**
	 * Sitemap XSL stylesheet output.
	 *
	 * @since 2.8.0
	 * @since 3.1.0 1. Now outputs 200-response code.
	 *              2. Now outputs robots tag, preventing indexing.
	 *              3. Now overrides other header tags.
	 */
	public function output_sitemap_xsl_stylesheet() {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->output_sitemap_xsl_stylesheet()', '3.3.0' );
		return \The_SEO_Framework\Bridges\Sitemap::get_instance()->output_stylesheet();
	}

	/**
	 * Determines if post type supports The SEO Framework.
	 *
	 * @since 2.3.9
	 * @since 3.1.0 1. Removed caching.
	 *              2. Now works in admin.
	 * @since 3.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $post_type Optional. The post type to check.
	 * @return bool true of post type is supported.
	 */
	public function post_type_supports_custom_seo( $post_type = '' ) {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->post_type_supports_custom_seo()', '3.3.0', 'the_seo_framework()->is_post_type_supported()' );
		return \the_seo_framework()->is_post_type_supported( $post_type );
	}

	/**
	 * Determines if the taxonomy supports The SEO Framework.
	 *
	 * Checks if at least one taxonomy objects post type supports The SEO Framework,
	 * and wether the taxonomy is public and rewritable.
	 *
	 * @since 3.1.0
	 * @since 3.3.0 1. Now goes over all post types for the taxonomy.
	 *              2. Can now return true if at least one post type for the taxonomy is supported.
	 *              3. Deprecated.
	 * @deprecated
	 *
	 * @param string $taxonomy Optional. The taxonomy name.
	 * @return bool True if at least one post type in taxonomy isn't disabled.
	 */
	public function taxonomy_supports_custom_seo( $taxonomy = '' ) {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->taxonomy_supports_custom_seo()', '3.3.0', 'the_seo_framework()->is_taxonomy_supported()' );
		return \the_seo_framework()->is_taxonomy_supported( $taxonomy );
	}

	/**
	 * Returns taxonomical canonical URL.
	 * Automatically adds pagination if the ID matches the query.
	 *
	 * @since 3.0.0
	 * @since 3.3.0 Deprecated
	 * @deprecated
	 *
	 * @param int    $term_id The term ID.
	 * @param string $taxonomy The taxonomy.
	 * @return string The taxonomical canonical URL, if any.
	 */
	public function get_taxonomial_canonical_url( $term_id, $taxonomy ) {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->get_taxonomial_canonical_url()', '3.3.0', 'the_seo_framework()->get_taxonomical_canonical_url()' );
		return \the_seo_framework()->get_taxonomical_canonical_url( $term_id, $taxonomy );
	}

	/**
	 * Tries to fetch a term by $id from query.
	 *
	 * @since 2.6.0
	 * @since 3.0.0 Can now get custom post type objects.
	 * @since 3.3.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $id The possible taxonomy Term ID.
	 * @return false|object The Term object.
	 */
	public function fetch_the_term( $id = '' ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->fetch_the_term()', '3.3.0' );

		static $term = [];

		if ( isset( $term[ $id ] ) )
			return $term[ $id ];

		//* Return null if no term can be detected.
		if ( false === $tsf->is_archive() )
			return false;

		if ( $tsf->is_admin() ) {
			$taxonomy = $tsf->get_current_taxonomy();
			if ( $taxonomy ) {
				$term_id     = $id ?: $tsf->get_the_real_admin_ID();
				$term[ $id ] = \get_term_by( 'id', $term_id, $taxonomy );
			}
		} else {
			if ( $tsf->is_category() || $tsf->is_tag() ) {
				$term[ $id ] = \get_queried_object();
			} elseif ( $tsf->is_tax() ) {
				$term[ $id ] = \get_term_by( 'slug', \get_query_var( 'term' ), \get_query_var( 'taxonomy' ) );
			} elseif ( \is_post_type_archive() ) {
				$post_type = \get_query_var( 'post_type' );
				$post_type = is_array( $post_type ) ? reset( $post_type ) : $post_type;

				$term[ $id ] = \get_post_type_object( $post_type );
			}
		}

		if ( isset( $term[ $id ] ) )
			return $term[ $id ];

		return $term[ $id ] = false;
	}

	/**
	 * Return custom field post meta data.
	 *
	 * Return only the first value of custom field. Return false if field is
	 * blank or not set.
	 *
	 * @since 2.0.0
	 * @since 3.3.0 Deprecated
	 * @deprecated
	 * @staticvar array $field_cache
	 *
	 * @param string $field     Custom field key.
	 * @param int    $post_id   The post ID.
	 * @return mixed|boolean Return value or false on failure.
	 */
	public function get_custom_field( $field, $post_id = null ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->get_custom_field()', '3.3.0', 'the_seo_framework()->get_post_meta_item()' );

		//* If field is falsesque, get_post_meta() will return an array.
		if ( ! $field )
			return false;

		static $field_cache = [];

		if ( isset( $field_cache[ $field ][ $post_id ] ) )
			return $field_cache[ $field ][ $post_id ];

		if ( empty( $post_id ) )
			$post_id = $tsf->get_the_real_ID();

		$custom_field = \get_post_meta( $post_id, $field, true );

		//* If custom field is empty, empty cache..
		if ( empty( $custom_field ) )
			$field_cache[ $field ][ $post_id ] = '';

		//* Render custom field, slashes stripped, sanitized if string
		$field_cache[ $field ][ $post_id ] = is_array( $custom_field ) ? \stripslashes_deep( $custom_field ) : stripslashes( $custom_field );

		return $field_cache[ $field ][ $post_id ];
	}
}
