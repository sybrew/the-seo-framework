<?php
/**
 * @package The_SEO_Framework\Classes\Interpreters\Markdown
 * @subpackage The_SEO_Framework\Admin\Settings
 */

namespace The_SEO_Framework\Interpreters;

/**
 * The SEO Framework plugin
 * Copyright (C) 2021 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Interprets anything you send here into Markdown. Or so it should.
 *
 * @since 4.1.4
 * @note Use `the_seo_framework()->convert_markdown() for easy access.
 *
 * @access protected
 *         Everything in this class is subject to change or deletion.
 * @internal
 * @final Can't be extended.
 */
final class Markdown {

	/**
	 * Converts markdown text into HMTL.
	 * Does not support list or block elements. Only inline statements.
	 *
	 * Note: This code has been rightfully stolen from the Extension Manager plugin (sorry Sybre!).
	 *
	 * @since 4.1.4
	 * @link https://wordpress.org/plugins/about/readme.txt
	 *
	 * @param string $text    The text that might contain markdown. Expected to be escaped.
	 * @param array  $convert The markdown style types wished to be converted.
	 *                        If left empty, it will convert all.
	 * @param array  $args    The function arguments.
	 * @return string The markdown converted text.
	 */
	public static function convert( $text, $convert = [], $args = [] ) {

		// preprocess
		$text = str_replace( "\r\n", "\n", $text );
		$text = str_replace( "\t", ' ', $text );
		$text = trim( $text );

		// You need 3 chars to make a markdown: *m*
		if ( \strlen( $text ) < 3 )
			return '';

		// Merge defaults with $args.
		$args = array_merge( [ 'a_internal' => false ], $args );

		/**
		 * The conversion list's keys are per reference only.
		 */
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

		if ( 2 === \count( array_intersect( $md_types, [ 'em', 'strong' ] ) ) ) :
			$text = static::strong_em( $text );
		endif;

		foreach ( $md_types as $type ) :
			switch ( $type ) :
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
					break;

				default:
					break;
			endswitch;
		endforeach;

		return $text;
	}

	/**
	 * Makes strong>em elements.
	 * We do this separately because em and strong use the same operators.
	 *
	 * @since 4.1.4
	 *
	 * @param string $text The input text.
	 * @return string
	 */
	private static function strong_em( $text ) {

		$count = preg_match_all( '/(?:\*{3})([^\*{\3}]+)(?:\*{3})/', $text, $matches, PREG_PATTERN_ORDER );
		for ( $i = 0; $i < $count; $i++ ) {
			$text = str_replace(
				$matches[0][ $i ],
				sprintf( '<strong><em>%s</em></strong>', \esc_html( $matches[1][ $i ] ) ),
				$text
			);
		}

		return $text;
	}

	/**
	 * Makes strong elements.
	 *
	 * @since 4.1.4
	 *
	 * @param string $text The input text.
	 * @return string
	 */
	private static function strong( $text ) {

		$count = preg_match_all( '/(?:\*{2})([^\*{\2}]+)(?:\*{2})/', $text, $matches, PREG_PATTERN_ORDER );

		for ( $i = 0; $i < $count; $i++ ) {
			$text = str_replace(
				$matches[0][ $i ],
				sprintf( '<strong>%s</strong>', \esc_html( $matches[1][ $i ] ) ),
				$text
			);
		}

		return $text;
	}

	/**
	 * Makes em elements.
	 *
	 * @since 4.1.4
	 *
	 * @param string $text The input text.
	 * @return string
	 */
	private static function em( $text ) {

		$count = preg_match_all( '/(?:\*{1})([^\*{\1}]+)(?:\*{1})/', $text, $matches, PREG_PATTERN_ORDER );

		for ( $i = 0; $i < $count; $i++ ) {
			$text = str_replace(
				$matches[0][ $i ],
				sprintf( '<em>%s</em>', \esc_html( $matches[1][ $i ] ) ),
				$text
			);
		}

		return $text;
	}

	/**
	 * Makes code elements.
	 *
	 * @since 4.1.4
	 *
	 * @param string $text The input text.
	 * @return string
	 */
	private static function code( $text ) {

		$count = preg_match_all( '/(?:`{1})([^`{\1}]+)(?:`{1})/', $text, $matches, PREG_PATTERN_ORDER );

		for ( $i = 0; $i < $count; $i++ ) {
			$text = str_replace(
				$matches[0][ $i ],
				sprintf( '<code>%s</code>', \esc_html( $matches[1][ $i ] ) ),
				$text
			);
		}
		return $text;
	}

	/**
	 * Makes header h1~6 elements.
	 *
	 * @since 4.1.4
	 *
	 * @param string $text The input text.
	 * @param string $type The header type. Accepts `/h[1-6]{1}/`.
	 * @return string
	 */
	private static function h123456( $text, $type = 'h1' ) {

		// Considers word non-boundary. @TODO consider removing that?
		$expression = sprintf(
			'/(?:\={%1$d})\B([^\={\%1$s}]+)\B(?:\={%1$d})/',
			filter_var( $type, FILTER_SANITIZE_NUMBER_INT )
		);

		$count = preg_match_all( $expression, $text, $matches, PREG_PATTERN_ORDER );

		for ( $i = 0; $i < $count; $i++ ) {
			$text = str_replace(
				$matches[0][ $i ],
				sprintf( '<%1$s>%2$s</%1$s>', \esc_attr( $type ), \esc_html( $matches[1][ $i ] ) ),
				$text
			);
		}

		return $text;
	}

	/**
	 * Makes a elements.
	 *
	 * @since 4.1.4
	 *
	 * @param string $text     The input text.
	 * @param bool   $internal Whether the link is internal (_self) or external (_blank).
	 *                         External-type links also get no-follow/referrer/opener'd.
	 * @return string
	 */
	private static function a( $text, $internal = true ) {

		$count = preg_match_all( '/(?:(?:\[{1})([^\]]+)(?:\]{1})(?:\({1})([^\)\(]+)(?:\){1}))/', $text, $matches, PREG_PATTERN_ORDER );

		$_string = $internal ? '<a href="%s">%s</a>' : '<a href="%s" target="_blank" rel="nofollow noreferrer noopener">%s</a>';

		for ( $i = 0; $i < $count; $i++ ) {
			$text = str_replace(
				$matches[0][ $i ],
				sprintf( $_string, \esc_url( $matches[2][ $i ], [ 'https', 'http' ] ), \esc_html( $matches[1][ $i ] ) ),
				$text
			);
		}

		return $text;
	}
}
