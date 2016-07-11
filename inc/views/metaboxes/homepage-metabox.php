<?php
$this->description( __( 'These settings will take precedence over the settings set within the Home Page edit screen, if any.', 'autodescription' ) );

?><hr><?php

/**
 * Parse tabs content.
 *
 * @param array $default_tabs { 'id' = The identifier =>
 *			array(
 *				'name' 		=> The name
 *				'callback' 	=> The callback function, use array for method calling (accepts $this, but isn't used here for optimization purposes)
 *				'dashicon'	=> Desired dashicon
 *			)
 * }
 *
 * @since 2.6.0
 */
$default_tabs = array(
	'general' => array(
		'name' 		=> __( 'General', 'autodescription' ),
		'callback'	=> array( $this, 'homepage_metabox_general' ),
		'dashicon'	=> 'admin-generic',
	),
	'additions' => array(
		'name'		=> __( 'Additions', 'autodescription' ),
		'callback'	=> array( $this, 'homepage_metabox_additions' ),
		'dashicon'	=> 'plus',
	),
	'robots' => array(
		'name'		=> __( 'Robots', 'autodescription' ),
		'callback'	=> array( $this, 'homepage_metabox_robots' ),
		'dashicon'	=> 'visibility',
	),
);

/**
 * Applies filters the_seo_framework_homepage_settings_tabs : array see $default_tabs
 * @since 2.6.0
 * Used to extend HomePage tabs.
 */
$defaults = (array) apply_filters( 'the_seo_framework_homepage_settings_tabs', $default_tabs, $args );

$tabs = wp_parse_args( $args, $defaults );

$this->nav_tab_wrapper( 'homepage', $tabs, '2.6.0' );
