<?php
/**
 * @package The_SEO_Framework\Classes\Builders\SEOBar\Term
 * @subpackage The_SEO_Framework\SEOBar
 */

namespace The_SEO_Framework\Builders\SEOBar;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Generates the SEO Bar for posts.
 *
 * @since 4.0.0
 * @since 4.2.0 Renamed to `The_SEO_Framework\Builders\SEOBar\Term` from `The_SEO_Framework\Builders\SeoBar_Term`
 *
 * @access private
 * @internal
 * @see \The_SEO_Framework\Interpreters\SEOBar
 *      Use \The_SEO_Framework\Interpreters\SEOBar::generate_bar() instead.
 */
final class Term extends Main {

	/**
	 * @since 4.0.0
	 * @abstract
	 * @var array All known tests.
	 */
	public static $tests = [ 'title', 'description', 'indexing', 'following', 'archiving', 'redirect' ];

	/**
	 * Primes the cache.
	 *
	 * @since 4.0.0
	 * @abstract
	 */
	protected function prime_cache() {
		// phpcs:disable, PEAR.Functions.FunctionCallSignature.Indent -- False negative.
		static::get_cache( 'general/i18n/inputguidelines' )
			or static::set_cache(
				'general/i18n/inputguidelines',
				static::$tsf->get_input_guidelines_i18n()
			);

		static::get_cache( 'general/detect/robotsglobal' )
			or static::set_cache(
				'general/detect/robotsglobal',
				[
					'hasrobotstxt' => static::$tsf->has_robots_txt(),
					'blogpublic'   => static::$tsf->is_blog_public(),
					'site'         => [
						'noindex'   => static::$tsf->get_option( 'site_noindex' ),
						'nofollow'  => static::$tsf->get_option( 'site_nofollow' ),
						'noarchive' => static::$tsf->get_option( 'site_noarchive' ),
					],
					'posttype'     => [
						'noindex'   => static::$tsf->get_option( static::$tsf->get_robots_post_type_option_id( 'noindex' ) ),
						'nofollow'  => static::$tsf->get_option( static::$tsf->get_robots_post_type_option_id( 'nofollow' ) ),
						'noarchive' => static::$tsf->get_option( static::$tsf->get_robots_post_type_option_id( 'noarchive' ) ),
					],
					'taxonomy'     => [
						'noindex'   => static::$tsf->get_option( static::$tsf->get_robots_taxonomy_option_id( 'noindex' ) ),
						'nofollow'  => static::$tsf->get_option( static::$tsf->get_robots_taxonomy_option_id( 'nofollow' ) ),
						'noarchive' => static::$tsf->get_option( static::$tsf->get_robots_taxonomy_option_id( 'noarchive' ) ),
					],
				]
			);
		// phpcs:enable, PEAR.Functions.FunctionCallSignature.Indent
	}

	/**
	 * Primes the current query cache.
	 *
	 * @since 4.0.0
	 * @abstract
	 *
	 * @param array $query_cache The current query cache. Passed by reference.
	 */
	protected function prime_query_cache( array &$query_cache = [] ) {
		$query_cache = [
			'term'   => \get_term( static::$query['id'], static::$query['taxonomy'] ),
			'meta'   => static::$tsf->get_term_meta( static::$query['id'], true ), // Use TSF cache--TSF initializes it anyway.
			'states' => [
				'locale'       => \get_locale(),
				'isempty'      => ! static::$tsf->is_term_populated( static::$query['id'], static::$query['taxonomy'] ),
				'posttypes'    => static::$tsf->get_post_types_from_taxonomy( static::$query['taxonomy'] ),
				'robotsmeta'   => array_merge(
					[
						'noindex'   => false,
						'nofollow'  => false,
						'noarchive' => false,
					],
					static::$tsf->generate_robots_meta(
						[
							'id'       => static::$query['id'],
							'taxonomy' => static::$query['taxonomy'],
						],
						[ 'noindex', 'nofollow', 'noarchive' ],
						\The_SEO_Framework\ROBOTS_ASSERT
					)
				),
				// We don't use this... yet. I couldn't find a way to properly implement the assertions in the right order.
				// The asserter should be leading, but the SEO Bar should be readable.
				'robotsassert' => static::$tsf->retrieve_robots_meta_assertions(),
			],
		];
	}

	/**
	 * Tests for blocking redirection.
	 *
	 * @since 4.0.0
	 * @abstract
	 *
	 * @return bool True if there's a blocking redirect, false otherwise.
	 */
	protected function has_blocking_redirect() {
		return ! empty( $this->query_cache['meta']['redirect'] );
	}

	/**
	 * Runs title tests.
	 *
	 * @since 4.0.0
	 * @since 4.0.5 1. Removed `['params']['prefixed'] from cache.
	 *              2. Now tests for term title prefix per state.
	 *              3. Added syntax test.
	 *
	 * @return array $item : {
	 *    string  $symbol : The displayed symbol that identifies your bar.
	 *    string  $title  : The title of the assessment.
	 *    int     $status : Power of two. See \The_SEO_Framework\Interpreters\SEOBar's class constants.
	 *    string  $reason : The final assessment: The reason for the $status. The latest state-changing reason is used.
	 *    string  $assess : The assessments on why the reason is set. Keep it short and concise!
	 *                     Does not accept HTML for performant ARIA support.
	 * }
	 */
	protected function test_title() {

		$cache = static::get_cache( 'term/title/defaults' ) ?: static::set_cache(
			'term/title/defaults',
			[
				'params'   => [
					'untitled'        => static::$tsf->get_static_untitled_title(),
					'blogname_quoted' => preg_quote( static::$tsf->get_blogname(), '/' ),
					/* translators: 1 = An assessment, 2 = Disclaimer, e.g. "take it with a grain of salt" */
					'disclaim'        => \__( '%1$s (%2$s)', 'autodescription' ),
					'estimated'       => \__( 'Estimated from the number of characters found. The pixel counter asserts the true length.', 'autodescription' ),
				],
				'assess'   => [
					'empty'      => \__( 'No title could be fetched.', 'autodescription' ),
					'untitled'   => sprintf(
						/* translators: %s = "Untitled" */
						\__( 'No title could be fetched, "%s" is used instead.', 'autodescription' ),
						static::$tsf->get_static_untitled_title()
					),
					'prefixed'   => \__( 'A term label prefix is automatically added which increases the length.', 'autodescription' ),
					'branding'   => [
						'not'       => \__( "It's not branded. Search engines may ignore your title. Consider adding back the site title.", 'autodescription' ),
						'manual'    => \__( "It's manually branded.", 'autodescription' ),
						'automatic' => \__( "It's automatically branded.", 'autodescription' ),
					],
					'duplicated' => \__( 'The site title is found multiple times.', 'autodescription' ),
					'syntax'     => \__( "Markup syntax was found that isn't transformed. Consider rewriting the custom title.", 'autodescription' ),
				],
				'reason'   => [
					'incomplete' => \__( 'Incomplete.', 'autodescription' ),
					'duplicated' => \__( 'The branding is repeated.', 'autodescription' ),
					'notbranded' => \__( 'Not branded.', 'autodescription' ),
					'syntax'     => \__( 'Found markup syntax.', 'autodescription' ),
				],
				'defaults' => [
					'generated' => [
						'symbol' => \_x( 'TG', 'Title Generated', 'autodescription' ),
						'title'  => \__( 'Title, generated', 'autodescription' ),
						'status' => \The_SEO_Framework\Interpreters\SEOBar::STATE_GOOD,
						'reason' => \__( 'Automatically generated.', 'autodescription' ),
						'assess' => [
							'base' => \__( "It's built from the term name.", 'autodescription' ),
						],
					],
					'custom'    => [
						'symbol' => \_x( 'T', 'Title', 'autodescription' ),
						'title'  => \__( 'Title', 'autodescription' ),
						'status' => \The_SEO_Framework\Interpreters\SEOBar::STATE_GOOD,
						'reason' => \__( 'Obtained from term SEO meta input.', 'autodescription' ),
						'assess' => [
							'base' => \__( "It's built from term SEO meta input.", 'autodescription' ),
						],
					],
				],
			]
		);

		$_generator_args = [
			'id'       => static::$query['id'],
			'taxonomy' => static::$query['taxonomy'],
		];

		// TODO instead of getting values from the options API, why don't we store the parameters and allow them to be modified?
		// This way, we can implement real-time live-edit AJAX SEO bar items...
		$title_part = static::$tsf->get_filtered_raw_custom_field_title( $_generator_args );

		if ( \strlen( $title_part ) ) {
			$item = $cache['defaults']['custom'];

			if ( static::$tsf->has_unprocessed_syntax( $title_part, false ) ) {
				$item['status']           = \The_SEO_Framework\Interpreters\SEOBar::STATE_BAD;
				$item['reason']           = $cache['reason']['syntax'];
				$item['assess']['syntax'] = $cache['assess']['syntax'];

				// Further assessments must be made later. Halt assertion here to prevent confusion.
				return $item;
			}
		} else {
			$item = $cache['defaults']['generated'];

			if ( static::$tsf->use_generated_archive_prefix( $this->query_cache['term'] ) ) {
				$item['assess']['prefixed'] = $cache['assess']['prefixed'];
			}

			$title_part = static::$tsf->get_filtered_raw_generated_title( $_generator_args );
		}

		if ( ! $title_part ) {
			$item['status']          = \The_SEO_Framework\Interpreters\SEOBar::STATE_BAD;
			$item['reason']          = $cache['reason']['incomplete'];
			$item['assess']['empty'] = $cache['assess']['empty'];

			// Further assessments must be made later. Halt assertion here to prevent confusion.
			return $item;
		} elseif ( $title_part === $cache['params']['untitled'] ) {
			$item['status']             = \The_SEO_Framework\Interpreters\SEOBar::STATE_BAD;
			$item['reason']             = $cache['reason']['incomplete'];
			$item['assess']['untitled'] = $cache['assess']['untitled'];

			// Further assessments must be made later. Halt assertion here to prevent confusion.
			return $item;
		}

		$title = $title_part;

		if ( static::$tsf->use_title_branding( $_generator_args ) ) {
			$_title_before = $title;
			static::$tsf->merge_title_branding( $title, $_generator_args );

			// Absence assertion is done after this.
			if ( $title === $_title_before ) {
				// Title didn't change, so no automatic branding was added.
				$item['assess']['branding'] = $cache['assess']['branding']['manual'];
			} else {
				$item['assess']['branding'] = $cache['assess']['branding']['automatic'];
			}
		} else {
			$item['assess']['branding'] = $cache['assess']['branding']['manual'];
		}

		$brand_count = \strlen( $cache['params']['blogname_quoted'] )
			? preg_match_all(
				"/{$cache['params']['blogname_quoted']}/ui",
				$title,
				$matches
			)
			: 0;

		if ( ! $brand_count ) {
			// Override branding state.
			$item['status']             = \The_SEO_Framework\Interpreters\SEOBar::STATE_UNKNOWN;
			$item['reason']             = $cache['reason']['notbranded'];
			$item['assess']['branding'] = $cache['assess']['branding']['not'];
		} elseif ( $brand_count > 1 ) {
			$item['status']               = \The_SEO_Framework\Interpreters\SEOBar::STATE_BAD;
			$item['reason']               = $cache['reason']['duplicated'];
			$item['assess']['duplicated'] = $cache['assess']['duplicated'];

			// Further assessments must be made later. Halt assertion here to prevent confusion.
			return $item;
		}

		$title_len = mb_strlen(
			html_entity_decode(
				\wp_specialchars_decode( static::$tsf->s_title_raw( $title ), ENT_QUOTES ),
				ENT_NOQUOTES
			)
		);

		$guidelines      = static::$tsf->get_input_guidelines( $this->query_cache['states']['locale'] )['title']['search']['chars'];
		$guidelines_i18n = static::get_cache( 'general/i18n/inputguidelines' );

		if ( $title_len < $guidelines['lower'] ) {
			$item['status'] = \The_SEO_Framework\Interpreters\SEOBar::STATE_BAD;
			$item['reason'] = $guidelines_i18n['shortdot']['farTooShort'];
			$length_i18n    = $guidelines_i18n['long']['farTooShort'];
		} elseif ( $title_len < $guidelines['goodLower'] ) {
			$item['status'] = \The_SEO_Framework\Interpreters\SEOBar::STATE_OKAY;
			$item['reason'] = $guidelines_i18n['shortdot']['tooShort'];
			$length_i18n    = $guidelines_i18n['long']['tooShort'];
		} elseif ( $title_len > $guidelines['upper'] ) {
			$item['status'] = \The_SEO_Framework\Interpreters\SEOBar::STATE_BAD;
			$item['reason'] = $guidelines_i18n['shortdot']['farTooLong'];
			$length_i18n    = $guidelines_i18n['long']['farTooLong'];
		} elseif ( $title_len > $guidelines['goodUpper'] ) {
			$item['status'] = \The_SEO_Framework\Interpreters\SEOBar::STATE_OKAY;
			$item['reason'] = $guidelines_i18n['shortdot']['tooLong'];
			$length_i18n    = $guidelines_i18n['long']['tooLong'];
		} else {
			// Use unaltered reason and status.
			$length_i18n = $guidelines_i18n['long']['good'];
		}

		$item['assess']['length'] = sprintf(
			$cache['params']['disclaim'],
			$length_i18n,
			$cache['params']['estimated']
		);

		return $item;
	}

	/**
	 * Runs description tests.
	 *
	 * @since 4.0.0
	 * @since 4.0.5 Added syntax test.
	 * @see test_title() for return value.
	 *
	 * @return array $item
	 */
	protected function test_description() {

		$cache = static::get_cache( 'term/description/defaults' ) ?: static::set_cache(
			'term/description/defaults',
			[
				'params'   => [
					/* translators: 1 = An assessment, 2 = Disclaimer, e.g. "take it with a grain of salt" */
					'disclaim'   => \__( '%1$s (%2$s)', 'autodescription' ),
					'estimated'  => \__( 'Estimated from the number of characters found. The pixel counter asserts the true length.', 'autodescription' ),
					/**
					 * @since 2.6.0
					 * @param int $dupe_short The minimum stringlength of words to find as dupes.
					 */
					'dupe_short' => (int) \apply_filters( 'the_seo_framework_bother_me_desc_length', 3 ),
				],
				'assess'   => [
					'empty'  => \__( 'No description could be generated.', 'autodescription' ),
					/* translators: %s = list of repeated words */
					'dupes'  => \__( 'Found repeated words: %s', 'autodescription' ),
					'syntax' => \__( "Markup syntax was found that isn't transformed. Consider rewriting the custom description.", 'autodescription' ),
				],
				'reason'   => [
					'empty'         => \__( 'Empty.', 'autodescription' ),
					'founddupe'     => \__( 'Found repeated words.', 'autodescription' ),
					'foundmanydupe' => \__( 'Found too many repeated words.', 'autodescription' ),
					'syntax'        => \__( 'Found markup syntax.', 'autodescription' ),
				],
				'defaults' => [
					'generated'   => [
						'symbol' => \_x( 'DG', 'Description Generated', 'autodescription' ),
						'title'  => \__( 'Description, generated', 'autodescription' ),
						'status' => \The_SEO_Framework\Interpreters\SEOBar::STATE_GOOD,
						'reason' => \__( 'Automatically generated.', 'autodescription' ),
						'assess' => [
							'base' => \__( "It's built from the term description field.", 'autodescription' ),
						],
					],
					'emptynoauto' => [
						'symbol' => \_x( 'D', 'Description', 'autodescription' ),
						'title'  => \__( 'Description', 'autodescription' ),
						'status' => \The_SEO_Framework\Interpreters\SEOBar::STATE_UNKNOWN,
						'reason' => \__( 'Empty.', 'autodescription' ),
						'assess' => [
							'noauto' => \__( 'No term description is set.', 'autodescription' ),
						],
					],
					'custom'      => [
						'symbol' => \_x( 'D', 'Description', 'autodescription' ),
						'title'  => \__( 'Description', 'autodescription' ),
						'status' => \The_SEO_Framework\Interpreters\SEOBar::STATE_GOOD,
						'reason' => \__( 'Obtained from the term SEO meta input.', 'autodescription' ),
						'assess' => [
							'base' => \__( "It's built from the term SEO meta input.", 'autodescription' ),
						],
					],
				],
			]
		);

		$_generator_args = [
			'id'       => static::$query['id'],
			'taxonomy' => static::$query['taxonomy'],
		];

		// TODO instead of getting values from the options API, why don't we store the parameters and allow them to be modified?
		// This way, we can implement real-time live-edit AJAX SEO bar items...
		$desc = static::$tsf->get_description_from_custom_field( $_generator_args, false );

		if ( \strlen( $desc ) ) {
			$item = $cache['defaults']['custom'];

			if ( static::$tsf->has_unprocessed_syntax( $desc ) ) {
				$item['status']           = \The_SEO_Framework\Interpreters\SEOBar::STATE_BAD;
				$item['reason']           = $cache['reason']['syntax'];
				$item['assess']['syntax'] = $cache['assess']['syntax'];

				// Further assessments must be made later. Halt assertion here to prevent confusion.
				return $item;
			}
		} elseif ( ! static::$tsf->is_auto_description_enabled( $_generator_args ) ) {
			$item = $cache['defaults']['emptynoauto'];

			// No description is found. There's no need to continue parsing.
			return $item;
		} else {
			$item = $cache['defaults']['generated'];

			$desc = static::$tsf->get_generated_description( $_generator_args, false );

			if ( ! \strlen( $desc ) ) {
				$item['status'] = \The_SEO_Framework\Interpreters\SEOBar::STATE_UNDEFINED;
				$item['reason'] = $cache['reason']['empty'];

				// This is now inaccurate, purge it.
				// TODO consider alternative? "It TRIED to build it from...."?
				unset( $item['assess']['base'] );

				$item['assess']['empty'] = $cache['assess']['empty'];

				// No description is found. There's no need to continue parsing.
				return $item;
			}
		}

		// Fetch words that are outputted more than 3 times.
		$repeated_words = static::$tsf->get_word_count( $desc, 3, 5, $cache['params']['dupe_short'] );

		if ( $repeated_words ) {
			$dupes = [];
			foreach ( $repeated_words as $_dw ) :
				// Keep abbreviations... WordPress, make multibyte support mandatory already.
				// $_word = ctype_upper( reset( $_dw ) ) ? reset( $_dw ) : mb_strtolower( reset( $_dw ) );

				$dupes[] = sprintf(
					/* translators: 1: Word found, 2: Occurrences */
					\esc_attr__( '&#8220;%1$s&#8221; is used %2$d times.', 'autodescription' ),
					\esc_attr( key( $_dw ) ),
					reset( $_dw )
				);
			endforeach;

			$item['assess']['dupe'] = implode( ' ', $dupes );

			$max = max( $repeated_words );
			$max = reset( $max );

			// Warn when more than 3x triplet+/quintet+ words are found.
			if ( $max > 3 || \count( $repeated_words ) > 1 ) {
				// This must be resolved.
				$item['reason'] = $cache['reason']['foundmanydupe'];
				$item['status'] = \The_SEO_Framework\Interpreters\SEOBar::STATE_BAD;
				return $item;
			} else {
				$item['reason'] = $cache['reason']['founddupe'];
				$item['status'] = \The_SEO_Framework\Interpreters\SEOBar::STATE_OKAY;
			}
		}

		$guidelines      = static::$tsf->get_input_guidelines( $this->query_cache['states']['locale'] )['description']['search']['chars'];
		$guidelines_i18n = static::get_cache( 'general/i18n/inputguidelines' );

		$desc_len = mb_strlen(
			html_entity_decode(
				\wp_specialchars_decode( static::$tsf->s_description_raw( $desc ), ENT_QUOTES ),
				ENT_NOQUOTES
			)
		);

		if ( $desc_len < $guidelines['lower'] ) {
			$item['status'] = \The_SEO_Framework\Interpreters\SEOBar::STATE_BAD;
			$item['reason'] = $guidelines_i18n['shortdot']['farTooShort'];
			$length_i18n    = $guidelines_i18n['long']['farTooShort'];
		} elseif ( $desc_len < $guidelines['goodLower'] ) {
			$item['status'] = \The_SEO_Framework\Interpreters\SEOBar::STATE_OKAY;
			$item['reason'] = $guidelines_i18n['shortdot']['tooShort'];
			$length_i18n    = $guidelines_i18n['long']['tooShort'];
		} elseif ( $desc_len > $guidelines['upper'] ) {
			$item['status'] = \The_SEO_Framework\Interpreters\SEOBar::STATE_BAD;
			$item['reason'] = $guidelines_i18n['shortdot']['farTooLong'];
			$length_i18n    = $guidelines_i18n['long']['farTooLong'];
		} elseif ( $desc_len > $guidelines['goodUpper'] ) {
			$item['status'] = \The_SEO_Framework\Interpreters\SEOBar::STATE_OKAY;
			$item['reason'] = $guidelines_i18n['shortdot']['tooLong'];
			$length_i18n    = $guidelines_i18n['long']['tooLong'];
		} else {
			// Use unaltered reason and status.
			$length_i18n = $guidelines_i18n['long']['good'];
		}

		$item['assess']['length'] = sprintf(
			$cache['params']['disclaim'],
			$length_i18n,
			$cache['params']['estimated']
		);

		return $item;
	}

	/**
	 * Runs indexing tests.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now asserts all taxonomy robots settings.
	 * @see test_title() for return value.
	 *
	 * @return array $item
	 */
	protected function test_indexing() {

		$cache = static::get_cache( 'term/indexing/defaults' ) ?: static::set_cache(
			'term/indexing/defaults',
			[
				'params'   => [],
				'assess'   => [
					'robotstxt'     => \__( 'The robots.txt file is nonstandard, and may still direct search engines differently.', 'autodescription' ),
					'notpublic'     => \__( 'WordPress discourages crawling via the Reading Settings.', 'autodescription' ),
					'site'          => \__( 'Indexing is discouraged for the whole site at the SEO Settings screen.', 'autodescription' ),
					'posttypes'     => \__( 'Indexing is discouraged for all bound post types to this term at the SEO Settings screen.', 'autodescription' ),
					'taxonomy'      => \__( 'Indexing is discouraged for this taxonomy at the SEO Settings screen.', 'autodescription' ),
					'override'      => \__( 'The term SEO meta input overrides the indexing state.', 'autodescription' ),
					'empty'         => \__( 'No posts are attached to this term, so indexing is disabled.', 'autodescription' ),
					'emptyoverride' => \__( 'No posts are attached to this term, so indexing should be disabled.', 'autodescription' ),
					'canonicalurl'  => \__( 'A custom canonical URL is set that points to another page.', 'autodescription' ),
				],
				'reason'   => [
					'notpublic'     => \__( 'WordPress overrides the robots directive.', 'autodescription' ),
					'empty'         => \__( 'The term is empty.', 'autodescription' ),
					'emptyoverride' => \__( 'The term is empty yet still indexed.', 'autodescription' ),
					'canonicalurl'  => \__( 'The canonical URL points to another page.', 'autodescription' ),
				],
				'defaults' => [
					'index'   => [
						'symbol' => \_x( 'I', 'Indexing', 'autodescription' ),
						'title'  => \__( 'Indexing', 'autodescription' ),
						'status' => \The_SEO_Framework\Interpreters\SEOBar::STATE_GOOD,
						'reason' => \__( 'Term may be indexed.', 'autodescription' ),
						'assess' => [
							'base' => \__( 'The robots meta tag allows indexing.', 'autodescription' ),
						],
					],
					'noindex' => [
						'symbol' => \_x( 'I', 'Indexing', 'autodescription' ),
						'title'  => \__( 'Indexing', 'autodescription' ),
						'status' => \The_SEO_Framework\Interpreters\SEOBar::STATE_UNKNOWN,
						'reason' => \__( 'Term may not be indexed.', 'autodescription' ),
						'assess' => [
							'base' => \__( 'The robots meta tag does not allow indexing.', 'autodescription' ),
						],
					],
				],
			]
		);

		$robots_global = static::get_cache( 'general/detect/robotsglobal' );

		if ( $this->query_cache['states']['robotsmeta']['noindex'] ) {
			$item = $cache['defaults']['noindex'];
		} else {
			$item = $cache['defaults']['index'];
		}

		if ( ! $robots_global['blogpublic'] ) {
			$item['status'] = \The_SEO_Framework\Interpreters\SEOBar::STATE_BAD;
			$item['reason'] = $cache['reason']['notpublic'];

			unset( $item['assess']['base'] );

			$item['assess']['notpublic'] = $cache['assess']['notpublic'];

			// Change symbol to grab attention
			$item['symbol'] = '!!!';

			// Let the user resolve this first, everything's moot hereafter.
			return $item;
		}

		if ( $robots_global['site']['noindex'] ) {
			// Status is already set.
			$item['assess']['site'] = $cache['assess']['site'];
		}

		// Test all post types bound to the term. Only if all post types are excluded, set this option.
		$_post_type_noindex_set = [];
		foreach ( $this->query_cache['states']['posttypes'] as $_post_type ) {
			$_post_type_noindex_set[] = ! empty( $robots_global['posttype']['noindex'][ $_post_type ] );
		}
		if ( ! \in_array( false, $_post_type_noindex_set, true ) ) {
			// Status is already set.
			$item['assess']['posttypes'] = $cache['assess']['posttypes'];
		}

		if ( ! empty( $robots_global['taxonomy']['noindex'][ static::$query['taxonomy'] ] ) ) {
			// Status is already set.
			$item['assess']['taxonomy'] = $cache['assess']['taxonomy'];
		}

		if ( 0 !== static::$tsf->s_qubit( $this->query_cache['meta']['noindex'] ) ) {
			// Status is already set.

			// Don't assert posttype nor site as "blocking" if there's an overide.
			unset( $item['assess']['site'], $item['assess']['posttypes'], $item['assess']['taxonomy'] );

			$item['assess']['override'] = $cache['assess']['override'];
		}

		if ( $this->query_cache['meta']['canonical'] ) {
			$permalink = static::$tsf->get_canonical_url( [
				'id'               => static::$query['id'],
				'taxonomy'         => static::$query['taxonomy'],
				'get_custom_field' => false,
			] );
			// We create it because filters may apply.
			$canonical = static::$tsf->get_canonical_url( [
				'id'               => static::$query['id'],
				'taxonomy'         => static::$query['taxonomy'],
				'get_custom_field' => true,
			] );
			if ( $permalink !== $canonical ) {
				$item['status'] = \The_SEO_Framework\Interpreters\SEOBar::STATE_UNKNOWN;
				$item['reason'] = $cache['reason']['canonicalurl'];

				$item['assess']['protected'] = $cache['assess']['canonicalurl'];
			}
		}

		if ( $this->query_cache['states']['isempty'] ) {
			if ( $this->query_cache['states']['robotsmeta']['noindex'] ) {
				// Everything's as intended...
				$item['status'] = \The_SEO_Framework\Interpreters\SEOBar::STATE_UNKNOWN;
				$item['reason'] = $cache['reason']['empty'];

				$item['assess']['empty'] = $cache['assess']['empty'];
			} else {
				// Something's wrong. Maybe override, maybe filter, maybe me.
				$item['status'] = \The_SEO_Framework\Interpreters\SEOBar::STATE_BAD;

				$item['reason']          = $cache['reason']['emptyoverride'];
				$item['assess']['empty'] = $cache['assess']['emptyoverride'];
			}
		}

		if ( ! $this->query_cache['states']['robotsmeta']['noindex'] && $robots_global['hasrobotstxt'] ) {
			// Don't change status, we do not parse the robots.txt file. Merely disclaim.
			$item['assess']['robotstxt'] = $cache['assess']['robotstxt'];
		}

		return $item;
	}

	/**
	 * Runs following tests.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now asserts all taxonomy robots settings.
	 * @see test_title() for return value.
	 *
	 * @return array $item
	 */
	protected function test_following() {

		$cache = static::get_cache( 'term/following/defaults' ) ?: static::set_cache(
			'term/following/defaults',
			[
				'params'   => [],
				'assess'   => [
					'robotstxt' => \__( 'The robots.txt file is nonstandard, and may still direct search engines differently.', 'autodescription' ),
					'notpublic' => \__( 'WordPress discourages crawling via the Reading Settings.', 'autodescription' ),
					'site'      => \__( 'Link following is discouraged for the whole site at the SEO Settings screen.', 'autodescription' ),
					'posttypes' => \__( 'Link following is discouraged for all bound post types to this term at the SEO Settings screen.', 'autodescription' ),
					'taxonomy'  => \__( 'Link following is discouraged for this taxonomy at the SEO Settings screen.', 'autodescription' ),
					'override'  => \__( 'The term SEO meta input overrides the link following state.', 'autodescription' ),
					'noindex'   => \__( 'The term may not be indexed, this may also discourage link following.', 'autodescription' ),
				],
				'reason'   => [
					'notpublic' => \__( 'WordPress overrides the robots directive.', 'autodescription' ),
				],
				'defaults' => [
					'follow'   => [
						'symbol' => \_x( 'F', 'Following', 'autodescription' ),
						'title'  => \__( 'Following', 'autodescription' ),
						'status' => \The_SEO_Framework\Interpreters\SEOBar::STATE_GOOD,
						'reason' => \__( 'Term links may be followed.', 'autodescription' ),
						'assess' => [
							'base' => \__( 'The robots meta tag allows link following.', 'autodescription' ),
						],
					],
					'nofollow' => [
						'symbol' => \_x( 'F', 'Following', 'autodescription' ),
						'title'  => \__( 'Following', 'autodescription' ),
						'status' => \The_SEO_Framework\Interpreters\SEOBar::STATE_UNKNOWN,
						'reason' => \__( 'Term links may not be followed.', 'autodescription' ),
						'assess' => [
							'base' => \__( 'The robots meta tag does not allow link following.', 'autodescription' ),
						],
					],
				],
			]
		);

		$robots_global = static::get_cache( 'general/detect/robotsglobal' );

		if ( $this->query_cache['states']['robotsmeta']['nofollow'] ) {
			$item = $cache['defaults']['nofollow'];
		} else {
			$item = $cache['defaults']['follow'];
		}

		if ( ! $robots_global['blogpublic'] ) {
			$item['status'] = \The_SEO_Framework\Interpreters\SEOBar::STATE_BAD;
			$item['reason'] = $cache['reason']['notpublic'];

			unset( $item['assess']['base'] );

			$item['assess']['notpublic'] = $cache['assess']['notpublic'];

			// Change symbol to grab attention
			$item['symbol'] = '!!!';

			// Let the user resolve this first, everything's moot hereafter.
			return $item;
		}

		if ( $robots_global['site']['nofollow'] ) {
			// Status is already set.
			$item['assess']['site'] = $cache['assess']['site'];
		}

		// Test all post types bound to the term. Only if all post types are excluded, set this option.
		$_post_type_nofollow_set = [];
		foreach ( $this->query_cache['states']['posttypes'] as $_post_type ) {
			$_post_type_nofollow_set[] = ! empty( $robots_global['posttype']['nofollow'][ $_post_type ] );
		}
		if ( ! \in_array( false, $_post_type_nofollow_set, true ) ) {
			// Status is already set.
			$item['assess']['posttypes'] = $cache['assess']['posttypes'];
		}

		if ( ! empty( $robots_global['taxonomy']['nofollow'][ static::$query['taxonomy'] ] ) ) {
			// Status is already set.
			$item['assess']['taxonomy'] = $cache['assess']['taxonomy'];
		}

		if ( 0 !== static::$tsf->s_qubit( $this->query_cache['meta']['nofollow'] ) ) {
			// Status is already set.

			// Don't assert posttype nor site as "blocking" if there's an overide.
			unset( $item['assess']['site'], $item['assess']['posttypes'], $item['assess']['taxonomy'] );

			$item['assess']['override'] = $cache['assess']['override'];
		}

		if ( ! $this->query_cache['states']['robotsmeta']['nofollow'] ) {
			if ( $this->query_cache['states']['robotsmeta']['noindex'] ) {
				$item['status']            = \The_SEO_Framework\Interpreters\SEOBar::STATE_OKAY;
				$item['assess']['noindex'] = $cache['assess']['noindex'];
			}

			if ( $robots_global['hasrobotstxt'] ) {
				// Don't change status, we do not parse the robots.txt file. Merely disclaim.
				$item['assess']['robotstxt'] = $cache['assess']['robotstxt'];
			}
		}

		return $item;
	}

	/**
	 * Runs archiving tests.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now asserts all taxonomy robots settings.
	 * @see test_title() for return value.
	 *
	 * @return array $item
	 */
	protected function test_archiving() {

		$cache = static::get_cache( 'term/archiving/defaults' ) ?: static::set_cache(
			'term/archiving/defaults',
			[
				'params'   => [],
				'assess'   => [
					'robotstxt' => \__( 'The robots.txt file is nonstandard, and may still direct search engines differently.', 'autodescription' ),
					'notpublic' => \__( 'WordPress discourages crawling via the Reading Settings.', 'autodescription' ),
					'site'      => \__( 'Archiving is discouraged for the whole site at the SEO Settings screen.', 'autodescription' ),
					'posttypes' => \__( 'Archiving is discouraged for all bound post types to this term at the SEO Settings screen.', 'autodescription' ),
					'taxonomy'  => \__( 'Archiving is discouraged for this taxonomy at the SEO Settings screen.', 'autodescription' ),
					'override'  => \__( 'The term SEO meta input overrides the archiving state.', 'autodescription' ),
					'noindex'   => \__( 'The term may not be indexed, this may also discourage archiving.', 'autodescription' ),
				],
				'reason'   => [
					'notpublic' => \__( 'WordPress overrides the robots directive.', 'autodescription' ),
				],
				'defaults' => [
					'archive'   => [
						'symbol' => \_x( 'A', 'Archiving', 'autodescription' ),
						'title'  => \__( 'Archiving', 'autodescription' ),
						'status' => \The_SEO_Framework\Interpreters\SEOBar::STATE_GOOD,
						'reason' => \__( 'Term may be archived.', 'autodescription' ),
						'assess' => [
							'base' => \__( 'The robots meta tag allows archiving.', 'autodescription' ),
						],
					],
					'noarchive' => [
						'symbol' => \_x( 'A', 'Archiving', 'autodescription' ),
						'title'  => \__( 'Archiving', 'autodescription' ),
						'status' => \The_SEO_Framework\Interpreters\SEOBar::STATE_UNKNOWN,
						'reason' => \__( 'Term may not be archived.', 'autodescription' ),
						'assess' => [
							'base' => \__( 'The robots meta tag does not allow archiving.', 'autodescription' ),
						],
					],
				],
			]
		);

		$robots_global = static::get_cache( 'general/detect/robotsglobal' );

		if ( $this->query_cache['states']['robotsmeta']['noarchive'] ) {
			$item = $cache['defaults']['noarchive'];
		} else {
			$item = $cache['defaults']['archive'];
		}

		if ( ! $robots_global['blogpublic'] ) {
			$item['status'] = \The_SEO_Framework\Interpreters\SEOBar::STATE_BAD;
			$item['reason'] = $cache['reason']['notpublic'];

			unset( $item['assess']['base'] );

			$item['assess']['notpublic'] = $cache['assess']['notpublic'];

			// Change symbol to grab attention
			$item['symbol'] = '!!!';

			// Let the user resolve this first, everything's moot hereafter.
			return $item;
		}

		if ( $robots_global['site']['noarchive'] ) {
			// Status is already set.
			$item['assess']['site'] = $cache['assess']['site'];
		}

		// Test all post types bound to the term. Only if all post types are excluded, set this option.
		$_post_type_noarchive_set = [];
		foreach ( $this->query_cache['states']['posttypes'] as $_post_type ) {
			$_post_type_noarchive_set[] = ! empty( $robots_global['posttype']['noarchive'][ $_post_type ] );
		}
		if ( ! \in_array( false, $_post_type_noarchive_set, true ) ) {
			// Status is already set.
			$item['assess']['posttypes'] = $cache['assess']['posttypes'];
		}

		if ( ! empty( $robots_global['taxonomy']['noarchive'][ static::$query['taxonomy'] ] ) ) {
			// Status is already set.
			$item['assess']['taxonomy'] = $cache['assess']['taxonomy'];
		}

		if ( 0 !== static::$tsf->s_qubit( $this->query_cache['meta']['noarchive'] ) ) {
			// Status is already set.

			// Don't assert posttype nor site as "blocking" if there's an overide.
			unset( $item['assess']['site'], $item['assess']['posttypes'], $item['assess']['taxonomy'] );

			$item['assess']['override'] = $cache['assess']['override'];
		}

		if ( ! $this->query_cache['states']['robotsmeta']['noarchive'] ) {
			if ( $this->query_cache['states']['robotsmeta']['noindex'] ) {
				$item['status']            = \The_SEO_Framework\Interpreters\SEOBar::STATE_OKAY;
				$item['assess']['noindex'] = $cache['assess']['noindex'];
			}

			if ( $robots_global['hasrobotstxt'] ) {
				// Don't change status, we do not parse the robots.txt file. Merely disclaim.
				$item['assess']['robotstxt'] = $cache['assess']['robotstxt'];
			}
		}

		return $item;
	}

	/**
	 * Runs redirect tests.
	 *
	 * @since 4.0.0
	 * @see test_title() for return value.
	 *
	 * @return array $item
	 */
	protected function test_redirect() {
		if ( empty( $this->query_cache['meta']['redirect'] ) ) {
			return static::get_cache( 'term/redirect/default/0' ) ?: static::set_cache(
				'term/redirect/default/0',
				[
					'symbol' => \_x( 'R', 'Redirect', 'autodescription' ),
					'title'  => \__( 'Redirection', 'autodescription' ),
					'status' => \The_SEO_Framework\Interpreters\SEOBar::STATE_GOOD,
					'reason' => \__( 'Term does not redirect visitors.', 'autodescription' ),
					'assess' => [
						'redirect' => \__( 'All visitors and crawlers may access this page.', 'autodescription' ),
					],
					'meta'   => [
						'blocking' => false,
					],
				]
			);
		} else {
			return static::get_cache( 'term/redirect/default/1' ) ?: static::set_cache(
				'term/redirect/default/1',
				[
					'symbol' => \_x( 'R', 'Redirect', 'autodescription' ),
					'title'  => \__( 'Redirection', 'autodescription' ),
					'status' => \The_SEO_Framework\Interpreters\SEOBar::STATE_UNKNOWN,
					'reason' => \__( 'Term redirects visitors.', 'autodescription' ),
					'assess' => [
						'redirect' => \__( 'All visitors and crawlers are being redirected. So, no other SEO enhancements are effective.', 'autodescription' ),
					],
					'meta'   => [
						'blocking' => true,
					],
				]
			);
		}
	}
}
