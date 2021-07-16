<?php
/**
 * @package The_SEO_Framework\Classes\Bridges\Feed
 * @subpackage The_SEO_Framework\Feed
 */

namespace The_SEO_Framework\Bridges;

/**
 * The SEO Framework plugin
 * Copyright (C) 2020 - 2021 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Sets up class loader as file is loaded.
 * This is done asynchronously, because static calls are handled prior and after.
 *
 * @see EOF. Because of the autoloader and (future) trait calling, we can't do it before the class is read.
 * @link https://bugs.php.net/bug.php?id=75771
 */
$_load_feed_class = function() {
	// phpcs:ignore, TSF.Performance.Opcodes.ShouldHaveNamespaceEscape
	new Feed();
};

/**
 * Prepares feed mofifications.
 *
 * @since 4.1.0
 * @access protected
 * @final Can't be extended.
 */
final class Feed {

	/**
	 * @since 4.1.0
	 * @var \The_SEO_Framework\Bridges\Feed
	 */
	private static $instance;

	/**
	 * @var null|\The_SEO_Framework\Load
	 */
	private static $tsf = null;

	/**
	 * Returns this instance.
	 *
	 * @since 4.1.0
	 *
	 * @return \The_SEO_Framework\Bridges\Feed $instance
	 */
	public static function get_instance() {
		return static::$instance;
	}

	/**
	 * Prepares the class and loads constructor.
	 *
	 * Use this if the actions need to be registered early, but nothing else of
	 * this class is needed yet.
	 *
	 * @since 4.1.0
	 */
	public static function prepare() {}

	/**
	 * The constructor. Can't be instantiated externally from this file.
	 * Kills PHP on subsequent duplicated request. Enforces singleton.
	 *
	 * This probably autoloads at action "template_redirect", priority "1".
	 *
	 * @since 4.1.0
	 * @access private
	 * @internal
	 */
	public function __construct() {

		static $count = 0;
		0 === $count++ or \wp_die( 'Don\'t instance <code>' . __CLASS__ . '</code>.' );

		static::$tsf      = \the_seo_framework();
		static::$instance = &$this;
	}

	/**
	 * Initialized feed modifications.
	 *
	 * @since 4.1.0
	 * @access private
	 */
	public function _init() {

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

		if ( ! $content ) return '';

		/**
		 * Don't alter already-excerpts or descriptions.
		 * $feed_type is only set on 'the_content_feed' filter.
		 */
		if ( isset( $feed_type ) && static::$tsf->get_option( 'excerpt_the_feed' ) ) {
			$content = $this->convert_feed_entry_to_excerpt( $content );
		}

		if ( static::$tsf->get_option( 'source_the_feed' ) ) {
			$content .= PHP_EOL . $this->get_feed_entry_source_link();
		}

		return $content;
	}

	/**
	 * Converts feed content to excerpt.
	 *
	 * @since 4.1.0
	 *
	 * @param string $content The full feed entry content.
	 * @return string The excerpted feed.
	 */
	protected function convert_feed_entry_to_excerpt( $content = '' ) {

		if ( ! $content ) return '';

		// Strip all code and lines.
		$excerpt = static::$tsf->s_excerpt_raw( $content, false );

		/**
		 * @since 2.5.2
		 * @param int $max_len The maximum feed (multibyte) string length.
		 */
		$max_len = (int) \apply_filters( 'the_seo_framework_max_content_feed_length', 400 );

		// Generate excerpt.
		$excerpt = static::$tsf->trim_excerpt( $excerpt, 0, $max_len );

		return '<p>' . $excerpt . '</p>';
	}

	/**
	 * Generates and returns feed source link.
	 *
	 * @since 4.1.0
	 *
	 * @return string The translatable feed entry source link.
	 */
	protected function get_feed_entry_source_link() {
		/**
		 * @since 2.6.0
		 * @since 2.7.2 or 2.7.3 : Escaped output.
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

$_load_feed_class();
