<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Generate_Url
 * @subpackage The_SEO_Framework\Getters\URL
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2021 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
	 * Determines if the given page has a custom canonical URL.
	 *
	 * @since 3.2.4
	 *
	 * @param null|array $args The canonical URL arguments, leave null to autodetermine query : {
	 *    int    $id               The Post, Page or Term ID to generate the URL for.
	 *    string $taxonomy         The taxonomy.
	 * }
	 * @return bool
	 */
	public function has_custom_canonical_url( $args = null ) {

		if ( ! $args ) {
			if ( $this->is_singular() ) {
				$has = $this->get_singular_custom_canonical_url( $this->get_the_real_ID() );
			} else {
				$has = false;
			}
		} else {
			$this->fix_generation_args( $args );
			if ( ! $args || $args['taxonomy'] ) {
				$has = false;
			} else {
				$has = $this->get_singular_custom_canonical_url( $args['id'] );
			}
		}

		return (bool) $has;
	}

	/**
	 * Caches and returns the current URL.
	 * Memoizes the return value.
	 *
	 * @since 3.0.0
	 *
	 * @return string The current URL.
	 */
	public function get_current_canonical_url() {
		static $cache;
		return isset( $cache ) ? $cache : $cache = $this->get_canonical_url();
	}

	/**
	 * Caches and returns the current permalink.
	 * This link excludes any pagination. Great for structured data.
	 *
	 * Does not work for unregistered pages, like search, 404, date, author, and CPTA.
	 * Memoizes the return value.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 Now properly generates taxonomical URLs.
	 *
	 * @return string The current permalink.
	 */
	public function get_current_permalink() {
		static $cache;
		return isset( $cache ) ? $cache : $cache = $this->create_canonical_url( [
			'id'       => $this->get_the_real_ID(),
			'taxonomy' => $this->get_current_taxonomy(),
		] );
	}

	/**
	 * Caches and returns the homepage URL.
	 * Memoizes the return value.
	 *
	 * @since 3.0.0
	 *
	 * @return string The home URL.
	 */
	public function get_homepage_permalink() {
		static $cache;
		return isset( $cache ) ? $cache : $cache = $this->create_canonical_url( [
			'id'       => $this->get_the_front_page_ID(),
			'taxonomy' => '',
		] );
	}

	/**
	 * Returns a canonical URL based on parameters.
	 * The URL will never be paginated.
	 *
	 * @since 3.0.0
	 * @since 4.0.0 Now preemptively fixes the generation arguments, for easier implementation.
	 * @uses $this->get_canonical_url()
	 *
	 * @param array $args The canonical URL arguments : {
	 *    int    $id               The Post, Page or Term ID to generate the URL for.
	 *    string $taxonomy         The taxonomy.
	 *    bool   $get_custom_field Whether to get custom canonical URLs from user settings.
	 * }
	 * @return string The canonical URL, if any.
	 */
	public function create_canonical_url( $args = [] ) {

		$this->fix_generation_args( $args );
		$args = $args ?: [];

		$defaults = [
			'id'               => 0,
			'taxonomy'         => '',
			'get_custom_field' => false,
		];

		return $this->get_canonical_url( array_merge( $defaults, $args ) );
	}

	/**
	 * Returns the current canonical URL.
	 * Removes pagination if the URL isn't obtained via the query.
	 *
	 * @since 3.0.0
	 * @see $this->create_canonical_url()
	 *
	 * @param array|null $args : Private variable. Use $this->create_canonical_url() instead.
	 * @return string The canonical URL, if any.
	 */
	public function get_canonical_url( $args = null ) {

		if ( $args ) {
			// See and use `$this->create_canonical_url()` instead.
			$canonical_url = $this->build_canonical_url( $args );
			$query         = false;
		} else {
			$canonical_url = $this->generate_canonical_url();
			$query         = true;
		}

		if ( ! $canonical_url )
			return '';

		if ( ! $query && $args['id'] === $this->get_the_real_ID() ) {
			$canonical_url = $this->remove_pagination_from_url( $canonical_url );
		}
		if ( $this->matches_this_domain( $canonical_url ) ) {
			$canonical_url = $this->set_preferred_url_scheme( $canonical_url );
		}

		return $this->clean_canonical_url( $canonical_url );
	}

	/**
	 * Builds canonical URL from input arguments.
	 *
	 * @since 3.0.0
	 * @since 3.2.2 Now tests for the homepage as page prior getting custom field data.
	 * @since 4.0.0 Can now fetch custom canonical URL for terms.
	 * @see $this->create_canonical_url()
	 *
	 * @param array $args Use $this->create_canonical_url().
	 * @return string The canonical URL.
	 */
	protected function build_canonical_url( array $args ) {

		$url = '';

		if ( $args['taxonomy'] ) {
			if ( $args['get_custom_field'] ) {
				$url = $this->get_taxonomical_custom_canonical_url( $args['id'] );
			}
			$url = $url ?: $this->get_taxonomical_canonical_url( $args['id'], $args['taxonomy'] );
		} else {
			if ( $this->is_static_frontpage( $args['id'] ) ) {
				if ( $args['get_custom_field'] ) {
					$url = $this->get_singular_custom_canonical_url( $args['id'] );
				}
				$url = $url ?: $this->get_home_canonical_url();
			} elseif ( $this->is_real_front_page_by_id( $args['id'] ) ) {
				$url = $this->get_home_canonical_url();
			} elseif ( $args['id'] ) {
				if ( $args['get_custom_field'] ) {
					$url = $this->get_singular_custom_canonical_url( $args['id'] );
				}
				$url = $url ?: $this->get_singular_canonical_url( $args['id'] );
			}
		}

		return $url;
	}

	/**
	 * Generates canonical URL from current query.
	 *
	 * @since 3.0.0
	 * @since 4.0.0 Can now fetch custom canonical URL for terms.
	 * @TODO Remove the $id passthrough requirement? Methods lower than this pass it to the query handler...
	 * @see $this->get_canonical_url()
	 *
	 * @return string The canonical URL.
	 */
	protected function generate_canonical_url() {

		$id  = $this->get_the_real_ID();
		$url = '';

		if ( $this->is_real_front_page() ) {
			if ( $this->has_page_on_front() ) {
				$url = $this->get_singular_custom_canonical_url( $id )
					?: $this->get_home_canonical_url();
			} else {
				$url = $this->get_home_canonical_url();
			}
		} elseif ( $this->is_singular() ) {
			$url = $this->get_singular_custom_canonical_url( $id )
				?: $this->get_singular_canonical_url( $id );
		} elseif ( $this->is_archive() ) {
			if ( $this->is_term_meta_capable() ) {
				$url = $this->get_taxonomical_custom_canonical_url( $id )
					?: $this->get_taxonomical_canonical_url( $id, $this->get_current_taxonomy() );
			} elseif ( \is_post_type_archive() ) {
				$url = $this->get_post_type_archive_canonical_url();
			} elseif ( $this->is_author() ) {
				$url = $this->get_author_canonical_url( $id );
			} elseif ( $this->is_date() ) {
				if ( $this->is_day() ) {
					$url = $this->get_date_canonical_url( \get_query_var( 'year' ), \get_query_var( 'monthnum' ), \get_query_var( 'day' ) );
				} elseif ( $this->is_month() ) {
					$url = $this->get_date_canonical_url( \get_query_var( 'year' ), \get_query_var( 'monthnum' ) );
				} elseif ( $this->is_year() ) {
					$url = $this->get_date_canonical_url( \get_query_var( 'year' ) );
				}
			}
		} elseif ( $this->is_search() ) {
			$url = $this->get_search_canonical_url();
		}

		return $url;
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
			$url = \esc_url( $url, [ 'https', 'http' ] );
		} else {
			//= Keep the &'s more readable.
			$url = \esc_url_raw( $url, [ 'https', 'http' ] );
		}

		return $url;
	}

	/**
	 * Returns home canonical URL.
	 * Automatically adds pagination if the ID matches the query.
	 *
	 * @since 3.0.0
	 * @since 3.2.4 1. Now adds a slash to the home URL when it's a root URL.
	 *              2. Now skips slashing when queries have been appended to the URL.
	 *              3. Home-as-page pagination is now supported.
	 *
	 * @return string The home canonical URL.
	 */
	public function get_home_canonical_url() {

		//= Prevent admin bias by passing preferred scheme.
		$url = \get_home_url( null, '', $this->get_preferred_scheme() );

		if ( ! $url ) return '';

		$query_id = $this->get_the_real_ID();

		if ( $this->has_page_on_front() ) {
			if ( $this->is_static_frontpage( $query_id ) ) {
				// Yes, use the pagination base for the homepage-as-page!
				$url = $this->add_url_pagination( $url, $this->page(), true );
			}
		} elseif ( (int) \get_option( 'page_for_posts' ) === $query_id ) {
			$url = $this->add_url_pagination( $url, $this->paged(), true );
		}

		$parsed = parse_url( $url );

		// Don't slash the home URL if it's been modified by a (translation) plugin.
		if ( ! isset( $parsed['query'] ) ) {
			if ( isset( $parsed['path'] ) && '/' !== $parsed['path'] ) {
				$url = \user_trailingslashit( $url, 'home' );
			} else {
				$url = \trailingslashit( $url );
			}
		}

		return $url;
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
		return $this->get_post_meta_item( '_genesis_canonical_uri', $id ) ?: '';
	}

	/**
	 * Returns singular canonical URL.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 Added WC Shop and WP Blog (as page) pagination integration via $this->paged().
	 * @since 3.2.4 Removed pagination support for singular posts, as the SEO attack is now mitigated via WordPress.
	 * @since 4.0.5 Now passes the `$id` to `is_singular_archive()`
	 *
	 * @param int|null $id The page ID.
	 * @return string The custom canonical URL, if any.
	 */
	public function get_singular_canonical_url( $id = null ) {

		$url = \wp_get_canonical_url( $id ) ?: '';

		$_page = \get_query_var( 'page', 1 ) ?: 1; // WP_Query tests isset, not empty.
		if ( $url && $_page !== $this->page() ) {
			/** @link https://core.trac.wordpress.org/ticket/37505 */
			$url = $this->remove_pagination_from_url( $url, $_page, false );
		}

		if ( $url && $this->is_singular_archive( $id ) ) {
			// Singular archives, like blog pages and shop pages, use the pagination base with paged.
			$url = $this->add_url_pagination( $url, $this->paged(), true );
		}

		return $url;
	}

	/**
	 * Returns taxonomical custom field's canonical URL.
	 *
	 * @since 4.0.0
	 *
	 * @param int $term_id The term ID.
	 * @return string The custom canonical URL, if any.
	 */
	public function get_taxonomical_custom_canonical_url( $term_id ) {
		return $this->get_term_meta_item( 'canonical', $term_id ) ?: '';
	}

	/**
	 * Returns taxonomical canonical URL.
	 * Automatically adds pagination if the ID matches the query.
	 *
	 * @since 3.0.0
	 * @since 4.0.0 1. Renamed from "get_taxonomical_canonical_url" (note the typo)
	 *              2. Now works on the admin-screens.
	 *
	 * @param int    $term_id The term ID.
	 * @param string $taxonomy The taxonomy.
	 * @return string The taxonomical canonical URL, if any.
	 */
	public function get_taxonomical_canonical_url( $term_id, $taxonomy ) {

		$term = \get_term( $term_id, $taxonomy );
		$link = \get_term_link( $term, $taxonomy );

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
	 * @since 4.0.0 : 1. Deprecated first parameter as integer. Use strings or null.
	 *                2. Now forwards post type object calling to WordPress's function.
	 *
	 * @param null|string $post_type The post type archive's post type.
	 *                               Leave null to use query, and allow pagination.
	 * @return string The post type archive canonical URL, if any.
	 */
	public function get_post_type_archive_canonical_url( $post_type = null ) {

		if ( \is_int( $post_type ) ) {
			$this->_doing_it_wrong( __METHOD__, 'Only send strings or null in the first parameter.', '4.0.0' );
			$post_type = '';
		}

		$query = true;

		if ( null === $post_type ) {
			$post_type = \get_query_var( 'post_type' );
			$post_type = \is_array( $post_type ) ? reset( $post_type ) : $post_type;

			$query = false;
		}

		$link = \get_post_type_archive_link( $post_type ) ?: '';

		if ( $query && $link )
			$link = $this->add_url_pagination( $link, $this->paged(), true );

		return $link;
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

		// Determine whether the input matches query.
		$_paginate = true;
		switch ( $_get ) {
			case 'day':
				$_day      = \get_query_var( 'day' );
				$_paginate = $_paginate && $_day == $day; // phpcs:ignore, WordPress.PHP.StrictComparisons.LooseComparison
				// No break. Get month too.

			case 'month':
				$_month    = \get_query_var( 'monthnum' );
				$_paginate = $_paginate && $_month == $month; // phpcs:ignore, WordPress.PHP.StrictComparisons.LooseComparison
				// No break. Get year too.

			case 'year':
				$_year     = \get_query_var( 'year' );
				$_paginate = $_paginate && $_year == $year; // phpcs:ignore, WordPress.PHP.StrictComparisons.LooseComparison
				break;
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
	 * @since 3.1.0 : 1. The first parameter now defaults to null.
	 *                2. The search term is now matched with the input query if not set,
	 *                   instead of it being empty.
	 *
	 * @param string $query The search query. Mustn't be escaped.
	 *                      When left empty, the current query will be used.
	 * @return string The search link.
	 */
	public function get_search_canonical_url( $query = null ) {

		$_paginate = false;

		if ( ! isset( $query ) ) {
			$query     = \get_search_query( false );
			$_paginate = true;
		}

		$link = \get_search_link( $query );

		if ( $_paginate ) {
			//= Adds pagination if input query isn't null.
			$link = $this->add_url_pagination( $link, $this->paged(), true );
		}

		return $link;
	}

	/**
	 * Returns preferred $url scheme.
	 * Which can automatically be detected when not set, based on the site URL setting.
	 * Memoizes the return value.
	 *
	 * @since 3.0.0
	 * @since 4.0.0 Now gets the "automatic" scheme from the WordPress home URL.
	 *
	 * @return string The preferred URl scheme.
	 */
	public function get_preferred_scheme() {

		static $scheme;

		if ( isset( $scheme ) )
			return $scheme;

		switch ( $this->get_option( 'canonical_scheme' ) ) :
			case 'https':
				$scheme = 'https';
				break;

			case 'http':
				$scheme = 'http';
				break;

			default:
			case 'automatic':
				$scheme = $this->detect_site_url_scheme();
				break;
		endswitch;

		/**
		 * @since 2.8.0
		 * @param string $scheme The current URL scheme.
		 */
		$scheme = (string) \apply_filters( 'the_seo_framework_preferred_url_scheme', $scheme );

		return $scheme;
	}

	/**
	 * Detects site's URL scheme from site options.
	 * Falls back to is_ssl() when the hom misconfigured via wp-config.php
	 *
	 * NOTE: Some (insecure, e.g. SP) implementations for the `WP_HOME` constant, where
	 * the scheme is interpreted from the request, may cause this to be unreliable.
	 * We're going to ignore those edge-cases; they're doing it wrong.
	 *
	 * However, should we output a notification? Or let them suffer until they use Monitor to find the issue for them?
	 * Yea, Monitor's great for that. Gibe moni plos.
	 *
	 * @since 4.0.0
	 *
	 * @return string The detected URl scheme, lowercase.
	 */
	public function detect_site_url_scheme() {
		return strtolower( parse_url( \get_home_url(), PHP_URL_SCHEME ) ) ?: ( $this->is_ssl() ? 'https' : 'http' );
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
		return $this->set_url_scheme( $url, $this->get_preferred_scheme() );
	}

	/**
	 * Sets URL scheme for input URL.
	 * WordPress core function, without filter.
	 *
	 * @since 2.4.2
	 * @since 3.0.0 $use_filter now defaults to false.
	 * @since 3.1.0 The third parameter ($use_filter) is now $deprecated.
	 * @since 4.0.0 Removed the deprecated parameter.
	 *
	 * @param string $url    Absolute url that includes a scheme.
	 * @param string $scheme Optional. Scheme to give $url. Currently 'http', 'https', 'login', 'login_post', 'admin', or 'relative'.
	 * @return string url with chosen scheme.
	 */
	public function set_url_scheme( $url, $scheme = null ) {

		if ( empty( $scheme ) ) {
			$scheme = $this->is_ssl() ? 'https' : 'http';
		} elseif ( 'admin' === $scheme || 'login' === $scheme || 'login_post' === $scheme || 'rpc' === $scheme ) {
			$scheme = $this->is_ssl() || \force_ssl_admin() ? 'https' : 'http';
		} elseif ( 'http' !== $scheme && 'https' !== $scheme && 'relative' !== $scheme ) {
			$scheme = $this->is_ssl() ? 'https' : 'http';
		}

		$url = $this->make_fully_qualified_url( $url );

		if ( 'relative' === $scheme ) {
			$url = ltrim( preg_replace( '/^\w+:\/\/[^\/]*/', '', $url ) );

			if ( '' !== $url && '/' === $url[0] )
				$url = '/' . ltrim( $url, "/ \t\n\r\0\x0B" );
		} else {
			$url = preg_replace( '#^\w+://#', $scheme . '://', $url );
		}

		return $url;
	}

	/**
	 * Adds pagination to input URL.
	 *
	 * @since 3.0.0
	 * @since 3.2.4 1. Now considers query arguments when using pretty permalinks.
	 *              2. The second and third parameters are now optional.
	 *
	 * @param string $url      The fully qualified URL.
	 * @param int    $page     The page number. Should be bigger than 1 to paginate.
	 * @param bool   $use_base Whether to use pagination base.
	 *                         If null, it will autodetermine.
	 *                         Should be true on archives and the homepage (blog and static!).
	 *                         False on singular post types.
	 * @return string The fully qualified URL with pagination.
	 */
	public function add_url_pagination( $url, $page = null, $use_base = null ) {

		$_page = isset( $page ) ? $page : max( $this->paged(), $this->page() );

		if ( $_page < 2 )
			return $url;

		$_use_base = isset( $use_base ) ? $use_base :
			$this->is_archive() || $this->is_real_front_page() || $this->is_singular_archive();

		if ( $this->pretty_permalinks ) {

			$_query = parse_url( $url, PHP_URL_QUERY );
			// Remove queries, add them back later.
			if ( $_query )
				$url = $this->s_url( $url );

			static $base;
			$base = $base ?: $GLOBALS['wp_rewrite']->pagination_base;

			if ( $_use_base ) {
				$url = \user_trailingslashit( \trailingslashit( $url ) . $base . '/' . $_page, 'paged' );
			} else {
				$url = \user_trailingslashit( \trailingslashit( $url ) . $_page, 'single_paged' );
			}

			if ( $_query )
				$url = $this->append_url_query( $url, $_query );
		} else {
			if ( $_use_base ) {
				$url = \add_query_arg( 'paged', $_page, $url );
			} else {
				$url = \add_query_arg( 'page', $_page, $url );
			}
		}

		return $url;
	}

	/**
	 * Removes pagination from input URL.
	 * The URL must match this query if no second parameter is provided.
	 *
	 * @since 3.0.0
	 * @since 3.2.4 1. Now correctly removes the pagination base on singular post types.
	 *              2. The second parameter now accepts null or a value.
	 *              3. The third parameter is now changed to $use_base, from the archive pagination number.
	 *              4. Now supports pretty permalinks with query parameters.
	 *              5. Is now public.
	 * @since 4.1.2 Now correctly reappends query when pagination isn't removed.
	 *
	 * @param string    $url  The fully qualified URL to remove pagination from.
	 * @param int|null  $page The page number to remove. If null, it will get number from query.
	 * @param bool|null $use_base Whether to remove the pagination base.
	 *                            If null, it will autodetermine.
	 *                            Should be true on archives and the homepage (blog and static!).
	 *                            False on singular post types.
	 * @return string $url The fully qualified URL without pagination.
	 */
	public function remove_pagination_from_url( $url, $page = null, $use_base = null ) {

		if ( $this->pretty_permalinks ) {
			// Defensive programming...
			static $user_slash, $base;
			$user_slash = isset( $user_slash ) ? $user_slash :
				( $GLOBALS['wp_rewrite']->use_trailing_slashes ? '/' : '' );
			$base       = isset( $base ) ? $base : $GLOBALS['wp_rewrite']->pagination_base;

			$_page = isset( $page ) ? $page : max( $this->paged(), $this->page() );

			if ( $_page > 1 ) {
				$_url = $url;

				$_use_base = isset( $use_base ) ? $use_base
					: $this->is_archive() || $this->is_real_front_page() || $this->is_singular_archive();

				if ( $_use_base ) {
					$find = '/' . $base . '/' . $_page . $user_slash;
				} else {
					$find = '/' . $_page . $user_slash;
				}

				$_query = parse_url( $_url, PHP_URL_QUERY );
				// Remove queries, add them back later.
				if ( $_query )
					$_url = $this->s_url( $_url );

				$pos = strrpos( $_url, $find );
				// Defensive programming, only remove if $find matches the stack length, without query arguments.
				$continue = $pos && $pos + \strlen( $find ) === \strlen( $_url );

				if ( $continue ) {
					$_url = substr( $_url, 0, $pos );
					$_url = \user_trailingslashit( $_url );

					// Add back the query.
					if ( $_query )
						$_url = $this->append_url_query( $_url, $_query );
				}

				$url = $_url;
			}
		} else {
			$url = \remove_query_arg( [ 'page', 'paged', 'cpage' ], $url );
		}

		return $url;
	}

	/**
	 * Adjusts category post link.
	 *
	 * @since 3.0.0
	 * @since 4.0.3 Now fills in a fallback $post object when null.
	 * @access private
	 *
	 * @param \WP_Term $term  The category to use in the permalink.
	 * @param array    $terms Array of all categories (WP_Term objects) associated with the post. Unused.
	 * @param \WP_Post $post  The post in question.
	 * @return \WP_Term The primary term.
	 */
	public function _adjust_post_link_category( $term, $terms = null, $post = null ) {

		if ( null === $post )
			$post = \get_post( $this->get_the_real_ID() );

		return $this->get_primary_term( $post->ID, $term->taxonomy ) ?: $term;
	}

	/**
	 * Generates shortlink URL.
	 *
	 * @since 2.2.2
	 * @since 3.1.0 1. No longer accepts $post_id input. Output's based on query only.
	 *              2. Shortened date archive URL length.
	 *              3. Removed query parameter collisions.
	 *
	 * @return string|null Escaped site Shortlink URL.
	 */
	public function get_shortlink() {

		if ( ! $this->get_option( 'shortlink_tag' ) ) return '';
		if ( $this->is_real_front_page() ) return '';

		// We slash it because plain permalinks do that too, for consistency.
		$home = \trailingslashit( $this->get_homepage_permalink() );
		$id   = $this->get_the_real_ID();
		$url  = '';

		if ( $this->is_singular() ) {
			$url = \add_query_arg( [ 'p' => $id ], $home );
		} elseif ( $this->is_archive() ) {
			if ( $this->is_category() ) {
				$url = \add_query_arg( [ 'cat' => $id ], $home );
			} elseif ( $this->is_tag() ) {
				$url = \add_query_arg( [ 'post_tag' => $id ], $home );
			} elseif ( $this->is_date() && isset( $GLOBALS['wp_query']->query ) ) {
				// FIXME: Core Report: WP doesn't accept paged parameters w/ date parameters. It'll lead to the homepage.
				$_query = $GLOBALS['wp_query']->query;
				$_date  = [
					'y' => isset( $_query['year'] ) ? $_query['year'] : '',
					'm' => isset( $_query['monthnum'] ) ? $_query['monthnum'] : '',
					'd' => isset( $_query['day'] ) ? $_query['day'] : '',
				];

				$url = \add_query_arg( [ 'm' => implode( '', $_date ) ], $home );
			} elseif ( $this->is_author() ) {
				$url = \add_query_arg( [ 'author' => $id ], $home );
			} elseif ( $this->is_tax() ) {
				// Generate shortlink for object type and slug.
				$object = \get_queried_object();

				$tax  = isset( $object->taxonomy ) ? $object->taxonomy : '';
				$slug = isset( $object->slug ) ? $object->slug : '';

				if ( $tax && $slug ) {
					$url = \add_query_arg( [ $tax => $slug ], $home );
				}
			}
		} elseif ( $this->is_search() ) {
			$url = \add_query_arg( [ 's' => \get_search_query( false ) ], $home );
		}

		if ( ! $url ) return '';

		if ( $this->is_archive() || $this->is_singular_archive() || $this->is_search() ) {
			$paged = $this->maybe_get_paged( $this->paged(), false, true );
			$url   = \add_query_arg( [ 'paged' => $paged ], $url );
		} else {
			$page = $this->maybe_get_paged( $this->page(), false, true );
			$url  = \add_query_arg( [ 'page' => $page ], $url );
		}

		//? Append queries other plugins might've filtered.
		if ( $this->is_singular() ) {
			$url = $this->append_url_query(
				$url,
				parse_url( \get_permalink( $id ), PHP_URL_QUERY )
			);
		}

		return \esc_url_raw( $url, [ 'https', 'http' ] );
	}

	/**
	 * Generates Previous and Next links.
	 *
	 * @since 2.2.4
	 * @since 3.1.0 1. Now recognizes WC Shops and WP Blog pages as archival types.
	 *              2. Now sanitizes canonical URL according to permalink settings.
	 *              3. Removed second parameter. It was only a source of bugs.
	 *              4. Removed WordPress Core `get_pagenum_link` filter.
	 * @uses $this->get_paged_urls();
	 * @api Not used internally.
	 *
	 * @param string $next_prev Whether to get the previous or next page link.
	 *                          Accepts 'prev' and 'next'.
	 * @return string Escaped site Pagination URL
	 */
	public function get_paged_url( $next_prev ) {
		return $this->get_paged_urls()[ $next_prev ];
	}

	/**
	 * Generates Previous and Next links.
	 *
	 * @since 3.1.0
	 * @since 3.2.4 1. Now correctly removes the pagination base from singular URLs.
	 *              2. Now returns no URLs when a custom canonical URL is set.
	 * @since 4.1.0 Removed memoization.
	 * @since 4.1.2 1. Added back memoization.
	 *              2. Reduced needless canonical URL generation when it wouldn't be processed anyway.
	 *
	 * @return array Escaped site Pagination URLs: {
	 *    string 'prev'
	 *    string 'next'
	 * }
	 */
	public function get_paged_urls() {

		static $prev, $next;

		if ( isset( $prev, $next ) ) goto end;

		$prev = $next = '';

		if ( $this->has_custom_canonical_url() ) goto end;

		// phpcs:disable, WordPress.WhiteSpace.PrecisionAlignment
		if ( $this->is_singular() && ! $this->is_singular_archive() && $this->is_multipage() ) {
			$_run = $this->is_real_front_page()
				  ? $this->get_option( 'prev_next_frontpage' )
				  : $this->get_option( 'prev_next_posts' );

			if ( ! $_run ) goto end;

			$page      = $this->page();
			$_numpages = $this->numpages();
		} elseif ( $this->is_real_front_page() || $this->is_archive() || $this->is_singular_archive() || $this->is_search() ) {
			$_run = $this->is_real_front_page()
				  ? $this->get_option( 'prev_next_frontpage' )
				  : $this->get_option( 'prev_next_archives' );

			if ( ! $_run ) goto end;

			$page      = $this->paged();
			$_numpages = $this->numpages();
		} else {
			goto end;
		}
		// phpcs:enable, WordPress.WhiteSpace.PrecisionAlignment

		// See if-statements below.
		if ( ! ( $page + 1 <= $_numpages || $page > 1 ) ) goto end;

		$canonical = $this->remove_pagination_from_url( $this->get_current_canonical_url() );

		// If this page is not the last, create a next-URL.
		if ( $page + 1 <= $_numpages ) {
			$next = $this->add_url_pagination( $canonical, $page + 1 );
		}
		// If this page is not the first, create a prev-URL.
		if ( $page > 1 ) {
			$prev = $this->add_url_pagination( $canonical, $page - 1 );
		}

		end:;

		return compact( 'next', 'prev' );
	}

	/**
	 * Fetches home URL host. Like "wordpress.org".
	 * If this fails, you're going to have a bad time.
	 * Memoizes the return value.
	 *
	 * @since 2.7.0
	 * @since 2.9.2 : 1. Now considers port too.
	 *                2. Now uses get_home_url(), rather than get_option('home').
	 *
	 * @return string The home URL host.
	 */
	public function get_home_host() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		$parsed_url = parse_url( \get_home_url() );

		$host = isset( $parsed_url['host'] ) ? $parsed_url['host'] : '';

		if ( $host && isset( $parsed_url['port'] ) )
			$host .= ':' . $parsed_url['port'];

		return $cache = $host;
	}

	/**
	 * Add $paged if Paginated and allowed through arguments.
	 *
	 * @since 2.6.0
	 *
	 * @param int  $paged The current page number.
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
	 * Makes a fully qualified URL by adding the scheme prefix.
	 * Always adds http prefix, not https.
	 *
	 * NOTE: Expects the URL to have either a scheme, or a relative scheme set.
	 *       Domain-relative URLs aren't parsed correctly.
	 *       '/path/to/folder/` will become `http:///path/to/folder/`
	 *
	 * @since 2.6.5
	 * @see `$this->set_url_scheme()` to set the correct scheme.
	 * @see `$this->convert_to_url_if_path()` to create URLs from paths.
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
	 * Makes a fully qualified URL from any input.
	 *
	 * @since 4.0.0
	 * @see `$this->s_relative_url()` to make URLs relative.
	 *
	 * @param string $path Either the URL or path. Will always be transformed to the current domain.
	 * @param string $url  The URL to add the path to. Defaults to the current home URL.
	 * @return string $url
	 */
	public function convert_to_url_if_path( $path, $url = '' ) {
		return \WP_Http::make_absolute_url(
			$path,
			\trailingslashit( $url ?: $this->set_preferred_url_scheme( $this->get_home_host() ) )
		);
	}

	/**
	 * Appends given query to given URL.
	 *
	 * @since 4.1.4
	 *
	 * @param string $url   A fully qualified URL.
	 * @param string $query A fully qualified query taken from parse_url( $url, PHP_URL_QUERY );
	 * @return string A fully qualified URL with appended $query.
	 */
	public function append_url_query( $url, $query = '' ) {

		if ( ! $query )
			return $url;

		$_fragment = parse_url( $url, PHP_URL_FRAGMENT );

		if ( $_fragment )
			$url = str_replace( "#$_fragment", '', $url );

		parse_str( $query, $results );

		if ( $results )
			$url = \add_query_arg( $results, $url );

		if ( $_fragment )
			$url .= '#' . $_fragment;

		return $url;
	}

	/**
	 * Tests if input URL matches current domain.
	 *
	 * @since 2.9.4
	 * @since 4.1.0 Improved performance by testing an early match.
	 *
	 * @param string $url The URL to test. Required.
	 * @return bool true on match, false otherwise.
	 */
	public function matches_this_domain( $url ) {

		if ( ! $url )
			return false;

		static $home_domain;

		if ( ! $home_domain ) {
			$home_domain = \esc_url_raw( \get_home_url(), [ 'https', 'http' ] );
			//= Simply convert to HTTPS/HTTP based on is_ssl()
			$home_domain = $this->set_url_scheme( $home_domain );
		}

		// Test for likely match early, before transforming.
		if ( 0 === stripos( $url, $home_domain ) )
			return true;

		$url = \esc_url_raw( $url, [ 'https', 'http' ] );
		//= Simply convert to HTTPS/HTTP based on is_ssl()
		$url = $this->set_url_scheme( $url );

		//= If they start with the same, we can assume it's the same domain.
		if ( 0 === stripos( $url, $home_domain ) )
			return true;

		return false;
	}
}
