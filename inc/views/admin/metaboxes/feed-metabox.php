<?php
/**
 * @package The_SEO_Framework\Views\Admin\Metaboxes
 * @subpackage The_SEO_Framework\Admin\Settings
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

use The_SEO_Framework\Interpreters\HTML,
	The_SEO_Framework\Interpreters\Form;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and the_seo_framework()->_verify_include_secret( $_secret ) or die;

// Fetch the required instance within this file.
$instance = $this->get_view_instance( 'the_seo_framework_feed_metabox', $instance );

switch ( $instance ) :
	case 'the_seo_framework_feed_metabox_main':
		Form::header_title( __( 'Content Feed Settings', 'autodescription' ) );
		HTML::description( __( "Sometimes, your content can get stolen by robots through the WordPress feeds. This can cause duplicate content issues. To prevent this from happening, it's recommended to convert the feed's content into an excerpt.", 'autodescription' ) );
		HTML::description( __( 'Adding a backlink below the feed entries will also let the visitors know where the content came from.', 'autodescription' ) );

		?>
		<hr>
		<?php
		Form::header_title( __( 'Change Feed Settings', 'autodescription' ) );
		$excerpt_the_feed_label  = esc_html__( 'Convert feed entries into excerpts?', 'autodescription' );
		$excerpt_the_feed_label .= ' ' . HTML::make_info( __( 'By default the excerpt will be at most 400 characters long.', 'autodescription' ), '', false );

		$source_the_feed_label  = esc_html__( 'Add link to source below the feed entry content?', 'autodescription' );
		$source_the_feed_label .= ' ' . HTML::make_info( __( 'This link will not be followed by search engines.', 'autodescription' ), '', false );

		$index_the_feed_label  = esc_html__( 'Allow indexing of feeds?', 'autodescription' );
		$index_the_feed_label .= ' ' . HTML::make_info( __( 'If this site publishes podcasts, enable this option. Otherwise, leave it disabled. Indexing feeds can cause search engines to crawl and index new pages slower; however, some podcast services require feeds to be indexable.', 'autodescription' ), '', false );

		HTML::wrap_fields(
			[
				Form::make_checkbox( [
					'id'     => 'excerpt_the_feed',
					'label'  => $excerpt_the_feed_label,
					'escape' => false,
				] ),
				Form::make_checkbox( [
					'id'     => 'source_the_feed',
					'label'  => $source_the_feed_label,
					'escape' => false,
				] ),
				Form::make_checkbox( [
					'id'     => 'index_the_feed',
					'label'  => $index_the_feed_label,
					'escape' => false,
				] ),
			],
			true
		);

		if ( get_option( 'rss_use_excerpt' ) ) {
			HTML::description_noesc(
				$this->convert_markdown(
					sprintf(
						/* translators: %s = Reading Settings URL. Links are in Markdown! */
						esc_html__( 'Note: The feed is already converted into an excerpt through the [Reading Settings](%s).', 'autodescription' ),
						esc_url( admin_url( 'options-reading.php' ) )
					),
					[ 'a' ],
					[ 'a_internal' => false ] // open in new window, although it's internal.
				)
			);
		}

		HTML::description_noesc(
			sprintf(
				'<a href="%s" target=_blank rel=noopener>%s</a>',
				esc_url( get_feed_link(), [ 'https', 'http' ] ),
				esc_html__( 'View the main feed.', 'autodescription' )
			)
		);
		break;

	default:
		break;
endswitch;
