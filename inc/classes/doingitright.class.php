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
 * Class AutoDescription_DoingItRight
 *
 * Adds data in a column to edit.php and edit-tags.php
 * Shows you if you're doing the SEO right.
 *
 * @since 2.1.9
 */
class AutoDescription_DoingItRight extends AutoDescription_Search {

	/**
	 * Constructor, load parent constructor
	 *
	 * Initalizes columns and load post states.
	 */
	public function __construct() {
		parent::__construct();

		//* Initialize post states.
		add_action( 'current_screen', array( $this, 'post_state' ) );

		//* Ajax handlers for columns.
		add_action( 'admin_init', array( $this, 'init_columns_ajax' ) );
		//* Initialize columns.
		add_action( 'current_screen', array( $this, 'init_columns' ) );

	}

	/**
	 * Add post state on edit.php to the page or post that has been altered
	 *
	 * Applies filters `the_seo_framework_allow_states` : boolean
	 *
	 * @uses $this->add_post_state
	 *
	 * @since 2.1.0
	 */
	public function post_state() {

		//* Only load on singular pages.
		if ( $this->is_singular() ) {

			$allow_states = (bool) apply_filters( 'the_seo_framework_allow_states', true );

			if ( $allow_states )
				add_filter( 'display_post_states', array( $this, 'add_post_state' ), 10, 2 );

		}

	}

	/**
	 * Adds post states in post/page edit.php query
	 *
	 * @param array $states The current post states array
	 * @param object $post The Post Object.
	 *
	 * @since 2.1.0
	 */
	public function add_post_state( $states = array(), $post ) {

		$post_id = isset( $post->ID ) ? $post->ID : false;

		if ( $post_id ) {
			$searchexclude = (bool) $this->get_custom_field( 'exclude_local_search', $post_id );

			if ( $searchexclude )
				$states[] = __( 'No Search', 'autodescription' );
		}

		return $states;
	}

	/**
	 * AJAX wrapper for $this->init_columns
	 *
	 * @since 2.6.0
	 */
	public function init_columns_ajax() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			/**
			 * Securely check the referrer, instead of leaving holes everywhere.
			 */
			if ( current_user_can( 'publish_posts' ) && check_ajax_referer( 'add-tag', '_wpnonce_add-tag', false ) )
				$this->init_columns( '', true );
		}

	}

	/**
	 * Initializes columns
	 *
	 * Applies filter the_seo_framework_show_seo_column : Show the SEO column in edit.php
	 *
	 * @param object|empty $screen WP_Screen
	 * @param bool $doing_ajax Whether we're doing an AJAX response.
	 *
	 * @since 2.1.9
	 */
	public function init_columns( $screen = '', $doing_ajax = false ) {

		$show_seo_column = (bool) apply_filters( 'the_seo_framework_show_seo_column', true );

		if ( $doing_ajax ) {
			$post_type = isset( $_POST['post_type'] ) ? $_POST['post_type'] : '';
		} else {
			$post_type = isset( $screen->post_type ) ? $screen->post_type : '';
		}

		if ( $show_seo_column && $this->post_type_supports_custom_seo( $post_type ) ) {

			if ( $doing_ajax ) {

				$id = isset( $_POST['screen'] ) ? $_POST['screen'] : false;
				$taxonomy = isset( $_POST['taxonomy'] ) ? $_POST['taxonomy'] : false;

				if ( $taxonomy && $id ) {
					add_filter( 'manage_' . $id . '_columns', array( $this, 'add_column' ), 1 );
					add_action( 'manage_' . $taxonomy . '_custom_column', array( $this, 'seo_bar_ajax' ), 1, 3 );
				}

			} else {

				$id = isset( $screen->id ) ? $screen->id : '';

				if ( '' !== $id ) {

					if ( $this->is_wp_lists_edit() ) {
						add_filter( 'manage_' . $id . '_columns', array( $this, 'add_column' ), 10, 1 );

						$taxonomy = isset( $screen->taxonomy ) ? $screen->taxonomy : '';

						if ( $taxonomy )
							add_action( 'manage_' . $taxonomy . '_custom_column', array( $this, 'seo_bar' ), 1, 3 );

						/**
						 * Always load pages and posts.
						 * Many CPT plugins rely on these.
						 */
						add_action( 'manage_posts_custom_column', array( $this, 'seo_bar' ), 1, 3 );
						add_action( 'manage_pages_custom_column', array( $this, 'seo_bar' ), 1, 3 );
					}

				}
			}

		}

	}

	/**
	 * Adds SEO column on edit(-tags).php
	 *
	 * @param array $columns The existing columns
	 *
	 * @param $offset 	Determines where the column should be placed. Prefered before comments, then data, then tags.
	 *					If neither found, it will add the column to the end.
	 *
	 * @since 2.1.9
	 * @return array $columns the column data
	 */
	public function add_column( $columns ) {

		$seocolumn = array( 'ad_seo' => 'SEO' );

		$column_keys = array_keys( $columns );

		//* Column keys to look for, in order of appearance.
		$order_keys = array(
			'comments',
			'posts',
			'date',
			'tags',
			'bbp_topic_freshness',
			'bbp_forum_freshness',
			'bbp_reply_created',
		);

		foreach ( $order_keys as $key ) {
			//* Put value in $offset, if not false, break loop.
			if ( false !== ( $offset = array_search( $key, $column_keys ) ) )
				break;
		}

		//* I tried but found nothing
		if ( false === $offset ) {
			//* Add SEO bar at the end of columns.
			$columns = array_merge( $columns, $seocolumn );
		} else {
			//* Add seo bar between columns.

			//* Cache columns.
			$columns_before = $columns;

			$columns = array_merge(
				array_splice( $columns, 0, $offset ),
				$seocolumn,
				array_splice( $columns_before, $offset )
			);
		}

		return $columns;
	}

	/**
	 * Adds the SEO Bar.
	 *
	 * @param string $column the current column    : If it's a taxonomy, this is empty
	 * @param int $post_id the post id             : If it's a taxonomy, this is the column name
	 * @param string $tax_id this is empty         : If it's a taxonomy, this is the taxonomy id
	 *
	 * @param string $status the status in html
	 *
	 * @staticvar string $type_cache
	 * @staticvar string $column_cache
	 *
	 * @since 2.6.0
	 */
	public function seo_bar( $column, $post_id, $tax_id = '' ) {

		static $type_cache = null;
		static $column_cache = null;

		if ( ! isset( $type_cache ) || ! isset( $column_cache ) ) {
			$type = get_post_type( $post_id );

			if ( false === $type || '' !== $tax_id ) {
				$screen = (object) get_current_screen();

				if ( isset( $screen->taxonomy ) )
					$type = $screen->taxonomy;
			}

			$type_cache = $type;
			$column_cache = $column;
		}

		/**
		 * Params are shifted.
		 * @link https://core.trac.wordpress.org/ticket/33521
		 */
		if ( '' !== $tax_id ) {
			$column = $post_id;
			$post_id = $tax_id;
		}

		if ( 'ad_seo' === $column )
			echo $this->post_status( $post_id, $type_cache, true );

	}

	/**
	 * Adds SEO column to edit screens.
	 *
	 * @param string $column the current column    : If it's a taxonomy, this is empty
	 * @param int $post_id the post id             : If it's a taxonomy, this is the column name
	 * @param string $tax_id this is empty         : If it's a taxonomy, this is the taxonomy id
	 *
	 * @param string $status the status in html
	 *
	 * @staticvar string $type_cache
	 * @staticvar string $column_cache
	 *
	 * @since 2.1.9
	 */
	public function seo_bar_ajax( $column, $post_id, $tax_id = '' ) {

		$is_term = false;

		/**
		 * Params are shifted.
		 * @link https://core.trac.wordpress.org/ticket/33521
		 */
		if ( '' !== $tax_id ) {
			$is_term = true;
			$column = $post_id;
		}

		if ( 'ad_seo' === $column ) {
			$context = __( 'Refresh to see the SEO Bar status.', 'autodescription' );

			$ajax_id = $column . $tax_id;

			echo $this->post_status_special( $context, '?', 'unknown', $is_term, $ajax_id );
		}

	}

	/**
	 * Wrap a single-line block for the SEO bar, showing special statuses.
	 *
	 * @param string $context The hover/screenreader context.
	 * @param string $symbol The single-character symbol.
	 * @param string $class The SEO block color code. : 'bad', 'okay', 'good', 'unknown'.
	 * @param int|null $ajax_id The unique Ajax ID to generate a small on-hover script for this ID. May be Arbitrary.
	 *
	 * @since 2.6.0
	 *
	 * @return string The special block with wrap.
	 */
	protected function post_status_special( $context, $symbol = '?', $color = 'unknown', $is_term = '', $ajax_id = null ) {

		$classes = $this->get_the_seo_bar_classes();

		$args = array();
		$args['class'] = $classes[$color];
		$args['width'] = $classes['100%'];
		$args['notice'] = $context;
		$args['indicator'] = $symbol;

		$block = $this->wrap_the_seo_bar_block( $args );

		if ( empty( $is_term ) )
			$is_term = $this->is_archive();

		return $this->get_the_seo_bar_wrap( $block, $is_term, $ajax_id );
	}

	/**
	 * Renders post status. Caches the output.
	 *
	 * @param int $post_id The Post ID or taxonomy ID
	 * @param string $type Is fetched on edit.php, inpost, taxonomies, etc.
	 * @param bool $html return the status in html or string
	 *
	 * @staticvar string $post_i18n The post type slug.
	 * @staticvar bool $is_term If we're dealing with TT pages.
	 *
	 * @since 2.1.9
	 * @return string $content the post SEO status
	 */
	public function post_status( $post_id = '', $type = 'inpost', $html = true ) {

		$content = '';

		//* Fetch Post ID if it hasn't been provided.
		if ( empty( $post_id ) )
			$post_id = $this->get_the_real_ID();

		//* Only run when post ID is found.
		if ( isset( $post_id ) && $post_id ) {

			//* Fetch Post Type.
			if ( 'inpost' === $type || '' === $type )
				$type = get_post_type( $post_id );

			//* No need to re-evalute these.
			static $post_i18n = null;
			static $is_term = null;

			$term = false;
			/**
			 * Static caching.
			 * @since 2.3.8
			 */
			if ( ! isset( $post_i18n ) && ! isset( $is_term ) ) {

				//* Setup i18n values for posts and pages.
				if ( 'post' === $type ) {
					$post_i18n = __( 'Post', 'autodescription' );
					$is_term = false;
					$term = false;
				} else if ( 'page' === $type ) {
					$post_i18n = __( 'Page', 'autodescription' );
					$is_term = false;
					$term = false;
				} else {
					/**
					 * Because of static caching, $is_term was never assigned.
					 * @since 2.4.1
					 */
					$is_term = true;
				}
			}

			if ( $is_term ) {
				//* We're on a term or taxonomy. Try fetching names. Default back to "Page".
				$term = get_term_by( 'id', $post_id, $type, OBJECT );
				$post_i18n = $this->get_the_term_name( $term );

				/**
				 * Check if current post type is a page or taxonomy.
				 * Only check if is_term is not yet changed to false. To save processing power.
				 *
				 * @since 2.3.1
				 */
				if ( $is_term && $this->is_post_type_page( $type ) )
					$is_term = false;
			}

			$post_low = $this->maybe_lowercase_noun( $post_i18n );

			$args = array(
				'is_term' => $is_term,
				'term' => $term,
				'post_id' => $post_id,
				'post_i18n' => $post_i18n,
				'post_low' => $post_low,
				'type' => $type,
			);

			if ( $is_term ) {
				return $this->the_seo_bar_term( $args );
			} else {
				return $this->the_seo_bar_page( $args );
			}
		} else {
			$context = __( 'Failed to fetch post ID.', 'autodescription' );

			return $this->post_status_special( $context, '!', 'bad' );
		}
	}

	/**
	 * Outputs a part of the SEO Bar based on parameters.
	 *
	 * @since 2.6.0
	 *
	 * @param array $args : {
	 *		string $indicator
	 *		string $notice
	 *		string $width
	 *		string $class
	 * }
	 *
	 * @return string The SEO Bar block part.
	 */
	protected function wrap_the_seo_bar_block( $args ) {

		$wrap 	= '<span class="ad-sec-wrap ' . $args['width'] . '">'
					. '<a onclick="return false;" class="' . $args['class'] . '" aria-label="' . $args['notice'] . '" data-desc="' . $args['notice'] . '">'
						. $args['indicator']
					. '</a>'
				. '</span>';

		return $wrap;
	}

	/**
	 * Wrap the SEO bar.
	 *
	 * @staticvar string $class
	 * @since 2.6.0
	 *
	 * @param string $content The SEO Bar content.
	 * @param bool $is_term Whether the bar is for a term.
	 * @param int|null $ajax_id The unique Ajax ID to generate a small on-hover script for.
	 *
	 * If Ajax ID is set, a small jQuery script will also be output to reset the
	 * DOM element for the status bar hover.
	 *
	 * @return string The SEO Bar wrapped.
	 */
	protected function get_the_seo_bar_wrap( $content, $is_term, $ajax_id = null ) {

		static $class = null;

		if ( is_null( $class ) ) {
			$classes = $this->get_the_seo_bar_classes();

			$width = $is_term ? ' ' . $classes['100%'] : '';
			$pill = $this->pill_the_seo_bar() ? ' ' . $classes['pill'] : '';

			$class = 'ad-seo clearfix' . $width . $pill;
		}

		if ( isset( $ajax_id ) ) {
			//* Ajax handler.
			$script = '<script>jQuery("#' . esc_attr( $ajax_id ) . '").on( "hover click", autodescription.statusBarHover );</script>';

			return sprintf( '<span class="%s" id="%s"><span class="ad-bar-wrap">%s</span></span>', $class, $ajax_id, $content ) . $script;
		}

		return sprintf( '<span class="%s"><span class="ad-bar-wrap">%s</span></span>', $class, $content );
	}

	/**
	 * Output the SEO bar for Terms and Taxonomies.
	 *
	 * @since 2.6.0
	 *
	 * @param array $args {
	 *	 'is_term' => bool $is_term,
	 *	 'term' => object $term,
	 *	 'post_id' => int $post_id,
	 *	 'post_i18n' => string $post_i18n,
	 *	 'post_low' => string $post_low,
	 *	 'type' => string $type,
	 * }
	 *
	 * @return string $content The SEO bar.
	 */
	protected function the_seo_bar_term( $args ) {

		$post_id = $args['post_id'];
		$term = $args['term'];
		$post = $args['post_i18n'];
		$is_term = true;

		$noindex = isset( $term->admeta['noindex'] ) && $this->is_checked( $term->admeta['noindex'] ) ? true : false;
		$redirect = false; // We don't apply redirect on taxonomies (yet)

		$ad_savedflag = isset( $term->admeta['saved_flag'] ) && $this->is_checked( $term->admeta['saved_flag'] ) ? true : false;
		$flag = $ad_savedflag;

		//* Genesis data fetch
		if ( false === $noindex && false === $flag && isset( $term->meta['noindex'] ) )
			$noindex = $this->is_checked( $term->meta['noindex'] ) ? true : false;

		//* Blocked SEO, return simple bar.
		if ( $redirect || $noindex )
			return $this->the_seo_bar_blocked( array( 'is_term' => $is_term, 'redirect' => $redirect, 'noindex' => $noindex, 'post_i18n' => $post ) );

		$title_notice		= $this->the_seo_bar_title_notice( $args );
		$description_notice	= $this->the_seo_bar_description_notice( $args );
		$index_notice 		= $this->the_seo_bar_index_notice( $args );
		$follow_notice		= $this->the_seo_bar_follow_notice( $args );
		$archive_notice		= $this->the_seo_bar_archive_notice( $args );

		$content = $title_notice . $description_notice . $index_notice . $follow_notice . $archive_notice;

		return $this->get_the_seo_bar_wrap( $content, $is_term );
	}

	/**
	 * Output the SEO bar for Terms and Taxonomies.
	 *
	 * @since 2.6.0
	 *
	 * @param array $args {
	 *	 'is_term' => $is_term,
	 *	 'term' => $term,
	 *	 'post_id' => $post_id,
	 *	 'post_i18n' => $post_i18n,
	 *	 'post_low' => $post_low,
	 *	 'type' => $type,
	 * }
	 *
	 * @return string $content The SEO bar.
	 */
	protected function the_seo_bar_page( $args ) {

		$post_id = $args['post_id'];
		$post = $args['post_i18n'];
		$is_term = false;
		$is_front_page = $this->is_static_frontpage( $post_id );

		$redirect = $this->get_custom_field( 'redirect', $post_id );
		$redirect = empty( $redirect ) ? false : true;

		$noindex = $this->get_custom_field( '_genesis_noindex', $post_id );
		$noindex = $this->is_checked( $noindex );

		if ( $is_front_page )
			$noindex = $this->is_option_checked( 'homepage_noindex' ) ? true : $noindex;

		if ( $redirect || $noindex )
			return $this->the_seo_bar_blocked( array( 'is_term' => $is_term, 'redirect' => $redirect, 'noindex' => $noindex, 'post_i18n' => $post ) );

		$title_notice		= $this->the_seo_bar_title_notice( $args );
		$description_notice	= $this->the_seo_bar_description_notice( $args );
		$index_notice 		= $this->the_seo_bar_index_notice( $args );
		$follow_notice		= $this->the_seo_bar_follow_notice( $args );
		$archive_notice		= $this->the_seo_bar_archive_notice( $args );
		$redirect_notice	= $this->the_seo_bar_redirect_notice( $args );

		$content = $title_notice . $description_notice . $index_notice . $follow_notice . $archive_notice . $redirect_notice;

		return $this->get_the_seo_bar_wrap( $content, $is_term );
	}

	/**
	 * Fetch the post or term data for The SEO Bar, structured and cached.
	 *
	 * @staticvar array $data
	 * @since 2.6.0
	 *
	 * @param array $args The term/post args.
	 *
	 * @return array $data {
	 *	 'title' => $title,
	 *	 'title_is_from_custom_field' => $title_is_from_custom_field,
	 *	 'description' => $description,
	 *	 'description_is_from_custom_field' => $description_is_from_custom_field,
	 *	 'nofollow' => $nofollow,
	 *	 'noarchive' => $noarchive
	 * }
	 */
	protected function the_seo_bar_data( $args ) {

		$post_id = $args['post_id'];

		static $data = array();

		if ( isset( $data[$post_id] ) )
			return $data[$post_id];

		if ( $args['is_term'] ) {
			return $data[$post_id] = $this->the_seo_bar_term_data( $args );
		} else {
			return $data[$post_id] = $this->the_seo_bar_post_data( $args );
		}
	}

	/**
	 * Fetch the term data for The SEO Bar.
	 *
	 * @staticvar array $data
	 * @since 2.6.0
	 *
	 * @param array $args The term args.
	 *
	 * @return array $data {
	 *	 'title' => $title,
	 *	 'title_is_from_custom_field' => $title_is_from_custom_field,
	 *	 'description' => $description,
	 *	 'description_is_from_custom_field' => $description_is_from_custom_field,
	 *	 'nofollow' => $nofollow,
	 *	 'noarchive' => $noarchive
	 * }
	 */
	protected function the_seo_bar_term_data( $args ) {

		$term = $args['term'];
		$post_id = $args['post_id'];
		$taxonomy = $args['type'];

		$flag = isset( $term->admeta['saved_flag'] ) && $this->is_checked( $term->admeta['saved_flag'] ) ? true : false;

		$title_custom_field = isset( $term->admeta['doctitle'] ) ? $term->admeta['doctitle'] : '';
		$description_custom_field = isset( $term->admeta['description'] ) ? $term->admeta['description'] : '';
		$nofollow = isset( $term->admeta['nofollow'] ) ? $term->admeta['nofollow'] : '';
		$noarchive = isset( $term->admeta['noarchive'] ) ? $term->admeta['noarchive'] : '';

		//* Genesis data fetch
		if ( false === $flag && isset( $term->meta ) ) {
			if ( empty( $title_custom_field ) && isset( $term->meta['doctitle'] ) )
				$title_custom_field = $term->meta['doctitle'];

			if ( empty( $description_custom_field ) && isset( $term->meta['description'] ) )
				$description_custom_field = $term->meta['description'];

			if ( empty( $nofollow ) && isset( $term->meta['nofollow'] ) )
				$nofollow = $term->meta['nofollow'];

			if ( empty( $noarchive ) && isset( $term->meta['noarchive'] ) )
				$noarchive = $term->meta['noarchive'];
		}

		$title_is_from_custom_field = (bool) $title_custom_field;
		if ( $title_is_from_custom_field ) {
			$title = $this->title( '', '', '', array( 'term_id' => $post_id, 'taxonomy' => $taxonomy, 'get_custom_field' => true ) );
		} else {
			$title = $this->title( '', '', '', array( 'term_id' => $post_id, 'taxonomy' => $taxonomy, 'get_custom_field' => false ) );
		}

		$description_is_from_custom_field = (bool) $description_custom_field;
		if ( $description_is_from_custom_field ) {
			$taxonomy = isset( $term->taxonomy ) && $term->taxonomy ? $term->taxonomy : false;
			$description_args = $taxonomy ? array( 'id' => $post_id, 'taxonomy' => $term->taxonomy, 'get_custom_field' => true ) : array( 'get_custom_field' => true );

			$description = $this->generate_description( '', $description_args );
		} else {
			$taxonomy = isset( $term->taxonomy ) && $term->taxonomy ? $term->taxonomy : false;
			$description_args = $taxonomy ? array( 'id' => $post_id, 'taxonomy' => $term->taxonomy, 'get_custom_field' => false ) : array( 'get_custom_field' => false );

			$description = $this->generate_description( '', $description_args );
		}

		$nofollow = $this->is_checked( $nofollow );
		$noarchive = $this->is_checked( $noarchive );

		return array(
			'title' => $title,
			'title_is_from_custom_field' => $title_is_from_custom_field,
			'description' => $description,
			'description_is_from_custom_field' => $description_is_from_custom_field,
			'nofollow' => $nofollow,
			'noarchive' => $noarchive
		);
	}

	/**
	 * Fetch the post data for The SEO Bar.
	 *
	 * @staticvar array $data
	 * @since 2.6.0
	 *
	 * @param array $args The post args.
	 *
	 * @return array $data {
	 *	 'title' => $title,
	 *	 'title_is_from_custom_field' => $title_is_from_custom_field,
	 *	 'description' => $description,
	 *	 'description_is_from_custom_field' => $description_is_from_custom_field,
	 *	 'nofollow' => $nofollow,
	 *	 'noarchive' => $noarchive
	 * }
	 */
	protected function the_seo_bar_post_data( $args ) {

		$post_id = $args['post_id'];
		$page_on_front = $this->is_static_frontpage( $post_id );

		$title_custom_field = $this->get_custom_field( '_genesis_title', $post_id );
		$description_custom_field = $this->get_custom_field( '_genesis_description', $post_id );
		$nofollow = $this->get_custom_field( '_genesis_nofollow', $post_id );
		$noarchive = $this->get_custom_field( '_genesis_noarchive', $post_id );

		if ( $page_on_front ) {
			$title_custom_field = $this->get_option( 'homepage_title' ) ? $this->get_option( 'homepage_title' ) : $title_custom_field;
			$description_custom_field = $this->get_option( 'homepage_description' ) ? $this->get_option( 'homepage_description' ) : $description_custom_field;
			$nofollow = $this->get_option( 'homepage_nofollow' ) ? $this->get_option( 'homepage_nofollow' ) : $nofollow;
			$noarchive = $this->get_option( 'homepage_noarchive' ) ? $this->get_option( 'homepage_noarchive' ) : $noarchive;
		}

		$title_is_from_custom_field = (bool) $title_custom_field;
		if ( $title_is_from_custom_field ) {
			$title = $this->title( '', '', '', array( 'term_id' => $post_id, 'page_on_front' => $page_on_front, 'get_custom_field' => true ) );
		} else {
			$title = $this->title( '', '', '', array( 'term_id' => $post_id, 'page_on_front' => $page_on_front, 'get_custom_field' => false ) );
		}

		$description_is_from_custom_field = (bool) $description_custom_field;
		if ( $description_is_from_custom_field ) {
			$description = $this->generate_description( '', array( 'id' => $post_id, 'get_custom_field' => true ) );
		} else {
			$description = $this->generate_description( '', array( 'id' => $post_id, 'get_custom_field' => false ) );
		}

		$nofollow = $this->is_checked( $nofollow );
		$noarchive = $this->is_checked( $noarchive );

		return array(
			'title' => $title,
			'title_is_from_custom_field' => $title_is_from_custom_field,
			'description' => $description,
			'description_is_from_custom_field' => $description_is_from_custom_field,
			'nofollow' => $nofollow,
			'noarchive' => $noarchive,
		);
	}

	/**
	 * Render the SEO bar title block and notice.
	 *
	 * @param array $args
	 * @since 2.6.0
	 *
	 * @return string The SEO Bar Title Block
	 */
	protected function the_seo_bar_title_notice( $args ) {

		//* Fetch data
		$data = $this->the_seo_bar_data( $args );
		$title 						= $data['title'];
		$title_is_from_custom_field	= $data['title_is_from_custom_field'];

		//* Fetch CSS classes.
		$classes = $this->get_the_seo_bar_classes();
		$ad25 = $classes['25%'];

		//* Fetch i18n and put in vars
		$i18n = $this->get_the_seo_bar_i18n();
		$title_short	= $i18n['title_short'];
		$generated		= $i18n['generated_short'];
		$and_i18n		= $i18n['and'];
		$but_i18n		= $i18n['but'];

		//* Initialize notice.
		$notice = $i18n['title'];
		$class = $classes['good'];

		//* Generated notice.
		$generated_notice = '<br>' . $i18n['generated'];
		$gen_t = $title_is_from_custom_field ? '' : $generated;
		$gen_t_notice = $title_is_from_custom_field ? '' : $generated_notice;

		//* Title length. Convert &#8230; to a single character as well.
		$tit_len = mb_strlen( html_entity_decode( $title ) );

		//* Length notice.
		$title_length_warning = $this->get_the_seo_bar_title_length_warning( $tit_len, $class );
		$notice .= $title_length_warning ? ' ' . $title_length_warning['notice'] : '';
		$class = $title_length_warning['class'];

		$title_duplicated = false;
		//* Check if title is duplicated from blogname.
		if ( $this->add_title_additions() ) {
			//* We are using blognames in titles.

			$blogname = $this->get_blogname();

			$first = stripos( $title, $blogname );
			$last = strripos( $title, $blogname );

			if ( $first !== $last )
				$title_duplicated = true;
		}

		if ( $title_duplicated ) {
			//* If the title is good, we should use And. Otherwise 'But'.
			$but_and = $title_length_warning['but'] ? $but_i18n : $and_i18n;

			/* translators: %s = But or And */
			$notice .= '<br>' . sprintf( __( '%s the Title contains the Blogname multiple times.', 'autodescription' ), $but_and );
			$class = $classes['bad'];
		}

		//* Put everything together.
		$notice = $notice . $gen_t_notice;
		$title_short = $title_short . $gen_t;

		$tit_wrap_args = array(
			'indicator' => $title_short,
			'notice' => $notice,
			'width' => $ad25,
			'class' => $class,
		);

		$title_notice = $this->wrap_the_seo_bar_block( $tit_wrap_args );

		return $title_notice;
	}

	/**
	 * Render the SEO bar description block and notice.
	 *
	 * @param array $args
	 * @since 2.6.0
	 *
	 * @return string The SEO Bar Description Block
	 */
	protected function the_seo_bar_description_notice( $args ) {

		//* Fetch data
		$data = $this->the_seo_bar_data( $args );
		$description 						= $data['description'];
		$description_is_from_custom_field 	= $data['description_is_from_custom_field'];

		//* Fetch i18n and put in vars
		$i18n = $this->get_the_seo_bar_i18n();
		$description_short 	= $i18n['description_short'];
		$generated_short 	= $i18n['generated_short'];

		//* Description length. Convert &#8230; to a single character as well.
		$desc_len = mb_strlen( html_entity_decode( $description ) );

		//* Fetch CSS classes.
		$classes = $this->get_the_seo_bar_classes();
		$ad25 = $classes['25%'];

		//* Initialize notice.
		$notice = $i18n['description'];
		$class = $classes['good'];

		//* Length notice.
		$desc_length_warning = $this->get_the_seo_bar_description_length_warning( $desc_len, $class );
		$notice .= $desc_length_warning['notice'] ? $desc_length_warning['notice'] . '<br>' : '';
		$class = $desc_length_warning['class'];

		//* Duplicated Words notice.
		$desc_too_many = $this->get_the_seo_bar_description_words_warning( $description, $class );
		$notice .= $desc_too_many['notice'] ? $desc_too_many['notice'] . '<br>' : '';
		$class = $desc_too_many['class'];

		//* Generation notice.
		$generated_notice = $i18n['generated'] . ' ';
		$gen_d = $description_is_from_custom_field ? '' : $generated_short;
		$gen_d_notice = $description_is_from_custom_field ? '' : $generated_notice;

		//* Put everything together.
		$notice = $notice . $gen_d_notice;
		$description_short = $description_short . $gen_d;

		$desc_wrap_args = array(
			'indicator' => $description_short,
			'notice' => $notice,
			'width' => $ad25,
			'class' => $class,
		);

		$description_notice = $this->wrap_the_seo_bar_block( $desc_wrap_args );

		return $description_notice;
	}

	/**
	 * Description Length notices.
	 *
	 * @param int $desc_len The Title length
	 * @param string $class The current color class.
	 *
	 * @since 2.6.0
	 *
	 * @return array {
	 * 		notice => The notice,
	 * 		class => The class,
	 * }
	 */
	protected function get_the_seo_bar_description_length_warning( $desc_len, $class ) {

		$classes = $this->get_the_seo_bar_classes();
		$bad	= $classes['bad'];
		$okay	= $classes['okay'];
		$good	= $classes['good'];

		if ( $desc_len < 100 ) {
			$notice = ' ' . __( 'Length is far too short.', 'autodescription' );
			$class = $bad;
		} else if ( $desc_len < 137 ) {
			$notice = ' ' . __( 'Length is too short.', 'autodescription' );

			// Don't make it okay if it's already bad.
			$class = $bad === $class ? $class : $okay;
		} else if ( $desc_len > 155 && $desc_len < 175 ) {
			$notice = ' ' . __( 'Length is too long.', 'autodescription' );

			// Don't make it okay if it's already bad.
			$class = $bad === $class ? $class : $okay;
		} else if ( $desc_len >= 175 ) {
			$notice = ' ' . __( 'Length is far too long.', 'autodescription' );
			$class = $bad;
		} else {
			$notice = ' ' . __( 'Length is good.', 'autodescription' );

			// Don't make it good if it's already bad or okay.
			$class = $good !== $class ? $class : $good;
		}

		return array(
			'notice' => $notice,
			'class' => $class
		);
	}

	/**
	 * Calculates the word count and returns a warning with the words used.
	 * Only when count is over 3.
	 *
	 * @param string $description The Description with maybe words too many.
	 * @param string $class The current color class.
	 *
	 * @since 2.6.0
	 *
	 * @return string The warning notice.
	 */
	protected function get_the_seo_bar_description_words_warning( $description, $class ) {

		$notice = '';
		$desc_too_many = '';

		//* Convert description's special characters into PHP readable words.
		$description = htmlentities( $description, ENT_COMPAT );

		//* Because we've converted all characters to XHTML codes, the odd ones should be only numerical.
		$html_special_chars = '&0123456789;';

		//* Count the words.
		$desc_words = str_word_count( strtolower( $description ), 2, $html_special_chars );

		static $bother_me_length = null;
		/**
		 * Applies filters 'the_seo_framework_bother_me_desc_length' : int Min Character length to bother you with.
		 * @since 2.6.0
		 */
		if ( is_null( $bother_me_length ) )
			$bother_me_length = (int) apply_filters( 'the_seo_framework_bother_me_desc_length', 3 );

		if ( is_array( $desc_words ) ) {
			//* We're going to fetch word based on key, and the last element (as first)
			$word_keys = array_flip( array_reverse( $desc_words, true ) );

			$desc_word_count = array_count_values( $desc_words );

			//* Parse word counting.
			if ( is_array( $desc_word_count ) ) {
				foreach ( $desc_word_count as $desc_word => $desc_word_count ) {

					if ( mb_strlen( html_entity_decode( $desc_word ) ) < $bother_me_length ) {
						$run = $desc_word_count >= 5 ? true : false;
					} else {
						$run = $desc_word_count >= 3 ? true : false;
					}

					if ( $run ) {
						//* The encoded word is longer or equal to the bother lenght.

						$word_len = mb_strlen( $desc_word );

						$position = $word_keys[$desc_word];
						$first_word_original = mb_substr( $description, $position, $word_len );

						//* Found words that are used too frequently.
						$desc_too_many[] = array( $first_word_original => $desc_word_count );
					}
				}
			}
		}

		if ( '' !== $desc_too_many && is_array( $desc_too_many ) ) {

			$classes = $this->get_the_seo_bar_classes();
			$bad = $classes['bad'];
			$okay = $classes['okay'];

			$words_count = count( $desc_too_many );
			//* Don't make it okay if it's already bad.
			$class = $bad !== $class && $words_count <= 1 ? $okay : $bad;

			$i = 1;
			$count = count( $desc_too_many );
			foreach ( $desc_too_many as $desc_array ) {
				foreach ( $desc_array as $desc_value => $desc_count ) {
					$notice .= ' ';

					/**
					 * Don't ucfirst abbrivations.
					 * @since 2.4.1
					 */
					$desc_value = ctype_upper( $desc_value ) ? $desc_value : ucfirst( $desc_value );

					$notice .= sprintf( __( '%s is used %d times.', 'autodescription' ), '<span>' . $desc_value . '</span>', $desc_count );

					//* Don't add break at last occurence.
					$notice .= $i === $count ? '' : '<br>';
					$i++;
				}
			}
		}

		return array(
			'notice' => $notice,
			'class' => $class
		);
	}

	/**
	 * Render the SEO bar index block and notice.
	 *
	 * @param array $args
	 * @since 2.6.0
	 *
	 * @return string The SEO Bar Index Block
	 */
	protected function the_seo_bar_index_notice( $args ) {

		$term = $args['term'];
		$is_term = $args['is_term'];
		$post_i18n = $args['post_i18n'];

		$data = $this->the_seo_bar_data( $args );

		$classes = $this->get_the_seo_bar_classes();
		$unknown	= $classes['unknown'];
		$bad		= $classes['bad'];
		$okay		= $classes['okay'];
		$good		= $classes['good'];
		$ad_125		= $classes['12.5%'];

		$i18n = $this->get_the_seo_bar_i18n();
		$index_short	= $i18n['index_short'];
		$but_i18n		= $i18n['but'];
		$and_i18n		= $i18n['and'];
		$ind_notice		= $i18n['index'];

		$ind_notice .= ' ' . sprintf( __( "%s is being indexed.", 'autodescription' ), $post_i18n );
		$ind_class = $good;

		/**
		 * Get noindex site option
		 *
		 * @since 2.2.2
		 */
		if ( $this->is_option_checked( 'site_noindex' ) ) {
			$ind_notice .= '<br>' . __( "But you've disabled indexing for the whole site.", 'autodescription' );
			$ind_class = $unknown;
			$ind_but = true;
		}

		//* Adds notice for global archive indexing options.
		if ( $is_term ) {

			/**
			 * @staticvar bool $checked
			 * @staticvar string $label
			 */
			static $checked = null;

			if ( ! isset( $checked ) ) {
				//* Fetch whether it's checked.
				$checked = $this->the_seo_bar_archive_robots_options( 'noindex' );
			}

			if ( $checked ) {
				$but_and = isset( $ind_but ) ? $and_i18n : $but_i18n;
				$label = $this->get_the_term_name( $term, false );

				/* translators: 1: But or And, 2: Current taxonomy term plural label */
				$ind_notice .= '<br>' . sprintf( __( '%1$s indexing for %2$s have been disabled.', 'autodescription' ), $but_and, $label );
				$ind_class = $unknown;
				$ind_but = true;
			}
		}

		//* Adds notice for WordPress blog public indexing.
		if ( false === $this->is_blog_public() ) {
			$but_and = isset( $ind_but ) ? $and_i18n : $but_i18n;
			/* translators: %s = But or And */
			$ind_notice .= '<br>' . sprintf( __( "%s the blog isn't set to public. This means WordPress discourages indexing.", 'autodescription' ), $but_and );
			$ind_class = $bad;
			$ind_but = true;
		}

		/**
		 * Check if archive is empty, and therefore has set noindex for those.
		 *
		 * @since 2.2.8
		 */
		if ( $is_term && isset( $term->count ) && 0 === $term->count ) {
			$but_and = isset( $ind_but ) ? $and_i18n : $but_i18n;

			/* translators: %s = But or And */
			$ind_notice .= '<br>' . sprintf( __( "%s there are no posts in this term; therefore, indexing has been disabled.", 'autodescription' ), $but_and );
			//* Don't make it unknown if it's not good.
			$ind_class = $ind_class !== $good ? $ind_class : $unknown;
		}

		$ind_wrap_args = array(
			'indicator' => $index_short,
			'notice' => $ind_notice,
			'width' => $ad_125,
			'class' => $ind_class,
		);

		$index_notice = $this->wrap_the_seo_bar_block( $ind_wrap_args );

		return $index_notice;
	}

	/**
	 * Checks whether global index/archive/follow options are checked for archives.
	 *
	 * @since 2.6.0
	 * @staticvar bool $cache
	 *
	 * @param string $type : 'noindex', 'nofollow', 'noarchive'
	 *
	 * @return bool
	 */
	protected function the_seo_bar_archive_robots_options( $type ) {

		$taxonomy = false;

		if ( $this->is_category() )
			$taxonomy = 'category';

		if ( $this->is_tag() )
			$taxonomy = 'tag';

		if ( $taxonomy ) {
			static $cache = array();

			if ( isset( $cache[$type][$taxonomy] ) )
				return $cache[$type][$taxonomy];

			if ( $this->is_option_checked( $taxonomy . '_' . $type ) )
				return $cache[$type][$taxonomy] = true;

			return $cache[$type][$taxonomy] = false;
		}

		return false;
	}

	/**
	 * Render the SEO bar follow block and notice.
	 *
	 * @param array $args
	 * @since 2.6.0
	 *
	 * @return string The SEO Bar Follow Block
	 */
	protected function the_seo_bar_follow_notice( $args ) {

		$followed = true;

		$term = $args['term'];
		$is_term = $args['is_term'];
		$post_i18n = $args['post_i18n'];

		$data = $this->the_seo_bar_data( $args );
		$nofollow = $data['nofollow'];

		$classes = $this->get_the_seo_bar_classes();
		$unknown	= $classes['unknown'];
		$bad		= $classes['bad'];
		$okay		= $classes['okay'];
		$good		= $classes['good'];
		$ad_125		= $classes['12.5%'];

		$i18n = $this->get_the_seo_bar_i18n();
		$follow_i18n	= $i18n['follow'];
		$but_i18n		= $i18n['but'];
		$and_i18n		= $i18n['and'];
		$follow_short	= $i18n['follow_short'];

		if ( $nofollow ) {
			$fol_notice = $follow_i18n . ' ' . sprintf( __( "%s links aren't being followed.", 'autodescription' ), $post_i18n );
			$fol_class = $unknown;
			$fol_but = true;

			$followed = false;
		} else {
			$fol_notice = $follow_i18n . ' ' . sprintf( __( '%s links are being followed.', 'autodescription' ), $post_i18n );
			$fol_class = $good;
		}

		/**
		 * Get nofolow site option
		 *
		 * @since 2.2.2
		 */
		if ( $this->is_option_checked( 'site_nofollow' ) ) {
			$but_and = isset( $fol_but ) ? $and_i18n : $but_i18n;
			/* translators: %s = But or And */
			$fol_notice .= '<br>' . sprintf( __( "%s you've disabled the following of links for the whole site.", 'autodescription' ), $but_and );
			$fol_class = $unknown;
			$fol_but = true;

			$followed = false;
		}

		//* Adds notice for global archive indexing options.
		if ( $is_term ) {

			/**
			 * @staticvar bool $checked
			 * @staticvar string $label
			 */
			static $checked = null;

			if ( ! isset( $checked ) ) {
				//* Fetch whether it's checked.
				$checked = $this->the_seo_bar_archive_robots_options( 'nofollow' );
			}

			if ( $checked ) {
				$but_and = isset( $fol_but ) ? $and_i18n : $but_i18n;
				$label = $this->get_the_term_name( $term, false );

				/* translators: 1: But or And, 2: Current taxonomy term plural label */
				$fol_notice .= '<br>' . sprintf( __( '%1$s following for %2$s have been disabled.', 'autodescription' ), $but_and, $label );
				$fol_class = $unknown;

				$followed = false;
			}
		}

		if ( false === $this->is_blog_public() ) {
			//* Make it "and" if following has not been disabled otherwise.
			$but_and = $followed || ! isset( $fol_but ) ? $and_i18n : $but_i18n;

			/* translators: %s = But or And */
			$fol_notice .= '<br>' . sprintf( __( "%s the blog isn't set to public. This means WordPress allows the links to be followed regardless.", 'autodescription' ), $but_and );
			$fol_class = $followed ? $fol_class : $okay;
			$fol_but = true;

			$followed = false;
		}

		$fol_wrap_args = array(
			'indicator' => $follow_short,
			'notice' => $fol_notice,
			'width' => $ad_125,
			'class' => $fol_class,
		);

		$follow_notice = $this->wrap_the_seo_bar_block( $fol_wrap_args );

		return $follow_notice;
	}

	/**
	 * Render the SEO bar archive block and notice.
	 *
	 * @param array $args
	 * @since 2.6.0
	 *
	 * @return string The SEO Bar Follow Block
	 */
	protected function the_seo_bar_archive_notice( $args ) {

		$archived = true;

		$term = $args['term'];
		$is_term = $args['is_term'];
		$post_low = $args['post_low'];

		$data = $this->the_seo_bar_data( $args );
		$noarchive	= $data['noarchive'];

		$classes = $this->get_the_seo_bar_classes();
		$unknown	= $classes['unknown'];
		$bad		= $classes['bad'];
		$okay		= $classes['okay'];
		$good		= $classes['good'];
		$ad_125		= $classes['12.5%'];

		$i18n = $this->get_the_seo_bar_i18n();
		$archive_i18n	= $i18n['archive'];
		$but_i18n		= $i18n['but'];
		$and_i18n		= $i18n['and'];
		$archive_short	= $i18n['archive_short'];

		if ( $noarchive ) {
			$arc_notice = $archive_i18n . ' ' . sprintf( __( "Search Engine aren't allowed to archive this %s.", 'autodescription' ), $post_low );
			$arc_class = $unknown;
			$archived = false;
			$arc_but = true;
		} else {
			$arc_notice = $archive_i18n . ' ' . sprintf( __( 'Search Engine are allowed to archive this %s.', 'autodescription' ), $post_low );
			$arc_class = $good;
		}

		/**
		 * Get noarchive site option
		 *
		 * @since 2.2.2
		 */
		if ( $this->is_option_checked( 'site_noarchive' ) ) {
			$but_and = isset( $arc_but ) ? $and_i18n : $but_i18n;

			$arc_notice .= '<br>' . sprintf( __( "But you've disabled archiving for the whole site.", 'autodescription' ), $but_and );
			$arc_class = $unknown;
			$arc_but = true;

			$archived = false;
		}

		//* Adds notice for global archive indexing options.
		if ( $is_term ) {

			/**
			 * @staticvar bool $checked
			 * @staticvar string $label
			 */
			static $checked = null;

			if ( ! isset( $checked ) ) {
				//* Fetch whether it's checked.
				$checked = $this->the_seo_bar_archive_robots_options( 'noarchive' );
			}

			if ( $checked ) {
				$but_and = isset( $arc_but ) ? $and_i18n : $but_i18n;
				$label = $this->get_the_term_name( $term, false );

				/* translators: 1: But or And, 2: Current taxonomy term plural label */
				$arc_notice .= '<br>' . sprintf( __( '%1$s archiving for %2$s have been disabled.', 'autodescription' ), $but_and, $label );
				$arc_class = $unknown;
				$arc_but = true;

				$archived = false;
			}
		}

		if ( false === $this->is_blog_public() ) {
			//* Make it "and" if archiving has not been disabled otherwise.
			$but_and = $archived || ! isset( $arc_but ) ? $and_i18n : $but_i18n;

			/* translators: %s = But or And */
			$arc_notice .= '<br>' . sprintf( __( "%s the blog isn't set to public. This means WordPress allows the blog to be archived regardless.", 'autodescription' ), $but_and );
			$arc_but = true;

			$arc_class = $archived ? $arc_class : $okay;
			$archived = true;
		}

		$arc_wrap_args = array(
			'indicator' => $archive_short,
			'notice' => $arc_notice,
			'width' => $ad_125,
			'class' => $arc_class,
		);

		$archive_notice = $this->wrap_the_seo_bar_block( $arc_wrap_args );

		return $archive_notice;
	}

	/**
	 * Render the SEO bar redirect block and notice.
	 *
	 * @param array $args
	 * @since 2.6.0
	 *
	 * @return string The SEO Bar Redirect Block
	 */
	protected function the_seo_bar_redirect_notice( $args ) {

		$is_term = $args['is_term'];

		if ( $is_term ) {
			//* No redirection on taxonomies (yet).
			$redirect_notice = '';
		} else {
			//* Pretty much outputs that it's not being redirected.

			$post = $args['post_i18n'];

			$classes = $this->get_the_seo_bar_classes();
			$ad_125 = $classes['12.5%'];

			$i18n = $this->get_the_seo_bar_i18n();
			$redirect_i18n = $i18n['redirect'];
			$redirect_short = $i18n['redirect_short'];

			$red_notice = $redirect_i18n . ' ' . sprintf( __( "%s isn't being redirected.", 'autodescription' ), $post );
			$red_class = $classes['good'];

			$red_wrap_args = array(
				'indicator' => $redirect_short,
				'notice' => $red_notice,
				'width' => $ad_125,
				'class' => $red_class,
			);

			$redirect_notice = $this->wrap_the_seo_bar_block( $red_wrap_args );
		}

		return $redirect_notice;
	}

	/**
	 * Render the SEO bar when the page/term is blocked.
	 *
	 * @param array $args {
	 *		$is_term => bool,
	 *		$redirect => bool,
	 *		$noindex => bool,
	 *		$post_i18n => string
	 * }
	 *
	 * @since 2.6.0
	 *
	 * @return string The SEO Bar
	 */
	protected function the_seo_bar_blocked( $args ) {

		$classes = $this->get_the_seo_bar_classes();
		$i18n = $this->get_the_seo_bar_i18n();

		$is_term = $args['is_term'];
		$redirect = $args['redirect'];
		$noindex = $args['noindex'];
		$post = $args['post_i18n'];

		if ( $redirect && $noindex ) {
			//* Redirect and noindex found, why bother showing SEO info?

			$red_notice = $i18n['redirect'] . ' ' . sprintf( __( "%s is being redirected. This means no SEO values have to be set.", 'autodescription' ), $post );
			$red_class = $classes['unknown'];

			$noi_notice = $i18n['index'] . ' ' . sprintf( __( "%s is not being indexed. This means no SEO values have to be set.", 'autodescription' ), $post );
			$noi_class = $classes['unknown'];

			$red_wrap_args = array(
				'indicator' => $i18n['redirect_short'],
				'notice' => $red_notice,
				'width' => $classes['50%'],
				'class' => $red_class,
			);

			$noi_wrap_args = array(
				'indicator' => $i18n['index_short'],
				'notice' => $noi_notice,
				'width' => $classes['50%'],
				'class' => $noi_class,
			);

			$redirect_notice = $this->wrap_the_seo_bar_block( $red_wrap_args );
			$noindex_notice = $this->wrap_the_seo_bar_block( $noi_wrap_args );

			$content = $redirect_notice . $noindex_notice;

			return $this->get_the_seo_bar_wrap( $content, $is_term );
		} else if ( $redirect && false === $noindex ) {
			//* Redirect found, why bother showing SEO info?

			$red_notice = $i18n['redirect'] . ' ' . sprintf( __( "%s is being redirected. This means no SEO values have to be set.", 'autodescription' ), $post );
			$red_class = $classes['unknown'];

			$red_wrap_args = array(
				'indicator' => $i18n['redirect_short'],
				'notice' => $red_notice,
				'width' => $classes['100%'],
				'class' => $red_class,
			);

			$redirect_notice = $this->wrap_the_seo_bar_block( $red_wrap_args );

			return $this->get_the_seo_bar_wrap( $redirect_notice, $is_term );
		} else if ( $noindex && false === $redirect ) {
			//* Noindex found, why bother showing SEO info?

			$noi_notice = $i18n['index'] . ' ' . sprintf( __( "%s is not being indexed. This means no SEO values have to be set.", 'autodescription' ), $post );
			$noi_class = $classes['unknown'];

			$noi_wrap_args = array(
				'indicator' => $i18n['index_short'],
				'notice' => $noi_notice,
				'width' => $classes['100%'],
				'class' => $noi_class,
			);

			$noindex_notice = $this->wrap_the_seo_bar_block( $noi_wrap_args );

			return $this->get_the_seo_bar_wrap( $noindex_notice, $is_term );
		}

		return '';
	}

	/**
	 * Title Length notices.
	 *
	 * @since 2.6.0
	 *
	 * @param int $tit_len The Title length
	 * @param string $class The Current Title notification class.
	 *
	 * @return array {
	 * 		string $notice => The notice,
	 * 		string $class => The class,
	 * 		bool $but => Whether we should use but or and,
	 * }
	 */
	protected function get_the_seo_bar_title_length_warning( $tit_len, $class ) {

		$classes = $this->get_the_seo_bar_classes();
		$bad	= $classes['bad'];
		$okay	= $classes['okay'];
		$good	= $classes['good'];

		$but = false;

		if ( $tit_len < 25 ) {
			$notice = ' ' . __( 'Length is far too short.', 'autodescription' );
			$class = $bad;
		} else if ( $tit_len < 42 ) {
			$notice = ' ' . __( 'Length is too short.', 'autodescription' );
			$class = $okay;
		} else if ( $tit_len > 55 && $tit_len < 75 ) {
			$notice = ' ' . __( 'Length is too long.', 'autodescription' );
			$class = $okay;
		} else if ( $tit_len >= 75 ) {
			$notice = ' ' . __( 'Length is far too long.', 'autodescription' );
			$class = $bad;
		} else {
			$notice = ' ' . __( 'Length is good.', 'autodescription' );
			$class = $good;
			$but = true;
		}

		return array(
			'notice' => $notice,
			'class' => $class,
			'but' => $but
		);
	}

	/**
	 * Returns an array of the classes used for CSS within The SEO Bar.
	 *
	 * @since 2.6.0
	 *
	 * @return array The class names.
	 */
	public function get_the_seo_bar_classes() {
		return array(
			'bad' 		=> 'ad-seo-bad',
			'okay' 		=> 'ad-seo-okay',
			'good' 		=> 'ad-seo-good',
			'unknown' 	=> 'ad-seo-unknown',

			'pill' => 'pill',

			'100%' 	=> 'ad-100',
			'60%' 	=> 'ad-60',
			'50%' 	=> 'ad-50',
			'40%' 	=> 'ad-40',
			'33%' 	=> 'ad-33',
			'25%' 	=> 'ad-25',
			'25%' 	=> 'ad-25',
			'20%' 	=> 'ad-20',
			'16%' 	=> 'ad-16',
			'12.5%' => 'ad-12-5',
			'11%' 	=> 'ad-11',
			'10%' 	=> 'ad-10',
		);
	}

	/**
	 * Returns an array of the i18n notices for The SEO Bar.
	 *
	 * @staticvar array $i18n
	 * @since 2.6.0
	 *
	 * @return array The i18n sentences.
	 */
	public function get_the_seo_bar_i18n() {

		static $i18n = null;

		if ( isset( $i18n ) )
			return $i18n;

		return $i18n = array(
			'title'			=> __( 'Title:', 'autodescription' ),
			'description' 	=> __( 'Description:', 'autodescription' ),
			'index'			=> __( 'Index:', 'autodescription' ),
			'follow'		=> __( 'Follow:', 'autodescription' ),
			'archive'		=> __( 'Archive:', 'autodescription' ),
			'redirect'		=> __( 'Redirect:', 'autodescription' ),

			'generated' => __( 'Generated: Automatically generated.', 'autodescription'),

			'generated_short'	=> _x( 'G', 'Generated', 'autodescription' ),
			'title_short'		=> _x( 'T', 'Title', 'autodescription' ),
			'description_short'	=> _x( 'D', 'Description', 'autodescription' ),
			'index_short'		=> _x( 'I', 'no-Index', 'autodescription' ),
			'follow_short'		=> _x( 'F', 'no-Follow', 'autodescription' ),
			'archive_short'		=> _x( 'A', 'no-Archive', 'autodescription' ),
			'redirect_short'	=> _x( 'R', 'Redirect', 'autodescription' ),

			'but' => _x( 'But', 'But there are...', 'autodescription' ),
			'and' => _x( 'And', 'And there are...', 'autodescription' ),
		);
	}

	/**
	 * Whether to square or pill the seo bar.
	 *
	 * Applies filters 'the_seo_framework_seo_bar_pill' : boolean
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 *
	 * @return bool
	 */
	protected function pill_the_seo_bar() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		//* TODO add option.
		$filter = (bool) apply_filters( 'the_seo_framework_seo_bar_pill', false );

		return $cache = $filter ? true : false;
	}

}
