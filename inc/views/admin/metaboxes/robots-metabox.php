<?php
/**
 * @package The_SEO_Framework\Views\Admin\Metaboxes
 * @subpackage The_SEO_Framework\Admin\Settings
 */

use The_SEO_Framework\Bridges\SeoSettings;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

//* Fetch the required instance within this file.
$instance = $this->get_view_instance( 'the_seo_framework_robots_metabox', $instance );

switch ( $instance ) :
	case 'the_seo_framework_robots_metabox_main':
		//* Robots types
		$types = [
			'category' => __( 'Category archives', 'autodescription' ),
			'tag'      => __( 'Tag archives', 'autodescription' ),
			'author'   => __( 'Author pages', 'autodescription' ),
			'date'     => __( 'Date archives', 'autodescription' ),
			'search'   => __( 'Search pages', 'autodescription' ),
			'site'     => _x( 'the entire site', '...for the entire site', 'autodescription' ),
		];

		$post_types = $this->get_rewritable_post_types();

		//* Robots i18n
		$robots = [
			'noindex'   => [
				'value' => 'noindex',
				'desc'  => __( 'These options most likely prevent indexing of the selected archives and pages. If you enable this, the selected archives or pages will urge to be removed from search engine results pages.', 'autodescription' ),
			],
			'nofollow'  => [
				'value' => 'nofollow',
				'desc'  => __( 'These options most likely prevent links from being followed on the selected archives and pages. If you enable this, the selected archives or pages in-page links will gain no SEO value, including your own links.', 'autodescription' ),
			],
			'noarchive' => [
				'value' => 'noarchive',
				'desc'  => __( 'These options most likely prevent caching of the selected archives and pages. If you enable this, bots are urged not create a cached copy of the selected archives or pages.', 'autodescription' ),
			],
		];

		$default_tabs = [
			'general' => [
				'name'     => __( 'General', 'autodescription' ),
				'callback' => SeoSettings::class . '::_robots_metabox_general_tab',
				'dashicon' => 'admin-generic',
				'args'     => '',
			],
			'index'   => [
				'name'     => __( 'Indexing', 'autodescription' ),
				'callback' => SeoSettings::class . '::_robots_metabox_no_tab',
				'dashicon' => 'filter',
				'args'     => [ $types, $post_types, $robots['noindex'] ],
			],
			'follow'  => [
				'name'     => __( 'Following', 'autodescription' ),
				'callback' => SeoSettings::class . '::_robots_metabox_no_tab',
				'dashicon' => 'editor-unlink',
				'args'     => [ $types, $post_types, $robots['nofollow'] ],
			],
			'archive' => [
				'name'     => __( 'Archiving', 'autodescription' ),
				'callback' => SeoSettings::class . '::_robots_metabox_no_tab',
				'dashicon' => 'download',
				'args'     => [ $types, $post_types, $robots['noarchive'] ],
			],
		];

		/**
		 * @since 2.2.4
		 * @param array $defaults The default tabs.
		 * @param array $args     The args added on the callback.
		 */
		$defaults = (array) apply_filters( 'the_seo_framework_robots_settings_tabs', $default_tabs, $args );

		$tabs = wp_parse_args( $args, $defaults );

		SeoSettings::_nav_tab_wrapper( 'robots', $tabs );
		break;

	case 'the_seo_framework_robots_metabox_general':
		?>
		<h4><?php esc_html_e( 'Paginated Archive Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( "Indexing the second or later page of any archive might cause duplication errors. Search engines look down upon them; therefore, it's recommended to disable indexing of those pages.", 'autodescription' ) );

		$this->wrap_fields(
			$this->make_checkbox(
				'paged_noindex',
				$this->convert_markdown(
					/* translators: the backticks are Markdown! Preserve them as-is! */
					esc_html__( 'Apply `noindex` to every second or later archive page?', 'autodescription' ),
					[ 'code' ]
				),
				'',
				false
			),
			true
		);
		?>
		<hr>

		<h4><?php esc_html_e( 'Copyright Directive Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( "Some search engines allow you to control copyright directives on the content they aggregate. It's best to allow some content to be taken by these aggregators, as that can improve contextualized exposure via snippets and previews. When left unspecified, regional regulations may apply. It is up to the aggregator to honor these requests.", 'autodescription' ) );

		$this->wrap_fields(
			$this->make_checkbox(
				'set_copyright_directives',
				esc_html__( 'Specify aggregator copyright compliance directives?', 'autodescription' ),
				'',
				false
			),
			true
		);

		$_text_snippet_types['default'] = [
			-1 => __( 'Unlimited', 'autodescription' ),
			0  => _x( 'None, disallow snippet', 'quanity: zero', 'autodescription' ),
		];
		foreach ( range( 1, 600, 1 ) as $_n ) {
			/* translators: %d = number */
			$_text_snippet_types['number'][ $_n ] = sprintf( _n( '%d character', '%d characters', $_n, 'autodescription' ), $_n );
		}
		$text_snippet_options = '';
		$_current             = $this->get_option( 'max_snippet_length' );
		foreach ( $_text_snippet_types as $_type => $_values ) {
			$_label = 'default' === $_type
					? __( 'Standard directive', 'autodescription' )
					: __( 'Granular directive', 'autodescription' );

			$_options = '';
			foreach ( $_values as $_value => $_name ) {
				$_options .= vsprintf(
					'<option value="%s" %s>%s</option>',
					[
						esc_attr( $_value ),
						selected( $_current, esc_attr( $_value ), false ),
						esc_html( $_name ),
					]
				);
			}

			$text_snippet_options .= sprintf( '<optgroup label="%s">%s</optgroup>', esc_attr( $_label ), $_options );
		}
		$this->wrap_fields(
			vsprintf(
				'<p><label for="%1$s"><strong>%2$s</strong> %5$s</label></p>
				<p><select name="%3$s" id="%1$s">%4$s</select></p>
				<p class=description>%6$s</p>',
				[
					$this->get_field_id( 'max_snippet_length' ),
					esc_html__( 'Maximum text snippet length', 'autodescription' ),
					$this->get_field_name( 'max_snippet_length' ),
					$text_snippet_options,
					$this->make_info(
						__( 'This may limit the text snippet length for all pages on this site.', 'autodescription' ),
						'',
						false
					),
					esc_html__( "This directive also imposes a limit on meta descriptions and structured data, which unintentionally restricts the amount of information you can share. Therefore, it's best to use at least a 320 character limit.", 'autodescription' ),
				]
			),
			true
		);

		$image_preview_options = '';
		$_current              = $this->get_option( 'max_image_preview' );
		$_image_preview_types  = [
			'none'     => _x( 'None, disallow preview', 'quanity: zero', 'autodescription' ),
			'standard' => __( 'Thumbnail or standard size', 'autodescription' ),
			'large'    => __( 'Large or full size', 'autodescription' ),
		];
		foreach ( $_image_preview_types as $_value => $_name ) {
			$image_preview_options .= vsprintf(
				'<option value="%s" %s>%s</option>',
				[
					esc_attr( $_value ),
					selected( $_current, esc_attr( $_value ), false ),
					esc_html( $_name ),
				]
			);
		}
		$this->wrap_fields(
			vsprintf(
				'<p><label for="%1$s"><strong>%2$s</strong> %5$s</label></p>
				<p><select name="%3$s" id="%1$s">%4$s</select></p>
				<p class=description>%6$s</p>',
				[
					$this->get_field_id( 'max_image_preview' ),
					esc_html__( 'Maximum image preview size', 'autodescription' ),
					$this->get_field_name( 'max_image_preview' ),
					$image_preview_options,
					$this->make_info(
						__( 'This may limit the image preview size for all images from this site.', 'autodescription' ),
						'',
						false
					),
					$this->convert_markdown(
						sprintf(
							/* translators: Backticks and hyperlink are Markdown! %s = link to documentation. */
							esc_html__( 'The "None, disallow preview" setting will not be used when `nofollow` or `noarchive` are set for a page. This is to work around unexpected deindexing behavior in Google Search. [Learn more](%s).', 'autodescription' ),
							'https://kb.theseoframework.com/kb/why-is-max-image-preview-none-purged/'
						),
						[ 'code', 'a' ],
						[ 'a_external' => true ]
					),
				]
			),
			true
		);

		$_video_snippet_types['default'] = [
			-1 => __( 'Full video preview', 'autodescription' ),
			0  => _x( 'None, still image only', 'quanity: zero', 'autodescription' ),
		];
		foreach ( range( 1, 600, 1 ) as $_n ) {
			/* translators: %d = number */
			$_video_snippet_types['number'][ $_n ] = sprintf( _n( '%d second', '%d seconds', $_n, 'autodescription' ), $_n );
		}
		$video_preview_options = '';
		$_current             = $this->get_option( 'max_video_preview' );
		foreach ( $_video_snippet_types as $_type => $_values ) {
			$_label = 'default' === $_type
					? __( 'Standard directive', 'autodescription' )
					: __( 'Granular directive', 'autodescription' );

			$_options = '';
			foreach ( $_values as $_value => $_name ) {
				$_options .= vsprintf(
					'<option value="%s" %s>%s</option>',
					[
						esc_attr( $_value ),
						selected( $_current, esc_attr( $_value ), false ),
						esc_html( $_name ),
					]
				);
			}

			$video_preview_options .= sprintf( '<optgroup label="%s">%s</optgroup>', esc_attr( $_label ), $_options );
		}
		$this->wrap_fields(
			vsprintf(
				'<p><label for="%1$s"><strong>%2$s</strong> %5$s</label></p>
				<p><select name="%3$s" id="%1$s">%4$s</select></p>',
				[
					$this->get_field_id( 'max_video_preview' ),
					esc_html__( 'Maximum video preview length', 'autodescription' ),
					$this->get_field_name( 'max_video_preview' ),
					$video_preview_options,
					$this->make_info(
						__( 'This may limit the video preview length for all videos on this site.', 'autodescription' ),
						'',
						false
					),
				]
			),
			true
		);
		break;

	case 'the_seo_framework_robots_metabox_no':
		$ro_value = $robots['value'];
		$ro_i18n  = $robots['desc'];

		/* translators: 1 = noindex/nofollow/noarchive, 2 = Post, Post type, Category archives, the entire site, etc. */
		$apply_x_to_y_i18n = esc_html__( 'Apply %1$s to %2$s?', 'autodescription' );

		$ro_name_wrapped = $this->code_wrap( $ro_value );

		?>
		<h4><?php esc_html_e( 'Robots Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( $ro_i18n );
		?>
		<hr>
		<?php

		$checkboxes = '';
		foreach ( $types as $type => $i18n ) {

			$label = sprintf(
				$apply_x_to_y_i18n,
				$ro_name_wrapped,
				esc_html( $i18n )
			);

			$id = $this->s_field_id( $type . '_' . $ro_value );

			//* Add warning if it's 'site'.
			if ( 'site' === $type ) {
				$checkboxes .= '<hr class="tsf-option-spacer">';

				if ( in_array( $ro_value, [ 'noindex', 'nofollow' ], true ) )
					$checkboxes .= sprintf(
						'<p><span class="description attention">%s</span></p>',
						esc_html__( 'Warning: No public site should ever enable this option.', 'autodescription' )
					);
			}

			$checkboxes .= $this->make_checkbox( $id, $label, '', false );
		}

		$this->wrap_fields( $checkboxes, true );

		?>
		<hr>

		<h4><?php esc_html_e( 'Post Type Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'These settings are applied to the post type pages and their terms. When terms are shared between post types, all their post types should be checked for this to have an effect.', 'autodescription' ) );

		$option_id = $this->get_robots_post_type_option_id( $ro_value );

		if ( in_array( $ro_value, [ 'noindex', 'nofollow' ], true ) )
			$this->attention_description( __( 'Warning: No site should enable these options for Posts and Pages.', 'autodescription' ) );

		// TODO can we assume that there's at least one post type at all times? Can WP be used in this way, albeit headless?
		// Let's assign $boxes, for that matter.
		$boxes = [];

		foreach ( $post_types as $post_type ) {
			$pto = \get_post_type_object( $post_type );
			if ( ! $pto ) continue;

			$boxes[] = $this->make_checkbox_array( [
				'id'       => $option_id,
				'index'    => $post_type,
				'label'    => sprintf(
					// RTL supported: Because the post types are Roman, browsers enforce the order.
					'%s &ndash; <code>%s</code>',
					sprintf( $apply_x_to_y_i18n, $ro_name_wrapped, esc_html( $pto->labels->name ) ),
					esc_html( $post_type )
				),
				'escape'   => false,
				'disabled' => false,
				'default'  => 'noindex' === $ro_value && 'attachment' === $post_type,
				'warned'   => in_array( $ro_value, [ 'noindex', 'nofollow' ], true ) && in_array( $post_type, [ 'page', 'post' ], true ),
			] );
		}

		$this->wrap_fields( $boxes, true );
		break;

	default:
		break;
endswitch;
