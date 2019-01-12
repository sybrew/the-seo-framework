/**
 * This file holds The SEO Framework plugin's JS code for forwards compatibility with Gutenberg.
 *
 * This is an intermediate step where I hijack old code to support the new WordPress editor.
 * The current code will be rewritten later. Do not rely on this file. However, the JQ triggers
 * will sustain support until further notice.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/autodescription/>
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Holds tsfGBC (tsf Gutenberg Compat) values in an object to avoid polluting global namespace.
 *
 * This is a self-constructed function assigned as an object.
 *
 * @since 3.2.0
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfGBC = function( $ ) {

	// const { addFilter } = wp.hooks;
	// const { addQueryArgs } = wp.url;
	// const apiFetch = wp.apiFetch;

	const editor = wp.data.select( 'core/editor' );
	const { debounce } = lodash;

	/**
	 * Data property injected by our Scripts l10n handler.
	 *
	 * @since 3.2.0
	 * @access private
	 * @type {(Object<string, *>)|boolean|null} l10n Localized strings
	 */
	let l10n = 'undefined' !== typeof tsfGBCL10n && tsfGBCL10n;

	/**
	 * Post data holder.
	 *
	 * @since 3.2.0
	 * @access private
	 * @type {(Object<string, *>)|boolean|null} post data
	 */
	let postData;

	/**
	 * Retrieves post attribute.
	 *
	 * @since 3.2.0
	 * @access private
	 *
	 * @function
	 * @param {String} attribute
	 * @return {mixed|null}
	 */
	function getPostAttribute( attribute ) {
		return editor.getEditedPostAttribute( attribute );
	}

	/**
	 * Sets editor data.
	 *
	 * @since 3.2.0
	 * @since 3.2.2 Now sets post visibility.
	 * @access private
	 *
	 * @function
	 */
	function setData() {
		postData = {
			title:      getPostAttribute( 'title' ),
			link:       editor.getPermalink(),
			content:    getPostAttribute( 'content' ),
			excerpt:    getPostAttribute( 'excerpt' ),
			visibility: editor.getEditedPostVisibility(),
		};
	}

	/**
	 * Returns known editor data.
	 *
	 * @since 3.2.0
	 * @access private
	 *
	 * @function
	 * @param {String} type
	 * @return {mixed|null}
	 */
	function getData( type ) {
		return postData[ type ] || null;
	}

	/**
	 * Assesses the editor data, and dispatches the data when changed.
	 *
	 * @since 3.2.0
	 * @since 3.2.2 Now dispatches visibility changes.
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	function assessData() {
		let oldData = postData;
		setData();
		if ( oldData.title !== postData.title ) {
			triggerUpdate( 'title' );
		}
		if ( oldData.link !== postData.link ) {
			triggerUpdate( 'link' );
		}
		if ( oldData.content !== postData.content ) {
			triggerUpdate( 'content' );
		}
		if ( oldData.excerpt !== postData.excerpt ) {
			triggerUpdate( 'excerpt' );
		}
		if ( oldData.visibility !== postData.visibility ) {
			triggerUpdate( 'visibility' );
		}
	}

	/**
	 * Dispatches an event of a data type, also sends data of set type.
	 *
	 * @since 3.2.0
	 * @access public
	 *
	 * @function
	 * @param {String} type
	 * @return {undefined}
	 */
	const triggerUpdate = ( type ) => {
		$( document ).trigger( 'tsf-updated-gutenberg-' + type, [ getData( type ) ] );
	}

	/**
	 * Maintains asynchronous save states.
	 * @since 3.2.0
	 * @access private
	 * @type {boolean} saved
	 * @type {boolean} queueSaveDone
	 */
	let saved = false, queueSaveDone = false;
	/**
	 * Checks if the document is saved successfully, and then dispatches an event if so.
	 *
	 * @since 3.2.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	function saveDispatcher() {
		if ( ! saved && ( editor.isSavingPost() || editor.isAutosavingPost() ) ) {
			saved = true;
		} else if ( saved ) {
			if ( editor.didPostSaveRequestSucceed() ) {
				dispatchSavedEvent();
			} else {
				saved = false;
			}
		}
	}

	/**
	 * Dispatches save event.
	 *
	 * @since 3.2.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	function dispatchSavedEvent() {
		saved = false;
		if ( editor.isPostSavingLocked() ) {
			// Retry again.
			queueSaveDone = true;
			debounce( dispatchSavedEvent, 500 );
		} else if ( ! queueSaveDone ) {
			$( document ).trigger( 'tsf-gutenberg-saved-document' );
		}
	}

	/**
	 * Initializes Gutenberg's compatibility and dispatches event hooks.
	 *
	 * @since 3.2.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initCompat = () => {
		wp.data.subscribe( debounce( assessData, 300 ) );
		wp.data.subscribe( saveDispatcher );
		// Set all values prior debouncing.
		setTimeout( () => {
			setData();

			triggerUpdate( 'title' );
			triggerUpdate( 'link' );
			triggerUpdate( 'content' );
			triggerUpdate( 'excerpt' );
			triggerUpdate( 'visibility' );

			$( document ).trigger( 'tsf-subscribed-to-gutenberg' );
		} );
	}

	//? IE11 Object.assign() alternative.
	return $.extend( {
		/**
		 * Initialises all aspects of the scripts.
		 * You shouldn't call this.
		 *
		 * @since 3.2.0
		 * @access protected
		 *
		 * @function
		 * @return {undefined}
		 */
		load: function() {
			$( document.body ).on( 'tsf-onload', _initCompat );
		}
	}, {
		triggerUpdate,
	} );
}( jQuery );
jQuery( window.tsfGBC.load );
