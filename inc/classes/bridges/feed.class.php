<?php
/**
 * @package The_SEO_Framework\Classes\Bridges\Feed
 * @subpackage The_SEO_Framework\Feed
 */

namespace The_SEO_Framework\Bridges;

/**
 * The SEO Framework plugin
 * Copyright (C) 2020 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Prepares feed mofifications.
 *
 * @since 4.1.0
 * @access private
 * @final Can't be extended.
 */
final class Feed {

	/**
	 * @since 4.1.0
	 * @var \The_SEO_Framework\Bridges\Feed
	 * @ignore
	 */
	private static $instance;

	/**
	 * Returns this instance.
	 *
	 * @since 4.1.0
	 * @TODO deprecate. Use constructor instead.
	 * @ignore
	 *
	 * @return \The_SEO_Framework\Bridges\Feed $instance
	 */
	public static function get_instance() {
		return static::$instance ?? ( static::$instance = new static );
	}

	/**
	 * Prepares the class and loads constructor.
	 *
	 * Use this if the actions need to be registered early, but nothing else of
	 * this class is needed yet.
	 *
	 * @since 4.1.0
	 * @TODO deprecate. Use constructor instead.
	 * @ignore
	 */
	public static function prepare() {
		static::get_instance();
	}

	/**
	 * Initialized feed modifications.
	 *
	 * @since 4.2.7
	 * @access private
	 */
	public function __construct() {
		$this->_init();
	}

	/**
	 * Initialized feed modifications.
	 *
	 * @since 4.1.0
	 * @access private
	 * @TODO deprecate. Use constructor instead.
	 */
	public function _init() {

		if ( \The_SEO_Framework\has_run( __METHOD__ ) ) return;

		// Alter the content feed.
		\add_filter( 'the_content_feed', [ $this, '_modify_the_content_feed' ], 10, 2 );

		// Only add the feed link to the excerpt if we're only building excerpts.
		if ( \get_option( 'rss_use_excerpt' ) )
			\add_filter( 'the_excerpt_rss', [ $this, '_modify_the_content_feed' ], 10, 1 );
	}

	/**
	 * Changes feed's content based on options.
	 *
	 * This method converts the input $content to an excerpt and is able to add
	 * a nofollow backlink at the end of the feed.
	 *
	 * @since 4.1.0
	 * @access private
	 *
	 * @param string      $content   The feed's content.
	 * @param null|string $feed_type The feed type (not used in excerpted content)
	 * @return string The modified feed entry.
	 */
	public function _modify_the_content_feed( $content = '', $feed_type = null ) {

		// When there's no content, there's nothing to modify or quote.
		if ( ! $content ) return '';

		$tsf = \tsf();

		/**
		 * Don't alter already-excerpts or descriptions.
		 * $feed_type is only set on 'the_content_feed' filter.
		 */
		if ( isset( $feed_type ) && $tsf->get_option( 'excerpt_the_feed' ) ) {
			// Strip all code and lines, and AI-trim it.
			$excerpt = $tsf->trim_excerpt(
				$tsf->s_excerpt_raw( $content, false ),
				0,
				/**
				 * @since 2.5.2
				 * @param int $max_len The maximum feed (multibyte) string length.
				 */
				\apply_filters( 'the_seo_framework_max_content_feed_length', 400 )
			);

			$content = "<p>$excerpt</p>";
		}

		if ( $tsf->get_option( 'source_the_feed' ) ) {
			$content .= sprintf(
				"\n" . '<p><a href="%s" rel="nofollow">%s</a></p>', // Keep XHTML valid!
				\esc_url( \get_permalink() ),
				\esc_html(
					/**
					 * @since 2.6.0
					 * @since 2.7.2 or 2.7.3: Escaped output.
					 * @param string $source The source indication string.
					 */
					\apply_filters(
						'the_seo_framework_feed_source_link_text',
						\_x( 'Source', 'The content source', 'autodescription' )
					)
				)
			);
		}

		return $content;
	}
}
