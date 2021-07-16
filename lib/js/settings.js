/**
 * This file holds The SEO Framework plugin's JS code for the SEO Settings page.
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
 * Holds tsfSettings values in an object to avoid polluting global namespace.
 *
 * @since 4.0.0
 * TODO FIXME: We check for setting's existence inconsistently... Resolve at TSF 5.0?
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfSettings = function( $ ) {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 4.0.0
	 * @access public
	 * @type {(Object<string, *>)|boolean|null} l10n Localized strings
	 */
	const l10n = 'undefined' !== typeof tsfSettingsL10n && tsfSettingsL10n;

	/**
	 * Returns settings ID.
	 *
	 * @since 4.1.1
	 * @access private
	 *
	 * @function
	 * @param {string} name
	 * @return {string} The full settings ID/name.
	 */
	const _getSettingsId = name => `autodescription-site-settings[${name}]`;

	/**
	 * Initializes input helpers for the General Settings.
	 *
	 * @since 4.0.0
	 * @access private
	 * TODO remove event-based architecture, and just invoke the changes, instead. See: `$.trigger()`
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	const _initGeneralSettings = () => {

		const $window = $( window );

		/**
		 * Triggers displaying/hiding of character counters on the settings page.
		 *
		 * @since 4.1.1
		 * @access private
		 *
		 * @function
		 * @param {Event} event
		 * @return {undefined}
		 */
		const toggleCharCounterDisplay = event => {
			document.querySelectorAll( '.tsf-counter-wrap' ).forEach( el => {
				el.style.display = event.target.checked ? '' : 'none';
			} );
			event.target.checked && tsfC.triggerCounterUpdate();
		}
		const displayCharCounterInput = document.getElementById( _getSettingsId( 'display_character_counter' ) );
		if ( displayCharCounterInput ) {
			displayCharCounterInput.addEventListener( 'click', toggleCharCounterDisplay );
		}

		/**
		 * Triggers displaying/hiding of pixel counters on the settings page.
		 *
		 * @since 4.0.0
		 * @access private
		 *
		 * @function
		 * @param {Event} event
		 * @return {undefined}
		 */
		const togglePixelCounterDisplay = event => {
			document.querySelectorAll( '.tsf-pixel-counter-wrap' ).forEach( el => {
				el.style.display = event.target.checked ? '' : 'none';
			} );
			event.target.checked && tsfC.triggerCounterUpdate();
		}
		const displayPixelCounterInput = document.getElementById( _getSettingsId( 'display_pixel_counter' ) );
		if ( displayPixelCounterInput ) {
			displayPixelCounterInput.addEventListener( 'click', togglePixelCounterDisplay );
		}

		const postTypeHelpTemplate   = wp.template( 'tsf-disabled-post-type-help' )();
		const taxonomyHelpTemplate   = wp.template( 'tsf-disabled-taxonomy-help' )();
		const taxonomyPtHelpTemplate = wp.template( 'tsf-disabled-taxonomy-from-pt-help' )();

		/**
		 * @param {string} postType
		 * @return {string} The cloned input class used for sending POST data.
		 */
		const getCloneClassPT = postType => tsf.escapeString( 'tsf-disabled-post-type-input-clone-' + postType );
		/**
		 * @param {string} taxonomy
		 * @return {string} The cloned input class used for sending POST data.
		 */
		const getCloneClassTaxonomy = taxonomy => tsf.escapeString( 'tsf-disabled-taxonomy-input-clone-' + taxonomy );
		/**
		 * @param {string} postType
		 * @return {array} A list of affected post type settings.
		 */
		const getPostTypeRobotsSettings = postType => [
			document.getElementById( `${ _getSettingsId( 'noindex_post_types' ) }[${postType}]` ),
			document.getElementById( `${ _getSettingsId( 'nofollow_post_types' ) }[${postType}]` ),
			document.getElementById( `${ _getSettingsId( 'noarchive_post_types' ) }[${postType}]` ),
		].filter( el => el );
		/**
		 * @param {string} taxonomy
		 * @return {array} A list of affected post type settings.
		 */
		const getTaxonomyRobotsSettings = taxonomy => [
			document.getElementById( `${ _getSettingsId( 'noindex_taxonomies' ) }[${taxonomy}]` ),
			document.getElementById( `${ _getSettingsId( 'nofollow_taxonomies' ) }[${taxonomy}]` ),
			document.getElementById( `${ _getSettingsId( 'noarchive_taxonomies' ) }[${taxonomy}]` ),
		].filter( el => el );

		const augmentPTRobots = ( postType, disable ) => {
			if ( disable ) {
				getPostTypeRobotsSettings( postType ).forEach( element => {
					if ( ! element ) return;

					let clone = element.cloneNode( true );
					clone.type = 'hidden';
					// Because the clone is hidden, we must set its value based on the checked state's + value thereof:
					clone.value = element.checked ? element.value : '';
					// Note that this might cause inconsistencies when other JS elements try to amend the data via ID.
					// However, they should use 'getElementsByName', anyway.
					clone.id += '-cloned' ;
					clone.classList.add( getCloneClassPT( postType ) );

					element.disabled                = true;
					element.dataset.disabledWarning = 1;

					$( element.closest( 'label' ) ).append( postTypeHelpTemplate ).append( clone );
				} );

				tsfTT.triggerReset();
			} else {
				getPostTypeRobotsSettings( postType ).forEach( element => {
					if ( ! element ) return;
					if ( ! element.dataset.disabledWarning ) return;

					// 'tsf-post-type-warning' is defined at `../inc/views/templates/settings/settings.php`
					element.closest( 'label' ).querySelector( '.tsf-post-type-warning' ).remove();

					document.querySelectorAll( '.' + getCloneClassPT( postType ) ).forEach( ( clone ) => {
						clone.remove();
					} );

					element.disabled               = false;
					element.dataset.disabledWarning = '';
				} );
			}
		}
		const augmentTaxonomyRobots = ( taxonomy, disable ) => {
			if ( disable ) {
				getTaxonomyRobotsSettings( taxonomy ).forEach( element => {
					if ( ! element ) return;

					let clone = element.cloneNode( true );
					clone.type = 'hidden';
					// Because the clone is hidden, we must set its value based on the checked state's + value thereof:
					clone.value = element.checked ? element.value : '';
					// Note that this might cause inconsistencies when other JS elements try to amend the data via ID.
					// However, they should use 'getElementsByName', anyway.
					clone.id += '-cloned' ;
					clone.classList.add( getCloneClassTaxonomy( taxonomy ) );

					element.disabled               = true;
					element.dataset.disabledWarning = 1;

					$( element.closest( 'label' ) ).append( taxonomyHelpTemplate ).append( clone );
				} );

				tsfTT.triggerReset();
			} else {
				getTaxonomyRobotsSettings( taxonomy ).forEach( element => {
					if ( ! element ) return;
					if ( ! element.dataset.disabledWarning ) return;

					// 'tsf-taxonomy-warning' is defined at `../inc/views/templates/settings/settings.php`
					element.closest( 'label' ).querySelector( '.tsf-taxonomy-warning' ).remove();

					document.querySelectorAll( '.' + getCloneClassTaxonomy( taxonomy ) ).forEach( ( clone ) => {
						clone.remove();
					} );

					element.disabled                = false;
					element.dataset.disabledWarning = '';
				} );
			}
		}

		const addTaxDisabledByPtWarning = ( taxonomy, disable ) => {
			let taxEl = document.getElementById( `${ _getSettingsId( 'disabled_taxonomies' ) }[${taxonomy}]` );
			if ( disable ) {
				$( taxEl.closest( 'label' ) ).append( taxonomyPtHelpTemplate );
				tsfTT.triggerReset();
			} else {
				// 'tsf-taxonomy-from-pt-warning' is defined at `../inc/views/templates/settings/settings.php`
				taxEl.closest( 'label' ).querySelector( '.tsf-taxonomy-from-pt-warning' ).remove();
			}
		}

		const excludedPostTypes     = new Set(),
			  excludedTaxonomies    = new Set(),
			  excludedTaxonomiesAll = new Set(),
			  excludedPtTaxonomies  = new Set();
		const validateTaxonomyState = () => {
			// We want to show that the taxonomy is excluded, but make that auto-reversible, and somehow still enactable?

			let taxEntries    = document.querySelectorAll( '.tsf-excluded-taxonomies' ),
				triggerchange = false;

			taxEntries.forEach( element => {
				// get taxonomy from last [] entry.
				let taxonomy = element.name.split( /(?:.+\[)(.+?)(?:])/ ).join( '' );

				let taxPostTypes = JSON.parse( element.dataset.postTypes ),
					disabled     = taxPostTypes.every( postType => excludedPostTypes.has( postType ) );

				if ( disabled ) {
					if ( ! excludedPtTaxonomies.has( taxonomy ) ) {
						// Newly disabled, trigger change.
						triggerchange = true;
					}
					// Filter it out to prevent duplicates. Redundant?
					excludedPtTaxonomies.add( taxonomy );
				} else {
					if ( excludedPtTaxonomies.has( taxonomy ) ) {
						excludedPtTaxonomies.delete( taxonomy );
						// Enabled again, was disabled. Trigger change.
						triggerchange = true;
					}
				}
				// TODO Collect and combine changes, to condense paint stack (perceptive performance, reduce race condition changes)?
				triggerchange && triggerTaxonomyChange( taxonomy );
			} );
		}
		const validatePostTypes = ( event, postType ) => {
			augmentPTRobots( postType, excludedPostTypes.has( postType ) );
			validateTaxonomyState();
		}
		let validateTaxonomiesCache = new Map();
		const getValidateTaxonomiesCache = key => validateTaxonomiesCache.get( key ) || (new Set());
		// TODO trigger new events here, to make it easier to work with for others?
		const validateTaxonomies = ( event, taxonomy ) => {

			// Only check length--should be good enough (unless we face race conditions, which we can sort out elsewhere.)
			if ( getValidateTaxonomiesCache( 'excludedTaxonomiesAll' ).size !== excludedTaxonomiesAll.size ) {
				augmentTaxonomyRobots( taxonomy, excludedTaxonomiesAll.has( taxonomy ) );
			}

			// Don't place these in the if-statement above (which is mutually inclusive)--these are mutually exclusive.
			// if ( getValidateTaxonomiesCache( 'excludedTaxonomies' ).size !== excludedTaxonomies.size ) {
				// excludedTaxonomies.has( taxonomy )
			// }
			if ( getValidateTaxonomiesCache( 'excludedPtTaxonomies' ).size !== excludedPtTaxonomies.size ) {
				addTaxDisabledByPtWarning( taxonomy, excludedPtTaxonomies.has( taxonomy ) );
			}

			// Create new pointers in the memory by shadowcloning the object.
			validateTaxonomiesCache.set( 'excludedTaxonomiesAll', new Set( excludedTaxonomiesAll ) );
			validateTaxonomiesCache.set( 'excludedTaxonomies', new Set( excludedTaxonomies ) );
			validateTaxonomiesCache.set( 'excludedPtTaxonomies', new Set( excludedPtTaxonomies ) );
		}
		$window.on( 'tsf-post-type-support-changed', validatePostTypes );
		$window.on( 'tsf-taxonomy-support-changed', validateTaxonomies );

		const triggerTaxonomyChange = taxonomy => {
			// Refresh and concatenate.
			excludedTaxonomiesAll.clear();
			excludedTaxonomies.forEach( tax => excludedTaxonomiesAll.add( tax ) );
			excludedPtTaxonomies.forEach( tax => excludedTaxonomiesAll.add( tax ) );

			$window.trigger( 'tsf-taxonomy-support-changed', [ taxonomy, excludedTaxonomiesAll, excludedTaxonomies, excludedPtTaxonomies ] );
		}

		// This prevents notice-removal checks before they're added.
		let init = false;
		const checkDisabledPT = event => {

			if ( ! event.target.name ) return;

			// get post type from last [] entry.
			let postType = event.target.name.split( /(?:.+\[)(.+?)(?:])/ ).join( '' );
			if ( event.target.checked ) {
				excludedPostTypes.add( postType );
				$window.trigger( 'tsf-post-type-support-changed', [ postType, excludedPostTypes ] );
			} else {
				// No need to filter when it was never registered in the first place.
				if ( init ) {
					excludedPostTypes.delete( postType );
					$window.trigger( 'tsf-post-type-support-changed', [ postType, excludedPostTypes ] );
				}
			}
		}
		const checkDisabledTaxonomy = event => {

			if ( ! event.target.name ) return;

			// get taxonomy from last [] entry.
			let taxonomy = event.target.name.split( /(?:.+\[)(.+?)(?:])/ ).join( '' );
			if ( event.target.checked ) {
				excludedTaxonomies.add( taxonomy );
				triggerTaxonomyChange( taxonomy );
			} else {
				// No need to filter when it was never registered in the first place.
				if ( init ) {
					excludedTaxonomies.delete( taxonomy );
					triggerTaxonomyChange( taxonomy );
				}
			}
		}
		const changeEvent = new Event( 'change' );
		document.querySelectorAll( '.tsf-excluded-post-types' ).forEach( el => {
			el.addEventListener( 'change', checkDisabledPT );
			el.dispatchEvent( changeEvent );
		} );
		document.querySelectorAll( '.tsf-excluded-taxonomies' ).forEach( el => {
			el.addEventListener( 'change', checkDisabledTaxonomy );
			el.dispatchEvent( changeEvent );
		} );

		init = true;
	}

	/**
	 * Enables wpColorPicker on input.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	const _initColorPicker = () => {

		document.querySelectorAll( '.tsf-color-picker' ).forEach( element => {
			// We might as well switch to jQuery instantly since wpColorPicker added its prototype to it.
			let $input       = $( element ),
				currentColor = '',
				defaultColor = $input.data( 'tsf-default-color' );

			$input.wpColorPicker( {
				defaultColor: defaultColor,
				width: 238,
				change: ( event, ui ) => {
					currentColor = $input.wpColorPicker( 'color' );

					if ( '' === currentColor )
						currentColor = defaultColor;

					element.value = defaultColor;

					tsfAys.registerChange();
				},
				clear: () => {
					// We can't loop this to the change method, as it's not reliable (due to deferring?).
					// So, we just fill it in for the user.
					if ( defaultColor.length ) {
						element.value = defaultColor;
						$input.closest( '.wp-picker-container' ).find( '.wp-color-result' ).css( 'backgroundColor', defaultColor );
					}
					tsfAys.registerChange();
				},
				palettes: false,
			} );
		} );
	}

	/**
	 * Initializes Titles' meta input.
	 *
	 * @since 4.0.0
	 * @since 4.0.5 Fixed the additionsToggle getter.
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initTitleSettings = () => {

		const additionsToggle            = document.getElementById( _getSettingsId( 'title_rem_additions' ) ),
			  socialAdditionsToggle      = document.getElementById( _getSettingsId( 'social_title_rem_additions' ) ),
			  titleAdditionsHelpTemplate = wp.template( 'tsf-disabled-title-additions-help' )();

		/**
		 * Toggles example on Left/Right selection of global title options.
		 *
		 * @function
		 * @return {undefined}
		 */
		const toggleAdditionsDisplayExample = () => {
			if ( additionsToggle.checked ) {
				document.querySelectorAll( '.tsf-title-additions-js' ).forEach( el => el.style.display = 'none' );
				if ( socialAdditionsToggle ) {
					socialAdditionsToggle.dataset.disabledWarning = 1;
					$( socialAdditionsToggle.closest( 'label' ) ).append( titleAdditionsHelpTemplate );
					tsfTT.triggerReset();
				}
			} else {
				document.querySelectorAll( '.tsf-title-additions-js' ).forEach( el => el.style.display = 'inline' );
				// 'tsf-title-additions-warning' is defined at `../inc/views/templates/settings/settings.php`
				if ( socialAdditionsToggle && socialAdditionsToggle.dataset.disabledWarning ) {
					socialAdditionsToggle.closest( 'label' ).querySelector( '.tsf-title-additions-warning' ).remove();
				}
			}
		}
		if ( additionsToggle ) {
			additionsToggle.addEventListener( 'change', toggleAdditionsDisplayExample );
			additionsToggle.dispatchEvent( new Event( 'change' ) );
		}

		/**
		 * Toggles title additions location for the Title examples.
		 * There are two elements, rather than one. One is hidden by default.
		 *
		 * @function
		 * @param {Event} event
		 * @return {undefined}
		 */
		const toggleAdditionsLocationExample = event => {
			let showLeft = 'left' === event.target.value;

			document.querySelectorAll( '.tsf-title-additions-example-left' ).forEach( el => {
				el.style.display = showLeft ? 'inline' : 'none';
			} );
			document.querySelectorAll( '.tsf-title-additions-example-right' ).forEach( el => {
				el.style.display = showLeft ? 'none' : 'inline';
			} );
		}
		document.querySelectorAll( '#tsf-title-location input' ).forEach( el => {
			el.addEventListener( 'click', toggleAdditionsLocationExample );
		} );

		/**
		 * Toggles title prefixes for the Prefix Title example.
		 *
		 * @function
		 * @param {Event} event
		 * @return {undefined}
		 */
		const adjustPrefixExample = event => {
			document.querySelectorAll( '.tsf-title-prefix-example' ).forEach( el => {
				// Checked = hide.
				el.style.display = event.target.checked ? 'none' : 'inline';
			} );
		}
		const archivePrefixInput = document.getElementById( _getSettingsId( 'title_rem_prefixes' ) );
		if ( archivePrefixInput ) {
			archivePrefixInput.addEventListener( 'click', adjustPrefixExample );
		}

		/**
		 * Updates used separator and all examples thereof.
		 *
		 * @function
		 * @param {Event} event
		 * @return {undefined}
		 */
		const updateSeparator = event => {
			const separator   = tsf.decodeEntities( event.target.dataset.entity ),
				  activeClass = 'tsf-title-separator-active';

			document.querySelectorAll( '.tsf-sep-js' ).forEach( el => {
				el.innerText = ` ${separator} `;
			} );

			window.dispatchEvent(
				new CustomEvent(
					'tsf-title-sep-updated',
					{
						detail: { separator }
					}
				)
			);

			let oldActiveLabel = document.querySelector( `.${activeClass}` );
			oldActiveLabel && oldActiveLabel.classList.remove( activeClass, 'tsf-no-focus-ring' );

			let activeLabel = document.querySelector( `label[for="${event.target.id}"]` );
			activeLabel && activeLabel.classList.add( activeClass );
		}
		document.querySelectorAll( '#tsf-title-separator input' ).forEach( el => {
			el.addEventListener( 'click', updateSeparator );
		} );

		/**
		 * Sets a class to the active element which helps excluding focus rings.
		 *
		 * @function
		 * @param {Event} event
		 * @return {(undefined|null)}
		 */
		const addNoFocusClass = event => {
			event.target.classList.add( 'tsf-no-focus-ring' );
		}
		document.querySelectorAll( '#tsf-title-separator label' ).forEach( el => {
			el.addEventListener( 'click', addNoFocusClass );
		} );
	}

	/**
	 * Initializes Homepage's meta title input.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initHomeTitleSettings = () => {

		const titleId = _getSettingsId( 'homepage_title' );

		const
			titleInput    = document.getElementById( titleId ),
			taglineInput  = document.getElementById( _getSettingsId( 'homepage_title_tagline' ) ),
			taglineToggle = document.getElementById( _getSettingsId( 'homepage_tagline' ) );

		tsfTitle.setInputElement( titleInput );

		let state = JSON.parse(
			document.getElementById( 'tsf-title-data_' + titleId ).dataset.state
		);

		tsfTitle.updateStateOf( titleId, 'allowReferenceChange', ! state.refTitleLocked );
		tsfTitle.updateStateOf( titleId, 'defaultTitle', state.defaultTitle.trim() );
		tsfTitle.updateStateOf( titleId, 'addAdditions', state.addAdditions );
		tsfTitle.updateStateOf( titleId, 'useSocialTagline', !! ( state.useSocialTagline || false ) );
		tsfTitle.updateStateOf( titleId, 'additionValue', state.additionValue.trim() );
		tsfTitle.updateStateOf( titleId, 'additionPlacement', state.additionPlacement );
		tsfTitle.updateStateOf( titleId, 'hasLegacy', !! ( state.hasLegacy || false ) );

		tsfTitle.enqueueUnregisteredInputTrigger( titleId );

		/**
		 * Updates the hover additions placement.
		 *
		 * @since 4.1.1
		 *
		 * @function
		 * @return {undefined}
		 */
		const toggleHoverAdditionsPlacement = event => {
			let oldPlacement = tsfTitle.getStateOf( titleId, 'additionPlacement' ),
				newPlacement = 'left' === event.target.value ? 'before' : 'after';

			if ( newPlacement !== oldPlacement ) {
				tsfTitle.updateStateOf( titleId, 'additionPlacement', newPlacement );
			}
		}
		document.querySelectorAll( '#tsf-home-title-location input' ).forEach( el => {
			el.addEventListener( 'click', toggleHoverAdditionsPlacement );
		} );

		/**
		 * Sets private/protected visibility state.
		 *
		 * @function
		 * @param {string} visibility
		 * @return {undefined}
		 */
		const setTitleVisibilityPrefix = ( visibility ) => {

			let oldPrefixValue = tsfTitle.getStateOf( titleId, 'prefixValue' ),
				prefixValue    = '';

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
		if ( l10n.states.isFrontPrivate ) {
			setTitleVisibilityPrefix( 'private' );
		} else if ( l10n.states.isFrontProtected ) {
			setTitleVisibilityPrefix( 'password' );
		}

		/**
		 * Adjusts homepage left/right title example part.
		 *
		 * @function
		 * @param {Event} event
		 * @return {undefined}
		 */
		const adjustHomepageExampleOutput = event => {
			let examples = document.querySelectorAll( '.tsf-custom-title-js' ),
				val      = tsf.decodeEntities( tsf.sDoubleSpace( event.target.value.trim() ) );

			if ( val.length ) {
				val = tsf.escapeString( val );
				examples.forEach( el => el.innerHTML = val );
			} else {
				val = tsf.escapeString( tsf.decodeEntities( tsfTitle.getStateOf( titleId, 'defaultTitle' ) ) );
				examples.forEach( el => el.innerHTML = val );
			}
		};
		titleInput.addEventListener( 'input', adjustHomepageExampleOutput );
		titleInput.dispatchEvent( new Event( 'input' ) );

		let updateHomePageTaglineExampleOutputBuffer,
			$exampleTagline = $( '.tsf-custom-tagline-js' );
		/**
		 * Updates homepage title example output.
		 * Has high debounce timer, as it's crucially visible on the input screen anyway.
		 *
		 * @function
		 * @return {undefined}
		 */
		const updateHomePageTaglineExampleOutput = () => {

			clearTimeout( updateHomePageTaglineExampleOutputBuffer );

			updateHomePageTaglineExampleOutputBuffer = setTimeout( () => {
				let value = tsfTitle.getStateOf( titleId, 'additionValue' );

				value = tsf.decodeEntities( tsf.sDoubleSpace( value.trim() ) );

				if ( value.length && tsfTitle.getStateOf( titleId, 'addAdditions' ) ) {
					$exampleTagline.html( tsf.escapeString( value ) );
					$( '.tsf-custom-blogname-js' ).show();
				} else {
					$( '.tsf-custom-blogname-js' ).hide();
				}
			} );
		}

		/**
		 * Updates the hover additions value.
		 *
		 * @function
		 * @return {undefined}
		 */
		const updateHoverAdditionsValue = () => {
			let oldValue = tsfTitle.getStateOf( titleId, 'additionValue' ),
				value    = taglineInput.value.trim();

			if ( ! value.length ) {
				value = taglineInput.placeholder || '';
			}

			value = tsf.escapeString( tsf.decodeEntities( value.trim() ) );

			if ( oldValue !== value ) {
				tsfTitle.updateStateOf( titleId, 'additionValue', value );
				updateHomePageTaglineExampleOutput();
			}
		}
		taglineInput.addEventListener( 'input', updateHoverAdditionsValue );
		taglineInput.dispatchEvent( new Event( 'input' ) );

		/**
		 * Toggle tagline end examples within the Left/Right example for the homepage titles.
		 * Also disables the input field for extra clarity.
		 *
		 * @function
		 * @param {Event} event
		 * @return {undefined}
		 */
		const toggleHomePageTaglineExampleDisplay = event => {
			let addAdditions = false;

			if ( event.target.checked ) {
				addAdditions            = true;
				taglineInput.readOnly = false;
			} else {
				addAdditions            = false;
				taglineInput.readOnly = true;
			}

			// A change action implies a change. Don't test for previous; it changed!
			// (also, it defaults to false; which would cause a bug not calling updateHomePageTaglineExampleOutput on-load)
			tsfTitle.updateStateOf( titleId, 'addAdditions', addAdditions );
			updateHomePageTaglineExampleOutput();
		}
		taglineToggle.addEventListener( 'change', toggleHomePageTaglineExampleDisplay );
		taglineToggle.dispatchEvent( new Event( 'change' ) );

		/**
		 * Updates separator used in the titles.
		 *
		 * @function
		 * @param {Event} event
		 * @return {undefined}
		 */
		const updateSeparator = event => {
			tsfTitle.updateStateAll( 'separator', event.detail.separator );
		}
		window.addEventListener( 'tsf-title-sep-updated', updateSeparator );
	}

	/**
	 * Initializes Homepage's meta description input.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initHomeDescriptionSettings = () => {

		const descId = _getSettingsId( 'homepage_description' );

		let state = JSON.parse(
			document.getElementById( 'tsf-description-data_' + descId ).dataset.state
		);

		tsfDescription.setInputElement( document.getElementById( descId ) );

		// tsfDescription.updateState( 'allowReferenceChange', ! state.refDescriptionLocked );
		tsfDescription.updateStateOf( descId, 'defaultDescription', state.defaultDescription.trim() );
		tsfDescription.updateStateOf( descId, 'hasLegacy', !! ( state.hasLegacy || false ) );

		tsfDescription.enqueueUnregisteredInputTrigger( descId );
	}

	/**
	 * Initializes uncategorized general tab meta input listeners.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initHomeGeneralListeners = () => {

		/**
		 * Enqueues meta title and description input triggers
		 *
		 * @function
		 * @return {undefined}
		 */
		const enqueueGeneralInputListeners = () => {
			tsfTitle.enqueueUnregisteredInputTrigger( _getSettingsId( 'homepage_title' ) );
			tsfDescription.enqueueUnregisteredInputTrigger( _getSettingsId( 'homepage_description' ) );
		}

		/**
		 * Enqueues doctitles input trigger synchronously on postbox collapse or open.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @param {Element}       elem
		 * @return {undefined}
		 */
		const triggerPostboxSynchronousUnregisteredInput = function( event, elem ) {
			if ( 'autodescription-homepage-settings' === elem.id ) {
				let inside = elem.querySelector( '.inside' );
				if ( inside.offsetHeight > 0 && inside.offsetWidth > 0 ) {
					enqueueGeneralInputListeners();
				}
			}
		}
		$( document ).on( 'postbox-toggled', triggerPostboxSynchronousUnregisteredInput );

		// This also triggers change for the homepage description, which isn't necessary. But, this trims down codebase.
		const homepageGeneralTab = document.getElementById( 'tsf-homepage-tab-general' );
		homepageGeneralTab && homepageGeneralTab.addEventListener( 'tsf-tab-toggled', enqueueGeneralInputListeners );
	}

	/**
	 * Initializes Social meta input.
	 *
	 * @since 4.1.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initSocialSettings = () => {

		const socialAdditionsToggle = document.getElementById( _getSettingsId( 'social_title_rem_additions' ) );

		/**
		 * Changes the useSocialTagline state for dynamic social-title-placeholder updates.
		 *
		 * @function
		 * @param {Event} event
		 * @return {undefined}
		 */
		const updateSocialAdditions = event => {
			if ( event.target.checked ) {
				tsfTitle.updateStateAll( 'useSocialTagline', false );
			} else {
				tsfTitle.updateStateAll( 'useSocialTagline', true );
			}
		}
		socialAdditionsToggle && socialAdditionsToggle.addEventListener( 'change', updateSocialAdditions );
	}

	/**
	 * Initializes Robots' meta input.
	 *
	 * @since 4.0.2
	 * @since 4.1.1 Now adds taxonomy warnings.
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initRobotsInputs = () => {

		const copyrightToggle = document.getElementById( _getSettingsId( 'set_copyright_directives' ) );

		if ( copyrightToggle ) {
			const controlNodes = [
				"max_snippet_length",
				"max_image_preview",
				"max_video_preview",
			].map( name => document.getElementById( _getSettingsId( name ) ) );

			const surrogateClass = 'tsf-toggle-directives-surrogate';
			/**
			 * Toggles copyright directive option states.
			 *
			 * @function
			 * @param {Event} event
			 * @return {undefined}
			 */
			const toggleCopyrightControl = event => {
				if ( event.target.checked ) {
					controlNodes.forEach( el => el.disabled = false );
					document.querySelectorAll( `.${surrogateClass}` ).forEach( el => el.remove() );
				} else {
					controlNodes.forEach( el => {
						el.disabled = true;
						let surrogate = document.createElement( 'input' );
						surrogate.type = 'hidden';
						surrogate.name = el.name || '';
						surrogate.value = el.value || 0;
						surrogate.classList.add( surrogateClass );
						el.insertAdjacentElement( 'afterend', surrogate );
					} );
				}
			}
			copyrightToggle.addEventListener( 'change', toggleCopyrightControl );
			copyrightToggle.dispatchEvent( new Event( 'change' ) );
		}

		const $window            = $( window );
		const postTypeRobotsHelp = wp.template( 'tsf-robots-pt-help' )();

		const robotsPostTypes     = {},
			  robotsPtTaxonomies  = {};
		[ robotsPostTypes, robotsPtTaxonomies ].forEach( _const => {
			_const.noindex   = new Set();
			_const.nofollow  = new Set();
			_const.noarchive = new Set();
		} );
		const addTaxRobotsByPtWarning = ( taxonomy, robotsType, disable ) => {
			// Yes, stacked template literals. Sue me :)
			let taxEl = document.getElementById( `${ _getSettingsId( `${robotsType}_taxonomies` ) }[${taxonomy}]` );
			if ( disable ) {
				$( taxEl.closest( 'label' ) ).append( postTypeRobotsHelp );
				tsfTT.triggerReset();
			} else {
				// 'tsf-taxonomy-from-pt-robots-warning' is defined at `../inc/views/templates/settings/settings.php`
				taxEl.closest( 'label' ).querySelector( '.tsf-taxonomy-from-pt-robots-warning' ).remove();
			}

			toggleWarnings( taxonomy );
		}

		const validateTaxonomyState = robotsType => {
			// We want to show that the taxonomy is de-robotsTyped, but make that auto-reversible, and somehow still enactable?

			let taxEntries    = document.querySelectorAll( `.tsf-robots-taxonomies[data-robots="${robotsType}"]` ),
				triggerchange = false;

			taxEntries.forEach( element => {
				// get taxonomy from last [] entry.
				let taxonomy = element.name.split( /(?:.+\[)(.+?)(?:])/ ).join( '' );

				let taxPostTypes = JSON.parse( element.dataset.postTypes ),
					hasRobots    = taxPostTypes.every( postType => robotsPostTypes[ robotsType ].has( postType ) );

				if ( hasRobots ) {
					if ( ! robotsPtTaxonomies[ robotsType ].has( taxonomy ) ) {
						// Newly disabled, trigger change.
						triggerchange = true;
					}
					// Filter it out to prevent duplicates. Redundant?
					robotsPtTaxonomies[ robotsType ].add( taxonomy );
				} else {
					if ( robotsPtTaxonomies[ robotsType ].has( taxonomy ) ) {
						robotsPtTaxonomies[ robotsType ].delete( taxonomy );
						// Enabled again, was disabled. Trigger change.
						triggerchange = true;
					}
				}
				// TODO Collect and combine changes, to condense paint stack (perceptive performance, reduce race condition changes)?
				triggerchange && triggerTaxonomyChange( taxonomy, robotsType );
			} );
		}
		const validatePostTypes = ( event, postType, robotsType ) => {
			// augmentPTRobots( postType, robotsType. robotsPostTypes[ robotsType ].has( postType ) );
			validateTaxonomyState( robotsType );
		}
		let validateTaxonomiesCache = {
			noindex:   new Map(),
			nofollow:  new Map(),
			noarchive: new Map(),
		};
		const getValidateTaxonomiesCache = ( key, robotsType ) => validateTaxonomiesCache[ robotsType ].get( key ) || ( new Set() );
		// TODO trigger new events here, to make it easier to work with for others?
		const validateTaxonomies = ( event, taxonomy, robotsType ) => {
			if ( getValidateTaxonomiesCache( 'robotsPtTaxonomies', robotsType ).size !== robotsPtTaxonomies[ robotsType ].size ) {
				addTaxRobotsByPtWarning( taxonomy, robotsType, robotsPtTaxonomies[ robotsType ].has( taxonomy ) );
			}

			// Create new pointers in the memory by shadowcloning the object.
			validateTaxonomiesCache[ robotsType ].set( 'robotsPtTaxonomies', new Set( robotsPtTaxonomies[ robotsType ] ) );
		}
		$window.on( 'tsf-post-type-robots-changed', validatePostTypes );
		$window.on( 'tsf-taxonomy-robots-changed', validateTaxonomies );

		const triggerTaxonomyChange = ( taxonomy, robotsType ) => {
			$window.trigger( 'tsf-taxonomy-robots-changed', [
				taxonomy,
				robotsType,
				robotsPtTaxonomies[ robotsType ]
			] );
		}

		/**
		 * Add exclusions support by removing duplicated warnings.
		 * @param {string} taxonomy
		 */
		const toggleWarnings = taxonomy => {
			for ( let robotsType in robotsPtTaxonomies ) {
				if ( robotsPtTaxonomies[ robotsType ].has( taxonomy ) ) {
					let taxEl   = document.getElementById( `${ _getSettingsId( `${robotsType}_taxonomies` ) }[${taxonomy}]` ),
						warning = taxEl.closest( 'label' ).querySelector( '.tsf-taxonomy-from-pt-robots-warning' );

					if ( taxEl.dataset.disabledWarning ) {
						warning.style.display = 'none';
					} else {
						warning.style.display = '';
					}
				}
			}
		}
		$window.on( 'tsf-taxonomy-support-changed', ( event, taxonomy ) => toggleWarnings( taxonomy ) );

		// This prevents notice-removal checks before they're added.
		let init = false;
		const checkRobotsPT = event => {

			if ( ! event.target.name ) return;

			// get post type from last [] entry.
			let postType   = event.target.name.split( /(?:.+\[)(.+?)(?:])/ ).join( '' ),
				robotsType = event.target.dataset.robots;
			if ( event.target.checked ) {
				robotsPostTypes[ robotsType ].add( postType );
				$window.trigger( 'tsf-post-type-robots-changed', [ postType, robotsType, robotsPostTypes[ robotsType ] ] );
			} else {
				// No need to filter when it was never registered in the first place.
				if ( init ) {
					robotsPostTypes[ robotsType ].delete( postType );
					$window.trigger( 'tsf-post-type-robots-changed', [ postType, robotsType, robotsPostTypes[ robotsType ] ] );
				}
			}
		}
		const changeEvent = new Event( 'change' );
		document.querySelectorAll( '.tsf-robots-post-types' ).forEach( el => {
			el.addEventListener( 'change', checkRobotsPT );
			el.dispatchEvent( changeEvent );
		} );

		init = true;
	}

	/**
	 * Initializes Webmasters' meta input.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initWebmastersInputs = () => {

		const webmasterNodes = [
			"google_verification",
			"bing_verification",
			"yandex_verification",
			"baidu_verification",
			"pint_verification",
		].map( name => document.getElementById( _getSettingsId( name ) ) );

		/**
		 * @function
		 * @param {Event} event
		 * @return {undefined}
		 */
		const trimScript = event => {
			let val = event.clipboardData && event.clipboardData.getData( 'text' ) || '';

			if ( val ) {
				// Extrude tag paste's content value and set that as a value.
				let match = /<meta[^>]+content=(\"|\')?([^\"\'>\s]+)\1?.*?>/i.exec( val );
				if ( match && 2 in match && 'string' === typeof match[2] && match[2].length ) {
					event.stopPropagation();
					event.preventDefault(); // Prevents save listener
					event.target.value = match[2];
					// Tell change:
					tsfAys.registerChange();
				}
			}
		}
		webmasterNodes.forEach( el => el.addEventListener( 'paste', trimScript ) );
	}

	/**
	 * Initializes settings scripts on TSF-load.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _loadSettings = () => {
		_initGeneralSettings();
		_initTitleSettings();

		_initHomeTitleSettings();
		_initHomeDescriptionSettings();
		_initHomeGeneralListeners();

		_initSocialSettings();

		_initRobotsInputs();
		_initWebmastersInputs();
		_initColorPicker();
	}

	/**
	 * Initializes settings scripts on TSF-ready.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now registers the refNa title input.
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _readySettings = () => {

		const titleId = _getSettingsId( 'homepage_title' ),
			  descId  = _getSettingsId( 'homepage_description' );

		tsfSocial.initTitleInputs( {
			ref:   document.getElementById( 'tsf-title-reference_' + titleId ),
			refNa: document.getElementById( 'tsf-title-noadditions-reference_' + titleId ),
			meta:  document.getElementById( titleId ),
			og:    document.getElementById( _getSettingsId( 'homepage_og_title' ) ),
			tw:    document.getElementById( _getSettingsId( 'homepage_twitter_title' ) ),
		} );

		tsfSocial.initDescriptionInputs( {
			ref:  document.getElementById( 'tsf-description-reference_' + descId ),
			meta: document.getElementById( descId ),
			og:   document.getElementById( _getSettingsId( 'homepage_og_description' ) ),
			tw:   document.getElementById( _getSettingsId( 'homepage_twitter_description' ) ),
		} );
	}

	/**
	 * Sets a class to the active element which helps excluding focus rings.
	 *
	 * @since 4.0.0
	 * @since 4.1.3 Now offloaded to tsfTabs.
	 * @access private
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	const _initTabs = () => {
		tsfTabs.initStack(
			'tsfSettings',
			{
				tabToggledEvent: new CustomEvent( 'tsf-tab-toggled' ),
				HTMLClasses:     {
					wrapper:          'tsf-nav-tab-wrapper',
					tabRadio:         'tsf-tabs-radio', // bad name
					tabLabel:         'tsf-nav-tab',
					activeTab:        'tsf-tab-active',
					// TODO make this tsf-tab-active-content (force -content affix?)
					activeTabContent: 'tsf-active-tab-content',
				},
				fixHistory:      true, // false for flex? Doesn't seem like it was?
			}
		);
	}

	return Object.assign( {
		/**
		 * Initialises all aspects of the scripts.
		 * You shouldn't call this.
		 *
		 * @since 4.0.0
		 * @since 4.0.3 Now also displaces notice-info.
		 * @access protected
		 *
		 * @function
		 * @return {undefined}
		 */
		load: () => {
			// Execute this ASAP, to prevent late layout shifting. Use same anchor as core--so to prevent subsequent movement.
			$( 'div.updated, div.error, div.notice, .notice-error, .notice-warning, .notice-info' ).insertAfter( '.wp-header-end' );

			document.body.addEventListener( 'tsf-onload', _loadSettings );
			document.body.addEventListener( 'tsf-ready', _readySettings );

			// Initializes tabs early; we rely a fallback event that tsf-onload/tsf-ready uses there.
			_initTabs();
		}
	}, {}, {
		l10n
	} );
}( jQuery );
window.tsfSettings.load();
