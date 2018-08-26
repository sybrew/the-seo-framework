/**
 * This file holds The SEO Framework plugin's JS code for Image Selection and Cropping.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/autodescription/>
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2018 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * This is a self-constructed function assigned as an object.
 *
 * @since 3.1.0
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfMedia = function( $ ) {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 3.1.0
	 * @access private
	 * @type {(Object<string, *>)|boolean|null} l10n Localized strings
	 */
	var l10n = 'undefined' !== typeof tsfMediaL10n && tsfMediaL10n;

	/**
	 * Image cropper instance.
	 *
	 * @since 3.1.0
	 * @access public
	 * @type {!Object} cropper
	 */
	var cropper = {};

	/**
	 * Escapes HTML class or ID keys. Doesn't double-escape.
	 *
	 * @since 3.1.0
	 * @access private
	 *
	 * @function
	 * @param {String} str
	 * @return {(string|undefined)} HTML to jQuery converted string
	 */
	const escapeKey = ( str ) => {

		if ( str )
			return str.replace( /(?!\\)(?=[\[\]\/])/g, '\\' );

		return str;
	}

	/**
	 * Opens the image editor on request.
	 *
	 * @since 3.1.0
	 * @access private
	 *
	 * @function
	 * @param {!jQuery.Event} event jQuery event
	 * @return {undefined}
	 */
	const openImageEditor = ( event ) => {

		let $button = $( event.target );

		if ( $button.prop( 'disabled' ) || 'undefined' === typeof wp.media ) {
			event.preventDefault();
			event.stopPropagation();
			return;
		}

		let inputURL = $button.data( 'input-url' ),
			inputType = $button.data( 'input-type' ),
			s_inputURL = escapeKey( inputURL ),
			inputID = $button.data( 'input-id' ),
			s_inputID = escapeKey( inputURL ),
			frame; // Backbone.js var.

		event.preventDefault();
		event.stopPropagation();

		//* Init extend cropper.
		extendCropper();

		let _states = {
			suggestedWidth: $button.data( 'width' ) || 1200,
			suggestedHeight: $button.data( 'height' ) || 630,
			isFlex: typeof $button.data( 'flex' ) !== 'undefined' ? $button.data( 'flex' ) : 1,
		};

		cropper.control = {
			'params' : {
				'flex_width' : _states.isFlex ? 4096 : 0,
				'flex_height' : _states.isFlex ? 4096 : 0,
				'width' : _states.suggestedWidth,
				'height' : _states.suggestedHeight,
				'isFlex' : _states.isFlex,
			},
		};

		frame = wp.media( {
			button : {
				'text' : l10n.labels[ inputType ].imgFrameButton,
				'close' : false,
			},
			states: [
				new wp.media.controller.Library( {
					'title' : l10n.labels[ inputType ].imgFrameTitle,
					'library' : wp.media.query({ 'type' : 'image' }),
					'multiple' : false,
					'date' : false,
					'priority' : 20,
					'suggestedWidth' : _states.suggestedWidth,
					'suggestedHeight' : _states.suggestedHeight
				} ),
				new cropper( {
					'imgSelectOptions' : calculateImageSelectOptions
				} ),
			],
		} );

		const onSelect = (function() {
			frame.setState( 'cropper' );
		} );
		frame.off( 'select', onSelect );
		frame.on( 'select', onSelect );

		const onCropped = function( croppedImage ) {
			let url = croppedImage.url,
				attachmentId = croppedImage.id,
				w = croppedImage.width,
				h = croppedImage.height;

			// Send the attachment id to our hidden input. URL to explicit output.
			jQuery( '#' + inputID + '-url' ).val( url );
			jQuery( '#' + inputID + '-id' ).val( attachmentId );
		};
		frame.off( 'cropped', onCropped );
		frame.on( 'cropped', onCropped );

		const onSkippedCrop = function( selection ) {
			let url = selection.get( 'url' ),
				attachmentId = selection.get( 'id' ),
				w = selection.get( 'width' ),
				h = selection.get( 'height' );

			// Send the attachment id to our hidden input. URL to explicit output.
			jQuery( '#' + inputID + '-url' ).val( url );
			jQuery( '#' + inputID + '-id' ).val( attachmentId );
		};
		frame.off( 'skippedcrop', onSkippedCrop );
		frame.on( 'skippedcrop', onSkippedCrop );

		const onDone = function( imageSelection ) {
			jQuery( '#' + inputID + '-select' ).text( l10n.labels[ inputType ].imgChange );
			jQuery( '#' + inputID + '-url' ).prop( 'readonly', true ).css( 'opacity', 0 ).animate(
				{ 'opacity' : 1 },
				{ 'queue' : true, 'duration' : 1000 },
				'swing'
			);

			appendRemoveButton( $button, { 'id': inputID, 'type': inputType }, true );
			tsf.registerChange();
		};
		frame.off( 'skippedcrop cropped', onDone );
		frame.on( 'skippedcrop cropped', onDone );

		frame.open();
	}

	/**
	 * Removes the image editor image on request.
	 *
	 * @since 3.1.0
	 * @access private
	 *
	 * @function
	 * @param {!jQuery.event.target} target jQuery event.target
	 * @param {array} data The input data.
	 * @return {(undefined|null)}
	 */
	const appendRemoveButton = ( target, data, animate ) => {

		if ( target && data.id ) {
			if ( ! $( '#' + data.id + '-remove' ).length ) {
				target.after(
					'<button type=button id="'
						+ data.id + '-remove" class="tsf-remove-image-button button button-small"'
						+ ' data-input-id="' + data.id + '"'
						+ ' data-input-type="' + data.type + '"'
					+ ' title="' + l10n.labels[ data.type ].imgRemoveTitle + '">' + l10n.labels[ data.type ].imgRemove + '</button>'
				);
				if ( animate ) {
					$( '#' + data.id + '-remove' ).css( 'opacity', 0 ).animate(
						{ 'opacity' : 1 },
						{ 'queue' : true, 'duration' : 1000 }
					);
				}
			}
		}

		//* Reset cache.
		resetImageEditorRemovalActions();
	}

	/**
	 * Removes the image editor image on request.
	 *
	 * @since 3.1.0
	 * @access private
	 *
	 * @function
	 * @param {!jQuery.Event} event jQuery event
	 * @return {(undefined|null)}
	 */
	const removeEditorImage = ( event ) => {

		let inputID = $( event.target ).data( 'input-id' ),
			inputType = $( event.target ).data( 'input-type' );

		if ( $( '#' + inputID + '-select' ).prop( 'disabled' ) )
			return;

		$( '#' + inputID + '-select' ).addClass( 'disabled' ).prop( 'disabled', true );

		//* event.target.id === '#' + inputID + '-remove'.
		$( '#' + inputID + '-remove' ).addClass( 'disabled' ).prop( 'disabled', true ).fadeOut( 500, function() {
			$( this ).remove();
			$( '#' + inputID + '-select' ).text( l10n.labels[ inputType ].imgSelect ).removeClass( 'disabled' ).removeProp( 'disabled' );
		} );

		let $inputUrl = $( '#' + inputID + '-url' );

		$inputUrl.val( '' );
		if ( ! $inputUrl.data( 'readonly' ) ) {
			$inputUrl.removeProp( 'readonly' );
		}
		$inputUrl.css( 'opacity', 0 ).animate(
			{ 'opacity' : 1 },
			{ 'queue' : true, 'duration' : 500 },
			'swing'
		);

		$( '#' + inputID + '-id' ).val( '' );

		tsf.registerChange();
	}

	/**
	 * Builds constructor for media cropper.
	 *
	 * @since 3.1.0
	 * @access private
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	const extendCropper = () => {

		if ( 'undefined' !== typeof cropper.control )
			return;

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
		let TSFCropper,
			Controller = wp.media.controller;

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
		let TSFView,
			View = wp.media.view;

		TSFView = View.Cropper.extend( {
			className: 'crop-content tsf-image',
			ready: function () {
				View.Cropper.prototype.ready.apply( this, arguments );
			},
			onImageLoad: function() {
				let imgOptions = this.controller.get( 'imgSelectOptions' ),
					imgSelect;

				if ( typeof imgOptions === 'function' ) {
					imgOptions = imgOptions( this.options.attachment, this.controller );
				}

				//= Seriously Core team, was this condition too hard to implement?
				if ( 'undefined' === typeof imgOptions.aspectRatio ) {
					imgOptions = _.extend( imgOptions, {
						parent: this.$el,
						onInit: function() {
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
					} );
				}
				this.trigger( 'image-loaded' );
				imgSelect = this.controller.imgSelect = this.$image.imgAreaSelect( imgOptions );
			},
		} );

		TSFCropper = Controller.Cropper.extend( {
			createCropContent: function() {
				this.cropperView = new TSFView( {
					controller: this,
					attachment: this.get( 'selection' ).first()
				} );
				this.cropperView.on( 'image-loaded', this.createCropToolbar, this );
				this.frame.content.set( this.cropperView );
			},
			doCrop: function( attachment ) {
				let cropDetails = attachment.get( 'cropDetails' ),
					control = cropper.control; // prototyped earlier.

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

				return wp.ajax.post( 'tsf-crop-image', {
					'nonce' : tsf.nonces['upload_files'],
					'id' : attachment.get( 'id' ),
					'context' : 'tsf-image',
					'cropDetails' : cropDetails
				} );
			}
		} );

		TSFCropper.prototype.control = {};

		cropper = TSFCropper;
	}

	/**
	 * Returns a set of options, computed from the attached image data and
	 * control-specific data, to be fed to the imgAreaSelect plugin in
	 * wp.media.view.Cropper.
	 *
	 * @since 3.1.0
	 * @access private
	 *
	 * @function
	 * @param {wp.media.model.Attachment} attachment
	 * @param {wp.media.controller.Cropper} controller
	 * @return {Object} imgSelectOptions
	 */
	const calculateImageSelectOptions = ( attachment, controller ) => {

		let control = cropper.control;

		let flexWidth  = !! parseInt( control.params.flex_width, 10 ),
			flexHeight = !! parseInt( control.params.flex_height, 10 ),
			xInit = parseInt( control.params.width, 10 ),
			yInit = parseInt( control.params.height, 10 );

		let realWidth  = attachment.get( 'width' ),
			realHeight = attachment.get( 'height' ),
			ratio = xInit / yInit,
			xImg  = xInit,
			yImg  = yInit,
			x1,
			y1,
			imgSelectOptions;

		let canSkipCrop;
		if ( control.params.isFlex ) {
			canSkipCrop = ! mustBeCropped( control.params.flex_width, control.params.flex_height, realWidth, realHeight );
		} else {
			//= Not flex. If ratios match, then we can skip.
			canSkipCrop = ratio === realWidth / realHeight;
		}

		controller.set( 'control', control.params );
		controller.set( 'canSkipCrop', canSkipCrop );

		if ( realWidth / realHeight > ratio ) {
			yInit = realHeight;
			xInit = yInit * ratio;
		} else {
			xInit = realWidth;
			yInit = xInit / ratio;
		}

		x1 = ( realWidth - xInit ) / 2;
		y1 = ( realHeight - yInit ) / 2;

		imgSelectOptions = {
			'handles' : true,
			'keys' : true,
			'instance' : true,
			'persistent' : true,
			'imageWidth' : realWidth,
			'imageHeight' : realHeight,
			'minWidth' : xImg > xInit ? xInit : xImg,
			'minHeight' : yImg > yInit ? yInit : yImg,
			'x1' : x1,
			'y1' : y1,
			'x2' : xInit + x1,
			'y2' : yInit + y1
		};

		// @TODO Convert set img min-width/height to output ratio.
		// i.e. 200x2000 will become x = 1500/2000*200 = 150px, which is too small.
		//= Unlikely...

		if ( ! control.params.isFlex ) {
			imgSelectOptions.handles = 'corners';
			imgSelectOptions.aspectRatio = xInit + ':' + yInit;
		} else if ( ! flexHeight && ! flexWidth ) {
			imgSelectOptions.aspectRatio = xInit + ':' + yInit;
		} else {
			if ( flexHeight ) {
				imgSelectOptions.minHeight = 200;
				imgSelectOptions.maxWidth = realWidth;
			}
			if ( flexWidth ) {
				imgSelectOptions.minWidth = 200;
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
	 *
	 * @function
	 * @param {Number} dstW
	 * @param {Number} dstH
	 * @param {Number} imgW
	 * @param {Number} imgH
	 * @return {Boolean}
	 */
	const mustBeCropped = ( dstW, dstH, imgW, imgH ) => {

		if ( imgW <= dstW && imgH <= dstH )
			return false;

		return true;
	}

	/**
	 * Checks if input is filled in by image editor.
	 *
	 * @since 3.1.0
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	const checkImageEditorInput = () => {

		let $buttons = $( '.tsf-set-image-button' );

		if ( $buttons.length ) {
			let inputID = '',
				inputType = '',
				$valID = '';

			$.each( $buttons, function( index, value ) {
				inputID = $( value ).data( 'input-id' );
				inputType = $( value ).data( 'input-type' );
				$valID = $( '#' + inputID + '-id' );

				if ( $valID.length && $valID.val() > 0 ) {
					$( '#' + inputID + '-url' ).prop( 'readonly', true );
					appendRemoveButton( $( value ), { 'id': inputID, 'type': inputType }, false );
				}

				if ( $( '#' + inputID + '-url' ).val() ) {
					$( '#' + inputID + '-select' ).text( l10n.labels[ inputType ].imgChange );
				}
			} );
		}
	}

	/**
	 * Resets jQuery image editor cache for when the removal button appears.
	 *
	 * @since 3.1.0
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	const resetImageEditorSetActions = () => {
		$( '.tsf-set-image-button' ).off( 'click', openImageEditor );
		$( '.tsf-set-image-button' ).on( 'click', openImageEditor );
	}

	/**
	 * Resets jQuery image editor cache for when the removal button appears.
	 *
	 * @since 3.1.0
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	const resetImageEditorRemovalActions = () => {
		$( '.tsf-remove-image-button' ).off( 'click', removeEditorImage );
		$( '.tsf-remove-image-button' ).on( 'click', removeEditorImage );
	}

	/**
	 * Sets up jQuery image editor cache.
	 *
	 * @since 3.1.0
	 *
	 * @function
	 * @return {(undefined|null)}
	 */
	const setupImageEditorActions = () => {
		resetImageEditorSetActions();
		resetImageEditorRemovalActions();

		$( '.tsf-enable-media-if-js' ).removeProp( 'disabled' ).removeClass( 'tsf-enable-media-if-js' );
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
			// Initialize image uploader button cache.
			$( document.body ).ready( setupImageEditorActions );

			// Determine image editor button input states.
			$( document.body ).ready( checkImageEditorInput );
		}
	}, {} );
}( jQuery );
jQuery( window.tsfMedia.load );
