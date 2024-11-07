/**
 * This file holds The SEO Framework plugin's JS code for Primary Term Selection for Gutenberg.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/autodescription/>
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 */
window.tsfPTGB = function () {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 4.0.0
	 * @access public
	 * @type {(Object<string,*>)|boolean|null} l10n Localized strings
	 */
	const l10n = tsfPTL10n;

	/**
	 * @since 4.1.5
	 * @access private
	 */
	const supportedTaxonomies = l10n.taxonomies;

	/**
	 * @since 4.1.5
	 * @access private
	 */
	const { createElement, Fragment, Component, useState, useEffect } = wp.element;
	const { SelectControl } = wp.components;
	const { useSelect } = wp.data;

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
	 * @param {String} taxonomySlug
	 * @param {String} what The i18n to get.
	 */
	function _geti18n( taxonomySlug, what ) {
		return supportedTaxonomies[ taxonomySlug ]?.i18n[ what ] || '';
	}

	/**
	 * @since 5.1.0
	 * @access private
	 * @param {Number} id
	 * @param {String} taxonomySlug
	 */
	function dispatchUpdateEvent( id, taxonomySlug ) {
		document.dispatchEvent(
			new CustomEvent(
				'tsf-updated-primary-term',
				{
					detail: { id, taxonomy: taxonomySlug },
				}
			)
		);
	}

	const _registeredFields = new Map();
	/**
	 * @since 4.1.5
	 * @access private
	 * @param {String} taxonomySlug
	 * @returns {{
 	 *     get:                   () => integer,
	 *     set:                   ( id: string|integer, fallback: string|integer ) => integer,
	 *     revalidate:            ( selectedTerms: integer[] ) => integer,
	 *     registerPostField:     () => void,
	 *     isPostFieldRegistered: () => boolean,
	 * }}
	 */
	function _primaryTerm( taxonomySlug ) {

		const _primaryTermField = () => document.getElementById( `autodescription[_primary_term_${taxonomySlug}]` );

		const get = () => +( _primaryTermField().value );
		const set = ( id, fallback ) => {
			id = +id;
			_primaryTermField().value = id;
			dispatchUpdateEvent( id || +fallback, taxonomySlug );
			return id;
		};

		const revalidate = selectedTerms => {
			const primaryTerm = get();

			if ( selectedTerms.includes( primaryTerm ) )
				return primaryTerm;

			return set( selectedTerms?.[0] || 0 );
		}

		const registerPostField = () => {
			const wrap = document.getElementById( 'tsf-gutenberg-data-holder' );

			if ( ! wrap ) {
				_registeredFields.set( taxonomySlug, false );
			} else {
				wrap.insertAdjacentHTML(
					'beforeend',
					wp.template( 'tsf-primary-term-selector' )( { taxonomy: supportedTaxonomies[ taxonomySlug ] } )
				);

				_registeredFields.set( taxonomySlug, true );
			}
		}
		const isPostFieldRegistered = () => !! _registeredFields.get( taxonomySlug );

		return { get, set, revalidate, registerPostField, isPostFieldRegistered };
	}

	/**
	 * Initializes primary term selection for Gutenberg.
	 *
	 * @since 3.2.0
	 * @access private
	 */
	function _initPrimaryTerm() {

		if ( ! Object.keys( supportedTaxonomies ).length ) return;

		function primaryTermSelector( props ) {

			const { taxonomySlug }            = props;
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
						availableTerms: getEntityRecords( ..._query ) || EMPTY_ARRAY,
					};
				},
				[ taxonomySlug ],
			);

			// Forward data to our store based on mutability of "selection".
			// TODO add test for availableTerms.includes( +selection ) -> User deletes terms while in Gutenberg?
				// -> `! availableTerms.map( term => term.id ).includes( id )`
				// = Mega edge case Gutenberg doesn't handle (properly) either.
			useEffect(
				() => {
					if ( ! selectedTerms.includes( +selection ) || primaryTerm.get() !== +selection ) {
						primaryTerm.revalidate( selectedTerms );
						setSelection( primaryTerm.get() );
					}
				},
				[ selectedTerms ],
			);

			if ( selectedTerms?.length < 2 ) {
				// Reset stored selection. Append fallback.
				primaryTerm.set(
					0,
					   selectedTerms?.[0]
					|| availableTerms?.[0]?.id
					|| 0
				);
				// Hide selector. Halt function.
				return null;
			}

			const onChange = termId => {
				if ( ! selectedTerms.includes( +termId ) ) return;
				primaryTerm.set( termId );
				setSelection( primaryTerm.get() );
				tsfAys.registerChange();
			};

			const getSelectOptions = () => {
				return availableTerms.map(
					term =>
						   selectedTerms.includes( term?.id )
						&& {
							value: term.id,
							label: tsf.decodeEntities( term?.name ), // TODO, consider using https://github.com/WordPress/gutenberg/pull/47561/files.
						},
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
					__nextHasNoMarginBottom: true, // WP 6.7 'next/future' default.
				},
			);
		}

		const PrimaryTermSelectorFilter = OriginalComponent => class extends Component {
			render() {
				// If we cannot store the primary term for this taxonomy, bail.
				if ( ! _primaryTerm( this.props?.slug ).isPostFieldRegistered() )
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
						{
							taxonomySlug: this.props?.slug,
						},
					)
				);
			}
		}

		for ( let taxonomySlug in supportedTaxonomies )
			_primaryTerm( taxonomySlug ).registerPostField();

		wp.hooks.addFilter(
			'editor.PostTaxonomyType',
			'tsf/pt',
			PrimaryTermSelectorFilter,
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
		 */
		load: () => {
			document.body.addEventListener( 'tsf-ready', _initPrimaryTerm );
		},
	}, {
		l10n,
	} );
}();
window.tsfPTGB.load();
