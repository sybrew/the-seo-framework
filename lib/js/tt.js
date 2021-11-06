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
 */
window.tsfTT = function() {

	const _ttBase = 'tsf-tooltip';
	const ttNames = {
		base:     _ttBase,
		item:     `${_ttBase}-item`,
		wrap:     `${_ttBase}-wrap`,
		text:     `${_ttBase}-text`,
		textWrap: `${_ttBase}-text-wrap`,
		boundary: `${_ttBase}-boundary`,
		arrow:    `${_ttBase}-arrow`,
	}
	// Yes, I'm too lazy to copy/paste whatever's above again, so I spent half an hour figuring this.
	const ttSelectors = Object.fromEntries( Object.entries( ttNames ).map( ( [ i, v ] ) => [ i, `.${v}` ] ) );

	const _activeToolTipHandles = {
		updateDesc: event => {
			if ( ! event.target.classList.contains( ttNames.item ) ) return;

			let tooltipText = event.target.querySelector( ttSelectors.text );
			if ( tooltipText instanceof Element ) {
				tooltipText.innerHTML = event.target.dataset.desc;
				event.target.dispatchEvent( new Event( 'mousemove' ) ); // performance: <.3ms
			}
		},
		pointerEnter: async event => {
			let desc = event.target.dataset.desc || event.target.title || '';

			// Don't create tooltip if bubbled.
			if ( desc && ! event.target.getElementsByClassName( ttNames.base ).length ) {
				// Exchanges data-desc with found desc to sustain easy access.
				event.target.dataset.desc = desc;
				// Clear title to prevent default browser tooltip.
				event.target.removeAttribute( 'title' );

				return await doTooltip( event, event.target, desc );
			}

			return false;
		},
		pointerMove: event => {
			_pointer.currPos.x     = event.pageX || NaN;
			_pointer.lastMoveEvent = event;
		},
		pointerLeave: event => {
			removeTooltip( event.target );
			_events( event.target ).unset();

			// Simply continue the animation if we continue onto another tooltip item.
			// If relatedTarget doesn't exist, also cancel.
			if ( ! event.relatedTarget?.classList?.contains( ttNames.item ) )
				_cancelArrowAnimation();
		},
	}

	const _events = target => {
		const commonEvents = {
			mousemove:  _activeToolTipHandles.pointerMove,
			mouseleave: _activeToolTipHandles.pointerLeave,
			mouseout:   _activeToolTipHandles.pointerLeave,
			blur:       _activeToolTipHandles.pointerLeave,
		};

		return {
			set: () => {
				for ( const [ event, callBack ] of Object.entries( commonEvents ) ) {
					target.addEventListener( event, callBack );
				}

				target.addEventListener( 'tsf-tooltip-update', _activeToolTipHandles.updateDesc );
			},
			unset: () => {
				for ( const [ event, callBack ] of Object.entries( commonEvents ) ) {
					target.removeEventListener( event, callBack );
				}
			},
		};
	}

	const _activeTooltipElements = {
		tooltip: void 0,
		arrow:   void 0,
		wrap:    void 0,
		reset:   () => {
			_activeTooltipElements.tooltip = _activeTooltipElements.arrow = _activeTooltipElements.wrap = void 0;
		}
	};
	const _pointer = {
		lastPos:       { x: void 0 },
		currPos:       { x: void 0 },
		lastMoveEvent: void 0,
		reset:         () => {
			_pointer.lastMoveEvent = void 0;
			// Before and after should have objects assigned separately.
			// For otherwise they get the same pointer. Yes, a memory pointer: reference.
			_pointer.currPos = { x: void 0 };
			_pointer.lastPos = { x: void 0 };
		}
	}

	const {
		_requestArrowAnimation,
		_cancelArrowAnimation,
		_requestArrowAnimationOnce,
	} = ( () => {
		let _pointerAnimationId = void 0;

		const _requestArrowAnimation = () => {
			_pointerAnimationId = requestAnimationFrame( animate );
		}

		const _cancelArrowAnimation = () => {
			cancelAnimationFrame( _pointerAnimationId );
			_pointer.lastMoveEvent = void 0;
			_activeTooltipElements.reset();
			_pointer.reset();
		}

		const _requestArrowAnimationOnce = () => {
			animate();
			_cancelArrowAnimation();
		}

		const animate = () => {
			let isMouseEvent = ! [ _pointer.currPos.x ].includes( NaN );

			if ( isMouseEvent ) {
				if ( _pointer.currPos.x === _pointer.lastPos.x ) {
					_requestArrowAnimation();
					return;
				}
			}

			_pointer.lastPos.x = _pointer.currPos.x;

			const event = _pointer.lastMoveEvent;

			let	tooltip = _activeTooltipElements.tooltip || ( event && event.target.querySelector( ttSelectors.base ) );

			// Browser lagged, no tooltip exists (yet). Bail.
			if ( ! tooltip ) {
				_requestArrowAnimation();
				return;
			}

			_activeTooltipElements.tooltip ||= tooltip;
			_activeTooltipElements.arrow   ||= tooltip.querySelector( ttSelectors.arrow );
			_activeTooltipElements.wrap    ||= event.target.closest( ttSelectors.wrap ) || event.target.parentNode;

			let pagex         = _pointer.currPos.x,
				arrowBoundary = 7,
				arrowWidth    = 16;

			if ( 'focus' === event.type ) {
				// Grab the middle of the item on focus.
				pagex = event.target.getBoundingClientRect().left + ( event.target.offsetWidth / 2 );
			} else if ( isNaN( pagex ) ) {
				// Get the last known tooltip position on manual tooltip alteration.
				pagex = _activeTooltipElements.tooltip.dataset.lastPagex || event.target.getBoundingClientRect().left;
			}
			// Keep separate record of pagex, so updateDesc() can utilize this via isNaN hereabove.
			_activeTooltipElements.tooltip.dataset.lastPagex = pagex;

			let mousex        = pagex - _activeTooltipElements.wrap.getBoundingClientRect().left - ( arrowWidth / 2 ),
				textWrap      = _activeTooltipElements.tooltip.querySelector( ttSelectors.textWrap ),
				textWrapWidth = textWrap.offsetWidth,
				adjust        = _activeTooltipElements.tooltip.dataset.adjust,
				boundaryRight = textWrapWidth - arrowWidth - arrowBoundary;

			// mousex is skewed, adjust.
			adjust = parseInt( adjust, 10 );
			adjust = isNaN( adjust ) ? 0 : Math.round( adjust );

			if ( adjust ) {
				mousex = mousex - adjust;

				// Use textWidth for right boundary if adjustment exceeds.
				if ( boundaryRight + adjust > _activeTooltipElements.wrap.offsetWidth ) {
					let innerText = textWrap.querySelector( ttSelectors.text ),
						textWidth = innerText.offsetWidth;
					boundaryRight = textWidth - arrowWidth - arrowBoundary;
				}
			}

			if ( mousex <= arrowBoundary ) {
				// Overflown left.
				_activeTooltipElements.arrow.style.left = `${arrowBoundary}px`;
			} else if ( mousex >= boundaryRight ) {
				// Overflown right.
				_activeTooltipElements.arrow.style.left = `${boundaryRight}px`;
			} else {
				// Somewhere in the middle.
				_activeTooltipElements.arrow.style.left = `${mousex}px`;
			}

			if ( isMouseEvent ) {
				_requestArrowAnimation();
			} else {
				_pointerAnimationId && _cancelArrowAnimation();
			}
		}

		return {
			_requestArrowAnimation,
			_cancelArrowAnimation,
			_requestArrowAnimationOnce,
		};
	} )();

	const _clickLocker = element => {
		return {
			lock: () => {
				element.dataset.preventedClick = 1;

				// If the element is a label with a "for"-attribute, then we must forward this
				if ( element instanceof HTMLLabelElement && element.htmlFor ) {
					let input = document.getElementById( element.htmlFor );
					if ( input ) input.dataset.preventedClick = 1;
				}
				if ( element instanceof HTMLInputElement && element.id ) {
					document.querySelectorAll( `label[for="${element.id}"]` ).forEach(
						label => { label.dataset.preventedClick = 1; }
					);
				}
			},
			release: () => {
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
			},
			isLocked: () => element instanceof Element && !!+element.dataset.preventedClick,
		}
	}

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
	 * @param {event?} event
	 * @param {Element} element
	 * @param {string} desc
	 */
	const _initToolTips = () => {

		// TODO move this test to the main tsf object? This whole file doesn't rely on `window.tsf` though.
		let passiveSupported = false,
			captureSupported = false;
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
		} catch ( e ) {
			passiveSupported = false;
			captureSupported = false;
		}

		/**
		 * Loads tooltips within wrapper.
		 * @function
		 * @param {Event} event
		 */
		const loadToolTip = async ( event ) => {

			if ( event.target.dataset.hasTooltip ) return;

			let isTouch = false;

			switch ( event.type ) {
				case 'mouseenter':
					// Most likely, thus placed first.
					break;

				case 'pointerdown':
				case 'touchstart':
					isTouch = true;
					break;

				case 'focus':
				default:
					break;
			}

			if ( ! isTouch )
				_clickLocker( event.target ).lock();

			_cancelArrowAnimation();

			if ( ! ( await _activeToolTipHandles.pointerEnter( event ) ) ) return;

			// Initiate arrow placement directly.
			_activeToolTipHandles.pointerMove( event );

			if ( isTouch ) {
				_requestArrowAnimationOnce();
			} else {
				_requestArrowAnimation();
			}

			// Set other events, like removal when tapping elsewhere, or hitting "tab."
			_events( event.target ).set();
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
			if ( _clickLocker( event.target ).isLocked() ) return;
			event.preventDefault();
			// iOS 12 bug causes two clicks at once. Let's set this asynchronously.
			setTimeout( () => _clickLocker( event.target ).lock() );
		}

		let instigatingTooltip = false;
		/**
		 * Handles earliest stages of the tooltip.
		 *
		 * Note to self: Don't debounce using timeouts!
		 * Even at 144hz (7ms) it makes the tt flicker obviously when traveling over the SEO Bar.
		 *
		 * @function
		 * @param {Event} event
		 */
		const handleToolTip = event => {

			if ( instigatingTooltip ) return;

			instigatingTooltip = true;

			if ( event.target.classList.contains( ttNames.item ) )
				loadToolTip( event );

			event.stopPropagation();

			instigatingTooltip = false;
		}

		let initTimeout = void 0;
		const options = passiveSupported && captureSupported ? { capture: true, passive: true } : true;
		/**
		 * Initializes tooltips.
		 * @function
		 */
		const init = () => {
			let wraps   = document.querySelectorAll( ttSelectors.wrap ),
				actions = 'mouseenter pointerdown touchstart focus'.split( ' ' );

			for ( let i = 0; i < wraps.length; i++ ) {
				actions.forEach( e => {
					wraps[ i ].addEventListener( e, handleToolTip, options );
				} );
				// NOTE: If the tooltip-wrap is a label with a "for"-attribute, the input is forwarded to the <input> field.
				// We mitigated this issue at loadToolTip().
				wraps[ i ].addEventListener(
					'click',
					preventTooltipHandleClick,
					captureSupported ? { capture: false } : false
				);
			}
		}
		window.addEventListener( 'tsf-tooltip-reset', init );
		triggerReset();

		addBoundary( '#wpwrap' ); //! All pages, but Gutenberg destroys the boundaries.. @see tsfGBC
	}

	/**
	 * Renders tooltip.
	 *
	 * @since 4.2.0
	 * @access private
	 *
	 * @function
	 * @param {event?}  event   Optional. The current mouse/touch event to center
	 *                                    tooltip position for to make it seem more natural.
	 * @param {Element} element The element to add the tooltip to.
	 * @param {string}  desc    The tooltip, may contain renderable HTML.
	 * @return {Boolean} True on success, false otherwise.
	 */
	const _renderTooltip = ( event, element, desc ) => {

		element.dataset.hasTooltip = 1;

		let tooltip = document.createElement( 'div' );

		tooltip.classList.add( ttNames.base );
		tooltip.insertAdjacentHTML(
			'afterbegin',
			`<span class=${ttNames.textWrap}><span class=${ttNames.text}>${desc}</span></span><div class=${ttNames.arrow} style=will-change:left></div>`
		);

		element.prepend( tooltip );

		let boundary      = element.closest( ttSelectors.boundary ) || document.body,
			boundaryRect  = boundary.getBoundingClientRect(),
			boundaryTop   = boundaryRect.top - ( boundary.scrollTop || 0 ),
			boundaryWidth = boundary.offsetWidth,
			maxWidth      = 250, // Gutenberg is 262. The tooltip has 24px padding (12*2)...
			appeal        = 12;

		let hoverItemWrap      = element.closest( ttSelectors.wrap ) || element.parentElement,
			hoverItemWrapRect  = hoverItemWrap.getBoundingClientRect(),
			textWrap           = tooltip.querySelector( ttSelectors.textWrap ),
			textWrapRect       = textWrap.getBoundingClientRect(),
			hoverItemWrapWidth = hoverItemWrapRect.width;

		if ( textWrapRect.width > maxWidth && hoverItemWrapWidth < maxWidth && hoverItemWrapWidth > 150 ) {
			// The hoveritemwrap is of an acceptable size. Format it to that.
			textWrap.style.flexBasis = `${hoverItemWrapWidth}px`;
		}

		// Calculate the appeal with the spacing.
		if ( textWrap.offsetWidth > ( boundaryWidth - ( appeal / 2 ) ) ) {
			// Overflown the boundary size. Squeeze the box. (thank you, Gutenberg.)

			// Use the bounding box minus appeal. Don't double the appeal since that'll mess up the arrow.
			// Maximum 250px.
			textWrap.style.flexBasis = `${Math.min( maxWidth, boundaryWidth - appeal )}px`;

			// Halve appeal from here. So each side gets a bit.
			appeal /= 2;
		} else if ( textWrapRect.width > maxWidth ) {
			// Resize the text wrap if it exceeds 250px on auto-grow.
			textWrap.style.flexBasis = `${maxWidth}px`;
		}

		let boundaryLeft  = boundaryRect.left - ( boundary.scrollLeft || 0 ),
			boundaryRight = boundaryLeft + boundaryWidth;

		let textWrapWidth   = textWrap.offsetWidth,
			textBorderLeft  = textWrapRect.left,
			textBorderRight = textBorderLeft + textWrapWidth;

		let horIndent = 0;

		if ( textBorderLeft < boundaryLeft ) {
			// Overflown over left boundary (likely window)
			// Add indent relative to boundary.
			horIndent = boundaryLeft - textBorderLeft + appeal;
		} else if ( textBorderRight > boundaryRight ) {
			// Overflown over right boundary (likely window)
			// Add indent relative to boundary minus text wrap width.
			horIndent = boundaryRight - textBorderLeft - textWrapWidth - appeal;
		} else if ( hoverItemWrapWidth < 42 ) {
			// Small tooltip container. Add indent relative to the item to make it visually appealing.
			horIndent = ( -hoverItemWrapWidth / 2 ) - appeal;
		} else if ( hoverItemWrapWidth > textWrapWidth ) {
			// Wrap is larger than tooltip. Find middle of pointer (if any) and adjust accordingly.
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
				// Overflown left more than appeal, let's move it more over the hoverwrap.
				horIndent = appealLeft;
			}
			if ( horIndent > appealRight ) {
				// Overflown right more than appeal, let's move it more over the hoverwrap.
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

		tooltip.style.left     = `${horIndent}px`;
		tooltip.dataset.adjust = horIndent;

		// Finally, see if the tooltip overflows top or bottom. We need to do this last as the tooltip may be squashed upward.
		// arrow is 8 high, add that to the total height.
		let tooltipHeight = element.offsetHeight + 8,
			tooltipTop    = tooltip.getBoundingClientRect().top - tooltipHeight;

		if ( boundaryTop > tooltipTop ) {
			tooltip.classList.add( 'tsf-tooltip-down' );
			tooltip.style.top = `${tooltipHeight}px`;
		} else {
			tooltip.style.bottom = `${tooltipHeight}px`;
		}

		return true;
	}

	/**
	 * Outputs tooltip.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 1. Tooltips are now prepended, instead of appended--so they no longer break the order of flow.
	 *                 Careful, however, as some CSS queries may be subjected differently.
	 *              2. Now calculates up/down overflow at the end, so it accounts for squashing and stretching.
	 * @since 4.2.0 1. Is now asynchronous.
	 *              2. Now returns boolean whether the tooltip was entered successfully.
	 *              3. Now removes all other tooltips. Only one may prevail!
	 * @access public
	 *
	 * @function
	 * @param {event?}  event   Optional. The current mouse/touch event to center
	 *                                    tooltip position for to make it seem more natural.
	 * @param {Element} element The element to add the tooltip to.
	 * @param {string}  desc    The tooltip, may contain renderable HTML.
	 * @return {Promise<Boolean>} True on success, false otherwise.
	 */
	const doTooltip = ( event, element, desc ) => {

		// Backward compatibility for jQuery vs ES.
		if ( element?.[0] )
			element = element[0];

		// Remove old tooltips, if any.
		for ( const element of document.querySelectorAll( ttSelectors.base ) ) {
			removeTooltip( element );
			_events( element ).unset();
		}

		if ( ! desc.length ) return false;

		return _renderTooltip( event, element, desc );
	}

	/**
	 * Adds tooltip boundaries.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @function
	 * @param {!jQuery|Element|string} element The jQuery element, DOM Element or query selector.
	 */
	const addBoundary = element => { element instanceof Element && element.classList.add( ttNames.boundary ) };

	/**
	 * Removes the description balloon and arrow from element.
	 *
	 * @since 3.1.0
	 * @since 4.1.0 Now also clears the data of the tooltip.
	 * @access public
	 *
	 * @function
	 * @param {!jQuery|Element|string} element
	 */
	const removeTooltip = element => {

		// Backward compatibility for jQuery vs ES.
		if ( element?.[0] )
			element = element[0];

		if ( element instanceof HTMLElement ) {
			delete element.dataset.hasTooltip;
			_clickLocker( element ).release();
		}

		const toolTip = getTooltip( element );
		toolTip?.parentNode.removeChild( toolTip );
	}

	/**
	 * Returns the containing tooltip, if input element isn't already a tooltip.
	 *
	 * @since 3.1.0
	 * @since 4.2.0 Now returns a `HTMLElement` instead of a `jQuery.Element`.
	 * @access public
	 *
	 * @function
	 * @param {!jQuery|Element|string} element
	 * @return {(Element|undefined)}
	 */
	const getTooltip = element => {

		// Backward compatibility for jQuery vs ES.
		if ( element?.[0] )
			element = element[0];

		return element?.classList.contains( ttNames.base )
			? element
			: element?.querySelector( ttSelectors.base );
	}

	let _debounceTriggerReset = void 0;
	/**
	 * Triggers tooltip reset.
	 * This takes .5ms via the event handler thread, feel free to use it whenever.
	 *
	 * @since 3.1.0
	 * @since 4.2.0 Added debouncing.
	 * @access public
	 *
	 * @function
	 */
	const triggerReset = () => {
		clearTimeout( _debounceTriggerReset );
		_debounceTriggerReset = setTimeout(
			() => window.dispatchEvent( new CustomEvent( 'tsf-tooltip-reset' ) ),
			100 // Magic number. Low enough not to cause annoyances, high enough not to cause lag.
		);
	}

	/**
	 * Triggers active tooltip update.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @function
	 * @param {Element|NodeList} element
	 */
	const triggerUpdate = element => {

		if ( ! element || ! ( element instanceof Element ) )
			element = document.querySelectorAll( ttSelectors.item );

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
}();
window.tsfTT.load();
