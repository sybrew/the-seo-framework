<h4><?php _e( 'Knowledge Graph Settings', 'autodescription' ); ?></h4><?php
$this->description( __( "The Knowledge Graph lets Google and other Search Engines know where to find you or your organization and its relevant content.", 'autodescription' ) );
$this->description( __( "Google is becoming more of an 'Answer Engine' than a 'Search Engine'. Setting up these options could have a positive impact on the SEO value of your website.", 'autodescription' ) );

//* Echo checkbox.
$this->wrap_fields(
	$this->make_checkbox(
		'knowledge_output',
		__( 'Output Knowledge tags?', 'autodescription' ),
		''
	), true
);

if ( $this->wp_version( '4.2.999', '>=' ) ) :
?>
	<hr>

	<h4><?php _e( "Website logo", 'autodescription' ); ?></h4>
	<?php
	//* Echo checkbox.
	$this->wrap_fields(
		$this->make_checkbox(
			'knowledge_logo',
			__( 'Use the Favicon from Customizer as the Organization Logo?', 'autodescription' ),
			__( "This option only has an effect when this site represents an Organization. If left disabled, Search Engines will look elsewhere for a logo, if it exists and is assigned as a logo.", 'autodescription' )
		), true
	);
endif;
