<?php
/**
 * @package The_SEO_Framework\Classes\Internal\Deprecated
 * @subpackage The_SEO_Framework\Debug\Deprecated
 */

namespace The_SEO_Framework\Internal;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2021 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\{
	memo,   // Precautionary.
	umemo,  // Precautionary.
};

/**
 * Class The_SEO_Framework\Internal\Deprecated
 *
 * Contains all deprecated functions.
 *
 * @since 2.8.0
 * @since 3.1.0 Removed all methods deprecated in 3.0.0.
 * @since 4.0.0 Removed all methods deprecated in 3.1.0.
 * @since 4.1.4 Removed all methods deprecated in 4.0.0.
 * @since 4.2.0 1. Changed namespace from \The_SEO_Framework to \The_SEO_Framework\Internal
 *              2. Removed all methods deprecated in 4.1.0.
 * @ignore
 */
final class Deprecated {

	/**
	 * Appends given query to given URL.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 Now uses parse_str and add_query_arg, preventing duplicated entries.
	 * @since 4.1.4 Deprecated silently.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param string $url   A fully qualified URL.
	 * @param string $query A fully qualified query taken from parse_url( $url, PHP_URL_QUERY );
	 * @return string A fully qualified URL with appended $query.
	 */
	public function append_php_query( $url, $query = '' ) {
		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->append_php_query()', '4.2.0', 'tsf()->append_url_query()' );
		return $tsf->append_url_query( $url, $query );
	}

	/**
	 * Runs header actions.
	 *
	 * @since 3.1.0
	 * @since 4.2.0 Deprecated
	 * @deprecated
	 *
	 * @param string $location Either 'before' or 'after'.
	 * @return string The filter output.
	 */
	public function get_legacy_header_filters_output( $location = 'before' ) {

		$output = '';

		/**
		 * @since 2.2.6
		 * @since 4.2.0 Deprecated
		 * @deprecated
		 * @param array $functions {
		 *    'callback' => string|array The function to call.
		 *    'args'     => scalar|array Arguments. When array, each key is a new argument.
		 * }
		 */
		$functions = (array) \apply_filters_deprecated(
			"the_seo_framework_{$location}_output",
			[ [] ],
			'4.2.0 of The SEO Framework',
			"Action the_seo_framework_{$location}_meta"
		);

		foreach ( $functions as $function ) {
			if ( ! empty( $function['callback'] ) ) {
				$output .= \call_user_func_array( $function['callback'], (array) ( $function['args'] ?? '' ) );
			}
		}

		return $output;
	}

	/**
	 * Generates front-end HTMl output.
	 *
	 * @since 4.0.5
	 * @since 4.1.4 Deprecated silently.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @return string The HTML output.
	 */
	public function get_html_output() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_html_output()', '4.2.0' );

		$robots = $tsf->robots();

		/**
		 * @since 2.6.0
		 * @param string $before The content before the SEO output.
		 */
		$before = (string) \apply_filters( 'the_seo_framework_pre', '' );

		$before_legacy = $tsf->get_legacy_header_filters_output( 'before' );

		// Limit processing and redundant tags on 404 and search.
		if ( $tsf->is_search() ) :
			$output = $tsf->og_locale()
					. $tsf->og_type()
					. $tsf->og_title()
					. $tsf->og_url()
					. $tsf->og_sitename()
					. $tsf->theme_color()
					. $tsf->shortlink()
					. $tsf->canonical()
					. $tsf->paged_urls()
					. $tsf->google_site_output()
					. $tsf->bing_site_output()
					. $tsf->yandex_site_output()
					. $tsf->baidu_site_output()
					. $tsf->pint_site_output();
		elseif ( $tsf->is_404() ) :
			$output = $tsf->theme_color()
					. $tsf->google_site_output()
					. $tsf->bing_site_output()
					. $tsf->yandex_site_output()
					. $tsf->baidu_site_output()
					. $tsf->pint_site_output();
		elseif ( $tsf->is_query_exploited() ) :
			// aqp = advanced query protection
			$output = '<meta name="tsf:aqp" value="1" />' . PHP_EOL;
		else :
			// Inefficient concatenation is inefficient. Improve this?
			$output = $tsf->the_description()
					. $tsf->og_image()
					. $tsf->og_locale()
					. $tsf->og_type()
					. $tsf->og_title()
					. $tsf->og_description()
					. $tsf->og_url()
					. $tsf->og_sitename()
					. $tsf->facebook_publisher()
					. $tsf->facebook_author()
					. $tsf->facebook_app_id()
					. $tsf->article_published_time()
					. $tsf->article_modified_time()
					. $tsf->twitter_card()
					. $tsf->twitter_site()
					. $tsf->twitter_creator()
					. $tsf->twitter_title()
					. $tsf->twitter_description()
					. $tsf->twitter_image()
					. $tsf->theme_color()
					. $tsf->shortlink()
					. $tsf->canonical()
					. $tsf->paged_urls()
					. $tsf->ld_json()
					. $tsf->google_site_output()
					. $tsf->bing_site_output()
					. $tsf->yandex_site_output()
					. $tsf->baidu_site_output()
					. $tsf->pint_site_output();
		endif;

		$after_legacy = $tsf->get_legacy_header_filters_output( 'after' );

		/**
		 * @since 2.6.0
		 * @param string $after The content after the SEO output.
		 */
		$after = (string) \apply_filters( 'the_seo_framework_pro', '' );

		return "{$robots}{$before}{$before_legacy}{$output}{$after_legacy}{$after}";
	}

	/**
	 * Generates the `noindex` robots meta code array from arguments.
	 *
	 * This method is tailor-made for everything that relies on the noindex-state, as it's
	 * a very controlling and powerful feature.
	 *
	 * Note that the home-as-blog page can be used for this method.
	 *
	 * We deprecated this because in the real world, it barely mattered. We'd much rather
	 * have a proper and predictable API.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now uses the new taxonomy robots settings.
	 * @since 4.1.4 Soft deprecated. Use 'robots_meta' instead.
	 * @since 4.2.0 1. Hard deprecation.
	 *              2. Now supports the `$args['pta']` index. (inferred)
	 * @deprecated
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'taxonomy', and 'pta'.
	 * @param int <bit>  $ignore The ignore level. {
	 *    0 = 0b00: Ignore nothing.
	 *    1 = 0b01: Ignore protection. (\The_SEO_Framework\ROBOTS_IGNORE_PROTECTION)
	 *    2 = 0b10: Ignore post/term setting. (\The_SEO_Framework\ROBOTS_IGNORE_SETTINGS)
	 *    3 = 0b11: Ignore protection and post/term setting.
	 * }
	 * @return bool Whether noindex is set or not
	 */
	public function is_robots_meta_noindex_set_by_args( $args, $ignore = 0b00 ) {
		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_robots_meta_noindex_set_by_args()', '4.2.0', 'tsf()->generate_robots_meta()' );
		$meta = $tsf->generate_robots_meta( $args, null, $ignore );
		return isset( $meta['noindex'] ) && 'noindex' === $meta['noindex'];
	}

	/**
	 * Returns the `noindex`, `nofollow`, `noarchive` robots meta code array.
	 *
	 * @since 2.2.2
	 * @since 2.2.4 Added robots SEO settings check.
	 * @since 2.2.8 Added check for empty archives.
	 * @since 2.8.0 Added check for protected/private posts.
	 * @since 3.0.0 1. Removed noodp.
	 *              2. Improved efficiency by grouping if statements.
	 * @since 3.1.0 1. Simplified statements, often (not always) speeding things up.
	 *              2. Now checks for wc_shop and blog types for pagination.
	 *              3. Removed noydir.
	 * @since 4.0.0 1. Now tests for qubit metadata.
	 *              2. Added custom query support.
	 *              3. Added two parameters.
	 * @since 4.0.2 1. Added new copyright directive tags.
	 *              2. Now strictly parses the validity of robots directives via a boolean check.
	 * @since 4.0.3 1. Changed `max_snippet_length` to `max_snippet`
	 *              2. Changed the copyright directive's spacer from `=` to `:`.
	 * @since 4.0.5 1. Removed copyright directive bug workaround. <https://kb.theseoframework.com/kb/why-is-max-image-preview-none-purged/>
	 *              2. Now sets noindex and nofollow when queries are exploited (requires option enabled).
	 * @since 4.1.4 Deprecated silently. Use generate_robots_meta() instead.
	 * @since 4.2.0 1. Hard deprecation.
	 *              2. Now supports the `$args['pta']` index. (inferred)
	 * @deprecated
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'taxonomy', and 'pta'.
	 * @param int <bit>  $ignore The ignore level. {
	 *    0 = 0b00: Ignore nothing.
	 *    1 = 0b01: Ignore protection. (\The_SEO_Framework\ROBOTS_IGNORE_PROTECTION)
	 *    2 = 0b10: Ignore post/term setting. (\The_SEO_Framework\ROBOTS_IGNORE_SETTINGS)
	 *    3 = 0b11: Ignore protection and post/term setting.
	 * }
	 * @return array Only values actualized for display: {
	 *    string index : string value
	 * }
	 */
	public function robots_meta( $args = null, $ignore = 0b00 ) {
		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->robots_meta()', '4.2.0', 'tsf()->generate_robots_meta()' );
		return $tsf->generate_robots_meta( $args, null, $ignore );
	}

	/**
	 * Determines whether to add a line within robots based by plugin detection, or sitemap output option.
	 *
	 * @since 2.6.0
	 * @since 2.8.0 Added check_option parameter.
	 * @since 2.9.0 Now also checks for subdirectory installations.
	 * @since 2.9.2 Now also checks for permalinks.
	 * @since 2.9.3 Now also checks for sitemap_robots option.
	 * @since 3.1.0 Removed Jetpack's sitemap check -- it's no longer valid.
	 * @since 4.0.0 1. Now uses has_robots_txt()
	 *              2. Now uses the get_robots_txt_url() to determine validity.
	 * @since 4.1.4 Soft deprecated.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param bool $check_option Whether to check for sitemap option.
	 * @return bool True when no conflicting plugins are detected or when The SEO Framework's Sitemaps are output.
	 */
	public function can_do_sitemap_robots( $check_option = true ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->can_do_sitemap_robots()', '4.2.0' );

		if ( $check_option ) {
			if ( ! $tsf->get_option( 'sitemaps_output' )
			|| ! $tsf->get_option( 'sitemaps_robots' ) )
				return false;
		}

		return ! $tsf->has_robots_txt() && \strlen( $tsf->get_robots_txt_url() );
	}

	/**
	 * Setting nav tab wrappers.
	 * Outputs Tabs and settings content.
	 *
	 * @since 2.3.6
	 * @since 2.6.0 Refactored.
	 * @since 3.1.0 Now prefixes the IDs.
	 * @since 4.0.0 Deprecated third parameter, silently.
	 * @since 4.1.4 Deprecated silently. Use `\The_SEO_Framework\Bridges\SeoSettings::_nav_tab_wrapper()` instead.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param string $id      The nav-tab ID
	 * @param array  $tabs    The tab content {
	 *    string tab ID => array : {
	 *       string   name     : Tab name.
	 *       callable callback : Output function.
	 *       string   dashicon : The dashicon to use.
	 *       mixed    args     : Optional callback function args.
	 *    }
	 * }
	 * @param null   $depr     Deprecated.
	 * @param bool   $use_tabs Whether to output tabs, only works when $tabs count is greater than 1.
	 */
	public function nav_tab_wrapper( $id, $tabs = [], $depr = null, $use_tabs = true ) {
		\tsf()->_deprecated_function( 'tsf()->nav_tab_wrapper()', '4.2.0', '\The_SEO_Framework\Bridges\SeoSettings::_nav_tab_wrapper' );
		\The_SEO_Framework\Bridges\SeoSettings::_nav_tab_wrapper( $id, $tabs, $use_tabs );
	}

	/**
	 * Outputs in-post flex navigational wrapper and its content.
	 *
	 * @since 2.9.0
	 * @since 3.0.0 Converted to view.
	 * @since 4.0.0 Deprecated third parameter, silently.
	 * @since 4.1.4 Deprecated silently. Use `\The_SEO_Framework\Bridges\PostSettings()` instead.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param string $id       The nav-tab ID
	 * @param array  $tabs     The tab content {
	 *    string tab ID => array : {
	 *       string   name     : Tab name.
	 *       callable callback : Output function.
	 *       string   dashicon : The dashicon to use.
	 *       mixed    args     : Optional callback function args.
	 *    }
	 * }
	 * @param null   $_depr    Deprecated.
	 * @param bool   $use_tabs Whether to output tabs, only works when $tabs count is greater than 1.
	 */
	public function inpost_flex_nav_tab_wrapper( $id, $tabs = [], $_depr = null, $use_tabs = true ) {
		\tsf()->_deprecated_function( 'tsf()->inpost_flex_nav_tab_wrapper()', '4.2.0', '\The_SEO_Framework\Bridges\PostSettings::_flex_nav_tab_wrapper' );
		\The_SEO_Framework\Bridges\PostSettings::_flex_nav_tab_wrapper( $id, $tabs, $use_tabs );
	}

	/**
	 * Returns social image uploader form button.
	 * Also registers additional i18n strings for JS.
	 *
	 * @since 2.8.0
	 * @since 3.1.0 No longer prepares media l10n data.
	 * @since 4.0.0 Now adds a media preview dispenser.
	 * @since 4.1.2 No longer adds a redundant title to the selection button.
	 * @since 4.1.4 Deprecated. Use `get_image_uploader_form()` instead.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param string $input_id Required. The HTML input id to pass URL into.
	 * @return string The image uploader button.
	 */
	public function get_social_image_uploader_form( $input_id ) {
		\tsf()->_deprecated_function( 'tsf()->get_social_image_uploader_form()', '4.2.0', 'The_SEO_Framework\Interpreters\Form::get_image_uploader_form()' );
		return \The_SEO_Framework\Interpreters\Form::get_image_uploader_form( [ 'id' => $input_id ] );
	}

	/**
	 * Returns logo uploader form buttons.
	 * Also registers additional i18n strings for JS.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 No longer prepares media l10n data.
	 * @since 4.0.0 Now adds a media preview dispenser.
	 * @since 4.1.4 Deprecated silently. Use `get_image_uploader_form()` instead.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param string $input_id Required. The HTML input id to pass URL into.
	 * @return string The image uploader button.
	 */
	public function get_logo_uploader_form( $input_id ) {
		\tsf()->_deprecated_function( 'tsf()->get_logo_uploader_form()', '4.2.0', 'The_SEO_Framework\Interpreters\Form::get_image_uploader_form()' );
		return \The_SEO_Framework\Interpreters\Form::get_image_uploader_form( [
			'id'   => $input_id,
			'data' => [
				'inputType' => 'logo',
				'width'     => 512,
				'height'    => 512,
				'minWidth'  => 112,
				'minHeight' => 112,
				'flex'      => true,
			],
			'i18n' => [
				'button_title' => '',
				'button_text'  => \__( 'Select Logo', 'autodescription' ),
			],
		] );
	}

	/**
	 * Proportionate dimensions based on Width and Height.
	 * AKA Aspect Ratio.
	 *
	 * @since 2.6.0
	 * @ignore Unused. The relying methods were yeeted off in 4.0.0.
	 *                 "We no longer automatically resize images when they’re deemed too large."
	 * @since 4.1.4 Deprecated silently. Marked for quick deletion.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param int $i  The dimension to resize.
	 * @param int $r1 The dimension that determines the ratio.
	 * @param int $r2 The dimension to proportionate to.
	 * @return int The proportional dimension, rounded.
	 */
	public function proportionate_dimensions( $i, $r1, $r2 ) {
		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->proportionate_dimensions()', '4.2.0' );
		return round( $i / ( $r1 / $r2 ) );
	}

	/**
	 * Returns the SEO Settings page URL.
	 *
	 * @since 2.6.0
	 * @since 4.1.4 Deprecated silently. Use `get_seo_settings_page_url()` instead.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @return string The escaped SEO Settings page URL.
	 */
	public function seo_settings_page_url() {
		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->seo_settings_page_url()', '4.2.0', 'tsf()->get_seo_settings_page_url()' );
		return $tsf->get_seo_settings_page_url();
	}

	/**
	 * Returns default user meta.
	 *
	 * @since 3.0.0
	 * @since 4.1.4 Deprecated silently. Use `get_user_meta_defaults()` instead.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @return array The default user meta index and values.
	 */
	public function get_default_user_data() {
		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_default_user_data()', '4.2.0', 'tsf()->get_user_meta_defaults()' );
		return $tsf->get_user_meta_defaults();
	}

	/**
	 * Fetches user SEO user meta data by name.
	 * Memoizes all meta data per $user_id.
	 *
	 * If no $user_id is supplied, it will fetch the current logged in user ID.
	 * TODO supplement $default===null for $this->get_user_meta_defaults()[$option]?
	 *
	 * @since 2.7.0
	 * @since 3.0.0 1. Default is no longer cached.
	 *              2. Now always fallbacks to $default.
	 *              3. Added not-found cache.
	 * @since 4.1.4 Deprecated silently. Use `get_user_meta()` instead.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param int    $user_id The user ID. When empty, it will try to fetch the current user.
	 * @param string $option  The option name.
	 * @param mixed  $default The default value to return when the data doesn't exist.
	 * @return mixed The metadata value.
	 */
	public function get_user_option( $user_id = 0, $option = '', $default = null ) {
		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_user_option()', '4.2.0', 'tsf()->get_user_meta_item()' );
		return $tsf->get_user_meta_item( $user_id ?: $tsf->get_user_id(), $option ) ?: $default;
	}

	/**
	 * Returns current post author option.
	 *
	 * @since 3.0.0
	 * @since 4.1.4 Silently deprecated. use `get_current_post_author_id()` instead.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param int    $author_id The author ID. When empty, it will return $default.
	 * @param string $option    The option name. When empty, it will return $default.
	 * @param mixed  $default   The default value to return when the data doesn't exist.
	 * @return mixed The metadata value
	 */
	public function get_author_option( $author_id, $option, $default = null ) {
		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_author_option()', '4.2.0', 'tsf()->get_current_post_author_id()' );
		return $tsf->get_user_meta_item( $option, $author_id ?: $tsf->get_current_post_author_id() ) ?: $default;
	}

	/**
	 * Returns current post author option.
	 *
	 * @since 3.0.0
	 * @since 4.1.4 Silently deprecated. Use `get_current_post_author_meta_item()` instead.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param string $option  The option name.
	 * @param mixed  $default The default value to return when the data doesn't exist.
	 * @return mixed The metadata value
	 */
	public function get_current_author_option( $option, $default = null ) {
		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_current_author_option()', '4.2.0', 'tsf()->get_current_post_author_meta_item()' );
		return $tsf->get_current_post_author_meta_item( $option ) ?: $default;
	}

	/**
	 * Determines if the $post is the WooCommerce plugin shop page.
	 *
	 * @since 2.5.2
	 * @since 4.0.5 Now has a first parameter `$post`.
	 * @since 4.0.5 Soft deprecated.
	 * @since 4.1.4 1. Another silent deprecation. Use `is_shop()` instead.
	 *              2. Removed output memoization.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 * @internal
	 *
	 * @param int|WP_Post|null $post (Optional) Post ID or post object.
	 * @return bool True if on the WooCommerce shop page.
	 */
	public function is_wc_shop( $post = null ) {

		\tsf()->_deprecated_function( 'tsf()->is_wc_shop()', '4.2.0', 'tsf()->is_shop()' );

		if ( isset( $post ) ) {
			$post = \get_post( $post );
			$id   = $post ? $post->ID : 0;
		} else {
			$id = null;
		}

		if ( isset( $id ) ) {
			$is_shop = (int) \get_option( 'woocommerce_shop_page_id' ) === $id;
		} else {
			$is_shop = ! \is_admin() && \function_exists( 'is_shop' ) && \is_shop();
		}

		return $is_shop;
	}

	/**
	 * Determines if the page is the WooCommerce plugin Product page.
	 *
	 * @since 2.5.2
	 * @since 4.0.0 1. Added admin support.
	 *              2. Added parameter for the Post ID or post to test.
	 * @since 4.0.5 Soft deprecated.
	 * @since 4.1.4 1. Another silent deprecation. Use `is_product()` instead.
	 *              2. Removed output memoization.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 * @internal
	 *
	 * @param int|\WP_Post $post When set, checks if the post is of type product.
	 * @return bool True if on a WooCommerce Product page.
	 */
	public function is_wc_product( $post = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_wc_product()', '4.2.0', 'tsf()->is_product()' );

		if ( \is_admin() )
			return $tsf->is_wc_product_admin();

		if ( $post ) {
			$is_product = 'product' === \get_post_type( $post );
		} else {
			$is_product = \function_exists( 'is_product' ) && \is_product();
		}

		return $is_product;
	}

	/**
	 * Detects products within the admin area.
	 *
	 * @since 4.0.0
	 * @see $this->is_wc_product()
	 * @since 4.0.5 Soft deprecated.
	 * @since 4.1.4 1. Another silent deprecation. Use `is_product_admin()` instead.
	 *              2. Removed output memoization.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 * @internal
	 *
	 * @return bool
	 */
	public function is_wc_product_admin() {
		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_wc_product_admin()', '4.2.0', 'tsf()->is_product_admin()' );
		// Checks for "is_singular_admin()" because the post type is non-hierarchical.
		return $tsf->is_singular_admin() && 'product' === $tsf->get_admin_post_type();
	}

	/**
	 * Updates user SEO option.
	 *
	 * @since 2.7.0
	 * @since 2.8.0 New users now get a new array assigned.
	 * @since 4.1.4 Deprecated silently. Use `update_single_user_meta_item()` instead.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param int    $user_id The user ID.
	 * @param string $option  The user's SEO metadata option.
	 * @param mixed  $value   The escaped option value.
	 * @return bool True on success. False on failure.
	 */
	public function update_user_option( $user_id = 0, $option = '', $value = '' ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->update_user_option()', '4.2.0', 'tsf()->update_single_user_meta_item()' );

		if ( ! $option )
			return false;

		if ( empty( $user_id ) )
			$user_id = $tsf->get_user_id();

		if ( empty( $user_id ) )
			return false;

		$meta = $tsf->get_user_meta( $user_id, false );

		/**
		 * @since 2.8.0 initializes new array on empty values.
		 */
		\is_array( $meta ) or $meta = [];

		$meta[ $option ] = $value;

		return \update_user_meta( $user_id, THE_SEO_FRAMEWORK_USER_OPTIONS, $meta );
	}

	/**
	 * Helper function that constructs name attributes for use in form fields.
	 *
	 * Other page implementation classes may wish to construct and use a
	 * get_field_id() method, if the naming format needs to be different.
	 *
	 * @since 2.2.2
	 * @since 4.1.4 Deprecated silently.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param string $name Field name base
	 * @return string Full field name
	 */
	public function get_field_name( $name ) {
		\tsf()->_deprecated_function( 'tsf()->get_field_name()', '4.2.0' );
		return \The_SEO_Framework\Interpreters\Settings_Input::get_field_name( $name );
	}

	/**
	 * Echo constructed name attributes in form fields.
	 *
	 * @since 2.2.2
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 * @uses $this->get_field_name() Construct name attributes for use in form fields.
	 *
	 * @param string $name Field name base
	 */
	public function field_name( $name ) {
		\tsf()->_deprecated_function( 'tsf()->field_name()', '4.2.0' );
		return \The_SEO_Framework\Interpreters\Settings_Input::field_name( $name );
	}

	/**
	 * Helper function that constructs id attributes for use in form fields.
	 *
	 * @since 2.2.2
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param string $id Field id base
	 * @return string Full field id
	 */
	public function get_field_id( $id ) {
		\tsf()->_deprecated_function( 'tsf()->get_field_id()', '4.2.0' );
		return \The_SEO_Framework\Interpreters\Settings_Input::get_field_id( $id );
	}

	/**
	 * Echo constructed id attributes in form fields.
	 *
	 * @since 2.2.2
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 * @uses $this->get_field_id() Constructs id attributes for use in form fields.
	 *
	 * @param string  $id Field id base.
	 * @param boolean $echo Whether to escape echo or just return.
	 * @return string Full field id
	 */
	public function field_id( $id, $echo = true ) {
		\tsf()->_deprecated_function( 'tsf()->field_id()', '4.2.0' );
		if ( $echo ) {
			// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- this escapes.
			echo \The_SEO_Framework\Interpreters\Settings_Input::field_id( $id );
		} else {
			return \The_SEO_Framework\Interpreters\Settings_Input::field_id( $id );
		}
	}

	/**
	 * Mark up content with code tags.
	 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
	 *
	 * @since 2.0.0
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param string $content Content to be wrapped in code tags.
	 * @return string Content wrapped in code tags.
	 */
	public function code_wrap( $content ) {
		\tsf()->_deprecated_function( 'tsf()->code_wrap()', '4.2.0' );
		return \The_SEO_Framework\Interpreters\HTML::code_wrap( $content );
	}

	/**
	 * Mark up content with code tags.
	 * Escapes no HTML.
	 *
	 * @since 2.2.2
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param string $content Content to be wrapped in code tags.
	 * @return string Content wrapped in code tags.
	 */
	public function code_wrap_noesc( $content ) {
		\tsf()->_deprecated_function( 'tsf()->code_wrap_noesc()', '4.2.0' );
		return \The_SEO_Framework\Interpreters\HTML::code_wrap_noesc( $content );
	}

	/**
	 * Mark up content in description wrap.
	 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
	 *
	 * @since 2.7.0
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param string $content Content to be wrapped in the description wrap.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public function description( $content, $block = true ) {
		\tsf()->_deprecated_function( 'tsf()->description()', '4.2.0' );
		return \The_SEO_Framework\Interpreters\HTML::description( $content, $block );
	}

	/**
	 * Mark up content in description wrap.
	 *
	 * @since 2.7.0
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param string $content Content to be wrapped in the description wrap. Expected to be escaped.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public function description_noesc( $content, $block = true ) {
		\tsf()->_deprecated_function( 'tsf()->description_noesc()', '4.2.0' );
		return \The_SEO_Framework\Interpreters\HTML::description_noesc( $content, $block );
	}

	/**
	 * Mark up content in attention wrap.
	 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
	 *
	 * @since 3.1.0
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param string $content Content to be wrapped in the attention wrap.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public function attention( $content, $block = true ) {
		\tsf()->_deprecated_function( 'tsf()->attention()', '4.2.0' );
		return \The_SEO_Framework\Interpreters\HTML::attention( $content, $block );
	}

	/**
	 * Mark up content in attention wrap.
	 *
	 * @since 3.1.0
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param string $content Content to be wrapped in the attention wrap. Expected to be escaped.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public function attention_noesc( $content, $block = true ) {
		\tsf()->_deprecated_function( 'tsf()->attention_noesc()', '4.2.0' );
		return \The_SEO_Framework\Interpreters\HTML::attention_noesc( $content, $block );
	}

	/**
	 * Mark up content in a description+attention wrap.
	 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
	 *
	 * @since 3.1.0
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param string $content Content to be wrapped in the wrap. Expected to be escaped.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public function attention_description( $content, $block = true ) {
		\tsf()->_deprecated_function( 'tsf()->attention_description()', '4.2.0' );
		return \The_SEO_Framework\Interpreters\HTML::attention_description( $content, $block );
	}

	/**
	 * Mark up content in a description+attention wrap.
	 *
	 * @since 3.1.0
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param string $content Content to be wrapped in the wrap. Expected to be escaped.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public function attention_description_noesc( $content, $block = true ) {
		\tsf()->_deprecated_function( 'tsf()->attention_description_noesc()', '4.2.0' );
		return \The_SEO_Framework\Interpreters\HTML::attention_description_noesc( $content, $block );
	}

	/**
	 * Echo or return a chechbox fields wrapper.
	 *
	 * This method does NOT escape.
	 *
	 * @since 2.6.0
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param string $input The input to wrap. Should already be escaped.
	 * @param bool   $echo  Whether to escape echo or just return.
	 * @return string|void Wrapped $input.
	 */
	public function wrap_fields( $input = '', $echo = false ) {
		\tsf()->_deprecated_function( 'tsf()->wrap_fields()', '4.2.0' );
		return \The_SEO_Framework\Interpreters\HTML::wrap_fields( $input, $echo );
	}

	/**
	 * Return a wrapped question mark.
	 *
	 * @since 2.6.0
	 * @since 3.0.0 Links are now no longer followed, referred or bound to opener.
	 * @since 4.0.0 Now adds a tabindex to the span tag, so you can focus it using keyboard navigation.
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param string $description The descriptive on-hover title.
	 * @param string $link        The non-escaped link.
	 * @param bool   $echo        Whether to echo or return.
	 * @return string HTML checkbox output if $echo is false.
	 */
	public function make_info( $description = '', $link = '', $echo = true ) {
		\tsf()->_deprecated_function( 'tsf()->make_info()', '4.2.0', '\The_SEO_Framework\Interpreters\HTML::make_info()' );
		return \The_SEO_Framework\Interpreters\HTML::make_info( $description, $link, $echo );
	}

	/**
	 * Makes either simple or JSON-encoded data-* attributes for HTML elements.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 No longer adds an extra space in front of the return value when no data is generated.
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 4.2.0 Hard deprecation.
	 * @internal
	 * @deprecated
	 *
	 * @param array $data : {
	 *    string $k => mixed $v
	 * }
	 * @return string The HTML data attributes, with added space to the start.
	 */
	public function make_data_attributes( $data ) {
		\tsf()->_deprecated_function( 'tsf()->make_data_attributes()', '4.2.0' );
		return \The_SEO_Framework\Interpreters\HTML::make_data_attributes( $data );
	}

	/**
	 * Returns a chechbox wrapper.
	 *
	 * @since 2.6.0
	 * @since 2.7.0 Added escape parameter. Defaults to true.
	 * @since 3.0.3 Added $disabled parameter. Defaults to false.
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param string $field_id    The option ID. Must be within the Autodescription settings.
	 * @param string $label       The checkbox description label.
	 * @param string $description Addition description to place beneath the checkbox.
	 * @param bool   $escape      Whether to escape the label and description.
	 * @param bool   $disabled    Whether to disable the input.
	 * @return string HTML checkbox output.
	 */
	public function make_checkbox( $field_id = '', $label = '', $description = '', $escape = true, $disabled = false ) {
		\tsf()->_deprecated_function( 'tsf()->make_checkbox()', '4.2.0' );
		return \The_SEO_Framework\Interpreters\Settings_Input::make_checkbox( [
			'id'          => $field_id,
			'index'       => '',
			'label'       => $label,
			'description' => $description,
			'escape'      => $escape,
			'disabled'    => $disabled,
		] );
	}

	/**
	 * Returns a HTML select form elements for qubit options: -1, 0, or 1.
	 * Does not support "multiple" field selections.
	 *
	 * @since 4.0.0
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param array $args : {
	 *    string     $id       The select field ID.
	 *    string     $class    The div wrapper class.
	 *    string     $name     The option name.
	 *    int|string $default  The current option value.
	 *    array      $options  The select option values : { value => name }
	 *    string     $label    The option label.
	 *    string     $required Whether the field must be required.
	 *    array      $data     The select field data. Sub-items are expected to be escaped if they're not an array.
	 *    array      $info     Extra info field data.
	 * }
	 * @return string The option field.
	 */
	public function make_single_select_form( $args ) {
		\tsf()->_deprecated_function( 'tsf()->make_single_select_form()', '4.2.0', 'The_SEO_Framework\Interpreters\Form::make_single_select_form()' );
		return \The_SEO_Framework\Interpreters\Form::make_single_select_form( $args );
	}

	/**
	 * Returns the HTML class wrap for default Checkbox options.
	 *
	 * This function does nothing special. But is merely a simple wrapper.
	 * Just like code_wrap.
	 *
	 * @since 2.2.5
	 * @since 3.1.0 Deprecated second parameter.
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 4.2.0 1. Hard deprecation.
	 *              2. Now always returns an empty string.
	 * @deprecated
	 *
	 * @param string $key  The option name which returns boolean.
	 * @param string $depr Deprecated
	 * @param bool   $wrap Whether to wrap the class name in `class="%s"`
	 * @param bool   $echo Whether to echo or return the output.
	 * @return string Empty on echo or the class name with an optional wrapper.
	 */
	public function is_default_checked( $key, $depr = '', $wrap = true, $echo = true ) { // phpcs:ignore, VariableAnalysis.CodeAnalysis
		\tsf()->_deprecated_function( 'tsf()->is_default_checked()', '4.2.0' );
		return '';
	}

	/**
	 * Returns the HTML class wrap for warning Checkbox options.
	 *
	 * @since 2.3.4
	 * @since 3.1.0 Deprecated second parameter.
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 4.2.0 10 Hard deprecation.
	 *              2. Now always returns an empty string.
	 * @deprecated
	 *
	 * @param string $key  The option name which returns boolean.
	 * @param string $deprecated Deprecated.
	 * @param bool   $wrap Whether to wrap the class name in `class="%s"`
	 * @param bool   $echo Whether to echo or return the output.
	 * @return string Empty on echo or the class name with an optional wrapper.
	 */
	public function is_warning_checked( $key, $deprecated = '', $wrap = true, $echo = true ) { // phpcs:ignore, VariableAnalysis.CodeAnalysis
		\tsf()->_deprecated_function( 'tsf()->is_warning_checked()', '4.2.0' );
		return '';
	}

	/**
	 * Returns the HTML class wrap for warning/default Checkbox options.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Added the $wrap parameter.
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 4.2.0 1. Hard deprecation.
	 *              2. Now always returns false.
	 * @deprecated
	 *
	 * @param string $key  The option name which returns boolean.
	 * @param bool   $wrap Whether to wrap the class name in `class="%s"`
	 */
	public function get_is_conditional_checked( $key, $wrap = true ) { // phpcs:ignore, VariableAnalysis.CodeAnalysis
		\tsf()->_deprecated_function( 'tsf()->get_is_conditional_checked()', '4.2.0' );
		return false;
	}

	/**
	 * Returns the HTML class wrap for warning/default Checkbox options.
	 *
	 * @since 2.3.4
	 * @since 3.1.0 Deprecated second parameter.
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 4.2.0 1. Hard deprecation.
	 *              2. Now always returns false.
	 * @deprecated
	 *
	 * @param string $key        The option name which returns boolean.
	 * @param string $deprecated Deprecated. Used to be the settings field.
	 * @param bool   $wrap       Whether to wrap the class name in `class="%s"`
	 * @param bool   $echo       Whether to echo or return the output.
	 * @return string Empty on echo or the class name with an optional wrapper.
	 */
	public function is_conditional_checked( $key, $deprecated = '', $wrap = true, $echo = true ) { // phpcs:ignore, VariableAnalysis.CodeAnalysis
		\tsf()->_deprecated_function( 'tsf()->is_conditional_checked()', '4.2.0' );
		return false;
	}

	/**
	 * Outputs character counter wrap for both JavaScript and no-Javascript.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 1. Added an "what if you click" onhover-title.
	 *              2. Removed second parameter's usage. For passing the expected string.
	 *              3. The whole output is now hidden from no-js.
	 * @since 4.1.0 No longer marks up the counter with the `description` HTML class.
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param string $for     The input ID it's for.
	 * @param string $depr    The initial value for no-JS. Deprecated.
	 * @param bool   $display Whether to display the counter. (options page gimmick)
	 */
	public function output_character_counter_wrap( $for, $depr = '', $display = true ) {
		\tsf()->_deprecated_function( 'tsf()->output_character_counter_wrap()', '4.2.0' );
		return \The_SEO_Framework\Interpreters\Form::output_character_counter_wrap( $for, $display );
	}

	/**
	 * Outputs pixel counter wrap for javascript.
	 *
	 * @since 3.0.0
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param string $for  The input ID it's for.
	 * @param string $type Whether it's a 'title' or 'description' counter.
	 * @param bool   $display Whether to display the counter. (options page gimmick)
	 */
	public function output_pixel_counter_wrap( $for, $type, $display = true ) {
		\tsf()->_deprecated_function( 'tsf()->output_pixel_counter_wrap()', '4.2.0' );
		return \The_SEO_Framework\Interpreters\Form::output_pixel_counter_wrap( $for, $type, $display );
	}

	/**
	 * Determines if WP is above or below a version
	 *
	 * @since 2.2.1
	 * @since 2.3.8 Added caching
	 * @since 2.8.0 No longer overwrites global $wp_version
	 * @since 3.1.0 1. No longer caches.
	 *              2. Removed redundant parameter checks.
	 *              3. Now supports x.yy.zz WordPress versions.
	 * @since 4.2.0 Deprecated. Use your own method instead.
	 *
	 * @param string $version the three part version to compare to WordPress
	 * @param string $compare the comparing operator, default "$version >= Current WP Version"
	 * @return bool True if the WordPress version comparison passes.
	 */
	public function wp_version( $version = '4.3.0', $compare = '>=' ) {

		\tsf()->_deprecated_function( 'tsf()->wp_version()', '4.2.0' );

		$wp_version = $GLOBALS['wp_version'];

		/**
		 * Add a .0 if WP outputs something like 4.3 instead of 4.3.0
		 * Does consider 4.xx, which will become 4.xx.0.
		 * Does not consider 4.xx-dev, which will become 4.xx-dev.0. Oh well.
		 */
		if ( 1 === substr_count( $wp_version, '.' ) )
			$wp_version .= '.0';

		return (bool) version_compare( $wp_version, $version, $compare );
	}

	/**
	 * Checks for current theme support.
	 *
	 * Maintains detection cache, array and strings are mixed through foreach loops.
	 *
	 * @since 2.2.5
	 * @since 3.1.0 Removed caching
	 * @since 4.2.0 Deprecated. Use WP core `current_theme_supports()` instead.
	 *
	 * @param string|array required $features The features to check for.
	 * @return bool theme support.
	 */
	public function detect_theme_support( $features ) {

		\tsf()->_deprecated_function( 'tsf()->detect_theme_support()', '4.2.0', 'current_theme_supports()' );

		foreach ( (array) $features as $feature ) {
			if ( \current_theme_supports( $feature ) ) {
				return true;
			}
			continue;
		}

		return false;
	}

	/**
	 * Detects presence of a page builder.
	 * Memoizes the return value.
	 *
	 * Detects the following builders:
	 * - Elementor by Elementor LTD
	 * - Divi Builder by Elegant Themes
	 * - Visual Composer by WPBakery
	 * - Page Builder by SiteOrigin
	 * - Beaver Builder by Fastline Media
	 *
	 * @since 4.0.0
	 * @since 4.0.6 The output is now filterable.
	 * @since 4.2.0 Deprecated
	 * @ignore unused.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function detect_page_builder() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->detect_page_builder()', '4.2.0' );

		static $detected = null;

		if ( isset( $detected ) ) return $detected;

		/**
		 * @since 4.0.6
		 * @param bool $detected Whether an active page builder is detected.
		 * @NOTE not to be confused with `the_seo_framework_detect_page_builder`, which tests
		 *       the page builder status for each post individually.
		 */
		return $detected = (bool) \apply_filters(
			'the_seo_framework_page_builder_active',
			$tsf->detect_plugin( [
				'constants' => [
					'ELEMENTOR_VERSION',
					'ET_BUILDER_VERSION',
					'WPB_VC_VERSION',
					'SITEORIGIN_PANELS_VERSION',
					'FL_BUILDER_VERSION',
				],
			] )
		);
	}

	/**
	 * Determines whether the post has a page builder attached to it.
	 * Doesn't use plugin detection features as some builders might be incorporated within themes.
	 *
	 * Detects the following builders:
	 * - Elementor by Elementor LTD
	 * - Divi Builder by Elegant Themes
	 * - Visual Composer by WPBakery
	 * - Page Builder by SiteOrigin
	 * - Beaver Builder by Fastline Media
	 *
	 * @since 2.6.6
	 * @since 3.1.0 Added Elementor detection
	 * @since 4.0.0 Now detects page builders before looping over the meta.
	 * @since 4.2.0 Deprecated.
	 * @TODO -> We may use this data for they have FSE builders. We may want to interface with those, some day.
	 *    -> We'd want to return the TYPE of pagebuilder used, if anything. Just deprecate this.
	 * @ignore unused.
	 * @deprecated
	 *
	 * @param int $post_id The post ID to check.
	 * @return bool
	 */
	public function uses_page_builder( $post_id ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->uses_page_builder()', '4.2.0' );

		$meta = \get_post_meta( $post_id );

		/**
		 * @since 2.6.6
		 * @since 3.1.0 1. Now defaults to `null`
		 *              2. Now, when a boolean (either true or false) is defined, it'll short-circuit this function.
		 * @param boolean|null $detected Whether a builder should be detected.
		 * @param int          $post_id The current Post ID.
		 * @param array        $meta The current post meta.
		 */
		$detected = \apply_filters( 'the_seo_framework_detect_page_builder', null, $post_id, $meta );

		if ( \is_bool( $detected ) )
			return $detected;

		if ( ! $tsf->detect_page_builder() )
			return false;

		if ( empty( $meta ) )
			return false;

		if ( isset( $meta['_elementor_edit_mode'][0] ) && '' !== $meta['_elementor_edit_mode'][0] && \defined( 'ELEMENTOR_VERSION' ) ) :
			// Elementor by Elementor LTD
			return true;
		elseif ( isset( $meta['_et_pb_use_builder'][0] ) && 'on' === $meta['_et_pb_use_builder'][0] && \defined( 'ET_BUILDER_VERSION' ) ) :
			// Divi Builder by Elegant Themes
			return true;
		elseif ( isset( $meta['_wpb_vc_js_status'][0] ) && 'true' === $meta['_wpb_vc_js_status'][0] && \defined( 'WPB_VC_VERSION' ) ) :
			// Visual Composer by WPBakery
			return true;
		elseif ( isset( $meta['panels_data'][0] ) && '' !== $meta['panels_data'][0] && \defined( 'SITEORIGIN_PANELS_VERSION' ) ) :
			// Page Builder by SiteOrigin
			return true;
		elseif ( isset( $meta['_fl_builder_enabled'][0] ) && '1' === $meta['_fl_builder_enabled'][0] && \defined( 'FL_BUILDER_VERSION' ) ) :
			// Beaver Builder by Fastline Media...
			return true;
		endif;

		return false;
	}

	/**
	 * Returns Facebook locales array values.
	 *
	 * @since 2.5.2
	 * @TODO deprecate me.
	 *
	 * @see https://www.facebook.com/translations/FacebookLocales.xml (deprecated)
	 * @see https://wordpress.org/support/topic/oglocale-problem/#post-11456346
	 * mirror: http://web.archive.org/web/20190601043836/https://wordpress.org/support/topic/oglocale-problem/
	 * @see $this->language_keys() for the associative array keys.
	 *
	 * @return array Valid Facebook locales
	 */
	public function fb_locales() {
		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->fb_locales()', '4.2.0', 'tsf()->supported_social_locales()' );
		return \array_keys( $tsf->supported_social_locales() );
	}

	/**
	 * Returns Facebook locales' associative array keys.
	 *
	 * This is apart from the fb_locales array since there are "duplicated" keys.
	 * Use this to compare the numeric key position.
	 *
	 * @since 2.5.2
	 * @TODO deprecate me.
	 * @see https://www.facebook.com/translations/FacebookLocales.xml (deprecated)
	 * @see https://wordpress.org/support/topic/oglocale-problem/#post-11456346
	 * mirror: http://web.archive.org/web/20190601043836/https://wordpress.org/support/topic/oglocale-problem/
	 *
	 * @return array Valid Facebook locale keys
	 */
	public function language_keys() {
		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->language_keys()', '4.2.0', 'tsf()->supported_social_locales()' );
		return \array_values( $tsf->supported_social_locales() );
	}

	/**
	 * Returns the PHP timezone compatible string.
	 * UTC offsets are unreliable.
	 *
	 * @since 2.6.0
	 * @since 4.2.0 Deprecated.
	 * @deprecated
	 *
	 * @param bool $guess If true, the timezone will be guessed from the
	 *                    WordPress core gmt_offset option.
	 * @return string PHP Timezone String. May be empty (thus invalid).
	 */
	public function get_timezone_string( $guess = false ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_timezone_string()', '4.2.0' );

		$tzstring = \get_option( 'timezone_string' );

		if ( false !== strpos( $tzstring, 'Etc/GMT' ) )
			$tzstring = '';

		if ( $guess && empty( $tzstring ) ) {
			$tzstring = timezone_name_from_abbr( '', round( \get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ), 1 );
		}

		return $tzstring;
	}

	/**
	 * Sets and resets the timezone.
	 *
	 * NOTE: Always call reset_timezone() ASAP. Don't let changes linger, as they can be destructive.
	 *
	 * This exists because WordPress's current_time() adds discrepancies between UTC and GMT.
	 * This is also far more accurate than WordPress's tiny time table.
	 *
	 * @TODO Note that WordPress 5.3 no longer requires this, and that we should rely on wp_date() instead.
	 *       So, we should remove this dependency ASAP.
	 *
	 * @since 2.6.0
	 * @since 3.0.6 Now uses the old timezone string when a new one can't be generated.
	 * @since 4.0.4 Now also unsets the stored timezone string on reset.
	 * @since 4.2.0 Deprecated.
	 * @link http://php.net/manual/en/timezones.php
	 * @deprecated
	 *
	 * @param string $tzstring Optional. The PHP Timezone string. Best to leave empty to always get a correct one.
	 * @param bool   $reset Whether to reset to default. Ignoring first parameter.
	 * @return bool True on success. False on failure.
	 */
	public function set_timezone( $tzstring = '', $reset = false ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->set_timezone()', '4.2.0' );

		static $old_tz = null;

		$old_tz = $old_tz ?: date_default_timezone_get() ?: 'UTC';

		if ( $reset ) {
			$_revert_tz = $old_tz;
			$old_tz     = null;
			// phpcs:ignore, WordPress.DateTime.RestrictedFunctions.timezone_change_date_default_timezone_set
			return date_default_timezone_set( $_revert_tz );
		}

		if ( empty( $tzstring ) )
			$tzstring = $tsf->get_timezone_string( true ) ?: $old_tz;

		// phpcs:ignore, WordPress.DateTime.RestrictedFunctions.timezone_change_date_default_timezone_set
		return date_default_timezone_set( $tzstring );
	}

	/**
	 * Resets the timezone to default or UTC.
	 *
	 * @since 2.6.0
	 * @since 4.2.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool True on success. False on failure.
	 */
	public function reset_timezone() {
		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->reset_timezone()', '4.2.0' );
		return $tsf->set_timezone( '', true );
	}

	/**
	 * Returns and caches term meta for the current query.
	 * Memoizes the return value for the current request.
	 *
	 * @since 3.0.0
	 * @since 4.0.1 Now uses the filterable `get_the_real_ID()`
	 * @since 4.2.0 Deprecated. Use get_term_meta() or get_term_meta_item() instead.
	 * @deprecated
	 *
	 * @return array The current term meta.
	 */
	public function get_current_term_meta() {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_current_term_meta()', '4.2.0', 'tsf()->get_term_meta()' );

		static $cache;

		if ( isset( $cache ) )
			return $cache;

		if ( $tsf->is_term_meta_capable() ) {
			$cache = $tsf->get_term_meta( $tsf->get_the_real_ID() ) ?: [];
		} else {
			$cache = [];
		}

		return $cache;
	}

	/**
	 * Detect the non-home blog page by query (ID).
	 *
	 * @since 2.3.4
	 * @since 4.2.0 Deprecated. Use is_home_as_page() instead.
	 * @deprecated
	 * @see is_wc_shop() -- that's the correct implementation. However, we're dealing with erratic queries here (ET & legacy WP)
	 *
	 * @param int $id the Page ID.
	 * @return bool true if is blog page. Always false if blog page is homepage.
	 */
	public function is_blog_page( $id = 0 ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->is_blog_page()', '4.2.0', 'tsf()->is_home_as_page()' );

		// When the blog page is the front page, treat it as front instead of blog.
		if ( ! $tsf->has_page_on_front() )
			return false;

		$id = $id ?: $tsf->get_the_real_ID();

		static $pfp;

		$pfp = $pfp ?? (int) \get_option( 'page_for_posts' );

		return ( $id && $id === $pfp && false === \is_archive() ) || \is_home();
	}

	/**
	 * Checks blog page by sole ID.
	 *
	 * @since 4.0.0
	 * @since 4.1.4 1. Improved performance by switching the conditional.
	 *              2. Improved performance by adding memoization.
	 * @since 4.2.0 1. Removed memoization.
	 *              2. Deprecated. Use is_home() instead.
	 * @deprecated
	 * @see is_wc_shop() -- that's the correct implementation.
	 *
	 * @param int $id The ID to check
	 * @return bool
	 */
	public function is_blog_page_by_id( $id ) {
		\tsf()->_deprecated_function( 'tsf()->is_blog_page()', '4.2.0', 'tsf()->is_home()' );

		// ID 0 cannot be a blog page.
		if ( ! $id ) return false;

		return (int) \get_option( 'page_for_posts' ) === $id;
	}

	/**
	 * Checks for front page by input ID.
	 *
	 * NOTE: Doesn't always return true when the ID is 0, although the homepage might be.
	 *       This is because it checks for the query, to prevent conflicts.
	 *
	 * @see $this->is_real_front_page_by_id(); Alternative to NOTE.
	 *
	 * @since 2.9.0
	 * @since 2.9.3 Now tests for archive and 404 before testing homepage as blog.
	 * @since 3.2.2 Removed SEO settings page check. This now returns false on that page.
	 * @since 4.2.0 1. No longer casts input $id to integer.
	 *              2. Deprecated.
	 * @deprecated
	 *
	 * @param int $id The page ID, required. Can be 0.
	 * @return bool True if ID if for the homepage.
	 */
	public function is_front_page_by_id( $id ) {
		\tsf()->_deprecated_function( 'tsf()->is_front_page_by_id()', '4.2.0', 'tsf()->is_real_front_page_by_id()' );

		$pof = (int) \get_option( 'page_on_front' );

		switch ( \get_option( 'show_on_front' ) ) :
			case 'page':
				$is_front_page = $pof === $id;
				break;

			case 'posts':
				$is_front_page =
					( 0 === $pof && $this->is_home() )
					|| $pof === $id;
				break;

			default:
				// Elegant Themes's Extra support
				$is_front_page = 0 === $id && $this->is_home();
				break;
		endswitch;

		return $is_front_page;
	}

	/**
	 * Prepends the taxonomy label to the title.
	 *
	 * @since 4.1.0
	 * @since 4.1.2 Now supports WP 5.5 archive titles.
	 * @since 4.2.0 Deprecated
	 * @deprecated
	 *
	 * @param string $title    The title to prepend taxonomy label to.
	 * @param string $taxonomy The taxonomy to get label from.
	 * @return string The title with possibly prepended tax-label.
	 */
	public function prepend_tax_label_prefix( $title, $taxonomy ) {

		$tsf = \tsf();
		\tsf()->_deprecated_function( 'tsf()->prepend_tax_label_prefix()', '4.2.0' );

		$prefix = $tsf->get_tax_type_label( $taxonomy ) ?: '';

		if ( $prefix ) {
			$title = sprintf(
				/* translators: 1: Title prefix. 2: Title. */
				\_x( '%1$s %2$s', 'archive title', 'default' ),
				/* translators: %s: Taxonomy singular name. */
				sprintf( \_x( '%s:', 'taxonomy term archive title prefix', 'default' ), $prefix ),
				$title
			);
		}

		return $title;
	}

	/**
	 * Get the real ID from plugins.
	 *
	 * Only works on front-end as there's no need to check for inconsistent
	 * functions for the current ID in the admin.
	 *
	 * @since 2.5.0
	 * @since 3.1.0 1. Now checks for the feed.
	 *              2. No longer caches.
	 * @since 4.0.5 1. The shop ID is now handled via the filter.
	 *              2. The question ID (AnsPress) is no longer called. This should work out-of-the-box since AnsPress 4.1.
	 * @since 4.2.0 Deprecated
	 * @deprecated
	 *
	 * @return int The admin ID.
	 */
	public function check_the_real_ID() { // phpcs:ignore -- ID is capitalized because WordPress does that too: get_the_ID().

		\tsf()->_deprecated_function( 'tsf()->check_the_real_ID()', '4.2.0', 'tsf()->get_the_real_ID()' );

		/**
		 * @since 2.5.0
		 * @param int $id
		 */
		return (int) \apply_filters(
			'the_seo_framework_real_id',
			$this->is_feed() ? \get_the_ID() : 0
		);
	}

	/**
	 * Get the default of any of the The SEO Framework settings.
	 *
	 * @since 2.2.4
	 * @since 2.8.2 No longer decodes entities on request.
	 * @since 3.1.0 1. Now returns null if the option doesn't exist, instead of -1.
	 *              2. Is now influenced by filters.
	 *              3. Now also strips slashes when using cache.
	 *              4. The second parameter is deprecated.
	 * @since 4.2.0 Deprecated
	 * @deprecated
	 * @uses $this->get_default_site_options()
	 *
	 * @param string $key       Required. The option name.
	 * @param string $depr      Deprecated. Leave empty.
	 * @param bool   $use_cache Optional. Whether to use the options cache or bypass it.
	 * @return mixed default option
	 *         null If option doesn't exist.
	 */
	public function get_default_settings( $key, $depr = '', $use_cache = true ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_default_settings()', '4.2.0', 'tsf()->get_default_option()' );

		if ( ! $key ) return false;

		if ( $depr )
			$tsf->_doing_it_wrong( __METHOD__, 'The second parameter is deprecated.', '3.1.0' );

		if ( ! $use_cache ) {
			$defaults = $tsf->get_default_site_options();
			return isset( $defaults[ $key ] ) ? \stripslashes_deep( $defaults[ $key ] ) : null;
		}

		static $cache;

		return (
			$cache = $cache ?? \stripslashes_deep( $tsf->get_default_site_options() )
		)[ $key ] ?? null;
	}

	/**
	 * Get the warned setting of any of the The SEO Framework settings.
	 *
	 * @since 2.3.4
	 * @since 3.1.0 Now returns 0 if the option doesn't exist, instead of -1.
	 * @since 4.2.0 Deprecated
	 * @deprecated
	 * @uses THE_SEO_FRAMEWORK_SITE_OPTIONS
	 * @uses $this->get_warned_site_options()
	 *
	 * @param string $key       Required. The option name.
	 * @param string $depr      Deprecated. Leave empty.
	 * @param bool   $use_cache Optional. Whether to use the options cache or bypass it.
	 * @return int 0|1 Whether the option is flagged as dangerous for SEO.
	 */
	public function get_warned_settings( $key, $depr = '', $use_cache = true ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_warned_settings()', '4.2.0', 'tsf()->get_warned_option()' );

		if ( empty( $key ) )
			return false;

		if ( $depr )
			$tsf->_doing_it_wrong( __METHOD__, 'The second parameter is deprecated.', '3.1.0' );

		if ( ! $use_cache )
			return $tsf->s_one_zero( ! empty( $tsf->get_warned_site_options()[ $key ] ) );

		static $cache;

		if ( ! isset( $cache ) )
			$cache = $tsf->get_warned_site_options();

		return $tsf->s_one_zero( ! empty( $cache[ $key ] ) );
	}

	/**
	 * Returns image URL suitable for Schema items.
	 *
	 * These are images that are strictly assigned to the Post or Page, fallbacks are omitted.
	 * Themes should compliment these. If not, then Open Graph should at least compliment these.
	 * If that's not even true, then I don't know what happens. But then you're in a grey area...
	 *
	 * @since 4.0.0
	 * @since 4.2.0 1. Now gets correctly separated results when $args changes.
	 *              2. Now supports the `$args['pta']` index.
	 *              3. Deprecated.
	 * @uses $this->get_image_details()
	 * @deprecated
	 *
	 * @param array|null $args    The query arguments. Accepts 'id', 'taxonomy', and 'pta'.
	 *                            Leave null to autodetermine query.
	 * @param bool       $details Whether to return all details, or just a simple URL.
	 * @return string|array $url The Schema.org safe image.
	 */
	public function get_safe_schema_image( $args = null, $details = false ) {

		$tsf = \tsf();
		$tsf->_deprecated_function( 'tsf()->get_safe_schema_image()', '4.2.0', 'tsf()->get_image_details()' );

		$image_details = memo( null, $args ) ?? memo( current( $tsf->get_image_details( $args, true, 'schema' ), $args ) );

		return $details ? $image_details : ( $image_details['url'] ?? '' );
	}
}
