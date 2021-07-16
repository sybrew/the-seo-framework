<?php
/**
 * @package The_SEO_Framework\Classes\Deprecated
 * @subpackage The_SEO_Framework\Debug\Deprecated
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

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

/**
 * Class The_SEO_Framework\Deprecated
 *
 * Contains all deprecated functions.
 *
 * @since 2.8.0
 * @since 3.1.0 Removed all methods deprecated in 3.0.0.
 * @since 4.0.0 Removed all methods deprecated in 3.1.0.
 * @since 4.1.4 Removed all methods deprecated in 4.0.0.
 * @ignore
 */
final class Deprecated {

	/**
	 * Detect if the current screen type is a page or taxonomy.
	 * Memoizes the return value.
	 *
	 * @since 2.3.1
	 * @since 4.1.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $type the Screen type
	 * @return bool true if post type is a page or post
	 */
	public function is_post_type_page( $type ) {

		static $is_page = [];

		if ( isset( $is_page[ $type ] ) )
			return $is_page[ $type ];

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->is_post_type_page()', '4.1.0' );

		$post_page = (array) \get_post_types( [ 'public' => true ] );

		foreach ( $post_page as $screen ) {
			if ( $type === $screen ) {
				return $is_page[ $type ] = true;
			}
		}

		return $is_page[ $type ] = false;
	}

	/**
	 * Checks whether the taxonomy is public and rewritable.
	 *
	 * @since 3.1.0
	 * @since 4.1.0 1: Now returns true on all public taxonomies; not just public taxonomies with rewrite capabilities.
	 *              2: Deprecated.
	 * @deprecated
	 *
	 * @param string $taxonomy The taxonomy name.
	 * @return bool
	 */
	public function is_taxonomy_public( $taxonomy = '' ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->is_taxonomy_public()', '4.1.0', 'the_seo_framework()->is_taxonomy_supported()' );

		$taxonomy = $taxonomy ?: $tsf->get_current_taxonomy();
		if ( ! $taxonomy ) return false;

		$tax = \get_taxonomy( $taxonomy );

		if ( false === $tax ) return false;

		return ! empty( $tax->public );
	}

	/**
	 * Return option from the options table and cache result.
	 * Memoizes the return value.
	 *
	 * Values pulled from the database are cached on each request, so a second request for the same value won't cause a
	 * second DB interaction.
	 *
	 * @since 2.0.0
	 * @since 2.8.2 No longer decodes entities on request.
	 * @since 3.1.0 Now uses the filterable call when caching is disabled.
	 * @since 4.1.0 Deprecated.
	 * @thanks StudioPress (http://www.studiopress.com/) for some code.
	 * @deprecated
	 *
	 * @param string  $key        Option name.
	 * @param string  $setting    Optional. Settings field name. Eventually defaults to null if not passed as an argument.
	 * @param boolean $use_cache  Optional. Whether to use the cache value or not.
	 * @return mixed The value of this $key in the database. Empty string on failure.
	 */
	public function the_seo_framework_get_option( $key, $setting = null, $use_cache = true ) {

		if ( ! $setting ) return '';

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->the_seo_framework_get_option()', '4.1.0', 'the_seo_framework()->get_option()' );

		if ( ! $use_cache ) {
			$options = $tsf->get_all_options( $setting, true );
			return isset( $options[ $key ] ) ? \stripslashes_deep( $options[ $key ] ) : '';
		}

		static $cache = [];

		if ( ! isset( $cache[ $setting ] ) )
			$cache[ $setting ] = \stripslashes_deep( $tsf->get_all_options( $setting ) );

		return isset( $cache[ $setting ][ $key ] ) ? $cache[ $setting ][ $key ] : '';
	}

	/**
	 * Returns the homepage tagline from option or bloginfo, when set.
	 *
	 * @since 3.0.4
	 * @since 4.0.0 Added caching.
	 * @since 4.1.0 Deprecated.
	 * @uses $this->get_blogdescription(), this method already trims.
	 * @deprecated
	 *
	 * @return string The trimmed tagline.
	 */
	public function get_home_page_tagline() {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->get_home_page_tagline()', '4.1.0', 'the_seo_framework()->get_home_title_additions()' );

		return $tsf->get_home_title_additions();
	}

	/**
	 * Cached WordPress permalink structure settings.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Removed caching.
	 * @since 4.1.0 Deprecated.
	 * @deprecated
	 *
	 * @return string permalink structure.
	 */
	public function permalink_structure() {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->permalink_structure()', '4.1.0', "get_option( 'permalink_structure' )" );

		return \get_option( 'permalink_structure' );
	}

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
		$tsf = \the_seo_framework();
		// $tsf->_deprecated_function( 'the_seo_framework()->append_php_query()', '4.2.0', 'the_seo_framework()->append_url_query()' );
		return $tsf->append_url_query( $url, $query );
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

		$tsf = \the_seo_framework();

		// $tsf->_deprecated_function( 'the_seo_framework()->get_html_output()', '4.2.0' );

		$robots = $tsf->robots();

		/** @since 4.0.4 Added as WP 5.3 patch. */
		$tsf->set_timezone( 'UTC' );

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

		/** @since 4.0.4 Added as WP 5.3 patch. */
		$tsf->reset_timezone();

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
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param array|null $args   The query arguments. Accepts 'id' and 'taxonomy'.
	 * @param int <bit>  $ignore The ignore level. {
	 *    0 = 0b00: Ignore nothing.
	 *    1 = 0b01: Ignore protection. (\The_SEO_Framework\ROBOTS_IGNORE_PROTECTION)
	 *    2 = 0b10: Ignore post/term setting. (\The_SEO_Framework\ROBOTS_IGNORE_SETTINGS)
	 *    3 = 0b11: Ignore protection and post/term setting.
	 * }
	 * @return bool Whether noindex is set or not
	 */
	public function is_robots_meta_noindex_set_by_args( $args, $ignore = 0b00 ) {
		$tsf = \the_seo_framework();
		// $tsf->_deprecated_function( 'the_seo_framework()->is_robots_meta_noindex_set_by_args()', '4.2.0', 'the_seo_framework()->robots_meta()' );
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
	 * @since 3.0.0 : 1. Removed noodp.
	 *                2. Improved efficiency by grouping if statements.
	 * @since 3.1.0 : 1. Simplified statements, often (not always) speeding things up.
	 *                2. Now checks for wc_shop and blog types for pagination.
	 *                3. Removed noydir.
	 * @since 4.0.0 : 1. Now tests for qubit metadata.
	 *                2. Added custom query support.
	 *                3. Added two parameters.
	 * @since 4.0.2 : 1. Added new copyright directive tags.
	 *                2. Now strictly parses the validity of robots directives via a boolean check.
	 * @since 4.0.3 : 1. Changed `max_snippet_length` to `max_snippet`
	 *                2. Changed the copyright directive's spacer from `=` to `:`.
	 * @since 4.0.5 : 1. Removed copyright directive bug workaround. <https://kb.theseoframework.com/kb/why-is-max-image-preview-none-purged/>
	 *                2. Now sets noindex and nofollow when queries are exploited (requires option enabled).
	 * @since 4.1.4 Deprecated silently. Use generate_robots_meta() instead.
	 * @since 4.2.0 Hard deprecation.
	 *
	 * @param array|null $args   The query arguments. Accepts 'id' and 'taxonomy'.
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
		$tsf = \the_seo_framework();
		// $tsf->_deprecated_function( 'the_seo_framework()->robots_meta()', '5.0.0', 'the_seo_framework()->generate_robots_meta()' );
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
	 * @since 4.0.0 : 1. Now uses has_robots_txt()
	 *              : 2. Now uses the get_robots_txt_url() to determine validity.
	 * FIXME This method also checks for file existence (and location...), but is only used when the file definitely doesn't exist.
	 * @since 4.1.4 Soft deprecated.
	 * @since 4.2.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param bool $check_option Whether to check for sitemap option.
	 * @return bool True when no conflicting plugins are detected or when The SEO Framework's Sitemaps are output.
	 */
	public function can_do_sitemap_robots( $check_option = true ) {

		$tsf = \the_seo_framework();

		// $tsf->_deprecated_function( 'the_seo_framework()->is_robots_meta_noindex_set_by_args()', '4.2.0' );

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
		// \the_seo_framework()->_deprecated_function( 'the_seo_framework()->nav_tab_wrapper()', '4.2.0', '\The_SEO_Framework\Bridges\PostSettings::_nav_tab_wrapper' );
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
		// \the_seo_framework()->_deprecated_function( 'the_seo_framework()->inpost_flex_nav_tab_wrapper()', '4.2.0', '\The_SEO_Framework\Bridges\PostSettings::_flex_nav_tab_wrapper' );
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
		// \the_seo_framework()->_deprecated_function( 'the_seo_framework()->get_social_image_uploader_form()', '4.2.0', 'The_SEO_Framework\Interpreters\Form::get_image_uploader_form()' );
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
		// \the_seo_framework()->_deprecated_function( 'the_seo_framework()->get_logo_uploader_form()', '4.2.0', 'The_SEO_Framework\Interpreters\Form::get_image_uploader_form()' );
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
	 * @TODO delete me, bypass deprecation? This method makes no sense to the outsider, anyway. -> 4.2.0
	 *
	 * @param int $i  The dimension to resize.
	 * @param int $r1 The dimension that determines the ratio.
	 * @param int $r2 The dimension to proportionate to.
	 * @return int The proportional dimension, rounded.
	 */
	public function proportionate_dimensions( $i, $r1, $r2 ) {
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
		$tsf = \the_seo_framework();
		// $tsf->_deprecated_function( 'the_seo_framework()->seo_settings_page_url()', '4.2.0', 'the_seo_framework()->get_seo_settings_page_url()' );
		return $tsf->get_seo_settings_page_url();
	}

	/**
	 * Returns default user meta.
	 *
	 * @since 3.0.0
	 * @since 4.1.4 Deprecated silently. Use `get_user_meta_defaults()` instead.
	 * @since 4.2.0 Hard deprecation.
	 *
	 * @return array The default user meta index and values.
	 */
	public function get_default_user_data() {
		$tsf = \the_seo_framework();
		// $tsf->_deprecated_function( 'the_seo_framework()->get_default_user_data()', '4.2.0', 'the_seo_framework()->get_user_meta_defaults()' );
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
	 *
	 * @param int    $user_id The user ID. When empty, it will try to fetch the current user.
	 * @param string $option  The option name.
	 * @param mixed  $default The default value to return when the data doesn't exist.
	 * @return mixed The metadata value.
	 */
	public function get_user_option( $user_id = 0, $option = '', $default = null ) {
		$tsf = \the_seo_framework();
		// $tsf->_deprecated_function( 'the_seo_framework()->get_user_option()', '4.2.0', 'the_seo_framework()->get_user_meta_item()' );
		return $tsf->get_user_meta_item( $user_id ?: $tsf->get_user_id(), $option ) ?: $default;
	}

	/**
	 * Returns current post author option.
	 *
	 * @since 3.0.0
	 * @since 4.1.4 Silently deprecated. use `get_current_post_author_id()` instead.
	 * @since 4.2.0 Hard deprecation.
	 *
	 * @param int    $author_id The author ID. When empty, it will return $default.
	 * @param string $option    The option name. When empty, it will return $default.
	 * @param mixed  $default   The default value to return when the data doesn't exist.
	 * @return mixed The metadata value
	 */
	public function get_author_option( $author_id, $option, $default = null ) {
		$tsf = \the_seo_framework();
		// $tsf->_deprecated_function( 'the_seo_framework()->get_author_option()', '4.2.0', 'the_seo_framework()->get_current_post_author_id()' );
		return $tsf->get_user_meta_item( $option, $author_id ?: $tsf->get_current_post_author_id() ) ?: $default;
	}

	/**
	 * Returns current post author option.
	 *
	 * @since 3.0.0
	 * @since 4.1.4 Silently deprecated. Use `get_current_post_author_meta_item()` instead.
	 * @since 4.2.0 Hard deprecation.
	 *
	 * @param string $option  The option name.
	 * @param mixed  $default The default value to return when the data doesn't exist.
	 * @return mixed The metadata value
	 */
	public function get_current_author_option( $option, $default = null ) {
		$tsf = \the_seo_framework();
		// $tsf->_deprecated_function( 'the_seo_framework()->get_current_author_option()', '4.2.0', 'the_seo_framework()->get_current_post_author_meta_item()' );
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

		// \the_seo_framework()->_deprecated_function( 'the_seo_framework()->is_wc_shop()', '5.0.0', 'the_seo_framework()->is_shop()' );

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
	 * @since 4.0.0 : 1. Added admin support.
	 *                2. Added parameter for the Post ID or post to test.
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

		$tsf = \the_seo_framework();
		// $tsf->_deprecated_function( 'the_seo_framework()->is_wc_product()', '5.0.0', 'the_seo_framework()->is_product()' );

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
		$tsf = \the_seo_framework();
		// $tsf->_deprecated_function( 'the_seo_framework()->is_wc_product_admin()', '5.0.0', 'the_seo_framework()->is_product_admin()' );
		// Checks for "is_singular_admin()" because the post type is non-hierarchical.
		return $tsf->is_singular_admin() && 'product' === $tsf->get_admin_post_type();
	}

	/**
	 * Updates user SEO option.
	 *
	 * @since 2.7.0
	 * @since 2.8.0 New users now get a new array assigned.
	 * @since 4.1.4 Deprecated silently. Use `update_single_user_meta_item()` instead.
	 *
	 * @param int    $user_id The user ID.
	 * @param string $option  The user's SEO metadata option.
	 * @param mixed  $value   The escaped option value.
	 * @return bool True on success. False on failure.
	 */
	public function update_user_option( $user_id = 0, $option = '', $value = '' ) {

		$tsf = \the_seo_framework();
		// $tsf->_deprecated_function( 'the_seo_framework()->update_user_option()', '5.0.0', 'the_seo_framework()->update_single_user_meta_item()' );

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
	 * @since 5.0.0 Hard deprecation.
	 * @deprecated
	 *
	 * @param string $name Field name base
	 * @return string Full field name
	 */
	public function get_field_name( $name ) {
		// \the_seo_framework()->_deprecated_function( 'the_seo_framework()->get_field_name()', '5.0.0' );
		return \The_SEO_Framework\Interpreters\Form::get_field_name( $name );
	}

	/**
	 * Echo constructed name attributes in form fields.
	 *
	 * @since 2.2.2
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 5.0.0 Hard deprecation.
	 * @uses $this->get_field_name() Construct name attributes for use in form fields.
	 *
	 * @param string $name Field name base
	 */
	public function field_name( $name ) {
		// $tsf->_deprecated_function( 'the_seo_framework()->field_name()', '5.0.0' );
		return \The_SEO_Framework\Interpreters\Form::field_name( $name );
	}

	/**
	 * Helper function that constructs id attributes for use in form fields.
	 *
	 * @since 2.2.2
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 5.0.0 Hard deprecation.
	 *
	 * @param string $id Field id base
	 * @return string Full field id
	 */
	public function get_field_id( $id ) {
		// \the_seo_framework()->_deprecated_function( 'the_seo_framework()->get_field_id()', '5.0.0' );
		return \The_SEO_Framework\Interpreters\Form::get_field_id( $id );
	}

	/**
	 * Echo constructed id attributes in form fields.
	 *
	 * @since 2.2.2
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 5.0.0 Hard deprecation.
	 * @uses $this->get_field_id() Constructs id attributes for use in form fields.
	 *
	 * @param string  $id Field id base.
	 * @param boolean $echo Whether to escape echo or just return.
	 * @return string Full field id
	 */
	public function field_id( $id, $echo = true ) {
		// \the_seo_framework()->_deprecated_function( 'the_seo_framework()->field_id()', '5.0.0' );
		return \The_SEO_Framework\Interpreters\Form::field_id( $id, $echo );
	}

	/**
	 * Mark up content with code tags.
	 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
	 *
	 * @since 2.0.0
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 5.0.0 Hard deprecation.
	 *
	 * @param string $content Content to be wrapped in code tags.
	 * @return string Content wrapped in code tags.
	 */
	public function code_wrap( $content ) {
		// \the_seo_framework()->_deprecated_function( 'the_seo_framework()->code_wrap()', '5.0.0' );
		return \The_SEO_Framework\Interpreters\HTML::code_wrap( $content );
	}

	/**
	 * Mark up content with code tags.
	 * Escapes no HTML.
	 *
	 * @since 2.2.2
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 5.0.0 Hard deprecation.
	 *
	 * @param string $content Content to be wrapped in code tags.
	 * @return string Content wrapped in code tags.
	 */
	public function code_wrap_noesc( $content ) {
		// \the_seo_framework()->_deprecated_function( 'the_seo_framework()->code_wrap_noesc()', '5.0.0' );
		return \The_SEO_Framework\Interpreters\HTML::code_wrap_noesc( $content );
	}

	/**
	 * Mark up content in description wrap.
	 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
	 *
	 * @since 2.7.0
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 5.0.0 Hard deprecation.
	 *
	 * @param string $content Content to be wrapped in the description wrap.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public function description( $content, $block = true ) {
		// \the_seo_framework()->_deprecated_function( 'the_seo_framework()->description()', '5.0.0' );
		return \The_SEO_Framework\Interpreters\HTML::description( $content, $block );
	}

	/**
	 * Mark up content in description wrap.
	 *
	 * @since 2.7.0
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 5.0.0 Hard deprecation.
	 *
	 * @param string $content Content to be wrapped in the description wrap. Expected to be escaped.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public function description_noesc( $content, $block = true ) {
		// \the_seo_framework()->_deprecated_function( 'the_seo_framework()->description_noesc()', '5.0.0' );
		return \The_SEO_Framework\Interpreters\HTML::description_noesc( $content, $block );
	}

	/**
	 * Mark up content in attention wrap.
	 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
	 *
	 * @since 3.1.0
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 5.0.0 Hard deprecation.
	 *
	 * @param string $content Content to be wrapped in the attention wrap.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public function attention( $content, $block = true ) {
		// \the_seo_framework()->_deprecated_function( 'the_seo_framework()->attention()', '5.0.0' );
		return \The_SEO_Framework\Interpreters\HTML::attention( $content, $block );
	}

	/**
	 * Mark up content in attention wrap.
	 *
	 * @since 3.1.0
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 5.0.0 Hard deprecation.
	 *
	 * @param string $content Content to be wrapped in the attention wrap. Expected to be escaped.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public function attention_noesc( $content, $block = true ) {
		// \the_seo_framework()->_deprecated_function( 'the_seo_framework()->attention_noesc()', '5.0.0' );
		return \The_SEO_Framework\Interpreters\HTML::attention_noesc( $content, $block );
	}

	/**
	 * Mark up content in a description+attention wrap.
	 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
	 *
	 * @since 3.1.0
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 5.0.0 Hard deprecation.
	 *
	 * @param string $content Content to be wrapped in the wrap. Expected to be escaped.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public function attention_description( $content, $block = true ) {
		// \the_seo_framework()->_deprecated_function( 'the_seo_framework()->attention_description()', '5.0.0' );
		return \The_SEO_Framework\Interpreters\HTML::attention_description( $content, $block );
	}

	/**
	 * Mark up content in a description+attention wrap.
	 *
	 * @since 3.1.0
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 5.0.0 Hard deprecation.
	 *
	 * @param string $content Content to be wrapped in the wrap. Expected to be escaped.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public function attention_description_noesc( $content, $block = true ) {
		// \the_seo_framework()->_deprecated_function( 'the_seo_framework()->attention_description_noesc()', '5.0.0' );
		return \The_SEO_Framework\Interpreters\HTML::attention_description_noesc( $content, $block );
	}

	/**
	 * Echo or return a chechbox fields wrapper.
	 *
	 * This method does NOT escape.
	 *
	 * @since 2.6.0
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 5.0.0 Hard deprecation.
	 *
	 * @param string $input The input to wrap. Should already be escaped.
	 * @param bool   $echo  Whether to escape echo or just return.
	 * @return string|void Wrapped $input.
	 */
	public function wrap_fields( $input = '', $echo = false ) {
		// \the_seo_framework()->_deprecated_function( 'the_seo_framework()->wrap_fields()', '5.0.0' );
		return \The_SEO_Framework\Interpreters\HTML::wrap_fields( $input, $echo );
	}

	/**
	 * Return a wrapped question mark.
	 *
	 * @since 2.6.0
	 * @since 3.0.0 Links are now no longer followed, referred or bound to opener.
	 * @since 4.0.0 Now adds a tabindex to the span tag, so you can focus it using keyboard navigation.
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 5.0.0 Hard deprecation.
	 *
	 * @param string $description The descriptive on-hover title.
	 * @param string $link        The non-escaped link.
	 * @param bool   $echo        Whether to echo or return.
	 * @return string HTML checkbox output if $echo is false.
	 */
	public function make_info( $description = '', $link = '', $echo = true ) {
		// \the_seo_framework()->_deprecated_function( 'the_seo_framework()->make_info()', '5.0.0' );
		return \The_SEO_Framework\Interpreters\HTML::make_info( $description, $link, $echo );
	}

	/**
	 * Makes either simple or JSON-encoded data-* attributes for HTML elements.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 No longer adds an extra space in front of the return value when no data is generated.
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 5.0.0 Hard deprecation.
	 * @internal
	 *
	 * @param array $data : {
	 *    string $k => mixed $v
	 * }
	 * @return string The HTML data attributes, with added space to the start.
	 */
	public function make_data_attributes( array $data ) {
		// \the_seo_framework()->_deprecated_function( 'the_seo_framework()->make_data_attributes()', '5.0.0' );
		return \The_SEO_Framework\Interpreters\HTML::make_data_attributes( $data );
	}

	/**
	 * Returns a chechbox wrapper.
	 *
	 * @since 2.6.0
	 * @since 2.7.0 Added escape parameter. Defaults to true.
	 * @since 3.0.3 Added $disabled parameter. Defaults to false.
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 5.0.0 Hard deprecation.
	 *
	 * @param string $field_id    The option ID. Must be within the Autodescription settings.
	 * @param string $label       The checkbox description label.
	 * @param string $description Addition description to place beneath the checkbox.
	 * @param bool   $escape      Whether to escape the label and description.
	 * @param bool   $disabled    Whether to disable the input.
	 * @return string HTML checkbox output.
	 */
	public function make_checkbox( $field_id = '', $label = '', $description = '', $escape = true, $disabled = false ) {
		// \the_seo_framework()->_deprecated_function( 'the_seo_framework()->make_checkbox()', '5.0.0' );
		return \The_SEO_Framework\Interpreters\Form::make_checkbox( [
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
	 * @since 5.0.0 Hard deprecation.
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
	public function make_single_select_form( array $args ) {
		// \the_seo_framework()->_deprecated_function( 'the_seo_framework()->make_single_select_form()', '5.0.0' );
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
	 * @since 5.0.0 Hard deprecation.
	 *
	 * @param string $key  The option name which returns boolean.
	 * @param string $depr Deprecated
	 * @param bool   $wrap Whether to wrap the class name in `class="%s"`
	 * @param bool   $echo Whether to echo or return the output.
	 * @return string Empty on echo or the class name with an optional wrapper.
	 */
	public function is_default_checked( $key, $depr = '', $wrap = true, $echo = true ) {
		// \the_seo_framework()->_deprecated_function( 'the_seo_framework()->is_default_checked()', '5.0.0' );
		return \The_SEO_Framework\Interpreters\Form::is_default_checked( $key, $wrap, $echo );
	}
	/**
	 * Returns the HTML class wrap for warning Checkbox options.
	 *
	 * @since 2.3.4
	 * @since 3.1.0 Deprecated second parameter.
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 5.0.0 Hard deprecation.
	 *
	 * @param string $key  The option name which returns boolean.
	 * @param string $deprecated Deprecated.
	 * @param bool   $wrap Whether to wrap the class name in `class="%s"`
	 * @param bool   $echo Whether to echo or return the output.
	 * @return string Empty on echo or the class name with an optional wrapper.
	 */
	public function is_warning_checked( $key, $deprecated = '', $wrap = true, $echo = true ) {
		// \the_seo_framework()->_deprecated_function( 'the_seo_framework()->is_warning_checked()', '5.0.0' );
		return \The_SEO_Framework\Interpreters\Form::is_warning_checked( $key, $wrap, $echo );
	}
	/**
	 * Returns the HTML class wrap for warning/default Checkbox options.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Added the $wrap parameter.
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 5.0.0 Hard deprecation.
	 *
	 * @param string $key  The option name which returns boolean.
	 * @param bool   $wrap Whether to wrap the class name in `class="%s"`
	 */
	public function get_is_conditional_checked( $key, $wrap = true ) {
		// \the_seo_framework()->_deprecated_function( 'the_seo_framework()->get_is_conditional_checked()', '5.0.0' );
		return \The_SEO_Framework\Interpreters\Form::get_is_conditional_checked( $key, $wrap );
	}

	/**
	 * Returns the HTML class wrap for warning/default Checkbox options.
	 *
	 * @since 2.3.4
	 * @since 3.1.0 Deprecated second parameter.
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 5.0.0 Hard deprecation.
	 *
	 * @param string $key        The option name which returns boolean.
	 * @param string $deprecated Deprecated. Used to be the settings field.
	 * @param bool   $wrap       Whether to wrap the class name in `class="%s"`
	 * @param bool   $echo       Whether to echo or return the output.
	 * @return string Empty on echo or the class name with an optional wrapper.
	 */
	public function is_conditional_checked( $key, $deprecated = '', $wrap = true, $echo = true ) {
		// \the_seo_framework()->_deprecated_function( 'the_seo_framework()->is_conditional_checked()', '5.0.0' );
		return \The_SEO_Framework\Interpreters\Form::is_conditional_checked( $key, $wrap, $echo );
	}

	/**
	 * Outputs character counter wrap for both JavaScript and no-Javascript.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 : 1. Added an "what if you click" onhover-title.
	 *                2. Removed second parameter's usage. For passing the expected string.
	 *                3. The whole output is now hidden from no-js.
	 * @since 4.1.0 No longer marks up the counter with the `description` HTML class.
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 5.0.0 Hard deprecation.
	 *
	 * @param string $for     The input ID it's for.
	 * @param string $depr    The initial value for no-JS. Deprecated.
	 * @param bool   $display Whether to display the counter. (options page gimmick)
	 */
	public function output_character_counter_wrap( $for, $depr = '', $display = true ) {
		// \the_seo_framework()->_deprecated_function( 'the_seo_framework()->output_character_counter_wrap()', '5.0.0' );
		return \The_SEO_Framework\Interpreters\Form::output_character_counter_wrap( $for, $display );
	}

	/**
	 * Outputs pixel counter wrap for javascript.
	 *
	 * @since 3.0.0
	 * @since 4.1.4 Deprecated silently. Alternative marked for deletion.
	 * @since 5.0.0 Hard deprecation.
	 *
	 * @param string $for  The input ID it's for.
	 * @param string $type Whether it's a 'title' or 'description' counter.
	 * @param bool   $display Whether to display the counter. (options page gimmick)
	 */
	public function output_pixel_counter_wrap( $for, $type, $display = true ) {
		// \the_seo_framework()->_deprecated_function( 'the_seo_framework()->output_pixel_counter_wrap()', '5.0.0' );
		return \The_SEO_Framework\Interpreters\Form::output_pixel_counter_wrap( $for, $type, $display );
	}
}
