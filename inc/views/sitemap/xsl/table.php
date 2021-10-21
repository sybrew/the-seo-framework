<?php
/**
 * @package The_SEO_Framework\Views\Sitemap\XSL\Table
 * @subpackage The_SEO_Framework\Sitemap\XSL
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and tsf()->_verify_include_secret( $_secret ) or die;

$vars  = [
	'itemURL' => '<xsl:variable name="itemURL" select="sitemap:loc"/>',
	'lastmod' => '<xsl:variable name="lastmod" select="concat(substring(sitemap:lastmod,0,11),concat(\' \',substring(sitemap:lastmod,12,5)))"/>',
];
$empty = array_fill_keys( [ 'th', 'td' ], '' );

$url = [
	'th' => sprintf( '<th>%s</th>', esc_xml( __( 'URL', 'autodescription' ) ) ),
	'td' => '<td><a href="{$itemURL}"><xsl:choose><xsl:when test="string-length($itemURL)&gt;95"><xsl:value-of select="substring($itemURL,0,93)" />...</xsl:when><xsl:otherwise><xsl:value-of select="$itemURL" /></xsl:otherwise></xsl:choose></a></td>',
];

if ( $this->get_option( 'sitemaps_modified' ) ) {
	$last_updated = [
		'th' => sprintf( '<th>%s</th>', esc_xml( __( 'Last Updated', 'autodescription' ) ) ),
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
			<?php echo $url['th']; ?>
			<?php echo $last_updated['th']; ?>
		</tr>
	</thead>
	<tbody>
	<xsl:for-each select="sitemap:urlset/sitemap:url">
		<?php echo implode( $vars ); ?>
		<tr>
			<?php echo $url['td']; ?>
			<?php echo $last_updated['td']; ?>
		</tr>
	</xsl:for-each>
	</tbody>
</table>
