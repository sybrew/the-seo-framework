<?php
/**
 * @package The_SEO_Framework\Classes\Interpreters\SEOBar
 * @subpackage The_SEO_Framework\SEOBar
 */

namespace The_SEO_Framework\Interpreters;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

\tsf()->_deprecated_function( 'The_SEO_Framework\Interpreters\SEOBar', '5.0.0' );
/**
 * Interprets the SEO Bar into an HTML item.
 *
 * @since 4.0.0
 * @since 5.0.0 1. Moved to `\The_SEO_Framework\Admin\SEOBar\Builder`.
 *              2. Deprecated.
 * @access protected
 * @deprecated
 */
class_alias( 'The_SEO_Framework\Admin\SEOBar\Builder', 'The_SEO_Framework\Interpreters\SEOBar', true );
