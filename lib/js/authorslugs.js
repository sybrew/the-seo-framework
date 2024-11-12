/**
 * This file holds The SEO Framework plugin's JS code for TSF author slug fetching and caching.
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
 * Holds tsfAuthorSlugs values in an object to avoid polluting global namespace.
 *
 * This is a self-constructed function assigned as an object.
 *
 * @since 5.1.0
 *
 * @constructor
 */
window.tsfAuthorSlugs = function () {

	/**
	 * @since 5.1.0
	 * @access public
	 * @type {Map<string,Map<number,Object<string,*>>}
	 */
	const cache = new Map();

	/**
	 * Retrieves the author slug from the cache.
	 *
	 * @since 5.1.0
	 * @access public
	 *
	 * @param {number} authorId The author ID.
	 * @returns {Promise<String>}
	 */
	async function get( authorId ) {

		authorId = +authorId;

		if ( ! authorId || authorId < 1 )
			return '';

		if ( ! cache.has( authorId ) )
			await fetch( authorId );

		return cache.get( authorId ) || '';
	}

	/**
	 * Stores the given author ID and its slug in the cache.
	 *
	 * @since 5.1.0
	 * @access public
	 *
	 * @param {Array<{id:number,slug:string}>} authors The authors to store.
	 */
	function store( authors ) {
		authors.forEach( author => {
			cache.set( +author.id, author.slug );
		} );
	}

	/**
	 * Fetches the author slugs from the server.
	 *
	 * @since 5.1.0
	 * @access private
	 *
	 * @param {number} authorId The author ID.
	 * @return {Promise<void>} A promise that resolves when the author slug is fetched.
	 */
	function fetch( authorId ) {
		// TODO should we add a "fetchingfor" test to prevent concurrent fetches?
		return new Promise( ( resolve, reject ) => {

			if ( ! authorId ) {
				reject();
				return;
			}

			wp.ajax.send(
				'tsf_get_author_slug',
				{
					data: {
						nonce: tsf.l10n.nonces.edit_posts,
						author_id: authorId,
					},
					timeout: 7000,
				},
			).done( response => {
				store( tsf.convertJSONResponse( response ) );
				resolve();
			} ).fail( reject );
		} );
	}

	return {
		get,
		store,
	};
}();
