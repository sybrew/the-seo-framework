<?php
defined( 'ABSPATH' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

$title = __( 'XML Sitemap', 'autodescription' );
if ( $this->add_title_additions() )
	$title = $this->process_title_additions( $title, $this->get_blogname(), $this->get_title_seplocation( '', false ) );

/**
 * Applies filters 'the_seo_framework_sitemap_logo' : array
 * @since 2.8.0
 */
$logo = (array) apply_filters( 'the_seo_framework_sitemap_logo', wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), array( 29, 29 ) ) );
if ( ! empty( $logo[0] ) ) {
	$logo = sprintf( '<img src="%s" width="%s" height="%s" />', $logo[0], $logo[1], $logo[2] );
} else {
	$logo = '';
}

/**
 * Applies filters 'the_seo_framework_sitemap_color' : string
 * @since 2.8.0
 * @see https://github.com/EastDesire/jscolor for option? It's GPLv3 :)
 * Or... use a dropdown of common WP/TSF colors.
 */
$sitemap_color = (string) ltrim( apply_filters( 'the_seo_framework_sitemap_color', '00cd98' ), '#' );

/**
 * Applies filters 'the_seo_framework_indicator_sitemap' : boolean
 * @since 2.8.0
 */
$indicator = (bool) apply_filters( 'the_seo_framework_indicator_sitemap', true );

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
						font: 14px -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
						margin: 0;
					}
					a {
						color: #05809e;
						text-decoration: none;
					}
					h1 {
						font: 24px Verdana,Geneva,sans-serif;
						margin: 0;
						color: #' . $sitemap_color . ';
					}
					h1 img {
						vertical-align: bottom;
						margin-right: 14px;
					}
					#description {
						background-color: #333;
						border-bottom: 7px solid #' . $sitemap_color . ';
						color: #f1f1f1;
						padding: 30px 30px 20px;
					}
					#description a {
						color: #f1f1f1;
					}
					#content {
						padding: 10px 30px 30px;
						background: #fff;
					}
					a:hover {
						border-bottom: 1px solid;
					}
					table {
						min-width: 600px;
					}
					th, td {
						font-size: 12px;
					}
					th {
						text-align: left;
						border-bottom: 1px solid #ccc;
					}
					th, td {
						padding: 10px 15px;
					}
					.odd {
						background-color: #f1f1f1;
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
					<a href="' . esc_url( get_home_url() ) . '"><h1>' .
						ent2ncr( $logo ) .
						esc_html( ent2ncr( $this->get_blogname() . ' &mdash; ' . __( 'XML Sitemap', 'autodescription' ) ) ) . '</h1></a>
					<p>' .
					wp_kses(
						ent2ncr(
							$this->convert_markdown(
								/* translators: URLs are in Markdown. */
								__( 'This is a generated XML Sitemap, meant to be consumed by search engines like [Google](https://www.google.com/) or [Bing](https://www.bing.com/).', 'autodescription' ),
								array( 'a' )
						 	)
						),
						array(
							'a' => array(
								'href' => true,
								'rel' => true,
							),
						)
					) . '</p>
					<p>' .
					wp_kses(
						ent2ncr(
							$this->convert_markdown(
								/* translators: URLs are in Markdown. */
								__( 'You can find more information on XML sitemaps at [sitemaps.org](https://www.sitemaps.org/).', 'autodescription' ),
								array( 'a' )
							)
						),
						array(
							'a' => array(
								'href' => true,
								'rel' => true,
							),
						)
					) . '</p>
				</div>
				<div id="content">
					<table>
						<tr>
							<th>' . esc_html( ent2ncr( __( 'URL', 'autodescription' ) ) ) . '</th>';

if ( $this->is_option_checked( 'sitemaps_modified' ) ) :
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

if ( $this->is_option_checked( 'sitemaps_modified' ) ) :
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
								array( 'a' )
							)
						),
						array(
							'a' => array(
								'href' => true,
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

echo $xml;
