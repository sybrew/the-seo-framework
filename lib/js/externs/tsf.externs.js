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
 * This file acts as a sort of interface of all public tsf JS object methods.
 *
 * @see https://github.com/sybrew/the-seo-framework
 * @see https://developer.theseoframework.com/ (i.e. https://theseoframework.com/api/)
 * @see https://raw.githubusercontent.com/sybrew/the-seo-framework/master/lib/js/tsf.externs.js
 * @externs
 */

/**
 * @constructor
 * @struct
 */
function tsf() {};

/**
 * @type {string}
 */
tsf.nonce;

/**
 * @type {Object<string, string>}
 */
tsf.nonces;

/**
 * @const {Object<string, string>}
 */
tsf.i18n = {};

/**
 * @const {Object<string, boolean|number>}
 */
tsf.states = {};

/**
 * @const {Object<string, string>}
 */
tsf.params = {};

/**
 * @const {Object<string, ?>}
 */
tsf.other = {};

/**
 * @const {Object<string, ?>}
 */
tsf.cropper = {};

/**
 * @type {(Object<string, *>|Array<Object<string, *>>)}
 * @const
 */
var tsfL10n = {};

/**
 * @type {string}
 */
tsfL10n.prototype.nonce;

/**
 * @const {Object<string, string>}
 */
tsfL10n.prototype.i18n;

/**
 * @const {Object<string, boolean|number>}
 */
tsfL10n.prototype.states;

/**
 * @const {Object<string, *>}
 */
tsfL10n.prototype.params = {};

/**
 * @const {Object<string, ?>}
 */
tsfL10n.prototype.other = {};

/**
 * @return {undefined}
 */
tsf.prototype.statusBarHover = function() {};

/**
 * @type {!Object<?, ?>}
 */
tsf.calculateImageSelectOptions.prototype.attachment = {};

/**
 * @param {string} arg1
 * @return {!Object}
 * @nosideeffects
 */
tsf.calculateImageSelectOptions.attachment.prototype.get = function( arg1 ) {};

/**
 * @param {string} arg1
 * @param {*} arg2
 * @return {!Object}
 * @nosideeffects
 */
tsf.calculateImageSelectOptions.attachment.prototype.set = function( arg1, arg2 ) {};

/**
 * @type {!Object<?, ?>}
 * @nosideeffects
 */
tsf.cropper.prototype.control = {};

/**
 * @type {!Object<string, ?>}
 * @nosideeffects
 */
tsf.cropper.control.prototype.params = {};

/**
 * @type {number}
 */
tsf.cropper.prototype.width;

/**
 * @type {number}
 */
tsf.cropper.prototype.height;

/**
 * @type {number}
 */
tsf.cropper.prototype.dst_width;

/**
 * @type {number}
 */
tsf.cropper.prototype.dst_height;

/**
 * @type {number}
 */
tsf.cropper.prototype.flex_width;

/**
 * @type {number}
 */
tsf.cropper.prototype.flex_height;

/**
 * +========================+
 * +====== WP externs ======+
 * +========================+
 *
 * Specifically, cropper, color picker and AJAX.
 */

/**
 * @type {string}
 */
var ajaxurl;

/**
 * @constructor
 * @struct
 */
function wp() {};

/**
 * @const
 * @type {!Object<*, *>}
 */
wp.prototype.media = {};

/**
 * @type {!Object<*, *>}
 */
wp.media.prototype.controller = {};

/**
 * @param {*=} arg1
 * @return {*}
 */
wp.media.prototype.query = function( arg1 ) {};

/**
 * @type {!Object<*, *>}
 */
wp.media.controller.prototype.Cropper = {};

/**
 * @type {!Object<*, *>}
 */
wp.media.controller.prototype.Library = {};

/**
 * @type {!Object<*, *>}
 */
wp.prototype.ajax = {};

/**
 * @param {string} action
 * @param {*=} arg2
 * @return {*}
 */
wp.ajax.prototype.post = function( action, arg2 ) {};

/**
 * @constructor {Object.wp}
 * @struct
 */
var frame = {};

/**
 * @param {string} action
 * @param {*=} arg2
 * @return {undefined}
 */
frame.prototype.on = function( action, arg2 ) {};

/**
 * @return {undefined}
 */
frame.prototype.open = function() {};

/**
 * @param {string} state
 * @return {undefined}
 */
frame.prototype.setState = function( state ) {};

/**
 * @constructor
 * @param {!Object} attachment
 * @return {!jQuery.jqXHR}
 */
function doCrop( attachment ) {};

/**
 * @param {string} arg1
 * @return {!Object}
 * @nosideeffects
 */
doCrop.prototype.get = function( arg1 ) {};

/**
 * @return {string}
 * @nosideeffects
 */
doCrop.get.prototype.edit = function() {};

/**
 * @param {(string|Object<string,*>)} arg1
 * @return {(string|!jQuery)}
 */
jQuery.prototype.wpColorPicker = function( arg1 ) {};
