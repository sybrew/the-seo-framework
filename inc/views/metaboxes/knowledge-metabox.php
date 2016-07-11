<?php
/**
 * Parse tabs content.
 *
 * @since 2.2.8
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
		'callback'	=> array( $this, 'knowledge_metabox_general_tab' ),
		'dashicon'	=> 'admin-generic',
	),
	'website' => array(
		'name'		=> __( 'Website', 'autodescription' ),
		'callback'	=> array( $this, 'knowledge_metabox_about_tab' ),
		'dashicon'	=> 'admin-home',
	),
	'social' => array(
		'name'		=> 'Social Sites',
		'callback'	=> array( $this, 'knowledge_metabox_social_tab' ),
		'dashicon'	=> 'networking',
	),
);

/**
 * Applies filter knowledgegraph_settings_tabs : Array see $default_tabs
 * @since 2.2.8
 * Used to extend Knowledge Graph tabs
 */
$defaults = (array) apply_filters( 'the_seo_framework_knowledgegraph_settings_tabs', $default_tabs, $args );

$tabs = wp_parse_args( $args, $defaults );

$this->nav_tab_wrapper( 'knowledge', $tabs, '2.2.8' );
