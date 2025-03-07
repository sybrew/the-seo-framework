<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\bbPress
 * @subpackage The_SEO_Framework\Compatibility
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function The_SEO_Framework\normalize_generation_args;

use The_SEO_Framework\{
	Data\Filter\Sanitize,
	Helper\Query,
};

/**
 * Override wp_title's bbPress title with the one generated by The SEO Framework.
 *
 * @since 2.3.5
 */
\add_filter( 'bbp_title', [ Front\Title::class, 'set_document_title' ], 99 );

\add_filter( 'the_seo_framework_title_from_generation', __NAMESPACE__ . '\\_bbpress_filter_title', 10, 2 );
\add_filter( 'the_seo_framework_seo_column_keys_order', __NAMESPACE__ . '\\_bbpress_filter_order_keys' );
\add_filter( 'the_seo_framework_do_adjust_archive_query', __NAMESPACE__ . '\\_bbpress_filter_do_adjust_query', 10, 2 );
\add_filter( 'the_seo_framework_robots_meta_array', __NAMESPACE__ . '\\_bbpress_filter_robots', 10, 2 );
\add_action( 'the_seo_framework_seo_bar', __NAMESPACE__ . '\\_assert_bbpress_noindex_defaults_seo_bar', 10, 2 );

/**
 * Override's The SEO Framework's auto-generated title with bbPress's on bbPress queries.
 *
 * Still waiting for an API to get the proper title from bbPress (e.g. get raw 'Forum edit: %s'), instead of them formulating
 * a complete title for us.
 * We already notified them about this, but perhaps we should create a ticket on bbPress's Trac and
 * request feature "bbp_get_title()" or something.
 *
 * We're going to trust Automattic/bbPress that they'll deprecate all functions called here, instead of removing them,
 * might they desire to add/improve new functionality.
 *
 * @hook the_seo_framework_title_from_generation 10
 * @since 4.0.6
 * @source bbp_title()
 * @NOTE Do NOT call `bbp_title()` or apply filter `bbptitle` here, it'll cause an infinite loop.
 *
 * @param string     $title The title.
 * @param array|null $args  The query arguments. Contains 'id', 'tax', 'pta', and 'uid'.
 *                          Is null when the query is auto-determined.
 * @return string The corrected bbPress title on bbPress pages.
 */
function _bbpress_filter_title( $title, $args ) {

	if (
		   isset( $args )
		|| ! \function_exists( 'is_bbpress' )
		|| ! \is_bbpress()
	) return $title;

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
		} elseif ( true === $is_user_home ) {
			$new_title['text'] = \esc_attr__( 'Your Profile', 'bbpress' );
		} else {
			$new_title['text'] = \get_userdata( \bbp_get_user_id() )->display_name;
			/* translators: user's display name */
			$new_title['format'] = \esc_attr__( "%s's Profile", 'bbpress' );
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
		'title',
	);

	// Get the formatted raw title
	$new_title = \sprintf( $new_title['format'], $new_title['text'] );

	// Filter the raw title.
	$new_title = \apply_filters( 'bbp_raw_title', $new_title, $sep = '&raquo;', $seplocation = '' ); // phpcs:ignore,VariableAnalysis -- readability.

	// Compare new title with original title
	if ( $new_title === $title ) {
		return $title;
	}

	// phpcs:enable, Squiz.Commenting.BlockComment, Generic.WhiteSpace.ScopeIndent, WordPress.WP.I18n, Generic.Formatting.MultipleStatementAlignment -- Not my code.

	return $new_title;
}

/**
 * Filters the order keys for The SEO Bar.
 *
 * @hook the_seo_framework_seo_column_keys_order 10
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

/**
 * Fixes bbPress exclusion of first reply, when the topic is set to be excluded everywhere else.
 *
 * @hook the_seo_framework_do_adjust_archive_query 10
 * @since 3.0.3
 * @access private
 * @link <https://bbpress.trac.wordpress.org/ticket/2607> (regression)
 *
 * @param bool      $adjust   Whether to adjust the query.
 * @param \WP_Query $wp_query The query.
 * @return bool
 */
function _bbpress_filter_do_adjust_query( $adjust, $wp_query ) {

	if (
		   isset( $wp_query->query['post_type'] )
		&& \in_array( 'reply', (array) $wp_query->query['post_type'], true )
		&& \function_exists( 'is_bbpress' )
		&& \is_bbpress()
	) {
		$adjust = false;
	}

	return $adjust;
}

/**
 * Filters bbPress hidden forums.
 *
 * This should actually only consider non-loop queries, for hidden forums can't be reached anyway.
 *
 * @hook the_seo_framework_robots_meta_array 10
 * @since 4.2.8
 * @access private
 *
 * @param array      $meta    {
 *     The parsed robots meta.
 *
 *     @type string $noindex           Ideally be empty or 'noindex'
 *     @type string $nofollow          Ideally be empty or 'nofollow'
 *     @type string $noarchive         Ideally be empty or 'noarchive'
 *     @type string $max_snippet       Ideally be empty or 'max-snippet:<R>=-1>'
 *     @type string $max_image_preview Ideally be empty or 'max-image-preview:<none|standard|large>'
 *     @type string $max_video_preview Ideally be empty or 'max-video-preview:<R>=-1>'
 * }
 * @param array|null $args The query arguments. Contains 'id', 'tax', 'pta', and 'uid'.
 *                         Is null when the query is auto-determined.
 * @return array
 */
function _bbpress_filter_robots( $meta, $args ) {

	if ( isset( $args ) ) {
		if ( ! Helper\Compatibility::can_i_use( [
			'functions' => [
				'bbp_get_forum_post_type',
				'bbp_get_topic_post_type',
				'bbp_get_reply_post_type',
			],
		] ) ) return $meta;

		normalize_generation_args( $args );

		// Custom query, back-end or sitemap.
		if ( empty( $args['pta'] ) && empty( $args['tax'] ) && empty( $args['uid'] ) ) {
			switch ( \get_post_type( $args['id'] ) ) {
				case \bbp_get_forum_post_type():
					$forum_id = $args['id'];
					break;
				case \bbp_get_topic_post_type():
				case \bbp_get_reply_post_type():
					$forum_id = \get_post_meta( $args['id'], '_bbp_forum_id', true );
			}
		}
	} else {
		if ( ! Helper\Compatibility::can_i_use( [
			'functions' => [
				'bbp_is_single_forum',
				'bbp_is_single_topic',
				'bbp_is_single_reply',
			],
		] ) ) return $meta;

		// Front-end
		if ( \bbp_is_single_forum() ) {
			$forum_id = Query::get_the_real_id();
		} elseif ( \bbp_is_single_topic() ) {
			$forum_id = \get_post_meta( Query::get_the_real_id(), '_bbp_forum_id', true );
		} elseif ( \bbp_is_single_reply() ) {
			$forum_id = \get_post_meta( Query::get_the_real_id(), '_bbp_forum_id', true );
		}
	}

	if ( ! empty( $forum_id ) && ! \bbp_is_forum_public( $forum_id ) )
		$meta['noindex'] = 'noindex';

	return $meta;
}

/**
 * Appends noindex default checks to the noindex item of the SEO Bar for pages.
 *
 * @hook the_seo_framework_seo_bar 10
 * @since 4.2.8
 * @access private
 *
 * @param string $interpreter The interpreter class name.
 * @param object $builder     The builder's class instance.
 */
function _assert_bbpress_noindex_defaults_seo_bar( $interpreter, $builder ) {

	if ( $interpreter::$query['tax'] ) return;

	if ( ! Helper\Compatibility::can_i_use( [
		'functions' => [
			'bbp_get_forum_post_type',
			'bbp_get_topic_post_type',
			'bbp_get_reply_post_type',
			'bbp_is_forum_public',
		],
	] ) ) return;

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
	}

	if ( empty( $forum_id ) || \bbp_is_forum_public( $forum_id ) ) return;

	$index_item           = &$interpreter::edit_seo_bar_item( 'indexing' );
	$index_item['status'] = 0 !== Sanitize::qubit( $builder->get_query_cache()['meta']['_genesis_noindex'] )
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
