<?php
/**
 * @package The_SEO_Framework\Classes\Builders\Sitemap
 * @subpackage The_SEO_Framework\Sitemap
 */

namespace The_SEO_Framework\Builders;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2021 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

\tsf()->_deprecated_function( 'The_SEO_Framework\Builders\Sitemap', '4.2.0', 'The_SEO_Framework\Builders\Sitemap\Main' );
/**
 * Generates the sitemap.
 *
 * @since 4.0.0
 * @since 4.2.0 1. Moved to \The_SEO_Framework\Builders\Sitemap\Main
 *              2. Deprecated.
 * @abstract
 * @deprecated
 * @ignore
 *
 * @access protected
 *         Use \The_SEO_Framework\Builders\Sitemap\Main instead.
 */
class_alias( 'The_SEO_Framework\Builders\Sitemap\Main', 'The_SEO_Framework\Builders\Sitemap', true );
