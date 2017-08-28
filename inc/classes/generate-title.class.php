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

defined( 'ABSPATH' ) or die;

/**
 * Class The_SEO_Framework\Generate_Title
 *
 * Generates title SEO data based on content.
 *
 * @since 2.8.0
 */
class Generate_Title extends Generate_Description {

	/**
	 * Constructor, load parent constructor
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Gets the title. Main function.
	 * Always use this function for the title unless you're absolutely sure what you're doing.
	 *
	 * This function is used for all these: Taxonomies and Terms, Posts, Pages, Blog, front page, front-end, back-end.
	 *
	 * @since 1.0.0
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
	public function title( $title = '', $sep = '', $seplocation = '', $args = array() ) {

		//* Use WordPress default feed title.
		if ( $this->is_feed() )
			return trim( $title );

		$args = $this->reparse_title_args( $args );

		/**
		 * Return early if the request is the Title only (without tagline/blogname).
		 */
		if ( $args['notagline'] )
			return $this->build_title_notagline( $args );

		/**
		 * Add doing it wrong notice for better SEO consistency.
		 * Only when in wp_title.
		 *
		 * @since 2.2.5
		 */
		if ( false === $args['meta'] && false === $this->is_admin() ) {
			if ( false === $this->current_theme_supports_title_tag() && doing_filter( 'wp_title' ) ) {
				if ( $seplocation || $sep ) {
					//* Set doing it wrong parameters.
					$this->set_tell_title_doing_it_wrong( $title, $sep, $seplocation, false );
					//* And echo them.
					\add_action( 'wp_footer', array( $this, 'tell_title_doing_it_wrong' ), 20 );

					//* Notify cache.
					$this->title_doing_it_wrong = true;

					//* Notify transients
					$this->set_theme_dir_transient( false );

					//* Title is empty.
					if ( empty( $seplocation ) && $sep )
						$args['empty_title'] = true;

					return $this->build_title_doingitwrong( $title, $sep, $seplocation, $args );
				}
			}
		}

		//* Notify cache to keep using the same output. We're doing it right :).
		if ( ! isset( $this->title_doing_it_wrong ) )
			$this->title_doing_it_wrong = false;

		//* Set transient to true if the theme is doing it right.
		if ( false === $this->title_doing_it_wrong )
			$this->set_theme_dir_transient( true );

		//* Empty title and rebuild it.
		$title = '';
		return $this->build_title( $title, $seplocation, $args );
	}

	/**
	 * Parse and sanitize title args.
	 *
	 * @since 2.4.0
	 *
	 * @param array $args required The passed arguments.
	 * @param array $defaults The default arguments.
	 * @param bool $get_defaults Return the default arguments. Ignoring $args.
	 * @return array $args parsed args.
	 */
	public function parse_title_args( $args = array(), $defaults = array(), $get_defaults = false ) {

		//* Passing back the defaults reduces the memory usage.
		if ( empty( $defaults ) ) {
			$defaults = array(
				'term_id'           => $this->get_the_real_ID(),
				'taxonomy'          => '',
				'page_on_front'     => false,
				'notagline'         => false,
				'meta'              => false,
				'get_custom_field'  => true,
				'description_title' => false,
				'is_front_page'     => false,
				'escape'            => true,
			);

			/**
			 * Applies filters the_seo_framework_title_args : {
			 *    @param int term_id The Taxonomy Term ID when taxonomy is also filled in. Else post ID.
			 *    @param string taxonomy The Taxonomy name.
			 *    @param bool page_on_front Page on front condition for example generation.
			 *    @param bool notagline Generate title without tagline.
			 *    @param bool meta Ignore doing_it_wrong. Used in og:title/twitter:title
			 *    @param bool get_custom_field Do not fetch custom title when false.
			 *    @param bool description_title Fetch title for description.
			 *    @param bool is_front_page Fetch front page title.
			 * }
			 *
			 * @since 2.5.0
			 *
			 * @param array $defaults The title defaults.
			 * @param array $args The input args.
			 */
			$defaults = (array) \apply_filters( 'the_seo_framework_title_args', $defaults, $args );
		}

		//* Return early if it's only a default args request.
		if ( $get_defaults )
			return $defaults;

		//* Array merge doesn't support sanitation. We're simply type casting here.
		$args['term_id']           = isset( $args['term_id'] )           ? (int) $args['term_id']            : $defaults['term_id'];
		$args['taxonomy']          = isset( $args['taxonomy'] )          ? (string) $args['taxonomy']        : $defaults['taxonomy'];
		$args['page_on_front']     = isset( $args['page_on_front'] )     ? (bool) $args['page_on_front']     : $defaults['page_on_front'];
		$args['notagline']         = isset( $args['notagline'] )         ? (bool) $args['notagline']         : $defaults['notagline'];
		$args['meta']              = isset( $args['meta'] )              ? (bool) $args['meta']              : $defaults['meta'];
		$args['get_custom_field']  = isset( $args['get_custom_field'] )  ? (bool) $args['get_custom_field']  : $defaults['get_custom_field'];
		$args['description_title'] = isset( $args['description_title'] ) ? (bool) $args['description_title'] : $defaults['description_title'];
		$args['is_front_page']     = isset( $args['is_front_page'] )     ? (bool) $args['is_front_page']     : $defaults['is_front_page'];
		$args['escape']            = isset( $args['escape'] )            ? (bool) $args['escape']            : $defaults['escape'];

		return $args;
	}

	/**
	 * Reparses title args.
	 *
	 * @since 2.6.0
	 * @since 2.9.2 Now passes args to filter.
	 *
	 * @param array $args required The passed arguments.
	 * @return array $args parsed args.
	 */
	public function reparse_title_args( $args = array() ) {

		$default_args = $this->parse_title_args( $args, '', true );

		if ( is_array( $args ) ) {
			if ( empty( $args ) ) {
				$args = $default_args;
			} else {
				$args = $this->parse_title_args( $args, $default_args );
			}
		} else {
			//* Old style parameters are used. Doing it wrong.
			$this->_doing_it_wrong( __METHOD__, 'Use $args = array() for parameters.', '2.5.0' );
			$args = $default_args;
		}

		return $args;
	}

	/**
	 * Builds the title based on input, without tagline.
	 *
	 * @since 2.4.0
	 *
	 * @param array $args : accepted args : {
	 *    @param int term_id The Taxonomy Term ID
	 *    @param bool placeholder Generate placeholder, ignoring options.
	 *    @param bool page_on_front Page on front condition for example generation
	 * }
	 * @return string Title without tagline.
	 */
	protected function build_title_notagline( $args = array() ) {

		$title = $this->do_title_pre_filter( '', $args, false );

		if ( empty( $title ) )
			$title = $this->get_notagline_title( $args );

		if ( empty( $title ) )
			$title = $this->untitled();

		$title = $this->do_title_pro_filter( $title, $args, false );

		if ( $args['escape'] )
			$title = $this->escape_title( $title );

		return $title;
	}

	/**
	 * Build the title based on input, without tagline.
	 * Note: Not escaped.
	 *
	 * @since 2.6.0
	 *
	 * @param array $args : accepted args : {
	 *    @param int $term_id The Taxonomy Term ID
	 *    @param bool $placeholder Generate placeholder, ignoring options.
	 *    @param bool $page_on_front Page on front condition for example generation
	 * }
	 * @return string Title without tagline.
	 */
	protected function get_notagline_title( $args = array() ) {

		$title = '';

		//* Fetch title from custom fields or filter.
		if ( $args['get_custom_field'] )
			$title = $this->get_custom_field_title( $title, $args['term_id'], $args['taxonomy'] );

		//* Generate the Title if empty or if home.
		if ( empty( $title ) )
			$title = (string) $this->generate_title( $args, false );

		return $title;
	}

	/**
	 * Builds the title based on input and query status for themes that are doing it wrong.
	 * Pretty much a duplicate of build_title but contains different variables.
	 * Keep this in mind.
	 *
	 * @since 2.4.0
	 *
	 * @param string $title The Title to return
	 * @param string $sep The Title sepeartor
	 * @param string $seplocation The Title sepeartor location ( accepts 'left' or 'right' )
	 * @param array $args : accepted args : {
	 *    @param int term_id The Taxonomy Term ID
	 *    @param string taxonomy The Taxonomy name
	 *    @param bool placeholder Generate placeholder, ignoring options.
	 *    @param bool get_custom_field Do not fetch custom title when false.
	 * }
	 * @return string $title Title
	 */
	public function build_title_doingitwrong( $title = '', $sep = '', $seplocation = '', $args = array() ) {

		if ( $this->the_seo_framework_debug ) $this->debug_init( __METHOD__, true, $debug_key = microtime( true ), get_defined_vars() );

		/**
		 * Empty the title, because most themes think they 'know' how to SEO the front page.
		 * Because, most themes know how to make the title 'pretty'.
		 * And therefor add all kinds of stuff.
		 *
		 * Moved up and return early to reduce processing.
		 * @since 2.3.8
		 */
		if ( $this->is_real_front_page() )
			return $title = '';

		$args = $this->reparse_title_args( $args );

		/**
		 * When using an empty wp_title() function, outputs are unexpected.
		 * This small piece of code will fix all that.
		 * By removing the separator from the title and adding the blog name always to the right.
		 * Which is always the case with doing_it_wrong.
		 *
		 * @thanks JW_ https://wordpress.org/support/topic/wp_title-problem-bug
		 * @since 2.4.3
		 */
		if ( isset( $args['empty_title'] ) ) {
			$title = trim( str_replace( $sep, '', $title ) );
			$seplocation = 'right';
		}

		/**
		 * Applies filters 'the_seo_framework_doingitwrong_add_sep' : bool
		 * Determines additions of separator.
		 * @since 2.4.2
		 */
		$add_sep = (bool) \apply_filters( 'the_seo_framework_doingitwrong_add_sep', true );

		$sep_replace = false;
		//* Maybe remove separator.
		if ( $add_sep && ( $sep || $title ) ) {
			$sep_replace = true;
			$sep_to_replace = (string) $sep;
		}

		//* Fetch the title as is.
		$title = $this->get_notagline_title( $args );

		/**
		 * Applies filters the_seo_framework_title_separator : String The title separator
		 */
		if ( $add_sep )
			$sep = $this->get_title_separator();

		/**
		 * Add $sep_to_replace
		 *
		 * @since 2.3.8
		 */
		if ( $sep_replace ) {
			//* Title always contains something at this point.
			$tit_len = mb_strlen( $title );

			/**
			 * Prevent double separator on date archives.
			 * This will cause manual titles with the same separator at the end to be removed.
			 * Then again, update your theme. D:
			 *
			 * A separator is at least 2 long (space + separator).
			 *
			 * @since 2.4.1 Now also considers seplocation.
			 *
			 * @param string $sep_to_replace Already confirmed to contain the old sep string.
			 */
			if ( $sep_to_replace ) {
				$sep_to_replace_length = mb_strlen( $sep_to_replace );

				if ( 'right' === $seplocation ) {
					if ( $tit_len > $sep_to_replace_length && ! mb_strpos( $title, $sep_to_replace, $tit_len - $sep_to_replace_length ) )
						$title = $title . ' ' . $sep_to_replace;
				} else {
					if ( $tit_len > $sep_to_replace_length && ! mb_strpos( $title, $sep_to_replace, $sep_to_replace_length ) )
						$title = $sep_to_replace . ' ' . $title;
				}
			}

			/**
			 * Convert characters to easier match and prevent removal of matching entities and title characters.
			 * Reported by Riccardo: https://wordpress.org/support/topic/problem-with-post-titles
			 * @since 2.5.2
			 */
			$sep_to_replace = html_entity_decode( $sep_to_replace );
			$title = html_entity_decode( $title );

			/**
			 * Now also considers seplocation.
			 * @since 2.4.1
			 */
			if ( 'right' === $seplocation ) {
				$title = trim( rtrim( $title, "$sep_to_replace " ) ) . " $sep ";
			} else {
				$title = " $sep " . trim( ltrim( $title, " $sep_to_replace" ) );
			}
		} else {
			$title = trim( $title ) . " $sep ";
		}

		if ( ! $args['description_title'] )
			$title = $this->add_title_protection( $title, $args['term_id'] );

		if ( $args['escape'] )
			$title = $this->escape_title( $title, false );

		if ( $this->the_seo_framework_debug ) $this->debug_init( __METHOD__, false, $debug_key, array( 'title_output' => $title ) );

		return $title;
	}

	/**
	 * Builds the title based on input and query status.
	 *
	 * @since 2.4.0
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
	public function build_title( $title = '', $seplocation = '', $args = array() ) {

		if ( $this->the_seo_framework_debug ) $this->debug_init( __METHOD__, true, $debug_key = microtime( true ), get_defined_vars() );

		$args = $this->reparse_title_args( $args );

		/**
		 * Overwrite title here, prevents duplicate title issues, since we're working with a filter.
		 * @since 2.2.2
		 * Use filter title.
		 * @since 2.6.0
		 */
		$title = $this->do_title_pre_filter( '', $args, false );
		$blogname = '';

		$is_front_page = $args['page_on_front'] || $this->is_real_front_page() || $this->is_front_page_by_id( $args['term_id'] );

		$seplocation = $this->get_title_seplocation( $seplocation );

		/**
		 * Generate the Title if empty or if home.
		 *
		 * Generation of title has acquired its own functions.
		 * @since 2.3.4
		 */
		if ( $is_front_page ) {
			$generated = (array) $this->generate_home_title( $args['get_custom_field'], $seplocation, '', false );

			if ( $generated && is_array( $generated ) ) {
				if ( empty( $title ) )
					$title = $generated['title'] ? (string) $generated['title'] : $title;

				$blogname = $generated['blogname'] ? (string) $generated['blogname'] : $blogname;
				$seplocation = $generated['seplocation'] ? (string) $generated['seplocation'] : $seplocation;
			}
		} else {
			//* Fetch the title as is.
			if ( empty( $title ) )
				$title = $this->get_notagline_title( $args );

			$blogname = $this->get_blogname();
		}

		/**
		 * From WordPress core get_the_title.
		 * Bypasses get_post() function object which causes conflict with some themes and plugins.
		 *
		 * Also bypasses the_title filters.
		 * And now also works in admin. It gives you a true representation of its output.
		 *
		 * Title for the description bypasses sanitation and additions.
		 *
		 * @since 2.4.1
		 */
		if ( ! $args['description_title'] ) {
			$title = $this->add_title_protection( $title, $args['term_id'] );
			$title = $this->add_title_pagination( $title );

			if ( $is_front_page ) {
				if ( $this->home_page_add_title_tagline() )
					$title = $this->process_title_additions( $blogname, $title, $seplocation );
			} else {
				if ( $this->add_title_additions() )
					$title = $this->process_title_additions( $title, $blogname, $seplocation );
			}
		}

		$title = $this->do_title_pro_filter( $title, $args, false );

		/**
		 * Applies filters 'the_seo_framework_do_shortcodes_in_title' : Boolean
		 * @since 2.6.6
		 */
		if ( \apply_filters( 'the_seo_framework_do_shortcodes_in_title', false ) )
			$title = \do_shortcode( $title );

		if ( $args['escape'] )
			$title = $this->escape_title( $title );

		if ( $this->the_seo_framework_debug ) $this->debug_init( __METHOD__, false, $debug_key, array( 'title_output' => $title ) );

		return $title;
	}

	/**
	 * Generate the title based on query conditions.
	 *
	 * @since 2.3.4
	 * @since 2.8.0 : Cache now works.
	 * @staticvar array $cache : contains $title strings.
	 *
	 * @param array $args The Title Args.
	 * @param bool $escape Parse Title through saninitation calls.
	 * @return string $title The Generated Title.
	 */
	public function generate_title( $args = array(), $escape = true ) {

		$args = $this->reparse_title_args( $args );

		$title = '';
		$id = $args['term_id'];
		$taxonomy = $args['taxonomy'];

		if ( $this->is_admin() ) {
			$cache = array();
		} else {
			static $cache = array();

			if ( isset( $cache[ $id ][ $taxonomy ] ) )
				$title = $cache[ $id ][ $taxonomy ];
		}

		if ( empty( $title ) ) {

			if ( $args['page_on_front'] ) {
				$title = $this->title_for_home( '', $args['get_custom_field'], false, true );
			} elseif ( $this->is_archive() ) {
				if ( ( $id && $taxonomy ) || $this->is_category() || $this->is_tag() || $this->is_tax() ) {
					$title = $this->title_for_terms( $args, false );
				} else {
					$term = \get_queried_object();
					/**
					 * Get all other archive titles
					 * @since 2.5.2
					 */
					$title = $this->get_the_real_archive_title( $term, $args );
				}
			}

			$title = $this->get_the_404_title( $title );

			/**
			 * @since 2.9.4 This no longer affects post filters on admin pages.
			 */
			$title = $this->get_the_search_title( $title, false );

			//* Fetch the post title if no title is found.
			if ( empty( $title ) )
				$title = $this->post_title_from_ID( $id );

			//* You forgot to enter a title "anywhere"!
			if ( empty( $title ) )
				$title = $this->untitled();

		}

		if ( $escape )
			$title = $this->escape_title( $title, false );

		return $cache[ $id ][ $taxonomy ] = $title;
	}

	/**
	 * Generate the title based on conditions for the home page.
	 *
	 * @since 2.3.4
	 * @access private
	 *
	 * @param bool $get_custom_field Fetch Title from Custom Fields.
	 * @param string $seplocation The separator location
	 * @param string $deprecated Deprecated: The Home Page separator location
	 * @param bool $escape Parse Title through saninitation calls.
	 * @param bool $get_option Whether to fetch the SEO Settings option.
	 * @return array {
	 *    'title'       => (string) $title : The Generated Title
	 *    'blogname'    => (string) $blogname : The Generated Blogname
	 *    'add_tagline' => (bool) $add_tagline : Whether to add the tagline
	 *    'seplocation' => (string) $seplocation : The Separator Location
	 * }
	 */
	public function generate_home_title( $get_custom_field = true, $seplocation = '', $deprecated = '', $escape = true, $get_option = true ) {

		$add_tagline = $this->home_page_add_title_tagline();

		/**
		 * Add tagline or not based on option
		 *
		 * @since 2.2.2
		 */
		if ( $add_tagline ) {
			/**
			 * Tagline based on option.
			 * @since 2.3.8
			 */
			$blogname = $this->get_option( 'homepage_title_tagline' );
			$blogname = $blogname ? $blogname : $this->get_blogdescription();
		} else {
			$blogname = '';
		}

		/**
		 * Render from function
		 * @since 2.2.8
		 */
		$title = $this->title_for_home( '', $get_custom_field, false, $get_option );
		$seplocation = $this->get_home_title_seplocation( $seplocation );

		if ( $escape ) {
			$title = $this->escape_title( $title, false );
			$blogname = $this->escape_title( $blogname, false );
		}

		$defaults = array(
			'title' => $title,
			'blogname' => $blogname,
			'add_tagline' => $add_tagline,
			'seplocation' => $seplocation,
		);

		/**
		 * Applies filters 'the_seo_framework_home_title_args' : array {
		 *   @param string $title : NOTE: This is the blogname
		 *   @param string $blogname : NOTE: This is the tagline.
		 *   @param bool $add_tagline
		 *   @param string $seplocation : 'left' or 'right'
		 * }
		 *
		 * @since 2.8.0
		 *
		 * @param array $args
		 * @param array $defaults
		 */
		$args = (array) \apply_filters( 'the_seo_framework_home_title_args', array(), $defaults );

		return \wp_parse_args( $args, $defaults );
	}

	/**
	 * Gets the title for the static home page.
	 * Essentially falling back to the blogname. Not to be confused with $blogname.
	 *
	 * @since 2.2.8
	 * @access private
	 * @see $this->generate_home_title()
	 *
	 * @param string $home_title The fallback title.
	 * @param bool $get_custom_field Fetch Title from InPost Custom Fields.
	 * @param bool $escape Parse Title through saninitation calls.
	 * @param bool $get_option Whether to fetch the SEO Settings option.
	 * @return string The Title.
	 */
	public function title_for_home( $home_title = '', $get_custom_field = true, $escape = false, $get_option = true ) {

		/**
		 * Get blogname title based on option
		 * @since 2.2.2
		 */
		if ( $get_option )
			$home_title = $this->get_option( 'homepage_title' ) ?: $home_title;

		/**
		 * Fetch from Home Page InPost SEO Box if available.
		 * Only from page on front.
		 */
		if ( $get_custom_field && empty( $home_title ) && $this->has_page_on_front() ) {

			/**
			 * Applies filters 'the_seo_framework_custom_field_home_title' : string
			 * @see filter 'the_seo_framework_custom_field_title'
			 *
			 * @since 2.8.0
			 *
			 * @param string $title The special title.
			 */
			if ( $filter_title = (string) \apply_filters( 'the_seo_framework_custom_field_home_title', '' ) ) {
				$home_title = $filter_title;
			} else {
				$custom_field = $this->get_custom_field( '_genesis_title', $this->get_the_front_page_ID() );
				$home_title = $custom_field ? $custom_field : $this->get_blogname();
			}
		} else {
			$home_title = $home_title ? $home_title : $this->get_blogname();
		}

		if ( $escape )
			$home_title = $this->escape_title( $home_title, false );

		return (string) $home_title;
	}

	/**
	 * Gets the title for Category, Tag or Taxonomy
	 *
	 * @since 2.2.8
	 *
	 * @param array $args The Title arguments.
	 * @param bool $escape Parse Title through saninitation calls.
	 * @return string The Title.
	 */
	public function title_for_terms( $args = array(), $escape = false ) {

		$args = $this->reparse_title_args( $args );

		$title = '';
		$term = null;

		if ( $args['term_id'] && $args['taxonomy'] )
			$term = \get_term( $args['term_id'], $args['taxonomy'], OBJECT, 'raw' );

		if ( $this->is_category() || $this->is_tag() || $this->is_tax() ) {
			if ( ! isset( $term ) && $this->is_tax() )
				$term = \get_term_by( 'slug', \get_query_var( 'term' ), \get_query_var( 'taxonomy' ) );

			if ( ! isset( $term ) )
				$term = $this->fetch_the_term( $args['term_id'] );

			if ( $args['get_custom_field'] ) {
				$data = $this->get_term_data( $term, $args['term_id'] );
				$title = empty( $data['doctitle'] ) ? $title : $data['doctitle'];
			}
		}

		if ( empty( $title ) )
			$title = $this->get_the_real_archive_title( $term, $args );

		if ( $escape )
			$title = $this->escape_title( $title, false );

		return (string) $title;
	}

	/**
	 * Gets the title from custom field
	 *
	 * @since 2.2.8
	 *
	 * @param string $title the fallback title.
	 * @param bool $escape Parse Title through saninitation calls.
	 * @param int $id The Post ID.
	 * @param string $taxonomy The term name.
	 * @return string The Title.
	 */
	public function title_from_custom_field( $title = '', $escape = false, $id = null, $taxonomy = null ) {

		$id = isset( $id ) ? $id : $this->get_the_real_ID();

		/**
		 * Create something special for blog page. Only if it's not the home page.
		 * @since 2.2.8
		 */
		if ( $this->is_singular() ) {
			//* Get title from custom field, empty it if it's not there to override the default title
			$title = $this->get_custom_field( '_genesis_title', $id ) ?: $title;
		} elseif ( $this->is_blog_page( $id ) ) {
			//* Posts page title.
			$title = $this->get_custom_field( '_genesis_title', $id ) ?: \get_the_title( $id );
		} elseif ( $this->is_archive() || ( $id && $taxonomy ) ) {
			//* Get the custom title for terms.
			$term = \get_term( $id, $taxonomy, OBJECT, 'raw' );
			$data = $this->get_term_data( $term, $id );

			$title = empty( $data['doctitle'] ) ? $title : $data['doctitle'];
		}

		if ( $escape )
			$title = $this->escape_title( $title, false );

		return (string) $title;
	}

	/**
	 * Gets the archive Title, including filter. Also works in admin.
	 *
	 * @NOTE Taken from WordPress core. Altered to work in the Admin area.
	 *
	 * @since 2.6.0
	 * @since 2.9.2 : Added WordPress core filter 'get_the_archive_title'
	 *
	 * @param object $term The Term object.
	 * @param array $args The Title arguments.
	 * @return string The Archive Title, not escaped.
	 */
	public function get_the_real_archive_title( $term = null, $args = array() ) {

		if ( empty( $term ) )
			$term = \get_queried_object();

		/**
		 * Applies filters 'the_seo_framework_the_archive_title' : string
		 *
		 * @since 2.6.0
		 *
		 * @param string $title The short circuit title.
		 * @param object $term The Term object.
		 */
		$title = (string) \apply_filters( 'the_seo_framework_the_archive_title', '', $term );

		if ( $title )
			return $title;

		/**
		 * @since 2.6.0
		 */
		$use_prefix = $this->use_archive_prefix( $term, $args );

		if ( $this->is_category() || $this->is_tag() || $this->is_tax() ) {
			$title = $this->single_term_title( '', false, $term );
			/* translators: Front-end output. 1: Taxonomy singular name, 2: Current taxonomy term */
			$title = $use_prefix ? sprintf( \__( '%1$s: %2$s', 'autodescription' ), $this->get_the_term_name( $term ), $title ) : $title;
		} elseif ( $this->is_author() ) {
			$title = \get_the_author();
			/* translators: Front-end output. */
			$title = $use_prefix ? sprintf( \__( 'Author: %s', 'autodescription' ), $title ) : $title;
		} elseif ( $this->is_date() ) {
			if ( $this->is_year() ) {
				/* translators: Front-end output. */
				$title = \get_the_date( \_x( 'Y', 'yearly archives date format', 'autodescription' ) );
				/* translators: Front-end output. */
				$title = $use_prefix ? sprintf( \__( 'Year: %s', 'autodescription' ), $title ) : $title;
			} elseif ( $this->is_month() ) {
				/* translators: Front-end output. */
				$title = \get_the_date( \_x( 'F Y', 'monthly archives date format', 'autodescription' ) );
				/* translators: Front-end output. */
				$title = $use_prefix ? sprintf( \__( 'Month: %s', 'autodescription' ), $title ) : $title;
			} elseif ( $this->is_day() ) {
				/* translators: Front-end output. */
				$title = \get_the_date( \_x( 'F j, Y', 'daily archives date format', 'autodescription' ) );
				/* translators: Front-end output. */
				$title = $use_prefix ? sprintf( \__( 'Day: %s', 'autodescription' ), $title ) : $title;
			}
		} elseif ( $this->is_tax( 'post_format' ) ) {
			if ( \is_tax( 'post_format', 'post-format-aside' ) ) {
				/* translators: Front-end output. */
				$title = \_x( 'Asides', 'post format archive title', 'autodescription' );
			} elseif ( $this->is_tax( 'post_format', 'post-format-gallery' ) ) {
				/* translators: Front-end output. */
				$title = \_x( 'Galleries', 'post format archive title', 'autodescription' );
			} elseif ( $this->is_tax( 'post_format', 'post-format-image' ) ) {
				/* translators: Front-end output. */
				$title = \_x( 'Images', 'post format archive title', 'autodescription' );
			} elseif ( $this->is_tax( 'post_format', 'post-format-video' ) ) {
				/* translators: Front-end output. */
				$title = \_x( 'Videos', 'post format archive title', 'autodescription' );
			} elseif ( $this->is_tax( 'post_format', 'post-format-quote' ) ) {
				/* translators: Front-end output. */
				$title = \_x( 'Quotes', 'post format archive title', 'autodescription' );
			} elseif ( $this->is_tax( 'post_format', 'post-format-link' ) ) {
				/* translators: Front-end output. */
				$title = \_x( 'Links', 'post format archive title', 'autodescription' );
			} elseif ( $this->is_tax( 'post_format', 'post-format-status' ) ) {
				/* translators: Front-end output. */
				$title = \_x( 'Statuses', 'post format archive title', 'autodescription' );
			} elseif ( $this->is_tax( 'post_format', 'post-format-audio' ) ) {
				/* translators: Front-end output. */
				$title = \_x( 'Audio', 'post format archive title', 'autodescription' );
			} elseif ( $this->is_tax( 'post_format', 'post-format-chat' ) ) {
				/* translators: Front-end output. */
				$title = \_x( 'Chats', 'post format archive title', 'autodescription' );
			}
		} elseif ( \is_post_type_archive() ) {
			$title = \post_type_archive_title( '', false ) ?: $this->get_the_term_name( $term, true, false );
			/* translators: Front-end output. */
			$title = $use_prefix ? sprintf( __( 'Archives: %s' ), $title ) : $title;
		} elseif ( isset( $term ) ) {
			$title = $this->single_term_title( '', false, $term );

			if ( $use_prefix ) {
				/* translators: Front-end output. 1: Taxonomy singular name, 2: Current taxonomy term */
				$title = sprintf( __( '%1$s: %2$s', 'autodescription' ), $this->get_the_term_name( $term, true, false ), $title );
			}
		} else {
			/* translators: Front-end output. */
			$title = \__( 'Archives', 'autodescription' );
		}

		/**
		 * Filters the archive title.
		 *
		 * @since WordPress Core 4.1.0
		 *
		 * @param string $title Archive title to be displayed.
		 */
		return \apply_filters( 'get_the_archive_title', $title );
	}

	/**
	 * Fetches single term title.
	 * @NOTE Taken from WordPress core. Altered to work in the Admin area.
	 *
	 * @since 2.6.0
	 *
	 * @return string Single term title.
	 */
	public function single_term_title( $prefix = '', $display = true, $term = null ) {

		if ( is_null( $term ) )
			$term = \get_queried_object();

		if ( ! $term )
			return;

		if ( isset( $term->name ) ) {
			if ( $this->is_category() ) {
				/**
				* Filter the category archive page title.
				*
				* @since 2.0.10 WP CORE
				*
				* @param string $term_name Category name for archive being displayed.
				*/
				$term_name = \apply_filters( 'single_cat_title', $term->name );
			} elseif ( $this->is_tag() ) {
				/**
				* Filter the tag archive page title.
				*
				* @since 2.3.0 WP CORE
				*
				* @param string $term_name Tag name for archive being displayed.
				*/
				$term_name = \apply_filters( 'single_tag_title', $term->name );
			} elseif ( $this->is_tax() || $this->is_admin() ) {
				/**
				* Filter the custom taxonomy archive page title.
				*
				* @since 3.1.0 WP CORE
				*
				* @param string $term_name Term name for archive being displayed.
				*/
				$term_name = \apply_filters( 'single_term_title', $term->name );
			} else {
				return '';
			}
		}

		//* Might be empty through filters.
		if ( empty( $term_name ) )
			$term_name = $this->untitled();

		if ( $display ) {
			echo \esc_attr( $prefix . $term_name );
		} else {
			return $prefix . $term_name;
		}
	}

	/**
	 * Returns custom field title.
	 *
	 * @since 2.6.0
	 *
	 * @param string $title The current title.
	 * @param int $id The post or TT ID.
	 * @param string $taxonomy The TT name.
	 * @return string $title The custom field title.
	 */
	public function get_custom_field_title( $title = '', $id = '', $taxonomy = '' ) {

		/**
		 * Applies filters 'the_seo_framework_custom_field_title' : string
		 * NOTE: This does NOT filter the title for the HOMEpage.
		 * @see filter 'the_seo_framework_custom_field_home_title'
		 *
		 * @since 2.8.0
		 *
		 * @param string $title The special title.
		 * @param int $id The post or TT ID.
		 * @param string $taxonomy the TT name.
		 */
		if ( $filter_title = (string) \apply_filters( 'the_seo_framework_custom_field_title', '', $id, $taxonomy ) ) {
			$title = $filter_title;
		} else {
			$title = $this->title_from_custom_field( $title, false, $id, $taxonomy ) ?: $title;
		}

		return $title;
	}

	/**
	 * Returns untitled title.
	 *
	 * @since 2.6.0
	 *
	 * @return string Untitled. Not escaped.
	 */
	public function untitled() {
		/* translators: Front-end output. */
		return \__( 'Untitled', 'autodescription' );
	}

	/**
	 * Returns Post Title from ID.
	 *
	 * @since 2.6.0
	 *
	 * @param int $id The Post ID.
	 * @param string $title Optional. The current/fallback Title.
	 * @return string Post Title
	 */
	public function post_title_from_ID( $id = 0, $title = '' ) {

		if ( $this->is_archive() )
			return $title;

		$post = \get_post( $id, OBJECT );

		return $title = isset( $post->post_title ) ? $post->post_title : $title;
	}

	/**
	 * Returns search title.
	 *
	 * @since 2.6.0
	 *
	 * @param string $title the current title.
	 * @param bool $escape Whether to escape attributes from query.
	 * @return string Search Title
	 */
	public function get_the_search_title( $title = '', $escape = true ) {

		if ( $this->is_search() ) {
			/* translators: Front-end output. */
			$search_title = (string) \apply_filters( 'the_seo_framework_search_title', __( 'Search results for:', 'autodescription' ) );

			return $search_title . ' ' . trim( \get_search_query( $escape ) );
		}

		return $title;
	}

	/**
	 * Returns 404 title.
	 *
	 * @since 2.6.0
	 *
	 * @since 2.5.2:
	 * Applies filters string the_seo_framework_404_title
	 *
	 * @param string $title The current Title
	 * @return string 404 Title
	 */
	public function get_the_404_title( $title = '' ) {

		if ( $this->is_404() )
			return (string) \apply_filters( 'the_seo_framework_404_title', '404' );

		return $title;
	}

	/**
	 * Gets Title Separator.
	 *
	 * @since 2.6.0
	 * @staticvar string $sep
	 *
	 * @since 2.3.9:
	 * Applies filters the_seo_framework_title_separator
	 *
	 * @return string The Separator, unescaped.
	 */
	public function get_title_separator() {

		static $sep = null;

		if ( isset( $sep ) )
			return $sep;

		return $sep = (string) \apply_filters( 'the_seo_framework_title_separator', $this->get_separator( 'title', false ) );
	}

	/**
	 * Gets Title Seplocation.
	 *
	 * @since 2.3.9
	 * Applies filters the_seo_framework_title_seplocation : string the title location.
	 * Applies filters the_seo_framework_title_seplocation_front : string the home page title location.
	 *
	 * @access private
	 * @since 2.6.0:
	 * @staticvar string $cache
	 *
	 * @param string $seplocation The current seplocation.
	 * @param bool $home The home seplocation.
	 * @return string The Seplocation
	 */
	public function get_title_seplocation( $seplocation = '', $home = false ) {

		static $cache = array();

		if ( isset( $cache[ $seplocation ][ $home ] ) )
			return $cache[ $seplocation ][ $home ];

		if ( empty( $seplocation ) || 'right' !== $seplocation || 'left' !== $seplocation ) {
			if ( $home ) {
				return $cache[ $seplocation ][ $home ] = (string) \apply_filters( 'the_seo_framework_title_seplocation_front', $this->get_option( 'home_title_location' ) );
			} else {
				return $cache[ $seplocation ][ $home ] = (string) \apply_filters( 'the_seo_framework_title_seplocation', $this->get_option( 'title_location' ) );
			}
		}

		return $cache[ $seplocation ][ $home ] = $seplocation;
	}

	/**
	 * Gets Title Seplocation for the homepage.
	 *
	 * @since 2.6.0
	 *
	 * @param string $seplocation The current seplocation.
	 * @return string The Seplocation for the homepage.
	 */
	public function get_home_title_seplocation( $seplocation = '' ) {
		return $this->get_title_seplocation( $seplocation, true );
	}

	/**
	 * Determines whether to add or remove title additions.
	 *
	 * @since 2.4.3
	 * Applies filters the_seo_framework_add_blogname_to_title : boolean
	 *
	 * @since 2.6.0:
	 * @staticvar bool $add
	 *
	 * @return bool True when additions are allowed.
	 */
	public function add_title_additions() {

		static $add = null;

		if ( isset( $add ) )
			return $add;

		if ( $this->can_manipulate_title() )
			if ( $this->is_option_checked( 'title_rem_additions' ) || false === (bool) \apply_filters( 'the_seo_framework_add_blogname_to_title', true ) )
				return $add = false;

		return $add = true;
	}

	/**
	 * Adds the title additions to the title.
	 *
	 * @since 2.6.0
	 *
	 * @param string $title The tite.
	 * @param string $blogname The blogname.
	 * @param string $seplocation The separator location.
	 * @return string Title with possible additions.
	 */
	public function process_title_additions( $title = '', $blogname = '', $seplocation = '' ) {

		$sep = $this->get_title_separator();

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
	 * Adds title protection prefixes.
	 *
	 * @since 2.6.0
	 * @since 2.8.0 Now first checks if is singular.
	 *
	 * @param $title The current Title.
	 * @param $id The page ID.
	 * @return string $title with possible affixes.
	 */
	public function add_title_protection( $title, $id = 0 ) {

		if ( $this->is_singular() ) {
			/**
			 * This is from WordPress core get_the_title().
			 *
			 * Bypasses get_post() function object which causes conflict with some themes and plugins.
			 * Also bypasses 'the_title' filters.
			 *
			 * And now also works in admin. It gives you a true representation of its output.
			 *
			 * @since 2.4.1
			 * Applies filters WordPress core 'protected_title_format' : string The protected title format.
			 * Applies filters WordPress core 'private_title_format' : string The private title format.
			 */

			$post = \get_post( $id, OBJECT );

			if ( isset( $post->post_password ) && '' !== $post->post_password ) {
				/* translators: Front-end output. */
				$protected_title_format = (string) \apply_filters( 'protected_title_format', \__( 'Protected: %s', 'autodescription' ), $post );
				$title = sprintf( $protected_title_format, $title );
			} elseif ( isset( $post->post_status ) && 'private' === $post->post_status ) {
				/* translators: Front-end output. */
				$private_title_format = (string) \apply_filters( 'private_title_format', \__( 'Private: %s', 'autodescription' ), $post );
				$title = sprintf( $private_title_format, $title );
			}
		}

		return $title;
	}

	/**
	 * Adds title pagination, if paginated.
	 *
	 * @since 2.6.0
	 *
	 * @param string $title The current Title.
	 * @return string Title with maybe pagination added.
	 */
	public function add_title_pagination( $title ) {

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
	 * Determines whether to use a title prefix or not.
	 *
	 * @since 2.6.0
	 * @staticvar bool $cache
	 *
	 * @param object $term The Term object.
	 * @param array $args The title arguments.
	 * @return bool
	 */
	public function use_archive_prefix( $term = null, $args = array() ) {

		//* Don't add prefix in meta.
		if ( $args['meta'] )
			return false;

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		/**
		 * Applies filters the_seo_framework_use_archive_title_prefix : {
		 *   @param bool true to add prefix.
		 *   @param object $term The Term object.
		 * }
		 *
		 * @since 2.6.0
		 */
		$filter = (bool) \apply_filters( 'the_seo_framework_use_archive_title_prefix', true, $term );
		$option = ! $this->get_option( 'title_rem_prefixes' );

		return $cache = $option && $filter;
	}

	/**
	 * Filters the title prior to output.
	 *
	 * @since 2.6.0
	 * @access private
	 *
	 * @param string $title The current title.
	 * @param array $args The title args.
	 * @param bool $escape Whether to escape the title.
	 * @return string $title
	 */
	public function do_title_pre_filter( $title, $args, $escape = true ) {

		/**
		 * Applies filters 'the_seo_framework_pre_add_title' : string
		 * @since 2.6.0
		 * @param string $title
		 * @param array $args
		 * @param bool $escape
		 */
		$title = (string) \apply_filters( 'the_seo_framework_pre_add_title', $title, $args, $escape );

		if ( $escape )
			$title = $this->escape_title( $title );

		return $title;
	}

	/**
	 * Filters the title prior to output.
	 *
	 * @since 2.6.0
	 * @access private
	 *
	 * @param string $title The current title.
	 * @param array $args The title args.
	 * @param bool $escape Whether to escape the title.
	 * @return string $title
	 */
	public function do_title_pro_filter( $title, $args, $escape = true ) {

		/**
		 * Applies filters 'the_seo_framework_pro_add_title' : string
		 * @since 2.6.0
		 * @param string $title
		 * @param array $args
		 * @param bool $escape
		 */
		$title = (string) \apply_filters( 'the_seo_framework_pro_add_title', $title, $args, $escape );

		if ( $escape )
			$title = $this->escape_title( $title );

		return $title;
	}

	/**
	 * Determines whether to add home page tagline.
	 *
	 * @since 2.6.0
	 *
	 * @return bool
	 */
	public function home_page_add_title_tagline() {
		return $this->is_option_checked( 'homepage_tagline' );
	}
}
