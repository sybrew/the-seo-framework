<?php
/**
 * @package The_SEO_Framework
 * @subpackage The_SEO_Framework\Bootstrap
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Admin\Script\AJAX;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * We do not test for headlessness here.
 * These callbacks respond to only registered actions, and those do check headlessness.
 */

// Admin AJAX for notice dismissal.
\add_action( 'wp_ajax_tsf_dismiss_notice', [ AJAX::class, 'dismiss_notice' ] );

// Admin AJAX for cropping images.
\add_action( 'wp_ajax_tsf_crop_image', [ AJAX::class, 'crop_image' ] );

// Admin AJAX for counter options.
\add_action( 'wp_ajax_tsf_update_counter', [ AJAX::class, 'update_counter_type' ] );

// Admin AJAX for Gutenberg data update.
\add_action( 'wp_ajax_tsf_update_post_data', [ AJAX::class, 'get_post_data' ] );

// Admin AJAX for Term slug fetching.
\add_action( 'wp_ajax_tsf_get_term_parent_slugs', [ AJAX::class, 'get_term_parent_slugs' ] );

// Admin AJAX for Post slug fetching.
\add_action( 'wp_ajax_tsf_get_post_parent_slugs', [ AJAX::class, 'get_post_parent_slugs' ] );

// Admin AJAX for Author slug fetching.
\add_action( 'wp_ajax_tsf_get_author_slug', [ AJAX::class, 'get_author_slug' ] );
