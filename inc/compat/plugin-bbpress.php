<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\bbPress
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and \tsf()->_verify_include_secret( $_secret ) or die;

/**
 * Override wp_title's bbPress title with the one generated by The SEO Framework.
 *
 * @since 2.3.5
 */
\add_filter( 'bbp_title', [ $this, 'get_document_title' ], 99, 3 );

\add_filter( 'the_seo_framework_title_from_generation', __NAMESPACE__ . '\\_bbpress_filter_title', 10, 2 );
/**
 * Override's The SEO Framework's auto-generated title with bbPress's on bbPress queries.
 *
 * Still waiting for an API to get the proper title from bbPress (e.g. get raw 'Forum edit: %s'), instead of them formulating
 * a complete title for us.
 *
 * We're going to trust Automattic/bbPress that they'll deprecate all functions called here, instead of removing them,
 * might they desire to add/improve new functionality.
 *
 * @since 4.0.6
 * @source bbp_title()
 * @NOTE Do NOT call `bbp_title()` or apply filter `bbptitle` here, it'll cause an infinite loop.
 *
 * @param string     $title The title.
 * @param array|null $args  The query arguments. Contains 'id' and 'taxonomy'.
 *                          Is null when query is autodetermined.
 * @return string The corrected bbPress title on bbPress pages.
 */
function _bbpress_filter_title( $title, $args ) {

	if ( null !== $args || ! \is_bbpress() ) return $title;

	// phpcs:disable, Squiz.Commenting.BlockComment, Generic.WhiteSpace.ScopeIndent, WordPress.WP.I18n, Generic.Formatting.MultipleStatementAlignment -- Not my code.

	// Title array
	$new_title = [];

	/** Archives **************************************************************/

	// Forum Archive
	if ( \bbp_is_forum_archive() ) {
		$new_title['text'] = \bbp_get_forum_archive_title();

	// Topic Archive
	} elseif ( \bbp_is_topic_archive() ) {
		$new_title['text'] = \bbp_get_topic_archive_title();

	/** Edit ******************************************************************/

	// Forum edit page
	} elseif ( \bbp_is_forum_edit() ) {
		$new_title['text']   = \bbp_get_forum_title();
		$new_title['format'] = \esc_attr__( 'Forum Edit: %s', 'bbpress' );

	// Topic edit page
	} elseif ( \bbp_is_topic_edit() ) {
		$new_title['text']   = \bbp_get_topic_title();
		$new_title['format'] = \esc_attr__( 'Topic Edit: %s', 'bbpress' );

	// Reply edit page
	} elseif ( \bbp_is_reply_edit() ) {
		$new_title['text']   = \bbp_get_reply_title();
		$new_title['format'] = \esc_attr__( 'Reply Edit: %s', 'bbpress' );

	// Topic tag edit page
	} elseif ( \bbp_is_topic_tag_edit() ) {
		$new_title['text']   = \bbp_get_topic_tag_name();
		$new_title['format'] = \esc_attr__( 'Topic Tag Edit: %s', 'bbpress' );

	/** Singles ***************************************************************/

	// Forum page
	} elseif ( \bbp_is_single_forum() ) {
		$new_title['text']   = \bbp_get_forum_title();
		$new_title['format'] = \esc_attr__( 'Forum: %s', 'bbpress' );

	// Topic page
	} elseif ( \bbp_is_single_topic() ) {
		$new_title['text']   = \bbp_get_topic_title();
		$new_title['format'] = \esc_attr__( 'Topic: %s', 'bbpress' );

	// Replies
	} elseif ( \bbp_is_single_reply() ) {
		$new_title['text']   = \bbp_get_reply_title();

	// Topic tag page
	} elseif ( \bbp_is_topic_tag() || \get_query_var( 'bbp_topic_tag' ) ) {
		$new_title['text']   = \bbp_get_topic_tag_name();
		$new_title['format'] = \esc_attr__( 'Topic Tag: %s', 'bbpress' );

	/** Users *****************************************************************/

	// Profile page
	} elseif ( \bbp_is_single_user() ) {

		// Is user viewing their own profile?
		$is_user_home = \bbp_is_user_home();

		// User topics created
		if ( \bbp_is_single_user_topics() ) {
			if ( true === $is_user_home ) {
				$new_title['text'] = \esc_attr__( 'Your Topics', 'bbpress' );
			} else {
				$new_title['text'] = \get_userdata( \bbp_get_user_id() )->display_name;
				/* translators: user's display name */
				$new_title['format'] = \esc_attr__( "%s's Topics", 'bbpress' );
			}

		// User replies created
		} elseif ( \bbp_is_single_user_replies() ) {
			if ( true === $is_user_home ) {
				$new_title['text'] = \esc_attr__( 'Your Replies', 'bbpress' );
			} else {
				$new_title['text'] = \get_userdata( \bbp_get_user_id() )->display_name;
				/* translators: user's display name */
				$new_title['format'] = \esc_attr__( "%s's Replies", 'bbpress' );
			}

		// User favorites
		} elseif ( \bbp_is_favorites() ) {
			if ( true === $is_user_home ) {
				$new_title['text'] = \esc_attr__( 'Your Favorites', 'bbpress' );
			} else {
				$new_title['text'] = \get_userdata( \bbp_get_user_id() )->display_name;
				/* translators: user's display name */
				$new_title['format'] = \esc_attr__( "%s's Favorites", 'bbpress' );
			}

		// User subscriptions
		} elseif ( \bbp_is_subscriptions() ) {
			if ( true === $is_user_home ) {
				$new_title['text'] = \esc_attr__( 'Your Subscriptions', 'bbpress' );
			} else {
				$new_title['text'] = \get_userdata( \bbp_get_user_id() )->display_name;
				/* translators: user's display name */
				$new_title['format'] = \esc_attr__( "%s's Subscriptions", 'bbpress' );
			}

		// User "home"
		} else {
			if ( true === $is_user_home ) {
				$new_title['text'] = \esc_attr__( 'Your Profile', 'bbpress' );
			} else {
				$new_title['text'] = \get_userdata( \bbp_get_user_id() )->display_name;
				/* translators: user's display name */
				$new_title['format'] = \esc_attr__( "%s's Profile", 'bbpress' );
			}
		}

	// Profile edit page
	} elseif ( \bbp_is_single_user_edit() ) {

		// Current user
		if ( \bbp_is_user_home_edit() ) {
			$new_title['text']   = \esc_attr__( 'Edit Your Profile', 'bbpress' );

		// Other user
		} else {
			$new_title['text']   = \get_userdata( \bbp_get_user_id() )->display_name;
			$new_title['format'] = \esc_attr__( "Edit %s's Profile", 'bbpress' );
		}

	/** Views *****************************************************************/

	// Views
	} elseif ( \bbp_is_single_view() ) {
		$new_title['text']   = \bbp_get_view_title();
		$new_title['format'] = \esc_attr__( 'View: %s', 'bbpress' );

	/** Search ****************************************************************/

	// Search
	} elseif ( \bbp_is_search() ) {
		$new_title['text'] = \bbp_get_search_title();
	}

	// This filter is deprecated. Use 'bbp_before_title_parse_args' instead.
	$new_title = \apply_filters( 'bbp_raw_title_array', $new_title );

	// Set title array defaults
	$new_title = \bbp_parse_args(
		$new_title,
		[
			'text'   => $title,
			'format' => '%s',
		],
		'title'
	);

	// Get the formatted raw title
	$new_title = sprintf( $new_title['format'], $new_title['text'] );

	// Filter the raw title.
	$new_title = \apply_filters( 'bbp_raw_title', $new_title, $sep = '&raquo;', $seplocation = '' ); // phpcs:ignore,VariableAnalysis -- readability.

	// Compare new title with original title
	if ( $new_title === $title ) {
		return $title;
	}

	// phpcs:enable, Squiz.Commenting.BlockComment, Generic.WhiteSpace.ScopeIndent, WordPress.WP.I18n, Generic.Formatting.MultipleStatementAlignment -- Not my code.

	return $new_title;
}

\add_filter( 'the_seo_framework_seo_column_keys_order', __NAMESPACE__ . '\\_bbpress_filter_order_keys' );
/**
 * Filters the order keys for The SEO Bar.
 *
 * @since 2.8.0
 * @access private
 *
 * @param array $current_keys The current column keys TSF looks for.
 * @return array Expanded keyset.
 */
function _bbpress_filter_order_keys( $current_keys = [] ) {

	$new_keys = [
		'bbp_topic_freshness',
		'bbp_forum_freshness',
		'bbp_reply_created',
	];

	return array_merge( $current_keys, $new_keys );
}

\add_filter( 'the_seo_framework_title_from_generation', __NAMESPACE__ . '\\_bbpress_filter_pre_title', 10, 2 );
/**
 * Fixes bbPress tag titles.
 *
 * @since 2.9.0
 * @since 3.1.0 1. Updated to support new title generation.
 *              2. Now no longer fixes the title when `is_tax()` is true. Because,
 *                 this method is no longer necessary when bbPress fixes this issue.
 *                 This should be fixed as of bbPress 2.6. Which seemed to be released internally August 6th, 2018.
 * @since 4.0.0 No longer overrules external queries.
 * @access private
 *
 * @param string     $title The filter title.
 * @param array|null $args  The query arguments. Contains 'id' and 'taxonomy'.
 *                          Is null when query is autodetermined.
 * @return string $title The bbPress title.
 */
function _bbpress_filter_pre_title( $title = '', $args = null ) {

	if ( null === $args && \is_bbpress() ) {
		if ( \bbp_is_topic_tag() ) {
			$term  = \get_queried_object();
			$title = $term->name ?? \tsf()->get_static_untitled_title();
		}
	}

	return $title;
}

\add_filter( 'the_seo_framework_fetched_description_excerpt', __NAMESPACE__ . '\\_bbpress_filter_excerpt_generation', 10, 3 );
/**
 * Fixes bbPress excerpts.
 *
 * Now, bbPress has a hard time maintaining WordPress's query after the original query.
 * This should be fixed with bbPress 3.0.
 * This function fixes the Excerpt part.
 *
 * @since 2.9.0
 * @since 3.0.4 Default value for $max_char_length has been increased from 155 to 300.
 * @since 3.1.0 Now no longer fixes the description when `is_tax()` is true.
 *              @see `_bbpress_filter_pre_title()` for explanation.
 * @since 4.0.0 No longer overrules external queries.
 * @access private
 *
 * @param string     $excerpt The excerpt to use.
 * @param int        $page_id Deprecated.
 * @param array|null $args The query arguments. Contains 'id', 'taxonomy', and 'pta'.
 *                         Is null when query is autodetermined.
 * @return string The excerpt.
 */
function _bbpress_filter_excerpt_generation( $excerpt = '', $page_id = 0, $args = null ) {

	if ( null === $args && \is_bbpress() ) {
		if ( \bbp_is_topic_tag() ) {
			// Always overwrite, even when none is found.
			$excerpt = \tsf()->s_description_raw( \get_queried_object()->description ?? '' );
		}
	}

	return $excerpt;
}

\add_filter( 'the_seo_framework_custom_field_description', __NAMESPACE__ . '\\_bbpress_filter_custom_field_description', 10, 2 );
/**
 * Fixes bbPress custom Description for social meta.
 *
 * Now, bbPress has a hard time maintaining WordPress's query after the original query.
 * This should be fixed with bbPress 3.0.
 * This function fixes the Custom Description part.
 *
 * @since 2.9.0
 * @since 4.0.0 No longer overrules external queries.
 * @access private
 *
 * @param string     $desc The custom-field description.
 * @param array|null $args The query arguments. Contains 'id', 'taxonomy', and 'pta'.
 *                         Is null when query is autodetermined.
 * @return string The custom description.
 */
function _bbpress_filter_custom_field_description( $desc = '', $args = null ) {

	if ( null === $args && \is_bbpress() && \bbp_is_topic_tag() ) {
		// Ovewrite $desc.
		return \tsf()->get_term_meta( \get_queried_object_id() )['description'] ?? '';
	}

	return $desc;
}

\add_filter( 'the_seo_framework_do_adjust_archive_query', __NAMESPACE__ . '\\_bbpress_filter_do_adjust_query', 10, 2 );
/**
 * Fixes bbPress exclusion of first reply.
 *
 * Now, bbPress has a hard time maintaining WordPress's query after the original query.
 * This should be fixed with bbPress 3.0.
 * This function fixes the query alteration part.
 *
 * @since 3.0.3
 * @access private
 * @link <https://bbpress.trac.wordpress.org/ticket/2607> (regression)
 *
 * @param bool      $do       Whether to adjust the query.
 * @param \WP_Query $wp_query The query.
 * @return bool
 */
function _bbpress_filter_do_adjust_query( $do, $wp_query ) {

	if ( \is_bbpress() && isset( $wp_query->query['post_type'] ) ) {
		if ( \in_array( 'reply', (array) $wp_query->query['post_type'], true ) ) {
			$do = false;
		}
	}

	return $do;
}

\add_filter( 'the_seo_framework_robots_meta_array', __NAMESPACE__ . '\\_bbpress_filter_robots', 10, 2 );
/**
 * Filters bbPress hidden forums.
 *
 * This should actually only consider non-loop queries, for hidden forums can't be reached anyway.
 *
 * @since 4.2.8
 * @access private
 *
 * @param array      $meta The parsed robots meta. {
 *    string 'noindex', ideally be empty or 'noindex'
 *    string 'nofollow', ideally be empty or 'nofollow'
 *    string 'noarchive', ideally be empty or 'noarchive'
 *    string 'max_snippet', ideally be empty or 'max-snippet:<R>=-1>'
 *    string 'max_image_preview', ideally be empty or 'max-image-preview:<none|standard|large>'
 *    string 'max_video_preview', ideally be empty or 'max-video-preview:<R>=-1>'
 * }
 * @param array|null $args    The query arguments. Contains 'id' and 'taxonomy'.
 *                            Is null when query is autodetermined.
 * @return array
 */
function _bbpress_filter_robots( $meta, $args ) {

	if ( null === $args ) {
		// Front-end
		if ( \bbp_is_single_forum() ) {
			$forum_id = \tsf()->get_the_real_ID();
		} elseif ( \bbp_is_single_topic() ) {
			$forum_id = \get_post_meta( \tsf()->get_the_real_ID(), '_bbp_forum_id', true );
		} elseif ( \bbp_is_single_reply() ) {
			$forum_id = \get_post_meta( \tsf()->get_the_real_ID(), '_bbp_forum_id', true );
		}
	} else {
		// Custom query, back-end or sitemap.
		if ( empty( $args['pta'] ) && empty( $args['taxonomy'] ) ) {
			switch ( \get_post_type( $args['id'] ) ) {
				case \bbp_get_forum_post_type():
					$forum_id = $args['id'];
					break;
				case \bbp_get_topic_post_type():
				case \bbp_get_reply_post_type():
					$forum_id = \get_post_meta( $args['id'], '_bbp_forum_id', true );
					break;
			}
		}
	}

	if ( ! empty( $forum_id ) && ! \bbp_is_forum_public( $forum_id ) )
		$meta['noindex'] = 'noindex';

	return $meta;
}

\add_action( 'the_seo_framework_seo_bar', __NAMESPACE__ . '\\_assert_bbpress_noindex_defaults_seo_bar' );
/**
 * Appends noindex default checks to the noindex item of the SEO Bar for pages.
 *
 * @since 4.2.8
 * @access private
 *
 * @param string $interpreter The interpreter class name.
 */
function _assert_bbpress_noindex_defaults_seo_bar( $interpreter ) {

	if ( $interpreter::$query['taxonomy'] ) return;

	$items = $interpreter::collect_seo_bar_items();

	// Don't do anything if there's a blocking redirect.
	if ( ! empty( $items['redirect']['meta']['blocking'] ) ) return;

	switch ( $interpreter::$query['post_type'] ) {
		case \bbp_get_forum_post_type():
			$forum_id = $interpreter::$query['id'];
			break;
		case \bbp_get_topic_post_type():
		case \bbp_get_reply_post_type():
			$forum_id = \get_post_meta( $interpreter::$query['id'], '_bbp_forum_id', true );
			break;
	}

	if ( empty( $forum_id ) || \bbp_is_forum_public( $forum_id ) ) return;

	$index_item           = &$interpreter::edit_seo_bar_item( 'indexing' );
	$index_item['status'] =
		0 !== \tsf()->s_qubit(
			\The_SEO_Framework\Builders\SEOBar\Page::get_instance()->get_query_cache()['meta']['_genesis_noindex']
		)
			? $interpreter::STATE_OKAY
			: $interpreter::STATE_UNKNOWN;

	if ( 'forum' === $interpreter::$query['post_type'] ) {
		$index_item['assess']['notpublic'] = \__( 'This is not a public forum.', 'autodescription' );
	} else {
		$index_item['assess']['notpublic'] = \__( 'This page is not part of a public forum.', 'autodescription' );
	}

	// No amount of overriding will fix this -- the forum/topic/reply is publicly unreachable.
	unset( $index_item['override'] );
}
