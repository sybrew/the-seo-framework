<?php
/**
 * @package The_SEO_Framework\Classes\Deprecated
 */
namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2018 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\Deprecated
 *
 * Contains all deprecated functions.
 *
 * @since 2.8.0
 * @ignore
 */
final class Deprecated {

	/**
	 * Constructor. Does nothing.
	 */
	public function __construct() { }

	/**
	 * Fetch set Term data.
	 *
	 * @since 2.6.0
	 * @since 2.7.0 Handles term object differently for upgraded database.
	 * @since 3.0.0 Deprecated.
	 *
	 * @deprecated.
	 *
	 * @param object|null $term The TT object, if it isn't set, one is fetched.
	 * @param int         $term_id The term object.
	 * @return array The SEO Framework TT data.
	 */
	public function get_term_data( $term = null, $term_id = 0 ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->get_term_data( $term, $term_id )', '3.0.0', 'the_seo_framework()->get_term_meta( $term_id )' );

		if ( is_null( $term ) )
			$term = $tsf->fetch_the_term( $term_id );

		if ( isset( $term->term_id ) )
			return $tsf->get_term_meta( $term->term_id );

		//* Return null if no term can be set.
		return null;
	}

	/**
	 * Creates canonical URL.
	 *
	 * @since 2.0.0
	 * @since 2.4.2 : Refactored arguments
	 * @since 2.8.0 : No longer tolerates $id as Post object.
	 * @since 2.9.0 : When using 'home => true' args parameter, the home path is added when set.
	 * @since 2.9.2 Added filter usage cache.
	 * @since 3.0.0 Deprecated.
	 * @deprecated
	 * @staticvar array $_has_filters
	 *
	 * @param string $url the url
	 * @param array $args : accepted args : {
	 *    @param bool $paged Return current page URL without pagination if false
	 *    @param bool $paged_plural Whether to add pagination for the second or later page.
	 *    @param bool $from_option Get the canonical uri option
	 *    @param object $post The Post Object.
	 *    @param bool $external Whether to fetch the current WP Request or get the permalink by Post Object.
	 *    @param bool $is_term Fetch url for term.
	 *    @param object $term The term object.
	 *    @param bool $home Fetch home URL.
	 *    @param bool $forceslash Fetch home URL and slash it, always.
	 *    @param int $id The Page or Term ID.
	 * }
	 * @return string Escape url.
	 */
	public function the_url( $url = '', $args = array() ) {

		$tsf = \the_seo_framework();

		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->the_url()', '3.0.0', 'the_seo_framework()->get_canonical_url()' );

		$args = $tsf->reparse_url_args( $args );

		/**
		 * Fetch permalink if Feed.
		 * @since 2.5.2
		 */
		if ( $tsf->is_feed() )
			$url = \get_permalink();

		//* Reset cache.
		$tsf->url_slashit = true;
		$tsf->unset_current_subdomain();
		$tsf->current_host = '';

		$path = '';
		$scheme = '';
		$slashit = true;

		if ( false === $args['home'] && empty( $url ) ) {
			/**
			 * Get URL from options.
			 * @since 2.2.9
			 */
			if ( $args['get_custom_field'] && $tsf->is_singular() ) {
				$custom_url = $tsf->get_custom_field( '_genesis_canonical_uri' );

				if ( $custom_url ) {
					$url = $custom_url;
					$tsf->url_slashit = false;
					$parsed_url = \wp_parse_url( $custom_url );
					$scheme = isset( $parsed_url['scheme'] ) ? $parsed_url['scheme'] : 'http';
				}
			}

			if ( empty( $url ) )
				$path = $tsf->generate_url_path( $args );
		} elseif ( $args['home'] ) {
			$path = $tsf->get_home_path();
		}

		static $_has_filters = null;
		if ( null === $_has_filters ) {
			$_has_filters = array();
			$_has_filters['the_seo_framework_url_path'] = \has_filter( 'the_seo_framework_url_path' );
			$_has_filters['the_seo_framework_url_output_args'] = \has_filter( 'the_seo_framework_url_output_args' );
		}

		if ( $_has_filters['the_seo_framework_url_path'] ) {
			/**
			 * Applies filters 'the_seo_framework_url_path' : array
			 *
			 * @since 2.8.0
			 *
			 * @param string $path the URL path.
			 * @param int $id The current post, page or term ID.
			 * @param bool $external Whether the call is made from outside the current ID scope. Like from the Sitemap.
			 */
			$path = (string) \apply_filters( 'the_seo_framework_url_path', $path, $args['id'], $args['external'] );
		}

		if ( $_has_filters['the_seo_framework_url_output_args'] ) {
			/**
			 * Applies filters 'the_seo_framework_sanitize_redirect_url' : array
			 *
			 * @since 2.8.0
			 *
			 * @param array : { 'url' => The full URL built from $path, 'scheme' => The preferred scheme }
			 * @param string $path the URL path.
			 * @param int $id The current post, page or term ID.
			 * @param bool $external Whether the call is made from outside the current ID scope. Like from the Sitemap.
			 */
			$url_filter = (array) \apply_filters( 'the_seo_framework_url_output_args', array(), $path, $args['id'], $args['external'] );

			if ( $url_filter ) {
				$url = $url_filter['url'];
				$scheme = $url_filter['scheme'];
			}
		}

		//* Non-custom URL
		if ( empty( $url ) ) {
			//* Reset cache if request is for the home URL.
			if ( $args['home'] )
				$tsf->unset_current_subdomain();

			$url = $tsf->add_url_host( $path );
			$scheme = '';

			$url = $tsf->add_url_subdomain( $url );
		}

		$scheme = $scheme ?: $tsf->get_preferred_scheme();

		$url = $tsf->set_url_scheme( $url, $scheme );

		if ( $tsf->url_slashit ) {
			if ( $args['forceslash'] ) {
				$url = \trailingslashit( $url );
			} elseif ( $slashit ) {
				$url = \user_trailingslashit( $url );
			}
		}

		if ( $tsf->pretty_permalinks ) {
			$url = \esc_url( $url, [ 'http', 'https' ] );
		} else {
			//* Keep the &'s more readable.
			$url = \esc_url_raw( $url, [ 'http', 'https' ] );
		}

		return $url;
	}

	/**
	 * Parse and sanitize url args.
	 *
	 * @since 2.4.2
	 * @since 2.9.2 Added filter usage cache.
	 * @since 3.0.0 Deprecated.
	 * @deprecated
	 * @staticvar bool $_has_filter
	 *
	 * @param array $args required The passed arguments.
	 * @param array $defaults The default arguments.
	 * @param bool $get_defaults Return the default arguments. Ignoring $args.
	 * @return array $args parsed args.
	 */
	public function parse_url_args( $args = array(), $defaults = array(), $get_defaults = false ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->parse_url_args()', '3.0.0' );

		//* Passing back the defaults reduces the memory usage.
		if ( empty( $defaults ) ) :
			$defaults = array(
				'paged'            => false,
				'paged_plural'     => true,
				'get_custom_field' => true,
				'external'         => false,
				'is_term'          => false,
				'post'             => null,
				'term'             => null,
				'home'             => false,
				'forceslash'       => false,
				'id'               => $tsf->get_the_real_ID(),
			);

			static $_has_filter = null;
			if ( null === $_has_filter )
				$_has_filter = \has_filter( 'the_seo_framework_url_args' );

			if ( $_has_filter ) {
				/**
				 * @applies filters the_seo_framework_url_args : {
				 *    @param bool $paged Return current page URL without pagination if false
				 *    @param bool $paged_plural Whether to add pagination for the second or later page.
				 *    @param bool $from_option Get the canonical uri option
				 *    @param object $post The Post Object.
				 *    @param bool $external Whether to fetch the current WP Request or get the permalink by Post Object.
				 *    @param bool $is_term Fetch url for term.
				 *    @param object $term The term object.
				 *    @param bool $home Fetch home URL.
				 *    @param bool $forceslash Fetch home URL and slash it, always.
				 *    @param int $id The Page or Term ID.
				 * }
				 *
				 * @since 2.5.0
				 * @since 3.0.0 Deprecated
				 * @deprecated
				 *
				 * @param array $defaults The url defaults.
				 * @param array $args The input args.
				 */
				$defaults = (array) \apply_filters( 'the_seo_framework_url_args', $defaults, $args );
			}
		endif;

		//* Return early if it's only a default args request.
		if ( $get_defaults )
			return $defaults;

		// phpcs:disable -- whitespace OK.
		//* Array merge doesn't support sanitation. We're simply type casting here.
		$args['paged']            = isset( $args['paged'] )            ? (bool) $args['paged']            : $defaults['paged'];
		$args['paged_plural']     = isset( $args['paged_plural'] )     ? (bool) $args['paged_plural']     : $defaults['paged_plural'];
		$args['get_custom_field'] = isset( $args['get_custom_field'] ) ? (bool) $args['get_custom_field'] : $defaults['get_custom_field'];
		$args['external']         = isset( $args['external'] )         ? (bool) $args['external']         : $defaults['external'];
		$args['is_term']          = isset( $args['is_term'] )          ? (bool) $args['is_term']          : $defaults['is_term'];
		$args['post']             = isset( $args['post'] )             ? (object) $args['post']           : $defaults['post'];
		$args['term']             = isset( $args['term'] )             ? (object) $args['term']           : $defaults['term'];
		$args['home']             = isset( $args['home'] )             ? (bool) $args['home']             : $defaults['home'];
		$args['forceslash']       = isset( $args['forceslash'] )       ? (bool) $args['forceslash']       : $defaults['forceslash'];
		$args['id']               = isset( $args['id'] )               ? (int) $args['id']                : $defaults['id'];
		// phpcs:enable

		return $args;
	}

	/**
	 * Reparse URL args.
	 *
	 * @since 2.6.2
	 * @since 2.9.2 Now passes args to filter.
	 * @since 3.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param array $args required The passed arguments.
	 * @return array $args parsed args.
	 */
	public function reparse_url_args( $args = array() ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->reparse_url_args()', '3.0.0' );

		$default_args = $tsf->parse_url_args( $args, '', true );

		if ( is_array( $args ) ) {
			if ( empty( $args ) ) {
				$args = $default_args;
			} else {
				$args = $tsf->parse_url_args( $args, $default_args );
			}
		} else {
			//* Old style parameters are used. Doing it wrong.
			$tsf->_doing_it_wrong( __METHOD__, 'Use $args = array() for parameters.', '2.4.2' );
			$args = $default_args;
		}

		return $args;
	}

	/**
	 * Generate URL from arguments.
	 *
	 * @since 2.6.0
	 * @since 3.0.0 Deprecated.
	 * @deprecated
	 * @NOTE: Handles full path, including home directory.
	 *
	 * @param array $args the URL args.
	 * @return string $path
	 */
	public function generate_url_path( $args = array() ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->generate_url_path()', '3.0.0' );

		$args = $tsf->reparse_url_args( $args );

		if ( $tsf->is_archive() || $args['is_term'] ) :

			$term = $args['term'];

			//* Term or Taxonomy.
			if ( ! isset( $term ) )
				$term = \get_queried_object();

			if ( isset( $term->taxonomy ) ) {
				//* Registered Terms and Taxonomies.
				$path = $tsf->get_relative_term_url( $term, $args );
			} elseif ( ! $args['external'] && isset( $GLOBALS['wp']->request ) ) {
				//* Everything else.
				$_url = \trailingslashit( \get_option( 'home' ) ) . $GLOBALS['wp']->request;
				$path = $tsf->set_url_scheme( $_url, 'relative' );
			} else {
				//* Nothing to see here...
				$path = '';
			}
		elseif ( $tsf->is_search() ) :
			$_url = \get_search_link();
			$path = $tsf->set_url_scheme( $_url, 'relative' );
		else :
			/**
			 * Reworked to use the $args['id'] check based on get_the_real_ID.
			 * @since 2.6.0 & 2.6.2
			 */
			$post_id = isset( $args['post']->ID ) ? $args['post']->ID : $args['id'];

			if ( $tsf->pretty_permalinks && $post_id && $tsf->is_singular( $post_id ) ) {
				$post = \get_post( $post_id );

				//* Don't slash draft links.
				if ( isset( $post->post_status ) && ( 'auto-draft' === $post->post_status || 'draft' === $post->post_status ) )
					$tsf->url_slashit = false;
			}

			$path = $tsf->build_singular_relative_url( $post_id, $args );
		endif;

		return $path;
	}

	/**
	 * Generates relative URL for the Homepage and Singular Posts.
	 *
	 * @since 2.6.5
	 * @NOTE: Handles full path, including home directory.
	 * @since 2.8.0: Continues on empty post ID. Handles it as HomePage.
	 * @since 3.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $post_id The ID.
	 * @param array $args The URL arguments.
	 * @return string relative Post or Page url.
	 */
	public function build_singular_relative_url( $post_id = null, $args = array() ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->build_singular_relative_url()', '3.0.0' );

		if ( empty( $post_id ) ) {
			//* We can't fetch the post ID when there's an external request.
			if ( $args['external'] ) {
				$post_id = 0;
			} else {
				$post_id = $tsf->get_the_real_ID();
			}
		}

		$args = $tsf->reparse_url_args( $args );

		if ( $args['external'] || ! $tsf->is_real_front_page() || ! $tsf->is_front_page_by_id( $post_id ) ) {
			$url = \get_permalink( $post_id );
		} elseif ( $tsf->is_real_front_page() || $tsf->is_front_page_by_id( $post_id ) ) {
			$url = \get_home_url();
		} elseif ( ! $args['external'] ) {
			if ( isset( $GLOBALS['wp']->request ) )
				$url = \trailingslashit( \get_home_url() ) . $GLOBALS['wp']->request;
		}

		//* No permalink found.
		if ( ! isset( $url ) )
			return '';

		$paged = false;

		if ( false === $args['external'] ) {
			$paged = $tsf->is_singular() ? $tsf->page() : $tsf->paged();
			$paged = $tsf->maybe_get_paged( $paged, $args['paged'], $args['paged_plural'] );
		}

		if ( $paged ) {
			if ( $tsf->pretty_permalinks ) {
				if ( $tsf->is_singular() ) {
					$url = \trailingslashit( $url ) . $paged;
				} else {
					$url = \trailingslashit( $url ) . 'page/' . $paged;
				}
			} else {
				if ( $tsf->is_singular() ) {
					$url = \add_query_arg( 'page', $paged, $url );
				} else {
					$url = \add_query_arg( 'paged', $paged, $url );
				}
			}
		}

		return $tsf->set_url_scheme( $url, 'relative' );
	}

	/**
	 * Generates relative URL for current term.
	 *
	 * @since 2.4.2
	 * @since 2.7.0 Added home directory to output.
	 * @since 3.0.0 Deprecated.
	 * @deprecated
	 * @global object $wp_rewrite
	 * @NOTE: Handles full path, including home directory.
	 *
	 * @param object $term The term object.
	 * @param array|bool $args {
	 *    'external' : Whether to fetch the WP Request or get the permalink by Post Object.
	 *    'paged'    : Whether to add pagination for all types.
	 *    'paged_plural' : Whether to add pagination for the second or later page.
	 * }
	 * @return string Relative term or taxonomy URL.
	 */
	public function get_relative_term_url( $term = null, $args = array() ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->get_relative_term_url()', '3.0.0' );

		global $wp_rewrite;

		if ( ! is_array( $args ) ) {
			/**
			 * @since 2.6.0
			 * '$args = array()' replaced '$no_request = false'.
			 */
			$tsf->_doing_it_wrong( __METHOD__, 'Use $args = array() for parameters.', '2.6.0' );

			$no_request = (bool) $args;
			$args = $tsf->parse_url_args( '', '', true );
			$args['external'] = $no_request;
		}

		// We can't fetch the Term object within sitemaps.
		if ( $args['external'] && is_null( $term ) )
			return '';

		if ( is_null( $term ) )
			$term = \get_queried_object();

		$taxonomy = $term->taxonomy;
		$path = $wp_rewrite->get_extra_permastruct( $taxonomy );

		$slug = $term->slug;
		$t = \get_taxonomy( $taxonomy );

		$paged = $tsf->maybe_get_paged( $tsf->paged(), $args['paged'], $args['paged_plural'] );

		if ( empty( $path ) ) :
			//* Default permalink structure.

			if ( 'category' === $taxonomy ) {
				$path = '?cat=' . $term->term_id;
			} elseif ( isset( $t->query_var ) && '' !== $t->query_var ) {
				$path = '?' . $t->query_var . '=' . $slug;
			} else {
				$path = '?taxonomy=' . $taxonomy . '&term=' . $slug;
			}

			if ( $paged )
				$path .= '&paged=' . $paged;

			//* Don't slash it.
			$tsf->url_slashit = false;

		else :
			if ( $t->rewrite['hierarchical'] ) {
				$hierarchical_slugs = array();
				$ancestors = \get_ancestors( $term->term_id, $taxonomy, 'taxonomy' );

				foreach ( (array) $ancestors as $ancestor ) {
					$ancestor_term = \get_term( $ancestor, $taxonomy );
					$hierarchical_slugs[] = $ancestor_term->slug;
				}

				$hierarchical_slugs = array_reverse( $hierarchical_slugs );
				$hierarchical_slugs[] = $slug;

				$path = str_replace( "%$taxonomy%", implode( '/', $hierarchical_slugs ), $path );
			} else {
				$path = str_replace( "%$taxonomy%", $slug, $path );
			}

			if ( $paged )
				$path = \trailingslashit( $path ) . 'page/' . $paged;

			$path = \user_trailingslashit( $path, 'category' );
		endif;

		//* Add plausible domain subdirectories.
		$url = \trailingslashit( \get_option( 'home' ) ) . ltrim( $path, ' \\/' );
		$path = $tsf->set_url_scheme( $url, 'relative' );

		return $path;
	}

	/**
	 * Adds subdomain to input URL.
	 *
	 * @since 2.6.5
	 * @since 3.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $url The current URL without subdomain.
	 * @return string $url Fully qualified URL with possible subdomain.
	 */
	public function add_url_subdomain( $url = '' ) {

		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->add_url_subdomain()', '3.0.0' );

		$url = $tsf->make_fully_qualified_url( $url );

		//* Add subdomain, if set.
		if ( $subdomain = $tsf->get_current_subdomain() ) {
			$parsed_url = \wp_parse_url( $url );
			$scheme = isset( $parsed_url['scheme'] ) ? $parsed_url['scheme'] : 'http';
			$url = str_replace( $scheme . '://', '', $url );

			//* Put it together.
			$url = $scheme . '://' . $subdomain . '.' . $url;
		}

		return $url;
	}

	/**
	 * Fetches current subdomain set by $this->set_current_subdomain();
	 *
	 * @since 2.7.0
	 * @since 3.0.0 Deprecated.
	 * @deprecated
	 * @staticvar string $subdomain
	 *
	 * @param null|string $set Whether to set a new subdomain.
	 * @param bool $unset Whether to remove subdomain from cache.
	 * @return string|bool The set subdomain, false if none is set.
	 */
	public function get_current_subdomain( $set = null, $unset = false ) {

		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->get_current_subdomain()', '3.0.0' );

		static $subdomain = null;

		if ( isset( $set ) )
			$subdomain = \esc_html( $set );

		if ( $unset )
			unset( $subdomain );

		if ( isset( $subdomain ) )
			return $subdomain;

		return false;
	}

	/**
	 * Sets current working subdomain.
	 *
	 * @since 2.7.0
	 * @since 3.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $subdomain The current subdomain.
	 * @return string The set subdomain.
	 */
	public function set_current_subdomain( $subdomain = '' ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->unset_current_subdomain()', '3.0.0' );

		return $tsf->get_current_subdomain( $subdomain );
	}

	/**
	 * Unsets current working subdomain.
	 *
	 * @since 2.7.0
	 * @since 3.0.0 Deprecated.
	 * @deprecated
	 */
	public function unset_current_subdomain() {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->unset_current_subdomain()', '3.0.0' );

		$tsf->get_current_subdomain( null, true );
	}

	/**
	 * Create full valid URL with parsed host.
	 * Don't forget to use set_url_scheme() afterwards.
	 *
	 * Note: will return $path if no host can be found.
	 *
	 * @since 2.6.5
	 * @since 3.0.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $path Current path.
	 * @return string Full valid URL with http host.
	 */
	public function add_url_host( $path = '' ) {

		$tsf = \the_seo_framework();

		$tsf->_deprecated_function( 'the_seo_framework()->add_url_host()', '3.0.0' );

		$host = $tsf->current_host ?: $tsf->get_home_host();

		$scheme = $host ? 'http://' : '';

		return $url = $scheme . \trailingslashit( $host ) . ltrim( $path, ' \\/' );
	}

	/**
	 * Fetches home URL subdirectory path. Like "wordpress.org/plugins/".
	 *
	 * @since 2.7.0
	 * @since 3.0.0 Deprecated.
	 * @deprecated
	 * @staticvar string $cache
	 *
	 * @return string The home URL path.
	 */
	public function get_home_path() {

		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->get_home_path()', '3.0.0' );

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		$path = '';

		$parsed_url = \wp_parse_url( \get_option( 'home' ) );

		if ( ! empty( $parsed_url['path'] ) && $path = ltrim( $parsed_url['path'], ' \\/' ) )
			$path = '/' . $path;

		return $cache = $path;
	}

	/**
	 * Cache current URL in static variable
	 * Must be called inside the loop
	 *
	 * @since 2.2.2
	 * @since 3.0.0 Deprecated.
	 * @deprecated
	 * @staticvar array $url_cache
	 *
	 * @param string $url the url
	 * @param int $post_id the page id, if empty it will fetch the requested ID, else the page uri
	 * @param bool $paged Return current page URL with pagination
	 * @param bool $from_option Get the canonical uri option
	 * @param bool $paged_plural Whether to allow pagination on second or later pages.
	 * @return string The url
	 */
	public function the_url_from_cache( $url = '', $post_id = null, $paged = false, $from_option = true, $paged_plural = true ) {

		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->the_url_from_cache()', '3.0.0', 'the_seo_framework()->get_current_canonical_url()' );

		return $tsf->get_current_canonical_url();
	}

	/**
	 * Cache home URL in static variable
	 *
	 * @since 2.5.0
	 * @since 2.9.0 Now returns subdirectory installations paths too.
	 * @since 3.0.0 1: Now no longer regenerates home URL when parameters differ.
	 *              2: Deprecated.
	 * @deprecated
	 * @staticvar string $url
	 *
	 * @param bool $force_slash Force slash
	 * @return string The url
	 */
	public function the_home_url_from_cache( $force_slash = false ) {

		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->the_home_url_from_cache()', '3.0.0', 'the_seo_framework()->get_homepage_permalink()' );

		static $url;

		if ( ! $url )
			$url = $tsf->get_homepage_permalink();

		return $force_slash ? \trailingslashit( $url ) : $url;
	}

	/**
	 * Returns the TSF meta output Object cache key.
	 *
	 * @since 2.8.0
	 * @since 3.1.0 Deprecated.
	 * @deprecated
	 * @uses THE_SEO_FRAMEWORK_DB_VERSION as cache key buster.
	 * @see $this->get_meta_output_cache_key_by_type();
	 *
	 * @param int $id The ID. Defaults to $this->get_the_real_ID();
	 * @return string The TSF meta output cache key.
	 */
	public function get_meta_output_cache_key( $id = 0 ) {

		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->get_meta_output_cache_key()', '3.1.0', 'the_seo_framework()->get_meta_output_cache_key_by_query()' );

		/**
		 * Cache key buster.
		 * Busts cache on each new db version.
		 */
		$key = $tsf->generate_cache_key( $id ) . '_' . THE_SEO_FRAMEWORK_DB_VERSION;

		/**
		 * Give each paged pages/archives a different cache key.
		 * @since 2.2.6
		 */
		$page = (string) $tsf->page();
		$paged = (string) $tsf->paged();

		return $cache_key = 'seo_framework_output_' . $key . '_' . $paged . '_' . $page;
	}

	/**
	 * Alias of $this->get_preferred_scheme().
	 * Typo.
	 *
	 * @since 2.8.0
	 * @since 2.9.2 Added filter usage cache.
	 * @since 3.0.0 Silently deprecated.
	 * @since 3.1.0 Hard deprecated.
	 * @deprecated
	 * @staticvar string $scheme
	 *
	 * @return string The preferred URl scheme.
	 */
	public function get_prefered_scheme() {
		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->get_prefered_scheme()', '3.1.0', 'the_seo_framework()->get_preferred_scheme()' );
		return $tsf->get_preferred_scheme();
	}

	/**
	 * Cache description in static variable
	 * Must be called inside the loop
	 *
	 * @since 2.2.2
	 * @deprecated
	 * @since 3.0.6 Silently deprecated.
	 * @since 3.1.0 1. Hard deprecated.
	 *              2. Removed caching.
	 *
	 * @param bool $social Determines whether the description is social.
	 * @return string The description
	 */
	public function description_from_cache( $social = false ) {
		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->description_from_cache()', '3.1.0', 'the_seo_framework()->get_description()' );
		return $tsf->generate_description( '', array( 'social' => $social ) );
	}

	/**
	 * Gets the title. Main function.
	 * Always use this function for the title unless you're absolutely sure what you're doing.
	 *
	 * This function is used for all these: Taxonomies and Terms, Posts, Pages, Blog, front page, front-end, back-end.
	 *
	 * @since 1.0.0
	 * @since 3.1.0 Deprecated
	 * @deprecated
	 *
	 * Params required wp_title filter :
	 * @param string $title The Title to return
	 * @param string $sep The Title sepeartor
	 * @param string $seplocation The Title sepeartor location ( accepts 'left' or 'right' )
	 *
	 * @since 2.4.0:
	 * @param array $args : accepted args : {
	 *    @param int term_id The Taxonomy Term ID when taxonomy is also filled in. Else post ID.
	 *    @param string taxonomy The Taxonomy name.
	 *    @param bool page_on_front Page on front condition for example generation.
	 *    @param bool placeholder Generate placeholder, ignoring options.
	 *    @param bool notagline Generate title without tagline.
	 *    @param bool meta Ignore doing_it_wrong. Used in og:title/twitter:title
	 *    @param bool get_custom_field Do not fetch custom title when false.
	 *    @param bool description_title Fetch title for description.
	 *    @param bool is_front_page Fetch front page title.
	 * }
	 * @return string $title Title
	 */
	public function title( $title = '', $sep = '', $seplocation = '', $args = [] ) {

		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->title()', '3.1.0', 'the_seo_framework()->get_title(...)' );

		if ( isset( $args['term_id'] ) ) {
			$new_args = [];
			$new_args['id'] = $args['term_id'];
		}
		if ( isset( $args['taxonomy'] ) ) {
			$new_args = isset( $new_args ) ? $new_args : [];
			$new_args['taxonomy'] = $args['taxonomy'];
		}
		if ( ! empty( $args['is_front_page'] ) ) {
			$new_args = [ 'id' => $tsf->get_the_front_page_ID() ];
		}

		return $tsf->get_title( empty( $new_args ) ? null : $new_args );
	}

	/**
	 * Generate the title based on conditions for the home page.
	 *
	 * @since 2.3.4
	 * @since 2.3.8 Now checks tagline option.
	 * @since 3.1.0 Deprecated.
	 * @deprecated
	 * @access private
	 *
	 * @param bool $get_custom_field Fetch Title from Custom Fields.
	 * @param string $seplocation The separator location
	 * @param string $deprecated Deprecated: The Home Page separator location
	 * @param bool $escape Parse Title through saninitation calls.
	 * @param bool $get_option Whether to fetch the SEO Settings option.
	 * @return array {
	 *    'title'       => (string) $title : The Generated "Title"
	 *    'blogname'    => (string) $blogname : The Generated "Blogname"
	 *    'add_tagline' => (bool) $add_tagline : Whether to add the tagline
	 *    'seplocation' => (string) $seplocation : The Separator Location
	 * }
	 */
	public function generate_home_title() {
		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->generate_home_title()', '3.1.0', 'the_seo_framework()->get_title(...)' );
		return array(
			'title' => $tsf->get_unprocessed_generated_title( array( 'id' => $tsf->get_the_front_page_ID() ) ),
			'blogname' =>  $tsf->get_home_page_tagline(),
			'add_tagline' => $tsf->use_home_page_title_tagline(),
			'seplocation' => $tsf->get_title_seplocation(),
		);
	}

	/**
	 * Gets the archive Title, including filter. Also works in admin.
	 *
	 * @NOTE Taken from WordPress core. Altered to work for metadata.
	 * @see WP Core get_the_archive_title()
	 *
	 * @since 2.6.0
	 * @since 2.9.2 : Added WordPress core filter 'get_the_archive_title'
	 * @since 3.0.4 : 1. Removed WordPress core filter 'get_the_archive_title'
	 *                2. Added filter 'the_seo_framework_generated_archive_title'
	 * @since 3.1.0 Deprecated.
	 * @deprecated
	 *
	 * @param \WP_Term|null $term The Term object.
	 * @param array $args The Title arguments.
	 * @return string The Archive Title, not escaped.
	 */
	public function get_the_real_archive_title( $term = null, $args = array() ) {
		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->get_the_real_archive_title()', '3.1.0', 'the_seo_framework()->get_generated_archive_title()' );
		return $tsf->get_generated_archive_title( $term );
	}

	/**
	 * Determines whether to use a title prefix or not.
	 *
	 * @since 2.6.0
	 * @since 3.0.0 Removed second parameter.
	 * @since 3.1.0 Deprecated.
	 * @deprecated
	 *
	 * @return bool
	 */
	public function use_archive_prefix() {
		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->use_archive_prefix()', '3.1.0', 'the_seo_framework()->use_generated_archive_prefix()' );
		return $tsf->use_generated_archive_prefix();
	}

	/**
	 * Adds title pagination, if paginated.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $title The current Title.
	 * @return string Title with maybe pagination added.
	 */
	public function add_title_pagination( $title ) {

		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->add_title_pagination()', '3.1.0', 'the_seo_framework()->merge_title_pagination()' );

		if ( $this->is_404() || $this->is_admin() || $this->is_preview() )
			return $title;
		$page = $this->page();
		$paged = $this->paged();
		if ( $page && $paged ) {
			/**
			 * @since 2.4.3
			 * Adds page numbering within the title.
			 */
			if ( $paged >= 2 || $page >= 2 ) {
				$sep = $this->get_title_separator();
				$page_number = max( $paged, $page );
				/**
				 * Applies filters 'the_seo_framework_title_pagination' : string
				 *
				 * @since 2.9.4
				 *
				 * @param string $pagination  The pagination addition.
				 * @param string $title       The old title.
				 * @param int    $page_number The page number.
				 * @param string $sep         The separator used.
				 */
				$pagination = \apply_filters_ref_array(
					'the_seo_framework_title_pagination',
					array(
						/* translators: %d = page number. Front-end output. */
						" $sep " . sprintf( \__( 'Page %d', 'autodescription' ), $page_number ),
						$title,
						$page_number,
						$sep,
					)
				);
				$title .= $pagination;
			}
		}
		return $title;
	}

	/**
	 * Adds the title additions to the title.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $title The tite.
	 * @param string $blogname The blogname.
	 * @param string $seplocation The separator location.
	 * @return string Title with possible additions.
	 */
	public function process_title_additions( $title = '', $blogname = '', $seplocation = '' ) {

		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->process_title_additions()', '3.1.0', 'the_seo_framework()->merge_title_branding()' );

		$sep = $tsf->get_title_separator();

		$title = trim( $title );
		$blogname = trim( $blogname );

		if ( $blogname && $title ) {
			if ( 'left' === $seplocation ) {
				$title = $blogname . " $sep " . $title;
			} else {
				$title = $title . " $sep " . $blogname;
			}
		}

		return $title;
	}

	/**
	 * Cache current Title in static variable
	 * Must be called inside the loop
	 *
	 * @since 2.2.2
	 * @since 2.4.0 : If the theme is doing it right, override cache parameters to speed things up.
	 * @staticvar array $title_cache
	 *
	 * @param string $title The Title to return
	 * @param string $sep The Title sepeartor
	 * @param string $seplocation The Title sepeartor location, accepts 'left' or 'right'.
	 * @param bool $meta Ignore theme doing it wrong.
	 * @return string The title
	 */
	public function title_from_cache( $title = '', $sep = '', $seplocation = '', $meta = false ) {
		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->title_from_cache()', '3.1.0', 'the_seo_framework()->get_title(...)' );
		return $meta ? $tsf->get_open_graph_title() : $tsf->get_title();
	}

	/**
	 * Fetches single term title.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $depr Deprecated.
	 * @param bool   $depr Deprecated.
	 * @param \WP_Term|null $term The WP_Term object.
	 * @return string Single term title.
	 */
	public function single_term_title( $depr = '', $_depr = true, $term = null ) {
		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->single_term_title()', '3.1.0', 'the_seo_framework()->get_generated_single_term_title()' );
		return $tsf->get_generated_single_term_title( $term );
	}
}
