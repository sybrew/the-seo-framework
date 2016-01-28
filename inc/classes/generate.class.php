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

/**
 * Class AutoDescription_Generate
 *
 * Generates SEO data based on content
 * Returns strings/arrays
 *
 * @since 2.1.6
 */
class AutoDescription_Generate extends AutoDescription_PostData {

	/**
	 * Constructor, load parent constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Create description
	 *
	 * @param string $description the description.
	 * @param array $args description args : {
	 * 		@param int $id the term or page id.
	 * 		@param string $taxonomy taxonomy name.
	 * 		@param bool $is_home We're generating for the home page.
	 * 		@param bool $get_custom_field Do not fetch custom title when false.
	 * 		@param bool $social Generate Social Description when true.
	 * }
	 *
	 * @since 1.0.0
	 *
	 * @return string The description
	 */
	 public function generate_description( $description = '', $args = array() ) {

		$default_args = $this->parse_description_args( '', '', true );

		/**
		 * Parse args.
		 * @since 2.5.0
		 */
		if ( ! is_array( $args ) ) {
			//* Old style parameters are used. Doing it wrong.
			_doing_it_wrong( __CLASS__ . '::' . __FUNCTION__, 'Use $args = array() for parameters.', $this->the_seo_framework_version( '2.5.0' ) );
			$args = $default_args;
		} else if ( ! empty( $args ) ) {
			$args = $this->parse_description_args( $args, $default_args );
		} else {
			$args = $default_args;
		}

		if ( $args['get_custom_field'] && empty( $description ) ) {
			//* Fetch from options, if any.
			$description = (string) $this->description_from_custom_field( $args, false );

			//* We've already checked the custom fields, so let's remove the check in the generation.
			$args['get_custom_field'] = false;
		}

		//* Still no description found? Create an auto description based on content.
		if ( empty( $description ) || ! is_string( $description ) )
			$description = $this->generate_description_from_id( $args, false );

		/**
		 * Beautify.
		 * @since 2.3.4
		 */
		$description = wptexturize( $description );
		$description = convert_chars( $description );
		$description = esc_html( $description );
		$description = capital_P_dangit( $description );
		$description = trim( $description );

		return $description;
	}

	/**
	 * Parse and sanitize description args.
	 *
	 * @param array $args required The passed arguments.
	 * @param array $defaults The default arguments.
	 * @param bool $get_defaults Return the default arguments. Ignoring $args.
	 *
	 * @applies filters the_seo_framework_description_args : {
	 * 		@param int $id the term or page id.
	 * 		@param string $taxonomy taxonomy name.
	 * 		@param bool $is_home We're generating for the home page.
	 * 		@param bool $get_custom_field Do not fetch custom title when false.
	 * 		@param bool $social Generate Social Description when true.
	 * }
	 *
	 * @since 2.5.0
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
	 * Create description
	 *
	 * @param array $args description args : {
	 * 		@param int $id the term or page id.
	 * 		@param string $taxonomy taxonomy name.
	 * 		@param bool $is_home We're generating for the home page.
	 * }
	 * @param bool $escape Escape the output if true.
	 *
	 * @since 2.4.1
	 *
	 * @return string|mixed The description, might be unsafe for html output.
	 */
	public function description_from_custom_field( $args = array(), $escape = true ) {
		global $wp_query;

		$default_args = $this->parse_description_args( '', '', true );

		/**
		 * Parse args.
		 * @since 2.5.0
		 */
		if ( ! is_array( $args ) ) {
			//* Old style parameters are used. Doing it wrong.
			_doing_it_wrong( __CLASS__ . '::' . __FUNCTION__, 'Use $args = array() for parameters.', $this->the_seo_framework_version( '2.5.0' ) );
			$args = $default_args;
		} else if ( ! empty( $args ) ) {
			$args = $this->parse_description_args( $args, $default_args );
		} else {
			$args = $default_args;
		}

		$description = '';

		if ( $args['is_home'] || is_front_page() || ( '' === $args['taxonomy'] && $this->is_static_frontpage( $args['id'] ) ) ) {
			$custom_desc = $this->get_option( 'homepage_description' );
			$description = ! empty( $custom_desc ) ? $custom_desc : $description;
		}

		if ( '' === $description && $this->is_singular( $args['id'] ) ) {
			//* Bugfix 2.2.7 run only if description is stil empty from home page.
			$custom_desc = $this->get_custom_field( '_genesis_description', $args['id'] );
			$description = $custom_desc ? $custom_desc : $description;
		}

		if ( is_category() ) {
			$term = $wp_query->get_queried_object();

			$description = ! empty( $term->admeta['description'] ) ? $term->admeta['description'] : $description;

			$flag = $term->admeta['saved_flag'] != '0' ? true : false;

			if ( ! $flag && empty( $description ) && isset( $term->meta['description'] ) )
				$description = ! empty( $term->meta['description'] ) ? $term->meta['description'] : $description;
		}

		if ( is_tag() ) {
			$term = $wp_query->get_queried_object();

			$description = ! empty( $term->admeta['description'] ) ? $term->admeta['description'] : $description;

			$flag = $term->admeta['saved_flag'] != '0' ? true : false;

			if ( ! $flag && empty( $description ) && isset( $term->meta['description'] ) )
				$description = ! empty( $term->meta['description'] ) ? $term->meta['description'] : $description;
		}

		if ( is_tax() ) {
			$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
			$description = ! empty( $term->admeta['description'] ) ? wp_kses_stripslashes( wp_kses_decode_entities( $term->admeta['description'] ) ) : $description;

			$flag = $term->admeta['saved_flag'] != '0' ? true : false;

			if ( ! $flag && empty( $description ) && isset( $term->meta['description'] ) )
				$description = ! empty( $term->meta['description'] ) ? $term->meta['description'] : $description;
		}

		if ( is_author() ) {
			$user_description = get_the_author_meta( 'meta_description', (int) get_query_var( 'author' ) );

			$description = $user_description ? $user_description : $description;
		}

		if ( $escape ) {
			$description = wptexturize( $description );
			$description = convert_chars( $description );
			$description = esc_html( $description );
			$description = capital_P_dangit( $description );
			$description = trim( $description );
		}

		return $description;
	}

	/**
	 * Generate description from content
	 *
	 * @since 1.0.0
	 *
	 * @param array $args description args : {
	 * 		@param int $id the term or page id.
	 * 		@param string $taxonomy taxonomy name.
	 * 		@param bool $is_home We're generating for the home page.
	 * 		@param bool $get_custom_field Do not fetch custom title when false.
	 * 		@param bool $social Generate Social Description when true.
	 * }
	 * @param bool $escape Escape output when true.
	 * @param bool $_escape deprecated.
	 *
	 * Gained its own function.
	 * @since 2.3.3
	 *
	 * @return string output The description.
	 */
	public function generate_description_from_id( $args = array(), $escape = true, $_escape = 'depr' ) {

		//* @TODO remove @since 2.6.0
		if ( 'depr' !== $_escape ) {
			_deprecated_argument( __FUNCTION__, $this->the_seo_framework_version( '2.5.2' ), 'Use 2nd argument for escape.' );
			$escape = (bool) $_escape;
		}

		/**
		 * Applies filters bool 'the_seo_framework_enable_auto_description' : Enable or disable the description.
		 *
		 * @since 2.5.0
		 */
		$autodescription = (bool) apply_filters( 'the_seo_framework_enable_auto_description', true );

		if ( ! $autodescription )
			return '';

		$default_args = $this->parse_description_args( '', '', true );

		/**
		 * Parse args.
		 * @since 2.5.0
		 */
		if ( ! is_array( $args ) ) {
			//* Old style parameters are used. Doing it wrong.
			_doing_it_wrong( __CLASS__ . '::' . __FUNCTION__, 'Use $args = array() for parameters.', $this->the_seo_framework_version( '2.5.0' ) );
			$args = $default_args;
		} else if ( ! empty( $args ) ) {
			$args = $this->parse_description_args( $args, $default_args );
		} else {
			$args = $default_args;
		}

		/**
		 * Debug parameters.
		 * @since 2.3.4
		 */
		if ( $this->the_seo_framework_debug ) {
			if ( $this->the_seo_framework_debug_hidden )
				echo "<!--\r\n";

			echo "\r\n<br>\r\n" . 'START: ' . __CLASS__ . '::' . __FUNCTION__ .  "\r\n";

			$timer_start = microtime( true );

			if ( $this->the_seo_framework_debug_more ) {
				$this->echo_debug_information( array( 'args' => $args ) );
			}

			echo "\r\n<br>\r\n";

			if ( $this->the_seo_framework_debug_hidden )
				echo "\r\n-->";
		}

		$term = '';
		if ( ! empty( $args['taxonomy'] ) && false !== $args['id'] ) {
			//* Fetch taxonomy from args.
			//* This only runs in admin, because we provide these arg values there.
			$term = get_term_by( 'id', $args['id'], $args['taxonomy'], OBJECT );
		} else if ( is_admin() ) {
			//* Test other admin screens.
			global $current_screen;

			if ( isset( $current_screen->taxonomy ) && ! empty( $current_screen->taxonomy ) ) {
				//* Fetch taxonomy in admin.
				$args['taxonomy'] = $current_screen->taxonomy;
				$term = get_term_by( 'id', $args['id'], $args['taxonomy'], OBJECT );
			}
		} else if ( is_archive() && ! is_front_page() && ! $this->is_singular( $args['id'] ) ) {
			//* Fetch Taxonomy through wp_query on front-end
			global $wp_query;

			$term = $wp_query->get_queried_object();
			$args['taxonomy'] = isset( $term->taxonomy ) ? $term->taxonomy : '';
		}

		$page_on_front = false;
		/**
		 * We're on the home page now. So let's create something special.
		 * Check if ID is false means its a blog page as home.
		 */
		if ( is_front_page() || $args['is_home'] || $this->is_static_frontpage( $args['id'] ) ) {
			$page_on_front = true;
			$args['id'] = (int) get_option( 'page_on_front' );

			/**
			 * Return early if description is found from Home Page Settings.
			 * Only do so when $args['get_custom_field'] is true.
			 *
			 * @since 2.3.4
			 */
			if ( $args['get_custom_field'] ) {
				$custom_desc = $this->get_option( 'homepage_description' );
				$description = $custom_desc ? $custom_desc : null;

				if ( isset( $description ) )
					return $description;
			}
		}

		//* Fetch Description Title.
		$title = $this->generate_description_title( $args['id'], $term, $page_on_front );
		/* translators: Front-end output. */
		$on = _x( 'on', 'Placement. e.g. Post Title "on" Blog Name', 'autodescription' );
		$blogname = $this->get_blogname();

		if ( ! $page_on_front ) {

			$description_additions = $this->get_option( 'description_blogname' );

			/**
			 * Now uses options.
			 * @since 2.3.4
			 *
			 * Applies filters the_seo_framework_description_separator
			 * @since 2.3.9
			 */
			$sep = (string) apply_filters( 'the_seo_framework_description_separator', $this->get_separator( 'description' ) );

			/**
			 * Setup transient.
			 */
			$this->setup_auto_description_transient( $args['id'], $args['taxonomy'] );

			/**
			 * Cache the generated description within a transient.
			 *
			 * @since 2.3.3
			 *
			 * Put inside a different function.
			 * @since 2.3.4
			 */
			$excerpt = get_transient( $this->auto_description_transient );
			if ( false === $excerpt ) {

				/**
				 * Get max char length
				 * 149 will account for the added (single char) ... and two spaces around $on and the separator + 2 spaces around the separator: makes 155
				 *
				 * 151 will count for the added (single char) ... and the separator + 2 spaces around the separator: makes 155
				 *
				 * Default to 200 when $args['social']
				 */
				$max_char_length_normal = $description_additions ? (int) 149 - mb_strlen( html_entity_decode( $title . $on . $blogname ) ) : (int) 151 - mb_strlen( html_entity_decode( $title ) );
				$max_char_length_social = 200;

				//* Generate Excerpts.
				$excerpt_normal = $this->generate_excerpt( $args['id'], $term, $max_char_length_normal );
				$excerpt_social = $this->generate_excerpt( $args['id'], $term, $max_char_length_social );

				//* Put in array to be accessed later.
				$excerpt = array(
					'normal' => $excerpt_normal,
					'social' => $excerpt_social
				);

				/**
				 * Transient expiration: 1 week.
				 * Keep the description for at most 1 week.
				 *
				 * 60s * 60m * 24h * 7d
				 */
				$expiration = 60 * 60 * 24 * 7;

				set_transient( $this->auto_description_transient, $excerpt, $expiration );
			}

			/**
			 * Check for Social description, don't add blogname then.
			 * Also continues normally if it's the front page.
			 *
			 * @since 2.5.0
			 */
			if ( $args['social'] ) {
				/**
				 * @since 2.5.2
				 */
				$excerpt_exists = ! empty( $excerpt['social'] ) ? true : false;

				if ( $excerpt_exists && $description_additions ) {
					$description = $excerpt['social'];
				} else {
					$description = (string) sprintf( '%s %s %s', $title, $on, $blogname );
				}
			} else {
				$excerpt_exists = ! empty( $excerpt['normal'] ) ? true : false;

				if ( $excerpt_exists && $description_additions ) {
					$description = (string) sprintf( '%s %s %s %s %s', $title, $on, $blogname, $sep, $excerpt['normal'] );
				} else if ( $excerpt_exists ) {
					$description = (string) sprintf( '%s %s %s', $title, $sep, $excerpt['normal'] );
				} else {
					//* We still add the additions when no excerpt has been found.
					// i.e. home page or empty/shortcode filled page.
					$description = (string) sprintf( '%s %s %s', $title, $on, $blogname );
				}
			}
		} else {
			//* Home page Description.
			$description = (string) sprintf( '%s %s %s', $title, $on, $blogname );
		}

		if ( $escape ) {
			$description = wptexturize( $description );
			$description = convert_chars( $description );
			$description = esc_html( $description );
			$description = capital_P_dangit( $description );
			$description = trim( $description );
		}

		/**
		 * Debug cache key and output.
		 * @since 2.3.4
		 */
		if ( $this->the_seo_framework_debug ) {

			if ( $this->the_seo_framework_debug_hidden )
				echo "<!--\r\n";

			$excerpt = $page_on_front ? 'Front page has no excerpt.' : $excerpt;

			$this->echo_debug_information( array( 'description excerpt cache key' => $this->auto_description_transient ) );
			$this->echo_debug_information( array( 'page on front' => $page_on_front ) );
			$this->echo_debug_information( array( 'is static frontpage' => $this->is_static_frontpage( $args['id'] ) ) );
			$this->echo_debug_information( array( 'description excerpt' => $excerpt ) );
			$this->echo_debug_information( array( 'Generation time' => number_format( microtime(true) - $timer_start, 5 ) . 's' ) );

			echo "\r\n<br>\r\n" . 'END: ' . __CLASS__ . '::' . __FUNCTION__ .  "\r\n<br><br>";

			if ( $this->the_seo_framework_debug_hidden )
				echo "\r\n-->";
		}

		return (string) $description;
	}

	/**
	 * Generates the Title for description.
	 *
	 * @param int $id The page ID.
	 * @param void|object $term The term object.
	 * @param bool $page_on_front If front page.
	 *
	 * @since 2.5.2
	 *
	 * @return string The description title.
	 */
	public function generate_description_title( $id = '',  $term = '', $page_on_front = false ) {

		if ( '' === $id )
			$id = $this->get_the_real_ID();

		if ( ! $page_on_front ) {
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
				$custom_title = $this->get_custom_field( '_genesis_title', $id );
				$title = $custom_title ? $custom_title : $this->title( '', '', '', array( 'term_id' => $id, 'placeholder' => true, 'notagline' => true, 'description_title' => true, 'escape' => false ) );

				// @TODO create option.
				/* translators: Front-end output. */
				$title = __( 'Latest posts:', 'autodescription' ) . ' ' . $title;
			} else if ( ! empty( $term ) && is_object( $term ) ) {
				//* We're on a taxonomy now.

				if ( isset( $term->admeta['doctitle'] ) && ! empty( $term->admeta['doctitle'] ) ) {
					$title = $term->admeta['doctitle'];
				} else if ( isset( $term->name ) && ! empty( $term->name ) ) {
					$title = $term->name;
				} else if ( isset( $term->slug ) && ! empty( $term->slug ) ) {
					$title = $term->slug;
				}
			} else {
				//* We're on a page now.
				$custom_title = $this->get_custom_field( '_genesis_title', $id );
				$title = $custom_title ? $custom_title : $this->title( '', '', '', array( 'term_id' => $id, 'placeholder' => true, 'notagline' => true, 'description_title' => true, 'escape' => false ) );
			}
		} else {
			$title = $this->get_blogdescription();
		}

		/**
		 * Use Untitled on empty titles.
		 * @since 2.2.8
		 */
		/* translators: Front-end output. */
		$title = empty( $title ) ? __( 'Untitled', 'autodescription' ) : trim( $title );

		return $title;
	}

	/**
	 * Generate the excerpt.
	 *
	 * @param int|string $page_id required : The Page ID
	 * @param object|null $term The Taxonomy Term.
	 * @param int $max_char_length The maximum excerpt char length.
	 * @param int $_max_char_length deprecated.
	 * @param int $__max_char_length deprecated.
	 *
	 * @since 2.3.4
	 *
	 * @staticvar array $excerpt_cache Holds the excerpt
	 * @staticvar array $excerptlength_cache Holds the excerpt length
	 *
	 * Please note that this does not reflect the actual output becaue the $max_char_length isn't calculated on direct call.
	 */
	public function generate_excerpt( $page_id, $term = '', $max_char_length = 155, $_max_char_length = 'depr', $__max_char_length = 'depr' ) {

		//* @TODO remove @since 2.6.0
		if ( 'depr' !== $__max_char_length ) {
			_deprecated_argument( __FUNCTION__, $this->the_seo_framework_version( '2.5.2' ), 'Use 3nd argument for max_car_length.' );
			$max_char_length = (int) $__max_char_length;
		}

		//* @TODO remove @since 2.6.0
		if ( 'depr' !== $_max_char_length ) {
			_deprecated_argument( __FUNCTION__, $this->the_seo_framework_version( '2.5.2' ), 'Removed last 2 arguments.' );
		}

		static $excerpt_cache = array();
		static $excerptlength_cache = array();

		$term_id = isset( $term->term_id ) ? $term->term_id : false;

		//* Put excerpt in cache.
		if ( ! isset( $excerpt_cache[$page_id][$term_id] ) ) {
			if ( $this->is_singular( $page_id ) ) {
				//* We're on the blog page now.
				$excerpt = $this->get_excerpt_by_id( '', $page_id );
			} else if ( ! empty( $term ) && is_object( $term ) ) {
				//* We're on a taxonomy now.
				$excerpt = ! empty( $term->description ) ? $term->description : $this->get_excerpt_by_id( '', '', $page_id );
			} else {
				$excerpt = '';
			}

			$excerpt_cache[$page_id][$term_id] = $excerpt;
		}

		//* Fetch excerpt from cache.
		$excerpt = $excerpt_cache[$page_id][$term_id];

		/**
		 * Put excerptlength in cache.
		 * Why cache? My tests have shown that mb_strlen is 1.03x faster than cache fetching.
		 * However, _mb_strlen (compat) is about 1740x slower. And this is the reason it's cached!
		 */
		if ( ! isset( $excerptlength_cache[$page_id][$term_id] ) )
			$excerptlength_cache[$page_id][$term_id] = (int) mb_strlen( $excerpt );

		//* Fetch the length from cache.
		$excerptlength = $excerptlength_cache[$page_id][$term_id];

		// Trunculate if the excerpt is longer than the max char length
		if ( $excerptlength > $max_char_length ) {

			// Cut string to fit $max_char_length.
			$subex = mb_substr( $excerpt, 0, $max_char_length );
			// Split words in array. Boom.
			$exwords = explode( ' ', $subex );
			// Calculate if last word exceeds.
			$excut = - ( mb_strlen( $exwords[ count( $exwords ) - (int) 1 ] ) );

			if ( $excut < (int) 0 ) {
				//* Cut out exceeding word.
				$excerpt = mb_substr( $subex, 0, $excut );
			} else {
				// We're all good here, continue.
				$excerpt = $subex;
			}

			$excerpt = rtrim( $excerpt ) . '...';
		}

		return (string) $excerpt;
	}

	/**
	 * Get the title. God function.
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
	 * 		@param int term_id The Taxonomy Term ID when taxonomy is also filled in. Else post ID.
	 * 		@param string taxonomy The Taxonomy name.
	 * 		@param bool page_on_front Page on front condition for example generation.
	 * 		@param bool placeholder Generate placeholder, ignoring options.
	 * 		@param bool notagline Generate title without tagline.
	 * 		@param bool meta Ignore doing_it_wrong. Used in og:title/twitter:title
	 * 		@param bool get_custom_field Do not fetch custom title when false.
	 * 		@param bool description_title Fetch title for description.
	 * 		@param bool is_front_page Fetch front page title.
	 * }
	 *
	 * @return string $title Title
	 */
	public function title( $title = '', $sep = '', $seplocation = '', $args = array() ) {

		//* Use WordPress default feed title.
		if ( is_feed() )
			return trim( $title );

		/**
		 * Debug parameters.
		 * @since 2.3.4
		 */
		if ( $this->the_seo_framework_debug ) {

			if ( $this->the_seo_framework_debug_hidden )
				echo "<!--\r\n";

			echo  "\r\n" . 'START: ' . __CLASS__ . '::' . __FUNCTION__ .  "\r\n";

			if ( $this->the_seo_framework_debug_more ) {
				$this->echo_debug_information( array( 'title' => $title ) );
				$this->echo_debug_information( array( 'sep' => $sep ) );
				$this->echo_debug_information( array( 'seplocation' => $seplocation ) );
				$this->echo_debug_information( array( 'args' => $args ) );
			}

			if ( $this->the_seo_framework_debug_hidden )
				echo "\r\n-->";
		}

		$default_args = $this->parse_title_args( '', '', true );

		/**
		 * Parse args.
		 * @since 2.4.0
		 */
		if ( ! is_array( $args ) ) {
			//* Old style parameters are used. Doing it wrong.
			_doing_it_wrong( __CLASS__ . '::' . __FUNCTION__, 'Use $args = array() for parameters.', $this->the_seo_framework_version( '2.4.0' ) );
			$args = $default_args;
		} else if ( ! empty( $args ) ) {
			$args = $this->parse_title_args( $args, $default_args );
		} else {
			$args = $default_args;
		}

		/**
		 * Return early if the request is the Title only (without tagline/blogname).
		 * Admin only.
		 */
		if ( $args['notagline'] && is_admin() )
			return $this->build_title_notagline( $title, $args );

		/**
		 * Add doing it wrong notice for better SEO consistency.
		 * Only when in wp_title.
		 *
		 * @since 2.2.5
		 */
		if ( ! $args['meta'] ) {
			if ( ! $this->detect_theme_support( 'title-tag' ) && doing_filter( 'wp_title' ) ) {
				if ( ! empty( $seplocation ) ) {
					// Don't disturb the precious title when WP_DEBUG is on.
					add_action( 'wp_footer', array( $this, 'title_doing_it_wrong' ), 20 );

					//* Notify cache.
					$this->title_doing_it_wrong = true;

					//* Notify transients
					$this->set_theme_dir_transient( false );

					return $this->build_title_doingitwrong( $title, $sep, $seplocation, $args );
				} else if ( ! empty( $sep ) ) {
					// Don't disturb the precious title when WP_DEBUG is on.
					add_action( 'wp_footer', array( $this, 'title_doing_it_wrong' ), 20 );

					//* Notify cache.
					$this->title_doing_it_wrong = true;

					//* Notify transients
					$this->set_theme_dir_transient( false );

					//* Title is empty.
					$args['empty_title'] = true;

					return $this->build_title_doingitwrong( $title, $sep, $seplocation, $args );
				}
			}
		}

		//* Notify cache to keep using the same output. We're doing it right :).
		if ( ! isset( $this->title_doing_it_wrong ) )
			$this->title_doing_it_wrong = false;

		//* Empty title and rebuild it.
		return $this->build_title( $title = '', $seplocation, $args );
	}

	/**
	 * Parse and sanitize title args.
	 *
	 * @param array $args required The passed arguments.
	 * @param array $defaults The default arguments.
	 * @param bool $get_defaults Return the default arguments. Ignoring $args.
	 *
	 * @applies filters the_seo_framework_title_args : {
	 * 		@param int term_id The Taxonomy Term ID when taxonomy is also filled in. Else post ID.
	 * 		@param string taxonomy The Taxonomy name.
	 * 		@param bool page_on_front Page on front condition for example generation.
	 * 		@param bool placeholder Generate placeholder, ignoring options.
	 * 		@param bool notagline Generate title without tagline.
	 * 		@param bool meta Ignore doing_it_wrong. Used in og:title/twitter:title
	 * 		@param bool get_custom_field Do not fetch custom title when false.
	 * 		@param bool description_title Fetch title for description.
	 * 		@param bool is_front_page Fetch front page title.
	 * }
	 *
	 * @since 2.4.0
	 * @return array $args parsed args.
	 */
	public function parse_title_args( $args = array(), $defaults = array(), $get_defaults = false ) {

		//* Passing back the defaults reduces the memory usage.
		if ( empty( $defaults ) ) {
			$defaults = array(
				'term_id' 			=> $this->get_the_real_ID(),
				'taxonomy' 			=> '',
				'page_on_front'		=> false,
				'placeholder'		=> false,
				'notagline' 		=> false,
				'meta' 				=> true,
				'get_custom_field'	=> true,
				'description_title'	=> false,
				'is_front_page'		=> false,
				'escape'			=> true
			);

			//* @since 2.5.0
			$defaults = (array) apply_filters( 'the_seo_framework_title_args', $defaults, $args );
		}

		//* Return early if it's only a default args request.
		if ( $get_defaults )
			return $defaults;

		//* Array merge doesn't support sanitation. We're simply type casting here.
		$args['term_id'] 			= isset( $args['term_id'] ) 			? (int) $args['term_id'] 			: $defaults['term_id'];
		$args['taxonomy'] 			= isset( $args['taxonomy'] ) 			? (string) $args['taxonomy'] 		: $defaults['taxonomy'];
		$args['page_on_front'] 		= isset( $args['page_on_front'] ) 		? (bool) $args['page_on_front'] 	: $defaults['page_on_front'];
		$args['placeholder'] 		= isset( $args['placeholder'] ) 		? (bool) $args['placeholder'] 		: $defaults['placeholder'];
		$args['notagline'] 			= isset( $args['notagline'] ) 			? (bool) $args['notagline'] 		: $defaults['notagline'];
		$args['meta'] 				= isset( $args['meta'] ) 				? (bool) $args['meta'] 				: $defaults['meta'];
		$args['get_custom_field'] 	= isset( $args['get_custom_field'] ) 	? (bool) $args['get_custom_field'] 	: $defaults['get_custom_field'];
		$args['description_title'] 	= isset( $args['description_title'] ) 	? (bool) $args['description_title'] : $defaults['description_title'];
		$args['is_front_page'] 		= isset( $args['is_front_page'] ) 		? (bool) $args['is_front_page'] 	: $defaults['is_front_page'];
		$args['escape'] 			= isset( $args['escape'] ) 				? (bool) $args['escape'] 			: $defaults['escape'];

		return $args;
	}

	/**
	 * Build the title based on input, without tagline.
	 *
	 * @param string $title The Title to return
	 * @param array $args : accepted args : {
	 * 		@param int term_id The Taxonomy Term ID
	 * 		@param bool placeholder Generate placeholder, ignoring options.
	 * 		@param bool page_on_front Page on front condition for example generation
	 * }
	 *
	 * @since 2.4.0
	 *
	 * @return string Title without tagline.
	 */
	public function build_title_notagline( $title = '', $args = array() ) {

		if ( empty( $args ) )
			$args = $this->parse_title_args( '', '', true );

		$title = $this->get_placeholder_title( $title, $args );

		if ( empty( $title ) )
			$title = __( 'Untitled', 'autodescription' );

		if ( true === $args['escape'] ) {
			$title = wptexturize( $title );
			$title = convert_chars( $title );
			$title = esc_html( $title );
			$title = capital_P_dangit( $title );
			$title = trim( $title );
		}

		return $title;
	}

	/**
	 * Build the title based on input, without tagline.
	 * Note: Not escaped.
	 *
	 * @param string $title The Title to return
	 * @param array $args : accepted args : {
	 * 		@param int term_id The Taxonomy Term ID
	 * 		@param bool placeholder Generate placeholder, ignoring options.
	 * 		@param bool page_on_front Page on front condition for example generation
	 * }
	 *
	 * @since 2.4.0
	 *
	 * @return string Title without tagline.
	 */
	public function get_placeholder_title( $title = '', $args = array() ) {

		if ( empty( $args ) )
			$args = $this->parse_title_args( '', '', true );

		/**
		 * Detect if placeholder is being generated.
		 * @since 2.2.4
		 */
		if ( $args['placeholder'] && empty( $title ) ) {
			$term_id = $args['term_id'];

			if ( ! empty( $term_id ) ) {
				$title = get_the_title( $term_id );
			} else if ( $args['page_on_front'] ) {
				$title = get_the_title( get_option( 'page_on_front' ) );
			} else {
				$post = get_post( $term_id, OBJECT );

				/**
				 * Memory leak fix
				 * @since 2.3.5
				 */
				$title = isset( $post->post_title ) && ! empty( $post->post_title ) ? $post->post_title : '';
			}
		}

		return $title;
	}

	/**
	 * Build the title based on input for themes that are doing it wrong.
	 * Pretty much a duplicate of build_title but contains many more variables.
	 * Keep this in mind.
	 *
	 * @param string $title The Title to return
	 * @param string $sep The Title sepeartor
	 * @param string $seplocation The Title sepeartor location ( accepts 'left' or 'right' )
	 * @param array $args : accepted args : {
	 * 		@param int term_id The Taxonomy Term ID
	 * 		@param string taxonomy The Taxonomy name
	 * 		@param bool placeholder Generate placeholder, ignoring options.
	 * 		@param bool get_custom_field Do not fetch custom title when false.
	 * }
	 *
	 * @since 2.4.0
	 *
	 * @return string $title Title
	 */
	public function build_title_doingitwrong( $title = '', $sep = '', $seplocation = '', $args = array() ) {

		/**
		 * Empty the title, because most themes think they 'know' how to SEO the front page.
		 * Because, most themes know how to make the title 'pretty'.
		 * And therefor add all kinds of stuff.
		 *
		 * Moved up and return early to reduce processing.
		 * @since 2.3.8
		 */
		if ( is_front_page() )
			return $title = '';

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

		if ( empty( $args ) )
			$args = $this->parse_title_args( '', '', true );

		$blogname = $this->get_blogname();

		//* Remove separator if true.
		$sep_replace = false;

		/**
		 * Don't add/replace separator when false.
		 *
		 * @applies filters the_seo_framework_doingitwrong_add_sep
		 *
		 * @since 2.4.2
		 */
		$add_sep = (bool) apply_filters( 'the_seo_framework_doingitwrong_add_sep', true );

		//* Maybe remove separator.
		if ( $add_sep && ( ! empty( $sep ) || ! empty( $title ) ) ) {
			$sep_replace = true;
			$sep_to_replace = (string) $sep;
		}

		//* Fetch title from custom fields.
		if ( $args['get_custom_field'] && $this->is_singular( $args['term_id'] ) ) {
			$title_special = $this->title_from_special_fields();

			if ( empty( $title_special ) ) {
				$title_from_custom_field = $this->title_from_custom_field( $title, false, $args['term_id'] );
				$title = ! empty( $title_from_custom_field ) ? $title_from_custom_field : $title;
			} else {
				$title = $title_special;
			}
		}

		//* Generate the Title if empty or if home.
		if ( empty( $title ) )
			$title = (string) $this->generate_title( $args['term_id'], $args['taxonomy'], $escape = false );

		/**
		 * New filter.
		 * @since 2.3.0
		 *
		 * Removed previous filter.
		 * @since 2.3.5
		 */
		if ( $add_sep )
			$sep = (string) apply_filters( 'the_seo_framework_title_separator', $this->get_separator( 'title' ) );

		/**
		 * Add $sep_to_replace
		 *
		 * @since 2.3.8
		 */
		if ( $add_sep && $sep_replace ) {
			//* Title always contains something at this point.
			$tit_len = mb_strlen( $title );

			/**
			 * Prevent double separator on date archives.
			 * This will cause manual titles with the same separator at the end to be removed.
			 * Then again, update your theme. D:
			 *
			 * A separator is at least 2 long (space + separator).
			 *
			 * @param string $sep_to_replace Already confirmed to contain the old sep string.
			 *
			 * @since ???
			 *
			 * Now also considers seplocation.
			 * @since 2.4.1
			 */
			if ( $seplocation == 'right' ) {
				if ( $tit_len > 2 && ! mb_strpos( $title, $sep_to_replace, $tit_len - 2 ) )
					$title = $title . ' ' . $sep_to_replace;
			} else {
				if ( $tit_len > 2 && ! mb_strpos( $title, $sep_to_replace, 2 ) )
					$title = $sep_to_replace . ' ' . $title;
			}
		}

		//* Sep location has no influence.
		if ( $sep_replace && $add_sep ) {
			//* Add trailing space for the tagline/blogname is stuck onto this part with trim.

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
			if ( $seplocation == 'right' ) {
				$title = trim( rtrim( $title, "$sep_to_replace " ) ) . " $sep ";
			} else {
				$title = " $sep " . trim( ltrim( $title, " $sep_to_replace" ) );
			}

		} else {
			$title = trim( $title ) . " $sep ";
		}

		/**
		 * From WordPress core get_the_title.
		 * Bypasses get_post() function object which causes conflict with some themes and plugins.
		 *
		 * Also bypasses the_title filters.
		 * And now also works in admin. It gives you a true representation of its output.
		 *
		 * @since 2.4.1
		 *
		 * @applies filters core : protected_title_format
		 * @applies filters core : private_title_format
		 */
		if ( ! $args['description_title'] ) {
			$post = get_post( $args['term_id'], OBJECT );

			if ( isset( $post->post_password ) && ! empty( $post->post_password ) ) {
				$protected_title_format = apply_filters( 'protected_title_format', __( 'Protected: %s', 'autodescription' ), $post );
				$title = sprintf( $protected_title_format, $title );
			} else if ( isset( $post->post_status ) && 'private' == $post->post_status ) {
				$private_title_format = apply_filters( 'private_title_format', __( 'Private: %s', 'autodescription' ), $post );
				$title = sprintf( $private_title_format, $title );
			}

		}

		if ( true === $args['escape'] ) {
			$title = wptexturize( $title );
			$title = convert_chars( $title );
			$title = esc_html( $title );
			$title = capital_P_dangit( $title );
		}

		/**
		 * Debug output.
		 * @since 2.3.4
		 */
		if ( $this->the_seo_framework_debug ) {
			if ( $this->the_seo_framework_debug_hidden )
				echo "<!--\r\n";

			$this->echo_debug_information( array( 'title output' => $title ) );
			echo "\r\n<br>\r\n" . 'END: ' . __CLASS__ . '::' . __FUNCTION__ .  "\r\n<br><br>";

			if ( $this->the_seo_framework_debug_hidden )
				echo "\r\n-->";
		}

		return $title;
	}

	/**
	 * Build the title based on input.
	 *
	 * @param string $title The Title to return
	 * @param string $seplocation The Title sepeartor location ( accepts 'left' or 'right' )
	 * @param array $args : accepted args : {
	 * 		@param int 		term_id The Taxonomy Term ID
	 * 		@param string 	taxonomy The Taxonomy name
	 * 		@param bool 	page_on_front Page on front condition for example generation
	 * 		@param bool 	placeholder Generate placeholder, ignoring options.
	 * 		@param bool 	get_custom_field Do not fetch custom title when false.
	 * 		@param bool 	is_front_page Fetch front page title.
	 * }
	 *
	 * @since 2.4.0
	 *
	 * @return string $title Title
	 */
	public function build_title( $title = '', $seplocation = '', $args = array() ) {

		if ( empty( $args ) )
			$args = $this->parse_title_args( '', '', true );

		/**
		 * Overwrite title here, prevents duplicate title issues, since we're working with a filter.
		 *
		 * @since 2.2.2
		 */
		$title = '';

		$is_front_page = is_front_page() || $args['page_on_front'] ? true : false;
		$blogname = $this->get_blogname();

		/**
		 * Cache the seplocation for is_home()
		 * @since 2.2.2
		 */
		$seplocation_home = $seplocation;

		/**
		 * Filters the separator location
		 * @since 2.1.8
		 */
		if ( '' === $seplocation || 'right' !== $seplocation || 'left' !== $seplocation || empty( $seplocation ) ) {
			/**
			 * New filter.
			 * @since 2.3.0
			 *
			 * Removed previous filter.
			 * @since 2.3.5
			 */
			$seplocation = (string) apply_filters( 'the_seo_framework_title_seplocation', $this->get_option( 'title_location' ) );
		}

		/**
		 * Filters the separator
		 * @since 2.0.5
		 *
		 * New filter.
		 * @since 2.3.0
		 *
		 * Removed previous filter.
		 * @since 2.3.5
		 */
		$sep = (string) apply_filters( 'the_seo_framework_title_separator', $this->get_separator( 'title' ) );

		//* Fetch title from custom fields.
		if ( $args['get_custom_field'] && $this->is_singular( $args['term_id'] ) ) {
			$title_special = $this->title_from_special_fields();

			if ( empty( $title_special ) ) {
				$title_from_custom_field = $this->title_from_custom_field( $title, '', $args['term_id'] );
				$title = ! empty( $title_from_custom_field ) ? $title_from_custom_field : $title;
			} else {
				$title = $title_special;
			}
		}

		/**
		 * Tagline conditional for homepage
		 *
		 * @since 2.2.2
		 */
		$add_tagline = 0;

		/**
		 * Generate the Title if empty or if home.
		 *
		 * Generation of title has acquired its own functions.
		 * @since 2.3.4
		 */
		if ( $is_front_page || $this->is_static_frontpage( $args['term_id'] ) || $args['is_front_page'] ) {
			$generated = (array) $this->generate_home_title( $args['get_custom_field'], $seplocation, $seplocation_home, $escape = false );

			if ( ! empty( $generated ) && is_array( $generated ) ) {
				$title = $generated['title'] ? (string) $generated['title'] : $title;
				$blogname = $generated['blogname'] ? (string) $generated['blogname'] : $blogname;
				$add_tagline = $generated['add_tagline'] ? (bool) $generated['add_tagline'] : $add_tagline;
				$seplocation = $generated['seplocation'] ? (string) $generated['seplocation'] : $seplocation;
			}
		} else if ( empty( $title ) ) {
			$title = (string) $this->generate_title( $args['term_id'], $args['taxonomy'], $escape = false );
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
		 *
		 * @global $page
		 * @global $paged
		 *
		 * @applies filters core : protected_title_format
		 * @applies filters core : private_title_format
		 */
		if ( ! $args['description_title'] ) {
			global $page, $paged;

			$post = get_post( $args['term_id'], OBJECT );

			if ( isset( $post->post_password ) && ! empty( $post->post_password ) ) {
				$protected_title_format = apply_filters( 'protected_title_format', __( 'Protected: %s', 'autodescription' ), $post );
				$title = sprintf( $protected_title_format, $title );
			} else if ( isset( $post->post_status ) && 'private' == $post->post_status ) {
				$private_title_format = apply_filters( 'private_title_format', __( 'Private: %s', 'autodescription' ), $post );
				$title = sprintf( $private_title_format, $title );
			}

			/**
			 * @since 2.4.3
			 * Adds page numbering within the title.
			 */
			if ( ! is_404() && ( $paged >= 2 || $page >= 2 ) )
				$title .= " $sep " . sprintf( __( 'Page %s', 'autodescription' ), max( $paged, $page ) );

			//* Title for title (meta) tags.
			if ( $is_front_page && ! $add_tagline ) {
				//* Render frontpage output without tagline
				$title = $blogname;
			}

			/**
			 * Get blogname additions from option, invert it and cast to bool.
			 * @since 2.5.2
			 */
			$add_blogname_option = (bool) ! $this->get_option( 'title_rem_additions' );

			//* If theme is doing it wrong, add it anyway in the admin area.
			if ( is_admin() && ! $this->theme_title_doing_it_right() )
				$add_blogname_option = true;

			/**
			 * Applies filters the_seo_framework_add_blogname_to_title.
			 * @since 2.4.3
			 */
			$add_blogname = (bool) apply_filters( 'the_seo_framework_add_blogname_to_title', $add_blogname_option );

			/**
			 * On frontpage: Add title if add_tagline is true.
			 * On all other pages: Add tagline if filters $add_blogname is true.
			 *
			 * @since 2.4.3
			 */
			if ( ( $add_blogname && ! $is_front_page ) || ( $is_front_page && $add_tagline ) ) {
				$title = trim( $title );
				$blogname = trim( $blogname );

				if ( 'right' == $seplocation ) {
					$title = $title . " $sep " . $blogname;
				} else {
					$title = $blogname . " $sep " . $title;
				}
			}

			if ( true === $args['escape'] ) {
				$title = wptexturize( $title );
				$title = convert_chars( $title );
				$title = esc_html( $title );
				$title = capital_P_dangit( $title );
				$title = trim( $title );
			}
		}

		/**
		 * Debug output.
		 * @since 2.3.4
		 */
		if ( $this->the_seo_framework_debug ) {

			if ( $this->the_seo_framework_debug_hidden )
				echo "<!--\r\n";

			$this->echo_debug_information( array( 'is static frontpage' => $this->is_static_frontpage( $this->get_the_real_ID() ) ) );
			$this->echo_debug_information( array( 'title output' => $title ) );
			echo "\r\n<br>\r\n" . 'END: ' . __CLASS__ . '::' . __FUNCTION__ .  "\r\n<br><br>";

			if ( $this->the_seo_framework_debug_hidden )
				echo "\r\n-->";
		}

		return $title;
	}

	/**
	 * Fetches title from special fields, like other plugins.
	 * Used before and has priority over custom fields.
	 * Front end only.
	 *
	 * @since 2.5.2
	 *
	 * @return string $title Title from Special Field.
	 */
	public function title_from_special_fields() {

		$title = '';

		if ( ! is_admin() ) {
			if ( $this->is_ultimate_member_user_page() && um_is_core_page( 'user' ) && um_get_requested_user() ) {
				$title = um_user( 'display_name' );
			}
		}

		return $title;
	}

	/**
	 * Generate the title based on query conditions.
	 *
	 * @since 2.3.4
	 *
	 * @param int $term_id The Taxonomy Term ID
	 * @param string $taxonomy The Taxonomy name
	 * @param bool $escape Parse Title through saninitation calls.
	 *
	 * @return string $title The Generated Title.
	 */
	public function generate_title( $term_id = 0, $taxonomy = '', $escape = false ) {

		/**
		 * Combined the statements
		 * @since 2.2.7 && @since 2.2.8
		 *
		 * Check for singular first, like WooCommerce shop.
		 * @since 2.5.2
		 */
		if ( ! $this->is_singular( $term_id ) ) {
			if ( is_category() || is_tag() || is_tax() || ( ! empty( $term_id ) && ! empty( $taxonomy ) ) ) {
				$title = $this->title_for_terms();
			} else if ( is_archive() ) {
				/**
				 * Get all other archive titles
				 * @since 2.5.2
				 */
				$title = wp_strip_all_tags( $this->get_the_archive_title() );
			}
		}

		/**
		 * Applies filters string the_seo_framework_404_title
		 * @since 2.5.2
		 */
		if ( is_404() )
			$title = (string) apply_filters( 'the_seo_framework_404_title', '404' );

		if ( is_search() ) {
			/**
			 * Applies filters string the_seo_framework_404_title
			 * @since 2.5.2
			 */
			/* translators: Front-end output. */
			$search_title = (string) apply_filters( 'the_seo_framework_search_title', __( 'Search results for:', 'autodescription' ) );
			$title = $search_title . ' ' . trim( get_search_query() );
		}

		//* Generate admin placeholder for taxonomies
		if ( empty( $title ) && ! empty( $term_id ) && ! empty( $taxonomy ) ) {
			$term = get_term_by( 'id', $term_id, $taxonomy, OBJECT );

			if ( ! empty( $term ) && is_object( $term ) ) {
				$term_name = ! empty( $term->name ) ? $term->name : $term->slug;
			} else {
				/* translators: Front-end output. */
				$term_name = __( 'Untitled', 'autodescription' );
			}

			$tax_type = $term->taxonomy;

			/**
			 * Dynamically fetch the term name.
			 *
			 * @since 2.3.1
			 */
			$term_labels = $this->get_tax_labels( $tax_type );

			if ( isset( $term_labels ) && isset( $term_labels->singular_name ) ) {
				$title = $term_labels->singular_name . ': ' . $term_name;
			} else {
				/* translators: Front-end output. */
				$title = __( 'Archives', 'autodescription' );
			}
		}

		//* Fetch the post title if no title is found.
		if ( ! isset( $title ) || empty( $title ) ) {

			if ( empty( $term_id ) )
				$term_id = $this->get_the_real_ID();

			$post = get_post( $term_id, OBJECT );

			$title = '';

			/**
			 * From WordPress core get_the_title.
			 * Bypasses get_post() function object which causes conflict with some themes and plugins.
			 *
			 * Also bypasses the_title filters.
			 * And now also works in admin. It gives you a true representation of its output.
			 *
			 * @since 2.4.1
			 */
			$title = isset( $post->post_title ) ? $post->post_title : $title;
		}

		//* You forgot to enter a title "anywhere"!
		//* So it's untitled :D
		if ( empty( $title ) ) {
			/* translators: Front-end output. */
			$title = __( 'Untitled', 'autodescription' );
		}

		if ( $escape ) {
			$title = wptexturize( $title );
			$title = convert_chars( $title );
			$title = esc_html( $title );
			$title = capital_P_dangit( $title );
		}

		return $title;
	}

	/**
	 * Generate the title based on conditions for the home page.
	 *
	 * @since 2.3.4
	 *
	 * @param bool $get_custom_field Fetch Title from Custom Fields.
	 * @param string $seplocation The separator location
	 * @param string $seplocation_home The Homepage separator location
	 * @param bool $escape Parse Title through saninitation calls.
	 *
	 * @return array {
	 *		'title' => (string) $title : The Generated Title
	 *		'blogname' => (string) $blogname : The Generated Blogname
	 *		'add_tagline' => (bool) $add_tagline : Wether to add the tagline
	 *		'seplocation' => (string) $seplocation : The Separator Location
	 *	}
	 */
	public function generate_home_title( $get_custom_field = true, $seplocation = '', $seplocation_home = '', $escape = false ) {

		/**
		 * Tagline conditional for homepage
		 *
		 * @since 2.2.2
		 *
		 * Conditional statement.
		 * @since 2.3.4
		 */
		$add_tagline = $this->get_option( 'homepage_tagline' ) ? $this->get_option( 'homepage_tagline' ) : 0;

		/**
		 * Add tagline or not based on option
		 *
		 * @since 2.2.2
		 */
		if ( $add_tagline ) {
			/**
			 * Tagline based on option.
			 *
			 * @since 2.3.8
			 */
			$tagline = (string) $this->get_option( 'homepage_title_tagline' );
			$title = ! empty( $tagline ) ? $tagline : $this->get_blogdescription();
		} else {
			$title = '';
		}

		/**
		 * Render from function
		 * @since 2.2.8
		 */
		$title_for_home = $this->title_for_home( '', $get_custom_field, false );
		$blogname = ! empty( $title_for_home ) ? $title_for_home : $this->get_blogname();

		if ( empty( $seplocation_home ) || $seplocation_home !== 'left' || $seplocation_home !== 'right' ) {
			/**
			 * New filter.
			 * @since 2.3.0
			 *
			 * Removed previous filter.
			 * @since 2.3.5
			 */
			$seplocation = (string) apply_filters( 'the_seo_framework_title_seplocation_front', $this->get_option( 'home_title_location' ) );
		}

		if ( $escape ) {
			$title = wptexturize( $title );
			$title = convert_chars( $title );
			$title = esc_html( $title );
			$title = capital_P_dangit( $title );
		}

		return array(
			'title' => $title,
			'blogname' => $blogname,
			'add_tagline' => $add_tagline,
			'seplocation' => $seplocation
		);
	}

	/**
	 * Gets the title for the static home page.
	 *
	 * @since 2.2.8
	 *
	 * @param string $home_title The fallback title.
	 * @param bool $get_custom_field Fetch Title from Custom Fields.
	 * @param bool $escape Parse Title through saninitation calls.
	 *
	 * @return string The Title.
	 */
	public function title_for_home( $home_title = '', $get_custom_field = true, $escape = false ) {

		/**
		 * Get blogname title based on option
		 *
		 * @since 2.2.2
		 */
		$home_title_option = (string) $this->get_option( 'homepage_title' );
		$home_title = ! empty( $home_title_option ) ? $home_title_option : $home_title;

		/**
		 * Fetch from Home Page InPost SEO Box if empty.
		 *
		 * @since 2.2.4
		 *
		 * Add home is page check.
		 * @since 2.2.5
		 *
		 * Add get custom Inpost field check
		 * @since 2.3.4
		 */
		if ( $get_custom_field && 'page' === get_option( 'show_on_front' ) && empty( $home_title ) ) {
			$custom_field = $this->get_custom_field( '_genesis_title' );
			$home_title = ! empty( $custom_field ) ? (string) $custom_field : $home_title;
		}

		if ( $escape ) {
			$home_title = wptexturize( $home_title );
			$home_title = convert_chars( $home_title );
			$home_title = esc_html( $home_title );
			$home_title = capital_P_dangit( $home_title );
		}

		return (string) $home_title;
	}

	/**
	 * Gets the title for Category, Tag or Taxonomy
	 *
	 * @since 2.2.8
	 *
	 * @param string $title the fallback title.
	 * @param bool $escape Parse Title through saninitation calls.
	 *
	 * @return string The Title.
	 */
	public function title_for_terms( $title = '', $escape = false ) {

		if ( is_category() || is_tag() ) {
			global $wp_query;

			$term = $wp_query->get_queried_object();

			$title = ! empty( $term->admeta['doctitle'] ) ? $term->admeta['doctitle'] : $title;
			$flag = $term->admeta['saved_flag'] != '0' ? true : false;

			if ( ! $flag && empty( $title ) && isset( $term->meta['doctitle'] ) )
				$title = ! empty( $term->meta['doctitle'] ) ? $term->meta['doctitle'] : $title;

			if ( empty( $title ) )
				$title = ! empty( $term->name ) ? wp_strip_all_tags( $this->get_the_archive_title() ) : $term->slug;

		} else if ( is_tax() ) {

			$term  = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );

			$title = ! empty( $term->admeta['doctitle'] ) ? wp_kses_stripslashes( wp_kses_decode_entities( $term->admeta['doctitle'] ) ) : $title;
			$flag = $term->admeta['saved_flag'] != '0' ? true : false;

			if ( ! $flag && empty( $title ) && isset( $term->meta['doctitle'] ) )
				$title = ! empty( $term->meta['doctitle'] ) ? wp_kses_stripslashes( wp_kses_decode_entities( $term->meta['doctitle'] ) ) : $title;

			if ( empty( $title ) )
				$title = ! empty( $term->name ) ? wp_strip_all_tags( $this->get_the_archive_title() ) : $term->slug;

		}

		if ( $escape ) {
			$title = wptexturize( $title );
			$title = convert_chars( $title );
			$title = esc_html( $title );
			$title = capital_P_dangit( $title );
		}

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
	 *
	 * @return string The Title.
	 */
	public function title_from_custom_field( $title = '', $escape = false, $id = null ) {

		$id = isset( $id ) ? $id : $this->get_the_real_ID();

		/**
		 * Create something special for blog page.
		 * Only if it's not the home page.
		 *
		 * @since 2.2.8
		 */
		if ( $this->is_blog_page( $id ) ) {
			//* Posts page title.
			$title = $this->get_custom_field( '_genesis_title', $id ) ? $this->get_custom_field( '_genesis_title', $id ) : get_the_title( $id );
		} else {
			//* Get title from custom field, empty it if it's not there to override the default title
			$title = $this->get_custom_field( '_genesis_title', $id ) ? $this->get_custom_field( '_genesis_title', $id ) : $title;
		}

		/**
		 * Fetch Title from WordPress page title input.
		 */
		if ( empty( $title ) ) {
			$post = get_post( $id, OBJECT );
			$title = isset( $post->post_title ) ? $post->post_title : '';
		}

		if ( $escape ) {
			$title = wptexturize( $title );
			$title = convert_chars( $title );
			$title = esc_html( $title );
			$title = capital_P_dangit( $title );
		}

		return (string) $title;
	}

	/**
	 * Creates canonical url
	 *
	 * @uses WPMUdev's domain mapping
	 *
	 * @param string $url the url
	 * @param int $page_id the page id, if empty it will fetch the requested ID, else the page uri.
	 *
	 * @since 2.4.2
	 * @param array $args : accepted args : {
	 * 			@param bool $paged Return current page URL without pagination
	 * 			@param bool $from_option Get the canonical uri option
	 * 			@param object $post The Post Object.
	 * 			@param bool $external Wether to fetch the current WP Request or get the permalink by Post Object.
	 * 			@param bool $is_term Fetch url for term.
	 * 			@param object $term The term object.
	 * 			@param bool $home Fetch home URL.
	 * 			@param bool $forceslash Fetch home URL and slash it, always.
	 * }
	 *
	 * @TODO Clean this function up @since 2.5.3
	 *
	 * @since 2.0.0
	 */
	public function the_url( $url = '', $page_id = '', $args = array() ) {

		/**
		 * Debug parameters.
		 * @since 2.4.2
		 */
		if ( $this->the_seo_framework_debug && ! $this->doing_sitemap ) {
			if ( $this->the_seo_framework_debug_hidden )
				echo "<!--\r\n";

			echo  "\r\n" . 'START: ' . __CLASS__ . '::' . __FUNCTION__ .  "\r\n";
			$this->echo_debug_information( array( 'input url' => $url ) );

			if ( $this->the_seo_framework_debug_more ) {
				$this->echo_debug_information( array( 'page_id' => $page_id ) );
				$this->echo_debug_information( array( 'args' => $args ) );
			}

			$timer_start = microtime( true );

			if ( $this->the_seo_framework_debug_hidden )
				echo "\r\n-->";
		}

		$default_args = $this->parse_url_args( '', '', true );

		/**
		 * Parse args.
		 * @since 2.4.2
		 */
		if ( ! is_array( $args ) ) {
			//* Old style parameters are used. Doing it wrong.
			_doing_it_wrong( __CLASS__ . '::' . __FUNCTION__, 'Use $args = array() for parameters.', $this->the_seo_framework_version( '2.4.2' ) );
			$args = $default_args;
		} else if ( ! empty( $args ) ) {
			$args = $this->parse_url_args( $args, $default_args );
		} else {
			$args = $default_args;
		}

		$path = '';
		$scheme = '';

		/**
		 * Trailing slash the post, or not.
		 * @since 2.2.4
		 */
		$slashit = true;

		/**
		 * Fetch permalink if Feed.
		 * @since 2.5.2
		 */
		if ( is_feed() )
			$url = get_permalink( $args['post'] );

		if ( empty( $url ) && ! $args['home'] ) {

			/**
			 * Get url from options
			 * @since 2.2.9
			 */
			if ( $args['get_custom_field'] && $this->is_singular() )
				$url = $this->get_custom_field( '_genesis_canonical_uri' ) ? $this->get_custom_field( '_genesis_canonical_uri' ) : $url;

			if ( empty( $url ) ) {

				if ( $args['is_term'] || is_archive() ) {

					$term = $args['term'];

					//* Term or Taxonomy.
					if ( ! isset( $term ) ) {
						global $wp_query;
						$term = $wp_query->get_queried_object();
					}

					if ( isset( $term->taxonomy ) ) {
						//* Registered Terms and Taxonomies.
						$path = $this->get_relative_term_url( $term, $args['external'] );
					} else if ( ! $args['external'] ) {
						//* Everything else.
						global $wp;
						$path = $wp->request;
					} else {
						//* Nothing to see here...
						$path = '';
					}
				} else {

					$post = $args['post'];

					/**
					 * Fetch post object
					 * @since 2.2.4
					 */
					if ( ! isset( $post ) )
						$post = get_post( $page_id, OBJECT );

					/**
					 * Get page uri if Page ID is given
					 * @since 2.1.9
					 *
					 * Get page uri if $paged is true.
					 * Don't get page uri if there's no pretty permalinks or unconditioned post statusses
					 * @since 2.2.4
					 *
					 * Get relative full path.
					 * @since 2.3.0
					 */
					if ( isset( $post ) ) {

						$page_id = empty( $page_id ) ? $post->ID : $page_id;

						$permalink_structure = get_option( 'permalink_structure' );

						if ( ( $args['paged'] || ! empty( $page_id ) ) && '' != $permalink_structure && ! $this->in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft', 'future' ) ) ) {
							//* Registered page.

							if ( ! $args['paged'] && ! empty( $page_id ) && $page_id == get_option( 'page_on_front' ) ) {
								//* Static Home Page.
								$path = '';
							} else {
								//* Any other page.
								$path = $this->get_relative_url( $post, $args['external'] );
							}
						} else if ( '' == $permalink_structure || $this->in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft', 'future' ) ) ) {
							//* Registered page, Default permalink structure.

							if ( ! empty( $page_id ) && $page_id != get_option( 'page_on_front' ) ) {
								//* Every other page.
								$path = $this->the_url_path_default_permalink_structure( $post );
								$slashit = false;
							} else {
								//* Home page
								$path = '';
							}
						} else if ( $args['paged'] || ! empty( $page_id ) ) {
							//* Registered pending/draft page.
							$path = $this->get_relative_url( $post, $args['external'], $page_id );
						} else if ( ! $args['external'] ) {
							//* Everything else.
							global $wp;
							$path = $wp->request;
						} else {
							//* Nothing to see here...
							$path = '';
						}

					}

				}

			}
		}

		//* Domain Mapping canonical URL
		if ( empty( $url ) ) {
			$wpmu_url = $this->the_url_wpmudev_domainmap( $path, true );
			if ( ! empty( $wpmu_url ) && is_array( $wpmu_url ) ) {
				$url = $wpmu_url[0];
				$scheme = $wpmu_url[1];
			}
		}

		//* Domain Mapping canonical URL
		if ( empty( $url ) ) {
			$dm_url = $this->the_url_donncha_domainmap( $path, true );
			if ( ! empty( $dm_url ) && is_array( $dm_url ) ) {
				$url = $dm_url[0];
				$scheme = $dm_url[1];
			}
		}

		//* Non-domainmap URL
		if ( empty( $url ) ) {
			if ( $args['home'] ) {
				$url = user_trailingslashit( get_option( 'home' ) );
				$slashit = false;
			} else {
				$url = trailingslashit( get_option( 'home' ) ) . ltrim( $path, '\/ ' );
				$scheme = is_ssl() ? 'https' : 'http';
			}
		}

		//* URL has been given manually or $args['home'] is true.
		if ( ! isset( $scheme ) )
			$scheme = is_ssl() ? 'https' : 'http';

		$output = $this->set_url_scheme( $url, $scheme );

		/**
		 * Slash it only if $slashit is true
		 *
		 * @since 2.2.4
		 */
		if ( $slashit && ! $args['forceslash'] )
			$output = user_trailingslashit( $output );

		//* Be careful with the default permalink structure.
		if ( $args['forceslash'] )
			$output = trailingslashit( $output );

		$output = esc_url( $output );

		/**
		 * Debug parameters.
		 * @since 2.4.2
		 */
		if ( $this->the_seo_framework_debug && ! $this->doing_sitemap ) {

			if ( $this->the_seo_framework_debug_hidden )
				echo "<!--\r\n";

			$this->echo_debug_information( array( 'output' => $output ) );

			if ( $this->the_seo_framework_debug_more ) {
				$this->echo_debug_information( array( 'page_id' => $page_id ) );
				$this->echo_debug_information( array( 'args' => $args ) );
			}
			$this->echo_debug_information( array( 'Generation time' => number_format( microtime(true) - $timer_start, 5 ) . 's' ) );
			echo  "\r\n<br>\r\n" . 'END: ' . __CLASS__ . '::' . __FUNCTION__ .  "\r\n<br>\r\n";

			if ( $this->the_seo_framework_debug_hidden )
				echo "\r\n-->";
		}

		return $output;
	}

	/**
	 * Parse and sanitize url args.
	 *
	 * @param array $args required The passed arguments.
	 * @param array $defaults The default arguments.
	 * @param bool $get_defaults Return the default arguments. Ignoring $args.
	 *
	 * @applies filters the_seo_framework_url_args : {
	 * 		@param bool $paged Return current page URL without pagination
	 * 		@param bool $from_option Get the canonical uri option
	 * 		@param object $post The Post Object.
	 * 		@param bool $external Wether to fetch the current WP Request or get the permalink by Post Object.
	 * 		@param bool $is_term Fetch url for term.
	 * 		@param object $term The term object.
	 * 		@param bool $home Fetch home URL.
	 * 		@param bool $forceslash Fetch home URL and slash it, always.
	 * }
	 *
	 * @since 2.4.2
	 * @return array $args parsed args.
	 */
	public function parse_url_args( $args = array(), $defaults = array(), $get_defaults = false ) {

		//* Passing back the defaults reduces the memory usage.
		if ( empty( $defaults ) ) {
			$defaults = array(
				'paged' 			=> false,
				'get_custom_field'	=> true,
				'external'			=> false,
				'is_term' 			=> false,
				'post' 				=> null,
				'term'				=> null,
				'home'				=> false,
				'forceslash'		=> false
			);

			//* @since 2.5.0
			$defaults = (array) apply_filters( 'the_seo_framework_url_args', $defaults, $args );
		}

		//* Return early if it's only a default args request.
		if ( $get_defaults )
			return $defaults;

		//* Array merge doesn't support sanitation. We're simply type casting here.
		$args['paged'] 				= isset( $args['paged'] ) 				? (bool) $args['paged'] 			: $defaults['paged'];
		$args['get_custom_field'] 	= isset( $args['get_custom_field'] ) 	? (bool) $args['get_custom_field'] 	: $defaults['get_custom_field'];
		$args['external'] 			= isset( $args['external'] ) 			? (bool) $args['external'] 			: $defaults['external'];
		$args['is_term'] 			= isset( $args['is_term'] ) 			? (bool) $args['is_term'] 			: $defaults['is_term'];
		$args['get_custom_field'] 	= isset( $args['get_custom_field'] ) 	? (bool) $args['get_custom_field'] 	: $defaults['get_custom_field'];
		$args['post'] 				= isset( $args['post'] ) 				? (object) $args['post'] 			: $defaults['post'];
		$args['term'] 				= isset( $args['term'] ) 				? (object) $args['term'] 			: $defaults['term'];
		$args['home'] 				= isset( $args['home'] ) 				? (bool) $args['home'] 				: $defaults['home'];
		$args['forceslash'] 		= isset( $args['forceslash'] ) 			? (bool) $args['forceslash'] 		: $defaults['forceslash'];

		return $args;
	}

	/**
	 * Generates relative URL for current post_ID.
	 *
	 * @param object $post The post.
	 * @param bool $external Wether to fetch the WP Request or get the permalink by Post Object.
	 * @param id $page_id The page id.
	 *
	 * @since 2.3.0
	 *
	 * @return relative Post or Page url.
	 */
	public function get_relative_url( $post = null, $external = false, $page_id = null ) {

		if ( ! isset( $post ) )
			global $post;

		if ( $external ) {
			if ( isset( $page_id ) ) {
				$permalink = get_permalink( $page_id );
			} else {
				$permalink = get_permalink( $post );
			}
		} else {
			global $wp;
			$permalink = isset( $wp->request ) ? $wp->request : get_permalink( $post );
		}

		/**
		 * @since 2.4.2
		 */
		$path = $this->set_url_scheme( $permalink, 'relative' );

		/**
		 * @since 2.5.2
		 */
		static $icl_exists = null;

		if ( ! isset( $icl_exists ) )
			$icl_exists = (bool) defined( 'ICL_LANGUAGE_CODE' );

		//* WPML support.
		if ( $icl_exists && ! is_admin() && isset( $post->guid ) )
			$path = $this->get_relative_wmpl_url( $path, $post );

		/**
		 * @since 2.5.2
		 */
		static $qt_exists = null;

		if ( ! isset( $qt_exists ) )
			$qt_exists = (bool) class_exists( 'QTX_Translator' );

		//* qTranslate X support. Doesn't need to work on sitemaps.
		if ( ! $external && $qt_exists ) {
			static $q_config = null;

			if ( ! isset( $q_config ) )
				global $q_config;

			$mode = $q_config['url_mode'];

			//* Only change URL on Pre-Path mode.
			if ( (int) 2 === $mode ) {

				//* If false, change canonical URL for every page.
				$hide = $q_config['hide_default_language'];

				$current_lang = $q_config['language'];
				$default_lang = $q_config['default_language'];

				//* Add prefix.
				if ( ! $hide || $current_lang != $default_lang )
					$path = '/' . $current_lang . '/' . ltrim( $path, '\/ ' );

			}
		}

		return $path;
	}

	/**
	 * Generate relative WPML url.
	 *
	 * @param string $path The current path.
	 * @param string $post The Post Object.
	 *
	 * @since 2.4.3
	 *
	 * @return relative path for WPML urls.
	 */
	public function get_relative_wmpl_url( $path, $post ) {
		global $sitepress;

		if ( isset( $sitepress ) ) {

			$post_guid = $post->guid;

			$negotiation_type = $sitepress->get_setting( 'language_negotiation_type' );

			//* If negotiation_type is 2, the home_url will handle this.

			if ( 1 === $negotiation_type ) {
				//* Language is path.

				/**
				 * @since 2.5.2
				 */
				static $icl_gl_exists = null;

				if ( ! isset( $icl_gl_exists ) )
					$icl_gl_exists = (bool) function_exists( 'icl_get_languages' );

				if ( $icl_gl_exists && strpos( $post_guid, 'lang=' ) !== false ) {
					//* Language is found in query arg.

					//* Fetch first directory path
					$lang_path = explode( '/', $path );
					$lang_path = isset( $lang_path[1] ) ? $lang_path[1] : '';

					if ( ! empty( $lang_path ) ) {
						//* Directory path parsed succesfully.

						$language_keys = array_keys( icl_get_languages() );
						if ( ! empty( $language_keys ) && ! in_array( $lang_path, $language_keys ) ) {
							//* Language code isn't found in first part of path. Add it.
							$path = '/' . $lang_path . '/' . ltrim( $path, '\/ ' );
						}
					}
				} else {

					$neg_offset = (int) - strlen( ICL_LANGUAGE_CODE );

					//* Only add if ICL Language is available in guid.
					if ( strpos( $post_guid, '/' . ICL_LANGUAGE_CODE . '/' ) !== false ) {
						//* Language path is found in GUID.

						if ( strpos( $path, '/' . ICL_LANGUAGE_CODE . '/' ) === false ) {
							//* Language path isn't found in permalink. Add it.
							$path = '/' . ICL_LANGUAGE_CODE . '/' . ltrim( $path, '\/ ' );
						}

					} else if ( $icl_gl_exists && strpos( $post_guid, 'lang=' ) !== false ) {
						//* Language is found in query arg.

						//* Fetch first directory path
						$lang_path = explode( '/', $path );
						$lang_path = $lang_path[1];

						if ( ! empty( $lang_path ) ) {
							//* Directory path parsed succesfully.

							$language_keys = array_keys( icl_get_languages() );
							if ( ! empty( $language_keys ) && in_array( $lang_path, $language_keys ) ) {
								//* Language code isn't found in first part of path. Add it.
								$path = '/' . $lang_path . '/' . ltrim( $path, '\/ ' );
							}
						}
					}
				}

			} else if ( 3 === $negotiation_type ) {
				//* Language names are parameters.

				if ( false !== strpos( $post_guid, 'lang=' ) ) {
					//* Add language parameter.

					$parsed_url = parse_url( $post_guid );
					parse_str( $parts['query'], $queries );

					$path = user_trailingslashit( $path ) . '?lang=' . $queries['lang'];
				}
			}
		}

		return $path;
	}

	/**
	 * Generates relative URL for current term.
	 *
	 * @global WP_Query object $wp_query
	 * @global WP_Rewrite $wp_rewrite
	 * @global Paged $paged
	 *
	 * @param object $term The term object.
	 * @param bool $no_request wether to fetch the WP Request or get the permalink by Post Object.
	 *
	 * @since 2.4.2
	 *
	 * @return Relative term or taxonomy URL.
	 */
	public function get_relative_term_url( $term = null, $no_request = false ) {

		// We can't fetch the Term object within sitemaps.
		if ( $no_request && ! isset( $term ) )
			return '';

		if ( ! isset( $term ) ) {
			global $wp_query;
			$term = $wp_query->get_queried_object();
		}

		global $wp_rewrite,$paged;

		$taxonomy = $term->taxonomy;

		$termlink = $wp_rewrite->get_extra_permastruct( $taxonomy );

		$slug = $term->slug;
		$t = get_taxonomy( $taxonomy );

		if ( empty( $termlink ) ) {
			if ( 'category' == $taxonomy ) {
				$termlink = '?cat=' . $term->term_id;
			} elseif ( isset( $t->query_var ) && ! empty( $t->query_var ) ) {
				$termlink = "?$t->query_var=$slug";
			} else {
				$termlink = "?taxonomy=$taxonomy&term=$slug";
			}

			if ( $paged )
				$termlink .= '&page=' . $paged;

		} else {
			if ( $t->rewrite['hierarchical'] ) {
				$hierarchical_slugs = array();
				$ancestors = get_ancestors( $term->term_id, $taxonomy, 'taxonomy' );

				foreach ( (array) $ancestors as $ancestor ) {
					$ancestor_term = get_term( $ancestor, $taxonomy );
					$hierarchical_slugs[] = $ancestor_term->slug;
				}

				$hierarchical_slugs = array_reverse( $hierarchical_slugs );
				$hierarchical_slugs[] = $slug;

				$termlink = str_replace( "%$taxonomy%", implode( '/', $hierarchical_slugs ), $termlink );
			} else {
				$termlink = str_replace( "%$taxonomy%", $slug, $termlink );
			}

			if ( $paged )
				$termlink = trailingslashit( $termlink )  . 'page/' . $paged;

			$termlink = user_trailingslashit( $termlink, 'category' );
		}

		$path = $this->set_url_scheme( $termlink, 'relative' );

		return $path;
	}

	/**
	 * Set url scheme.
	 * WordPress core function, without filter.
	 *
	 * @param string $url Absolute url that includes a scheme.
	 * @param string $scheme optional. Scheme to give $url. Currently 'http', 'https', 'login', 'login_post', 'admin', or 'relative'.
	 *
	 * @since 2.4.2
	 * @return string url with chosen scheme.
	 */
	public function set_url_scheme( $url, $scheme = null ) {

		if ( ! isset( $scheme ) ) {
			$scheme = is_ssl() ? 'https' : 'http';
		} else if ( $scheme === 'admin' || $scheme === 'login' || $scheme === 'login_post' || $scheme === 'rpc' ) {
			$scheme = is_ssl() || force_ssl_admin() ? 'https' : 'http';
		} else if ( $scheme !== 'http' && $scheme !== 'https' && $scheme !== 'relative' ) {
			$scheme = is_ssl() ? 'https' : 'http';
		}

		$url = trim( $url );
		if ( substr( $url, 0, 2 ) === '//' )
			$url = 'http:' . $url;

		if ( 'relative' == $scheme ) {
			$url = ltrim( preg_replace( '#^\w+://[^/]*#', '', $url ) );
			if ( $url !== '' && $url[0] === '/' )
				$url = '/' . ltrim( $url , "/ \t\n\r\0\x0B" );

		} else {
			//* This will break if $scheme is set to false.
			$url = preg_replace( '#^\w+://#', $scheme . '://', $url );
		}

		return $url;
	}

	/**
	 * Creates canonical url for the default permalink structure.
	 *
	 * @param object $post The post.
	 *
	 * @since 2.3.0
	 */
	public function the_url_path_default_permalink_structure( $post = null ) {

		if ( ! $this->is_singular() ) {
			//* We're on a taxonomy
			$object = get_queried_object();

			if ( is_object( $object ) ) {
				if ( is_category() ) {
					$id = $object->term_id;
					$path = '?cat=' . $id;
				} else if ( is_tag() ) {
					$name = $object->name;
					$path = '?tag=' . $id;
				} else if ( is_date() ) {
					global $wp_query;

					$query = $wp_query->query;

					$year = $query->year;
					$month = ! empty( $query->monthnum ) ? '&monthnum=' . $query->monthnum : '';
					$day = ! empty( $query->day ) ? '&day=' . $query->day : '';

					$path = '?year=' . $year . $month . $day;
				} else if ( is_author() ) {
					$name = $object->author_name;
					$path = '?author=' . $name;
				} else if ( is_tax() ) {
					$name = $object->taxonomy;
					$path = '?taxonomy=' . $name;
				} else {
					$id = $object->ID;
					$path = '?p=' . $id;
				}
			}
		}

		if ( ! isset( $path ) ) {
			if ( isset( $post->ID ) ) {
				$id = $post->ID;
			} else {
				$id = $this->get_the_real_ID();
			}

			$path = '?p=' . $id;
		}

		return $path;
	}

	/**
	 * Try to get an canonical URL when WPMUdev Domain Mapping is active.
	 *
	 * @param string $path The post relative path.
	 *
	 * @since 2.3.0
	 *
	 * @param bool $get_scheme Output array with scheme.
	 * @since 2.4.0
	 *
	 * @return string|array|void The unescaped URL, the scheme
	 */
	public function the_url_wpmudev_domainmap( $path, $get_scheme = false ) {

		if ( $this->is_domainmapping_active() ) {
			global $wpdb,$blog_id;

			$mapped_key = 'wpmudev_mapped_domain_' . $blog_id;

			//* Check if the domain is mapped
			$mapped_domain = $this->object_cache_get( $mapped_key );
			if ( false === $mapped_domain ) {
				$mapped_domain = $wpdb->get_var( $wpdb->prepare( "SELECT domain FROM {$wpdb->base_prefix}domain_mapping WHERE blog_id = %d", $blog_id ) );
				$this->object_cache_set( $mapped_key, $mapped_domain, 3600 );
			}

			if ( ! empty( $mapped_domain ) ) {

				$scheme_key = 'wpmudev_mapped_scheme_' . $blog_id;

				//* Fetch scheme
				$mappedscheme = $this->object_cache_get( $scheme_key );
				if ( false === $mappedscheme ) {
					$mappedscheme = $wpdb->get_var( $wpdb->prepare( "SELECT scheme FROM {$wpdb->base_prefix}domain_mapping WHERE blog_id = %d", $blog_id ) );
					$this->object_cache_set( $scheme_key, $mappedscheme, 3600 );
				}

				if ( $mappedscheme === '1' ) {
					$scheme_full = 'https://';
					$scheme = 'https';
				} else {
					$scheme_full = 'http://';
					$scheme = 'http';
				}

				// Put it all together
				$url = trailingslashit( $scheme_full . $mapped_domain ) . ltrim( $path, '\/' );

				if ( ! $get_scheme ) {
					return $url;
				} else {
					return array( $url, $scheme );
				}
			}
		}

		return '';
	}

	/**
	 * Try to get an canonical URL when Donncha Domain Mapping is active.
	 *
	 * @param string $path The post relative path.
	 * @param bool $get_scheme Output array with scheme.
	 *
	 * @since 2.4.0
	 *
	 * @return string|array|void The unescaped URL, the scheme
	 */
	public function the_url_donncha_domainmap( $path, $get_scheme = false ) {

		if ( $this->is_donncha_domainmapping_active() ) {
			global $wpdb,$current_blog;

			$scheme = is_ssl() ? 'https' : 'http';

			//* This url is cached statically.
			$url = function_exists( 'domain_mapping_siteurl' ) ? domain_mapping_siteurl( false ) : false;

			$request_uri = '';

			if ( $url && $url != untrailingslashit( $scheme . '://' . $current_blog->domain . $current_blog->path ) ) {
				if ( ( defined( 'VHOST' ) && VHOST != 'yes' ) || ( defined( 'SUBDOMAIN_INSTALL' ) && SUBDOMAIN_INSTALL == false ) ) {
					$request_uri = str_replace( $current_blog->path, '/', $_SERVER[ 'REQUEST_URI' ] );
				}

				$url = trailingslashit( $url . $request_uri ) . ltrim( $path, '\/ ' );

				if ( ! $get_scheme ) {
					return $url;
				} else {
					return array( $url, $scheme );
				}
			}
		}

		return '';
	}

	/**
	 * Fetches og:image
	 *
	 * @uses get_header_image
	 *
	 * @param string $post_id the post ID
	 * @param string $image output url for image
	 * @param bool $escape Wether to escape the image url
	 *
	 * @since 2.2.1
	 *
	 * Applies filters string the_seo_framework_og_image_after_featured
	 * Applies filters string the_seo_framework_og_image_after_header
	 * @since 2.5.2
	 *
	 * @todo create options and upload area
	 */
	public function get_image( $post_id = '', $args = array(), $escape = true ) {

		if ( empty( $post_id ) )
			$post_id = $this->get_the_real_ID();

		if ( empty( $post_id ) )
			return '';

		$default_args = $this->parse_image_args( '', '', true );

		/**
		 * Parse args.
		 * @since 2.5.0
		 */
		if ( ! is_array( $args ) ) {
			//* Old style parameters are used. Doing it wrong.
			_doing_it_wrong( __CLASS__ . '::' . __FUNCTION__, 'Use $args = array() for parameters.', $this->the_seo_framework_version( '2.5.0' ) );
			$args = $default_args;
		} else if ( ! empty( $args ) ) {
			$args = $this->parse_image_args( $args, $default_args );
		} else {
			$args = $default_args;
		}

		/**
		 * Backwards compat with parse args
		 * @since 2.5.0
		 */
		if ( ! isset( $args['post_id'] ) )
			$args['post_id'] = $post_id;

		//* 0. Image from argument.
		$image = $args['image'];

		$check = (bool) empty( $args['disallowed'] );

		//* 1. Fetch image from featured
		if ( empty( $image ) && ( $check || ! in_array( 'featured', $args['disallowed'] ) ) )
			$image = $this->get_image_from_post_thumbnail( $args );

		//* 2. Fetch image from fallback filter 1
		if ( empty( $image ) )
			$image = (string) apply_filters( 'the_seo_framework_og_image_after_featured', '' );

		//* 3. Fallback: Get header image if exists
		if ( empty( $image ) && ( $check || ! in_array( 'header', $args['disallowed'] ) ) )
			$image = get_header_image();

		//* 4. Fetch image from fallback filter 1
		if ( empty( $image ) )
			$image = (string) apply_filters( 'the_seo_framework_og_image_after_header', '' );

		//* 5. Get the WP 4.3.0 Site Icon
		if ( empty( $image ) && ( $check || ! in_array( 'icon', $args['disallowed'] ) ) )
			$image = $this->site_icon();

		//* 6. If there still is no image, try the "site avatar" from WPMUdev Avatars
		if ( empty( $image ) && ( $check || ! in_array( 'wpmudev-avatars', $args['disallowed'] ) ) )
			$image = $this->get_image_from_wpmudev_avatars();

		/**
		 * Escape in Generation.
		 * @since 2.5.2
		 */
		if ( ! empty( $image ) && $escape )
			return esc_url( $image );

		return '';
	}

	/**
	 * Parse and sanitize image args.
	 *
	 * @param array $args required The passed arguments.
	 * @param array $defaults The default arguments.
	 * @param bool $get_defaults Return the default arguments. Ignoring $args.
	 *
	 * Applies filters the_seo_framework_og_image_args : {
	 *		@param string image The image url
	 *		@param mixed size The image size
	 *		@param bool icon Fetch Image icon
	 *		@param array attr Image attributes
	 *		@param array disallowed Disallowed image types : {
	 *			array (
	 * 				string 'featured'
	 * 				string 'header'
	 * 				string 'icon'
	 * 				string 'wpmudev-avatars'
	 *			)
	 * 		}
	 * }
	 * The image set in the filter will always be used as fallback
	 *
	 * @since 2.5.0
	 * @return array $args parsed args.
	 */
	public function parse_image_args( $args = array(), $defaults = array(), $get_defaults = false ) {

		//* Passing back the defaults reduces the memory usage.
		if ( empty( $defaults ) ) {
			$defaults = array(
				'post_id'	=> $this->get_the_real_ID(),
				'image'		=> '',
				'size'		=> 'full',
				'icon'		=> false,
				'attr'		=> array(),
				'disallowed' => array(),
			);

			//* @since 2.0.1
			$defaults = (array) apply_filters( 'the_seo_framework_og_image_args', $defaults, $args );
		}

		//* Return early if it's only a default args request.
		if ( $get_defaults )
			return $defaults;

		//* Array merge doesn't support sanitation. We're simply type casting here.
		$args['post_id'] 	= isset( $args['post_id'] ) 	? (int) $args['post_id'] 		: $defaults['post_id'];
		$args['image'] 		= isset( $args['image'] ) 		? (string) $args['image'] 		: $defaults['image'];
		$args['size'] 		= isset( $args['size'] ) 		? $args['size'] 				: $defaults['size']; // Mixed.
		$args['icon'] 		= isset( $args['icon'] ) 		? (bool) $args['icon'] 			: $defaults['icon'];
		$args['attr'] 		= isset( $args['attr'] ) 		? (array) $args['attr'] 		: $defaults['attr'];
		$args['disallowed'] = isset( $args['disallowed'] ) 	? (array) $args['disallowed'] 	: $defaults['disallowed'];

		return $args;
	}

	/**
	 * Fetches image from post thumbnail.
	 * Resizes the image between 1500px if bigger. Then it saves the image and
	 * Keeps dimensions relative.
	 *
	 * @param array $args Image arguments.
	 *
	 * @since 2.3.0
	 *
	 * @return string|null the image url.
	 */
	public function get_image_from_post_thumbnail( $args ) {

		if ( ! isset( $args['post_id'] ) )
			$args['post_id'] = $this->get_the_real_ID();

		$id = get_post_thumbnail_id( $args['post_id'] );

		$image = $id ? $this->parse_og_image( $id, $args ) : '';

		return $image;
	}

	/**
	 * Fetches images id's from WooCommerce gallery
	 *
	 * @staticvar array $ids The image ids
	 *
	 * @param array $args Image arguments.
	 *
	 * @since 2.5.0
	 *
	 * @return array The image URL's.
	 */
	public function get_image_from_woocommerce_gallery() {

		static $ids = null;

		if ( isset( $ids ) )
			return $ids;

		$attachment_ids = '';

		$post_id = $this->get_the_real_ID();

		if ( metadata_exists( 'post', $post_id, '_product_image_gallery' ) ) {
			$product_image_gallery = get_post_meta( $post_id, '_product_image_gallery', true );

			$attachment_ids = array_filter( explode( ',', $product_image_gallery ) );
		}

		return $ids = $attachment_ids;
	}

	/**
	 * Parses OG image to correct size
	 *
	 * @staticvar string $called Checks if image ID has already been fetched (to prevent duplicate output on WooCommerce).
	 *
	 * @param int $id The attachment ID.
	 * @param array $args The image args
	 *
	 * @since 2.5.0
	 *
	 * @return string|empty Parsed image url or empty if already called
	 */
	public function parse_og_image( $id, $args = array() ) {

		//* Don't do anything if $id isn't given.
		if ( ! isset( $id ) || empty( $id ) )
			return;

		static $called = array();

		if ( isset( $called[$id] ) )
			return '';

		if ( empty( $args ) )
			$args = $this->parse_image_args( '', '', true );

		$src = wp_get_attachment_image_src( $id, $args['size'], $args['icon'], $args['attr'] );

		$i = $src[0]; // Source URL
		$w = $src[1]; // Width
		$h = $src[2]; // Height

		//* Prefered 1500px, resize it
		if ( $w > 1500 || $h > 1500 ) {

			if ( $w == $h ) {
				//* Square
				$w = 1500;
				$h = 1500;
			} else if ( $w > $h ) {
				//* Landscape
				$dev = $w / 1500;

				$h = $h / $dev;

				$h = round( $h );
				$w = 1500;
			} else if ( $h > $w ) {
				//* Portrait
				$dev = $h / 1500;

				$w = $w / $dev;

				$w = round( $w );
				$h = 1500;
			}

			// Get path of image and load it into the wp_get_image_editor
			$i_file_path = get_attached_file( $id );

			$i_file_old_name	= basename( get_attached_file( $id ) );
			$i_file_ext			= pathinfo( $i_file_path, PATHINFO_EXTENSION );

			if ( ! empty( $i_file_ext ) ) {
				$i_file_dir_name 	= pathinfo( $i_file_path, PATHINFO_DIRNAME );
				// Add trailing slash
				$i_file_dir_name	.= ( substr( $i_file_dir_name, -1 ) == '/' ? '' : '/' );

				$i_file_file_name 	= pathinfo( $i_file_path, PATHINFO_FILENAME );

				// Yes I know, I should use generate_filename, but it's slower.
				// Will look at that later. This is already 100 lines of correctly working code.
				$new_image_dirfile 	= $i_file_dir_name . $i_file_file_name . '-' . $w . 'x' . $h . '.' . $i_file_ext;

				// This should work on multisite too.
				$upload_dir 	= wp_upload_dir();
				$upload_url 	= $upload_dir['baseurl'];
				$upload_basedir = $upload_dir['basedir'];

				// Dub this $new_image
				$new_image_url = preg_replace( '/' . preg_quote( $upload_basedir, '/' ) . '/', $upload_url, $new_image_dirfile );

				// Generate file if it doesn't exists yet.
				if ( ! file_exists( $new_image_dirfile ) ) {

					$image_editor = wp_get_image_editor( $i_file_path );

					if ( ! is_wp_error( $image_editor ) ) {
						$image_editor->resize( $w, $h, false );
						$image_editor->set_quality( 70 ); // Let's save some bandwidth, Facebook compresses it even further anyway.
						$image_editor->save( $new_image_dirfile );
					}
				}

				$i = $new_image_url;
			}
		}

		return $called[$id] = $i;
	}

	/**
	 * Fetches site image from WPMUdev Avatars.
	 *
	 * @since 2.3.0
	 *
	 * @return string|null the image url.
	 */
	public function get_image_from_wpmudev_avatars() {

		$image = '';

		$plugins = array( 'classes' => array( 'Avatars' ) );

		if ( $this->detect_plugin( $plugins ) ) {
			global $ms_avatar;

			$path = '';

			if ( isset( $ms_avatar->blog_avatar_dir ) ) {
				global $blog_id;

				$size = '256';

				if ( method_exists( $ms_avatar, 'encode_avatar_folder' ) ) {
					$file = $ms_avatar->blog_avatar_dir . $ms_avatar->encode_avatar_folder( $blog_id ) . '/blog-' . $blog_id . '-' . $size . '.png';
				} else {
					return '';
				}

				if ( false !== $file && is_file( $file ) ) {

					$upload_dir = wp_upload_dir();
					$upload_url = $upload_dir['baseurl'];

					/**
					 * Isn't there a more elegant core option? =/
					 * I'm basically backwards enginering the wp_upload_dir
					 * function to get the base url without /sites/blogid or /blogid.
					 */
					if ( is_multisite() && ! ( is_main_network() && is_main_site() && defined( 'MULTISITE' ) ) ) {
						if ( ! get_site_option( 'ms_files_rewriting' ) ) {
							if ( defined( 'MULTISITE' ) ) {
								$upload_url = str_replace( '/sites/' . $blog_id, '', $upload_url );
							} else {
								// This should never run.
								$upload_url = str_replace( '/' . $blog_id, '', $upload_url );
							}
						} else if ( defined( 'UPLOADS' ) && ! ms_is_switched() ) {
							/**
							 * Special cases. UPLOADS is defined.
							 * Where UPLOADS is defined AND we're on the main blog AND
							 * WPMUdev avatars is used AND file is uploaded on main blog AND
							 * no header image is set AND no favicon is uploaded.
							 *
							 * So yeah: I'm not sure what to do here so I'm just gonna fall back to default.
							 * I'll wait for a bug report.
							 */
							$upload_url = str_replace( '/sites/' . $blog_id, '', $upload_url );
						}
					}

					// I think I should've used get_site_url...
					$avatars_url = trailingslashit( trailingslashit( $upload_url ) . basename( dirname( $ms_avatar->blog_avatar_dir ) ) );
					$path = preg_replace( '/' . preg_quote( dirname( $ms_avatar->blog_avatar_dir ) . '/', '/') . '/', $avatars_url, $file );

				}
			}

			$image = ! empty( $path ) ? $path : '';
		}

		return $image;
	}

	/**
	 * Fetches site icon brought in WordPress 4.3.0
	 *
	 * @param string $size 	The icon size, accepts 'full' and pixel values
	 * @since 2.2.1
	 *
	 * @return string url site icon, not escaped.
	 */
	public function site_icon( $size = 'full' ) {

		$icon = '';

		if ( function_exists( 'has_site_icon' ) && $this->wp_version( '4.3.0', '>=' ) ) {
			if ( $size == 'full' ) {
				$site_icon_id = get_option( 'site_icon' );

				$url_data = '';

				if ( $site_icon_id ) {
					$url_data = wp_get_attachment_image_src( $site_icon_id, $size );
				}

				$icon = $url_data ? $url_data[0] : '';
			} else if ( is_int( $size ) ) {
				$icon = get_site_icon_url( $size );
			}
		}
		return $icon;
	}

	/**
	 * Output the `index`, `follow`, `noodp`, `noydir`, `noarchive` robots meta code in array
	 *
	 * @since 2.2.2
	 *
	 * @uses genesis_get_seo_option()   Get SEO setting value.
	 * @uses genesis_get_custom_field() Get custom field value.
	 *
	 * @global WP_Query $wp_query Query object.
	 *
	 * @return array|null robots
	 */
	public function robots_meta() {
		global $wp_query;

		$query_vars = is_object( $wp_query ) ? (array) $wp_query->query_vars : '';
		$paged = is_array( $query_vars ) ? $query_vars["paged"] : '';

		//* Defaults
		$meta = array(
			'noindex'   => $this->get_option( 'site_noindex' ) ? 'noindex' : '',
			'nofollow'  => $this->get_option( 'site_nofollow' ) ? 'nofollow' : '',
			'noarchive' => $this->get_option( 'site_noarchive' ) ? 'noarchive' : '',
			'noodp'     => $this->get_option( 'noodp' ) ? 'noodp' : '',
			'noydir'    => $this->get_option( 'noydir' ) ? 'noydir' : '',
		);

		/**
		 * Check the Robots SEO settings, set noindex for paged archives.
		 * @since 2.2.4
		 */
		if ( (int) $paged > (int) 1 )
			$meta['noindex'] = $this->get_option( 'paged_noindex' ) ? 'noindex' : $meta['noindex'];

		/**
		 * Check if archive is empty, set noindex for those.
		 *
		 * @todo maybe create option
		 * @since 2.2.8
		 */
		if ( isset( $wp_query->post_count ) && $wp_query->post_count === (int) 0 )
			$meta['noindex'] = 'noindex';

		//* Check home page SEO settings, set noindex, nofollow and noarchive
		if ( is_front_page() ) {
			$meta['noindex']   = $this->get_option( 'homepage_noindex' ) ? 'noindex' : $meta['noindex'];
			$meta['nofollow']  = $this->get_option( 'homepage_nofollow' ) ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = $this->get_option( 'homepage_noarchive' ) ? 'noarchive' : $meta['noarchive'];
		}

		if ( is_category() ) {
			$term = $wp_query->get_queried_object();

			$meta['noindex']   = $term->admeta['noindex'] ? 'noindex' : $meta['noindex'];
			$meta['nofollow']  = $term->admeta['nofollow'] ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = $term->admeta['noarchive'] ? 'noarchive' : $meta['noarchive'];

			if ( empty( $meta['noindex'] ) )
				$meta['noindex'] = $this->get_option( 'category_noindex' ) ? 'noindex' : $meta['noindex'];

			if ( empty( $meta['nofollow'] ) )
				$meta['nofollow'] = $this->get_option( 'category_nofollow' ) ? 'nofollow' : $meta['nofollow'];

			if ( empty( $meta['noarchive'] ) )
				$meta['noarchive'] = $this->get_option( 'category_noindex' ) ? 'noarchive' : $meta['noarchive'];

			$flag = $term->admeta['saved_flag'] != '0' ? true : false;

			if ( ! $flag && isset( $term->meta ) ) {
				if ( empty( $meta['noindex'] ) )
					$meta['noindex'] = $term->meta['noindex'] ? 'noindex' : $meta['noindex'];

				if ( empty( $meta['nofollow'] ) )
					$meta['nofollow'] = $term->meta['nofollow'] ? 'nofollow' : $meta['nofollow'];

				if ( empty( $meta['noarchive'] ) )
					$meta['noarchive'] = $term->meta['noarchive'] ? 'noarchive' : $meta['noarchive'];
			}
		}

		if ( is_tag() ) {
			$term = $wp_query->get_queried_object();

			$meta['noindex']   = $term->admeta['noindex'] ? 'noindex' : $meta['noindex'];
			$meta['nofollow']  = $term->admeta['nofollow'] ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = $term->admeta['noarchive'] ? 'noarchive' : $meta['noarchive'];

			if ( empty( $meta['noindex'] ) )
				$meta['noindex'] = $this->get_option( 'tag_noindex' ) ? 'noindex' : $meta['noindex'];

			if ( empty( $meta['nofollow'] ) )
				$meta['nofollow'] = $this->get_option( 'tag_nofollow' ) ? 'nofollow' : $meta['nofollow'];

			if ( empty( $meta['noarchive'] ) )
				$meta['noarchive'] = $this->get_option( 'tag_noindex' ) ? 'noarchive' : $meta['noarchive'];

			$flag = $term->admeta['saved_flag'] != '0' ? true : false;

			if ( ! $flag && isset( $term->meta ) ) {
				if ( empty( $meta['noindex'] ) )
					$meta['noindex'] = $term->meta['noindex'] ? 'noindex' : $meta['noindex'];

				if ( empty( $meta['nofollow'] ) )
					$meta['nofollow'] = $term->meta['nofollow'] ? 'nofollow' : $meta['nofollow'];

				if ( empty( $meta['noarchive'] ) )
					$meta['noarchive'] = $term->meta['noarchive'] ? 'noarchive' : $meta['noarchive'];
			}
		}

		// Is custom Taxonomy page. But not a category or tag. Should've recieved specific term SEO settings.
		if ( is_tax() ) {
			$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );

			$meta['noindex']   = $term->admeta['noindex'] ? 'noindex' : $meta['noindex'];
			$meta['nofollow']  = $term->admeta['nofollow'] ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = $term->admeta['noarchive'] ? 'noarchive' : $meta['noarchive'];
		}

		if ( is_author() ) {

			/**
			 * @todo really, @todo. External plugin?
			 */
			/*
			$meta['noindex']   = get_the_author_meta( 'noindex', (int) get_query_var( 'author' ) ) ? 'noindex' : $meta['noindex'];
			$meta['nofollow']  = get_the_author_meta( 'nofollow', (int) get_query_var( 'author' ) ) ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = get_the_author_meta( 'noarchive', (int) get_query_var( 'author' ) ) ? 'noarchive' : $meta['noarchive'];
			*/

			$meta['noindex'] = $this->get_option( 'author_noindex' ) ? 'noindex' : $meta['noindex'];
			$meta['nofollow'] = $this->get_option( 'author_nofollow' ) ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = $this->get_option( 'author_noarchive' ) ? 'noarchive' : $meta['noarchive'];
		}

		if ( is_date() ) {
			$meta['noindex'] = $this->get_option( 'date_noindex' ) ? 'noindex' : $meta['noindex'];
			$meta['nofollow'] = $this->get_option( 'date_nofollow' ) ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = $this->get_option( 'date_noarchive' ) ? 'noarchive' : $meta['noarchive'];
		}

		if ( is_search() ) {
			$meta['noindex'] = $this->get_option( 'search_noindex' ) ? 'noindex' : $meta['noindex'];
			$meta['nofollow'] = $this->get_option( 'search_nofollow' ) ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = $this->get_option( 'search_noarchive' ) ? 'noarchive' : $meta['noarchive'];
		}

		if ( is_attachment() ) {
			$meta['noindex']   = $this->get_option( 'attachment_noindex' ) ? 'noindex' : $meta['noindex'];
			$meta['nofollow']  = $this->get_option( 'attachment_nofollow' ) ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = $this->get_option( 'attachment_noarchive' ) ? 'noarchive' : $meta['noarchive'];
		}

		if ( $this->is_singular() ) {
			$meta['noindex'] = $this->get_custom_field( '_genesis_noindex' ) ? 'noindex' : $meta['noindex'];
			$meta['nofollow'] = $this->get_custom_field( '_genesis_nofollow' ) ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = $this->get_custom_field( '_genesis_noarchive' ) ? 'noarchive' : $meta['noarchive'];
		}

		//* Strip empty array items
		$meta = array_filter( $meta );

		return $meta;
	}

	/**
	 * Generates shortlink url
	 *
	 * @since 2.2.2
	 *
	 * @param int $post_id The post ID
	 * @return string|null Escaped site Shortlink URL
	 */
	public function get_shortlink( $post_id = 0 ) {

		if ( $this->get_option( 'shortlink_tag' ) ) {

			$path = null;

			if ( $this->is_singular( $post_id ) ) {

				if ( 0 == $post_id )
					$post_id = $this->get_the_real_ID();

				if ( ! empty( $post_id ) ) {
					if ( $this->is_static_frontpage( $post_id ) ) {
						$path = '';
					} else {
						$path = '?p=' . $post_id;
					}
				}
			} else if ( ! is_front_page() ) {
				$object = get_queried_object();

				if ( is_category() ) {
					$id = $object->term_id;
					$path = '?cat=' . $id;
				}

				if ( is_tag() ) {
					$name = $object->name;
					$path = '?tag=' . $name;
				}

				if ( is_date() ) {
					// This isn't exactly "short" for a shortlink...
					$year = get_query_var( 'year' );
					$month = get_query_var( 'monthnum' ) ? '&monthnum=' . get_query_var( 'monthnum' ) : '';
					$day = get_query_var( 'day' ) ? '&day=' . get_query_var( 'day' ) : '';

					$path = '?year=' . $year . $month . $day;
				}

				if ( is_author() ) {
					$id = $object->ID;
					$path = '?author=' . $id;
				}

				if ( is_tax() ) {
					$id = $object->ID;
					$path = '?taxonomy=' . $id;
				}

				if ( empty( $path ) ) {
					$id = isset( $object->ID ) ? $object->ID : 0;

					if ( ! empty( $id ) )
						$path = '?p=' . $id;
				}

			} else {
				//* Home page
				$path = '';
			}

			if ( isset( $path ) ) {

				$home_url = get_option( 'home' );
				$scheme = is_ssl() ? 'https' : 'http';

				if ( empty( $path ) ) {
					//* Home url.
					$url = $this->set_url_scheme( $home_url, $scheme );
					$url = user_trailingslashit( $url );
				} else {
					//* Everything else.
					$url = trailingslashit( $home_url ) . $path;
					$url = $this->set_url_scheme( $url, $scheme );
				}

				return esc_url_raw( $url );
			}
		}

		return '';
	}

	/**
	 * Generates Previous and Next links
	 *
	 * @since 2.2.4
	 *
	 * @param string $prev_next Previous or next page link
	 * @param int $post_id The post ID
	 *
	 * @return string|null Escaped site Pagination URL
	 */
	public function get_paged_url( $prev_next = 'next', $post_id = 0 ) {

		if ( ! $this->get_option( 'prev_next_posts' ) && ! $this->get_option( 'prev_next_archives' ) )
			return '';

		global $wp_query;

		$prev = '';
		$next = '';

		if ( $this->get_option( 'prev_next_archives' ) && ! is_singular() ) {

			$paged = get_query_var( 'paged' ) ? (int) get_query_var( 'paged' ) : 1;

			if ( $prev_next == 'prev' )
				$prev = $paged > 1 ? get_previous_posts_page_link() : $prev;

			if ( $prev_next == 'next' )
				$next = $paged < $wp_query->max_num_pages ? get_next_posts_page_link() : $next;

		} else if ( $this->get_option( 'prev_next_posts' ) && is_singular() ) {

			$page  = (int) get_query_var( 'page' );

			$numpages = substr_count( $wp_query->post->post_content, '<!--nextpage-->' ) + 1;

			if ( $numpages && ! $page ) {
				$page = 1;
			}

			if ( $prev_next == 'prev' ) {
				if ( $page > 1 ) {
					$prev = (string) $this->get_paged_post_url( $page - 1, $post_id, 'prev' );
				}
			}

			if ( $prev_next == 'next' ) {
				if ( $page < $numpages ) {
					$next = (string) $this->get_paged_post_url( $page + 1, $post_id, 'next' );
				}
			}

		}

		if ( ! empty( $prev ) )
			return esc_url_raw( $prev );

		if ( ! empty( $next ) )
			return esc_url_raw( $next );

		return '';
	}

	/**
	 * Return the special URL of a paged post.
	 *
	 * Taken from _wp_link_page() in WordPress core, but instead of anchor markup, just return the URL.
	 * Also adds WPMUdev Domain Mapping support and is optimized for speed.
	 *
	 * @uses $this->the_url_from_cache();
	 * @since 2.2.4
	 *
	 * @param int $i The page number to generate the URL from.
	 * @param int $post_id The post ID
	 * @param string $pos Which url to get, accepts next|prev
	 *
	 * @return string Unescaped URL
	 */
	public function get_paged_post_url( $i, $post_id = 0, $pos = '' ) {

		$from_option = false;

		if ( $i === (int) 1 ) {
			$url = $this->the_url_from_cache( '', $post_id, true, $from_option );
		} else {
			$post = get_post( $post_id );

			/**
			 * Fix the url.
			 *
			 * @since 2.2.5
			 */
			if ( $i >= (int) 2 ) {
				//* Fix adding pagination url.

				$urlfromcache = $this->the_url_from_cache( '', $post_id, false, $from_option );

				// Calculate current page number.
				$int_current = 'next' == $pos ? $i -1 : $i + 1;
				$string_current = (string) $int_current;

				if ( $i === (int) 1 ) {
					//* We're returning to the first page. Continue normal behavior.
					$urlfromcache = $urlfromcache;
				} else {
					//* We're adding a page.
					$last_occurence = strrpos( $urlfromcache, '/' . $string_current . '/' );

					if ( $last_occurence !== false )
						$urlfromcache = substr_replace( $urlfromcache, '/', $last_occurence, strlen( '/' . $string_current . '/' ) );
				}
			} else {
				$urlfromcache = $this->the_url_from_cache( '', $post_id, false, $from_option );
			}

			if ( '' == get_option( 'permalink_structure' ) || in_array( $post->post_status, array( 'draft', 'pending' ) ) ) {
				$url = add_query_arg( 'page', $i, $urlfromcache );
			} else if ( 'page' == get_option( 'show_on_front' ) && get_option( 'page_on_front' ) == $post->ID ) {
				global $wp_rewrite;

				$url = trailingslashit( $urlfromcache ) . user_trailingslashit( "$wp_rewrite->pagination_base/" . $i, 'single_paged' );
			} else {
				$url = trailingslashit( $urlfromcache ) . user_trailingslashit( $i, 'single_paged' );
			}
		}

		return $url;
	}

	/**
	 * Generate LD+Json search helper.
	 *
	 * @since 2.2.8
	 *
	 * @return escaped LD+json search helper string.
	 * @TODO Create option for output.
	 */
	public function ld_json_search() {

		/**
		 * Applies filters the_seo_framework_json_search_output
		 * @since 2.3.9
		 */
		$output = (bool) apply_filters( 'the_seo_framework_json_search_output', true );

		if ( true !== $output )
			return '';

		$context = json_encode( 'http://schema.org' );
		$webtype = json_encode( 'WebSite' );
		$url = json_encode( esc_url( home_url( '/' ) ) );
		$name = json_encode( $this->get_blogname() );
		$alternatename = $name;
		$actiontype = json_encode( 'SearchAction' );

		// Remove trailing quote and add it back.
		$target = mb_substr( json_encode( esc_url( home_url( '/?s=' ) ) ), 0, -1 ) . '{search_term_string}"';

		$queryaction = json_encode( 'required name=search_term_string' );

		$json = sprintf( '{"@context":%s,"@type":%s,"url":%s,"name":%s,"alternateName":%s,"potentialAction":{"@type":%s,"target":%s,"query-input":%s}}', $context, $webtype, $url, $name, $alternatename, $actiontype, $target, $queryaction );

		return $json;
	}

	/**
	 * Generate LD+Json breadcrumb helper.
	 *
	 * @since 2.4.2
	 *
	 * @return escaped LD+json search helper string.
	 * @TODO Create option for output.
	 */
	public function ld_json_breadcrumbs() {

		/**
		 * Applies filters the_seo_framework_json_breadcrumb_output
		 * @since 2.4.2
		 */
		$output = (bool) apply_filters( 'the_seo_framework_json_breadcrumb_output', true );

		if ( true !== $output )
			return '';

		//* Used to count ancestors and categories.
		$count = 0;

		$output = '';

		if ( is_single() ) {
			//* Get categories.

			$post_id = $this->get_the_real_ID();

			$r = is_object_in_term( $post_id, 'category', '' );

			if ( is_wp_error( $r ) || ! $r )
				return '';

			$cats = wp_get_object_terms( $post_id, 'category', array( 'fields' => 'all_with_object_id', 'orderby' => 'parent' ) );

			if ( is_wp_error( $cats ) || empty( $cats ) )
				return '';

			$cat_ids = array();
			$kittens = array();

			//* Fetch cats children id's, if any.
			foreach ( $cats as $cat ) {
				//* The category objects. The cats.
				$cat_id = $cat->term_id;

				// Check if they have kittens.
				$children = get_term_children( $cat_id, $cat->taxonomy );

				//* No need to fetch them again, save object in the array.
				$cat_obj[$cat_id] = $cat;

				//* Save children id's as kittens.
				$kittens[$cat_id] = $children;
			}

			$todo = array();
			$trees = array();

			/**
			 * Build category ID tree.
			 * Sort by parents with children ($trees). These are recursive, 3+ item scripts.
			 * Sort by parents without children ($todo). These are singular 2 item scripts.
			 */
			foreach ( $kittens as $parent => $kitten ) {
				if ( ! empty( $kitten ) ) {
					if ( 1 == count( $kitten ) ) {
						$trees[] = array( $kitten[0], $parent );
					} else {
						//* @TODO, this is very, very complicated. Requires multiple loops.
						$trees[] = array();
					}
				} else {
					$todo[] = $parent;
				}
			}

			//* Remove Duplicates from $todo by comparing to $tree
			foreach ( $todo as $key => $value ) {
				foreach ( $trees as $tree ) {
					if ( $this->in_array( $value, $tree ) )
						unset( $todo[$key] );
				}
			}

			$context = json_encode( 'http://schema.org' );
			$context_type = json_encode( 'BreadcrumbList' );
			$item_type = json_encode( 'ListItem' );

			$items = '';

			foreach ( $trees as $tree ) {
				if ( ! empty( $tree ) ) {

					$tree = array_reverse( $tree );

					foreach ( $tree as $position => $parent_id ) {
						$pos = $position + 2;

						$cat = isset( $cat_obj[$parent_id] ) ? $cat_obj[$parent_id] : get_term_by( 'id', $parent_id, 'category', OBJECT, 'raw' );

						$id = json_encode( $this->the_url( '', '', array( 'get_custom_field' => false, 'external' => true, 'is_term' => true, 'term' => $cat ) ) );

						$custom_field_name = isset( $cat->admeta['doctitle'] ) ? $cat->admeta['doctitle'] : '';
						$cat_name = ! empty( $custom_field_name ) ? $custom_field_name : $cat->name;
						$name = json_encode( $cat_name );

						$items .= sprintf( '{"@type":%s,"position":%s,"item":{"@id":%s,"name":%s}},', $item_type, (string) $pos, $id, $name );
					}

					if ( ! empty( $items ) ) {

						$items = $this->ld_json_breadcrumb_first( $item_type ) . $items . $this->ld_json_breadcrumb_last( $item_type, $pos, $post_id );

						//* Put it all together.
						$breadcrumbhelper = sprintf( '{"@context":%s,"@type":%s,"itemListElement":[%s]}', $context, $context_type, $items );
						$output .= "<script type='application/ld+json'>" . $breadcrumbhelper . "</script>" . "\r\n";
					}
				}
			}

			//* For each of the todo items, create a separated script.
			if ( ! empty( $todo ) ) {
				foreach ( $todo as $tid ) {

					$items = '';
					$cat = get_term_by( 'id', $tid, 'category', OBJECT, 'raw' );

					if ( '1' !== $cat->admeta['noindex'] ) {

						if ( empty( $children ) ) {
							// The position of the current item is always static here.
							$pos = '2';
							$id = json_encode( $this->the_url( '', '', array( 'get_custom_field' => false, 'is_term' => true, 'term' => $cat ) ) ); // Why not external???

							$custom_field_name = isset( $cat->admeta['doctitle'] ) ? $cat->admeta['doctitle'] : '';
							$cat_name = ! empty( $custom_field_name ) ? $custom_field_name : $cat->name;
							$name = json_encode( $cat_name );

							$items .= sprintf( '{"@type":%s,"position":%s,"item":{"@id":%s,"name":%s}},', $item_type, (string) $pos, $id, $name );
						}

						if ( ! empty( $items ) ) {

							$items = $this->ld_json_breadcrumb_first( $item_type ) . $items . $this->ld_json_breadcrumb_last( $item_type, $pos, $post_id );

							//* Put it all together.
							$breadcrumbhelper = sprintf( '{"@context":%s,"@type":%s,"itemListElement":[%s]}', $context, $context_type, $items );
							$output .= "<script type='application/ld+json'>" . $breadcrumbhelper . "</script>" . "\r\n";
						}
					}
				}
			}
		} else if ( ! is_front_page() && is_page() ) {
			//* Get ancestors.
			$page_id = $this->get_the_real_ID();

			$parents = get_post_ancestors( $page_id );

			if ( ! empty( $parents ) ) {

				$context = json_encode( 'http://schema.org' );
				$context_type = json_encode( 'BreadcrumbList' );
				$item_type = json_encode( 'ListItem' );

				$items = '';

				$parents = array_reverse( $parents );

				foreach ( $parents as $position => $parent_id ) {
					$pos = $position + 2;

					$id = json_encode( $this->the_url( '', $parent_id, array( 'get_custom_field' => false, 'external' => true ) ) );

					$custom_field_name = $this->get_custom_field( '_genesis_title', $parent_id );
					$parent_name = ! empty( $custom_field_name ) ? $custom_field_name : $this->title( '', '', '', array( 'term_id' => $parent_id, 'get_custom_field' => false, 'placeholder' => true, 'notagline' => true, 'description_title' => true ) );

					$name = json_encode( $parent_name );

					$items .= sprintf( '{"@type":%s,"position":%s,"item":{"@id":%s,"name":%s}},', $item_type, (string) $pos, $id, $name );
				}

				if ( ! empty( $items ) ) {

					$items = $this->ld_json_breadcrumb_first( $item_type ) . $items . $this->ld_json_breadcrumb_last( $item_type, $pos, $page_id );

					//* Put it all together.
					$breadcrumbhelper = sprintf( '{"@context":%s,"@type":%s,"itemListElement":[%s]}', $context, $context_type, $items );
					$output = "<script type='application/ld+json'>" . $breadcrumbhelper . "</script>" . "\r\n";
				}
			}
		}

		return $output;
	}

	/**
	 * Return home page item for LD Json Breadcrumbs.
	 *
	 * @staticvar string $first_item.
	 *
	 * @since 2.4.2
	 *
	 * @param string $item_type the breadcrumb item type.
	 *
	 * @return string Home Breadcrumb item
	 */
	public function ld_json_breadcrumb_first( $item_type ) {

		static $first_item = null;

		if ( ! isset( $first_item ) ) {

			if ( ! isset( $item_type ) )
				$item_type = json_encode( 'ListItem' );

			$id = json_encode( $this->the_home_url_from_cache() );

			$home_title = $this->get_option( 'homepage_title' );

			if ( $home_title ) {
				$custom_name = $home_title;
			} else if ( 'page' == get_option( 'show_on_front' ) ) {
				$home_id = (int) get_option( 'page_on_front' );

				$custom_name = $this->get_custom_field( '_genesis_title', $home_id );
				$custom_name = $custom_name ? $custom_name : $this->get_blogname();
			} else {
				$custom_name = $this->get_blogname();
			}

			$custom_name = json_encode( $custom_name );

			//* Add trailing comma.
			$first_item = sprintf( '{"@type":%s,"position":%s,"item":{"@id":%s,"name":%s}},', $item_type, '1', $id, $custom_name );
		}

		return $first_item;
	}

	/**
	 * Return current page item for LD Json Breadcrumbs.
	 *
	 * @staticvar string $last_item.
	 *
	 * @since 2.4.2
	 *
	 * @param string $item_type the breadcrumb item type.
	 * @param int $pos Last known position.
	 * @param int $post_id The current Post ID
	 *
	 * @staticvar string $type The breadcrumb item type.
	 * @staticvar string $id The current post/page/archive url.
	 * @staticvar string $name The current post/page/archive title.
	 *
	 * @return string Lat Breadcrumb item
	 */
	public function ld_json_breadcrumb_last( $item_type, $pos, $post_id ) {

		// 2 (becomes 3) holds mostly true for single term items. This shouldn't run anyway. Pos should always be provided.
		if ( ! isset( $pos ) )
			$pos = '2';

		if ( ! isset( $item_type ) ) {
			static $type = null;

			if ( ! isset( $type ) )
				$type = json_encode( 'ListItem' );

			$item_type = $type;
		}

		if ( ! isset( $post_id ) || empty( $post_id ) )
			$post_id = $this->get_the_real_ID();

		//* Add current page.
		$pos = $pos + 1;

		static $id = null;
		static $name = null;

		if ( ! isset( $id ) )
			$id = json_encode( $this->the_url_from_cache() );

		if ( ! isset( $name ) ) {
			$custom_field = $this->get_custom_field( '_genesis_title', $post_id );
			$name = $custom_field ? $custom_field : $this->title( '', '', '', array( 'term_id' => $post_id, 'placeholder' => true, 'notagline' => true, 'description_title' => true ) );
			$name = json_encode( $name );
		}

		$last_item = sprintf( '{"@type":%s,"position":%s,"item":{"@id":%s,"name":%s}}', $item_type, (string) $pos, $id, $name );

		return $last_item;
	}

	/**
	 * Return LD+Json Knowledge Graph helper.
	 *
	 * @since 2.2.8
	 *
	 * @return null|escaped LD+json Knowledge Graph helper string.
	 * @todo transient cache this.
	 */
	public function ld_json_knowledge() {

		if ( ! $this->get_option( 'knowledge_output' ) )
			return '';

		$knowledge_type = $this->get_option( 'knowledge_type' );

		/**
		 * Forgot to add this.
		 * @since 2.4.3
		 */
		$knowledge_name = $this->get_option( 'knowledge_name' );
		$knowledge_name = ! empty( $knowledge_name ) ? $knowledge_name : $this->get_blogname();

		$context = json_encode( 'http://schema.org' );
		$type = json_encode( ucfirst( $knowledge_type ) );
		$name = json_encode( $knowledge_name );
		$url = json_encode( esc_url( home_url( '/' ) ) );

		$logo = '';

		if ( $this->get_option( 'knowledge_logo' ) && 'organization' === $knowledge_type ) {
			$icon = $this->site_icon();

			if ( ! empty( $icon ) ) {
				$logourl = esc_url_raw( $icon );

				//* Add trailing comma
				$logo = '"logo":' . json_encode( $logourl ) . ',';
			}
		}

		/**
		 * Fetch option names
		 *
		 * @uses filter the_seo_framework_json_options
		 */
		$options = (array) apply_filters( 'the_seo_framework_json_options', array(
			'knowledge_facebook',
			'knowledge_twitter',
			'knowledge_gplus',
			'knowledge_instagram',
			'knowledge_youtube',
			'knowledge_linkedin',
			'knowledge_pinterest',
			'knowledge_soundcloud',
			'knowledge_tumblr',
		) );

		$sameurls = '';
		$comma = ',';

		//* Put the urls together from the options.
		if ( is_array( $options ) ) {
			foreach ( $options as $option ) {
				$the_option = $this->get_option( $option );

				if ( '' !== $the_option )
					$sameurls .= json_encode( $the_option ) . $comma;
			}
		}

		//* Remove trailing comma
		$sameurls = rtrim( $sameurls, $comma );
		$json = '';

		if ( ! empty( $sameurls ) )
			$json = sprintf( '{"@context":%s,"@type":%s,"name":%s,"url":%s,%s"sameAs":[%s]}', $context, $type, $name, $url, $logo, $sameurls );

		return $json;
	}

	/**
	 * Get the archive Title.
	 *
	 * WordPress core function 4.1.0
	 *
	 * @since 2.3.6
	 */
	public function get_the_archive_title() {

		//* Return WP Core function.
		if ( function_exists( 'get_the_archive_title' ) )
			return get_the_archive_title();

		if ( is_category() ) {
			/* translators: Front-end output. */
			$title = sprintf( __( 'Category: %s', 'autodescription' ), single_cat_title( '', false ) );
		} elseif ( is_tag() ) {
			/* translators: Front-end output. */
			$title = sprintf( __( 'Tag: %s', 'autodescription' ), single_tag_title( '', false ) );
		} elseif ( is_author() ) {
			/* translators: Front-end output. */
			$title = sprintf( __( 'Author: %s', 'autodescription' ), '<span class="vcard">' . get_the_author() . '</span>' );
		} elseif ( is_year() ) {
			/* translators: Front-end output. */
			$title = sprintf( __( 'Year: %s', 'autodescription' ), get_the_date( _x( 'Y', 'yearly archives date format', 'autodescription' ) ) );
		} elseif ( is_month() ) {
			/* translators: Front-end output. */
			$title = sprintf( __( 'Month: %s', 'autodescription' ), get_the_date( _x( 'F Y', 'monthly archives date format', 'autodescription' ) ) );
		} elseif ( is_day() ) {
			/* translators: Front-end output. */
			$title = sprintf( __( 'Day: %s', 'autodescription' ), get_the_date( _x( 'F j, Y', 'daily archives date format', 'autodescription' ) ) );
		} elseif ( is_tax( 'post_format' ) ) {
			if ( is_tax( 'post_format', 'post-format-aside' ) ) {
				/* translators: Front-end output. */
				$title = _x( 'Asides', 'post format archive title', 'autodescription' );
			} elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) {
				/* translators: Front-end output. */
				$title = _x( 'Galleries', 'post format archive title', 'autodescription' );
			} elseif ( is_tax( 'post_format', 'post-format-image' ) ) {
				/* translators: Front-end output. */
				$title = _x( 'Images', 'post format archive title', 'autodescription' );
			} elseif ( is_tax( 'post_format', 'post-format-video' ) ) {
				/* translators: Front-end output. */
				$title = _x( 'Videos', 'post format archive title', 'autodescription' );
			} elseif ( is_tax( 'post_format', 'post-format-quote' ) ) {
				/* translators: Front-end output. */
				$title = _x( 'Quotes', 'post format archive title', 'autodescription' );
			} elseif ( is_tax( 'post_format', 'post-format-link' ) ) {
				/* translators: Front-end output. */
				$title = _x( 'Links', 'post format archive title', 'autodescription' );
			} elseif ( is_tax( 'post_format', 'post-format-status' ) ) {
				/* translators: Front-end output. */
				$title = _x( 'Statuses', 'post format archive title', 'autodescription' );
			} elseif ( is_tax( 'post_format', 'post-format-audio' ) ) {
				/* translators: Front-end output. */
				$title = _x( 'Audio', 'post format archive title', 'autodescription' );
			} elseif ( is_tax( 'post_format', 'post-format-chat' ) ) {
				/* translators: Front-end output. */
				$title = _x( 'Chats', 'post format archive title', 'autodescription' );
			}
		} elseif ( is_post_type_archive() ) {
			/* translators: Front-end output. */
			$title = sprintf( __( 'Archives: %s' ), post_type_archive_title( '', false ) );
		} elseif ( is_tax() ) {
			$tax = get_taxonomy( get_queried_object()->taxonomy );
			/* translators: Front-end output. 1: Taxonomy singular name, 2: Current taxonomy term */
			$title = sprintf( __( '%1$s: %2$s', 'autodescription' ), $tax->labels->singular_name, single_term_title( '', false ) );
		} else {
			/* translators: Front-end output. */
			$title = __( 'Archives', 'autodescription' );
		}

		/**
		* Filter the archive title.
		*
		* @since 4.1.0
		*
		* @param string $title Archive title to be displayed.
		*/
		return apply_filters( 'get_the_archive_title', $title );
	}

	/**
	 * Returns cached and parsed separator option.
	 *
	 * @param string $type The separator type. Used to fetch option.
	 * @param bool $escape Escape the separator.
	 *
	 * @staticvar array $sepcache The separator cache.
	 * @staticvar array $sep_esc The escaped separator cache.
	 *
	 * @since 2.3.9
	 */
	public function get_separator( $type = 'title', $escape = false ) {

		static $sepcache = array();
		static $sep_esc = array();

		if ( isset( $sep_esc[$type][$escape] ) )
			return $sep_esc[$type][$escape];

		if ( ! isset( $sepcache[$type] ) ) {
			if ( 'title' == $type ) {
				$sep_option = $this->get_option( 'title_seperator' ); // Note: typo.
			} else {
				$sep_option = $this->get_option( $type . '_separator' );
			}

			if ( 'pipe' === $sep_option ) {
				$sep = '|';
			} else if ( 'dash' === $sep_option ) {
				$sep = '-';
			} else if ( ! empty( $sep_option ) ) {
				//* Encapsulate within html entities.
				$sep = '&' . $sep_option . ';';
			} else {
				//* Nothing found.
				$sep = '|';
			}

			$sepcache[$type] = $sep;
		}

		if ( $escape ) {
			return $sep_esc[$type][$escape] = esc_html( $sepcache[$type] );
		} else {
			return $sep_esc[$type][$escape] = $sepcache[$type];
		}
	}

	/**
	 * Fetch blogname
	 *
	 * @staticvar string $blogname
	 *
	 * @since 2.5.2
	 * @return string $blogname The trimmed and sanitized blogname
	 */
	public function get_blogname() {

		$blogname = null;

		if ( isset( $blogname ) )
			return $blogname;

		return $blogname = trim( get_bloginfo( 'name', 'display' ) );
	}

	/**
	 * Fetch blog description.
	 *
	 * @staticvar string $description
	 *
	 * @since 2.5.2
	 * @return string $blogname The trimmed and sanitized blog description.
	 */
	public function get_blogdescription() {

		$description = null;

		if ( isset( $description ) )
			return $description;

		return $description = trim( get_bloginfo( 'description', 'display' ) );
	}

	/**
	 * Matches WordPress locales.
	 * If not matched, it will calculate a locale.
	 *
	 * @param $match the locale to match. Defaults to WordPress locale.
	 *
	 * @since 2.5.2
	 *
	 * @return string Facebook acceptable OG locale.
	 */
	public function fetch_locale( $match = '' ) {

		if ( empty( $match ) )
			$match = get_locale();

		$match_len = strlen( $match );
		$valid_locales = (array) $this->fb_locales();
		$default = 'en_US';

		if ( 5 === $match_len ) {
			//* Full locale is used.

			//* Return the match if found.
			if ( $this->in_array( $match, $valid_locales ) )
				return $match;

			//* Convert to only language portion.
			$match = substr( $match, 0, 2 );
			$match_len = 2;
		}

		if ( 2 === $match_len ) {
			//* Language key is provided.

			$locale_keys = (array) $this->language_keys();

			//* No need to do for each loop. Just match the keys.
			if ( $key = array_search( $match, $locale_keys ) ) {
				//* Fetch the corresponding value from key within the language array.
				return $valid_locales[$key];
			}
		}

		return $default;
	}

}
