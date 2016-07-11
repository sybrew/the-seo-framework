<?php
$pages_i18n = __( 'Pages', 'autodescription' );
$posts_i18n = __( 'Posts', 'autodescription' );
$home_i18n = __( 'Home Page', 'autodescription' );

?><h4><?php _e( 'Post Date Settings', 'autodescription' ); ?></h4><?php
$this->description( __( "Some Search Engines output the publishing date and modified date next to the search results. These help Search Engines find new content and could impact the SEO value.", 'autodescription' ) );
$this->description( __( "It's recommended on posts, but it's not recommended on pages unless you modify or create new pages frequently.", 'autodescription' ) );

/* translators: 1: Option, 2: Post Type */
$post_publish_time_label = sprintf( __( 'Add %1$s to %2$s?', 'autodescription' ), $this->code_wrap( 'article:published_time' ), $posts_i18n );
$post_publish_time_checkbox = $this->make_checkbox( 'post_publish_time', $post_publish_time_label, '' );

/* translators: 1: Option, 2: Post Type */
$page_publish_time_label = sprintf( __( 'Add %1$s to %2$s?', 'autodescription' ), $this->code_wrap( 'article:published_time' ), $pages_i18n );
$page_publish_time_checkbox = $this->make_checkbox( 'page_publish_time', $page_publish_time_label, '' );

//* Echo checkboxes.
$this->wrap_fields( $post_publish_time_checkbox . $page_publish_time_checkbox, true );

/* translators: 1: Option, 2: Post Type */
$post_modify_time_label = sprintf( __( 'Add %1$s to %2$s?', 'autodescription' ), $this->code_wrap( 'article:modified_time' ), $posts_i18n );
$post_modify_time_checkbox = $this->make_checkbox( 'post_modify_time', $post_modify_time_label, '' );

/* translators: 1: Option, 2: Post Type */
$page_modify_time_label = sprintf( __( 'Add %1$s to %2$s?', 'autodescription' ), $this->code_wrap( 'article:modified_time' ), $pages_i18n );
$page_modify_time_checkbox = $this->make_checkbox( 'page_modify_time', $page_modify_time_label, '' );

//* Echo checkboxes.
$this->wrap_fields( $post_modify_time_checkbox . $page_modify_time_checkbox, true );

?>
<hr>

<h4><?php _e( 'Home Page', 'autodescription' ); ?></h4>
<?php
$this->description( __( "Because you only publish the Home Page once, Search Engines might think your website is outdated. This can be prevented by disabling the following options.", 'autodescription' ) );

/* translators: 1: Option, 2: Post Type */
$home_publish_time_label = sprintf( __( 'Add %1$s to %2$s?', 'autodescription' ), $this->code_wrap( 'article:published_time' ), $home_i18n );
$home_publish_time_checkbox = $this->make_checkbox( 'home_publish_time', $home_publish_time_label, '' );

/* translators: 1: Option, 2: Post Type */
$home_modify_time_label = sprintf( __( 'Add %1$s to %2$s?', 'autodescription' ), $this->code_wrap( 'article:modified_time' ), $home_i18n );
$home_modify_time_checkbox = $this->make_checkbox( 'home_modify_time', $home_modify_time_label, '' );

//* Echo checkboxes.
$this->wrap_fields( $home_publish_time_checkbox . $home_modify_time_checkbox, true );
