/**
 * This file holds The SEO Framework plugin's JS code for the Post SEO Settings.
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
 * Holds tsfPost values in an object to avoid polluting global namespace.
 *
 * @since 4.0.0
 *
 * @constructor
 */
window.tsfPost = function () {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 4.0.0
	 * @access public
	 * @type {(Object<string,*>)|boolean|null} l10n Localized strings
	 */
	const l10n = tsfPostL10n;

	/**
	 * @since 4.1.0
	 * @access private
	 * @type {string}
	 */
	const _titleId = 'autodescription_title';
	/**
	 * @since 4.1.0
	 * @access private
	 * @type {string}
	 */
	const _descId = 'autodescription_description';
	/**
	 * @since 5.1.0
	 * @access private
	 * @type {string}
	 */
	const _canonicalId = 'autodescription_canonical';

	/**
	 * @since 4.2.0
	 * @access private
	 * @type {string}
	 */
	const _socialGroup = 'autodescription_social_singular';

	/**
	 * Registers on resize/orientationchange listeners and debounces to only run
	 * at set intervals.
	 *
	 * For Flexbox implementation.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	function _doFlexResizeListener() {

		if ( ! document.querySelector( '.tsf-flex' ) ) return;

		const wrapper = document.getElementById( 'tsf-flex-inpost-tabs-wrapper' );

		const overflowAnimationFrame = new Map();

		const calculateTextOverflow = target => {

			const innerWrap = target.querySelector( '.tsf-flex-nav-tab-inner' ),
				  navNames  = target.querySelectorAll( '.tsf-flex-nav-name' );

			if ( innerWrap.clientWidth <= target.clientWidth ) {
				if ( +( target.dataset.displayedNames || 1 ) ) return; // Names are displayed by default on-load. Ergo, 1 by default.
				target.dataset.displayedNames = 1;
				navNames.forEach( element => {
					element.style.display = null;
					tsfUI.fadeIn( element );
				} );
			} else {
				if ( ! +( target.dataset.displayedNames || 1 ) ) return;
				target.dataset.displayedNames = 0;
				// Don't animate, we're overflowing--rectify that ASAP.
				navNames.forEach( element => { element.style.display = 'none' } );
			}

			if ( +target.dataset.displayedNames ) {
				if ( innerWrap.clientWidth > target.clientWidth ) {
					// Don't animate, we're overflowing--rectify that ASAP.
					navNames.forEach( element => { element.style.display = 'none' } );
					target.dataset.displayedNames = 0;
				} else {
					// Loop once just to be certain, for the browser may be too slow to notice the offset change.
					// Usually, this only happens once when the navNames are meant to be displayed (target width growing).
					setTimeout(
						() => {
							cancelAnimationFrame( overflowAnimationFrame.get( target.id ) );
							overflowAnimationFrame.set( target.id, requestAnimationFrame( () => calculateTextOverflow( target ) ) );
						},
						1000/144, // 144hz.
					);
				}
			}
		}
		const prepareCalculateTextOverflow = event => {
			const target = event.detail.target || wrapper;
			if ( target )
				overflowAnimationFrame.set( target.id, requestAnimationFrame( () => calculateTextOverflow( target ) ) );
		}
		window.addEventListener( 'tsf-flex-resize', prepareCalculateTextOverflow );

		/**
		 * Triggers resize on event.
		 *
		 * @function
		 * @param {HTMLElement|undefined} target The target that's being resized. Optional.
		 */
		const triggerResize = target => {
			window.dispatchEvent( new CustomEvent(
				'tsf-flex-resize',
				{
					bubbles:    false,
					cancelable: false,
					detail:     {
						target,
					},
				},
			) );
		}
		let resizeAnimationFrame = {};
		const resizeObserver = new ResizeObserver( entries => {
			// There should be only one entry... Nevertheless, let's loop this for we might add more metaboxes.
			for ( const entry of entries ) {
				let target = entry.target;
				cancelAnimationFrame( resizeAnimationFrame[ target.id ] );
				resizeAnimationFrame[ target.id ] = requestAnimationFrame( () => {
					// No support for all major browsers yet. Neither for entry.contentRect.
					// entry.dataset.boxSizeWidth ||= contentBoxSize.inlineSize;

					target.dataset.lastWidth ||= 0;

					if ( +target.clientWidth !== +target.dataset.lastWidth ) {
						target.dataset.lastWidth = target.clientWidth;
						triggerResize( target );
					}
				} );
			}
		} );
		wrapper && resizeObserver.observe( wrapper );

		// Trigger after setup
		triggerResize();
	}

	/**
	 * Sets the navigation tabs content equal to the buttons.
	 *
	 * @since 4.0.0
	 * @since 4.1.3 Now offloaded to tsfTabs.
	 * @access private
	 */
	function _initTabs() {
		tsfTabs.initStack(
			'tsfSettings',
			{
				tabToggledEvent: new CustomEvent( 'tsf-flex-tab-toggled' ),
				HTMLClasses:     {
					wrapper:          'tsf-flex-nav-tab-wrapper',
					tabRadio:         'tsf-flex-nav-tab-radio',
					tabLabel:         'tsf-flex-nav-tab-label',
					activeTab:        'tsf-flex-tab-active', // change to tsf-flex-nav-tab-active?
					// TODO make this tsf-flex-tab-active-content (force -content affix?)
					activeTabContent: 'tsf-flex-tab-content-active',
				},
				fixHistory:      true, // doesn't work since the inputs reset on navigation; enabled for future-proofing.
			}
		);
	}


	/**
	 * Returns the visibility setting from the Classic editor, as WordPress's PHP would interpret it.
	 * We could optimize this (it runs thrice in a row without a debouncer), but that's not for this function to fix.
	 * If WordPress didn't have this bug, we needn't have done these lookups at all.
	 *
	 * @since 5.0.5
	 * @access private
	 *
	 * @return {string} 'public', 'password', or 'private'.
	 */
	function _getClassicVisibility() {

		let visibility = [ ...document.getElementsByName( 'visibility' ) ].filter( e => e.checked )?.[0]?.value;

		// If password type is selected, but no password is set (or a falsy one), then assume public. This is a bug in WP.
		if ( 'password' === visibility ) {
			const val = document.getElementById( 'post_password' )?.value;
			if ( val?.length && '0' === val )
				visibility = 'public';
		}

		return visibility;
	}

	/**
	 * Registers the post privacy  listener.
	 *
	 * @since 5.1.0
	 * @access private
	 *
	 * @param {callable} callback
	 */
	function _registerPostPrivacyListener( callback ) {
		// Block Editor.
		document.addEventListener( 'tsf-updated-block-editor-visibility', event => callback( event.detail.value ) );

		// Debounce the callback for Classic Editor, because toggling visibility will also trigger a password input event.
		callback = tsfUtils.debounce( callback, 20 ); // Magic number. The duplicate event happens in under a few ms; this is also imperceptible.

		// Classic Editor.
		document.querySelector( '#visibility .save-post-visibility' )
			?.addEventListener( 'click', () => callback( _getClassicVisibility() ) );
	}

	/**
	 * Initializes canonical URL meta input listeners.
	 *
	 * @since 4.0.0
	 * @since 4.1.2 Changed name from _initCanonicalInput
	 * @since 4.1.4 Now no longer proceeds on absence of element ID 'autodescription_noindex'.
	 * @since 5.1.0 Refactored to support dynamic URL structures.
	 * @access private
	 */
	function _initVisibilityListeners() {

		const noindexSelect  = document.getElementById( 'autodescription_noindex' ),
			  canonicalInput = document.getElementById( 'autodescription_canonical' );

		const urlDataParts = new Map();

		// Prefixed with B because I don't trust using 'protected' (might become reserved).
		const BPROTECTED = 0b01,
			  BNOINDEX   = 0b10;

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
		 * @since 4.1.2
		 *
		 * @function
		 */
		const updateCanonicalPlaceholder = () => {
			tsfCanonical.updateStateOf(
				_canonicalId,
				'showUrlPlaceholder',
				( canonicalPhState & BPROTECTED ) || ( canonicalPhState & BNOINDEX )
					? false
					: true,
			);
			tsfCanonical.updateStateOf(
				_canonicalId,
				'urlDataParts',
				Object.fromEntries( urlDataParts.entries() ),
			);
		}

		if ( tsfCanonical.usingPermalinks && canonicalInput ) {
			// We rewrote %pagename% to %postname% at `Meta\URI\Utils::get_url_permastruct()`.
			const writePostname = tsfCanonical.structIncludes( _canonicalId, '%postname%' );
			const writeDate     = tsfCanonical.structIncludes( _canonicalId, [ '%year%', '%monthnum%', '%day%', '%hour%', '%minute%', '%second%' ] );
			const writeTerm     = {};
			const writeAuthor   = tsfCanonical.structIncludes( _canonicalId, '%author%' );

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
				writeTerm[ taxonomy ] = tsfCanonical.structIncludes( _canonicalId, `%${taxonomy}%` ); // Should always be true...
			} );
			// Unpack term slugs per taxonomy.
			for ( const [ taxonomy, terms ] of Object.entries( state.parentTermSlugs ) ) {
				tsfTermSlugs.store( terms, taxonomy );
				termSlugs[ taxonomy ] = terms.map( term => term.slug );
			}

			// Unpack author slugs.
			if ( writeAuthor ) {
				tsfAuthorSlugs.store( state.authorSlugs );
				authorSlug = state.authorSlugs?.[0]?.slug; // There should only be one.
			}

			/**
			 * @since 4.0.0
			 * @since 5.1.0 Now obtains an accurate canonical URL via AJAX.
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
					// Slug falls back to the title.
					if ( ! activeSlug.length && postTitle.length )
						activeSlug = tsfCanonical.sanitizeSlug( postTitle.substring( 0, 200 ) );

					// However, if the title is '0', it'll be used (and the page becomes unreachable).
					if ( ! activeSlug.length )
						activeSlug = l10n.params.id;

					// We rewrote %pagename% to %postname% at `Meta\URI\Utils::get_url_permastruct()`
					urlDataParts.set( `%postname%`, [ ...parentSlugs, activeSlug ].join( '/' ) );
				}

				// Just write these without checks; there's no meaningful performance hit.
				urlDataParts
					.set( `%post_id%`, l10n.params.id )
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

			document.addEventListener(
				'tsf-updated-block-editor',
				async event => {
					// This event is already debounced. No need to debounce it again.
					switch ( event.detail.type ) {
						case 'title':
							if ( writePostname ) {
								// The canonical URL falls back to the title.
								postTitle = event.detail.postData.get( 'title' );
								queueUpdateCanonical();
							}
							break;
						case 'slug':
							if ( writePostname ) {
								postSlug = event.detail.postData.get( 'slug' );
								queueUpdateCanonical();
							}
							break;
						case 'parent':
							if ( writePostname ) {
								parentSlugs = await tsfPostSlugs.get( event.detail.postData.get( 'parent' ) );
								queueUpdateCanonical();
							}
							break;
						case 'author':
							if ( writeAuthor ) {
								authorSlug = await tsfAuthorSlugs.get( event.detail.postData.get( 'author' ) );
								queueUpdateCanonical();
							}
							break;
						case 'date':
							if ( writeDate ) {
								dateString = new Date( event.detail.postData.get( 'date' ) ).toISOString();
								queueUpdateCanonical();
							}
					}
				}
			);

			if ( Object.values( writeTerm ).includes( true ) ) {
				// We're not debouncing this because the event is difficult to trigger in quick succession.
				const updateParentTermSlugsViaPrimary = tsfUtils.debounce(
					async event => {
						const taxonomy = event.detail.taxonomy;
						if ( writeTerm[ taxonomy ] ) {
							termSlugs[ taxonomy ] = await tsfTermSlugs.get( event.detail.id, taxonomy );
							queueUpdateCanonical();
						}
					},
					100, // Magic number. High enough to prevent self-DoS, low enough to be responsive.
				);
				document.addEventListener( `tsf-updated-primary-term`, updateParentTermSlugsViaPrimary );
			}

			// Classic Editor.
			if ( ! l10n.params.isBlockEditor ) {
				if ( writePostname ) {
					// 'editable-post-name' and 'sample-permalink' get destroyed on slug change, so we can't rely on them.
					// editSlugBox is only available when the post is not a draft for "publish posts" capability.
					const editSlugBox   = document.getElementById( 'edit-slug-box' );
					const postNameInput = document.getElementById( 'post_name' );
					const titleInput    = document.getElementById( 'title' );
					const parentIdInput = document.getElementById( 'parent_id' );

					const updatePostName = () => {
						// Title isn't used directly, but may be used if the slug isn't set.
						postTitle = titleInput?.value ?? ''
						postSlug  = postNameInput?.value ?? document.getElementById( 'editable-post-name-full' )?.innerText ?? '';
						queueUpdateCanonical();
					}
					titleInput?.addEventListener( 'input', updatePostName );
					postNameInput?.addEventListener( 'input', updatePostName );
					updatePostName();

					if ( editSlugBox ) {
						// Observe these for changes. Otherwise, we'd have to rely on a multitude of jQuery callbacks affecting it.
						new MutationObserver(
							mutationList => {
								for ( const mutation of mutationList ) {
									// Look for the reintroduction of the "slug is edited" box.
									if ( mutation.addedNodes.entries().some(
										( [ , node ] ) => 'editable-post-name-full' === node.id
									) ) {
										updatePostName();
										break;
									}
								}
							}
						).observe(
							editSlugBox,
							{ childList: true, subtree: true },
						);
					}

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
					const authorIdInput = document.getElementById( 'post_author_override' ) ?? document.getElementById( 'post_author' );

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
						// These fields don't show the date very accurately when posted "immediately" (new post).
						// TODO fixme? We'd need to run a clock. It would be a cool gimmick, though.
						document.getElementById( 'aa' ),
						document.getElementById( 'mm' ),
						document.getElementById( 'jj' ),
						document.getElementById( 'hh' ),
						document.getElementById( 'mn' ),
						document.getElementById( 'ss' ),
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
			}
		}

		if ( noindexSelect ) {
			/**
			 * @since 4.1.2
			 *
			 * @function
			 * @param {string} visibility
			 */
			const setRobotsDefaultIndexingState = visibility => {
				let _defaultIndexOption = noindexSelect.querySelector( '[value="0"]' ),
					indexDefaultValue   = '';

				switch ( visibility ) {
					case 'password':
					case 'private':
						indexDefaultValue = 'noindex';
						canonicalPhState |= BPROTECTED;
						break;

					default:
					case 'public':
						indexDefaultValue = noindexSelect.dataset.defaultUnprotected;
						canonicalPhState &= ~BPROTECTED;
						break;
				}

				if ( _defaultIndexOption )
					_defaultIndexOption.innerHTML = noindexSelect.dataset.defaultI18n.replace(
						'%s',
						tsf.escapeString( tsf.decodeEntities( indexDefaultValue ) )
					);

				updateCanonicalPlaceholder();
			}
			_registerPostPrivacyListener( setRobotsDefaultIndexingState );

			if ( l10n.states.isPrivate ) {
				setRobotsDefaultIndexingState( 'private' );
			} else if ( l10n.states.isProtected ) {
				setRobotsDefaultIndexingState( 'password' );
			} else {
				setRobotsDefaultIndexingState( 'public' );
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
			noindexSelect.addEventListener( 'change', event => setRobotsIndexingState( event.target.value ) );
			setRobotsIndexingState( noindexSelect.value );
		}
	}

	/**
	 * Initializes title meta input listeners.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	function _initTitleListeners() {

		const titleInput = document.getElementById( _titleId );
		if ( ! titleInput ) return;

		tsfTitle.setInputElement( titleInput );

		const state = JSON.parse( document.getElementById( `tsf-title-data_${_titleId}` )?.dataset.state || 0 );

		if ( state ) {
			tsfTitle.updateStateOf( _titleId, 'allowReferenceChange', ! state.refTitleLocked );
			tsfTitle.updateStateOf( _titleId, 'defaultTitle', state.defaultTitle );
			tsfTitle.updateStateOf( _titleId, 'addAdditions', state.addAdditions );
			tsfTitle.updateStateOf( _titleId, 'additionValue', state.additionValue );
			tsfTitle.updateStateOf( _titleId, 'additionPlacement', state.additionPlacement );
		}

		/**
		 * Updates title additions, based on singular settings change.
		 *
		 * @function
		 * @param {Event} event
		 */
		const updateTitleAdditions = event => {
			let addAdditions = ! event.target.checked;

			if ( l10n.params.additionsForcedDisabled ) {
				addAdditions = false;
			} else if ( l10n.params.additionsForcedEnabled ) {
				addAdditions = true;
			}

			tsfTitle.updateStateOf( _titleId, 'addAdditions', addAdditions );
		}
		const blogNameTrigger = document.getElementById( 'autodescription_title_no_blogname' );
		if ( blogNameTrigger ) {
			blogNameTrigger.addEventListener( 'change', updateTitleAdditions );
			blogNameTrigger.dispatchEvent( new Event( 'change' ) );
		}

		/**
		 * Sets private/protected visibility state.
		 *
		 * @function
		 * @param {string} visibility
		 */
		const setTitleVisibilityPrefix = visibility => {
			let prefixValue = '';

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

			tsfTitle.updateStateOf( _titleId, 'prefixValue', prefixValue );
		}
		_registerPostPrivacyListener( setTitleVisibilityPrefix );

		if ( l10n.states.isPrivate ) {
			setTitleVisibilityPrefix( 'private' );
		} else if ( l10n.states.isProtected ) {
			setTitleVisibilityPrefix( 'password' );
		}

		/**
		 * Updates default title placeholder.
		 *
		 * @function
		 * @param {string} value
		 */
		const updateDefaultTitle = val => {
			val = val?.trim() || '';

			tsfTitle.updateStateOf(
				_titleId,
				'defaultTitle',
				( tsfTitle.stripTitleTags ? tsf.stripTags( val ) : val ) || tsfTitle.untitledTitle,
			);
		}
		// The homepage listens to a static preset value. Update all others.
		if ( ! l10n.params.isFront ) {
			document.querySelector( '#titlewrap #title' ) // Extra specific to only target Classic Editor.
				?.addEventListener(
					'input',
					event => { updateDefaultTitle( event.target.value ) },
				);

			document.addEventListener(
				'tsf-updated-block-editor-title',
				event => updateDefaultTitle( event.detail.value )
			);
		}

		tsfTitle.enqueueUnregisteredInputTrigger( _titleId );
	}

	/**
	 * Initializes description meta input listeners.
	 *
	 * @since 4.0.0
	 * @since 4.1.2 Now prefills the 'useDefaultDescription' accordingly.
	 * @access private
	 */
	function _initDescriptionListeners() {

		const descInput = document.getElementById( _descId );
		if ( ! descInput ) return;

		tsfDescription.setInputElement( descInput );

		const state = JSON.parse( document.getElementById( `tsf-description-data_${_descId}` )?.dataset.state || 0 );

		if ( state ) {
			tsfDescription.updateStateOf( _descId, 'allowReferenceChange', ! state.refDescriptionLocked );
			tsfDescription.updateStateOf( _descId, 'defaultDescription', state.defaultDescription.trim() );
		}

		tsfDescription.enqueueUnregisteredInputTrigger( _descId );

		/**
		 * Sets private/protected visibility state.
		 *
		 * @function
		 * @param {string} visibility
		 */
		const setDescriptionVisibility = visibility => {
			let oldUseDefaultDescription = tsfDescription.getStateOf( _descId, 'useDefaultDescription' ),
				useDefaultDescription    = true;

			switch ( visibility ) {
				case 'password':
				case 'private':
					useDefaultDescription = false;
					break;

				default:
				case 'public':
					useDefaultDescription = true;
					break;
			}

			if ( useDefaultDescription !== oldUseDefaultDescription )
				tsfDescription.updateStateOf( _descId, 'useDefaultDescription', useDefaultDescription );
		}
		_registerPostPrivacyListener( setDescriptionVisibility );

		if ( l10n.states.isPrivate ) {
			setDescriptionVisibility( 'private' );
		} else if ( l10n.states.isProtected ) {
			setDescriptionVisibility( 'password' );
		}
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
		tsfSocial.updateStateOf(
			_socialGroup,
			'inputLocks',
			{
				ogTitle: groupData.og.state?.titleLock || false,
				twTitle: groupData.tw.state?.titleLock || false,
				ogDesc:  groupData.og.state?.descLock || false,
				twDesc:  groupData.tw.state?.descLock || false,
			}
		);
	}

	/**
	 * Initializes uncategorized general tab meta input listeners.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Removed postbox-toggled listener, since tsf-flex-resize is all-encapsulating now.
	 * @access private
	 */
	function _initGeneralListeners() {

		const enqueueGeneralInputListeners = () => {
			tsfTitle.enqueueUnregisteredInputTrigger( _titleId );
			tsfDescription.enqueueUnregisteredInputTrigger( _descId );
		}

		document.getElementById( 'tsf-flex-inpost-tab-general' )
			?.addEventListener( 'tsf-flex-tab-toggled', enqueueGeneralInputListeners );
		window.addEventListener( 'tsf-flex-resize', enqueueGeneralInputListeners );
	}

	/**
	 * Updates the SEO Bar and meta description placeholders on successful save.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	function _initUpdateMetaBox() {

		if ( ! l10n.params.isBlockEditor ) return;

		const seobar = document.querySelector( '.tsf-seo-bar' );

		// We only use this because it looks nice. The rest is implied via the counter updates.
		const seobarAjaxLoader = document.querySelector( '#tsf-doing-it-right-wrap .tsf-ajax' );

		const imageUrl = document.getElementById( 'autodescription_socialimage-url' );

		const _ogDescription = tsfSocial.getInputInstance( _socialGroup )?.inputs?.ogDesc;
		const _twDescription = tsfSocial.getInputInstance( _socialGroup )?.inputs?.twDesc;

		const getData = {
			seobar:          !! seobar,
			metadescription: !! document.getElementById( _descId ),
			ogdescription:   !! _ogDescription,
			twdescription:   !! _twDescription,
			imageurl:        !! imageUrl,
		}

		const onSuccess = response => {

			response = tsf.convertJSONResponse( response );

			// Wait the same amount of time as the SEO Bar, so to sync the changes.
			const fadeTime = 75;

			setTimeout(
				() => {
					tsfDescription.updateStateOf( _descId, 'defaultDescription', response.data.metadescription.trim() );

					const socialDefaults = tsfSocial.getStateOf( _socialGroup, 'defaults' );
					socialDefaults.ogDesc = response.data.ogdescription.trim();
					socialDefaults.twDesc = response.data.twdescription.trim();
					tsfSocial.updateStateOf( _socialGroup, 'defaults', socialDefaults );

					if ( imageUrl ) {
						// Is this necessary? It's safer than assuming, though :)
						imageUrl.placeholder = tsf.decodeEntities( response.data.imageurl );
						imageUrl.dispatchEvent( new Event( 'change' ) );
						tsfTT.triggerReset();
					}

					tsfAys.reset();
				},
				fadeTime,
			);

			seobar && tsfUI.fadeOut(
				seobar,
				fadeTime,
				() => {
					seobarAjaxLoader && tsf.unsetAjaxLoader( seobarAjaxLoader, true );
					seobar.innerHTML = response.data.seobar;

					tsfUI.fadeIn(
						seobar,
						fadeTime,
						() => {
							tsfTT.triggerReset();
						}
					)
				},
			);
		};

		const onFailure = () => {
			seobarAjaxLoader && tsf.unsetAjaxLoader( seobarAjaxLoader, false );
		}

		document.addEventListener(
			'tsf-gutenberg-onsave',
			() => {
				// Reset ajax loader, we only do that for the SEO Bar.
				seobarAjaxLoader && tsf.resetAjaxLoader( seobarAjaxLoader );

				// Set ajax loader.
				seobarAjaxLoader && tsf.setAjaxLoader( seobarAjaxLoader );

				wp.ajax.send(
					'tsf_update_post_data',
					{
						data: {
							nonce:   l10n.nonces.edit_post[ l10n.params.id ],
							post_id: l10n.params.id,
							get:     getData,
						},
						timeout: 7000,
					},
				).done( onSuccess ).fail( onFailure );
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
			_initGeneralListeners,
		].forEach( fn => {
			try {
				fn();
			} catch ( error ) {
				console.error( `Error in ${fn.name}:`, error );
			}
		} );
	}

	/**
	 * Initializes settings scripts on TSF-ready.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now registers the refNa title input.
	 * @access private
	 */
	function _readySettings() {
		// Initializes flex tab resize listeners.
		_doFlexResizeListener();

		// Initializes flex tab listeners and fixes positions.
		_initTabs();

		// Set Gutenberg update listeners.
		_initUpdateMetaBox();
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
			document.body.addEventListener( 'tsf-ready', _readySettings );
		},
	}, {
		l10n,
	} );
}();
window.tsfPost.load();
