<?php

defined( 'ABSPATH' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

//* Fetch the required instance within this file.
$instance = $this->get_view_instance( 'the_seo_framework_feed_metabox', $instance );

switch ( $instance ) :
	case 'the_seo_framework_feed_metabox_main' :
		?><h4><?php esc_html_e( 'Content Feed Settings', 'autodescription' ); ?></h4><?php
		$this->description( __( "Sometimes, your content can get stolen by robots through the WordPress feeds. This can cause duplicate content issues. To prevent this from happening, it's recommended to convert the feed's content into an excerpt.", 'autodescription' ) );
		$this->description( __( 'Adding a backlink below the feed entries will also let the visitors know where the content came from.', 'autodescription' ) );

		?>
		<hr>

		<h4><?php esc_html_e( 'Change Feed Settings', 'autodescription' ); ?></h4>
		<?php
		$excerpt_the_feed_label = esc_html__( 'Convert feed entries into excerpts?', 'autodescription' );
		$excerpt_the_feed_label .= ' ' . $this->make_info( __( 'By default the excerpt will be at most 400 characters long', 'autodescription' ), '', false );

		$source_the_feed_label = esc_html__( 'Add link to source below the feed entry content?', 'autodescription' );
		$source_the_feed_label .= ' ' . $this->make_info( __( 'This link will not be followed by Search Engines', 'autodescription' ), '', false );

		//* Echo checkboxes.
		$this->wrap_fields(
			array(
				$this->make_checkbox( 'excerpt_the_feed', $excerpt_the_feed_label, '', false ),
				$this->make_checkbox( 'source_the_feed', $source_the_feed_label, '', false ),
			), true
		);

		if ( $this->rss_uses_excerpt() ) {
			$reading_settings_url = admin_url( 'options-reading.php' );
			$reading_settings_title = __( 'Reading Settings', 'autodescription' );
			$reading_settings = '<a href="' . esc_url( $reading_settings_url ) . '" target="_blank" title="' . esc_attr( $reading_settings_title ) . '">' . esc_html( $reading_settings_title ) . '</a>';

			$this->description_noesc( sprintf( esc_html_x( 'Note: The feed is already converted into an excerpt (summary) through the %s.', '%s = Reading Settings', 'autodescription' ), $reading_settings ) );
		}

		$feed_url = get_feed_link();
		$here = '<a href="' . esc_url( $feed_url ) . '" target="_blank" title="' . esc_attr__( 'View feed', 'autodescription' ) . '">' . esc_html_x( 'here', 'The feed can be found %s.', 'autodescription' ) . '</a>';

		$this->description_noesc( sprintf( esc_html_x( 'The feed can be found %s.', '%s = here', 'autodescription' ), $here ) );
		break;

	default :
		break;
endswitch;
