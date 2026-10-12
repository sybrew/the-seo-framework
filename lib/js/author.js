/**
 * This file holds The SEO Framework plugin's JS code for the Author profile fields.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/autodescription/>
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2026 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds tsfAuthor values in an object to avoid polluting global namespace.
 *
 * @since 5.2.0
 *
 * @constructor
 */
window.tsfAuthor = function () {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 5.2.0
	 * @access public
	 * @type {(Object<string,*>)|Boolean|null} l10n Localized strings
	 */
	const l10n = tsfAuthorL10n;

	/**
	 * @since 5.2.0
	 * @access private
	 * @type {string}
	 */
	const _titleId = 'tsf-user-meta[doctitle]';
	/**
	 * @since 5.2.0
	 * @access private
	 * @type {string}
	 */
	const _descId = 'tsf-user-meta[description]';
	/**
	 * @since 5.2.0
	 * @access private
	 * @type {string}
	 */
	const _canonicalId = 'tsf-user-meta[canonical]';

	/**
	 * @since 5.2.0
	 * @access private
	 * @type {string}
	 */
	const _socialGroup = 'tsf_user_social';

	/**
	 * @since 5.2.0
	 * @access private
	 * @type {string}
	 */
	const _fediverseHandleId = 'tsf-user-meta[fediverse_page]',
		  _fediverseUrlId    = 'tsf-user-meta[fediverse_page_url]';

	/**
	 * Initializes Fediverse profile input listeners.
	 *
	 * @since 5.2.0
	 * @access private
	 */
	function _initFediverseListeners() {

		const handleInput = document.getElementById( _fediverseHandleId ),
			  urlInput    = document.getElementById( _fediverseUrlId );

		if ( ! handleInput ) return;

		/**
		 * Shows the profile URL an empty field will use.
		 *
		 * @since 5.2.0
		 *
		 * @function
		 */
		function updateUrlPlaceholder() {

			if ( ! urlInput ) return;

			urlInput.placeholder =
				   handleInput.value.trim()
				? tsfFediverse.urlFromHandle( handleInput.value )
				: ( urlInput.dataset.tsfFediverseFallback || '' );
			window.tsfPropose?.syncProposed( urlInput );
		}

		handleInput.addEventListener(
			'input',
			updateUrlPlaceholder,
		);
		updateUrlPlaceholder();

		tsfFediverse.bindPair( handleInput, urlInput );
	}

	/**
	 * Shows a profile example when a Facebook, Fediverse, or X field does not match.
	 *
	 * @since 5.2.0
	 * @access private
	 */
	function _initSocialFormatHints() {
		document.querySelectorAll(
			'.tsf-user-settings [data-social-formats], .tsf-user-settings [data-social-example]',
		).forEach( input => {
			tsfSocialFormat.bind( input );
		} );
	}

	/**
	 * Initializes Canonical URL meta input listeners.
	 *
	 * @since 5.2.0
	 * @access private
	 */
	function _initVisibilityListeners() {

		const noindexSelect  = document.getElementById( 'tsf-user-meta[noindex]' ),
			  canonicalInput = document.getElementById( _canonicalId );

		const urlDataParts = new Map();

		// Prefixed with B because I don't trust using 'protected' (might become reserved).
		const BNOINDEX = 0b10;

		let canonicalPhState = 0b00;

		tsfCanonical.setInputElement( canonicalInput );

		const state = JSON.parse( document.getElementById( `tsf-canonical-data_${_canonicalId}` )?.dataset.state || 0 );

		if ( state ) {
			tsfCanonical.updateStateOf( _canonicalId, 'allowReferenceChange', ! state.refCanonicalLocked );
			tsfCanonical.updateStateOf( _canonicalId, 'defaultCanonical', state.defaultCanonical.trim() );
			tsfCanonical.updateStateOf( _canonicalId, 'preferredScheme', state.preferredScheme.trim() );
			tsfCanonical.updateStateOf( _canonicalId, 'urlStructure', state.urlStructure );
		}

		tsfCanonical.enqueueTriggerUnregisteredInput( _canonicalId );

		/**
		 * @since 5.2.0
		 *
		 * @function
		 */
		const updateCanonicalPlaceholder = () => {

			tsfCanonical.updateStateOf(
				_canonicalId,
				'showUrlPlaceholder',
				canonicalPhState & BNOINDEX ? false : true,
			);
			tsfCanonical.updateStateOf(
				_canonicalId,
				'urlDataParts',
				Object.fromEntries( urlDataParts.entries() ),
			);
		}

		if (
			   tsfCanonical.canTrackUrlStructure( _canonicalId )
			&& canonicalInput
		) {
			const writeAuthor   = tsfCanonical.structIncludes( _canonicalId, '%author%' );
			const writeAuthorId = tsfCanonical.structIncludes( _canonicalId, '%author_id%' );

			// Profile screens have no nicename field. Start from the stored slug.
			let nicename = String( l10n.params.nicename || '' );

			/**
			 * @since 5.2.0
			 *
			 * @function
			 */
			const updateCanonical = () => {

				// An empty nicename stays empty. Do not invent one from the display name.
				if ( writeAuthor )
					urlDataParts.set(
						'%author%',
						nicename.length ? tsfCanonical.sanitizeSlug( nicename.substring( 0, 50 ) ) : '',
					);

				if ( writeAuthorId )
					urlDataParts.set( '%author_id%', l10n.params.id );

				updateCanonicalPlaceholder();
			}
			const queueUpdateCanonical = tsfUtils.debounce( updateCanonical, 1000/60 ); // 60 fps.

			if ( writeAuthor ) {
				const nicenameInput = document.getElementById( 'user_nicename' );

				if ( nicenameInput ) {
					const updateNicename = () => {

						nicename = nicenameInput.value ?? '';
						queueUpdateCanonical();
					}
					nicenameInput.addEventListener( 'input', updateNicename );
					updateNicename();
				}
			}

			queueUpdateCanonical();
		}

		/**
		 * @since 5.2.0
		 *
		 * @function
		 * @param {Number} value
		 */
		const setRobotsIndexingState = value => {

			let type = '';

			switch ( +value ) {
				case 0: // default, unset since unknown.
					type = noindexSelect.dataset.defaultUnprotected;
					break;
				case -1: // index
					type = 'index';
					break;
				case 1: // noindex
					type = 'noindex';
					break;
			}
			if ( 'noindex' === type ) {
				canonicalPhState |= BNOINDEX;
			} else {
				canonicalPhState &= ~BNOINDEX;
			}

			updateCanonicalPlaceholder();
		}
		if ( noindexSelect ) {
			noindexSelect.addEventListener( 'change', event => setRobotsIndexingState( event.target.value ) );
			setRobotsIndexingState( noindexSelect.value );
		}
	}

	/**
	 * Initializes Title meta input listeners.
	 *
	 * @since 5.2.0
	 * @access private
	 */
	function _initTitleListeners() {

		const titleInput = document.getElementById( _titleId );
		if ( ! titleInput ) return;

		tsfTitle.setInputElement( titleInput );

		const state = JSON.parse(
			document.getElementById( `tsf-title-data_${_titleId}` )?.dataset.state || 0,
		);

		if ( state ) {
			tsfTitle.updateStateOf( _titleId, 'allowReferenceChange', ! state.refTitleLocked );
			tsfTitle.updateStateOf( _titleId, 'defaultTitle', state.defaultTitle.trim() );
			tsfTitle.updateStateOf( _titleId, 'addAdditions', state.addAdditions );
			tsfTitle.updateStateOf( _titleId, 'useSocialTagline', !! ( state.useSocialTagline || false ) );
			tsfTitle.updateStateOf( _titleId, 'additionValue', state.additionValue.trim() );
			tsfTitle.updateStateOf( _titleId, 'additionPlacement', state.additionPlacement );
		}

		// tsfTitle shouldn't be aware of this--since we remove the prefix on-input.
		const authorPrefix = tsf.escapeString( l10n.params.authorPrefix );

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
		const blogNameTrigger = document.getElementById( 'tsf-user-meta[title_no_blog_name]' );
		if ( blogNameTrigger ) {
			blogNameTrigger.addEventListener( 'change', updateTitleAdditions );
			blogNameTrigger.dispatchEvent( new Event( 'change' ) );
		}

		/**
		 * Updates default title placeholder.
		 *
		 * @function
		 * @param {string} value
		 */
		const updateDefaultTitle = val => {

			val = val?.trim();

			let title = tsfTitle.stripTitleTags ? tsf.stripTags( val ) : val;

			title ||= tsfTitle.untitledTitle;

			let defaultTitle;

			if ( window.isRtl ) {
				defaultTitle = `${title} ${authorPrefix}`;
			} else {
				defaultTitle = `${authorPrefix} ${title}`;
			}

			tsfTitle.updateStateOf( _titleId, 'defaultTitle', defaultTitle );
		}
		document.getElementById( 'display_name' )
			?.addEventListener( 'input', event => updateDefaultTitle( event.target.value ) );

		tsfTitle.enqueueUnregisteredInputTrigger( _titleId );
	}

	/**
	 * Initializes description meta input listeners.
	 *
	 * @since 5.2.0
	 * @access private
	 */
	function _initDescriptionListeners() {

		const descInput = document.getElementById( _descId );
		if ( ! descInput ) return;

		tsfDescription.setInputElement( descInput );

		const state = JSON.parse(
			document.getElementById( `tsf-description-data_${_descId}` )?.dataset.state || 0,
		);
		if ( state )
			tsfDescription.updateStateOf( _descId, 'defaultDescription', state.defaultDescription.trim() );

		tsfDescription.enqueueUnregisteredInputTrigger( _descId );
	}

	/**
	 * Initializes social meta input listeners.
	 *
	 * @since 5.2.0
	 * @access private
	 */
	function _initSocialListeners() {

		tsfSocial.setInputInstance( _socialGroup, _titleId, _descId );

		const groupData = JSON.parse(
			document.getElementById( `tsf-social-data_${_socialGroup}` )?.dataset.settings || 0,
		);
		if ( ! groupData ) return;

		tsfSocial.updateStateOf( _socialGroup, 'addAdditions', groupData.og.state.addAdditions );

		tsfSocial.updateStateOf(
			_socialGroup,
			'defaults',
			{
				ogTitle: groupData.og.state.defaultTitle,
				twTitle: groupData.tw.state.defaultTitle,
				ogDesc:  groupData.og.state.defaultDesc,
				twDesc:  groupData.tw.state.defaultDesc,
			},
		);
	}

	/**
	 * Initializes settings scripts on TSF-load.
	 *
	 * @since 5.2.0
	 * @access private
	 */
	function _loadSettings() {

		// One is not reliant on the other; this way, if one crashes, the rest still works.
		[
			_initVisibilityListeners,
			_initTitleListeners,
			_initDescriptionListeners,
			_initSocialListeners,
			_initFediverseListeners,
			_initSocialFormatHints,
		].forEach( fn => {
			try {
				fn();
			} catch ( error ) {
				console.error( `Error in ${fn.name}:`, error );
			}
		} );
	}

	return Object.assign( {
		/**
		 * Initialises all aspects of the scripts.
		 * You shouldn't call this.
		 *
		 * @since 5.2.0
		 * @access protected
		 *
		 * @function
		 */
		load: () => {
			document.body.addEventListener( 'tsf-onload', _loadSettings );
		},
	} );
}();
window.tsfAuthor.load();
