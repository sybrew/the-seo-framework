/**
 * This file holds The SEO Framework plugin's JS code for the SEO Settings page.
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
 * Holds tsfSettings values in an object to avoid polluting global namespace.
 *
 * @since 4.0.0
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
	 * Initializes input helpers for the General Settings.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	const _initGeneralSettings = () => {
		/**
		 * Triggers displaying/hiding of character counters on the settings page.
		 *
		 * @since 4.0.0
		 * @access private
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const togglePixelCounterDisplay = ( event ) => {
			if ( $( event.target ).is( ':checked' ) ) {
				$( '.tsf-pixel-counter-wrap' ).show();
				//= Pixels couldn't be counted when it was hidden.
				tsfC.triggerCounterUpdate();
			} else {
				$( '.tsf-pixel-counter-wrap' ).hide();
			}
		}
		$( '#autodescription-site-settings\\[display_character_counter\\]' ).on( 'click', togglePixelCounterDisplay );
		$( '#autodescription-site-settings\\[display_pixel_counter\\]' ).on( 'click', togglePixelCounterDisplay );

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
		const getPostTypeRobotsSettings = ( postType ) => [
			document.getElementById( 'autodescription-site-settings[noindex_post_types][' + postType + ']' ),
			document.getElementById( 'autodescription-site-settings[nofollow_post_types][' + postType + ']' ),
			document.getElementById( 'autodescription-site-settings[noarchive_post_types][' + postType + ']' ),
		].filter( el => el );
		/**
		 * @param {string} taxonomy
		 * @return {array} A list of affected post type settings.
		 */
		const getTaxonomyRobotsSettings = ( taxonomy ) => [
			document.getElementById( 'autodescription-site-settings[noindex_taxonomies][' + taxonomy + ']' ),
			document.getElementById( 'autodescription-site-settings[nofollow_taxonomies][' + taxonomy + ']' ),
			document.getElementById( 'autodescription-site-settings[noarchive_taxonomies][' + taxonomy + ']' ),
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

					element.disabled           = true;
					element.dataset.hasWarning = 1;

					$( element.closest( 'label' ) ).append( postTypeHelpTemplate ).append( clone );
				} );

				tsfTT.triggerReset();
			} else {
				getPostTypeRobotsSettings( postType ).forEach( element => {
					if ( ! element ) return;
					if ( ! element.dataset.hasWarning ) return;

					// 'tsf-post-type-warning' is defined at `../inc/views/templates/settings/settings.php`
					element.closest( 'label' ).querySelector( '.tsf-post-type-warning' ).remove();

					document.querySelectorAll( '.' + getCloneClassPT( postType ) ).forEach( ( clone ) => {
						clone.remove();
					} );

					element.disabled           = false;
					element.dataset.hasWarning = '';
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

					element.disabled           = true;
					element.dataset.hasWarning = 1;

					$( element.closest( 'label' ) ).append( taxonomyHelpTemplate ).append( clone );
				} );

				tsfTT.triggerReset();
			} else {
				getTaxonomyRobotsSettings( taxonomy ).forEach( element => {
					if ( ! element ) return;
					if ( ! element.dataset.hasWarning ) return;

					// 'tsf-taxonomy-warning' is defined at `../inc/views/templates/settings/settings.php`
					element.closest( 'label' ).querySelector( '.tsf-taxonomy-warning' ).remove();

					document.querySelectorAll( '.' + getCloneClassTaxonomy( taxonomy ) ).forEach( ( clone ) => {
						clone.remove();
					} );

					element.disabled           = false;
					element.dataset.hasWarning = '';
				} );
			}
		}

		const addTaxDisabledByPtWarning = ( taxonomy, disable ) => {
			let taxEl = document.getElementById( 'autodescription-site-settings[disabled_taxonomies][' + taxonomy + ']' );
			if ( disable ) {
				$( taxEl.closest( 'label' ) ).append( taxonomyPtHelpTemplate );
				tsfTT.triggerReset();
			} else {
				// 'tsf-taxonomy-from-pt-warning' is defined at `../inc/views/templates/settings/settings.php`
				taxEl.closest( 'label' ).querySelector( '.tsf-taxonomy-from-pt-warning' ).remove();
			}
		}

		let disabledPostTypes     = new Set(),
			disabledTaxonomies    = new Set(),
			disabledTaxonomiesAll = new Set(),
			disabledPtTaxonomies  = new Set();
		const validateTaxonomyState = () => {
			// We want to show that the taxonomy is disabled, but make that auto-reversible, and somehow still enactable?

			let taxEntries    = document.querySelectorAll( '.tsf-disabled-taxonomies' ),
				triggerchange = false;

			taxEntries.forEach( element => {
				// get taxonomy from last [] entry.
				let taxonomy = element.name.split( /(?:.+\[)(.+?)(?:])/ ).join( '' );

				let taxPostTypes = JSON.parse( element.dataset.postTypes ),
					disabled     = taxPostTypes.every( postType => disabledPostTypes.has( postType ) );

				if ( disabled ) {
					if ( ! disabledPtTaxonomies.has( taxonomy ) ) {
						// Newly disabled, trigger change.
						triggerchange = true;
					}
					// Filter it out to prevent duplicates. Redundant?
					disabledPtTaxonomies.add( taxonomy );
				} else {
					if ( disabledPtTaxonomies.has( taxonomy ) ) {
						disabledPtTaxonomies.delete( taxonomy );
						// Enabled again, was disabled. Trigger change.
						triggerchange = true;
					}
				}
				// TODO Collect and combine changes, to condense paint stack (perceptive performance, reduce race condition changes)?
				triggerchange && triggerTaxonomyChange( taxonomy );
			} );
		}
		const validatePostTypes = ( event, postType ) => {
			augmentPTRobots( postType, disabledPostTypes.has( postType ) );
			validateTaxonomyState();
		}
		let validateTaxonomiesCache = new Map();
		const getValidateTaxonomiesCache = key => validateTaxonomiesCache.get( key ) || (new Set());
		// TODO trigger new events here, to make it easier to work with for others?
		const validateTaxonomies = ( event, taxonomy ) => {

			// Only check length--should be good enough (unless we face race conditions, which we can sort out elsewhere.)
			if ( getValidateTaxonomiesCache( 'disabledTaxonomiesAll' ).size !== disabledTaxonomiesAll.size ) {
				augmentTaxonomyRobots( taxonomy, disabledTaxonomiesAll.has( taxonomy ) );
			}

			// Don't place these in the if-statement above (which is mutually inclusive)--these are mutually exclusive.
			// if ( getValidateTaxonomiesCache( 'disabledTaxonomies' ).size !== disabledTaxonomies.size ) {
				// disabledTaxonomies.has( taxonomy )
			// }
			if ( getValidateTaxonomiesCache( 'disabledPtTaxonomies' ).size !== disabledPtTaxonomies.size ) {
				addTaxDisabledByPtWarning( taxonomy, disabledPtTaxonomies.has( taxonomy ) );
			}

			// Create new pointers in the memory by shadowcloning the object.
			validateTaxonomiesCache.set( 'disabledTaxonomiesAll', new Set( disabledTaxonomiesAll ) );
			validateTaxonomiesCache.set( 'disabledTaxonomies', new Set( disabledTaxonomies ) );
			validateTaxonomiesCache.set( 'disabledPtTaxonomies', new Set( disabledPtTaxonomies ) );
		}
		$( window ).on( 'tsf-post-type-support-changed', validatePostTypes );
		$( window ).on( 'tsf-taxonomy-support-changed', validateTaxonomies );

		const triggerTaxonomyChange = ( taxonomy ) => {
			// Refresh and concatenate.
			disabledTaxonomiesAll.clear();
			disabledTaxonomies.forEach( tax => disabledTaxonomiesAll.add( tax ) );
			disabledPtTaxonomies.forEach( tax => disabledTaxonomiesAll.add( tax ) );

			$( window ).trigger( 'tsf-taxonomy-support-changed', [ taxonomy, disabledTaxonomiesAll, disabledTaxonomies, disabledPtTaxonomies ] );
		}

		// This prevents notice-removal checks before they're added.
		let init = false;
		const checkDisabledPT = ( event ) => {

			if ( ! event.target.name ) return;

			// get post type from last [] entry.
			let postType = event.target.name.split( /(?:.+\[)(.+?)(?:])/ ).join( '' );
			if ( $( event.target ).is( ':checked' ) ) {
				disabledPostTypes.add( postType );
				$( window ).trigger( 'tsf-post-type-support-changed', [ postType, disabledPostTypes ] );
			} else {
				// No need to filter when it was never registered in the first place.
				if ( init ) {
					disabledPostTypes.delete( postType );
					$( window ).trigger( 'tsf-post-type-support-changed', [ postType, disabledPostTypes ] );
				}
			}
		}
		const checkDisabledTaxonomy = ( event ) => {

			if ( ! event.target.name ) return;

			// get taxonomy from last [] entry.
			let taxonomy = event.target.name.split( /(?:.+\[)(.+?)(?:])/ ).join( '' );
			if ( $( event.target ).is( ':checked' ) ) {
				disabledTaxonomies.add( taxonomy );
				triggerTaxonomyChange( taxonomy );
			} else {
				// No need to filter when it was never registered in the first place.
				if ( init ) {
					disabledTaxonomies.delete( taxonomy );
					triggerTaxonomyChange( taxonomy );
				}
			}
		}
		$( '.tsf-disabled-post-types' )
			.on( 'change.tsfSetPostType', checkDisabledPT )
			.trigger( 'change.tsfSetPostType' );
		$( '.tsf-disabled-taxonomies' )
			.on( 'change.tsfSetTaxonomy', checkDisabledTaxonomy )
			.trigger( 'change.tsfSetTaxonomy' );

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

		let $selectors = $( '.tsf-color-picker' );

		if ( $selectors.length ) {
			$.each( $selectors, ( index, value ) => {
				let $input = $( value ),
					currentColor = '',
					defaultColor = $input.data( 'tsf-default-color' );

				$input.wpColorPicker( {
					defaultColor: defaultColor,
					width: 238,
					change: ( event, ui ) => {
						currentColor = $input.wpColorPicker( 'color' );

						if ( '' === currentColor )
							currentColor = defaultColor;

						$input.val( currentColor );

						tsfAys.registerChange();
					},
					clear: () => {
						// We can't loop this to the change method, as it's not reliable (due to deferring?).
						// So, we just fill it in for the user.
						if ( defaultColor.length ) {
							$input.val( defaultColor );
							$input.closest( '.wp-picker-container' ).find( '.wp-color-result' ).css( 'backgroundColor', defaultColor );
						}
						tsfAys.registerChange();
					},
					palettes: false,
				} );
			} );
		}
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

		const
			additionsToggle            = document.getElementById( 'autodescription-site-settings[title_rem_additions]' ),
			socialAdditionsToggle      = document.getElementById( 'autodescription-site-settings[social_title_rem_additions]' ),
			titleAdditionsHelpTemplate = wp.template( 'tsf-disabled-title-additions-help' )();
		/**
		 * Toggles example on Left/Right selection of global title options.
		 *
		 * @function
		 * @return {undefined}
		 */
		const toggleAdditionsDisplayExample = () => {
			let $exampleAdditions = $( '.tsf-title-additions-js' );

			if ( $( additionsToggle ).is( ':checked' ) ) {
				$exampleAdditions.css( 'display', 'none' );
				if ( socialAdditionsToggle ) {
					socialAdditionsToggle.dataset.hasWarning = 1;
					$( socialAdditionsToggle.closest( 'label' ) ).append( titleAdditionsHelpTemplate );
					tsfTT.triggerReset();
				}
			} else {
				$exampleAdditions.css( 'display', 'inline' );
				// 'tsf-title-additions-warning' is defined at `../inc/views/templates/settings/settings.php`
				if ( socialAdditionsToggle && socialAdditionsToggle.dataset.hasWarning ) {
					socialAdditionsToggle.closest( 'label' ).querySelector( '.tsf-title-additions-warning' ).remove();
				}
			}
		}
		$( additionsToggle )
			.on( 'change.tsfSetAdditions', toggleAdditionsDisplayExample )
			.trigger( 'change.tsfSetAdditions' );

		/**
		 * Toggles title additions location for the Title examples.
		 * There are two elements, rather than one. One is hidden by default.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const toggleAdditionsLocationExample = event => {
			let $titleExampleLeft  = $( '.tsf-title-additions-example-left' ),
				$titleExampleRight = $( '.tsf-title-additions-example-right' );

			if ( 'right' === $( event.target ).val() ) {
				$titleExampleLeft.css( 'display', 'none' );
				$titleExampleRight.css( 'display', 'inline' );
			} else {
				$titleExampleLeft.css( 'display', 'inline' );
				$titleExampleRight.css( 'display', 'none' );
			}
		}
		$( '#tsf-title-location input' ).on( 'click', toggleAdditionsLocationExample );

		/**
		 * Toggles title prefixes for the Prefix Title example.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const adjustPrefixExample = event => {
			let $this   = $( event.target ),
				$prefix = $( '.tsf-title-prefix-example' );

			if ( $this.is(':checked') ) {
				$prefix.css( 'display', 'none' );
			} else {
				$prefix.css( 'display', 'inline' );
			}
		}
		$( '#tsf-title-prefixes-toggle :input' ).on( 'click', adjustPrefixExample );

		/**
		 * Updates used separator and all examples thereof.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const updateSeparator = event => {
			let separator   = tsf.decodeEntities( event.target.dataset.entity ),
				activeClass = 'tsf-title-separator-active';

			$( '.tsf-sep-js' ).text( ` ${separator} ` );
			$( window ).trigger( 'tsf-title-sep-updated', [ separator ] );

			let oldActiveLabel = document.querySelector( `.${activeClass}` );
			oldActiveLabel && oldActiveLabel.classList.remove( activeClass, 'tsf-no-focus-ring' );

			let activeLabel = document.querySelector( `label[for="${event.target.id}"]` );
			activeLabel && activeLabel.classList.add( activeClass );
		}
		$( '#tsf-title-separator :input' ).on( 'click', updateSeparator );

		/**
		 * Sets a class to the active element which helps excluding focus rings.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {(undefined|null)}
		 */
		const addNoFocusClass = event => {
			event.target.classList.add( 'tsf-no-focus-ring' );
		}
		$( '#tsf-title-separator label' ).on( 'click', addNoFocusClass );
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

		const titleId = 'autodescription-site-settings[homepage_title]';

		const
			titleInput    = document.getElementById( titleId ),
			taglineInput  = document.getElementById( 'autodescription-site-settings[homepage_title_tagline]' ),
			taglineToggle = document.getElementById( 'autodescription-site-settings[homepage_tagline]' );

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
		 * @since 4.0.0
		 * @since 4.0.6 No longer changes behavior depending on RTL-status.
		 *
		 * @function
		 * @return {undefined}
		 */
		const setHoverAdditionsPlacement = () => {
			let oldPlacement = tsfTitle.getStateOf( titleId, 'additionPlacement' ),
				placement    = 'after';

			if ( 'left' === $( '#tsf-home-title-location input:checked' ).val() ) {
				placement = 'before';
			}

			if ( placement !== oldPlacement ) {
				tsfTitle.updateStateOf( titleId, 'additionPlacement', placement );
			}
		}
		setHoverAdditionsPlacement();
		$( '#tsf-home-title-location' ).on( 'click', ':input', setHoverAdditionsPlacement );

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
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const adjustHomepageExampleOutput = ( event ) => {
			let $example = $( '.tsf-custom-title-js' ),
				val      = tsf.decodeEntities( tsf.sDoubleSpace( event.target.value.trim() ) );

			if ( val.length ) {
				$example.html( tsf.escapeString( val ) );
			} else {
				$example.html( tsf.escapeString( tsf.decodeEntities( tsfTitle.getStateOf( titleId, 'defaultTitle' ) ) ) );
			}
		};
		$( titleInput )
			.on( 'input.tsfInputTitle', adjustHomepageExampleOutput )
			.trigger( 'input.tsfInputTitle' );

		let updateHomePageTaglineExampleOutputBuffer,
			$exampleTagline = $( '.tsf-custom-tagline-js' );
		/**
		 * Updates homepage title example output.
		 * Has high debounce timer, as it's crucially visible on the input screen anyway.
		 *
		 * @function
		 * @param {!jQuery.Event} event
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
		$( taglineInput )
			.on( 'input.tsfInputTagline', updateHoverAdditionsValue )
			.trigger( 'input.tsfInputTagline' );

		/**
		 * Toggle tagline end examples within the Left/Right example for the homepage titles.
		 * Also disables the input field for extra clarity.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const toggleHomePageTaglineExampleDisplay = ( event ) => {
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
		$( taglineToggle )
			.on( 'change.tsfToggleTagline', toggleHomePageTaglineExampleDisplay )
			.trigger( 'change.tsfToggleTagline' );

		/**
		 * Updates separator used in the titles.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @param {string}        separator
		 * @return {undefined}
		 */
		const updateSeparator = ( event, separator ) => {
			tsfTitle.updateStateAll( 'separator', separator );
		}
		$( window ).on( 'tsf-title-sep-updated', updateSeparator );
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

		const descId = 'autodescription-site-settings[homepage_description]';

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
		 * @param {!jQuery.Event} event
		 * @param {Element} elem
		 * @return {undefined}
		 */
		const enqueueGeneralInputListeners = () => {
			tsfTitle.enqueueUnregisteredInputTrigger( 'autodescription-site-settings[homepage_title]' );
			tsfDescription.enqueueUnregisteredInputTrigger( 'autodescription-site-settings[homepage_description]' );
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
		$( '#tsf-homepage-tab-general' ).on( 'tsf-tab-toggled', enqueueGeneralInputListeners );
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

		const socialAdditionsToggle = document.getElementById( 'autodescription-site-settings[social_title_rem_additions]' );

		/**
		 * Changes the useSocialTagline state for dynamic social-title-placeholder updates.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const updateSocialAdditions = event => {
			if ( $( socialAdditionsToggle ).is( ':checked' ) ) {
				tsfTitle.updateStateAll( 'useSocialTagline', false );
			} else {
				tsfTitle.updateStateAll( 'useSocialTagline', true );
			}
		}
		$( socialAdditionsToggle )
			.on( 'change.tsfSetAdditions', updateSocialAdditions ); // We shouldn't have to trigger this, no?
	}

	/**
	 * Initializes Robots' meta input.
	 *
	 * @since 4.0.2
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initRobotsInputs = () => {

		const $input = $( '#autodescription-site-settings\\[set_copyright_directives\\]' );

		const $controls = $( [
			"#autodescription-site-settings\\[max_snippet_length\\]",
			"#autodescription-site-settings\\[max_image_preview\\]",
			"#autodescription-site-settings\\[max_video_preview\\]",
		].join( ', ' ) );

		if ( ! $input.length || ! $controls.length ) return;

		/**
		 * Toggles control directive option states.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const togglePreviewControl = ( event ) => {
			if ( event.target.checked ) {
				$controls.prop( 'disabled', false );
				$( '.tsf-toggle-directives-surrogate' ).remove();
			} else {
				$controls.prop( 'disabled', true );
				$controls.each( ( i, element ) => {
					$( '<input />' )
						.attr( 'type', 'hidden' )
						.attr( 'name', element.name || '' )
						.val( element.value || 0 )
						.addClass( 'tsf-toggle-directives-surrogate' )
						.insertAfter( element );
				} );
			}
		}
		$input.on( 'change.tsfToggleDirectives', togglePreviewControl );
		$input.trigger( 'change.tsfToggleDirectives' );
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

		const $inputs = $( [
			"#autodescription-site-settings\\[google_verification\\]",
			"#autodescription-site-settings\\[bing_verification\\]",
			"#autodescription-site-settings\\[yandex_verification\\]",
			"#autodescription-site-settings\\[baidu_verification\\]",
			"#autodescription-site-settings\\[pint_verification\\]",
		].join( ', ' ) );

		if ( ! $inputs.length ) return;

		/**
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {undefined}
		 */
		const trimScript = ( event ) => {
			let val = event.originalEvent.clipboardData && event.originalEvent.clipboardData.getData('text') || void 0;

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
		$inputs.on( 'paste', trimScript );
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

		const titleId = 'autodescription-site-settings[homepage_title]',
			  descId  = 'autodescription-site-settings[homepage_description]';

		tsfSocial.initTitleInputs( {
			ref:   document.getElementById( 'tsf-title-reference_' + titleId ),
			refNa: document.getElementById( 'tsf-title-noadditions-reference_' + titleId ),
			meta:  document.getElementById( titleId ),
			og:    document.getElementById( 'autodescription-site-settings[homepage_og_title]' ),
			tw:    document.getElementById( 'autodescription-site-settings[homepage_twitter_title]' ),
		} );

		tsfSocial.initDescriptionInputs( {
			ref:  document.getElementById( 'tsf-description-reference_' + descId ),
			meta: document.getElementById( descId ),
			og:   document.getElementById( 'autodescription-site-settings[homepage_og_description]' ),
			tw:   document.getElementById( 'autodescription-site-settings[homepage_twitter_description]' ),
		} );
	}

	/**
	 * Sets a class to the active element which helps excluding focus rings.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {(undefined|null)}
	 */
	const _initTabs = () => {

		let togglePromises   = {},
			toggleTarget     = {},
			toggleWrap$      = {},
			toggleContainer$ = {};
		/**
		 * Sets correct tab content and classes on toggle.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @param {undefined|true} onload
		 * @return {(undefined|null)}
		 */
		const tabToggle = ( event, onload ) => {

			const $currentToggle = $( event.target );

			// Why is this here? For broken customEvent triggers?
			if ( ! $currentToggle.is( ':checked' ) ) return;

			onload = typeof onload === 'boolean' ? onload : false;

			const toggleId   = event.target.id,
				  toggleName = event.target.name;

			toggleWrap$.hasOwnProperty( toggleName ) || (
				toggleWrap$[ toggleName ] = $currentToggle.closest( '.tsf-nav-tab-wrapper' )
			);

			const activeClass       = 'tsf-active-tab-content',
				  toggleActiveClass = 'tsf-tab-active',
				  $previousContent  = $( `.${activeClass}` ),
				  $previousToggle   = toggleWrap$[ toggleName ].find( `.${toggleActiveClass}` );

			if ( ! onload ) {
				// Perform validity check, this prevents non-invoking hidden browser validation errors.
				const $invalidInput = $previousContent.find( 'input:invalid, select:invalid, textarea:invalid' );
				if ( $invalidInput.length ) {
					$invalidInput[0].reportValidity();

					$previousToggle.prop( 'checked', true );
					$currentToggle.prop( 'checked', false );
					event.stopPropagation();
					event.preventDefault();
					return false; // stop propagation in jQuery.
				}
			}

			//= Previous active-state logger.
			$previousToggle.removeClass( toggleActiveClass );
			$previousToggle.siblings( 'label' ).removeClass( 'tsf-no-focus-ring' );
			$currentToggle.addClass( toggleActiveClass );

			if ( onload ) {
				const newContent = document.getElementById( `${toggleId}-content` );

				if ( ! newContent.classList.contains( activeClass ) ) {
					const allContent = document.querySelectorAll( `.${toggleName}-content` );
					allContent && allContent.forEach( element => {
						element.classList.remove( activeClass );
					} );
					newContent && newContent.classList.add( activeClass );
				}

				$( `#${toggleId}` ).trigger( 'tsf-tab-toggled' );
			} else {
				if ( ! toggleContainer$.hasOwnProperty( toggleName ) ) {
					toggleContainer$[ toggleName ] = $currentToggle.closest( '.inside' );
					togglePromises[ toggleName ] = void 0;
				}

				const fadeOutTimeout = 150;

				// Set toggleTarget for (active or upcoming) Promise.
				toggleTarget[ toggleName ] = toggleId;
				// If the promise is running, let it finish and consider the newly set ID.
				if ( 'undefined' !== typeof togglePromises[ toggleName ] ) return;

				const $allContent = $( '.' + toggleName + '-content' );
				const setCorrectTab = () => {
					$( `#${toggleTarget[ toggleName ]}-content` ).stop( false, true ).addClass( activeClass ).fadeIn( 250 );
					toggleContainer$[ toggleName ].css( 'minHeight', '' );
					return new Promise( resolve => setTimeout( resolve, fadeOutTimeout ) );
				};
				const lockHeight = () => {
					toggleContainer$[ toggleName ].css( 'minHeight', toggleContainer$[ toggleName ].height() );
				}

				togglePromises[ toggleName ] = () => new Promise( resolve => {
					// Lock height, so to prevent jumping.
					lockHeight();

					// Stop any running animations, and hide the content. Put in $.Deferred so to run the thenable only once.
					$.when( $allContent.stop( false, true ).fadeOut( fadeOutTimeout ) ).then( () => {
						$allContent.removeClass( activeClass );
						resolve();
					} );
				} ).then(
					setCorrectTab
				).then( () => {
					let toggledContent = document.getElementById( `${toggleTarget[ toggleName ]}-content` );

					// Test if the correct tab has been set--otherwise, try again.
					// Resolve if the query fails, so to prevent an infinite loop.
					if ( ! toggledContent || toggledContent.classList.contains( activeClass ) ) {
						$( `#${toggleTarget[ toggleName ]}` ).trigger( 'tsf-tab-toggled' );
						togglePromises[ toggleName ] = void 0;
					} else {
						// Lock height to prevent jumping.
						lockHeight();
						// Hide everything instantly. We don't make false promises here.
						$allContent.removeClass( activeClass );
						// Retry self.
						togglePromises[ toggleName ]();
					}
				} );

				togglePromises[ toggleName ]();
			}
		}
		$( '.tsf-tabs-radio' ).on( 'change', tabToggle );

		/**
		 * Sets a class to the active element which helps excluding focus rings.
		 *
		 * @see tabToggle Handles this HTML class.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @return {(undefined|null)}
		 */
		const _addNoFocusClass = ( event ) => {
			event.currentTarget.classList.add( 'tsf-no-focus-ring' );
		}
		$( '.tsf-nav-tab-wrapper' ).on( 'click.tsfNavTab', '.tsf-nav-tab', _addNoFocusClass );

		/**
		 * Sets the correct tab based on selected radio button prior window.history navigation.
		 *
		 * @see tabToggle Handles this HTML class.
		 *
		 * @function
		 * @return {(undefined|null)}
		 */
		const _correctTabFocus = () => {
			// Don't handle subsequent triggers.
			window.removeEventListener( 'load', _correctTabFocus );

			$( '.tsf-tabs-radio:checked' ).each( ( i, element ) => {
				$( element ).trigger( 'change', [ true ] );
			} );
		}
		window.addEventListener( 'load', _correctTabFocus );
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
