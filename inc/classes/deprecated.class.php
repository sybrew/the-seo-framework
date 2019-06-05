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
			$is_term   = false;
			$post_i18n = $tsf->get_post_type_label( $type );
			$post_type = $type;
		} else {
			$is_term   = true;
			$term      = $tsf->fetch_the_term( $post_id );
			$taxonomy  = $tsf->get_current_taxonomy();
			$post_type = $tsf->get_admin_post_type();
		}

		$bar = $tsf->get_generated_seo_bar( [
			'id'        => $post_id,
			'post_type' => $post_type,
			'taxonomy'  => $taxonomy,
		] );

		if ( $echo ) {
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
	 * @since 3.3.0 Deprecated.
	 * @deprecated
	 *
	 * @return string The scripts class name.
	 */
	public function Scripts() {
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
	 * @since 3.3.0 Deprecated
	 * @deprecated
	 */
	public function initialize_term_meta() {
		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->initialize_term_meta()', '3.3.0', '\the_seo_framework()->init_term_meta()' );
		\the_seo_framework()->init_term_meta();
	}
}
