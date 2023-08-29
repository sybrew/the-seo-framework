<?php
/**
 * @package The_SEO_Framework\Views\Admin\Metaboxes
 * @subpackage The_SEO_Framework\Admin\Settings
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

use The_SEO_Framework\Bridges\SeoSettings,
	The_SEO_Framework\Interpreters\HTML,
	The_SEO_Framework\Interpreters\Settings_Input as Input;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and tsf()->_verify_include_secret( $_secret ) or die;

switch ( $this->get_view_instance( 'robots', $instance ) ) :
	case 'robots_main':
		$global_types = [
			'author' => [
				'i18n'     => __( 'Author pages', 'autodescription' ),
				'i18ntype' => 'plural',
			],
			'date'   => [
				'i18n'     => __( 'Date archives', 'autodescription' ),
				'i18ntype' => 'plural',
			],
			'search' => [
				'i18n'     => __( 'Search pages', 'autodescription' ),
				'i18ntype' => 'plural',
			],
			// Must be last for proper <hr> styling!
			'site'   => [
				'i18n'     => _x( 'the entire site', '...for the entire site', 'autodescription' ),
				'i18ntype' => 'singular',
			],
		];

		$post_types = $this->get_public_post_types();
		$taxonomies = $this->get_public_taxonomies();

		// Robots i18n
		$robots = [
			'noindex'   => [
				'value' => 'noindex',
				'desc'  => __( 'These options most likely prevent indexing of the selected archives and pages. If you enable this, the selected archives or pages will urge to be removed from search engine results pages.', 'autodescription' ),
			],
			'nofollow'  => [
				'value' => 'nofollow',
				'desc'  => __( 'These options most likely prevent links from being followed on the selected archives and pages. If you enable this, the selected archives or pages in-page links will gain no SEO value, including your internal links.', 'autodescription' ),
			],
			'noarchive' => [
				'value' => 'noarchive',
				'desc'  => __( 'These options most likely prevent caching of the selected archives and pages. If you enable this, bots are urged not create a cached copy of the selected archives or pages.', 'autodescription' ),
			],
		];

		$_settings_class = SeoSettings::class;

		$tabs = [
			'general' => [
				'name'     => __( 'General', 'autodescription' ),
				'callback' => [ $_settings_class, '_robots_metabox_general_tab' ],
				'dashicon' => 'admin-generic',
				'args'     => '',
			],
			'index'   => [
				'name'     => __( 'Indexing', 'autodescription' ),
				'callback' => [ $_settings_class, '_robots_metabox_no_tab' ],
				'dashicon' => 'filter',
				'args'     => [
					'global_types' => $global_types,
					'post_types'   => $post_types,
					'taxonomies'   => $taxonomies,
					'robots'       => $robots['noindex'],
				],
			],
			'follow'  => [
				'name'     => __( 'Following', 'autodescription' ),
				'callback' => [ $_settings_class, '_robots_metabox_no_tab' ],
				'dashicon' => 'editor-unlink',
				'args'     => [
					'global_types' => $global_types,
					'post_types'   => $post_types,
					'taxonomies'   => $taxonomies,
					'robots'       => $robots['nofollow'],
				],
			],
			'archive' => [
				'name'     => __( 'Archiving', 'autodescription' ),
				'callback' => [ $_settings_class, '_robots_metabox_no_tab' ],
				'dashicon' => 'download',
				'args'     => [
					'global_types' => $global_types,
					'post_types'   => $post_types,
					'taxonomies'   => $taxonomies,
					'robots'       => $robots['noarchive'],
				],
			],
		];

		SeoSettings::_nav_tab_wrapper(
			'robots',
			/**
			 * @since 2.2.4
			 * @param array $tabs The default tabs.
			 */
			(array) apply_filters( 'the_seo_framework_robots_settings_tabs', $tabs )
		);
		break;

	case 'robots_general_tab':
		HTML::header_title( __( 'Advanced Query Protection', 'autodescription' ) );
		HTML::description( __( 'Some URL queries can cause WordPress to show faux archives. When search engines spot these, they will crawl and index them, which may cause a drop in ranking. Advanced query protection will prevent robots from indexing these archives.', 'autodescription' ) );

		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'    => 'advanced_query_protection',
				'label' => __( 'Enable advanced query protection?', 'autodescription' ),
			] ),
			true
		);
		?>
		<hr>
		<?php
		HTML::header_title( __( 'Paginated Archive Settings', 'autodescription' ) );
		HTML::description( __( "Indexing the second or later page of any archive might cause duplication errors. Search engines look down upon them; therefore, it's recommended to disable indexing of those pages.", 'autodescription' ) );

		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'     => 'paged_noindex',
				'label'  => $this->convert_markdown(
					/* translators: the backticks are Markdown! Preserve them as-is! */
					esc_html__( 'Apply `noindex` to every second or later archive page?', 'autodescription' ),
					[ 'code' ]
				),
				'escape' => false,
			] ),
			true
		);
		?>
		<hr>
		<?php
		HTML::header_title( __( 'Copyright Directive Settings', 'autodescription' ) );
		HTML::description( __( "Some search engines allow you to control copyright directives on the content they aggregate. It's best to allow some content to be taken by these aggregators, as that can improve contextualized exposure via snippets and previews. When left unspecified, regional regulations may apply. It is up to the aggregator to honor these requests.", 'autodescription' ) );

		HTML::wrap_fields(
			Input::make_checkbox( [
				'id'    => 'set_copyright_directives',
				'label' => __( 'Specify aggregator copyright compliance directives?', 'autodescription' ),
			] ),
			true
		);

		$_text_snippet_types['default'] = [
			-1 => __( 'Unlimited', 'autodescription' ),
			0  => _x( 'None, disallow snippet', 'quantity: zero', 'autodescription' ),
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
		HTML::wrap_fields(
			vsprintf(
				'<p><label for="%1$s"><strong>%2$s</strong> %5$s</label></p>
				<p><select name="%3$s" id="%1$s">%4$s</select></p>
				<p class=description>%6$s</p>',
				[
					Input::get_field_id( 'max_snippet_length' ),
					esc_html__( 'Maximum text snippet length', 'autodescription' ),
					Input::get_field_name( 'max_snippet_length' ),
					$text_snippet_options,
					HTML::make_info(
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
			'none'     => _x( 'None, disallow preview', 'quantity: zero', 'autodescription' ),
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
		HTML::wrap_fields(
			vsprintf(
				'<p><label for="%1$s"><strong>%2$s</strong> %5$s</label></p>
				<p><select name="%3$s" id="%1$s">%4$s</select></p>',
				[
					Input::get_field_id( 'max_image_preview' ),
					esc_html__( 'Maximum image preview size', 'autodescription' ),
					Input::get_field_name( 'max_image_preview' ),
					$image_preview_options,
					HTML::make_info(
						__( 'This may limit the image preview size for all images from this site.', 'autodescription' ),
						'',
						false
					),
				]
			),
			true
		);

		$_video_snippet_types['default'] = [
			-1 => __( 'Full video preview', 'autodescription' ),
			0  => _x( 'None, still image only', 'quantity: zero', 'autodescription' ),
		];
		foreach ( range( 1, 600, 1 ) as $_n ) {
			/* translators: %d = number */
			$_video_snippet_types['number'][ $_n ] = sprintf( _n( '%d second', '%d seconds', $_n, 'autodescription' ), $_n );
		}
		$video_preview_options = '';
		$_current              = $this->get_option( 'max_video_preview' );
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
		HTML::wrap_fields(
			vsprintf(
				'<p><label for="%1$s"><strong>%2$s</strong> %5$s</label></p>
				<p><select name="%3$s" id="%1$s">%4$s</select></p>',
				[
					Input::get_field_id( 'max_video_preview' ),
					esc_html__( 'Maximum video preview length', 'autodescription' ),
					Input::get_field_name( 'max_video_preview' ),
					$video_preview_options,
					HTML::make_info(
						__( 'This may limit the video preview length for all videos on this site.', 'autodescription' ),
						'',
						false
					),
				]
			),
			true
		);
		break;

	case 'robots_no_tab':
		$ro_value = $robots['value'];
		$ro_i18n  = $robots['desc'];

		/* translators: SINGULAR. 1 = noindex/nofollow/noarchive, 2 = The entire site */
		$apply_x_to_y_i18n_singular = esc_html_x( 'Apply %1$s to %2$s?', 'singular', 'autodescription' );
		/* translators: PLURAL. 1 = noindex/nofollow/noarchive, 2 = Archives, Posts, Pages, etc. */
		$apply_x_to_y_i18n_plural = esc_html_x( 'Apply %1$s to %2$s?', 'plural', 'autodescription' );

		$ro_name_wrapped = HTML::code_wrap( $ro_value );

		HTML::header_title( __( 'Robots Settings', 'autodescription' ) );
		HTML::description( $ro_i18n );
		?>
		<hr>
		<?php
		HTML::header_title( __( 'Post Type Settings', 'autodescription' ) );
		HTML::description( __( 'These settings apply to the post type pages and their terms. When terms are shared between post types, all their post types should be checked for this to have an effect.', 'autodescription' ) );

		// When the post OR page post types are available, show this warning.
		if ( in_array( $ro_value, [ 'noindex', 'nofollow' ], true ) && array_intersect( $post_types, [ 'post', 'page' ] ) )
			HTML::attention_description( __( 'Warning: No site should enable these options for Posts and Pages.', 'autodescription' ) );

		$checkboxes = [];

		$pt_option_id = $this->get_robots_post_type_option_id( $ro_value );

		foreach ( $post_types as $post_type ) {
			$checkboxes[] = Input::make_checkbox( [
				'id'     => [ $pt_option_id, $post_type ],
				'class'  => 'tsf-robots-post-types',
				'label'  => sprintf(
					// RTL supported: Because the post types are Roman, browsers enforce the order.
					'%s &ndash; <code>%s</code>',
					sprintf( $apply_x_to_y_i18n_plural, $ro_name_wrapped, esc_html( $this->get_post_type_label( $post_type, false ) ) ),
					esc_html( $post_type )
				),
				'escape' => false,
				'data'   => [
					'robots' => $ro_value,
				],
			] );
		}

		HTML::wrap_fields( $checkboxes, true );

		?>
		<hr>
		<?php
		HTML::header_title( __( 'Taxonomy Settings', 'autodescription' ) );
		HTML::description( __( "These settings apply to the taxonomies of post types. When taxonomies have all their bound post types' options checked, they will inherit their status.", 'autodescription' ) );

		$tax_option_id = $this->get_robots_taxonomy_option_id( $ro_value );

		$checkboxes = [];

		foreach ( $taxonomies as $taxonomy ) {
			$checkboxes[] = Input::make_checkbox( [
				'id'     => [ $tax_option_id, $taxonomy ],
				'class'  => 'tsf-robots-taxonomies',
				'label'  => sprintf(
					// RTL supported: Because the post types are Roman, browsers enforce the order.
					'%s &ndash; <code>%s</code>',
					sprintf( $apply_x_to_y_i18n_plural, $ro_name_wrapped, esc_html( $this->get_tax_type_label( $taxonomy, false ) ) ),
					esc_html( $taxonomy )
				),
				'escape' => false,
				'data'   => [
					'postTypes' => $this->get_post_types_from_taxonomy( $taxonomy ),
					'robots'    => $ro_value,
				],
			] );
		}

		// TODO can we assume that there's at least one taxonomy at all times? Can WP be used in this way, albeit headless?
		HTML::wrap_fields( $checkboxes, true );

		?>
		<hr>
		<?php
		HTML::header_title( __( 'Global Settings', 'autodescription' ) );
		HTML::description( __( 'These settings apply to other globally registered content types.', 'autodescription' ) );

		$checkboxes = '';
		foreach ( $global_types as $type => $data ) {

			$label = sprintf(
				'singular' === $data['i18ntype'] ? $apply_x_to_y_i18n_singular : $apply_x_to_y_i18n_plural,
				$ro_name_wrapped,
				esc_html( $data['i18n'] )
			);

			// Legacy.
			$id = $this->s_field_id( "{$type}_{$ro_value}" );

			// Add warning if it's 'site'.
			if ( 'site' === $type ) {
				$checkboxes .= '<hr class=tsf-option-spacer>';

				if ( in_array( $ro_value, [ 'noindex', 'nofollow' ], true ) )
					$checkboxes .= sprintf(
						'<p><span class="description attention">%s</span></p>',
						esc_html__( 'Warning: No public site should ever enable this option.', 'autodescription' )
					);
			}

			$checkboxes .= Input::make_checkbox( [
				'id'     => $id,
				'class'  => 'site' === $type ? 'tsf-robots-site' : 'tsf-robots-globals',
				'label'  => $label,
				'escape' => false,
				'data'   => [
					'robots' => $ro_value,
				],
			] );
		}

		HTML::wrap_fields( $checkboxes, true );
endswitch;
