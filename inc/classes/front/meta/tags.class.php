<?php
/**
 * @package The_SEO_Framework\Classes\Front\Meta
 * @subpackage The_SEO_Framework\Front
 */

namespace The_SEO_Framework\Front\Meta;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

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
 * Interprets the meta tags from human readable PHP to machine readable HTML.
 *
 * @since 5.0.0
 * @access private
 */
final class Tags {

	/**
	 * @since 5.0.0
	 * @var array[] {
	 *     The meta tags' render data defaults.
	 *
	 *     @type ?array        $attributes A list of attributes by [ name => value ].
	 *     @type ?string       $tag        The tag name. Defaults to 'meta' if left empty.
	 *     @type ?string|array $content    The tag's content. Leave null to not render content.
	 * }
	 */
	private const DATA_DEFAULTS = [
		'attributes' => [],
		'tag'        => 'meta',
		'content'    => null,
	];

	/**
	 * @since 5.0.0
	 * @var callable[] Meta tag generator callbacks.
	 */
	private static $tag_generators = [];

	/**
	 * @since 5.0.0
	 * @var array[] {
	 *     The meta tags' render data.
	 *
	 *     @type ?array        $attributes A list of attributes by [ name => value ].
	 *     @type ?string       $tag        The tag name. Defaults to 'meta' if left empty.
	 *     @type ?string|array $content    The tag's content. Leave null to not render content.
	 *     @type ?true         $rendered   Private, whether the tag is rendered.
	 * }
	 */
	private static $tags_render_data = [];

	/**
	 * Returns the registered callbacks by reference.
	 *
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * @since 5.0.0
	 */
	public static function fill_render_data_from_registered_generators() {

		$tags_render_data = &static::$tags_render_data;
		$i                = 0;

		foreach ( static::$tag_generators as $callback )
			foreach ( \call_user_func( $callback ) as $id => $data )
				$tags_render_data[ $id ?: ++$i ] = $data;
	}

	/**
	 * Outputs all registered callbacks.
	 *
	 * @since 5.0.0
	 */
	public static function render_tags() {

		// Remit FETCH_STATIC_PROP_R opcode calls every time we'd otherwise use static::DATA_DEFAULTS hereinafter.
		$data_defaults = static::DATA_DEFAULTS;
		// Also remit FETCH_DIM_R by writing the index to variables: https://3v4l.org/SLKbq & https://3v4l.org/ipmh5.
		$default_attributes = $data_defaults['attributes'];
		$default_tag        = $data_defaults['tag'];
		$default_content    = $data_defaults['content'];

		foreach ( static::$tags_render_data as &$tagdata ) {
			if ( $tagdata['rendered'] ?? false ) continue;

			static::render(
				$tagdata['attributes'] ??= $default_attributes,
				$tagdata['tag']        ??= $default_tag,
				$tagdata['content']    ??= $default_content,
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
	 * @since 5.0.0
	 *
	 * @param array         $attributes {
	 *                          Associative array of tag names and tag values.
	 *
	 *                          @type string $value The attributes's value, keyed by name.
	 *                      }
	 * @param string        $tag        The element's tag-name.
	 * @param ?string|array $content    The tag's content. Leave null to not render content.
	 *                                  It will create a content-wrapping element when filled.
	 *                                  When array, accepts keys 'content' and boolean 'escape'.
	 */
	public static function render(
		$attributes = self::DATA_DEFAULTS['attributes'],
		$tag = self::DATA_DEFAULTS['tag'],
		$content = self::DATA_DEFAULTS['content'] // php 8.0+, add trailing comma
	) {

		$attr = '';

		foreach ( $attributes as $name => $value ) {
			$name = trim( $name );

			// Test lowercase for sanitization, but don't confuse devs in outputting it lowercase.
			switch ( strtolower( $name ) ) {
				case 'href':
				case 'xlink:href':
				case 'src':
					// Perform discrete URL-encoding on resources that could bypass attributes, without mangling the URL.
					// Ampersand is missing because that doesn't affect HTML attributes; plus, it's a struct for query parameters.
					$_secure_attr_value = strtr(
						\sanitize_url( $value ),
						[
							'"' => '%22',
							"'" => '%27',
							'<' => '%3C',
							'>' => '%3E',
						],
					);
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

					// This replaces more than necessary -- may we wish to exchange it.
					$_secure_attr_value = \esc_attr( $value );
			}

			$attr .= \sprintf(
				' %s="%s"',
				/**
				 * This will also strip "safe" characters outside of the alphabet, 0-9, and :_-.
				 * I don't want angry parents ringing me at home for their site didn't
				 * support proper UTF. We can afford empty tags in rare situations -- not here.
				 * So, we'll only allow ASCII 45~95 except 46, 47, 59~64, and 91~94.
				 *
				 * @link <https://www.w3.org/TR/2011/WD-html5-20110525/syntax.html#attributes-0>
				 */
				preg_replace( '/[^a-z\d:_-]+/i', '', $name ),
				$_secure_attr_value,
			);
		}

		// phpcs:disable, WordPress.Security.EscapeOutput -- already escaped.
		if ( isset( $content ) ) {
			vprintf(
				'<%1$s%2$s>%3$s</%1$s>',
				[
					/** @link <https://www.w3.org/TR/2011/WD-html5-20110525/syntax.html#syntax-tag-name> */
					preg_replace( '/[^a-z\d]+/i', '', $tag ), // phpcs:ignore, WordPress.Security.EscapeOutput -- this escapes.
					$attr,
					\is_array( $content )
						? (
							( $content['escape'] ?? true )
								? \esc_html( $content['content'] ) // Yes, we're filling the content with content.
								: $content['content']
						)
						: \esc_html( $content ),
				],
			);
		} else {
			printf(
				'<%s%s />', // XHTML compatible.
				/** @link <https://www.w3.org/TR/2011/WD-html5-20110525/syntax.html#syntax-tag-name> */
				preg_replace( '/[^0-9a-zA-Z]+/', '', $tag ),
				$attr,
			);
		}
		// phpcs:enable, WordPress.Security.EscapeOutput

		echo "\n";
	}
}
