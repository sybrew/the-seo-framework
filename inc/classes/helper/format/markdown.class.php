<?php
/**
 * @package The_SEO_Framework\Classes\Helper\Format\Markdown
 * @subpackage The_SEO_Framework\Formatting
 */

namespace The_SEO_Framework\Helper\Format;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2021 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds methods for Markdown conversion.
 *
 * @since 4.1.4
 * @since 5.0.0 Moved from `\The_SEO_Framework\Interpreters\Markdown`.
 *
 * @NOTE to self: This is also used in XHTML configurations. Keep it strict!
 *
 * @access protected
 *         Use tsf()->format()->markdown() instead.
 */
class Markdown {

	/**
	 * Converts markdown text into HTML.
	 * Does not support list or block elements. Only inline statements.
	 *
	 * Note: This code has been rightfully stolen from the Extension Manager plugin (sorry Sybre!).
	 *
	 * @since 4.1.4
	 * @since 4.2.8 No longer blocks text with either { or } from being parsed.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Interpreters\Markdown`.
	 * @link https://wordpress.org/plugins/about/readme.txt
	 *
	 * @param string $text    The text that might contain markdown. Expected to be escaped.
	 * @param array  $convert The markdown style types wished to be converted.
	 *                        If left empty, it will convert all.
	 * @param array  $args    The function arguments. Accepts boolean 'a_internal'.
	 * @return string The markdown converted text.
	 */
	public static function convert( $text, $convert = [], $args = [] ) {

		// preprocess
		$text = trim( str_replace( [ "\r\n", "\r", "\t" ], [ "\n", "\n", ' ' ], $text ) );

		// You need at least 3 chars to make a markdown: *m*
		if ( \strlen( $text ) < 3 )
			return '';

		$args += [ 'a_internal' => false ];

		$conversions = [
			'**'     => 'strong',
			'*'      => 'em',
			'`'      => 'code',
			'[]()'   => 'a',
			'======' => 'h6',
			'====='  => 'h5',
			'===='   => 'h4',
			'==='    => 'h3',
			'=='     => 'h2',
			'='      => 'h1',
		];

		$md_types = empty( $convert ) ? $conversions : array_intersect( $conversions, $convert );

		if ( isset( $md_types['*'], $md_types['**'] ) )
			$text = static::strong_em( $text );

		foreach ( $md_types as $type ) {
			switch ( $type ) {
				case 'strong':
					$text = static::strong( $text );
					break;

				case 'em':
					$text = static::em( $text );
					break;

				case 'code':
					$text = static::code( $text );
					break;

				case 'h6':
				case 'h5':
				case 'h4':
				case 'h3':
				case 'h2':
				case 'h1':
					$text = static::h123456( $text, $type );
					break;

				case 'a':
					$text = static::a( $text, $args['a_internal'] );
			}
		}

		return $text;
	}

	/**
	 * Makes strong>em elements.
	 * We do this separately because em and strong use the same operators.
	 *
	 * @since 4.1.4
	 * @since 4.2.8 No longer blocks text with either { or } from being parsed.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Interpreters\Markdown`.
	 *
	 * @param string $text The input text.
	 * @return string
	 */
	private static function strong_em( $text ) {

		// Discrepancy with strong OR em: we exclude * here, we only want to capture full blocks.
		preg_match_all( '/\*{3}([^\*]+)\*{3}/', $text, $matches, \PREG_SET_ORDER );

		foreach ( $matches as $match ) {
			$text = str_replace(
				$match[0],
				\sprintf( '<strong><em>%s</em></strong>', \esc_html( $match[1] ) ),
				$text,
			);
		}

		return $text;
	}

	/**
	 * Makes strong elements.
	 *
	 * @since 4.1.4
	 * @since 4.2.8 No longer blocks text with either { or } from being parsed.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Interpreters\Markdown`.
	 *
	 * @param string $text The input text.
	 * @return string
	 */
	private static function strong( $text ) {

		preg_match_all( '/\*{2}(.+?)\*{2}/', $text, $matches, \PREG_SET_ORDER );

		foreach ( $matches as $match ) {
			$text = str_replace(
				$match[0],
				\sprintf( '<strong>%s</strong>', \esc_html( $match[1] ) ),
				$text,
			);
		}

		return $text;
	}

	/**
	 * Makes em elements.
	 *
	 * @since 4.1.4
	 * @since 4.2.8 No longer blocks text with either { or } from being parsed.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Interpreters\Markdown`.
	 *
	 * @param string $text The input text.
	 * @return string
	 */
	private static function em( $text ) {

		preg_match_all( '/\*([^\*]+)\*/', $text, $matches, \PREG_SET_ORDER );

		foreach ( $matches as $match ) {
			$text = str_replace(
				$match[0],
				\sprintf( '<em>%s</em>', \esc_html( $match[1] ) ),
				$text,
			);
		}

		return $text;
	}

	/**
	 * Makes code elements.
	 *
	 * @since 4.1.4
	 * @since 4.2.8 No longer blocks text with either { or } from being parsed.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Interpreters\Markdown`.
	 *
	 * @param string $text The input text.
	 * @return string
	 */
	private static function code( $text ) {

		preg_match_all( '/`([^`]+)`/', $text, $matches, \PREG_SET_ORDER );

		foreach ( $matches as $match ) {
			$text = str_replace(
				$match[0],
				\sprintf( '<code>%s</code>', \esc_html( $match[1] ) ),
				$text,
			);
		}

		return $text;
	}

	/**
	 * Makes header h1~6 elements.
	 *
	 * @since 4.1.4
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Interpreters\Markdown`.
	 *
	 * @param string $text The input text.
	 * @param string $type The header type. Accepts `/h[1-6]{1}/`.
	 * @return string
	 */
	private static function h123456( $text, $type = 'h1' ) {

		preg_match_all(
			// Considers word non-boundary. @TODO consider removing that?
			\sprintf(
				'/\={%1$d}\s(.+)\s\={%1$d}/',
				filter_var( $type, \FILTER_SANITIZE_NUMBER_INT )
			),
			$text,
			$matches,
			\PREG_SET_ORDER,
		);

		$type = \esc_attr( $type );

		foreach ( $matches as $match ) {
			$text = str_replace(
				$match[0],
				\sprintf( '<%1$s>%2$s</%1$s>', $type, \esc_html( $match[1] ) ),
				$text,
			);
		}

		return $text;
	}

	/**
	 * Makes a elements.
	 *
	 * @since 4.1.4
	 * @since 4.2.8 1. No longer blocks text with either { or } from being parsed.
	 *              2. No longer blocks URLs with either ( or ) from being parsed.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Interpreters\Markdown`.
	 *
	 * @param string $text     The input text.
	 * @param bool   $internal Whether the link is internal (_self) or external (_blank).
	 *                         External-type links also get no-follow/referrer/opener'd.
	 * @return string
	 */
	private static function a( $text, $internal = true ) {

		preg_match_all( '/\[([^[\]]+)]\(([^\s]+)\s*\)/', $text, $matches, \PREG_SET_ORDER );

		// Keep this XHTML compatible!
		$format = $internal ? '<a href="%s">%s</a>' : '<a href="%s" target="_blank" rel="nofollow noreferrer noopener">%s</a>';

		foreach ( $matches as $match ) {
			$text = str_replace(
				$match[0],
				\sprintf( $format, \esc_url( $match[2], [ 'https', 'http' ] ), \esc_html( $match[1] ) ),
				$text,
			);
		}

		return $text;
	}
}
