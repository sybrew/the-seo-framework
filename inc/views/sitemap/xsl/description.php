<?php
/**
 * @package The_SEO_Framework\Views\Sitemap\XSL\Description
 * @subpackage The_SEO_Framework\Sitemap\XSL
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and tsf()->_verify_include_secret( $_secret ) or die;

$logo = '';
if ( $this->get_option( 'sitemap_logo' ) ) {

	$id   = $this->get_option( 'sitemap_logo_id' ) ?: get_theme_mod( 'custom_logo' ) ?: get_option( 'site_icon' );
	$_src = $id ? wp_get_attachment_image_src( $id, [ 29, 29 ] ) : []; // Magic number "SITEMAP_LOGO_PX"

	/**
	 * @since 2.8.0
	 * @param array $_src An empty array, or the logo details: {
	 *    0 => string The image URL,
	 *    1 => int    The width in px,
	 *    2 => int    The height in px,
	 * }
	 */
	$_src = (array) apply_filters( 'the_seo_framework_sitemap_logo', $_src );

	if ( ! empty( $_src[0] ) ) {
		$logo = sprintf(
			'<img src="%s" width="%s" height="%s" />',
			esc_url( $_src[0] ),
			esc_attr( $_src[1] ?? '' ),
			esc_attr( $_src[2] ?? '' )
		);
	}
}

printf(
	'<a href="%s"><h1>%s%s</h1></a>',
	esc_url( get_home_url(), [ 'https', 'http' ] ),
	wp_kses(
		$logo,
		[
			'img' => [
				'src'    => true,
				'width'  => true,
				'height' => true,
			],
		]
	),
	esc_xml(
		$this->s_title_raw( $this->get_blogname() . ' &mdash; ' . __( 'XML Sitemap', 'autodescription' ) )
	)
);
?>

<p>
	<?php
	// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- convert_markdown escapes.
	echo $this->convert_markdown(
		/* translators: URLs are in Markdown. Don't forget to localize the URLs. */
		esc_xml( __( 'This is a generated XML Sitemap, meant to be consumed by search engines like [Google](https://www.google.com/) or [Bing](https://www.bing.com/).', 'autodescription' ) ),
		[ 'a' ],
		[ 'a_internal' => false ]
	);
	?>
</p>
<p>
	<?php
	// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- convert_markdown escapes.
	$this->convert_markdown(
		/* translators: URLs are in Markdown. Don't localize this URL. */
		esc_xml( __( 'You can find more information on XML sitemaps at [sitemaps.org](https://www.sitemaps.org/).', 'autodescription' ) ),
		[ 'a' ],
		[ 'a_internal' => false ]
	);
	?>
</p>
