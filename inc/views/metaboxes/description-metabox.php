<?php
$blogname = $this->get_blogname();
$sep = $this->get_separator( 'description', true );

/**
 * Generate example.
 */
$page_title = __( 'Example Title', 'autodescription' );
$on = _x( 'on', 'Placement. e.g. Post Title "on" Blog Name', 'autodescription' );
$excerpt = __( 'This is an example description...', 'autodescription' );

$page_title = $this->escape_description( $page_title );
$on = $this->escape_description( $on );
$excerpt = $this->escape_description( $excerpt );

//* Put it together.
$example 	= '<span id="description-additions-js">'
				. $page_title
				. '<span id="on-blogname-js">' . " $on " . $blogname . '</span>'
				. '<span id="autodescription-descsep-js">' . " $sep " . '</span>'
			. '</span>'
			. $excerpt
			;

$nojs_additions = '';
//* Add or remove additions based on option.
if ( $this->add_description_additions() ) {
	$description_blogname_additions = $this->get_option( 'description_blogname' );

	$nojs_additions = $description_blogname_additions ? $page_title . " $on " . $blogname : $page_title;
	$nojs_additions = $nojs_additions . " $sep ";
}

$example_nojs = $nojs_additions . $excerpt;

?>
<h4><?php printf( __( 'Automated Description Settings', 'autodescription' ) ); ?></h4>
<p><span class="description"><?php printf( __( "The meta description can be used to determine the text used under the title on Search Engine results pages.", 'autodescription' ) ); ?></span></p>

<h4><?php _e( 'Example Automated Description Output', 'autodescription' ); ?></h4>
<p class="hide-if-no-js"><?php echo $this->code_wrap_noesc( $example ); ?></p>
<p class="hide-if-js"><?php echo $this->code_wrap( $example_nojs ); ?></p>

<hr>
<?php

/**
 * Parse tabs content.
 *
 * @since 2.6.0
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
		'callback'	=> array( $this, 'description_metabox_general_tab' ),
		'dashicon'	=> 'admin-generic',
	),
	'additions' => array(
		'name'		=> __( 'Additions', 'autodescription' ),
		'callback'	=> array( $this, 'description_metabox_additions_tab' ),
		'dashicon'	=> 'plus',
	),
);

/**
 * Applies filters the_seo_framework_description_settings_tabs : array see $default_tabs
 * @since 2.6.0
 *
 * Used to extend Description tabs.
 */
$defaults = (array) apply_filters( 'the_seo_framework_description_settings_tabs', $default_tabs, $args );

$tabs = wp_parse_args( $args, $defaults );

$this->nav_tab_wrapper( 'description', $tabs, '2.6.0' );
