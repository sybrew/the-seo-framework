<?php
/**
 * @package The_SEO_Framework\Views\Sitemap\XSL\Table
 * @subpackage The_SEO_Framework\Sitemap\XSL
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Admin\Template::verify_secret( $secret ) or die;

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * The SEO Framework plugin
 * Copyright (C) 2021 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

$title    = \__( 'XML Sitemap', 'autodescription' );
$sep      = \The_SEO_Framework\Meta\Title::get_separator(); // Lacking import OK.
$addition = \The_SEO_Framework\Data\Blog::get_public_blog_name(); // Lacking import OK.

?>
<title><?= \esc_xml( \tsf()->sanitize_text( "$title $sep $addition" ) ) ?></title>
