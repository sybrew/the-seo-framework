<?php
/**
 * @package The_SEO_Framework\Classes\Front\Meta
 * @subpackage The_SEO_Framework\Meta
 */

namespace The_SEO_Framework\Front\Meta;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\{
	memo,
	_bootstrap_timer,
};

use \The_SEO_Framework\{
	Data,
	Helper\Query,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Outputs the front-end metadata output in WP Head.
 *
 * @since 5.0.0
 * @access protected
 */
final class Head {

	/**
	 * Prints the indicator wrap and meta tags.
	 * Adds various action hooks for outside the wrap.
	 *
	 * @hook wp_head 1
	 * @since 5.0.0
	 */
	public static function print_wrap_and_tags() {

		if ( ! Query\Utils::query_supports_seo() ) return;

		/**
		 * @since 2.6.0
		 */
		\do_action( 'the_seo_framework_do_before_output' );

		/**
		 * The bootstrap timer keeps adding when metadata is strapping.
		 * This causes both timers to increase simultaneously.
		 * We catch the bootstrap's value here, and let the meta-print-timer take over.
		 */
		$bootstrap_timer = _bootstrap_timer();
		/**
		 * Start the meta timer here. This also catches file inclusions,
		 * which _bootstrap_timer() also reads; hence, we separate them.
		 */
		$print_start = hrtime( true );

		static::print_plugin_indicator( 'before' );

		static::print_tags();

		static::print_plugin_indicator(
			'after',
			( hrtime( true ) - $print_start ) / 1e9,
			$bootstrap_timer,
		);

		/**
		 * @since 2.6.0
		 */
		\do_action( 'the_seo_framework_do_after_output' );
	}

	/**
	 * Registers, generates, and prints the meta tags.
	 * Adds various action hooks for around the tags.
	 *
	 * @since 5.0.0
	 */
	public static function print_tags() {

		/**
		 * @since 4.2.0
		 */
		\do_action( 'the_seo_framework_before_meta_output' );

		// Limit processing and redundant tags on 404 and search.
		// TODO consider switching is_404 and aqp again when resolved: https://core.trac.wordpress.org/ticket/51117.
		switch ( true ) {
			case \is_search():
				$generator_pools = [ 'Robots', 'URI', 'Open_Graph', 'Theme_Color', 'Webmasters', 'Schema' ];
				break;
			case Query\Utils::is_query_exploited():
				// search cannot be exploited, hence they're tested earlier.
				$generator_pools = [ 'Robots', 'Advanced_Query_Protection', 'Theme_Color', 'Webmasters' ];
				break;
			case \is_404():
				$generator_pools = [ 'Robots', 'Theme_Color', 'Webmasters', 'Schema' ];
				break;
			default:
				$generator_pools = [
					'Robots',
					'URI',
					'Description',
					'Theme_Color',
					'Open_Graph',
					'Facebook',
					'Twitter',
					'Webmasters',
					'Schema',
				];
		}

		/**
		 * @since 3.1.4
		 * @since 5.0.0 Deprecated
		 * @deprecated
		 * @param bool $use_og_tags
		 */
		if ( ! \apply_filters_deprecated(
			'the_seo_framework_use_og_tags',
			[ (bool) Data\Plugin::get_option( 'og_tags' ) ],
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_generator_pools',
		) ) {
			// phpcs:ignore, VariableAnalysis.CodeAnalysis.VariableAnalysis -- coalescable.
			$remove_pools[] = 'Open_Graph';
		}
		/**
		 * @since 3.1.4
		 * @since 5.0.0 Deprecated
		 * @deprecated
		 * @param bool $use_facebook_tags
		 */
		if ( ! \apply_filters_deprecated(
			'the_seo_framework_use_facebook_tags',
			[ (bool) Data\Plugin::get_option( 'facebook_tags' ) ],
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_generator_pools',
		) ) {
			$remove_pools[] = 'Facebook';
		}
		/**
		 * @since 3.1.4
		 * @since 5.0.0 Deprecated
		 * @deprecated
		 * @param bool $use_twitter_tags
		 */
		if ( ! \apply_filters_deprecated(
			'the_seo_framework_use_twitter_tags',
			[ (bool) Data\Plugin::get_option( 'twitter_tags' ) ],
			'5.0.0 of The SEO Framework',
			'the_seo_framework_meta_generator_pools',
		) ) {
			$remove_pools[] = 'Twitter';
		}

		/**
		 * @since 5.0.0
		 * @param string[] $generator_pools A list of tag pools requested for the current query.
		 *                                  The tag pool names correspond directly to the classes'.
		 *                                  Do not register new pools, it'll cause a fatal error.
		 */
		$generator_pools = \apply_filters(
			'the_seo_framework_meta_generator_pools',
			isset( $remove_pools ) ? array_diff( $generator_pools, $remove_pools ) : $generator_pools,
		);

		$tag_generators   = &Tags::tag_generators();
		$generators_queue = [];

		// Queue array_merge for improved performance. Do not use __NAMESPACE__; needs to be found easily.
		foreach ( $generator_pools as $pool )
			$generators_queue[] = ( "\The_SEO_Framework\Front\Meta\Generator\\$pool" )::GENERATORS;

		/**
		 * @since 5.0.0
		 * @param callable[] $tag_generators  A list of meta tag generator callbacks.
		 *                                    The generators may offload work to other generators.
		 * @param string[]   $generator_pools A list of tag pools requested for the current query.
		 *                                    The tag pool names correspond directly to the classes'.
		 */
		$tag_generators = \apply_filters(
			'the_seo_framework_meta_generators',
			array_merge( ...$generators_queue ),
			$generator_pools,
		);

		Tags::fill_render_data_from_registered_generators();

		/**
		 * @since 5.0.0
		 * @param array[]    $tags_render_data The meta tags' render data : string id => {
		 *    ?array  attributes A list of attributes by [ name => value ].
		 *    ?string tag        The tag name. Defaults to 'meta' if left empty.
		 *    ?string content    The tag's content. Leave null to not render content.
		 *    ?true   rendered   Do not write; tells whether the tag is rendered.
		 * }
		 * @param callable[] $tag_generators   A list of meta tag generator callbacks.
		 *                                     The generators may offload work to other generators.
		 */
		$tags_render_data = \apply_filters( // phpcs:ignore, Generic.Formatting -- bug in PHPCS.
			'the_seo_framework_meta_render_data',
			$tags_render_data = &Tags::tags_render_data(),
			$tag_generators,
		);

		// Now output everything.
		Tags::render_tags();

		/**
		 * @since 4.2.0
		 */
		\do_action( 'the_seo_framework_after_meta_output' );
	}

	/**
	 * Returns the plugin hidden HTML indicators.
	 * Memoizes the filter outputs.
	 *
	 * @since 5.0.0
	 * @access private
	 *
	 * @param string $where                 Determines the position of the indicator.
	 *                                      Accepts 'before' for before, anything else for after.
	 * @param float  $meta_timer            Total meta time in seconds.
	 * @param float  $bootstrap_timer       Total bootstrap time in seconds.
	 * @return string The SEO Framework's HTML plugin indicator.
	 */
	private static function print_plugin_indicator( $where = 'before', $meta_timer = 0, $bootstrap_timer = 0 ) {

		$cache = memo() ?? memo( [
			/**
			 * @since 2.0.0
			 * @param bool $run Whether to run and show the plugin indicator.
			 */
			'run'        => (bool) \apply_filters( 'the_seo_framework_indicator', true ),
			/**
			 * @since 2.4.0
			 * @param bool $show_timer Whether to show the generation time in the indicator.
			 */
			'show_timer' => (bool) \apply_filters( 'the_seo_framework_indicator_timing', true ),
			'annotation' => \esc_html( trim( vsprintf(
				/* translators: 1 = The SEO Framework, 2 = 'by Sybre Waaijer */
				\__( '%1$s %2$s', 'autodescription' ),
				[
					'The SEO Framework',
					/**
					 * @since 2.4.0
					 * @param bool $sybre Whether to show the author name in the indicator.
					 */
					\apply_filters( 'sybre_waaijer_<3', true ) // phpcs:ignore, WordPress.NamingConventions.ValidHookName -- Easter egg.
						? \__( 'by Sybre Waaijer', 'autodescription' )
						: '',
				]
			) ) ),
		] );

		if ( ! $cache['run'] ) return '';

		switch ( $where ) {
			case 'before':
				// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped earlier.
				echo "\n<!-- {$cache['annotation']} -->\n";
				break;
			case 'after':
				if ( $cache['show_timer'] && $meta_timer && $bootstrap_timer ) {
					$timers = \sprintf(
						' | %s meta | %s boot',
						number_format( $meta_timer * 1e3, 2, null, '' ) . 'ms',
						number_format( $bootstrap_timer * 1e3, 2, null, '' ) . 'ms',
					);
				} else {
					$timers = '';
				}

				// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped earlier.
				echo "<!-- / {$cache['annotation']}{$timers} -->\n\n";
		}
	}
}
