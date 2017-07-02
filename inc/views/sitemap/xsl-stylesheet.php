<?php
/**
 * @package The_SEO_Framework\Views\Sitemap
 */

defined( 'ABSPATH' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

$title = __( 'XML Sitemap', 'autodescription' );
if ( $this->add_title_additions() )
	$title = $this->process_title_additions( $title, $this->get_blogname(), $this->get_title_seplocation( '', false ) );

if ( $this->is_option_checked( 'sitemap_logo' ) ) {

	$logo = $this->can_use_logo() ? wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), array( 29, 29 ) ) : array();
	/**
	 * Applies filters 'the_seo_framework_sitemap_logo' : array
	 * @since 2.8.0
	 */
	$logo = (array) apply_filters( 'the_seo_framework_sitemap_logo', $logo );

	if ( ! empty( $logo[0] ) ) {
		$logo = sprintf( '<img src="%s" width="%s" height="%s" />', esc_url( $logo[0] ), esc_attr( $logo[1] ), esc_attr( $logo[2] ) );
	} else {
		$logo = '';
	}
} else {
	$logo = '';
}

$colors = $this->get_sitemap_colors();

/**
 * Applies filters 'the_seo_framework_sitemap_color_main' : string
 * @since 2.8.0
 */
$sitemap_color_main = '#' . $this->s_color_hex( (string) apply_filters( 'the_seo_framework_sitemap_color_accent', $colors['main'] ) );

/**
 * Applies filters 'the_seo_framework_sitemap_color_accent' : string
 * @since 2.8.0
 */
$sitemap_color_accent = '#' . $this->s_color_hex( (string) apply_filters( 'the_seo_framework_sitemap_color_main', $colors['accent'] ) );

/**
 * Applies filters 'the_seo_framework_sitemap_relative_font_color' : string
 * @since 2.8.0
 */
$relative_font_color = '#' . $this->s_color_hex( (string) apply_filters( 'the_seo_framework_sitemap_relative_font_color', $this->get_relative_fontcolor( $sitemap_color_main ) ) );

/**
 * Applies filters 'the_seo_framework_indicator_sitemap' : boolean
 * @since 2.8.0
 */
$indicator = (bool) apply_filters( 'the_seo_framework_indicator_sitemap', true );

$output_modified = $this->is_option_checked( 'sitemaps_modified' );

$xml = '<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0"
				xmlns:html="http://www.w3.org/TR/REC-html40"
				xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
				xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>
	<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<title>' . esc_html( ent2ncr( $title ) ) . '</title>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
				<style type="text/css">
					body {
						font-size: 14px;
						font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
						margin: 0;
					}
					a {
						color: #05809e;
						text-decoration: none;
					}
					h1 {
						font-size: 24px;
						font-family: Verdana,Geneva,sans-serif;
						font-weight: normal;
						margin: 0;
						color: ' . $sitemap_color_accent . ';
					}
					h1 img {
						vertical-align: bottom;
						margin-right: 14px;
						image-rendering: -webkit-optimize-contrast;
					}
					#description {
						background-color: ' . $sitemap_color_main . ';
						border-bottom: 7px solid ' . $sitemap_color_accent . ';
						color: ' . $relative_font_color . ';
						padding: 30px 30px 20px;
					}
					#description a {
						color: ' . $relative_font_color . ';
					}
					#content {
						padding: 10px 30px 30px;
						background: #fff;
					}
					a:hover {
						border-bottom: 1px solid;
					}
					table {
						min-width: ' . ( $output_modified ? '600' : '450' ) . 'px;
						border-spacing: 0;
					}
					th, td {
						font-size: 12px;
						border: 0px solid;
						padding: 10px 15px;
					}
					th {
						text-align: left;
						border-bottom: 1px solid ' . $sitemap_color_accent . ';
					}
					.odd {
						background-color: #eaeaea;
					}
					#footer {
						margin: 20px 30px;
						font-size: 12px;
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
				</style>
			</head>
			<body>
				<div id="description">
					<a href="' . esc_url( ent2ncr( get_home_url() ), array( 'http', 'https' ) ) . '"><h1>' .
						ent2ncr( $logo ) .
						esc_html( ent2ncr( $this->get_blogname() . ' &mdash; ' . __( 'XML Sitemap', 'autodescription' ) ) ) . '</h1></a>
					<p>' .
					wp_kses(
						ent2ncr(
							$this->convert_markdown(
								/* translators: URLs are in Markdown. Don't forget to localize the URLs. */
								__( 'This is a generated XML Sitemap, meant to be consumed by search engines like [Google](https://www.google.com/) or [Bing](https://www.bing.com/).', 'autodescription' ),
								array( 'a' ),
								array( 'a_internal' => false )
						 	)
						),
						array(
							'a' => array(
								'href' => true,
								'target' => true,
								'rel' => true,
							),
						)
					) . '</p>
					<p>' .
					wp_kses(
						ent2ncr(
							$this->convert_markdown(
								/* translators: URLs are in Markdown. Don't localize this URL. */
								__( 'You can find more information on XML sitemaps at [sitemaps.org](https://www.sitemaps.org/).', 'autodescription' ),
								array( 'a' ),
								array( 'a_internal' => false )
							)
						),
						array(
							'a' => array(
								'href' => true,
								'target' => true,
								'rel' => true,
							),
						)
					) . '</p>
				</div>
				<div id="content">
					<table>
						<tr>
							<th>' . esc_html( ent2ncr( __( 'URL', 'autodescription' ) ) ) . '</th>';

if ( $output_modified ) :
	$xml .= '
							<th>' . esc_html( ent2ncr( __( 'Last Updated', 'autodescription' ) ) ) . '</th>';
endif;

$xml .= '
							<th>' . esc_html( ent2ncr( __( 'Priority', 'autodescription' ) ) ) . '</th>
						</tr>
						<xsl:variable name="lower" select="\'abcdefghijklmnopqrstuvwxyz\'"/>
						<xsl:variable name="upper" select="\'ABCDEFGHIJKLMNOPQRSTUVWXYZ\'"/>
						<xsl:for-each select="sitemap:urlset/sitemap:url">
							<tr>
								<xsl:choose>
									<xsl:when test="position() mod 2 != 1">
										<xsl:attribute name="class">odd</xsl:attribute>
									</xsl:when>
								</xsl:choose>
								<td>
									<xsl:variable name="itemURL">
										<xsl:value-of select="sitemap:loc"/>
									</xsl:variable>
									<a href="{$itemURL}">
										<xsl:value-of select="sitemap:loc"/>
									</a>
								</td>';

if ( $output_modified ) :
	$xml .= '
								<td>
									<xsl:value-of select="concat(substring(sitemap:lastmod,0,11),concat(\' \', substring(sitemap:lastmod,12,5)))"/>
								</td>';
endif;

$xml .= '
								<td>
									<xsl:value-of select="substring(sitemap:priority,0,4)"/>
								</td>
							</tr>
						</xsl:for-each>
					</table>
				</div>
				<div id="footer">';

if ( $indicator ) :
	$xml .= '
					<p>' .
					wp_kses(
						ent2ncr(
							$this->convert_markdown(
								/* translators: URLs are in Markdown. */
								__( 'Generated by [The SEO Framework](https://wordpress.org/plugins/autodescription/)', 'autodescription' ),
								array( 'a' ),
								array( 'a_internal' => false )
							)
						),
						array(
							'a' => array(
								'href' => true,
								'target' => true,
								'rel' => true,
							),
						)
					) . '</p>';
endif;

$xml .= '
				</div>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>';

//* Already escaped.
echo $xml;
