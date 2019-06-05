<?php
/**
 * @package The_SEO_Framework\Classes\Builders
 * @subpackage The_SEO_Framework\Builders
 */

namespace The_SEO_Framework\Builders;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * Generates the SEO Bar for posts.
 *
 * @since 3.3.0
 *
 * @access private
 * @internal
 * @see \The_SEO_Framework\Interpreters\SeoBar
 *      Use \The_SEO_Framework\Interpreters\SeoBar::generate_bar() instead.
 */
final class SeoBar_Term extends SeoBar {

	/**
	 * @since 3.3.0
	 * @access private
	 * @abstract
	 * @var array All known tests.
	 */
	public static $tests = [ 'title', 'description', 'indexing', 'following', 'archiving', 'redirect' ];

	/**
	 * Tests for blocking redirection.
	 *
	 * @since 3.3.0
	 *
	 * @return bool True if there's a blocking redirect, false otherwise.
	 */
	protected function has_blocking_redirect() {
		$data = static::$tsf->get_term_meta( static::$query['id'] );
		return ! empty( $data['redirect'] );
	}

	/**
	 * Runs title tests.
	 *
	 * @since 3.3.0
	 *
	 * @return array $item : {
	 *    string $symbol : Required. The displayed symbol that identifies your bar.
	 *    string $title  : Required. The title of the assessment.
	 *    string $status : Required. Accepts 'good', 'okay', 'bad', 'unknown'.
	 *    string $reason : Required. The final assessment: The reason for the $status.
	 *    string $assess : Required. The assessments on why the reason is set. Keep it short and concise!
	 *                               Does not accept HTML for performant ARIA support.
	 * }
	 */
	protected function test_title() {
		return [
			'symbol' => '?',
			'title'  => \__( 'Unknown', 'autodescription' ),
			'status' => \The_SEO_Framework\Interpreters\SeoBar::STATE_UNKNOWN,
			'reason' => \__( 'Unknown test.', 'autodescription' ),
			'assess' => [
				'redirect' => \__( 'Test is unkown.', 'autodescription' ),
			],
		];
	}

	/**
	 * Runs title tests.
	 *
	 * @since 3.3.0
	 * @see test_title() for return value.
	 *
	 * @return array $item
	 */
	protected function test_description() {
		return [
			'symbol' => '?',
			'title'  => \__( 'Unknown', 'autodescription' ),
			'status' => \The_SEO_Framework\Interpreters\SeoBar::STATE_UNKNOWN,
			'reason' => \__( 'Unknown test.', 'autodescription' ),
			'assess' => [
				'redirect' => \__( 'Test is unkown.', 'autodescription' ),
			],
		];
	}

	/**
	 * Runs description tests.
	 *
	 * @since 3.3.0
	 * @see test_title() for return value.
	 *
	 * @return array $item
	 */
	protected function test_indexing() {
		return [
			'symbol' => '?',
			'title'  => \__( 'Unknown', 'autodescription' ),
			'status' => \The_SEO_Framework\Interpreters\SeoBar::STATE_UNKNOWN,
			'reason' => \__( 'Unknown test.', 'autodescription' ),
			'assess' => [
				'redirect' => \__( 'Test is unkown.', 'autodescription' ),
			],
		];
	}

	/**
	 * Runs following tests.
	 *
	 * @since 3.3.0
	 * @see test_title() for return value.
	 *
	 * @return array $item
	 */
	protected function test_following() {
		return [
			'symbol' => '?',
			'title'  => \__( 'Unknown', 'autodescription' ),
			'status' => \The_SEO_Framework\Interpreters\SeoBar::STATE_UNKNOWN,
			'reason' => \__( 'Unknown test.', 'autodescription' ),
			'assess' => [
				'redirect' => \__( 'Test is unkown.', 'autodescription' ),
			],
		];
	}

	/**
	 * Runs archiving tests.
	 *
	 * @since 3.3.0
	 * @see test_title() for return value.
	 *
	 * @return array $item
	 */
	protected function test_archiving() {
		return [
			'symbol' => '?',
			'title'  => \__( 'Unknown', 'autodescription' ),
			'status' => \The_SEO_Framework\Interpreters\SeoBar::STATE_UNKNOWN,
			'reason' => \__( 'Unknown test.', 'autodescription' ),
			'assess' => [
				'redirect' => \__( 'Test is unkown.', 'autodescription' ),
			],
		];
	}

	/**
	 * Runs redirect tests.
	 *
	 * @since 3.3.0
	 * @see test_title() for return value.
	 *
	 * @return array $item
	 */
	protected function test_redirect() {

		$data = \the_seo_framework()->get_term_meta( static::$query['id'] );

		if ( empty( $data['redirect'] ) ) {
			return static::get_cache( 'term\redirect\default\0' ) ?: static::set_cache(
				'term\redirect\default\0',
				[
					'symbol' => 'R',
					'title'  => \__( 'Redirection', 'autodescription' ),
					'status' => \The_SEO_Framework\Interpreters\SeoBar::STATE_GOOD,
					'reason' => \__( 'Term does not redirect visitors.', 'autodescription' ),
					'assess' => [
						'redirect' => \__( 'All visitors and crawlers may access this page.', 'autodescription' ),
					],
				]
			);
		} else {
			return static::get_cache( 'term\redirect\default\1' ) ?: static::set_cache(
				'term\redirect\default\1',
				[
					'symbol' => 'R',
					'title'  => \__( 'Redirection', 'autodescription' ),
					'status' => \The_SEO_Framework\Interpreters\SeoBar::STATE_UNKNOWN,
					'reason' => \__( 'Term redirects visitors.', 'autodescription' ),
					'assess' => [
						'redirect' => \__( 'All visitors and crawlers are being redirected. So, no other SEO enhancements are effective.', 'autodescription' ),
					],
				]
			);
		}
	}
}
