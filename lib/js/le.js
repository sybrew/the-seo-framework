/**
 * This file holds The SEO Framework plugin's JS code for WordPress List Edit adjustments.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/autodescription/>
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2020 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Holds tsfLe values in an object to avoid polluting global namespace.
 *
 * @since 4.0.0
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfLe = function( $ ) {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 4.0.0
	 * @access public
	 * @type {(Object<string, *>)|boolean|null} l10n Localized strings
	 */
	const l10n = 'undefined' !== typeof tsfLeL10n && tsfLeL10n;

	let dispatchTimeout;
	/**
	 * Dispatches Le update event.
	 *
	 * @since 4.0.5
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _dispatchUpdate = () => {

		clearTimeout( dispatchTimeout );

		dispatchTimeout = setTimeout( () => {
			document.dispatchEvent( new Event( 'tsfLeUpdated' ) );
		}, 50 ); // 20fps
	}

	/**
	 * Runs after a list edit item has been updated.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _updated = () => {
		tsfTT && tsfTT.triggerReset();
	}

	/**
	 * Sets inline post values for quick-edit.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @param {string} id
	 */
	const _setInlinePostValues = id => {

		let dataElement = document.getElementById( `tsfLeData[${id}]` ),
			data        = void 0;

		try {
			data = JSON.parse( dataElement.dataset.le ) || void 0;
		} catch( e ) {}

		if ( ! data ) return;

		let element;

		for ( let option in data ) {

			element = document.getElementById( 'autodescription-quick[%s]'.replace( '%s', option ) );
			if ( ! element ) continue;

			if ( data[ option ].isSelect ) {
				tsf.selectByValue( element, data[ option ].value );

				// Do `sprintf( 'Default (%s)', x.default )`.
				let _default = element.querySelector( '[value="0"]' );
				if ( _default )
					_default.innerHTML = _default.innerHTML.replace( '%s', tsf.decodeEntities( data[ option ].default ) );
			} else {
				element.value = tsf.decodeEntities( data[ option ].value );
			}
		}
	}

	/**
	 * Sets inline term values for quick-edit.
	 * Copy of _setInlinePostValues(), for now.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @param {string} id
	 */
	const _setInlineTermValues = id => _setInlinePostValues( id );

	/**
	 * Sets private/protected visibility state.
	 *
	 * @since 4.1.0
	 * @access private
	 *
	 * @function
	 * @param {Event} event
	 * @return {undefined}
	 */
	const _setTitleVisibilityPrefix = event => {
		let target         = ( event.originalEvent || event ).target,
			titleId        = target.dataset.tsfTitleId,
			oldPrefixValue = tsfTitle.getStateOf( titleId, 'prefixValue' ),
			prefixValue    = '',
			visibility     = 'public';

		if ( 'keep_private' === target.name ) {
			visibility = target.checked ? 'private' : 'public';
		} else {
			visibility = target.value && target.value.length ? 'password' : 'public';
		}

		switch ( visibility ) {
			case 'password':
				prefixValue = tsfTitle.protectedPrefix;
				break;

			case 'private':
				prefixValue = tsfTitle.privatePrefix;
				break;

			default:
			case 'public':
				prefixValue = '';
				break;
		}

		if ( prefixValue !== oldPrefixValue )
			tsfTitle.updateStateOf( titleId, 'prefixValue', prefixValue );
	}

	/**
	 * Sets default title state.
	 *
	 * @since 4.1.0
	 * @access private
	 *
	 * @function
	 * @param {Event} event
	 * @return {undefined}
	 */
	const _setDefaultTitle = event => {
		let target          = ( event.originalEvent || event ).target,
			titleId         = target.dataset.tsfTitleId,
			inputTitle      = 'string' === typeof target.value && target.value.trim() || '',
			defaultTitle    = tsfTitle.stripTitleTags ? tsf.stripTags( inputTitle ) : inputTitle,
			oldDefaultTitle = tsfTitle.getStateOf( titleId, 'defaultTitle' ),
			termPrefix      = 'string' === typeof target.dataset.termPrefix && target.dataset.termPrefix.trim() || '';

		defaultTitle = defaultTitle || tsfTitle.untitledTitle;

		if ( termPrefix.length ) {
			if ( tsf.l10n.states.isRTL ) {
				defaultTitle = defaultTitle + ' ' + termPrefix;
			} else {
				defaultTitle = termPrefix + ' ' + defaultTitle;
			}
		}

		defaultTitle = tsf.escapeString( tsf.decodeEntities( defaultTitle.trim() ) );

		if ( defaultTitle !== oldDefaultTitle )
			tsfTitle.updateStateOf( titleId, 'defaultTitle', defaultTitle );
	}

	/**
	 * Augments and binds inline title input values for quick-edit.
	 *
	 * @since 4.1.0
	 * @access private
	 *
	 * @function
	 * @param {string} id
	 */
	const _prepareTitleInput = id => {

		let dataElement = document.getElementById( 'tsfLeTitleData[' + id + ']' ),
			data        = void 0;

		try {
			data = JSON.parse( dataElement.dataset.leTitle ) || void 0;
		} catch( e ) {}

		if ( ! data ) return;

		const titleId    = 'autodescription-quick[doctitle]',
			  titleInput = document.getElementById( titleId );

		// Reset and rebuild. Map won't be affected.
		tsfTitle.setInputElement( titleInput );

		tsfTitle.updateStateOf( titleId, 'allowReferenceChange', ! data.refTitleLocked );
		tsfTitle.updateStateOf( titleId, 'defaultTitle', data.defaultTitle.trim() );
		tsfTitle.updateStateOf( titleId, 'addAdditions', data.addAdditions );
		tsfTitle.updateStateOf( titleId, 'additionValue', data.additionValue.trim() );
		tsfTitle.updateStateOf( titleId, 'additionPlacement', data.additionPlacement );

		let inlineEdit = document.getElementById( 'edit-' + id );
		// inlineEdit is a wrapper of a clone of a template (#inline-edit). So, we must specifically target the cloned wrapper.
		if ( inlineEdit ) { // this test should never return false...
			inlineEdit.querySelectorAll( '[name=post_password]' ).forEach( element => {
				element.dataset.tsfTitleId = titleId;
				element.addEventListener( 'input', _setTitleVisibilityPrefix );
				element.dispatchEvent( new CustomEvent( 'input' ) );
			} );
			inlineEdit.querySelectorAll( '[name=keep_private]' ).forEach( element => {
				element.dataset.tsfTitleId = titleId;
				element.addEventListener( 'click', _setTitleVisibilityPrefix );
				element.dispatchEvent( new CustomEvent( 'click' ) );
			} );
			// Post titles. TODO Should we use class "ptitle" instead?
			inlineEdit.querySelectorAll( '[name=post_title]' ).forEach( element => {
				element.dataset.tsfTitleId = titleId;
				// tsfTitle shouldn't be aware of this--since we remove the prefix on-input.
				// element.dataset.termPrefix = termPrefix;
				element.addEventListener( 'input', _setDefaultTitle );
				element.dispatchEvent( new CustomEvent( 'input' ) );
			} );
			// Term titles. TODO Should we use class "ptitle" instead?
			inlineEdit.querySelectorAll( '[name=name]' ).forEach( element => {
				element.dataset.tsfTitleId = titleId;
				// tsfTitle shouldn't be aware of this--since we remove the prefix on-input.
				element.dataset.termPrefix = data.termPrefix || '';
				element.addEventListener( 'input', _setDefaultTitle );
				element.dispatchEvent( new CustomEvent( 'input' ) );
			} );
		}

		tsfTT.triggerReset();
	}

	/**
	 * Augments and binds inline description input values for quick-edit.
	 *
	 * @since 4.1.0
	 * @access private
	 *
	 * @function
	 * @param {string} id
	 */
	const _prepareDescriptionInput = id => {

		let dataElement = document.getElementById( 'tsfLeDescriptionData[' + id + ']' ),
			data        = void 0;

		try {
			data = JSON.parse( dataElement.dataset.leDescription ) || void 0;
		} catch( e ) {}

		if ( ! data ) return;

		let descId    = 'autodescription-quick[description]',
			descInput = document.getElementById( descId );

		// Reset and rebuild. Map won't be affected.
		tsfDescription.setInputElement( descInput );
		tsfDescription.updateStateOf( descId, 'allowReferenceChange', ! data.refDescriptionLocked );
		tsfDescription.updateStateOf( descId, 'defaultDescription', data.defaultDescription.trim() );

		tsfTT.triggerReset();
	}

	/**
	 * Initializes List-edit listeners on ready.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _setListeners = () => {
		document.addEventListener( 'tsfLeDispatchUpdate', _dispatchUpdate );
		document.addEventListener( 'tsfLeUpdated', _updated );
	}

	/**
	 * Hijacks the quick and bulk-edit listeners.
	 *
	 * NOTE: The bulk-editor doesn't need adjusting, yet.
	 *       Moreover, the bulk-edit doesn't have a "save" callback, because it's
	 *       not using AJAX to save data.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 */
	const _hijackListeners = () => {

		let _oldInlineEditCallback;

		if ( window.inlineEditPost ) {
			_oldInlineEditCallback = 'edit' in window.inlineEditPost && window.inlineEditPost.edit;

			if ( _oldInlineEditCallback ) {
				window.inlineEditPost.edit = function( id ) {
					let ret = _oldInlineEditCallback.apply( this, arguments );

					if ( typeof( id ) === 'object' )
						id = window.inlineEditPost.getId( id );

					if ( ! id ) return ret;

					_setInlinePostValues( id );
					_prepareTitleInput( id );
					_prepareDescriptionInput( id );
					'tsfC' in window && tsfC.resetCounterListener();

					return ret;
				}
			}
		}

		if ( window.inlineEditTax ) {
			_oldInlineEditCallback = 'edit' in window.inlineEditTax && window.inlineEditTax.edit;

			if ( _oldInlineEditCallback ) {
				window.inlineEditTax.edit = function( id ) {
					let ret = _oldInlineEditCallback.apply( this, arguments );

					if ( typeof( id ) === 'object' )
						id = window.inlineEditTax.getId( id );

					if ( ! id ) return ret;

					_setInlineTermValues( id );
					_prepareTitleInput( id );
					_prepareDescriptionInput( id );
					'tsfC' in window && tsfC.resetCounterListener();

					return ret;
				}
			}
		}
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
		 * @return {undefined}
		 */
		load: () => {
			document.body.addEventListener( 'tsf-onload', _setListeners );
			document.body.addEventListener( 'tsf-onload', _hijackListeners );
		}
	}, {}, {
		l10n
	} );
}( jQuery );
window.tsfLe.load();
