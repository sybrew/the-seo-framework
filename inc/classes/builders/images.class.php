<?php
/**
 * @package The_SEO_Framework\Classes\Builders\Images
 * @subpackage The_SEO_Framework\Getters\Image
 */

namespace The_SEO_Framework\Builders;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Helper\Query;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

\tsf()->_deprecated_function( 'The_SEO_Framework\Builders\Images', '4.3.0', 'tsf()->image()' );
/**
 * Generates the sitemap.
 *
 * @since 4.0.0
 * @since 4.3.0 1. Moved to \The_SEO_Framework\Meta\Image\Main
 *              2. Deprecated.
 * @abstract
 * @deprecated
 * @ignore
 *
 * @access protected
 *         Use tsf()->image() instead.
 */
class_alias( 'The_SEO_Framework\Builders\Images', 'The_SEO_Framework\Meta\Image\Main', true );
