<?php
/**
 * @package The_SEO_Framework\Views\Sitemap\XSL\Vars
 * @subpackage The_SEO_Framework\Sitemap\XSL
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and tsf()->_verify_include_secret( $_secret ) or die;

// Styles generic.
printf(
	'<xsl:variable name="tableMinWidth" select="\'%s\'"/>',
	$this->get_option( 'sitemaps_modified' ) ? '700' : '550'
);

$colors = $this->get_sitemap_colors();

// phpcs:disable, WordPress.Security.EscapeOutput.OutputNotEscaped -- s_color_hex() escapes.
printf(
	'<xsl:variable name="colorMain" select="\'%s\'"/>',
	'#' . $this->s_color_hex(
		/**
		 * @since 2.8.0
		 * @since 3.1.0 It now filters the mail color, instead of accent.
		 * @param string $colorMain A hexadecimal color.
		 */
		apply_filters( 'the_seo_framework_sitemap_color_main', $colors['main'] )
	)
);
printf(
	'<xsl:variable name="colorAccent" select="\'%s\'"/>',
	'#' . $this->s_color_hex(
		/**
		 * @since 2.8.0
		 * @since 3.1.0 It now filters the accent color, instead of main.
		 * @param string $colorAccent A hexadecimal color.
		 */
		apply_filters( 'the_seo_framework_sitemap_color_accent', $colors['accent'] )
	)
);
printf(
	'<xsl:variable name="relativeFontColor" select="\'%s\'"/>',
	'#' . $this->s_color_hex(
		/**
		 * @since 2.8.0
		 * @param string $relativeFontColor A hexadecimal color.
		 */
		apply_filters(
			'the_seo_framework_sitemap_relative_font_color',
			$this->get_relative_fontcolor( $colors['main'] )
		)
	)
);
// phpcs:enable, WordPress.Security.EscapeOutput.OutputNotEscaped
