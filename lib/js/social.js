/**
 * This file holds The SEO Framework plugin's JS code for the Social Input Settings.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/autodescription/>
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
	 * @access public
	 *
	 * @function
	 * @param {(Object<string, Element>)} inputs The social input elements.
	 * @return {undefined}
	 */
	const initTitleInputs = ( inputs ) => {

		let $ogTitle   = $( inputs.og ),
			$twTitle   = $( inputs.tw ),
			$refTitle  = $( inputs.ref ),
			$metaTitle = $( inputs.meta );

		if ( ! $ogTitle.length || ! $twTitle.length || ! $metaTitle.length || ! $refTitle.length )
			return;

		let ogLocked   = l10n.params.homeLocks.ogTitleLock,
			ogPHLocked = l10n.params.homeLocks.ogTitlePHLock,
			twLocked   = l10n.params.homeLocks.twTitleLock,
			twPHLocked = l10n.params.homeLocks.twTitlePHLock;

		let ogTitleValue  = ogLocked ? $ogTitle.attr( 'placeholder' ) : $ogTitle.val(),
			twTitleValue  = twLocked ? $twTitle.attr( 'placeholder' ) : $twTitle.val(),
			refTitleValue = $refTitle.html();

		const getActiveValue = ( what ) => {
			let val = '';
			switchActive:
			switch ( what ) {
				case 'twitter':
					val = twTitleValue;
					if ( twLocked || twPHLocked ) {
						val = val.length ? val : $twTitle.attr( 'placeholder' );
						break switchActive;
					}
					// get next if not set.
				case 'og':
					val = val.length ? val : ogTitleValue;
					if ( ogLocked || ogPHLocked ) {
						val = val.length ? val : $ogTitle.attr( 'placeholder' );
						break switchActive;
					}
					// get next if not set.
				case 'meta':
					/**
					 * Nothing here, all is handled by ref due to the title's complexity.
					 * Moreover, OG Title and TW Title still contain the BLOGNAME additions by default.
					 * NOTE this will become optional, see https://github.com/sybrew/the-seo-framework/issues/394
					 * Then, we will run this switch state conditionally.
					 *
					 * e.g.
					 * if ( l10n.params.useSocialTitleAdditions ) {
					 *    // do nothing
					 * } else {
					 *    val = $metaTitle.val();
					 * }
					 */
					// get next if not set.
				case 'ref':
					val = val.length ? val : refTitleValue;
					break;
			}
			return val;
		};
		const setPlaceholders = () => {
			// $.attr escapes.
			ogLocked || ogPHLocked || $ogTitle.attr( 'placeholder', tsf.decodeEntities( getActiveValue( 'meta' ) ) );
			twLocked || twPHLocked || $twTitle.attr( 'placeholder', tsf.decodeEntities( getActiveValue( 'og' ) ) );
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
		let updateSocialCountersBuffer = 0;
		const updateSocialCounters = () => {
			clearTimeout( updateSocialCountersBuffer );
			updateSocialCountersBuffer = setTimeout( () => {
				$ogTitle.each( ( i, el ) => updateCounter( el, getActiveValue( 'og' ), 'opengraph' ) );
				$twTitle.each( ( i, el ) => updateCounter( el, getActiveValue( 'twitter' ), 'twitter' ) );
			}, 10 );
		};
		const updateRefTitle = ( event ) => {
			refTitleValue = $refTitle.html();
			setPlaceholders();
			updateSocialCounters();
		};
		$refTitle.on( 'change.tsfUpdateRefTitle', updateRefTitle );

		const updateOgTitle = ( event ) => {
			if ( ! ogLocked ) {
				let text = event.target.value.trim();
				ogTitleValue = text.length ? tsf.sDoubleSpace( text ) : '';
			}
			setPlaceholders();
			updateSocialCounters();
		};
		const updateTwTitle = ( event ) => {
			if ( ! twLocked ) {
				let text = event.target.value.trim();
				twTitleValue = text.length ? tsf.sDoubleSpace( text ) : '';
			}
			setPlaceholders();
			updateSocialCounters();
		};
		$ogTitle.on( 'input.tsfUpdateOgTitle', updateOgTitle );
		$twTitle.on( 'input.tsfUpdateTwTitle', updateTwTitle );
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

		let $ogDesc   = $( inputs.og ),
			$twDesc   = $( inputs.tw ),
			$metaDesc = $( inputs.meta ),
			$refDesc  = $( inputs.ref );

		if ( ! $ogDesc.length || ! $twDesc.length || ! $metaDesc.length || ! $refDesc.length )
			return;

		let ogLocked   = l10n.params.homeLocks.ogDescriptionLock,
			ogPHLocked = l10n.params.homeLocks.ogDescriptionPHLock,
			twLocked   = l10n.params.homeLocks.twDescriptionLock,
			twPHLocked = l10n.params.homeLocks.twDescriptionPHLock;

		let ogDescValue  = ogLocked ? $ogDesc.attr( 'placeholder' ) : $ogDesc.val(),
			twDescValue  = twLocked ? $twDesc.attr( 'placeholder' ) : $twDesc.val(),
			refDescValue = $refDesc.html(); // already escaped.

		const getActiveValue = ( what, context ) => {
			let val = '';
			switchActive:
			switch ( what ) {
				case 'twitter':
					val = twDescValue;
					if ( twLocked || twPHLocked ) {
						val = val.length ? val : $twDesc.attr( 'placeholder' );
						break switchActive;
					}
					// get next if not set.
				case 'og':
					val = val.length ? val : ogDescValue;
					if ( ogLocked || ogPHLocked ) {
						val = val.length ? val : $ogDesc.attr( 'placeholder' );
						break switchActive;
					}
					// get next if not set.
				case 'meta':
						if ( ! val.length ) {
							if ( ! $metaDesc.val().length ) {
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
			// $.attr escapes.
			ogLocked || ogPHLocked || $ogDesc.attr( 'placeholder', tsf.decodeEntities( getActiveValue( 'meta', 'og' ) ) );
			twLocked || twPHLocked || $twDesc.attr( 'placeholder', tsf.decodeEntities( getActiveValue( 'og', 'twitter' ) ) );
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
				$ogDesc.each( ( i, el ) => updateCounter( el, getActiveValue( 'og', 'og' ), 'opengraph' ) );
				$twDesc.each( ( i, el ) => updateCounter( el, getActiveValue( 'twitter', 'twitter' ), 'twitter'  ) );
			}, 10 );
		};
		const updateRefDesc = ( event ) => {
			refDescValue = $refDesc.html();
			setPlaceholders();
			updateSocialCounters();
		};
		$refDesc.on( 'change.tsfUpdateRefDesc', updateRefDesc );

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
		$ogDesc.on( 'input.tsfUpdateOgDesc', updateOgDesc );
		$twDesc.on( 'input.tsfUpdateTwDesc', updateTwDesc );
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
jQuery( window.tsfSocial.load );
