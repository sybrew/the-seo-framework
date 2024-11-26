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
window.tsfLe = function () {

	/**
	 * The current default fields data obtained via tsfLeData.
	 * @typedef {?Object} fieldsData
	 * @property {Object} doctitle    - {value: string}
	 * @property {Object} description - {value: string}
	 * @property {Object} canonical   - {value: string}
	 * @property {Object} noindex     - {value: number, isSelect: boolean, default: string}
	 * @property {Object} nofollow    - {value: number, isSelect: boolean, default: string}
	 * @property {Object} noarchive   - {value: number, isSelect: boolean, default: string}
	 * @property {Object} redirect    - {value: string, placeholder: string}
	 */
	let fieldsData;

	/**
	 * The current default post data obtained via tsfLePostData.
	 * @typedef {?Object} postData
	 * @property {Boolean} isFront
	 */
	let postData;

	/**
	 * The currently invoked quick editor type.
	 * @since 5.1.0
	 * @access private
	 * @var {string} editType
	 */
	let _editType = '';

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
	 */
	function _setInlinePostValues() {
		for ( const option in fieldsData ) {
			const params  = fieldsData[ option ];
			const element = document.getElementById( 'autodescription-quick[%s]'.replace( '%s', option ) );

			if ( ! element ) continue;

			if ( params.isSelect ) {
				tsf.selectByValue( element, params.value );

				// Do `sprintf( 'Default (%s)', params.default )`.
				const _default = element.querySelector( '[value="0"]' );
				if ( _default )
					_default.innerHTML = _default.innerHTML.replace( '%s', tsf.escapeString( tsf.decodeEntities( params.default ) ) );
			} else {
				element.value = tsf.decodeEntities( params.value );

				if ( params.placeholder?.length )
					element.placeholder = tsf.decodeEntities( params.placeholder );
			}
		}
	}

	/**
	 * Sets inline term values for quick-edit.
	 * Copy of _setInlinePostValues(), for now.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	function _setInlineTermValues() {
		return _setInlinePostValues();
	}

	/**
	 * Returns the post's visibility.
	 *
	 * @since 5.1.0
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
			const pass = inlineEditWrap?.querySelector( '[name=post_password]' )?.value;
			// If password type is filled, but the password is falsy, then assume public. This is a bug in WP.
			if ( pass?.length && '0' !== pass )
				visibility = 'password';
		}

		return visibility;
	}

	/**
	 * Registers the post privacy  listener.
	 *
	 * @since 5.1.0
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

		if ( 'post' === _editType ) {
			/**
			 * @since 4.1.0
			 * @since 5.1.0 1. No longer considers '0' a valid password.
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
		}

		/**
		 * Sets default title state.
		 *
		 * @since 4.1.0
		 * @since 4.1.4 Is now considerate of additionsForcedDisabled/additionsForceEnabled
		 * @since 5.1.0 Moved from parent scope.
		 *
		 * @function
		 * @param {Event} event
		 */
		const setDefaultTitle = event => {
			const target     = ( event.originalEvent || event ).target,
				  inputTitle = target.value?.trim() || '';

			// '0' doesn't return false for ||. So, this needs no string.length test.
			let defaultTitle = (
					tsfTitle.stripTitleTags
						? tsf.stripTags( inputTitle )
						: inputTitle
				) || tsfTitle.untitledTitle;

			if ( 'tax' === _editType ) {
				const termPrefix = data?.termPrefix?.trim() || '';

				if ( termPrefix.length ) {
					if ( window.isRtl ) {
						defaultTitle = `${defaultTitle} ${termPrefix}`;
					} else {
						defaultTitle = `${termPrefix} ${defaultTitle}`;
					}
				}
			}

			// TODO figure out if this is necessary. tsfTitle also escapes...
			defaultTitle = tsf.escapeString( tsf.decodeEntities( defaultTitle.trim() ) );

			tsfTitle.updateStateOf( titleId, 'defaultTitle', defaultTitle );
		}

		// This wrap is a clone of a template (#inline-edit). So, we must specifically target the cloned wrapper.
		const inlineEditWrap = document.getElementById( `edit-${id}` );

		switch ( _editType ) {
			case 'post':
				// The wrap should always exist, but since we didn't create it, we test for its existence now.
				const postTitleInput = inlineEditWrap?.querySelector( '[name=post_title]' );

				if ( postTitleInput ) {
					// The homepage listens to a static preset value. Update all others.
					if ( ! postData.isFront ) {
						postTitleInput.addEventListener( 'input', setDefaultTitle );
						postTitleInput.dispatchEvent( new Event( 'input' ) );
					}
				}
				break;
			case 'tax':
				// The wrap should always exist, but since we didn't create it, we test for its existence now.
				const termNameInput  = inlineEditWrap?.querySelector( '[name=name]' );

				if ( termNameInput ) {
					termNameInput.addEventListener( 'input', setDefaultTitle );
					termNameInput.dispatchEvent( new Event( 'input' ) );
				}
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

		const state = JSON.parse( document.getElementById( `tsfLeDescriptionData[${id}]` )?.dataset.leDescription || 0 );
		if ( state ) {
			tsfDescription.updateStateOf( descId, 'allowReferenceChange', ! state.refDescriptionLocked );
			tsfDescription.updateStateOf( descId, 'defaultDescription', state.defaultDescription.trim() );
		}

		tsfTT.triggerReset();
	}

	/**
	 * Augments and binds inline Visibility input values for quick-edit.
	 *
	 * @since 5.1.0
	 * @access private
	 *
	 * @param {string} id The post/term ID.
	 */
	function _prepareVisibilityInput( id ) {

		const indexId     = 'autodescription-quick[noindex]',
			  canonicalId = 'autodescription-quick[canonical]';

		const indexSelect    = document.getElementById( indexId ),
			  canonicalInput = document.getElementById( canonicalId );

		const urlDataParts = new Map();

		// Prefixed with B because I don't trust using 'protected' (might become reserved).
		const BPROTECTED = 0b01, // Post only, ignored on Term.
			  BNOINDEX   = 0b10;

		let canonicalPhState = 0b00;

		tsfCanonical.setInputElement( canonicalInput );

		const state = JSON.parse( document.getElementById( `tsfLeCanonicalData[${id}]` )?.dataset.leCanonical || 0 );

		if ( state ) {
			tsfCanonical.updateStateOf( canonicalId, 'allowReferenceChange', ! state.refCanonicalLocked );
			tsfCanonical.updateStateOf( canonicalId, 'defaultCanonical', state.defaultCanonical.trim() );
			tsfCanonical.updateStateOf( canonicalId, 'preferredScheme', state.preferredScheme.trim() );
			tsfCanonical.updateStateOf( canonicalId, 'urlStructure', state.urlStructure );
		}

		tsfCanonical.enqueueTriggerUnregisteredInput( canonicalId );

		/**
		 * @since 5.1.0
		 *
		 * @function
		 */
		const updateCanonicalPlaceholder = () => {
			tsfCanonical.updateStateOf(
				canonicalId,
				'showUrlPlaceholder',
				( canonicalPhState & BPROTECTED ) || ( canonicalPhState & BNOINDEX )
					? false
					: true,
			);
			tsfCanonical.updateStateOf(
				canonicalId,
				'urlDataParts',
				Object.fromEntries( urlDataParts.entries() ),
			);
		}

		// This wrap is a clone of a template (#inline-edit). So, we must specifically target the cloned wrapper.
		const inlineEditWrap = document.getElementById( `edit-${id}` );

		// The wrap should always exist, but since we didn't create it, we test for it.
		if ( tsfCanonical.usingPermalinks && canonicalInput && inlineEditWrap ) switch ( _editType ) {
			case 'post': { // rescope for there's reused block-scoped variables with 'tax'.
				// We rewrote %pagename% to %postname% at `Meta\URI\Utils::get_url_permastruct()`.
				const writePostname = tsfCanonical.structIncludes( canonicalId, '%postname%' );
				const writeDate     = tsfCanonical.structIncludes( canonicalId, [ '%year%', '%monthnum%', '%day%', '%hour%', '%minute%', '%second%' ] );
				const writeTerm     = {};
				const writeAuthor   = tsfCanonical.structIncludes( canonicalId, '%author%' );

				let postSlug    = '',
					postTitle   = '',
					authorSlug  = '',
					dateString  = '',
					parentSlugs = [],
					termSlugs   = [];

				// Unpack post slugs.
				if ( writePostname ) {
					tsfPostSlugs.store( state.parentPostSlugs );
					// We preemptively write here because the selection might be unavailable.
					parentSlugs = state.parentPostSlugs.map( post => post.slug ); // isHierarchical is checked in PHP for this.
				}

				// Add support for every registered taxonomy. We do this because the terms are not always available.
				state.supportedTaxonomies.forEach( taxonomy => {
					writeTerm[ taxonomy ] = tsfCanonical.structIncludes( canonicalId, `%${taxonomy}%` ); // Should always be true...
				} );
				// Unpack term slugs per taxonomy.
				for ( const [ taxonomy, terms ] of Object.entries( state.parentTermSlugs ) ) {
					tsfTermSlugs.store( terms, taxonomy );
					termSlugs[ taxonomy ] = terms.map( term => term.slug );
				}

				// Unpack author slugs.
				if ( writeAuthor ) {
					tsfAuthorSlugs.store( state.authorSlugs );
					authorSlug = state.authorSlugs?.[0].slug; // There should only be one.
				}

				/**
				 * @since 5.1.0
				 *
				 * @function
				 */
				const updateCanonical = () => {
					if ( writePostname ) {
						let activeSlug = '';

						if ( postSlug.length ) {
							// postName always gets trimmed to the first 200 characters.
							// Parent slugs have already had the same treatment by WP Core, so we ignore those.
							activeSlug = tsfCanonical.sanitizeSlug( postSlug.substring( 0, 200 ) );

							if ( '0' === activeSlug ) // '0' will be ignored by WP.
								activeSlug = '';
						}
						// Slug falls back to the title, also if '0'. However, if the title is '0', it'll be used.
						if ( ! activeSlug.length && postTitle.length )
							activeSlug = tsfCanonical.sanitizeSlug( postTitle.substring( 0, 200 ) );

						// However, if the title is '0', it'll be used (and the page becomes unreachable).
						if ( ! activeSlug.length )
							activeSlug = id;

						// We rewrote %pagename% to %postname% at `Meta\URI\Utils::get_url_permastruct()`
						urlDataParts.set( `%postname%`, [ ...parentSlugs, activeSlug ].join( '/' ) );
					}

					// Just write these without checks; there's no meaningful performance hit.
					urlDataParts
						.set( `%post_id%`, id )
						.set( `%author%`, authorSlug );

					if ( writeDate ) {
						const date    = new Date( dateString );
						const padDate = v => String( v ).padStart( 2, '0' );

						urlDataParts
							.set( `%year%`, date.getFullYear() )
							.set( `%monthnum%`, padDate( date.getMonth() + 1 ) )
							.set( `%day%`, padDate( date.getDate() ) )
							.set( `%hour%`, padDate( date.getHours() ) )
							.set( `%minute%`, padDate( date.getMinutes() ) )
							.set( `%second%`, padDate( date.getSeconds() ) ); // This doesn't even work on the front-end.
					}

					for ( const taxonomy in writeTerm ) {
						// If is writeable, then set the URL data part.
						writeTerm[ taxonomy ] && urlDataParts
							.set(
								`%${taxonomy}%`,
								Object.values( termSlugs[ taxonomy ] ?? {} ).join( '/' ),
							);
					}

					updateCanonicalPlaceholder();
				}
				const queueUpdateCanonical = tsfUtils.debounce( updateCanonical, 1000/60 ); // 60 fps.

				// TODO 5.1.x
				// if ( Object.values( writeTerm ).includes( true ) ) {
				// 	// We're not debouncing this because the event is difficult to trigger in quick succession.
				// 	const updateParentTermSlugsViaPrimary = tsfUtils.debounce(
				// 		async event => {
				// 			const taxonomy = event.detail.taxonomy;
				// 			if ( writeTerm[ taxonomy ] ) {
				// 				termSlugs[ taxonomy ] = await tsfTermSlugs.get( event.detail.id, taxonomy );
				// 				queueUpdateCanonical();
				// 			}
				// 		},
				// 		100, // Magic number. High enough to prevent self-DoS, low enough to be responsive.
				// 	);
				// 	document.addEventListener( `tsf-updated-primary-term`, updateParentTermSlugsViaPrimary );
				// }
				if ( writePostname ) {
					const postNameInput = inlineEditWrap.querySelector( '[name=post_name]' );
					const titleInput    = inlineEditWrap.querySelector( '[name=post_title]' );
					const parentIdInput = inlineEditWrap.querySelector( '[name=post_parent]' );

					const updatePostName = () => {
						// Title isn't used directly, but may be used if the slug isn't set.
						postTitle = titleInput?.value ?? ''
						postSlug  = postNameInput?.value ?? '';
						queueUpdateCanonical();
					}
					postNameInput?.addEventListener( 'input', updatePostName );
					titleInput?.addEventListener( 'input', updatePostName );
					updatePostName();

					if ( parentIdInput ) {
						const updateParentSlug = tsfUtils.debounce(
							async () => {
								parentSlugs = await tsfPostSlugs.get( parentIdInput.value );
								queueUpdateCanonical();
							},
							100, // Magic number. High enough to prevent self-DoS, low enough to be responsive.
						);
						parentIdInput.addEventListener( 'input', updateParentSlug );
						updateParentSlug();
					}
				}

				if ( writeAuthor ) {
					const authorIdInput = inlineEditWrap.querySelector( '[name=post_author]' );

					if ( authorIdInput ) {
						// We debounce the event listener since we're making AJAX requests.
						const updateAuthor = tsfUtils.debounce(
							async () => {
								authorSlug = await tsfAuthorSlugs.get( authorIdInput.value );
								queueUpdateCanonical();
							},
							100, // Magic number. High enough to prevent self-DoS, low enough to be responsive.
						);
						authorIdInput.addEventListener( 'input', updateAuthor );
						updateAuthor();
					}
				}

				if ( writeDate ) {
					const dateFields = [
						inlineEditWrap.querySelector( '[name=aa]' ),
						inlineEditWrap.querySelector( '[name=mm]' ), // doesn't start at 0, but 1.
						inlineEditWrap.querySelector( '[name=jj]' ),
						inlineEditWrap.querySelector( '[name=hh]' ),
						inlineEditWrap.querySelector( '[name=mn]' ),
						inlineEditWrap.querySelector( '[name=ss]' ), // hidden
					];
					const useDateFields = ! dateFields.some( v => v === null );

					const getActiveDateValues = () => {
						const values = dateFields.map( field => field.value );

						// WordPress compensated for the 0-index month, we need to revert that.
						if ( values[1] )
							--values[1];

						return values.map( v => v ?? '00' );
					}

					const updateDateString = () => {
						dateString = useDateFields
							? new Date( ...getActiveDateValues() ).toISOString()
							: state.publishDate;
						queueUpdateCanonical();
					}

					dateFields.forEach( field => {
						field?.addEventListener( 'change', updateDateString );
					} );

					updateDateString();
				}
				queueUpdateCanonical();
				break;
			}
			case 'tax': { // rescope for there's reused block-scoped variables with 'post'.
				// This is how window.inlineEditTax.save() gets the taxonomy (actually, it gets it from anywhere on the page).
				const taxonomy = inlineEditWrap.querySelector( 'input[name=taxonomy]' )?.value || '';

				const writeTaxonomy = tsfCanonical.structIncludes( canonicalId, `%${taxonomy}%` );

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
							activeSlug = id;

						urlDataParts.set( `%${taxonomy}%`, [ ...parentSlugs, activeSlug ].join( '/' ) );
					}

					updateCanonicalPlaceholder();
				}
				const queueUpdateCanonical = tsfUtils.debounce( updateCanonical, 1000/60 ); // 60 fps.

				if ( writeTaxonomy ) {
					const termNameInput = inlineEditWrap.querySelector( '[name=name]' ),
						  termSlugInput = inlineEditWrap.querySelector( '[name=slug]' );

					const updateTermName = () => {
						// Title isn't used directly, but may be used if the slug isn't set.
						termName = termNameInput?.value ?? '';
						termSlug = termSlugInput?.value ?? ''
						queueUpdateCanonical();
					}
					termSlugInput?.addEventListener( 'input', updateTermName );
					termNameInput?.addEventListener( 'input', updateTermName );
					updateTermName();
				}

				queueUpdateCanonical();
			}
		}

		if ( indexSelect ) {
			if ( 'post' === _editType ) {
				/**
				 * @since 5.1.0
				 *
				 * @function
				 */
				const setRobotsDefaultIndexingState = tsfUtils.debounce(
					() => {
						let _defaultIndexOption = indexSelect.querySelector( '[value="0"]' ),
							indexDefaultValue   = '';

						switch ( _getPostVisibility( id ) ) {
							case 'password':
							case 'private':
								indexDefaultValue = 'noindex';
								canonicalPhState |= BPROTECTED;
								break;

							default:
							case 'public':
								indexDefaultValue = fieldsData.noindex.default;
								canonicalPhState &= ~BPROTECTED;
								break;
						}

						if ( _defaultIndexOption )
							_defaultIndexOption.innerHTML = indexSelect.dataset.defaultI18n.replace(
								'%s',
								tsf.escapeString( tsf.decodeEntities( indexDefaultValue ) )
							);

						updateCanonicalPlaceholder();
					},
					1000/60, // 60 fps
				);

				inlineEditWrap?.querySelector( '[name=post_password]' )
					?.addEventListener( 'input', () => setRobotsDefaultIndexingState() );
				inlineEditWrap?.querySelector( '[name=keep_private]' )
					?.addEventListener( 'change', () => setRobotsDefaultIndexingState() );

				setRobotsDefaultIndexingState();
			}

			/**
			 * @since 5.1.0
			 *
			 * @function
			 * @param {Number} value
			 */
			const setRobotsIndexingState = value => {
				let type = '';

				switch ( +value ) {
					case 0: // default, unset since unknown.
						type = fieldsData.noindex.default;
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
			indexSelect.addEventListener( 'change', event => setRobotsIndexingState( event.target.value ) );
			setRobotsIndexingState( indexSelect.value );
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

				_editType  = 'post';
				fieldsData = JSON.parse( document.getElementById( `tsfLeData[${id}]` )?.dataset.le || 0 ) || {};
				postData   = JSON.parse( document.getElementById( `tsfLePostData[${id}]` )?.dataset.lePostData || 0 ) || {};

				[
					_setInlinePostValues,
					_prepareVisibilityInput,
					_prepareTitleInput,
					_prepareDescriptionInput,
				].forEach( fn => {
					try {
						fn( id );
					} catch ( error ) {
						console.error( `Error in ${fn.name}:`, error );
					}
				} );
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

				_editType  = 'tax';
				fieldsData = JSON.parse( document.getElementById( `tsfLeData[${id}]` )?.dataset.le || 0 ) || {};

				[
					_setInlineTermValues,
					_prepareVisibilityInput,
					_prepareTitleInput,
					_prepareDescriptionInput,
				].forEach( fn => {
					try {
						fn( id );
					} catch ( error ) {
						console.error( `Error in ${fn.name}:`, error );
					}
				} );
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
