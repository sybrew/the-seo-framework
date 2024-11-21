<?php
/**
 * @package The_SEO_Framework\Views\Admin\Metaboxes
 * @subpackage The_SEO_Framework\Admin\Settings
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and Helper\Template::verify_secret( $secret ) or die;

use \The_SEO_Framework\Admin\Settings\Layout\{
	HTML,
	Input,
};
use \The_SEO_Framework\Helper\{
	Format\Markdown,
	Post_Type,
	Query,
	Taxonomy,
};

// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * The SEO Framework plugin
 * Copyright (C) 2016 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

// See _general_metabox et al.
[ $instance ] = $view_args;

switch ( $instance ) :
	case 'main':
		$_settings_class = Admin\Settings\Plugin::class;

		$tabs = [
			'layout'      => [
				'name'     => \__( 'Layout', 'autodescription' ),
				'callback' => [ $_settings_class, '_general_metabox_layout_tab' ],
				'dashicon' => 'screenoptions',
			],
			'performance' => [
				'name'     => \__( 'Performance', 'autodescription' ),
				'callback' => [ $_settings_class, '_general_metabox_performance_tab' ],
				'dashicon' => 'performance',
			],
			'canonical'   => [
				'name'     => \__( 'Canonical', 'autodescription' ),
				'callback' => [ $_settings_class, '_general_metabox_canonical_tab' ],
				'dashicon' => 'external',
			],
			'timestamps'  => [
				'name'     => \__( 'Timestamps', 'autodescription' ),
				'callback' => [ $_settings_class, '_general_metabox_timestamps_tab' ],
				'dashicon' => 'clock',
			],
			'exclusions'  => [
				'name'     => \__( 'Exclusions', 'autodescription' ),
				'callback' => [ $_settings_class, '_general_metabox_exclusions_tab' ],
				'dashicon' => 'editor-unlink',
			],
		];

		Admin\Settings\Plugin::nav_tab_wrapper(
			'general',
			/**
			 * @since 2.8.0
			 * @param array $tabs The default tabs.
			 */
			(array) \apply_filters( 'the_seo_framework_general_settings_tabs', $tabs )
		);
		break;

	case 'layout':
		HTML::header_title( \__( 'Administrative Layout Settings', 'autodescription' ) );
		HTML::description( \__( 'SEO hints can be visually displayed throughout the dashboard.', 'autodescription' ) );

		?>
		<hr>
		<?php
		HTML::header_title( \__( 'SEO Bar Settings', 'autodescription' ) );
		HTML::wrap_fields(
			[
				Input::make_checkbox( [
					'id'     => 'display_seo_bar_tables',
					'label'  => \esc_html__( 'Display the SEO Bar in overview tables?', 'autodescription' ),
					'escape' => false,
				] ),
				Input::make_checkbox( [
					'id'     => 'display_seo_bar_metabox',
					'label'  => \esc_html__( 'Display the SEO Bar in the SEO Settings meta box?', 'autodescription' ),
					'escape' => false,
				] ),
				Input::make_checkbox( [
					'id'     => 'seo_bar_low_contrast',
					'label'  => \esc_html__( 'Use a reduced contrast color palette?', 'autodescription' ) . ' ' . HTML::make_info(
						\__( 'If you find the SEO Bar distracting, this may help you focus better.', 'autodescription' ),
						'',
						false,
					),
					'escape' => false,
				] ),
				Input::make_checkbox( [
					'id'     => 'seo_bar_symbols',
					'label'  => \esc_html__( 'Use symbols for warnings?', 'autodescription' ) . ' ' . HTML::make_info(
						\__( 'If you have difficulty discerning colors, this may help you spot issues more easily.', 'autodescription' ),
						'',
						false,
					),
					'escape' => false,
				] ),
			],
			true,
		);

		?>
		<hr>
		<?php
		HTML::header_title( \__( 'Counter Settings', 'autodescription' ) );

		$pixel_info = HTML::make_info(
			\__( 'The pixel counter computes whether the input will fit on search engine result pages.', 'autodescription' ),
			'https://kb.theseoframework.com/?p=48',
			false,
		);

		$character_info = HTML::make_info(
			\__( 'The character counter is based on guidelines.', 'autodescription' ),
			'',
			false,
		);

		HTML::wrap_fields(
			[
				Input::make_checkbox( [
					'id'     => 'display_pixel_counter',
					'label'  => \esc_html__( 'Display pixel counters?', 'autodescription' ) . " $pixel_info",
					'escape' => false,
				] ),
				Input::make_checkbox( [
					'id'     => 'display_character_counter',
					'label'  => \esc_html__( 'Display character counters?', 'autodescription' ) . " $character_info",
					'escape' => false,
				] ),
			],
			true,
		);
		break;

	case 'performance':
		HTML::header_title( \__( 'Performance Settings', 'autodescription' ) );
		HTML::description( \__( "Depending on your server's configuration, adjusting these settings can affect performance.", 'autodescription' ) );

		?>
		<hr>
		<?php
		HTML::header_title( \__( 'Query Alteration Settings', 'autodescription' ) );
		HTML::description_noesc(
			\esc_html__( "Altering the query allows for more control of the site's hierarchy.", 'autodescription' )
			. '<br>' .
			\esc_html__( 'If your website has thousands of pages, these options can greatly affect database performance.', 'autodescription' )
		);

		HTML::description_noesc(
			\esc_html__( 'Altering the query in the database is more accurate, but can increase database query time.', 'autodescription' )
			. '<br>' .
			\esc_html__( 'Altering the query on the site is much faster, but can lead to inconsistent pagination. It can also lead to 404 error messages if all queried pages have been excluded.', 'autodescription' )
		);

		$query_types = (array) \apply_filters(
			'the_seo_framework_query_alteration_types',
			[
				'in_query'   => \_x( 'In the database', 'Perform query alteration: In the database', 'autodescription' ),
				'post_query' => \_x( 'On the site', 'Perform query alteration: On the site', 'autodescription' ),
			],
		);

		$search_query_select_options = '';
		$_current                    = Data\Plugin::get_option( 'alter_search_query_type' );
		foreach ( $query_types as $value => $name ) {
			$search_query_select_options .= \sprintf(
				'<option value="%s" %s>%s</option>',
				\esc_attr( $value ),
				\selected( $_current, \esc_attr( $value ), false ),
				\esc_html( $name ),
			);
		}

		$archive_query_select_options = '';
		$_current                     = Data\Plugin::get_option( 'alter_archive_query_type' );
		foreach ( $query_types as $value => $name ) {
			$archive_query_select_options .= \sprintf(
				'<option value="%s" %s>%s</option>',
				\esc_attr( $value ),
				\selected( $_current, \esc_attr( $value ), false ),
				\esc_html( $name ),
			);
		}

		$perform_alteration_i18n = \esc_html__( 'Perform alteration:', 'autodescription' );

		$search_query_select_field = vsprintf(
			'<label for="%1$s"><strong>%2$s</strong></label> <select name="%3$s" id="%1$s">%4$s</select>',
			[
				Input::get_field_id( 'alter_search_query_type' ),
				$perform_alteration_i18n,
				Input::get_field_name( 'alter_search_query_type' ),
				$search_query_select_options,
			],
		);

		$archive_query_select_field = vsprintf(
			'<label for="%1$s"><strong>%2$s</strong></label> <select name="%3$s" id="%1$s">%4$s</select>',
			[
				Input::get_field_id( 'alter_archive_query_type' ),
				$perform_alteration_i18n,
				Input::get_field_name( 'alter_archive_query_type' ),
				$archive_query_select_options,
			],
		);

		HTML::wrap_fields(
			[
				Input::make_checkbox( [
					'id'     => 'alter_search_query',
					'label'  => \esc_html__( 'Enable search query alteration?', 'autodescription' )
						. ' ' . HTML::make_info( \__( 'This allows you to exclude pages from on-site search results.', 'autodescription' ), '', false ),
					'escape' => false,
				] ),
				$search_query_select_field,
			],
			true,
		);

		HTML::wrap_fields(
			[
				Input::make_checkbox( [
					'id'     => 'alter_archive_query',
					'label'  => \esc_html__( 'Enable archive query alteration?', 'autodescription' )
						. ' ' . HTML::make_info( \__( 'This allows you to exclude pages from on-site archive listings.', 'autodescription' ), '', false ),
					'escape' => false,
				] ),
				$archive_query_select_field,
			],
			true,
		);
		break;

	case 'canonical':
		HTML::header_title( \__( 'Canonical URL Settings', 'autodescription' ) );
		HTML::description( \__( 'The canonical URL meta tag urges search engines to go to the outputted URL.', 'autodescription' ) );
		HTML::description( \__( 'If the canonical URL meta tag represents the visited page, then the search engine will crawl the visited page. Otherwise, the search engine may go to the outputted URL.', 'autodescription' ) );
		?>
		<hr>
		<?php
		HTML::header_title( \__( 'Scheme Settings', 'autodescription' ) );
		HTML::description( \__( 'If your website is accessible via both HTTP as HTTPS, you may want to set this to HTTPS if not detected automatically. Secure connections are preferred by search engines.', 'autodescription' ) );

		$scheme_options  = '';
		$detected_scheme = Meta\URI\Utils::detect_site_url_scheme();
		$current_scheme  = Data\Plugin::get_option( 'canonical_scheme' );
		$scheme_types    = (array) \apply_filters(
			'the_seo_framework_canonical_scheme_types',
			[
				'automatic' => \sprintf(
					/* translators: %s = HTTP or HTTPS */
					\__( 'Detect automatically (%s)', 'autodescription' ),
					strtoupper( $detected_scheme ),
				),
				'http'      => 'HTTP',
				'https'     => 'HTTPS',
			],
		);
		foreach ( $scheme_types as $value => $name ) {
			$scheme_options .= \sprintf(
				'<option value="%s" %s>%s</option>',
				\esc_attr( $value ),
				\selected( $current_scheme, $value, false ),
				\esc_html( $name ),
			);
		}

		HTML::wrap_fields(
			vsprintf(
				'<label for="%1$s"><strong>%2$s</strong></label> <select name="%3$s" id="%1$s" %4$s>%5$s</select>',
				[
					Input::get_field_id( 'canonical_scheme' ),
					\esc_html_x( 'Preferred canonical URL scheme:', '= Detect Automatically, HTTPS, HTTP', 'autodescription' ),
					Input::get_field_name( 'canonical_scheme' ),
					HTML::make_data_attributes( [ 'values' => [ 'automatic' => $detected_scheme ] ] ),
					$scheme_options,
				],
			),
			true,
		);
		?>
		<hr>
		<?php
		HTML::header_title( \__( 'Paginated Link Relationship Settings', 'autodescription' ) );
		HTML::description( \__( 'Some search engines look for relations between the content of your pages. If you have pagination on a post or page, or have archives indexed, these options will help search engines look for the right page to display in the search results.', 'autodescription' ) );
		HTML::description( \__( 'Enable these options to mitigate duplicated content issues.', 'autodescription' ) );

		$prev_next_posts_checkbox = Input::make_checkbox( [
			'id'     => 'prev_next_posts',
			'label'  => Markdown::convert(
				/* translators: the backticks are Markdown! Preserve them as-is! */
				\esc_html__( 'Add `rel` link tags to pages?', 'autodescription' ),
				[ 'code' ],
			),
			'escape' => false,
		] );

		$prev_next_archives_checkbox = Input::make_checkbox( [
			'id'     => 'prev_next_archives',
			'label'  => Markdown::convert(
				/* translators: the backticks are Markdown! Preserve them as-is! */
				\esc_html__( 'Add `rel` link tags to archives?', 'autodescription' ),
				[ 'code' ],
			),
			'escape' => false,
		] );

		$prev_next_frontpage_checkbox = Input::make_checkbox( [
			'id'     => 'prev_next_frontpage',
			'label'  => Markdown::convert(
				/* translators: the backticks are Markdown! Preserve them as-is! */
				\esc_html__( 'Add `rel` link tags to the homepage?', 'autodescription' ),
				[ 'code' ],
			),
			'escape' => false,
		] );

		HTML::wrap_fields( $prev_next_posts_checkbox . $prev_next_archives_checkbox . $prev_next_frontpage_checkbox, true );
		break;

	case 'timestamps':
		/**
		 * @see The_SEO_Framework\Helper\Format\Time::get_preferred_format()
		 */
		$timestamp_date     = gmdate( 'Y-m-d' );
		$timestamp_datetime = gmdate( 'Y-m-d\TH:i:sP' ); // Could use 'c', but that specification is ambiguous

		HTML::header_title( \__( 'Timestamp Settings', 'autodescription' ) );
		HTML::description( \__( 'Timestamps help indicate when a page has been published and modified.', 'autodescription' ) );
		?>
		<hr>

		<fieldset>
			<legend><?php HTML::header_title( \__( 'Timestamp Format Settings', 'autodescription' ) ); ?></legend>
			<?php HTML::description( \__( 'This setting determines how specific the timestamp is.', 'autodescription' ) ); ?>

			<p id=sitemaps-timestamp-format class=tsf-fields>
				<span class=tsf-toblock>
					<input type=radio name="<?php Input::field_name( 'timestamps_format' ); ?>" id="<?php Input::field_id( 'timestamps_format_0' ); ?>" value=0 <?php \checked( Data\Plugin::get_option( 'timestamps_format' ), '0' ); ?>>
					<label for="<?php Input::field_id( 'timestamps_format_0' ); ?>">
						<?php
						// phpcs:ignore, WordPress.Security.EscapeOutput -- code_wrap escapes.
						echo HTML::code_wrap( $timestamp_date ), ' ', HTML::make_info(
							\__( 'This outputs the complete date.', 'autodescription' )
						);
						?>
					</label>
				</span>
				<span class=tsf-toblock>
					<input type=radio name="<?php Input::field_name( 'timestamps_format' ); ?>" id="<?php Input::field_id( 'timestamps_format_1' ); ?>" value=1 <?php \checked( Data\Plugin::get_option( 'timestamps_format' ), '1' ); ?>>
					<label for="<?php Input::field_id( 'timestamps_format_1' ); ?>">
						<?php
						// phpcs:ignore, WordPress.Security.EscapeOutput -- code_wrap escapes.
						echo HTML::code_wrap( $timestamp_datetime ), ' ', HTML::make_info(
							\__( 'This outputs the complete date including hours, minutes, seconds, and time zone.', 'autodescription' )
						);
						?>
					</label>
				</span>
			</p>
		</fieldset>
		<?php
		break;

	case 'exclusions':
		HTML::header_title( \__( 'Exclusion Settings', 'autodescription' ) );
		HTML::description( \__( 'Check these options to remove meta optimizations, SEO suggestions, and sitemap inclusions for selected post types and taxonomies.', 'autodescription' ) );
		HTML::attention_description_noesc( Markdown::convert(
			\sprintf(
				/* translators: backticks are code wraps. Markdown! */
				\esc_html__( "Exclusions don't block search engines. If a post type is publicly queryable and shouldn't be indexed, don't exclude it. Instead, consider applying `noindex` via Robots Settings.", 'autodescription' ),
				'#autodescription-robots-settings',
			),
			[ 'code' ],
		) );
		HTML::description( \__( 'Default post types and taxonomies can not be excluded.', 'autodescription' ) );
		?>

		<hr>
		<?php
		HTML::header_title( \__( 'Post Type Exclusions', 'autodescription' ) );
		HTML::description( \__( 'Select post types which should be excluded.', 'autodescription' ) );
		HTML::description( \__( 'These settings apply to the post type pages and their terms. When terms are shared between post types, all their post types should be checked for this to have an effect.', 'autodescription' ) );

		$forced_pt = Post_Type::get_all_forced_supported();
		$boxes     = [];

		foreach ( Post_Type::get_all_public() as $post_type ) {
			$_label = Post_Type::get_label( $post_type, false );
			if ( ! \strlen( $_label ) ) continue;

			$_label = \sprintf(
				'%s &ndash; <code>%s</code>',
				\esc_html( $_label ),
				\esc_html( $post_type ),
			);

			$boxes[] = Input::make_checkbox( [
				'id'       => [ 'disabled_post_types', $post_type ],
				'class'    => 'tsf-excluded-post-types',
				'label'    => $_label,
				'escape'   => false,
				'disabled' => \in_array( $post_type, $forced_pt, true ),
			] );
		}

		HTML::wrap_fields( $boxes, true );

		?>
		<hr>
		<?php
		HTML::header_title( \__( 'Taxonomy Exclusions', 'autodescription' ) );
		HTML::description( \__( 'Select taxonomies which should be excluded.', 'autodescription' ) );
		HTML::description( \__( 'When taxonomies have all their bound post types excluded, they will inherit their exclusion status.', 'autodescription' ) );

		$forced_tax = Taxonomy::get_all_forced_supported();
		$boxes      = [];

		foreach ( Taxonomy::get_all_public() as $taxonomy ) {
			$_label = Taxonomy::get_label( $taxonomy, false );
			if ( ! \strlen( $_label ) ) continue;

			$_label = \sprintf(
				'%s &ndash; <code>%s</code>',
				\esc_html( $_label ),
				\esc_html( $taxonomy ),
			);

			$boxes[] = Input::make_checkbox( [
				'id'       => [ 'disabled_taxonomies', $taxonomy ], // disabled_taxonomies is the option name.
				'class'    => 'tsf-excluded-taxonomies',
				'label'    => $_label,
				'escape'   => false,
				'disabled' => \in_array( $taxonomy, $forced_tax, true ),
				'data'     => [
					'postTypes' => Taxonomy::get_post_types( $taxonomy ),
				],
			] );
		}

		HTML::wrap_fields( $boxes, true );
endswitch;
