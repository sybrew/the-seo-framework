/**
 * This file holds The SEO Framework plugin's JS code externs
 * for Google's Closure Compiler.
 *
 * @author Sybre Waaijer https://cyberwire.nl/
 * @link https://wordpress.org/plugins/the-seo-framework/
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

/**
 * @fileoverview Externs for The SEO Framework tsf.js
 *
 * These are listed per reference, and should only be included if all protected methods
 * and properties are wished for to be made public.
 *
 * @see https://github.com/sybrew/the-seo-framework
 * @see https://developer.theseoframework.com/ (i.e. https://theseoframework.com/api/)
 * @externs
 */

/**
 * @protected
 * @type {(Boolean|null|undefined)}
 */
tsf.prototype.settingsChanged;

/**
 * @protected
 * @type {Boolean}
 */
tsf.prototype.useTagline;

/**
 * @protected
 * @type {String}
 */
tsf.prototype.titleSeparator;

/**
 * @protected
 * @type {String}
 */
tsf.prototype.descriptionSeparator;

/**
 * @protected
 * @type {(String|number)}
 */
tsf.prototype.counterType;

/**
 * @protected
 * @type {(Boolean|null|undefined)}
 */
tsf.prototype.hasInput;

/**
 * @protected
 * @type {(String|null)}
 */
tsf.prototype.additionsClass;

/**
 * @protected
 * @type {!Object}
 */
tsf.prototype.cropper;

/**
 * @protected
 * @return {!jQuery}
 */
tsf.prototype.docTitles = function() {};

/**
 * @protected
 * @return {!jQuery}
 */
tsf.prototype.docDescriptions = function() {};

/**
 * @protected
 * @param {String} text
 * @return {Boolean|null}
 */
tsf.prototype.confirm = function( text ) {};

/**
 * @protected
 * @param {!jQuery.Event} event
 * @return {undefined}
 */
tsf.prototype.updateCharacterCountDescription = function( event ) {};

/**
 * @protected
 * @param {!jQuery.Event} event
 * @return {undefined}
 */
tsf.prototype.updateCharacterCountTitle = function( event ) {};

/**
 * @protected
 * @param {String} str
 * @return {String}
 */
tsf.prototype.escapeStr = function( str ) {};

/**
 * @protected
 * @param {!jQuery.Event} event
 * @return {undefined}
 */
tsf.prototype.separatorSwitchTitle = function( event ) {};

/**
 * @protected
 * @param {!jQuery.Event} event
 * @return {undefined}
 */
tsf.prototype.separatorSwitchDesc = function( event ) {};

/**
 * @protected
 * @return {undefined}
 */
tsf.prototype.statusBarHover = function() {};

/**
 * @protected
 * @param {!jQuery.Event} event
 * @return {undefined}
 */
tsf.prototype.statusBarHoverEnter = function( event ) {};

/**
 * @protected
 * @param {!jQuery.Event} event
 * @return {undefined}
 */
tsf.prototype.statusBarHoverMove = function( event ) {};

/**
 * @protected
 * @param {!jQuery.Event} event
 * @return {undefined}
 */
tsf.prototype.statusBarHoverLeave = function( event ) {};

/**
 * @protected
 * @return {undefined}
 */
tsf.prototype.removeDesc = function() {};

/**
 * @protected
 * @param {!jQuery.Event} event
 * @return {undefined}
 */
tsf.prototype.tabToggle = function( event ) {};

/**
 * @protected
 * @param {!jQuery.Event} event
 * @return {undefined}
 */
tsf.prototype.taglineToggleTitle = function( event ) {};

/**
 * @protected
 * @param {!jQuery.Event} event
 * @return {undefined}
 */
tsf.prototype.taglineToggleDesc = function( event ) {};

/**
 * @protected
 * @param {!jQuery.Event} event
 * @return {undefined}
 */
tsf.prototype.titleLocationToggle = function( event ) {};

/**
 * @protected
 * @param {!jQuery.Event} event
 * @return {undefined}
 */
tsf.prototype.titlePrefixToggle = function( event ) {};

/**
 * @protected
 * @param {!jQuery.Event} event
 * @return {undefined}
 */
tsf.prototype.additionsToggleDesc = function( event ) {};

/**
 * @protected
 * @return {undefined}
 */
tsf.prototype.taglineToggleOnload = function() {};

/**
 * @protected
 * @param {!jQuery.Event} event
 * @return {undefined}
 */
tsf.prototype.titleProp = function( event ) {};

/**
 * @protected
 * @param {!jQuery.Event} event
 * @return {undefined}
 */
tsf.prototype.taglineProp = function( event ) {};

/**
 * @protected
 * @return {undefined}
 */
tsf.prototype.taglinePropTrigger = function() {};

/**
 * @protected
 * @return {undefined}
 */
tsf.prototype.titleToggle = function() {};

/**
 * @protected
 * @return {undefined}
 */
tsf.prototype.attachUnsavedChangesListener = function() {};

/**
 * @protected
 * @return {undefined}
 */
tsf.prototype.registerChange = function() {};

/**
 * @protected
 * @return {undefined}
 */
tsf.prototype.confirmedReset = function() {};

/**
 * @protected
 * @return {Boolean|null}
 */
tsf.prototype.confirmedReset = function() {};

/**
 * @protected
 * @param {!jQuery.Event} event
 * @return {(!jQuery|undefined)}
 */
tsf.prototype.dynamicPlaceholder = function( event ) {};

/**
 * @protected
 * @return {undefined}
 */
tsf.prototype.selectTitleInput = function() {};

/**
 * @protected
 * @return {undefined}
 */
tsf.prototype.triggerDescriptionOnLoad = function() {};

/**
 * @protected
 * @return {undefined}
 */
tsf.prototype.triggerTitleOnLoad = function() {};

/**
 * @protected
 * @return {undefined}
 */
tsf.prototype.onLoadUnregisterChange = function() {};

/**
 * @protected
 * @param {!jQuery.Event} event
 * @return {undefined}
 */
tsf.prototype.dismissNotice = function( event ) {};

/**
 * @protected
 * @param {String} target
 * @return {undefined}
 */
tsf.prototype.setAjaxLoader = function( target ) {};

/**
 * @protected
 * @param {String} target
 * @param {Boolean} success
 * @return {undefined}
 */
tsf.prototype.unsetAjaxLoader = function( target, success ) {};

/**
 * @protected
 * @param {String} target
 * @return {undefined}
 */
tsf.prototype.resetAjaxLoader = function( target ) {};

/**
 * @protected
 * @param {!jQuery.Event} event
 * @return {undefined}
 */
tsf.prototype.counterUpdate = function( event ) {};

/**
 * @protected
 * @param {String} target
 * @param {Boolean} success
 * @return {undefined}
 */
tsf.prototype.counterUpdatedResponse = function( target, success ) {};

/**
 * @protected
 * @return {undefined}
 */
tsf.prototype.additionsClassInit = function() {};

/**
 * @protected
 * @return {undefined}
 */
tsf.prototype.updateCounters = function() {};

/**
 * @protected
 * @param {String} type
 * @return {String}
 */
tsf.prototype.getCounterName = function( type ) {};

/**
 * @protected
 * @param {String} type
 * @return {String}
 */
tsf.prototype.getSep = function( type ) {};

/**
 * @protected
 * @param {!jQuery.Event} event
 * @return {(undefined|null)}
 */
tsf.prototype.openImageEditor = function( event ) {};

/**
 * @protected
 * @return {(Object<?, ?>|undefined|null)}
 */
tsf.prototype.extendCropper = function() {};

/**
 * @protected
 * @param {wp.media.model.Attachment} attachment
 * @param {wp.media.controller.Cropper} controller
 * @return {Object}
 */
tsf.prototype.calculateImageSelectOptions = function( attachment, controller ) {};

/**
 * @protected
 * @param {Number} dstW
 * @param {Number} dstH
 * @param {Number} imgW
 * @param {Number} imgH
 * @return {Boolean}
 */
tsf.prototype.mustBeCropped = function( dstW, dstH, imgW, imgH ) {};

/**
 * @protected
 * @return {undefined}
 */
tsf.prototype.ready = function() {};
