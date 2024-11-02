/**
 * This file holds The SEO Framework plugin's JS code for TSF term slug fetching and caching.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/autodescription/>
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

'use strict';

/**
 * Holds tsfTermSlugs values in an object to avoid polluting global namespace.
 *
 * This is a self-constructed function assigned as an object.
 *
 * @since 5.0.7
 *
 * @constructor
 */
window.tsfTermSlugs = function () {

	/**
	 * @since 5.0.7
	 * @access public
	 * @type {Map<string,Map<number,Object<string,*>>}
	 */
	const cache = new Map();

	/**
	 * Retrieves the term from the cache.
	 *
	 * @since 5.0.7
	 * @access public
	 *
	 * @param {number} termId   The term ID.
	 * @param {string} taxonomy The taxonomy slug.
	 * @returns
	 */
	async function get( termId, taxonomy ) {

		termId = +termId;

		if ( ! termId || termId < 1 )
			return {};

		if ( ! cache.get( taxonomy )?.has( termId ) )
			await fetch( termId, taxonomy );

		return cache.get( taxonomy )?.get( termId ) || {};
	}

	/**
	 * Stores the given term and its parents in the cache.
	 *
	 * It walks over all the term's parents and stores them in the cache as well
	 * as separate entries for quick access.
	 *
	 * @since 5.0.7
	 * @access public
	 *
	 * @param {Object} terms    The terms to store.
	 * @param {string} taxonomy The taxonomy slug.
	 */
	function store( terms, taxonomy ) {

		if ( ! cache.has( taxonomy ) )
			cache.set( taxonomy, new Map() );

		const termParentCache = cache.get( taxonomy );

		// Walk the ancestoral tree, and store each as anew for quick access.
		Object.entries( terms ).forEach( ( [ termId ], index, terms ) => {
			termId = +termId;
			// We may want to skip this check, for the new data might be updated? Edge-case deluxe.
			termParentCache.has( termId ) || termParentCache.set(
				termId,
				Object.fromEntries( terms.slice( 0, index + 1 ) ), // +1 to include self.
			);
		} );
	}

	/**
	 * Fetches the term parent slugs from the server.
	 *
	 * @since 5.0.7
	 * @access private
	 *
	 * @param {number} termId   The term ID.
	 * @param {string} taxonomy The taxonomy slug.
	 * @return {Promise} A promise that resolves when the term parent slugs are fetched.
	 */
	function fetch( termId, taxonomy ) {
		// TODO should we add a "fetchingfor" test to prevent concurrent fetches?
		return new Promise( ( resolve, reject ) => {

			if ( ! termId ) {
				reject();
				return;
			}

			wp.ajax.send(
				'tsf_get_term_parent_slugs',
				{
					data: {
						nonce:   tsf.l10n.nonces.edit_posts,
						term_id: termId,
						taxonomy,
					},
					timeout: 7000,
				},
			).done( response => {
				store( tsf.convertJSONResponse( response ), taxonomy );
				resolve();
			} ).fail( reject );
		} );
	}

	return {
		get,
		store,
	};
}();
window.tsfDescription.load();
