<?php
/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

defined( 'ABSPATH' ) or die;

/**
 * Class AutoDescription_Feed
 *
 * Influences WordPress feeds.
 * "Report Cybr for Feeding." - General EUW League of Legends player.
 *
 * @since 2.5.2
 */
class AutoDescription_Feed extends AutoDescription_Transients {

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	private function __wakeup() { }

	/**
	 * Handle unapproachable invoked methods.
	 */
	public function __call( $name, $arguments ) {
		parent::__call( $name, $arguments );
	}

	/**
	 * Constructor, load parent constructor and run functions.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'template_redirect', array( $this, 'init_feed' ) );
	}

	/**
	 * Initializes feed actions and hooks.
	 *
	 * @since 2.6.0
	 */
	public function init_feed() {

		if ( false === $this->is_feed() )
			return;

		add_filter( 'the_content_feed', array( $this, 'the_content_feed' ), 10, 2 );

		//* Only add the feed link to the excerpt if we're only building excerpts.
		if ( $this->rss_uses_excerpt() )
			add_filter( 'the_excerpt_rss', array( $this, 'the_content_feed' ), 10, 1 );

	}

	/**
	 * Determines whether the WordPress excerpt RSS feed option is used.
	 *
	 * @since 2.6.0
	 *
	 * @return bool
	 */
	public function rss_uses_excerpt() {
		return (bool) get_option( 'rss_use_excerpt' );
	}

	/**
	 * Changes feed's content.
	 *
	 * @param $content The feed's content.
	 * @param $feed_type The feed type (not used in excerpted content)
	 *
	 * @since 2.5.2
	 */
	public function the_content_feed( $content, $feed_type = null ) {

		if ( $content ) {

			/**
			 * Don't alter already-excerpts or descriptions.
			 * $feed_type is only set on 'the_content_feed' filter.
			 */
			if ( isset( $feed_type ) && $this->get_option( 'excerpt_the_feed' ) ) {
				//* Strip all code and lines.
				$excerpt = $this->get_excerpt_by_id( $content );

				$excerpt_len = (int) mb_strlen( $excerpt );
				/**
				 * Applies filters the_seo_framework_max_content_feed_length : The max excerpt length.
				 * @since 2.5.2
				 */
				$max_len = (int) apply_filters( 'the_seo_framework_max_content_feed_length', 400 );

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
						$h2_output = '<h2>' . $h2_content . "</h2>\r\n";
					}
				}

				$content = $h2_output . '<p>' . trim( $excerpt ) . '</p>';
			}

			if ( $this->get_option( 'source_the_feed' ) ) {

				//* Fetch permalink and add it to the content.
				$permalink = $this->the_url();

				/**
				 * Applies filters 'the_seo_framework_feed_source_link' : string
				 * @since 2.6.0
				 */
				$source_i18n = (string) apply_filters( 'the_seo_framework_feed_source_link_text', _x( 'Source', 'The content source', 'autodescription' ) );
				$content .= "\r\n" . '<p><a href="' . $permalink . '" rel="external nofollow">' . $source_i18n . '</a></p>';
			}
		}

		return $content;
	}
}
