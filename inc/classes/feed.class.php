<?php
/**
 * @package The_SEO_Framework\Classes
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
	 * Constructor, load parent constructor and run functions.
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Initializes feed actions and hooks.
	 *
	 * @since 2.9.0
	 * @access private
	 *
	 * @return void Early if this request isn't for a feed.
	 */
	public function _init_feed_output() {

		if ( ! $this->is_feed() )
			return;

		\add_filter( 'the_content_feed', array( $this, 'the_content_feed' ), 10, 2 );

		//* Only add the feed link to the excerpt if we're only building excerpts.
		if ( $this->rss_uses_excerpt() )
			\add_filter( 'the_excerpt_rss', array( $this, 'the_content_feed' ), 10, 1 );

	}

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
	 * @param $content The feed's content.
	 * @param $feed_type The feed type (not used in excerpted content)
	 * @return string The modified feed entry.
	 */
	public function the_content_feed( $content = '', $feed_type = null ) {

		if ( ! $content )
			return '';

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
	 *
	 * @param string $content The full feed entry content.
	 * @return string The excerpted feed.
	 */
	protected function convert_feed_entry_to_excerpt( $content = '' ) {

		if ( ! $content )
			return '';

		//* Strip all code and lines.
		$excerpt = $this->s_excerpt_raw( $content, false );

		$excerpt_len = (int) mb_strlen( $excerpt );
		/**
		 * Applies filters the_seo_framework_max_content_feed_length : The max excerpt length.
		 * @since 2.5.2
		 */
		$max_len = (int) \apply_filters( 'the_seo_framework_max_content_feed_length', 400 );

		//* Generate excerpt.
		$excerpt = $this->trim_excerpt( $excerpt, $excerpt_len, $max_len );

		$h2_output = '';

		if ( 0 === strpos( $content, '<h2>' ) ) {
			//* Add the h2 title back
			$h2_end = mb_strpos( $content, '</h2>' );

			if ( false !== $h2_end ) {
				//* Start of content, plus <h2>
				$h2_start = 4;
				//* Remove the length of <h2>, again.
				$h2_end = $h2_end - $h2_start;

				//* Fetch h2 content.
				$h2_content = mb_substr( $content, $h2_start, $h2_end );

				//* Remove the H2 content from the excerpt.
				$count = 1;
				$excerpt = str_replace( $h2_content, '', $excerpt, $count );

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
	 *
	 * @return string The translatable feed entry source link.
	 */
	protected function get_feed_entry_source_link() {

		//* Fetch permalink and add it to the content. Already escaped.
		$permalink = $this->the_url();

		/**
		 * Applies filters 'the_seo_framework_feed_source_link' : string
		 *
		 * @since 2.6.0
		 * @since 2.7.2 or 2.7.3: Escaped output.
		 *
		 * @param string $source The source indication string.
		 */
		$source_i18n = (string) \apply_filters( 'the_seo_framework_feed_source_link_text', \_x( 'Source', 'The content source', 'autodescription' ) );
		$content = '<p><a href="' . $permalink . '" rel="nofollow">' . \esc_html( $source_i18n ) . '</a></p>';

		return $content;
	}
}
