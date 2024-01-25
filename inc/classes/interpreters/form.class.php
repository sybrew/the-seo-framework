<?php
/**
 * @package The_SEO_Framework\Classes\Interpreters\Form
 * @subpackage The_SEO_Framework\Admin\Settings
 */

namespace The_SEO_Framework\Interpreters;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

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

\tsf()->_deprecated_function( 'The_SEO_Framework\Interpreters\Form', '5.0.0', 'The_SEO_Framework\Admin\Settings\Layout\Form' );
/**
 * Interprets anything you send here into Form HTML. Or so it should.
 *
 * @since 4.1.4
 * @since 5.0.0 1. Moved to `\The_SEO_Framework\Admin\Settings\Layout\Form`.
 *              2. Deprecated.
 *
 * @access private
 * @deprecated
 */
class_alias( 'The_SEO_Framework\Admin\Settings\Layout\Form', 'The_SEO_Framework\Interpreters\Form', true );
