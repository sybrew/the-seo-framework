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
 * @since 3.3.0
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfSocial = function( $ ) {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 3.3.0
	 * @access public
	 * @type {(Object<string, *>)|boolean|null} l10n Localized strings
	 */
	const l10n = 'undefined' !== typeof tsfSocialL10n && tsfSocialL10n;

	/**
	 * Initializes social titles.
	 *
	 * @since 3.3.0
	 * @access public
	 *
	 * @function
	 * @param {(Object<string, Element>)} inputs The social input elements.
	 * @return {undefined}
	 */
	const initTitleInputs = ( inputs ) => {

		let $ogTitle   = $( inputs.og ),
			$twTitle   = $( inputs.tw ),
			$metaTitle = $( inputs.meta );

		if ( ! $ogTitle.length || ! $twTitle.length || ! $metaTitle.length )
			return;

		let ogLocked   = l10n.params.homeLocks.ogTitleLock,
			ogPHLocked = l10n.params.homeLocks.ogTitlePHLock,
			twLocked   = l10n.params.homeLocks.twTitleLock,
			twPHLocked = l10n.params.homeLocks.twTitlePHLock;

		let ogTitleValue   = ogLocked ? $ogTitle.prop( 'placeholder' ) : $ogTitle.val(),
			twTitleValue   = twLocked ? $twTitle.prop( 'placeholder' ) : $twTitle.val(),
			metaTitleValue = $metaTitle.text();

		const getActiveValue = ( what ) => {
			let val = '';
			switchActive:
			switch ( what ) {
				case 'twitter':
					val = twTitleValue;
					if ( twLocked || twPHLocked ) {
						val = val.length ? val : $twTitle.prop( 'placeholder' );
						break switchActive;
					}
					// get next if not set.
				case 'og':
					val = val.length ? val : ogTitleValue;
					if ( ogLocked || ogPHLocked ) {
						val = val.length ? val : $ogTitle.prop( 'placeholder' );
						break switchActive;
					}
					// get next if not set.
				case 'meta':
					val = val.length ? val : metaTitleValue;
					break;
			}
			return val;
		};
		const setPlaceholders = () => {
			// This escapes.
			ogLocked || ogPHLocked || $ogTitle.prop( 'placeholder', getActiveValue( 'meta' ) );
			twLocked || twPHLocked || $twTitle.prop( 'placeholder', getActiveValue( 'og' ) );
		};
		const updateCounter = ( target, text, type ) => {
			let counter = document.getElementById( target.id + '_chars' );

			if ( ! counter ) return;

			tsfC.updateCharacterCounter( {
				e:     counter,
				text:  tsf.unescapeString( text ),
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
		$metaTitle.on( 'change', () => {
			metaTitleValue = $metaTitle.text();
			setPlaceholders();
			updateSocialCounters();
		} );

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
	 * @since 3.3.0
	 * @access public
	 *
	 * @function
	 * @param {(Object<string, Element>)} inputs The social input elements.
	 * @return {undefined}
	 */
	const initDescriptionInputs = ( inputs ) => {

		let $ogDesc   = $( inputs.og ),
			$twDesc   = $( inputs.tw ),
			$metaDesc = $( inputs.meta );

		if ( ! $ogDesc.length || ! $twDesc.length || ! $metaDesc.length )
			return;

		let ogLocked   = l10n.params.homeLocks.ogDescriptionLock,
			ogPHLocked = l10n.params.homeLocks.ogDescriptionPHLock,
			twLocked   = l10n.params.homeLocks.twDescriptionLock,
			twPHLocked = l10n.params.homeLocks.twDescriptionPHLock;

		let ogDescValue   = ogLocked ? $ogDesc.prop( 'placeholder' ) : $ogDesc.val(),
			twDescValue   = twLocked ? $twDesc.prop( 'placeholder' ) : $twDesc.val(),
			metaDescValue = $metaDesc.text(); // already escaped.

		const getActiveValue = ( what, context ) => {
			let val = '';
			switchActive:
			switch ( what ) {
				case 'twitter':
					val = twDescValue;
					if ( twLocked || twPHLocked ) {
						val = val.length ? val : $twDesc.prop( 'placeholder' );
						break switchActive;
					}
					// get next if not set.
				case 'og':
					val = val.length ? val : ogDescValue;
					if ( ogLocked || ogPHLocked ) {
						val = val.length ? val : $ogDesc.prop( 'placeholder' );
						break switchActive;
					}
					// get next if not set.
				case 'meta':
					if ( ! val.length ) {
						if ( metaDescValue.length ) {
							val = metaDescValue;
						} else {
							if ( 'twitter' === context ) {
								val = l10n.params.placeholders.twDesc;
							} else if ( 'og' === context ) {
								val = l10n.params.placeholders.ogDesc;
							}
						}
					}
					break;
			}
			return val;
		};
		const setPlaceholders = () => {
			// This escapes.
			ogLocked || ogPHLocked || $ogDesc.attr( 'placeholder', getActiveValue( 'meta', 'og' ) );
			twLocked || twPHLocked || $twDesc.attr( 'placeholder', getActiveValue( 'og', 'twitter' ) );
		};
		const updateCounter = ( target, text, type ) => {
			let counter = document.getElementById( target.id + '_chars' );

			if ( ! counter ) return;

			tsfC.updateCharacterCounter( {
				e:     counter,
				text:  tsf.unescapeString( text ),
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
		$metaDesc.on( 'change', () => {
			metaDescValue = $metaDesc.text();
			setPlaceholders();
			updateSocialCounters();
		} );

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
		$twDesc.on( 'input.tsfUpdateOgDesc', updateTwDesc );
	}

	return Object.assign( {
		/**
		 * Initialises all aspects of the scripts.
		 * You shouldn't call this.
		 *
		 * @since 3.3.0
		 * @access protected
		 *
		 * @function
		 * @return {undefined}
		 */
		load: () => { }
	}, {
		initTitleInputs,
		initDescriptionInputs,
	}, {
		l10n
	} );
}( jQuery );
jQuery( window.tsfSocial.load );
