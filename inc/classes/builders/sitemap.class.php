<?php
/**
 * @package The_SEO_Framework\Classes\Builders\Sitemap
 * @subpackage The_SEO_Framework\Sitemap
 */

namespace The_SEO_Framework\Builders;

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
 * Generates the sitemap.
 *
 * @since 4.0.0
 * @abstract
 *
 * @access public
 */
abstract class Sitemap {

	/**
	 * @var null|\The_SEO_Framework\Load
	 */
	protected static $tsf = null;

	/**
	 * Constructor.
	 *
	 * @since 4.0.0
	 */
	final public function __construct() {
		static::$tsf = \the_seo_framework();
	}

	/**
	 * Destructor.
	 *
	 * @since 4.0.0
	 */
	final public function __destruct() {
		static::$tsf = null;
	}

	/**
	 * Prepares sitemap generation by raising the memory limit and fixing the timezone.
	 *
	 * @since 4.0.0
	 * @since 4.0.4 Now sets timezone to UTC to fix WP 5.3 bug <https://core.trac.wordpress.org/ticket/48623>
	 */
	final public function prepare_generation() {

		\wp_raise_memory_limit( 'sitemap' );

		// Set timezone according to settings.
		static::$tsf->set_timezone( 'UTC' );
	}

	/**
	 * Shuts down the sitemap generator.
	 *
	 * @since 4.0.0
	 */
	final public function shutdown_generation() {
		static::$tsf->reset_timezone();
	}

	/**
	 * Generates and returns the sitemap content.
	 * We recommend you overwriting this method to include caching.
	 *
	 * @since 4.1.2
	 * @abstract
	 * TODO consider adding ...$args?
	 *
	 * @return string The sitemap content.
	 */
	public function generate_sitemap() {

		$this->prepare_generation();

		$sitemap = $this->build_sitemap();

		$this->shutdown_generation();

		return $sitemap;
	}

	/**
	 * Returns the sitemap content.
	 *
	 * @since 4.0.0
	 * @abstract
	 *
	 * @return string The sitemap content.
	 */
	abstract public function build_sitemap();

	/**
	 * Creates XML entry from array input.
	 * Input is expected to be escaped and XML-safe.
	 *
	 * Note: Not final, other classes may overwrite this.
	 *
	 * @since 4.1.1
	 *
	 * @param array $data  The data to create an XML item from. Expected to be escaped and XML-safe!
	 * @param int   $level The iteration level. Default 1 (one level in from urlset).
	 *                     Affects non-mandatory tab indentation for readability.
	 * @return string The XML data.
	 */
	protected function create_xml_entry( $data, $level = 1 ) {

		$out = '';

		foreach ( $data as $key => $value ) {
			$tabs = str_repeat( "\t", $level );

			if ( \is_array( $value ) )
				$value = "\n" . $this->create_xml_entry( $value, $level + 1 ) . $tabs;

			$out .= "$tabs<$key>$value</$key>\n";
		}

		return $out;
	}

	/**
	 * Determines if post is possibly included in the sitemap.
	 *
	 * This is a weak check, as the filter might not be present outside of the sitemap's scope.
	 * The URL also isn't checked, nor the position.
	 *
	 * @since 3.0.4
	 * @since 3.0.6 First filter value now works as intended.
	 * @since 3.1.0 1. Resolved a PHP notice when ID is 0, resulting in returning false-esque unintentionally.
	 *              2. Now accepts 0 in the filter.
	 * @since 4.0.0 1. Now tests qubit options.
	 *              2. FALSE: Now tests for redirect settings. <- it never did! We did document this though...
	 *              3. First parameter can now be a post object.
	 *              4. If the first parameter is 0, it's now indicative of a home-as-blog page.
	 *              5. Moved to \The_SEO_Framework\Builders\Sitemap
	 * @since 4.1.4 TRUE: Now tests for redirect settings.
	 *
	 * @param int $post_id The Post ID to check.
	 * @return bool True if included, false otherwise.
	 */
	final public function is_post_included_in_sitemap( $post_id ) {

		static $excluded = null;
		if ( null === $excluded ) {
			/**
			 * @since 2.5.2
			 * @since 2.8.0 No longer accepts '0' as entry.
			 * @since 3.1.0 '0' is accepted again.
			 * @param int[] $excluded Sequential list of excluded IDs: [ int ...post_id ]
			 */
			$excluded = (array) \apply_filters( 'the_seo_framework_sitemap_exclude_ids', [] );

			if ( empty( $excluded ) ) {
				$excluded = [];
			} else {
				// isset() is faster than in_array(). So, we flip it.
				$excluded = array_flip( $excluded );
			}
		}

		$included = ! isset( $excluded[ $post_id ] );

		while ( $included ) :
			$_args = [
				'id'       => $post_id,
				'taxonomy' => '',
			];

			// ROBOTS_IGNORE_PROTECTION as we don't need to test 'private' (because of sole 'publish'), and 'password' (because of false 'has_password')
			$meta     = static::$tsf->generate_robots_meta( $_args, null, \The_SEO_Framework\ROBOTS_IGNORE_PROTECTION );
			$included = ! ( isset( $meta['noindex'] ) && 'noindex' === $meta['noindex'] );

			if ( ! $included ) break;

			$included = ! static::$tsf->get_redirect_url( $_args );
			break;
		endwhile;

		return $included;
	}

	/**
	 * Determines if post is possibly included in the sitemap.
	 *
	 * This is a weak check, as the filter might not be present outside of the sitemap's scope.
	 * The URL also isn't checked, nor the position.
	 *
	 * @since 4.0.0
	 * @since 4.1.4 Now tests for redirect settings.
	 * @see https://github.com/sybrew/tsf-term-sitemap for example.
	 *
	 * @param int    $term_id  The Term ID to check.
	 * @param string $taxonomy The taxonomy.
	 * @return bool True if included, false otherwise.
	 */
	final public function is_term_included_in_sitemap( $term_id, $taxonomy ) {

		static $excluded = null;
		if ( null === $excluded ) {
			/**
			 * @since 4.0.0
			 * @param int[] $excluded Sequential list of excluded IDs: [ int ...term_id ]
			 */
			$excluded = (array) \apply_filters( 'the_seo_framework_sitemap_exclude_term_ids', [] );

			if ( empty( $excluded ) ) {
				$excluded = [];
			} else {
				// isset() is faster than in_array(). So, we flip it.
				$excluded = array_flip( $excluded );
			}
		}

		$included = ! isset( $excluded[ $term_id ] );

		// Yes, 90% of this code code isn't DRY. However, terms != posts. terms == posts, though :).
		// Really: <https://core.trac.wordpress.org/ticket/50568>
		while ( $included ) :
			$_args = [
				'id'       => $term_id,
				'taxonomy' => $taxonomy,
			];

			// ROBOTS_IGNORE_PROTECTION is not tested for terms. However, we may use that later.
			$meta     = static::$tsf->generate_robots_meta( $_args, null, \The_SEO_Framework\ROBOTS_IGNORE_PROTECTION );
			$included = ! ( isset( $meta['noindex'] ) && 'noindex' === $meta['noindex'] );

			if ( ! $included ) break;

			$included = ! static::$tsf->get_redirect_url( $_args );
			break;
		endwhile;

		return $included;
	}

	/**
	 * Returns the sitemap post query limit.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 Moved to \The_SEO_Framework\Builders\Sitemap
	 *
	 * @param bool $hierarchical Whether the query is for hierarchical post types or not.
	 * @return int The post limit
	 */
	final protected function get_sitemap_post_limit( $hierarchical = false ) {
		/**
		 * @since 2.2.9
		 * @since 2.8.0 Increased to 1200 from 700.
		 * @since 3.1.0 Now returns an option value; it falls back to the default value if not set.
		 * @since 4.0.0 1. The default is now 3000, from 1200.
		 *              2. Now passes a second parameter.
		 * @param int $total_post_limit
		 * @param bool $hierarchical Whether the query is for hierarchical post types or not.
		 */
		return (int) \apply_filters(
			'the_seo_framework_sitemap_post_limit',
			static::$tsf->get_option( 'sitemap_query_limit' ),
			$hierarchical
		);
	}
}
