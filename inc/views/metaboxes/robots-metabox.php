<?php

defined( 'ABSPATH' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

//* Fetch the required instance within this file.
$instance = $this->get_view_instance( 'the_seo_framework_robots_metabox', $instance );

switch ( $instance ) :
	case 'the_seo_framework_robots_metabox_main' :

		//* Robots types
		$types = array(
			'category' => __( 'Category', 'autodescription' ),
			'tag' => __( 'Tag', 'autodescription' ),
			'author' => __( 'Author', 'autodescription' ),
			'date' => __( 'Date', 'autodescription' ),
			'search' => __( 'Search Pages', 'autodescription' ),
			'attachment' => __( 'Attachment Pages', 'autodescription' ),
			'site' => _x( 'the entire site', '...for the entire site', 'autodescription' ),
		);

		//* Robots i18n
		$robots = array(
			'noindex' => array(
				'value' => 'noindex',
				'name'  => __( 'NoIndex', 'autodescription' ),
				'desc'  => __( 'These options prevent indexing of the selected archives and pages. If you enable this, the selected archives or pages will be removed from Search Engine results pages.', 'autodescription' ),
			),
			'nofollow' => array(
				'value' => 'nofollow',
				'name'  => __( 'NoFollow', 'autodescription' ),
				'desc'  => __( 'These options prevent links from being followed on the selected archives and pages. If you enable this, the selected archives or pages in-page links will gain no SEO value, including your own links.', 'autodescription' ),
			),
			'noarchive' => array(
				'value' => 'noarchive',
				'name'  => __( 'NoArchive', 'autodescription' ),
				'desc'  => __( 'These options prevent caching of the selected archives and pages. If you enable this, search engines will not create a cached copy of the selected archives or pages.', 'autodescription' ),
			),
		);

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
		$default_tabs = array(
			'general' => array(
				'name'     => __( 'General', 'autodescription' ),
				'callback' => array( $this, 'robots_metabox_general_tab' ),
				'dashicon' => 'admin-generic',
				'args'     => '',
			),
			'index' => array(
				'name'     => __( 'Indexing', 'autodescription' ),
				'callback' => array( $this, 'robots_metabox_no_tab' ),
				'dashicon' => 'filter',
				'args'     => array( $types, $robots['noindex'] ),
			),
			'follow' => array(
				'name'     => __( 'Following', 'autodescription' ),
				'callback' => array( $this, 'robots_metabox_no_tab' ),
				'dashicon' => 'editor-unlink',
				'args'     => array( $types, $robots['nofollow'] ),
			),
			'archive' => array(
				'name'     => __( 'Archiving', 'autodescription' ),
				'callback' => array( $this, 'robots_metabox_no_tab' ),
				'dashicon' => 'download',
				'args'     => array( $types, $robots['noarchive'] ),
			),
		);

		/**
		 * Applies filters 'the_seo_framework_robots_settings_tabs' : array see $default_tabs
		 *
		 * Used to extend Social tabs
		 * @since 2.2.4
		 */
		$defaults = (array) apply_filters( 'the_seo_framework_robots_settings_tabs', $default_tabs, $args );

		$tabs = wp_parse_args( $args, $defaults );

		$this->nav_tab_wrapper( 'robots', $tabs, '2.2.4' );
		break;

	case 'the_seo_framework_robots_metabox_general' :

		?>
		<h4><?php esc_html_e( 'Open Directory Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( "Sometimes, search engines use resources from certain Directories to find titles and descriptions for your content. You generally don't want them to. Turn these options on to prevent them from doing so.", 'autodescription' ) );
		$this->description( __( "The Yahoo! Directory may contain outdated SEO values. Therefore, it's best to leave the option checked.", 'autodescription' ) );

		$fields = $this->wrap_fields(
			$this->make_checkbox(
				'noydir',
				sprintf( esc_html__( 'Apply %s to the entire site?', 'autodescription' ), $this->code_wrap( 'noydir' ) ),
				'',
				false
			), true
		);
		?>
		<hr>

		<h4><?php esc_html_e( 'Paginated Archive Settings', 'autodescription' ); ?></h4>
		<p class="description">
			<?php
			printf(
				esc_html__( "Indexing the second or later page of any archive might cause duplication errors. Search engines look down upon them; therefore, it's recommended to disable indexing of those pages.", 'autodescription' ),
				$this->code_wrap( 'noodp' ),
				$this->code_wrap( 'noydir' )
			);
			?>
		</p>
		<?php

		$this->wrap_fields(
			$this->make_checkbox(
				'paged_noindex',
				sprintf( esc_html__( 'Apply %s to every second or later archive page?', 'autodescription' ), $this->code_wrap( 'noindex' ) ),
				'',
				false
			), true
		);
		break;

	case 'the_seo_framework_robots_metabox_no' :

		$ro_value = $robots['value'];
		$ro_name = esc_html( $robots['name'] );
		$ro_i18n = $robots['desc'];

		?><h4><?php printf( esc_html__( '%s Robots Settings', 'autodescription' ), $ro_name ); ?></h4><?php
		$this->description( $ro_i18n );

		$checkboxes = '';
		foreach ( $types as $type => $i18n ) {

			if ( 'site' === $type || 'attachment' === $type || 'search' === $type ) {
				//* Singular.
				/* translators: 1: Option, 2: Post Type */
				$label = sprintf( esc_html__( 'Apply %1$s to %2$s?', 'autodescription' ), $this->code_wrap( $ro_name ), esc_html( $i18n ) );
			} else {
				//* Archive.
				/* translators: 1: Option, 2: Post Type */
				$label = sprintf( esc_html__( 'Apply %1$s to %2$s Archives?', 'autodescription' ), $this->code_wrap( $ro_name ), esc_html( $i18n ) );
			}

			$id = $type . '_' . $ro_value;

			//* Add <hr> if it's 'site'
			$checkboxes .= 'site' === $type ? '<hr class="tsf-option-spacer">' : '';

			$checkboxes .= $this->make_checkbox( esc_html( $id ), $label, '', false );
		}

		?><p class="tsf-fields"><?php
			//* Echo checkboxes.
			$this->wrap_fields( $checkboxes, true );
		?></p><?php
		break;

	default :
		break;
endswitch;
