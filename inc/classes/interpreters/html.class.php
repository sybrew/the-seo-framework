<?php
/**
 * @package The_SEO_Framework\Classes\Interpreters\HTML
 * @subpackage The_SEO_Framework\Admin\Settings
 */

namespace The_SEO_Framework\Interpreters;

/**
 * The SEO Framework plugin
 * Copyright (C) 2021 - 2022 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Interprets anything you send here into HTML. Or so it should.
 *
 * @since 4.1.4
 *
 * @access protected
 *         Everything in this class is subject to change or deletion.
 * @internal
 * @final Can't be extended.
 */
final class HTML {

	/**
	 * Helper function that constructs header elements. Does not escape.
	 *
	 * @since 4.1.4
	 *
	 * @param string $title The header title.
	 * @return string The header title.
	 */
	public static function get_header_title( $title ) {
		return sprintf( '<h4>%s</h4>', $title );
	}

	/**
	 * Helper function that constructs header elements.
	 *
	 * @since 4.1.4
	 *
	 * @param string $title The header title.
	 */
	public static function header_title( $title ) {
		// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- it is.
		echo static::get_header_title( \esc_html( $title ) );
	}

	/**
	 * Mark up content with code tags.
	 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
	 *
	 * @since 4.1.4
	 *
	 * @param string $content Content to be wrapped in code tags.
	 * @return string Content wrapped in code tags.
	 */
	public static function code_wrap( $content ) {
		return static::code_wrap_noesc( \esc_html( $content ) );
	}

	/**
	 * Mark up content with code tags.
	 * Escapes no HTML.
	 *
	 * @since 4.1.4
	 *
	 * @param string $content Content to be wrapped in code tags.
	 * @return string Content wrapped in code tags.
	 */
	public static function code_wrap_noesc( $content ) {
		return "<code>$content</code>";
	}

	/**
	 * Mark up content in description wrap.
	 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
	 *
	 * @since 4.1.4
	 *
	 * @param string $content Content to be wrapped in the description wrap.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public static function description( $content, $block = true ) {
		static::description_noesc( \esc_html( $content ), $block );
	}

	/**
	 * Mark up content in description wrap.
	 *
	 * @since 4.1.4
	 *
	 * @param string $content Content to be wrapped in the description wrap. Expected to be escaped.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public static function description_noesc( $content, $block = true ) {
		printf(
			( $block ? '<p>%s</p>' : '%s' ),
			// phpcs:ignore, WordPress.Security.EscapeOutput -- Method clearly states it's not escaped.
			"<span class=description>$content</span>"
		);
	}

	/**
	 * Mark up content in attention wrap.
	 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
	 *
	 * @since 4.1.4
	 *
	 * @param string $content Content to be wrapped in the attention wrap.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public static function attention( $content, $block = true ) {
		static::attention_noesc( \esc_html( $content ), $block );
	}

	/**
	 * Mark up content in attention wrap.
	 *
	 * @since 3.1.0
	 *
	 * @param string $content Content to be wrapped in the attention wrap. Expected to be escaped.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public static function attention_noesc( $content, $block = true ) {
		printf(
			( $block ? '<p>%s</p>' : '%s' ),
			// phpcs:ignore, WordPress.Security.EscapeOutput -- Method clearly states it's not escaped.
			"<span class=attention>$content</span>"
		);
	}

	/**
	 * Mark up content in a description+attention wrap.
	 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
	 *
	 * @since 3.1.0
	 *
	 * @param string $content Content to be wrapped in the wrap. Expected to be escaped.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public static function attention_description( $content, $block = true ) {
		static::attention_description_noesc( \esc_html( $content ), $block );
	}

	/**
	 * Mark up content in a description+attention wrap.
	 *
	 * @since 3.1.0
	 *
	 * @param string $content Content to be wrapped in the wrap. Expected to be escaped.
	 * @param bool   $block Whether to wrap the content in <p> tags.
	 */
	public static function attention_description_noesc( $content, $block = true ) {
		printf(
			( $block ? '<p>%s</p>' : '%s' ),
			// phpcs:ignore, WordPress.Security.EscapeOutput -- Method clearly states it's not escaped.
			"<span class=\"description attention\">$content</span>"
		);
	}

	/**
	 * Echo or return a chechbox fields wrapper.
	 *
	 * This method does NOT escape.
	 *
	 * @since 2.6.0
	 *
	 * @param string $input The input to wrap. Should already be escaped.
	 * @param bool   $echo  Whether to escape echo or just return.
	 * @return string|void Wrapped $input.
	 */
	public static function wrap_fields( $input = '', $echo = false ) {

		if ( \is_array( $input ) )
			$input = implode( PHP_EOL, $input );

		$output = "<div class=tsf-fields>$input</div>";

		if ( $echo ) {
			// phpcs:ignore, WordPress.Security.EscapeOutput -- Escape your $input prior!
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Return a wrapped question mark.
	 *
	 * @since 2.6.0
	 * @since 3.0.0 Links are now no longer followed, referred or bound to opener.
	 * @since 4.0.0 Now adds a tabindex to the span tag, so you can focus it using keyboard navigation.
	 *
	 * @param string $description The descriptive on-hover title.
	 * @param string $link        The non-escaped link.
	 * @param bool   $echo        Whether to echo or return.
	 * @return string HTML checkbox output if $echo is false.
	 */
	public static function make_info( $description = '', $link = '', $echo = true ) {

		if ( $link ) {
			$output = sprintf(
				'<a href="%1$s" class="tsf-tooltip-item tsf-help" target=_blank rel="nofollow noreferrer noopener" title="%2$s" data-desc="%2$s">[?]</a>',
				\esc_url( $link, [ 'https', 'http' ] ),
				\esc_attr( $description )
			);
		} else {
			$output = sprintf(
				'<span class="tsf-tooltip-item tsf-help" title="%1$s" data-desc="%1$s" tabindex=0>[?]</span>',
				\esc_attr( $description )
			);
		}

		$output = sprintf( '<span class=tsf-tooltip-wrap>%s</span>', $output );

		if ( $echo ) {
			// phpcs:ignore, WordPress.Security.EscapeOutput
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Makes either simple or JSON-encoded data-* attributes for HTML elements.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 No longer adds an extra space in front of the return value when no data is generated.
	 * @internal
	 *
	 * @param iterable $data : {
	 *    string $k => mixed $v
	 * }
	 * @return string The HTML data attributes, with added space to the start if something's created.
	 */
	public static function make_data_attributes( $data ) {

		$ret = [];

		foreach ( $data as $k => $v ) {
			$ret[] = sprintf(
				'data-%s="%s"',
				strtolower( preg_replace(
					'/([A-Z])/',
					'-$1',
					preg_replace( '/[^a-z0-9_\-]/i', '', $k )
				) ), // dash case.
				is_scalar( $v ) ? \esc_attr( $v ) : htmlspecialchars( json_encode( $v, JSON_UNESCAPED_SLASHES ), ENT_COMPAT, 'UTF-8' )
			);
		}

		return $ret ? ' ' . implode( ' ', $ret ) : '';
	}
}
