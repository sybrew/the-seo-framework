/**
 * This file holds The SEO Framework plugin's JS code for Image Selection and Cropping.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/autodescription/>
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2018 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds tsfMedia values in an object to avoid polluting global namespace.
 *
 * @since 3.1.0
 *
 * @constructor
 */
window.tsfMedia = function () {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 3.1.0
	 * @access public
	 * @type {(Object<string,*>)|boolean|null} l10n Localized strings
	 */
	const l10n = tsfMediaL10n;

	/**
	 * Image Cropper instance.
	 *
	 * @since 3.1.0
	 * @access private
	 * @type {!Object} Cropper
	 */
	let Cropper = {};

	/**
	 * Opens the image editor on request.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @param {Event} event
	 */
	function _openImageEditor( event ) {

		const button = event.target;

		if ( button.disabled || 'undefined' === typeof wp.media ) {
			event.preventDefault();
			event.stopPropagation();
			return;
		}

		const imageType = button.dataset.inputType || '',
			  imageId   = button.dataset.inputId || '';

		let frame; // Backbone.js var.

		event.preventDefault();
		event.stopPropagation();

		// Init extend Cropper.
		_extendCropper();

		const _states = {
			suggestedWidth:  +( button.dataset.width || 1200 ),
			suggestedHeight: +( button.dataset.height || 630 ),
			isFlex:          +( button.dataset.flex || 1 ), // Dataset is string, "0" is a passable, useful value.
			minWidth:        +( button.dataset.minWidth || 200 ),
			minHeight:       +( button.dataset.minHeight || 200 ),
		};

		Cropper.control = {
			params: {
				flex_width:  _states.isFlex ? 4096 : 0,
				flex_height: _states.isFlex ? 4096 : 0,
				width:       _states.suggestedWidth,
				height:      _states.suggestedHeight,
				isFlex:      _states.isFlex,
				minWidth:    _states.minWidth,
				minHeight:   _states.minHeight,
			},
		};

		frame = wp.media( {
			button : {
				text:  l10n.labels[ imageType ].imgFrameButton,
				close: false,
			},
			states: [
				new wp.media.controller.Library( {
					title:           l10n.labels[ imageType ].imgFrameTitle,
					library:         wp.media.query({ 'type' : 'image' }),
					multiple:        false,
					date:            false,
					priority:        20,
					suggestedWidth:  _states.suggestedWidth,
					suggestedHeight: _states.suggestedHeight
				} ),
				new Cropper( {
					imgSelectOptions: _calculateImageSelectOptions
				} ),
			],
		} );

		const inputUrl = document.getElementById( `${imageId}-url` ),
			  inputId  = document.getElementById( `${imageId}-id` );

		const onSelect = () => {
			frame.setState( 'cropper' );
		};
		frame.off( 'select', onSelect );
		frame.on( 'select', onSelect );

		const onCropped = croppedImage => {
			let url          = croppedImage.url,
				attachmentId = croppedImage.id;
				// w = croppedImage.width,
				// h = croppedImage.height;

			if ( inputUrl ) {
				inputUrl.value = url;
				inputUrl.dispatchEvent( new Event( 'change' ) );
			}
			// Send the attachment id to our hidden input. URL to explicit output.
			if ( inputId ) {
				inputId.value = attachmentId;
				inputId.dispatchEvent( new Event( 'change' ) );
			}
		};
		frame.off( 'cropped', onCropped );
		frame.on( 'cropped', onCropped );

		const onSkippedCrop = selection => {
			let url          = selection.get( 'url' ),
				attachmentId = selection.get( 'id' );
				// w = selection.get( 'width' ),
				// h = selection.get( 'height' );

			if ( inputUrl ) {
				inputUrl.value = url;
				inputUrl.dispatchEvent( new Event( 'change' ) );
			}
			// Send the attachment id to our hidden input. URL to explicit output.
			if ( inputId ) {
				inputId.value = attachmentId;
				inputId.dispatchEvent( new Event( 'change' ) );
			}
		};
		frame.off( 'skippedcrop', onSkippedCrop );
		frame.on( 'skippedcrop', onSkippedCrop );

		const onDone = () => {
			button.textContent = l10n.labels[ imageType ].imgChange;

			if ( inputUrl )
				inputUrl.readOnly = true;

			_appendRemoveButton( button, true );
			'tsfAys' in window && tsfAys.registerChange();
		};
		frame.off( 'skippedcrop cropped', onDone );
		frame.on( 'skippedcrop cropped', onDone );

		frame.open();
	}

	/**
	 * Removes the image editor image on request.
	 *
	 * @since 3.1.0
	 * @since 4.1.1 Removed second parameter, shifted third to second.
	 * @since 4.2.8 Now parses button classList data.
	 * @access private
	 *
	 * @param {Element} target  event target
	 * @param {Boolean} animate Whether to fade in the button.
	 */
	function _appendRemoveButton( target, animate ) {

		const inputId   = target.dataset?.inputId,
			  inputType = target.dataset?.inputType;

		if ( ! inputId || ! inputType ) return;

		// Don't append another remove button if one's found.
		if ( document.getElementById( `${inputId}-remove` ) ) return;

		const button = document.createElement( 'button' );

		button.type              = 'button';
		button.id                = `${inputId}-remove`
		button.dataset.inputId   = inputId;
		button.dataset.inputType = inputType;
		button.title             = tsf.decodeEntities( l10n.labels[ inputType ].imgRemoveTitle );
		button.innerHTML         = tsf.escapeString( tsf.decodeEntities( l10n.labels[ inputType ].imgRemove ) );

		button.classList.add( 'tsf-remove-image-button', ...( JSON.parse( target.dataset?.buttonClass || 0 )?.remove || [] ) );

		target.insertAdjacentElement( 'afterend', button );

		if ( animate )
			tsfUI.fadeIn( button, 250 ); // Magic number: tickbox doesn't quickly disappear; so double the default.

		// Reset cache.
		_resetImageEditorRemovalActions();
	}

	/**
	 * Removes the image editor image on request.
	 *
	 * @since 4.0.0
	 * @access private
	 *
	 * @param {Event} event
	 */
	function _removeEditorImage( event ) {

		const imageId   = event.target.dataset.inputId,
			  imageType = event.target.dataset.inputType;

		if ( ! imageId || ! imageType ) return;

		const inputSelect = document.getElementById( `${imageId}-select` );

		// Another image remover is probably handling this entry.
		if ( inputSelect.disabled ) return;

		inputSelect.disabled = true;
		inputSelect.classList.add( 'disabled' );

		const inputRemove = document.getElementById( `${imageId}-remove` );
		if ( inputRemove ) {
			inputRemove.disabled = true;
			inputRemove.classList.add( 'disabled' );

			tsfUI.fadeOut(
				inputRemove,
				125, // Magic number: default.
				() => {
					inputRemove.remove();

					inputSelect.textContent = l10n.labels[ imageType ].imgSelect;
					inputSelect.classList.remove( 'disabled' );
					inputSelect.disabled = false;
				}
			);
		}

		const inputUrl = document.getElementById( `${imageId}-url` );
		if ( inputUrl ) {
			inputUrl.value = '';
			inputUrl.dispatchEvent( new Event( 'change' ) );
			if ( ! inputUrl.dataset.readonly ) { // this data entry should be added when the input should not be user-editable. Honor it.
				inputUrl.readOnly = false;
			}
		}

		const inputId = document.getElementById( `${imageId}-id` );
		if ( inputId ) {
			inputId.value = '';
			inputId.dispatchEvent( new Event( 'change' ) );
		}

		'tsfAys' in window && tsfAys.registerChange();
	}

	/**
	 * Builds constructor for media Cropper.
	 *
	 * @since 3.1.0
	 * @access private
	 */
	function _extendCropper() {

		if ( 'undefined' !== typeof Cropper.control )
			return;

		const View = wp.media.view;
		/**
		 * wp.media.view.Cropper augmentation.
		 *
		 * Allows for squaring images.
		 *
		 * @class
		 * @augments wp.media.View
		 * @augments wp.Backbone.View
		 * @augments Backbone.View
		 */
		const TSFView = View.Cropper.extend( {
			className:   'crop-content tsf-image',
			ready:       function () {
				View.Cropper.prototype.ready.apply( this, arguments );
			},
			onImageLoad: function () {
				let imgOptions = this.controller.get( 'imgSelectOptions' ),
					imgSelect;

				if ( typeof imgOptions === 'function' )
					imgOptions = imgOptions( this.options.attachment, this.controller );

				if ( 'undefined' === typeof imgOptions.aspectRatio ) {
					imgOptions = Object.assign(
						imgOptions,
						{
							parent: this.$el,
							onInit: function () {
								this.parent.children().on( 'mousedown touchstart', function( e ) {
									if ( e.shiftKey ) {
										imgSelect.setOptions( {
											aspectRatio: '1:1'
										} );
									} else {
										imgSelect.setOptions( {
											aspectRatio: false
										} );
									}
								} );
							}
						},
					);
				}
				this.trigger( 'image-loaded' );
				imgSelect = this.controller.imgSelect = this.$image.imgAreaSelect( imgOptions );
			},
		} );

		/**
		 * wp.media.controller.Cropper augmentation.
		 *
		 * A state for cropping an image.
		 *
		 * @class
		 * @augments wp.media.controller.Cropper
		 * @augments wp.media.controller.State
		 * @augments Backbone.Model
		 */
		const TSFCropper = wp.media.controller.Cropper.extend( {
			createCropContent: function () {
				this.cropperView = new TSFView( {
					controller: this,
					attachment: this.get( 'selection' ).first()
				} );
				this.cropperView.on( 'image-loaded', this.createCropToolbar, this );
				this.frame.content.set( this.cropperView );
			},
			doCrop:            function( attachment ) {
				let cropDetails = attachment.get( 'cropDetails' ),
					control     = Cropper.control; // prototyped prior cropping, below.

				// Use crop measurements when flexible in both directions.
				if ( control.params.flex_width && control.params.flex_height ) {
					// Square
					if ( cropDetails.width === cropDetails.height ) {
						if ( cropDetails.width > control.params.flex_width ) {
							cropDetails.dst_width = cropDetails.dst_height = control.params.flex_width;
						}
					// Landscape/Portrait
					} else {
						// Resize to flex width/height
						if ( cropDetails.width > control.params.flex_width || cropDetails.height > control.params.flex_height ) {
							// Landscape
							if ( cropDetails.width > cropDetails.height ) {
								let _ratio = cropDetails.width / control.params.flex_width;

								cropDetails.dst_width  = control.params.flex_width;
								cropDetails.dst_height = Math.round( cropDetails.height / _ratio );
							// Portrait
							} else {
								let _ratio = cropDetails.height / control.params.flex_height;

								cropDetails.dst_height = control.params.flex_height;
								cropDetails.dst_width  = Math.round( cropDetails.width / _ratio );
							}
						}
					}
				}

				// Nothing happened. Set destination to 0 and let PHP figure it out.
				if ( 'undefined' === typeof cropDetails.dst_width ) {
					cropDetails.dst_width  = 0;
					cropDetails.dst_height = 0;
				}

				return wp.ajax.post(
					'tsf_crop_image',
					{
						nonce:      l10n.nonce,
						id:         attachment.get( 'id' ),
						context:    'tsf-image',
						cropDetails: cropDetails,
					}
				);
			}
		} );

		TSFCropper.prototype.control = {};

		Cropper = TSFCropper;
	}

	/**
	 * Returns a set of options, computed from the attached image data and
	 * control-specific data, to be fed to the imgAreaSelect plugin in
	 * wp.media.view.Cropper.
	 *
	 * @since 3.1.0
	 * @access private
	 *
	 * @param {wp.media.model.Attachment} attachment
	 * @param {wp.media.controller.Cropper} controller
	 * @return {Object} imgSelectOptions
	 */
	function _calculateImageSelectOptions( attachment, controller ) {

		const control = Cropper.control;

		let xInit = parseInt( control.params.width, 10 ),
			yInit = parseInt( control.params.height, 10 );

		const flexWidth  = !! parseInt( control.params.flex_width, 10 ),
			  flexHeight = !! parseInt( control.params.flex_height, 10 );

		const realWidth  = attachment.get( 'width' ),
			  realHeight = attachment.get( 'height' ),
			  ratio      = xInit / yInit,
			  xImg       = xInit,
			  yImg       = yInit;

		let canSkipCrop;
		if ( control.params.isFlex ) {
			canSkipCrop = ! _mustBeCropped( control.params.flex_width, control.params.flex_height, realWidth, realHeight );
		} else {
			// Not flex. If ratios match, then we can skip.
			canSkipCrop = ratio === realWidth / realHeight;
		}

		controller.set( 'control', control.params );
		controller.set( 'canSkipCrop', canSkipCrop );

		// Correct aspect ratio if fixed.
		if ( realWidth / realHeight > ratio ) {
			yInit = realHeight;
			xInit = yInit * ratio;
		} else {
			xInit = realWidth;
			yInit = xInit / ratio;
		}

		// Find starting points, I think? Why do we halve this?
		// This is taken from WordPress's very own '_calculateImageSelectOptions' as-is.
		let x1 = ( realWidth - xInit ) / 2,
			y1 = ( realHeight - yInit ) / 2;

		const imgSelectOptions = {
			handles:     true,
			keys:        true,
			instance:    true,
			persistent:  true,
			imageWidth:  realWidth,
			imageHeight: realHeight,
			minWidth:    xImg > xInit ? xInit : xImg,
			minHeight:   yImg > yInit ? yInit : yImg,
			x1:          x1,
			y1:          y1,
			x2:          xInit + x1,
			y2:          yInit + y1,
		};

		// @TODO Convert set img min-width/height to output ratio.
		// i.e. 200x2000 will become x = 1500/2000*200 = 150px, which is too small.
		// Unlikely...

		if ( ! control.params.isFlex ) {
			imgSelectOptions.handles = 'corners';
			imgSelectOptions.aspectRatio = `${xInit}:${yInit}`;
		} else if ( ! flexHeight && ! flexWidth ) {
			imgSelectOptions.aspectRatio = `${xInit}:${yInit}`;
		} else {
			if ( flexHeight ) {
				imgSelectOptions.minHeight = control.params.minHeight;
				imgSelectOptions.maxWidth  = realWidth;
			}
			if ( flexWidth ) {
				imgSelectOptions.minWidth  = control.params.minWidth;
				imgSelectOptions.maxHeight = realHeight;
			}
		}

		return imgSelectOptions;
	}

	/**
	 * Return whether the image must be cropped, based on required dimensions.
	 * Disregards flexWidth/Height.
	 *
	 * @since 3.1.0
	 * @access private
	 *
	 * @param {Number} dstW
	 * @param {Number} dstH
	 * @param {Number} imgW
	 * @param {Number} imgH
	 * @return {Boolean}
	 */
	function _mustBeCropped( dstW, dstH, imgW, imgH ) {
		return imgW > dstW || imgH > dstH;
	}

	/**
	 * Updates button text on change.
	 *
	 * @since 4.1.0
	 * @access private
	 *
	 * @param {!jQuery.event} event
	 */
	function _updateButtonText( event ) {
		const imageId   = event.target.dataset.id || '',
			  imageType = event.target.dataset.type || '';

		if ( ! imageId || ! imageType ) return;

		const inputSelect = document.getElementById( `${imageId}-select` );

		// The image remover is probably handling this entry.
		if ( inputSelect.disabled ) return;

		inputSelect.textContent = event.target.value.length
			? l10n.labels[ imageType ].imgChange
			: l10n.labels[ imageType ].imgSelect;
	}

	/**
	 * Checks if input is filled in by image editor.
	 *
	 * @since 3.1.0
	 * @since 4.1.0 Now prepares an input change event.
	 * @access private
	 */
	function _checkImageEditorInput() {

		document.querySelectorAll( '.tsf-set-image-button' ).forEach(
			element => {
				const imageId  = element.dataset.inputId || '',
					  inputId  = imageId && document.getElementById( `${imageId}-id` ),
					  inputUrl = imageId && document.getElementById( `${imageId}-url` );

				if ( inputId && inputId.value > 0 ) {
					if ( inputUrl ) inputUrl.readOnly = true;
					_appendRemoveButton( element, false );
				}

				if ( inputUrl ) {
					inputUrl.addEventListener( 'change', _updateButtonText );
					inputUrl.dispatchEvent( new Event( 'change' ) );
				}
			}
		);
	}

	/**
	 * Resets jQuery image editor cache for when the removal button appears.
	 *
	 * @since 3.1.0
	 * @access private
	 */
	function _resetImageEditorSetActions() {
		document.querySelectorAll( '.tsf-set-image-button' ).forEach( el => {
			el.addEventListener( 'click', _openImageEditor );
		} );
	}

	/**
	 * Resets jQuery image editor cache for when the removal button appears.
	 *
	 * @since 3.1.0
	 * @access private
	 */
	function _resetImageEditorRemovalActions() {
		document.querySelectorAll( '.tsf-remove-image-button' ).forEach( el => {
			el.addEventListener( 'click', _removeEditorImage );
		} );
	}

	/**
	 * Sets up jQuery image editor cache.
	 *
	 * @since 3.1.0
	 * @see tsfemMedia.setupImageEditorActions() (Extension Manager plugin)
	 * @access private
	 */
	function _setupImageEditorActions() {
		_resetImageEditorSetActions();
		_resetImageEditorRemovalActions();

		document.querySelectorAll( '.tsf-enable-media-if-js' ).forEach( el => {
			el.disabled = false;
			el.classList.remove( 'tsf-enable-media-if-js' );
		} );

		_checkImageEditorInput(); // This fires a change event... is that desired?
		_prepareTooltip();
	}

	let _notificationBuffer = new Map();
	/**
	 * Updates the input's parentNode tooltip input.
	 *
	 * @since 4.1.4
	 * @since 5.1.0 Reworked to support more notification types.
	 *
	 * @param {Event} event
	 */
	function _updateImageNotifications( event ) {
		const imageId = event.target.id?.replace( /-[a-z]+$/, '' ); // inferred from get_image_uploader_form().

		if ( ! imageId ) return;

		const preview      = document.getElementById( `${imageId}-preview` ),
			  imageWarning = document.getElementById( `${imageId}-image-warning` );

		_notificationBuffer.has( imageId ) && clearTimeout( _notificationBuffer.get( imageId ) );

		const firstLoad = ! ( event.target.dataset.tsfNotificationsLoaded || false );

		let inputSrc = event.target.value || event.target.placeholder || '';

		const showElement = el => {
			if ( firstLoad ) {
				el.classList.remove( 'hidden' );
				el.style.opacity = '1';
			} else {
				if ( el.classList.contains( 'hidden' ) ) {
					el.classList.remove( 'hidden' );
					tsfUI.fadeIn( el );
				}
			}
			tsfTT.triggerUpdate( el );
		}
		// Hide without fading; albeit nicer, it will cause the elements to move when rearranged.
		// There's no workaround but to keep track of every element's position, which adds a lot of code smell.
		const hideElement = el => {
			el.classList.add( 'hidden' );
			el.style.opacity = '0';
		}

		const updateToolTip = () => {
			if ( ! inputSrc.length ) {
				[ preview, imageWarning ].forEach( hideElement );
				return;
			}

			let currentUpdateTime = Date.now();
			event.target.dataset.tsfCurrentInputTime = currentUpdateTime; // note: this will be a string.

			const imageObject = new Image();

			const writeTooltips = success => {

				// If the input has changed, we don't want to update the tooltip.
				if ( currentUpdateTime !== +event.target.dataset.tsfCurrentInputTime ) return;

				let warning     = '',
					warningType = 'warning';

				if ( preview ) {
					if ( success ) {
						// 250px is the max width for tooltips; we subtract 24 for padding, and 1 for subpixel rounding errors.
						// We set min-height and width as that will prevent jumping. Also, those are the absolute-minimum for sharing/schema images.
						imageObject.style    = "max-width:225px;max-height:225px;min-width:60px;min-height:60px;border-radius:3px;display:block;";
						preview.dataset.desc = imageObject.outerHTML;

						showElement( preview );
					} else {
						warning     = l10n.warning.i18n.notLoaded;
						warningType = 'error';

						hideElement( preview );
					}
				}

				if ( imageWarning ) {
					// If there's already a display warning, we don't want to overwrite it.
					if ( ! warning.length ) {
						// Let's keep the check simple for now.
						let ext = imageObject.src.length && imageObject.src.split( '.' ).pop().toLowerCase();

						if ( ext.length ) {
							if ( l10n.warning.warnedTypes.hasOwnProperty( ext ) ) {
								warning = l10n.warning.i18n.extWarned.replace( '%s', tsf.escapeString( ext ) );
							} else if ( l10n.warning.forbiddenTypes.hasOwnProperty( ext ) ) {
								warning      = l10n.warning.i18n.extForbidden.replace( '%s', tsf.escapeString( ext ) );
								warningType = 'error';
							}
						}
						// TODO: If the above fails, we'd have to parse the MIME type.
						// But we don't know how to do this yet securely, so it's punted.
						// We'd probably have to extract the file's first few bytes and check for magic numbers.
					}

					if ( warning.length ) {
						// Remove here because it'll flicker otherwise.
						imageWarning.classList.toggle( 'tsf-media-warning', warningType === 'warning' );
						imageWarning.classList.toggle( 'tsf-media-error', warningType === 'error' );

						imageWarning.dataset.desc = warning;

						showElement( imageWarning );
					} else {
						hideElement( imageWarning );
					}
				}
			}

			// Prepare asynchronous tooltip update.
			let invokedTooltipUpdate = false;
			imageObject.onload  = () => {
				writeTooltips( true );
				invokedTooltipUpdate = true;
			}
			imageObject.onerror = () => {
				writeTooltips( false );
				invokedTooltipUpdate = true;
			}
			// Do something after 7s if neither loads have been invoked (slow connection, huge image).
			setTimeout(
				() => {
					if ( ! invokedTooltipUpdate ) {
						imageObject.src = '';
						writeTooltips( false );
					}
				},
				7000, // Magic number: default keep-alive time. Huge images shouldn't be used anyway.
			);

			/**
			 * Preload image. Moreover, the Image object escapes
			 *
			 * XSS tests that passed (i.e., no issue), because the --browser-- must (and does) block these:
			 * - data:text/html;base64,amF2YXNjcmlwdDphbGVydCgnaGknKTs=
			 * - svg loading with scripts attached (CORB blocks, good. Thank you for bringing attention, Meltdown & Spectr)
			 * - "/><script>alert(\'XSS\');</script>
			 *
			 * CSRF should be blocked by the browser, as well. Otherwise, Authors and Editors are able to execute
			 * these via the default WordPress editor, already.
			 *
			 * URLs that aren't trusted are also filtered via sanitization on save.
			 *
			 * We are NOT creating a document node here, that's something we leave for the tooltip.
			 * We're just writing a template.
			 */
			imageObject.src = inputSrc;
		}

		event.target.dataset.tsfNotificationsLoaded = true;

		_notificationBuffer.set(
			imageId,
			setTimeout(
				updateToolTip,
				// High timeout: Don't DoS the inputted URL, plus the delay is quite nice. This equals to about 522ms.
				firstLoad && 0 ? inputSrc.length : 1000/(115/60), // Magic number: 115 Keys/Min is considered a "slow" typer. ISBN: 978-3-319-20498-7.
			)
		);
	}

	/**
	 * Sets up image input tooltip handler.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	function _prepareTooltip() {
		document.querySelectorAll( '.tsf-image-notifications' ).forEach( el => {
			const inputUrl = document.getElementById( `${el.dataset.for}-url` );
			if ( ! inputUrl ) return;

			inputUrl.addEventListener( 'input', _updateImageNotifications );
			inputUrl.addEventListener( 'change', _updateImageNotifications );

			inputUrl.dispatchEvent( new Event( 'change' ) );
		} );
	}

	/**
	 * Resets image editor actions and selectors.
	 *
	 * High timeout. Resets should only happen during failures or changing document states;
	 * the latter of which is slow.
	 *
	 * @since 4.1.2
	 * @access public
	 *
	 * @function
	 */
	const resetImageEditorActions = tsfUtils.debounce( _setupImageEditorActions, 500 );

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
			// Initialize image uploader button cache.
			document.body.addEventListener( 'tsf-ready', _setupImageEditorActions );
		},
	}, {
		resetImageEditorActions,
	}, {
		l10n,
	} );
}();
window.tsfMedia.load();
