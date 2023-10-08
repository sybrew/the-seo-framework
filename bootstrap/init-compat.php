<?php
/**
 * @package The_SEO_Framework
 * @subpackage The_SEO_Framework\Bootstrap
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// Disable Headway theme SEO.
\add_filter( 'headway_seo_disabled', '__return_true' );

if ( \tsf()->is_theme( 'genesis' ) ) {
	// Genesis Framework
	_include_compat( 'genesis', 'theme' );
}

if ( \tsf()->detect_plugin( [ 'constants' => [ 'ICL_LANGUAGE_CODE' ] ] ) ) {
	// WPML
	_include_compat( 'wpml', 'plugin' );
}
if ( \tsf()->detect_plugin( [ 'constants' => [ 'POLYLANG_VERSION' ] ] ) ) {
	// Polylang
	_include_compat( 'polylang', 'plugin' );
}

if ( \tsf()->detect_plugin( [ 'globals' => [ 'ultimatemember' ] ] ) ) {
	// Ultimate Member
	_include_compat( 'ultimatemember', 'plugin' );
}
if ( \tsf()->detect_plugin( [ 'globals' => [ 'bp' ] ] ) ) {
	// BuddyPress
	_include_compat( 'buddypress', 'plugin' );
}

if ( \tsf()->detect_plugin( [ 'functions' => [ 'bbpress' ] ] ) ) {
	// bbPress
	_include_compat( 'bbpress', 'plugin' );
} elseif ( \tsf()->detect_plugin( [ 'constants' => [ 'WPFORO_BASENAME' ] ] ) ) {
	// wpForo
	_include_compat( 'wpforo', 'plugin' );
}

if ( \tsf()->detect_plugin( [ 'functions' => [ 'wc' ] ] ) ) {
	// WooCommerce.
	_include_compat( 'woocommerce', 'plugin' );
} elseif ( \tsf()->detect_plugin( [ 'constants' => [ 'EDD_VERSION' ] ] ) ) {
	// Easy Digital Downloads.
	_include_compat( 'edd', 'plugin' );
}

if ( \tsf()->detect_plugin( [ 'constants' => [ 'ELEMENTOR_VERSION' ] ] ) ) {
	// Elementor
	_include_compat( 'elementor', 'plugin' );
}

/**
 * Includes compatibility files, only once per request.
 * Embedded in a function to isolate the scope.
 *
 * @since 4.3.0
 * @access private
 *
 * @param string $what The vendor/plugin/theme name for the compatibility.
 * @param string $type The compatibility type. Be it 'plugin' or 'theme'.
 */
function _include_compat( $what, $type ) {
	require \THE_SEO_FRAMEWORK_DIR_PATH_COMPAT . "$type-$what.php";
}
