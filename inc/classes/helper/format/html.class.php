<?php
/**
 * @package The_SEO_Framework\Classes\Helper\Format\Color
 * @subpackage The_SEO_Framework\Formatting
 */

namespace The_SEO_Framework\Helper\Format;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\umemo;

use \The_SEO_Framework\{
	Data,
	Data\Filter\Sanitize,
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
 * Holds methods for HTML Color interpretation and conversion.
 *
 * @since 5.0.0
 *
 * @access protected
 *         Use tsf()->format()->html() instead.
 */
class HTML {

	/**
	 * Strips all URLs that are placed on new lines. These are prone to be embeds.
	 *
	 * This might leave stray line feeds. Use `tsf()->sanitize()->newline_to_space()` to fix that.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 * @see \WP_Embed::autoembed()
	 *
	 * @param string $content The content to look for embed.
	 * @return string $content Content without single-lined URLs.
	 */
	public static function strip_newline_urls( $content ) {
		return preg_replace( '/^(?!\r|\n)\s*?(https?:\/\/[^\s<>"]+)(\s*)$/mi', '', $content );
	}

	/**
	 * Strips all URLs that are placed in paragraphs on their own. These are prone to be embeds.
	 *
	 * This might leave stray line feeds. Use `tsf()->sanitize()->newline_to_space()` to fix that.
	 *
	 * @since 3.1.0
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Improved regex to reflect absurd HTML.
	 * @see \WP_Embed::autoembed()
	 * @link <https://regex101.com/r/hjHjgp/2>
	 *
	 * @param string $content The content to look for embed.
	 * @return string $content Content without the paragraphs containing solely URLs.
	 */
	public static function strip_paragraph_urls( $content ) {
		return preg_replace( '/<p\b[^>]*>\s*https?:\/\/[^\s<>"]+\s*<\/p\s*>/i', '', $content );
	}

	/**
	 * Strips tags with HTML Context-Sensitivity and outputs its breakdown.
	 *
	 * It essentially strips all tags, and replaces block-type tags' endings with spaces.
	 * When done, it performs a sanity-cleanup via `strip_tags()`.
	 *
	 * Tip: You might want to use method `s_dupe_space()` to clear up the duplicated/repeated spaces afterward.
	 *
	 * @since 3.2.4
	 * @since 4.0.0 Now allows emptying the indexes `space` and `clear`.
	 * @since 4.0.5 1. Added the `strip` argument index to the second parameter for clearing leftover tags.
	 *              2. Now also clears `iframe` tags by default.
	 *              3. Now no longer (for example) accidentally takes `link` tags when only `li` tags are set for stripping.
	 *              4. Now performs a separate query for void elements; to prevent regex recursion.
	 * @since 4.1.0 Now detects nested elements and preserves that content correctly--as if we'd pass through scrupulously beyond infinity.
	 * @since 4.1.1 Can now replace void elements with spaces when so inclined via the arguments (space vs clear).
	 * @since 4.2.7 1. Revamped the HTML lookup: it now (more) accurately processes HTML, and is less likely to be fooled by HTML tags
	 *                 in attributes.
	 *              2. The 'space' index no longer has default `fieldset`, `figcaption`, `form`, `main`, `nav`, `pre`, `table`, and `tfoot`.
	 *              3. The space index now has added to default `details`, `hgroup`, and `hr`.
	 *              4. The 'clear' index no longer has default `bdo`, `hr`, `link`, `meta`, `option`, `samp`, `style`, and `var`.
	 *              5. The 'clear' index now has added to default `area`, `audio`, `datalist`, `del`, `dialog`, `fieldset`, `form`, `map`,
	 *                 `menu`, `meter`, `nav`, `object`, `output`, `pre`, `progress`, `s`, `table`, and `template`.
	 *              6. Added the 'passes' index to `$args`. This tells the maximum passes 'space' may process.
	 *                 Read TSF option `auto_description_html_method` to use the user-defined method.
	 *              7. Now replaces all elements passed with spaces. For void elements, or phrasing elements, you'd want to omit
	 *                 those from '$args' so it falls through to `strip_tags()`.
	 *              8. Added preparation memoization using cache delimiters `$args['space']` and `$args['clear']`.
	 * @since 4.2.8 Elements with that start with exactly the same text as others won't be preemptively closed.
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Load`.
	 *
	 * @link https://developer.mozilla.org/en-US/docs/Web/Guide/HTML/Content_categories
	 * @link https://html.spec.whatwg.org/multipage/syntax.html#void-elements
	 *
	 * @param string $input The input text that needs its tags stripped.
	 * @param array  $args  The input arguments. Tags not included are ignored. {
	 *                         'space'   : @param ?string[] HTML elements that should be processed for spacing. If the space
	 *                                                      element is of void element type, it'll be treated as 'clear'.
	 *                                                      If not set or null, skip check.
	 *                                                      If empty array, skips stripping; otherwise, use input.
	 *                         'clear'   : @param ?string[] HTML elements that should be emptied and replaced with a space.
	 *                                                      If not set or null, skip check.
	 *                                                      If empty array, skips stripping; otherwise, use input.
	 *                         'strip'   : @param ?bool     If set, strip_tags() is performed before returning the output.
	 *                                                      Recommended always true, since Regex doesn't understand XML. Default true.
	 *                         'passes'  : @param ?int      If set, the maximum number of passes 'space' may conduct. More is slower,
	 *                                                      but more accurate. 'clear' is unaffected. Default 1.
	 *                      }
	 *                      NOTE: WARNING The array values are forwarded to a regex without sanitization/quoting.
	 *                      NOTE: Unlisted, script, and style tags will be stripped via PHP's `strip_tags()`. (togglable via `$args['strip']`)
	 *                            This means that their contents are maintained as-is, without added spaces. So, CSS and JS will become text.
	 *                            It is why you should always list `style` and `script` in the `clear` array, never in 'space'.
	 * @return string The output string without tags. May have many stray and repeated spaces.
	 *                NOT SECURE for display! Don't trust this method. Always use esc_* functionality.
	 */
	public static function strip_tags_cs( $input, $args = [] ) {

		if ( ! str_contains( $input, '<' ) )
			return $input;

		/**
		 * Find the optimized version in `s_excerpt()`. The defaults here treats HTML for a18y reading, not description generation.
		 *
		 * Contains HTML5 supported flow content elements only, even though browsers might behave differently.
		 * https://developer.mozilla.org/en-US/docs/Web/Guide/HTML/Content_categories#flow_content
		 *
		 * Missing phrasing elements: 'a', 'abbr', 'b', 'bdo', 'bdi', 'cite', 'data', 'dfn', 'em', 'embed', 'i', 'img', 'ins', 'kbd',
		 * 'mark', 'math', 'picture', 'q', 'ruby', 'samp', 'small', 'span', 'strong', 'sub', 'sup', 'time', 'u', 'var', and 'wbr'.
		 * There's no need to add these, for they're cleared plainly by `strip_tags()`.
		 *
		 * Missing flow elements: 'link', 'meta'
		 * There's no need to add these, for they are void content.
		 *
		 * Contains all form elements. Those must be stripped in almost any context.
		 */
		$default_args = [
			'space'  =>
				[ 'address', 'article', 'aside', 'br', 'blockquote', 'details', 'dd', 'div', 'dl', 'dt', 'figure', 'footer', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', 'hgroup', 'hr', 'li', 'ol', 'p', 'section', 'ul' ],
			'clear'  =>
				[ 'area', 'audio', 'button', 'canvas', 'code', 'datalist', 'del', 'dialog', 'fieldset', 'form', 'iframe', 'input', 'label', 'map', 'menu', 'meter', 'nav', 'noscript', 'object', 'output', 'pre', 'progress', 's', 'script', 'select', 'style', 'svg', 'table', 'template', 'textarea', 'video' ],
			'strip'  => true,
			'passes' => 1,
		];

		if ( ! $args ) {
			$args = $default_args;
		} else {
			// We don't use array_merge() here because we want to default these to [] when $args is given.
			foreach ( [ 'clear', 'space' ] as $type )
				$args[ $type ] = (array) ( $args[ $type ] ?? [] );

			$args['strip']  ??= $default_args['strip'];
			$args['passes'] ??= $default_args['passes'];
		}

		$parse = umemo( __METHOD__ . '/parse', null, $args['space'], $args['clear'] );
		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( ! $parse ) {
			// Void elements never have content. 'param', 'source', 'track',
			$void = [ 'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'wbr' ];
			// Phrase elements should be replaced without spacing around them. There are more phrasing (54) than block elements (39)...
			// Blocks: address, area, article, aside, audio, blockquote, br, button, canvas, dd, details, dialog, div, dl, dt, fieldset, figure, footer, form, h1, h2, h3, h4, h5, h6, header, hgroup, hr, li, ol, pre, table, td, template, textarea, th, tr, ul, video.
			// Some block elements can be interpreted as phrasing elements, like audio, canvas, button, and video; hence, they're also listed in $phrase.
			// 'br' is a phrase element, but also a struct whitespace -- let's omit it so we can substitute it with a space as block.
			$phrase = [ 'a', 'area', 'abbr', 'audio', 'b', 'bdo', 'bdi', 'button', 'canvas', 'cite', 'code', 'data', 'datalist', 'del', 'dfn', 'em', 'embed', 'i', 'iframe', 'img', 'input', 'ins', 'link', 'kbd', 'label', 'map', 'mark', 'meta', 'math', 'meter', 'noscript', 'object', 'output', 'picture', 'progress', 'q', 'ruby', 's', 'samp', 'script', 'select', 'small', 'span', 'strong', 'sub', 'sup', 'svg', 'textarea', 'time', 'u', 'var', 'video', 'wbr' ];

			$marked_for_parsing = array_merge( $args['space'], $args['clear'] );

			$void_elements = array_intersect( $marked_for_parsing, $void );
			$flow_elements = array_diff( $marked_for_parsing, $void );

			$clear_elements = array_intersect( $flow_elements, $args['clear'] );

			$parse = umemo(
				__METHOD__ . '/parse',
				[
					// void = element without content.
					'void_query'  => [
						'phrase' => array_intersect( $void_elements, $phrase ),
						'block'  => array_diff( $void_elements, $phrase ),
					],
					// fill = <normal | template | raw text | escapable text | foreign> element.
					'clear_query' => [
						'phrase' => array_intersect( $clear_elements, $phrase ),
						'block'  => array_diff( $clear_elements, $phrase ),
					],
					'space_query' => [
						'phrase' => array_intersect( $flow_elements, $args['space'] ),
					],
				],
				$args['space'],
				$args['clear'],
			);
		}

		foreach ( $parse as $query_type => $handles ) {
			foreach ( $handles as $flow_type => $elements ) {
				// Test $input again as it's overwritten in loop.
				if ( ! str_contains( $input, '<' ) || ! $elements ) break 2;

				switch ( $query_type ) {
					case 'void_query':
						$input = preg_replace(
							/**
							 * This one grabs opening tags only, and no content.
							 * Basically, the content and closing tag reader is split from clear_query/flow_query's regex.
							 * Akin to https://regex101.com/r/BqUCCG/1.
							 */
							\sprintf(
								'/<(?!\/)(?:%s)\b(?:[^=>\/]*=(?:(?:([\'"])[^$]*?\g{-1})|[\s\/]*))*+[^>]*>/i',
								implode( '|', $elements )
							),
							'phrase' === $flow_type ? '' : ' ', // Add space if block, otherwise clear.
							$input
						) ?? '';
						break;

					case 'space_query':
						$passes      = $args['passes'];
						$replacement = ' $4 ';
						// Fall through;
					case 'clear_query':
						$passes      ??= 1;
						$replacement ??= 'phrase' === $flow_type ? '' : ' ';

						// Akin to https://regex101.com/r/LR8iem/6. (This might be outdated, copy work!)
						// Ref https://www.w3.org/TR/2011/WD-html5-20110525/syntax.html (specifically end-tags)
						$regex = \sprintf(
							'/<(?!\/)(%s)\b([^=>\/]*=(?:(?:([\'"])[^$]*?\g{-1})|[\s\/]*))*+(?:(?2)++|[^>]*>)((?:[^<]*+(?:<(?!\/?\1\b.*?>)[^<]+)*|(?R))*?)<\/\1\s*>/i', // good enough
							implode( '|', $elements )
						);
						// Work in progress: /(<(?(R)\/?|(?!\/))(%s)\b)([^=>\/]*=(?:(?:([\'"])[^$]*?\g{-1})|[\s\/]*))*+(?:(?-2)*+|(?:.*?))>([^<]*+|(?R)|<\/\2\b\s*>)/i

						$i = 0;
						// To be most accurate, we should parse 'space' $type at least twice, up to 6 times. This is a performance hog.
						// This is because we process the tags from the outer layer to the most inner. Each pass goes deeper.
						while ( $i++ < $passes ) {
							$pre_pass_input = $input;
							$input          = preg_replace( $regex, $replacement, $input ) ?? '';

							// If nothing changed, or no more HTML is present, we're done.
							if ( $pre_pass_input === $input || ! str_contains( $input, '<' ) ) break;
						}

						// Reset for next fall-through null-coalescing.
						unset( $passes, $replacement );
				}
			}
		}

		// phpcs:ignore, WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- $args defines stripping of 'script' and 'style'.
		return $args['strip'] ? \strip_tags( $input ) : $input;
	}

	/**
	 * Extracts a usable excerpt from singular content.
	 *
	 * @since 2.8.0
	 * @since 2.8.2 1. Added `$allow_shortcodes` parameter.
	 *              2. Added `$escape` parameter.
	 * @since 3.2.4 Now selectively clears tags.
	 * @since 4.1.0 Moved `figcaption`, `figure`, `footer`, and `tfoot`, from `space` to `clear`.
	 * @since 4.2.7 1. No longer clears `figcaption`, `hr`, `link`, `meta`, `option`, or `tfoot`.
	 *              2. Now clears `area`, `audio`, `datalist`, `del`, `dialog`, `dl`, `hgroup`, `menu`, `meter`, `ol`,
	 *                 `object`, `output`, `progress`, `s`, `template`, and `ul`.
	 *              3. Now adds spaces around `blockquote`, `details`, and `hr`.
	 *              4. Now ignores `dd`, `dl`, `dt`, `li`, `main`, for they are inherently excluded or ignored anyway.
	 *              5. Now processed the `auto_description_html_method` option for stripping tags.
	 * @since 5.0.0 1. The first parameter is now required.
	 *              2. Now returns an empty string when something falsesque is returned.
	 *              3. Removed the third `$escape` parameter.
	 *              4. The second parameter is changed from `$allow_shortcodes`
	 *              5. Moved from `\The_SEO_Framework\Load`.
	 *              6. Renamed from `s_excerpt`.
	 *
	 * @param string $html The HTML to extract content from.
	 * @param array  $args {
	 *     Optional. The extraction parameters.
	 *
	 *     @type bool      $allow_shortcodes Whether to allow shortcodes. Default true.
	 *     @type bool      $sanitize         Whether to sanitize spacing and make the return value single-line.
	 *                                       Default true.
	 *     @type false|int $clamp            Set to int to clamp the sentence intelligently to that number of characters.
	 * }
	 * @return string The extracted html content.
	 */
	public static function extract_content( $html, $args = [] ) {

		if ( empty( $html ) ) return '';

		$args += [
			'allow_shortcodes' => true,
			'sanitize'         => true,
			'clamp'            => false,
		];

		switch ( Data\Plugin::get_option( 'auto_description_html_method' ) ) {
			case 'thorough':
				$passes = 12;
				break;
			case 'accurate':
				$passes = 6;
				break;
			case 'fast':
			default:
				$passes = 2;
		}

		/**
		 * Missing 'th', 'tr', 'tbody', 'thead', 'dd', 'dt', and 'li' -- these are obligatory subelements of what's already cleared.
		 *
		 * @since 5.0.5
		 * @param array $strip_args The content stripping arguments, associative.
		 *                          Refer to the second parameter of `\The_SEO_Framework\Helper\Format\HTML::strip_tags_cs()`.
		 */
		$strip_args = (array) \apply_filters(
			'the_seo_framework_extract_content_strip_args',
			[
				'space'  =>
					[ 'article', 'br', 'blockquote', 'details', 'div', 'hr', 'p', 'section' ],
				'clear'  =>
					[ 'address', 'area', 'aside', 'audio', 'blockquote', 'button', 'canvas', 'code', 'datalist', 'del', 'dialog', 'dl', 'fieldset', 'figure', 'footer', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', 'hgroup', 'iframe', 'input', 'label', 'map', 'menu', 'meter', 'nav', 'noscript', 'ol', 'object', 'output', 'pre', 'progress', 's', 'script', 'select', 'style', 'svg', 'table', 'template', 'textarea', 'ul', 'video' ],
				'passes' => $passes,
			]
		);

		/**
		 * Always strip shortcodes unless specifically allowed via the filter.
		 * Always strip shortcodes if not allowed by the arguments, ignoring the filter.
		 *
		 * @since 2.6.6.1
		 * @since 5.0.0 Added the third `$args` parameter.
		 * @param bool $allow_shortcodes Whether to allow shortcodes.
		 * @param array $args The extraction parameters.
		 */
		if ( ! $args['allow_shortcodes'] || ! \apply_filters( 'the_seo_framework_allow_excerpt_shortcode_tags', false, $args ) )
			$html = \strip_shortcodes( $html );

		$html = static::strip_tags_cs( $html, $strip_args );

		if ( \is_int( $args['clamp'] ) )
			$html = Strings::clamp_sentence( $html, 1, $args['clamp'] );

		return $args['sanitize'] ? Sanitize::metadata_content( $html ) : $html;
	}
}
