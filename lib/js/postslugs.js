/**
 * This file holds The SEO Framework plugin's JS code for TSF post slug fetching and caching.
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
 * Holds tsfPostSlugs values in an object to avoid polluting global namespace.
 *
 * This is a self-constructed function assigned as an object.
 *
 * @since 5.1.0
 *
 * @constructor
 */
window.tsfPostSlugs = function () {

	/**
	 * @since 5.1.0
	 * @access public
	 * @type {Map<string,Map<number,Object<string,*>>}
	 */
	const cache = new Map();

	/**
	 * Retrieves the post slug from the cache.
	 *
	 * @since 5.1.0
	 * @access public
	 *
	 * @param {number} postId The post ID.
	 * @returns {Promise<String[]>}
	 */
	async function get( postId ) {

		postId = +postId;

		if ( ! postId || postId < 1 )
			return [];

		if ( ! cache.has( postId ) )
			await fetch( postId );

		return cache.get( postId ) || [];
	}

	/**
	 * Stores the given post ID and its slug in the cache.
	 *
	 * @since 5.1.0
	 * @access public
	 *
	 * @param {Array<{id:number,slug:string}>} posts The posts to store.
	 */
	function store( posts ) {
		// Walk the ancestral tree, and store each as anew for quick access.
		posts.forEach( ( post, index, posts ) => {
			const postId = +post.id;
			// We may want to skip this check, for the new data might be updated? Edge-case deluxe.
			cache.has( postId ) || cache.set(
				postId,
				posts.slice( 0, index + 1 ).map( post => post.slug ), // +1 to include self.
			);
		} );
	}

	/**
	 * Fetches the post parent slugs from the server.
	 *
	 * @since 5.1.0
	 * @access private
	 *
	 * @param {number} postId The post ID.
	 * @return {Promise<void>} A promise that resolves when the post parent slugs are fetched.
	 */
	function fetch( postId ) {
		// TODO should we add a "fetchingfor" test to prevent concurrent fetches?
		return new Promise( ( resolve, reject ) => {

			if ( ! postId ) {
				reject();
				return;
			}

			wp.ajax.send(
				'tsf_get_post_parent_slugs',
				{
					data: {
						nonce:   tsf.l10n.nonces.edit_posts,
						post_id: postId,
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
