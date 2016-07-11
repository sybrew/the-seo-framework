<?php
//* Robots types
$types = array(
	'category' => __( 'Category', 'autodescription' ),
	'tag' => __( 'Tag', 'autodescription' ),
	'author' => __( 'Author', 'autodescription' ),
	'date' => __( 'Date', 'autodescription' ),
	'search' => __( 'Search Pages', 'autodescription' ),
	'attachment' => __( 'Attachment Pages', 'autodescription' ),
	'site' => _x( 'the entire site', '...for the entire site', 'autodescription' ),
);

//* Robots i18n
$robots = array(
	'noindex' =>  array(
		'value' => 'noindex',
		'name' 	=> __( 'NoIndex', 'autodescription' ),
		'desc' 	=> __( 'These options prevent indexing of the selected archives and pages. If you enable this, the selected archives or pages will be removed from Search Engine results pages.', 'autodescription' ),
	),
	'nofollow' =>  array(
		'value' => 'nofollow',
		'name'	=> __( 'NoFollow', 'autodescription' ),
		'desc'	=> __( 'These options prevent links from being followed on the selected archives and pages. If you enable this, the selected archives or pages in-page links will gain no SEO value, including your own links.', 'autodescription' ),
	),
	'noarchive' =>  array(
		'value' => 'noarchive',
		'name'	=> __( 'NoArchive', 'autodescription' ),
		'desc'	=> __( 'These options prevent caching of the selected archives and pages. If you enable this, Search Engines will not create a cached copy of the selected archives or pages.', 'autodescription' ),
	),
);

/**
 * Parse tabs content.
 *
 * @since 2.2.2
 *
 * @param array $default_tabs { 'id' = The identifier =>
 *			array(
 *				'name' 		=> The name
 *				'callback'	=> function callback
 *				'dashicon'	=> WordPress Dashicon
 *				'args'		=> function args
 *			)
 * }
 */
$default_tabs = array(
		'general' => array(
			'name' 		=> __( 'General', 'autodescription' ),
			'callback'	=> array( $this, 'robots_metabox_general_tab' ),
			'dashicon'	=> 'admin-generic',
			'args'		=> '',
		),
		'index' => array(
			'name' 		=> __( 'Indexing', 'autodescription' ),
			'callback'	=> array( $this, 'robots_metabox_no_tab' ),
			'dashicon'	=> 'filter',
			'args'		=> array( $types, $robots['noindex'] ),
		),
		'follow' => array(
			'name'		=> __( 'Following', 'autodescription' ),
			'callback'	=> array( $this, 'robots_metabox_no_tab' ),
			'dashicon'	=> 'editor-unlink',
			'args'		=> array( $types, $robots['nofollow'] ),
		),
		'archive' => array(
			'name'		=> __( 'Archiving', 'autodescription' ),
			'callback'	=> array( $this, 'robots_metabox_no_tab' ),
			'dashicon'	=> 'download',
			'args'		=> array( $types, $robots['noarchive'] ),
		),
	);

/**
 * Applies filters 'the_seo_framework_robots_settings_tabs' : array see $default_tabs
 *
 * Used to extend Social tabs
 * @since 2.2.4
 */
$defaults = (array) apply_filters( 'the_seo_framework_robots_settings_tabs', $default_tabs, $args );

$tabs = wp_parse_args( $args, $defaults );

$this->nav_tab_wrapper( 'robots', $tabs, '2.2.4' );
