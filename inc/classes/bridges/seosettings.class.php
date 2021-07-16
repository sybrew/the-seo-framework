<?php
/**
 * @package The_SEO_Framework\Classes\Bridges\SeoSettings
 * @subpackage The_SEO_Framework\Admin\Settings
 */

namespace The_SEO_Framework\Bridges;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2021 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

/**
 * Prepares the SEO Settings page interface.
 *
 * Note the use of "metabox" instead of "meta_box" throughout.
 *
 * @since 4.0.0
 * @access protected
 * @internal
 * @final Can't be extended.
 */
final class SeoSettings {

	/**
	 * Registers meta boxes on the Site SEO Settings page.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	public static function _register_seo_settings_meta_boxes() {

		/**
		 * Various metabox filters.
		 * Set any to false if you wish the meta box to be removed.
		 *
		 * @since 2.2.4
		 * @since 2.8.0 Added `the_seo_framework_general_metabox` filter.
		 */
		$general     = (bool) \apply_filters( 'the_seo_framework_general_metabox', true );
		$title       = (bool) \apply_filters( 'the_seo_framework_title_metabox', true );
		$description = (bool) \apply_filters( 'the_seo_framework_description_metabox', true );
		$robots      = (bool) \apply_filters( 'the_seo_framework_robots_metabox', true );
		$home        = (bool) \apply_filters( 'the_seo_framework_home_metabox', true );
		$social      = (bool) \apply_filters( 'the_seo_framework_social_metabox', true );
		$schema      = (bool) \apply_filters( 'the_seo_framework_schema_metabox', true );
		$webmaster   = (bool) \apply_filters( 'the_seo_framework_webmaster_metabox', true );
		$sitemap     = (bool) \apply_filters( 'the_seo_framework_sitemap_metabox', true );
		$feed        = (bool) \apply_filters( 'the_seo_framework_feed_metabox', true );

		$settings_page_hook = \the_seo_framework()->seo_settings_page_hook;

		// General Meta Box
		if ( $general )
			\add_meta_box(
				'autodescription-general-settings',
				\esc_html__( 'General Settings', 'autodescription' ),
				__CLASS__ . '::_general_metabox',
				$settings_page_hook,
				'main',
				[]
			);

		// Title Meta Box
		if ( $title )
			\add_meta_box(
				'autodescription-title-settings',
				\esc_html__( 'Title Settings', 'autodescription' ),
				__CLASS__ . '::_title_metabox',
				$settings_page_hook,
				'main',
				[]
			);

		// Description Meta Box
		if ( $description )
			\add_meta_box(
				'autodescription-description-settings',
				\esc_html__( 'Description Meta Settings', 'autodescription' ),
				__CLASS__ . '::_description_metabox',
				$settings_page_hook,
				'main',
				[]
			);

		// Homepage Meta Box
		if ( $home )
			\add_meta_box(
				'autodescription-homepage-settings',
				\esc_html__( 'Homepage Settings', 'autodescription' ),
				__CLASS__ . '::_homepage_metabox',
				$settings_page_hook,
				'main',
				[]
			);

		// Social Meta Box
		if ( $social )
			\add_meta_box(
				'autodescription-social-settings',
				\esc_html__( 'Social Meta Settings', 'autodescription' ),
				__CLASS__ . '::_social_metabox',
				$settings_page_hook,
				'main',
				[]
			);

		// Schema Meta Box
		if ( $schema )
			\add_meta_box(
				'autodescription-schema-settings',
				\esc_html__( 'Schema.org Settings', 'autodescription' ),
				__CLASS__ . '::_schema_metabox',
				$settings_page_hook,
				'main',
				[]
			);

		// Robots Meta Box
		if ( $robots )
			\add_meta_box(
				'autodescription-robots-settings',
				\esc_html__( 'Robots Meta Settings', 'autodescription' ),
				__CLASS__ . '::_robots_metabox',
				$settings_page_hook,
				'main',
				[]
			);

		// Webmaster Meta Box
		if ( $webmaster )
			\add_meta_box(
				'autodescription-webmaster-settings',
				\esc_html__( 'Webmaster Meta Settings', 'autodescription' ),
				__CLASS__ . '::_webmaster_metabox',
				$settings_page_hook,
				'main',
				[]
			);

		// Sitemaps Meta Box
		if ( $sitemap )
			\add_meta_box(
				'autodescription-sitemap-settings',
				\esc_html__( 'Sitemap Settings', 'autodescription' ),
				__CLASS__ . '::_sitemaps_metabox',
				$settings_page_hook,
				'main',
				[]
			);

		// Feed Meta Box
		if ( $feed )
			\add_meta_box(
				'autodescription-feed-settings',
				\esc_html__( 'Feed Settings', 'autodescription' ),
				__CLASS__ . '::_feed_metabox',
				$settings_page_hook,
				'main',
				[]
			);
	}

	/**
	 * Setting nav tab wrappers.
	 * Outputs Tabs and settings content.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @param string $id      The nav-tab ID.
	 * @param array  $tabs    The tab content {
	 *    string tab ID => array : {
	 *       string   name     : Tab name.
	 *       callable callback : Output function.
	 *       string   dashicon : The dashicon to use.
	 *       mixed    args     : Optional callback function args. These arguments
	 *                           will be extracted to variables in scope of the view.
	 *    }
	 * }
	 * @param bool   $use_tabs Whether to output tabs, only works when $tabs count is greater than 1.
	 */
	public static function _nav_tab_wrapper( $id, $tabs = [], $use_tabs = true ) { // phpcs:ignore,VariableAnalysis
		\the_seo_framework()->get_view( 'admin/wrap-nav', get_defined_vars() );
		\the_seo_framework()->get_view( 'admin/wrap-content', get_defined_vars() );
	}

	/**
	 * Outputs SEO Settings page wrap.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	public static function _output_wrap() {
		/**
		 * @since 3.0.0
		 */
		\do_action( 'the_seo_framework_pre_seo_settings' );
		\the_seo_framework()->get_view( 'admin/seo-settings-wrap' );
		/**
		 * @since 3.0.0
		 */
		\do_action( 'the_seo_framework_pro_seo_settings' );
	}

	/**
	 * Outputs SEO Settings columns.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	public static function _output_columns() {
		\the_seo_framework()->get_view( 'admin/seo-settings-columns' );
	}

	/**
	 * Outputs General Settings meta box on the Site SEO Settings page.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @param \WP_Post|null $post The current post object.
	 * @param array         $args The metabox arguments.
	 */
	public static function _general_metabox( $post = null, $args = [] ) {
		/**
		 * @since 2.8.0
		 */
		\do_action( 'the_seo_framework_general_metabox_before' );
		\the_seo_framework()->get_view( 'admin/metaboxes/general-metabox', $args );
		/**
		 * @since 2.8.0
		 */
		\do_action( 'the_seo_framework_general_metabox_after' );
	}

	/**
	 * Outputs General Settings meta box general tab.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see static::general_metabox() : Callback for General Settings box.
	 */
	public static function _general_metabox_general_tab() {
		\the_seo_framework()->get_view( 'admin/metaboxes/general-metabox', [], 'general' );
	}

	/**
	 * Outputs General Settings meta box layout tab.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see static::general_metabox() : Callback for General Settings box.
	 */
	public static function _general_metabox_layout_tab() {
		\the_seo_framework()->get_view( 'admin/metaboxes/general-metabox', [], 'layout' );
	}

	/**
	 * Outputs General Settings meta box performance tab.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see static::general_metabox() : Callback for General Settings box.
	 */
	public static function _general_metabox_performance_tab() {
		\the_seo_framework()->get_view( 'admin/metaboxes/general-metabox', [], 'performance' );
	}

	/**
	 * Outputs General Settings meta box canonical tab.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see static::general_metabox() : Callback for General Settings box.
	 */
	public static function _general_metabox_canonical_tab() {
		\the_seo_framework()->get_view( 'admin/metaboxes/general-metabox', [], 'canonical' );
	}

	/**
	 * Outputs General Settings meta box timestamps tab.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see static::general_metabox() : Callback for General Settings box.
	 */
	public static function _general_metabox_timestamps_tab() {
		\the_seo_framework()->get_view( 'admin/metaboxes/general-metabox', [], 'timestamps' );
	}

	/**
	 * Outputs General Settings meta box exclusions tab.
	 *
	 * @since 4.1.0
	 * @access private
	 * @see static::general_metabox() : Callback for General Settings box.
	 */
	public static function _general_metabox_exclusions_tab() {
		\the_seo_framework()->get_view( 'admin/metaboxes/general-metabox', [], 'exclusions' );
	}

	/**
	 * Title meta box on the Site SEO Settings page.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @param \WP_Post|null $post The current post object.
	 * @param array         $args The metabox arguments.
	 */
	public static function _title_metabox( $post = null, $args = [] ) {
		/**
		 * @since 2.5.0 or earlier.
		 */
		\do_action( 'the_seo_framework_title_metabox_before' );
		\the_seo_framework()->get_view( 'admin/metaboxes/title-metabox', $args );
		/**
		 * @since 2.5.0 or earlier.
		 */
		\do_action( 'the_seo_framework_title_metabox_after' );
	}

	/**
	 * Title meta box general tab.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see static::title_metabox() : Callback for Title Settings box.
	 */
	public static function _title_metabox_general_tab() {
		\the_seo_framework()->get_view( 'admin/metaboxes/title-metabox', [], 'general' );
	}

	/**
	 * Title meta box general tab.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see static::title_metabox() : Callback for Title Settings box.
	 *
	 * @param array $args The variables to pass to the metabox tab.
	 */
	public static function _title_metabox_additions_tab( $args ) {
		\the_seo_framework()->get_view( 'admin/metaboxes/title-metabox', $args, 'additions' );
	}

	/**
	 * Title meta box prefixes tab.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see static::title_metabox() : Callback for Title Settings box.
	 *
	 * @param array $args The variables to pass to the metabox tab.
	 */
	public static function _title_metabox_prefixes_tab( $args ) {
		\the_seo_framework()->get_view( 'admin/metaboxes/title-metabox', $args, 'prefixes' );
	}

	/**
	 * Description meta box on the Site SEO Settings page.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @param \WP_Post|null $post The current post object.
	 * @param array         $args The metabox arguments.
	 */
	public static function _description_metabox( $post = null, $args = [] ) {
		/**
		 * @since 2.5.0 or earlier.
		 */
		\do_action( 'the_seo_framework_description_metabox_before' );
		\the_seo_framework()->get_view( 'admin/metaboxes/description-metabox', $args );
		/**
		 * @since 2.5.0 or earlier.
		 */
		\do_action( 'the_seo_framework_description_metabox_after' );
	}

	/**
	 * Robots meta box on the Site SEO Settings page.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @param \WP_Post|null $post The current post object.
	 * @param array         $args The metabox arguments.
	 */
	public static function _robots_metabox( $post = null, $args = [] ) {
		/**
		 * @since 2.5.0 or earlier.
		 */
		\do_action( 'the_seo_framework_robots_metabox_before' );
		\the_seo_framework()->get_view( 'admin/metaboxes/robots-metabox', $args );
		/**
		 * @since 2.5.0 or earlier.
		 */
		\do_action( 'the_seo_framework_robots_metabox_after' );
	}

	/**
	 * Robots Metabox General Tab output.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see static::robots_metabox() Callback for Robots Settings box.
	 */
	public static function _robots_metabox_general_tab() {
		\the_seo_framework()->get_view( 'admin/metaboxes/robots-metabox', [], 'general' );
	}

	/**
	 * Robots Metabox "No-: Index/Follow/Archive" Tab output.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see static::robots_metabox() Callback for Robots Settings box.
	 *
	 * @param array $args The variables to pass to the metabox tab.
	 */
	public static function _robots_metabox_no_tab( $args ) {
		\the_seo_framework()->get_view( 'admin/metaboxes/robots-metabox', $args, 'no' );
	}

	/**
	 * Outputs the Homepage meta box on the Site SEO Settings page.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @param \WP_Post|null $post The current post object.
	 * @param array         $args The navigation tabs args.
	 */
	public static function _homepage_metabox( $post = null, $args = [] ) {
		/**
		 * @since 2.5.0 or earlier.
		 */
		\do_action( 'the_seo_framework_homepage_metabox_before' );
		\the_seo_framework()->get_view( 'admin/metaboxes/homepage-metabox', $args );
		/**
		 * @since 2.5.0 or earlier.
		 */
		\do_action( 'the_seo_framework_homepage_metabox_after' );
	}

	/**
	 * Homepage Metabox General Tab Output.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see static::homepage_metabox() Callback for Homepage Settings box.
	 */
	public static function _homepage_metabox_general_tab() {
		\the_seo_framework()->get_view( 'admin/metaboxes/homepage-metabox', [], 'general' );
	}

	/**
	 * Homepage Metabox Additions Tab Output.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see static::homepage_metabox() Callback for Homepage Settings box.
	 */
	public static function _homepage_metabox_additions_tab() {
		\the_seo_framework()->get_view( 'admin/metaboxes/homepage-metabox', [], 'additions' );
	}

	/**
	 * Homepage Metabox Robots Tab Output
	 *
	 * @since 4.0.0
	 * @access private
	 * @see static::homepage_metabox() Callback for Homepage Settings box.
	 */
	public static function _homepage_metabox_robots_tab() {
		\the_seo_framework()->get_view( 'admin/metaboxes/homepage-metabox', [], 'robots' );
	}

	/**
	 * Homepage Metabox Social Tab Output
	 *
	 * @since 4.0.0
	 * @access private
	 * @see static::homepage_metabox() Callback for Homepage Settings box.
	 */
	public static function _homepage_metabox_social_tab() {
		\the_seo_framework()->get_view( 'admin/metaboxes/homepage-metabox', [], 'social' );
	}

	/**
	 * Social meta box on the Site SEO Settings page.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @param \WP_Post|null $post The current post object.
	 * @param array         $args The navigation tabs args.
	 */
	public static function _social_metabox( $post = null, $args = [] ) {
		/**
		 * @since 2.5.0 or earlier.
		 */
		\do_action( 'the_seo_framework_social_metabox_before' );
		\the_seo_framework()->get_view( 'admin/metaboxes/social-metabox', $args );
		/**
		 * @since 2.5.0 or earlier.
		 */
		\do_action( 'the_seo_framework_social_metabox_after' );
	}

	/**
	 * Social Metabox General Tab output.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see static::social_metabox() Callback for Social Settings box.
	 */
	public static function _social_metabox_general_tab() {
		\the_seo_framework()->get_view( 'admin/metaboxes/social-metabox', [], 'general' );
	}

	/**
	 * Social Metabox Facebook Tab output.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see static::social_metabox() Callback for Social Settings box.
	 */
	public static function _social_metabox_facebook_tab() {
		\the_seo_framework()->get_view( 'admin/metaboxes/social-metabox', [], 'facebook' );
	}

	/**
	 * Social Metabox Twitter Tab output.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see static::social_metabox() Callback for Social Settings box.
	 */
	public static function _social_metabox_twitter_tab() {
		\the_seo_framework()->get_view( 'admin/metaboxes/social-metabox', [], 'twitter' );
	}

	/**
	 * Social Metabox oEmbed Tab output.
	 *
	 * @since 4.0.5
	 * @access private
	 * @see static::social_metabox() Callback for Social Settings box.
	 */
	public static function _social_metabox_oembed_tab() {
		\the_seo_framework()->get_view( 'admin/metaboxes/social-metabox', [], 'oembed' );
	}

	/**
	 * Social Metabox PostDates Tab output.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see static::social_metabox() Callback for Social Settings box.
	 */
	public static function _social_metabox_postdates_tab() {
		\the_seo_framework()->get_view( 'admin/metaboxes/social-metabox', [], 'postdates' );
	}

	/**
	 * Webmaster meta box on the Site SEO Settings page.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @param \WP_Post|null $post The current post object.
	 * @param array         $args The navigation tabs args.
	 */
	public static function _webmaster_metabox( $post = null, $args = [] ) {
		/**
		 * @since 2.5.0 or earlier.
		 */
		\do_action( 'the_seo_framework_webmaster_metabox_before' );
		\the_seo_framework()->get_view( 'admin/metaboxes/webmaster-metabox', $args );
		/**
		 * @since 2.5.0 or earlier.
		 */
		\do_action( 'the_seo_framework_webmaster_metabox_after' );
	}

	/**
	 * Sitemaps meta box on the Site SEO Settings page.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see static::sitemaps_metabox() Callback for Sitemaps Settings box.
	 *
	 * @param \WP_Post|null $post The current post object.
	 * @param array         $args The navigation tabs args.
	 */
	public static function _sitemaps_metabox( $post = null, $args = [] ) {
		/**
		 * @since 2.5.0 or earlier.
		 */
		\do_action( 'the_seo_framework_sitemaps_metabox_before' );
		\the_seo_framework()->get_view( 'admin/metaboxes/sitemaps-metabox', $args );
		/**
		 * @since 2.5.0 or earlier.
		 */
		\do_action( 'the_seo_framework_sitemaps_metabox_after' );
	}

	/**
	 * Sitemaps Metabox General Tab output.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see static::sitemaps_metabox() Callback for Sitemaps Settings box.
	 */
	public static function _sitemaps_metabox_general_tab() {
		\the_seo_framework()->get_view( 'admin/metaboxes/sitemaps-metabox', [], 'general' );
	}

	/**
	 * Sitemaps Metabox Robots Tab output.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see static::sitemaps_metabox() Callback for Sitemaps Settings box.
	 */
	public static function _sitemaps_metabox_robots_tab() {
		\the_seo_framework()->get_view( 'admin/metaboxes/sitemaps-metabox', [], 'robots' );
	}

	/**
	 * Sitemaps Metabox Metadata Tab output.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see static::sitemaps_metabox() Callback for Sitemaps Settings box.
	 */
	public static function _sitemaps_metabox_metadata_tab() {
		\the_seo_framework()->get_view( 'admin/metaboxes/sitemaps-metabox', [], 'metadata' );
	}

	/**
	 * Sitemaps Metabox Notify Tab output.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see static::sitemaps_metabox() Callback for Sitemaps Settings box.
	 */
	public static function _sitemaps_metabox_notify_tab() {
		\the_seo_framework()->get_view( 'admin/metaboxes/sitemaps-metabox', [], 'notify' );
	}

	/**
	 * Sitemaps Metabox Style Tab output.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see static::sitemaps_metabox() Callback for Sitemaps Settings box.
	 */
	public static function _sitemaps_metabox_style_tab() {
		\the_seo_framework()->get_view( 'admin/metaboxes/sitemaps-metabox', [], 'style' );
	}

	/**
	 * Feed Metabox on the Site SEO Settings page.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @param \WP_Post|null $post The current post object.
	 * @param array         $args The navigation tabs args.
	 */
	public static function _feed_metabox( $post = null, $args = [] ) {
		/**
		 * @since 2.5.2
		 */
		\do_action( 'the_seo_framework_feed_metabox_before' );
		\the_seo_framework()->get_view( 'admin/metaboxes/feed-metabox', $args );
		/**
		 * @since 2.5.2
		 */
		\do_action( 'the_seo_framework_feed_metabox_after' );
	}

	/**
	 * Schema Metabox on the Site SEO Settings page.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @param \WP_Post|null $post The current post object.
	 * @param array         $args The navigation tabs args.
	 */
	public static function _schema_metabox( $post = null, $args = [] ) {
		/**
		 * @since 2.6.0
		 */
		\do_action( 'the_seo_framework_schema_metabox_before' );
		\the_seo_framework()->get_view( 'admin/metaboxes/schema-metabox', $args );
		/**
		 * @since 2.6.0
		 */
		\do_action( 'the_seo_framework_schema_metabox_after' );
	}

	/**
	 * Schema Metabox Structure Tab output.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see static::schema_metabox() Callback for Schema.org Settings box.
	 */
	public static function _schema_metabox_structure_tab() {
		\the_seo_framework()->get_view( 'admin/metaboxes/schema-metabox', [], 'structure' );
	}

	/**
	 * Schema Metabox PResence Tab output.
	 *
	 * @since 4.0.0
	 * @access private
	 * @see static::schema_metabox() Callback for Schema.org Settings box.
	 */
	public static function _schema_metabox_presence_tab() {
		\the_seo_framework()->get_view( 'admin/metaboxes/schema-metabox', [], 'presence' );
	}
}
