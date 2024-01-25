<?php
/**
 * @package The_SEO_Framework\Views\Sitemap
 * @subpackage The_SEO_Framework\Sitemap
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) or die;

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * The SEO Framework plugin
 * Copyright (C) 2017 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// echo here, otherwise XML closes PHP...
echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";

?>
<xsl:stylesheet version="2.0"
				xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
				xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>
	<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml" <?php \language_attributes( 'html' ); ?>>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
				<meta name="viewport" content="width=device-width, initial-scale=1" />
				<?php
				/**
				 * @since 3.1.0
				 * @param \The_SEO_Framework\Load Alias of `tsf()`
				 * @TODO 5.1.0 Remove first parameter. It's useless now.
				 */
				\do_action( 'the_seo_framework_xsl_head', \tsf() );
				?>
			</head>
			<body class="<?= \is_rtl() ? 'rtl' : 'ltr' ?>">
				<div id="description">
					<div class="wrap">
						<?php
						/**
						 * @since 3.1.0
						 * @param \The_SEO_Framework\Load Alias of `tsf()`
						 * @TODO 5.1.0 Remove first parameter. It's useless now.
						 */
						\do_action( 'the_seo_framework_xsl_description', \tsf() );
						?>
					</div>
				</div>
				<div id="content">
					<div class="wrap">
						<?php
						/**
						 * @since 3.1.0
						 * @param \The_SEO_Framework\Load Alias of `tsf()`
						 * @TODO 5.1.0 Remove first parameter. It's useless now.
						 */
						\do_action( 'the_seo_framework_xsl_content', \tsf() );
						?>
					</div>
				</div>
				<div id="footer">
					<div class="wrap">
						<?php
						/**
						 * @since 3.1.0
						 * @param \The_SEO_Framework\Load Alias of `tsf()`
						 * @TODO 5.1.0 Remove first parameter. It's useless now.
						 */
						\do_action( 'the_seo_framework_xsl_footer', \tsf() );
						?>
					</div>
				</div>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>
<?php
