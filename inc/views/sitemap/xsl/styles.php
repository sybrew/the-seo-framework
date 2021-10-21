<?php
/**
 * @package The_SEO_Framework\Views\Sitemap\XSL\Styles
 * @subpackage The_SEO_Framework\Sitemap\XSL
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and tsf()->_verify_include_secret( $_secret ) or die;

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
		margin-right: 1.4rem;
		image-rendering: -webkit-optimize-contrast;
	}
	.rtl h1 img {
		margin-right: unset;
		margin-left: 1.4rem;
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
		max-width: <xsl:value-of select="concat( $tableMinWidth - 159, 'px' )" />;
		min-width: 99px;
		overflow-wrap: anywhere;
	}
	th {
		text-align: left;
		border-bottom: 1px solid <xsl:value-of select="$colorAccent" />;
	}
	.rtl th {
		text-align: right;
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
echo apply_filters( 'the_seo_framework_sitemap_styles', $styles );
// phpcs:enable, WordPress.Security.EscapeOutput
?>
</style>
