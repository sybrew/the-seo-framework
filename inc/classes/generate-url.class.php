<?php
/**
 * @package The_SEO_Framework\Classes
 */
namespace The_SEO_Framework;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\Generate_Url
 *
 * Generates URL and permalink SEO data based on content.
 *
 * @since 2.8.0
 */
class Generate_Url extends Generate_Title {

	/**
	 * Whether to slash the url or not. Used when query vars are in url.
	 *
	 * @since 2.6.0
	 * @since 2.8.0 : Made public.
	 *
	 * @var bool Whether to slash the url.
	 */
	public $url_slashit;

	/**
	 * Holds current HTTP host.
	 *
	 * @since 2.6.5
	 * @since 2.8.0 : Made public.
	 *
	 * @var string The current HTTP host.
	 */
	public $current_host;

	/**
	 * Constructor, load parent constructor and set up variables.
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Creates canonical URL.
	 *
	 * @since 2.0.0
	 * @since 2.4.2 : Refactored arguments
	 * @since 2.8.0 : No longer tolerates $id as Post object.
	 * @since 2.9.0 : When using 'home => true' args parameter, the home path is added when set.
	 * @since 2.9.2 Added filter usage cache.
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

		$this->the_seo_framework_debug && false === $this->doing_sitemap and $this->debug_init( __METHOD__, true, $debug_key = microtime( true ), get_defined_vars() );

		$args = $this->reparse_url_args( $args );

		/**
		 * Fetch permalink if Feed.
		 * @since 2.5.2
		 */
		if ( $this->is_feed() )
			$url = \get_permalink();

		//* Reset cache.
		$this->url_slashit = true;
		$this->unset_current_subdomain();
		$this->current_host = '';

		$path = '';
		$scheme = '';
		$slashit = true;

		if ( false === $args['home'] && empty( $url ) ) {
			/**
			 * Get URL from options.
			 * @since 2.2.9
			 */
			if ( $args['get_custom_field'] && $this->is_singular() ) {
				$custom_url = $this->get_custom_field( '_genesis_canonical_uri' );

				if ( $custom_url ) {
					$url = $custom_url;
					$this->url_slashit = false;
					$parsed_url = \wp_parse_url( $custom_url );
					$scheme = isset( $parsed_url['scheme'] ) ? $parsed_url['scheme'] : 'http';
				}
			}

			if ( empty( $url ) )
				$path = $this->generate_url_path( $args );
		} elseif ( $args['home'] ) {
			$path = $this->get_home_path();
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
				$this->unset_current_subdomain();

			$url = $this->add_url_host( $path );
			$scheme = '';

			$url = $this->add_url_subdomain( $url );
		}

		$scheme = $scheme ?: $this->get_prefered_scheme();

		$url = $this->set_url_scheme( $url, $scheme );

		if ( $this->url_slashit ) {
			if ( $args['forceslash'] ) {
				$url = \trailingslashit( $url );
			} elseif ( $slashit ) {
				$url = \user_trailingslashit( $url );
			}
		}

		if ( $this->pretty_permalinks ) {
			$url = \esc_url( $url, array( 'http', 'https' ) );
		} else {
			//* Keep the &'s more readable.
			$url = \esc_url_raw( $url, array( 'http', 'https' ) );
		}

		$this->the_seo_framework_debug && false === $this->doing_sitemap and $this->debug_init( __METHOD__, false, $debug_key, array( 'url_output' => $url ) );

		return $url;
	}

	/**
	 * Parse and sanitize url args.
	 *
	 * @since 2.4.2
	 * @since 2.9.2 Added filter usage cache.
	 * @staticvar bool $_has_filter
	 *
	 * @param array $args required The passed arguments.
	 * @param array $defaults The default arguments.
	 * @param bool $get_defaults Return the default arguments. Ignoring $args.
	 * @return array $args parsed args.
	 */
	public function parse_url_args( $args = array(), $defaults = array(), $get_defaults = false ) {

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
				'id'               => $this->get_the_real_ID(),
			);

			static $_has_filter = null;
			if ( null === $_has_filter )
				$_has_filter = \has_filter( 'the_seo_framework_url_args' );

			if ( $_has_filter ) {
				/**
				 * @applies filters the_seo_framework_url_args : {
				 *  	@param bool $paged Return current page URL without pagination if false
				 *  	@param bool $paged_plural Whether to add pagination for the second or later page.
				 *  	@param bool $from_option Get the canonical uri option
				 *  	@param object $post The Post Object.
				 *  	@param bool $external Whether to fetch the current WP Request or get the permalink by Post Object.
				 *  	@param bool $is_term Fetch url for term.
				 *  	@param object $term The term object.
				 *  	@param bool $home Fetch home URL.
				 *  	@param bool $forceslash Fetch home URL and slash it, always.
				 *  	@param int $id The Page or Term ID.
				 * }
				 *
				 * @since 2.5.0
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

		return $args;
	}

	/**
	 * Reparse URL args.
	 *
	 * @since 2.6.2
	 * @since 2.9.2 Now passes args to filter.
	 *
	 * @param array $args required The passed arguments.
	 * @return array $args parsed args.
	 */
	public function reparse_url_args( $args = array() ) {

		$default_args = $this->parse_url_args( $args, '', true );

		if ( is_array( $args ) ) {
			if ( empty( $args ) ) {
				$args = $default_args;
			} else {
				$args = $this->parse_url_args( $args, $default_args );
			}
		} else {
			//* Old style parameters are used. Doing it wrong.
			$this->_doing_it_wrong( __METHOD__, 'Use $args = array() for parameters.', '2.4.2' );
			$args = $default_args;
		}

		return $args;
	}

	/**
	 * Generate URL from arguments.
	 *
	 * @since 2.6.0
	 * @NOTE: Handles full path, including home directory.
	 *
	 * @param array $args the URL args.
	 * @return string $path
	 */
	public function generate_url_path( $args = array() ) {

		$args = $this->reparse_url_args( $args );

		if ( $this->is_archive() || $args['is_term'] ) :

			$term = $args['term'];

			//* Term or Taxonomy.
			if ( ! isset( $term ) )
				$term = \get_queried_object();

			if ( isset( $term->taxonomy ) ) {
				//* Registered Terms and Taxonomies.
				$path = $this->get_relative_term_url( $term, $args );
			} elseif ( ! $args['external'] && isset( $GLOBALS['wp']->request ) ) {
				//* Everything else.
				$_url = \trailingslashit( \get_option( 'home' ) ) . $GLOBALS['wp']->request;
				$path = $this->set_url_scheme( $_url, 'relative' );
			} else {
				//* Nothing to see here...
				$path = '';
			}
		elseif ( $this->is_search() ) :
			$_url = \get_search_link();
			$path = $this->set_url_scheme( $_url, 'relative' );
		else :
			/**
			 * Reworked to use the $args['id'] check based on get_the_real_ID.
			 * @since 2.6.0 & 2.6.2
			 */
			$post_id = isset( $args['post']->ID ) ? $args['post']->ID : $args['id'];

			if ( $this->pretty_permalinks && $post_id && $this->is_singular( $post_id ) ) {
				$post = \get_post( $post_id );

				//* Don't slash draft links.
				if ( isset( $post->post_status ) && ( 'auto-draft' === $post->post_status || 'draft' === $post->post_status ) )
					$this->url_slashit = false;
			}

			$path = $this->build_singular_relative_url( $post_id, $args );
		endif;

		return $path;
	}

	/**
	 * Generates relative URL for the Homepage and Singular Posts.
	 *
	 * @since 2.6.5
	 * @NOTE: Handles full path, including home directory.
	 * @since 2.8.0: Continues on empty post ID. Handles it as HomePage.
	 *
	 * @param int $post_id The ID.
	 * @param array $args The URL arguments.
	 * @return relative Post or Page url.
	 */
	public function build_singular_relative_url( $post_id = null, $args = array() ) {

		if ( empty( $post_id ) ) {
			//* We can't fetch the post ID when there's an external request.
			if ( $args['external'] ) {
				$post_id = 0;
			} else {
				$post_id = $this->get_the_real_ID();
			}
		}

		$args = $this->reparse_url_args( $args );

		if ( $args['external'] || ! $this->is_real_front_page() || ! $this->is_front_page_by_id( $post_id ) ) {
			$url = \get_permalink( $post_id );
		} elseif ( $this->is_real_front_page() || $this->is_front_page_by_id( $post_id ) ) {
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
			$paged = $this->is_singular() ? $this->page() : $this->paged();
			$paged = $this->maybe_get_paged( $paged, $args['paged'], $args['paged_plural'] );
		}

		if ( $paged ) {
			if ( $this->pretty_permalinks ) {
				if ( $this->is_singular() ) {
					$url = \trailingslashit( $url ) . $paged;
				} else {
					$url = \trailingslashit( $url ) . 'page/' . $paged;
				}
			} else {
				if ( $this->is_singular() ) {
					$url = \add_query_arg( 'page', $paged, $url );
				} else {
					$url = \add_query_arg( 'paged', $paged, $url );
				}
			}
		}

		return $this->set_url_scheme( $url, 'relative' );
	}

	/**
	 * Create full valid URL with parsed host.
	 * Don't forget to use set_url_scheme() afterwards.
	 *
	 * Note: will return $path if no host can be found.
	 *
	 * @since 2.6.5
	 *
	 * @param string $path Current path.
	 * @return string Full valid URL with http host.
	 */
	public function add_url_host( $path = '' ) {

		$host = $this->current_host ?: $this->get_home_host();

		$scheme = $host ? 'http://' : '';

		return $url = $scheme . \trailingslashit( $host ) . ltrim( $path, ' \\/' );
	}

	/**
	 * Generates relative URL for current term.
	 *
	 * @since 2.4.2
	 * @since 2.7.0 Added home directory to output.
	 * @global object $wp_rewrite
	 * @NOTE: Handles full path, including home directory.
	 *
	 * @param object $term The term object.
	 * @param array|bool $args {
	 *		'external' : Whether to fetch the WP Request or get the permalink by Post Object.
	 *		'paged'	: Whether to add pagination for all types.
	 *		'paged_plural' : Whether to add pagination for the second or later page.
	 * }
	 * @return Relative term or taxonomy URL.
	 */
	public function get_relative_term_url( $term = null, $args = array() ) {
		global $wp_rewrite;

		if ( ! is_array( $args ) ) {
			/**
			 * @since 2.6.0
			 * '$args = array()' replaced '$no_request = false'.
			 */
			$this->_doing_it_wrong( __METHOD__, 'Use $args = array() for parameters.', '2.6.0' );

			$no_request = (bool) $args;
			$args = $this->parse_url_args( '', '', true );
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

		$paged = $this->maybe_get_paged( $this->paged(), $args['paged'], $args['paged_plural'] );

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
			$this->url_slashit = false;

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
		$path = $this->set_url_scheme( $url, 'relative' );

		return $path;
	}

	/**
	 * Returns preferred $url scheme.
	 * Can be automatically be detected.
	 *
	 * @since 2.8.0
	 * @since 2.9.2 Added filter usage cache.
	 * @staticvar string $scheme
	 * @staticvar bool $_has_filter
	 *
	 * @return string The preferred URl scheme.
	 */
	public function get_prefered_scheme() {

		static $scheme;

		if ( isset( $scheme ) )
			return $scheme;

		switch ( $this->get_option( 'canonical_scheme' ) ) :
			case 'https' :
				$scheme = 'https';
				break;

			case 'http' :
				$scheme = 'http';
				break;

			case 'automatic' :
			default :
				$scheme = $this->is_ssl() ? 'https' : 'http';
				break;
		endswitch;

		static $_has_filter = null;

		if ( null === $_has_filter )
			$_has_filter = \has_filter( 'the_seo_framework_preferred_url_scheme' );

		if ( $_has_filter ) {
			/**
			 * Applies filters 'the_seo_framework_preferred_url_scheme' : string
			 *
			 * @since 2.8.0
			 *
			 * @param string $scheme The current URL scheme.
			 */
			$scheme = (string) \apply_filters( 'the_seo_framework_preferred_url_scheme', $scheme );
		}

		return $scheme;
	}

	/**
	 * Sets URL to preferred URL scheme.
	 * Does not sanitize output.
	 *
	 * @since 2.8.0
	 *
	 * @param string $url The URL to set scheme for.
	 * @return string The URL with the preferred scheme.
	 */
	public function set_preferred_url_scheme( $url ) {
		return $this->set_url_scheme( $url, $this->get_prefered_scheme(), false );
	}

	/**
	 * Sets URL scheme for input URL.
	 * WordPress core function, without filter.
	 *
	 * @since 2.4.2
	 *
	 * @param string $url Absolute url that includes a scheme.
	 * @param string $scheme optional. Scheme to give $url. Currently 'http', 'https', 'login', 'login_post', 'admin', or 'relative'.
	 * @param bool $use_filter Whether to parse filters.
	 * @return string url with chosen scheme.
	 */
	public function set_url_scheme( $url, $scheme = null, $use_filter = true ) {

		if ( empty( $scheme ) ) {
			$scheme = $this->is_ssl() ? 'https' : 'http';
		} elseif ( 'admin' === $scheme || 'login' === $scheme  || 'login_post' === $scheme || 'rpc' === $scheme ) {
			$scheme = $this->is_ssl() || \force_ssl_admin() ? 'https' : 'http';
		} elseif ( 'http' !== $scheme && 'https' !== $scheme && 'relative' !== $scheme ) {
			$scheme = $this->is_ssl() ? 'https' : 'http';
		}

		$url = $this->make_fully_qualified_url( $url );

		if ( 'relative' === $scheme ) {
			$url = ltrim( preg_replace( '#^\w+://[^/]*#', '', $url ) );
			if ( '' !== $url && '/' === $url[0] )
				$url = '/' . ltrim( $url , "/ \t\n\r\0\x0B" );
		} else {
			//* This will break if $scheme is set to false.
			$url = preg_replace( '#^\w+://#', $scheme . '://', $url );
		}

		if ( $use_filter )
			return $this->set_url_scheme_filter( $url, $scheme );

		return $url;
	}

	/**
	 * Set URL scheme based on filter.
	 *
	 * @since 2.6.0
	 * @since 2.8.0 Deprecated.
	 * @since 2.9.2 Added filter usage cache.
	 * @staticvar $_has_filter;
	 * @deprecated
	 *
	 * @param string $url The url with scheme.
	 * @param string $scheme The current scheme.
	 * @return $url with applied filters.
	 */
	public function set_url_scheme_filter( $url, $current_scheme ) {

		static $_has_filter = null;
		if ( null === $_has_filter )
			$_has_filter = \has_filter( 'the_seo_framework_canonical_force_scheme' );

		if ( $_has_filter ) {

			$this->_deprecated_filter( 'the_seo_framework_canonical_force_scheme', '2.8.0', 'the_seo_framework_preferred_url_scheme' );

			/**
			 * Applies filters the_seo_framework_canonical_force_scheme : Changes scheme.
			 *
			 * Accepted variables:
			 * (string) 'https'		: Force https
			 * (bool) true 			: Force https
			 * (bool) false			: Force http
			 * (string) 'http'		: Force http
			 * (string) 'relative' 	: Scheme relative
			 * (void) null			: Do nothing
			 *
			 * @since 2.4.2
			 * @since 2.8.0 Deprecated.
			 * @deprecated
			 *
			 * @param string $current_scheme the current used scheme.
			 */
			$scheme_settings = \apply_filters( 'the_seo_framework_canonical_force_scheme', null, $current_scheme );

			if ( null !== $scheme_settings ) {
				if ( 'https' === $scheme_settings || 'http' === $scheme_settings || 'relative' === $scheme_settings ) {
					$url = $this->set_url_scheme( $url, $scheme_settings, false );
				} elseif ( ! $scheme_settings ) {
					$url = $this->set_url_scheme( $url, 'http', false );
				} elseif ( $scheme_setting ) {
					$url = $this->set_url_scheme( $url, 'https', false );
				}
			}
		}

		return $url;
	}

	/**
	 * Generates shortlink URL.
	 *
	 * @since 2.2.2
	 *
	 * @param int $post_id The post ID.
	 * @return string|null Escaped site Shortlink URL.
	 */
	public function get_shortlink( $post_id = 0 ) {

		if ( ! $this->get_option( 'shortlink_tag' ) )
			return '';

		if ( $this->is_real_front_page() || $this->is_front_page_by_id( $post_id ) )
			return '';

		$path = '';

		if ( $this->is_singular( $post_id ) ) {
			if ( 0 === $post_id )
				$post_id = $this->get_the_real_ID();

			if ( $post_id ) {
				if ( $this->is_static_frontpage( $post_id ) ) {
					$path = '';
				} else {
					//* This will be converted to '?p' later.
					$path = '?page_id=' . $post_id;
				}
			}
		} elseif ( $this->is_archive() ) {
			if ( $this->is_category() ) {
				$id = \get_queried_object_id();
				$path = '?cat=' . $id;
			} elseif ( $this->is_tag() ) {
				$id = \get_queried_object_id();
				$path = '?post_tag=' . $id;
			} elseif ( $this->is_date() && isset( $GLOBALS['wp_query']->query ) ) {
				$query = $GLOBALS['wp_query']->query;
				$var = '';

				$first = true;
				foreach ( $query as $key => $val ) {
					$var .= $first ? '?' : '&';
					$var .= $key . '=' . $val;
					$first = false;
				}

				$path = $var;
			} elseif ( $this->is_author() ) {
				$id = \get_queried_object_id();
				$path = '?author=' . $id;
			} elseif ( $this->is_tax() ) {
				//* Generate shortlink for object type and slug.
				$object = \get_queried_object();

				$t = isset( $object->taxonomy ) ? urlencode( $object->taxonomy ) : '';

				if ( $t ) {
					$slug = isset( $object->slug ) ? urlencode( $object->slug ) : '';

					if ( $slug )
						$path = '?' . $t . '=' . $slug;
				}
			}
		}

		if ( empty( $path ) )
			return '';

		if ( 0 === $post_id )
			$post_id = $this->get_the_real_ID();

		//* Get additional public queries from the page URL.
		$url = $this->the_url_from_cache( '', $post_id, false, false, false );
		$query = parse_url( $url, PHP_URL_QUERY );

		$additions = '';
		if ( ! empty( $query ) ) {
			if ( false !== strpos( $query, '&' ) ) {
				//= This can fail on malformed URLs
				$query = explode( '&', $query );
			} else {
				$query = array( $query );
			}

			foreach ( $query as $arg ) {
				/**
				 * @since 2.9.4 Added $args availability check.
				 * This is a band-aid, not a fix.
				 * @TODO inspect prior explode().
				 * @link https://wordpress.org/support/topic/error-when-previewing-a-draft-of-knowledge-base-article/#post-9452791
				 */
				if ( $arg && false === strpos( $path, $arg ) )
					$additions .= '&' . $arg;
			}
		}

		//* We used 'page_id' to determine duplicates. Now we can convert it to a shorter form.
		$path = str_replace( 'page_id=', 'p=', $path );

		if ( $this->is_archive() || $this->is_home() ) {
			$paged = $this->maybe_get_paged( $this->paged(), false, true );
			if ( $paged )
				$path .= '&paged=' . $paged;
		} else {
			$page = $this->maybe_get_paged( $this->page(), false, true );
			if ( $page )
				$path .= '&page=' . $page;
		}

		$home_url = $this->the_home_url_from_cache( true );
		$url = $home_url . $path . $additions;

		return \esc_url_raw( $url, array( 'http', 'https' ) );
	}

	/**
	 * Generates Previous and Next links.
	 *
	 * @since 2.2.4
	 *
	 * @param string $prev_next Previous or next page link.
	 * @param int $post_id The post ID.
	 * @return string|null Escaped site Pagination URL
	 */
	public function get_paged_url( $prev_next = 'next', $post_id = 0 ) {

		if ( ! $this->get_option( 'prev_next_posts' ) && ! $this->get_option( 'prev_next_archives' ) && ! $this->get_option( 'prev_next_frontpage' ) )
			return '';

		$prev = '';
		$next = '';

		if ( $this->is_singular() ) :
			if ( $this->is_real_front_page() || $this->is_static_frontpage( $post_id ) ) {
				$output_singular_paged = $this->is_option_checked( 'prev_next_frontpage' );
			} else {
				$output_singular_paged = $this->is_option_checked( 'prev_next_posts' );
			}

			if ( $output_singular_paged ) :

				$page = $this->page();

				if ( ! $page )
					$page = 1;

				if ( 'prev' === $prev_next ) {
					$prev = $page > 1 ? $this->get_paged_post_url( $page - 1, $post_id, 'prev' ) : '';
				} elseif ( 'next' === $prev_next ) {
					$_numpages = substr_count( $this->get_post_content( $post_id ), '<!--nextpage-->' ) + 1;
					$next = $page < $_numpages ? $this->get_paged_post_url( $page + 1, $post_id, 'next' ) : '';
				}
			endif;
		elseif ( $this->is_archive() || $this->is_home() ) :

			$output_archive_paged = false;
			if ( $this->is_real_front_page() || $this->is_front_page_by_id( $post_id ) ) {
				//* Only home.
				$output_archive_paged = $this->is_option_checked( 'prev_next_frontpage' );
			} else {
				//* Both home and archives.
				$output_archive_paged = $this->is_option_checked( 'prev_next_archives' );
			}

			if ( $output_archive_paged ) {
				$paged = $this->paged();

				if ( 'prev' === $prev_next && $paged > 1 ) {
					$paged = intval( $paged ) - 1;

					if ( $paged < 1 )
						$paged = 1;

					$prev = \get_pagenum_link( $paged, false );
				} elseif ( 'next' === $prev_next && $paged < $GLOBALS['wp_query']->max_num_pages ) {

					if ( ! $paged )
						$paged = 1;
					$paged = intval( $paged ) + 1;

					$next = \get_pagenum_link( $paged, false );
				}
			}
		endif;

		if ( $prev )
			return $this->set_preferred_url_scheme( \esc_url_raw( $prev, array( 'http', 'https' ) ) );

		if ( $next )
			return $this->set_preferred_url_scheme( \esc_url_raw( $next, array( 'http', 'https' ) ) );

		return '';
	}

	/**
	 * Returns the special URL of a paged post.
	 *
	 * Taken from _wp_link_page() in WordPress core, but instead of anchor markup, just return the URL.
	 * Also adds WPMUdev Domain Mapping support and is optimized for speed.
	 *
	 * @since 2.2.4
	 * @uses $this->the_url_from_cache();
	 *
	 * @param int $i The page number to generate the URL from.
	 * @param int $post_id The post ID
	 * @param string $pos Which url to get, accepts next|prev
	 * @return string Unescaped URL
	 */
	public function get_paged_post_url( $i, $post_id = 0, $pos = 'prev' ) {

		$from_option = false;

		if ( empty( $post_id ) )
			$post_id = $this->get_the_real_ID();

		if ( 1 === $i ) :
			$url = $this->the_url_from_cache( '', $post_id, false, $from_option, false );
		else :
			$post = \get_post( $post_id );

			$urlfromcache = $this->the_url_from_cache( '', $post_id, false, $from_option, false );

			if ( $i >= 2 ) {
				//* Fix adding pagination url.

				//* Parse query arg, put in var and remove from current URL.
				$query_arg = parse_url( $urlfromcache, PHP_URL_QUERY );
				if ( isset( $query_arg ) )
					$urlfromcache = str_replace( '?' . $query_arg, '', $urlfromcache );

				//* Continue if still bigger than or equal to 2.
				if ( $i >= 2 ) {
					// Calculate current page number.
					$_current = 'next' === $pos ? (string) ( $i - 1 ) : (string) ( $i + 1 );

					//* We're adding a page.
					$_last_occurrence = strrpos( $urlfromcache, '/' . $_current . '/' );

					if ( false !== $_last_occurrence )
						$urlfromcache = substr_replace( $urlfromcache, '/', $_last_occurrence, strlen( '/' . $_current . '/' ) );
				}
			}

			if ( ! $this->pretty_permalinks || in_array( $post->post_status, array( 'draft', 'auto-draft', 'pending' ), true ) ) {

				//* Put removed query arg back prior to adding pagination.
				if ( isset( $query_arg ) )
					$urlfromcache = $urlfromcache . '?' . $query_arg;

				$url = \add_query_arg( 'page', $i, $urlfromcache );
			} elseif ( $this->is_static_frontpage( $post_id ) ) {
				global $wp_rewrite;

				$url = \trailingslashit( $urlfromcache ) . \user_trailingslashit( $wp_rewrite->pagination_base . '/' . $i, 'single_paged' );

				//* Add back query arg if removed.
				if ( isset( $query_arg ) )
					$url = $url . '?' . $query_arg;
			} else {
				$url = \trailingslashit( $urlfromcache ) . \user_trailingslashit( $i, 'single_paged' );

				//* Add back query arg if removed.
				if ( isset( $query_arg ) )
					$url = $url . '?' . $query_arg;
			}
		endif;

		return $url;
	}

	/**
	 * Adds subdomain to input URL.
	 *
	 * @since 2.6.5
	 *
	 * @param string $url The current URL without subdomain.
	 * @return string $url Fully qualified URL with possible subdomain.
	 */
	public function add_url_subdomain( $url = '' ) {

		$url = $this->make_fully_qualified_url( $url );

		//* Add subdomain, if set.
		if ( $subdomain = $this->get_current_subdomain() ) {
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
	 * @staticvar string $subdomain
	 *
	 * @param null|string $set Whether to set a new subdomain.
	 * @param bool $unset Whether to remove subdomain from cache.
	 * @return string|bool The set subdomain, false if none is set.
	 */
	public function get_current_subdomain( $set = null, $unset = false ) {

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
	 *
	 * @param string $subdomain The current subdomain.
	 * @return string The set subdomain.
	 */
	public function set_current_subdomain( $subdomain = '' ) {
		return $this->get_current_subdomain( $subdomain );
	}

	/**
	 * Unsets current working subdomain.
	 *
	 * @since 2.7.0
	 */
	public function unset_current_subdomain() {
		$this->get_current_subdomain( null, true );
	}

	/**
	 * Cached WordPress permalink structure settings.
	 *
	 * @since 2.6.0
	 * @staticvar string $structure
	 *
	 * @return string permalink structure.
	 */
	public function permalink_structure() {

		static $structure = null;

		if ( isset( $structure ) )
			return $structure;

		return $structure = \get_option( 'permalink_structure' );
	}

	/**
	 * Add $paged if Paginated and allowed through arguments.
	 *
	 * @since 2.6.0
	 *
	 * @param int $paged The current page number.
	 * @param bool $singular Whether to allow plural and singular.
	 * @param bool $plural Whether to allow plural regardless.
	 *
	 * @return int|bool $paged. False if not allowed or on page 0. int if allowed.
	 */
	protected function maybe_get_paged( $paged = 0, $singular = false, $plural = true ) {

		if ( $paged ) {
			if ( $singular )
				return $paged;

			if ( $plural && $paged >= 2 )
				return $paged;
		}

		return false;
	}

	/**
	 * Makes a fully qualified URL from input. Always uses http to fix.
	 * @see $this->set_url_scheme()
	 *
	 * @since 2.6.5
	 *
	 * @param string $url Required the current maybe not fully qualified URL.
	 * @return string $url
	 */
	public function make_fully_qualified_url( $url ) {

		if ( '//' === substr( $url, 0, 2 ) ) {
			$url = 'http:' . $url;
		} elseif ( 'http' !== substr( $url, 0, 4 ) ) {
			$url = 'http://' . $url;
		}

		return $url;
	}

	/**
	 * Fetches home URL host. Like "wordpress.org".
	 * If this fails, you're going to have a bad time.
	 *
	 * @since 2.7.0
	 * @since 2.9.2 : Now considers port too.
	 *              : Now uses get_home_url(), rather than get_option('home').
	 * @staticvar string $cache
	 *
	 * @return string The home URL host.
	 */
	public function get_home_host() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		$parsed_url = \wp_parse_url( \get_home_url() );

		$host = isset( $parsed_url['host'] ) ? $parsed_url['host'] : '';

		if ( $host && isset( $parsed_url['port'] ) )
			$host .= ':' . $parsed_url['port'];

		return $cache = $host;
	}

	/**
	 * Fetches home URL subdirectory path. Like "wordpress.org/plugins/".
	 *
	 * @since 2.7.0
	 * @staticvar string $cache
	 *
	 * @return string The home URL path.
	 */
	public function get_home_path() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		$path = '';

		$parsed_url = \wp_parse_url( \get_option( 'home' ) );

		if ( ! empty( $parsed_url['path'] ) && $path = ltrim( $parsed_url['path'], ' \\/' ) )
			$path = '/' . $path;

		return $cache = $path;
	}
}
