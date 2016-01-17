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
	 * Constructor, load parent constructor and run functions.
	 */
	public function __construct() {
		parent::__construct();

		add_filter( 'the_content_feed', array( $this, 'the_content_feed' ), 10, 2 );
	}

	/**
	 * Changes feed's content.
	 *
	 * @since 2.5.2
	 */
	public function the_content_feed( $content, $feed_type ) {

		if ( ! empty( $content ) ) {

			if ( $this->get_option( 'excerpt_the_feed' ) ) {
				//* Strip all code and lines.
				$excerpt = $this->get_excerpt_by_id( $content );

				$excerpt_len = (int) mb_strlen( $excerpt );
				/**
				 * Applies filters the_seo_framework_max_content_feed_length : The max excerpt length.
				 * @since 2.5.2
				 */
				$max_len = (int) apply_filters( 'the_seo_framework_max_content_feed_length', 400 );

				//* Generate excerpt.
				if ( $excerpt_len > $max_len ) {
					// Cut string to fit $max_char_length.
					$subex = mb_substr( $excerpt, 0, $max_len );
					// Split words in array. Boom.
					$exwords = explode( ' ', $subex );
					// Calculate if last word exceeds.
					$excut = - ( mb_strlen( $exwords[ count( $exwords ) - (int) 1 ] ) );

					if ( $excut < (int) 0 ) {
						//* Cut out exceeding word.
						$excerpt = mb_substr( $subex, 0, $excut );
					} else {
						// We're all good here, continue.
						$excerpt = $subex;
					}

					$excerpt = rtrim( $excerpt ) . '...';
				}

				$h2_output = '';

				if ( 0 === mb_strpos( $content, '<h2>' ) ) {
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

				$source_i18n = _x( 'Source', 'The content source', 'autodescription' );
				$content .= "\r\n" . '<p><a href="' . $permalink . '" rel="external nofollow">' . $source_i18n . '</a></p>';
			}

		}

		return $content;
	}

}
