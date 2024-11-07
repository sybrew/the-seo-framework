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

	const editor   = wp.data.select( 'core/editor' );
	const editPost = wp.data.select( 'core/edit-post' );

	/**
	 * Post data holder.
	 *
	 * @since 3.2.0
	 * @access private
	 * @type {Map<string,string>} postData
	 */
	const postData = new Map();

	/**
	 * Retrieves post attribute.
	 *
	 * @since 3.2.0
	 * @access private
	 *
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
	 * @since 5.0.3 Renamed from `setData()`.
	 * @since 5.1.0 Now sets the 'slug', 'parent', 'date', and 'author'.
	 * @access private
	 */
	function updateData() {
		postData.set( 'title', getPostAttribute( 'title' ) )
				.set( 'link', editor.getPermalink() )
				.set( 'slug', getPostAttribute( 'slug' ) )
				.set( 'parent', getPostAttribute( 'parent' ) )
				.set( 'date', getPostAttribute( 'date' ) )
				.set( 'author', getPostAttribute( 'author' ) )
				.set( 'content', getPostAttribute( 'content' ) )
				.set( 'excerpt', getPostAttribute( 'excerpt' ) )
				.set( 'visibility', editor.getEditedPostVisibility() );
	}

	/**
	 * Assesses the editor data, and dispatches the data when changed.
	 *
	 * @since 3.2.0
	 * @since 3.2.2 Now dispatches visibility changes.
	 * @access private
	 */
	function assessData() {

		const oldData = new Map( postData );

		updateData();

		postData.forEach( ( val, key ) => {
			if ( val !== oldData.get( key ) )
				triggerUpdate( key );
		} );
	}

	/**
	 * Maintains asynchronous save states.
	 * @since 3.2.0
	 * @access private
	 * @type {boolean} saved
	 */
	let saved = false;
	/**
	 * Maintains asynchronous save type.
	 * @since 4.0.0
	 * @access private
	 * @type {String} The saved type. Either 'save', 'autosave', or 'preview'.
	 */
	let savedType = '';
	/**
	 * Checks if the document is saved successfully, and then dispatches an event if so.
	 *
	 * @since 3.2.0
	 * @since 4.0.0 1. Now waits for 7 seconds for the saveDispatcher to resolve before canceling the process.
	 *              2. Added `saveType` checking, to discern events with stale dirty content.
	 * @access private
	 */
	function saveDispatcher() {
		if ( ! saved ) {
			if ( editor.isSavingPost() ) {
				if ( editor.isPreviewingPost() ) {
					saved     = true;
					savedType = 'preview';
				} else if ( editor.isAutosavingPost() ) {
					saved     = true;
					savedType = 'autosave';
				} else {
					saved     = true;
					savedType = 'save';
				}
			}
		} else {
			if ( editor.didPostSaveRequestSucceed() ) {
				dispatchSaveEventDebouncer();
				revertSaveStateDebouncer().cancel();
				revertSaveState();
			} else {
				revertSaveStateDebouncer();
			}
		}
	}

	const revertSaveStateDebouncer = tsfUtils.debounce( revertSaveState, 7000 ); // 7s: timeout for HTTP resolving
	/**
	 * Reverts save state.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	function revertSaveState() {
		saved = false;
	}

	const dispatchSaveEventDebouncer = tsfUtils.debounce( dispatchSavedEvent, 500 );
	/**
	 * Maintains retry states.
	 * @since 4.0.0
	 * @access private
	 * @type {number} retryDispatch
	 */
	let retryDispatch = 0;
	/**
	 * Dispatches save event.
	 *
	 * @since 3.2.0
	 * @since 4.0.0 1. Added `saveType` checking.
	 *              2. Now forwards the `saveType` parameter in `tsf-gutenberg-saved-document`.
	 * @access private
	 */
	function dispatchSavedEvent() {
		if ( editor.isPostSavingLocked() ) {
			// Retry again
			if ( ++retryDispatch < 3 ) {
				dispatchSaveEventDebouncer();
			} else {
				dispatchSaveEventDebouncer().cancel();
				retryDispatch = 0;
			}
		} else {
			retryDispatch = 0;

			// When a new post is being created, autosave and preview are synonymous to saving.
			// With that, hasChangedContent() will be set to "false".
			let triggerOnSaveEvent = ! editor.hasChangedContent();

			switch ( savedType ) {
				case 'preview':
					document.dispatchEvent( new CustomEvent( 'tsf-gutenberg-onpreview' ) );
					break;
				case 'autosave':
					document.dispatchEvent( new CustomEvent( 'tsf-gutenberg-onautosave' ) );
					break;
				case 'save':
					triggerOnSaveEvent = true;
					break;
			}

			if ( triggerOnSaveEvent ) {
				document.dispatchEvent( new CustomEvent( 'tsf-gutenberg-onsave' ) )
					&& document.dispatchEvent( new CustomEvent( 'tsf-gutenberg-onsave-completed' ) );
			}

			document.dispatchEvent(
				new CustomEvent(
					'tsf-gutenberg-saved-document',
					{
						detail: { savedType },
					}
				)
			);
			savedType = '';
		}
	}

	/**
	 * Maintains the sidebar opening/closing states.
	 *
	 * @since 4.0.0
	 * @access private
	 * @type {Object<string,*>}
	 */
	const lastSidebarState = {
		opened: false,
	};
	/**
	 * Checks if user changed the sidebar layout.
	 *
	 * @since 3.2.0
	 * @access private
	 */
	function sidebarDispatcher() {
		if ( editPost.isEditorSidebarOpened() ) {
			if ( ! lastSidebarState.opened ) {
				lastSidebarState.opened = true;
				document.dispatchEvent( new CustomEvent( 'tsf-gutenberg-sidebar-opened' ) );
			}
		} else {
			if ( lastSidebarState.opened ) {
				lastSidebarState.opened = false;
				document.dispatchEvent( new CustomEvent( 'tsf-gutenberg-sidebar-closed' ) );
			}
		}
	}

	/**
	 * Dispatches an event of a data type, also sends data of set type.
	 *
	 * @since 3.2.0
	 * @since 5.1.0 Added a non-jQuery event alternatives: `tsf-uppdated-block-editor` and `tsf-uppdated-block-editor-${type}`.
	 * @access public
	 *
	 * @param {String} type
	 */
	function triggerUpdate( type ) {

		const value = postData.get( type );

		document.dispatchEvent( new CustomEvent(
			`tsf-updated-block-editor`,
			{ detail: { type, value, postData } },
		) );

		document.dispatchEvent( new CustomEvent(
			`tsf-updated-block-editor-${type}`,
			{ detail: { type, value } },
		) );

		$._data( document, 'events' )?.[ `tsf-updated-gutenberg-${type}` ]
			&& tsf.deprecatedFunc(
				'jQuery event "tsf-updated-gutenberg"',
				'5.1.0',
				`JS Event "tsf-updated-block-editor" or "tsf-updated-block-editor-${type}"`,
			);

		// Unfortunately, we still rely on jQuery here.
		// We can't move away from this quickly, since the data sent is definitely used by other plugins (like our Focus extension was).
		$( document ).trigger(
			`tsf-updated-gutenberg-${type}`,
			[ value ],
		);
	}

	/**
	 * Initializes Gutenberg's compatibility and dispatches event hooks.
	 *
	 * @since 3.2.0
	 * @since 4.0.0 Now adds tooltip boundaries (moved from tt.js)
	 * @access private
	 */
	function _initCompat() {

		const { subscribe } = wp.data;

		subscribe( tsfUtils.debounce( sidebarDispatcher, 500 ) );
		subscribe( tsfUtils.debounce( assessData, 300 ) );
		subscribe( saveDispatcher );

		document.dispatchEvent( new CustomEvent( 'tsf-subscribed-to-gutenberg' ) );
	}

	return Object.assign( {
		/**
		 * Initialises all aspects of the scripts.
		 * You shouldn't call this.
		 *
		 * @since 4.0.0
		 * @access protected
		 *
		 * @function
		 */
		load: () => {
			document.body.addEventListener( 'tsf-onload', _initCompat );
		},
	}, {
		triggerUpdate,
	} );
}( jQuery );
window.tsfGBC.load();
