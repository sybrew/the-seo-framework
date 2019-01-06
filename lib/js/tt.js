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
 * This is a self-constructed function assigned as an object.
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
	 * @access private
	 *
	 * @function
	 * @param {(event|undefined)} event
	 * @param {Element} element
	 * @param {string} desc
	 * @return {undefined}
	 */
	const _initToolTips = () => {

		let touchBuffer = 0,
			inTouchBuffer = false,
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
				$target.off( 'mousemove.tsfTT mouseleave.tsfTT mouseout.tsfTT' );
				$( document.body ).off( touchEvents );
			} else {
				$target.on( {
					'mousemove.tsfTT'  : mouseMove,
					'mouseleave.tsfTT' : mouseLeave,
					'mouseout.tsfTT'   : mouseLeave,
				} );
				$( document.body ).off( touchEvents ).on( touchEvents, touchRemove );
			}

			//= Always set this, as the events might be reintroduced later.
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
			if ( desc && 0 === event.target.getElementsByClassName( 'tsf-tooltip' ).length ) {
				//= Exchanges data-desc with found desc to sustain easy access.
				event.target.dataset.desc = desc;
				//= Clear title to prevent default browser tooltip.
				event.target.title = '';

				doTooltip( event, event.target, desc );
			}
		}
		let lastPageX = NaN;
		const mouseMove = ( event ) => {
			let $target = $( event.target ),
				$tooltip = $target.find( '.tsf-tooltip' ),
				$arrow = $tooltip.find( '.tsf-tooltip-arrow' ),
				pagex = event.originalEvent && event.originalEvent.pageX || event.pageX, // iOS touch support
				arrowBoundary = 7,
				arrowWidth = 16;

			// Keep record of pagex, so updateDesc() can utilize this.
			pagex = isNaN( pagex ) ? lastPageX : pagex;
			lastPageX = pagex;

			let $hoverItemWrap = $target.closest( '.tsf-tooltip-wrap' );

			if ( ! $hoverItemWrap.length )
				$hoverItemWrap = $hoverItem.parent();

			let mousex = pagex - $hoverItemWrap.offset().left - arrowWidth / 2,
				$textWrap = $tooltip.find( '.tsf-tooltip-text-wrap' ),
				textWrapWidth = $textWrap.outerWidth( true ),
				adjust = $tooltip.data( 'adjust' ),
				// adjustDir = $tooltip.data( 'adjustDir' ),
				boundaryRight = textWrapWidth - arrowWidth - arrowBoundary;

			//= mousex is skewed, adjust.
			adjust = parseInt( adjust, 10 );
			adjust = isNaN( adjust ) ? 0 : Math.round( adjust );

			if ( adjust ) {
				mousex = mousex - adjust;

				//= Use textWidth for right boundary if adjustment exceeds.
				if ( boundaryRight + adjust > $hoverItemWrap.outerWidth( true ) ) {
					let $innerText = $textWrap.find( '.tsf-tooltip-text' ),
						textWidth = $innerText.outerWidth( true );
					boundaryRight = textWidth - arrowWidth - arrowBoundary;
				}
			}

			if ( mousex <= arrowBoundary ) {
				//* Overflown left.
				$arrow.css( 'left', arrowBoundary + "px" );
			} else if ( mousex >= boundaryRight ) {
				//* Overflown right.
				$arrow.css( 'left', boundaryRight + "px" );
			} else {
				//= Somewhere in the middle.
				$arrow.css( 'left', mousex + "px" );
			}
		}
		const mouseLeave = ( event ) => {
			//* @see touchRemove
			if ( inTouchBuffer )
				return;

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

			let itemSelector = '.tsf-tooltip-item',
				balloonSelector = '.tsf-tooltip';

			let $target = $( event.target ),
				$keepBalloon;

			if ( $target.hasClass( 'tsf-tooltip-item' ) ) {
				$keepBalloon = $target.find( balloonSelector );
			}
			if ( ! $keepBalloon ) {
				let $children = $target.children( itemSelector );
				if ( $children.length ) {
					$keepBalloon = $children.find( balloonSelector );
				}
			}

			if ( $keepBalloon && $keepBalloon.length ) {
				//= Remove all but this.
				$( balloonSelector ).not( $keepBalloon ).remove();
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

			if ( inTouchBuffer )
				return;

			let isTouch = false;

			switch ( event.type ) {
				case 'mouseenter' :
					//= Most likely, thus placed first.
					break;

				case 'pointerdown' :
				case 'touchstart' :
					isTouch = true;
					break;

				default :
					break;
			}

			//= Removes previous items and sets buffer.
			isTouch && touchRemove( event );

			mouseEnter( event );
			//= Initiate placement directly for Windows Touch or when overflown.
			mouseMove( event );

			// Set other events.
			setEvents( event.target );
		}

		/**
		 * Handles earliest stages of the tooltip.
		 *
		 * @function
		 * @param {Event} event
		 */
		const toolTipHandler = ( event ) => {
			if ( event.target.classList.contains( 'tsf-tooltip-item' ) ) {
				loadToolTip( event );
			}
			event.stopPropagation();
		}

		/**
		 * Initializes tooltips.
		 * @function
		 */
		const init = () => {
			let wraps = document.querySelectorAll( '.tsf-tooltip-wrap' ),
				options = passiveSupported ? { capture: true, passive: true } : true;

			for ( let i = 0; i < wraps.length; i++ ) {
				'mouseenter pointerdown touchstart'.split( ' ' ).forEach( e => {
					wraps[i].removeEventListener( e, toolTipHandler, options );
					wraps[i].addEventListener( e, toolTipHandler, options );
				} );
			}
		}
		init();
		$( window ).on( 'tsf-tooltip-reset', init );

		addBoundary( '#wpcontent' ); //! All pages, but Gutenberg destroys the boundaries..
		addBoundary( '#editor' ); //! Gutenberg
		// if ( tsfL10n && tsf.states && tsf.states.isGutenbergPage ) {
		// 	addBoundary( '.edit-post-layout__metaboxes' ); //! TSF's postbox container... ideally.
		// }
	}

	/**
	 * Outputs tooltip.
	 *
	 * @since 3.1.0
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

		let $hoverItem = $( element );
		if ( $hoverItem.find( '.tsf-tooltip' ).length ) removeTooltip( element );
		if ( ! desc.length ) return;

		let $tooltip = $(
				'<div class="tsf-tooltip"><span class="tsf-tooltip-text-wrap"><span class="tsf-tooltip-text">'
					+ desc +
				'</span></span><div class="tsf-tooltip-arrow"></div></div>'
			);
		$hoverItem.append( $tooltip );

		let $boundary = $hoverItem.closest( '.tsf-tooltip-boundary' );
		$boundary = $boundary.length && $boundary || $( document.body );

		//= arrow (8)
		let tooltipHeight = $hoverItem.outerHeight() + 8,
			tooltipTop = $tooltip.offset().top - tooltipHeight,
			boundaryTop = $boundary.offset().top - ( $boundary.prop( 'scrolltop' ) || 0 );

		if ( boundaryTop > tooltipTop ) {
			$tooltip.addClass( 'tsf-tooltip-down' );
			$tooltip.css( 'top', tooltipHeight + 'px' );
		} else {
			$tooltip.css( 'bottom', tooltipHeight + 'px' );
		}

		let $hoverItemWrap = $hoverItem.closest( '.tsf-tooltip-wrap' );
		if ( ! $hoverItemWrap.length )
			$hoverItemWrap = $hoverItem.parent();

		let $textWrap = $tooltip.find( '.tsf-tooltip-text-wrap' ),
			$innerText = $textWrap.find( '.tsf-tooltip-text' ),
			hoverItemWrapWidth = $hoverItemWrap.width(),
			textWrapWidth = $textWrap.outerWidth( true ),
			textWidth = $innerText.outerWidth( true ),
			borderLeft = $textWrap.offset().left,
			borderRight = borderLeft + textWrapWidth,
			boundaryLeft = $boundary.offset().left - ( $boundary.prop( 'scrollLeft' ) || 0 ),
			boundaryRight = boundaryLeft + $boundary.width();

		let direction = 'left',
			horIndent = NaN;

		if ( borderLeft < boundaryLeft ) {
			//= Overflown over left boundary (likely window)
			//= Add indent relative to boundary. Add 12px for visual appeal.
			horIndent = boundaryLeft - borderLeft + 12;
		} else if ( borderRight > boundaryRight ) {
			//= Overflown over right boundary (likely window)
			//= Add indent relative to boundary minus text wrap width. Add 12px for visual appeal.
			horIndent = boundaryRight - borderLeft - textWrapWidth - 12;
		} else if ( hoverItemWrapWidth < 42 ) {
			//= Small tooltip container. Add indent to make it visually appealing.
			horIndent = -hoverItemWrapWidth * 2 + hoverItemWrapWidth / 2;
			if ( -horIndent > textWrapWidth ) horIndent = -15; // failsafe; this will NEVER trigger.
		} else if ( event && $( event.target ).find( $tooltip ).length < 1 ) {
			//= Manually triggered event that doesn't overflow.
			horIndent = 0;
		} else if ( event && hoverItemWrapWidth > textWrapWidth ) {
			//= Wrap is bigger than tooltip. Adjust accordingly.
			let pagex = event.originalEvent && event.originalEvent.pageX || event.pageX, // iOS touch support,
				hoverItemLeft = $hoverItemWrap.offset().left,
				center = pagex - hoverItemLeft,
				left = center - textWrapWidth / 2,
				right = left + textWrapWidth;

			horIndent = left;

			if ( left < 0 ) {
				//= Don't overflow left.
				horIndent = 0;
			} else if ( right > hoverItemWrapWidth ) {
				//= Don't overflow right.
				//* Use textWidth instead of textWrapWidth as it gets squashed in flex.
				horIndent = hoverItemWrapWidth - textWidth;
			}
		}

		if ( ! isNaN( horIndent ) ) {
			let basis = parseInt( $textWrap.css( 'flex-basis' ), 10 );
			/**
			 * If the overflow is greater than the tooltip flex basis,
			 * the tooltip was grown. Shrink it back to basis and use that.
			 */
			if ( horIndent < -basis ) horIndent = -basis;

			$tooltip.css( direction, horIndent + 'px' );
			$tooltip.data( 'adjust', horIndent );
			$tooltip.data( 'adjustDir', direction );
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

	//? IE11 Object.assign() alternative.
	return $.extend( {
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
		load: function() {
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
//= Run before jQuery.ready() === DOMContentLoaded
jQuery( window.tsfTT.load );
