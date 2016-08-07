<?php
/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class AutoDescription_Generate_Description
 *
 * Generates Description SEO data based on content.
 *
 * @since 2.6.0
 */
class AutoDescription_Generate_Description extends AutoDescription_Generate {

	/**
	 * Determines whether we're parsing the manual content Excerpt for the automated description.
	 *
	 * @since 2.6.0
	 *
	 * @var bool Using manual excerpt.
	 */
	protected $using_manual_excerpt = false;

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	private function __wakeup() { }

	/**
	 * Handle unapproachable invoked methods.
	 */
	public function __call( $name, $arguments ) {
		parent::__call( $name, $arguments );
	}

	/**
	 * Constructor, loads parent constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Creates description. Base function.
	 *
	 * @since 1.0.0
	 *
	 * @param string $description The optional description to simply parse.
	 * @param array $args description args : {
	 * 		@param int $id the term or page id.
	 * 		@param string $taxonomy taxonomy name.
	 * 		@param bool $is_home We're generating for the home page.
	 * 		@param bool $get_custom_field Do not fetch custom title when false.
	 * 		@param bool $social Generate Social Description when true.
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
			$description = (string) $this->description_from_custom_field( $args, false );

			//* We've already checked the custom fields, so let's remove the check in the generation.
			$args['get_custom_field'] = false;
		}

		//* Still no description found? Create an auto description based on content.
		if ( empty( $description ) || ! is_scalar( $description ) )
			$description = $this->generate_description_from_id( $args, false );

		/**
		 * Applies filters 'the_seo_framework_do_shortcodes_in_description' : Boolean
		 * @since 2.6.6
		 */
		if ( apply_filters( 'the_seo_framework_do_shortcodes_in_description', false ) )
			$description = do_shortcode( $description );

		/**
		 * Sanitize.
		 * @since 2.3.4 Beautifies too.
		 */
		$description = $this->escape_description( $description );

		return $description;
	}

	/**
	 * Escapes and beautifies description.
	 *
	 * @since 2.5.2
	 *
	 * @param string $description The description to escape and beautify.
	 * @return string Escaped and beautified description.
	 */
	public function escape_description( $description = '' ) {

		$description = wptexturize( $description );
		$description = convert_chars( $description );
		$description = esc_html( $description );
		$description = capital_P_dangit( $description );
		$description = trim( $description );

		return $description;
	}

	/**
	 * Parses and sanitizes description arguments.
	 *
	 * @since 2.5.0
	 *
	 * @applies filters the_seo_framework_description_args : {
	 * 		@param int $id the term or page id.
	 * 		@param string $taxonomy taxonomy name.
	 * 		@param bool $is_home We're generating for the home page.
	 * 		@param bool $get_custom_field Do not fetch custom title when false.
	 * 		@param bool $social Generate Social Description when true.
	 * }
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
				'id' 				=> $this->get_the_real_ID(),
				'taxonomy'			=> '',
				'is_home'			=> false,
				'get_custom_field' 	=> true,
				'social' 			=> false,
			);

			$defaults = (array) apply_filters( 'the_seo_framework_description_args', $defaults, $args );
		}

		//* Return early if it's only a default args request.
		if ( $get_defaults )
			return $defaults;

		//* Array merge doesn't support sanitation. We're simply type casting here.
		$args['id'] 				= isset( $args['id'] ) 					? (int) $args['id'] 				: $defaults['id'];
		$args['taxonomy'] 			= isset( $args['taxonomy'] ) 			? (string) $args['taxonomy'] 		: $defaults['taxonomy'];
		$args['is_home'] 			= isset( $args['is_home'] ) 			? (bool) $args['is_home'] 			: $defaults['is_home'];
		$args['get_custom_field'] 	= isset( $args['get_custom_field'] ) 	? (bool) $args['get_custom_field'] 	: $defaults['get_custom_field'];
		$args['social'] 			= isset( $args['social'] ) 				? (bool) $args['social'] 			: $defaults['social'];

		return $args;
	}

	/**
	 * Reparses description args.
	 *
	 * @param array $args required The passed arguments.
	 *
	 * @since 2.6.0
	 * @return array $args parsed args.
	 */
	public function reparse_description_args( $args = array() ) {

		$default_args = $this->parse_description_args( '', '', true );

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
	 * 		@param int $id the term or page id.
	 * 		@param string $taxonomy taxonomy name.
	 * 		@param bool $is_home We're generating for the home page.
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
	 * @access protected
	 * Use $this->description_from_custom_field() instead.
	 *
	 * @param array $args Description args.
	 * @return string The Description
	 */
	protected function get_custom_homepage_description( $args ) {

		$description = '';

		if ( $args['is_home'] || $this->is_front_page() || ( empty( $args['taxonomy'] ) && $this->is_static_frontpage( $args['id'] ) ) ) {
			$homedesc = $this->get_option( 'homepage_description' );
			$description = $homedesc ? $homedesc : '';
		}

		return $description;
	}

	/**
	 * Fetches Singular Description from custom field.
	 *
	 * @since 2.6.0
	 * @access protected
	 * Use $this->description_from_custom_field() instead.
	 *
	 * @param int $id The page ID.
	 * @return string The Description
	 */
	protected function get_custom_singular_description( $id ) {

		$description = '';

		if ( $this->is_singular( $id ) ) {
			$custom_desc = $this->get_custom_field( '_genesis_description', $id );
			$description = $custom_desc ? $custom_desc : $description;
		}

		return $description;
	}

	/**
	 * Fetch Archive Description from custom field.
	 *
	 * @since 2.6.0
	 * @access protected
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
	 * 		@param int $id the term or page id.
	 * 		@param string $taxonomy taxonomy name.
	 * 		@param bool $is_home We're generating for the home page.
	 * 		@param bool $get_custom_field Do not fetch custom title when false.
	 * 		@param bool $social Generate Social Description when true.
	 * }
	 * @param bool $escape Escape output when true.
	 * @return string $output The description.
	 */
	public function generate_description_from_id( $args = array(), $escape = true ) {

		if ( $this->the_seo_framework_debug ) $this->debug_init( __METHOD__, true, $debug_key = microtime( true ), get_defined_vars() );

		/**
		 * Applies filters bool 'the_seo_framework_enable_auto_description' : Enable or disable the description.
		 *
		 * @since 2.5.0
		 */
		$autodescription = (bool) apply_filters( 'the_seo_framework_enable_auto_description', true );
		if ( false === $autodescription )
			return '';

		$description = $this->generate_the_description( $args, false );

		if ( $escape )
			$description = $this->escape_description( $description );

		if ( $this->the_seo_framework_debug ) $this->debug_init( __METHOD__, false, $debug_key, array( 'description' => $description, 'transient_key' => $this->auto_description_transient ) );

		return (string) $description;
	}

	/**
	 * Generates description from content.
	 *
	 * @since 2.6.0
	 * @staticvar string $title
	 *
	 * @param array $args description args : {
	 * 		@param int $id the term or page id.
	 * 		@param string $taxonomy taxonomy name.
	 * 		@param bool $is_home We're generating for the home page.
	 * 		@param bool $get_custom_field Do not fetch custom title when false.
	 * 		@param bool $social Generate Social Description when true.
	 * }
	 * @param bool $escape Whether to escape the description.
	 * @return string The description.
	 */
	protected function generate_the_description( $args, $escape = true ) {

		/**
		 * Parse args.
		 * @since 2.5.0
		 */
		$args = $this->reparse_description_args( $args );

		//* Home Page description
		if ( $args['is_home'] || $this->is_front_page() || $this->is_static_frontpage( $args['id'] ) )
			return $this->generate_home_page_description( $args['get_custom_field'], $escape );

		$term = $this->fetch_the_term( $args['id'] );

		//* Whether the post ID has a manual excerpt.
		if ( empty( $term ) && has_excerpt( $args['id'] ) )
			$this->using_manual_excerpt = true;

		$title_on_blogname = $this->generate_description_additions( $args['id'], $term, false );
		$title = $title_on_blogname['title'];
		$on = $title_on_blogname['on'];
		$blogname = $title_on_blogname['blogname'];
		$sep = $title_on_blogname['sep'];

		/**
		 * Setup transient.
		 */
		$this->setup_auto_description_transient( $args['id'], $args['taxonomy'] );

		/**
		 * Cache the generated description within a transient.
		 * @since 2.3.3
		 * @since 2.3.4 Put inside a different function.
		 */
		$excerpt = $this->get_transient( $this->auto_description_transient );
		if ( false === $excerpt ) {

			/**
			 * Get max char length.
			 * Default to 200 when $args['social'] as there are no additions.
			 */
			$additions = trim( $title . " $on " . $blogname );
			//* If there are additions, add a trailing space.
			if ( $additions )
				$additions .= ' ';

			$max_char_length_normal = 155 - mb_strlen( html_entity_decode( $additions ) );
			$max_char_length_social = 200;

			//* Generate Excerpts.
			$excerpt_normal = $this->generate_excerpt( $args['id'], $term, $max_char_length_normal );
			$excerpt_social = $this->generate_excerpt( $args['id'], $term, $max_char_length_social );

			//* Put in array to be accessed later.
			$excerpt = array(
				'normal' => $excerpt_normal,
				'social' => $excerpt_social,
			);

			/**
			 * Transient expiration: 1 week.
			 * Keep the description for at most 1 week.
			 */
			$expiration = WEEK_IN_SECONDS;

			$this->set_transient( $this->auto_description_transient, $excerpt, $expiration );
		}

		/**
		 * Check for Social description, don't add blogname then.
		 * Also continues normally if it's the front page.
		 *
		 * @since 2.5.0
		 */
		if ( $args['social'] ) {
			if ( $excerpt['social'] ) {
				$description = $excerpt['social'];
			} else {
				//* No social description if nothing is found.
				$description = '';
			}
		} else {

			if ( empty( $excerpt['normal'] ) ) {
				//* Fetch additions ignoring options.

				$title_on_blogname = $this->generate_description_additions( $args['id'], $term, true );
				$title = $title_on_blogname['title'];
				$on = $title_on_blogname['on'];
				$blogname = $title_on_blogname['blogname'];
				$sep = $title_on_blogname['sep'];
			}

			/* translators: 1: Title, 2: on, 3: Blogname */
			$title_on_blogname = trim( sprintf( __( '%1$s %2$s %3$s', 'autodescription' ), $title, $on, $blogname ) );

			if ( $excerpt['normal'] ) {
				/* translators: 1: Title on Blogname, 2: Separator, 3: Excerpt */
				$description = sprintf( __( '%1$s %2$s %3$s', 'autodescription' ), $title_on_blogname, $sep, $excerpt['normal'] );
			} else {
				//* We still add the additions when no excerpt has been found.
				// i.e. home page or empty/shortcode filled page.
				$description = $title_on_blogname;
			}
		}

		if ( $escape )
			$description = $this->escape_description( $description );

		return $description;
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
	 * @staticvar bool $cache
	 *
	 * @param int $id The current page or post ID.
	 * @param object|emptystring $term The current Term.
	 * @return bool Whether to add description additions.
	 */
	public function add_description_additions( $id = '', $term = '' ) {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		/**
		 * Applies filters the_seo_framework_add_description_additions : {
		 *		@param bool true to add prefix.
		 * 		@param int $id The Term object ID or The Page ID.
		 * 		@param object $term The Term object.
		 *	}
		 *
		 * @since 2.6.0
		 */
		$filter = (bool) apply_filters( 'the_seo_framework_add_description_additions', true, $id, $term );
		$option = (bool) $this->get_option( 'description_additions' );
		$excerpt = ! $this->using_manual_excerpt;

		return $cache = $option && $filter && $excerpt;
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

		return $sep = (string) apply_filters( 'the_seo_framework_description_separator', $this->get_separator( 'description', false ) );
	}

	/**
	 * Generates description additions.
	 *
	 * @since 2.6.0
	 * @staticvar array $title string of titles.
	 * @staticvar string $on
	 * @access private
	 *
	 * @param int $id The post or term ID
	 * @param object|empty $term The term object
	 * @param bool $ignore Whether to ignore options and filters.
	 * @return array : {
	 *		$title		=> The title
	 *		$on 		=> The word separator
	 *		$blogname	=> The blogname
	 *		$sep		=> The separator
	 * }
	 */
	public function generate_description_additions( $id = '', $term = '', $ignore = false ) {

		static $title = array();

		if ( $ignore || $this->add_description_additions( $id, $term ) ) {

			if ( ! isset( $title[ $id ] ) )
				$title[ $id ] = $this->generate_description_title( $id, $term, $ignore );

			if ( $ignore || $this->is_option_checked( 'description_blogname' ) ) {

				static $on = null;
				if ( is_null( $on ) ) {
					/* translators: Front-end output. */
					$on = _x( 'on', 'Placement. e.g. Post Title "on" Blog Name', 'autodescription' );
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

		return array(
			'title' => $title[ $id ],
			'on' => $on,
			'blogname' => $blogname,
			'sep' => $sep,
		);
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

		if ( $page_on_front || $this->is_static_frontpage( $id ) ) {
			$tagline = $this->get_option( 'homepage_title_tagline' );
			$title = $tagline ? $tagline : $this->get_blogdescription();
		} else {
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
				$title = __( 'Latest posts:', 'autodescription' ) . ' ' . $title;
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
		}

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
	 * @staticvar array $excerpt_cache Holds the excerpt
	 * @staticvar array $excerptlength_cache Holds the excerpt length
	 *
	 * @param int|string $page_id required : The Page ID
	 * @param object|null $term The Taxonomy Term.
	 * @param int $max_char_length The maximum excerpt char length.
	 */
	public function generate_excerpt( $page_id, $term = '', $max_char_length = 154 ) {

		static $excerpt_cache = array();
		static $excerptlength_cache = array();

		$term_id = isset( $term->term_id ) ? $term->term_id : false;

		//* Put excerpt in cache.
		if ( ! isset( $excerpt_cache[ $page_id ][ $term_id ] ) ) {
			if ( $this->is_singular( $page_id ) ) {
				//* We're on the blog page now.
				$excerpt = $this->get_excerpt_by_id( '', $page_id );
			} elseif ( $term_id ) {
				//* We're on a taxonomy now.
				$excerpt = empty( $term->description ) ? $this->get_excerpt_by_id( '', '', $page_id ) : $this->s_description( $term->description );
			} elseif ( $this->is_author() ) {
				$excerpt = $this->s_description( get_the_author_meta( 'description', (int) get_query_var( 'author' ) ) );
			} else {
				$excerpt = '';
			}

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

		if ( $excerpt_length > $max_char_length ) {

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

			//* Remove trailing/leading comma's and spaces.
			$excerpt = trim( $excerpt, ' ,' );

			//* Fetch last character.
			$last_char = substr( $excerpt, -1 );

			$stops = array( '.', '?', '!' );
			//* Add three dots if there's no full stop at the end of the excerpt.
			if ( ! in_array( $last_char, $stops, true ) )
				$excerpt .= '...';

		}

		return trim( $excerpt );
	}
}
