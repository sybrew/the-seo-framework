/**
 * This file holds The SEO Framework plugin's JS code for pixel and character counters.
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
 * Holds tsfC values in an object to avoid polluting global namespace.
 *
 * @since 4.0.0
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfC = function( $ ) {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 4.0.0
	 * @access public
	 * @type {(Object<string, *>)|boolean|null} l10n Localized strings
	 */
	const l10n = 'undefined' !== typeof tsfCL10n && tsfCL10n;

	/**
	 * The current counter type.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @type {Number} countertype The counterType
	 */
	let counterType = +( l10n.counterType || 0 );

	/**
	 * Returns the counter type.
	 *
	 * @since 4.0.0
	 * @access public
	 * @see counterClasses
	 *
	 * @function
	 * @return {Number} The counter type
	 */
	const getCounterType = () => counterType;

	/**
	 * The current character counter classes.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @type {(Object<number,string>)} counterClasses
	 */
	const counterClasses = {
		0: 'tsf-counter-zero',
		1: 'tsf-counter-one',
		2: 'tsf-counter-two',
		3: 'tsf-counter-three',
	};

	/**
	 * Updates character counter.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @param {Object} test
	 * @return {undefined}
	 */
	const updateCharacterCounter = ( test ) => {

		let el         = test.e,
			text       = tsf.decodeEntities( test.text ),
			guidelines = l10n.guidelines[ test.field ][ test.type ].chars;

		let testLength = tsf.getStringLength( text ),
			newClass = '',
			exclaimer = '';

		let classes = {
			bad:     'tsf-count-bad',
			okay:    'tsf-count-okay',
			good:    'tsf-count-good',
			unknown: 'tsf-count-unknown',
		};

		if ( ! testLength ) {
			newClass  = classes.unknown;
			exclaimer = l10n.i18n.guidelines.short.empty;
		} else if ( testLength < guidelines.lower ) {
			newClass  = classes.bad;
			exclaimer = l10n.i18n.guidelines.short.farTooShort;
		} else if ( testLength < guidelines.goodLower ) {
			newClass  = classes.okay;
			exclaimer = l10n.i18n.guidelines.short.tooShort;
		} else if ( testLength > guidelines.upper ) {
			newClass  = classes.bad;
			exclaimer = l10n.i18n.guidelines.short.farTooLong;
		} else if ( testLength > guidelines.goodUpper ) {
			newClass  = classes.okay;
			exclaimer = l10n.i18n.guidelines.short.tooLong;
		} else {
			//= between goodUpper and goodLower.
			newClass  = classes.good;
			exclaimer = l10n.i18n.guidelines.short.good;
		}

		switch ( counterType ) {
			case 3:
				exclaimer = testLength.toString() + ' - ' + exclaimer;
				break;
			case 2:
				// 2 uses exclaimer as-is.
				break;
			case 1:
			default:
				exclaimer = testLength.toString();
				break;
		}

		el.innerHTML = exclaimer;

		for ( let _c in classes ) {
			el.classList.remove( classes[ _c ] );
		}
		for ( let _c in counterClasses ) {
			el.classList.remove( counterClasses[ _c ] );
		}
		el.classList.add( newClass );
		el.classList.add( counterClasses[ counterType ] );
	}

	/**
	 * Updates pixel counter.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @function
	 * @param {Object} test
	 * @return {undefined}
	 */
	const updatePixelCounter = ( test ) => {

		let el         = test.e,
			text       = tsf.decodeEntities( test.text ),
			guidelines = l10n.guidelines[ test.field ][ test.type ].pixels;

		let wrap = el.parentElement;

		if ( ! wrap )
			return;

		let bar    = wrap.querySelector( '.tsf-pixel-counter-bar' ),
			shadow = wrap.querySelector( '.tsf-pixel-counter-shadow' );

		if ( ! bar || ! shadow )
			return;

		shadow.innerHTML = tsf.escapeString( text );

		let testWidth = shadow.offsetWidth,
			newClass = '',
			newWidth = '',
			guidelineHelper = '';

		let classes = {
			bad:     'tsf-pixel-counter-bad',
			okay:    'tsf-pixel-counter-okay',
			good:    'tsf-pixel-counter-good',
			unknown: 'tsf-pixel-counter-unknown',
		};

		//= Can never be over 100. Good.
		newWidth = ( testWidth / guidelines.goodUpper * 100 ) + '%';

		if ( ! testWidth ) {
			newClass = classes.unknown;
			newWidth = '100%'; // It's 100% unknown, not 0%.
			guidelineHelper = l10n.i18n.guidelines.long.empty;
		} else if ( testWidth < guidelines.lower ) {
			newClass = classes.bad;
			guidelineHelper = l10n.i18n.guidelines.long.farTooShort;
		} else if ( testWidth < guidelines.goodLower ) {
			newClass = classes.okay;
			guidelineHelper = l10n.i18n.guidelines.long.tooShort;
		} else if ( testWidth > guidelines.upper ) {
			//= Can never be 0. Good. Add 2/3rds of difference to it; implying emphasis.
			newWidth = ( guidelines.upper / ( testWidth + ( ( testWidth - guidelines.upper ) * 2 / 3 ) ) * 100 ) + '%';
			newClass = classes.bad;
			guidelineHelper = l10n.i18n.guidelines.long.farTooLong;
		} else if ( testWidth > guidelines.goodUpper ) {
			newClass = classes.okay;
			guidelineHelper = l10n.i18n.guidelines.long.tooLong;
			newWidth = '100%'; // Let's just assume someone will break this otherwise.
		} else {
			//= between goodUpper and goodLower.
			newClass = classes.good;
			guidelineHelper = l10n.i18n.guidelines.long.good;
		}

		let sub = bar.querySelector( '.tsf-pixel-counter-fluid' ),
			label;

		label = l10n.i18n.pixelsUsed.replace( /%1\$d/g, testWidth );
		label = label.replace( /%2\$d/g, guidelines.goodUpper );

		label = label + '<br>' + guidelineHelper;

		//= IE11 compat... great. Spread syntax please :)
		for ( let _c in classes ) {
			bar.classList.remove( classes[ _c ] );
		}

		// Set visuals.
		bar.classList.add( newClass );
		sub.style.width = newWidth;

		// Update tooltip and ARIA label.
		bar.dataset.desc = label;
		// Replace HTML with spaces. TODO see TSF's PHP-code `strip_tags_cs()` for a better solution.
		// NOTE: Screen readers don't always read out HTML entities as intended. They should fix that, not us, as it's an escaping issue.
		bar.setAttribute( 'aria-label', tsf.escapeString( label.replace( /(<([^>]+)?>?)/ig, ' ' ) ) );

		tsfTT.triggerUpdate( bar );
	}

	/**
	 * Triggers counter update event.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @function
	 * @return {undefined}
	 */
	const triggerCounterUpdate = () => {
		$( window ).trigger( 'tsf-counter-updated' );
	}

	/**
	 * Updated counter type variable and triggers a global event.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @param {(undefined|boolean)} countUp Whether to add one.
	 * @return {undefined}
	 */
	const updateCounterClasses = ( countUp ) => {

		if ( countUp ) ++counterType;

		// Reset to 0 if needed. We have 4 options: 0, 1, 2, 3
		if ( counterType > 3 )
			counterType = 0;

		triggerCounterUpdate();
	}

	/**
	 * Updates the counter type.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @param {!jQuery.Event} event
	 * @return {undefined}
	 */
	const _counterUpdate = ( event ) => {

		//* Update counters locally, and add a number.
		//! We don't want this to be promised after the AJAX call, that'll resolve separately.
		updateCounterClasses( true );

		let target = '.tsf-counter-wrap .tsf-ajax',
			status = 0;

		//* Reset ajax loader
		tsf.resetAjaxLoader( target );

		//* Set ajax loader.
		tsf.setAjaxLoader( target );

		//* Setup external update.
		let settings = {
			method: 'POST',
			url: ajaxurl,
			datatype: 'json',
			data: {
				action: 'the_seo_framework_update_counter',
				nonce:  tsf.l10n.nonces.edit_posts,
				val:    counterType,
			},
			async: true,
			success: response => {

				response = tsf.convertJSONResponse( response );

				//* I could do value check, but that will simply lag behind. Unless an annoying execution delay is added.
				if ( 'success' === response.type )
					status = 1;

				switch ( status ) {
					case 0:
						tsf.unsetAjaxLoader( target, false );
						break;
					case 1:
						tsf.unsetAjaxLoader( target, true );
						break;
					default:
						tsf.resetAjaxLoader( target );
						break;
				}
			},
			error: () => {
				tsf.unsetAjaxLoader( target, false );
			}
		}

		$.ajax( settings );
	}

	/**
	 * Resets counter a11y onClick listener.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @function
	 * @return {jQuery}
	 */
	const resetCounterListener = () => $( '.tsf-counter' ).off( 'click.tsfC' ).on( 'click.tsfC', _counterUpdate );

	/**
	 * Initializes counters.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initCounters = () => {

		// Any edit screen
		resetCounterListener();

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
		load: () => {
			$( document.body ).on( 'tsf-onload', _initCounters );
		}
	}, {
		updatePixelCounter,
		updateCharacterCounter,
		triggerCounterUpdate,
		resetCounterListener,
		getCounterType,
	}, {
		counterClasses,
		l10n,
	} );
}( jQuery );
jQuery( window.tsfC.load );
