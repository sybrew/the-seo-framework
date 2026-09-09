<?php
/**
 * @package The_SEO_Framework\Views\Sitemap\CSS\Styles
 * @subpackage The_SEO_Framework\Sitemap\CSS
 */

namespace The_SEO_Framework;

( \defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) ) or die;

use The_SEO_Framework\Data\Filter\{
	Escape,
	Sanitize,
};
use The_SEO_Framework\Helper\Format\{
	Color,
	Markdown,
	Minify,
};

// phpcs:disable WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

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

$has_lastmod     = Data\Plugin::get_option( 'sitemaps_modified' );
$table_min_width = $has_lastmod ? 727 : 557; // magic numbers: sexy primes
$cell_max_width  = $table_min_width - 173; // magic number: sexy primes
$url_columns     = $has_lastmod ? 'minmax(0, 1fr) max-content' : 'minmax(0, 1fr)';
$direction       = \is_rtl() ? 'rtl' : 'ltr';
$colors          = Sitemap\Utils::get_sitemap_colors();

$color_main = '#' . Sanitize::rgb_hex(
	/**
	 * @since 2.8.0
	 * @since 3.1.0 It now filters the mail color, instead of accent.
	 * @param string $colorMain A hexadecimal color.
	 */
	\apply_filters( 'the_seo_framework_sitemap_color_main', $colors['main'] ),
);
$color_accent = '#' . Sanitize::rgb_hex(
	/**
	 * @since 2.8.0
	 * @since 3.1.0 It now filters the accent color, instead of main.
	 * @param string $colorAccent A hexadecimal color.
	 */
	\apply_filters( 'the_seo_framework_sitemap_color_accent', $colors['accent'] ),
);
$relative_font_color = '#' . Sanitize::rgb_hex(
	/**
	 * @since 2.8.0
	 * @param string $relativeFontColor A hexadecimal color.
	 */
	\apply_filters(
		'the_seo_framework_sitemap_relative_font_color',
		Color::get_relative_fontcolor( $colors['main'] ),
	),
);

$title  = Escape::css_content( Sanitize::metadata_content(
	Data\Blog::get_public_blog_name() . ' &mdash; ' . \__( 'XML Sitemap', 'autodescription' ),
) );
$desc_1 = Escape::css_content( \wp_strip_all_tags( Markdown::convert(
	/* translators: URLs are in Markdown. Don't forget to localize the URLs. */
	\__( 'This is an optimized XML sitemap meant to be processed quickly by search engines like [Google](https://www.google.com/) or [Bing](https://www.bing.com/).', 'autodescription' ),
	[ 'a' ],
	[ 'a_internal' => false ],
) ) );
$desc_2 = Escape::css_content( \wp_strip_all_tags( Markdown::convert(
	/* translators: URLs are in Markdown. Don't localize this URL. */
	\__( 'You can find more information on XML sitemaps at [sitemaps.org](https://www.sitemaps.org/).', 'autodescription' ),
	[ 'a' ],
	[ 'a_internal' => false ],
) ) );

$url_header             = Escape::css_content( \__( 'URL', 'autodescription' ) );
$lastmod_header         = $has_lastmod ? Escape::css_content( \__( 'Last Updated', 'autodescription' ) ) : 'none';
$lastmod_header_display = $has_lastmod ? 'block' : 'none';

// Same inset as `url`: 2rem when narrow, else centered to the table min-width.
$wrap_pad        = "max(2rem, calc((100% - {$table_min_width}px) / 2))";
$logo_image      = 'none';
$logo_size       = '0';
$title_pad_left  = $wrap_pad;
$title_pad_right = $wrap_pad;
$logo_pos        = '0 0';

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
		$logo_width  = (int) ( $_src[1] ?? 29 ); // Magic number "SITEMAP_LOGO_PX"
		$logo_height = (int) ( $_src[2] ?? 29 ); // Magic number "SITEMAP_LOGO_PX"
		$logo_size   = "{$logo_width}px {$logo_height}px";
		$logo_pos    = "calc(-{$logo_width}px - 1.4rem) 0";
		$logo_image  = 'url(' . Escape::css_content( \esc_url(
			$_src[0],
			[ 'https', 'http' ],
		) ) . ')';

		$title_extra    = "{$logo_width}px + 1.4rem"; // 1.4rem: XSL `h1 img` margin-inline-end
		$title_pad_left = "max(calc(2rem + {$title_extra}), calc((100% - {$table_min_width}px) / 2 + {$title_extra}))";

		if ( 'rtl' === $direction ) {
			$logo_pos = "right calc(-{$logo_width}px - 1.4rem) top 0";

			$title_pad_right = $title_pad_left;
			$title_pad_left  = $wrap_pad;
		}
	}
}

$footer_content = 'none';
$footer_display = 'none';

/**
 * @since 2.8.0
 * @param bool $indicator
 */
if ( \apply_filters( 'the_seo_framework_indicator_sitemap', true ) ) {
	$footer_content = Escape::css_content( \wp_strip_all_tags( Markdown::convert(
		/* translators: URLs are in Markdown. */
		\__( 'Generated by [The SEO Framework](https://theseoframework.com/)', 'autodescription' ),
		[ 'a' ],
		[ 'a_internal' => false ],
	) ) );
	$footer_display = 'block';
}

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- This is CSS, not HTML. Also, escaped above.
echo Minify::css(
	<<<CSS
		@namespace url("http://www.sitemaps.org/schemas/sitemap/0.9");
		urlset {
			display: flex;
			flex-direction: column;
			font-size: 62.5%;
			min-height: 100vh;
			margin: 0;
			direction: {$direction};
			font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
			background: #fff;
			box-sizing: border-box;
		}
		urlset::before {
			content: {$title};
			order: -2;
			display: block;
			overflow-wrap: break-word;
			font-size: 2.4rem;
			font-family: Verdana,Geneva,sans-serif;
			font-weight: normal;
			background-color: {$color_main};
			background-image: {$logo_image};
			background-repeat: no-repeat;
			background-origin: content-box;
			background-position: {$logo_pos};
			background-size: {$logo_size};
			image-rendering: -webkit-optimize-contrast;
			color: {$color_accent};
			padding-top: 2rem;
			padding-right: {$title_pad_right};
			padding-bottom: 0;
			padding-left: {$title_pad_left};
		}
		urlset::after {
			/* XSL: two `p` (UA `margin: 1em 0`, collapsed between), `#description` `padding: 2rem 2rem 1.3rem`. */
			content: {$desc_1} "\\A\\A" {$desc_2};
			order: -1;
			display: block;
			white-space: pre-wrap;
			overflow-wrap: break-word;
			font-size: 1.4rem;
			background-color: {$color_main};
			border-bottom: .7rem solid {$color_accent};
			color: {$relative_font_color};
			padding-top: 1em; /* first `p` margin-top; `h1` has `margin: 0` */
			padding-right: {$wrap_pad};
			padding-bottom: calc(1em + 1.3rem); /* last `p` margin-bottom + `#description` padding-bottom */
			padding-left: {$wrap_pad};
		}
		url {
			display: grid;
			grid-template-columns: {$url_columns};
			box-sizing: border-box;
			flex-shrink: 0;
			width: calc(100% - 4rem);
			max-width: {$table_min_width}px;
			margin-inline: auto;
			font-size: 1.4rem;
		}
		url:first-of-type {
			margin-block-start: 2rem;
		}
		url:last-of-type {
			/* XSL `body` is `grid-template-rows: auto 1fr auto`; grow the last row so the footer sits on the viewport. */
			flex: 1 0 auto;
			grid-template-rows: auto 1fr;
			margin-block-end: 2rem;
		}
		url:nth-of-type(2n) loc,
		url:nth-of-type(2n) lastmod {
			background-color: #eaeaea;
		}
		url:first-of-type loc::before,
		url:first-of-type lastmod::before {
			box-sizing: border-box;
			width: calc(100% + 3rem); /* 1.5rem cell padding, both sides */
			font-weight: bold;
			color: #000;
			white-space: normal;
			text-overflow: clip;
			margin-inline: -1.5rem;
			margin-block-end: 1rem;
			padding: 1rem 1.5rem;
			border-bottom: 1px solid {$color_accent};
		}
		url:first-of-type loc::before {
			content: {$url_header};
			display: block;
		}
		url:first-of-type lastmod::before {
			content: {$lastmod_header};
			display: {$lastmod_header_display};
		}
		url:first-of-type loc {
			overflow: visible;
		}
		url:last-of-type::after {
			content: {$footer_content};
			display: {$footer_display};
			grid-column: 1 / -1;
			align-self: end;
			padding: 2rem 0;
			margin: 1rem 0;
			font-size: 1.1rem;
			color: #999;
		}
		loc,
		lastmod {
			font-size: 1.2rem;
			padding: 1rem 1.5rem;
			max-width: {$cell_max_width}px;
			min-width: 113px; /* magic number: XSL cell floor */
		}
		loc {
			min-width: 0;
			overflow: hidden;
			white-space: pre;
			text-overflow: ellipsis;
		}
		lastmod {
			/* W3C datetime as stored. CSS cannot substring; do not rewrite lastmod in XML to pretty-print. */
			white-space: nowrap;
		}
	CSS,
);
