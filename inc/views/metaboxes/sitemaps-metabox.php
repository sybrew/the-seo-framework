<?php
if ( ! $this->pretty_permalinks ) {

	$permalink_settings_url = esc_url( admin_url( 'options-permalink.php' ) );
	$here = '<a href="' . $permalink_settings_url  . '" target="_blank" title="' . __( 'Permalink Settings', 'autodescription' ) . '">' . _x( 'here', 'The sitemap can be found %s.', 'autodescription' ) . '</a>';

	?><h4><?php _e( "You're using the plain permalink structure.", 'autodescription' ); ?></h4><?php
	$this->description( __( "This means we can't output the sitemap through the WordPress rewrite rules.", 'autodescription' ) );
	?><hr><?php
	$this->description_noesc( sprintf( _x( "Change your Permalink Settings %s (Recommended: 'postname').", '%s = here', 'autodescription' ), $here ) );

} else {

	/**
	 * Parse tabs content
	 *
	 * @param array $default_tabs { 'id' = The identifier =>
	 *			array(
	 *				'name' 		=> The name
	 *				'callback' 	=> The callback function, use array for method calling (accepts $this, but isn't used here for optimization purposes)
	 *				'dashicon'	=> Desired dashicon
	 *			)
	 * }
	 *
	 * @since 2.2.9
	 */
	$default_tabs = array(
		'general' => array(
			'name' 		=> __( 'General', 'autodescription' ),
			'callback'	=> array( $this, 'sitemaps_metabox_general_tab' ),
			'dashicon'	=> 'admin-generic',
		),
		'robots' => array(
			'name'		=> 'Robots.txt',
			'callback'	=> array( $this, 'sitemaps_metabox_robots_tab' ),
			'dashicon'	=> 'share-alt2',
		),
		'timestamps' => array(
			'name'		=> __( 'Timestamps', 'autodescription' ),
			'callback'	=> array( $this, 'sitemaps_metabox_timestamps_tab' ),
			'dashicon'	=> 'backup',
		),
		'notify' => array(
			'name'		=> _x( 'Ping', 'Ping or notify Search Engine', 'autodescription' ),
			'callback'	=> array( $this, 'sitemaps_metabox_notify_tab' ),
			'dashicon'	=> 'megaphone',
		),
	);

	/**
	 * Applies filters the_seo_framework_sitemaps_settings_tabs : array see $default_tabs
	 *
	 * Used to extend Knowledge Graph tabs
	 */
	$defaults = (array) apply_filters( 'the_seo_framework_sitemaps_settings_tabs', $default_tabs, $args );

	$tabs = wp_parse_args( $args, $defaults );
	$use_tabs = true;

	$sitemap_plugin = $this->detect_sitemap_plugin();
	$sitemap_detected = $this->has_sitemap_xml();
	$robots_detected = $this->has_robots_txt();

	/**
	 * Remove the timestamps and notify submenus
	 * @since 2.5.2
	 */
	if ( $sitemap_plugin || $sitemap_detected ) {
		unset( $tabs['timestamps'] );
		unset( $tabs['notify'] );
	}

	/**
	 * Remove the robots submenu
	 * @since 2.5.2
	 */
	if ( $robots_detected ) {
		unset( $tabs['robots'] );
	}

	if ( $robots_detected && ( $sitemap_plugin || $sitemap_detected ) )
		$use_tabs = false;

	$this->nav_tab_wrapper( 'sitemaps', $tabs, '2.2.8', $use_tabs );

}
