/**
 * This file holds Tooltips' code for adding on-hover balloons.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link https://wordpress.org/plugins/autodescription/
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
 * Holds tsfTT (tsf tooltip) values in an object to avoid polluting global namespace.
 *
 * @since 3.1.0
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfTT = function( $ ) {

	/**
	 * Optimized for minification.
	 * @internal
	 */
	const toolTipName         = 'tsf-tooltip';
	const toolTipSelector     = `.${toolTipName}`;
	const toolTipItemName     = 'tsf-tooltip-item';
	const toolTipItemSelector = `.${toolTipItemName}`;
	const toolTipWrapName     = 'tsf-tooltip-wrap';
	const toolTipWrapSelector = `.${toolTipWrapName}`;

	/**
	 * Initializes tooltips.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 1. Now adds default boundary to `wpwrap` instead of `wpcontent`.
	 *              2. Added focus/blur support.
	 * @since
	 * @access private
	 *
	 * @function
	 * @param {(event|undefined)} event
	 * @param {Element} element
	 * @param {string} desc
	 * @return {undefined}
	 */
	const _initToolTips = () => {

		let passiveSupported      = false,
			captureSupported      = false,
			mouseMoveAnimationId  = void 0,
			mousePos              = {},
			activeTooltipElements = {},
			lastAnimationEvent    = void 0;

		/**
		 * Sets passive & capture support flag.
		 * @link https://developer.mozilla.org/en-US/docs/Web/API/EventTarget/addEventListener
		 */
		try {
			( () => {
				const options = {
					get passive() {
						passiveSupported = true;
						return false;
					},
					get capture() {
						captureSupported = true;
						return false;
					},
				};
				// These EventTarget methods will try to get 'passive' and/or 'capture' when it's supported.
				window.addEventListener( 'tsf-tt-test-passive', null, options );
				window.removeEventListener( 'tsf-tt-test-passive', null, options );
			} )();
		} catch ( err ) {
			passiveSupported = false;
			captureSupported = false;
		}

		const setEvents = ( target, unset ) => {

			unset = unset || false;

			let events = {
				mousemove:  mouseMove,
				mouseleave: mouseLeave,
				mouseout:   mouseLeave,
				blur:       mouseLeave,
			};
			let touchEvents = [ 'pointerdown', 'touchstart', 'click' ];

			if ( unset ) {
				for ( const [ event, callBack ] of Object.entries( events ) ) {
					target.removeEventListener( event, callBack );
				}
				touchEvents.forEach( event => {
					document.body.removeEventListener( event, touchRemove );
				} );
			} else {
				for ( const [ event, callBack ] of Object.entries( events ) ) {
					target.addEventListener( event, callBack );
				}
				touchEvents.forEach( event => {
					document.body.addEventListener( event, touchRemove );
				} );
			}

			//= Always set this, as the events may be reintroduced via other code.
			target.addEventListener( 'tsf-tooltip-update', updateDesc );
		}
		const unsetEvents = target => setEvents( target, true );
		const updateDesc = event => {
			if ( event.target.classList.contains( toolTipItemName ) ) {
				let tooltipText = event.target.querySelector( '.tsf-tooltip-text' );
				if ( tooltipText instanceof Element ) {
					tooltipText.innerHTML = event.target.dataset.desc;
					event.target.dispatchEvent( new Event( 'mousemove' ) ); // performance: <.3ms
				}
			}
		}

		const requestMouseMoveAnimation = () => {
			mouseMoveAnimationId = requestAnimationFrame( doMouseMove );
		}
		const cancelMouseMoveAnimation = () => {
			lastAnimationEvent = void 0;
			activeTooltipElements = {
				tooltip: void 0,
				arrow:   void 0,
				wrap:    void 0,
			};
			mousePos = {
				last: { x: void 0 },
				curr: { x: void 0 },
			};
			cancelAnimationFrame( mouseMoveAnimationId );
		}
		const doMouseMove = () => {
			let isMouseEvent = ! [ mousePos.curr.x ].includes( NaN );
			if ( isMouseEvent ) {
				if ( mousePos.curr.x === mousePos.last.x ) {
					requestMouseMoveAnimation();
					return;
				}
			}

			mousePos.last.x = mousePos.curr.x;

			const event = lastAnimationEvent;

			let	tooltip = activeTooltipElements.tooltip || ( event && event.target.querySelector( toolTipSelector ) );

			// Browser lagged, no tooltip exists (yet). Bail.
			if ( ! tooltip ) {
				requestMouseMoveAnimation();
				return;
			}

			if ( ! activeTooltipElements.tooltip ) {
				activeTooltipElements.tooltip = tooltip;
			}
			if ( ! activeTooltipElements.arrow ) {
				activeTooltipElements.arrow = tooltip.querySelector( '.tsf-tooltip-arrow' );
			}
			if ( ! activeTooltipElements.wrap ) {
				activeTooltipElements.wrap = event.target.closest( toolTipWrapSelector ) || event.target.parentNode;
			}

			let pagex         = mousePos.curr.x,
				arrowBoundary = 7,
				arrowWidth    = 16;

			if ( 'focus' === event.type ) {
				// Grab the middle of the item on focus.
				pagex = event.target.getBoundingClientRect().left + ( event.target.offsetWidth / 2 );
			} else if ( isNaN( pagex ) ) {
				// Get the last known tooltip position on manual tooltip alteration.
				pagex = activeTooltipElements.tooltip.dataset.lastPagex || event.target.getBoundingClientRect().left;
			}
			// Keep separate record of pagex, so updateDesc() can utilize this via isNaN hereabove.
			activeTooltipElements.tooltip.dataset.lastPagex = pagex;

			let mousex        = pagex - activeTooltipElements.wrap.getBoundingClientRect().left - ( arrowWidth / 2 ),
				textWrap      = activeTooltipElements.tooltip.querySelector( '.tsf-tooltip-text-wrap' ),
				textWrapWidth = textWrap.offsetWidth,
				adjust        = activeTooltipElements.tooltip.dataset.adjust,
				boundaryRight = textWrapWidth - arrowWidth - arrowBoundary;

			//= mousex is skewed, adjust.
			adjust = parseInt( adjust, 10 );
			adjust = isNaN( adjust ) ? 0 : Math.round( adjust );

			if ( adjust ) {
				mousex = mousex - adjust;

				//= Use textWidth for right boundary if adjustment exceeds.
				if ( boundaryRight + adjust > activeTooltipElements.wrap.offsetWidth ) {
					let innerText = textWrap.querySelector( '.tsf-tooltip-text' ),
						textWidth = innerText.offsetWidth;
					boundaryRight = textWidth - arrowWidth - arrowBoundary;
				}
			}

			if ( mousex <= arrowBoundary ) {
				// Overflown left.
				activeTooltipElements.arrow.style.left = arrowBoundary + "px";
			} else if ( mousex >= boundaryRight ) {
				// Overflown right.
				activeTooltipElements.arrow.style.left = boundaryRight + "px";
			} else {
				//= Somewhere in the middle.
				activeTooltipElements.arrow.style.left = mousex + "px";
			}

			if ( isMouseEvent ) {
				requestMouseMoveAnimation();
			} else {
				cancelMouseMoveAnimation();
			}
		}

		/**
		 * Prevents default click action on a tooltip item.
		 *
		 * Doesn't test whether a tooltip is present, since that happens asynchronously--often (yet _not always_) after the click finishes.
		 * If we set a datapoint where we tell the tooltip is still building, we might be able to read that out (e.g. instigatingTooltip).
		 *
		 * @function
		 * @param {Event} event
		 */
		const preventTooltipHandleClick = event => {
			if ( _isClickLocked( event.target ) ) return;
			event.preventDefault();
			// iOS 12 bug causes two clicks at once. Let's set this asynchronously.
			setTimeout( () => _lockClick( event.target ) );
		}

		const mouseEnter = event => {
			let desc = event.target.dataset.desc || event.target.title || '';

			// Don't create tooltip if bubbled.
			if ( desc && ! event.target.getElementsByClassName( toolTipName ).length ) {
				//= Exchanges data-desc with found desc to sustain easy access.
				event.target.dataset.desc = desc;
				//= Clear title to prevent default browser tooltip.
				event.target.removeAttribute( 'title' );

				doTooltip( event, event.target, desc );
			}
		}
		const mouseMove = event => {
			mousePos.curr.x    = event.pageX || NaN;
			lastAnimationEvent = event;
		}
		const mouseLeave = event => {
			removeTooltip( event.target );
			unsetEvents( event.target );

			cancelMouseMoveAnimation();
		}
		const touchRemove = event => {

			if ( event.target.dataset.hasTooltip ) {
				return;
			}
			if ( event.target instanceof HTMLInputElement && event.target.id ) {
				let labels = document.querySelectorAll( `label[for="${event.target.id}"]` );
				if ( labels.length && [...labels].some( label => label.dataset.hasTooltip ) )
					return;
			}

			let balloonItemToKeep = void 0;

			if ( event.target.classList.contains( toolTipItemName ) ) {
				balloonItemToKeep = event.target;
			}
			if ( ! balloonItemToKeep ) {
				let children = event.target.querySelectorAll( toolTipItemSelector );
				for ( const element of children ) {
					if ( element.querySelector( toolTipSelector ) ) {
						balloonItemToKeep = element;
						break;
					}
				}
			}

			if ( balloonItemToKeep ) {
				//= Remove all but current target.
				for ( const balloon of document.querySelectorAll( toolTipSelector ) ) {
					if ( balloon.closest( toolTipItemSelector ) !== balloonItemToKeep ) {
						removeTooltip( balloon );
						unsetEvents( balloon );
					}
				}
			} else {
				for ( const element of document.querySelectorAll( toolTipItemSelector ) ) {
					removeTooltip( element );
					unsetEvents( element );
				}
				cancelMouseMoveAnimation();
			}
		}

		/**
		 * Loads tooltips within wrapper.
		 * @function
		 * @param {Event} event
		 */
		const loadToolTip = event => {

			if ( event.target.dataset.hasTooltip ) return;

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

			if ( isTouch ) {
				//= Removes possibly previously set items and resets buffer.
				touchRemove( event );
			} else {
				_lockClick( event.target );
			}
			cancelMouseMoveAnimation();

			mouseEnter( event );
			// NOTE: Here we are asynchronous to element-insertion, the browser may not have inserted the tooltip yet.
			//= Initiate arrow placement directly.
			mouseMove( event );
			requestMouseMoveAnimation();

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
		const toolTipHandler = event => {

			if ( instigatingTooltip ) return;

			instigatingTooltip = true;

			if ( event.target.classList.contains( toolTipItemName ) ) {
				loadToolTip( event );
			}
			event.stopPropagation();

			instigatingTooltip = false;
		}

		let initTimeout = void 0;
		const options = passiveSupported || captureSupported ? { capture: true, passive: true } : true;
		/**
		 * Initializes tooltips.
		 * @function
		 */
		const init = () => {
			clearTimeout( initTimeout );
			initTimeout = setTimeout( () => {
				let wraps   = document.querySelectorAll( toolTipWrapSelector ),
					actions = 'mouseenter pointerdown touchstart focus'.split( ' ' );

				for ( let i = 0; i < wraps.length; i++ ) {
					actions.forEach( e => {
						// Redundant https://developer.mozilla.org/en-US/docs/Web/API/EventTarget/addEventListener#Multiple_identical_event_listeners
						// wraps[i].removeEventListener( e, toolTipHandler, options );
						wraps[i].addEventListener( e, toolTipHandler, options );
					} );
					// NOTE: If the tooltip-wrap is a label with a "for"-attribute, the input is forwarded to the <input> field.
					// We mitigated this issue at loadToolTip().
					wraps[i].addEventListener(
						'click',
						preventTooltipHandleClick,
						captureSupported ? { capture: false } : false
					);
				}
			}, 25 ); // Increase this for slow computers? Or allow the loop to be broken from the outside?
		}
		init();
		window.addEventListener( 'tsf-tooltip-reset', init );

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

		element.dataset.hasTooltip = 1;
		if ( element.querySelector( toolTipSelector ) ) removeTooltip( element );

		if ( ! desc.length ) return;

		let tooltip = document.createElement( 'div' );
		tooltip.classList.add( toolTipName );
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

		let hoverItemWrap      = element.closest( toolTipWrapSelector ) || element.parentElement,
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
	 * Prevents click on element.
	 *
	 * @since 4.1.0
	 * @access private
	 * @internal
	 *
	 * @param {Element} element
	 * @return {undefined}
	 */
	const _lockClick = element => {
		if ( ! ( element instanceof Element ) ) return;

		element.dataset.preventedClick = 1;
		// If the element is a label with a "for"-attribute, then we must forward this
		if ( element instanceof HTMLLabelElement && element.htmlFor ) {
			let input = document.getElementById( element.htmlFor );
			if ( input ) input.dataset.preventedClick = 1;
		}
		if ( element instanceof HTMLInputElement && element.id ) {
			document.querySelectorAll( `label[for="${element.id}"]` ).forEach(
				la => { la.dataset.preventedClick = 1; }
			);
		}
	}
	/**
	 * Releases click prevention on element.
	 *
	 * @since 4.1.0
	 * @access private
	 * @internal
	 *
	 * @param {Element} element
	 * @return {undefined}
	 */
	const _releaseClick = element => {
		if ( ! ( element instanceof Element ) ) return;

		delete element.dataset.preventedClick;

		if ( element instanceof HTMLLabelElement && element.htmlFor ) {
			let input = document.getElementById( element.htmlFor );
			if ( input ) delete input.dataset.preventedClick;
		}
		if ( element instanceof HTMLInputElement && element.id ) {
			document.querySelectorAll( `label[for="${element.id}"]` ).forEach(
				la => { delete la.dataset.preventedClick; }
			);
		}
	}
	/**
	 * Tells whether an element should prevent a click.
	 *
	 * @since 4.1.0
	 * @access private
	 * @internal
	 *
	 * @param {Element} element
	 * @return {boolean}
	 */
	const _isClickLocked = element => element instanceof Element && +element.dataset.preventedClick;

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
	const addBoundary = element => element instanceof Element && element.classList.add( 'tsf-tooltip-boundary' );

	/**
	 * Removes the description balloon and arrow from element.
	 *
	 * @since 3.1.0
	 * @since 4.1.0 Now also clears the data of the tooltip.
	 * @access public
	 *
	 * @function
	 * @param {!jQuery|Element|string} element
	 * @return {undefined}
	 */
	const removeTooltip = element => {
		// Backward compatibility for jQuery vs ES.
		if ( element instanceof $ ) {
			element = element.get( 0 );
		}
		if ( element instanceof HTMLElement ) {
			delete element.dataset.hasTooltip;
			_releaseClick( element );
		}
		getTooltip( element ).remove();
	}

	/**
	 * Returns the description balloon node form element.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @function
	 * @param {!jQuery|Element|string} element
	 * @return {jQuery.element}
	 */
	const getTooltip = element => $( element ).find( toolTipSelector ).first();

	/**
	 * Triggers tooltip reset.
	 * This takes .5ms via the event handler thread, feel free to use it whenever.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @function
	 * @return {undefined}
	 */
	const triggerReset = () => {
		window.dispatchEvent( new CustomEvent( 'tsf-tooltip-reset' ) );
	}

	/**
	 * Triggers active tooltip update.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @function
	 * @param {{HTMLElement|undefined}} element
	 * @return {undefined}
	 */
	const triggerUpdate = element => {

		if ( ! element || ! ( element instanceof Element ) )
			element = document.querySelectorAll( toolTipItemSelector );

		if ( ! element ) return;

		const updateEvent = new CustomEvent( 'tsf-tooltip-update' );

		if ( element instanceof Element ) {
			element.dispatchEvent( updateEvent );
		} else if ( element instanceof Nodelist ) {
			element.forEach( el => el.dispatchEvent( updateEvent ) );
		}
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
			document.body.addEventListener( 'tsf-ready', _initToolTips );
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
window.tsfTT.load();
