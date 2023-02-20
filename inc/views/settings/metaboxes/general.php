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

switch ( $this->get_view_instance( 'general', $instance ) ) :
	case 'general_main':
		$_settings_class = SeoSettings::class;

		$tabs = [
			'layout'      => [
				'name'     => __( 'Layout', 'autodescription' ),
				'callback' => [ $_settings_class, '_general_metabox_layout_tab' ],
				'dashicon' => 'screenoptions',
			],
			'performance' => [
				'name'     => __( 'Performance', 'autodescription' ),
				'callback' => [ $_settings_class, '_general_metabox_performance_tab' ],
				'dashicon' => 'performance',
			],
			'canonical'   => [
				'name'     => __( 'Canonical', 'autodescription' ),
				'callback' => [ $_settings_class, '_general_metabox_canonical_tab' ],
				'dashicon' => 'external',
			],
			'timestamps'  => [
				'name'     => __( 'Timestamps', 'autodescription' ),
				'callback' => [ $_settings_class, '_general_metabox_timestamps_tab' ],
				'dashicon' => 'clock',
			],
			'exclusions'  => [
				'name'     => __( 'Exclusions', 'autodescription' ),
				'callback' => [ $_settings_class, '_general_metabox_exclusions_tab' ],
				'dashicon' => 'editor-unlink',
			],
		];

		SeoSettings::_nav_tab_wrapper(
			'general',
			/**
			 * @since 2.8.0
			 * @param array $tabs The default tabs.
			 */
			(array) apply_filters( 'the_seo_framework_general_settings_tabs', $tabs )
		);
		break;

	case 'general_layout_tab':
		HTML::header_title( __( 'Administrative Layout Settings', 'autodescription' ) );
		HTML::description( __( 'SEO hints can be visually displayed throughout the dashboard.', 'autodescription' ) );

		?>
		<hr>
		<?php
		HTML::header_title( __( 'SEO Bar Settings', 'autodescription' ) );
		HTML::wrap_fields(
			[
				Input::make_checkbox( [
					'id'     => 'display_seo_bar_tables',
					'label'  => esc_html__( 'Display the SEO Bar in overview tables?', 'autodescription' ),
					'escape' => false,
				] ),
				Input::make_checkbox( [
					'id'     => 'display_seo_bar_metabox',
					'label'  => esc_html__( 'Display the SEO Bar in the SEO Settings meta box?', 'autodescription' ),
					'escape' => false,
				] ),
				Input::make_checkbox( [
					'id'     => 'seo_bar_symbols',
					'label'  => esc_html__( 'Use symbols for warnings?', 'autodescription' ) . ' ' . HTML::make_info(
						__( 'If you have difficulty discerning colors, this may help you spot issues more easily.', 'autodescription' ),
						'',
						false
					),
					'escape' => false,
				] ),
			],
			true
		);

		?>
		<hr>
		<?php
		HTML::header_title( __( 'Counter Settings', 'autodescription' ) );

		$pixel_info = HTML::make_info(
			__( 'The pixel counter computes whether the input will fit on search engine result pages.', 'autodescription' ),
			'https://kb.theseoframework.com/?p=48',
			false
		);

		$character_info = HTML::make_info(
			__( 'The character counter is based on guidelines.', 'autodescription' ),
			'',
			false
		);

		HTML::wrap_fields(
			[
				Input::make_checkbox( [
					'id'     => 'display_pixel_counter',
					'label'  => esc_html__( 'Display pixel counters?', 'autodescription' ) . " $pixel_info",
					'escape' => false,
				] ),
				Input::make_checkbox( [
					'id'     => 'display_character_counter',
					'label'  => esc_html__( 'Display character counters?', 'autodescription' ) . " $character_info",
					'escape' => false,
				] ),
			],
			true
		);
		break;

	case 'general_performance_tab':
		HTML::header_title( __( 'Performance Settings', 'autodescription' ) );
		HTML::description( __( "Depending on your server's configuration, adjusting these settings can affect performance.", 'autodescription' ) );

		?>
		<hr>
		<?php
		HTML::header_title( __( 'Query Alteration Settings', 'autodescription' ) );
		HTML::description_noesc(
			esc_html__( "Altering the query allows for more control of the site's hierarchy.", 'autodescription' )
			. '<br>' .
			esc_html__( 'If your website has thousands of pages, these options can greatly affect database performance.', 'autodescription' )
		);

		HTML::description_noesc(
			esc_html__( 'Altering the query in the database is more accurate, but can increase database query time.', 'autodescription' )
			. '<br>' .
			esc_html__( 'Altering the query on the site is much faster, but can lead to inconsistent pagination. It can also lead to 404 error messages if all queried pages have been excluded.', 'autodescription' )
		);

		$query_types = (array) apply_filters(
			'the_seo_framework_query_alteration_types',
			[
				'in_query'   => _x( 'In the database', 'Perform query alteration: In the database', 'autodescription' ),
				'post_query' => _x( 'On the site', 'Perform query alteration: On the site', 'autodescription' ),
			]
		);

		$search_query_select_options = '';
		$_current                    = $this->get_option( 'alter_search_query_type' );
		foreach ( $query_types as $value => $name ) {
			$search_query_select_options .= vsprintf(
				'<option value="%s" %s>%s</option>',
				[
					esc_attr( $value ),
					selected( $_current, esc_attr( $value ), false ),
					esc_html( $name ),
				]
			);
		}

		$archive_query_select_options = '';
		$_current                     = $this->get_option( 'alter_archive_query_type' );
		foreach ( $query_types as $value => $name ) {
			$archive_query_select_options .= vsprintf(
				'<option value="%s" %s>%s</option>',
				[
					esc_attr( $value ),
					selected( $_current, esc_attr( $value ), false ),
					esc_html( $name ),
				]
			);
		}

		$perform_alteration_i18n = esc_html__( 'Perform alteration:', 'autodescription' );

		$search_query_select_field = vsprintf(
			'<label for="%1$s">%2$s</label>
			<select name="%3$s" id="%1$s">%4$s</select>',
			[
				Input::get_field_id( 'alter_search_query_type' ),
				$perform_alteration_i18n,
				Input::get_field_name( 'alter_search_query_type' ),
				$search_query_select_options,
			]
		);

		$archive_query_select_field = vsprintf(
			'<label for="%1$s">%2$s</label>
			<select name="%3$s" id="%1$s">%4$s</select>',
			[
				Input::get_field_id( 'alter_archive_query_type' ),
				$perform_alteration_i18n,
				Input::get_field_name( 'alter_archive_query_type' ),
				$archive_query_select_options,
			]
		);

		HTML::wrap_fields(
			[
				Input::make_checkbox( [
					'id'     => 'alter_search_query',
					'label'  => esc_html__( 'Enable search query alteration?', 'autodescription' )
						. ' ' . HTML::make_info( __( 'This allows you to exclude pages from on-site search results.', 'autodescription' ), '', false ),
					'escape' => false,
				] ),
				$search_query_select_field,
			],
			true
		);

		HTML::wrap_fields(
			[
				Input::make_checkbox( [
					'id'     => 'alter_archive_query',
					'label'  => esc_html__( 'Enable archive query alteration?', 'autodescription' )
						. ' ' . HTML::make_info( __( 'This allows you to exclude pages from on-site archive listings.', 'autodescription' ), '', false ),
					'escape' => false,
				] ),
				$archive_query_select_field,
			],
			true
		);
		break;

	case 'general_canonical_tab':
		HTML::header_title( __( 'Canonical URL Settings', 'autodescription' ) );
		HTML::description( __( 'The canonical URL meta tag urges search engines to go to the outputted URL.', 'autodescription' ) );
		HTML::description( __( 'If the canonical URL meta tag represents the visited page, then the search engine will crawl the visited page. Otherwise, the search engine may go to the outputted URL.', 'autodescription' ) );
		?>
		<hr>
		<?php
		HTML::header_title( __( 'Scheme Settings', 'autodescription' ) );
		HTML::description( __( 'If your website is accessible via both HTTP as HTTPS, you may want to set this to HTTPS if not detected automatically. Secure connections are preferred by search engines.', 'autodescription' ) );
		?>
		<label for="<?php Input::field_id( 'canonical_scheme' ); ?>"><?= esc_html_x( 'Preferred canonical URL scheme:', '= Detect Automatically, HTTPS, HTTP', 'autodescription' ) ?></label>
		<select name="<?php Input::field_name( 'canonical_scheme' ); ?>" id="<?php Input::field_id( 'canonical_scheme' ); ?>">
			<?php
			$scheme_types = (array) apply_filters(
				'the_seo_framework_canonical_scheme_types',
				[
					'automatic' => sprintf(
						/* translators: %s = HTTP or HTTPS */
						__( 'Detect automatically (%s)', 'autodescription' ),
						strtoupper( $this->detect_site_url_scheme() )
					),
					'http'      => 'HTTP',
					'https'     => 'HTTPS',
				]
			);
			$_current     = $this->get_option( 'canonical_scheme' );
			foreach ( $scheme_types as $value => $name )
				vprintf(
					'<option value="%s" %s>%s</option>',
					[
						esc_attr( $value ),
						selected( $_current, esc_attr( $value ), false ),
						esc_html( $name ),
					]
				);
			?>
		</select>

		<hr>
		<?php
		HTML::header_title( __( 'Link Relationship Settings', 'autodescription' ) );
		HTML::description( __( 'Some search engines look for relations between the content of your pages. If you have pagination on a post or page, or have archives indexed, these options will help search engines look for the right page to display in the search results.', 'autodescription' ) );
		HTML::description( __( "It's recommended to turn these options on for better SEO consistency and to prevent duplicated content issues.", 'autodescription' ) );

		$prev_next_posts_checkbox = Input::make_checkbox( [
			'id'     => 'prev_next_posts',
			'label'  => $this->convert_markdown(
				/* translators: the backticks are Markdown! Preserve them as-is! */
				esc_html__( 'Add `rel` link tags to posts and pages?', 'autodescription' ),
				[ 'code' ]
			),
			'escape' => false,
		] );

		$prev_next_archives_checkbox = Input::make_checkbox( [
			'id'     => 'prev_next_archives',
			'label'  => $this->convert_markdown(
				/* translators: the backticks are Markdown! Preserve them as-is! */
				esc_html__( 'Add `rel` link tags to archives?', 'autodescription' ),
				[ 'code' ]
			),
			'escape' => false,
		] );

		$prev_next_frontpage_checkbox = Input::make_checkbox( [
			'id'     => 'prev_next_frontpage',
			'label'  => $this->convert_markdown(
				/* translators: the backticks are Markdown! Preserve them as-is! */
				esc_html__( 'Add `rel` link tags to the homepage?', 'autodescription' ),
				[ 'code' ]
			),
			'escape' => false,
		] );

		HTML::wrap_fields( $prev_next_posts_checkbox . $prev_next_archives_checkbox . $prev_next_frontpage_checkbox, true );
		break;

	case 'general_timestamps_tab':
		$timestamp_0 = gmdate( $this->get_timestamp_format( false ) );
		$timestamp_1 = gmdate( $this->get_timestamp_format( true ) );

		HTML::header_title( __( 'Timestamp Settings', 'autodescription' ) );
		HTML::description( __( 'Timestamps help indicate when a page has been published and modified.', 'autodescription' ) );
		?>
		<hr>

		<fieldset>
			<legend><?php HTML::header_title( __( 'Timestamp Format Settings', 'autodescription' ) ); ?></legend>
			<?php HTML::description( __( 'This setting determines how specific the timestamp is.', 'autodescription' ) ); ?>

			<p id=sitemaps-timestamp-format class=tsf-fields>
				<span class=tsf-toblock>
					<input type=radio name="<?php Input::field_name( 'timestamps_format' ); ?>" id="<?php Input::field_id( 'timestamps_format_0' ); ?>" value=0 <?php checked( $this->get_option( 'timestamps_format' ), '0' ); ?> />
					<label for="<?php Input::field_id( 'timestamps_format_0' ); ?>">
						<?php
						// phpcs:ignore, WordPress.Security.EscapeOutput -- code_wrap escapes.
						echo HTML::code_wrap( $timestamp_0 ), ' ', HTML::make_info(
							__( 'This outputs the complete date.', 'autodescription' )
						);
						?>
					</label>
				</span>
				<span class=tsf-toblock>
					<input type=radio name="<?php Input::field_name( 'timestamps_format' ); ?>" id="<?php Input::field_id( 'timestamps_format_1' ); ?>" value=1 <?php checked( $this->get_option( 'timestamps_format' ), '1' ); ?> />
					<label for="<?php Input::field_id( 'timestamps_format_1' ); ?>">
						<?php
						// phpcs:ignore, WordPress.Security.EscapeOutput -- code_wrap escapes.
						echo HTML::code_wrap( $timestamp_1 ), ' ', HTML::make_info(
							__( 'This outputs the complete date including hours, minutes, and timezone.', 'autodescription' )
						);
						?>
					</label>
				</span>
			</p>
		</fieldset>
		<?php
		break;

	case 'general_exclusions_tab':
		HTML::header_title( __( 'Exclusion Settings', 'autodescription' ) );
		HTML::description( __( 'When checked, these options will remove meta optimizations, SEO suggestions, and sitemap inclusions for the selected post types and taxonomies. This will allow search engines to crawl the post type and taxonomies without advanced restrictions or directions.', 'autodescription' ) );
		HTML::attention_description_noesc(
			$this->convert_markdown(
				/* translators: backticks are code wraps. Markdown! */
				esc_html__( "These options should not need changing when post types and taxonomies are registered correctly. When they aren't, consider applying `noindex` to purge them from search engines, instead.", 'autodescription' ),
				[ 'code' ]
			)
		);
		HTML::description( __( 'Default post types and taxonomies can not be excluded.', 'autodescription' ) );
		?>

		<hr>
		<?php
		HTML::header_title( __( 'Post Type Exclusions', 'autodescription' ) );
		HTML::description( __( 'Select post types which should be excluded.', 'autodescription' ) );
		HTML::description( __( 'These settings apply to the post type pages and their terms. When terms are shared between post types, all their post types should be checked for this to have an effect.', 'autodescription' ) );

		$forced_pt = $this->get_forced_supported_post_types();
		$boxes     = [];

		foreach ( $this->get_public_post_types() as $post_type ) {
			$_label = $this->get_post_type_label( $post_type, false );
			if ( ! strlen( $_label ) ) continue;

			$_label = sprintf(
				'%s &ndash; <code>%s</code>',
				esc_html( $_label ),
				esc_html( $post_type )
			);

			$boxes[] = Input::make_checkbox( [
				'id'       => [ 'disabled_post_types', $post_type ],
				'class'    => 'tsf-excluded-post-types',
				'label'    => $_label,
				'escape'   => false,
				'disabled' => in_array( $post_type, $forced_pt, true ),
			] );
		}

		HTML::wrap_fields( $boxes, true );

		?>
		<hr>
		<?php
		HTML::header_title( __( 'Taxonomy Exclusions', 'autodescription' ) );
		HTML::description( __( 'Select taxonomies which should be excluded.', 'autodescription' ) );
		HTML::description( __( 'When taxonomies have all their bound post types excluded, they will inherit their exclusion status.', 'autodescription' ) );

		$forced_tax = $this->get_forced_supported_taxonomies();
		$boxes      = [];

		foreach ( $this->get_public_taxonomies() as $taxonomy ) {
			$_label = $this->get_tax_type_label( $taxonomy, false );
			if ( ! strlen( $_label ) ) continue;

			$_label = sprintf(
				'%s &ndash; <code>%s</code>',
				esc_html( $_label ),
				esc_html( $taxonomy )
			);

			$boxes[] = Input::make_checkbox( [
				'id'       => [ 'disabled_taxonomies', $taxonomy ], // disabled_taxonomies is the option name.
				'class'    => 'tsf-excluded-taxonomies',
				'label'    => $_label,
				'escape'   => false,
				'disabled' => in_array( $taxonomy, $forced_tax, true ),
				'data'     => [
					'postTypes' => $this->get_post_types_from_taxonomy( $taxonomy ),
				],
			] );
		}

		HTML::wrap_fields( $boxes, true );
		break;

	default:
		break;
endswitch;
