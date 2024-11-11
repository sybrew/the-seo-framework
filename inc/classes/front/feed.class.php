<?php
/**
 * @package The_SEO_Framework\Classes\Front\Feed
 * @subpackage The_SEO_Framework\Feed
 */

namespace The_SEO_Framework\Front;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\{
	Data,
	Helper,
	Helper\Format,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2020 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Prepares feed mofifications.
 *
 * @since 4.1.0
 * @since 5.0.0 Moved from `\The_SEO_Framework\Bridges`.
 * @access private
 */
final class Feed {

	/**
	 * Sets the X-Robots-Tag headers for feeds.
	 *
	 * @hook template_redirect 10
	 * @since 5.0.0
	 */
	public static function output_robots_noindex_headers_on_feed() {
		\is_feed() and Helper\Headers::output_robots_noindex_headers();
	}

	/**
	 * Changes feed's content based on options.
	 *
	 * This method converts the input $content to an excerpt and is able to add
	 * a nofollow backlink to the end of the feed.
	 *
	 * @hook the_content_feed 10
	 * @hook the_excerpt_rss 10
	 * @since 5.0.0
	 *
	 * @param string      $content   The feed's content.
	 * @param null|string $feed_type The feed type (not used in excerpted content)
	 * @return string The modified feed entry.
	 */
	public static function modify_the_content_feed( $content = '', $feed_type = null ) {

		// When there's no content, there's nothing to modify or quote.
		if ( empty( $content ) ) return '';

		/**
		 * Don't alter already-excerpts or descriptions.
		 * $feed_type is only set on 'the_content_feed' filter.
		 */
		if ( isset( $feed_type ) && Data\Plugin::get_option( 'excerpt_the_feed' ) ) {
			/**
			 * @since 2.5.2
			 * @param int $clamp_length The maximum feed (multibyte) string length.
			 */
			$clamp_length = (int) \apply_filters( 'the_seo_framework_max_content_feed_length', 400 );

			// Strip all code and lines, and AI-trim it.
			$excerpt = Format\HTML::extract_content(
				$content,
				[
					'allow_shortcodes' => false,
					'clamp'            => $clamp_length,
				],
			);

			$content = "<p>$excerpt</p>";
		}

		if ( Data\Plugin::get_option( 'source_the_feed' ) ) {
			$content .= \sprintf(
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
