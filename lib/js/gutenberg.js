/**
 * This file holds The SEO Framework plugin's JS code for Gutenberg integration.
 *
 * Nope, no JSNext, I have better things to do than sending out megabytes of already
 * available data and writing a compiler.
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
 * Holds tsfGutenberg values in a global object.
 *
 * This is a standalone testing module. Not ready (at all) for production.
 * NOTE: CONCEPT! Not implemented. Here for educational purposes.
 * @link <https://github.com/sybrew/the-seo-framework/commit/f7552fcfc4e3624ab1fb73a75bc4b1b919b7c8fd>
 *
 * @since 3.1.0
 * NOTE: This is probably not the best implementation.
 *       Don't try to learn from this.
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfGutenberg = function( $, wp ) {

	// TSFPanels is defined in inc\views\templates\gutenberg\panels.php @f7552fcfc4e3624ab1fb73a75bc4b1b919b7c8fd
	// The file isn't available in release, so this code won't run.
	if ( ! TSFPanels ) return;

	const {
		PluginSidebar,
		PluginSidebarMoreMenuItem
	} = wp.editPost;
	const { createElement, Fragment } = wp.element;
	const { registerPlugin } = wp.plugins;
	// const { __, setLocaleData } = wp.i18n;
	const {
		// SlotFillProvider,
		// Slot,
		// Fill,
		Panel,
		PanelRow,
		PanelBody,
	} = wp.components;
	// const { PostTypeSupportCheck } = wp.editor;
	// const { compose } = wp.compose;
	// const { withSelect, withDispatch } = wp.data;

	const defaults = {
		namespace: 'the-seo-framework',
		target:    'tsf-sidebar',
		slot:      'tsf-sidebar-slot',
	};
	const i18n = {
		sidebar: {
			name: 'SEO Settings',
		},
		settings: {
			general: 'General',
		}
	};

	const TSFSidebar = () => createElement(
		Fragment, // Initializes sidebar fragment.
		{},
		createElement(
			PluginSidebar, // Initializes contents for sidebar.
			{
				name:  defaults.target,
				title: i18n.sidebar.name,
			},
			//? Maybe create sidebar-header here...  SettingsHeader from wp.components
			createElement(
				Panel, // Initializes panel wrapper.
				{
					className: 'tsf-panel-body',
				},
				...renderComponents( TSFPanels )
			)
		),
		createElement( // This allows users to select the plugin from the "More" menu.
			PluginSidebarMoreMenuItem,
			{
				target: defaults.target,
			},
			i18n.sidebar.name
		)
	);

	let store = [];
	const renderComponents = ( panels ) => {
		if ( store.length ) return store; //!! FIXME: this destroys all previous input...
		panels.forEach( ( panel ) => {
			store.push( createElement(
				PanelBody,
				panel.data.components,
				createContent( panel.data.tmpl )
			) );
		} );
		return store;
	};

	const createContent = ( template ) => {
		let tmpl = document.getElementById( template );
		return createElement(
			PanelRow,
			{},
			createElement(
				'div',
				{
					style: {
						maxWidth: '100%', // prevents overflow
					},
					dangerouslySetInnerHTML: { // I'm not going to spend 600 hours reimplementing for a beta product.
						__html: tmpl instanceof Element ? tmpl.innerHTML : __( 'Failed to load content.', 'autodescription' ),
					}
				}
			)
		)
	};

	const _registerPlugin = () => {
		registerPlugin( defaults.namespace, {
			icon:  'search',
			render: TSFSidebar,
		} );
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
			_registerPlugin();
		}
	}, {} );
}( jQuery, wp );
jQuery( window.tsfGutenberg.load );
