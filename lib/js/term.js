/**
 * This file holds The SEO Framework plugin's JS code for the Term SEO Settings.
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
 * Holds tsfTerm values in an object to avoid polluting global namespace.
 *
 * @since 4.0.0
 *
 * @constructor
 */
window.tsfTerm = function () {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 4.0.0
	 * @access public
	 * @type {(Object<string,*>)|boolean|null} l10n Localized strings
	 */
	const l10n = tsfTermL10n;

	/**
	 * @since 5.1.0
	 * @access public
	 * @type {String} The current taxonomy.
	 */
	const taxonomy = tsf.escapeString( l10n.params.taxonomy );

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
	 * @since 5.1.0
	 * @access private
	 * @type {string}
	 */
	const _canonicalId = 'autodescription-meta[canonical]';

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
	 */
	function _initVisibilityListeners() {

		const noindexSelect  = document.getElementById( 'autodescription-meta[noindex]' ),
			  canonicalInput = document.getElementById( 'autodescription-meta[canonical]' );

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
			// We don't set parentTermSlugs or isHierarchical here. They aren't something tsfCanonical can work with.
		}

		tsfCanonical.enqueueTriggerUnregisteredInput( _canonicalId );

		/**
		 * @since 4.1.2
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

		if ( tsfCanonical.usingPermalinks && canonicalInput ) {
			const writeTaxonomy = tsfCanonical.structIncludes( _canonicalId, `%${taxonomy}%` );

			let termSlug    = '',
				termName    = '',
				parentSlugs = [];

			// Unpack post slugs.
			if ( writeTaxonomy ) {
				tsfTermSlugs.store( state.parentTermSlugs, taxonomy );
				// We preemptively write here because the selection might be unavailable.
				parentSlugs = state.parentTermSlugs.map( term => term.slug ); // isHierarchical is checked in PHP for this.
			}

			/**
			 * @since 5.1.0
			 *
			 * @function
			 */
			const updateCanonical = () => {
				if ( writeTaxonomy ) {
					let activeSlug = '';

					if ( termSlug.length ) {
						// postName always gets trimmed to the first 200 characters.
						// Parent slugs have already had the same treatment by WP Core, so we ignore those.
						activeSlug = tsfCanonical.sanitizeSlug( termSlug.substring( 0, 200 ) );

						if ( '0' === activeSlug ) // '0' will be ignored by WP.
							activeSlug = '';
					}
					// Slug falls back to the title.
					if ( ! activeSlug.length && termName.length )
						activeSlug = tsfCanonical.sanitizeSlug( termName.substring( 0, 200 ) );

					// However, if the title is '0', it'll be used (but the homepage is shown).
					if ( ! activeSlug.length )
						activeSlug = l10n.params.id;

					urlDataParts.set( `%${taxonomy}%`, [ ...parentSlugs, activeSlug ].join( '/' ) );
				}

				updateCanonicalPlaceholder();
			}
			const queueUpdateCanonical = tsfUtils.debounce( updateCanonical, 1000/60 ); // 60 fps.

			if ( writeTaxonomy ) {
				const termSlugInput = document.getElementById( 'slug' ),
					  termNameInput = document.getElementById( 'name' ),
					  parentIdInput = document.getElementById( 'parent' );

				const updateTermName = () => {
					// Title isn't used directly, but may be used if the slug isn't set.
					termName = termNameInput?.value ?? '';
					termSlug = termSlugInput?.value ?? ''
					queueUpdateCanonical();
				}
				termSlugInput?.addEventListener( 'input', updateTermName );
				termNameInput?.addEventListener( 'input', updateTermName );
				updateTermName();

				if ( parentIdInput ) {
					const updateParentSlug = tsfUtils.debounce(
						async () => {
							parentSlugs = await tsfTermSlugs.get( parentIdInput.value, taxonomy );
							queueUpdateCanonical();
						},
						100, // Magic number. High enough to prevent self-DoS, low enough to be responsive.
					);
					document.getElementById( 'parent' )?.addEventListener( 'change', updateParentSlug );
					updateParentSlug();
				}
			}

			queueUpdateCanonical();
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
	 * @since 4.0.0
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
	 */
	function _initDescriptionListeners() {

		const descInput = document.getElementById( _descId );
		if ( ! descInput ) return;

		tsfDescription.setInputElement( descInput );

		const state = JSON.parse(
			document.getElementById( `tsf-description-data_${_descId}` )?.dataset.state || 0,
		);
		if ( state ) {
			// tsfDescription.updateState( 'allowReferenceChange', ! state.refDescriptionLocked );
			tsfDescription.updateStateOf( _descId, 'defaultDescription', state.defaultDescription.trim() );
		}

		// TODO set term-description-content (via ajax) listeners?

		tsfDescription.enqueueUnregisteredInputTrigger( _descId );
	}

	/**
	 * Initializes social meta input listeners.
	 *
	 * @since 4.2.0
	 * @access private
	 */
	function _initSocialListeners() {

		tsfSocial.setInputInstance( _socialGroup, _titleId, _descId );

		const groupData = JSON.parse(
			document.getElementById( `tsf-social-data_${_socialGroup}` )?.dataset.settings || 0,
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
	 * @since 5.1.0 Added error handling.
	 * @access private
	 */
	function _loadSettings() {
		// One is not reliant on the other; this way, if one crashes, the rest still works.
		[
			_initVisibilityListeners,
			_initTitleListeners,
			_initDescriptionListeners,
			_initSocialListeners,
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
		 * @since 4.0.0
		 * @access protected
		 *
		 * @function
		 */
		load: () => {
			document.body.addEventListener( 'tsf-onload', _loadSettings );
		},
	}, {
		l10n,
		taxonomy,
	} );
}();
window.tsfTerm.load();
