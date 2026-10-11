/**
 * This file holds The SEO Framework plugin's JS code for editing generated values.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/autodescription/>
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2026 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds tsfPropose values in an object to avoid polluting global namespace.
 *
 * @since 5.2.0
 *
 * @constructor
 */
window.tsfPropose = function () {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 5.2.0
	 * @access public
	 * @type {(Object<string,*>)|Boolean|null} l10n Localized strings
	 */
	const l10n = tsfProposeL10n;

	/**
	 * Class for an unedited fill.
	 *
	 * @since 5.2.0
	 * @access private
	 * @type {string} CLASS_PROPOSED Keeps the placeholder blur.
	 */
	const CLASS_PROPOSED = 'tsf-proposed';

	/**
	 * Class for a fill the user has edited.
	 *
	 * @since 5.2.0
	 * @access private
	 * @type {string} CLASS_DIRTY Shows normal input text.
	 */
	const CLASS_DIRTY = 'tsf-proposed-dirty';

	/**
	 * @since 5.2.0
	 * @access private
	 * @type {Boolean}
	 */
	let engaged = false;

	/**
	 * @since 5.2.0
	 * @access private
	 * @type {Boolean}
	 */
	let loaded = false;

	/**
	 * Unedited fills. Weak so a removed quick-edit clone can be collected.
	 *
	 * @since 5.2.0
	 * @access private
	 * @type {WeakMap<HTMLInputElement|HTMLTextAreaElement,{dirty:boolean,filled:boolean}>}
	 */
	const sessions = new WeakMap();

	/**
	 * The caret correction still waiting for the browser's hit test.
	 *
	 * @since 5.2.0
	 * @access private
	 * @type {{el:HTMLInputElement|HTMLTextAreaElement,offset:number}|null}
	 */
	let pendingCaret = null;

	/**
	 * @since 5.2.0
	 * @access private
	 * @type {number}
	 */
	let caretFrame = 0;

	/**
	 * Tests whether the node is an input this script may fill.
	 *
	 * @since 5.2.0
	 * @access private
	 *
	 * @param {EventTarget|null} el The node to test.
	 * @return {Boolean} True when this script may fill the node.
	 */
	function isProposeField( el ) {
		return ( el instanceof HTMLInputElement || el instanceof HTMLTextAreaElement )
			&& el.hasAttribute( 'data-tsf-proposed' );
	}

	/**
	 * Returns the session for a field, without creating one.
	 *
	 * @since 5.2.0
	 * @access private
	 *
	 * @param {HTMLInputElement|HTMLTextAreaElement} el The field.
	 * @return {{dirty:boolean,filled:boolean}} The stored fill, or a blank one.
	 */
	function sessionOf( el ) {
		return sessions.get( el ) ?? {
			dirty:  false,
			filled: false,
		};
	}

	/**
	 * Notifies listeners without marking the field unsaved.
	 * `tsfAys` ignores untrusted input events.
	 *
	 * @since 5.2.0
	 * @access private
	 *
	 * @param {HTMLInputElement|HTMLTextAreaElement} el The field to notify.
	 */
	function dispatchInput( el ) {
		el.dispatchEvent(
			new Event(
				'input',
				{ bubbles: true },
			),
		);
	}

	/**
	 * Clears a fill the user never edited, so it cannot be stored.
	 *
	 * @since 5.2.0
	 * @access private
	 *
	 * @param {HTMLInputElement|HTMLTextAreaElement} el The field to clear.
	 */
	function release( el ) {

		const session = sessionOf( el );

		if ( ! session.filled || session.dirty ) return;

		// Drop the session before notifying, so an attribute refresh cannot refill.
		sessions.set(
			el,
			{
				dirty:  false,
				filled: false,
			},
		);
		el.value = '';
		el.classList.remove( CLASS_PROPOSED, CLASS_DIRTY );
		dispatchInput( el );
	}

	/**
	 * Clears every unedited fill.
	 *
	 * @since 5.2.0
	 * @access private
	 */
	function releaseAll() {
		document.querySelectorAll(
			`[data-tsf-proposed].${CLASS_PROPOSED}`,
		).forEach( el => {
			isProposeField( el ) && release( el );
		} );
	}

	/**
	 * Copies the proposed text into an empty field and keeps the placeholder blur.
	 *
	 * @since 5.2.0
	 * @access private
	 *
	 * @param {HTMLInputElement|HTMLTextAreaElement} el   The empty field.
	 * @param {string}                               text The proposed text.
	 */
	function fill( el, text ) {

		el.value = text;
		el.classList.add( CLASS_PROPOSED );
		el.classList.remove( CLASS_DIRTY );
		sessions.set(
			el,
			{
				dirty:  false,
				filled: true,
			},
		);
		dispatchInput( el );
	}

	/**
	 * Places a collapsed caret. Number inputs reject selection ranges.
	 *
	 * @since 5.2.0
	 * @access private
	 *
	 * @param {HTMLInputElement|HTMLTextAreaElement} el     The field.
	 * @param {number}                               offset The character index.
	 */
	function setCaret( el, offset ) {

		const end = el.value.length;

		try {
			el.setSelectionRange(
				Math.min( offset, end ),
				Math.min( offset, end ),
			);
		} catch ( error ) {}
	}

	/**
	 * Reads a character offset when the hit landed in the field's own text.
	 * An offset of 0 from the input element itself is ignored: that is the
	 * empty-field hit, which the mirror then measures.
	 *
	 * @since 5.2.0
	 * @access private
	 *
	 * @param {CaretPosition|Range|null}             hit The browser hit, if any.
	 * @param {HTMLInputElement|HTMLTextAreaElement} el  The clicked field.
	 * @return {number|null} The character index, or null when the hit missed the text.
	 */
	function caretOffset( hit, el ) {

		if ( ! hit ) return null;

		const node   = hit.offsetNode ?? hit.startContainer,
			  offset = hit.offset ?? hit.startOffset;

		if ( Node.TEXT_NODE === node?.nodeType && el.contains( node ) )
			return offset;

		if ( node === el && offset > 0 )
			return offset;

		return null;
	}

	/**
	 * Asks the browser which character sits at the pointer.
	 *
	 * @since 5.2.0
	 * @access private
	 *
	 * @param {number} x Viewport X coordinate.
	 * @param {number} y Viewport Y coordinate.
	 * @return {CaretPosition|Range|null} The browser hit, or null.
	 */
	function hitFromPoint( x, y ) {
		return document.caretPositionFromPoint?.( x, y )
			?? document.caretRangeFromPoint?.( x, y )
			?? null;
	}

	/**
	 * Measures the click against a copy of the field, because caret APIs
	 * miss input and textarea text. The copy takes the field's computed
	 * style, then pins that box over the field.
	 *
	 * @since 5.2.0
	 * @access private
	 *
	 * @param {HTMLInputElement|HTMLTextAreaElement} el The field.
	 * @param {number}                               x  Viewport X coordinate.
	 * @param {number}                               y  Viewport Y coordinate.
	 * @return {number|null} The character index, or null when it cannot be measured.
	 */
	function offsetFromMirror( el, x, y ) {

		const style  = getComputedStyle( el ),
			  rect   = el.getBoundingClientRect(),
			  mirror = document.createElement( 'div' );

		mirror.textContent = el.value;

		// Used values, so a later admin rule still moves the glyphs.
		for ( const prop of style )
			mirror.style.setProperty(
				prop,
				style.getPropertyValue( prop ),
			);

		// The rect is the border box, already including transforms.
		mirror.style.position      = 'fixed';
		mirror.style.left          = `${rect.left}px`;
		mirror.style.top           = `${rect.top}px`;
		mirror.style.right         = 'auto';
		mirror.style.bottom        = 'auto';
		mirror.style.width         = `${rect.width}px`;
		mirror.style.height        = `${rect.height}px`;
		mirror.style.margin        = '0';
		mirror.style.boxSizing     = 'border-box';
		mirror.style.minWidth      = '0';
		mirror.style.maxWidth      = 'none';
		mirror.style.minHeight     = '0';
		mirror.style.maxHeight     = 'none';
		mirror.style.overflow      = 'hidden';
		mirror.style.opacity       = '0';
		mirror.style.zIndex        = '2147483647';
		mirror.style.pointerEvents = 'auto';
		mirror.style.visibility    = 'visible';
		mirror.style.appearance    = 'none';
		mirror.style.transform     = 'none';
		mirror.style.transition    = 'none';

		document.body.appendChild( mirror );

		let measured = null;

		try {
			mirror.scrollLeft = el.scrollLeft;
			mirror.scrollTop  = el.scrollTop;

			const hit    = hitFromPoint( x, y );
			const node   = hit?.offsetNode ?? hit?.startContainer;
			const offset = hit?.offset ?? hit?.startOffset;

			if (
				   Node.TEXT_NODE === node?.nodeType
				&& mirror.contains( node )
			) {
				measured = offset;
			} else if ( node === mirror ) {
				measured = offset ? el.value.length : 0;
			}
		} finally {
			mirror.remove();
		}

		if ( null === measured ) return null;

		return Math.min( measured, el.value.length );
	}

	/**
	 * Resolves the character under the pointer, after overlays step aside.
	 *
	 * @since 5.2.0
	 * @access private
	 *
	 * @param {HTMLInputElement|HTMLTextAreaElement} el The field.
	 * @param {number}                               x  Viewport X coordinate.
	 * @param {number}                               y  Viewport Y coordinate.
	 * @return {number|null} The character index, or null when it cannot be measured.
	 */
	function offsetFromPoint( el, x, y ) {

		const direct = caretOffset(
			hitFromPoint( x, y ),
			el,
		);

		if ( null !== direct ) return direct;

		const blockers = [];
		let retry = null;

		try {
			for ( const node of document.elementsFromPoint( x, y ) ) {
				if ( node === el ) break;

				if (
					   ! ( node instanceof Element )
					|| node.contains( el )
				) {
					continue;
				}

				blockers.push( [
					node,
					node.style.pointerEvents,
				] );
				node.style.pointerEvents = 'none';
			}

			retry = caretOffset(
				hitFromPoint( x, y ),
				el,
			);
		} finally {
			for ( const [ node, pointerEvents ] of blockers )
				node.style.pointerEvents = pointerEvents;
		}

		if ( null !== retry ) return retry;

		return offsetFromMirror( el, x, y );
	}

	/**
	 * Corrects a caret that the empty field's hit test left at the start.
	 * A selection the browser already made is left alone.
	 *
	 * @since 5.2.0
	 * @access private
	 */
	function correctCaret() {

		const pending = pendingCaret;

		if ( ! pending ) return;

		const el      = pending.el,
			  session = sessionOf( el );

		if (
			   ! session.filled
			|| session.dirty
			|| el.selectionStart !== el.selectionEnd
			|| 0 !== el.selectionStart
		) {
			return;
		}

		setCaret( el, pending.offset );
	}

	/**
	 * Runs the caret correction after the browser's own hit test.
	 *
	 * @since 5.2.0
	 * @access private
	 *
	 * @param {HTMLInputElement|HTMLTextAreaElement} el     The field.
	 * @param {number}                               offset The character index to restore.
	 */
	function scheduleCaretFix( el, offset ) {

		pendingCaret = {
			el,
			offset,
		};
		cancelAnimationFrame( caretFrame );
		caretFrame = requestAnimationFrame( correctCaret );
	}

	/**
	 * Fills an empty field before the browser places the caret.
	 *
	 * @since 5.2.0
	 * @access private
	 *
	 * @param {PointerEvent} event The pointerdown event.
	 */
	function onPointerDown( event ) {

		if (
			   ! engaged
			|| 0 !== event.button
			|| false === event.isPrimary
		) {
			return;
		}

		const el = event.target;

		if ( ! isProposeField( el ) || el.disabled || el.readOnly ) return;

		if ( ! el.getClientRects().length ) return;

		// A later click of a double- or triple-click must keep the browser's selection.
		if ( event.detail > 1 ) {
			pendingCaret = null;
			cancelAnimationFrame( caretFrame );

			return;
		}

		if ( el.value.length ) return;

		const proposed = el.getAttribute( 'data-tsf-proposed' ) ?? '';

		if ( ! proposed.length ) return;

		fill( el, proposed );

		const offset = offsetFromPoint(
			el,
			event.clientX,
			event.clientY,
		);

		if ( null === offset ) return;

		setCaret( el, offset );
		scheduleCaretFix( el, offset );
	}

	/**
	 * Re-measures once the browser has finished placing the caret.
	 *
	 * @since 5.2.0
	 * @access private
	 *
	 * @param {PointerEvent} event The pointerup event.
	 */
	function onPointerUp( event ) {

		if ( ! pendingCaret || pendingCaret.el !== event.target ) return;

		if ( event.detail > 1 ) {
			pendingCaret = null;
			cancelAnimationFrame( caretFrame );

			return;
		}

		scheduleCaretFix(
			pendingCaret.el,
			pendingCaret.offset,
		);
	}

	/**
	 * The first trusted edit leaves the placeholder blur. Emptying reengages it.
	 *
	 * @since 5.2.0
	 * @access private
	 *
	 * @param {InputEvent} event The input event.
	 */
	function onInput( event ) {

		if ( ! engaged || ! event.isTrusted ) return;

		const el = event.target;

		if ( ! isProposeField( el ) ) return;

		if ( ! el.value.length ) {
			el.classList.remove( CLASS_PROPOSED, CLASS_DIRTY );
			sessions.set(
				el,
				{
					dirty:  false,
					filled: false,
				},
			);

			return;
		}

		el.classList.remove( CLASS_PROPOSED );
		el.classList.add( CLASS_DIRTY );
		sessions.set(
			el,
			{
				dirty:  true,
				filled: true,
			},
		);
	}

	/**
	 * Drops an unedited fill when the field is left, including before save.
	 *
	 * @since 5.2.0
	 * @access private
	 *
	 * @param {FocusEvent} event The focusout event.
	 */
	function onFocusOut( event ) {
		isProposeField( event.target ) && release( event.target );
	}

	/**
	 * Drops unedited fills before the form is serialized.
	 *
	 * @since 5.2.0
	 * @access private
	 */
	function onSubmit() {
		releaseAll();
	}

	/**
	 * Keeps an unedited fill aligned with a producer that changed the proposal.
	 *
	 * @since 5.2.0
	 * @access private
	 *
	 * @param {MutationRecord[]} mutations Changes to `data-tsf-proposed`.
	 */
	function onAttribute( mutations ) {

		if ( ! engaged ) return;

		for ( const mutation of mutations ) {
			const el = mutation.target;

			if ( ! isProposeField( el ) ) continue;

			const session = sessionOf( el );

			if ( ! session.filled || session.dirty ) continue;

			const proposed = el.getAttribute( 'data-tsf-proposed' ) ?? '';

			if ( ! proposed.length ) {
				release( el );
				continue;
			}

			if ( el.value === proposed ) continue;

			const start = el.selectionStart,
				  end   = el.selectionEnd;

			el.value = proposed;
			dispatchInput( el );

			if ( null === start || null === end ) continue;

			const nextEnd = el.value.length;

			try {
				el.setSelectionRange(
					Math.min( start, nextEnd ),
					Math.min( end, nextEnd ),
				);
			} catch ( error ) {}
		}
	}

	/**
	 * Writes `data-tsf-proposed` when that attribute is present.
	 * An omitted value uses the field's placeholder. The attribute listener
	 * refreshes a fill the user has not edited.
	 *
	 * @since 5.2.0
	 * @access public
	 *
	 * @param {Element} input    The field that may carry `data-tsf-proposed`.
	 * @param {string}  proposed The editable text. Defaults to the placeholder.
	 */
	function syncProposed( input, proposed ) {

		if ( ! input?.hasAttribute( 'data-tsf-proposed' ) ) return;

		input.dataset.tsfProposed = 'string' === typeof proposed
			? proposed
			: ( input.placeholder ?? '' );
	}

	/**
	 * Allows empty fields to fill from their proposed text.
	 *
	 * @since 5.2.0
	 * @access public
	 */
	function engage() {
		engaged = true;
	}

	/**
	 * Stops filling and clears fills the user has not edited.
	 *
	 * @since 5.2.0
	 * @access public
	 */
	function disengage() {

		engaged = false;
		releaseAll();
	}

	return Object.assign(
		{
			/**
			 * Initialises all aspects of the scripts.
			 * You shouldn't call this.
			 *
			 * @since 5.2.0
			 * @access protected
			 *
			 * @function
			 */
			load: () => {

				if ( loaded ) return;

				loaded = true;

				document.addEventListener( 'pointerdown', onPointerDown, true );
				document.addEventListener( 'pointerup', onPointerUp, true );
				document.addEventListener( 'input', onInput, true );
				document.addEventListener( 'focusout', onFocusOut, true );
				document.addEventListener( 'submit', onSubmit, true );

				new MutationObserver( onAttribute ).observe(
					document.documentElement,
					{
						attributes:      true,
						attributeFilter: [ 'data-tsf-proposed' ],
						subtree:         true,
					},
				);

				// wp_localize_script stringifies false to "" and true to "1".
				if ( l10n.enabled )
					engage();
			},
		},
		{
			engage,
			disengage,
			syncProposed,
		},
	);
}();
window.tsfPropose.load();
