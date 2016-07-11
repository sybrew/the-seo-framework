<?php
$site_url = $this->the_home_url_from_cache( true );

$sitemap_url = $site_url . 'sitemap.xml';
$has_sitemap_plugin = $this->detect_sitemap_plugin();
$sitemap_detected = $this->has_sitemap_xml();

?><h4><?php _e( 'Sitemap Integration Settings', 'autodescription' ); ?></h4><?php

if ( $has_sitemap_plugin ) {
	$this->description( __( "Another active sitemap plugin has been detected. This means that the sitemap functionality has been replaced.", 'autodescription' ) );
} else if ( $sitemap_detected ) {
	$this->description( __( "A sitemap has been detected in the root folder of your website. This means that the sitemap functionality has no effect.", 'autodescription' ) );
} else {
	$this->description( __( "The Sitemap is an XML file that lists pages and posts for your website along with optional metadata about each post or page. This helps Search Engines crawl your website more easily.", 'autodescription' ) );
	$this->description( __( "The optional metadata include the post and page modified time and a page priority indication, which is automated.", 'autodescription' ) );

	?>
	<hr>

	<h4><?php _e( 'Sitemap Output', 'autodescription' ); ?></h4>
	<?php

	//* Echo checkbox.
	$this->wrap_fields(
		$this->make_checkbox(
			'sitemaps_output',
			__( 'Output Sitemap?', 'autodescription' ),
			''
		), true
	);
}

if ( ! ( $has_sitemap_plugin || $sitemap_detected ) && $this->get_option( 'sitemaps_output' ) ) {
	$here = '<a href="' . $sitemap_url  . '" target="_blank" title="' . __( 'View sitemap', 'autodescription' ) . '">' . _x( 'here', 'The sitemap can be found %s.', 'autodescription' ) . '</a>';
	$this->description_noesc( sprintf( _x( 'The sitemap can be found %s.', '%s = here', 'autodescription' ), $here ) );
}
