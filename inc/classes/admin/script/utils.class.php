<?php
/**
 * @package The_SEO_Framework\Classes\Admin\Script\Loader
 * @subpackage The_SEO_Framework\Scripts
 */

namespace The_SEO_Framework\Admin\Script;

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

/**
 * Holds various utility functionality for scripts.
 *
 * @since 5.0.0
 * @access private
 */
class Utils {

	/**
	 * Decodes entities of a string, making it workable for different encoding in both JS and HTML.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Bridges\Scripts`.
	 *
	 * @param mixed $value If string, it'll be decoded.
	 * @return mixed
	 */
	public static function decode_entities( $value ) {
		return \is_string( $value ) && \strlen( $value )
			? html_entity_decode( $value, \ENT_QUOTES, 'UTF-8' )
			: $value;
	}

	/**
	 * Decodes all entities of the input.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Moved from `\The_SEO_Framework\Bridges\Scripts`.
	 *
	 * @param mixed $values The entries to decode.
	 * @return mixed
	 */
	public static function decode_all_entities( $values ) {

		if ( \is_scalar( $values ) )
			return static::decode_entities( $values );

		foreach ( $values as &$v )
			$v = static::decode_entities( $v );

		return $values;
	}

	/**
	 * Checks ajax referred set by set_js_nonces based on capability.
	 *
	 * @since 5.0.0
	 * @since 5.1.0 Added the `...$args` parameter.
	 * @access private
	 *
	 * @param string $capability The capability to check for the nonce.
	 *                           This is subsequently uses to generate a nonce action value.
	 * @param mixed  ...$args    The arguments to pass to the capability check.
	 * @return string The nonce to create.
	 */
	public static function create_ajax_capability_nonce( $capability, ...$args ) {
		return \current_user_can( $capability, ...$args )
			? \wp_create_nonce( "tsf-ajax-$capability" )
			: '';
	}

	/**
	 * Checks AJAX capability, referer and nonces.
	 *
	 * Performs wp_die( -1, 403 ) on failure.
	 *
	 * @since 3.1.0 Introduced in 2.9.0, but the name changed.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `_check_tsf_ajax_referer`.
	 * @since 5.1.0 Added the `...$args` parameter.
	 * @access private
	 *
	 * @param string $capability The capability that was required for the nonce check to be created.
	 * @param mixed  ...$args    The arguments to pass to the capability check.
	 * @return null|false|int False if the nonce is invalid, 1 if the nonce is valid
	 *                        and generated between 0-12 hours ago, 2 if the nonce is
	 *                        valid and generated between 12-24 hours ago.
	 *                        Null on capability check failure.
	 */
	public static function check_ajax_capability_referer( $capability, ...$args ) {

		if ( \current_user_can( $capability, ...$args ) )
			return \check_ajax_referer( "tsf-ajax-$capability", 'nonce', true );

		\wp_die( -1, 403 );
	}
}
