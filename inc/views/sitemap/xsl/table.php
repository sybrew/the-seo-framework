<?php
/**
 * @package The_SEO_Framework\Views\Sitemap\XSL\Table
 * @subpackage The_SEO_Framework\Sitemap\XSL
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) or die;

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

$vars  = [
	'itemURL' => '<xsl:variable name="itemURL" select="sitemap:loc"/>',
	'lastmod' => '<xsl:variable name="lastmod" select="concat(substring(sitemap:lastmod,0,11),concat(\' \',substring(sitemap:lastmod,12,8)))"/>',
];
$empty = array_fill_keys( [ 'th', 'td' ], '' );

$url = [
	'th' => \sprintf( '<th>%s</th>', \esc_xml( \__( 'URL', 'autodescription' ) ) ),
	'td' => '<td><a href="{$itemURL}"><xsl:choose><xsl:when test="string-length($itemURL)&gt;95"><xsl:value-of select="substring($itemURL,0,93)" />...</xsl:when><xsl:otherwise><xsl:value-of select="$itemURL" /></xsl:otherwise></xsl:choose></a></td>',
];

if ( \The_SEO_Framework\Data\Plugin::get_option( 'sitemaps_modified' ) ) {
	$last_updated = [
		'th' => \sprintf( '<th>%s</th>', \esc_xml( \__( 'Last Updated', 'autodescription' ) ) ),
		'td' => '<td><xsl:value-of select="$lastmod" /></td>',
	];
} else {
	$last_updated = $empty;
	unset( $vars['lastmod'] );
}

// phpcs:disable, WordPress.Security.EscapeOutput, output is escaped.
?>
<table>
	<thead>
		<tr>
			<?= $url['th'] ?>
			<?= $last_updated['th'] ?>
		</tr>
	</thead>
	<tbody>
	<xsl:for-each select="sitemap:urlset/sitemap:url">
		<?= implode( $vars ) ?>
		<tr>
			<?= $url['td'] ?>
			<?= $last_updated['td'] ?>
		</tr>
	</xsl:for-each>
	</tbody>
</table>
