<?php
/**
 * @package The_SEO_Framework\Classes\Admin\SEOBar\Builder\User
 * @subpackage The_SEO_Framework\SEOBar
 */

namespace The_SEO_Framework\Admin\SEOBar\Builder;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use const The_SEO_Framework\ROBOTS_ASSERT;

use The_SEO_Framework\{
	Admin\SEOBar\Builder,
	Data,
	Data\Filter\Sanitize,
	Meta,
	RobotsTXT,
};
use The_SEO_Framework\Helper;
use The_SEO_Framework\Helper\{
	Format\Strings,
	Guidelines,
	Migrate,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2026 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Generates the SEO Bar for users.
 *
 * @since 5.2.0
 * @access private
 * @see \The_SEO_Framework\Admin\SEOBar\Builder
 */
final class User extends Main {

	/**
	 * @since 5.2.0
	 * @var string[] All known tests.
	 */
	public static $tests = [ 'title', 'description', 'indexing', 'following', 'archiving', 'redirect' ];

	/**
	 * Primes the cache.
	 *
	 * @since 5.2.0
	 */
	protected function prime_cache() {

		if ( ! static::get_cache( 'general/i18n/textsizeguidelines' ) )
			static::set_cache(
				'general/i18n/textsizeguidelines',
				Guidelines::get_text_size_guidelines_i18n(),
			);

		if ( ! static::get_cache( 'user/detect/robotsglobal' ) )
			static::set_cache(
				'user/detect/robotsglobal',
				[
					'hasrobotstxt' => RobotsTXT\Utils::has_root_robots_txt(),
					'blogpublic'   => Data\Blog::is_public(),
					'site'         => [
						'noindex'   => Data\Plugin::get_option( 'site_noindex' ),
						'nofollow'  => Data\Plugin::get_option( 'site_nofollow' ),
						'noarchive' => Data\Plugin::get_option( 'site_noarchive' ),
					],
					'author'       => [
						'noindex'   => Data\Plugin::get_option( 'author_noindex' ),
						'nofollow'  => Data\Plugin::get_option( 'author_nofollow' ),
						'noarchive' => Data\Plugin::get_option( 'author_noarchive' ),
					],
				],
			);
	}

	/**
	 * Primes the current query cache.
	 *
	 * @since 5.2.0
	 */
	protected function prime_query_cache() {

		$user_id = static::$query['uid'];
		$public  = Helper\User::is_supported( $user_id );

		if ( ! $public ) {
			$this->query_cache = [
				'states' => [
					'public' => false,
				],
			];

			return;
		}

		$this->query_cache = [
			'user'   => Data\User::get_userdata( $user_id ),
			'meta'   => Data\Plugin\User::get_meta( $user_id ),
			'states' => [
				'public'       => true,
				'locale'       => \get_locale(),
				'isempty'      => ! Data\User::has_published_posts( $user_id ),
				'robotsmeta'   => array_merge(
					[
						'noindex'   => false,
						'nofollow'  => false,
						'noarchive' => false,
					],
					Meta\Robots::get_generated_meta(
						[ 'uid' => $user_id ],
						[ 'noindex', 'nofollow', 'noarchive' ],
						ROBOTS_ASSERT,
					),
				),
				// We don't use this... yet. I couldn't find a way to properly implement the assertions in the right order.
				// The asserter should be leading, but the SEO Bar should be readable.
				'robotsassert' => Meta\Robots::get_collected_meta_assertions(),
			],
		];
	}

	/**
	 * Tests for blocking redirection.
	 *
	 * @since 5.2.0
	 *
	 * @return bool True if there's a blocking redirect, false otherwise.
	 */
	protected function has_blocking_redirect() {
		return ! empty( $this->query_cache['meta']['redirect'] );
	}

	/**
	 * Tests whether indexing replaces the other tests.
	 *
	 * @since 5.2.0
	 *
	 * @return bool True when the indexing block replaces the other tests, false otherwise.
	 */
	protected function has_blocking_noindex() {
		return empty( $this->query_cache['states']['public'] );
	}

	/**
	 * Runs title tests.
	 *
	 * @since 5.2.0
	 *
	 * @return array $item {
	 *     The SEO Bar title item.
	 *
	 *     @type string    $symbol The displayed symbol that identifies your bar.
	 *     @type string    $title  The title of the assessment.
	 *     @type int <bit> $status See `STATE_*` class constants.
	 *     @type string    $reason The final assessment: The reason for the $status. The latest state-changing reason is used.
	 *     @type string[]  $assess The assessments on why the reason is set. Keep it short and concise!
	 *                             Does not accept HTML for performant ARIA support.
	 * }
	 */
	protected function test_title() {

		$cache =
			   static::get_cache( 'user/title/defaults' )
			?? static::set_cache(
				'user/title/defaults',
				[
					'params'   => [
						'untitled'        => Meta\Title::get_untitled_title(),
						'blogname_quoted' => preg_quote(
							Sanitize::normalize_metadata_content_for_strcmp( Data\Blog::get_public_blog_name() ),
							'/',
						),
						/* translators: 1 = An assessment, 2 = Disclaimer, e.g. "take it with a grain of salt" */
						'disclaim'        => \__( '%1$s (%2$s)', 'autodescription' ),
						'estimated'       => \__( 'Estimated from the number of characters found. The pixel counter asserts the true length.', 'autodescription' ),
					],
					'assess'   => [
						'empty'      => \__( 'No title could be fetched.', 'autodescription' ),
						'untitled'   => \sprintf(
							/* translators: %s = "Untitled" */
							\__( 'No title could be fetched, "%s" is used instead.', 'autodescription' ),
							Meta\Title::get_untitled_title(),
						),
						'prefixed'   => \__( 'An author label prefix is automatically added which increases the length.', 'autodescription' ),
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
							'status' => Builder::STATE_GOOD,
							'reason' => \__( 'Automatically generated.', 'autodescription' ),
							'assess' => [
								'base' => \__( "It's built from the author's display name.", 'autodescription' ),
							],
						],
						'custom'    => [
							'symbol' => \_x( 'T', 'Title', 'autodescription' ),
							'title'  => \__( 'Title', 'autodescription' ),
							'status' => Builder::STATE_GOOD,
							'reason' => \__( 'Obtained from author SEO meta input.', 'autodescription' ),
							'assess' => [
								'base' => \__( "It's built from author SEO meta input.", 'autodescription' ),
							],
						],
					],
				],
			);

		$generator_args = [ 'uid' => static::$query['uid'] ];

		$title_part = Meta\Title::get_bare_custom_title( $generator_args );

		if ( \strlen( $title_part ) ) {
			$item = $cache['defaults']['custom'];

			if ( Migrate::text_has_unprocessed_syntax( $title_part, false ) ) {
				$item['status']           = Builder::STATE_BAD;
				$item['reason']           = $cache['reason']['syntax'];
				$item['assess']['syntax'] = $cache['assess']['syntax'];

				// Further assessments must be made later. Halt assertion here to prevent confusion.
				return $item;
			}
		} else {
			$item = $cache['defaults']['generated'];

			if ( Meta\Title\Conditions::use_generated_archive_prefix( $this->query_cache['user'] ) )
				$item['assess']['prefixed'] = $cache['assess']['prefixed'];

			$title_part = Meta\Title::get_bare_generated_title( $generator_args );
		}

		if ( ! \strlen( $title_part ) ) {
			$item['status']          = Builder::STATE_BAD;
			$item['reason']          = $cache['reason']['incomplete'];
			$item['assess']['empty'] = $cache['assess']['empty'];

			// Further assessments must be made later. Halt assertion here to prevent confusion.
			return $item;
		} elseif ( $title_part === $cache['params']['untitled'] ) {
			$item['status']             = Builder::STATE_BAD;
			$item['reason']             = $cache['reason']['incomplete'];
			$item['assess']['untitled'] = $cache['assess']['untitled'];

			// Further assessments must be made later. Halt assertion here to prevent confusion.
			return $item;
		}

		$title = $title_part;

		if ( Meta\Title\Conditions::use_branding( $generator_args ) ) {
			$_title_before = $title;
			$title         = Meta\Title::add_branding( $title, $generator_args );

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

		$strcmp_title = Sanitize::normalize_metadata_content_for_strcmp( $title );

		$brand_count = \strlen( $cache['params']['blogname_quoted'] )
			? preg_match_all(
				"/{$cache['params']['blogname_quoted']}/ui",
				$strcmp_title,
				$matches,
			)
			: 0;

		if ( ! $brand_count ) {
			// Override branding state.
			$item['status']             = Builder::STATE_UNKNOWN;
			$item['reason']             = $cache['reason']['notbranded'];
			$item['assess']['branding'] = $cache['assess']['branding']['not'];
		} elseif ( $brand_count > 1 ) {
			$item['status']               = Builder::STATE_BAD;
			$item['reason']               = $cache['reason']['duplicated'];
			$item['assess']['duplicated'] = $cache['assess']['duplicated'];

			// Further assessments must be made later. Halt assertion here to prevent confusion.
			return $item;
		}

		$title_len = mb_strlen( $strcmp_title );

		$guidelines      = Guidelines::get_text_size_guidelines(
			$this->query_cache['states']['locale'],
		)['title']['search']['chars'];
		$guidelines_i18n = static::get_cache( 'general/i18n/textsizeguidelines' );

		if ( $title_len < $guidelines['lower'] ) {
			$item['status'] = Builder::STATE_BAD;
			$item['reason'] = $guidelines_i18n['shortdot']['farTooShort'];
			$length_i18n    = $guidelines_i18n['long']['farTooShort'];
		} elseif ( $title_len < $guidelines['goodLower'] ) {
			$item['status'] = Builder::STATE_OKAY;
			$item['reason'] = $guidelines_i18n['shortdot']['tooShort'];
			$length_i18n    = $guidelines_i18n['long']['tooShort'];
		} elseif ( $title_len > $guidelines['upper'] ) {
			$item['status'] = Builder::STATE_BAD;
			$item['reason'] = $guidelines_i18n['shortdot']['farTooLong'];
			$length_i18n    = $guidelines_i18n['long']['farTooLong'];
		} elseif ( $title_len > $guidelines['goodUpper'] ) {
			$item['status'] = Builder::STATE_OKAY;
			$item['reason'] = $guidelines_i18n['shortdot']['tooLong'];
			$length_i18n    = $guidelines_i18n['long']['tooLong'];
		} else {
			// Use unaltered reason and status.
			$length_i18n = $guidelines_i18n['long']['good'];
		}

		$item['assess']['length'] = \sprintf(
			$cache['params']['disclaim'],
			$length_i18n,
			$cache['params']['estimated'],
		);

		return $item;
	}

	/**
	 * Runs description tests.
	 *
	 * @since 5.2.0
	 *
	 * @return array $item {
	 *     The SEO Bar description item.
	 *
	 *     @type string    $symbol The displayed symbol that identifies your bar.
	 *     @type string    $title  The title of the assessment.
	 *     @type int <bit> $status See `STATE_*` class constants.
	 *     @type string    $reason The final assessment: The reason for the $status. The latest state-changing reason is used.
	 *     @type string[]  $assess The assessments on why the reason is set. Keep it short and concise!
	 *                             Does not accept HTML for performant ARIA support.
	 * }
	 */
	protected function test_description() {

		$cache =
			   static::get_cache( 'user/description/defaults' )
			?? static::set_cache(
				'user/description/defaults',
				[
					'params'   => [
						/* translators: 1 = An assessment, 2 = Disclaimer, e.g. "take it with a grain of salt" */
						'disclaim'   => \__( '%1$s (%2$s)', 'autodescription' ),
						'estimated'  => \__( 'Estimated from the number of characters found. The pixel counter asserts the true length.', 'autodescription' ),
						/**
						 * @since 2.6.0
						 * @param int $short_word_length The minimum string length of words to find as dupes.
						 */
						'dupe_short' => (int) \apply_filters( 'the_seo_framework_bother_me_desc_length', 3 ),
					],
					'assess'   => [
						'empty'  => \__( 'No description could be generated.', 'autodescription' ),
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
							'status' => Builder::STATE_GOOD,
							'reason' => \__( 'Automatically generated.', 'autodescription' ),
							'assess' => [
								'base' => \__( "It's built from the author's biographical info.", 'autodescription' ),
							],
						],
						'emptynoauto' => [
							'symbol' => \_x( 'D', 'Description', 'autodescription' ),
							'title'  => \__( 'Description', 'autodescription' ),
							'status' => Builder::STATE_UNKNOWN,
							'reason' => \__( 'Empty.', 'autodescription' ),
							'assess' => [
								'noauto' => \__( 'No biographical info is set.', 'autodescription' ),
							],
						],
						'custom'      => [
							'symbol' => \_x( 'D', 'Description', 'autodescription' ),
							'title'  => \__( 'Description', 'autodescription' ),
							'status' => Builder::STATE_GOOD,
							'reason' => \__( 'Obtained from the author SEO meta input.', 'autodescription' ),
							'assess' => [
								'base' => \__( "It's built from the author SEO meta input.", 'autodescription' ),
							],
						],
					],
				],
			);

		$generator_args = [ 'uid' => static::$query['uid'] ];

		$desc = Meta\Description::get_custom_description( $generator_args );

		if ( \strlen( $desc ) ) {
			$item = $cache['defaults']['custom'];

			if ( Migrate::text_has_unprocessed_syntax( $desc ) ) {
				$item['status']           = Builder::STATE_BAD;
				$item['reason']           = $cache['reason']['syntax'];
				$item['assess']['syntax'] = $cache['assess']['syntax'];

				// Further assessments must be made later. Halt assertion here to prevent confusion.
				return $item;
			}
		} elseif ( ! Meta\Description::may_generate( $generator_args ) ) {
			$item = $cache['defaults']['emptynoauto'];

			// No description is found. There's no need to continue parsing.
			return $item;
		} else {
			$item = $cache['defaults']['generated'];

			$desc = Meta\Description::get_generated_description( $generator_args );

			if ( ! \strlen( $desc ) ) {
				$item['status'] = Builder::STATE_UNDEFINED;
				$item['reason'] = $cache['reason']['empty'];

				// This is now inaccurate, purge it.
				unset( $item['assess']['base'] );

				$item['assess']['empty'] = $cache['assess']['empty'];

				// No description is found. There's no need to continue parsing.
				return $item;
			}
		}

		// Fetch words that are outputted more than 3 times.
		$repeated_words = Strings::get_word_count(
			$desc,
			[ 'short_word_length' => $cache['params']['dupe_short'] ],
		);

		if ( $repeated_words ) {
			$dupes = [];

			foreach ( $repeated_words as $_repeated_word ) {
				// Keep abbreviations... WordPress, make multibyte support mandatory already.
				// $_word = ctype_upper( reset( $_repeated_word ) ) ? reset( $_repeated_word ) : mb_strtolower( reset( $_repeated_word ) );

				$dupes[] = \sprintf(
					/* translators: 1: Word found, 2: Occurrences */
					\esc_attr__( '&#8220;%1$s&#8221; is used %2$d times.', 'autodescription' ),
					\esc_attr( array_key_first( $_repeated_word ) ),
					array_first( $_repeated_word ), // escaped in sprintf %d.
				);
			}

			$item['assess']['dupe'] = implode( ' ', $dupes );

			$max = max( $repeated_words );
			$max = reset( $max );

			// Warn when more than 3x triplet+/quintet+ words are found.
			if ( $max > 3 || \count( $repeated_words ) > 1 ) {
				// This must be resolved.
				$item['reason'] = $cache['reason']['foundmanydupe'];
				$item['status'] = Builder::STATE_BAD;

				return $item;
			} else {
				$item['reason'] = $cache['reason']['founddupe'];
				$item['status'] = Builder::STATE_OKAY;
			}
		}

		$guidelines      = Guidelines::get_text_size_guidelines(
			$this->query_cache['states']['locale'],
		)['description']['search']['chars'];
		$guidelines_i18n = static::get_cache( 'general/i18n/textsizeguidelines' );

		$desc_len = mb_strlen( Sanitize::normalize_metadata_content_for_strcmp( $desc ) );

		if ( $desc_len < $guidelines['lower'] ) {
			$item['status'] = Builder::STATE_BAD;
			$item['reason'] = $guidelines_i18n['shortdot']['farTooShort'];
			$length_i18n    = $guidelines_i18n['long']['farTooShort'];
		} elseif ( $desc_len < $guidelines['goodLower'] ) {
			$item['status'] = Builder::STATE_OKAY;
			$item['reason'] = $guidelines_i18n['shortdot']['tooShort'];
			$length_i18n    = $guidelines_i18n['long']['tooShort'];
		} elseif ( $desc_len > $guidelines['upper'] ) {
			$item['status'] = Builder::STATE_BAD;
			$item['reason'] = $guidelines_i18n['shortdot']['farTooLong'];
			$length_i18n    = $guidelines_i18n['long']['farTooLong'];
		} elseif ( $desc_len > $guidelines['goodUpper'] ) {
			$item['status'] = Builder::STATE_OKAY;
			$item['reason'] = $guidelines_i18n['shortdot']['tooLong'];
			$length_i18n    = $guidelines_i18n['long']['tooLong'];
		} else {
			// Use unaltered reason and status.
			$length_i18n = $guidelines_i18n['long']['good'];
		}

		$item['assess']['length'] = \sprintf(
			$cache['params']['disclaim'],
			$length_i18n,
			$cache['params']['estimated'],
		);

		return $item;
	}

	/**
	 * Runs indexing tests.
	 *
	 * @since 5.2.0
	 *
	 * @return array $item {
	 *     The SEO Bar robots indexing item.
	 *
	 *     @type string    $symbol The displayed symbol that identifies your bar.
	 *     @type string    $title  The title of the assessment.
	 *     @type int <bit> $status See `STATE_*` class constants.
	 *     @type string    $reason The final assessment: The reason for the $status. The latest state-changing reason is used.
	 *     @type string[]  $assess The assessments on why the reason is set. Keep it short and concise!
	 *                             Does not accept HTML for performant ARIA support.
	 * }
	 */
	protected function test_indexing() {

		if ( empty( $this->query_cache['states']['public'] ) )
			return static::get_cache( 'user/indexing/notpublic' )
				?? static::set_cache(
					'user/indexing/notpublic',
					[
						'symbol' => \_x( 'I', 'Indexing', 'autodescription' ),
						'title'  => \__( 'Indexing', 'autodescription' ),
						'status' => Builder::STATE_UNKNOWN,
						'reason' => \__( 'Author may not be indexed.', 'autodescription' ),
						'assess' => [
							'notpublic' => \__( 'This user is not a public author.', 'autodescription' ),
						],
					],
				);

		$cache =
			   static::get_cache( 'user/indexing/defaults' )
			?? static::set_cache(
				'user/indexing/defaults',
				[
					'params'   => [],
					'assess'   => [
						'robotstxt'     => \__( 'The robots.txt file is nonstandard, and may still direct search engines differently.', 'autodescription' ),
						'notpublic'     => \__( 'WordPress discourages crawling via the Reading Settings.', 'autodescription' ),
						'site'          => \__( 'Indexing is discouraged for the whole site at the SEO Settings screen.', 'autodescription' ),
						'author'        => \__( 'Indexing is discouraged for author archives at the SEO Settings screen.', 'autodescription' ),
						'override'      => \__( 'The author SEO meta input overrides the indexing state.', 'autodescription' ),
						'empty'         => \__( 'The author has no posts, so indexing is disabled.', 'autodescription' ),
						'emptyoverride' => \__( 'The author has no posts, so indexing should be disabled.', 'autodescription' ),
						'canonicalurl'  => \__( 'A custom canonical URL is set that points to another page.', 'autodescription' ),
					],
					'reason'   => [
						'notpublic'     => \__( 'WordPress overrides the robots directive.', 'autodescription' ),
						'empty'         => \__( 'The author has no posts.', 'autodescription' ),
						'emptyoverride' => \__( 'The author has no posts, yet is still indexed.', 'autodescription' ),
						'canonicalurl'  => \__( 'The canonical URL points to another page.', 'autodescription' ),
					],
					'defaults' => [
						'index'   => [
							'symbol' => \_x( 'I', 'Indexing', 'autodescription' ),
							'title'  => \__( 'Indexing', 'autodescription' ),
							'status' => Builder::STATE_GOOD,
							'reason' => \__( 'Author may be indexed.', 'autodescription' ),
							'assess' => [
								'base' => \__( 'The robots meta tag allows indexing.', 'autodescription' ),
							],
						],
						'noindex' => [
							'symbol' => \_x( 'I', 'Indexing', 'autodescription' ),
							'title'  => \__( 'Indexing', 'autodescription' ),
							'status' => Builder::STATE_UNKNOWN,
							'reason' => \__( 'Author may not be indexed.', 'autodescription' ),
							'assess' => [
								'base' => \__( 'The robots meta tag does not allow indexing.', 'autodescription' ),
							],
						],
					],
				],
			);

		$robots_global = static::get_cache( 'user/detect/robotsglobal' );

		if ( $this->query_cache['states']['robotsmeta']['noindex'] ) {
			$item = $cache['defaults']['noindex'];
		} else {
			$item = $cache['defaults']['index'];
		}

		if ( ! $robots_global['blogpublic'] ) {
			$item['status'] = Builder::STATE_BAD;
			$item['reason'] = $cache['reason']['notpublic'];

			unset( $item['assess']['base'] );

			$item['assess']['notpublic'] = $cache['assess']['notpublic'];

			// Change symbol to grab attention
			$item['symbol'] = '!!!';

			// Let the user resolve this first, everything's moot hereafter.
			return $item;
		}

		if ( $robots_global['site']['noindex'] )
			$item['assess']['site'] = $cache['assess']['site'];

		if ( $robots_global['author']['noindex'] )
			$item['assess']['author'] = $cache['assess']['author'];

		if ( 0 !== Sanitize::qubit( $this->query_cache['meta']['noindex'] ) ) {
			// Don't assert site nor author archives as blocking if there's an override.
			unset( $item['assess']['site'], $item['assess']['author'] );

			$item['assess']['override'] = $cache['assess']['override'];
		}

		if ( $this->query_cache['meta']['canonical'] ) {
			$generator_args = [ 'uid' => static::$query['uid'] ];

			$permalink = Meta\URI::get_generated_url( $generator_args );
			$canonical = Meta\URI::get_canonical_url( $generator_args );

			if ( $permalink !== $canonical ) {
				$item['status'] = Builder::STATE_UNKNOWN;
				$item['reason'] = $cache['reason']['canonicalurl'];

				$item['assess']['protected'] = $cache['assess']['canonicalurl'];
			}
		}

		if ( $this->query_cache['states']['isempty'] ) {
			if ( $this->query_cache['states']['robotsmeta']['noindex'] ) {
				// Everything's as intended...
				$item['status'] = Builder::STATE_UNKNOWN;
				$item['reason'] = $cache['reason']['empty'];

				$item['assess']['empty'] = $cache['assess']['empty'];
			} else {
				// Something's wrong. Maybe override, maybe filter, maybe me.
				$item['status'] = Builder::STATE_BAD;

				$item['reason']          = $cache['reason']['emptyoverride'];
				$item['assess']['empty'] = $cache['assess']['emptyoverride'];
			}
		}

		if (
			   ! $this->query_cache['states']['robotsmeta']['noindex']
			&& $robots_global['hasrobotstxt']
		) {
			// Don't change status, we do not parse the robots.txt file. Merely disclaim.
			$item['assess']['robotstxt'] = $cache['assess']['robotstxt'];
		}

		return $item;
	}

	/**
	 * Runs following tests.
	 *
	 * @since 5.2.0
	 *
	 * @return array $item {
	 *     The SEO Bar robots following item.
	 *
	 *     @type string    $symbol The displayed symbol that identifies your bar.
	 *     @type string    $title  The title of the assessment.
	 *     @type int <bit> $status See `STATE_*` class constants.
	 *     @type string    $reason The final assessment: The reason for the $status. The latest state-changing reason is used.
	 *     @type string[]  $assess The assessments on why the reason is set. Keep it short and concise!
	 *                             Does not accept HTML for performant ARIA support.
	 * }
	 */
	protected function test_following() {

		$cache =
			   static::get_cache( 'user/following/defaults' )
			?? static::set_cache(
				'user/following/defaults',
				[
					'params'   => [],
					'assess'   => [
						'robotstxt' => \__( 'The robots.txt file is nonstandard, and may still direct search engines differently.', 'autodescription' ),
						'notpublic' => \__( 'WordPress discourages crawling via the Reading Settings.', 'autodescription' ),
						'site'      => \__( 'Link following is discouraged for the whole site at the SEO Settings screen.', 'autodescription' ),
						'author'    => \__( 'Link following is discouraged for author archives at the SEO Settings screen.', 'autodescription' ),
						'override'  => \__( 'The author SEO meta input overrides the link following state.', 'autodescription' ),
						'noindex'   => \__( 'The author may not be indexed, this may also discourage link following.', 'autodescription' ),
					],
					'reason'   => [
						'notpublic' => \__( 'WordPress overrides the robots directive.', 'autodescription' ),
					],
					'defaults' => [
						'follow'   => [
							'symbol' => \_x( 'F', 'Following', 'autodescription' ),
							'title'  => \__( 'Following', 'autodescription' ),
							'status' => Builder::STATE_GOOD,
							'reason' => \__( 'Author links may be followed.', 'autodescription' ),
							'assess' => [
								'base' => \__( 'The robots meta tag allows link following.', 'autodescription' ),
							],
						],
						'nofollow' => [
							'symbol' => \_x( 'F', 'Following', 'autodescription' ),
							'title'  => \__( 'Following', 'autodescription' ),
							'status' => Builder::STATE_UNKNOWN,
							'reason' => \__( 'Author links may not be followed.', 'autodescription' ),
							'assess' => [
								'base' => \__( 'The robots meta tag does not allow link following.', 'autodescription' ),
							],
						],
					],
				],
			);

		$robots_global = static::get_cache( 'user/detect/robotsglobal' );

		if ( $this->query_cache['states']['robotsmeta']['nofollow'] ) {
			$item = $cache['defaults']['nofollow'];
		} else {
			$item = $cache['defaults']['follow'];
		}

		if ( ! $robots_global['blogpublic'] ) {
			$item['status'] = Builder::STATE_BAD;
			$item['reason'] = $cache['reason']['notpublic'];

			unset( $item['assess']['base'] );

			$item['assess']['notpublic'] = $cache['assess']['notpublic'];

			// Change symbol to grab attention
			$item['symbol'] = '!!!';

			// Let the user resolve this first, everything's moot hereafter.
			return $item;
		}

		if ( $robots_global['site']['nofollow'] )
			$item['assess']['site'] = $cache['assess']['site'];

		if ( $robots_global['author']['nofollow'] )
			$item['assess']['author'] = $cache['assess']['author'];

		if ( 0 !== Sanitize::qubit( $this->query_cache['meta']['nofollow'] ) ) {
			// Don't assert site nor author archives as blocking if there's an override.
			unset( $item['assess']['site'], $item['assess']['author'] );

			$item['assess']['override'] = $cache['assess']['override'];
		}

		if ( ! $this->query_cache['states']['robotsmeta']['nofollow'] ) {
			if ( $this->query_cache['states']['robotsmeta']['noindex'] ) {
				$item['status']            = Builder::STATE_OKAY;
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
	 * @since 5.2.0
	 *
	 * @return array $item {
	 *     The SEO Bar robots archiving item.
	 *
	 *     @type string    $symbol The displayed symbol that identifies your bar.
	 *     @type string    $title  The title of the assessment.
	 *     @type int <bit> $status See `STATE_*` class constants.
	 *     @type string    $reason The final assessment: The reason for the $status. The latest state-changing reason is used.
	 *     @type string[]  $assess The assessments on why the reason is set. Keep it short and concise!
	 *                             Does not accept HTML for performant ARIA support.
	 * }
	 */
	protected function test_archiving() {

		$cache =
			   static::get_cache( 'user/archiving/defaults' )
			?? static::set_cache(
				'user/archiving/defaults',
				[
					'params'   => [],
					'assess'   => [
						'robotstxt' => \__( 'The robots.txt file is nonstandard, and may still direct search engines differently.', 'autodescription' ),
						'notpublic' => \__( 'WordPress discourages crawling via the Reading Settings.', 'autodescription' ),
						'site'      => \__( 'Archiving is discouraged for the whole site at the SEO Settings screen.', 'autodescription' ),
						'author'    => \__( 'Archiving is discouraged for author archives at the SEO Settings screen.', 'autodescription' ),
						'override'  => \__( 'The author SEO meta input overrides the archiving state.', 'autodescription' ),
						'noindex'   => \__( 'The author may not be indexed, this may also discourage archiving.', 'autodescription' ),
					],
					'reason'   => [
						'notpublic' => \__( 'WordPress overrides the robots directive.', 'autodescription' ),
					],
					'defaults' => [
						'archive'   => [
							'symbol' => \_x( 'A', 'Archiving', 'autodescription' ),
							'title'  => \__( 'Archiving', 'autodescription' ),
							'status' => Builder::STATE_GOOD,
							'reason' => \__( 'Author may be archived.', 'autodescription' ),
							'assess' => [
								'base' => \__( 'The robots meta tag allows archiving.', 'autodescription' ),
							],
						],
						'noarchive' => [
							'symbol' => \_x( 'A', 'Archiving', 'autodescription' ),
							'title'  => \__( 'Archiving', 'autodescription' ),
							'status' => Builder::STATE_UNKNOWN,
							'reason' => \__( 'Author may not be archived.', 'autodescription' ),
							'assess' => [
								'base' => \__( 'The robots meta tag does not allow archiving.', 'autodescription' ),
							],
						],
					],
				],
			);

		$robots_global = static::get_cache( 'user/detect/robotsglobal' );

		if ( $this->query_cache['states']['robotsmeta']['noarchive'] ) {
			$item = $cache['defaults']['noarchive'];
		} else {
			$item = $cache['defaults']['archive'];
		}

		if ( ! $robots_global['blogpublic'] ) {
			$item['status'] = Builder::STATE_BAD;
			$item['reason'] = $cache['reason']['notpublic'];

			unset( $item['assess']['base'] );

			$item['assess']['notpublic'] = $cache['assess']['notpublic'];

			// Change symbol to grab attention
			$item['symbol'] = '!!!';

			// Let the user resolve this first, everything's moot hereafter.
			return $item;
		}

		if ( $robots_global['site']['noarchive'] )
			$item['assess']['site'] = $cache['assess']['site'];

		if ( $robots_global['author']['noarchive'] )
			$item['assess']['author'] = $cache['assess']['author'];

		if ( 0 !== Sanitize::qubit( $this->query_cache['meta']['noarchive'] ) ) {
			// Don't assert site nor author archives as blocking if there's an override.
			unset( $item['assess']['site'], $item['assess']['author'] );

			$item['assess']['override'] = $cache['assess']['override'];
		}

		if ( ! $this->query_cache['states']['robotsmeta']['noarchive'] ) {
			if ( $this->query_cache['states']['robotsmeta']['noindex'] ) {
				$item['status']            = Builder::STATE_OKAY;
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
	 * @since 5.2.0
	 *
	 * @return array $item {
	 *     The SEO Bar redirect item.
	 *
	 *     @type string    $symbol The displayed symbol that identifies your bar.
	 *     @type string    $title  The title of the assessment.
	 *     @type int <bit> $status See `STATE_*` class constants.
	 *     @type string    $reason The final assessment: The reason for the $status. The latest state-changing reason is used.
	 *     @type string[]  $assess The assessments on why the reason is set. Keep it short and concise!
	 *                             Does not accept HTML for performant ARIA support.
	 * }
	 */
	protected function test_redirect() {

		if ( $this->has_blocking_redirect() )
			return static::get_cache( 'user/redirect/default/1' )
				?? static::set_cache(
					'user/redirect/default/1',
					[
						'symbol' => \_x( 'R', 'Redirect', 'autodescription' ),
						'title'  => \__( 'Redirection', 'autodescription' ),
						'status' => Builder::STATE_UNKNOWN,
						'reason' => \__( 'Author redirects visitors.', 'autodescription' ),
						'assess' => [
							'redirect' => \__( 'All visitors and crawlers are being redirected. So, no other SEO enhancements are effective.', 'autodescription' ),
						],
						'meta'   => [
							'blocking' => true,
						],
					],
				);

		return static::get_cache( 'user/redirect/default/0' )
			?? static::set_cache(
				'user/redirect/default/0',
				[
					'symbol' => \_x( 'R', 'Redirect', 'autodescription' ),
					'title'  => \__( 'Redirection', 'autodescription' ),
					'status' => Builder::STATE_GOOD,
					'reason' => \__( 'Author does not redirect visitors.', 'autodescription' ),
					'assess' => [
						'redirect' => \__( 'All visitors and crawlers may access this page.', 'autodescription' ),
					],
					'meta'   => [
						'blocking' => false,
					],
				],
			);
	}
}
