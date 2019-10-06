<?php
/**
 * @package The_SEO_Framework\Classes\Bridges\PostSettings
 * @subpackage The_SEO_Framework\Admin\Edit\Post
 */

namespace The_SEO_Framework\Bridges;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * Prepares the Post Settings meta box interface.
 *
 * TODO carry over what we implemented in TSFEM, and make that the standard.
 *
 * @since 4.0.0
 * @access protected
 * @internal
 * @final Can't be extended.
 */
final class PostSettings {
	use \The_SEO_Framework\Traits\Enclose_Stray_Private;

	/**
	 * Registers the meta box for the Post edit screens.
	 *
	 * @since 4.0.0
	 *
	 * @param string $post_type The current Post Type.
	 */
	public static function _prepare_meta_box( $post_type ) {

		$tsf = \the_seo_framework();

		$label = $tsf->get_post_type_label( $post_type );

		/**
		 * @since 2.9.0
		 * @param string $context Accepts 'normal', 'side', and 'advanced'.
		 */
		$context = (string) \apply_filters( 'the_seo_framework_metabox_context', 'normal' );

		/**
		 * @since 2.6.0
		 * @param string $default Accepts 'high', 'default', 'low'
		 *                        Defaults to high, this box is seen right below the post/page edit screen.
		 */
		$priority = (string) \apply_filters( 'the_seo_framework_metabox_priority', 'high' );

		if ( $tsf->is_front_page_by_id( $tsf->get_the_real_ID() ) ) {
			if ( $tsf->can_access_settings() ) {
				$schema = \is_rtl() ? '%2$s - %1$s' : '%1$s - %2$s';
				$title  = sprintf(
					$schema,
					\esc_html__( 'Homepage SEO Settings', 'autodescription' ),
					$tsf->make_info(
						\__( 'The SEO Settings may take precedence over these settings.', 'autodescription' ),
						$tsf->seo_settings_page_url(),
						false
					)
				);
			} else {
				$title = \esc_html__( 'Homepage SEO Settings', 'autodescription' );
			}
		} else {
			/* translators: %s = Post Type label */
			$title = sprintf( \esc_html__( '%s SEO Settings', 'autodescription' ), $label );
		}

		\add_meta_box( 'tsf-inpost-box', $title, __CLASS__ . '::_meta_box', $post_type, $context, $priority, [] );
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
	 *       mixed    args     : Optional callback function args.
	 *    }
	 * }
	 * @param bool   $use_tabs Whether to output tabs, only works when $tabs count is greater than 1.
	 */
	public static function _flex_nav_tab_wrapper( $id, $tabs = [], $use_tabs = true ) {
		\the_seo_framework()->get_view( 'edit/wrap-nav', get_defined_vars() );
		\the_seo_framework()->get_view( 'edit/wrap-content', get_defined_vars() );
	}

	/**
	 * Outputs the meta box.
	 *
	 * @since 4.0.0
	 */
	public static function _meta_box() {

		static::output_nonce_field();

		$tsf = \the_seo_framework();

		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pre_page_inpost_box' );

		$tsf->is_gutenberg_page()
			and $tsf->get_view( 'edit/seo-settings-singular-gutenberg-data' );
		$tsf->get_view( 'edit/seo-settings-singular' );

		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pro_page_inpost_box' );
	}

	/**
	 * Outputs nonce fields for the post settings.
	 * Redundant, but added for sanity.
	 *
	 * @since 4.0.0
	 */
	private static function output_nonce_field() {
		$tsf = \the_seo_framework();
		\wp_nonce_field( $tsf->inpost_nonce_field, $tsf->inpost_nonce_name );
	}

	/**
	 * Outputs the Post SEO box general tab.
	 *
	 * @since 4.0.0
	 */
	public static function _general_tab() {
		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pre_page_inpost_general_tab' );
		\the_seo_framework()->get_view( 'edit/seo-settings-singular', [], 'general' );
		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pro_page_inpost_general_tab' );
	}

	/**
	 * Outputs the Post SEO box visibility tab.
	 *
	 * @since 4.0.0
	 */
	public static function _visibility_tab() {
		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pre_page_inpost_visibility_tab' );
		\the_seo_framework()->get_view( 'edit/seo-settings-singular', [], 'visibility' );
		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pro_page_inpost_visibility_tab' );
	}

	/**
	 * Outputs the Post SEO box social tab.
	 *
	 * @since 4.0.0
	 */
	public static function _social_tab() {
		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pre_page_inpost_social_tab' );
		\the_seo_framework()->get_view( 'edit/seo-settings-singular', [], 'social' );
		/**
		 * @since 2.9.0
		 */
		\do_action( 'the_seo_framework_pro_page_inpost_social_tab' );
	}
}
