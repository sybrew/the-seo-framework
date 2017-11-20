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
	 * Returns a canonical URL based on parameters.
	 * The URL will never be paginated.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args The canonical URL arguments : {
	 *    int    $id               The Post, Page or Term ID to generate the URL for.
	 *    string $taxonomy         The taxonomy.
	 *    bool   $get_custom_field Whether to get custom canonical URLs from user settings.
	 * }
	 * @return string The canonical URL, if any.
	 */
	public function create_canonical_url( $args = array() ) {

		$defaults = array(
			'id' => 0,
			'taxonomy' => '',
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

		if ( $args ) {
			//= See $this->create_canonical_url().
			$canonical_url = $this->build_canonical_url( $args );
			$query = false;
		} else {
			$canonical_url = $this->generate_canonical_url();
			$query = true;
		}

		if ( ! $canonical_url )
			return '';

		if ( ! $query && $args['id'] === $this->get_the_real_ID() ) {
			$canonical_url = $this->remove_pagination_from_url( $canonical_url );
		}
		if ( $this->matches_this_domain( $canonical_url ) ) {
			$canonical_url = $this->set_preferred_url_scheme( $canonical_url );
		}
		$canonical_url = $this->clean_canonical_url( $canonical_url );

		$this->the_seo_framework_debug && false === $this->doing_sitemap and $this->debug_init( __METHOD__, false, $debug_key, compact( 'canonical_url' ) );

		return $canonical_url;
	}

	/**
	 * Builds canonical URL from input arguments.
	 *
	 * @since 3.0.0
	 * @see $this->create_canonical_url()
	 *
	 * @param array $args. Use $this->create_canonical_url().
	 * @return string The canonical URL.
	 */
	protected function build_canonical_url( $args ) {

		//* See $this->create_canonical_url().
		extract( $args );

		$canonical_url = '';

		if ( $taxonomy ) {
			$canonical_url = $this->get_taxonomial_canonical_url( $id, $taxonomy );
		} else {
			if ( $get_custom_field ) {
				$canonical_url = $this->get_singular_custom_canonical_url( $id );
			}

			if ( ! $canonical_url ) {
				if ( ! $id || ( $this->has_page_on_front() && $this->is_front_page_by_id( $id ) ) ) {
					$canonical_url = $this->get_home_canonical_url();
				} elseif ( $id ) {
					$canonical_url = $this->get_singular_canonical_url( $id );
				}
			}
		}

		return $canonical_url;
	}

	/**
	 * Generates canonical URL from current query.
	 *
	 * @since 3.0.0
	 * @see $this->get_canonical_url()
	 *
	 * @return string The canonical URL.
	 */
	protected function generate_canonical_url() {

		$id = $this->get_the_real_ID();
		$canonical_url = '';

		if ( $this->is_real_front_page() ) {
			if ( $this->has_page_on_front() )
				$canonical_url = $this->get_singular_custom_canonical_url( $id );
			if ( ! $canonical_url )
				$canonical_url = $this->get_home_canonical_url();
		} elseif ( $this->is_singular() ) {
			$canonical_url = $this->get_singular_custom_canonical_url( $id );
			if ( ! $canonical_url )
				$canonical_url = $this->get_singular_canonical_url( $id );
		} elseif ( $this->is_archive() ) {
			if ( $this->is_category() || $this->is_tag() || $this->is_tax() ) {
				$canonical_url = $this->get_taxonomial_canonical_url( $id, $this->get_current_taxonomy() );
			} elseif ( \is_post_type_archive() ) {
				$canonical_url = $this->get_post_type_archive_canonical_url( $id );
			} elseif ( $this->is_author() ) {
				$canonical_url = $this->get_author_canonical_url( $id );
			} elseif ( $this->is_date() ) {
				if ( $this->is_day() ) {
					$canonical_url = $this->get_date_canonical_url( \get_query_var( 'year' ), \get_query_var( 'monthnum' ), \get_query_var( 'day' ) );
				} elseif ( $this->is_month() ) {
					$canonical_url = $this->get_date_canonical_url( \get_query_var( 'year' ), \get_query_var( 'monthnum' ) );
				} elseif ( $this->is_year() ) {
					$canonical_url = $this->get_date_canonical_url( \get_query_var( 'year' ) );
				}
			}
		} elseif ( $this->is_search() ) {
			$canonical_url = $this->get_search_canonical_url();
		}

		return $canonical_url;
	}

	/**
	 * Cleans canonical URL.
	 * Looks at permalink settings to determine roughness of escaping.
	 *
	 * @since 3.0.0
	 *
	 * @param string $url A fully qualified URL.
	 * @return string A fully qualified clean URL.
	 */
	public function clean_canonical_url( $url ) {

		if ( $this->pretty_permalinks ) {
			$url = \esc_url( $url, array( 'http', 'https' ) );
		} else {
			//= Keep the &'s more readable.
			$url = \esc_url_raw( $url, array( 'http', 'https' ) );
		}

		return $url;
	}

	/**
	 * Returns home canonical URL.
	 * Automatically adds pagination if the ID matches the query.
	 *
	 * @since 3.0.0
	 *
	 * @return string The home canonical URL.
	 */
	public function get_home_canonical_url() {

		//= Prevent admin bias by passing preferred scheme.
		$url = \get_home_url( null, '', $this->get_preferred_scheme() );

		if ( $url ) {
			if ( $this->get_the_real_ID() === (int) \get_option( 'page_for_posts' ) ) {
				$url = $this->add_url_pagination( $url, $this->paged(), true );
			}

			return \user_trailingslashit( $url );
		}

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
	 * Automatically adds pagination if the ID matches the query.
	 *
	 * Prevents SEO attacks regarding pagination.
	 *
	 * @since 3.0.0
	 *
	 * @param int|null $id The page ID.
	 * @return string The custom canonical URL, if any.
	 */
	public function get_singular_canonical_url( $id = null ) {

		$canonical_url = \wp_get_canonical_url( $id );

		if ( ! $canonical_url )
			return '';

		//* @link https://core.trac.wordpress.org/ticket/37505
		$_page = \get_query_var( 'page', 0 );
		if ( $_page !== $this->page() ) {
			$canonical_url = $this->remove_pagination_from_url( $canonical_url, $_page );
		}

		return $canonical_url;
	}

	/**
	 * Returns taxonomial canonical URL.
	 * Automatically adds pagination if the ID matches the query.
	 *
	 * @since 3.0.0
	 *
	 * @param int $term_id The term ID.
	 * @param string $taxonomy The taxonomy.
	 * @return string The taxonomial canonical URL, if any.
	 */
	public function get_taxonomial_canonical_url( $term_id, $taxonomy ) {

		$link = \get_term_link( $term_id, $taxonomy );

		if ( \is_wp_error( $link ) )
			return '';

		if ( $term_id === $this->get_the_real_ID() ) {
			//= Adds pagination if ID matches query.
			$link = $this->add_url_pagination( $link, $this->paged(), true );
		}

		return $link;
	}

	/**
	 * Returns post type archive canonical URL.
	 *
	 * @since 3.0.0
	 *
	 * @param int|string $post_type The post type archive ID or post type.
	 * @return string The post type archive canonical URL, if any.
	 */
	public function get_post_type_archive_canonical_url( $post_type ) {

		if ( is_int( $post_type ) ) {
			$term_id = (int) $post_type;
			$term = $this->fetch_the_term( $term_id );

			if ( $term instanceof \WP_Post_Type ) {
				$link = \get_post_type_archive_link( $term->name );

				if ( $term_id === $this->get_the_real_ID() ) {
					//= Adds pagination if ID matches query.
					$link = $this->add_url_pagination( $link, $this->paged(), true );
				}
			}
		} else {
			$link = \get_post_type_archive_link( $post_type );
		}

		return $link ?: '';
	}

	/**
	 * Returns author canonical URL.
	 * Automatically adds pagination if the ID matches the query.
	 *
	 * @since 3.0.0
	 *
	 * @param int $author_id The author ID.
	 * @return string The author canonical URL, if any.
	 */
	public function get_author_canonical_url( $author_id ) {

		$link = \get_author_posts_url( $author_id );

		if ( ! $link )
			return '';

		if ( $author_id === $this->get_the_real_ID() ) {
			//= Adds pagination if ID matches query.
			$link = $this->add_url_pagination( $link, $this->paged(), true );
		}

		return $link;
	}

	/**
	 * Returns date canonical URL.
	 * Automatically adds pagination if the date input matches the query.
	 *
	 * @since 3.0.0
	 *
	 * @param int $year  The year.
	 * @param int $month The month.
	 * @param int $day   The day.
	 * @return string The author canonical URL, if any.
	 */
	public function get_date_canonical_url( $year, $month = null, $day = null ) {

		if ( $day ) {
			$link = \get_day_link( $year, $month, $day );
			$_get = 'day';
		} elseif ( $month ) {
			$link = \get_month_link( $year, $month );
			$_get = 'month';
			$_get = 1;
		} else {
			$link = \get_year_link( $year );
			$_get = 'year';
		}

		//* Determine whether the input matches query.
		$_paginate = true;
		switch ( $_get ) {
			case 'day' :
				$_day = \get_query_var( 'day' );
				$_paginate = $_paginate && $_day == $day;
				$_get = 'month';
				// Continue switch.

			case 'month' :
				$_month = \get_query_var( 'monthnum' );
				$_paginate = $_paginate && $_month == $month;
				$_get = 'year';
				// Continue switch.

			case 'year' :
				$_year = \get_query_var( 'year' );
				$_paginate = $_paginate && $_year == $year;
		}

		if ( $_paginate ) {
			//= Adds pagination if input matches query.
			$link = $this->add_url_pagination( $link, $this->paged(), true );
		}

		return $link;
	}

	/**
	 * Returns search canonical URL.
	 * Automatically adds pagination if the input matches the query.
	 *
	 * @since 3.0.0
	 *
	 * @param string $query The search query. Mustn't be escaped.
	 *                      When left empty, the current query will be used.
	 * @return string The search link.
	 */
	public function get_search_canonical_url( $query = '' ) {

		$_query = \get_search_query( false );
		$query = $query ?: $_query;
		$link = \get_search_link( $query );

		if ( $_query === $query ) {
			//= Adds pagination if input matches query.
			$link = $this->add_url_pagination( $link, $this->paged(), true );
		}

		return $link;
	}

	/**
	 * Alias of $this->get_preferred_scheme().
	 * Typo.
	 *
	 * @since 2.8.0
	 * @since 2.9.2 Added filter usage cache.
	 * @since 3.0.0 Silently deprecated.
	 * @TODO deprecate visually
	 * @deprecated
	 * @staticvar string $scheme
	 *
	 * @return string The preferred URl scheme.
	 */
	public function get_prefered_scheme() {
		return $this->get_preferred_scheme();
	}

	/**
	 * Returns preferred $url scheme.
	 * Can automatically be detected.
	 *
	 * @since 3.0.0
	 * @staticvar string $scheme
	 *
	 * @return string The preferred URl scheme.
	 */
	public function get_preferred_scheme() {

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
		return $this->set_url_scheme( $url, $this->get_preferred_scheme(), false );
	}

	/**
	 * Sets URL scheme for input URL.
	 * WordPress core function, without filter.
	 *
	 * @since 2.4.2
	 * @since 3.0.0 $use_filter now defaults to false.
	 *
	 * @param string $url Absolute url that includes a scheme.
	 * @param string $scheme optional. Scheme to give $url. Currently 'http', 'https', 'login', 'login_post', 'admin', or 'relative'.
	 * @param bool $use_filter Whether to parse filters.
	 * @return string url with chosen scheme.
	 */
	public function set_url_scheme( $url, $scheme = null, $use_filter = false ) {

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
	 * Adds pagination to input URL.
	 *
	 * @since 3.0.0
	 *
	 * @param string $url      The fully qualified URL.
	 * @param int    $page     The page number. Must be bigger than 1.
	 * @param bool   $use_base Whether to use pagination base. True on archives, false on pages.
	 * @return string The fully qualified URL with pagination.
	 */
	public function add_url_pagination( $url, $page, $use_base ) {

		if ( $page < 2 )
			return $url;

		if ( $this->pretty_permalinks ) {
			if ( $use_base ) {
				static $base;
				$base = $base ?: $GLOBALS['wp_rewrite']->pagination_base;

				$url = \user_trailingslashit( \trailingslashit( $url ) . $base . '/' . $page, 'single_paged' );
			} else {
				$url = \user_trailingslashit( \trailingslashit( $url ) . $page, 'single_paged' );
			}
		} else {
			if ( $use_base ) {
				$url = \add_query_arg( 'paged', $page, $url );
			} else {
				$url = \add_query_arg( 'page', $page, $url );
			}
		}

		return $url;
	}

	/**
	 * Removes pagination from input URL.
	 * The URL must match this query.
	 *
	 * @since 3.0.0
	 *
	 * @param string $url   The fully qualified URL to remove pagination from.
	 * @param int    $page  The page number to remove. If empty, it will get query.
	 * @param int    $paged The page number to remove. If empty, it will get query.
	 * @return string $url The fully qualified URL without pagination.
	 */
	protected function remove_pagination_from_url( $url, $page = 0, $paged = 0 ) {

		if ( $this->pretty_permalinks ) {
			//* Defensive programming...
			static $user_slash;
			$user_slash = isset( $user_slash ) ? $user_slash :
				( $GLOBALS['wp_rewrite']->use_trailing_slashes ? '/' : '' );

			$paged = $paged ?: $this->paged();
			$page = $page ?: $this->page();
			$find = '';

			if ( $paged > 1 ) {
				static $base;
				$base = $base ?: $GLOBALS['wp_rewrite']->pagination_base;

				$find = '/' . $base . '/' . $paged . $user_slash;
			} elseif ( $page > 1 ) {
				$find = '/' . $page . $user_slash;
			}

			if ( $find ) {
				$pos = strrpos( $url, $find );
				//* Defensive programming...
				$continue = $pos && $pos + strlen( $find ) === strlen( $url );
				if ( $continue ) {
					$url = substr( $url, 0, $pos );
					$url = \user_trailingslashit( $url );
				}
			}
		} else {
			$url = \remove_query_arg( array( 'page', 'paged', 'cpage' ), $url );
		}

		return $url;
	}

	/**
	 * Adjusts category post link.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Term  $term  The category to use in the permalink.
	 * @param array     $terms Array of all categories (WP_Term objects) associated with the post.
	 * @param \WP_Post  $post  The post in question.
	 * @return \WP_Term The primary term.
	 */
	public function _adjust_post_link_category( $term, $terms = null, $post = null ) {
		return $this->get_primary_term( $post->ID, $term->taxonomy ) ?: $term;
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

		$home_url = $this->get_homepage_permalink();
		$url = \trailingslashit( $home_url ) . $path . $additions;

		return \esc_url_raw( $url, array( 'http', 'https' ) );
	}

	/**
	 * Generates Previous and Next links.
	 *
	 * @since 2.2.4
	 * @TODO rewrite to use the new 3.0.0+ URL generation.
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
	 * @TODO deprecate.
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
