/**
 * This file holds The SEO Framework plugin's JS code for Primary Term Selection for Gutenberg.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/autodescription/>
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
 * Holds tsfPTGB (tsf primary term Gutenberg) values in an object to avoid polluting global namespace.
 *
 * This is a self-constructed function assigned as an object.
 *
 * @since 3.2.0
 * @since 4.1.5 Rewritten to support Gutenberg 11.3.0+ / WP 5.9+
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.tsfPTGB = function( $ ) {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 4.0.0
	 * @access public
	 * @type {(Object<string, *>)|boolean|null} l10n Localized strings
	 */
	const l10n = 'undefined' !== typeof tsfPTL10n && tsfPTL10n;

	/**
	 * @since 4.1.5
	 * @access private
	 */
	const supportedTaxonomies = l10n?.taxonomies;

	/**
	 * @since 4.1.5
	 * @access private
	 */
	const { createElement, Fragment, Component, useState, useEffect } = wp.element;
	const { SelectControl } = wp.components;
	const { useSelect } = wp.data;
	const { unescape } = lodash;

	/**
	 * Arrays are unique objects, meaning [] !== [].
	 * Let's store one that's immutable, so we have [] === [], preventing state changes.
	 * @since 4.1.5
	 * @access private
	 */
	const EMPTY_ARRAY = [];

	/**
	 * @since 4.1.5
	 * @access private
	 */
	const DEFAULT_QUERY = {
		per_page: -1,
		orderby:  'id',
		order:    'asc',
		_fields:  'id,name',
	};

	/**
	 * @since 4.1.5
	 * @access private
	 */
	const _geti18n = ( taxonomySlug, what ) => supportedTaxonomies[ taxonomySlug ].i18n[ what ] || '';

	let _registeredFields = {}; // memo. TODO Make Map()? This doesn't need to be fast...
	/**
	 * @since 4.1.5
	 * @access private
	 */
	const _primaryTerm = taxonomySlug => {

		const _dataHolder = () => document.getElementById( `autodescription[_primary_term_${taxonomySlug}]` );

		const get = () => +_dataHolder().value;
		const set = id => +( _dataHolder().value = +id );

		const revalidate = selectedTerms => {
			let primaryTerm = get();

			if ( selectedTerms.includes( primaryTerm ) )
				return primaryTerm;

			return set( selectedTerms?.[0] || 0 );
		}

		const register = () => {
			let wrap = document.getElementById( 'tsf-gutenberg-data-holder' );
			if ( ! wrap ) return _registeredFields[ taxonomySlug ] = false;

			let template = wp.template( 'tsf-primary-term-selector' )( { taxonomy: supportedTaxonomies[ taxonomySlug ] } );
			return _registeredFields[ taxonomySlug ] = !! $( template ).appendTo( wrap );
		}
		const isRegistered = () => _registeredFields[ taxonomySlug ] || false;

		return {
			get,
			set,
			revalidate,
			register,
			isRegistered
		};
	};

	/**
	 * Initializes primary term selection for Gutenberg.
	 *
	 * @since 3.2.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initPrimaryTerm = () => {

		if ( ! Object.keys( supportedTaxonomies ).length ) return;

		function primaryTermSelector( { taxonomySlug } ) {

			const primaryTerm                 = _primaryTerm( taxonomySlug );
			const [ selection, setSelection ] = useState( primaryTerm.get() );

			// Ref: <https://github.com/WordPress/gutenberg/pull/33418#issuecomment-903686737>
			const {
				selectedTerms,
				loading,
				availableTerms,
			} = useSelect(
				select => {
					const { getTaxonomy, getEntityRecords, isResolving } = select( 'core' );
					const { getEditedPostAttribute }                     = select( 'core/editor' );

					const _taxonomy = getTaxonomy( taxonomySlug );
					const _query    = [ 'taxonomy', taxonomySlug, DEFAULT_QUERY ];

					return {
						selectedTerms:  getEditedPostAttribute( _taxonomy?.rest_base ) || EMPTY_ARRAY, // GB bug: causes 70 useSelect-calls on-load.
						loading:        isResolving( 'getEntityRecords', _query ),
						availableTerms: getEntityRecords( ..._query ) || EMPTY_ARRAY
					};
				},
				[ taxonomySlug ]
			);

			useEffect( () => {
				if ( ! selectedTerms.includes( +selection ) || primaryTerm.get() !== +selection ) {
					primaryTerm.revalidate( selectedTerms );
					setSelection( primaryTerm.get() );
				}
			} );

			if ( selectedTerms?.length < 2 ) {
				// Delete entry.
				primaryTerm.set( 0 );
				// Hide selector. Halt function.
				return null;
			}

			const onChange = termId => {
				if ( ! selectedTerms.includes( +termId ) ) return;
				primaryTerm.set( termId );
				setSelection( primaryTerm.get() );
				'tsfAys' in window && tsfAys.registerChange();
			};

			const getSelectOptions = () => {
				return availableTerms.map( term =>
					selectedTerms.includes( term?.id )
						&& {
							value: term.id,
							label: unescape( term?.name )
						}
				).filter( Boolean ) || '';
			};

			const isDisabled = () => ! ( selectedTerms.length && availableTerms.length && ! loading );

			return createElement(
				SelectControl,
				{
					label:     _geti18n( taxonomySlug, 'selectPrimary' ),
					value:     selection,
					className: 'tsf-pt-gb-selector',
					onChange:  onChange,
					options:   getSelectOptions(),
					disabled:  isDisabled(),
					// Yes, it's neater. No, we shouldn't. Not our bug.
					// style:     { lineHeight: 'unset' }, // <https://github.com/WordPress/gutenberg/issues/27194>
				},
			);
		}

		const PrimaryTermSelectorFilter = OriginalComponent => class extends Component {
			render() {
				// If we cannot store the primary term for this taxonomy, bail.
				if ( ! _primaryTerm( this.props?.slug ).isRegistered() )
					return createElement( OriginalComponent, { ...this.props } );

				return createElement(
					Fragment,
					null,
					createElement(
						OriginalComponent,
						{ ...this.props },
					),
					createElement(
						primaryTermSelector,
						{ taxonomySlug: this.props?.slug },
					)
				);
			}
		}

		for ( let taxonomySlug in supportedTaxonomies )
			_primaryTerm( taxonomySlug ).register();

		wp.hooks.addFilter(
			'editor.PostTaxonomyType',
			'tsf/pt',
			PrimaryTermSelectorFilter
		);
	}

	return Object.assign( {
		/**
		 * Initialises all aspects of the scripts.
		 * You shouldn't call this.
		 *
		 * @since 3.2.0
		 * @access protected
		 *
		 * @function
		 * @return {undefined}
		 */
		load: () => {
			document.body.addEventListener( 'tsf-onload', _initPrimaryTerm );
		}
	}, {}, {
		l10n
	} );
}( jQuery );
window.tsfPTGB.load();
