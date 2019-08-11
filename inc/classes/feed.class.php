<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Feed
 * @subpackage The_SEO_Framework\Feed
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
 * Class The_SEO_Framework\Feed
 *
 * Influences WordPress feeds.
 * "Report Cybr for Feeding." - General EUW League of Legends player.
 *
 * @since 2.8.0
 */
class Feed extends Cache {

	/**
	 * Determines whether the WordPress excerpt RSS feed option is used.
	 *
	 * @since 2.6.0
	 *
	 * @return bool
	 */
	public function rss_uses_excerpt() {
		return (bool) \get_option( 'rss_use_excerpt' );
	}

	/**
	 * Changes feed's content based on options.
	 *
	 * This method converts the input $content to an excerpt and is able to add
	 * a nofollow backlink at the end of the feed.
	 *
	 * @since 2.5.2
	 *
	 * @param string      $content   The feed's content.
	 * @param null|string $feed_type The feed type (not used in excerpted content)
	 * @return string The modified feed entry.
	 */
	public function the_content_feed( $content = '', $feed_type = null ) {

		if ( ! $content ) return '';

		/**
		 * Don't alter already-excerpts or descriptions.
		 * $feed_type is only set on 'the_content_feed' filter.
		 */
		if ( isset( $feed_type ) && $this->get_option( 'excerpt_the_feed' ) ) {
			$content = $this->convert_feed_entry_to_excerpt( $content );
		}

		if ( $this->get_option( 'source_the_feed' ) ) {
			$content .= PHP_EOL . $this->get_feed_entry_source_link();
		}

		return $content;
	}

	/**
	 * Converts feed content to excerpt.
	 *
	 * @since 2.9.0
	 * @since 4.0.0 No longer uses mbstring for html tagging, it was redundant as we were looking for ASCII characters.
	 *
	 * @param string $content The full feed entry content.
	 * @return string The excerpted feed.
	 */
	protected function convert_feed_entry_to_excerpt( $content = '' ) {

		if ( ! $content ) return '';

		//* Strip all code and lines.
		$excerpt = $this->s_excerpt_raw( $content, false );

		/**
		 * @since 2.5.2
		 * @param int $max_len The maximum feed (multibyte) string length.
		 */
		$max_len = (int) \apply_filters( 'the_seo_framework_max_content_feed_length', 400 );

		//* Generate excerpt.
		$excerpt = $this->trim_excerpt( $excerpt, 0, $max_len );

		$h2_output = '';

		if ( 0 === strpos( $content, '<h2>' ) ) {
			//* Add the h2 title back
			$h2_end = strpos( $content, '</h2>' );

			if ( false !== $h2_end ) {
				//* Start of content, plus strlen( '<h2>' )
				$h2_start = strlen( '<h2>' );
				//* Remove the length of <h2>, again.
				$h2_end = $h2_end - $h2_start;

				//* Fetch h2 content.
				$h2_content = substr( $content, $h2_start, $h2_end );

				//* Remove the H2 content from the excerpt.
				$excerpt = str_replace( $h2_content, '', $excerpt );

				//* Wrap h2 content in h2 tags.
				$h2_output = '<h2>' . $h2_content . '</h2>' . PHP_EOL;
			}
		}

		$content = $h2_output . '<p>' . trim( $excerpt ) . '</p>';

		return $content;
	}

	/**
	 * Generates and returns feed source link.
	 *
	 * @since 2.9.0
	 * @since 3.0.0 Now uses plain permalink, rather than enhanced canonical URL.
	 *
	 * @return string The translatable feed entry source link.
	 */
	protected function get_feed_entry_source_link() {
		/**
		 * @since 2.6.0
		 * @since 2.7.2 or 2.7.3: Escaped output.
		 * @param string $source The source indication string.
		 */
		$source_i18n = (string) \apply_filters(
			'the_seo_framework_feed_source_link_text',
			\_x( 'Source', 'The content source', 'autodescription' )
		);

		return sprintf(
			'<p><a href="%s" rel="nofollow">%s</a></p>',
			\esc_url( \get_permalink() ),
			\esc_html( $source_i18n )
		);
	}
}
