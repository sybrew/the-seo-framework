<?php
/**
 * @package The_SEO_Framework\Classes\Admin\Settings\Post
 * @subpackage The_SEO_Framework\Admin\Edit\Post
 */

namespace The_SEO_Framework\Admin\Settings;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\is_headless;

use \The_SEO_Framework\{
	Admin,
	Data,
};

use \The_SEO_Framework\Helper\{
	Post_Types,
	Query,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Prepares the Post Settings meta box interface.
 *
 * TODO carry over what we implemented in TSFEM, and make that the standard.
 *
 * @since 4.0.0
 * @since 4.3.0 1. Renamed from `PostSettings` to `Post`.
 *              2. Moved from `\The_SEO_Framework\Bridges`.
 * @access private
 */
final class Post {

	/**
	 * Prepares post edit view, like outputting the fields.
	 *
	 * @hook add_meta_boxes 10
	 * @since 4.0.0
	 * @since 4.0.5 Now registers custom postbox classes.
	 * @since 4.2.8 No longer uses the post type label for the meta box title.
	 * @since 4.3.0 1. No longer uses the $post_type for the screen-parameter in add_meta_box.
	 *              2. No longer generates a dynamic title for the Homepage with settings-helper.
	 *                 This because Gutenberg is inconsistent with metabox display and escapes HTML incorrectly.
	 *              3. Now registers homepage warnings in the primary tabs.
	 *              4. Now adds postbox class to non-posts as well.
	 *              5. Moved from `\The_SEO_Framework\The_SEO_Framework\Bridges\PostSettings`.
	 *
	 * @param string $post_type The current post type.
	 */
	public static function prepare_meta_box( $post_type ) {

		/**
		 * @since 2.0.0
		 * @param bool $show_seobox Whether to show the SEO meta box.
		 */
		if (
			   ! Query::is_post_edit()
			|| ! Post_Types::is_post_type_supported( $post_type )
			|| ! \apply_filters( 'the_seo_framework_seobox_output', true )
		) return;

		$box_id = 'tsf-inpost-box';

		// TODO add the_seo_framework_post_metabox_args, and deprecate filters below?
		// -> We'll concede to using Gutenberg, one day, dismissing this.
		\add_meta_box(
			$box_id,
			\esc_html__( 'SEO Settings', 'autodescription' ),
			[ static::class, 'meta_box' ],
			null, // We used to forward hook $post_type, which redundantly forces WP to regenerate the current screen type.
			/**
			 * @since 2.9.0
			 * @param string $context Accepts 'normal', 'side', and 'advanced'.
			 */
			(string) \apply_filters( 'the_seo_framework_metabox_context', 'normal' ),
			/**
			 * @since 2.6.0
			 * @param string $default Accepts 'high', 'default', 'low'
			 *                        Defaults to high, this box is seen right below the post/page edit screen.
			 */
			(string) \apply_filters( 'the_seo_framework_metabox_priority', 'high' )
		);

		$screen_id = \get_current_screen()->id;

		\add_filter( "postbox_classes_{$screen_id}_{$box_id}", [ static::class, 'add_postbox_class' ] );

		if ( ! is_headless( 'settings' ) && Query::is_static_front_page( Query::get_the_real_id() ) ) {
			$output_homepage_warning = [ static::class, 'output_homepage_warning' ];
			\add_action( 'the_seo_framework_pre_page_inpost_general_tab', $output_homepage_warning );
			\add_action( 'the_seo_framework_pre_page_inpost_visibility_tab', $output_homepage_warning );
			\add_action( 'the_seo_framework_pre_page_inpost_social_tab', $output_homepage_warning );
		}
	}

	/**
	 * Setting nav tab wrappers.
	 * Outputs Tabs and settings content.
	 *
	 * @since 4.0.0
	 * @since 4.3.0 1. Removed third parameter: $use_tabs.
	 *              2. Moved from `\The_SEO_Framework\Bridges`.
	 *              3. Renamed from `_flex_nav_tab_wrapper`.
	 *
	 * @param string $id   The nav-tab ID.
	 * @param array  $tabs The tab content {
	 *    string tab ID => array : {
	 *       string   name     : Tab name.
	 *       callable callback : Output function.
	 *       string   dashicon : The dashicon to use.
	 *       mixed    args     : Optional callback function args.
	 *    }
	 * }
	 */
	public static function flex_nav_tab_wrapper( $id, $tabs = [] ) {

		$vars = get_defined_vars();

		Admin\Template::output_view( 'post/wrap-nav', $id, $tabs );
		Admin\Template::output_view( 'post/wrap-content', $id, $tabs );
	}

	/**
	 * Outputs the meta box.
	 *
	 * @since 4.0.0
	 * @since 4.3.0 1. Moved from `\The_SEO_Framework\Bridges`.
	 *              2. Renamed from `_meta_box`.
	 */
	public static function meta_box() {

		\wp_nonce_field( Data\Admin\Post::$nonce_action, Data\Admin\Post::$nonce_name );

		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pre_page_inpost_box' );

		\tsf()->is_gutenberg_page()
			and Admin\Template::output_view( 'post/gutenberg-data' );

		Admin\Template::output_view( 'post/settings', 'main' );

		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pro_page_inpost_box' );
	}

	/**
	 * Adds a Gutenberg/Block-editor box class.
	 *
	 * @since 4.0.5
	 * @since 4.3.0 1. Moved from `\The_SEO_Framework\Bridges`.
	 *              2. Renamed from `_add_postbox_class`.
	 *
	 * @param array $classes The registered postbox classes.
	 * @return array
	 */
	public static function add_postbox_class( $classes = [] ) {

		if ( \tsf()->is_gutenberg_page() )
			$classes[] = 'tsf-is-block-editor';

		return $classes;
	}

	/**
	 * Outputs the Homepage SEO settings warning.
	 *
	 * @since 4.3.0
	 */
	public static function output_homepage_warning() {
		Admin\Template::output_view( 'post/homepage-warning' );
	}

	/**
	 * Outputs the Post SEO box general tab.
	 *
	 * @since 4.0.0
	 * @since 4.3.0 1. Moved from `\The_SEO_Framework\Bridges`.
	 *              2. Renamed from `_general_tab`.
	 */
	public static function general_tab() {
		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pre_page_inpost_general_tab' );
		Admin\Template::output_view( 'post/settings', 'general' );
		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pro_page_inpost_general_tab' );
	}

	/**
	 * Outputs the Post SEO box visibility tab.
	 *
	 * @since 4.0.0
	 * @since 4.3.0 1. Moved from `\The_SEO_Framework\Bridges`.
	 *              2. Renamed from `_visibility_tab`.
	 */
	public static function visibility_tab() {
		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pre_page_inpost_visibility_tab' );
		Admin\Template::output_view( 'post/settings', 'visibility' );
		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pro_page_inpost_visibility_tab' );
	}

	/**
	 * Outputs the Post SEO box social tab.
	 *
	 * @since 4.0.0
	 * @since 4.3.0 1. Moved from `\The_SEO_Framework\Bridges`.
	 *              2. Renamed from `_social_tab`.
	 */
	public static function social_tab() {
		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pre_page_inpost_social_tab' );
		Admin\Template::output_view( 'post/settings', 'social' );
		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pro_page_inpost_social_tab' );
	}
}