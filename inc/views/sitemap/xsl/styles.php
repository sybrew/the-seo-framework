<?php
/**
 * @package The_SEO_Framework\Views\Sitemap\XSL\Styles
 * @subpackage The_SEO_Framework\Sitemap\XSL
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) or die;

use \The_SEO_Framework\Helper\Format\Minify;

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

$styles = <<<'CSS'
	html {
		font-size: 62.5%;
		height: 100%;
	}
	body {
		font-size: 1.4rem;
		font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
		min-height: 100%;
		display: grid;
		grid-template-rows: auto 1fr auto;
		margin: 0;
	}
	.wrap {
		max-width: <xsl:value-of select="concat( $tableMinWidth, 'px' )" />;
		margin: 0 auto;
		overflow-wrap: break-word;
	}
	a {
		color: #05809e;
		text-decoration: none;
	}
	h1 {
		font-size: 2.4rem;
		font-family: Verdana,Geneva,sans-serif;
		font-weight: normal;
		margin: 0;
		color: <xsl:value-of select="$colorAccent" />;
	}
	h1 img {
		vertical-align: bottom;
		margin-inline-end: 1.4rem;
		image-rendering: -webkit-optimize-contrast;
	}
	#description {
		background-color: <xsl:value-of select="$colorMain" />;
		border-bottom: .7rem solid <xsl:value-of select="$colorAccent" />;
		color: <xsl:value-of select="$relativeFontColor" />;
		padding: 2rem 2rem 1.3rem;
	}
	#description a {
		color: <xsl:value-of select="$relativeFontColor" />;
	}
	#content {
		padding: 2rem;
		background: #fff;
	}
	a:hover {
		border-bottom: 1px solid;
	}
	table {
		border-spacing: 0;
		table-layout: fixed;
	}
	th, td {
		font-size: 1.2rem;
		border: 0px solid;
		padding: 1rem 1.5rem;
		width: 100%;
		/* Magic numbers: sexy primes. Either of these work on their own (+ a few extra pixels): */
		max-width: <xsl:value-of select="concat( $tableMinWidth - 173, 'px' )" />;
		min-width: 113px;
		overflow-wrap: anywhere;
	}
	th {
		text-align: start;
		border-bottom: 1px solid <xsl:value-of select="$colorAccent" />;
	}
	tr:nth-of-type(2n) {
		background-color: #eaeaea;
	}
	#footer {
		padding: 0 3rem 2rem;
		font-size: 1.1rem;
		color: #999;
	}
	#footer a {
		color: inherit;
	}
	#description a, #footer a {
		border-bottom: 1px solid;
	}
	#description a:hover, #footer a:hover {
		border-bottom: none;
	}
CSS;

?>
<style style="text/css">
<?php
// phpcs:disable, WordPress.Security.EscapeOutput
/**
 * @since 3.1.0
 * @param string $styles The sitemap XHTML styles. Must be escaped.
 */
echo Minify::css( \apply_filters( 'the_seo_framework_sitemap_styles', $styles ) );
// phpcs:enable, WordPress.Security.EscapeOutput
?>
</style>
