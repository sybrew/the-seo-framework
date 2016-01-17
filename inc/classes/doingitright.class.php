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
	 * Initalizes columns
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'current_screen', array( $this, 'init_columns' ) );
	}

	/**
	 * Initializes columns
	 *
	 * Applies filter the_seo_framework_show_seo_column : Show the SEO column in edit.php
	 *
	 * @param array $support_admin_pages the supported admin pages
	 *
	 * @since 2.1.9
	 */
	public function init_columns() {

		/**
		 * New filter.
		 * @since 2.3.0
		 *
		 * Removed previous filter.
		 * @since 2.3.5
		 */
		$show_seo_column = (bool) apply_filters( 'the_seo_framework_show_seo_column', true );

		if ( $show_seo_column && $this->post_type_supports_custom_seo() ) {
			global $current_screen;

			$id = isset( $current_screen->id ) ? $current_screen->id : '';

			if ( ! empty( $id ) ) {

				$type = $id;
				$slug = substr( $id, (int) 5 );

				if ( 'post' !== $type && 'page' !== $type ) {
					add_action( "manage_{$type}_columns", array( $this, 'add_column' ), 10, 1 );
					add_action( "manage_{$slug}_custom_column", array( $this, 'seo_column' ), 10, 3 );
				}

				/**
				 * Always load pages and posts.
				 * Many plugins rely on these.
				 */
				add_action( 'manage_posts_columns', array( $this, 'add_column' ), 10, 1 );
				add_action( 'manage_pages_columns', array( $this, 'add_column' ), 10, 1 );
				add_action( 'manage_posts_custom_column', array( $this, 'seo_column' ), 10, 3 );
				add_action( 'manage_pages_custom_column', array( $this, 'seo_column' ), 10, 3 );
			}

		}

	}

	/**
	 * Adds SEO column on edit.php
	 *
	 * @param $offset 	determines where the column should be placed. Prefered before comments, then data, then tags.
	 *					If neither found, it will add the column to the end.
	 *
	 * @since 2.1.9
	 * @return array $columns the column data
	 */
	public function add_column( $columns ) {

		$seocolumn = array( 'ad_seo' => 'SEO' );

		$column_keys = array_keys( $columns );

		// Try comments
		$offset = array_search( 'comments', $column_keys );

		// Try Count (posts) on taxonomies
		if ( false === $offset )
			$offset = array_search( 'posts', $column_keys );

		// Try date
		if ( false === $offset )
			$offset = array_search( 'date', $column_keys );

		// Try tags
		if ( false === $offset )
			$offset = array_search( 'tags', $column_keys );

		// Try bbPress Topic Freshness
		if ( false === $offset )
			$offset = array_search( 'bbp_topic_freshness', $column_keys );

		// Try bbPress Forum Freshness
		if ( false === $offset )
			$offset = array_search( 'bbp_forum_freshness', $column_keys );

		// I tried but found nothing
		if ( false === $offset ) {
			//* Add SEO bar at the end of columns.
			$columns = array_merge( $columns, $seocolumn );
		} else {
			//* Add seo bar between columns.

			// Cache columns.
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
	 * Adds SEO column to two to the left.
	 *
	 * @param string $column the current column    : If it's a taxonomy, this is empty
	 * @param int $post_id the post id             : If it's a taxonomy, this is the column name
	 * @param string $tax_id this is empty         : If it's a taxonomy, this is the taxonomy id
	 * @param string $status the status in html
	 *
	 * @since 2.1.9
	 * @return array $columns the column data
	 */
	public function seo_column( $column, $post_id, $tax_id = '' ) {

		$status = '';

		$type = get_post_type( $post_id );

		// It's a bug in WP? I'll report.
		// Reported: https://core.trac.wordpress.org/ticket/33521
		if ( ! $type || ! empty( $tax_id ) ) {
			$column = $post_id;
			$post_id = $tax_id;

			$screen = (object) get_current_screen();

			if ( isset( $screen->taxonomy ) )
				$type = $screen->taxonomy;
		}

		if ( 'ad_seo' === $column )
			$status = $this->post_status( $post_id, $type, true );

		echo $status;
	}

	/**
	 * Renders post status. Caches the output.
	 *
	 * Applies filter the_seo_framework_seo_bar_squared : Make the SEO Bar squared.
	 *
	 * @param int $post_id The Post ID or taxonomy ID
	 * @param string $type Is fetched on edit.php, inpost, taxonomies, etc.
	 * @param bool $html return the status in html or string
	 *
	 * @todo document this further. It's been created within a day.
	 *
	 * @staticvar string $post The post type slug.
	 * @staticvar bool $is_term If we're dealing with tt pages.
	 * @staticvar object $term The Term object.
	 *
	 * @since 2.1.9
	 * @return string $content the post SEO status
	 */
	protected function post_status( $post_id = '', $type = 'inpost', $html = true ) {

		$content = '';
		$desclen_class = '';

		//* Fetch Post ID if it hasn't been provided.
		if ( empty( $post_id ) )
			$post_id = $this->get_the_real_ID();

		//* Only run when post ID is found.
		if ( isset( $post_id ) && ! empty( $post_id ) ) {

			//* Fetch Post Type.
			if ( empty( $type ) || 'inpost' === $type )
				$type = get_post_type( $post_id );

			//* No need to re-evalute these.
			static $post = null;
			static $is_term = null;

			/**
			 * Static caching.
			 * @since 2.3.8
			 */
			if ( ! isset( $post ) && ! isset( $is_term ) ) {
				//* Setup i18n values for posts and pages.
				if ( $type == 'post' ) {
					$post = __( 'Post', 'autodescription' );
					$is_term = false;
					$term = false;
				} else if ( $type == 'page' ) {
					$post = __( 'Page', 'autodescription' );
					$is_term = false;
					$term = false;
				} else {
					/**
					 * Because of static caching, $is_term was never assigned.
					 * @bugfix.
					 *
					 * @since 2.4.1
					 */
					$is_term = true;
				}
			}

			if ( $is_term ) {
				//* We're on a term or taxonomy. Try fetching names. Default back to "Page".

				$term = get_term_by( 'id', $post_id, $type, OBJECT );

				if ( ! empty( $term ) && is_object( $term ) ) {
					$tax_type = $term->taxonomy;

					/**
					 * Dynamically fetch the term name.
					 *
					 * @since 2.3.1
					 */
					$term_labels = $this->get_tax_labels( $tax_type );

					if ( isset( $term_labels ) ) {
						$post = $term_labels->singular_name;
					} else {
						// Fallback to Page as it is generic.
						$post = __( 'Page', 'autodescription' );
					}

				} else {
					// Fallback to Page as it is generic.
					$post = __( 'Page', 'autodescription' );
				}

				/**
				 * Check if current post type is a page or taxonomy.
				 * Only check if is_term is not yet changed to false. To save processing power.
				 *
				 * @since 2.3.1
				 */
				if ( $is_term && $this->is_post_type_page( $type ) )
					$is_term = false;
			}

			/**
			 * Square the SEO Bar.
			 *
			 * New filter.
			 * @since 2.3.0
			 *
			 * Removed previous filter.
			 * @since 2.3.5
			 */
			$square_it = (bool) apply_filters( 'the_seo_framework_seo_bar_squared', false );
			$square = $square_it ? ' square' : '';

			//* German Capitalization compat.
			$post_low = $this->is_locale( 'de' ) ? $post : strtolower( $post );

			$is_front_page = $this->is_static_frontpage( $post_id );

			//* CSS class values for colors
			$bad = 'ad-seo-bad';
			$okay = 'ad-seo-okay';
			$good = 'ad-seo-good';
			$unknown = 'ad-seo-unknown';

			//* All notices.
			$titlen_notice = '';
			$desclen_notice = '';
			$title_notice = '';
			$description_notice = '';
			$redirect_notice = '';
			$noindex_notice = '';
			$desc_too_many = '';

			//* i18n
			$title_i18n = __( 'Title:', 'autodescription' );
			$description_i18n = __( 'Description:', 'autodescription' );
			$index_i18n = __( 'Index:', 'autodescription' );
			$follow_i18n = __( 'Follow:', 'autodescription' );
			$archive_i18n = __( 'Archive:', 'autodescription' );
			$redirect_i18n = __( 'Redirect:', 'autodescription' );

			if ( ! $is_term ) {
				$redirect = $this->get_custom_field( 'redirect' );
				$noindex = $this->get_custom_field( '_genesis_noindex' );

				if ( $is_front_page )
					$noindex = $this->get_option( 'homepage_noindex' ) ? $this->get_option( 'homepage_noindex' ) : $noindex;

				$ad_125 = 'ad-12-5';
				$ad_100 = '';
			} else {
				$ad_savedflag = $term->admeta['saved_flag'] != '0' ? true : false;
				$flag = (bool) $ad_savedflag;

				$noindex = isset( $term->admeta['noindex'] ) ? $term->admeta['noindex'] : '';
				$redirect = ''; // We don't apply redirect on taxonomies (yet)

				//* Genesis data fetch
				if ( empty( $noindex ) && ! $flag && isset( $term->meta ) )
					$noindex = isset( $term->meta['noindex'] ) ? $term->meta['noindex'] : '';

				$ad_125 = 'ad-16';
				$ad_100 = 'ad-100';
			}

			if ( empty( $redirect ) && empty( $noindex ) ) {
				//* No redirect or noindex found, proceed.

				if ( ! $is_term ) {
					$title_custom_field = (bool) $this->get_custom_field( '_genesis_title' );

					$description = $this->get_custom_field( '_genesis_description' ) ? $this->get_custom_field( '_genesis_description' ) : '';

					$nofollow = $this->get_custom_field( '_genesis_nofollow' );
					$noarchive = $this->get_custom_field( '_genesis_noarchive' );

					if ( $is_front_page ) {
						$title_custom_field = $this->get_option( 'homepage_title' ) ? true : $title_custom_field;

						$description = $this->get_option( 'homepage_description' ) ? $this->get_option( 'homepage_description' ) : $description;

						$nofollow = $this->get_option( 'homepage_nofollow' ) ? $this->get_option( 'homepage_nofollow' ) : $nofollow;
						$noarchive = $this->get_option( 'homepage_noarchive' ) ? $this->get_option( 'homepage_noarchive' ) : $noarchive;
					}

					//* Fetch the title normally.
					if ( $title_custom_field && ! $is_front_page ) {
						//* Let's try not to fix the bloated function for now.
						$blogname = $this->get_blogname();

						$title = $this->title_from_custom_field();

						/**
						 * Separator doesn't matter. Since html_entity_decode is used.
						 * Order doesn't matter either. Since it's just used for length calculation.
						 *
						 * @since 2.3.4
						 */
						$title = $blogname . " | " . $title;
					} else if ( $is_front_page ) {
						$title = $this->title( '', '', '', array( 'page_on_front' => true ) );
					} else {
						//* Fetch the title normally.
						$title = $this->title();
					}
				} else {
					$title_custom_field = isset( $term->admeta['doctitle'] ) && $term->admeta['doctitle'] ? true : false;

					//* Fetch the title normally.
					if ( $title_custom_field ) {
						//* Let's try not to fix the bloated function for now.
						$blogname = $this->get_blogname();

						/**
						 * Separator doesn't matter. Since html_entity_decode is used.
						 * Order doesn't matter either. Since it's just used for length calculation.
						 *
						 * @since 2.3.4
						 */
						$title = $blogname . " | " . $term->admeta['doctitle'];
					} else {
						$title = $this->title( '', '', '', array( 'term_id' => $post_id, 'taxonomy' => $type ) );
					}

					$description = isset( $term->admeta['description'] ) ? $term->admeta['description'] : '';

					$nofollow = isset( $term->admeta['nofollow'] ) ? $term->admeta['nofollow'] : '';
					$noarchive = isset( $term->admeta['noarchive'] ) ? $term->admeta['noarchive'] : '';

					//* Genesis data fetch
					if ( ! $flag && isset( $term->meta ) ) {
						if ( empty( $title ) && isset( $term->meta['doctitle'] ) )
							$title = $term->meta['doctitle'];

						if ( empty( $description ) && isset( $term->meta['description'] ) )
							$description = $term->meta['description'];

						if ( empty( $nofollow ) && isset( $term->meta['nofollow'] ) )
							$nofollow = $term->meta['nofollow'];

						if ( empty( $noarchive ) && isset( $term->meta['noarchive'] ) )
							$noarchive = $term->meta['noarchive'];
					}
				}

				/**
				 * Convert to what Google outputs.
				 *
				 * This will convert e.g. &raquo; to a single length character.
				 * @since 2.3.4
				 */
				$title = trim( html_entity_decode( $title ) );
				$desc_len_parsed = trim( html_entity_decode( $description ) );

				//* Calculate length.
				$tit_len = mb_strlen( $title );
				$desc_len = mb_strlen( $desc_len_parsed );

				$description_custom_field = true;

				//* Generate description if custom isn't found.
				if ( 0 == $desc_len ) {
					if ( ! $is_term ) {
						$description_args = array( 'id' => $post_id, 'get_custom_field' => false );
						$description = $this->generate_description( '', $description_args );
					} else {
						$description_args = array( 'id' => $post_id, 'taxonomy' => $type, 'get_custom_field' => false );
						$description = $this->generate_description( '', $description_args );
					}

					//* Convert to what Google outputs. @since 2.3.4
					$desc_len_parsed = trim( html_entity_decode( $description ) );
					$desc_len = mb_strlen( $desc_len_parsed );

					$description_custom_field = false;
				}

				//* Count the words.
				$desc_words = str_word_count( strtolower( $description ), 2 );

				if ( is_array( $desc_words ) ) {
					//* We're going to fetch word based on key, and the last element (as first)
					$word_keys = array_flip( array_reverse( $desc_words, true ) );

					$desc_word_count = array_count_values( $desc_words );

					//* Parse word counting.
					if ( is_array( $desc_word_count ) ) {
						foreach ( $desc_word_count as $desc_word => $desc_word_count ) {
							if ( $desc_word_count >= 3 ) {
								$position = $word_keys[$desc_word];

								$word_len = mb_strlen( $desc_word );
								$first_word_original = mb_substr( $description, $position, $word_len );

								//* Found words that are used too frequently.
								$desc_too_many[] = array( $first_word_original => $desc_word_count );
							}
						}
					}
				}

				// Add starting space
				$generated = ' ' . _x( 'G', 'Generated', 'autodescription');
				// Add starting break. Yes it's being put inside an HTML attribute. Yes it's allowed. No this can't be put into the title attribute.
				$generated_notice = '<br />' . __( 'Generated: Automatically generated.', 'autodescription');

				$gen_t = ! $title_custom_field ? $generated : '';
				$gen_d = ! $description_custom_field ? $generated : '';

				$gen_t_notice = ! $title_custom_field ? $generated_notice : '';
				$gen_d_notice = ! $description_custom_field ? $generated_notice : '';

				if ( $tit_len < 25 ) {
					$titlen_notice = $title_i18n . ' ' . __( 'far too short.', 'autodescription' );
					$titlen_class = $bad;
				} else if ( $tit_len < 42 ) {
					$titlen_notice = $title_i18n . ' ' . __( 'too short.', 'autodescription' );
					$titlen_class = $okay;
				} else if ( $tit_len > 55 && $tit_len < 75 ) {
					$titlen_notice = $title_i18n . ' ' . __( 'too long.', 'autodescription' );
					$titlen_class = $okay;
				} else if ( $tit_len >= 75 ) {
					$titlen_notice = $title_i18n . ' ' . __( 'far too long.', 'autodescription' );
					$titlen_class = $bad;
				} else {
					$titlen_notice = $title_i18n . ' ' . __( 'good.', 'autodescription' );
					$titlen_class = $good;
				}

				$desclen_notice = $description_i18n;

				if ( ! empty( $desc_too_many ) && is_array( $desc_too_many ) ) {

					$words_count = count( $desc_too_many );
					$desclen_class = $words_count <= 1 ? $okay : $bad;

					foreach ( $desc_too_many as $key => $desc_array ) {
						foreach ( $desc_array as $desc_value => $desc_count ) {
							$desclen_notice .= ' ';

							/**
							 * Don't ucfirst abbrivations.
							 * @since 2.4.1
							 */
							$desc_value = ctype_upper( $desc_value ) ? $desc_value : ucfirst( $desc_value );

							$desclen_notice .= sprintf( __( '%s is used %d times.', 'autodescription' ), '<span>' . $desc_value . '</span>', $desc_count );
							$desclen_notice .= '<br />'; // Yes, <br /> is used inside an attribute. Allowed.
						}
					}
				}

				if ( $desc_len < 100 ) {
					$desclen_notice .= ' ' . __( 'Length is far too short.', 'autodescription' );
					$desclen_class = $bad;
				} else if ( $desc_len < 145 ) {
					$desclen_notice .= ' ' . __( 'Length is too short.', 'autodescription' );

					// Don't make it okay if it's already bad.
					$desclen_class = $desclen_class == $bad ? $desclen_class : $okay;
				} else if ( $desc_len > 155 && $desc_len < 175 ) {
					$desclen_notice .= ' ' . __( 'Length is too long.', 'autodescription' );

					// Don't make it okay if it's already bad.
					$desclen_class = $desclen_class == $bad ? $desclen_class : $okay;
				} else if ( $desc_len >= 175 ) {
					$desclen_notice .= ' ' . __( 'Length is far too long.', 'autodescription' );
					$desclen_class = $bad;
				} else {
					$desclen_notice .= ' ' . __( 'Length is good.', 'autodescription' );

					// Don't make it good if it's already bad or okay.
					$desclen_class = $desclen_class == $bad || $desclen_class == $okay ? $desclen_class : $good;
				}

				$ind_notice = $index_i18n . ' ' . sprintf( __( "%s is being indexed.", 'autodescription' ), $post );
				$ind_class = $good;

				/**
				 * Get noindex site option
				 *
				 * @since 2.2.2
				 */
				if ( $this->get_option( 'site_noindex' ) ) {
					$ind_notice .= '<br />' . sprintf( __( "But you've disabled indexing for the whole site.", 'autodescription' ), $post );
					$ind_class = $unknown;
				}

				if ( ! get_option( 'blog_public' ) ) {
					$ind_notice .= '<br />' . sprintf( __( "But the blog isn't set to public. This means WordPress disencourages indexing.", 'autodescription' ), $post );
					$ind_class = $unknown;
				}

				/**
				 * Check if archive is empty, and therefore has set noindex for those.
				 *
				 * @since 2.2.8
				 */
				if ( $is_term && isset( $term->count ) && $term->count === (int) 0 ) {
					$ind_notice .= '<br />' . sprintf( __( "But there are no posts in this %s. Therefore indexing has been disabled.", 'autodescription' ), $post );
					$ind_class = $unknown;
				}

				if ( empty( $nofollow ) ) {
					$fol_notice = $follow_i18n . ' ' . sprintf( __( '%s links are being followed.', 'autodescription' ), $post );
					$fol_class = $good;

					/**
					 * Get nofolow site option
					 *
					 * @since 2.2.2
					 */
					if ( $this->get_option( 'site_nofollow' ) ) {
						$fol_notice .= '<br />' . __( "But you've disabled following of links for the whole site.", 'autodescription' );
						$fol_class = $unknown;
					}
				} else {
					$fol_notice = $follow_i18n . ' ' . sprintf( __( "%s links aren't being followed.", 'autodescription' ), $post );
					$fol_class = $unknown;

					if ( ! get_option( 'blog_public' ) ) {
						$fol_notice .= '<br />' . __( "But the blog isn't set to public. This means WordPress allows the links to be followed regardless.", 'autodescription' );
					}
				}

				if ( empty( $noarchive ) ) {
					$arc_notice = $archive_i18n . ' ' . sprintf( __( 'Search Engine are allowed to archive this %s.', 'autodescription' ), $post_low );
					$arc_class = $good;

					/**
					 * Get noarchive site option
					 *
					 * @since 2.2.2
					 */
					if ( $this->get_option( 'site_noarchive' ) ) {
						$arc_notice .= '<br />' . __( "But you've disabled archiving for the whole site.", 'autodescription' );
						$arc_class = $unknown;
					}

				} else {
					$arc_notice = $archive_i18n . ' ' . sprintf( __( "Search Engine aren't allowed to archive this %s.", 'autodescription' ), $post_low );
					$arc_class = $unknown;

					if ( ! get_option( 'blog_public' ) ) {
						$arc_notice .= '<br />' . __( "But the blog isn't set to public. This means WordPress allows the blog to be archived regardless.", 'autodescription' );
					}
				}

				$red_notice = $redirect_i18n . ' ' . sprintf( __( "%s isn't being redirected.", 'autodescription' ), $post );
				$red_class = $good;

				if ( ! empty( $titlen_notice ) )
					$title_notice		= '<span class="ad-sec-wrap ad-25">'
										. '<a href="#" onclick="return false;" class="' . $titlen_class . '"  data-desc="' . $titlen_notice . $gen_t_notice . '">' . _x( 'T', 'Title', 'autodescription') . $gen_t . '</a>'
										. '<span class="screen-reader-text">' . $titlen_notice . $gen_t_notice . '</span>'
										. '</span>'
										;

				if ( ! empty( $desclen_notice ) )
					$description_notice	= '<span class="ad-sec-wrap ad-25">'
										. '<a href="#" onclick="return false;" class="' . $desclen_class . '" data-desc="' . $desclen_notice . $gen_d_notice . '">' . _x( 'D', 'Description', 'autodescription') . $gen_d . '</a>'
										. '<span class="screen-reader-text">' . $desclen_notice . $gen_d_notice . '</span>'
										. '</span>'
										;

					$index_notice		= '<span class="ad-sec-wrap ' . $ad_125 . '">'
										. '<a href="#" onclick="return false;" class="' . $ind_class . '" data-desc="' . $ind_notice . '">' . _x( 'I', 'no-Index', 'autodescription') . '</a>'
										. '<span class="screen-reader-text">' . $ind_notice . '</span>'
										. '</span>'
										;

				if ( ! empty( $fol_notice ) )
					$follow_notice		= '<span class="ad-sec-wrap ' . $ad_125 . '">'
					 					. '<a href="#" onclick="return false;" class="' . $fol_class . '" data-desc="' . $fol_notice . '">' . _x( 'F', 'no-Follow', 'autodescription') . '</a>'
										. '<span class="screen-reader-text">' . $fol_notice . '</span>'
										. '</span>'
										;


				if ( ! empty( $arc_notice ) )
					$archive_notice		= '<span class="ad-sec-wrap ' . $ad_125 . '">'
										. '<a href="#" onclick="return false;" class="' . $arc_class . '" data-desc="' . $arc_notice . '">' . _x( 'A', 'no-Archive', 'autodescription') . '</a>'
										. '<span class="screen-reader-text">' . $arc_notice . '</span>'
										. '</span>'
										;

				// No redirection on taxonomies (yet).
				if ( ! $is_term ) {
					$redirect_notice	= '<span class="ad-sec-wrap ' . $ad_125 . '">'
										. '<a href="#" onclick="return false;" class="' . $red_class . '" data-desc="' . $red_notice . '">' . _x( 'R', 'Redirect', 'autodescription') . '</a>'
										. '<span class="screen-reader-text">' . $red_notice . '</span>'
										. '</span>'
										;
				} else {
					$redirect_notice 	= '';
				}

				$content = sprintf( '<span class="ad-seo clearfix ' . $ad_100 . $square . '"><span class="ad-bar-wrap">%s %s %s %s %s %s</span></span>', $title_notice, $description_notice, $index_notice, $follow_notice, $archive_notice, $redirect_notice );

			// Redirect and noindex found, why bother showing SEO.
			} else if ( ! empty( $redirect ) && ! empty( $noindex ) ) {

				$red_notice = $redirect_i18n . ' ' . sprintf( __( "%s is being redirected. This means no SEO values have to be set.", 'autodescription' ), $post );
				$red_class = $unknown;

				$redirect_notice	= '<span class="ad-sec-wrap ad-50">'
									. '<a href="#" onclick="return false;" class="' . $red_class . '" data-desc="' . $red_notice . '">' . _x( 'R', 'Redirect', 'autodescription') . '</a>'
									. '<span class="screen-reader-text">' . $red_notice . '</span>'
									. '</span>'
									;

				$noi_notice = $index_i18n . ' ' . sprintf( __( "%s is not being indexed. This means no SEO values have to be set.", 'autodescription' ), $post );
				$noi_class = $unknown;

				$noindex_notice		= '<span class="ad-sec-wrap ad-50">'
									. '<a href="#" onclick="return false;" class="' . $noi_class . '" data-desc="' . $noi_notice . '">' . _x( 'I', 'no-Index', 'autodescription') . '</a>'
									. '<span class="screen-reader-text">' . $noi_notice . '</span>'
									. '</span>'
									;

				$content = sprintf( '<span class="ad-seo clearfix ' . $ad_100 . $square . '"><span class="ad-bar-wrap">%s %s</span></span>', $redirect_notice, $noindex_notice );

			} else if ( ! empty( $redirect ) && empty( $noindex ) ) {
				//* Redirect found, why bother showing SEO info?

				$red_notice = $redirect_i18n . ' ' . sprintf( __( "%s is being redirected. This means no SEO values have to be set.", 'autodescription' ), $post );
				$red_class = $unknown;

				$redirect_notice	= '<span class="ad-sec-wrap ad-100">'
									. '<a href="#" onclick="return false;" class="' . $red_class . '" data-desc="' . $red_notice . '">' . _x( 'R', 'Redirect', 'autodescription') . '</a>'
									. '<span class="screen-reader-text">' . $red_notice . '</span>'
									. '</span>'
									;

				$content = sprintf( '<span class="ad-seo clearfix ' . $ad_100 . $square . '"><span class="ad-bar-wrap">%s</span></span>', $redirect_notice );

			// Noindex found, why bother showing SEO info?
			} else if ( empty( $redirect ) && ! empty( $noindex ) ) {

				$noi_notice = $index_i18n . ' ' . sprintf( __( "%s is not being indexed. This means no SEO values have to be set.", 'autodescription' ), $post );
				$noi_class = $unknown;

				$noindex_notice	= '<span class="ad-sec-wrap ad-100">'
								. '<a href="#" onclick="return false;" class="' . $noi_class . '" data-desc="' . $noi_notice . '">' . _x( 'I', 'no-Index', 'autodescription') . '</a>'
								. '<span class="screen-reader-text">' . $noi_notice . '</span>'
								. '</span>'
								;

				$content = sprintf( '<span class="ad-seo clearfix ' . $ad_100 . $square . '"><span class="ad-bar-wrap">%s</span></span>', $noindex_notice );
			}

		} else {
			$content = '<span>' . __( 'Failed to fetch post ID.', 'autodescription' ) . '</span>';
		}

		return $content;
	}

}
