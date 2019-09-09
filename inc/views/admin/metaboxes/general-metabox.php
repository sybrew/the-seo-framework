<?php
/**
 * @package The_SEO_Framework\Views\Admin\Metaboxes
 * @subpackage The_SEO_Framework\Admin\Settings
 */

use The_SEO_Framework\Bridges\SeoSettings;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

//* Fetch the required instance within this file.
$instance = $this->get_view_instance( 'the_seo_framework_general_metabox', $instance );

switch ( $instance ) :
	case 'the_seo_framework_general_metabox_main':
		$default_tabs = [
			'layout'      => [
				'name'     => __( 'Layout', 'autodescription' ),
				'callback' => SeoSettings::class . '::_general_metabox_layout_tab',
				'dashicon' => 'screenoptions',
			],
			'performance' => [
				'name'     => __( 'Performance', 'autodescription' ),
				'callback' => SeoSettings::class . '::_general_metabox_performance_tab',
				'dashicon' => 'performance',
			],
			'canonical'   => [
				'name'     => __( 'Canonical', 'autodescription' ),
				'callback' => SeoSettings::class . '::_general_metabox_canonical_tab',
				'dashicon' => 'external',
			],
			'timestamps'  => [
				'name'     => __( 'Timestamps', 'autodescription' ),
				'callback' => SeoSettings::class . '::_general_metabox_timestamps_tab',
				'dashicon' => 'clock',
			],
			'posttypes'   => [
				'name'     => __( 'Post Types', 'autodescription' ),
				'callback' => SeoSettings::class . '::_general_metabox_posttypes_tab',
				'dashicon' => 'index-card',
			],
		];

		/**
		 * @since 2.8.0
		 * @param array $defaults The default tabs.
		 * @param array $args     The args added on the callback.
		 */
		$defaults = (array) apply_filters( 'the_seo_framework_general_settings_tabs', $default_tabs, $args );

		$tabs = wp_parse_args( $args, $defaults );

		SeoSettings::_nav_tab_wrapper( 'general', $tabs );
		break;

	case 'the_seo_framework_general_metabox_layout':
		?>
		<h4><?php esc_html_e( 'Administrative Layout Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'SEO hints can be visually displayed throughout the dashboard.', 'autodescription' ) );

		?>
		<hr>

		<h4><?php esc_html_e( 'SEO Bar Settings', 'autodescription' ); ?></h4>
		<?php
		$this->wrap_fields(
			[
				$this->make_checkbox(
					'display_seo_bar_tables',
					esc_html__( 'Display the SEO Bar in overview tables?', 'autodescription' ),
					'',
					false
				),
				$this->make_checkbox(
					'display_seo_bar_metabox',
					esc_html__( 'Display the SEO Bar in the SEO Settings metabox?', 'autodescription' ),
					'',
					false
				),
				$this->make_checkbox(
					'seo_bar_symbols',
					esc_html__( 'Use symbols for warnings?', 'autodescription' ) . ' ' . $this->make_info(
						__( 'If you have difficulty discerning colors, this may help you spot issues more easily.', 'autodescription' ),
						'',
						false
					),
					'',
					false
				),
			],
			true
		);

		?>
		<hr>

		<h4><?php esc_html_e( 'Counter Settings', 'autodescription' ); ?></h4>
		<?php

		$pixel_info = $this->make_info(
			__( 'The pixel counter computes whether the input will fit on search engine result pages.', 'autodescription' ),
			'',
			false
		);

		$character_info = $this->make_info(
			__( 'The character counter is based on guidelines.', 'autodescription' ),
			'',
			false
		);

		$this->wrap_fields(
			[
				$this->make_checkbox(
					'display_pixel_counter',
					esc_html__( 'Display pixel counters?', 'autodescription' ) . ' ' . $pixel_info,
					'',
					false
				),
				$this->make_checkbox(
					'display_character_counter',
					esc_html__( 'Display character counters?', 'autodescription' ) . ' ' . $character_info,
					'',
					false
				),
			],
			true
		);
		break;

	case 'the_seo_framework_general_metabox_performance':
		?>
		<h4><?php esc_html_e( 'Performance Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( "Depending on your server's configuration, adjusting these settings can affect performance.", 'autodescription' ) );

		?>
		<hr>

		<h4><?php esc_html_e( 'Query Alteration Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description_noesc(
			esc_html__( "Altering the query allows for more control of the site's hierarchy.", 'autodescription' )
			. '<br>' .
			esc_html__( 'If your website has thousands of pages, these options can greatly affect database performance.', 'autodescription' )
		);

		$this->description_noesc(
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
				$this->get_field_id( 'alter_search_query_type' ),
				$perform_alteration_i18n,
				$this->get_field_name( 'alter_search_query_type' ),
				$search_query_select_options,
			]
		);

		$archive_query_select_field = vsprintf(
			'<label for="%1$s">%2$s</label>
			<select name="%3$s" id="%1$s">%4$s</select>',
			[
				$this->get_field_id( 'alter_archive_query_type' ),
				$perform_alteration_i18n,
				$this->get_field_name( 'alter_archive_query_type' ),
				$archive_query_select_options,
			]
		);

		$this->wrap_fields(
			[
				$this->make_checkbox(
					'alter_search_query',
					esc_html__( 'Enable search query alteration?', 'autodescription' )
					. ' ' . $this->make_info( __( 'This allows you to exclude pages from on-site search results.', 'autodescription' ), '', false ),
					'',
					false
				),
				$search_query_select_field,
			],
			true
		);

		$this->wrap_fields(
			[
				$this->make_checkbox(
					'alter_archive_query',
					esc_html__( 'Enable archive query alteration?', 'autodescription' )
					. ' ' . $this->make_info( __( 'This allows you to exclude pages from on-site archive listings.', 'autodescription' ), '', false ),
					'',
					false
				),
				$archive_query_select_field,
			],
			true
		);
		?>
		<hr>

		<h4><?php esc_html_e( 'Transient Cache Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'To improve performance, generated SEO output can be stored in the database as transient cache.', 'autodescription' ) );
		$this->description( __( 'If your website has thousands of pages, or if other forms of caching are used, you might wish to adjust these options.', 'autodescription' ) );

		$this->wrap_fields(
			[
				$this->make_checkbox(
					'cache_meta_schema',
					esc_html__( 'Enable automated Schema.org output cache?', 'autodescription' )
					. ' ' . $this->make_info( __( 'Schema.org output generally makes multiple calls to the database.', 'autodescription' ), '', false ),
					'',
					false
				),
				$this->make_checkbox(
					'cache_sitemap',
					esc_html__( 'Enable sitemap generation cache?', 'autodescription' )
					. ' ' . $this->make_info( __( 'Generating the sitemap can use a lot of server resources.', 'autodescription' ), '', false ),
					'',
					false
				),
			],
			true
		);

		if ( wp_using_ext_object_cache() ) :
			?>
			<hr>

			<h4><?php esc_html_e( 'Object Cache Settings', 'autodescription' ); ?></h4>
			<?php

			$this->wrap_fields(
				$this->make_checkbox(
					'cache_object',
					esc_html__( 'Enable object cache?', 'autodescription' )
					. ' ' . $this->make_info( __( 'Object cache generally works faster than transient cache.', 'autodescription' ), '', false ),
					esc_html__( 'An object cache handler has been detected. If you enable this option, you may wish to disable the Schema.org transient caching.', 'autodescription' ),
					false
				),
				true
			);
		endif;
		break;

	case 'the_seo_framework_general_metabox_canonical':
		?>
		<h4><?php esc_html_e( 'Canonical URL Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'The canonical URL meta tag urges search engines to go to the outputted URL.', 'autodescription' ) );
		$this->description( __( 'If the canonical URL meta tag represents the visited page, then the search engine will crawl the visited page. Otherwise, the search engine may go to the outputted URL.', 'autodescription' ) );
		?>
		<hr>

		<h4><?php esc_html_e( 'Scheme Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'If your website is accessible via both HTTP as HTTPS, you may want to set this to HTTPS if not detected automatically. Secure connections are preferred by search engines.', 'autodescription' ) );
		?>
		<label for="<?php $this->field_id( 'canonical_scheme' ); ?>"><?php echo esc_html_x( 'Preferred canonical URL scheme:', '= Detect Automatically, HTTPS, HTTP', 'autodescription' ); ?></label>
		<select name="<?php $this->field_name( 'canonical_scheme' ); ?>" id="<?php $this->field_id( 'canonical_scheme' ); ?>">
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
			foreach ( $scheme_types as $value => $name )
				echo '<option value="' . esc_attr( $value ) . '"' . selected( $this->get_option( 'canonical_scheme' ), esc_attr( $value ), false ) . '>' . esc_html( $name ) . '</option>' . "\n";
			?>
		</select>

		<hr>

		<h4><?php esc_html_e( 'Link Relationship Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'Some search engines look for relations between the content of your pages. If you have pagination on a post or page, or have archives indexed, these options will help search engines look for the right page to display in the search results.', 'autodescription' ) );
		$this->description( __( "It's recommended to turn these options on for better SEO consistency and to prevent duplicated content issues.", 'autodescription' ) );

		$prev_next_posts_checkbox = $this->make_checkbox(
			'prev_next_posts',
			$this->convert_markdown(
				/* translators: the backticks are Markdown! Preserve them as-is! */
				\esc_html__( 'Add `rel` link tags to posts and pages?', 'autodescription' ),
				[ 'code' ]
			),
			'',
			false
		);

		$prev_next_archives_checkbox = $this->make_checkbox(
			'prev_next_archives',
			$this->convert_markdown(
				/* translators: the backticks are Markdown! Preserve them as-is! */
				\esc_html__( 'Add `rel` link tags to archives?', 'autodescription' ),
				[ 'code' ]
			),
			'',
			false
		);

		$prev_next_frontpage_checkbox = $this->make_checkbox(
			'prev_next_frontpage',
			$this->convert_markdown(
				/* translators: the backticks are Markdown! Preserve them as-is! */
				\esc_html__( 'Add `rel` link tags to the homepage?', 'autodescription' ),
				[ 'code' ]
			),
			'',
			false
		);

		$this->wrap_fields( $prev_next_posts_checkbox . $prev_next_archives_checkbox . $prev_next_frontpage_checkbox, true );
		break;


	case 'the_seo_framework_general_metabox_timestamps':
		//* Sets timezone according to WordPress settings.
		$this->set_timezone();

		$timestamp_0 = date( 'Y-m-d' );

		/**
		 * @link https://www.w3.org/TR/NOTE-datetime
		 * We use the second expression of the time zone offset handling.
		 */
		$timestamp_1 = date( 'Y-m-d\TH:iP' );

		//* Reset timezone to previous value.
		$this->reset_timezone();

		?>
		<h4><?php esc_html_e( 'Timestamp Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'Timestamps help indicate when a page has been published and modified.', 'autodescription' ) );
		?>
		<hr>

		<fieldset>
			<legend>
				<h4><?php esc_html_e( 'Timestamp Format Settings', 'autodescription' ); ?></h4>
				<?php $this->description( __( 'This setting determines how specific the timestamp is.', 'autodescription' ) ); ?>
			</legend>

			<p id="sitemaps-timestamp-format" class="tsf-fields">
				<span class="tsf-toblock">
					<input type="radio" name="<?php $this->field_name( 'timestamps_format' ); ?>" id="<?php $this->field_id( 'timestamps_format_0' ); ?>" value="0" <?php checked( $this->get_option( 'timestamps_format' ), '0' ); ?> />
					<label for="<?php $this->field_id( 'timestamps_format_0' ); ?>">
						<?php
						// phpcs:ignore, WordPress.Security.EscapeOutput -- code_wrap escapes.
						echo $this->code_wrap( $timestamp_0 );
						echo ' ';
						$this->make_info(
							__( 'This outputs the complete date.', 'autodescription' )
						);
						?>
					</label>
				</span>
				<span class="tsf-toblock">
					<input type="radio" name="<?php $this->field_name( 'timestamps_format' ); ?>" id="<?php $this->field_id( 'timestamps_format_1' ); ?>" value="1" <?php checked( $this->get_option( 'timestamps_format' ), '1' ); ?> />
					<label for="<?php $this->field_id( 'timestamps_format_1' ); ?>">
						<?php
						// phpcs:ignore, WordPress.Security.EscapeOutput -- code_wrap escapes.
						echo $this->code_wrap( $timestamp_1 );
						echo ' ';
						$this->make_info(
							__( 'This outputs the complete date including hours, minutes, and timezone.', 'autodescription' )
						);
						?>
					</label>
				</span>
			</p>
		</fieldset>
		<?php
		break;

	case 'the_seo_framework_general_metabox_posttypes':
		?>
		<h4><?php esc_html_e( 'Post Type Settings', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'Post types are special content types. These options should not need changing when post types are registered correctly.', 'autodescription' ) );
		?>

		<hr>

		<h4><?php esc_html_e( 'Disable SEO', 'autodescription' ); ?></h4>
		<?php
		$this->description( __( 'Select post types which should not receive any SEO optimization whatsoever. This will remove meta optimizations, SEO suggestions, and sitemap inclusions for the selected post types.', 'autodescription' ) );
		$this->description( __( 'These settings are applied to the post type pages and their terms. When terms are shared between post types, all their post types should be checked for this to have an effect.', 'autodescription' ) );
		$this->description( __( 'Default post types can not be disabled.', 'autodescription' ) );

		$forced_pt = $this->get_forced_supported_post_types();
		$boxes     = [];

		foreach ( $this->get_rewritable_post_types() as $post_type ) {
			$pto = get_post_type_object( $post_type );
			if ( ! isset( $pto->labels->name ) ) continue;

			$_label = sprintf(
				'%s &ndash; <code>%s</code>',
				esc_html( $pto->labels->name ),
				esc_html( $post_type )
			);

			$boxes[] = $this->make_checkbox_array( [
				'id'       => 'disabled_post_types',
				'index'    => $post_type,
				'label'    => $_label,
				'escape'   => false,
				'disabled' => in_array( $post_type, $forced_pt, true ),
			] );
		}

		$this->wrap_fields( $boxes, true );
		break;

	default:
		break;
endswitch;
