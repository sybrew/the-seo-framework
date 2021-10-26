/**
 * This file holds The SEO Framework plugin's JS code for the Social Input Settings.
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
 * Holds tsfSocial values in an object to avoid polluting global namespace.
 *
 * @since 4.0.0
 * @since 4.2.0 Removed 'updateState', and 'getState' without deprecation.
 *
 * @constructor
 */
window.tsfSocial = function() {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 4.0.0
	 * @access public
	 * @type {(Object<string, *>)|boolean|null} l10n Localized strings
	 */
	const l10n = 'undefined' !== typeof tsfSocialL10n && tsfSocialL10n;

	/**
	 * @since 4.2.0
	 * @type {(Map<string,{
	 * 	 group:  string,
	 * 	 inputs: {ogTitle:Element,twTitle:Element,ogDesc:Element,twDesc:Element}
	 * 	 refs:   {title:Element,titleNa:Element,desc:Element}
	 * }>)} The input element instances.
	 */
	const inputInstances = new Map();

	/**
	 * @since 4.2.0
	 * @access private
	 * @type {(Object<string,Object<string,*>)} the query state.
	 */
	let states = {};

	/**
	 * Returns state of ID.
	 *
	 * @since 4.2.0
	 * @access public
	 *
	 * @param {string}             group The group ID.
	 * @param {(string|undefined)} part  The part to return. Leave empty to return the whole state.
	 * @return {(Object<string, *>)|*|null}
	 */
	const getStateOf = ( group, part ) => part ? states[ group ]?.[ part ] : states[ group ];

	/**
	 * Ticks a state change, which may propagate further events.
	 *
	 * @since 4.2.0
	 * @access private
	 *
	 * @param {string} group
	 * @param {string} part
	 */
	const _tickState = ( group, part ) => {
		switch ( part ) {
			case 'addAdditions':
				let titleRef = getInputInstance( group ).refs.title.dataset?.for;
				titleRef && tsfTitle.enqueueUnregisteredInputTrigger( titleRef );
				break;
			default:
				break;
		}
	}

	/**
	 * Updates state of ID.
	 *
	 * There's no need to escape the input, it may be double-escaped if you do so.
	 *
	 * @since 4.2.0
	 * @access public
	 *
	 * @param {string} group The group ID.
	 * @param {string} part  The state index to change.
	 * @param {*}      value The value to set the state to.
	 * @return {undefined}
	 */
	const updateStateOf = ( group, part, value ) => {
		states[ group ][ part ] = value;
		_tickState( group, part );
	}

	/**
	 * Updates substate of ID.
	 *
	 * There's no need to escape the input, it may be double-escaped if you do so.
	 *
	 * @since 4.2.0
	 * @access public
	 *
	 * @param {string} group The group ID.
	 * @param {string} part  The state index to change.
	 * @param {string} sub   The sub state index to change.
	 * @param {*}      value The value to set the state to.
	 * @return {undefined}
	 */
	const updateSubStateOf = ( group, part, sub, value ) => {
		states[ group ][ part ][ sub ] = value;
		_tickState( group, part );
	}

	/**
	 * Updates state of all elements.
	 *
	 * There's no need to escape the input, it may be double-escaped if you do so.
	 *
	 * @since 4.2.0
	 * @access public
	 * @TODO add a "but" ({String|Array})
	 *
	 * @param {string} part  The state index to change.
	 * @param {*}      value The value to set the state to.
	 * @return {undefined}
	 */
	const updateStateAll = ( part, value ) => {
		inputInstances.forEach( ( { group, inputs, refs } ) => {
			updateStateOf( group, part, value );
		} );
	}

	/**
	 * Updates substate of all elements.
	 *
	 * There's no need to escape the input, it may be double-escaped if you do so.
	 *
	 * @since 4.2.0
	 * @access public
	 *
	 * @param {string} part  The state index to change.
	 * @param {string} sub   The sub state index to change.
	 * @param {*}      value The value to set the state to.
	 * @return {undefined}
	 */
	 const updateSubStateAll = ( part, sub, value ) => {
		inputInstances.forEach( ( { group, inputs, refs } ) => {
			updateSubStateOf( group, part, sub, value );
		} );
	}

	/**
	 * Sets input group for all listeners. Must be called prior interacting with this object.
	 * Resets the state for the group ID.
	 *
	 * @since 4.2.0
	 * @access public
	 *
	 * @param {string} group    The group ID.
	 * @param {string} titleRef The group's title reference ID.
	 * @param {string} descRef  The group's description reference ID.
	 * @return {undefined}
	 */
	 const setInputInstance = ( group, titleRef, descRef ) => {

		const _getElement = type => document.querySelector(
			`[data-tsf-social-group="${group}"][data-tsf-social-type="${type}"]`
		);

		const inputs = {
			ogTitle: _getElement( 'ogTitle' ),
			twTitle: _getElement( 'twTitle' ),
			ogDesc:  _getElement( 'ogDesc' ),
			twDesc:  _getElement( 'twDesc' ),
		}

		const refs = {
			title:   document.getElementById( `tsf-title-reference_${titleRef}` ),
			titleNa: document.getElementById( `tsf-title-noadditions-reference_${titleRef}` ),
			desc:    document.getElementById( `tsf-description-reference_${descRef}` ),
		}

		inputInstances.set( group, { group, inputs, refs } );

		states[ group ] = {
			defaults: {
				ogTitle: '',
				twTitle: '',
				ogDesc:  '',
				twDesc:  '',
			},
			inputLocks: {
				ogTitle: false,
				twTitle: false,
				ogDesc:  false,
				twDesc:  false,
			},
			placeholderLocks: {
				ogTitle: false,
				twTitle: false,
				ogDesc:  false,
				twDesc:  false,
			},
		}

		_loadTitleActions( group );
		_loadDescriptionActions( group );

		return getInputInstance( group );
	}

	/**
	 * Gets input element, if exists.
	 *
	 * @since 4.1.0
	 * @access public
	 *
	 * @param {string} group The group ID.
	 */
	const getInputInstance = group => inputInstances.get( group );

	/**
	 * Loads Title actions for group.
	 *
	 * @since 4.2.0
	 * @access private
	 *
	 * @param {string} group The group ID.
	 */
	const _loadTitleActions = group => {

		const { inputs, refs } = getInputInstance( group );

		const getState = part => getStateOf( group, part );

		function* _generateActiveValue( what ) {
			const locks   = getState( 'inputLocks' ),
				  phLocks = getState( 'placeholderLocks' );

			switch ( what ) {
				case 'twitter':
					yield locks.twTitle
						? getState( 'defaults' ).twTitle
						: inputs.twTitle.value.trim();

					if ( locks.twTitle || phLocks.twTitle ) {
						yield getState( 'defaults' ).twTitle;
						break;
					}
				case 'og':
					yield locks.ogTitle
						? getState( 'defaults' ).ogTitle
						: inputs.ogTitle.value.trim();

					if ( locks.ogTitle || phLocks.ogTitle ) {
						yield getState( 'defaults' ).ogTitle;
						break;
					}
				case 'meta':
					// All is handled by ref due to the title's complexity.
				case 'ref':
					if ( getState( 'addAdditions' ) )  {
						yield refs.title.innerHTML;
					} else {
						yield refs.titleNa.innerHTML;
					}
					break;
			}
		}
		const getActiveValue = what => {
			const generator = _generateActiveValue( what );

			let val = '';

			while ( 'undefined' !== typeof val && ! val.length ) {
				val = generator.next().value;
				if ( val?.length )
					val = tsf.sDoubleSpace(
						tsf.sTabs(
							tsf.sSingleLine(
								val
							)
						)
					);
			}

			return val?.length ? val : '';
		}
		const setPlaceholders = () => {
			const locks   = getState( 'inputLocks' ),
				  phLocks = getState( 'placeholderLocks' );

			// Security OK. All getActiveValue is escaped.
			inputs.ogTitle.placeholder
				= locks.ogTitle || phLocks.ogTitle
					? tsf.decodeEntities( getState( 'defaults' ).ogTitle )
					: tsf.decodeEntities( getActiveValue( 'meta' ) );
			inputs.twTitle.placeholder
				= locks.twTitle || phLocks.twTitle
					? tsf.decodeEntities( getState( 'defaults' ).twTitle )
					: tsf.decodeEntities( getActiveValue( 'og' ) );
		}
		const updateCounter = ( target, text, type ) => {
			let counter = document.getElementById( `${target.id}_chars` );
			counter && tsfC.updateCharacterCounter( {
				e:     counter,
				text:  text,
				field: 'title',
				type:  type,
			} );
		}
		const updateSocialCounters = () => {
			updateCounter( inputs.ogTitle, getActiveValue( 'og' ), 'opengraph' );
			updateCounter( inputs.twTitle, getActiveValue( 'twitter' ), 'twitter' );
		}
		let updateRefTitleBuffer = void 0;
		const updateRefTitle = () => {
			clearTimeout( updateRefTitleBuffer );
			updateRefTitleBuffer = setTimeout( () => {
				setPlaceholders();
				updateSocialCounters();
			}, 1000/60 ); // 60fps
		};
		refs.title.addEventListener( 'change', updateRefTitle );
		refs.titleNa.addEventListener( 'change', updateRefTitle );
		let updateTitleBuffer = void 0;
		const updateTitle = () => {
			clearTimeout( updateTitleBuffer );
			updateTitleBuffer = setTimeout( () => {
				setPlaceholders();
				updateSocialCounters();
			}, 1000/60 ); // 60fps
		}
		inputs.ogTitle.addEventListener( 'input', updateTitle );
		inputs.twTitle.addEventListener( 'input', updateTitle );
	}

	/**
	 * Loads Title actions for group.
	 *
	 * @since 4.2.0
	 * @access private
	 *
	 * @param {string} group The group ID.
	 */
	const _loadDescriptionActions = group => {

		const { inputs, refs } = getInputInstance( group );

		const getState = part => getStateOf( group, part );

		// We use context here because the description guidelines differ per social media embed.
		function* _generateActiveValue( what, context ) {
			const locks   = getState( 'inputLocks' ),
				  phLocks = getState( 'placeholderLocks' );

			switch ( what ) {
				case 'twitter':
					yield locks.twDesc
						? getState( 'defaults' ).twDesc
						: inputs.twDesc.value.trim();

					if ( locks.twDesc || phLocks.twDesc ) {
						yield getState( 'defaults' ).twDesc;
						break;
					}
					// get next if not set.
				case 'og':
					yield locks.ogDesc
						? getState( 'defaults' ).ogDesc
						: inputs.ogDesc.value.trim();

					if ( locks.ogDesc || phLocks.ogDesc ) {
						yield getState( 'defaults' ).ogDesc;
						break;
					}
					// get next if not set.
				case 'meta':
					if ( 'twitter' === context ) {
						yield refs.desc.innerHTML || getState( 'defaults' ).twDesc;
					} else if ( 'og' === context ) {
						yield refs.desc.innerHTML || getState( 'defaults' ).ogDesc;
					}
					// get next if not set.
				case 'ref':
					yield refs.desc.innerHTML;
					break;
			}
		}
		const getActiveValue = ( what, context ) => {
			const generator = _generateActiveValue( what, context );

			let val = '';

			while ( 'undefined' !== typeof val && ! val.length ) {
				val = generator.next().value;
				if ( val?.length )
					val = tsf.sDoubleSpace(
						tsf.sTabs(
							tsf.sSingleLine(
								val
							)
						)
					);
			}

			return val?.length ? val : '';
		}
		const setPlaceholders = () => {
			const locks   = getState( 'inputLocks' ),
				  phLocks = getState( 'placeholderLocks' );

			// Security OK. All getActiveValue is escaped.
			inputs.ogDesc.placeholder
				= locks.ogDesc || phLocks.ogDesc
					? tsf.decodeEntities( getState( 'defaults' ).ogDesc )
					: tsf.decodeEntities( getActiveValue( 'meta', 'og' ) );
			// Security OK. All getActiveValue is escaped.
			inputs.twDesc.placeholder
				= locks.twDesc || phLocks.twDesc
					? tsf.decodeEntities( getState( 'defaults' ).twDesc )
					: tsf.decodeEntities( getActiveValue( 'og', 'twitter' ) );
		}
		const updateCounter = ( target, text, type ) => {
			let counter = document.getElementById( `${target.id}_chars` );
			counter && tsfC.updateCharacterCounter( {
				e:     counter,
				text:  text,
				field: 'description',
				type:  type,
			} );
		}
		const updateSocialCounters = () => {
			updateCounter( inputs.ogDesc, getActiveValue( 'og', 'og' ), 'opengraph' );
			updateCounter( inputs.twDesc, getActiveValue( 'twitter', 'twitter' ), 'twitter' );
		}
		let updateRefDescBuffer = void 0;
		const updateRefDesc = () => {
			clearTimeout( updateRefDescBuffer );
			updateRefDescBuffer = setTimeout( () => {
				setPlaceholders();
				updateSocialCounters();
			}, 1000/60 ); // 60fps
		};
		refs.desc.addEventListener( 'change', updateRefDesc );

		let updateDescBuffer = void 0;
		const updateDesc = () => {
			clearTimeout( updateDescBuffer );
			updateDescBuffer = setTimeout( () => {
				setPlaceholders();
				updateSocialCounters();
			}, 1000/60 ); // 60fps
		}
		inputs.ogDesc.addEventListener( 'input', updateDesc );
		inputs.twDesc.addEventListener( 'input', updateDesc );
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
		load: () => { }
	}, {
		setInputInstance,
		getInputInstance,
		updateStateOf,
		updateSubStateOf,
		updateStateAll,
		updateSubStateAll,
	}, {
		l10n
	} );
}();
window.tsfSocial.load();
