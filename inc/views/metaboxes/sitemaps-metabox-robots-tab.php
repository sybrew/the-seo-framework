<?php
$site_url = $this->the_home_url_from_cache( true );
$robots_url = trailingslashit( $site_url ) . 'robots.txt';
$here =  '<a href="' . $robots_url  . '" target="_blank" title="' . __( 'View robots.txt', 'autodescription' ) . '">' . _x( 'here', 'The sitemap can be found %s.', 'autodescription' ) . '</a>';

?>
<h4><?php _e( 'Robots.txt Settings', 'autodescription' ); ?></h4>
<?php

if ( $this->can_do_sitemap_robots() ) :
	$this->description( __( 'The robots.txt file is the first thing Search Engines look for. If you add the sitemap location in the robots.txt file, then Search Engines will look for and index the sitemap.', 'autodescription' ) );
	$this->description( __( 'If you do not add the sitemap location to the robots.txt file, you will need to notify Search Engines manually through the Webmaster Console provided by the Search Engines.', 'autodescription' ) );

	?>
	<hr>

	<h4><?php _e( 'Add sitemap location in robots.txt', 'autodescription' ); ?></h4>
	<?php

	//* Echo checkbox.
	$this->wrap_fields(
		$this->make_checkbox(
			'sitemaps_robots',
			__( 'Add sitemap location in robots?', 'autodescription' ),
			''
		), true
	);
else :
	$this->description( __( 'Another robots.txt sitemap Location addition has been detected.', 'autodescription' ) );
endif;

$this->description_noesc( sprintf( _x( 'The robots.txt file can be found %s.', '%s = here', 'autodescription' ), $here ) );
