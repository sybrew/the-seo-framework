<h4><?php _e( 'Content Feed Settings', 'autodescription' ); ?></h4><?php
$this->description( __( "Sometimes, your content can get stolen by robots through the WordPress feeds. This can cause duplicate content issues. To prevent this from happening, it's recommended to convert the feed's content into an excerpt.", 'autodescription' ) );
$this->description( __( "Adding a backlink below the feed's content will also let the visitors know where the content came from.", 'autodescription' ) );

?>
<hr>

<h4><?php _e( 'Change Feed Settings', 'autodescription' ); ?></h4>
<?php
$excerpt_the_feed_label = __( 'Convert feed content into excerpts?', 'autodescription' );
$excerpt_the_feed_label .= ' ' . $this->make_info( __( "By default the excerpt will be at most 400 characters long", 'autodescription' ), '', false );

$source_the_feed_label = __( 'Add backlinks below the feed content?', 'autodescription' );
$source_the_feed_label .= ' ' . $this->make_info( __( "This link will not be followed by Search Engines", 'autodescription' ), '', false );

//* Echo checkboxes.
$this->wrap_fields(
	array(
		$this->make_checkbox( 'excerpt_the_feed', $excerpt_the_feed_label, '' ),
		$this->make_checkbox( 'source_the_feed', $source_the_feed_label, '' ),
	), true
);

if ( $this->rss_uses_excerpt() ) {
	$reading_settings_url = esc_url( admin_url( 'options-reading.php' ) );
	$reading_settings = '<a href="' . $reading_settings_url  . '" target="_blank" title="' . __( 'Reading Settings', 'autodescription' ) . '">' . __( 'Reading Settings', 'autodescription' ) . '</a>';

	$this->description_noesc( sprintf( _x( "Note: The feed is already converted into an excerpt through the %s.", '%s = Reading Settings', 'autodescription' ), $reading_settings ) );
}

$feed_url = esc_url( get_feed_link() );
$here = '<a href="' . $feed_url  . '" target="_blank" title="' . __( 'View feed', 'autodescription' ) . '">' . _x( 'here', 'The feed can be found %s.', 'autodescription' ) . '</a>';

$this->description_noesc( sprintf( _x( 'The feed can be found %s.', '%s = here', 'autodescription' ), $here ) );
