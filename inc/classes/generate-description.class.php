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
 * Class The_SEO_Framework\Generate_Description
 *
 * Generates Description SEO data based on content.
 *
 * @since 2.8.0
 */
class Generate_Description extends Generate {

	/**
	 * Constructor, loads parent constructor.
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Creates description. Base function.
	 *
	 * @since 1.0.0
	 * @since 2.9.0 Added two filters.
	 *
	 * @param string $description The optional description to simply parse.
	 * @param array $args description args : {
	 *    @param int $id the term or page id.
	 *    @param string $taxonomy taxonomy name.
	 *    @param bool $is_home We're generating for the home page.
	 *    @param bool $get_custom_field Do not fetch custom title when false.
	 *    @param bool $social Generate Social Description when true.
	 * }
	 * @return string The description
	 */
	public function generate_description( $description = '', $args = array() ) {

		/**
		 * Parse args.
		 * @since 2.5.0
		 */
		$args = $this->reparse_description_args( $args );

		if ( $args['get_custom_field'] && empty( $description ) ) {
			//* Fetch from options, if any.
			$description = $this->description_from_custom_field( $args, false );

			/**
			 * Applies filters 'the_seo_framework_custom_field_description' : string
			 *
			 * Filters the description from custom field, if any.
			 *
			 * @since 2.9.0
			 *
			 * @param string $description The description.
			 * @param array $args The description arguments.
			 */
			$description = (string) \apply_filters( 'the_seo_framework_custom_field_description', $description, $args );

			//* We've already checked the custom fields, so let's remove the check in the generation.
			$args['get_custom_field'] = false;
		}

		//* Still no description found? Create an auto description based on content.
		if ( empty( $description ) || false === is_scalar( $description ) ) {
			$description = $this->generate_description_from_id( $args, false );

			/**
			 * Applies filters 'the_seo_framework_generated_description' : string
			 *
			 * Filters the generated description, if any.
			 *
			 * @since 2.9.0
			 *
			 * @param string $description The description.
			 * @param array $args The description arguments.
			 */
			$description = (string) \apply_filters( 'the_seo_framework_generated_description', $description, $args );
		}

		/**
		 * Applies filters 'the_seo_framework_do_shortcodes_in_description' : Boolean
		 * @since 2.6.6
		 */
		if ( \apply_filters( 'the_seo_framework_do_shortcodes_in_description', false ) )
			$description = \do_shortcode( $description );

		return $this->escape_description( $description );
	}

	/**
	 * Parses and sanitizes description arguments.
	 *
	 * @since 2.5.0
	 *
	 * @param array $args required The passed arguments.
	 * @param array $defaults The default arguments.
	 * @param bool $get_defaults Return the default arguments. Ignoring $args.
	 * @return array $args parsed args.
	 */
	public function parse_description_args( $args = array(), $defaults = array(), $get_defaults = false ) {

		//* Passing back the defaults reduces the memory usage.
		if ( empty( $defaults ) ) {
			$defaults = array(
				'id'               => $this->get_the_real_ID(),
				'taxonomy'         => '',
				'is_home'          => false,
				'get_custom_field' => true,
				'social'           => false,
			);

			/**
			 * Applies filters 'the_seo_framework_description_args' : array {
			 *    @param int $id the term or page id.
			 *    @param string $taxonomy taxonomy name.
			 *    @param bool $is_home We're generating for the home page.
			 *    @param bool $get_custom_field Do not fetch custom title when false.
			 *    @param bool $social Generate Social Description when true.
			 * }
			 *
			 * @since 2.5.0
			 *
			 * @param array $defaults The description defaults.
			 * @param array $args The input args.
			 */
			$defaults = (array) \apply_filters( 'the_seo_framework_description_args', $defaults, $args );
		}

		//* Return early if it's only a default args request.
		if ( $get_defaults )
			return $defaults;

		//* Array merge doesn't support sanitation. We're simply type casting here.
		$args['id']               = isset( $args['id'] )               ? (int) $args['id']                : $defaults['id'];
		$args['taxonomy']         = isset( $args['taxonomy'] )         ? (string) $args['taxonomy']       : $defaults['taxonomy'];
		$args['is_home']          = isset( $args['is_home'] )          ? (bool) $args['is_home']          : $defaults['is_home'];
		$args['get_custom_field'] = isset( $args['get_custom_field'] ) ? (bool) $args['get_custom_field'] : $defaults['get_custom_field'];
		$args['social']           = isset( $args['social'] )           ? (bool) $args['social']           : $defaults['social'];

		return $args;
	}

	/**
	 * Reparses description args.
	 *
	 * @since 2.6.0
	 * @since 2.9.2 Now passes args to filter.
	 *
	 * @param array $args required The passed arguments.
	 * @return array $args parsed args.
	 */
	public function reparse_description_args( $args = array() ) {

		$default_args = $this->parse_description_args( $args, '', true );

		if ( is_array( $args ) ) {
			if ( empty( $args ) ) {
				$args = $default_args;
			} else {
				$args = $this->parse_description_args( $args, $default_args );
			}
		} else {
			//* Old style parameters are used. Doing it wrong.
			$this->_doing_it_wrong( __METHOD__, 'Use $args = array() for parameters.', '2.5.0' );
			$args = $default_args;
		}

		return $args;
	}

	/**
	 * Creates description from custom fields.
	 *
	 * @since 2.4.1
	 *
	 * @param array $args description args : {
	 *    @param int $id the term or page id.
	 *    @param string $taxonomy taxonomy name.
	 *    @param bool $is_home We're generating for the home page.
	 * }
	 * @param bool $escape Escape the output if true.
	 * @return string|mixed The description.
	 */
	public function description_from_custom_field( $args = array(), $escape = true ) {

		/**
		 * Parse args.
		 * @since 2.5.0
		 */
		$args = $this->reparse_description_args( $args );

		//* HomePage Description.
		$description = $this->get_custom_homepage_description( $args );

		if ( empty( $description ) ) {
			if ( $this->is_archive() ) {
				$description = $this->get_custom_archive_description( $args );
			} else {
				$description = $this->get_custom_singular_description( $args['id'] );
			}
		}

		if ( $escape )
			$description = $this->escape_description( $description );

		return $description;
	}

	/**
	 * Fetches HomePage Description from custom field.
	 *
	 * @since 2.6.0
	 * @since 2.9.0 1. Removed $args['taxonomy'] check.
	 *              2. Added $this->is_archive() check.
	 * Use $this->description_from_custom_field() instead.
	 *
	 * @param array $args Description args.
	 * @return string The Description
	 */
	protected function get_custom_homepage_description( $args ) {

		$description = '';

		if ( $args['is_home'] || $this->is_real_front_page() || ( ! $this->is_archive() && $this->is_static_frontpage( $args['id'] ) ) )
			$description = $this->get_option( 'homepage_description' ) ?: '';

		return $description;
	}

	/**
	 * Fetches Singular Description from custom field.
	 *
	 * @since 2.6.0
	 * Use $this->description_from_custom_field() instead.
	 *
	 * @param int $id The page ID.
	 * @return string The Description
	 */
	protected function get_custom_singular_description( $id ) {

		$description = '';

		if ( $this->is_singular( $id ) ) {
			$description = $this->get_custom_field( '_genesis_description', $id ) ?: '';
		}

		return $description;
	}

	/**
	 * Fetch Archive Description from custom field.
	 *
	 * @since 2.6.0
	 * Use $this->description_from_custom_field() instead.
	 *
	 * @param array $args
	 * @return string The Description
	 */
	protected function get_custom_archive_description( $args ) {

		$description = '';

		if ( $this->is_archive() ) {
			if ( $this->is_category() || $this->is_tag() || $this->is_tax() ) {

				$term = $this->fetch_the_term( $args['id'] );
				$data = $this->get_term_data( $term, $args['id'] );

				$description = empty( $data['description'] ) ? $description : $data['description'];
			}
		}

		return $description;
	}

	/**
	 * Generates description from content while parsing filters.
	 *
	 * @since 2.3.3
	 *
	 * @param array $args description args : {
	 *    @param int $id the term or page id.
	 *    @param string $taxonomy taxonomy name.
	 *    @param bool $is_home We're generating for the home page.
	 *    @param bool $get_custom_field Do not fetch custom title when false.
	 *    @param bool $social Generate Social Description when true.
	 * }
	 * @param bool $escape Escape output when true.
	 * @return string $output The description.
	 */
	public function generate_description_from_id( $args = array(), $escape = true ) {

		$this->the_seo_framework_debug and $this->debug_init( __METHOD__, true, $debug_key = microtime( true ), get_defined_vars() );

		/**
		 * Applies filters bool 'the_seo_framework_enable_auto_description' : Enable or disable the description.
		 *
		 * @since 2.5.0
		 */
		$autodescription = (bool) \apply_filters( 'the_seo_framework_enable_auto_description', true );
		if ( false === $autodescription || $this->is_protected( $args['id'] ) )
			return '';

		$description = $this->generate_the_description( $args, false );

		if ( $escape )
			$description = $this->escape_description( $description );

		$this->the_seo_framework_debug and $this->debug_init( __METHOD__, false, $debug_key, array( 'description' => $description, 'transient_key' => $this->auto_description_transient ) );

		return (string) $description;
	}

	/**
	 * Generates description from content.
	 *
	 * @since 2.6.0
	 * @since 2.8.0 : The output is always trimmed if $escape is false.
	 *              : The cache will no longer be maintained on previews or search.
	 *
	 * @param array $args description args : {
	 *    @param int $id the term or page id.
	 *    @param string $taxonomy taxonomy name.
	 *    @param bool $is_home We're generating for the home page.
	 *    @param bool $get_custom_field Do not fetch custom title when false.
	 *    @param bool $social Generate Social Description when true.
	 * }
	 * @param bool $escape Whether to escape the description.
	 *        NOTE: When this is false, be sure to trim the output.
	 * @return string The description.
	 */
	protected function generate_the_description( $args, $escape = true ) {

		/**
		 * Parse args.
		 * @since 2.5.0
		 */
		$args = $this->reparse_description_args( $args );

		//* Home Page description
		if ( $args['is_home'] || $this->is_real_front_page() || $this->is_front_page_by_id( $args['id'] ) )
			return $this->generate_home_page_description( $args['get_custom_field'], $escape );

		/**
		 * Determines whether to prevent caching of transients.
		 * @since 2.8.0
		 */
		$nocache = ( ! $this->is_admin() && $this->is_search() ) || $this->is_preview();
		$use_cache = ! $nocache && $this->is_option_checked( 'cache_meta_description' );

		/**
		 * Setup transient.
		 */
		$use_cache and $this->setup_auto_description_transient( $args['id'], $args['taxonomy'] );

		$term = $this->fetch_the_term( $args['id'] );

		/**
		 * @since 2.8.0: Added check for option 'cache_meta_description'.
		 */
		$excerpt = $use_cache ? $this->get_transient( $this->auto_description_transient ) : false;
		if ( false === $excerpt ) {
			$excerpt = array();

			/**
			 * @since 2.8.0:
			 *    1. Added check for option 'cache_meta_description' and search/preview.
			 *    2. Moved generation functions in two different methods.
			 */
			if ( $use_cache ) {
				$excerpt_normal = $this->get_description_excerpt_normal( $args['id'], $term );

				$excerpt['normal'] = $excerpt_normal['excerpt'];
				$excerpt['trim'] = $excerpt_normal['trim'];
				$excerpt['social'] = $this->get_description_excerpt_social( $args['id'], $term );

				/**
				 * Transient expiration: 1 week.
				 * Keep the description for at most 1 week.
				 */
				$expiration = WEEK_IN_SECONDS;

				$this->set_transient( $this->auto_description_transient, $excerpt, $expiration );
			} elseif ( $args['social'] ) {
				$excerpt['social'] = $this->get_description_excerpt_social( $args['id'], $term );
			} else {
				$excerpt_normal = $this->get_description_excerpt_normal( $args['id'], $term );

				$excerpt['normal'] = $excerpt_normal['excerpt'];
				$excerpt['trim'] = $excerpt_normal['trim'];
			}
		}

		/**
		 * Check for Social description, don't add blogname then.
		 * Also continues normally if it's the front page.
		 *
		 * @since 2.5.0
		 */
		if ( $args['social'] ) {
			//* No social description if nothing is found.
			$description = $excerpt['social'] ? $excerpt['social'] : '';
		} else {
			if ( $excerpt['normal'] ) {
				if ( $excerpt['trim'] ) {
					$description = $excerpt['normal'];
				} else {
					if ( $term || ! \has_excerpt( $args['id'] ) ) {
						$additions = $this->generate_description_additions( $args['id'], $term, false );

						$title_on_blogname = $this->get_title_on_blogname( $additions );
						$sep = $additions['sep'];
					} else {
						$title_on_blogname = $sep = '';
					}

					/* translators: 1: Title, 2: Separator, 3: Excerpt */
					$description = sprintf( \_x( '%1$s %2$s %3$s', '1: Title, 2: Separator, 3: Excerpt', 'autodescription' ), $title_on_blogname, $sep, $excerpt['normal'] );
				}
			} else {
				//* Fetch additions ignoring options.
				$additions = $this->generate_description_additions( $args['id'], $term, true );

				//* We still add the additions when no excerpt has been found.
				// i.e. home page or empty/shortcode filled page.
				$description = $this->get_title_on_blogname( $additions );
			}
		}

		if ( $escape ) {
			$description = $this->escape_description( $description );
		} else {
			$description = trim( $description );
		}

		return $description;
	}

	/**
	 * Returns the generated description excerpt array for the normal description tag.
	 *
	 * @since 2.8.0
	 *
	 * @param int $id The post/term ID.
	 * @param bool|object The term object.
	 * @return array {
	 *    'excerpt' => string The excerpt. Unescaped.
	 *    'trim' => bool Whether to trim the additions.
	 * }
	 */
	public function get_description_excerpt_normal( $id = 0, $term = false ) {

		$title = '';
		$on = '';
		$blogname = '';
		$sep = '';

		if ( $term || ! \has_excerpt( $id ) ) {
			$title_on_blogname = $this->generate_description_additions( $id, $term, false );
			$title = $title_on_blogname['title'];
			$on = $title_on_blogname['on'];
			$blogname = $title_on_blogname['blogname'];
			$sep = $title_on_blogname['sep'];
		}

		$additions = trim( "$title $on $blogname" );
		//* If there are additions, add a trailing space.
		if ( $additions )
			$additions .= ' ';

		$additions_length = $additions ? mb_strlen( html_entity_decode( $additions ) ) : 0;
		/**
		 * Determine if the title is far too long (72+, rather than 75 in the Title guidelines).
		 * If this is the case, trim the "title on blogname" part from the description.
		 * @since 2.8.0
		 */
		if ( $additions_length > 71 ) {
			$max_char_length = 155;
			$trim = true;
		} else {
			$max_char_length = 155 - $additions_length;
			$trim = false;
		}

		$excerpt_normal = $this->generate_excerpt( $id, $term, $max_char_length );

		/**
		 * Put in array to be accessed later.
		 * @since 2.8.0 Added trim value.
		 */
		return array(
			'excerpt' => $excerpt_normal,
			'trim' => $trim,
		);
	}

	/**
	 * Returns the generated description excerpt for the social description tag.
	 *
	 * @since 2.8.0
	 *
	 * @param int $id The post/term ID.
	 * @param bool|object The term object.
	 * @return string The social description excerpt. Unescaped.
	 */
	public function get_description_excerpt_social( $id = 0, $term = false ) {

		$max_char_length = 200;

		$excerpt_social = $this->generate_excerpt( $id, $term, $max_char_length );

		return $excerpt_social;
	}

	/**
	 * Generates the home page description.
	 *
	 * @since 2.6.0
	 *
	 * @param bool $custom_field whether to check the Custom Field.
	 * @param bool $escape Whether to escape the output.
	 * @return string The description.
	 */
	public function generate_home_page_description( $custom_field = true, $escape = true ) {

		$id = $this->get_the_front_page_ID();

		/**
		 * Return early if description is found from Home Page Settings.
		 * Only do so when $args['get_custom_field'] is true.
		 * @since 2.3.4
		 */
		if ( $custom_field ) {
			$description = $this->get_custom_homepage_description( array( 'is_home' => true ) );

			if ( $description ) {

				if ( $escape )
					$description = $this->escape_description( $description );

				return $description;
			}
		}

		$title_on_blogname = $this->generate_description_additions( $id, '', true );

		$title = $title_on_blogname['title'];
		$on = $title_on_blogname['on'];
		$blogname = $title_on_blogname['blogname'];

		if ( $escape ) {
			$title = $this->escape_description( $title );
			$on = $this->escape_description( $on );
			$blogname = $this->escape_description( $blogname );
		}

		return $description = sprintf( '%s %s %s', $title, $on, $blogname );
	}

	/**
	 * Determines whether to add description additions. (╯°□°）╯︵ ┻━┻
	 *
	 * @since 2.6.0
	 * @since 2.7.0 Removed cache.
	 *              Whether an excerpt is available is no longer part of this check.
	 *
	 * @param int $id The current page or post ID.
	 * @param object|string $term The current Term.
	 * @return bool Whether to add description additions.
	 */
	public function add_description_additions( $id = '', $term = '' ) {

		/**
		 * Applies filters the_seo_framework_add_description_additions : {
		 *    @param bool true to add prefix.
		 *    @param int $id The Term object ID or The Page ID.
		 *    @param object $term The Term object.
		 * }
		 * @since 2.6.0
		 */
		$filter = \apply_filters( 'the_seo_framework_add_description_additions', true, $id, $term );
		$option = $this->get_option( 'description_additions' );

		return $option && $filter;
	}

	/**
	 * Gets Description Separator.
	 *
	 * Applies filters 'the_seo_framework_description_separator' : string
	 * @since 2.3.9
	 * @staticvar string $sep
	 *
	 * @return string The Separator, unescaped.
	 */
	public function get_description_separator() {

		static $sep = null;

		if ( isset( $sep ) )
			return $sep;

		return $sep = (string) \apply_filters( 'the_seo_framework_description_separator', $this->get_separator( 'description', false ) );
	}

	/**
	 * Returns translation string for "Title on Blogname".
	 *
	 * @since 2.8.0
	 * @see $this->generate_description_additions()
	 *
	 * @param array $additions The description additions.
	 * @return string The description additions.
	 */
	protected function get_title_on_blogname( $additions ) {

		if ( empty( $additions ) )
			return '';

		$title = $additions['title'];
		$on = $additions['on'];
		$blogname = $additions['blogname'];

		/* translators: 1: Title, 2: on, 3: Blogname */
		return trim( sprintf( \_x( '%1$s %2$s %3$s', '1: Title, 2: on, 3: Blogname', 'autodescription' ), $title, $on, $blogname ) );
	}

	/**
	 * Generates description additions.
	 *
	 * @since 2.6.0
	 * @since 2.9.2 Added filter.
	 * @staticvar array $title string of titles.
	 * @staticvar string $on
	 * @access private
	 *
	 * @param int $id The post or term ID
	 * @param object|empty $term The term object
	 * @param bool $ignore Whether to ignore options and filters.
	 * @return array : {
	 *    $title    => The title
	 *    $on       => The word separator
	 *    $blogname => The blogname
	 *    $sep      => The separator
	 * }
	 */
	public function generate_description_additions( $id = 0, $term = '', $ignore = false ) {

		static $title = array();

		if ( $ignore || $this->add_description_additions( $id, $term ) ) {

			if ( ! isset( $title[ $id ] ) ) {
				$title[ $id ] = $this->generate_description_title( $id, $term, $this->is_real_front_page() );
			}

			if ( $ignore || $this->is_option_checked( 'description_blogname' ) ) {

				static $on = null;
				if ( is_null( $on ) ) {
					/* translators: Front-end output. */
					$on = \_x( 'on', 'Placement. e.g. Post Title "on" Blog Name', 'autodescription' );
				}

				//* Already cached.
				$blogname = $this->get_blogname();
			} else {
				$on = '';
				$blogname = '';
			}

			//* Already cached.
			$sep = $this->get_description_separator();
		} else {
			$title[ $id ] = '';
			$on = '';
			$blogname = '';
			$sep = '';
		}

		if ( \has_filter( 'the_seo_framework_generated_description_additions' ) ) {
			/**
			 * Applies filters 'the_seo_framework_generated_description_additions'
			 *
			 * @since 2.9.2
			 *
			 * @param array $data   The description data.
			 * @param int   $id     The object ID.
			 * @param mixed $term   The term object, or empty (falsy).
			 * @param bool  $ignore Whether the settings have been ignored.
			 */
			$data = \apply_filters_ref_array( 'the_seo_framework_generated_description_additions', array(
				array(
					'title' => $title[ $id ],
					'on' => $on,
					'blogname' => $blogname,
					'sep' => $sep,
				),
				$id,
				$term,
				$ignore,
			) );
		} else {
			$data = array(
				'title' => $title[ $id ],
				'on' => $on,
				'blogname' => $blogname,
				'sep' => $sep,
			);
		}

		return $data;
	}

	/**
	 * Generates the Title for description.
	 *
	 * @since 2.5.2
	 *
	 * @param int $id The page ID.
	 * @param void|object $term The term object.
	 * @param bool $page_on_front If front page.
	 * @return string The description title.
	 */
	public function generate_description_title( $id = '', $term = '', $page_on_front = false ) {

		if ( '' === $id )
			$id = $this->get_the_real_ID();

		if ( $page_on_front || $this->is_front_page_by_id( $id ) ) :
			$title = $this->get_option( 'homepage_title_tagline' ) ?: $this->get_blogdescription();
		else :
			/**
			 * No need to parse these when generating social description.
			 *
			 * @since 2.5.0
			 */
			if ( $this->is_blog_page( $id ) ) {
				/**
				 * We're on the blog page now.
				 * @since 2.2.8
				 */
				$title = $this->title( '', '', '', array( 'term_id' => $id, 'notagline' => true, 'description_title' => true, 'escape' => false ) );

				/**
				 * @TODO create option.
				 * @priority medium 2.8.0+
				 */
				/* translators: Front-end output. */
				$title = \__( 'Latest posts:', 'autodescription' ) . ' ' . $title;
			} elseif ( $term && isset( $term->term_id ) ) {
				//* We're on a taxonomy now.

				$data = $this->get_term_data( $term, $term->term_id );

				if ( ! empty( $data['doctitle'] ) ) {
					$title = $data['doctitle'];
				} elseif ( ! empty( $term->name ) ) {
					$title = $term->name;
				} elseif ( ! empty( $term->slug ) ) {
					$title = $term->slug;
				}
			} else {
				//* We're on a page now.
				$title = $this->title( '', '', '', array( 'term_id' => $id, 'notagline' => true, 'description_title' => true, 'escape' => false ) );
			}
		endif;

		/**
		 * Use Untitled on empty titles.
		 * @since 2.2.8
		 */
		/* translators: Front-end output. */
		$title = empty( $title ) ? $this->untitled() : trim( $title );

		return $title;
	}

	/**
	 * Generates the excerpt.
	 * @NOTE Supply calculated $max_char_length to reflect actual output.
	 *
	 * @since 2.3.4
	 * @since 2.8.2 Now no longer escapes excerpt by accident in processing, preventing "too short" output.
	 * @staticvar array $excerpt_cache Holds the excerpt
	 * @staticvar array $excerptlength_cache Holds the excerpt length
	 *
	 * @param int|string $page_id required : The Page ID
	 * @param object|null $term The Taxonomy Term.
	 * @param int $max_char_length The maximum excerpt char length.
	 * @return string $excerpt The excerpt, not escaped.
	 */
	public function generate_excerpt( $page_id, $term = '', $max_char_length = 155 ) {

		static $excerpt_cache = array();
		static $excerptlength_cache = array();

		$term_id = isset( $term->term_id ) ? $term->term_id : false;

		//* Put excerpt in cache.
		if ( ! isset( $excerpt_cache[ $page_id ][ $term_id ] ) ) {
			if ( $this->is_singular( $page_id ) ) {
				//* We're on the blog page now.
				$excerpt = $this->get_excerpt_by_id( '', $page_id, '', false );
			} elseif ( $term_id ) {
				//* We're on a taxonomy now. Fetch excerpt from latest term post.
				$excerpt = empty( $term->description ) ? $this->get_excerpt_by_id( '', '', $page_id, false ) : $this->s_description_raw( $term->description );
			} elseif ( $this->is_author() ) {
				$excerpt = $this->s_description_raw( \get_the_author_meta( 'description', (int) \get_query_var( 'author' ) ) );
			} else {
				$excerpt = '';
			}

			/**
			 * Applies filters 'the_seo_framework_fetched_description_excerpt' : string
			 *
			 * @since 2.9.0
			 *
			 * @param string $excerpt The excerpt to use.
			 * @param bool $page_id The current page/term ID
			 * @param object|mixed $term The current term.
			 * @param int $max_char_length Determines the maximum length of excerpt after trimming.
			 */
			$excerpt = (string) \apply_filters( 'the_seo_framework_fetched_description_excerpt', $excerpt, $page_id, $term, $max_char_length );

			$excerpt_cache[ $page_id ][ $term_id ] = $excerpt;
		}

		//* Fetch excerpt from cache.
		$excerpt = $excerpt_cache[ $page_id ][ $term_id ];

		/**
		 * Put excerptlength in cache.
		 * Why cache? My tests have shown that mb_strlen is 1.03x faster than cache fetching.
		 * However, _mb_strlen (compat) is about 1740x slower. And this is the reason it's cached!
		 */
		if ( ! isset( $excerptlength_cache[ $page_id ][ $term_id ] ) )
			$excerptlength_cache[ $page_id ][ $term_id ] = mb_strlen( $excerpt );

		//* Fetch the length from cache.
		$excerpt_length = $excerptlength_cache[ $page_id ][ $term_id ];

		//* Trunculate if the excerpt is longer than the max char length
		$excerpt = $this->trim_excerpt( $excerpt, $excerpt_length, $max_char_length );

		return (string) $excerpt;
	}

	/**
	 * Trims the excerpt by word and determines sentence stops.
	 *
	 * @since 2.6.0
	 *
	 * @param string $excerpt The untrimmed excerpt.
	 * @param int $excerpt_length The current excerpt length.
	 * @param int $max_char_length At what point to shave off the excerpt.
	 * @return string The trimmed excerpt.
	 */
	public function trim_excerpt( $excerpt, $excerpt_length, $max_char_length ) {

		if ( $excerpt_length <= $max_char_length )
			return trim( $excerpt );

		//* Cut string to fit $max_char_length.
		$sub_ex = mb_substr( $excerpt, 0, $max_char_length );
		$sub_ex = trim( html_entity_decode( $sub_ex ) );

		//* Split words in array separated by delimiter.
		$ex_words = explode( ' ', $sub_ex );

		//* Count to total words in the excerpt.
		$ex_total = count( $ex_words );

		//* Slice the complete excerpt and count the amount of words.
		$extra_ex_words = explode( ' ', trim( $excerpt ), $ex_total + 1 );
		$extra_ex_total = count( $extra_ex_words ) - 1;
		unset( $extra_ex_words[ $extra_ex_total ] );

		//* Calculate if last word exceeds.
		if ( $extra_ex_total >= $ex_total ) {
			$ex_cut = mb_strlen( $ex_words[ $ex_total - 1 ] );

			if ( $extra_ex_total > $ex_total ) {
				/**
				 * There are more words in the trimmed excerpt than the compared total excerpt.
				 * Remove the exceeding word.
				 */
				$excerpt = mb_substr( $sub_ex, 0, - $ex_cut );
			} else {
				/**
				 * The amount of words are the same in the comparison.
				 * Calculate if the chacterers are exceeding.
				 */
				$ex_extra_cut = mb_strlen( $extra_ex_words[ $extra_ex_total - 1 ] );

				if ( $ex_extra_cut > $ex_cut ) {
					//* Final word is falling off. Remove it.
					$excerpt = mb_substr( $sub_ex, 0, - $ex_cut );
				} else {
					//* We're all good here, continue.
					$excerpt = $sub_ex;
				}
			}
		}

		//* Remove trailing/leading commas and spaces.
		$excerpt = trim( $excerpt, ' ,' );

		//* Fetch last character.
		$last_char = substr( $excerpt, -1 );

		if ( ';' === $last_char ) {
			$excerpt = rtrim( $excerpt, ' ,.?!;' ) . '.';
		} else {
			$stops = array( '.', '?', '!' );
			//* Add three dots if there's no full stop at the end of the excerpt.
			if ( ! in_array( $last_char, $stops, true ) )
				$excerpt .= '...';
		}

		return trim( $excerpt );
	}
}
