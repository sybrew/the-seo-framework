<?php
/**
 * @package The_SEO_Framework\Views\Sitemap\XSL\Table
 * @subpackage The_SEO_Framework\Sitemap\XSL
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) or die;

use \The_SEO_Framework\Data\Filter\Sanitize;

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * The SEO Framework plugin
 * Copyright (C) 2021 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
$sep      = Meta\Title::get_separator();
$addition = Data\Blog::get_public_blog_name();

?>
<title><?= \esc_xml( Sanitize::metadata_content( "$title $sep $addition" ) ) ?></title>
