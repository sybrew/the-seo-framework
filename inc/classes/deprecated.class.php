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
	 *				accordingly upgraded database.
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

		if ( false === $tsf->enable_ld_json_sitename() )
			return '';

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
}
