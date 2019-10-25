<?php
/**
 * @package The_SEO_Framework\Classes\Deprecated
 * @subpackage The_SEO_Framework\Debug\Deprecated
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
 * @since 4.0.0: Removed all methods deprecated in 3.1.0.
 * @ignore
 */
final class Deprecated {

	/**
	 * Returns a filterable sequential array of default scripts.
	 *
	 * @since 3.2.2
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return array
	 */
	public function get_default_scripts() {

		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->get_default_scripts()', '4.0.0' );

		return array_merge(
			\The_SEO_Framework\Bridges\Scripts::get_tsf_scripts(),
			\The_SEO_Framework\Bridges\Scripts::get_tt_scripts()
		);
	}

	/**
	 * Enqueues Gutenberg-related scripts.
	 *
	 * @since 3.2.0
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return void Early if already enqueued.
	 */
	public function enqueue_gutenberg_compat_scripts() {

		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->enqueue_gutenberg_compat_scripts()', '4.0.0' );

		if ( \The_SEO_Framework\_has_run( __METHOD__ ) ) return;

		\The_SEO_Framework\Builders\Scripts::register(
			\The_SEO_Framework\Bridges\Scripts::get_gutenberg_compat_scripts()
		);
	}

	/**
	 * Enqueues Media Upload and Cropping scripts.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return void Early if already enqueued.
	 */
	public function enqueue_media_scripts() {

		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->enqueue_media_scripts()', '4.0.0' );

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
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return void Early if already enqueued.
	 */
	public function enqueue_primaryterm_scripts() {

		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->enqueue_primaryterm_scripts()', '4.0.0' );

		if ( \The_SEO_Framework\_has_run( __METHOD__ ) ) return;

		\The_SEO_Framework\Builders\Scripts::register(
			\The_SEO_Framework\Bridges\Scripts::get_primaryterm_scripts()
		);
	}

	/**
	 * Includes the necessary sortable metabox scripts.
	 *
	 * @since 2.2.2
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 */
	public function metabox_scripts() {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->metabox_scripts()', '4.0.0', '\The_SEO_Framework\Bridges\Scripts::prepare_metabox_scripts()' );
		\The_SEO_Framework\Bridges\Scripts::prepare_metabox_scripts();
	}

	/**
	 * Returns the SEO Bar.
	 *
	 * @since 3.0.4
	 * @since 4.0.0 Deprecated
	 * @staticvar string $type
	 * @deprecated
	 *
	 * @param string $column the current column : If it's a taxonomy, this is empty
	 * @param int    $post_id the post id       : If it's a taxonomy, this is the column name
	 * @param string $tax_id this is empty      : If it's a taxonomy, this is the taxonomy id
	 */
	public function get_seo_bar( $column, $post_id, $tax_id ) {

		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->post_status()', '4.0.0', 'the_seo_framework()->get_generated_seo_bar()' );

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
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int    $post_id The Post ID or taxonomy ID.
	 * @param string $type The content type.
	 * @param bool   $echo Whether to echo the value. Does not eliminate return.
	 * @return string|void $content The post SEO status. Void if $echo is true.
	 */
	public function post_status( $post_id, $type = '', $echo = false ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->post_status()', '4.0.0', 'the_seo_framework()->get_generated_seo_bar()' );

		if ( ! $post_id )
			$post_id = $tsf->get_the_real_ID();

		if ( 'inpost' === $type || ! $type ) {
			$type = \get_post_type( $post_id );
		}

		if ( $tsf->is_post_type_page( $type ) ) {
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
			// phpcs:ignore, WordPress.Security.EscapeOutput -- the SEO Bar is escaped.
			echo $bar;
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
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The scripts class name.
	 */
	public function Scripts() { // phpcs:ignore, WordPress.NamingConventions.ValidFunctionName
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->Scripts()', '4.0.0', '\The_SEO_Framework\Builders\Scripts::class' );
		return \The_SEO_Framework\Builders\Scripts::class;
	}

	/**
	 * Determines if we're doing ajax.
	 *
	 * @since 2.9.0
	 * @since 4.0.0 1. Now uses wp_doing_ajax()
	 *              2. Deprecated.
	 * @deprecated
	 *
	 * @return bool True if AJAX
	 */
	public function doing_ajax() {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->doing_ajax()', '4.0.0', 'wp_doing_ajax' );
		return \wp_doing_ajax();
	}

	/**
	 * Whether to lowercase the noun or keep it UCfirst.
	 * Depending if language is German.
	 *
	 * @since 2.6.0
	 * @since 4.0.0 Deprecated
	 * @deprecated
	 * @staticvar array $lowercase Contains nouns.
	 *
	 * @param string $noun The noun to lowercase.
	 * @return string The maybe lowercase noun.
	 */
	public function maybe_lowercase_noun( $noun ) {

		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->maybe_lowercase_noun()', '4.0.0' );

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
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $locale Required, the locale.
	 * @return bool Whether the input $locale is in the current WordPress locale.
	 */
	public function check_wp_locale( $locale = '' ) {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->check_wp_locale()', '4.0.0' );
		return false !== strpos( \get_locale(), $locale );
	}

	/**
	 * Initializes term meta data filters and functions.
	 *
	 * @since 2.7.0
	 * @since 3.0.0 No longer checks for admin query.
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 */
	public function initialize_term_meta() {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->initialize_term_meta()', '4.0.0', '\the_seo_framework()->init_term_meta()' );
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
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return void Early if blog is not public.
	 */
	public static function ping_searchengines() {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->ping_searchengines()', '4.0.0', '\The_SEO_Framework\Bridges\Ping::ping_search_engines()' );
		\The_SEO_Framework\Bridges\Ping::ping_search_engines();
	}

	/**
	 * Pings the sitemap location to Google.
	 *
	 * @since 2.2.9
	 * @since 3.1.0 Updated ping URL. Old one still worked, too.
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 * @link https://support.google.com/webmasters/answer/6065812?hl=en
	 */
	public static function ping_google() {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->ping_google()', '4.0.0', '\The_SEO_Framework\Bridges\Ping::ping_google()' );
		\The_SEO_Framework\Bridges\Ping::ping_google();
	}

	/**
	 * Pings the sitemap location to Bing.
	 *
	 * @since 2.2.9
	 * @since 3.2.3 Updated ping URL. Old one still worked, too.
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 * @link https://www.bing.com/webmaster/help/how-to-submit-sitemaps-82a15bd4
	 */
	public static function ping_bing() {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->ping_bing()', '4.0.0', '\The_SEO_Framework\Bridges\Ping::ping_bing()' );
		\The_SEO_Framework\Bridges\Ping::ping_bing();
	}

	/**
	 * Returns the stylesheet XSL location URL.
	 *
	 * @since 2.8.0
	 * @since 3.0.0 1: No longer uses home URL from cache. But now uses `get_home_url()`.
	 *              2: Now takes query parameters (if any) and restores them correctly.
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 * @global \WP_Rewrite $wp_rewrite
	 *
	 * @return string URL location of the XSL stylesheet. Unescaped.
	 */
	public function get_sitemap_xsl_url() {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->get_sitemap_xsl_url()', '4.0.0', '\The_SEO_Framework\Bridges\Sitemap::get_instance()->get_expected_sitemap_endpoint_url(\'xsl-stylesheet\')' );
		return \The_SEO_Framework\Bridges\Sitemap::get_instance()->get_expected_sitemap_endpoint_url( 'xsl-stylesheet' );
	}

	/**
	 * Returns the sitemap XML location URL.
	 *
	 * @since 2.9.2
	 * @since 3.0.0 1: No longer uses home URL from cache. But now uses `get_home_url()`.
	 *              2: Now takes query parameters (if any) and restores them correctly.
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 * @global \WP_Rewrite $wp_rewrite
	 *
	 * @return string URL location of the XML sitemap. Unescaped.
	 */
	public function get_sitemap_xml_url() {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->get_sitemap_xml_url()', '4.0.0', '\The_SEO_Framework\Bridges\Sitemap::get_instance()->get_expected_sitemap_endpoint_url()' );
		return \The_SEO_Framework\Bridges\Sitemap::get_instance()->get_expected_sitemap_endpoint_url();
	}

	/**
	 * Sitemap XSL stylesheet output.
	 *
	 * @since 2.8.0
	 * @since 3.1.0 1. Now outputs 200-response code.
	 *              2. Now outputs robots tag, preventing indexing.
	 *              3. Now overrides other header tags.
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 */
	public function output_sitemap_xsl_stylesheet() {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->output_sitemap_xsl_stylesheet()', '4.0.0' );
		return \The_SEO_Framework\Bridges\Sitemap::get_instance()->output_stylesheet();
	}

	/**
	 * Determines if post type supports The SEO Framework.
	 *
	 * @since 2.3.9
	 * @since 3.1.0 1. Removed caching.
	 *              2. Now works in admin.
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $post_type Optional. The post type to check.
	 * @return bool true of post type is supported.
	 */
	public function post_type_supports_custom_seo( $post_type = '' ) {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->post_type_supports_custom_seo()', '4.0.0', 'the_seo_framework()->is_post_type_supported()' );
		return \the_seo_framework()->is_post_type_supported( $post_type );
	}

	/**
	 * Determines if the taxonomy supports The SEO Framework.
	 *
	 * Checks if at least one taxonomy objects post type supports The SEO Framework,
	 * and wether the taxonomy is public and rewritable.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 1. Now goes over all post types for the taxonomy.
	 *              2. Can now return true if at least one post type for the taxonomy is supported.
	 *              3. Deprecated.
	 * @deprecated
	 *
	 * @param string $taxonomy Optional. The taxonomy name.
	 * @return bool True if at least one post type in taxonomy isn't disabled.
	 */
	public function taxonomy_supports_custom_seo( $taxonomy = '' ) {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->taxonomy_supports_custom_seo()', '4.0.0', 'the_seo_framework()->is_taxonomy_supported()' );
		return \the_seo_framework()->is_taxonomy_supported( $taxonomy );
	}

	/**
	 * Returns taxonomical canonical URL.
	 * Automatically adds pagination if the ID matches the query.
	 *
	 * @since 3.0.0
	 * @since 4.0.0 Deprecated
	 * @deprecated
	 *
	 * @param int    $term_id The term ID.
	 * @param string $taxonomy The taxonomy.
	 * @return string The taxonomical canonical URL, if any.
	 */
	public function get_taxonomial_canonical_url( $term_id, $taxonomy ) {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->get_taxonomial_canonical_url()', '4.0.0', 'the_seo_framework()->get_taxonomical_canonical_url()' );
		return \the_seo_framework()->get_taxonomical_canonical_url( $term_id, $taxonomy );
	}

	/**
	 * Tries to fetch a term by $id from query.
	 *
	 * @since 2.6.0
	 * @since 3.0.0 Can now get custom post type objects.
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $id The possible taxonomy Term ID.
	 * @return false|object The Term object.
	 */
	public function fetch_the_term( $id = '' ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->fetch_the_term()', '4.0.0' );

		static $term = [];

		if ( isset( $term[ $id ] ) )
			return $term[ $id ];

		//* Return null if no term can be detected.
		if ( false === $tsf->is_archive() )
			return false;

		if ( \is_admin() ) {
			$taxonomy = $tsf->get_current_taxonomy();
			if ( $taxonomy ) {
				$term_id     = $id ?: $tsf->get_the_real_admin_ID();
				$term[ $id ] = \get_term( $term_id, $taxonomy );
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
	 * @since 4.0.0 Deprecated
	 * @deprecated
	 * @staticvar array $field_cache
	 *
	 * @param string $field     Custom field key.
	 * @param int    $post_id   The post ID.
	 * @return mixed|boolean Return value or false on failure.
	 */
	public function get_custom_field( $field, $post_id = null ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->get_custom_field()', '4.0.0', 'the_seo_framework()->get_post_meta_item()' );

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

	/**
	 * Returns image URL suitable for Schema items.
	 *
	 * These are images that are strictly assigned to the Post or Page, fallbacks are omitted.
	 * Themes should compliment these. If not, then Open Graph should at least
	 * compliment these.
	 * If that's not even true, then I don't know what happens. But then you're
	 * in a grey area... @TODO make images optional for Schema?
	 *
	 * @since 2.9.3
	 * @since 3.2.2 No longer relies on the query.
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int|string $id       The page, post, product or term ID.
	 * @param bool       $singular Whether the ID is singular or archival.
	 * @return string|array $url The Schema.org safe image.
	 */
	public function get_schema_image( $id = 0, $singular = false ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->get_schema_image()', '4.0.0', 'the_seo_framework()->get_safe_schema_image()' );

		if ( ! $singular ) return '';

		return $tsf->get_safe_schema_image( $id ?: null, false );
	}

	/**
	 * Returns social image URL.
	 *
	 * @since 2.9.0
	 * @since 3.0.6 Added attachment page compatibility.
	 * @since 3.2.2 Now skips the singular meta images on archives.
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array $args The image arguments.
	 * @return string The social image.
	 */
	public function get_social_image( $args = [] ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->get_social_image()', '4.0.0', 'the_seo_framework()->get_image_from_cache()' );

		if ( isset( $args['post_id'] ) && $args['post_id'] ) {
			$image = current( $tsf->get_image_details( [ 'id' => $args['post_id'] ], true ) );
		} else {
			$image = current( $tsf->get_image_details( null, true ) );
		}

		return isset( $image['url'] ) ? $image['url'] : '';
	}

	/**
	 * Returns unescaped HomePage settings image URL from post ID input.
	 *
	 * @since 2.9.0
	 * @since 2.9.4 Now converts URL scheme.
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $id The post ID.
	 * @return string The unescaped HomePage social image URL.
	 */
	public function get_social_image_url_from_home_meta( $id = 0 ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->get_social_image_url_from_home_meta()', '4.0.0', "the_seo_framework()->get_option( 'homepage_social_image_url' )" );

		if ( false === $tsf->is_front_page_by_id( $id ) )
			return '';

		$src = $tsf->get_option( 'homepage_social_image_url' );

		if ( ! $src )
			return '';

		if ( $src && $tsf->matches_this_domain( $src ) )
			$src = $tsf->set_preferred_url_scheme( $src );

		return $src;
	}

	/**
	 * Returns unescaped Post settings image URL from post ID input.
	 *
	 * @since 2.8.0
	 * @since 2.9.0 1. The second parameter now works.
	 *              2. Fallback image ID has been removed.
	 * @since 2.9.4 Now converts URL scheme.
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $id The post ID. Required.
	 * @return string The unescaped social image URL.
	 */
	public function get_social_image_url_from_post_meta( $id ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->get_social_image_url_from_post_meta()', '4.0.0', "the_seo_framework()->get_post_meta_item( '_social_image_url' )" );

		$src = $id ? $tsf->get_post_meta_item( '_social_image_url', $id ) : '';

		if ( ! $src )
			return '';

		if ( $src && $tsf->matches_this_domain( $src ) )
			$src = $tsf->set_preferred_url_scheme( $src );

		return $src;
	}

	/**
	 * Returns unescaped URL from options input.
	 *
	 * @since 2.8.2
	 * @since 2.9.4 1: Now converts URL scheme.
	 *              2: $set_og_dimensions now works.
	 * @since 4.0.0 Deprecated
	 * @deprecated
	 *
	 * @return string The unescaped social image fallback URL.
	 */
	public function get_social_image_url_from_seo_settings() {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->get_social_image_url_from_seo_settings()', '4.0.0', "the_seo_framework()->get_option( 'social_image_fb_url' )" );

		$src = $tsf->get_option( 'social_image_fb_url' );

		if ( $src && $tsf->matches_this_domain( $src ) )
			$src = $tsf->set_preferred_url_scheme( $src );

		return $src;
	}

	/**
	 * Fetches image from post thumbnail.
	 *
	 * @since 2.9.0
	 * @since 2.9.3 Now supports 4K.
	 * @since 2.9.4 Now converts URL scheme.
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $id The post ID. Required.
	 * @return string The social image URL.
	 */
	public function get_social_image_url_from_post_thumbnail( $id ) {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->get_social_image_url_from_post_thumbnail()', '4.0.0' );
		return \The_SEO_Framework\Builders\Images::get_featured_image_details(
			[
				'id'       => $id,
				'taxonomy' => '',
			]
		)->current()['url'];
	}

	/**
	 * Returns the social image URL from an attachment page.
	 *
	 * @since 3.0.6
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $id The post ID. Required.
	 * @return string The attachment URL.
	 */
	public function get_social_image_url_from_attachment( $id ) {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->get_social_image_url_from_attachment()', '4.0.0' );
		return \The_SEO_Framework\Builders\Images::get_attachment_image_details(
			[
				'id'       => $id,
				'taxonomy' => '',
			]
		)->current()['url'];
	}

	/**
	 * Fetches images id's from WooCommerce gallery
	 *
	 * @since 2.5.0
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return array The image URL's.
	 */
	public function get_image_from_woocommerce_gallery() {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->get_image_from_woocommerce_gallery()', '4.0.0' );

		$ids = [];

		if ( function_exists( '\\The_SEO_Framework\\_get_product_gallery_image_details' ) ) {
			foreach ( \The_SEO_Framework\_get_product_gallery_image_details() as $details ) {
				$ids[] = $details['id'];
			}
		}

		return $ids;
	}

	/**
	 * Returns header image URL.
	 * Also sets image dimensions. Falls back to current post ID for index.
	 *
	 * @since 2.7.0
	 * @since 3.0.0 Now sets preferred canonical URL scheme.
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The header image URL, not escaped.
	 */
	public function get_header_image() {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->get_header_image()', '4.0.0' );
		return \The_SEO_Framework\Builders\Images::get_theme_header_image_details()->current()['url'];
	}

	/**
	 * Fetches site icon brought in WordPress 4.3
	 *
	 * @since 2.8.0
	 * @since 3.0.0 : Now sets preferred canonical URL scheme.
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string|int $size The icon size, accepts 'full' and pixel values.
	 * @return string URL site icon, not escaped.
	 */
	public function get_site_icon( $size = 'full' ) {

		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->get_site_icon()', '4.0.0' );

		$size = is_string( $size ) ? $size : 'full';

		return \The_SEO_Framework\Builders\Images::get_site_icon_image_details( null, $size )->current()['url'];
	}

	/**
	 * Fetches site logo brought in WordPress 4.5
	 *
	 * @since 2.8.0
	 * @since 3.0.0 Now sets preferred canonical URL scheme.
	 * @since 3.1.2 Now returns empty when it's deemed too small, and OG images are set.
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return string URL site logo, not escaped.
	 */
	public function get_site_logo() {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->get_site_logo()', '4.0.0' );
		return \The_SEO_Framework\Builders\Images::get_site_logo_image_details()->current()['url'];
	}

	/**
	 * Sanitizeses ID. Mainly removing spaces and coding characters.
	 *
	 * Unlike sanitize_key(), it doesn't alter the case nor applies filters.
	 * It also maintains the '@' character.
	 *
	 * @see WordPress Core sanitize_key()
	 * @since 3.1.0
	 * @since 4.0.0 1. Now allows square brackets.
	 *              2. Deprecated.
	 * @deprecated
	 *
	 * @param string $id The unsanitized ID.
	 * @return string The sanitized ID.
	 */
	public function sanitize_field_id( $id ) {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->sanitize_field_id()', '4.0.0', 'the_seo_framework()->s_field_id()' );
		return preg_replace( '/[^a-zA-Z0-9\[\]_\-@]/', '', $id );
	}

	/**
	 * Checks a theme's support for title-tag.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Removed caching
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function current_theme_supports_title_tag() {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->sanitize_field_id()', '4.0.0' );
		return \the_seo_framework()->detect_theme_support( 'title-tag' );
	}

	/**
	 * Determines if the current theme supports the custom logo addition.
	 *
	 * @since 2.8.0
	 * @since 3.1.0: 1. No longer checks for WP version 4.5+.
	 *               2. No longer uses caching.
	 * @since 4.0.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function can_use_logo() {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->can_use_logo()', '4.0.0' );
		return \the_seo_framework()->detect_theme_support( 'custom-logo' );
	}
}
