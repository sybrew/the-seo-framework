<?php
/**
 * @package The_SEO_Framework\Views\Sitemap
 */
namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = \the_seo_framework_class() and $this instanceof $_this or die;

\add_action( 'the_seo_framework_xsl_head', __NAMESPACE__ . '\\_print_xsl_global_variables', 0 );
/**
 * Prints global XSL variables.
 *
 * @since 3.1.0
 * @access private
 * @TODO move this to a dedicated sitemap "module" (a system that loads everything sitemap related).
 * @param \The_SEO_Framework\Load $tsf
 */
function _print_xsl_global_variables( $tsf ) {

	//= Styles generic.
	printf(
		'<xsl:variable name="tableMinWidth" select="\'%s\'"/>',
		$tsf->get_option( 'sitemaps_modified' ) ? '600px' : '450px'
	);

	$colors = $tsf->get_sitemap_colors();

	//= Styles colors.
	printf(
		'<xsl:variable name="colorMain" select="\'%s\'"/>',
		'#' . $tsf->s_color_hex(
			/**
			 * @since 2.8.0
			 * @since 3.1.0 It now filters the mail color, instead of accent.
			 * @param string $colorMain A hexadecimal color.
			 */
			\apply_filters( 'the_seo_framework_sitemap_color_main', $colors['main'] )
		)
	); // xss ok.
	printf(
		'<xsl:variable name="colorAccent" select="\'%s\'"/>',
		'#' . $tsf->s_color_hex(
			/**
			 * @since 2.8.0
			 * @since 3.1.0 It now filters the accent color, instead of main.
			 * @param string $colorAccent A hexadecimal color.
			 */
			\apply_filters( 'the_seo_framework_sitemap_color_accent', $colors['accent'] )
		)
	); // xss ok.
	printf(
		'<xsl:variable name="relativeFontColor" select="\'%s\'"/>',
		'#' . $tsf->s_color_hex(
			/**
			 * @since 2.8.0
			 * @param string $relativeFontColor A hexadecimal color.
			 */
			\apply_filters(
				'the_seo_framework_sitemap_relative_font_color',
				$tsf->get_relative_fontcolor( $colors['main'] )
			)
		)
	); // xss ok.
}

\add_action( 'the_seo_framework_xsl_head', __NAMESPACE__ . '\\_print_xsl_title' );
/**
 * Prints XSL title.
 *
 * @since 3.1.0
 * @access private
 * @TODO move this to a dedicated sitemap "module" (a system that loads everything sitemap related).
 * @param \The_SEO_Framework\Load $tsf
 */
function _print_xsl_title( $tsf ) {
	$title = \__( 'XML Sitemap', 'autodescription' );
	//? Trick the system into thinking (your CPU is a rock that's tricked doing this) it's not a real page, by feeding a signed int.
	$tsf->use_title_branding( [ 'id' => -1 ] ) and $tsf->merge_title_branding( $title, [ 'id' => -1 ] );

	printf(
		'<title>%s</title>',
		\esc_html( \ent2ncr( $title ) )
	);
}

\add_action( 'the_seo_framework_xsl_head', __NAMESPACE__ . '\\_print_xsl_styles' );
/**
 * Prints XSL styles.
 *
 * @since 3.1.0
 * @access private
 * @TODO move this to a dedicated sitemap "module" (a system that loads everything sitemap related).
 * @param \The_SEO_Framework\Load $tsf
 */
function _print_xsl_styles( $tsf ) {

	$styles = <<<'STYLES'
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
		color: <xsl:value-of select="$colorAccent" />;
	}
	h1 img {
		vertical-align: bottom;
		margin-right: 14px;
		image-rendering: -webkit-optimize-contrast;
	}
	#description {
		background-color: <xsl:value-of select="$colorMain" />;
		border-bottom: 7px solid <xsl:value-of select="$colorAccent" />;
		color: <xsl:value-of select="$relativeFontColor" />;
		padding: 30px 30px 20px;
	}
	#description a {
		color: <xsl:value-of select="$relativeFontColor" />;
	}
	#content {
		padding: 10px 30px 30px;
		background: #fff;
	}
	a:hover {
		border-bottom: 1px solid;
	}
	table {
		min-width: <xsl:value-of select="$tableMinWidth" />;
		border-spacing: 0;
	}
	th, td {
		font-size: 12px;
		border: 0px solid;
		padding: 10px 15px;
	}
	th {
		text-align: left;
		border-bottom: 1px solid <xsl:value-of select="$colorAccent" />;
	}
	tr:nth-of-type(2n+3) {
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
STYLES;
	/**
	 * @since 3.1.0
	 * @param string $styles The sitemap XHTML styles.
	 */
	printf( '<style style="text/css">%s</style>', \apply_filters( 'the_seo_framework_sitemap_styles', $styles ) ); // xss ok.
}

\add_action( 'the_seo_framework_xsl_description', __NAMESPACE__ . '\\_print_xsl_description' );
/**
 * Prints XSL description.
 *
 * @since 3.1.0
 * @access private
 * @TODO move this to a dedicated sitemap "module" (a system that loads everything sitemap related).
 * @param \The_SEO_Framework\Load $tsf
 */
function _print_xsl_description( $tsf ) {

	$logo = '';
	if ( $tsf->get_option( 'sitemap_logo' ) ) {

		$_src = $tsf->can_use_logo() ? \wp_get_attachment_image_src( \get_theme_mod( 'custom_logo' ), [ 29, 29 ] ) : [];
		/**
		 * @since 2.8.0
		 * @param array $_src The logo: {
		 *    0 => The image URL,
		 *    1 => width in px,
		 *    2 => height in px,
		 * }
		 */
		$_src = (array) \apply_filters( 'the_seo_framework_sitemap_logo', $_src );

		if ( ! empty( $_src[0] ) ) {
			$logo = sprintf( '<img src="%s" width="%s" height="%s" />', \esc_url( $_src[0] ), \esc_attr( $_src[1] ), \esc_attr( $_src[2] ) );
		}
	}

	echo \wp_kses(
		sprintf(
			'<a href="%s"><h1>%s%s</h1></a>',
			\esc_url( \ent2ncr( \get_home_url() ), [ 'http', 'https' ] ),
			$logo,
			\esc_html( \ent2ncr(
				$tsf->get_blogname() . ' &mdash; ' . \__( 'XML Sitemap', 'autodescription' )
			) )
		),
		[
			'h1' => true,
			'a' => [
				'href' => true,
			],
			'img' => [
				'src'    => true,
				'width'  => true,
				'height' => true,
			],
		]
	);
	printf( '<p>%s</p>',
		\wp_kses(
			$tsf->convert_markdown(
				\ent2ncr(
					/* translators: URLs are in Markdown. Don't forget to localize the URLs. */
					\__( 'This is a generated XML Sitemap, meant to be consumed by search engines like [Google](https://www.google.com/) or [Bing](https://www.bing.com/).', 'autodescription' )
				),
				[ 'a' ],
				[ 'a_internal' => false ]
			),
			[
				'a' => [
					'href'   => true,
					'target' => true,
					'rel'    => true,
				],
			]
		)
	);
	printf(
		'<p>%s</p>',
		\wp_kses(
			\ent2ncr(
				$tsf->convert_markdown(
					/* translators: URLs are in Markdown. Don't localize this URL. */
					\__( 'You can find more information on XML sitemaps at [sitemaps.org](https://www.sitemaps.org/).', 'autodescription' ),
					[ 'a' ],
					[ 'a_internal' => false ]
				)
			),
			[
				'a' => [
					'href' => true,
					'target' => true,
					'rel' => true,
				],
			]
		)
	);
}

\add_action( 'the_seo_framework_xsl_content', __NAMESPACE__ . '\\_print_xsl_content' );
/**
 * Prints XSL content.
 *
 * @since 3.1.0
 * @access private
 * @TODO move this to a dedicated sitemap "module" (a system that loads everything sitemap related).
 * @param \The_SEO_Framework\Load $tsf
 */
function _print_xsl_content( $tsf ) {

	$vars = [
		'itemURL'  => '<xsl:variable name="itemURL" select="sitemap:loc"/>',
		'lastmod'  => '<xsl:variable name="lastmod" select="concat(substring(sitemap:lastmod,0,11),concat(\' \',substring(sitemap:lastmod,12,5)))"/>',
		'priority' => '<xsl:variable name="priority" select="substring(sitemap:priority,0,4)"/>',
	];
	$empty = array_fill_keys( [ 'th', 'td' ], '' );

	$url = [
		'th' => sprintf( '<th>%s</th>', \esc_html( \ent2ncr( \__( 'URL', 'autodescription' ) ) ) ),
		'td' => '<td><a href="{$itemURL}"><xsl:choose><xsl:when test="string-length($itemURL)&gt;99"><xsl:value-of select="substring($itemURL,0,96)" />...</xsl:when><xsl:otherwise><xsl:value-of select="$itemURL" /></xsl:otherwise></xsl:choose></a></td>',
	];

	if ( $tsf->get_option( 'sitemaps_modified' ) ) {
		$last_updated = [
			'th' => sprintf( '<th>%s</th>', \esc_html( \ent2ncr( \__( 'Last Updated', 'autodescription' ) ) ) ),
			'td' => '<td><xsl:value-of select="$lastmod" /></td>',
		];
	} else {
		$last_updated = $empty;
		unset( $vars['lastmod'] );
	}

	if ( $tsf->get_option( 'sitemaps_priority' ) ) {
		$priority = [
			'th' => sprintf( '<th>%s</th>', \esc_html( \ent2ncr( \__( 'Priority', 'autodescription' ) ) ) ),
			'td' => '<td><xsl:value-of select="$priority" /></td>',
		];
	} else {
		$priority = $empty;
		unset( $vars['priority'] );
	}

	$vars = implode( $vars );

	$content = <<<CONTENT
<table>
	<tr>
		{$url['th']}
		{$last_updated['th']}
		{$priority['th']}
	</tr>
	<xsl:for-each select="sitemap:urlset/sitemap:url">
		$vars
		<tr>
			{$url['td']}
			{$last_updated['td']}
			{$priority['td']}
		</tr>
	</xsl:for-each>
</table>
CONTENT;

	echo $content; // xss OK.
}

\add_action( 'the_seo_framework_xsl_footer', __NAMESPACE__ . '\\_print_xsl_footer' );
/**
 * Prints XSL footer.
 *
 * @since 3.1.0
 * @access private
 * @TODO move this to a dedicated sitemap "module" (a system that loads everything sitemap related).
 * @param \The_SEO_Framework\Load $tsf
 */
function _print_xsl_footer( $tsf ) {

	/**
	 * @since 2.8.0
	 * @param bool $indicator
	 */
	\apply_filters( 'the_seo_framework_indicator_sitemap', true )
		and printf( '<p>%s</p>',
			\wp_kses(
				$tsf->convert_markdown(
					/* translators: URLs are in Markdown. */
					\ent2ncr( \__( 'Generated by [The SEO Framework](https://theseoframework.com/)', 'autodescription' ) ),
					[ 'a' ],
					[ 'a_internal' => false ]
				),
				[
					'a' => [
						'href'   => true,
						'target' => true,
						'rel'    => true,
					],
				]
			)
		);
}

\add_filter( 'site_icon_meta_tags', __NAMESPACE__ . '\\_convert_site_icon_meta_tags', PHP_INT_MAX );
/**
 * Converts meta tags that aren't XHTML to XHTML, loosely.
 * Doesn't fix attribute minimization. TODO?
 *
 * @since 3.1.4
 *
 * @param array $meta_tags Site Icon meta elements.
 * @return array The converted meta tags.
 */
function _convert_site_icon_meta_tags( $tags ) {

	foreach ( $tags as &$tag ) {
		$tag = \force_balance_tags( $tag );
		$tag = \wp_kses( $tag, [
			'link' => [
				'charset'  => [],
				'rel'      => [],
				'sizes'    => [],
				'href'     => [],
				'hreflang' => [],
				'media'    => [],
				'rev'      => [],
				'target'   => [],
				'type'     => [],
			],
			'meta' => [
				'content'    => [],
				'property'   => [],
				'http-equiv' => [],
				'name'       => [],
				'scheme'     => [],
			],
		], [] );
	}

	return $tags;
}

// echo here, otherwise it closes PHP.
echo '<?xml version="1.0" encoding="UTF-8"?>';

?>
<xsl:stylesheet version="2.0"
				xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
				xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>
	<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
				<?php
				/**
				 * @since 3.1.0
				 * @param \The_SEO_Framework\Load $this Alias of `the_seo_framework()`
				 */
				do_action( 'the_seo_framework_xsl_head', $this );
				?>
			</head>
			<body>
				<div id="description">
					<?php
					/**
					 * @since 3.1.0
					 * @param \The_SEO_Framework\Load $this Alias of `the_seo_framework()`
					 */
					do_action( 'the_seo_framework_xsl_description', $this );
					?>
				</div>
				<div id="content">
					<?php
					/**
					 * @since 3.1.0
					 * @param \The_SEO_Framework\Load $this Alias of `the_seo_framework()`
					 */
					do_action( 'the_seo_framework_xsl_content', $this );
					?>
				</div>
				<div id="footer">
					<?php
					/**
					 * @since 3.1.0
					 * @param \The_SEO_Framework\Load $this Alias of `the_seo_framework()`
					 */
					do_action( 'the_seo_framework_xsl_footer', $this );
					?>
				</div>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>
<?php
