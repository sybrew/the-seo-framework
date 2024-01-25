<?php
/**
 * @package The_SEO_Framework\Classes\Data\Admin\Term
 * @subpackage The_SEO_Framework\Data
 */

namespace The_SEO_Framework\Data\Admin;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Data;

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
 * Holds a collection of data saving methods for terms.
 *
 * @since 5.0.0
 * @access private
 */
class Term {

	/**
	 * Sanitizes and saves term meta data when a term is altered.
	 *
	 * @hook edit_term 10
	 * @since 2.7.0
	 * @since 4.0.0 1. Renamed from `update_term_meta`
	 *              2. noindex, nofollow, noarchive are now converted to qubits.
	 *              3. Added new keys to sanitize.
	 *              4. Now marked as private.
	 *              5. Added more sanity protection.
	 *              6. No longer runs when no `autodescription-meta` POST data is sent.
	 *              7. Now uses the current term meta to set new values.
	 *              8. No longer deletes meta from abstracting plugins on save when they're deactivated.
	 *              9. Now allows updating during `WP_AJAX`.
	 * @since 5.0.0 1. Moved from `\The_SEO_Framework\Load`.
	 *              2. Renamed from `_update_term_meta`.
	 * @access private
	 *         Use Data\Plugin\Term::save_meta() instead.
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 */
	public static function update_meta( $term_id, $tt_id, $taxonomy ) {
		// phpcs:disable, WordPress.Security.NonceVerification -- deferred.
		if ( ! empty( $_POST['autodescription-quick'] ) ) {
			static::update_via_quick_edit( $term_id, $taxonomy );
		} elseif ( ! empty( $_POST['autodescription-meta'] ) ) {
			static::update_via_term_edit( $term_id, $taxonomy );
		}
		// phpcs:enable, WordPress.Security.NonceVerification
	}

	/**
	 * Overwrites a part of the term meta on quick-edit.
	 *
	 * @since 4.0.0
	 * @since 4.0.2 1. Now tests for valid term ID in the term object.
	 *              2. Now continues using the filtered term object.
	 * @since 5.0.0 1. Removed second parameter $tt_id.
	 *              2. Moved from `\The_SEO_Framework\Load`.
	 *              3. Renamed from `update_quick_edit_term_meta`.
	 *
	 * @param int    $term_id  Term ID.
	 * @param string $taxonomy Taxonomy slug.
	 * @return void
	 */
	private static function update_via_quick_edit( $term_id, $taxonomy ) {

		$term = \get_term( $term_id, $taxonomy );

		// Check again against ambiguous injection...
		// Note, however: function wp_ajax_inline_save_tax() already performs all these checks for us before firing this callback's action.
		if (
			   empty( $term->term_id ) // We could test for is_wp_error( $term ), but this is more to the point.
			|| ! \current_user_can( 'edit_term', $term->term_id )
			|| ! \check_ajax_referer( 'taxinlineeditnonce', '_inline_edit', false )
		) return;

		// Unlike the term-edit saving, we don't reset the data, just overwrite what's given.
		// This is because we only update a portion of the meta.
		$data = array_merge(
			Data\Plugin\Term::get_meta( $term->term_id, false ),
			(array) \wp_unslash( $_POST['autodescription-quick'] ),
		);

		// Trim, sanitize, and save the metadata.
		Data\Plugin\Term::save_meta( $term->term_id, $data );
	}

	/**
	 * Overwrites all of the term meta on term-edit.
	 *
	 * @since 4.0.0
	 * @since 4.0.2 1. Now tests for valid term ID in the term object.
	 *              2. Now continues using the filtered term object.
	 * @since 5.0.0 1. Removed second parameter $tt_id.
	 *              2. Moved from `\The_SEO_Framework\Load`.
	 *              3. Renamed from `update_term_edit_term_meta`.
	 *
	 * @param int    $term_id  Term ID.
	 * @param string $taxonomy Taxonomy slug.
	 * @return void
	 */
	private static function update_via_term_edit( $term_id, $taxonomy ) {

		$term = \get_term( $term_id, $taxonomy );

		// Check again against ambiguous injection...
		// Note, however: function wp_update_term() already performs all these checks for us before firing this callback's action.
		if (
			   empty( $term->term_id ) // We could test for is_wp_error( $term ), but this is more to the point.
			|| ! \current_user_can( 'edit_term', $term->term_id )
			|| ! isset( $_POST['_wpnonce'] )
			|| ! \wp_verify_nonce( $_POST['_wpnonce'], "update-tag_{$term->term_id}" )
		) return;

		// Trim, sanitize, and save the metadata.
		Data\Plugin\Term::save_meta(
			$term->term_id,
			(array) \wp_unslash( $_POST['autodescription-meta'] ),
		);
	}
}
