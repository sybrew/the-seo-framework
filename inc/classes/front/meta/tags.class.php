<?php
/**
 * @package The_SEO_Framework\Classes\Front\Meta
 * @subpackage The_SEO_Framework\Front
 */

namespace The_SEO_Framework\Front\Meta;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Interprets the meta tags from human readable PHP to machine readable HTML.
 *
 * @since 4.3.0
 *
 * @final Can't be extended.
 */
final class Tags {

	/**
	 * @since 4.3.0
	 * @var callable[] Meta tag generator callbacks.
	 */
	private static $tag_generators = [];

	/**
	 * @since 4.3.0
	 * @var array[] The meta tags' render data : {
	 *    @param ?array  attributes A list of attributes by [ name => value ].
	 *    @param ?string tag        The tag name. Defaults to 'meta' if left empty.
	 *    @param ?string content    The tag's content. Leave null to not render content.
	 *    @param ?true   rendered   Private, whether the tag is rendered.
	 * }
	 */
	private static $tags_render_data = [];

	/**
	 * Returns the registered callbacks by reference.
	 *
	 * @since 4.3.0
	 * @access protected
	 * @see filter the_seo_framework_meta_generators.
	 *
	 * @return callable[] Callbacks, passed by reference.
	 */
	public static function &tag_generators() {
		return static::$tag_generators;
	}

	/**
	 * Returns the registered tag render data by reference.
	 *
	 * @since 4.3.0
	 * @access protected
	 * @see filter the_seo_framework_meta_render_data.
	 *
	 * @return array[] The meta tags, passed by reference.
	 */
	public static function &tags_render_data() {
		return static::$tags_render_data;
	}

	/**
	 * Walks over all tag generators and writes to $tags_render_data.
	 *
	 * @since 4.3.0
	 */
	public static function fill_render_data_from_registered_generators() {

		$tags_render_data = &static::tags_render_data();

		foreach ( static::$tag_generators as $callback ) {
			foreach ( \call_user_func( $callback ) as $data ) {
				$tags_render_data[] = array_merge(
					[
						'attributes' => [],
						'tag'        => 'meta',
						'content'    => null,
					],
					$data
				);
			}
		}
	}

	/**
	 * Outputs all registered callbacks.
	 *
	 * @since 4.3.0
	 */
	public static function render_tags() {

		$tags_render_data = &static::tags_render_data();

		foreach ( $tags_render_data as &$tagdata ) {
			if ( $tagdata['rendered'] ?? false ) continue;

			static::render(
				$tagdata['attributes'] ??= [],
				$tagdata['tag']        ??= 'meta',
				$tagdata['content']    ??= null,
			);

			$tagdata['rendered'] = true;
		}
	}

	/**
	 * Renders an XHTML element. Sane drop-in for DOMDocument and whatnot.
	 *
	 * Even though most (if not all) WordPress sites use HTML5, we expect some still use XHTML.
	 * We expect HTML5 fully on the back-end.
	 *
	 * @since 4.3.0
	 * @link <https://github.com/sybrew/the-seo-framework/commit/894d7d3a74e0ed6890b6e8851ef0866df15ea522>
	 *       Which is something we eventually want to go to, but that's not ready yet.
	 *
	 * @param array   $attributes Associative array of tag names and tag values : {
	 *    string $name => string $value
	 * }
	 * @param string  $tag      The element's tag-name.
	 * @param ?string $content  The element's contents. If not null,
	 *                          it will create a content-wrapping element.
	 */
	public static function render( $attributes = [], $tag = 'meta', $content = null ) {

		$attr = '';

		foreach ( $attributes as $name => $value ) {
			$name = trim( $name );

			switch ( $name ) {
				case 'href':
				case 'xlink:href':
				case 'src':
					$_secure_attr_value = \esc_url_raw( $value );
					break;
				default:
					if (
						/** @link <https://www.w3.org/TR/2011/WD-html5-20110525/elements.html> */
						0 === stripos( $name, 'on' )
					) {
						// phpcs:disable -- hint for when we need to get more specific.
						// \in_array(
						// 	$name,
						// 	[ 'onabort', 'onblur', 'oncanplay', 'oncanplaythrough', 'onchange', 'onclick', 'oncontextmenu', 'oncuechange', 'ondblclick', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'ondurationchange', 'onemptied', 'onended', 'onerror', 'onfocus', 'oninput', 'oninvalid', 'onkeydown', 'onkeypress', 'onkeyup', 'onload', 'onloadeddata', 'onloadedmetadata', 'onloadstart', 'onmousedown', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onpause', 'onplay', 'onplaying', 'onprogress', 'onratechange', 'onreadystatechange', 'onreset', 'onscroll', 'onseeked', 'onseeking', 'onselect', 'onshow', 'onstalled', 'onsubmit', 'onsuspend', 'ontimeupdate', 'onvolumechange', 'onwaiting' ],
						// 	true
						// )
						// phpcs:enable

						// Nope. Not this function. Skip writing on-stuff.
						continue 2;
					}

					$_secure_attr_value = \esc_attr( $value );
			}

			$attr .= sprintf(
				' %s="%s"',
				/**
				 * This will also strip "safe" characters outside of the alphabet, 0-9, and :_-.
				 * I don't want angry parents ringing me at home for their site didn't
				 * support proper UTF. We can afford empty tags in rare situations -- not here.
				 * So, we'll only allow ASCII 45~95 except 46, 47, 59~64, and 91~94.
				 *
				 * @link <https://www.w3.org/TR/2011/WD-html5-20110525/syntax.html#attributes-0>
				 */
				preg_replace( '/[^a-zA-Z0-9:_-]+/', '', $name ),
				$_secure_attr_value
			);
		}

		// phpcs:disable, WordPress.Security.EscapeOutput.OutputNotEscaped -- render escapes.
		if ( isset( $content ) ) {
			vprintf(
				'<%1$s%2$s>%3$s</%1$s>',
				[
					/** @link <https://www.w3.org/TR/2011/WD-html5-20110525/syntax.html#syntax-tag-name> */
					preg_replace( '/[^0-9a-zA-Z]+/', '', $tag ),
					$attr,
					\esc_html( $content ),
				]
			);
		} else {
			printf(
				'<%s%s />',
				/** @link <https://www.w3.org/TR/2011/WD-html5-20110525/syntax.html#syntax-tag-name> */
				preg_replace( '/[^0-9a-zA-Z]+/', '', $tag ),
				$attr
			);
		}

		echo "\n";
	}
}
