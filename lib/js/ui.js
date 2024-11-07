/**
 * This file holds UI code for TSF's interface.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link https://wordpress.org/plugins/autodescription/
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds tsfUI (tsf User Interface) values in an object to avoid polluting global namespace.
 *
 * @since 5.1.0
 *
 * @constructor
 */
window.tsfUI = function( $ ) {

	/**
	 * Sets postbox toggle handlers.
	 *
	 * TODO also check for hide-postbox-tog... it prevents the user from saving the page.
	 * TODO also check for the toggle of Gutenberg?
	 *
	 * @since 4.0.0
	 * @since 4.1.0 No longer causes an infinite loop (call stack size excession).
	 * @since 5.1.0 Moved to tsfUI.
	 * @access private
	 */
	function _initPostboxToggle() {

		// Get TSF postboxes. Move this inside of the event for the "dynamic web"?
		let $postboxes = $( '.postbox[id^="autodescription-"], .postbox#tsf-inpost-box' );

		/**
		 * HACK: Reopens a box if it contains invalid input values, and notifies the users thereof.
		 * WordPress should implement this in a non-hacky way, so to give us more freedom.
		 *
		 * Alternatively, we could validate the input and reopen the boxes when the user hits "save".
		 * I do prefer the direct feedback though.
		 *
		 * Note that this event might get deprecated!
		 */
		$( document ).on(
			'postbox-toggled',
			( event, $postbox ) => {
				if ( ! $postbox || ! $postboxes.is( $postbox ) ) return;

				// WordPress bug--they send an array but should've sent it within one.
				// Let's assume they might fix it by converting it to jQuery.
				$postbox = $( $postbox );

				let $input = $postbox.find( 'input:invalid, select:invalid, textarea:invalid' );
				if ( ! $input.length ) return;

				// Defer from event.
				setTimeout( () => {
					if ( $postbox.is( ':hidden' ) ) {
						// Unhide the postbox. Then, loop back to the other parts.
						$( `#${$postbox.attr( 'id' )}-hide` ).trigger( 'click.postboxes' );
					} else {
						if ( $postbox.hasClass( 'closed' ) ) {
							// Reopen self. Loops back to this function.
							$postbox.find( '.hndle, .handlediv' ).first().trigger( 'click.postboxes' );
						} else {
							// Phase 2, this runs after looping back.
							let firstInput = $input.get( 0 );
							if ( $( firstInput ).is( ':visible' ) ) {
								firstInput.reportValidity();
							}
						}
					}
				} );
			},
		);
	}

	/**
	 * Prepares notice dismissal listeners.
	 *
	 * @since 4.1.2
	 * @since 5.1.0 Moved to tsfUI.
	 * @access private
	 */
	function _initNotices() {

		/**
		 * Dismissible notices that use notice wrapper class .tsf-notice.
		 *
		 * @since 2.6.0
		 * @since 2.9.3 Now correctly removes the node from DOM.
		 * @since 4.1.0 1. Now is more in line with how WordPress dismisses notices.
		 *              2. Now also handles dismissible persistent notices.
		 * @since 4.1.2 Moved inside other method.
		 *
		 * @function
		 * @param {Event} event
		 */
		const dismissNotice = event => {

			const notice = event.target.closest( '.tsf-notice' ),
				  key    = event.target.dataset.key,
				  nonce  = event.target.dataset.nonce;

			// Mimics WordPress's jQuery fadeTo.slideUp animation
			notice.style.transformOrigin = 'bottom';
			const animation = notice.animate(
				[
					{ transform: 'scaleY(1)', maxHeight: `${notice.clientHeight}px`, opacity: 1 },
					{ transform: 'scaleY(1)', opacity: 0 },
					{ transform: 'scaleY(0)', maxHeight: 0, paddingTop: 0, paddingBottom: 0, marginTop: 0, marginBottom: 0, opacity: 0 },
				],
				{
					duration: 200,
					iterations: 1,
				},
			);
			animation.onfinish = () => notice.remove();

			if ( key && nonce ) {
				// The notice is removed regardless of this being completed.
				// Do not inform the user of its completion--it adds a lot to the annoyance.
				// Instead, rely on keeping the 'count' low!
				wp.ajax.post(
					'tsf_dismiss_notice',
					{
						tsf_dismiss_key:   key,
						tsf_dismiss_nonce: nonce,
					}
				);
			}
		}

		const reset = () => {
			// Enable dismissal of PHP-inserted notices.
			document.querySelectorAll( '.tsf-dismiss' ).forEach(
				el => { el.addEventListener( 'click', dismissNotice ) }
			);
		}
		/**
		 * @access private Use tsf.triggerNoticeReset() instead.
		 */
		document.body.addEventListener( 'tsf-reset-notice-listeners', reset );
		reset();
	}

	/**
	 * Fades in an element.
	 *
	 * @since 5.1.0
	 * @access public
	 *
	 * @param {Element}  element  The element to affect.
	 * @param {Int}      duration Optional. The animation duration in ms. Default 125ms.
	 * @param {callable} cb       Optional. The callback to run after the animation is done.
	 * @param {Object}   css      Optional. The CSS to apply.
	 * @return {Promise} A resolved promise object.
	 */
	async function fadeIn( element, duration = 125, cb = void 0, css = {} ) {

		css = Object.assign(
			{
				opacity:                 '1',
				animation:               'tsf-fade-in',
				animationDuration:       `${duration}ms`,
				animationTimingFunction: 'cubic-bezier(.54,.12,.90,.60)',
			},
			css,
		);

		for ( const prop in css )
			element.style[ prop ] = css[ prop ];

		const animationTrace = traceAnimation( element );

		await tsfUtils.delay( duration );

		animationTrace.unsetIfUnchanged();

		if ( 'function' === typeof cb )
			(cb)();

		return Promise.resolve();
	}

	/**
	 * Fades out an element.
	 *
	 * @since 5.1.0
	 * @access public
	 *
	 * @param {Element}  element  The element to affect.
	 * @param {Int}      duration Optional. The animation duration in ms. Default 125ms.
	 * @param {callable} cb       Optional. The callback to run after the animation is done.
	 * @param {Object}   css      Optional. The CSS to apply.
	 * @return {Promise} A resolved promise object.
	 */
	function fadeOut( element, duration = 125, cb = void 0, css = {} ) {

		css = Object.assign(
			{
				opacity:   '0',
				animation: 'tsf-fade-out',
			},
			css,
		);

		return fadeIn( element, duration, cb, css );
	}

	/**
	 * Traces and manages animation state for a given element.
	 *
	 * @since 5.1.0
	 * @access public
	 *
	 * @param {HTMLElement} element The DOM element to apply the animation to.
	 * @param {string}      name    Optional. The name of the animation.
	 * @return {{unchanged:function,unsetIfUnchanged:function}}
	 */
	function traceAnimation( element, name ) {

		name ||= element.style.animation ?? '';

		const animation = `${name}:${Date.now()}`;

		element.dataset.tsfIsAnimating = animation;

		return {
			unchanged:        () => animation === element.dataset.tsfIsAnimating,
			unsetIfUnchanged: () => {
				if ( animation === element.dataset.tsfIsAnimating )
					element.style.animation = null;
			}
		};
	}

	return Object.assign( {
		/**
		 * Initialises all aspects of the scripts.
		 * You shouldn't call this.
		 *
		 * @since 5.1.0
		 * @access protected
		 *
		 * @function
		 */
		load: () => {
			document.body.addEventListener( 'tsf-ready', _initPostboxToggle );
			document.body.addEventListener( 'tsf-ready', _initNotices );
		},
	}, {
		/**
		 * Copies internal public functions to tsfUI for public access.
		 * Don't overwrite these.
		 *
		 * @since 5.1.0
		 * @access public
		 */
		fadeIn,
		fadeOut,
		traceAnimation,
	} );
}( jQuery );
window.tsfUI.load();
