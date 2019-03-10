<?php
/**
 * @package The_SEO_Framework\Classes
 * @subpackage Classes\Deprecated
 */
namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * @since 3.1.0: Removed all methods deprecated in 3.0.0.
 * @ignore
 */
final class Deprecated {

	/**
	 * Constructor. Does nothing.
	 */
	public function __construct() { }

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
	 *              2. Removed caching, this is done at a deeper level.
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
	 * @param string $method The invoked method. @internal
	 * @return string $title Title
	 */
	public function title( $title = '', $sep = '', $seplocation = '', $args = [], $method = 'title' ) {

		$tsf = \the_seo_framework();
		$tsf->_deprecated_function(
			'the_seo_framework()->' . \esc_html( $method ) . '()',
			'3.1.0',
			'the_seo_framework()->get_title()'
		);

		if ( isset( $args['term_id'] ) ) {
			$new_args = [];
			$new_args['id'] = $args['term_id'];
		}
		if ( isset( $args['taxonomy'] ) ) {
			$new_args = isset( $new_args ) ? $new_args : [];
			$new_args['taxonomy'] = $args['taxonomy'];
		}
		if ( ! empty( $args['is_front_page'] ) || ! empty( $args['page_on_front'] ) ) {
			//= Overwrite args.
			$new_args = [ 'id' => $tsf->get_the_front_page_ID() ];
		}

		return $tsf->get_title( empty( $new_args ) ? null : $new_args );
	}

	/**
	 * Builds the title based on input and query status.
	 *
	 * @since 2.4.0
	 * @since 3.1.0 Deprecated
	 * @deprecated
	 *
	 * @param string $title The Title to return
	 * @param string $seplocation The Title sepeartor location ( accepts 'left' or 'right' )
	 * @param array $args : accepted args : {
	 *    @param int    term_id The Taxonomy Term ID
	 *    @param string taxonomy The Taxonomy name
	 *    @param bool   page_on_front Page on front condition for example generation
	 *    @param bool   placeholder Generate placeholder, ignoring options.
	 *    @param bool   get_custom_field Do not fetch custom title when false.
	 *    @param bool   is_front_page Fetch front page title.
	 * }
	 * @return string $title Title
	 */
	public function build_title( $title = '', $seplocation = '', $args = [] ) {
		return $this->title( $title, '', $seplocation, $args, 'build_title' );
	}

	/**
	 * Generate the title based on conditions for the home page.
	 *
	 * @since 2.3.4
	 * @since 2.3.8 Now checks tagline option.
	 * @since 3.1.0 Deprecated.
	 * @deprecated
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
			'title'       => $tsf->get_raw_generated_title( array( 'id' => $tsf->get_the_front_page_ID() ) ),
			'blogname'    => $tsf->get_home_page_tagline(),
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
	 * Returns untitled title.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Deprecated
	 * @deprecated
	 *
	 * @return string The untitled title.
	 */
	public function untitled() {
		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->untitled()', '3.1.0', 'the_seo_framework()->get_static_untitled_title()' );
		return $tsf->get_static_untitled_title();
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
				 * @since 2.9.4
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

	/**
	 * Returns Post Title from ID.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Deprecated.
	 * @deprecated
	 *
	 * @param int $id The Post ID.
	 * @param string $title Optional. The current/fallback Title.
	 * @return string Post Title
	 */
	public function post_title_from_ID( $id = 0, $title = '' ) { // phpcs:ignore -- ID is capitalized because WordPress does that too: get_the_ID().
		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->post_title_from_ID()', '3.1.0', 'the_seo_framework()->get_raw_generated_title( [ \'id\' => $id ] )' );

		if ( $tsf->is_archive() )
			return $title;

		return $tsf->get_raw_generated_title( [ 'id' => $id ] ) ?: $title;
	}

	/**
	 * Gets the title from custom field
	 *
	 * @since 2.2.8
	 * @since 3.1.0 Deprecated.
	 * @deprecated
	 *
	 * @param string $title the fallback title.
	 * @param bool $escape Parse Title through saninitation calls.
	 * @param int $id The Post ID.
	 * @param string $taxonomy The term name.
	 * @return string The Title.
	 */
	public function title_from_custom_field( $title = '', $escape = false, $id = null, $taxonomy = null ) {
		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->title_from_custom_field()', '3.1.0', 'the_seo_framework()->get_raw_custom_field_title()' );

		$id = isset( $id ) ? $id : $tsf->get_the_real_ID();

		$title = $tsf->get_raw_custom_field_title( [
			'id'       => $id,
			'taxonomy' => $taxonomy,
		] );

		return $escape ? $tsf->escape_title( $title, false ) : (string) $title;
	}

	/**
	 * Fetch Tax labels
	 *
	 * @since 2.3.1
	 * @since 3.1.0 Deprecated
	 * @deprecated
	 * @staticvar object $labels
	 *
	 * @param string $tax_type the Taxonomy type.
	 * @return object|null with all the labels as member variables
	 */
	public function get_tax_labels( $tax_type ) {

		\the_seo_framework()->_deprecated_function( 'the_seo_framework()->get_tax_labels()', '3.1.0' );

		static $labels = null;

		if ( isset( $labels ) )
			return $labels;

		$tax_object = \get_taxonomy( $tax_type );

		if ( is_object( $tax_object ) )
			return $labels = (object) $tax_object->labels;

		//* Nothing found.
		return null;
	}

	/**
	 * Checks (current) Post Type for if this plugin may use it for customizable SEO.
	 *
	 * @since 2.6.0
	 * @since 2.9.3 : Improved caching structure. i.e. it's faster now when no $post_type is supplied.
	 * @staticvar array $cache
	 * @since 3.1.0 1. Deprecated
	 *              2. First parameter is implied.
	 * @global \WP_Screen $current_screen
	 *
	 * @param bool $public Whether to only get Public Post types.
	 * @param string $post_type Optional. The post type to check.
	 * @return bool|string The allowed Post Type. False if it's not supported.
	 */
	public function get_supported_post_type( $public = true, $post_type = '' ) {
		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->get_supported_post_type()', '3.1.0', 'the_seo_framework()->is_post_type_supported()' );
		return $tsf->is_post_type_supported( $post_type ) ? $post_type : false;
	}

	/**
	 * Returns the special URL of a paged post.
	 *
	 * Taken from _wp_link_page() in WordPress core, but instead of anchor markup, just return the URL.
	 *
	 * @since 2.2.4
	 * @since 3.0.0 Now uses WordPress permalinks.
	 * @since 3.1.0 Deprecated.
	 *
	 * @param int $i The page number to generate the URL from.
	 * @param int $post_id The post ID.
	 * @param string $pos Which url to get, accepts next|prev.
	 * @return string The unescaped paged URL.
	 */
	public function get_paged_post_url( $i, $post_id = 0, $pos = 'prev' ) {
		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->get_paged_post_url()', '3.1.0', 'the_seo_framework()->get_paged_url()' );

		if ( empty( $post_id ) )
			$post_id = $tsf->get_the_real_ID();

		if ( 1 === $i ) :
			$url = \get_permalink( $post_id );
		else :
			$post = \get_post( $post_id );
			$url  = \get_permalink( $post_id );

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

			if ( ! $tsf->pretty_permalinks || $tsf->is_draft( $post ) ) {

				//* Put removed query arg back prior to adding pagination.
				if ( isset( $query_arg ) )
					$url = $url . '?' . $query_arg;

				$url = \add_query_arg( 'page', $i, $url );
			} elseif ( $tsf->is_static_frontpage( $post_id ) ) {
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
	 * Checks if the string input is exactly '1'.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Deprecated.
	 *
	 * @param string $value The value to check.
	 * @return bool true if value is '1'
	 */
	public function is_checked( $value ) {
		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->is_checked()', '3.1.0' );
		return (bool) $value;
	}

	/**
	 * Checks if the option is used and checked.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 Deprecated.
	 *
	 * @param string $option The option name.
	 * @return bool Option is checked.
	 */
	public function is_option_checked( $option ) {
		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->is_option_checked()', '3.1.0' );
		return (bool) $tsf->get_option( $option );
	}

	/**
	 * Returns the custom user-inputted description.
	 *
	 * @since 1.0.0
	 * @since 2.9.0 Added two filters.
	 * @since 3.0.6 Silently deprecated.
	 * @since 3.1.0 Deprecated.
	 * @deprecated Use `get_description()` instead.
	 * @deprecated Use `get_generated_description()` instead.
	 *
	 * @param array|null $args   An array of 'id' and 'taxonomy' values.
	 *                           Accepts int values for backward compatibility.
	 * @param bool       $escape Whether to escape the description.
	 * @return string The description
	 */
	public function generate_description( $description = '', $args = null, $escape ) {
		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->generate_description()', '3.1.0', 'the_seo_framework()->get_description()' );
		return $tsf->get_description( $args, $escape ) ?: $description;
	}

	/**
	 * Creates description from custom fields.
	 *
	 * @since 2.4.1
	 * @since 3.0.6 Silently deprecated.
	 * @since 3.1.0 Deprecated.
	 * @deprecated Use `get_description_from_custom_field()` instead.
	 *
	 * @param array $args description args : {
	 *    @param int $id the term or page id.
	 *    @param string $taxonomy taxonomy name.
	 *    @param bool $is_home We're generating for the home page.
	 * }
	 * @param bool  $escape Escape the output if true.
	 * @return string|mixed The description.
	 */
	public function description_from_custom_field( $args = null, $escape = true ) {
		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->description_from_custom_field()', '3.1.0', 'the_seo_framework()->get_description_from_custom_field()' );
		return $tsf->get_description_from_custom_field( $args, $escape );
	}

	/**
	 * Generates description from content while parsing filters.
	 *
	 * @since 2.3.3
	 * @since 3.0.0 No longer checks for protected posts.
	 *              Check is moved to $this->generate_the_description().
	 * @since 3.0.6 Silently deprecated.
	 * @since 3.1.0 Now listens to the `auto_description` option.
	 * @deprecated Use `get_generated_description()` instead.
	 *
	 * @param array $args description args : {
	 *    @param int $id the term or page id.
	 *    @param string $taxonomy taxonomy name.
	 *    @param bool $is_home Whether we're generating for the home page.
	 *    @param bool $get_custom_field Do not fetch custom title when false.
	 *    @param bool $social Generate Social Description when true.
	 * }
	 * @param bool $escape Escape output when true.
	 * @return string $output The description.
	 */
	public function generate_description_from_id( $args = null, $escape = true ) {
		$tsf = \the_seo_framework();
		$tsf->_deprecated_function( 'the_seo_framework()->generate_description_from_id()', '3.1.0', 'the_seo_framework()->get_generated_description()' );
		return $tsf->get_generated_description( $args, $escape );
	}
}
