<h4><?php _e( 'Link Relationship Settings', 'autodescription' ); ?></h4>
<?php
$this->description( __( "Some Search Engines look for relations between the content of your pages. If you have multiple pages for a single Post or Page, or have archives indexed, this option will help Search Engines look for the right page to display in the Search Results.", 'autodescription' ) );
$this->description( __( "It's recommended to turn this option on for better SEO consistency and to prevent duplicate content errors.", 'autodescription' ) );

?><hr><?php

/* translators: %s = <code>rel</code> */
$prev_next_posts_label = sprintf( __( 'Add %s link tags to Posts and Pages?', 'autodescription' ), $this->code_wrap( 'rel' ) );
$prev_next_posts_checkbox = $this->make_checkbox( 'prev_next_posts', $prev_next_posts_label, '' );

/* translators: %s = <code>rel</code> */
$prev_next_archives_label = sprintf( __( 'Add %s link tags to Archives?', 'autodescription' ), $this->code_wrap( 'rel' ) );
$prev_next_archives_checkbox = $this->make_checkbox( 'prev_next_archives', $prev_next_archives_label, '' );

/* translators: %s = <code>rel</code> */
$prev_next_frontpage_label = sprintf( __( 'Add %s link tags to the Home Page?', 'autodescription' ), $this->code_wrap( 'rel' ) );
$prev_next_frontpage_checkbox = $this->make_checkbox( 'prev_next_frontpage', $prev_next_frontpage_label, '' );

//* Echo checkboxes.
$this->wrap_fields( $prev_next_posts_checkbox . $prev_next_archives_checkbox . $prev_next_frontpage_checkbox, true );
