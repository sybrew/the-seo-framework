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
	 * Constructor, load parent constructor and set up variables.
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Returns a custom canonical URL.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args The canonical URL arguments.
	 * @return string The canonical URL, if any.
	 */
	public function create_canonical_url( $args = array() ) {

		$defaults = array(
			'id' => 0,
			'is_term' => false,
			'get_custom_field' => false,
		);
		$args = array_merge( $defaults, $args );

		return $this->get_canonical_url( $args );
	}

	/**
	 * Returns the current canonical URL.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args : Private variable. Use $this->create_canonical_url() instead.
	 * @return string The canonical URL, if any.
	 */
	public function get_canonical_url( $args = null ) {

		$this->the_seo_framework_debug && false === $this->doing_sitemap and $this->debug_init( __METHOD__, true, $debug_key = microtime( true ), get_defined_vars() );

		if ( is_array( $args ) ) {
			//= See $this->create_canonical_url().
			extract( $args );
		} else {
			$id = $this->get_the_real_ID();
			$is_term = $this->is_archive();
			$get_custom_field = true;
		}

		$canonical_url = '';

		if ( $is_term ) {
			$canonical_url = $this->get_archival_canonical_url( $id );
		} else {
			if ( $get_custom_field ) {
				$canonical_url = $this->get_singular_custom_canonical_url( $id );
			}

			if ( ! $canonical_url ) {
				if ( $this->is_front_page_by_id( $id ) ) {
					$canonical_url = $this->get_home_canonical_url();
				} elseif ( $id ) {
					$canonical_url = $this->get_singular_canonical_url( $id );
				}
			}
		}

		if ( ! $canonical_url )
			return '';

		$canonical_url = $this->set_preferred_url_scheme( $canonical_url );

		if ( $this->pretty_permalinks ) {
			$canonical_url = \esc_url( $canonical_url, array( 'http', 'https' ) );
		} else {
			//= Keep the &'s more readable.
			$canonical_url = \esc_url_raw( $canonical_url, array( 'http', 'https' ) );
		}

		$this->the_seo_framework_debug && false === $this->doing_sitemap and $this->debug_init( __METHOD__, false, $debug_key, compact( 'canonical_url' ) );

		return $canonical_url;
	}

	/**
	 * Returns home canonical URL.
	 *
	 * @since 3.0.0
	 *
	 * @return string The home canonical URL.
	 */
	public function get_home_canonical_url() {

		$url = \get_home_url();

		if ( $url )
			return \user_trailingslashit( $url );

		return '';
	}

	/**
	 * Returns singular custom field's canonical URL.
	 *
	 * @since 3.0.0
	 *
	 * @param int $id The page ID.
	 * @return string The custom canonical URL, if any.
	 */
	public function get_singular_custom_canonical_url( $id ) {
		return $this->get_custom_field( '_genesis_canonical_uri', $id ) ?: '';
	}

	/**
	 * Returns singular canonical URL.
	 *
	 * @since 3.0.0
	 *
	 * @param int|null $id The page ID.
	 * @return string The custom canonical URL, if any.
	 */
	public function get_singular_canonical_url( $id = null ) {
		return \wp_get_canonical_url( $id ) ?: '';
	}

	/**
	 * Returns archival canonical URL.
	 *
	 * @since 3.0.0
	 *
	 * @param int $term_id The term ID.
	 * @param string|null $taxonomy The taxonomy. When null, the current loop must be archival.
	 * @return string The custom canonical URL, if any.
	 */
	public function get_archival_canonical_url( $term_id, $taxonomy = null ) {

		if ( null === $taxonomy ) {
			$term = $this->fetch_the_term( $term_id );
			if ( $term instanceof \WP_Post_Type ) {
				$link = \get_post_type_archive_link( $term->name );
			} else {
				$link = \get_term_link( $term );
			}
		} else {
			$link = \get_term_link( $term_id, $taxonomy );
		}

		//= It can be of type \WP_Error.
		if ( is_string( $link ) )
			return $link ?: '';

		return '';
	}

	/**
	 * Returns preferred $url scheme.
	 * Can be automatically be detected.
	 *
	 * @since 2.8.0
	 * @since 2.9.2 Added filter usage cache.
	 * @staticvar string $scheme
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

			default :
			case 'automatic' :
				$scheme = $this->is_ssl() ? 'https' : 'http';
				break;
		endswitch;

		/**
		 * Applies filters 'the_seo_framework_preferred_url_scheme'
		 *
		 * @since 2.8.0
		 * @param string $scheme The current URL scheme.
		 */
		$scheme = (string) \apply_filters( 'the_seo_framework_preferred_url_scheme', $scheme );

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
			 * (string) 'https'    : Force https
			 * (bool) true         : Force https
			 * (bool) false        : Force http
			 * (string) 'http'     : Force http
			 * (string) 'relative' : Scheme relative
			 * (void) null         : Do nothing
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
		$url = \get_permalink( $post_id );
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

		$home_url = $this->get_homepage_canonical_url();
		$url = \trailingslashit( $home_url ) . $path . $additions;

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
	 *
	 * @since 2.2.4
	 * @since 3.0.0 Now uses WordPress permalinks.
	 *
	 * @param int $i The page number to generate the URL from.
	 * @param int $post_id The post ID.
	 * @param string $pos Which url to get, accepts next|prev.
	 * @return string The unescaped paged URL.
	 */
	public function get_paged_post_url( $i, $post_id = 0, $pos = 'prev' ) {

		$from_option = false;

		if ( empty( $post_id ) )
			$post_id = $this->get_the_real_ID();

		if ( 1 === $i ) :
			$url = \get_permalink( $post_id );
		else :
			$post = \get_post( $post_id );
			$url = \get_permalink( $post_id );

			if ( $i >= 2 ) {
				//* Fix adding pagination url.

				//* Parse query arg, put in var and remove from current URL.
				$query_arg = parse_url( $url, PHP_URL_QUERY );
				if ( isset( $query_arg ) )
					$url = str_replace( '?' . $query_arg, '', $url );

				//* Continue if still bigger than or equal to 2.
				if ( $i >= 2 ) {
					// Calculate current page number.
					$_current = 'next' === $pos ? (string) ( $i - 1 ) : (string) ( $i + 1 );

					//* We're adding a page.
					$_last_occurrence = strrpos( $url, '/' . $_current . '/' );

					if ( false !== $_last_occurrence )
						$url = substr_replace( $url, '/', $_last_occurrence, strlen( '/' . $_current . '/' ) );
				}
			}

			if ( ! $this->pretty_permalinks || in_array( $post->post_status, array( 'draft', 'auto-draft', 'pending' ), true ) ) {

				//* Put removed query arg back prior to adding pagination.
				if ( isset( $query_arg ) )
					$url = $url . '?' . $query_arg;

				$url = \add_query_arg( 'page', $i, $url );
			} elseif ( $this->is_static_frontpage( $post_id ) ) {
				global $wp_rewrite;

				$url = \trailingslashit( $url ) . \user_trailingslashit( $wp_rewrite->pagination_base . '/' . $i, 'single_paged' );

				//* Add back query arg if removed.
				if ( isset( $query_arg ) )
					$url = $url . '?' . $query_arg;
			} else {
				$url = \trailingslashit( $url ) . \user_trailingslashit( $i, 'single_paged' );

				//* Add back query arg if removed.
				if ( isset( $query_arg ) )
					$url = $url . '?' . $query_arg;
			}
		endif;

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
	 * Appends given query to given URL.
	 *
	 * @since 3.0.0
	 *
	 * @param string $url A fully qualified URL.
	 * @param string $query A fully qualified query taken from parse_url( $url, PHP_URL_QUERY );
	 * @return string A fully qualified URL with appended $query.
	 */
	public function append_php_query( $url, $query = '' ) {

		if ( ! $query )
			return $url;

		$p = parse_url( $url );
		$_fragment = ! empty( $p['fragment'] ) ? $p['fragment'] : '';
		$_query = ! empty( $p['query'] ) ? $p['query'] : '';

		if ( $_fragment )
			$url = str_replace( '#' . $_fragment, '', $url );

		if ( $_query ) {
			$url .= '&' . $query;
		} else {
			$url .= '?' . $query;
		}

		if ( $_fragment )
			$url .= '#' . $_fragment;

		return $url;
	}
}
