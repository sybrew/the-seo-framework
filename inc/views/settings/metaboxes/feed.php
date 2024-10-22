<?php
/**
 * @package The_SEO_Framework\Views\Admin\Metaboxes
 * @subpackage The_SEO_Framework\Admin\Settings
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) or die;

use \The_SEO_Framework\Admin\Settings\Layout\{
	HTML,
	Input,
};
use \The_SEO_Framework\Helper\Format\Markdown;

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * The SEO Framework plugin
 * Copyright (C) 2016 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// See _description_metabox et al.
[ $instance ] = $view_args;

switch ( $instance ) : // Quite useless, but prepared for expansion.
	case 'main':
		HTML::header_title( \__( 'Content Feed Settings', 'autodescription' ) );
		HTML::description( \__( "Sometimes, your content can get stolen by robots through the WordPress feeds. This can cause duplicate content issues. To prevent this from happening, it's recommended to convert the feed's content into an excerpt.", 'autodescription' ) );
		HTML::description( \__( 'Adding a backlink below the feed entries will also let the visitors know where the content came from.', 'autodescription' ) );

		?>
		<hr>
		<?php
		HTML::header_title( \__( 'Change Feed Settings', 'autodescription' ) );
		$excerpt_the_feed_label  = \esc_html__( 'Convert feed entries into excerpts?', 'autodescription' );
		$excerpt_the_feed_label .= ' ' . HTML::make_info( \__( 'By default the excerpt will be at most 400 characters long.', 'autodescription' ), '', false );

		$source_the_feed_label  = \esc_html__( 'Add link to source below the feed entry content?', 'autodescription' );
		$source_the_feed_label .= ' ' . HTML::make_info( \__( 'This link will not be followed by search engines.', 'autodescription' ), '', false );

		$index_the_feed_label  = \esc_html__( 'Allow indexing of feeds?', 'autodescription' );
		$index_the_feed_label .= ' ' . HTML::make_info( \__( 'If this site publishes podcasts, enable this option. Otherwise, leave it disabled. Indexing feeds can cause search engines to crawl and index new pages slower; however, some podcast services require feeds to be indexable.', 'autodescription' ), '', false );

		HTML::wrap_fields(
			[
				Input::make_checkbox( [
					'id'     => 'excerpt_the_feed',
					'label'  => $excerpt_the_feed_label,
					'escape' => false,
				] ),
				Input::make_checkbox( [
					'id'     => 'source_the_feed',
					'label'  => $source_the_feed_label,
					'escape' => false,
				] ),
				Input::make_checkbox( [
					'id'     => 'index_the_feed',
					'label'  => $index_the_feed_label,
					'escape' => false,
				] ),
			],
			true,
		);

		if ( \get_option( 'rss_use_excerpt' ) ) {
			HTML::description_noesc(
				Markdown::convert(
					\sprintf(
						/* translators: %s = Reading Settings URL. Links are in Markdown! */
						\esc_html__( 'Note: The feed is already converted into an excerpt through the [Reading Settings](%s).', 'autodescription' ),
						\esc_url( \admin_url( 'options-reading.php' ) ),
					),
					[ 'a' ],
					[ 'a_internal' => false ] // open in new window, although it's internal.
				)
			);
		}

		HTML::description_noesc( \sprintf(
			'<a href="%s" target=_blank rel=noopener>%s</a>',
			\esc_url( \get_feed_link(), [ 'https', 'http' ] ),
			\esc_html__( 'View the main feed.', 'autodescription' ),
		) );
endswitch;
