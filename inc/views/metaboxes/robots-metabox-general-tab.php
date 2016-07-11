<h4><?php _e( 'Open Directory Settings', 'autodescription' ); ?></h4>
<?php
$this->description( __( "Sometimes, Search Engines use resources from certain Directories to find titles and descriptions for your content. You generally don't want them to. Turn these options on to prevent them from doing so.", 'autodescription' ) );
$this->description( __( "The Open Directory Project and the Yahoo! Directory may contain outdated SEO values. Therefore, it's best to leave these options checked.", 'autodescription' ) );

$fields = $this->wrap_fields(
	array(
		$this->make_checkbox(
			'noodp',
			sprintf( __( 'Apply %s to the entire site?', 'autodescription' ), $this->code_wrap( 'noodp' ) ),
			''
		),
		$this->make_checkbox(
			'noydir',
			sprintf( __( 'Apply %s to the entire site?', 'autodescription' ), $this->code_wrap( 'noydir' ) ),
			''
		),
	),
	true
);
?>
<hr>

<h4><?php _e( 'Paginated Archive Settings', 'autodescription' ); ?></h4>
<p class="description"><?php printf( __( "Indexing the second or later page of any archive might cause duplication errors. Search Engines look down upon them; therefore, it's recommended to disable indexing of those pages.", 'autodescription' ), $this->code_wrap( 'noodp' ), $this->code_wrap( 'noydir' ) ); ?></p>
<?php

$this->wrap_fields(
	$this->make_checkbox(
		'paged_noindex',
		sprintf( __( 'Apply %s to every second or later archive page?', 'autodescription' ), $this->code_wrap( 'noindex' ) ),
		''
	),
true
);
