<h4><?php _e( 'Ping Settings', 'autodescription' ); ?></h4><?php
$this->description( __( "Notifying Search Engines of a sitemap change is helpful to get your content indexed as soon as possible.", 'autodescription' ) );
$this->description( __( "By default this will happen at most once an hour.", 'autodescription' ) );

?>
<hr>

<h4><?php _e( 'Notify Search Engines', 'autodescription' ); ?></h4>
<?php
$engines = array(
	'ping_google'	=> 'Google',
	'ping_bing' 	=> 'Bing',
	'ping_yandex'	=> 'Yandex'
);

$ping_checkbox = '';

foreach ( $engines as $option => $engine ) {
	$ping_label = sprintf( __( 'Notify %s about sitemap changes?', 'autodescription' ), $engine );
	$ping_checkbox .= $this->make_checkbox( $option, $ping_label, '' );
}

//* Echo checkbox.
$this->wrap_fields( $ping_checkbox, true );
