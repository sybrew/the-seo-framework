<?php
/**
 * @package The_SEO_Framework\Classes\Deprecated
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

defined( 'ABSPATH' ) or die;

/**
 * Class The_SEO_Framework\Deprecated
 *
 * Contains all deprecated functions.
 *
 * @since 2.8.0
 */
final class Deprecated {

	/**
	 * Constructor. Does nothing.
	 */
	public function __construct() { }

	/**
	 * HomePage Metabox General Tab Output.
	 *
	 * @since 2.6.0
	 * @see $this->homepage_metabox() Callback for HomePage Settings box.
	 *
	 * @deprecated
	 * @since 2.7.0
	 */
	public function homepage_metabox_general() {
		\the_seo_framework()->_deprecated_function( 'The_SEO_Framework_Metaboxes::' . __FUNCTION__, '2.7.0', 'The_SEO_Framework_Metaboxes::homepage_metabox_general_tab()' );
		\the_seo_framework()->get_view( 'metaboxes/homepage-metabox', array(), 'general' );
	}

	/**
	 * HomePage Metabox Additions Tab Output.
	 *
	 * @since 2.6.0
	 * @see $this->homepage_metabox() Callback for HomePage Settings box.
	 *
	 * @deprecated
	 * @since 2.7.0
	 */
	public function homepage_metabox_additions() {
		\the_seo_framework()->_deprecated_function( 'The_SEO_Framework_Metaboxes::' . __FUNCTION__, '2.7.0', 'The_SEO_Framework_Metaboxes::homepage_metabox_additions_tab()' );
		\the_seo_framework()->get_view( 'metaboxes/homepage-metabox', array(), 'additions' );
	}

	/**
	 * HomePage Metabox Robots Tab Output
	 *
	 * @since 2.6.0
	 * @see $this->homepage_metabox() Callback for HomePage Settings box.
	 *
	 * @deprecated
	 * @since 2.7.0
	 */
	public function homepage_metabox_robots() {
		\the_seo_framework()->_deprecated_function( 'The_SEO_Framework_Metaboxes::' . __FUNCTION__, '2.7.0', 'The_SEO_Framework_Metaboxes::homepage_metabox_robots_tab()' );
		\the_seo_framework()->get_view( 'metaboxes/homepage-metabox', array(), 'robots' );
	}

	/**
	 * Delete transient for the automatic description for blog on save request.
	 * Returns old option, since that's passed for sanitation within WP Core.
	 *
	 * @since 2.3.3
	 *
	 * @deprecated
	 * @since 2.7.0
	 *
	 * @param string $old_option The previous blog description option.
	 * @return string Previous option.
	 */
	public function delete_auto_description_blog_transient( $old_option ) {

		\the_seo_framework()->_deprecated_function( 'The_SEO_Framework_Transients::' . __FUNCTION__, '2.7.0', 'The_SEO_Framework_Transients::delete_auto_description_frontpage_transient()' );

		\the_seo_framework()->delete_auto_description_transient( \the_seo_framework()->get_the_front_page_ID(), '', 'frontpage' );

		return $old_option;
	}

	/**
	 * Add term meta data into options table of the term.
	 * Adds separated database options for terms, as the terms table doesn't allow for addition.
	 *
	 * Applies filters array the_seo_framework_term_meta_defaults : Array of default term SEO options
	 * Applies filters mixed the_seo_framework_term_meta_{field} : Override filter for specifics.
	 * Applies filters array the_seo_framework_term_meta : Override output for term or taxonomy.
	 *
	 * @since 2.1.8
	 *
	 * @deprecated silently.
	 * @since WordPress 4.4.0
	 * @since The SEO Framework 2.7.0
	 * @since 2.8.0: Deprecated visually.
	 *
	 * @param object $term     Database row object.
	 * @param string $taxonomy Taxonomy name that $term is part of.
	 * @return object $term Database row object.
	 */
	public function get_term_filter( $term, $taxonomy ) {

		\the_seo_framework()->_deprecated_function( 'The_SEO_Framework_Transients::' . __FUNCTION__, '2.7.0', 'WordPress Core "get_term_meta()"' );

		return false;
	}

	/**
	 * Adds The SEO Framework term meta data to functions that return multiple terms.
	 *
	 * @since 2.0.0
	 *
	 * @deprecated silently.
	 * @since WordPress 4.4.0
	 * @since The SEO Framework 2.7.0
	 * @since 2.8.0: Deprecated visually.
	 *
	 * @param array  $terms    Database row objects.
	 * @param string $taxonomy Taxonomy name that $terms are part of.
	 * @return array $terms Database row objects.
	 */
	public function get_terms_filter( array $terms, $taxonomy ) {

		\the_seo_framework()->_deprecated_function( 'The_SEO_Framework_Transients::' . __FUNCTION__, '2.7.0', 'WordPress Core "get_term_meta()"' );

		return false;
	}

	/**
	 * Save taxonomy meta data.
	 * Fires when a user edits and saves a taxonomy.
	 *
	 * @since 2.1.8
	 *
	 * @deprecated silently.
	 * @since WordPress 4.4.0
	 * @since The SEO Framework 2.7.0
	 * @since 2.8.0: Deprecated visually.
	 *
	 * @param integer $term_id Term ID.
	 * @param integer $tt_id   Term Taxonomy ID.
	 * @return void Early on AJAX call.
	 */
	public function taxonomy_seo_save( $term_id, $tt_id ) {

		\the_seo_framework()->_deprecated_function( 'The_SEO_Framework_Transients::' . __FUNCTION__, '2.7.0', 'WordPress Core "update_term_meta()"' );

		return false;
	}

	/**
	 * Delete term meta data.
	 * Fires when a user deletes a term.
	 *
	 * @since 2.1.8
	 *
	 * @deprecated silently.
	 * @since WordPress 4.4.0
	 * @since The SEO Framework 2.7.0
	 * @since 2.8.0: Deprecated visually.
	 *
	 * @param integer $term_id Term ID.
	 * @param integer $tt_id   Taxonomy Term ID.
	 */
	public function term_meta_delete( $term_id, $tt_id ) {

		\the_seo_framework()->_deprecated_function( 'The_SEO_Framework_Transients::' . __FUNCTION__, '2.7.0', 'WordPress Core "delete_term_meta()"' );

		return false;
	}

	/**
	 * Faster way of doing an in_array search compared to default PHP behavior.
	 * @NOTE only to show improvement with large arrays. Might slow down with small arrays.
	 * @NOTE can't do type checks. Always assume the comparing value is a string.
	 *
	 * @since 2.5.2
	 * @since 2.7.0 Deprecated.
	 * @deprecated
	 *
	 * @param string|array $needle The needle(s) to search for
	 * @param array $array The single dimensional array to search in.
	 * @return bool true if value is in array.
	 */
	public function in_array( $needle, $array, $strict = true ) {

		\the_seo_framework()->_deprecated_function( 'The_SEO_Framework_Core::' . __FUNCTION__, '2.7.0', 'in_array()' );

		$array = array_flip( $array );

		if ( is_string( $needle ) ) {
			if ( isset( $array[ $needle ] ) )
				return true;
		} elseif ( is_array( $needle ) ) {
			foreach ( $needle as $str ) {
				if ( isset( $array[ $str ] ) )
					return true;
			}
		}

		return false;
	}

	/**
	 * Fetches posts with exclude_local_search option on
	 *
	 * @since 2.1.7
	 * @since 2.7.0 Deprecated.
	 * @deprecated
	 *
	 * @return array Excluded Post IDs
	 */
	public function exclude_search_ids() {

		\the_seo_framework()->_deprecated_function( 'The_SEO_Framework_Search::' . __FUNCTION__, '2.7.0', 'the_seo_framework()->get_excluded_search_ids()' );

		return $this->get_excluded_search_ids();
	}

	/**
	 * Fetches posts with exclude_local_search option on.
	 *
	 * @since 2.1.7
	 * @since 2.7.0 No longer used for performance reasons.
	 * @uses $this->exclude_search_ids()
	 * @deprecated
	 * @since 2.8.0 deprecated.
	 *
	 * @param array $query The possible search query.
	 * @return void Early if no search query is found.
	 */
	public function search_filter( $query ) {

		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->search_filter()', '2.8.0' );

		// Don't exclude pages in wp-admin.
		if ( $query->is_search && false === \the_seo_framework()->is_admin() ) {

			$q = $query->query;
			//* Only interact with an actual Search Query.
			if ( false === isset( $q['s'] ) )
				return;

			//* Get excluded IDs.
			$protected_posts = $this->get_excluded_search_ids();
			if ( $protected_posts ) {
				$get = $query->get( 'post__not_in' );

				//* Merge user defined query.
				if ( is_array( $get ) && ! empty( $get ) )
					$protected_posts = array_merge( $protected_posts, $get );

				$query->set( 'post__not_in', $protected_posts );
			}

			// Parse all ID's, even beyond the first page.
			$query->set( 'no_found_rows', false );
		}
	}

	/**
	 * Fetches posts with exclude_local_search option on.
	 *
	 * @since 2.7.0
	 * @since 2.7.0 No longer used.
	 * @global int $blog_id
	 * @deprecated
	 *
	 * @return array Excluded Post IDs
	 */
	public function get_excluded_search_ids() {

		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->get_excluded_search_ids()', '2.7.0' );

		global $blog_id;

		$cache_key = 'exclude_search_ids_' . $blog_id . '_' . \get_locale();

		$post_ids = \the_seo_framework()->object_cache_get( $cache_key );
		if ( false === $post_ids ) {
			$post_ids = array();

			$args = array(
				'post_type'        => 'any',
				'numberposts'      => -1,
				'posts_per_page'   => -1,
				'order'            => 'DESC',
				'post_status'      => 'publish',
				'meta_key'         => 'exclude_local_search',
				'meta_value'       => 1,
				'meta_compare'     => '=',
				'cache_results'    => true,
				'suppress_filters' => false,
			);
			$get_posts = new \WP_Query;
			$excluded_posts = $get_posts->query( $args );
			unset( $get_posts );

			if ( $excluded_posts )
				$post_ids = \wp_list_pluck( $excluded_posts, 'ID' );

			\the_seo_framework()->object_cache_set( $cache_key, $post_ids, 86400 );
		}

		// return an array of exclude post IDs
		return $post_ids;
	}

	/**
	 * Registers option sanitation filter
	 *
	 * @since 2.2.2
	 * @since 2.7.0 : No longer used internally.
	 * @since 2.8.0 : Deprecated
	 * @deprecated
	 *
	 * @param string $filter The filter to call (see The_SEO_Framework_Site_Options::$available_filters for options)
	 * @param string $option The WordPress option name
	 * @param string|array $suboption Optional. The suboption or suboptions you want to filter
	 * @return true on completion.
	 */
	public function autodescription_add_option_filter( $filter, $option, $suboption = null ) {

		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->add_option_filter()', '2.8.0' );

		return \the_seo_framework()->add_option_filter( $filter, $option, $suboption );
	}

	/**
	 * Register each of the settings with a sanitization filter type.
	 *
	 * @since 2.2.2
	 * @since 2.8.0 Deprecated.
	 * @uses method add_filter() Assign filter to array of settings.
	 * @see The_SEO_Framework_Sanitize::add_filter() Add sanitization filters to options.
	 */
	public function sanitizer_filters() {

		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->sanitizer_filters()', '2.8.0', 'the_seo_framework()->init_sanitizer_filters()' );

		\the_seo_framework()->init_sanitizer_filters();
	}

	/**
	 * Fetches site icon brought in WordPress 4.3.0
	 *
	 * @since 2.2.1
	 * @since 2.8.0: Deprecated.
	 * @deprecated
	 *
	 * @param string $size The icon size, accepts 'full' and pixel values.
	 * @param bool $set_og_dimensions Whether to set size for OG image. Always falls back to the current post ID.
	 * @return string URL site icon, not escaped.
	 */
	public function site_icon( $size = 'full', $set_og_dimensions = false ) {

		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->site_icon()', '2.8.0', 'the_seo_framework()->get_site_icon()' );

		return the_seo_framework()->get_site_icon( $size, $set_og_dimensions );
	}

	/**
	 * Delete transient on post save.
	 *
	 * @since 2.2.9
	 * @since 2.8.0 : Deprecated
	 * @deprecated
	 *
	 * @param int $post_id The Post ID that has been updated.
	 * @return bool|null True when sitemap is flushed. False on revision. Null
	 * when sitemaps are deactivated.
	 */
	public function delete_transients_post( $post_id ) {

		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->delete_transients_post()', '2.8.0', 'the_seo_framework()->delete_post_cache()' );

		return \the_seo_framework()->delete_post_cache( $post_id );
	}

	/**
	 * Delete transient on profile save.
	 *
	 * @since 2.6.4
	 * @since 2.8.0 : Deprecated
	 * @deprecated
	 *
	 * @param int $user_id The User ID that has been updated.
	 */
	public function delete_transients_author( $user_id ) {

		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->delete_transients_author()', '2.8.0', 'the_seo_framework()->delete_author_cache()' );

		return \the_seo_framework()->delete_author_cache( $user_id );
	}

	/**
	 * Flushes the home page LD+Json transient.
	 *
	 * @since 2.6.0
	 * @since 2.8.0 deprecated.
	 * @staticvar bool $flushed Prevents second flush.
	 * @deprecated
	 *
	 * @return bool Whether it's flushed on current call.
	 */
	public function delete_front_ld_json_transient() {

		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->delete_front_ld_json_transient()', '2.8.0', 'the_seo_framework()->delete_cache( \'front\' )' );

		static $flushed = null;

		if ( isset( $flushed ) )
			return false;

		if ( ! \the_seo_framework()->is_option_checked( 'cache_meta_schema' ) )
			return $flushed = false;

		$front_id = \the_seo_framework()->get_the_front_page_ID();

		\the_seo_framework()->delete_ld_json_transient( $front_id, '', 'frontpage' );

		return $flushed = true;
	}

	/**
	 * Determines whether we can use the new WordPress core term meta functionality.
	 *
	 * @since 2.7.0
	 * @since 2.8.0: Deprecated. WordPress 4.4+ is now required.
	 * @staticvar bool $cache
	 * @deprecated
	 *
	 * @return bool True when WordPress is at version 4.4 or higher and has an
	 *              accordingly upgraded database.
	 */
	public function can_get_term_meta() {

		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->can_get_term_meta()', '2.8.0' );

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		return $cache = \get_option( 'db_version' ) >= 34370 && \get_option( 'the_seo_framework_upgraded_db_version' ) >= '2700' && \the_seo_framework()->wp_version( '4.3.999', '>' );
	}

	/**
	 * Fetches term metadata array for the inpost term metabox.
	 *
	 * @since 2.7.0
	 * @since 2.8.0: Deprecated. WordPress 4.4+ is now required.
	 * @deprecated
	 *
	 * @param object $term The TT object. Must be assigned.
	 * @return array The SEO Framework TT data.
	 */
	protected function get_old_term_data( $term ) {

		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->get_old_term_data()', '2.8.0' );

		$data = array();

		$data['title'] = isset( $term->admeta['doctitle'] ) ? $term->admeta['doctitle'] : '';
		$data['description'] = isset( $term->admeta['description'] ) ? $term->admeta['description'] : '';
		$data['noindex'] = isset( $term->admeta['noindex'] ) ? $term->admeta['noindex'] : '';
		$data['nofollow'] = isset( $term->admeta['nofollow'] ) ? $term->admeta['nofollow'] : '';
		$data['noarchive'] = isset( $term->admeta['noarchive'] ) ? $term->admeta['noarchive'] : '';
		$flag = isset( $term->admeta['saved_flag'] ) ? (bool) $term->admeta['saved_flag'] : false;

		//* Genesis data fetch. This will override our options with Genesis options on save.
		if ( false === $flag && isset( $term->meta ) ) {
			$data['title'] = empty( $data['title'] ) && isset( $term->meta['doctitle'] ) 				? $term->meta['doctitle'] : $data['noindex'];
			$data['description'] = empty( $data['description'] ) && isset( $term->meta['description'] )	? $term->meta['description'] : $data['description'];
			$data['noindex'] = empty( $data['noindex'] ) && isset( $term->meta['noindex'] ) 			? $term->meta['noindex'] : $data['noindex'];
			$data['nofollow'] = empty( $data['nofollow'] ) && isset( $term->meta['nofollow'] )			? $term->meta['nofollow'] : $data['nofollow'];
			$data['noarchive'] = empty( $data['noarchive'] ) && isset( $term->meta['noarchive'] )		? $term->meta['noarchive'] : $data['noarchive'];
		}

		return $data;
	}

	/**
	 * Fetches og:image URL.
	 *
	 * @since 2.2.2
	 * @since 2.2.8 : Added theme icon detection.
	 * @since 2.5.2 : Added args filters.
	 * @since 2.8.0 : 1. Added theme logo detection.
	 *                2. Added inpost image selection detection.
	 * @since 2.8.2 : 1. Now returns something on post ID 0.
	 *                2. Added SEO settings fallback image selection detection.
	 * @since 2.9.0 : 1. Added 'skip_fallback' option to arguments.
	 *                2. Added 'escape' option to arguments.
	 *                3. First parameter is now arguments. Fallback for integer is added.
	 *                4. Second parameter is now deprecated.
	 *                5. Deprecated.
	 * @deprecated Use get_social_image instead.
	 *
	 * @param int|array $args The image arguments.
	 *                  Was: $post_id.
	 *                  Warning: Integer usage is only used for backwards compat.
	 * @param array $depr_args, Deprecated;
	 *              Was $args The image arguments.
	 * @param bool $escape Whether to escape the image URL.
	 *             Deprecated: You should use $args['escape'].
	 * @return string the image URL.
	 */
	public function get_image( $args = array(), $depr_args = '', $depr_escape = true ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->get_image()', '2.9.0', 'the_seo_framework()->get_social_image()' );

		if ( is_int( $args ) || is_array( $depr_args ) ) {
			$tsf->_doing_it_wrong( __METHOD__, 'First parameter is now used for arguments. Second parameter is deprecated.', '2.9.0' );

			$post_id = $args;
			$args = array();

			/**
			 * Backwards compat with parse args.
			 * @since 2.5.0
			 */
			if ( ! isset( $depr_args['post_id'] ) ) {
				$args['post_id'] = $post_id ?: ( $tsf->is_singular( $post_id ) ? $tsf->get_the_real_ID() : 0 );
			}

			if ( is_array( $depr_args ) ) {
				$args = \wp_parse_args( $depr_args, $args );
			}
		}

		if ( false === $depr_escape ) {
			$tsf->_doing_it_wrong( __METHOD__, 'Third parameter has been deprecated. Use `$args["escape"] => false` instead.', '2.9.0' );
			$args['escape'] = false;
		}

		$args = $tsf->reparse_image_args( $args );

		//* 0. Image from argument.
		pre_0 : {
			if ( $image = $args['image'] )
				goto end;
		}

		//* Check if there are no disallowed arguments.
		$all_allowed = empty( $args['disallowed'] );

		//* 1. Fetch image from homepage SEO meta upload.
		if ( $all_allowed || false === in_array( 'homemeta', $args['disallowed'], true ) ) {
			if ( $image = $tsf->get_social_image_url_from_home_meta( $args['post_id'], true ) )
				goto end;
		}

		if ( $args['post_id'] ) {
			//* 2. Fetch image from SEO meta upload.
			if ( $all_allowed || false === in_array( 'postmeta', $args['disallowed'], true ) ) {
				if ( $image = $tsf->get_social_image_url_from_post_meta( $args['post_id'], true ) )
					goto end;
			}

			//* 3. Fetch image from featured.
			if ( $all_allowed || false === in_array( 'featured', $args['disallowed'], true ) ) {
				if ( $image = $tsf->get_image_from_post_thumbnail( $args, true ) )
					goto end;
			}
		}

		if ( $args['skip_fallback'] )
			goto end;

		//* 4. Fetch image from SEO settings
		if ( $all_allowed || false === in_array( 'option', $args['disallowed'], true ) ) {
			if ( $image = $tsf->get_social_image_url_from_seo_settings( true ) )
				goto end;
		}

		//* 5. Fetch image from fallback filter 1
		/**
		 * Applies filters 'the_seo_framework_og_image_after_featured' : string
		 * @since 2.5.2
		 */
		fallback_1 : {
			if ( $image = (string) \apply_filters( 'the_seo_framework_og_image_after_featured', '', $args['post_id'] ) )
				goto end;
		}

		//* 6. Fallback: Get header image if exists
		if ( ( $all_allowed || false === in_array( 'header', $args['disallowed'], true ) ) && \current_theme_supports( 'custom-header', 'default-image' ) ) {
			if ( $image = $tsf->get_header_image( true ) )
				goto end;
		}

		//* 7. Fetch image from fallback filter 2
		/**
		 * Applies filters 'the_seo_framework_og_image_after_header' : string
		 * @since 2.5.2
		 */
		fallback_2 : {
			if ( $image = (string) \apply_filters( 'the_seo_framework_og_image_after_header', '', $args['post_id'] ) )
				goto end;
		}

		//* 8. Get the WP 4.5 Site Logo
		if ( ( $all_allowed || false === in_array( 'logo', $args['disallowed'], true ) ) && $tsf->can_use_logo() ) {
			if ( $image = $tsf->get_site_logo( true ) )
				goto end;
		}

		//* 9. Get the WP 4.3 Site Icon
		if ( $all_allowed || false === in_array( 'icon', $args['disallowed'], true ) ) {
			if ( $image = $tsf->get_site_icon( 'full', true ) )
				goto end;
		}

		end :;

		if ( $args['escape'] && $image )
			$image = \esc_url( $image );

		return (string) $image;
	}

	/**
	 * Fetches image from post thumbnail.
	 * Resizes the image between 4096px if bigger. Then it saves the image and
	 * Keeps dimensions relative.
	 *
	 * @since 2.3.0
	 * @since 2.9.0 Changed parameters.
	 * @since 2.9.0 Deprecated.
	 * @since 2.9.3 Now supports 4K, rather than 1500px.
	 * @deprecated
	 *
	 * @param array $args The image args.
	 *              Was: int $id The post/page ID.
	 * @param bool $set_og_dimensions Whether to set Open Graph image dimensions.
	 *             Was: array $depr_args Deprecated. Image arguments.
	 * @return string|null the image url.
	 */
	public function get_image_from_post_thumbnail( $args = array(), $set_og_dimensions = false ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->get_image_from_post_thumbnail()', '2.9.0', 'the_seo_framework()->get_social_image_url_from_post_thumbnail()' );

		if ( is_array( $set_og_dimensions ) ) {
			$tsf->_doing_it_wrong( __METHOD__, 'First parameter are now arguments, second parameter is for setting og dimensions.', '2.9.0' );
			$args = $set_og_dimensions;
			$set_og_dimensions = false;
		}

		$args = $tsf->reparse_image_args( $args );

		$id = \get_post_thumbnail_id( $args['post_id'] );

		$args['get_the_real_ID'] = true;

		$image = $id ? $tsf->parse_og_image( $id, $args, $set_og_dimensions ) : '';

		return $image;
	}

	/**
	 * Detects front page.
	 *
	 * Returns true on SEO settings page if ID is 0.
	 *
	 * @since 2.6.0
	 * @since 2.9.0: Deprecated.
	 * @deprecated
	 *
	 * @param int $id The Page or Post ID.
	 * @return bool
	 */
	public function is_front_page( $id = 0 ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->is_front_page()', '2.9.0', 'the_seo_framework()->is_real_front_page() or the_seo_framework()->is_front_page_by_id()' );

		static $cache = array();

		if ( null !== $cache = $tsf->get_query_cache( __METHOD__, null, $id ) )
			return $cache;

		$is_front_page = false;

		if ( \is_front_page() && empty( $id ) )
			$is_front_page = true;

		//* Elegant Themes Support. Yay.
		if ( false === $is_front_page && empty( $id ) && $tsf->is_home() ) {
			$sof = \get_option( 'show_on_front' );

			if ( 'page' !== $sof && 'posts' !== $sof )
				$is_front_page = true;
		}

		//* Compare against $id
		if ( false === $is_front_page && $id ) {
			$sof = \get_option( 'show_on_front' );

			if ( 'page' === $sof && (int) \get_option( 'page_on_front' ) === $id )
				$is_front_page = true;

			if ( 'posts' === $sof && (int) \get_option( 'page_for_posts' ) === $id )
				$is_front_page = true;
		} elseif ( empty( $id ) && $tsf->is_seo_settings_page() ) {
			$is_front_page = true;
		}

		$tsf->set_query_cache(
			__METHOD__,
			$is_front_page,
			$id
		);

		return $is_front_page;
	}

	/**
	 * Returns http://schema.org json encoded context URL.
	 *
	 * @staticvar string $context
	 * @since 2.6.0
	 * @since 2.9.3 Deprecated.
	 * @deprecated
	 *
	 * @return string The json encoded context url.
	 */
	public function schema_context() {

		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->schema_context()', '2.9.3' );

		static $context;

		if ( isset( $context ) )
			return $context;

		return $context = json_encode( 'http://schema.org' );
	}


	/**
	 * Returns 'WebSite' json encoded type name.
	 *
	 * @staticvar string $context
	 * @since 2.6.0
	 * @since 2.9.3 Deprecated.
	 * @deprecated
	 *
	 * @return string The json encoded type name.
	 */
	public function schema_type() {

		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->schema_type()', '2.9.3' );

		static $type;

		if ( isset( $type ) )
			return $type;

		return $type = json_encode( 'WebSite' );
	}

	/**
	 * Returns json encoded home url.
	 *
	 * @staticvar string $url
	 * @since 2.6.0
	 * @since 2.9.3 Deprecated.
	 * @deprecated
	 *
	 * @return string The json encoded home url.
	 */
	public function schema_home_url() {

		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->schema_home_url()', '2.9.3' );

		static $type;

		if ( isset( $type ) )
			return $type;

		return $type = json_encode( \the_seo_framework()->the_home_url_from_cache() );
	}

	/**
	 * Returns json encoded blogname.
	 *
	 * @staticvar string $name
	 * @since 2.6.0
	 * @since 2.9.3 Deprecated.
	 * @deprecated
	 *
	 * @return string The json encoded blogname.
	 */
	public function schema_blog_name() {

		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->schema_blog_name()', '2.9.3' );

		static $name;

		if ( isset( $name ) )
			return $name;

		return $name = json_encode( \the_seo_framework()->get_blogname() );
	}

	/**
	 * Returns 'BreadcrumbList' json encoded type name.
	 *
	 * @staticvar string $crumblist
	 * @since 2.6.0
	 * @since 2.9.3 Deprecated.
	 * @deprecated
	 *
	 * @return string The json encoded 'BreadcrumbList'.
	 */
	public function schema_breadcrumblist() {

		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->schema_breadcrumblist()', '2.9.3' );

		static $crumblist;

		if ( isset( $crumblist ) )
			return $crumblist;

		return $crumblist = json_encode( 'BreadcrumbList' );
	}

	/**
	 * Returns 'ListItem' json encoded type name.
	 *
	 * @staticvar string $listitem
	 * @since 2.6.0
	 * @since 2.9.3 Deprecated.
	 * @deprecated
	 *
	 * @return string The json encoded 'ListItem'.
	 */
	public function schema_listitem() {

		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->schema_listitem()', '2.9.3' );

		static $listitem;

		if ( isset( $listitem ) )
			return $listitem;

		return $listitem = json_encode( 'ListItem' );
	}

	/**
	 * Returns 'image' json encoded value.
	 *
	 * @staticvar array $images
	 * @since 2.7.0
	 * @since 2.9.0 : 1. No longer uses image from cache, instead: it skips fallback images.
	 *                2. Can now fetch home-page as blog set image.
	 * @since 2.9.3 Deprecated.
	 * @deprecated
	 *
	 * @param int|string $id The page, post, product or term ID.
	 * @param bool $singular Whether the ID is singular.
	 */
	public function schema_image( $id = 0, $singular = false ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->schema_image()', '2.9.3' );

		static $images = array();

		$id = (int) $id;

		if ( isset( $images[ $id ][ $singular ] ) )
			return $images[ $id ][ $singular ];

		$image = '';

		if ( $singular ) {
			if ( $id === $tsf->get_the_front_page_ID() ) {
				if ( $tsf->has_page_on_front() ) {
					$image_args = array(
						'post_id' => $id,
						'skip_fallback' => true,
					);
				} else {
					$image_args = array(
						'post_id' => $id,
						'skip_fallback' => true,
						'disallowed' => array(
							'postmeta',
							'featured',
						),
					);
				}
			} else {
				$image_args = array(
					'post_id' => $id,
					'skip_fallback' => true,
					'disallowed' => array(
						'homemeta'
					),
				);
			}
			$image = $tsf->get_social_image( $image_args );
		} else {
			//* Placeholder.
			$image = '';
		}

		/**
		 * Applies filters 'the_seo_framework_ld_json_breadcrumb_image' : string
		 * @since 2.7.0
		 * @param string $image The current image.
		 * @param int $id The page, post, product or term ID.
		 * @param bool $singular Whether the ID is singular.
		 */
		$image = \apply_filters( 'the_seo_framework_ld_json_breadcrumb_image', $image, $id, $singular );

		return $images[ $id ][ $singular ] = json_encode( \esc_url_raw( $image ) );
	}

	/**
	 * Generate LD+Json search helper.
	 *
	 * @since 2.2.8
	 * @since 2.9.3 Deprecated.
	 * @deprecated
	 *
	 * @return escaped LD+json search helper string.
	 */
	public function ld_json_search() {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->ld_json_search()', '2.9.3' );

		if ( false === $tsf->enable_ld_json_searchbox() )
			return '';

		$context = $this->schema_context();
		$webtype = $this->schema_type();
		$url = $this->schema_home_url();
		$name = $this->schema_blog_name();
		$actiontype = json_encode( 'SearchAction' );

		/**
		 * Applies filters 'the_seo_framework_ld_json_search_url' : string
		 * @since 2.7.0
		 * @param string $search_url The default WordPress search URL without query parameters.
		 */
		$search_url = (string) \apply_filters( 'the_seo_framework_ld_json_search_url', $tsf->the_home_url_from_cache( true ) . '?s=' );

		// Remove trailing quote and add it back.
		$target = mb_substr( json_encode( $search_url ), 0, -1 ) . '{search_term_string}"';

		$queryaction = json_encode( 'required name=search_term_string' );

		$json = sprintf( '{"@context":%s,"@type":%s,"url":%s,"name":%s,"potentialAction":{"@type":%s,"target":%s,"query-input":%s}}', $context, $webtype, $url, $name, $actiontype, $target, $queryaction );

		$output = '';

		if ( $json )
			$output = '<script type="application/ld+json">' . $json . '</script>' . "\r\n";

		return $output;
	}

	/**
	 * Generate Site Name LD+Json script.
	 *
	 * @since 2.6.0
	 * @since 2.9.3 Deprecated.
	 * @deprecated
	 *
	 * @return string The LD+JSon Site Name script.
	 */
	public function ld_json_name() {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->ld_json_name()', '2.9.3' );

		$context = $this->schema_context();
		$webtype = $this->schema_type();
		$url = $this->schema_home_url();
		$name = $this->schema_blog_name();
		$alternate = '';

		$blogname = $tsf->get_blogname();
		$knowledge_name = $tsf->get_option( 'knowledge_name' );

		if ( $knowledge_name && $knowledge_name !== $blogname ) {
			$alternate = json_encode( \esc_html( $knowledge_name ) );
		}

		if ( $alternate ) {
			$json = sprintf( '{"@context":%s,"@type":%s,"name":%s,"alternateName":%s,"url":%s}', $context, $webtype, $name, $alternate, $url );
		} else {
			$json = sprintf( '{"@context":%s,"@type":%s,"name":%s,"url":%s}', $context, $webtype, $name, $url );
		}

		$output = '';
		if ( $json )
			$output = '<script type="application/ld+json">' . $json . '</script>' . "\r\n";

		return $output;
	}

	/**
	 * Return LD+Json Knowledge Graph helper.
	 *
	 * @since 2.2.8
	 * @since 2.9.2 : Now grabs home URL from cache.
	 * @since 2.9.3 Deprecated.
	 * @deprecated
	 *
	 * @return string LD+json Knowledge Graph helper.
	 */
	public function ld_json_knowledge() {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->ld_json_name()', '2.9.3', 'the_seo_framework()->get_ld_json_links()' );

		return $tsf->get_ld_json_links();
	}

	/**
	 * Generate LD+Json breadcrumb helper.
	 *
	 * @since 2.4.2
	 * @since 2.9.3 Deprecated.
	 * @deprecated
	 *
	 * @return escaped LD+json search helper string.
	 */
	public function ld_json_breadcrumbs() {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->ld_json_breadcrumbs()', '2.9.3', 'the_seo_framework()->get_ld_json_breadcrumbs()' );

		return $tsf->get_ld_json_breadcrumbs();
	}

	/**
	 * Generate post breadcrumb.
	 *
	 * @since 2.6.0
	 * @since 2.9.0 Now uses $this->ld_json_breadcrumbs_use_seo_title()
	 * @since 2.9.3 Deprecated.
	 * @deprecated
	 *
	 * @return string $output The breadcrumb script.
	 */
	public function ld_json_breadcrumbs_post() {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->ld_json_breadcrumbs_post()', '2.9.3', 'the_seo_framework()->get_ld_json_breadcrumbs_post()' );

		return $tsf->get_ld_json_breadcrumbs_post();
	}

	/**
	 * Generate page breadcrumb.
	 *
	 * @since 2.6.0
	 * @since 2.9.0 Now uses $this->ld_json_breadcrumbs_use_seo_title()
	 * @since 2.9.3 Deprecated.
	 * @deprecated
	 *
	 * @return string $output The breadcrumb script.
	 */
	public function ld_json_breadcrumbs_page() {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->ld_json_breadcrumbs_page()', '2.9.3', 'the_seo_framework()->get_ld_json_breadcrumbs_page()' );

		return $tsf->get_ld_json_breadcrumbs_page();
	}

	/**
	 * Return home page item for LD Json Breadcrumbs.
	 *
	 * @since 2.4.2
	 * @since 2.9.0 Now uses $this->ld_json_breadcrumbs_use_seo_title()
	 * @since 2.9.3 Deprecated.
	 * @deprecated
	 * @staticvar string $first_item.
	 *
	 * @param string|null $item_type the breadcrumb item type.
	 * @return string Home Breadcrumb item
	 */
	public function ld_json_breadcrumb_first( $item_type = null ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->ld_json_breadcrumb_first()', '2.9.3' );

		static $first_item = null;

		if ( isset( $first_item ) )
			return $first_item;

		if ( is_null( $item_type ) )
			$item_type = json_encode( 'ListItem' );

		$id = json_encode( $tsf->the_home_url_from_cache() );

		if ( $tsf->ld_json_breadcrumbs_use_seo_title() ) {

			$home_title = $tsf->get_option( 'homepage_title' );

			if ( $home_title ) {
				$custom_name = $home_title;
			} elseif ( $tsf->has_page_on_front() ) {
				$home_id = (int) \get_option( 'page_on_front' );

				$custom_name = $tsf->get_custom_field( '_genesis_title', $home_id ) ?: $tsf->get_blogname();
			} else {
				$custom_name = $tsf->get_blogname();
			}
		} else {
			$custom_name = $tsf->get_blogname();
		}

		$custom_name = json_encode( $custom_name );
		$image = $this->schema_image( $tsf->get_the_front_page_ID(), true );

		$breadcrumb = array(
			'type'  => $item_type,
			'pos'   => '1',
			'id'    => $id,
			'name'  => $custom_name,
			'image' => $image,
		);

		return $first_item = $tsf->make_breadcrumb( $breadcrumb, true );
	}

	/**
	 * Return current page item for LD Json Breadcrumbs.
	 *
	 * @since 2.4.2
	 * @since 2.9.0 Now uses $this->ld_json_breadcrumbs_use_seo_title()
	 * @since 2.9.3 Deprecated.
	 * @deprecated
	 * @staticvar string $last_item.
	 * @staticvar string $type The breadcrumb item type.
	 * @staticvar string $id The current post/page/archive url.
	 * @staticvar string $name The current post/page/archive title.
	 *
	 * @param string $item_type the breadcrumb item type.
	 * @param int $pos Last known position.
	 * @param int $post_id The current Post ID
	 * @return string Last Breadcrumb item
	 */
	public function ld_json_breadcrumb_last( $item_type = null, $pos = null, $post_id = null ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->ld_json_breadcrumb_last()', '2.9.3' );

		/**
		 * 2 (becomes 3) holds mostly true for single term items.
		 * This shouldn't run anyway. Pos should always be provided.
		 */
		if ( is_null( $pos ) )
			$pos = 2;

		//* Add current page.
		$pos = $pos + 1;

		if ( is_null( $item_type ) ) {
			static $type = null;

			if ( ! isset( $type ) )
				$type = json_encode( 'ListItem' );

			$item_type = $type;
		}

		if ( empty( $post_id ) )
			$post_id = $tsf->get_the_real_ID();

		static $id = null;
		static $name = null;

		if ( ! isset( $id ) )
			$id = json_encode( $tsf->the_url_from_cache() );

		$title_args = array(
			'term_id' => $post_id,
			'placeholder' => true,
			'meta' => true,
			'notagline' => true,
			'description_title' => true,
			'get_custom_field' => false,
		);

		if ( ! isset( $name ) ) {
			if ( $tsf->ld_json_breadcrumbs_use_seo_title() ) {
				$name = $tsf->get_custom_field( '_genesis_title', $post_id ) ?: $tsf->title( '', '', '', $title_args );
			} else {
				$name = $tsf->title( '', '', '', $title_args );
			}
			$name = json_encode( $name );
		}

		$image = $this->schema_image( $post_id, true );

		$breadcrumb = array(
			'type'  => $item_type,
			'pos'   => (string) $pos,
			'id'    => $id,
			'name'  => $name,
			'image' => $image,
		);

		return $this->make_breadcrumb( $breadcrumb, false );
	}

	/**
	 * Builds a breadcrumb.
	 *
	 * @since 2.6.0
	 * @since 2.9.0 : No longer outputs image if it's not present.
	 * @since 2.9.3 Deprecated.
	 * @deprecated
	 *
	 * @param array $item : {
	 *  'type',
	 *  'pos',
	 *  'id',
	 *  'name'
	 * }
	 * @param bool $comma Whether to add a trailing comma.
	 * @return string The LD+Json breadcrumb.
	 */
	public function make_breadcrumb( $item, $comma = true ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->make_breadcrumb()', '2.9.3' );

		$comma = $comma ? ',' : '';

		if ( $item['image'] && '""' !== $item['image'] ) {
			$retval = sprintf( '{"@type":%s,"position":%s,"item":{"@id":%s,"name":%s,"image":%s}}%s', $item['type'], $item['pos'], $item['id'], $item['name'], $item['image'], $comma );
		} else {
			$retval = sprintf( '{"@type":%s,"position":%s,"item":{"@id":%s,"name":%s}}%s', $item['type'], $item['pos'], $item['id'], $item['name'], $comma );
		}

		return $retval;
	}

	/**
	 * Fetch set Term data.
	 *
	 * @since 2.6.0
	 * @since 2.7.0 Handles term object differently for upgraded database.
	 * @since 3.0.0 Deprecated.
	 *
	 * @deprecated.
	 *
	 * @param object|null $term The TT object, if it isn't set, one is fetched.
	 * @param int         $term_id The term object.
	 * @return array The SEO Framework TT data.
	 */
	public function get_term_data( $term = null, $term_id = 0 ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->get_term_data( $term, $term_id )', '3.0.0', 'the_seo_framework()->get_term_meta( $term_id )' );

		if ( is_null( $term ) )
			$term = $tsf->fetch_the_term( $term_id );

		if ( isset( $term->term_id ) )
			return $tsf->get_term_meta( $term->term_id );

		//* Return null if no term can be set.
		return null;
	}

	/**
	 * Creates canonical URL.
	 *
	 * @since 2.0.0
	 * @since 2.4.2 : Refactored arguments
	 * @since 2.8.0 : No longer tolerates $id as Post object.
	 * @since 2.9.0 : When using 'home => true' args parameter, the home path is added when set.
	 * @since 2.9.2 Added filter usage cache.
	 * @since 3.0.0 Deprecated.
	 * @deprecated
	 * @staticvar array $_has_filters
	 *
	 * @param string $url the url
	 * @param array $args : accepted args : {
	 *    @param bool $paged Return current page URL without pagination if false
	 *    @param bool $paged_plural Whether to add pagination for the second or later page.
	 *    @param bool $from_option Get the canonical uri option
	 *    @param object $post The Post Object.
	 *    @param bool $external Whether to fetch the current WP Request or get the permalink by Post Object.
	 *    @param bool $is_term Fetch url for term.
	 *    @param object $term The term object.
	 *    @param bool $home Fetch home URL.
	 *    @param bool $forceslash Fetch home URL and slash it, always.
	 *    @param int $id The Page or Term ID.
	 * }
	 * @return string Escape url.
	 */
	public function the_url( $url = '', $args = array() ) {

		$tsf = \the_seo_framework();

		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->the_url()', '3.0.0', 'the_seo_framework()->get_canonical_url()' );

		$args = $tsf->reparse_url_args( $args );

		/**
		 * Fetch permalink if Feed.
		 * @since 2.5.2
		 */
		if ( $tsf->is_feed() )
			$url = \get_permalink();

		//* Reset cache.
		$tsf->url_slashit = true;
		$tsf->unset_current_subdomain();
		$tsf->current_host = '';

		$path = '';
		$scheme = '';
		$slashit = true;

		if ( false === $args['home'] && empty( $url ) ) {
			/**
			 * Get URL from options.
			 * @since 2.2.9
			 */
			if ( $args['get_custom_field'] && $tsf->is_singular() ) {
				$custom_url = $tsf->get_custom_field( '_genesis_canonical_uri' );

				if ( $custom_url ) {
					$url = $custom_url;
					$tsf->url_slashit = false;
					$parsed_url = \wp_parse_url( $custom_url );
					$scheme = isset( $parsed_url['scheme'] ) ? $parsed_url['scheme'] : 'http';
				}
			}

			if ( empty( $url ) )
				$path = $tsf->generate_url_path( $args );
		} elseif ( $args['home'] ) {
			$path = $tsf->get_home_path();
		}

		static $_has_filters = null;
		if ( null === $_has_filters ) {
			$_has_filters = array();
			$_has_filters['the_seo_framework_url_path'] = \has_filter( 'the_seo_framework_url_path' );
			$_has_filters['the_seo_framework_url_output_args'] = \has_filter( 'the_seo_framework_url_output_args' );
		}

		if ( $_has_filters['the_seo_framework_url_path'] ) {
			/**
			 * Applies filters 'the_seo_framework_url_path' : array
			 *
			 * @since 2.8.0
			 *
			 * @param string $path the URL path.
			 * @param int $id The current post, page or term ID.
			 * @param bool $external Whether the call is made from outside the current ID scope. Like from the Sitemap.
			 */
			$path = (string) \apply_filters( 'the_seo_framework_url_path', $path, $args['id'], $args['external'] );
		}

		if ( $_has_filters['the_seo_framework_url_output_args'] ) {
			/**
			 * Applies filters 'the_seo_framework_sanitize_redirect_url' : array
			 *
			 * @since 2.8.0
			 *
			 * @param array : { 'url' => The full URL built from $path, 'scheme' => The preferred scheme }
			 * @param string $path the URL path.
			 * @param int $id The current post, page or term ID.
			 * @param bool $external Whether the call is made from outside the current ID scope. Like from the Sitemap.
			 */
			$url_filter = (array) \apply_filters( 'the_seo_framework_url_output_args', array(), $path, $args['id'], $args['external'] );

			if ( $url_filter ) {
				$url = $url_filter['url'];
				$scheme = $url_filter['scheme'];
			}
		}

		//* Non-custom URL
		if ( empty( $url ) ) {
			//* Reset cache if request is for the home URL.
			if ( $args['home'] )
				$tsf->unset_current_subdomain();

			$url = $tsf->add_url_host( $path );
			$scheme = '';

			$url = $tsf->add_url_subdomain( $url );
		}

		$scheme = $scheme ?: $tsf->get_preferred_scheme();

		$url = $tsf->set_url_scheme( $url, $scheme );

		if ( $tsf->url_slashit ) {
			if ( $args['forceslash'] ) {
				$url = \trailingslashit( $url );
			} elseif ( $slashit ) {
				$url = \user_trailingslashit( $url );
			}
		}

		if ( $tsf->pretty_permalinks ) {
			$url = \esc_url( $url, array( 'http', 'https' ) );
		} else {
			//* Keep the &'s more readable.
			$url = \esc_url_raw( $url, array( 'http', 'https' ) );
		}

		return $url;
	}

	/**
	 * Parse and sanitize url args.
	 *
	 * @since 2.4.2
	 * @since 2.9.2 Added filter usage cache.
	 * @since 3.0.0 Deprecated.
	 * @deprecated
	 * @staticvar bool $_has_filter
	 *
	 * @param array $args required The passed arguments.
	 * @param array $defaults The default arguments.
	 * @param bool $get_defaults Return the default arguments. Ignoring $args.
	 * @return array $args parsed args.
	 */
	public function parse_url_args( $args = array(), $defaults = array(), $get_defaults = false ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->parse_url_args()', '3.0.0' );

		//* Passing back the defaults reduces the memory usage.
		if ( empty( $defaults ) ) :
			$defaults = array(
				'paged'            => false,
				'paged_plural'     => true,
				'get_custom_field' => true,
				'external'         => false,
				'is_term'          => false,
				'post'             => null,
				'term'             => null,
				'home'             => false,
				'forceslash'       => false,
				'id'               => $tsf->get_the_real_ID(),
			);

			static $_has_filter = null;
			if ( null === $_has_filter )
				$_has_filter = \has_filter( 'the_seo_framework_url_args' );

			if ( $_has_filter ) {
				/**
				 * @applies filters the_seo_framework_url_args : {
				 *    @param bool $paged Return current page URL without pagination if false
				 *    @param bool $paged_plural Whether to add pagination for the second or later page.
				 *    @param bool $from_option Get the canonical uri option
				 *    @param object $post The Post Object.
				 *    @param bool $external Whether to fetch the current WP Request or get the permalink by Post Object.
				 *    @param bool $is_term Fetch url for term.
				 *    @param object $term The term object.
				 *    @param bool $home Fetch home URL.
				 *    @param bool $forceslash Fetch home URL and slash it, always.
				 *    @param int $id The Page or Term ID.
				 * }
				 *
				 * @since 2.5.0
				 * @since 3.0.0 Deprecated
				 * @deprecated
				 *
				 * @param array $defaults The url defaults.
				 * @param array $args The input args.
				 */
				$defaults = (array) \apply_filters( 'the_seo_framework_url_args', $defaults, $args );
			}
		endif;

		//* Return early if it's only a default args request.
		if ( $get_defaults )
			return $defaults;

		//* Array merge doesn't support sanitation. We're simply type casting here.
		$args['paged']            = isset( $args['paged'] )            ? (bool) $args['paged']            : $defaults['paged'];
		$args['paged_plural']     = isset( $args['paged_plural'] )     ? (bool) $args['paged_plural']     : $defaults['paged_plural'];
		$args['get_custom_field'] = isset( $args['get_custom_field'] ) ? (bool) $args['get_custom_field'] : $defaults['get_custom_field'];
		$args['external']         = isset( $args['external'] )         ? (bool) $args['external']         : $defaults['external'];
		$args['is_term']          = isset( $args['is_term'] )          ? (bool) $args['is_term']          : $defaults['is_term'];
		$args['post']             = isset( $args['post'] )             ? (object) $args['post']           : $defaults['post'];
		$args['term']             = isset( $args['term'] )             ? (object) $args['term']           : $defaults['term'];
		$args['home']             = isset( $args['home'] )             ? (bool) $args['home']             : $defaults['home'];
		$args['forceslash']       = isset( $args['forceslash'] )       ? (bool) $args['forceslash']       : $defaults['forceslash'];
		$args['id']               = isset( $args['id'] )               ? (int) $args['id']                : $defaults['id'];

		return $args;
	}

	/**
	 * Reparse URL args.
	 *
	 * @since 2.6.2
	 * @since 2.9.2 Now passes args to filter.
	 * @since 3.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array $args required The passed arguments.
	 * @return array $args parsed args.
	 */
	public function reparse_url_args( $args = array() ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->reparse_url_args()', '3.0.0' );

		$default_args = $tsf->parse_url_args( $args, '', true );

		if ( is_array( $args ) ) {
			if ( empty( $args ) ) {
				$args = $default_args;
			} else {
				$args = $tsf->parse_url_args( $args, $default_args );
			}
		} else {
			//* Old style parameters are used. Doing it wrong.
			$tsf->_doing_it_wrong( __METHOD__, 'Use $args = array() for parameters.', '2.4.2' );
			$args = $default_args;
		}

		return $args;
	}

	/**
	 * Generate URL from arguments.
	 *
	 * @since 2.6.0
	 * @since 3.0.0 Deprecated.
	 * @deprecated
	 * @NOTE: Handles full path, including home directory.
	 *
	 * @param array $args the URL args.
	 * @return string $path
	 */
	public function generate_url_path( $args = array() ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->generate_url_path()', '3.0.0' );

		$args = $tsf->reparse_url_args( $args );

		if ( $tsf->is_archive() || $args['is_term'] ) :

			$term = $args['term'];

			//* Term or Taxonomy.
			if ( ! isset( $term ) )
				$term = \get_queried_object();

			if ( isset( $term->taxonomy ) ) {
				//* Registered Terms and Taxonomies.
				$path = $tsf->get_relative_term_url( $term, $args );
			} elseif ( ! $args['external'] && isset( $GLOBALS['wp']->request ) ) {
				//* Everything else.
				$_url = \trailingslashit( \get_option( 'home' ) ) . $GLOBALS['wp']->request;
				$path = $tsf->set_url_scheme( $_url, 'relative' );
			} else {
				//* Nothing to see here...
				$path = '';
			}
		elseif ( $tsf->is_search() ) :
			$_url = \get_search_link();
			$path = $tsf->set_url_scheme( $_url, 'relative' );
		else :
			/**
			 * Reworked to use the $args['id'] check based on get_the_real_ID.
			 * @since 2.6.0 & 2.6.2
			 */
			$post_id = isset( $args['post']->ID ) ? $args['post']->ID : $args['id'];

			if ( $tsf->pretty_permalinks && $post_id && $tsf->is_singular( $post_id ) ) {
				$post = \get_post( $post_id );

				//* Don't slash draft links.
				if ( isset( $post->post_status ) && ( 'auto-draft' === $post->post_status || 'draft' === $post->post_status ) )
					$tsf->url_slashit = false;
			}

			$path = $tsf->build_singular_relative_url( $post_id, $args );
		endif;

		return $path;
	}

	/**
	 * Generates relative URL for the Homepage and Singular Posts.
	 *
	 * @since 2.6.5
	 * @NOTE: Handles full path, including home directory.
	 * @since 2.8.0: Continues on empty post ID. Handles it as HomePage.
	 * @since 3.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $post_id The ID.
	 * @param array $args The URL arguments.
	 * @return string relative Post or Page url.
	 */
	public function build_singular_relative_url( $post_id = null, $args = array() ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->build_singular_relative_url()', '3.0.0' );

		if ( empty( $post_id ) ) {
			//* We can't fetch the post ID when there's an external request.
			if ( $args['external'] ) {
				$post_id = 0;
			} else {
				$post_id = $tsf->get_the_real_ID();
			}
		}

		$args = $tsf->reparse_url_args( $args );

		if ( $args['external'] || ! $tsf->is_real_front_page() || ! $tsf->is_front_page_by_id( $post_id ) ) {
			$url = \get_permalink( $post_id );
		} elseif ( $tsf->is_real_front_page() || $tsf->is_front_page_by_id( $post_id ) ) {
			$url = \get_home_url();
		} elseif ( ! $args['external'] ) {
			if ( isset( $GLOBALS['wp']->request ) )
				$url = \trailingslashit( \get_home_url() ) . $GLOBALS['wp']->request;
		}

		//* No permalink found.
		if ( ! isset( $url ) )
			return '';

		$paged = false;

		if ( false === $args['external'] ) {
			$paged = $tsf->is_singular() ? $tsf->page() : $tsf->paged();
			$paged = $tsf->maybe_get_paged( $paged, $args['paged'], $args['paged_plural'] );
		}

		if ( $paged ) {
			if ( $tsf->pretty_permalinks ) {
				if ( $tsf->is_singular() ) {
					$url = \trailingslashit( $url ) . $paged;
				} else {
					$url = \trailingslashit( $url ) . 'page/' . $paged;
				}
			} else {
				if ( $tsf->is_singular() ) {
					$url = \add_query_arg( 'page', $paged, $url );
				} else {
					$url = \add_query_arg( 'paged', $paged, $url );
				}
			}
		}

		return $tsf->set_url_scheme( $url, 'relative' );
	}

	/**
	 * Generates relative URL for current term.
	 *
	 * @since 2.4.2
	 * @since 2.7.0 Added home directory to output.
	 * @since 3.0.0 Deprecated.
	 * @deprecated
	 * @global object $wp_rewrite
	 * @NOTE: Handles full path, including home directory.
	 *
	 * @param object $term The term object.
	 * @param array|bool $args {
	 *    'external' : Whether to fetch the WP Request or get the permalink by Post Object.
	 *    'paged'	: Whether to add pagination for all types.
	 *    'paged_plural' : Whether to add pagination for the second or later page.
	 * }
	 * @return string Relative term or taxonomy URL.
	 */
	public function get_relative_term_url( $term = null, $args = array() ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->get_relative_term_url()', '3.0.0' );

		global $wp_rewrite;

		if ( ! is_array( $args ) ) {
			/**
			 * @since 2.6.0
			 * '$args = array()' replaced '$no_request = false'.
			 */
			$tsf->_doing_it_wrong( __METHOD__, 'Use $args = array() for parameters.', '2.6.0' );

			$no_request = (bool) $args;
			$args = $tsf->parse_url_args( '', '', true );
			$args['external'] = $no_request;
		}

		// We can't fetch the Term object within sitemaps.
		if ( $args['external'] && is_null( $term ) )
			return '';

		if ( is_null( $term ) )
			$term = \get_queried_object();

		$taxonomy = $term->taxonomy;
		$path = $wp_rewrite->get_extra_permastruct( $taxonomy );

		$slug = $term->slug;
		$t = \get_taxonomy( $taxonomy );

		$paged = $tsf->maybe_get_paged( $tsf->paged(), $args['paged'], $args['paged_plural'] );

		if ( empty( $path ) ) :
			//* Default permalink structure.

			if ( 'category' === $taxonomy ) {
				$path = '?cat=' . $term->term_id;
			} elseif ( isset( $t->query_var ) && '' !== $t->query_var ) {
				$path = '?' . $t->query_var . '=' . $slug;
			} else {
				$path = '?taxonomy=' . $taxonomy . '&term=' . $slug;
			}

			if ( $paged )
				$path .= '&paged=' . $paged;

			//* Don't slash it.
			$tsf->url_slashit = false;

		else :
			if ( $t->rewrite['hierarchical'] ) {
				$hierarchical_slugs = array();
				$ancestors = \get_ancestors( $term->term_id, $taxonomy, 'taxonomy' );

				foreach ( (array) $ancestors as $ancestor ) {
					$ancestor_term = \get_term( $ancestor, $taxonomy );
					$hierarchical_slugs[] = $ancestor_term->slug;
				}

				$hierarchical_slugs = array_reverse( $hierarchical_slugs );
				$hierarchical_slugs[] = $slug;

				$path = str_replace( "%$taxonomy%", implode( '/', $hierarchical_slugs ), $path );
			} else {
				$path = str_replace( "%$taxonomy%", $slug, $path );
			}

			if ( $paged )
				$path = \trailingslashit( $path ) . 'page/' . $paged;

			$path = \user_trailingslashit( $path, 'category' );
		endif;

		//* Add plausible domain subdirectories.
		$url = \trailingslashit( \get_option( 'home' ) ) . ltrim( $path, ' \\/' );
		$path = $tsf->set_url_scheme( $url, 'relative' );

		return $path;
	}

	/**
	 * Adds subdomain to input URL.
	 *
	 * @since 2.6.5
	 * @since 3.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $url The current URL without subdomain.
	 * @return string $url Fully qualified URL with possible subdomain.
	 */
	public function add_url_subdomain( $url = '' ) {

		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->add_url_subdomain()', '3.0.0' );

		$url = $tsf->make_fully_qualified_url( $url );

		//* Add subdomain, if set.
		if ( $subdomain = $tsf->get_current_subdomain() ) {
			$parsed_url = \wp_parse_url( $url );
			$scheme = isset( $parsed_url['scheme'] ) ? $parsed_url['scheme'] : 'http';
			$url = str_replace( $scheme . '://', '', $url );

			//* Put it together.
			$url = $scheme . '://' . $subdomain . '.' . $url;
		}

		return $url;
	}

	/**
	 * Fetches current subdomain set by $this->set_current_subdomain();
	 *
	 * @since 2.7.0
	 * @since 3.0.0 Deprecated.
	 * @deprecated
	 * @staticvar string $subdomain
	 *
	 * @param null|string $set Whether to set a new subdomain.
	 * @param bool $unset Whether to remove subdomain from cache.
	 * @return string|bool The set subdomain, false if none is set.
	 */
	public function get_current_subdomain( $set = null, $unset = false ) {

		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->get_current_subdomain()', '3.0.0' );

		static $subdomain = null;

		if ( isset( $set ) )
			$subdomain = \esc_html( $set );

		if ( $unset )
			unset( $subdomain );

		if ( isset( $subdomain ) )
			return $subdomain;

		return false;
	}

	/**
	 * Sets current working subdomain.
	 *
	 * @since 2.7.0
	 * @since 3.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $subdomain The current subdomain.
	 * @return string The set subdomain.
	 */
	public function set_current_subdomain( $subdomain = '' ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->unset_current_subdomain()', '3.0.0' );

		return $tsf->get_current_subdomain( $subdomain );
	}

	/**
	 * Unsets current working subdomain.
	 *
	 * @since 2.7.0
	 * @since 3.0.0 Deprecated.
	 * @deprecated
	 */
	public function unset_current_subdomain() {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->unset_current_subdomain()', '3.0.0' );

		$tsf->get_current_subdomain( null, true );
	}

	/**
	 * Create full valid URL with parsed host.
	 * Don't forget to use set_url_scheme() afterwards.
	 *
	 * Note: will return $path if no host can be found.
	 *
	 * @since 2.6.5
	 * @since 3.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $path Current path.
	 * @return string Full valid URL with http host.
	 */
	public function add_url_host( $path = '' ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->add_url_host()', '3.0.0' );

		$host = $tsf->current_host ?: $tsf->get_home_host();

		$scheme = $host ? 'http://' : '';

		return $url = $scheme . \trailingslashit( $host ) . ltrim( $path, ' \\/' );
	}

	/**
	 * Fetches home URL subdirectory path. Like "wordpress.org/plugins/".
	 *
	 * @since 2.7.0
	 * @since 3.0.0 Deprecated.
	 * @deprecated
	 * @staticvar string $cache
	 *
	 * @return string The home URL path.
	 */
	public function get_home_path() {

		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->get_home_path()', '3.0.0' );

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		$path = '';

		$parsed_url = \wp_parse_url( \get_option( 'home' ) );

		if ( ! empty( $parsed_url['path'] ) && $path = ltrim( $parsed_url['path'], ' \\/' ) )
			$path = '/' . $path;

		return $cache = $path;
	}

	/**
	 * Cache current URL in static variable
	 * Must be called inside the loop
	 *
	 * @since 2.2.2
	 * @since 3.0.0 Deprecated.
	 * @deprecated
	 * @staticvar array $url_cache
	 *
	 * @param string $url the url
	 * @param int $post_id the page id, if empty it will fetch the requested ID, else the page uri
	 * @param bool $paged Return current page URL with pagination
	 * @param bool $from_option Get the canonical uri option
	 * @param bool $paged_plural Whether to allow pagination on second or later pages.
	 * @return string The url
	 */
	public function the_url_from_cache( $url = '', $post_id = null, $paged = false, $from_option = true, $paged_plural = true ) {

		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->the_url_from_cache()', '3.0.0', `the_seo_framework()->get_current_canonical_url()` );

		return $tsf->get_current_canonical_url();
	}

	/**
	 * Cache home URL in static variable
	 *
	 * @since 2.5.0
	 * @since 2.9.0 Now returns subdirectory installations paths too.
	 * @since 3.0.0 1: Now no longer regenerates home URL when parameters differ.
	 *              2: Deprecated.
	 * @deprecated
	 * @staticvar string $url
	 *
	 * @param bool $force_slash Force slash
	 * @return string The url
	 */
	public function the_home_url_from_cache( $force_slash = false ) {

		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->the_home_url_from_cache()', '3.0.0', 'the_seo_framework()->get_homepage_permalink()' );

		static $url;

		if ( ! $url )
			$url = $tsf->get_homepage_permalink();

		return $force_slash ? \trailingslashit( $url ) : $url;
	}
}
