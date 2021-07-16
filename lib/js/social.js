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
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfSocial = function( $ ) {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 4.0.0
	 * @access public
	 * @type {(Object<string, *>)|boolean|null} l10n Localized strings
	 */
	const l10n = 'undefined' !== typeof tsfSocialL10n && tsfSocialL10n;

	/**
	 * @since 4.0.0
	 * @access private
	 * @todo deprecate; either remove and contain these within the callers, or convert to states. See tsfTitle & tsfDescription.
	 * @type {(Object<string, *>)} the query state.
	 */
	let state = {
		ogDescPlaceholder: l10n.states.placeholders.ogDesc,
		twDescPlaceholder: l10n.states.placeholders.twDesc,
	};

	/**
	 * Returns state.
	 *
	 * @since 4.0.0
	 * @access public
	 * @todo deprecate; either remove, or convert to getStateOf. See tsfTitle & tsfDescription.
	 *
	 * @param {(string|undefined)} part The part to return. Leave empty to return the whole state.
	 * @return {(Object<string, *>)|*|undefined}
	 */
	const getState = part => part ? ( part in state ? state[ part ] : void 0 ) : state;

	/**
	 * Updates state.
	 *
	 * There's no need to escape the input, it may be double-escaped if you do so.
	 *
	 * @since 4.0.0
	 * @access public
	 * @todo deprecate; either remove, or convert to updateStateOf. See tsfTitle & tsfDescription.
	 *
	 * @param {string} type  The state index to change.
	 * @param {*}      value The value to set the state to.
	 * @return {undefined}
	 */
	const updateState = ( type, value ) => {

		state[ type ] = value;

		switch ( type ) {
			case 'ogDescPlaceholder':
			case 'twDescPlaceholder':
				// We don't have access to these, because, unlike the desc and title scripts, all listeners are contained.
				// We found that this was more "interchangable," especially when we move to a REST-based admin UI--as proposed in WooCommerce.
				// However, this will break so many implementations, it's unfathomable.
				// With that, we haven't found to time to make it more like the description and title scripts. So, for now, trigger it yourself:
				// $refDesc.trigger( 'change.tsfUpdateMetaDesc' ); (preferred: tsfDescription.triggerInput())
				// $metaDesc.trigger( 'input.tsfUpdateMetaDesc' ); (preferred: tsfDescription.triggerInput())
				// $ogDesc.trigger( 'input.tsfUpdateOgDesc' );
				// $twDesc.trigger( 'input.tsfUpdateTwDesc' );
				//
				break;
			default:
				break;
		}
	}

	/**
	 * Initializes social titles.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now handles the refNa input for the "no additions" values.
	 * @access public
	 *
	 * @function
	 * @param {Object<string,Element>} inputs The social input elements.
	 * @return {undefined}
	 */
	const initTitleInputs = ( inputs ) => {

		let ogTitle    = inputs.og,
			twTitle    = inputs.tw,
			refTitle   = inputs.ref,
			refNaTitle = inputs.refNa,
			metaTitle  = inputs.meta;

		if ( ! ogTitle || ! twTitle || ! refTitle || ! refNaTitle || ! metaTitle )
			return;

		let ogLocked   = l10n.params.homeLocks.ogTitleLock,
			ogPHLocked = l10n.params.homeLocks.ogTitlePHLock,
			twLocked   = l10n.params.homeLocks.twTitleLock,
			twPHLocked = l10n.params.homeLocks.twTitlePHLock;

		let ogTitleValue    = ogLocked ? ogTitle.placeholder : ogTitle.value,
			twTitleValue    = twLocked ? twTitle.placeholder : twTitle.value,
			refTitleNaValue = refNaTitle.innerHTML, // Security OK. RefNa is escaped.
			refTitleValue   = refTitle.innerHTML;   // Security OK. Ref is escaped.

		const getActiveValue = ( what ) => {
			let val = '';
			switchActive:
			switch ( what ) {
				case 'twitter':
					val = twTitleValue;
					if ( twLocked || twPHLocked ) {
						// TODO we need to step away from our reliance on placeholders.
						val = val.length ? val : twTitle.placeholder;
						break switchActive;
					}
					// get next if not set.
				case 'og':
					val = val.length ? val : ogTitleValue;
					if ( ogLocked || ogPHLocked ) {
						// TODO we need to step away from our reliance on placeholders.
						val = val.length ? val : ogTitle.placeholder;
						break switchActive;
					}
					// get next if not set.
				case 'meta':
					// All is handled by ref due to the title's complexity.
				case 'ref':
					if ( ! val.length ) {
						// Tagline = additions = blog name = site title. Well done, Sybre. :) FIXME, noob.
						if ( tsfTitle.getStateOf( metaTitle.id, 'useSocialTagline' ) ) {
							val = refTitleValue;
						} else {
							val = refTitleNaValue;
						}
					}
					break;
			}
			return val;
		};
		const setPlaceholders = () => {
			if ( ! ogLocked && ! ogPHLocked ) {
				// Security OK. All getActiveValue is escaped.
				ogTitle.placeholder = tsf.decodeEntities( getActiveValue( 'meta' ) );
			}
			if ( ! twLocked && ! twPHLocked ) {
				// Security OK. All getActiveValue is escaped.
				twTitle.placeholder = tsf.decodeEntities( getActiveValue( 'og' ) );
			}
		};
		const updateCounter = ( target, text, type ) => {
			let counter = document.getElementById( target.id + '_chars' );

			if ( ! counter ) return;

			tsfC.updateCharacterCounter( {
				e:     counter,
				text:  text,
				field: 'title',
				type:  type,
			} );
		};
		let updateSocialCountersBuffer = void 0;
		const updateSocialCounters = () => {
			clearTimeout( updateSocialCountersBuffer );
			updateSocialCountersBuffer = setTimeout( () => {
				updateCounter( ogTitle, getActiveValue( 'og' ), 'opengraph' );
				updateCounter( twTitle, getActiveValue( 'twitter' ), 'twitter' );
			}, 10 );
		};
		let updateRefTitleBuffer = void 0;
		const updateRefTitle = event => {
			clearTimeout( updateRefTitleBuffer );
			updateRefTitleBuffer = setTimeout( () => {
				refTitleValue   = refTitle.innerHTML;   // Security OK. Ref is escaped.
				refTitleNaValue = refNaTitle.innerHTML; // Security OK. RefNa is escaped.
				setPlaceholders();
				updateSocialCounters();
			}, 10 );
		};
		refTitle.addEventListener( 'change', updateRefTitle );
		refNaTitle.addEventListener( 'change', updateRefTitle );

		const updateOgTitle = event => {
			if ( ! ogLocked ) {
				let text = event.target.value.trim();
				ogTitleValue = text.length ? tsf.sDoubleSpace( text ) : '';
			}
			setPlaceholders();
			updateSocialCounters();
		};
		const updateTwTitle = event => {
			if ( ! twLocked ) {
				let text = event.target.value.trim();
				twTitleValue = text.length ? tsf.sDoubleSpace( text ) : '';
			}
			setPlaceholders();
			updateSocialCounters();
		};
		ogTitle.addEventListener( 'input', updateOgTitle );
		twTitle.addEventListener( 'input', updateTwTitle );
	}

	/**
	 * Initializes social descriptions.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @function
	 * @param {(Object<string, Element>)} inputs The social input elements.
	 * @return {undefined}
	 */
	const initDescriptionInputs = ( inputs ) => {

		let ogDesc    = inputs.og,
			twDesc    = inputs.tw,
			refDesc   = inputs.ref,
			metaDesc  = inputs.meta; // unused?

		if ( ! ogDesc || ! twDesc || ! refDesc || ! metaDesc )
			return;

		let ogLocked   = l10n.params.homeLocks.ogDescriptionLock,
			ogPHLocked = l10n.params.homeLocks.ogDescriptionPHLock,
			twLocked   = l10n.params.homeLocks.twDescriptionLock,
			twPHLocked = l10n.params.homeLocks.twDescriptionPHLock;

		let ogDescValue  = ogLocked ? ogDesc.placeholder : ogDesc.value,
			twDescValue  = twLocked ? twDesc.placeholder : twDesc.value,
			refDescValue = refDesc.innerHTML; // Security OK. Ref is escaped.

		const getActiveValue = ( what, context ) => {
			let val = '';
			switchActive:
			switch ( what ) {
				case 'twitter':
					val = twDescValue;
					if ( twLocked || twPHLocked ) {
						// TODO we need to step away from our reliance on placeholders.
						val = val.length ? val : twDesc.placeholder;
						break switchActive;
					}
					// get next if not set.
				case 'og':
					val = val.length ? val : ogDescValue;
					if ( ogLocked || ogPHLocked ) {
						// TODO we need to step away from our reliance on placeholders.
						val = val.length ? val : ogDesc.placeholder;
						break switchActive;
					}
					// get next if not set.
				case 'meta':
						if ( ! val.length ) {
							if ( ! metaDesc.value.length ) {
								if ( 'twitter' === context ) {
									val = state.twDescPlaceholder;
								} else if ( 'og' === context ) {
									val = state.ogDescPlaceholder;
								}
							}
						}
						// get next if not set.
				case 'ref':
					val = val.length ? val : refDescValue;
					break;
			}
			return val;
		};
		const setPlaceholders = () => {
			if ( ! ogLocked && ! ogPHLocked ) {
				// Security OK. All getActiveValue is escaped.
				ogDesc.placeholder = tsf.decodeEntities( getActiveValue( 'meta', 'og' ) );
			}
			if ( ! twLocked && ! twPHLocked ) {
				// Security OK. All getActiveValue is escaped.
				twDesc.placeholder = tsf.decodeEntities( getActiveValue( 'og', 'twitter' ) );
			}
		};
		const updateCounter = ( target, text, type ) => {
			let counter = document.getElementById( target.id + '_chars' );

			if ( ! counter ) return;

			tsfC.updateCharacterCounter( {
				e:     counter,
				text:  text,
				field: 'description',
				type:  type,
			} );
		};
		let updateSocialCountersBuffer = 0;
		const updateSocialCounters = () => {
			clearTimeout( updateSocialCountersBuffer );
			updateSocialCountersBuffer = setTimeout( () => {
				updateCounter( ogDesc, getActiveValue( 'og', 'og' ), 'opengraph' );
				updateCounter( twDesc, getActiveValue( 'twitter', 'twitter' ), 'twitter' );
			}, 10 );
		};
		const updateRefDesc = event => {
			refDescValue = refDesc.innerHTML; // Security OK. Ref is escaped.
			setPlaceholders();
			updateSocialCounters();
		};
		refDesc.addEventListener( 'change', updateRefDesc );

		const updateOgDesc = ( event ) => {
			if ( ! ogLocked ) {
				let text = event.target.value.trim();
				ogDescValue = text.length ? tsf.sDoubleSpace( text ) : '';
			}
			setPlaceholders();
			updateSocialCounters();
		};
		const updateTwDesc = ( event ) => {
			if ( ! twLocked ) {
				let text = event.target.value.trim();
				twDescValue = text.length ? tsf.sDoubleSpace( text ) : '';
			}
			setPlaceholders();
			updateSocialCounters();
		};
		ogDesc.addEventListener( 'input', updateOgDesc );
		twDesc.addEventListener( 'input', updateTwDesc );
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
		initTitleInputs,
		initDescriptionInputs,
		getState,
		updateState,
	}, {
		l10n
	} );
}( jQuery );
window.tsfSocial.load();
