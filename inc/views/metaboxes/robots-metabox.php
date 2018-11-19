<?php
/**
 * @package The_SEO_Framework\Views\Admin
 * @subpackage The_SEO_Framework\Views\Metaboxes
 */

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

//* Fetch the required instance within this file.
$instance = $this->get_view_instance( 'the_seo_framework_robots_metabox', $instance );

switch ( $instance ) :
	case 'the_seo_framework_robots_metabox_main':
		//* Robots types
		$types = [
			'category'   => __( 'Category', 'autodescription' ),
			'tag'        => __( 'Tag', 'autodescription' ),
			'author'     => __( 'Author', 'autodescription' ),
			'date'       => __( 'Date', 'autodescription' ),
			'search'     => __( 'Search Pages', 'autodescription' ),
			'site'       => _x( 'the entire site', '...for the entire site', 'autodescription' ),
		];

		$post_types = $this->get_rewritable_post_types();

		//* Robots i18n
		$robots = [
			'noindex' => [
				'value' => 'noindex',
				'name'  => __( 'NoIndex', 'autodescription' ),
				'desc'  => __( 'These options most likely prevent indexing of the selected archives and pages. If you enable this, the selected archives or pages will urge to be removed from search engine results pages.', 'autodescription' ),
			],
			'nofollow' => [
				'value' => 'nofollow',
				'name'  => __( 'NoFollow', 'autodescription' ),
				'desc'  => __( 'These options most likely prevent links from being followed on the selected archives and pages. If you enable this, the selected archives or pages in-page links will gain no SEO value, including your own links.', 'autodescription' ),
			],
			'noarchive' => [
				'value' => 'noarchive',
				'name'  => __( 'NoArchive', 'autodescription' ),
				'desc'  => __( 'These options most likely prevent caching of the selected archives and pages. If you enable this, bots are urged not create a cached copy of the selected archives or pages.', 'autodescription' ),
			],
		];

		/**
		 * Parse tabs content.
		 *
		 * @since 2.2.2
		 *
		 * @param array $default_tabs { 'id' = The identifier =>
		 *    array(
		 *       'name'     => The name
		 *       'callback' => function callback
		 *       'dashicon' => WordPress Dashicon
		 *       'args'     => function args
		 *    )
		 * }
		 */
		$default_tabs = [
			'general' => [
				'name'     => __( 'General', 'autodescription' ),
				'callback' => [ $this, 'robots_metabox_general_tab' ],
				'dashicon' => 'admin-generic',
				'args'     => '',
			],
			'index' => [
				'name'     => __( 'Indexing', 'autodescription' ),
				'callback' => [ $this, 'robots_metabox_no_tab' ],
				'dashicon' => 'filter',
				'args'     => [ $types, $post_types, $robots['noindex'] ],
			],
			'follow' => [
				'name'     => __( 'Following', 'autodescription' ),
				'callback' => [ $this, 'robots_metabox_no_tab' ],
				'dashicon' => 'editor-unlink',
				'args'     => [ $types, $post_types, $robots['nofollow'] ],
			],
			'archive' => [
				'name'     => __( 'Archiving', 'autodescription' ),
				'callback' => [ $this, 'robots_metabox_no_tab' ],
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

		$this->nav_tab_wrapper( 'robots', $tabs, '2.2.4' );
		break;

	case 'the_seo_framework_robots_metabox_general':
		?>
		<h4><?php esc_html_e( 'Paginated Archive Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( "Indexing the second or later page of any archive might cause duplication errors. Search engines look down upon them; therefore, it's recommended to disable indexing of those pages.", 'autodescription' ) );

		$this->wrap_fields(
			$this->make_checkbox(
				'paged_noindex',
				/* translators: %s = noindex */
				sprintf( esc_html__( 'Apply %s to every second or later archive page?', 'autodescription' ), $this->code_wrap( 'noindex' ) ),
				'',
				false
			), true
		);
		break;

	case 'the_seo_framework_robots_metabox_no':
		$ro_value = $robots['value'];
		$ro_name  = esc_html( $robots['name'] );
		$ro_i18n  = $robots['desc'];
		$ro_name_wrapped = $this->code_wrap( $ro_name );

		?>
		<h4>
		<?php
			/* translators: %s = Category/Tag/Attachment/Site/Search */
			printf( esc_html__( '%s Robots Settings', 'autodescription' ), $ro_name ); // xss ok
		?>
		</h4>
		<?php
		$this->description( $ro_i18n );
		?>
		<hr>
		<?php

		$checkboxes = '';
		foreach ( $types as $type => $i18n ) {

			if ( 'site' === $type || 'attachment' === $type || 'search' === $type ) {
				//* Singular.
				/* translators: 1: Option, 2: Post Type */
				$label = sprintf( esc_html__( 'Apply %1$s to %2$s?', 'autodescription' ), $ro_name_wrapped, esc_html( $i18n ) );
			} else {
				//* Archive.
				/* translators: 1: Option, 2: Post Type */
				$label = sprintf( esc_html__( 'Apply %1$s to %2$s Archives?', 'autodescription' ), $ro_name_wrapped, esc_html( $i18n ) );
			}

			$id = $this->sanitize_field_id( $type . '_' . $ro_value );

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

		//* Echo checkboxes.
		$this->wrap_fields( $checkboxes, true );

		?>
		<hr>

		<h4><?php esc_html_e( 'Post Type Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'These settings are applied to the post type pages and their terms.', 'autodescription' ) );

		/* translators: 1: noindex/nofollow/noarchive, 2: Post Type label */
		$_label    = esc_html__( 'Apply %1$s to %2$s?', 'autodescription' );
		$option_id = $this->get_robots_post_type_option_id( $ro_value );

		if ( in_array( $ro_value, [ 'noindex', 'nofollow' ], true ) )
			$this->attention_description( __( 'Warning: No site should enable these options for Posts and Pages.', 'autodescription' ) );

		foreach ( $post_types as $post_type ) {
			$pto = \get_post_type_object( $post_type );
			if ( ! $pto ) continue;

			$boxes[] = $this->make_checkbox_array( [
				'id'       => $option_id,
				'index'    => $post_type,
				'label'    => sprintf( $_label, $ro_name_wrapped, esc_html( $pto->labels->name ) ),
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
