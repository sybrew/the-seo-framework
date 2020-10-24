/**
 * This file holds The SEO Framework plugin's JS code for Primary Term Selection for Gutenberg.
 *
 * @author Sybre Waaijer <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/autodescription/>
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2020 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
	 * @since 3.2.0
	 * @access private
	 */
	const { addFilter } = wp.hooks;
	const { createElement, Fragment } = wp.element;
	const { SelectControl } = wp.components;
	const { addQueryArgs } = wp.url;
	const apiFetch = wp.apiFetch;
	const { invoke, unescape } = lodash;

	/**
	 * @since 3.2.0
	 * @access private
	 */
	const DEFAULT_QUERY = {
		per_page: -1,
		orderby:  'id',
		order:    'asc',
		_fields:  'id,name',
	};

	/**
	 * Initializes primary term selection for gutenberg.
	 *
	 * @since 3.2.0
	 * @access private
	 *
	 * @function
	 * @return {undefined}
	 */
	const _initPrimaryTerm = () => {

		if ( ! Object.keys( l10n.taxonomies ).length )
			return;

		let taxonomies       = l10n.taxonomies,
			inputTemplate    = wp.template( 'tsf-primary-term-selector' ),
			registeredFields = {};

		const geti18n = ( taxonomy, what ) => what in taxonomies[ taxonomy ].i18n && taxonomies[ taxonomy ].i18n[ what ] || '';

		const getPrimaryTermHolder = taxonomy => document.getElementById( `autodescription[_primary_term_${taxonomy}]` );
		const getPrimaryTermID     = taxonomy => +getPrimaryTermHolder( taxonomy ).value;
		const setPrimaryTermID     = ( taxonomy, id ) => +( getPrimaryTermHolder( taxonomy ).value = +id );

		const addDataInput = ( taxonomy ) => {
			let wrap = document.getElementById( 'tsf-gutenberg-data-holder' );
			if ( ! wrap ) return registeredFields[ taxonomy ] = false;

			let template = inputTemplate( { 'taxonomy' : taxonomies[ taxonomy ] } );
			return registeredFields[ taxonomy ] = !! $( template ).appendTo( wrap );
		}
		const hasDataInput = ( taxonomy ) => registeredFields[ taxonomy ];

		const revalidatePrimaryTerm = ( taxonomy, terms ) => {
			if ( terms.indexOf( getPrimaryTermID( taxonomy ) ) < 0 ) {
				// Set to first found term, or empty the value if no term is selected.
				if ( 0 in terms ) {
					setPrimaryTermID( taxonomy, terms[0] );
				} else {
					setPrimaryTermID( taxonomy, 0 );
				}
			}
		}

		var dataStores = {};
		class DataStore {
			constructor( slug ) {
				this.slug = slug;
				this.reset();
			}

			registeredData() {
				return !! Object.keys( this.data ).length;
			}

			reset() {
				this.data = {};
			}

			read() {
				return this.data;
			}

			get( what ) {
				return what in this.data && this.data[ what ] || null;
			}

			set( what, value ) {
				return this.data[ what ] = value;
			}
		}
		const createStore = slug => dataStores[ slug ] = new DataStore( slug );
		const getStore    = slug => dataStores[ slug ] || createStore( slug );

		class PrimaryTermSelectorHandler extends React.Component {
			componentDidMount() {
				// Mounted for the first time.
				if ( ! this.dsAccess().registeredData() )
					this.registerData();

				if ( this.dsAccess().registeredData() ) {
					// Remounted thanks to adding a new term (from 1 selected, now 2).
					if ( this.hasNewTerms() ) {
						this.fetchTerms();
					} else {
						this.setState( {
							loading: false,
						} );
					}
				}
			}

			componentWillUnmount() {
				invoke( this.fetchRequest, [ 'abort' ] );
			}

			componentDidUpdate( prevProps, prevState ) {
				// No noteworthy update occurred. Probably, only the state updated.
				if ( prevProps.terms === this.props.terms ) return;

				if ( this.props.terms.length > 1 ) {
					if ( ! this.dsAccess().get( 'availableTerms' ) || this.hasNewTerms() ) {
						this.fetchTerms();
					}
				}
			}

			registerData() {
				this.dsAccess().set( 'availableTerms', [] );
				this.fetchTerms();
			}

			dsAccess() {
				return getStore( this.props.slug );
			}

			hasNewTerms() {
				let availableTermsIds = ( this.dsAccess().get( 'availableTerms' ) || [] ).map( x => x.id );
				return ! this.props.terms.every( id => availableTermsIds.includes( id ) );
			}
			isTermAvailable( id ) {
				return this.dsAccess().get( 'availableTerms' ).some( term => term.id === id );
			}

			fetchTerms() {
				const { taxonomy } = this.props;
				if ( ! taxonomy ) return;

				this.setState( {
					loading: true,
				} );

				// Abort previous, if any? WP doesn't do that either...
				this.fetchRequest = apiFetch( {
					path: addQueryArgs(
						`/wp/v2/${ taxonomy.rest_base }`,
						DEFAULT_QUERY
					),
				} );
				this.fetchRequest.then(
					( terms ) => { // resolve
						this.fetchRequest = null;
						this.setState( {
							loading: false,
						} );
						this.dsAccess().set( 'availableTerms', terms );
						this.forceUpdate();
					},
					( xhr ) => { // reject
						if ( xhr.statusText === 'abort' ) {
							return;
						}
						this.fetchRequest = null;
						this.setState( {
							loading: false,
						} );
						// Users may see empty select fields now... Strip them? See getTermName instead?
						// if ( ! this.dsAccess().get( 'availableTerms' ).length ) {}
					}
				);
			}
		}

		class TermSelector extends PrimaryTermSelectorHandler {
			constructor() {
				super( ...arguments );
				this.onChange = this.onChange.bind( this );
				this.state = {
					loading: true,
				}
			}

			getTermName( id ) {
				let availableTerms = this.dsAccess().get( 'availableTerms' );

				if ( ! Array.isArray( availableTerms ) ) return '';

				let term = availableTerms.find( term => term.id === id );
				return term && term.name || '';
			}

			getSelectOptions() {
				// Terms might not've been registered (yet).
				if ( ! Array.isArray( this.props.terms ) ) return '';

				return this.props.terms.sort().map( id => {
					return {
						value: id,
						// unescape is also in tsf.js. Prevents double-escape, since React re-escapes.
						label: unescape( this.getTermName( id ) )
					};
				} );
			}

			onChange( value ) {
				this.setState( {
					options: this.getSelectOptions(),
					value:   setPrimaryTermID( this.props.slug, value )
				} );
				'tsfAys' in window && tsfAys.registerChange();
			}

			isDisabled() {
				// Terms might not've been registered (yet).
				if ( this.state.loading || ! Array.isArray( this.props.terms ) )
					return true;

				let availableTerms = this.dsAccess().get( 'availableTerms' );
				return ! Array.isArray( availableTerms ) || ! availableTerms.length;
			}

			render() {
				// React causes this to loop back to render() once because of debugRenderPhaseSideEffectsForStrictMode...
				return createElement(
					SelectControl,
					{
						label:     geti18n( this.props.slug, 'selectPrimary' ),
						value:     getPrimaryTermID( this.props.slug ),
						className: 'tsf-pt-gb-selector',
						onChange:  this.onChange,
						options:   this.getSelectOptions(),
						disabled:  this.isDisabled(),
					},
				);
			}
		}

		const primaryTermSelectorFilter = PostTaxonomyType => class extends React.Component {
			initSelectors() {
				const { slug, terms } = this.props;
				if ( hasDataInput( slug ) ) {
					revalidatePrimaryTerm( slug, terms );
					if ( terms.length > 1 ) {
						return createElement(
							Fragment,
							{},
							createElement(
								TermSelector,
								this.props,
							)
						);
					}
				}
				return null;
			}

			render() {
				if ( ! ( this.props.slug in taxonomies ) ) {
					// Return original component.
					return createElement(
						PostTaxonomyType,
						this.props,
					);
				}

				// React causes this to loop back to render() once because of debugRenderPhaseSideEffectsForStrictMode...
				return createElement(
					Fragment,
					{},
					createElement(
						PostTaxonomyType,
						this.props,
					),
					// If we can access PostTaxonomyType from here, we can bypass the API fetch and use its results from state instead.
					this.initSelectors()
				);
			}
		}

		const _init = () => {
			for ( let taxonomy in taxonomies ) {
				addDataInput( taxonomy );
			}

			addFilter(
				'editor.PostTaxonomyType',
				'tsf/pt',
				primaryTermSelectorFilter,
				20
			);
		}
		_init();
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
