<h4><?php _e( 'Site Shortlink Settings', 'autodescription' ); ?></h4><?php
$this->description( __( 'The shortlink tag might have some use for 3rd party service discoverability, but it has little to no SEO value whatsoever.', 'autodescription' ) );

//* Echo checkboxes.
$this->wrap_fields(
	$this->make_checkbox(
		'shortlink_tag',
		__( 'Output shortlink tag?', 'autodescription' ),
		''
	),
	true
);

?>
<hr>

<h4><?php _e( 'Social Meta Tags Settings', 'autodescription' ); ?></h4><?php
$this->description( __( 'Output various meta tags for social site integration, among other 3rd party services.', 'autodescription' ) );

?><hr><?php

//* Echo Open Graph Tags checkboxes.
$this->wrap_fields(
	$this->make_checkbox(
		'og_tags',
		__( 'Output Open Graph meta tags?', 'autodescription' ),
		__( 'Facebook, Twitter, Pinterest and many other social sites make use of these tags.', 'autodescription' )
	),
	true
);

if ( $this->detect_og_plugin() )
	$this->description( __( 'Note: Another Open Graph plugin has been detected.', 'autodescription' ) );

?><hr><?php

//* Echo Facebook Tags checkbox.
$this->wrap_fields(
	$this->make_checkbox(
		'facebook_tags',
		__( 'Output Facebook meta tags?', 'autodescription' ),
		sprintf( __( 'Output various tags targetted at %s.', 'autodescription' ), 'Facebook' )
	),
	true
);

?><hr><?php

//* Echo Twitter Tags checkboxes.
$this->wrap_fields(
	$this->make_checkbox(
		'twitter_tags',
		__( 'Output Twitter meta tags?', 'autodescription' ),
		sprintf( __( 'Output various tags targetted at %s.', 'autodescription' ), 'Twitter' )
	),
	true
);

if ( $this->detect_twitter_card_plugin() )
	$this->description( __( 'Note: Another Twitter Card plugin has been detected.', 'autodescription' ) );
