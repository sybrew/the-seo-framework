<?php
/**
 * @package The_SEO_Framework\Views\Sitemap
 * @subpackage The_SEO_Framework\Sitemap
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and tsf()->_verify_include_secret( $_secret ) or die;

// echo here, otherwise XML closes PHP...
echo '<?xml version="1.0" encoding="UTF-8"?>', PHP_EOL;

?>
<xsl:stylesheet version="2.0"
				xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
				xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>
	<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes( 'html' ); ?>>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
				<meta name="viewport" content="width=device-width, initial-scale=1" />
				<?php
				/**
				 * @since 3.1.0
				 * @param \The_SEO_Framework\Load $this Alias of `tsf()`
				 */
				do_action( 'the_seo_framework_xsl_head', $this );
				?>
			</head>
			<body class="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
				<div id="description">
					<div class="wrap">
						<?php
						/**
						 * @since 3.1.0
						 * @param \The_SEO_Framework\Load $this Alias of `tsf()`
						 */
						do_action( 'the_seo_framework_xsl_description', $this );
						?>
					</div>
				</div>
				<div id="content">
					<div class="wrap">
						<?php
						/**
						 * @since 3.1.0
						 * @param \The_SEO_Framework\Load $this Alias of `tsf()`
						 */
						do_action( 'the_seo_framework_xsl_content', $this );
						?>
					</div>
				</div>
				<div id="footer">
					<div class="wrap">
						<?php
						/**
						 * @since 3.1.0
						 * @param \The_SEO_Framework\Load $this Alias of `tsf()`
						 */
						do_action( 'the_seo_framework_xsl_footer', $this );
						?>
					</div>
				</div>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>
<?php
