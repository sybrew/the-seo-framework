<?php
$latest_post_id = $this->get_latest_post_id();

if ( $latest_post_id ) {
	$post = get_post( $latest_post_id, OBJECT );
	$title = esc_attr( $post->post_title );
} else {
	$title = esc_attr__( 'Example Post Title', 'autodescription' );
}

$blogname = $this->get_blogname();
$sep = $this->get_separator( 'title', true );

$additions_left = '<span class="title-additions-js">' . $blogname . '<span class="autodescription-sep-js">' . " $sep " . '</span></span>';
$additions_right = '<span class="title-additions-js"><span class="autodescription-sep-js">' . " $sep " . '</span>' . $blogname . '</span>';

$example_left = '<em>' . $additions_left . $title . '</em>';
$example_right = '<em>' . $title . $additions_right . '</em>';

//* There's no need for "hide-if-no-js" here.
//* Check left first, as right is default (and thus fallback).
$showleft = 'left' === $this->get_option( 'title_location' );

?>
<h4><?php _e( 'Automated Title Settings', 'autodescription' ); ?></h4>
<?php $this->description( __( "The page title is prominently shown within the browser tab as well as within the Search Engine results pages.", 'autodescription' ) ); ?>

<h4><?php _e( 'Example Automated Title Output', 'autodescription' ); ?></h4>
<p>
	<span class="title-additions-example-left" style="display:<?php echo $showleft ? 'inline' : 'none'; ?>"><?php echo $this->code_wrap_noesc( $example_left ); ?></span>
	<span class="title-additions-example-right" style="display:<?php echo $showleft ? 'none' : 'inline'; ?>"><?php echo $this->code_wrap_noesc( $example_right ); ?></span>
</p>

<hr>
<?php

/**
 * Parse tabs content.
 *
 * @since 2.2.2
 *
 * @param array $default_tabs { 'id' = The identifier =>
 *			array(
 *				'name' 		=> The name
 *				'callback' 	=> The callback function, use array for method calling (accepts $this, but isn't used here for optimization purposes)
 *				'dashicon'	=> Desired dashicon
 *			)
 * }
 */
$default_tabs = array(
	'general' => array(
		'name' 		=> __( 'General', 'autodescription' ),
		'callback'	=> array( $this, 'title_metabox_general_tab' ),
		'dashicon'	=> 'admin-generic',
	),
	'additions' => array(
		'name'		=> __( 'Additions', 'autodescription' ),
		'callback'	=> array( $this, 'title_metabox_additions_tab' ),
		'dashicon'	=> 'plus',
		'args'		=> array(
			'examples' => array(
				'left'	=> $example_left,
				'right' => $example_right,
			),
		),
	),
	'prefixes' => array(
		'name'		=> __( 'Prefixes', 'autodescription' ),
		'callback'	=> array( $this, 'title_metabox_prefixes_tab' ),
		'dashicon'	=> 'plus-alt',
		'args'		=> array(
			'additions' => array(
				'left'	=> $additions_left,
				'right' => $additions_right,
			),
			'showleft' => $showleft,
		),
	)
);

/**
 * Applies filters the_seo_framework_title_settings_tabs : array see $default_tabs
 * @since 2.6.0
 *
 * Used to extend Description tabs.
 */
$defaults = (array) apply_filters( 'the_seo_framework_title_settings_tabs', $default_tabs, $args );

$tabs = wp_parse_args( $args, $defaults );

$this->nav_tab_wrapper( 'title', $tabs, '2.6.0' );
