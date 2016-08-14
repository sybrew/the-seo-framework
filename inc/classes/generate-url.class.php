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
 * Class AutoDescription_Generate_Url
 *
 * Generates URL and permalink SEO data based on content.
 *
 * @since 2.6.0
 */
class AutoDescription_Generate_Url extends AutoDescription_Generate_Title {

	/**
	 * Whether to slash the url or not. Used when query vars are in url.
	 *
	 * @since 2.6.0
	 *
	 * @var bool Whether to slash the url.
	 */
	protected $url_slashit;

	/**
	 * Holds current HTTP host.
	 *
	 * @since 2.6.5
	 *
	 * @var string The current HTTP host.
	 */
	protected $current_host;

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
	 * Constructor, load parent constructor and set up variables.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Creates canonical URL.
	 *
	 * @param string $url the url
	 *
	 * @since 2.4.2
	 * @param array $args : accepted args : {
	 * 		@param bool $paged Return current page URL without pagination if false
	 * 		@param bool $paged_plural Whether to add pagination for the second or later page.
	 * 		@param bool $from_option Get the canonical uri option
	 * 		@param object $post The Post Object.
	 * 		@param bool $external Whether to fetch the current WP Request or get the permalink by Post Object.
	 * 		@param bool $is_term Fetch url for term.
	 * 		@param object $term The term object.
	 * 		@param bool $home Fetch home URL.
	 * 		@param bool $forceslash Fetch home URL and slash it, always.
	 *		@param int $id The Page or Term ID.
	 * }
	 *
	 * @since 2.0.0
	 *
	 * @return string Escape url.
	 */
	public function the_url( $url = '', $args = array() ) {

		if ( $this->the_seo_framework_debug && false === $this->doing_sitemap ) $this->debug_init( __METHOD__, true, $debug_key = microtime( true ), get_defined_vars() );

		$args = $this->reparse_url_args( $args );

		/**
		 * Fetch permalink if Feed.
		 * @since 2.5.2
		 */
		if ( $this->is_feed() )
			$url = get_permalink();

		//* Reset cache.
		$this->url_slashit = true;
		$this->unset_current_subdomain();
		$this->current_host = '';

		$path = '';
		$scheme = '';

		/**
		 * Trailing slash the post, or not.
		 * @since 2.2.4
		 */
		$slashit = true;

		if ( ! $args['home'] && empty( $url ) ) {
			/**
			 * Get url from options
			 * @since 2.2.9
			 */
			if ( $args['get_custom_field'] && $this->is_singular() ) {
				$custom_url = $this->get_custom_field( '_genesis_canonical_uri' );

				if ( $custom_url ) {
					$url = $custom_url;
					$this->url_slashit = false;
					$parsed_url = wp_parse_url( $custom_url );
					$scheme = isset( $parsed_url['scheme'] ) ? $parsed_url['scheme'] : 'http';
				}
			}

			if ( empty( $url ) )
				$path = $this->generate_url_path( $args );
		}

		//* Translate the URL, when possible.
		$path = $this->get_translation_path( $path, $args['id'], $args['external'] );

		//* Domain Mapping canonical URL
		if ( empty( $url ) ) {
			$wpmu_url = $this->the_url_wpmudev_domainmap( $path, true );
			if ( $wpmu_url && is_array( $wpmu_url ) ) {
				$url = $wpmu_url[0];
				$scheme = $wpmu_url[1];
			}
		}

		//* Domain Mapping canonical URL
		if ( empty( $url ) ) {
			$dm_url = $this->the_url_donncha_domainmap( $path, true );
			if ( $dm_url && is_array( $dm_url ) ) {
				$url = $dm_url[0];
				$scheme = $dm_url[1];
			}
		}

		//* Non-domainmap URL
		if ( empty( $url ) ) {
			if ( $args['home'] )
				$this->unset_current_subdomain();

			$url = $this->add_url_host( $path );
			$scheme = is_ssl() ? 'https' : 'http';

			$url = $this->add_url_subdomain( $url );
		}

		//* URL has been given manually or $args['home'] is true.
		if ( ! isset( $scheme ) )
			$scheme = is_ssl() ? 'https' : 'http';

		$url = $this->set_url_scheme( $url, $scheme );

		if ( $this->url_slashit ) {
			/**
			 * Slash it only if $slashit is true
			 * @since 2.2.4
			 */
			if ( $slashit && ! $args['forceslash'] )
				$url = user_trailingslashit( $url );

			//* Be careful with the default permalink structure.
			if ( $args['forceslash'] )
				$url = trailingslashit( $url );
		}

		if ( $this->pretty_permalinks ) {
			$url = esc_url( $url );
		} else {
			//* Keep the &'s more readable.
			$url = esc_url_raw( $url );
		}

		if ( $this->the_seo_framework_debug && false === $this->doing_sitemap ) $this->debug_init( __METHOD__, false, $debug_key, array( 'url_output' => $url ) );

		return $url;
	}

	/**
	 * Parse and sanitize url args.
	 *
	 * @since 2.4.2
	 * @since 2.5.0:
	 * @applies filters the_seo_framework_url_args : {
	 * 		@param bool $paged Return current page URL without pagination if false
	 * 		@param bool $paged_plural Whether to add pagination for the second or later page.
	 * 		@param bool $from_option Get the canonical uri option
	 * 		@param object $post The Post Object.
	 * 		@param bool $external Whether to fetch the current WP Request or get the permalink by Post Object.
	 * 		@param bool $is_term Fetch url for term.
	 * 		@param object $term The term object.
	 * 		@param bool $home Fetch home URL.
	 * 		@param bool $forceslash Fetch home URL and slash it, always.
	 *		@param int $id The Page or Term ID.
	 * }
	 *
	 * @param array $args required The passed arguments.
	 * @param array $defaults The default arguments.
	 * @param bool $get_defaults Return the default arguments. Ignoring $args.
	 * @return array $args parsed args.
	 */
	public function parse_url_args( $args = array(), $defaults = array(), $get_defaults = false ) {

		//* Passing back the defaults reduces the memory usage.
		if ( empty( $defaults ) ) {
			$defaults = array(
				'paged' 			=> false,
				'paged_plural' 		=> true,
				'get_custom_field'	=> true,
				'external'			=> false,
				'is_term' 			=> false,
				'post' 				=> null,
				'term'				=> null,
				'home'				=> false,
				'forceslash'		=> false,
				'id'				=> $this->get_the_real_ID(),
			);

			$defaults = (array) apply_filters( 'the_seo_framework_url_args', $defaults, $args );
		}

		//* Return early if it's only a default args request.
		if ( $get_defaults )
			return $defaults;

		//* Array merge doesn't support sanitation. We're simply type casting here.
		$args['paged'] 				= isset( $args['paged'] ) 				? (bool) $args['paged'] 			: $defaults['paged'];
		$args['paged_plural'] 		= isset( $args['paged_plural'] ) 		? (bool) $args['paged_plural'] 		: $defaults['paged_plural'];
		$args['get_custom_field'] 	= isset( $args['get_custom_field'] ) 	? (bool) $args['get_custom_field'] 	: $defaults['get_custom_field'];
		$args['external'] 			= isset( $args['external'] ) 			? (bool) $args['external'] 			: $defaults['external'];
		$args['is_term'] 			= isset( $args['is_term'] ) 			? (bool) $args['is_term'] 			: $defaults['is_term'];
		$args['get_custom_field'] 	= isset( $args['get_custom_field'] ) 	? (bool) $args['get_custom_field'] 	: $defaults['get_custom_field'];
		$args['post'] 				= isset( $args['post'] ) 				? (object) $args['post'] 			: $defaults['post'];
		$args['term'] 				= isset( $args['term'] ) 				? (object) $args['term'] 			: $defaults['term'];
		$args['home'] 				= isset( $args['home'] ) 				? (bool) $args['home'] 				: $defaults['home'];
		$args['forceslash'] 		= isset( $args['forceslash'] ) 			? (bool) $args['forceslash'] 		: $defaults['forceslash'];
		$args['id'] 				= isset( $args['id'] ) 					? (int) $args['id'] 				: $defaults['id'];

		return $args;
	}

	/**
	 * Reparse URL args.
	 *
	 * @param array $args required The passed arguments.
	 *
	 * @since 2.6.2
	 * @return array $args parsed args.
	 */
	public function reparse_url_args( $args = array() ) {

		$default_args = $this->parse_url_args( '', '', true );

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
	 * @global object $wp
	 * @NOTE: Handles full path, including home directory.
	 *
	 * @param array $args the URL args.
	 * @return string $path
	 */
	public function generate_url_path( $args = array() ) {

		$args = $this->reparse_url_args( $args );

		if ( $this->is_archive() || $args['is_term'] ) {

			$term = $args['term'];

			//* Term or Taxonomy.
			if ( ! isset( $term ) )
				$term = get_queried_object();

			if ( isset( $term->taxonomy ) ) {
				//* Registered Terms and Taxonomies.
				$path = $this->get_relative_term_url( $term, $args );
			} elseif ( ! $args['external'] ) {
				//* Everything else.
				global $wp;
				$path = trailingslashit( get_option( 'home' ) ) . $wp->request;
				$path = $this->set_url_scheme( $path, 'relative' );
			} else {
				//* Nothing to see here...
				$path = '';
			}
		} else {

			/**
			 * Reworked to use the $args['id'] check based on get_the_real_ID.
			 * @since 2.6.0 & 2.6.2
			 */
			$post_id = isset( $args['post']->ID ) ? $args['post']->ID : $args['id'];

			if ( $this->pretty_permalinks && $post_id && $this->is_singular() ) {
				$post = get_post( $post_id );

				//* Don't slash draft links.
				if ( isset( $post->post_status ) && ( 'auto-draft' === $post->post_status || 'draft' === $post->post_status ) )
					$this->url_slashit = false;
			}

			$path = $this->build_singular_relative_url( $post_id, $args );
		}

		if ( isset( $path ) )
			return $path;

		return '';
	}

	/**
	 * Generates relative URL for the Homepage and Singular Posts.
	 *
	 * @since 2.6.5
	 * @global object $wp
	 * @NOTE: Handles full path, including home directory.
	 *
	 * @param int $post_id The ID.
	 * @param array $args The URL arguments.
	 * @return relative Post or Page url.
	 */
	public function build_singular_relative_url( $post_id = null, $args = array() ) {

		if ( ! isset( $post_id ) ) {
			//* We can't fetch the post ID when there's an external request.
			if ( $args['external'] )
				return '';

			$post_id = $this->get_the_real_ID();
		}

		$args = $this->reparse_url_args( $args );

		if ( $args['external'] || ! $this->is_front_page() ) {
			$url = get_permalink( $post_id );
		} elseif ( $this->is_front_page() ) {
			$url = get_home_url();
		} elseif ( ! $args['external'] ) {
			global $wp;

			if ( isset( $wp->request ) )
				$url = trailingslashit( get_option( 'home' ) ) . $wp->request;
		}

		//* No permalink found.
		if ( ! isset( $url ) )
			return '';

		$paged = $this->is_singular() ? $this->page() : $this->paged();
		$paged = $this->maybe_get_paged( $paged, $args['paged'], $args['paged_plural'] );

		if ( $paged ) {
			if ( $this->pretty_permalinks ) {
				if ( $this->is_singular() ) {
					$url = trailingslashit( $url ) . $paged;
				} else {
					$url = trailingslashit( $url ) . 'page/' . $paged;
				}
			} else {
				if ( $this->is_singular() ) {
					$url = add_query_arg( 'page', $paged, $url );
				} else {
					$url = add_query_arg( 'paged', $paged, $url );
				}
			}
		}

		$path = $this->set_url_scheme( $url, 'relative' );

		return $path;
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

		$host = $this->current_host ? $this->current_host : $this->get_home_host();

		$scheme = $host ? 'http://' : '';

		return $url = $scheme . trailingslashit( $host ) . ltrim( $path, ' \\/' );
	}

	/**
	 * Generates relative URL for current post_ID for translation plugins.
	 *
	 * @since 2.6.0
	 * @global object $post
	 * @NOTE: Handles full path, including home directory.
	 *
	 * @param string $path the current URL path.
	 * @param int $post_id The post ID.
	 * @param bool $external Whether the request for URL generation is external.
	 * @return relative Post or Page url.
	 */
	public function get_translation_path( $path = '', $post_id = null, $external = false ) {

		if ( is_object( $post_id ) )
			$post_id = isset( $post_id->ID ) ? $post_id->ID : $this->get_the_real_ID();

		if ( is_null( $post_id ) )
			$post_id = $this->get_the_real_ID();

		//* WPML support.
		if ( $this->is_wpml_active() )
			$path = $this->get_relative_wmpl_url( $path, $post_id );

		//* qTranslate X support. Can't work externally as we can't fetch the post's current language.
		if ( ! $external && $this->is_qtranslate_active() )
			$path = $this->get_relative_qtranslate_url( $path, $post_id );

		return $path;
	}

	/**
	 * Generates qtranslate URL.
	 *
	 * @since 2.6.0
	 * @staticvar int $q_config_mode
	 * @global array $q_config
	 * @NOTE: Handles full path, including home directory.
	 *
	 * @param string $path The current path.
	 * @param int $post_id The Post ID. Unused until qTranslate provides external URL forgery.
	 */
	public function get_relative_qtranslate_url( $path = '', $post_id = '' ) {

		//* Reset cache.
		$this->url_slashit = true;
		$this->unset_current_subdomain();

		static $q_config_mode = null;

		if ( ! isset( $q_config ) ) {
			global $q_config;
			$q_config_mode = $q_config['url_mode'];
		}

		//* If false, change canonical URL for every page.
		$hide = isset( $q_config['hide_default_language'] ) ? $q_config['hide_default_language'] : true;

		$current_lang = isset( $q_config['language'] ) ? $q_config['language'] : false;
		$default_lang = isset( $q_config['default_language'] ) ? $q_config['default_language'] : false;

		//* Don't to anything on default language when path is hidden.
		if ( $hide && $current_lang === $default_lang )
			return $path;

		switch ( $q_config_mode ) {
			case '1' :
				//* Negotiation type query var.

				//* Don't slash it further.
				$this->url_slashit = false;

				/**
				 * Path must have trailing slash for pagination permalinks to work.
				 * So we remove the query string and add it back with slash.
				 */
				if ( strpos( $path, '?lang=' . $current_lang ) !== false )
					$path = str_replace( '?lang=' . $current_lang, '', $path );

				return user_trailingslashit( $path ) . '?lang=' . $current_lang;
				break;

			case '2' :
				//* Subdirectory
				if ( 0 === strpos( $path, '/' . $current_lang . '/' ) )
					return $path;
				else
					return $path = trailingslashit( $current_lang ) . ltrim( $path, ' \\/' );
				break;

			case '3' :
				//* Notify cache of subdomain addition.
				$this->set_current_subdomain( $current_lang );

				//* No need to alter the path.
				return $path;
				break;

			default :
				return $path;
				break;
		}

		return $path;
	}

	/**
	 * Generate relative WPML url.
	 *
	 * @since 2.4.3
	 * @staticvar bool $gli_exists
	 * @staticvar string $default_lang
	 * @global object $sitepress
	 * @NOTE: Handles full path, including home directory.
	 *
	 * @param string $path The current path.
	 * @param int $post_id The Post ID.
	 * @return relative path for WPML urls.
	 */
	public function get_relative_wmpl_url( $path = '', $post_id = '' ) {
		global $sitepress;

		//* Reset cache.
		$this->url_slashit = true;
		$this->unset_current_subdomain();

		if ( ! isset( $sitepress ) )
			return $path;

		static $gli_exists = null;
		if ( is_null( $gli_exists ) )
			$gli_exists = function_exists( 'wpml_get_language_information' );

		if ( ! $gli_exists )
			return $path;

		if ( empty( $post_id ) )
			$post_id = $this->get_the_real_ID();

		//* Cache default language.
		static $default_lang = null;
		if ( is_null( $default_lang ) )
			$default_lang = $sitepress->get_default_language();

		/**
		 * Applies filters wpml_post_language_details : array|wp_error
		 *
		 * ... Somehow WPML thought this would be great and understandable.
		 * This should be put inside a callable function.
		 * @since 2.6.0
		 */
		$lang_info = apply_filters( 'wpml_post_language_details', null, $post_id );

		if ( is_wp_error( $lang_info ) ) {
			//* Terms and Taxonomies.
			$lang_info = array();

			//* Cache the code.
			static $lang_code = null;
			if ( is_null( $lang_code ) && defined( 'ICL_LANGUAGE_CODE' ) )
				$lang_code = ICL_LANGUAGE_CODE;

			$lang_info['language_code'] = $lang_code;
		}

		//* If filter isn't used, bail.
		if ( ! isset( $lang_info['language_code'] ) )
			return $path;

		$current_lang = $lang_info['language_code'];

		//* No need to alter URL if we're on default lang.
		if ( $current_lang === $default_lang )
			return $path;

		//* Cache negotiation type.
		static $negotiation_type = null;
		if ( ! isset( $negotiation_type ) )
			$negotiation_type = $sitepress->get_setting( 'language_negotiation_type' );

		switch ( $negotiation_type ) {

			case '1' :
				//* Subdirectory

				/**
				 * Might not always work.
				 * @TODO Fix.
				 * @priority OMG WTF BBQ
				 */
				$contains_path = strpos( $path, '/' . $current_lang . '/' );
				if ( false !== $contains_path && 0 === $contains_path ) {
					return $path;
				} else {
					return $path = trailingslashit( $current_lang ) . ltrim( $path, ' \\/' );
				}
				break;

			case '2' :
				//* Custom domain.

				$langsettings = $sitepress->get_setting( 'language_domains' );
				$current_lang_setting = isset( $langsettings[ $current_lang ] ) ? $langsettings[ $current_lang ] : '';

				if ( empty( $current_lang_setting ) )
					return $path;

				$current_lang_setting = $this->make_fully_qualified_url( $current_lang_setting );
				$parsed = wp_parse_url( $current_lang_setting );

				$this->current_host = isset( $parsed['host'] ) ? $parsed['host'] : '';
				$current_path = isset( $parsed['path'] ) ? trailingslashit( $parsed['path'] ) : '';

				return $current_path . $path;
				break;

			case '3' :
				//* Negotiation type query var.

				//* Don't slash it further.
				$this->url_slashit = false;

				/**
				 * Path must have trailing slash for pagination permalinks to work.
				 * So we remove the query string and add it back with slash.
				 */
				if ( false !== strpos( $path, '?lang=' . $current_lang ) )
					$path = str_replace( '?lang=' . $current_lang, '', $path );

				return user_trailingslashit( $path ) . '?lang=' . $current_lang;
				break;

		}

		return $path;
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
			$term = get_queried_object();

		$taxonomy = $term->taxonomy;
		$path = $wp_rewrite->get_extra_permastruct( $taxonomy );

		$slug = $term->slug;
		$t = get_taxonomy( $taxonomy );

		$paged = $this->maybe_get_paged( $this->paged(), $args['paged'], $args['paged_plural'] );

		if ( empty( $path ) ) {
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

				$path = str_replace( "%$taxonomy%", implode( '/', $hierarchical_slugs ), $path );
			} else {
				$path = str_replace( "%$taxonomy%", $slug, $path );
			}

			if ( $paged )
				$path = trailingslashit( $path ) . 'page/' . $paged;

			$path = user_trailingslashit( $path, 'category' );
		}

		//* Add plausible domain subdirectories.
		$url = trailingslashit( get_option( 'home' ) ) . ltrim( $path, ' \\/' );
		$path = $this->set_url_scheme( $url, 'relative' );

		return $path;
	}

	/**
	 * Set url scheme.
	 * WordPress core function, without filter.
	 *
	 * @param string $url Absolute url that includes a scheme.
	 * @param string $scheme optional. Scheme to give $url. Currently 'http', 'https', 'login', 'login_post', 'admin', or 'relative'.
	 * @param bool $use_filter Whether to parse filters.
	 *
	 * @since 2.4.2
	 * @return string url with chosen scheme.
	 */
	public function set_url_scheme( $url, $scheme = null, $use_filter = true ) {

		if ( ! isset( $scheme ) ) {
			$scheme = is_ssl() ? 'https' : 'http';
		} elseif ( 'admin' === $scheme || 'login' === $scheme  || 'login_post' === $scheme || 'rpc' === $scheme ) {
			$scheme = is_ssl() || force_ssl_admin() ? 'https' : 'http';
		} elseif ( 'http' !== $scheme && 'https' !== $scheme && 'relative' !== $scheme ) {
			$scheme = is_ssl() ? 'https' : 'http';
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
	 *
	 * @param string $url The url with scheme.
	 * @param string $scheme The current scheme.
	 * @return $url with applied filters.
	 */
	public function set_url_scheme_filter( $url, $current_scheme ) {

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
		 * @param string $current_scheme the current used scheme.
		 *
		 * @since 2.4.2
		 */
		$scheme_settings = apply_filters( 'the_seo_framework_canonical_force_scheme', null, $current_scheme );

		/**
		 * @TODO add options metabox.
		 * @priority medium 2.6.5+
		 */

		if ( isset( $scheme_settings ) ) {
			if ( 'https' === $scheme_settings || 'http' === $scheme_settings || 'relative' === $scheme_settings ) {
				$url = $this->set_url_scheme( $url, $scheme_settings, false );
			} elseif ( ! $scheme_settings ) {
				$url = $this->set_url_scheme( $url, 'http', false );
			} elseif ( $scheme_setting ) {
				$url = $this->set_url_scheme( $url, 'https', false );
			}
		}

		return $url;
	}

	/**
	 * Creates a full canonical URL when WPMUdev Domain Mapping is active from path.
	 *
	 * @since 2.3.0
	 * @since 2.4.0 Added $get_scheme parameter.
	 *
	 * @param string $path The post relative path.
	 * @param bool $get_scheme Output array with scheme.
	 * @return string|array|void The unescaped URL, the scheme
	 */
	public function the_url_wpmudev_domainmap( $path, $get_scheme = false ) {

		if ( false === $this->is_domainmapping_active() )
			return '';

		global $wpdb, $blog_id;

		/**
		 * Cache revisions. Hexadecimal.
		 * @since 2.6.0
		 */
		$revision = '1';

		$cache_key = 'wpmudev_mapped_domain_' . $revision . '_' . $blog_id;

		//* Check if the domain is mapped. Store in object cache.
		$mapped_domain = $this->object_cache_get( $cache_key );
		if ( false === $mapped_domain ) {

			$mapped_domains = $wpdb->get_results( $wpdb->prepare( "SELECT id, domain, is_primary, scheme FROM {$wpdb->base_prefix}domain_mapping WHERE blog_id = %d", $blog_id ), OBJECT );

			$primary_key = 0;
			$domain_ids = array();

			foreach ( $mapped_domains as $key => $domain ) {
				if ( isset( $domain->is_primary ) && '1' === $domain->is_primary ) {
					$primary_key = $key;

					//* We've found the primary key, break loop.
					break;
				} else {
					//* Save IDs.
					if ( isset( $domain->id ) && $domain->id )
						$domain_ids[ $key ] = $domain->id;
				}
			}

			if ( 0 === $primary_key && ! empty( $domain_ids ) ) {
				//* No primary ID has been found. Get the one with the lowest ID, which has been added first.
				$primary_key = array_keys( $domain_ids, min( $domain_ids ), true );
				$primary_key = reset( $primary_key );
			}

			//* Set 0, as we check for false to begin with.
			$mapped_domain = isset( $mapped_domains[ $primary_key ] ) ? $mapped_domains[ $primary_key ] : 0;

			$this->object_cache_set( $cache_key, $mapped_domain, 3600 );
		}

		if ( $mapped_domain ) {

			$domain = isset( $mapped_domain->domain ) ? $mapped_domain->domain : '0';
			$scheme = isset( $mapped_domain->scheme ) ? $mapped_domain->scheme : '';

			//* Fallback to is_ssl if no scheme has been found.
			if ( '' === $scheme )
				$scheme = is_ssl() ? '1' : '0';

			if ( '1' === $scheme ) {
				$scheme_full = 'https://';
				$scheme = 'https';
			} else {
				$scheme_full = 'http://';
				$scheme = 'http';
			}

			//* Put it all together.
			$url = trailingslashit( $scheme_full . $domain ) . ltrim( $path, ' \\/' );

			if ( $get_scheme )
				return array( $url, $scheme );
			else
				return $url;
		}

		return '';
	}

	/**
	 * Try to get an canonical URL when Donncha Domain Mapping is active.
	 *
	 * @since 2.4.0
	 *
	 * @param string $path The post relative path.
	 * @param bool $get_scheme Output array with scheme.
	 * @return string|array|void The unescaped URL, the scheme
	 */
	public function the_url_donncha_domainmap( $path, $get_scheme = false ) {

		if ( false === $this->is_donncha_domainmapping_active() )
			return '';

		global $current_blog;

		$scheme = is_ssl() ? 'https' : 'http';
		$url = function_exists( 'domain_mapping_siteurl' ) ? domain_mapping_siteurl( false ) : false;

		$request_uri = '';

		if ( $url && untrailingslashit( $scheme . '://' . $current_blog->domain . $current_blog->path ) !== $url ) {
			if ( ( defined( 'VHOST' ) && 'yes' !== VHOST ) || ( defined( 'SUBDOMAIN_INSTALL' ) && false === SUBDOMAIN_INSTALL ) )
				$request_uri = str_replace( $current_blog->path, '/', $_SERVER['REQUEST_URI'] );

			$url = trailingslashit( $url . $request_uri ) . ltrim( $path, '\\/ ' );

			if ( $get_scheme ) {
				return array( $url, $scheme );
			} else {
				return $url;
			}
		}

		return '';
	}

	/**
	 * Generates shortlink URL.
	 *
	 * @since 2.2.2
	 * @global object $wp_query
	 *
	 * @param int $post_id The post ID.
	 * @return string|null Escaped site Shortlink URL.
	 */
	public function get_shortlink( $post_id = 0 ) {

		if ( $this->get_option( 'shortlink_tag' ) ) {

			$path = null;

			if ( false === $this->is_front_page() ) {
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
						$id = get_queried_object_id();
						$path = '?cat=' . $id;
					} elseif ( $this->is_tag() ) {
						$id = get_queried_object_id();
						$path = '?post_tag=' . $id;
					} elseif ( $this->is_date() ) {
						global $wp_query;

						$query = $wp_query->query;
						$var = '';

						$first = true;
						foreach ( $query as $key => $val ) {
							$var .= $first ? '?' : '&';
							$var .= $key . '=' . $val;
							$first = false;
						}

						$path = $var;
					} elseif ( $this->is_author() ) {
						$id = get_queried_object_id();
						$path = '?author=' . $id;
					} elseif ( $this->is_tax() ) {
						//* Generate shortlink for object type and slug.
						$object = get_queried_object();

						$t = isset( $object->taxonomy ) ? urlencode( $object->taxonomy ) : '';

						if ( $t ) {
							$slug = isset( $object->slug ) ? urlencode( $object->slug ) : '';

							if ( $slug )
								$path = '?' . $t . '=' . $slug;
						}
					}
				}
			}

			if ( isset( $path ) ) {
				//* Path always has something. So we can safely use .='&' instead of add_query_arg().

				if ( 0 === $post_id )
					$post_id = $this->get_the_real_ID();

				$url = $this->the_url_from_cache( '', $post_id, false, false, false );
				$query = parse_url( $url, PHP_URL_QUERY );

				$additions = '';
				if ( isset( $query ) ) {
					if ( false !== strpos( $query, '&' ) ) {
						$query = explode( '&', $query );
					} else {
						$query = array( $query );
					}

					foreach ( $query as $arg ) {
						if ( false === strpos( $path, $arg ) )
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

				return esc_url_raw( $url );
			}
		}

		return '';
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

		if ( $this->is_singular() ) {

			$output_singular_paged = $this->is_front_page() ? $this->is_option_checked( 'prev_next_frontpage' ) : $this->is_option_checked( 'prev_next_posts' );

			if ( $output_singular_paged ) {

				$page = $this->page();
				$numpages = substr_count( $this->get_post_content( $post_id ), '<!--nextpage-->' ) + 1;

				if ( ! $page )
					$page = 1;

				if ( 'prev' === $prev_next ) {
					$prev = $page > 1 ? $this->get_paged_post_url( $page - 1, $post_id, 'prev' ) : '';
				} elseif ( 'next' === $prev_next ) {
					$next = $page < $numpages ? $this->get_paged_post_url( $page + 1, $post_id, 'next' ) : '';
				}
			}
		} elseif ( $this->is_archive() || $this->is_home() ) {

			$output_archive_paged = false;
			if ( $this->is_front_page() ) {
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

					$prev = get_pagenum_link( $paged, false );
				} elseif ( 'next' === $prev_next && $paged < $GLOBALS['wp_query']->max_num_pages ) {

					if ( ! $paged )
						$paged = 1;
					$paged = intval( $paged ) + 1;

					$next = get_pagenum_link( $paged, false );
				}
			}
		}

		if ( $prev )
			return esc_url_raw( $prev );

		if ( $next )
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
	 * @return string Unescaped URL
	 */
	public function get_paged_post_url( $i, $post_id = 0, $pos = 'prev' ) {

		$from_option = false;

		if ( empty( $post_id ) )
			$post_id = $this->get_the_real_ID();

		if ( 1 === $i ) {
			$url = $this->the_url_from_cache( '', $post_id, false, $from_option, false );
		} else {
			$post = get_post( $post_id );

			$urlfromcache = $this->the_url_from_cache( '', $post_id, false, $from_option, false );

			if ( $i >= 2 ) {
				//* Fix adding pagination url.

				//* Parse query arg, put in var and remove from current URL.
				$query_arg = parse_url( $urlfromcache, PHP_URL_QUERY );
				if ( isset( $query_arg ) )
					$urlfromcache = str_replace( '?' . $query_arg, '', $urlfromcache );

				// Calculate current page number.
				$current = 'next' === $pos ? ( $i - 1 ) : ( $i + 1 );
				$current = (string) $current;

				//* Continue if still bigger than or equal to 2.
				if ( $i >= 2 ) {
					//* We're adding a page.
					$last_occurence = strrpos( $urlfromcache, '/' . $current . '/' );

					if ( false !== $last_occurence )
						$urlfromcache = substr_replace( $urlfromcache, '/', $last_occurence, strlen( '/' . $current . '/' ) );
				}
			}

			if ( ! $this->pretty_permalinks || in_array( $post->post_status, array( 'draft', 'auto-draft', 'pending' ), true ) ) {

				//* Put removed query arg back prior to adding pagination.
				if ( isset( $query_arg ) )
					$urlfromcache = $urlfromcache . '?' . $query_arg;

				$url = add_query_arg( 'page', $i, $urlfromcache );
			} elseif ( $this->is_static_frontpage( $post_id ) ) {
				global $wp_rewrite;

				$url = trailingslashit( $urlfromcache ) . user_trailingslashit( $wp_rewrite->pagination_base . '/' . $i, 'single_paged' );

				//* Add back query arg if removed.
				if ( isset( $query_arg ) )
					$url = $url . '?' . $query_arg;
			} else {
				$url = trailingslashit( $urlfromcache ) . user_trailingslashit( $i, 'single_paged' );

				//* Add back query arg if removed.
				if ( isset( $query_arg ) )
					$url = $url . '?' . $query_arg;
			}
		}

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
			$parsed_url = wp_parse_url( $url );
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
			$subdomain = esc_html( $set );

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

		return $structure = get_option( 'permalink_structure' );
	}

	/**
	 * Add $paged if Paginated and allowed through arguments.
	 *
	 * @since 2.6.0
	 *
	 * @param int $paged
	 * @param bool $singular Whether to allow plural and singular.
	 * @param bool $plural Whether to allow plural regardless.
	 *
	 * @return int|bool $paged. False if not allowed. Int if allowed.
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

		if ( '//' === substr( $url, 0, 2 ) )
			$url = 'http:' . $url;
		elseif ( 'http' !== substr( $url, 0, 4 ) )
			$url = 'http://' . $url;

		return $url;
	}

	/**
	 * Fetches home URL host. Like "wordpress.org".
	 * If this fails, you're going to have a bad time.
	 *
	 * @since 2.7.0
	 * @staticvar string $cache
	 *
	 * @return string The home URL host.
	 */
	public function get_home_host() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		$parsed_url = wp_parse_url( get_option( 'home' ) );

		$host = isset( $parsed_url['host'] ) ? $parsed_url['host'] : '';

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

		$parsed_url = wp_parse_url( get_option( 'home' ) );

		if ( ! empty( $parsed_url['path'] ) && $path = ltrim( $parsed_url['path'], ' \\/' ) )
			$path = '/' . $path;

		return $cache = $path;
	}
}
