/**
 * This file holds Tooltips' code for adding on-hover balloons.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link https://wordpress.org/plugins/autodescription/
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
 * Holds tsfTT (tsf tooltip) values in an object to avoid polluting global namespace.
 *
 * @since 3.1.0
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfTT = function( $ ) {

	/**
	 * Initializes tooltips.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 1. Now adds default boundary to `wpwrap` instead of `wpcontent`.
	 *              2. Added focus/blur support.
	 * @access private
	 *
	 * @function
	 * @param {(event|undefined)} event
	 * @param {Element} element
	 * @param {string} desc
	 * @return {undefined}
	 */
	const _initToolTips = () => {

		let touchBuffer      = 0,
			inTouchBuffer    = false,
			passiveSupported = false;

		/**
		 * Sets passive support flag.
		 * @link https://developer.mozilla.org/en-US/docs/Web/API/EventTarget/addEventListener
		 */
		try {
			let options = Object.defineProperty( {}, 'passive', {
				get: () => { passiveSupported = true; }
			} );
			window.addEventListener( 'tsf-tt-test-passive', options, options )
				.removeEventListener( 'tsf-tt-test-passive', options, options );
		} catch( err ) {}

		const setTouchBuffer = () => {
			inTouchBuffer = true;
			clearTimeout( touchBuffer );
			touchBuffer = setTimeout( () => {
				inTouchBuffer = false;
			}, 250 );
		}

		const setEvents = ( target, unset ) => {

			unset = unset || false;

			let touchEvents = 'pointerdown.tsfTT touchstart.tsfTT click.tsfTT',
				$target = $( target );

			if ( unset ) {
				$target.off( 'mousemove.tsfTT mouseleave.tsfTT mouseout.tsfTT blur.tsfTT' );
				$( document.body ).off( touchEvents );
			} else {
				$target.on( {
					'mousemove.tsfTT':  mouseMove,
					'mouseleave.tsfTT': mouseLeave,
					'mouseout.tsfTT':   mouseLeave,
					'blur.tsfTT'    :   mouseLeave,
				} );
				$( document.body ).off( touchEvents ).on( touchEvents, touchRemove );
			}

			//= Always set this, as the events may be reintroduced via other code.
			$target
				.off( 'tsf-tooltip-update' )
				.on( 'tsf-tooltip-update', updateDesc );
		}
		const unsetEvents = ( target ) => {
			setEvents( target, true );
		}
		const updateDesc = ( event ) => {
			if ( event.target.classList.contains( 'tsf-tooltip-item' ) ) {
				let tooltipText = event.target.querySelector( '.tsf-tooltip-text' );
				if ( tooltipText instanceof Element ) {
					tooltipText.innerHTML = event.target.dataset.desc;
					$( event.target ).trigger( 'mousemove.tsfTT' ); // performance: .3ms
				}
			}
		}
		const mouseEnter = ( event ) => {
			let desc = event.target.dataset.desc || event.target.title || '';

			// Don't create tooltip if bubbled.
			if ( desc && ! event.target.getElementsByClassName( 'tsf-tooltip' ).length ) {
				//= Exchanges data-desc with found desc to sustain easy access.
				event.target.dataset.desc = desc;
				//= Clear title to prevent default browser tooltip.
				event.target.removeAttribute( 'title' );

				doTooltip( event, event.target, desc );
			}
		}
		const mouseMove = ( event ) => {
			let	tooltip = event.target.querySelector( '.tsf-tooltip' );

			// Browser lagged, no tooltip exists (yet). Bail.
			if ( ! tooltip ) return;

			let arrow         = tooltip.querySelector( '.tsf-tooltip-arrow' ),
				pagex         = event && event.pageX || NaN,
				arrowBoundary = 7,
				arrowWidth    = 16;

			if ( event && 'focus' === event.type ) {
				// Grab the middle of the item on focus.
				pagex = event.target.getBoundingClientRect().left + ( event.target.offsetWidth / 2 );
			} else if ( isNaN( pagex ) ) {
				// Get the last known tooltip position on manual tooltip alteration.
				pagex = tooltip.dataset.lastPagex || event.target.getBoundingClientRect().left;
			}

			// Keep record of pagex, so updateDesc() can utilize this.
			tooltip.dataset.lastPagex = pagex;

			let hoverItemWrap = event.target.closest( '.tsf-tooltip-wrap' ) || event.target.parentNode,
				mousex        = pagex - hoverItemWrap.getBoundingClientRect().left - ( arrowWidth / 2 ),
				textWrap      = tooltip.querySelector( '.tsf-tooltip-text-wrap' ),
				textWrapWidth = textWrap.offsetWidth,
				adjust        = tooltip.dataset.adjust,
				boundaryRight = textWrapWidth - arrowWidth - arrowBoundary;

			//= mousex is skewed, adjust.
			adjust = parseInt( adjust, 10 );
			adjust = isNaN( adjust ) ? 0 : Math.round( adjust );

			if ( adjust ) {
				mousex = mousex - adjust;

				//= Use textWidth for right boundary if adjustment exceeds.
				if ( boundaryRight + adjust > hoverItemWrap.offsetWidth  ) {
					let innerText = textWrap.querySelector( '.tsf-tooltip-text' ),
						textWidth = innerText.offsetWidth;
					boundaryRight = textWidth - arrowWidth - arrowBoundary;
				}
			}

			if ( mousex <= arrowBoundary ) {
				//* Overflown left.
				arrow.style.left = arrowBoundary + "px";
			} else if ( mousex >= boundaryRight ) {
				//* Overflown right.
				arrow.style.left = boundaryRight + "px";
			} else {
				//= Somewhere in the middle.
				arrow.style.left = mousex + "px";
			}
		}
		const mouseLeave = ( event ) => {
			//* @see touchRemove
			if ( inTouchBuffer ) return;

			removeTooltip( event.target );
			unsetEvents( event.target );
		}
		/**
		 * ^^^
		 * These two methods conflict eachother in EdgeHTML.
		 * Thusly, touch buffer.
		 * vvv
		 */
		const touchRemove = ( event ) => {

			//* @see mouseLeave
			setTouchBuffer();

			let itemSelector    = '.tsf-tooltip-item',
				balloonSelector = '.tsf-tooltip';

			let balloonToKeep = void 0;

			if ( event.target.classList.contains( 'tsf-tooltip-item' ) ) {
				balloonToKeep = event.target.querySelector( balloonSelector );
			}
			if ( ! balloonToKeep ) {
				let $children = $( event.target ).children( itemSelector );
				if ( $children ) {
					balloonToKeep = $children.find( balloonSelector );
				}
			}

			if ( balloonToKeep ) {
				//= Remove all but this.
				$( balloonSelector ).not( balloonToKeep ).remove();
			} else {
				//= Remove all.
				$( balloonSelector ).remove();
			}
		}

		/**
		 * Loads tooltips within wrapper.
		 * @function
		 * @param {Event} event
		 */
		const loadToolTip = ( event ) => {

			if ( inTouchBuffer ) return;

			let isTouch = false;

			switch ( event.type ) {
				case 'mouseenter':
					//= Most likely, thus placed first.
					break;

				case 'pointerdown':
				case 'touchstart':
					isTouch = true;
					break;

				case 'focus':
				default:
					break;
			}

			//= Removes previous items and sets buffer.
			isTouch && touchRemove( event );

			mouseEnter( event );
			// NOTE: Here we are asynchronous to element-insertion, the browser may not have inserted the tooltip yet.
			//= Initiate arrow placement directly.
			mouseMove( event );

			// Set other events, like removal when tapping elsewhere, or hitting "tab."
			setEvents( event.target );
		}

		let instigatingTooltip = false;
		/**
		 * Handles earliest stages of the tooltip.
		 *
		 * Note to self: Don't debounce using timeouts!
		 * Even at 144hz (7ms) it makes the tt flicker when traveling over the SEO Bar.
		 *
		 * @function
		 * @param {Event} event
		 */
		const toolTipHandler = ( event ) => {

			if ( instigatingTooltip ) return;

			instigatingTooltip = true;

			if ( event.target.classList.contains( 'tsf-tooltip-item' ) ) {
				loadToolTip( event );
			}
			event.stopPropagation();

			instigatingTooltip = false;
		}

		/**
		 * Initializes tooltips.
		 * @function
		 */
		const init = () => {
			let wraps   = document.querySelectorAll( '.tsf-tooltip-wrap' ),
				options = passiveSupported ? { capture: true, passive: true } : true;

			for ( let i = 0; i < wraps.length; i++ ) {
				'mouseenter pointerdown touchstart focus'.split( ' ' ).forEach( e => {
					wraps[i].removeEventListener( e, toolTipHandler, options );
					wraps[i].addEventListener( e, toolTipHandler, options );
				} );
			}
		}
		init();
		$( window ).on( 'tsf-tooltip-reset', init );

		addBoundary( '#wpwrap' ); //! All pages, but Gutenberg destroys the boundaries.. @see tsfGBC
	}

	/**
	 * Outputs tooltip.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 1. Tooltips are now prepended, instead of appended--so they no longer break the order of flow.
	 *                 Careful, however, as some CSS queries may be subjected differently.
	 *              2. Now calculates up/down overflow at the end, so it accounts for squashing and stretching.
	 * @access public
	 *
	 * @function
	 * @param {(event|undefined)} event   Optional. The current mouse/touch event to center
	 *                                    tooltip position for to make it seem more natural.
	 * @param {Element}           element The element to add the tooltip to.
	 * @param {string}            desc    The tooltip, may contain renderable HTML.
	 * @return {undefined}
	 */
	const doTooltip = ( event, element, desc ) => {

		// Backward compatibility for jQuery vs ES.
		if ( element instanceof $ ) {
			element = element.get( 0 );
		}

		if ( element.querySelector( '.tsf-tooltip' ) ) removeTooltip( element );

		if ( ! desc.length ) return;

		let tooltip = document.createElement( 'div' );
		tooltip.classList.add( 'tsf-tooltip' );
		tooltip.insertAdjacentHTML(
			'afterbegin',
			`<span class="tsf-tooltip-text-wrap"><span class="tsf-tooltip-text">${desc}</span></span><div class="tsf-tooltip-arrow" style=will-change:left></div>`
		);

		element.prepend( tooltip );

		let boundary      = element.closest( '.tsf-tooltip-boundary' ) || document.body,
			boundaryRect  = boundary.getBoundingClientRect(),
			boundaryTop   = boundaryRect.top - ( boundary.scrollTop || 0 ),
			boundaryWidth = boundary.offsetWidth,
			maxWidth      = 250, // Gutenberg is 262. The tooltip has 24px padding (12*2)...
			appeal        = 12;

		let hoverItemWrap      = element.closest( '.tsf-tooltip-wrap' ) || element.parentElement,
			hoverItemWrapRect  = hoverItemWrap.getBoundingClientRect(),
			textWrap           = tooltip.querySelector( '.tsf-tooltip-text-wrap' ),
			textWrapRect       = textWrap.getBoundingClientRect(),
			hoverItemWrapWidth = hoverItemWrapRect.width;

		if ( textWrapRect.width > maxWidth && hoverItemWrapWidth < maxWidth && hoverItemWrapWidth > 150 ) {
			// The hoveritemwrap is of an acceptable size. Format it to that.
			textWrap.style.flexBasis = hoverItemWrapWidth + 'px';
		}

		// Calculate the appeal with the spacing.
		if ( textWrap.offsetWidth > ( boundaryWidth - ( appeal / 2 ) ) ) {
			//= Overflown the boundary size. Squeeze the box. (thank you, Gutenberg.)

			// Use the bounding box minus appeal. Don't double the appeal since that'll mess up the arrow.
			// Maximum 250px.
			textWrap.style.flexBasis = Math.min( maxWidth, boundaryWidth - appeal ) + 'px';

			// Halve appeal from here. So each side gets a bit.
			appeal /= 2;
		} else if ( textWrapRect.width > maxWidth ) {
			// Resize the text wrap if it exceeds 250px on auto-grow.
			textWrap.style.flexBasis = maxWidth + 'px';
		}

		let boundaryLeft  = boundaryRect.left - ( boundary.scrollLeft || 0 ),
			boundaryRight = boundaryLeft + boundaryWidth;

		let textWrapWidth   = textWrap.offsetWidth,
			textBorderLeft  = textWrapRect.left,
			textBorderRight = textBorderLeft + textWrapWidth;

		let horIndent = 0;

		if ( textBorderLeft < boundaryLeft ) {
			//= Overflown over left boundary (likely window)
			//= Add indent relative to boundary.
			horIndent = boundaryLeft - textBorderLeft + appeal;
		} else if ( textBorderRight > boundaryRight ) {
			//= Overflown over right boundary (likely window)
			//= Add indent relative to boundary minus text wrap width.
			horIndent = boundaryRight - textBorderLeft - textWrapWidth - appeal;
		} else if ( hoverItemWrapWidth < 42 ) {
			//= Small tooltip container. Add indent relative to the item to make it visually appealing.
			horIndent = ( -hoverItemWrapWidth / 2 ) - appeal;
		} else if ( hoverItemWrapWidth > textWrapWidth ) {
			//= Wrap is larger than tooltip. Find middle of pointer (if any) and adjust accordingly.
			let pagex = event && event.pageX || NaN;

			if ( event && 'focus' === event.type ) {
				// No pointer-event found. Set indent to the middle instead.
				horIndent = ( hoverItemWrapWidth / 2 ) - ( textWrapWidth / 2 );
			} else if ( isNaN( pagex ) ) {
				horIndent = -appeal;
			} else {
				// Set to middle of pointer.
				horIndent = pagex - hoverItemWrapRect.left - ( textWrapWidth / 2 );
			}

			let appealLeft  = -appeal,
				appealRight = hoverItemWrapWidth - textWrapWidth + appeal;

			if ( horIndent < appealLeft ) {
				//= Overflown left more than appeal, let's move it more over the hoverwrap.
				horIndent = appealLeft;
			}
			if ( horIndent > appealRight ) {
				//= Overflown right more than appeal, let's move it more over the hoverwrap.
				horIndent = appealRight;
			}
		}

		if ( ( horIndent + textBorderLeft ) < ( boundaryLeft + appeal ) ) {
			// Overflows left boundary. Use half appeal to account for bordered tooltip items.
			let _adjustLeft = ( horIndent + textBorderLeft ) - ( boundaryLeft + ( appeal / 2 ) );
			horIndent = horIndent - _adjustLeft;
		}
		if ( ( horIndent + textBorderRight ) > ( boundaryRight + appeal ) ) {
			// Overflows right boundary. Use half appeal to account for bordered tooltip items.
			let _adjustRight = ( horIndent + textBorderRight ) - ( boundaryRight + ( appeal / 2 ) );
			horIndent = horIndent - _adjustRight;
		}
		if ( ( horIndent + textBorderLeft ) < boundaryLeft ) {
			// It failed again after alignment. Reset to 0.
			horIndent = 0;
		}

		if ( ! event ) {
			let basis = parseInt( textWrap.style.flexBasis, 10 );
			/**
			 * If the indent overflow is greater than the tooltip flex basis,
			 * the tooltip was repainted and shrunk. It may shrink beyond the horIndent,
			 * causing a misplaced box; so, we replace that with the basis.
			 * This can happen when no pointer event is assigned, like via updateDesc().
			 */
			if ( horIndent < -basis ) {
				horIndent = -basis;
			}
		}

		tooltip.style.left     = horIndent + 'px';
		tooltip.dataset.adjust = horIndent;

		// Finally, see if the tooltip overflows top or bottom. We need to do this last as the tooltip may be squashed upward.
		// arrow is 8 high, add that to the total height.
		let tooltipHeight = element.offsetHeight + 8,
			tooltipTop    = tooltip.getBoundingClientRect().top - tooltipHeight;

		if ( boundaryTop > tooltipTop ) {
			tooltip.classList.add( 'tsf-tooltip-down' );
			tooltip.style.top = tooltipHeight + 'px';
		} else {
			tooltip.style.bottom = tooltipHeight + 'px';
		}
	}

	/**
	 * Adds tooltip boundaries.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @function
	 * @param {!jQuery|Element|string} element The jQuery element, DOM Element or query selector.
	 * @return {undefined}
	 */
	const addBoundary = ( element ) => {
		$( element ).addClass( 'tsf-tooltip-boundary' );
	}

	/**
	 * Removes the description balloon and arrow from element.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @function
	 * @param {Element} element
	 * @return {undefined}
	 */
	const removeTooltip = ( element ) => {
		getTooltip( element ).remove();
	}

	/**
	 * Returns the description balloon node form element.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @function
	 * @param {Element} element
	 * @return {jQuery.element}
	 */
	const getTooltip = ( element ) => {
		return $( element ).find( '.tsf-tooltip' ).first();
	}

	/**
	 * Triggers tooltip reset.
	 * This takes .5ms via the event handler thread, feel free to use it whenever.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	const triggerReset = () => {
		$( window ).trigger( 'tsf-tooltip-reset' );
	}

	/**
	 * Triggers active tooltip update.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @function
	 * @param {Element} item
	 * @return {(undefined|null)}
	 */
	const triggerUpdate = ( item ) => {
		$( item || '.tsf-tooltip-item' ).trigger( 'tsf-tooltip-update' );
	}

	return Object.assign( {
		/**
		 * Initialises all aspects of the scripts.
		 * You shouldn't call this.
		 *
		 * @since 3.1.0
		 * @access protected
		 *
		 * @function
		 * @return {undefined}
		 */
		load: () => {
			$( document.body ).ready( _initToolTips );
		}
	}, {
		/**
		 * Copies internal public functions to tsfTT for public access.
		 * Don't overwrite these.
		 *
		 * @since 3.1.0
		 * @access public
		 */
 		doTooltip,
 		removeTooltip,
 		getTooltip,
 		addBoundary,
 		triggerReset,
 		triggerUpdate,
	} );
}( jQuery );
jQuery( window.tsfTT.load );
