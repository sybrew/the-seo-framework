/**
 * This file holds The SEO Framework plugin's JS code for the Post SEO Settings.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/autodescription/>
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2021 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds tsfTerm values in an object to avoid polluting global namespace.
 *
 * @since 4.0.0
 *
 * @constructor
 */
window.tsfTerm = function() {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 4.0.0
	 * @access public
	 * @type {(Object<string, *>)|boolean|null} l10n Localized strings
	 */
	const l10n = 'undefined' !== typeof tsfTermL10n && tsfTermL10n;

	/**
	 * @since 4.1.0
	 * @access private
	 * @type {string}
	 */
	const _titleId = 'autodescription-meta[doctitle]';
	/**
	 * @since 4.1.0
	 * @access private
	 * @type {string}
	 */
	const _descId = 'autodescription-meta[description]';

	/**
	 * @since 4.2.0
	 * @access private
	 * @type {string}
	 */
	const _socialGroup = 'autodescription_social_tt';

	/**
	 * Initializes Canonical URL meta input listeners.
	 *
	 * @since 4.0.0
	 * @since 4.1.2 Changed name from _initCanonicalInput
	 * @access private
	 *
	 * @function
	 */
	const _initVisibilityListeners = () => {

		const indexSelect = document.getElementById( 'autodescription-meta[noindex]' );

		let canonicalUrl    = '',
			showcanonicalPh = true;

		/**
		 * @since 4.1.2
		 *
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

		/**
		 * @since 4.1.2
		 *
		 * @function
		 * @param {Number} value
		 */
		const setRobotsIndexingState = value => {
			let type = '';

			switch ( +value ) {
				case 0: // default, unset since unknown.
					type = indexSelect.dataset.defaultUnprotected;
					break;
				case -1: // index
					type = 'index';
					break;
				case 1: // noindex
					type = 'noindex';
					break;
			}
			if ( 'noindex' === type ) {
				showcanonicalPh = false;
			} else {
				showcanonicalPh = true;
			}

			updateCanonicalPlaceholder();
		}
		if ( indexSelect ) {
			indexSelect.addEventListener( 'change', event => setRobotsIndexingState( event.target.value ) );
			setRobotsIndexingState( indexSelect.value );
		}
	}

	/**
	 * Initializes Title meta input listeners.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 */
	const _initTitleListeners = () => {

		tsfTitle.setInputElement( document.getElementById( _titleId ) );

		const state = JSON.parse(
			document.getElementById( `tsf-title-data_${_titleId}` )?.dataset.state || 0
		);

		if ( state ) {
			tsfTitle.updateStateOf( _titleId, 'allowReferenceChange', ! state.refTitleLocked );
			tsfTitle.updateStateOf( _titleId, 'defaultTitle', state.defaultTitle.trim() );
			tsfTitle.updateStateOf( _titleId, 'addAdditions', state.addAdditions );
			tsfTitle.updateStateOf( _titleId, 'useSocialTagline', !! ( state.useSocialTagline || false ) );
			tsfTitle.updateStateOf( _titleId, 'additionValue', state.additionValue.trim() );
			tsfTitle.updateStateOf( _titleId, 'additionPlacement', state.additionPlacement );
			tsfTitle.updateStateOf( _titleId, 'hasLegacy', !! ( state.hasLegacy || false ) );
		}

		// tsfTitle shouldn't be aware of this--since we remove the prefix on-input.
		const termPrefix = tsf.escapeString( l10n.params.termPrefix );

		/**
		 * Updates title additions, based on singular settings change.
		 *
		 * @function
		 * @param {Event} event
		 */
		const updateTitleAdditions = event => {
			let addAdditions = ! event.target.checked;

			if ( l10n.params.additionsForcedDisabled )
				addAdditions = false;

			tsfTitle.updateStateOf( _titleId, 'addAdditions', addAdditions );
		}
		const blogNameTrigger = document.getElementById( 'autodescription-meta[title_no_blog_name]' );
		if ( blogNameTrigger ) {
			blogNameTrigger.addEventListener( 'change', updateTitleAdditions );
			blogNameTrigger.dispatchEvent( new Event( 'change' ) );
		}

		//!? Disabled as we don't add prefixes when using a custom title:
		// const setTermPrefixValue = ( event ) => {
		// 	let prefixValue    = '';
		// 	if ( ! event.target.value.length )
		// 		prefixValue = l10n.params.termPrefix;
		// 	tsfTitle.updateState( 'prefixValue', prefixValue );
		// }
		// titleInput.addEventListener( 'input', setTermPrefixValue );

		/**
		 * Updates default title placeholder.
		 *
		 * @function
		 * @param {string} value
		 */
		const updateDefaultTitle = val => {
			val = val?.trim();

			let title   = tsfTitle.stripTitleTags ? tsf.stripTags( val ) : val;
			    title ||= tsfTitle.untitledTitle;

			let defaultTitle;

			if ( window.isRtl ) {
				defaultTitle = `${title} ${termPrefix}`;
			} else {
				defaultTitle = `${termPrefix} ${title}`;
			}

			tsfTitle.updateStateOf( _titleId, 'defaultTitle', defaultTitle );
		}
		document.querySelector( '#edittag #name' )
			?.addEventListener( 'input', event => updateDefaultTitle( event.target.value ) );

		tsfTitle.enqueueUnregisteredInputTrigger( _titleId );
	}

	/**
	 * Initializes description meta input listeners.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 */
	const _initDescriptionListeners = () => {

		tsfDescription.setInputElement( document.getElementById( _descId ) );

		const state = JSON.parse(
			document.getElementById( `tsf-description-data_${_descId}` )?.dataset.state || 0
		);
		if ( state ) {
			// tsfDescription.updateState( 'allowReferenceChange', ! state.refDescriptionLocked );
			tsfDescription.updateStateOf( _descId, 'defaultDescription', state.defaultDescription.trim() );
			tsfDescription.updateStateOf( _descId, 'hasLegacy', !! ( state.hasLegacy || false ) );
		}

		// TODO set term-description-content (via ajax) listeners?

		tsfDescription.enqueueUnregisteredInputTrigger( _descId );
	}

	/**
	 * Initializes social meta input listeners.
	 *
	 * @since 4.2.0
	 * @access private
	 *
	 * @function
	 */
	const _initSocialListeners = () => {

		tsfSocial.setInputInstance( _socialGroup, _titleId, _descId );

		const groupData = JSON.parse(
			document.getElementById( `tsf-social-data_${_socialGroup}` )?.dataset.settings || 0
		);
		if ( ! groupData ) return;

		tsfSocial.updateStateOf( _socialGroup, 'addAdditions', groupData.og.state.addAdditions ); // tw Also has one. Maybe future.

		tsfSocial.updateStateOf(
			_socialGroup,
			'defaults',
			{
				ogTitle: groupData.og.state.defaultTitle,
				twTitle: groupData.tw.state.defaultTitle,
				ogDesc:  groupData.og.state.defaultDesc,
				twDesc:  groupData.tw.state.defaultDesc,
			}
		);
	}

	/**
	 * Initializes settings scripts on TSF-load.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 */
	const _loadSettings = () => {
		_initVisibilityListeners();
		_initTitleListeners();
		_initDescriptionListeners();
		_initSocialListeners();
	}

	/**
	 * Initializes settings scripts on TSF-ready.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now registers the refNa title input.
	 * @access private
	 *
	 * @function
	 */
	const _readySettings = () => { }

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
			document.body.addEventListener( 'tsf-onload', _loadSettings );
			document.body.addEventListener( 'tsf-ready', _readySettings );
		}
	}, {
		l10n
	} );
}();
window.tsfTerm.load();
