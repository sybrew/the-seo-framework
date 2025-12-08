<?php
/**
 * @package The_SEO_Framework\Classes\Internal\Deprecated
 * @subpackage The_SEO_Framework\Debug\Deprecated
 */

namespace The_SEO_Framework\Internal;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

// Precautionary.
use function The_SEO_Framework\{
	is_headless,
	normalize_generation_args,
	get_query_type_from_args,
	memo,
	umemo,
};

// Precautionary.
use The_SEO_Framework\{
	Data,
	Helper,
	Helper\Query,
	Meta,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2025 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

/**
 * Class The_SEO_Framework\Internal\Deprecated
 *
 * Contains all deprecated methods of `\tsf()`
 *
 * @since 2.8.0
 * @since 3.1.0 Removed all methods deprecated in 3.0.0.
 * @since 4.0.0 Removed all methods deprecated in 3.1.0.
 * @since 4.1.4 Removed all methods deprecated in 4.0.0.
 * @since 4.2.0 1. Changed namespace from \The_SEO_Framework to \The_SEO_Framework\Internal
 *              2. Removed all methods deprecated in 4.1.0.
 * @since 5.0.0 Removed all methods deprecated in 4.2.0
 * @since 5.1.3 Removed all methods deprecated in 5.0.0 (~24 months later)
 * @ignore
 */
final class Deprecated {}
