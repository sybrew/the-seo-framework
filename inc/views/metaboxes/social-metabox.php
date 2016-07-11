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
		'callback'	=> array( $this, 'social_metabox_general_tab' ),
		'dashicon'	=> 'admin-generic',
	),
	'facebook' => array(
		'name'		=> 'Facebook',
		'callback'	=> array( $this, 'social_metabox_facebook_tab' ),
		'dashicon'	=> 'facebook-alt',
	),
	'twitter' => array(
		'name'		=> 'Twitter',
		'callback'	=> array( $this, 'social_metabox_twitter_tab' ),
		'dashicon'	=> 'twitter',
	),
	'postdates' => array(
		'name'		=> __( 'Post Dates', 'autodescription' ),
		'callback'	=> array( $this, 'social_metabox_postdates_tab' ),
		'dashicon'	=> 'backup',
	),
	'relationships' => array(
		'name'		=> __( 'Link Relationships', 'autodescription' ),
		'callback'	=> array( $this, 'social_metabox_relationships_tab' ),
		'dashicon'	=> 'leftright',
	),
);

/**
 * Applies filters the_seo_framework_social_settings_tabs : array see $default_tabs
 *
 * Used to extend Social tabs
 */
$defaults = (array) apply_filters( 'the_seo_framework_social_settings_tabs', $default_tabs, $args );

$tabs = wp_parse_args( $args, $defaults );

$this->nav_tab_wrapper( 'social', $tabs, '2.2.2' );
