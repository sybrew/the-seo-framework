/**
 * This file holds The SEO Framework plugin's JS code for WordPress List Edit adjustments.
 * Serve JavaScript as an addition, not as an ends or means.
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
 * Holds tsfLe values in an object to avoid polluting global namespace.
 *
 * @since 4.0.0
 *
 * @constructor
 */
window.tsfLe = function() {

	/**
	 * Dispatches Le update event.
	 *
	 * @since 4.0.5
	 * @access private
	 *
	 * @function
	 */
	const _dispatchUpdate = tsfUtils.debounce(
		() => { document.dispatchEvent( new CustomEvent( 'tsfLeUpdated' ) ); },
		50, // Magic number. Low enough not to visually glitch, high enough not to cause lag.
	);

	/**
	 * Runs after a list edit item has been updated.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	function _updated() {
		tsfTT.triggerReset();
	}

	/**
	 * Sets inline post values for quick-edit.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @param {string} id The post ID.
	 */
	function _setInlinePostValues( id ) {

		const data = JSON.parse( document.getElementById( `tsfLeData[${id}]` )?.dataset.le || 0 ) || {};

		for ( const option in data ) {
			const element = document.getElementById( 'autodescription-quick[%s]'.replace( '%s', option ) );

			if ( ! element ) continue;

			if ( data[ option ].isSelect ) {
				tsf.selectByValue( element, data[ option ].value );

				// Do `sprintf( 'Default (%s)', x.default )`.
				const _default = element.querySelector( '[value="0"]' );
				if ( _default )
					_default.innerHTML = _default.innerHTML.replace( '%s', tsf.escapeString( tsf.decodeEntities( data[ option ].default ) ) );
			} else {
				element.value = tsf.decodeEntities( data[ option ].value );

				if ( data[ option ].placeholder )
					element.placeholder = tsf.decodeEntities( data[ option ].placeholder );
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
	 * @param {string} id The term ID.
	 */
	function _setInlineTermValues( id ) {
		return _setInlinePostValues( id );
	}

	/**
	 * Returns the postdata element, or an empty object.
	 *
	 * @since 4.1.4
	 * @access private
	 *
	 * @param {string} id The post ID.
	 * @return {object}
	 */
	function _getPostData( id ) {
		return JSON.parse(
			document.getElementById( `tsfLePostData[${id}]` )?.dataset.lePostData || 0
		) || {};
	}

	/**
	 * Returns the post's visibility.
	 *
	 * @since 5.0.7
	 * @access private
	 *
	 * @param {string} id The post ID.
	 * @return {string} 'public', 'password', or 'private'.
	 */
	function _getPostVisibility( id ) {

		// This wrap is a clone of a template (#inline-edit). So, we must specifically target the cloned wrapper.
		const inlineEditWrap = document.getElementById( `edit-${id}` );

		let visibility = 'public';

		if ( inlineEditWrap?.querySelector( '[name=keep_private]' )?.checked ) {
			visibility = 'private';
		} else {
			const val = inlineEditWrap?.querySelector( '[name=post_password]' )?.value;
			// If password type is filled, but the password is falsy, then assume public. This is a bug in WP.
			if ( val?.length && '0' !== val )
				visibility = 'password';
		}

		return visibility;
	}

	/**
	 * Registers the post privacy  listener.
	 *
	 * @since 5.0.7
	 * @access private
	 *
	 * @param {string} id The post ID.
	 * @param {callable} callback
	 */
	function _registerPostPrivacyListener( id, callback ) {

		// This wrap is a clone of a template (#inline-edit). So, we must specifically target the cloned wrapper.
		const inlineEditWrap = document.getElementById( `edit-${id}` );

		// Debounce the callback, because toggling keep_private will also trigger a post_password input event.
		callback = tsfUtils.debounce( callback, 20 ); // Magic number. The duplicate event happens in under a few ms; this is also imperceptible.

		// The wrap should always exist, but since we didn't create it, we test for its existence now.
		// Also, the because it's a clone, it and its event-listeners get destroyed when changing the post. This is helpful.
		inlineEditWrap?.querySelector( '[name=post_password]' )?.addEventListener( 'input', callback );
		inlineEditWrap?.querySelector( '[name=keep_private]' )?.addEventListener( 'click', callback );
	}

	/**
	 * Augments and binds inline title input values for quick-edit.
	 *
	 * @since 4.1.0
	 * @access private
	 *
	 * @param {string} id The post/term ID.
	 */
	function _prepareTitleInput( id ) {

		const titleId    = 'autodescription-quick[doctitle]',
			  titleInput = document.getElementById( titleId );

		if ( ! titleInput ) return;

		// Reset and rebuild. Map won't be affected.
		tsfTitle.setInputElement( titleInput );

		const data = JSON.parse( document.getElementById( `tsfLeTitleData[${id}]` )?.dataset.leTitle || 0 );

		if ( data ) {
			tsfTitle.updateStateOf( titleId, 'allowReferenceChange', ! data.refTitleLocked );
			tsfTitle.updateStateOf( titleId, 'defaultTitle', data.defaultTitle.trim() );
			tsfTitle.updateStateOf( titleId, 'addAdditions', data.addAdditions );
			tsfTitle.updateStateOf( titleId, 'additionValue', data.additionValue.trim() );
			tsfTitle.updateStateOf( titleId, 'additionPlacement', data.additionPlacement );
		}

		/**
		 * @since 4.1.0
		 * @since 5.0.7 1. No longer considers '0' a valid password.
		 *              2. Moved from parent scope.
		 * @function
		 */
		const setTitleVisibilityPrefix = () => {
			let prefixValue = '';

			switch ( _getPostVisibility( id ) ) {
				case 'password':
					prefixValue = tsfTitle.protectedPrefix;
					break;
				case 'private':
					prefixValue = tsfTitle.privatePrefix;
					break;
				default:
				case 'public':
					prefixValue = '';
			}

			tsfTitle.updateStateOf( titleId, 'prefixValue', prefixValue );
		}
		_registerPostPrivacyListener( id, setTitleVisibilityPrefix );
		setTitleVisibilityPrefix();

		/**
		 * Sets default title state.
		 *
		 * @since 4.1.0
		 * @since 4.1.4 Is now considerate of additionsForcedDisabled/additionsForceEnabled
		 * @since 5.0.7 Moved from parent scope.
		 *
		 * @function
		 * @param {Event} event
		 */
		const setDefaultTitle = event => {
			const target     = ( event.originalEvent || event ).target,
				  inputTitle = target.value?.trim() || '',
				  termPrefix = data?.tsfTermPrefix?.trim() || '';

			// '0' doesn't return false for ||. So, this needs no string.length test.
			let defaultTitle = (
					tsfTitle.stripTitleTags
						? tsf.stripTags( inputTitle )
						: inputTitle
				) || tsfTitle.untitledTitle;

			if ( termPrefix.length ) {
				if ( window.isRtl ) {
					defaultTitle = `${defaultTitle} ${termPrefix}`;
				} else {
					defaultTitle = `${termPrefix} ${defaultTitle}`;
				}
			}

			// TODO figure out if this is necessary. tsfTitle also escapes...
			defaultTitle = tsf.escapeString( tsf.decodeEntities( defaultTitle.trim() ) );

			tsfTitle.updateStateOf( titleId, 'defaultTitle', defaultTitle );
		}

		// This wrap is a clone of a template (#inline-edit). So, we must specifically target the cloned wrapper.
		const inlineEditWrap = document.getElementById( `edit-${id}` );

		// The wrap should always exist, but since we didn't create it, we test for its existence now.
		const postTitleInput = inlineEditWrap?.querySelector( '[name=post_title]' ),
			  termNameInput  = inlineEditWrap?.querySelector( '[name=name]' );

		if ( postTitleInput ) {
			// The homepage listens to a static preset value. Update all others.
			if ( ! _getPostData( id ).isFront ) {
				postTitleInput.addEventListener( 'input', setDefaultTitle );
				postTitleInput.dispatchEvent( new Event( 'input' ) );
			}
		}

		if ( termNameInput ) {
			termNameInput.addEventListener( 'input', setDefaultTitle );
			termNameInput.dispatchEvent( new Event( 'input' ) );
		}

		tsfTT.triggerReset();
	}

	/**
	 * Augments and binds inline description input values for quick-edit.
	 *
	 * @since 4.1.0
	 * @access private
	 *
	 * @param {string} id The post/term ID.
	 */
	function _prepareDescriptionInput( id ) {

		const descId    = 'autodescription-quick[description]',
			  descInput = document.getElementById( descId );

		if ( ! descInput ) return;

		// Reset and rebuild. Map won't be affected.
		tsfDescription.setInputElement( descInput );

		const data = JSON.parse( document.getElementById( `tsfLeDescriptionData[${id}]` )?.dataset.leDescription || 0 );
		if ( data ) {
			tsfDescription.updateStateOf( descId, 'allowReferenceChange', ! data.refDescriptionLocked );
			tsfDescription.updateStateOf( descId, 'defaultDescription', data.defaultDescription.trim() );
		}

		tsfTT.triggerReset();
	}

	/**
	 * Augments and binds inline Visibility input values for quick-edit.
	 *
	 * @since 5.0.7
	 * @access private
	 *
	 * @param {string} id The post/term ID.
	 */
	function _prepareVisibilityInput( id ) {

		const indexSelect = document.getElementById( 'autodescription-meta[noindex]' );

		let canonicalUrl    = '',
			showcanonicalPh = true;

		// Redirect is not affected by any dynamic input, yet.
		const canonId    = 'autodescription-quick[canonical]',
			  canonInput = document.getElementById( canonId );

		/**
		 * @function
		 * @param {string} link
		 */
		const updateCanonicalPlaceholder = () => {
			let canonicalInput = document.getElementById( 'autodescription-meta[canonical]' );

			if ( ! canonicalInput ) return;

			// Link might not've been updated (yet). Fill it in with PHP-supplied value (if any).
			canonicalUrl ||= canonicalInput.placeholder;

			canonicalInput.placeholder = showcanonicalPh ? canonicalUrl : '';
		}

		if ( ! canonInput ) return;

		// Reset and rebuild. Map won't be affected.
		tsfCanonical.setInputElement( canonInput );

		const data = JSON.parse( document.getElementById( `tsfLeCanonicalData[${id}]` )?.dataset.leCanonical || 0 );
		if ( data ) {
			tsfCanonical.updateStateOf( canonId, 'allowReferenceChange', ! data.refCanonicalLocked );
			// tsfCanonical.updateStateOf( canonId, 'defaultCanonical', data.defaultCanonical.trim() ); var_dump
		}

		// This wrap is a clone of a template (#inline-edit). So, we must specifically target the cloned wrapper.
		const inlineEditWrap = document.getElementById( `edit-${id}` );

		// The wrap should always exist, but since we didn't create it, we test for its existence now.
		const postTitleInput = inlineEditWrap?.querySelector( '[name=post_title]' ),
			  termNameInput  = inlineEditWrap?.querySelector( '[name=name]' );

		if ( postTitleInput ) {
			// The homepage listens to a static preset value. Update all others.
			if ( ! _getPostData( id ).isFront ) {
				postTitleInput.addEventListener( 'input', setDefaultTitle );
				postTitleInput.dispatchEvent( new Event( 'input' ) );
			}
		}
	}

	/**
	 * Initializes List-edit listeners on ready.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	function _setListeners() {
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
	 */
	function _hijackListeners() {

		let _oldInlineEditPost,
			_oldInlineEditTax;

		_oldInlineEditPost = window.inlineEditPost?.edit;
		if ( _oldInlineEditPost ) {
			window.inlineEditPost.edit = function( id ) {
				let ret = _oldInlineEditPost.apply( this, arguments );

				if ( 'object' === typeof id )
					id = window.inlineEditPost?.getId( id );

				if ( ! id ) return ret;

				_setInlinePostValues( id );
				_prepareVisibilityInput( id );
				_prepareTitleInput( id );
				_prepareDescriptionInput( id );
				window.tsfC?.resetCounterListener();

				return ret;
			}
		}

		_oldInlineEditTax = window.inlineEditTax?.edit;
		if ( _oldInlineEditTax ) {
			window.inlineEditTax.edit = function( id ) {
				let ret = _oldInlineEditTax.apply( this, arguments );

				if ( 'object' === typeof id )
					id = window.inlineEditTax?.getId( id );

				if ( ! id ) return ret;

				_setInlineTermValues( id );
				_prepareVisibilityInput( id );
				_prepareTitleInput( id );
				_prepareDescriptionInput( id );
				window.tsfC?.resetCounterListener();

				return ret;
			}
		}
	}

	return {
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
			document.body.addEventListener( 'tsf-onload', _setListeners );
			document.body.addEventListener( 'tsf-onload', _hijackListeners );
		},
	};
}();
window.tsfLe.load();
