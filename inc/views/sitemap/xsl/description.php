<?php
/**
 * @package The_SEO_Framework\Views\Sitemap\XSL\Description
 * @subpackage The_SEO_Framework\Sitemap\XSL
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) or die;

use \The_SEO_Framework\{
	Data\Filter\Sanitize,
	Helper\Format\Markdown,
};

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

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

$logo = '';

if ( Data\Plugin::get_option( 'sitemap_logo' ) ) {

	$id   = Data\Plugin::get_option( 'sitemap_logo_id' ) ?: \get_theme_mod( 'custom_logo' ) ?: \get_option( 'site_icon' );
	$_src = $id ? \wp_get_attachment_image_src( $id, [ 29, 29 ] ) : []; // Magic number "SITEMAP_LOGO_PX"

	/**
	 * @since 2.8.0
	 * @param array $_src {
	 *     An empty array or the logo details.
	 *
	 *     @type string $0 The image URL.
	 *     @type int    $1 The width in pixels.
	 *     @type int    $2 The height in pixels.
	 * }
	 */
	$_src = (array) \apply_filters( 'the_seo_framework_sitemap_logo', $_src );

	if ( ! empty( $_src[0] ) ) {
		$logo = \sprintf(
			'<img src="%s" width="%s" height="%s" />', // Keep XHTML valid!
			\esc_url( $_src[0] ),
			\esc_attr( $_src[1] ?? '' ),
			\esc_attr( $_src[2] ?? '' ),
		);
	}
}

printf(
	'<a href="%s"><h1>%s%s</h1></a>',
	\esc_url( Data\Blog::get_front_page_url(), [ 'https', 'http' ] ),
	\wp_kses(
		$logo,
		[
			'img' => [
				'src'    => true,
				'width'  => true,
				'height' => true,
			],
		],
	),
	\esc_xml(
		Sanitize::metadata_content(
			Data\Blog::get_public_blog_name() . ' &mdash; ' . \__( 'XML Sitemap', 'autodescription' )
		)
	)
);
?>

<p>
	<?php
	// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- convert_markdown escapes.
	echo Markdown::convert(
		/* translators: URLs are in Markdown. Don't forget to localize the URLs. */
		\esc_xml( \__( 'This is an optimized XML sitemap meant to be processed quickly by search engines like [Google](https://www.google.com/) or [Bing](https://www.bing.com/).', 'autodescription' ) ),
		[ 'a' ],
		[ 'a_internal' => false ],
	);
	?>
</p>
<p>
	<?php
	// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- convert_markdown escapes.
	Markdown::convert(
		/* translators: URLs are in Markdown. Don't localize this URL. */
		\esc_xml( \__( 'You can find more information on XML sitemaps at [sitemaps.org](https://www.sitemaps.org/).', 'autodescription' ) ),
		[ 'a' ],
		[ 'a_internal' => false ],
	);
	?>
</p>
